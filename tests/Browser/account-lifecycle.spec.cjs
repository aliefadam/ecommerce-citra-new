const { test, expect } = require('@playwright/test');

const account = {
    name: 'Akun Lifecycle E2E',
    email: 'account.lifecycle.e2e@example.test',
    initialPassword: 'StartPass123!',
    updatedPassword: 'UpdatedPass456!',
};

async function submitLogin(page, password) {
    await page.locator('input[name=email]').fill(account.email);
    await page.locator('input[name=password]').fill(password);
    await page.getByRole('button', { name: 'Sign In', exact: true }).click();
}

test('customer can complete the account and profile lifecycle', async ({ page }) => {
    const pageErrors = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));

    await page.goto('/profil', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/\/login$/);

    await page.goto('/register', { waitUntil: 'domcontentloaded' });
    await page.locator('input[name=name]').fill(account.name);
    await page.locator('input[name=email]').fill(account.email);
    await page.locator('input[name=password]').fill(account.initialPassword);
    await page.locator('input[name=password_confirmation]').fill(account.initialPassword);
    await Promise.all([
        page.waitForURL((url) => url.pathname === '/profil', { waitUntil: 'domcontentloaded' }),
        page.getByRole('button', { name: 'Register', exact: true }).click(),
    ]);

    await expect(page.locator('#email')).toHaveValue(account.email);
    await expect(page.locator('#firstName')).toHaveValue('');
    await expect(page.locator('#lastName')).toHaveValue('');

    await page.locator('#firstName').fill('Akun');
    await page.locator('#lastName').fill('Lifecycle');
    await page.locator('#username').fill('account_lifecycle_e2e');
    await page.locator('input[name=gender][value=female]').check();
    await page.locator('#phoneNumber').fill('81200009999');
    await page.locator('#birthDate').fill('1997-08-17');
    await page.locator('#socialUrl').fill('https://example.test/account-lifecycle');
    await page.locator('#bio').fill('Profil customer untuk pengujian lifecycle akun.');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.getByRole('button', { name: 'Simpan Perubahan', exact: true }).click(),
    ]);
    await expect(page.getByText('Biodata berhasil diperbarui.', { exact: true })).toBeVisible();
    await expect(page.locator('#firstName')).toHaveValue('Akun');
    await expect(page.locator('#lastName')).toHaveValue('Lifecycle');
    await expect(page.locator('#username')).toHaveValue('account_lifecycle_e2e');
    await expect(page.locator('input[name=gender][value=female]')).toBeChecked();
    await expect(page.locator('#phoneNumber')).toHaveValue('81200009999');
    await expect(page.locator('#birthDate')).toHaveValue('1997-08-17');
    await expect(page.locator('#socialUrl')).toHaveValue('https://example.test/account-lifecycle');
    await expect(page.locator('#bio')).toHaveValue('Profil customer untuk pengujian lifecycle akun.');

    await page.locator('#nav-alamat').click();
    await expect(page.getByText('Belum ada alamat tersimpan', { exact: true })).toBeVisible();
    await page.locator('#nav-pesanan').click();
    await expect(page.getByText('Tidak ada pesanan', { exact: true })).toBeVisible();
    await page.locator('#nav-wishlist').click();
    await expect(page.getByText('Wishlist kamu masih kosong', { exact: true })).toBeVisible();
    await page.locator('#nav-notif').click();
    await expect(page.getByText('Belum ada notifikasi', { exact: true })).toBeVisible();

    await page.locator('#nav-keamanan').click();
    await page.locator('#currPwd').fill(account.initialPassword);
    await page.locator('#newPwd').fill(account.updatedPassword);
    await page.locator('#confPwd').fill(account.updatedPassword);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.getByRole('button', { name: 'Ubah Password', exact: true }).click(),
    ]);
    await expect(page.getByText('Password berhasil diperbarui.', { exact: true })).toBeVisible();

    await page.locator('button[onclick="confirmLogout()"]', { hasText: 'Keluar' }).click();
    await expect(page.locator('#logoutModal')).toBeVisible();
    await Promise.all([
        page.waitForURL(/\/login$/, { waitUntil: 'domcontentloaded' }),
        page.locator('#logoutModal').getByRole('button', { name: 'Keluar', exact: true }).click(),
    ]);

    await submitLogin(page, account.initialPassword);
    await expect(page).toHaveURL(/\/login$/);
    await expect(page.getByText('Email atau password tidak valid.', { exact: true })).toBeVisible();

    await submitLogin(page, account.updatedPassword);
    await expect(page).toHaveURL(/\/$/);
    await page.goto('/profil', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#firstName')).toHaveValue('Akun');
    await expect(page.locator('#username')).toHaveValue('account_lifecycle_e2e');

    await page.locator('button[onclick="confirmLogout()"]', { hasText: 'Keluar' }).click();
    await Promise.all([
        page.waitForURL(/\/login$/, { waitUntil: 'domcontentloaded' }),
        page.locator('#logoutModal').getByRole('button', { name: 'Keluar', exact: true }).click(),
    ]);

    await page.getByRole('link', { name: 'Lupa password?', exact: true }).click();
    await expect(page).toHaveURL(/\/forgot-password$/);
    await page.locator('input[name=email]').fill(account.email);
    await page.getByRole('button', { name: 'Kirim Link Reset', exact: true }).click();
    await expect(page.getByText('Link reset password berhasil dikirim ke email Anda.', { exact: true })).toBeVisible();

    expect(pageErrors).toEqual([]);
});
