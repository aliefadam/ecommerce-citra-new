const { test, expect } = require('@playwright/test');

test('member checkout separates products from two companies into independent orders', async ({ page }) => {
    const pageErrors = [];
    const serverErrors = [];

    page.on('pageerror', (error) => pageErrors.push(error.message));
    page.on('response', (response) => {
        if (response.url().startsWith('http://127.0.0.1:8765') && response.status() >= 500) {
            serverErrors.push(`${response.status()} ${response.url()}`);
        }
    });

    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await page.locator('input[name=email]').fill('aliefadam21@gmail.com');
    await page.locator('input[name=password]').fill('123123');
    await Promise.all([
        page.waitForURL((url) => !url.pathname.endsWith('/login'), { waitUntil: 'domcontentloaded' }),
        page.getByRole('button', { name: 'Sign In' }).click({ noWaitAfter: true }),
    ]);

    const addProductToCart = async (slug) => {
        await page.goto(`/detail-produk/${slug}`, { waitUntil: 'domcontentloaded' });
        const cartResponse = page.waitForResponse((response) => {
            const request = response.request();

            return request.method() === 'POST' && new URL(response.url()).pathname === '/cart';
        });
        await page.locator('#addToCartBtn').click();
        expect((await cartResponse).ok()).toBe(true);
    };

    await addProductToCart('baut-hex-m8-x-25mm-galvanis');
    await addProductToCart('mur-hex-m8-pt-dua-e2e');

    await page.goto('/cart', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#cartItems')).toContainText('Baut Hex M8 x 25mm Galvanis');
    await expect(page.locator('#cartItems')).toContainText('Mur Hex M8 PT Dua E2E');
    await expect(page.locator('#itemCountText')).toHaveText('2 item dipilih');
    await Promise.all([
        page.waitForURL('**/checkout', { waitUntil: 'domcontentloaded' }),
        page.locator('#checkoutBtn').click({ noWaitAfter: true }),
    ]);

    await expect(page.locator('#orderGroups')).toContainText('BOQ');
    await expect(page.locator('#orderGroups')).toContainText('PT Dua Sejahtera');
    await expect(page.locator('[id^=shippingOptions-]')).toHaveCount(2);
    for (const shippingGroup of await page.locator('[id^=shippingOptions-]').all()) {
        await expect(shippingGroup.locator('.shipping-card').first()).toBeVisible();
    }

    await page.locator('#tab-manual').click();
    await page.locator('#panel-manual label').click();
    await expect(page.locator('input[name=payment][value=manual_transfer]')).toBeChecked();
    await Promise.all([
        page.waitForURL('**/checkout/orders?ids=*', { waitUntil: 'domcontentloaded' }),
        page.locator('#payBtn').click({ noWaitAfter: true }),
    ]);

    const orderIds = new URL(page.url()).searchParams.get('ids').split(',');
    expect(orderIds).toHaveLength(2);
    expect(new Set(orderIds).size).toBe(2);
    for (const orderId of orderIds) {
        expect(orderId).toMatch(/^MAN-\d{14}-[A-Z0-9]{10}$/);
    }

    await expect(page.getByRole('heading', { name: '2 Pesanan Berhasil Dibuat' })).toBeVisible();
    await expect(page.getByText('BOQ', { exact: true })).toBeVisible();
    await expect(page.getByText('PT Dua Sejahtera', { exact: true })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Lihat Pembayaran' })).toHaveCount(2);

    await page.goto('/cart', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#itemCountText')).toHaveText('Keranjang kosong');
    expect(pageErrors).toEqual([]);
    expect(serverErrors).toEqual([]);
});
