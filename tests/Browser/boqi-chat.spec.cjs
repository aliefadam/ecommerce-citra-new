const { test, expect } = require('@playwright/test');

const mockBoqi = async (page) => {
    const requests = [];
    await page.route('**/chat-ai', async (route) => {
        requests.push(route.request().postDataJSON());
        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify({
                message: 'Saya menemukan Equal Tee. Panduan: https://example.com/help',
                reset: false,
                products: [{
                    id: 101,
                    name: 'Equal Tee PVC Diameter 15 mm',
                    slug: 'equal-tee-pvc-15-mm',
                    url: '/detail-produk/equal-tee-pvc-15-mm',
                    image: '/imgs/product-placeholder.svg',
                    price_min: 25000,
                    price_max: 30000,
                    in_stock: true,
                }],
            }),
        });
    });

    return requests;
};

test('Tanya BOQI supports quick replies, product cards, new chat, and WhatsApp handoff', async ({ page }) => {
    const requests = await mockBoqi(page);
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const launcher = page.getByTestId('boqi-launcher');
    await expect(launcher).toContainText('Tanya BOQI');
    await expect(launcher.locator('img')).toHaveAttribute('src', /boqi-mascot\.webp/);
    await launcher.click();

    await expect(page.getByRole('heading', { name: 'BOQI' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Cari Produk' })).toBeVisible();
    const handoff = page.getByRole('link', { name: 'Hubungi CS' });
    await expect(handoff).toHaveAttribute('href', /wa\.me/);
    await expect(handoff).toHaveAttribute('href', /text=/);

    await page.getByRole('button', { name: 'Cari Produk' }).click();
    await expect.poll(() => requests.length).toBe(1);
    expect(requests[0].message).toContain('merek, ukuran, spesifikasi, atau harga');

    const product = page.getByTestId('boqi-product-card');
    await expect(product).toContainText('Equal Tee PVC Diameter 15 mm');
    await expect(product).toContainText(/Rp\s?25\.000/);
    await expect(handoff).toHaveAttribute('href', /Ringkasan(\+|%20)pertanyaan/);
    await expect(product).toHaveAttribute('href', '/detail-produk/equal-tee-pvc-15-mm');
    await expect(page.locator('#storefrontAiMessages a[href="https://example.com/help"]')).toBeVisible();

    await page.getByTestId('boqi-new-chat').click();
    await expect(product).toHaveCount(0);
    await expect(page.locator('#storefrontAiMessages')).toContainText('Halo, saya BOQI!');
});

test('mobile app opens BOQI from its single home launcher and keeps the same actions', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await mockBoqi(page);
    await page.goto('/app', { waitUntil: 'domcontentloaded' });

    const launcher = page.getByRole('button', { name: /Tanya BOQI/ });
    await expect(launcher).toHaveCount(1);
    await launcher.click();
    await expect(page.getByRole('heading', { name: 'BOQI' })).toBeVisible();

    await page.getByRole('button', { name: 'Cari Produk' }).click();
    await expect(page.getByTestId('boqi-product-card-mobile')).toContainText('Equal Tee PVC Diameter 15 mm');
    await expect(page.getByRole('link', { name: 'Hubungi CS' })).toHaveAttribute('href', /Ringkasan(\+|%20)pertanyaan/);

    await page.getByTestId('boqi-new-chat-mobile').click();
    await expect(page.getByTestId('boqi-product-card-mobile')).toHaveCount(0);
});
