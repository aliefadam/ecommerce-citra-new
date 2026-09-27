const { test, expect } = require('@playwright/test');

async function login(page) {
    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await page.locator('input[name=email]').fill('aliefadam21@gmail.com');
    await page.locator('input[name=password]').fill('123123');
    await Promise.all([
        page.waitForURL((url) => !url.pathname.endsWith('/login'), { waitUntil: 'domcontentloaded' }),
        page.getByRole('button', { name: 'Sign In' }).click({ noWaitAfter: true }),
    ]);
}

async function setVariantValue(page, groupKey, value) {
    await page.locator(`select[data-group-key=${groupKey}]`).evaluate((select, selectedValue) => {
        select.tomselect.setValue(selectedValue);
    }, value);
}

test('variant selection and cart mutations work on mobile with recoverable errors', async ({ page }) => {
    const pageErrors = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));

    await login(page);
    await page.evaluate(async () => {
        const token = document.querySelector('meta[name=csrf-token]')?.content || '';
        await fetch('/cart', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
    });

    await page.goto('/detail-produk/baut-mur-baja-109', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#productMainPrice')).toHaveText('Rp 3.111');
    await expect(page.locator('#productSku')).toContainText('M10-X-20MM');
    await expect(page.locator('#productStock')).toHaveText('9 pcs');
    await expect(page.locator('#mainImg')).toHaveAttribute('src', /variant=e2e-m10-20/);

    await setVariantValue(page, 'length_mm', '25');
    await expect(page.locator('#productMainPrice')).toHaveText('Rp 3.222');
    await expect(page.locator('#productSku')).toContainText('M10-X-25MM');
    await expect(page.locator('#productStock')).toHaveText('8 pcs');
    await expect(page.locator('#mainImg')).toHaveAttribute('src', /variant=e2e-m10-25/);

    await setVariantValue(page, 'diameter', 'M12');
    await expect(page.locator('#productMainPrice')).toHaveText('Rp 4.333');
    await expect(page.locator('#productSku')).toContainText('M12-X-25MM');
    await expect(page.locator('#productStock')).toHaveText('7 pcs');
    await expect(page.locator('#mainImg')).toHaveAttribute('src', /variant=e2e-m12-25/);

    await setVariantValue(page, 'length_mm', '20');
    await expect(page.locator('#selected-diameter')).toHaveText('M10');
    await expect(page.locator('#productSku')).toContainText('M10-X-20MM');

    await setVariantValue(page, 'length_mm', '25');
    await setVariantValue(page, 'diameter', 'M12');
    await page.setViewportSize({ width: 390, height: 844 });

    await page.route('**/cart', async (route) => {
        if (route.request().method() !== 'POST') {
            await route.continue();
            return;
        }
        await route.fulfill({
            status: 422,
            contentType: 'application/json',
            body: JSON.stringify({ message: 'Simulasi stok berubah.' }),
        });
    });

    await page.locator('#mobileAddToCartBtn').click();
    await expect(page.locator('#variantDrawer')).toBeVisible();
    await page.locator('#drawerActionBtn').click();
    await expect(page.locator('#toast-msg')).toHaveText('Simulasi stok berubah.');
    await page.unroute('**/cart');

    const addResponse = page.waitForResponse((response) => {
        return response.request().method() === 'POST' && new URL(response.url()).pathname === '/cart';
    });
    await page.locator('#mobileAddToCartBtn').click();
    await page.locator('#drawerActionBtn').click();
    expect((await addResponse).ok()).toBe(true);

    await page.goto('/cart', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#cartItems')).toContainText('Baut Mur Baja 10.9');
    await expect(page.locator('#cartItems')).toContainText('M12');
    const quantityInput = page.getByRole('spinbutton', {
        name: 'Jumlah Baut Mur Baja 10.9',
        exact: true,
    });

    const updateResponse = page.waitForResponse((response) => {
        return response.request().method() === 'PATCH' && new URL(response.url()).pathname.startsWith('/cart/');
    });
    await page.getByLabel('Tambah jumlah Baut Mur Baja 10.9', { exact: true }).click();
    expect((await updateResponse).ok()).toBe(true);
    await expect(quantityInput).toHaveValue('2');

    let releaseQuantityFailure;
    await page.route('**/cart/*', async (route) => {
        if (route.request().method() !== 'PATCH') {
            await route.continue();
            return;
        }
        await new Promise((resolve) => {
            releaseQuantityFailure = resolve;
        });
        await route.fulfill({
            status: 422,
            contentType: 'application/json',
            body: JSON.stringify({ message: 'Simulasi jumlah ditolak.' }),
        });
    });

    await quantityInput.fill('3');
    await quantityInput.blur();
    await expect(quantityInput).toBeDisabled();
    await expect.poll(() => typeof releaseQuantityFailure).toBe('function');
    releaseQuantityFailure();
    await expect(page.locator('#toast-msg')).toHaveText('Simulasi jumlah ditolak.');
    await expect(quantityInput).toHaveValue('2');
    await page.unroute('**/cart/*');

    let releaseDeleteFailure;
    await page.route('**/cart/*', async (route) => {
        if (route.request().method() !== 'DELETE') {
            await route.continue();
            return;
        }
        await new Promise((resolve) => {
            releaseDeleteFailure = resolve;
        });
        await route.fulfill({
            status: 500,
            contentType: 'application/json',
            body: JSON.stringify({ message: 'Simulasi hapus gagal.' }),
        });
    });

    const removeButton = page.getByLabel('Hapus produk Baut Mur Baja 10.9 dari keranjang', { exact: true });
    await removeButton.click();
    await expect(removeButton).toBeDisabled();
    await expect.poll(() => typeof releaseDeleteFailure).toBe('function');
    releaseDeleteFailure();
    await expect(page.locator('#toast-msg')).toHaveText('Simulasi hapus gagal.');
    await expect(page.locator('#cartItems')).toContainText('Baut Mur Baja 10.9');
    await page.unroute('**/cart/*');

    const deleteResponse = page.waitForResponse((response) => {
        return response.request().method() === 'DELETE' && new URL(response.url()).pathname.startsWith('/cart/');
    });
    await page.getByLabel('Hapus produk Baut Mur Baja 10.9 dari keranjang', { exact: true }).click();
    expect((await deleteResponse).ok()).toBe(true);
    await expect(page.locator('#itemCountText')).toHaveText('Keranjang kosong');
    expect(pageErrors).toEqual([]);
});
