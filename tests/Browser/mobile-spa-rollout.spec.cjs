const { test, expect } = require('@playwright/test');

const login = async (page, returnPath = '/account') => {
    await page.goto(`/app/auth/login?return=${encodeURIComponent(returnPath)}`, { waitUntil: 'domcontentloaded' });
    await page.getByLabel('Email').fill('aliefadam21@gmail.com');
    await page.getByLabel('Password').fill('123123');
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
};

test('release candidate reflows across phone tablet and 200 percent text matrix', async ({ page }) => {
    const viewports = [
        { width: 320, height: 568 },
        { width: 360, height: 800 },
        { width: 390, height: 844 },
        { width: 430, height: 932 },
        { width: 768, height: 1024 },
    ];

    for (const viewport of viewports) {
        await page.setViewportSize(viewport);
        await page.goto('/app', { waitUntil: 'domcontentloaded' });
        await expect(page.getByTestId('mobile-spa-shell')).toBeVisible();
        const dimensions = await page.evaluate(() => ({
            documentWidth: document.documentElement.scrollWidth,
            viewportWidth: document.documentElement.clientWidth,
            shellWidth: document.querySelector('[data-testid="mobile-spa-shell"]')?.getBoundingClientRect().width,
        }));
        expect(dimensions.documentWidth).toBeLessThanOrEqual(dimensions.viewportWidth);
        expect(dimensions.shellWidth).toBeLessThanOrEqual(430);
    }

    await page.setViewportSize({ width: 360, height: 800 });
    await page.goto('/app/auth/login', { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => { document.documentElement.style.fontSize = '200%'; });
    await expect(page.getByRole('heading', { name: 'Masuk' })).toBeVisible();
    const zoomed = await page.evaluate(() => ({
        documentWidth: document.documentElement.scrollWidth,
        viewportWidth: document.documentElement.clientWidth,
    }));
    expect(zoomed.documentWidth).toBeLessThanOrEqual(zoomed.viewportWidth);
});

test('core controls meet touch input focus and reduced motion gates', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/app', { waitUntil: 'domcontentloaded' });
    const publicControls = await page.locator('.mobile-nav-item:visible, .mobile-astro-chat:visible, .mobile-astro-menu:visible').evaluateAll((elements) => elements.map((element) => ({
        label: element.getAttribute('aria-label') || element.textContent.trim(),
        width: element.getBoundingClientRect().width,
        height: element.getBoundingClientRect().height,
    })));
    for (const control of publicControls) {
        expect(control.width, control.label).toBeGreaterThanOrEqual(44);
        expect(control.height, control.label).toBeGreaterThanOrEqual(44);
    }

    await login(page);
    await expect(page.getByRole('heading', { name: 'Profil' })).toBeFocused();
    const formFontSizes = await page.locator('input:visible:not([type="radio"]):not([type="checkbox"]):not([type="file"]), select:visible, textarea:visible').evaluateAll((elements) => elements.map((element) => parseFloat(getComputedStyle(element).fontSize)));
    for (const fontSize of formFontSizes) expect(fontSize).toBeGreaterThanOrEqual(16);
    await expect(page.getByRole('link', { name: 'Kembali ke Versi Web' })).toHaveAttribute('href', '/?web=1');

    await page.emulateMedia({ reducedMotion: 'reduce' });
    const transitionDuration = await page.evaluate(() => {
        const main = document.querySelector('#mobile-main');
        main.classList.add('mobile-route-enter-active');
        return getComputedStyle(main).transitionDuration;
    });
    expect(parseFloat(transitionDuration)).toBeLessThanOrEqual(0.001);
});

test('analytics funnel sends only allowlisted non-personal context', async ({ page }) => {
    const telemetry = [];
    page.on('request', (request) => {
        if (request.url().includes('/api/mobile/v1/telemetry')) telemetry.push(request.postDataJSON());
    });
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/app', { waitUntil: 'domcontentloaded' });
    await page.getByRole('button', { name: 'Cari produk disini' }).click();
    await page.getByRole('combobox', { name: 'Cari produk' }).fill('Baut Mur Baja');
    await page.getByRole('combobox', { name: 'Cari produk' }).press('Enter');
    await expect(page).toHaveURL(/\/app\/search\?q=Baut/);
    await expect.poll(() => telemetry.some((event) => event.event === 'search_submitted')).toBe(true);
    await expect.poll(() => telemetry.some((event) => event.event === 'search_results_viewed')).toBe(true);

    const serialized = JSON.stringify(telemetry);
    expect(serialized).not.toContain('Baut Mur Baja');
    expect(serialized).not.toMatch(/email|address|token|password|npwp/i);
    expect(telemetry.every((event) => event.version && ['mobile_spa', undefined].includes(event.surface))).toBe(true);
});

test('public home stays within release candidate web vital and route feedback budgets', async ({ page }) => {
    await page.addInitScript(() => {
        window.__releaseVitals = { cls: 0, lcp: 0 };
        new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (!entry.hadRecentInput) window.__releaseVitals.cls += entry.value;
            }
        }).observe({ type: 'layout-shift', buffered: true });
        new PerformanceObserver((list) => {
            const entries = list.getEntries();
            window.__releaseVitals.lcp = entries.at(-1)?.startTime || 0;
        }).observe({ type: 'largest-contentful-paint', buffered: true });
    });
    await page.setViewportSize({ width: 390, height: 844 });
    const samples = [];
    for (let index = 0; index < 4; index += 1) {
        await page.goto('/app', { waitUntil: 'domcontentloaded' });
        await expect(page.locator('.mobile-product-card').first()).toBeVisible();
        await expect.poll(() => page.evaluate(() => window.__releaseVitals.lcp)).toBeGreaterThan(0);
        samples.push(await page.evaluate(() => window.__releaseVitals));
    }
    const percentile75 = (values) => [...values].sort((a, b) => a - b)[Math.ceil(values.length * 0.75) - 1];
    expect(percentile75(samples.map((sample) => sample.lcp))).toBeLessThanOrEqual(2500);
    expect(percentile75(samples.map((sample) => sample.cls))).toBeLessThanOrEqual(0.1);

    const feedbackDelay = page.evaluate(() => new Promise((resolve) => {
        const startedAt = performance.now();
        window.addEventListener('mobile-spa:route-feedback', () => resolve(performance.now() - startedAt), { once: true });
    }));
    await page.getByRole('link', { name: 'Katalog' }).click();
    expect(await feedbackDelay).toBeLessThanOrEqual(100);
    await expect(page.getByRole('heading', { name: 'Katalog' })).toBeFocused();
    await expect.poll(() => page.locator('img:visible').evaluateAll((images) => images.filter((image) => !image.complete || image.naturalWidth === 0 || image.getBoundingClientRect().width === 0 || image.getBoundingClientRect().height === 0).length)).toBe(0);
});
