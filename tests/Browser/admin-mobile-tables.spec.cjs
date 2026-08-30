const { test, expect } = require('@playwright/test');

const mobilePages = [
    ['/admin/transactions', 1],
    ['/admin/products', 1],
    ['/admin/main-categories', 1],
    ['/admin/admin-users', 1],
    ['/admin/product-reviews', 1],
    ['/admin/newsletter-subscribers', 2],
    ['/admin/reports/stock', 2],
    ['/admin/tax-invoices', 1],
    ['/admin/sales-orders', 1],
    ['/admin/api-docs', 1],
];

async function loginAsAdmin(page) {
    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await page.locator('input[name="email"]').fill('admin@citra.com');
    await page.locator('input[name="password"]').fill('123123');
    await Promise.all([
        page.waitForURL((url) => !url.pathname.endsWith('/login'), { waitUntil: 'domcontentloaded' }),
        page.getByRole('button', { name: 'Sign In' }).click({ noWaitAfter: true }),
    ]);
}

test('selected admin data tables become readable cards on mobile and stay tables on desktop', async ({ page }) => {
    const pageErrors = [];
    const serverErrors = [];

    page.on('pageerror', (error) => pageErrors.push(error.message));
    page.on('response', (response) => {
        if (response.url().startsWith('http://127.0.0.1:8765') && response.status() >= 500) {
            serverErrors.push(`${response.status()} ${response.url()}`);
        }
    });

    await page.setViewportSize({ width: 390, height: 844 });
    await loginAsAdmin(page);

    for (const [url, expectedTables] of mobilePages) {
        await page.goto(url, { waitUntil: 'domcontentloaded' });
        const tables = page.locator('table[data-mobile-cards]');
        await expect(tables, `${url} should expose the intended mobile-card tables`).toHaveCount(expectedTables);

        for (let index = 0; index < expectedTables; index += 1) {
            const table = tables.nth(index);
            await expect.poll(() => table.evaluate((element) => getComputedStyle(element).display), {
                message: `${url} table ${index + 1} should use the mobile card layout`,
            }).toBe('block');
            await expect(table.locator('thead')).toHaveCSS('display', 'none');

            const firstRow = table.locator('tbody tr').first();
            await expect(firstRow).toBeAttached();
            await expect.poll(() => firstRow.evaluate((element) => getComputedStyle(element).display), {
                message: `${url} table ${index + 1} rows should render as cards`,
            }).toBe('grid');

            const cells = firstRow.locator('td');
            if (await cells.count() > 1) {
                await expect(firstRow.locator('td[data-mobile-primary]')).toHaveCount(1);
                const labelledCells = firstRow.locator('td[data-mobile-label]:not([data-mobile-primary]):not([data-mobile-select])');
                expect(await labelledCells.count(), `${url} should label secondary card data`).toBeGreaterThan(0);
            } else {
                await expect(cells.first()).toHaveAttribute('data-mobile-empty', '');
            }
        }

        const hasPageOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
        expect(hasPageOverflow, `${url} should not require horizontal page scrolling`).toBe(false);
    }

    await page.setViewportSize({ width: 1440, height: 1000 });
    for (const url of ['/admin/transactions', '/admin/products', '/admin/reports/stock']) {
        await page.goto(url, { waitUntil: 'domcontentloaded' });
        const table = page.locator('table[data-mobile-cards]').first();
        await expect(table).toHaveCSS('display', 'table');
        await expect(table.locator('thead')).toHaveCSS('display', 'table-header-group');
        await expect(table.locator('tbody tr').first()).toHaveCSS('display', 'table-row');
    }

    expect(pageErrors).toEqual([]);
    expect(serverErrors).toEqual([]);
});
