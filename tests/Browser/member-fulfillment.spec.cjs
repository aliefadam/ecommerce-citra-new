const { test, expect } = require('@playwright/test');
const path = require('node:path');

test('member checkout manual sampai admin verifikasi, proses, dan kirim', async ({ page }) => {
    const pageErrors = [];
    const serverErrors = [];

    page.on('pageerror', (error) => pageErrors.push(error.message));
    page.on('response', (response) => {
        if (response.url().startsWith('http://127.0.0.1:8765') && response.status() >= 500) {
            serverErrors.push(`${response.status()} ${response.url()}`);
        }
    });
    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await page.locator('input[name="email"]').fill('aliefadam21@gmail.com');
    await page.locator('input[name="password"]').fill('123123');
    await Promise.all([
        page.waitForURL((url) => !url.pathname.endsWith('/login'), { waitUntil: 'domcontentloaded' }),
        page.getByRole('button', { name: 'Sign In' }).click({ noWaitAfter: true }),
    ]);

    await page.goto('/detail-produk/baut-hex-m8-x-25mm-galvanis', { waitUntil: 'domcontentloaded' });
    await Promise.all([
        page.waitForURL('**/checkout', { waitUntil: 'domcontentloaded' }),
        page.locator('#buyNowBtn').click({ noWaitAfter: true }),
    ]);

    await expect(page.getByText('Alamat Pengiriman', { exact: true })).toBeVisible();
    await expect(page.locator('input[name="address"]:checked')).toHaveAttribute('data-destination-id', '69217');
    await expect(page.locator('[id^="shippingOptions-"] .shipping-card')).toContainText('JNE REG');

    await page.locator('#tab-manual').click();
    await page.locator('#panel-manual label').click();
    await Promise.all([
        page.waitForURL('**/checkout/orders?ids=*', { waitUntil: 'domcontentloaded' }),
        page.locator('#payBtn').click({ noWaitAfter: true }),
    ]);

    const orderId = new URL(page.url()).searchParams.get('ids');
    expect(orderId).toMatch(/^MAN-\d{14}-[A-Z0-9]{10}$/);
    await Promise.all([
        page.waitForURL('**/checkout/waiting/**', { waitUntil: 'domcontentloaded' }),
        page.getByRole('link', { name: 'Lihat Pembayaran' }).click({ noWaitAfter: true }),
    ]);
    await expect(page.locator('#orderNum')).toHaveText(orderId);
    await page.locator('input[name="payment_proof"]').setInputFiles(
        path.resolve(__dirname, '..', '..', 'public', 'imgs', 'qris.png'),
    );
    await page.getByRole('button', { name: 'Kirim Bukti Pembayaran' }).click();
    await expect(page.getByText('Bukti transfer sudah diupload.')).toBeVisible();

    await page.context().clearCookies();
    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await page.locator('input[name="email"]').fill('admin@citra.com');
    await page.locator('input[name="password"]').fill('123123');
    await Promise.all([
        page.waitForURL((url) => !url.pathname.endsWith('/login'), { waitUntil: 'domcontentloaded' }),
        page.getByRole('button', { name: 'Sign In' }).click({ noWaitAfter: true }),
    ]);

    await page.goto('/admin/transactions', { waitUntil: 'domcontentloaded' });
    await page.locator('#txSearch').fill(orderId);
    await page.getByRole('button', { name: 'Verifikasi', exact: true }).click();
    await expect(page.locator('#verifyPaymentModal')).toHaveClass(/flex/);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.locator('#verifyPaymentModal').getByRole('button', { name: 'Setujui' }).click(),
    ]);

    await page.locator('#txSearch').fill(orderId);
    page.once('dialog', (dialog) => dialog.accept());
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.getByRole('button', { name: 'Proses', exact: true }).click(),
    ]);

    await page.locator('#txSearch').fill(orderId);
    await page.getByRole('button', { name: 'Kirim', exact: true }).click();
    await page.locator('#shipTrackingNumber').fill('E2E-RESI-001');
    await page.locator('#shipShippingLabel').fill('JNE REG');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.getByRole('button', { name: 'Submit Pengiriman' }).click(),
    ]);

    await page.locator('#txSearch').fill(orderId);
    await expect(page.getByRole('link', { name: 'Print Resi', exact: true })).toBeVisible();
    await expect(page.getByText('Resi: E2E-RESI-001')).toBeVisible();
    expect(pageErrors).toEqual([]);
    expect(serverErrors).toEqual([]);
});
