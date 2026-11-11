const { test, expect } = require('@playwright/test');

async function login(page, returnPath = '/cart') {
    await page.goto(`/app/auth/login?return=${encodeURIComponent(returnPath)}`, { waitUntil: 'domcontentloaded' });
    await page.getByLabel('Email').fill('aliefadam21@gmail.com');
    await page.getByLabel('Password').fill('123123');
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
}

test('member can add cart, checkout safely, and manage wishlist', async ({ page }) => {
    const pageErrors = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));
    await page.setViewportSize({ width: 390, height: 844 });

    await page.goto('/app/products/baut-mur-baja-109', { waitUntil: 'domcontentloaded' });
    await page.getByRole('button', { name: 'Tambah ke Keranjang' }).click();
    await expect(page).toHaveURL(/\/app\/auth\/login\?return=\/products\/baut-mur-baja-109$/);

    await page.getByLabel('Email').fill('aliefadam21@gmail.com');
    await page.getByLabel('Password').fill('123123');
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await expect(page).toHaveURL(/\/app\/products\/baut-mur-baja-109$/);

    await page.getByRole('button', { name: 'Tambah ke Keranjang' }).click();
    await expect(page.getByText('Produk ditambahkan ke keranjang.')).toBeVisible();
    await page.getByRole('button', { name: /Wishlist/ }).click();
    await expect(page.getByText('Produk disimpan ke wishlist.')).toBeVisible();

    await page.getByRole('link', { name: 'Keranjang' }).click();
    await expect(page.getByRole('heading', { name: 'Keranjang' })).toBeVisible();
    await expect(page.locator('.mobile-cart-item')).toContainText('Baut Mur Baja 10.9');
    await page.getByRole('button', { name: 'Tambah Baut Mur Baja 10.9' }).click();
    await expect(page.locator('.mobile-cart-item')).toContainText('2');
    await page.getByRole('link', { name: 'Lanjut Checkout' }).click();
    await expect(page.getByRole('heading', { name: 'Checkout' })).toBeVisible();
    await expect(page.getByText(/JNE.*REG/)).toBeVisible();
    await page.getByRole('button', { name: 'Buat Pesanan' }).click();
    await expect(page.getByRole('heading', { name: 'Pembayaran' })).toBeVisible();
    await expect(page.getByText('menunggu_verifikasi')).toBeVisible();

    await page.goto('/app/account', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Profil' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Biodata' })).toBeVisible();
    await page.getByRole('link', { name: /Pesanan/ }).click();
    await expect(page.getByRole('heading', { name: 'Transaksi' })).toBeVisible();
    await expect(page.locator('.mobile-order-card').first()).toBeVisible();
    await page.locator('.mobile-order-card').first().click();
    await expect(page.getByRole('heading', { name: 'Detail Pesanan' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Lihat Invoice' })).toBeVisible();

    await page.goto('/app/account', { waitUntil: 'domcontentloaded' });
    await page.getByRole('link', { name: /Notifikasi/ }).click();
    await expect(page.getByRole('heading', { name: 'Notifikasi' })).toBeVisible();

    await page.goto('/app/account', { waitUntil: 'domcontentloaded' });
    await page.getByRole('link', { name: /Wishlist/ }).click();
    await expect(page.getByRole('heading', { name: 'Wishlist' })).toBeVisible();
    await expect(page.locator('.mobile-product-card')).toContainText('Baut Mur Baja 10.9');
    await page.getByRole('button', { name: 'Hapus Baut Mur Baja 10.9 dari wishlist' }).click();
    await expect(page.getByRole('heading', { name: 'Wishlist masih kosong' })).toBeVisible();

    expect(pageErrors).toEqual([]);
});

test('registration creates a normalized session and session expiry returns to login', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/app/auth/register?return=/wishlist', { waitUntil: 'domcontentloaded' });
    await page.getByLabel('Nama').fill('Phase Three Customer');
    await page.getByLabel('Email').fill('PHASE3@example.test');
    await page.getByLabel('Password', { exact: true }).fill('secret123');
    await page.getByLabel('Ulangi password').fill('secret123');
    await page.getByRole('button', { name: 'Buat Akun' }).click();
    await expect(page).toHaveURL(/\/app\/wishlist$/);
    await expect(page.getByRole('heading', { name: 'Wishlist' })).toBeVisible();

    await page.route('**/api/mobile/v1/cart', async (route) => {
        await route.fulfill({
            status: 401,
            contentType: 'application/json',
            body: JSON.stringify({ code: 'unauthenticated', message: 'Sesi tidak tersedia atau sudah berakhir.' }),
        });
    });
    await page.goto('/app/cart', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/\/app\/auth\/login\?return=\/cart$/);
    await expect(page.getByRole('heading', { name: 'Masuk' })).toBeVisible();
});

test('checkout retry reuses idempotency key after a lost server response', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await login(page, '/products/baut-mur-baja-109');
    await expect(page).toHaveURL(/\/app\/products\/baut-mur-baja-109$/);
    await page.getByRole('button', { name: 'Tambah ke Keranjang' }).click();
    await page.getByRole('link', { name: 'Keranjang' }).click();
    await page.getByRole('link', { name: 'Lanjut Checkout' }).click();
    await expect(page.getByText(/JNE.*REG/)).toBeVisible();

    const idempotencyKeys = [];
    let orderAttempts = 0;
    await page.route('**/api/mobile/v1/checkout/orders', async (route) => {
        orderAttempts += 1;
        idempotencyKeys.push(route.request().headers()['idempotency-key']);
        if (orderAttempts === 1) {
            await route.fetch();
            await route.fulfill({
                status: 503,
                contentType: 'application/json',
                body: JSON.stringify({ message: 'Simulasi respons server hilang.' }),
            });
            return;
        }

        await route.continue();
    });

    await page.getByRole('button', { name: 'Buat Pesanan' }).click();
    await expect(page.getByText('Simulasi respons server hilang.')).toBeVisible();
    await page.getByRole('button', { name: 'Buat Pesanan' }).click();
    await expect(page.getByRole('heading', { name: 'Pembayaran' })).toBeVisible();
    expect(idempotencyKeys).toHaveLength(2);
    expect(idempotencyKeys[0]).toBe(idempotencyKeys[1]);
});
