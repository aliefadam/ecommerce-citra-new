const { test, expect } = require('@playwright/test');

async function loginAsAdmin(page) {
    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await page.locator('input[name="email"]').fill('admin@citra.com');
    await page.locator('input[name="password"]').fill('123123');

    await Promise.all([
        page.waitForURL((url) => !url.pathname.endsWith('/login'), { waitUntil: 'domcontentloaded' }),
        page.getByRole('button', { name: 'Sign In' }).click({ noWaitAfter: true }),
    ]);
}

test('admin can log in and create a product', async ({ page }) => {
    const productName = 'Half Coupling ISO 4144 merupakan komponen fitting pipa berkualitas tinggi yang dirancang untuk kebutuhan penyambungan pipa industri maupun perpipaan umum. Fitting ini memiliki ulir di bagian dalam (female thread) dan biasa dilas secara lang';
    const serverErrors = [];

    page.on('response', (response) => {
        if (response.url().startsWith('http://127.0.0.1:8765') && response.status() >= 500) {
            serverErrors.push(`${response.status()} ${response.request().method()} ${response.url()}`);
        }
    });

    await loginAsAdmin(page);
    await expect(page).not.toHaveURL(/\/login(?:\?|$)/);

    await page.goto('/admin/products/create', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Create Product' })).toBeVisible();

    await page.locator('input[name="name"]').fill(productName);
    await page.getByPlaceholder('Cari atau tambah kategori...').fill('Kunci Pas');
    await page.getByRole('button', { name: 'Kunci Pas', exact: true }).click();
    await page.locator('input[x-model="row.priceDisplay"]').fill('125000');
    await page.locator('input[x-model="row.stockDisplay"]').fill('10');
    await page.locator('input[name="variants[0][weight_grams]"]').fill('500');
    await expect(page.getByTestId('variant-sku')).toHaveValue('Dibuat otomatis setelah disimpan');

    await page.getByRole('button', { name: 'Save Product' }).click();
    await expect(page.getByRole('dialog')).toBeVisible();
    await page.getByRole('button', { name: 'Ya, Simpan Produk' }).click();

    await page.waitForURL('**/admin/products', { waitUntil: 'domcontentloaded' });
    await expect(page.getByText(productName, { exact: true })).toBeVisible();

    const productRow = page.locator('tr').filter({ hasText: productName });
    await productRow.locator('a[title="Edit"]').click();
    await expect(page.getByTestId('variant-sku')).toHaveValue(/^SKU-\d{6,}-\d{6,}$/);
    expect(serverErrors).toEqual([]);
});
