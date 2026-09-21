const { test, expect } = require('@playwright/test');

test('global shell supports keyboard menus and stable mobile navigation', async ({ page }, testInfo) => {
    const pageErrors = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    const searchCategoryTrigger = page.locator('#ecNavCategoryTrigger');
    const searchCategoryDropdown = page.locator('#ecNavCategoryDropdown');
    const searchCategoryInput = page.locator('#ecNavCategory');

    await searchCategoryTrigger.click();
    await expect(searchCategoryTrigger).toHaveAttribute('aria-expanded', 'true');
    await expect(searchCategoryDropdown).toBeVisible();
    const firstCategory = searchCategoryDropdown.locator('[data-category-value]:not([data-category-value=""])').first();
    const firstCategoryValue = await firstCategory.getAttribute('data-category-value');
    const firstCategoryLabel = await firstCategory.getAttribute('data-category-label');
    await firstCategory.click();
    await expect(searchCategoryDropdown).toBeHidden();
    await expect(searchCategoryInput).toHaveValue(firstCategoryValue);
    await expect(searchCategoryTrigger.locator('[data-search-category-label]')).toHaveText(firstCategoryLabel);

    const categoryTrigger = page.locator('#ecCategoryTrigger');
    const categoryDropdown = page.locator('#ecCategoryDropdown');

    await categoryTrigger.focus();
    await categoryTrigger.press('Enter');
    await expect(categoryTrigger).toHaveAttribute('aria-expanded', 'true');
    await expect(categoryDropdown).toBeVisible();
    await page.screenshot({ path: testInfo.outputPath('sprint-2-shell-desktop.png'), fullPage: false });
    await page.keyboard.press('Escape');
    await expect(categoryTrigger).toHaveAttribute('aria-expanded', 'false');
    await expect(categoryDropdown).toBeHidden();

    await page.setViewportSize({ width: 390, height: 844 });
    await expect(page.locator('.ec-site-header')).toHaveCSS('display', 'contents');
    await expect(page.locator('.ec-header-main')).toHaveCSS('position', 'sticky');
    const mobileSearchTrigger = page.locator('#ecMobileSearchToggle');
    await mobileSearchTrigger.click();
    await expect(page.locator('#ecNavSearchMobile')).toBeFocused();
    await expect(page.locator('nav[aria-label="Navigasi cepat"]')).toBeVisible();
    await expect(page.locator('nav[aria-label="Navigasi cepat"] a')).toHaveCount(5);
    await expect(page.locator('footer a[href*="kebijakan-privasi"]')).toBeVisible();
    await page.screenshot({ path: testInfo.outputPath('sprint-2-shell-mobile.png'), fullPage: false });
    await page.evaluate(() => window.scrollTo(0, 600));
    await expect.poll(async () => Math.round((await page.locator('.ec-header-main').boundingBox()).y)).toBe(0);
    const primaryNavBox = await page.locator('.ec-primary-nav').boundingBox();
    expect(primaryNavBox.y + primaryNavBox.height).toBeLessThan(0);

    await page.goto('/detail-produk/baut-hex-m8-x-25mm-galvanis', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('nav[aria-label="Navigasi cepat"]')).toHaveCount(0);
    await expect(page.locator('.product-variant-grid')).toBeHidden();

    const stickyActions = page.locator('#mobileStickyActions');
    const stickyActionsBox = await stickyActions.boundingBox();
    expect(Math.abs((stickyActionsBox.y + stickyActionsBox.height) - 844)).toBeLessThanOrEqual(1);

    await page.locator('#mobileBuyNowBtn').click();
    const variantDrawer = page.locator('#variantDrawer');
    await expect(variantDrawer).toBeVisible();
    const drawerBox = await variantDrawer.boundingBox();
    expect(Math.abs(drawerBox.x)).toBeLessThanOrEqual(1);
    expect(Math.abs(drawerBox.width - 390)).toBeLessThanOrEqual(1);

    await Promise.all([
        page.waitForURL('**/checkout', { waitUntil: 'domcontentloaded' }),
        page.locator('#drawerActionBtn').click({ noWaitAfter: true }),
    ]);
    await expect(page.locator('#mobilePayBtn')).toBeVisible();
    await expect(page.locator('#payBtn')).toBeHidden();
    await expect.poll(async () => ({
        desktop: (await page.locator('#grandTotal').textContent()).trim(),
        mobile: (await page.locator('#mobileCheckoutTotal').textContent()).trim(),
    })).toEqual(expect.objectContaining({
        desktop: expect.stringMatching(/^Rp /),
        mobile: expect.stringMatching(/^Rp /),
    }));
    expect((await page.locator('#mobileCheckoutTotal').textContent()).trim())
        .toBe((await page.locator('#grandTotal').textContent()).trim());

    expect(pageErrors).toEqual([]);
});
