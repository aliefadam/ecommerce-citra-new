const { test, expect } = require('@playwright/test');
const { mkdirSync } = require('node:fs');
const path = require('node:path');

test.skip(process.env.GENERATE_GUIDE_SCREENSHOTS !== '1', 'Run explicitly to refresh shopping-guide assets.');

test('generate shopping-guide screenshots from the real storefront flow', async ({ page }) => {
    const outputDirectory = path.resolve(__dirname, '..', '..', 'public', 'imgs', 'how-to-shop');
    mkdirSync(outputDirectory, { recursive: true });
    await page.setViewportSize({ width: 1280, height: 800 });

    const capture = async (name) => {
        await page.locator('img').evaluateAll(async (images) => {
            const replacements = images
                .filter((image) => image.complete && image.naturalWidth === 0)
                .map((image) => new Promise((resolve) => {
                    image.addEventListener('load', resolve, { once: true });
                    image.addEventListener('error', resolve, { once: true });
                    image.src = '/storage/product-variants/3fa7018c-21b6-41ca-bb12-f91db7970560.webp';
                }));

            await Promise.all(replacements);
        });
        await page.screenshot({
            path: path.join(outputDirectory, name),
            type: 'jpeg',
            quality: 82,
            animations: 'disabled',
        });
    };

    await page.goto('/kategori', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#prodGrid .store-product-card').first()).toBeVisible();
    await capture('step-1.jpg');

    await page.goto('/detail-produk/baut-hex-m8-x-25mm-galvanis', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#addToCartBtn')).toBeVisible();
    await capture('step-2.jpg');

    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await page.locator('input[name=email]').fill('aliefadam21@gmail.com');
    await page.locator('input[name=password]').fill('123123');
    await Promise.all([
        page.waitForURL((url) => !url.pathname.endsWith('/login'), { waitUntil: 'domcontentloaded' }),
        page.locator('form button[type=submit]').first().click({ noWaitAfter: true }),
    ]);
    await page.goto('/detail-produk/baut-hex-m8-x-25mm-galvanis', { waitUntil: 'domcontentloaded' });

    const cartResponse = page.waitForResponse((response) => {
        const request = response.request();

        return request.method() === 'POST' && new URL(response.url()).pathname === '/cart';
    });
    await page.locator('#addToCartBtn').click();
    expect((await cartResponse).ok()).toBe(true);

    await page.goto('/cart', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#cartItems')).toContainText('Baut Hex M8 x 25mm Galvanis');
    await capture('step-3.jpg');

    await page.goto('/detail-produk/baut-hex-m8-x-25mm-galvanis', { waitUntil: 'domcontentloaded' });
    await Promise.all([
        page.waitForURL('**/checkout', { waitUntil: 'domcontentloaded' }),
        page.locator('#buyNowBtn').click({ noWaitAfter: true }),
    ]);
    await expect(page.getByText('Alamat Pengiriman', { exact: true })).toBeVisible();
    await expect(page.locator('[id^="shippingOptions-"] .shipping-card').first()).toBeVisible();
    await capture('step-4.jpg');

    await page.locator('#tab-manual').click();
    await page.locator('#panel-manual label').click();
    await Promise.all([
        page.waitForURL('**/checkout/orders?ids=*', { waitUntil: 'domcontentloaded' }),
        page.locator('#payBtn').click({ noWaitAfter: true }),
    ]);
    await expect(page.getByRole('link', { name: 'Lihat Pembayaran' })).toBeVisible();
    const orderId = new URL(page.url()).searchParams.get('ids').split(',')[0];
    await capture('step-5.jpg');

    await page.goto(`/lacak-pesanan?order_id=${encodeURIComponent(orderId)}`, { waitUntil: 'domcontentloaded' });
    await page.locator('#trackingEmail').fill('aliefadam21@gmail.com');
    await page.getByRole('button', { name: 'Lacak', exact: true }).click();
    await expect(page.getByText('Order terverifikasi')).toBeVisible();
    await capture('step-6.jpg');
});
