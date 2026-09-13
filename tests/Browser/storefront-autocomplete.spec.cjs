const { test, expect } = require('@playwright/test');

test('storefront autocomplete supports async states and keyboard navigation', async ({ page }) => {
    const pageErrors = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));

    const appOrigin = `http://127.0.0.1:${process.env.PLAYWRIGHT_PORT || '8765'}`;
    await page.route(/^https?:\/\//, (route) => {
        const requestOrigin = new URL(route.request().url()).origin;
        return requestOrigin === appOrigin ? route.continue() : route.abort();
    });

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('nav').first()).toHaveCSS('position', 'sticky');
    const input = page.locator('#ecNavSearchDesktop');
    const dropdown = page.locator('#ecNavSearchDropdownDesktop');

    await input.fill('b');
    await expect(dropdown).toContainText('Ketik minimal 2 karakter');

    const suggestionsResponse = page.waitForResponse((response) => (
        response.url().includes('/pencarian/saran') && response.status() === 200
    ));
    await input.fill('baut');
    await suggestionsResponse;
    await expect(dropdown.locator('[role="option"]').first()).toBeVisible();
    await expect(input).toHaveAttribute('aria-expanded', 'true');

    await input.press('ArrowDown');
    const activeOption = dropdown.locator('[role="option"][aria-selected="true"]');
    await expect(activeOption).toHaveCount(1);
    const destination = await activeOption.getAttribute('href');
    await Promise.all([
        page.waitForURL((url) => url.pathname === new URL(destination).pathname, { waitUntil: 'domcontentloaded' }),
        input.press('Enter'),
    ]);

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    const freshInput = page.locator('#ecNavSearchDesktop');
    const freshDropdown = page.locator('#ecNavSearchDropdownDesktop');
    await freshInput.fill('produk-yang-pasti-tidak-ada-987654');
    await expect(freshDropdown).toContainText('Produk tidak ditemukan');

    await freshInput.press('Escape');
    await expect(freshInput).toHaveAttribute('aria-expanded', 'false');
    await expect(freshDropdown).toBeHidden();
    expect(pageErrors).toEqual([]);
});
