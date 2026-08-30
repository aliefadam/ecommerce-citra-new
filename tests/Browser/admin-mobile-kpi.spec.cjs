const { test, expect } = require('@playwright/test');

const pages = [
    ['/admin', 4],
    ['/admin/transactions', 4],
    ['/admin/tax-invoices', 4],
    ['/admin/reports/owner', 4],
    ['/admin/reports/sales', 5],
    ['/admin/reports/stock', 4],
    ['/admin/reports/payments', 4],
    ['/admin/reports/customers', 6],
    ['/admin/reports/promos', 4],
    ['/admin/reports/returns', 4],
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

async function renderedColumnCount(grid) {
    return grid.evaluate((element) => getComputedStyle(element).gridTemplateColumns
        .split(/\s+/)
        .filter(Boolean)
        .length);
}

test('admin KPI cards use two mobile columns and retain responsive desktop columns', async ({ page }) => {
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

    for (const [url] of pages) {
        await page.goto(url, { waitUntil: 'domcontentloaded' });
        const grids = page.locator('[data-kpi-grid]');
        await expect(grids.first(), `${url} must expose a KPI grid`).toBeVisible();

        for (let index = 0; index < await grids.count(); index += 1) {
            const grid = grids.nth(index);
            await expect.poll(() => renderedColumnCount(grid), {
                message: `${url} KPI grid ${index + 1} should render as two columns on mobile`,
            }).toBe(2);
        }

        const hasPageOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
        expect(hasPageOverflow, `${url} should not create horizontal page overflow`).toBe(false);
    }

    await page.setViewportSize({ width: 1440, height: 1000 });
    for (const [url, desktopColumns] of pages) {
        await page.goto(url, { waitUntil: 'domcontentloaded' });
        const firstGrid = page.locator('[data-kpi-grid]').first();
        await expect.poll(() => renderedColumnCount(firstGrid), {
            message: `${url} should retain its desktop KPI column count`,
        }).toBe(desktopColumns);
    }

    expect(pageErrors).toEqual([]);
    expect(serverErrors).toEqual([]);
});
