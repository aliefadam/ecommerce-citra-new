const { chromium } = require('playwright');
const fs = require('node:fs');
const path = require('node:path');

const baseURL = process.env.MANUAL_BASE_URL || 'http://127.0.0.1:8765';
const outputDir = path.resolve(__dirname, '..', 'docs', 'manual-book', 'screenshots');

const publicPages = [
    ['01-beranda', '/'],
    ['02-kategori-produk', '/kategori'],
    ['03-flash-sale', '/flash-sale'],
    ['04-keranjang', '/cart'],
    ['05-lacak-pesanan', '/lacak-pesanan'],
    ['06-blog', '/blog'],
    ['07-login', '/login'],
    ['08-registrasi', '/register'],
    ['09-lupa-password', '/forgot-password'],
    ['10-api-katalog-publik', '/docs/api-catalog'],
];

const customerPages = [
    ['12-profil-pelanggan', '/profil'],
    ['13-riwayat-pesanan', '/profil?tab=pesanan'],
    ['14-wishlist', '/wishlist'],
    ['15-redeem-poin', '/redeem-point'],
    ['16-checkout', '/checkout'],
    ['17-notifikasi', '/notifications'],
];

const adminPages = [
    ['20-dashboard-admin', '/admin'],
    ['21-penawaran-b2b', '/admin/quotations'],
    ['22-form-penawaran-b2b', '/admin/quotations/create'],
    ['23-sales-order', '/admin/sales-orders'],
    ['24-form-sales-order', '/admin/sales-orders/create'],
    ['25-proforma-invoice', '/admin/proforma-invoices'],
    ['26-surat-jalan', '/admin/delivery-notes'],
    ['27-packing-list', '/admin/packing-lists'],
    ['28-invoice-b2b', '/admin/b2b-invoices'],
    ['29-buat-transaksi-manual', '/admin/transactions/create-manual'],
    ['30-transaksi', '/admin/transactions'],
    ['31-retur', '/admin/return-requests'],
    ['32-faktur-pajak', '/admin/tax-invoices'],
    ['33-ulasan-produk', '/admin/product-reviews'],
    ['34-pusat-laporan', '/admin/reports'],
    ['35-laporan-penjualan', '/admin/reports/sales'],
    ['36-pelanggan', '/admin/users'],
    ['37-level-member', '/admin/member-tiers'],
    ['38-form-level-member', '/admin/member-tiers/create'],
    ['39-produk', '/admin/products'],
    ['40-form-produk', '/admin/products/create'],
    ['41-kategori-utama', '/admin/main-categories'],
    ['42-subkategori', '/admin/category-details'],
    ['43-varian', '/admin/variants'],
    ['44-stok', '/admin/stocks'],
    ['45-flash-sale-admin', '/admin/flash-sales'],
    ['46-kupon', '/admin/coupons'],
    ['47-pengaturan-toko', '/admin/settings'],
    ['48-banner', '/admin/banners'],
    ['49-newsletter', '/admin/newsletter-subscribers'],
    ['50-halaman-promo', '/admin/promo-pages'],
    ['51-konten-website', '/admin/content-pages'],
    ['52-perusahaan', '/admin/companies'],
    ['53-api-katalog-admin', '/admin/api-docs'],
    ['54-pengguna-admin', '/admin/admin-users'],
    ['55-role-izin', '/admin/admin-roles'],
    ['56-ganti-password', '/admin/change-password'],
];

async function capture(page, filename, url, fullPage = true) {
    const response = await page.goto(baseURL + url, { waitUntil: 'domcontentloaded', timeout: 45_000 });
    await page.waitForTimeout(600);
    await page.addStyleTag({ content: '*,*::before,*::after{animation:none!important;transition:none!important;caret-color:transparent!important}' }).catch(() => {});
    const finalUrl = page.url();
    const status = response ? response.status() : 0;
    const target = path.join(outputDir, `${filename}.png`);
    await page.screenshot({ path: target, fullPage });
    process.stdout.write(`${filename}\t${status}\t${finalUrl}\n`);
    return { filename, url, status, finalUrl, target };
}

async function login(page, email, password) {
    await page.goto(baseURL + '/login', { waitUntil: 'domcontentloaded' });
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await Promise.all([
        page.waitForLoadState('domcontentloaded'),
        page.locator('button[type="submit"]').click(),
    ]);
}

(async () => {
    fs.mkdirSync(outputDir, { recursive: true });
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 1000 }, colorScheme: 'light' });
    const page = await context.newPage();
    const report = [];

    for (const [name, url] of publicPages) report.push(await capture(page, name, url));

    await page.goto(baseURL + '/', { waitUntil: 'domcontentloaded' });
    const productHref = await page.locator('a[href*="/detail-produk/"]').first().getAttribute('href').catch(() => null);
    if (productHref) report.push(await capture(page, '11-detail-produk', new URL(productHref, baseURL).pathname));

    await login(page, 'aliefadam21@gmail.com', '123123');
    for (const [name, url] of customerPages) report.push(await capture(page, name, url));

    await context.clearCookies();
    await login(page, 'admin@citra.com', '123123');
    for (const [name, url] of adminPages) report.push(await capture(page, name, url));

    fs.writeFileSync(path.join(outputDir, 'capture-report.json'), JSON.stringify(report, null, 2));
    await browser.close();
})().catch((error) => {
    console.error(error);
    process.exit(1);
});
