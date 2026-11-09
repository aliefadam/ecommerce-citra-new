const { test, expect } = require('@playwright/test');

test('mobile SPA shell supports direct routes, guards, and desktop isolation', async ({ page }) => {
    const pageErrors = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));

    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/app', { waitUntil: 'domcontentloaded' });
    await expect(page.getByTestId('mobile-spa-shell')).toBeVisible();
    await expect(page.getByRole('navigation', { name: 'Navigasi aplikasi mobile' })).toBeVisible();
    await expect(page.getByRole('navigation', { name: 'Navigasi aplikasi mobile' }).getByRole('link')).toHaveCount(5);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Temukan material');

    await page.getByRole('link', { name: 'Katalog' }).click();
    await expect(page).toHaveURL(/\/app\/categories$/);
    await expect(page.getByRole('heading', { name: 'Katalog' })).toBeFocused();

    await page.reload({ waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Katalog' })).toBeVisible();
    await page.goBack({ waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/\/app\/?$/);

    await page.goto('/app/cart', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/\/app\/auth\/login\?return=\/cart$/);
    await expect(page.getByRole('heading', { name: 'Masuk' })).toBeVisible();

    await page.getByLabel('Email').fill('aliefadam21@gmail.com');
    await page.getByLabel('Password').fill('123123');
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await expect(page).toHaveURL(/\/app\/cart$/);
    await expect(page.getByRole('heading', { name: 'Keranjang' })).toBeVisible();

    await page.getByRole('link', { name: 'Profil' }).click();
    await expect(page.getByRole('heading', { name: 'Profil' })).toBeVisible();
    await page.getByRole('button', { name: 'Keluar' }).click();
    await expect(page).toHaveURL(/\/app\/?$/);

    await page.setViewportSize({ width: 1440, height: 1000 });
    await page.goto('/app', { waitUntil: 'domcontentloaded' });
    const shell = await page.getByTestId('mobile-spa-shell').boundingBox();
    expect(shell.width).toBeLessThanOrEqual(430);
    expect(Math.abs(shell.x - ((1440 - shell.width) / 2))).toBeLessThanOrEqual(1);

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-storefront-shell]')).toBeVisible();
    await expect(page.getByTestId('mobile-spa-shell')).toHaveCount(0);

    expect(pageErrors).toEqual([]);
});
