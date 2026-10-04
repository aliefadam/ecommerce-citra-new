const { test, expect } = require('@playwright/test');

test.describe('help center redesign', () => {
    for (const viewport of [
        { name: 'desktop', width: 1440, height: 1000 },
        { name: 'tablet', width: 820, height: 1180 },
        { name: 'mobile', width: 390, height: 844 },
    ]) {
        test(`renders without horizontal overflow on ${viewport.name}`, async ({ page }) => {
            const pageErrors = [];
            page.on('pageerror', error => pageErrors.push(error.message));
            await page.setViewportSize({ width: viewport.width, height: viewport.height });
            await page.goto('/pages/pusat-bantuan', { waitUntil: 'networkidle' });

            await expect(page.getByRole('heading', { name: 'Pusat Bantuan', level: 1 })).toBeVisible();
            await expect(page.locator('.help-hero img')).toHaveAttribute('src', /hero-help-center\.webp/);
            const sizes = await page.evaluate(() => ({
                scrollWidth: document.documentElement.scrollWidth,
                clientWidth: document.documentElement.clientWidth,
            }));
            expect(sizes.scrollWidth).toBeLessThanOrEqual(sizes.clientWidth);
            expect(pageErrors).toEqual([]);
        });
    }

    test('search, category filters, and accessible accordion work', async ({ page }) => {
        await page.goto('/pages/pusat-bantuan');

        const installIcon = page.locator('button[data-category="aplikasi"] > i');
        await expect(installIcon).toHaveClass(/fi-rr-mobile-notch/);
        expect(await installIcon.evaluate((icon) => getComputedStyle(icon, '::before').content)).not.toBe('none');

        await page.getByRole('button', { name: /Pembayaran/ }).first().click();
        await expect(page.getByRole('button', { name: 'Metode pembayaran apa saja yang tersedia?' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Bagaimana cara mengecek status pesanan?' })).toBeHidden();

        const search = page.getByRole('searchbox', { name: 'Cari pertanyaan atau topik bantuan' });
        await search.fill('retur');
        await expect(page.getByRole('button', { name: 'Bagaimana proses retur atau komplain produk?' })).toBeVisible();
        const returnTrigger = page.getByRole('button', { name: 'Bagaimana proses retur atau komplain produk?' });
        await returnTrigger.click();
        await expect(returnTrigger).toHaveAttribute('aria-expanded', 'true');
        await expect(page.getByText(/Siapkan nomor pesanan, foto produk/)).toBeVisible();
    });

    test('shopping guide shows generated screenshots and switches fully to English', async ({ page }) => {
        await page.goto('/pages/cara-belanja', { waitUntil: 'domcontentloaded' });

        const guideImages = page.locator('.shopping-guide img');
        await expect(guideImages).toHaveCount(6);
        for (const image of await guideImages.all()) {
            await image.scrollIntoViewIfNeeded();
            await expect(image).toBeVisible();
            await expect.poll(() => image.evaluate((element) => element.complete && element.naturalWidth > 0)).toBe(true);
        }

        const sidebarIcons = page.locator('.info-sidebar nav a > i:first-child');
        for (const icon of await sidebarIcons.all()) {
            expect(await icon.evaluate((element) => getComputedStyle(element, '::before').content)).not.toBe('none');
        }

        await page.locator('.ec-header-actions a[lang="en"]').click();
        await expect(page.getByRole('heading', { name: 'How to Shop', level: 1 })).toBeVisible();
        await expect(page.getByText('Visual shopping guide')).toBeVisible();
        await expect(page.locator('.shopping-guide').getByText('6. Track your order', { exact: true })).toBeVisible();
        await expect(page.getByText('Informasi Pelanggan')).toHaveCount(0);
    });

    test('popular payment guide opens the payment answers', async ({ page }) => {
        await page.goto('/pages/pusat-bantuan');

        await page.locator('.help-topic-card', { hasText: 'Pembayaran' }).click();

        await expect(page).toHaveURL(/category=pembayaran#faqHeading$/);
        await expect(page.getByRole('button', { name: 'Metode pembayaran apa saja yang tersedia?' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Metode pembayaran apa saja yang tersedia?' })).toHaveAttribute('aria-expanded', 'true');
    });

    test('mobile install entry opens the platform-specific PWA guide', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        const installEntry = page.locator('[data-pwa-install]');
        await expect(installEntry).toBeVisible();
        await expect(page.locator('.ec-utility-copy')).toBeHidden();
        await expect(installEntry).toHaveAttribute('href', /category=aplikasi#install-aplikasi$/);
        await expect(installEntry.locator('.ec-mobile-install-icon')).toHaveCount(0);
        await expect(installEntry.locator('.ec-mobile-install-copy')).toContainText('Belanja lebih cepat dari HP');
        await expect(installEntry.locator('.ec-mobile-install-action')).toContainText('INSTALL');

        await page.goto('/pages/pusat-bantuan?category=aplikasi#install-aplikasi');
        const installFaq = page.locator('#install-aplikasi');
        await expect(installFaq).toBeVisible();
        await expect(installFaq.getByText('Android / tablet')).toBeVisible();
        await expect(installFaq.getByText('iPhone / iPad')).toBeVisible();

        await page.setViewportSize({ width: 1440, height: 1000 });
        await page.goto('/', { waitUntil: 'domcontentloaded' });
        await expect(page.locator('[data-pwa-install]')).toBeHidden();
        await expect(page.locator('.ec-utility-copy')).toBeVisible();
    });
});
