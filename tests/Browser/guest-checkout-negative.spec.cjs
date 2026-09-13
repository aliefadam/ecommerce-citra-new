const { test, expect } = require('@playwright/test');
const { execFileSync } = require('node:child_process');
const path = require('node:path');

const root = path.resolve(__dirname, '..', '..');
const database = path.resolve(root, 'storage', 'framework', 'testing', 'browser-e2e.sqlite');

async function prepareGuestCheckout(page, { email = 'negative-guest@example.test', shippingFailure = false } = {}) {
    const appOrigin = `http://127.0.0.1:${process.env.PLAYWRIGHT_PORT || '8765'}`;
    await page.route(/^https?:\/\//, (route) => {
        const requestOrigin = new URL(route.request().url()).origin;
        return requestOrigin === appOrigin ? route.continue() : route.abort();
    });
    await page.route('**/rajaongkir/**', async (route) => {
        const pathname = new URL(route.request().url()).pathname;
        if (shippingFailure && pathname.endsWith('/shipping-options')) {
            await route.fulfill({ status: 503, contentType: 'application/json', body: JSON.stringify({ message: 'Courier unavailable' }) });
            return;
        }

        const responses = [
            ['/provinces', [{ id: 6, label: 'DKI Jakarta' }]],
            ['/cities', [{ id: 152, label: 'Jakarta Selatan' }]],
            ['/districts', [{ id: 2112, label: 'Setiabudi' }]],
            ['/subdistricts', [{ id: 101, destination_id: 101, label: 'Karet', zip_code: '12920' }]],
            ['/shipping-options', [{ code: 'jne', name: 'JNE', service: 'REG', etd: '1-2 hari', cost: 12000 }]],
        ];
        const data = responses.find(([suffix]) => pathname.endsWith(suffix))?.[1] || [];
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ data }) });
    });

    await page.goto('/detail-produk/baut-hex-m8-x-25mm-galvanis', { waitUntil: 'domcontentloaded' });
    const variantId = Number(await page.locator('#buyNowVariantId').inputValue());
    const originalStock = Number(await page.locator('#qtyDisplay').getAttribute('max'));
    await Promise.all([
        page.waitForURL('**/checkout', { waitUntil: 'domcontentloaded' }),
        page.locator('#buyNowBtn').click({ noWaitAfter: true }),
    ]);

    await page.locator('#guestEmail').fill(email);
    await page.locator('#checkoutRecipientName').fill('Negative Browser Guest');
    await page.locator('#checkoutPhoneNumber').fill('081234567890');

    for (const [inputId, optionLabel] of [
        ['checkoutProvinceInput', 'DKI Jakarta'],
        ['checkoutCityInput', 'Jakarta Selatan'],
        ['checkoutDistrictInput', 'Setiabudi'],
        ['checkoutSubdistrictInput', 'Karet'],
    ]) {
        const input = page.locator(`#${inputId}`);
        await expect(input).toBeEnabled();
        await input.fill(optionLabel);
        await page.locator(`#${inputId.replace('Input', 'Dropdown')} .co-opt`, { hasText: optionLabel }).click();
    }

    await page.locator('#checkoutAddressLine').fill('Jl. Negative E2E No. 20');
    await page.getByRole('button', { name: 'Simpan Data Pengiriman' }).click();

    return { variantId, originalStock };
}

async function chooseManualTransfer(page) {
    await expect(page.locator('[id^="shippingOptions-"] .shipping-card')).toContainText('JNE REG');
    await page.locator('#tab-manual').click();
    await page.locator('#panel-manual label').click();
    await expect(page.locator('input[name="payment"][value="manual_transfer"]')).toBeChecked();
}

async function createManualOrder(page) {
    await chooseManualTransfer(page);
    await Promise.all([
        page.waitForURL('**/checkout/waiting/**', { waitUntil: 'domcontentloaded' }),
        page.locator('#payBtn').click({ noWaitAfter: true }),
    ]);
    return (await page.locator('#orderNum').textContent()).trim();
}

function setVariantStock(variantId, stock) {
    const dsn = `sqlite:${database.replaceAll('\\', '/')}`;
    const script = `$pdo = new PDO(${JSON.stringify(dsn)}); $statement = $pdo->prepare('UPDATE product_variants SET stock = ? WHERE id = ?'); $statement->execute([${stock}, ${variantId}]);`;
    execFileSync('php', ['-r', script], { cwd: root });
}

test('shipping failure keeps checkout blocked and shows a useful error', async ({ page }) => {
    await prepareGuestCheckout(page, { shippingFailure: true });
    await expect(page.getByText('Gagal memuat ongkir RajaOngkir.')).toBeVisible();
    await expect(page.locator('#payBtn')).toBeDisabled();
    await expect(page).toHaveURL(/\/checkout$/);
});

test('registered email is sent to login without creating a guest order', async ({ page }) => {
    await prepareGuestCheckout(page, { email: 'aliefadam21@gmail.com' });
    await chooseManualTransfer(page);
    await Promise.all([
        page.waitForURL('**/login**', { waitUntil: 'domcontentloaded' }),
        page.locator('#payBtn').click({ noWaitAfter: true }),
    ]);
    await expect(page).toHaveURL(/\/login\?redirect=/);
    await expect(page.locator('body')).toContainText('Login');
});

test('stock sold out after Buy Now is rejected at payment time', async ({ page }) => {
    const { variantId, originalStock } = await prepareGuestCheckout(page, { email: 'sold-out@example.test' });
    await chooseManualTransfer(page);

    let dialogMessage = '';
    page.once('dialog', async (dialog) => {
        dialogMessage = dialog.message();
        await dialog.accept();
    });

    try {
        setVariantStock(variantId, 0);
        await page.locator('#payBtn').click();
        await expect.poll(() => dialogMessage).toContain('Stok salah satu produk sudah habis.');
        await expect(page).toHaveURL(/\/checkout$/);
    } finally {
        setVariantStock(variantId, originalStock);
    }
});

test('invalid proof is rejected and another session cannot access the order', async ({ page, browser }) => {
    await prepareGuestCheckout(page, { email: 'invalid-proof@example.test' });
    const orderId = await createManualOrder(page);

    await page.locator('input[name="payment_proof"]').setInputFiles(path.resolve(root, 'package.json'));
    await page.getByRole('button', { name: 'Kirim Bukti Pembayaran' }).click();
    await expect(page.getByText('Bukti pembayaran harus berupa gambar.')).toBeVisible();
    await expect(page.getByText('Upload Bukti Transfer', { exact: true })).toBeVisible();

    const foreignContext = await browser.newContext();
    const foreignPage = await foreignContext.newPage();
    await foreignPage.goto(`/checkout/waiting/${encodeURIComponent(orderId)}`, { waitUntil: 'domcontentloaded' });
    await expect(foreignPage).toHaveURL(/\/login(?:\?|$)/);
    await expect(foreignPage.locator('body')).not.toContainText(orderId);
    await foreignContext.close();
});
