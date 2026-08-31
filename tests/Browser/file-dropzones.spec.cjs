const { test, expect } = require('@playwright/test');

const adminPages = [
    ['/admin/settings', 1],
    ['/admin/banners/create', 1],
    ['/admin/main-categories/create', 1],
    ['/admin/products', 1],
    ['/admin/products/create', 1],
    ['/admin/companies/create', 1],
    ['/admin/content-pages/create', 1],
    ['/admin/promo-pages/create', 1],
    ['/admin/newsletter-subscribers', 1],
];

async function login(page, email) {
    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill('123123');
    await Promise.all([
        page.waitForURL((url) => !url.pathname.endsWith('/login'), { waitUntil: 'domcontentloaded' }),
        page.getByRole('button', { name: 'Sign In' }).click({ noWaitAfter: true }),
    ]);
}

async function expectEveryFileInputEnhanced(page, expectedCount) {
    const inputs = page.locator('input[type="file"]');
    await expect(inputs).toHaveCount(expectedCount);
    await expect.poll(async () => inputs.evaluateAll((elements) => elements.every((input) => (
        input.dataset.fileDropzoneReady === 'true'
        && Boolean(input.closest('[data-file-dropzone]'))
    )))).toBe(true);
}

test('all rendered admin and customer file inputs use the reusable dropzone', async ({ page }) => {
    const pageErrors = [];
    const serverErrors = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));
    page.on('response', (response) => {
        if (response.url().startsWith('http://127.0.0.1:8765') && response.status() >= 500) {
            serverErrors.push(`${response.status()} ${response.url()}`);
        }
    });

    await login(page, 'admin@citra.com');
    for (const [url, count] of adminPages) {
        await page.goto(url, { waitUntil: 'domcontentloaded' });
        await expectEveryFileInputEnhanced(page, count);

        for (let index = 0; index < count; index += 1) {
            const zone = page.locator('input[type="file"]').nth(index).locator('xpath=ancestor::*[@data-file-dropzone][1]');
            await expect(zone, `${url} upload ${index + 1} should expose a dropzone`).toBeAttached();
        }
    }

    await page.goto('/admin/settings', { waitUntil: 'domcontentloaded' });
    const logoInput = page.locator('input[name="store_logo"]');
    const logoZone = logoInput.locator('xpath=ancestor::*[@data-file-dropzone][1]');
    await logoInput.setInputFiles({
        name: 'logo-baru.png',
        mimeType: 'image/png',
        buffer: Buffer.from('iVBORw0KGgo=', 'base64'),
    });
    await expect(logoZone).toHaveAttribute('data-has-files', '');
    await expect(logoZone).toHaveAttribute('data-has-image', '');
    await expect(logoZone.locator('[data-file-dropzone-title]')).toHaveText('logo-baru.png');
    await expect(logoZone.locator('[data-file-dropzone-previews] img')).toHaveCount(1);

    await page.goto('/admin/companies/create', { waitUntil: 'domcontentloaded' });
    const companyInput = page.locator('input[name="logo"]');
    const companyZone = companyInput.locator('xpath=ancestor::*[@data-file-dropzone][1]');
    await companyZone.evaluate((zone) => {
        const transfer = new DataTransfer();
        transfer.items.add(new File(['company-logo'], 'company-logo.webp', { type: 'image/webp' }));
        zone.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer: transfer }));
    });
    await expect.poll(() => companyInput.evaluate((input) => input.files?.[0]?.name)).toBe('company-logo.webp');
    await expect(companyZone).toHaveAttribute('data-has-files', '');
    await companyZone.locator('[data-file-dropzone-clear]').click();
    await expect.poll(() => companyInput.evaluate((input) => input.files.length)).toBe(0);
    await expect(companyZone).not.toHaveAttribute('data-has-files', '');

    await page.context().clearCookies();
    await login(page, 'aliefadam21@gmail.com');
    await page.goto('/profil', { waitUntil: 'domcontentloaded' });
    await expectEveryFileInputEnhanced(page, 3);
    const avatarInput = page.locator('#profileAvatarFile');
    const avatarZone = avatarInput.locator('xpath=ancestor::*[@data-file-dropzone][1]');
    await expect(avatarZone).toBeVisible();
    await avatarInput.setInputFiles({
        name: 'avatar.png',
        mimeType: 'image/png',
        buffer: Buffer.from('iVBORw0KGgo=', 'base64'),
    });
    await expect(avatarZone.locator('[data-file-dropzone-title]')).toHaveText('avatar.png');

    expect(pageErrors).toEqual([]);
    expect(serverErrors).toEqual([]);
});
