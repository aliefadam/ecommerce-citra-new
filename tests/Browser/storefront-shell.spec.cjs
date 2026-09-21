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
    await expect(page.locator('.ec-site-header')).toHaveCSS('position', 'relative');
    const mobileSearchTrigger = page.locator('#ecMobileSearchToggle');
    await mobileSearchTrigger.click();
    await expect(page.locator('#ecNavSearchMobile')).toBeFocused();
    await expect(page.locator('nav[aria-label="Navigasi cepat"]')).toBeVisible();
    await expect(page.locator('nav[aria-label="Navigasi cepat"] a')).toHaveCount(5);
    await expect(page.locator('footer a[href*="kebijakan-privasi"]')).toBeVisible();
    await page.screenshot({ path: testInfo.outputPath('sprint-2-shell-mobile.png'), fullPage: false });

    expect(pageErrors).toEqual([]);
});
