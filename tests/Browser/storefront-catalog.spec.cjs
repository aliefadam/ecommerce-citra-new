const { test, expect } = require('@playwright/test');

async function expectNoHorizontalOverflow(page) {
    const sizes = await page.evaluate(() => ({
        viewport: window.innerWidth,
        document: document.documentElement.scrollWidth,
    }));
    expect(sizes.document).toBeLessThanOrEqual(sizes.viewport);
}

test('Sprint 3 catalog cards and filter state work on desktop', async ({ page }) => {
    const pageErrors = [];
    const consoleErrors = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));
    page.on('console', (message) => {
        if (message.type() === 'error') consoleErrors.push(message.text());
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    const homeCard = page.locator('#productGrid .store-product-card').first();
    await expect(homeCard).toBeVisible();
    await expect(homeCard.locator('.store-product-variant')).not.toHaveText('');
    await expect(homeCard.locator('.store-product-price')).toContainText('Rp');
    await expect(homeCard).toContainText(/Stok (?:[\d.]+ pcs|habis)/);
    await expect(homeCard).toContainText('Satuan pcs');
    await expect(homeCard.locator('.store-product-seller')).not.toHaveText('');
    const canonical = await page.locator('link[rel=canonical]').getAttribute('href');
    expect(new URL(canonical).pathname).toBe('/');
    await expectNoHorizontalOverflow(page);

    await page.goto('/kategori', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#prodGrid .store-product-card').first()).toBeVisible();
    await page.locator('#filterStock').check();
    await page.locator('#sortSel').selectOption('expensive');
    await expect(page.locator('#activeFilters')).toBeVisible();
    await expect(page.locator('#activeFilters')).toContainText('Stok tersedia');
    await expectNoHorizontalOverflow(page);

    await page.locator('#prodGrid .store-product-card a[aria-label^="Lihat"]').first().click();
    await expect(page).toHaveURL(/\/detail-produk\//);
    await page.goBack({ waitUntil: 'domcontentloaded' });
    await expect(page.locator('#filterStock')).toBeChecked();
    await expect(page.locator('#sortSel')).toHaveValue('expensive');
    await expect(page.locator('#activeFilters')).toContainText('Stok tersedia');

    await page.goto('/pencarian?q=baut', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#searchResultGrid .store-product-card').first()).toBeVisible();
    await expect(page.locator('meta[name=robots]')).toHaveAttribute('content', 'noindex, follow');
    await expectNoHorizontalOverflow(page);

    expect(pageErrors).toEqual([]);
    expect(consoleErrors).toEqual([]);
});

test('Sprint 3 mobile discovery, drawer, empty state, and expired promo are safe', async ({ page }) => {
    const pageErrors = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));
    await page.setViewportSize({ width: 390, height: 844 });

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    const firstProduct = page.locator('#productGrid .store-product-card').first();
    await expect(firstProduct).toBeVisible();
    const wishlistTarget = await firstProduct.locator('.store-wishlist-button').boundingBox();
    expect(wishlistTarget.width).toBeGreaterThanOrEqual(44);
    expect(wishlistTarget.height).toBeGreaterThanOrEqual(44);
    const productBox = await firstProduct.boundingBox();
    expect(productBox.y).toBeLessThan(2400);
    await expectNoHorizontalOverflow(page);

    await page.goto('/kategori', { waitUntil: 'domcontentloaded' });
    const mobileFilterButton = page.locator('main button[onclick="openMobileFilter()"]');
    const filterTarget = await mobileFilterButton.boundingBox();
    expect(filterTarget.width).toBeGreaterThanOrEqual(44);
    expect(filterTarget.height).toBeGreaterThanOrEqual(44);
    await mobileFilterButton.click();
    await expect(page.locator('#filterSidebar')).toHaveClass(/mobile-filter-open/);
    await expect(page.locator('#filterPanel')).toBeVisible();
    await expectNoHorizontalOverflow(page);
    await expect(page.locator('#filterPanel button').first()).toBeFocused();
    await page.locator('#filterStock').check();
    await expect(page.locator('#activeFilters')).toContainText('Stok tersedia');
    await page.keyboard.press('Escape');
    await expect(page.locator('#filterSidebar')).not.toHaveClass(/mobile-filter-open/);
    await expect(mobileFilterButton).toBeFocused();

    await page.goto('/pencarian?q=produk-yang-pasti-tidak-ada-e2e', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#emptyState')).toBeVisible();
    await expect(page.locator('#emptyState')).toContainText('Produk tidak ditemukan');
    await expect(page.locator('#emptyState').getByRole('button', { name: 'Reset Filter' })).toBeVisible();

    await page.goto('/flash-sale', { waitUntil: 'domcontentloaded' });
    if (await page.locator('[data-end-at]').count()) {
        await page.locator('[data-end-at]').evaluateAll((containers) => {
            containers.forEach((container) => container.setAttribute('data-end-at', '2000-01-01T00:00:00Z'));
            window.updateAllCampaignTimers();
        });
        await expect(page.locator('[data-expired-message]')).toBeVisible();
        await expect(page.locator('[data-campaign-container]').first()).toBeHidden();
    } else {
        await expect(page.getByText('Belum ada flash sale aktif saat ini.', { exact: true })).toBeVisible();
    }

    expect(pageErrors).toEqual([]);
});
