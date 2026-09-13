const { chromium } = require('@playwright/test');
const fs = require('node:fs');
const path = require('node:path');

const projectRoot = path.resolve(__dirname, '..');
const baseURL = process.env.FRONTEND_BASELINE_URL || 'http://127.0.0.1:8877';
const outputRoot = path.join(projectRoot, 'docs', 'frontend-baseline', 'screenshots');
const manifestPath = path.join(projectRoot, 'public', 'build', 'manifest.json');

const viewports = [
    ['360', 360, 800],
    ['390', 390, 844],
    ['768', 768, 1024],
    ['1024', 1024, 768],
    ['1440', 1440, 1000],
];

const publicPages = [
    ['home', '/'],
    ['category', '/kategori'],
    ['search', '/pencarian?q=baut'],
    ['flash-sale', '/flash-sale'],
    ['promo', '/promo'],
    ['blog', '/blog'],
    ['tracking', '/lacak-pesanan'],
    ['cart-guest', '/cart'],
    ['login', '/login'],
    ['register', '/register'],
    ['forgot-password', '/forgot-password'],
];

const memberPages = [
    ['profile', '/profil'],
    ['orders', '/profil?tab=pesanan'],
    ['wishlist', '/wishlist'],
    ['redeem-point', '/redeem-point'],
    ['notifications', '/notifications'],
    ['cart-member', '/cart'],
];

function resolveBuiltCss() {
    if (!fs.existsSync(manifestPath)) {
        throw new Error('Vite manifest tidak ditemukan. Jalankan npm run build terlebih dahulu.');
    }

    const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
    const entry = manifest['resources/css/app.css'];
    if (!entry?.file) {
        throw new Error('Entry resources/css/app.css tidak ditemukan pada Vite manifest.');
    }

    return path.join(projectRoot, 'public', 'build', entry.file);
}

async function login(page) {
    await page.goto(baseURL + '/login', { waitUntil: 'domcontentloaded', timeout: 45_000 });
    await page.locator('input[name="email"]').fill('aliefadam21@gmail.com');
    await page.locator('input[name="password"]').fill('123123');
    await Promise.all([
        page.waitForURL((url) => !url.pathname.endsWith('/login'), { waitUntil: 'domcontentloaded' }),
        page.locator('button[type="submit"]').click({ noWaitAfter: true }),
    ]);
}

async function capture(page, cssPath, viewportName, name, url, report) {
    const requestStart = report.requests.length;
    const startedAt = Date.now();
    const response = await page.goto(baseURL + url, { waitUntil: 'domcontentloaded', timeout: 45_000 });
    await page.addStyleTag({ path: cssPath });
    await page.addStyleTag({
        content: '*,*::before,*::after{animation:none!important;transition:none!important;caret-color:transparent!important}',
    });
    await page.waitForTimeout(250);

    const targetDirectory = path.join(outputRoot, viewportName);
    fs.mkdirSync(targetDirectory, { recursive: true });
    const target = path.join(targetDirectory, `${name}.png`);
    await page.screenshot({ path: target, fullPage: false });

    const navigation = await page.evaluate(() => {
        const entry = performance.getEntriesByType('navigation')[0];
        return entry ? {
            domContentLoadedMs: Math.round(entry.domContentLoadedEventEnd),
            loadMs: Math.round(entry.loadEventEnd),
            transferSize: entry.transferSize,
            decodedBodySize: entry.decodedBodySize,
        } : null;
    });

    report.pages.push({
        viewport: viewportName,
        name,
        requestedPath: url,
        finalUrl: page.url(),
        status: response?.status() || 0,
        captureDurationMs: Date.now() - startedAt,
        requestCount: report.requests.length - requestStart,
        navigation,
        screenshot: path.relative(projectRoot, target).replaceAll('\\', '/'),
    });
}

(async () => {
    const cssPath = resolveBuiltCss();
    fs.mkdirSync(outputRoot, { recursive: true });

    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const report = {
        generatedAt: new Date().toISOString(),
        baseURL,
        note: 'CSS hasil build Vite diinjeksi untuk audit karena layout customer baseline masih bergantung pada Tailwind Browser CDN.',
        cssPath: path.relative(projectRoot, cssPath).replaceAll('\\', '/'),
        pages: [],
        requests: [],
        failedRequests: [],
        pageErrors: [],
    };

    for (const [viewportName, width, height] of viewports) {
        const context = await browser.newContext({ viewport: { width, height }, colorScheme: 'light' });
        const page = await context.newPage();

        page.on('response', (response) => {
            report.requests.push({
                viewport: viewportName,
                status: response.status(),
                resourceType: response.request().resourceType(),
                url: response.url(),
            });
        });
        page.on('requestfailed', (request) => {
            report.failedRequests.push({
                viewport: viewportName,
                resourceType: request.resourceType(),
                url: request.url(),
                error: request.failure()?.errorText || 'unknown',
            });
        });
        page.on('pageerror', (error) => {
            report.pageErrors.push({ viewport: viewportName, message: error.message });
        });

        await page.route('**/rajaongkir/shipping-options**', async (route) => {
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({
                    data: [{ code: 'jne', name: 'JNE', service: 'REG', etd: '1-2 hari', cost: 12_000 }],
                }),
            });
        });

        for (const [name, url] of publicPages) {
            await capture(page, cssPath, viewportName, name, url, report);
        }

        await capture(page, cssPath, viewportName, 'product-detail', '/detail-produk/baut-hex-m8-x-25mm-galvanis', report);

        await login(page);
        for (const [name, url] of memberPages) {
            await capture(page, cssPath, viewportName, name, url, report);
        }

        await page.goto(baseURL + '/detail-produk/baut-hex-m8-x-25mm-galvanis', { waitUntil: 'domcontentloaded' });
        await Promise.all([
            page.waitForURL('**/checkout', { waitUntil: 'domcontentloaded' }),
            page.locator('#buyNowBtn').click({ noWaitAfter: true }),
        ]);
        await capture(page, cssPath, viewportName, 'checkout', '/checkout', report);

        await context.close();
    }

    await browser.close();

    const reportPath = path.join(projectRoot, 'docs', 'frontend-baseline', 'capture-report.json');
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
    process.stdout.write(`Baseline selesai: ${report.pages.length} screenshot.\n`);
    process.stdout.write(`Report: ${reportPath}\n`);
})().catch((error) => {
    console.error(error);
    process.exit(1);
});
