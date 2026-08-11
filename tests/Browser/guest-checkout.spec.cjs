const { test, expect } = require('@playwright/test');
const path = require('node:path');

test('guest checkout manual, tracking, proof upload, and account conversion', async ({ page }) => {
    const pageErrors = [];
    const serverErrors = [];

    page.on('pageerror', (error) => pageErrors.push(error.message));
    page.on('response', (response) => {
        if (response.url().startsWith('http://127.0.0.1:8765') && response.status() >= 500) {
            serverErrors.push(`${response.status()} ${response.url()}`);
        }
    });

    await page.route('**/rajaongkir/**', async (route) => {
        const pathname = new URL(route.request().url()).pathname;
        const responses = [
            ['/provinces', [{ id: 6, label: 'DKI Jakarta' }]],
            ['/cities', [{ id: 152, label: 'Jakarta Selatan' }]],
            ['/districts', [{ id: 2112, label: 'Setiabudi' }]],
            ['/subdistricts', [{ id: 101, destination_id: 101, label: 'Karet', zip_code: '12920' }]],
            ['/shipping-options', [{ code: 'jne', name: 'JNE', service: 'REG', etd: '1-2 hari', cost: 12000 }]],
        ];
        const data = responses.find(([suffix]) => pathname.endsWith(suffix))?.[1] || [];

        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({ data }),
        });
    });

    await page.goto('/detail-produk/baut-hex-m8-x-25mm-galvanis', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#buyNowBtn')).toBeVisible();
    await Promise.all([
        page.waitForURL('**/checkout', { waitUntil: 'domcontentloaded' }),
        page.locator('#buyNowBtn').click({ noWaitAfter: true }),
    ]);
    await expect(page.getByText('Checkout cepat tanpa akun')).toBeVisible();
    await expect(page.locator('#orderGroups')).toContainText('Baut Hex M8 x 25mm Galvanis');

    await page.locator('#guestEmail').fill('browser-guest@example.test');
    await page.locator('#checkoutRecipientName').fill('Browser Guest');
    await page.locator('#checkoutPhoneNumber').fill('081234567890');
    const selectLocation = async (inputId, optionLabel) => {
        const input = page.locator(`#${inputId}`);
        await expect(input).toBeEnabled();
        await input.fill(optionLabel);
        await page.locator(`#${inputId.replace('Input', 'Dropdown')} .co-opt`, { hasText: optionLabel }).click();
    };
    await selectLocation('checkoutProvinceInput', 'DKI Jakarta');
    await selectLocation('checkoutCityInput', 'Jakarta Selatan');
    await selectLocation('checkoutDistrictInput', 'Setiabudi');
    await selectLocation('checkoutSubdistrictInput', 'Karet');
    await expect(page.locator('#checkoutPostalCode')).toHaveValue('12920');
    await page.locator('#checkoutAddressLine').fill('Jl. Browser E2E No. 10');
    await expect(page.locator('#checkoutDestinationId')).toHaveValue('101');

    await page.getByRole('button', { name: 'Simpan Data Pengiriman' }).click();
    await expect(page.locator('[id^="shippingOptions-"] .shipping-card')).toContainText('JNE REG');

    await page.locator('#tab-manual').click();
    await page.locator('#panel-manual label').click();
    await expect(page.locator('input[name="payment"][value="manual_transfer"]')).toBeChecked();
    await Promise.all([
        page.waitForURL('**/checkout/waiting/**', { waitUntil: 'domcontentloaded' }),
        page.locator('#payBtn').click({ noWaitAfter: true }),
    ]);
    const orderId = (await page.locator('#orderNum').textContent()).trim();
    expect(orderId).toMatch(/^MAN-\d{14}-[A-Z0-9]{10}$/);
    await expect(page.getByText('Upload Bukti Transfer', { exact: true })).toBeVisible();

    await page.locator('input[name="payment_proof"]').setInputFiles(
        path.resolve(__dirname, '..', '..', 'public', 'imgs', 'qris.png'),
    );
    await page.getByRole('button', { name: 'Kirim Bukti Pembayaran' }).click();
    await expect(page.getByText('Bukti transfer sudah diupload.')).toBeVisible();

    await page.goto(`/lacak-pesanan?order_id=${encodeURIComponent(orderId)}`, { waitUntil: 'domcontentloaded' });
    await page.locator('#trackingEmail').fill('browser-guest@example.test');
    await page.locator('#trackingOrderId').fill(orderId);
    await page.getByRole('button', { name: 'Lacak', exact: true }).click();
    await expect(page.getByText('Order terverifikasi')).toBeVisible();
    await expect(page.getByText('Baut Hex M8 x 25mm Galvanis')).toBeVisible();

    await page.getByRole('link', { name: 'Buat Akun Saya' }).click();
    await expect(page.getByText('Pesanan siap dihubungkan')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toHaveValue('browser-guest@example.test');
    await page.locator('input[name="password"]').fill('browserSecret123');
    await page.locator('input[name="password_confirmation"]').fill('browserSecret123');
    await Promise.all([
        page.waitForURL('**/profil?tab=pesanan', { waitUntil: 'domcontentloaded' }),
        page.getByRole('button', { name: 'Buat Akun & Simpan Pesanan' }).click({ noWaitAfter: true }),
    ]);
    await expect(page.getByRole('heading', { name: 'Riwayat Pesanan' })).toBeVisible();
    await expect(page.getByText('Baut Hex M8 x 25mm Galvanis')).toBeVisible();
    await expect(page.getByRole('link', { name: 'Upload Bukti' })).toHaveAttribute('href', new RegExp(orderId));
    expect(pageErrors).toEqual([]);
    expect(serverErrors).toEqual([]);
});
