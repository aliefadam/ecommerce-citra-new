const { test, expect } = require('@playwright/test');

test('mobile catalog supports search, detail deep links, categories, and URL filters', async ({ page }) => {
    const pageErrors = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));

    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/app', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Temukan material yang tepat' })).toBeVisible();
    await expect(page.locator('.mobile-product-card').first()).toBeVisible();
    await expect.poll(() => page.locator('img:visible').evaluateAll((images) => images.filter((image) => !image.complete || image.naturalWidth === 0).length)).toBe(0);

    const search = page.getByRole('combobox', { name: 'Cari produk' });
    await search.fill('Baut Mur Baja');
    const suggestion = page.getByRole('option').first();
    await expect(suggestion).toContainText('Baut Mur Baja 10.9');
    await search.press('ArrowDown');
    await search.press('Enter');

    await expect(page).toHaveURL(/\/app\/products\/baut-mur-baja-109$/);
    await expect(page.getByRole('heading', { name: 'Baut Mur Baja 10.9' })).toBeFocused();
    await expect(page.getByText('Pilih varian')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Tambah ke Keranjang' })).toBeVisible();

    await page.reload({ waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Baut Mur Baja 10.9' })).toBeVisible();

    await page.goto('/app/categories', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Katalog' })).toBeVisible();
    await page.locator('.mobile-category-main').first().click();
    await expect(page).toHaveURL(/\/app\/c\/[^?]+$/);
    await expect(page.locator('.mobile-product-card').first()).toBeVisible();

    await page.getByText('Filter & urutkan').click();
    await page.getByLabel('Urutkan').selectOption('price_asc');
    await page.getByLabel('Hanya stok tersedia').check();
    await page.getByRole('button', { name: 'Terapkan' }).click();
    await expect(page).toHaveURL(/sort=price_asc/);
    await expect(page).toHaveURL(/in_stock=1/);
    await expect(page.locator('.mobile-product-card').first()).toBeVisible();

    await page.goto('/app/search?q=produk-yang-tidak-ada-e2e', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: /Hasil/ })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Produk tidak ditemukan' })).toBeVisible();

    expect(pageErrors).toEqual([]);
});

test('mobile catalog reflows across the phase 2 viewport matrix and restores scroll', async ({ page }) => {
    const viewports = [
        { width: 360, height: 800 },
        { width: 430, height: 932 },
        { width: 767, height: 1024 },
    ];

    for (const viewport of viewports) {
        await page.setViewportSize(viewport);
        await page.goto('/app/search', { waitUntil: 'domcontentloaded' });
        await expect(page.locator('.mobile-product-card').first()).toBeVisible();

        const dimensions = await page.evaluate(() => ({
            documentWidth: document.documentElement.scrollWidth,
            viewportWidth: document.documentElement.clientWidth,
            shellWidth: document.querySelector('[data-testid="mobile-spa-shell"]')?.getBoundingClientRect().width,
        }));
        expect(dimensions.documentWidth).toBeLessThanOrEqual(dimensions.viewportWidth);
        expect(dimensions.shellWidth).toBeLessThanOrEqual(430);
    }

    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/app/search', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('.mobile-product-card').nth(6)).toBeVisible();
    await page.locator('.mobile-product-card').nth(6).scrollIntoViewIfNeeded();
    const savedScroll = await page.evaluate(() => window.scrollY);
    expect(savedScroll).toBeGreaterThan(100);
    await page.locator('.mobile-product-card').nth(6).click();
    await expect(page).toHaveURL(/\/app\/products\//);
    await page.goBack();
    await expect(page).toHaveURL(/\/app\/search$/);
    await expect.poll(() => page.evaluate(() => window.scrollY)).toBeGreaterThan(100);
});

test('mobile catalog recovers from API failure and reports offline state', async ({ page, context }) => {
    let failOnce = true;
    await page.route('**/api/mobile/v1/products*', async (route) => {
        if (failOnce) {
            failOnce = false;
            await route.fulfill({
                status: 503,
                contentType: 'application/json',
                body: JSON.stringify({ code: 'server_error', message: 'Katalog sementara tidak tersedia.', request_id: 'e2e-request' }),
            });
            return;
        }

        await route.continue();
    });

    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/app/search', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Produk gagal dimuat' })).toBeVisible();
    await expect(page.getByText('Katalog sementara tidak tersedia.')).toBeVisible();
    await page.getByRole('button', { name: 'Coba Lagi' }).click();
    await expect(page.locator('.mobile-product-card').first()).toBeVisible();

    await context.setOffline(true);
    await expect(page.getByText('Anda sedang offline. Beberapa fungsi belum tersedia.')).toBeVisible();
    await context.setOffline(false);
    await expect(page.getByText('Anda sedang offline. Beberapa fungsi belum tersedia.')).toBeHidden();
});
