const { test, expect } = require('@playwright/test');
const fs = require('node:fs');

for (const [name, url] of [['home', '/'], ['catalog', '/kategori'], ['detail', '/detail-produk/baut-mur-baja-109']]) {
    test(`quality audit 360px and reflow: ${name}`, async ({ page }, testInfo) => {
        await page.setViewportSize({ width: 360, height: 800 });
        await page.emulateMedia({ reducedMotion: 'reduce' });
        await page.addInitScript(() => {
            window.auditVitals = { lcp: null, cls: 0 };
            new PerformanceObserver(list => {
                for (const entry of list.getEntries()) window.auditVitals.lcp = entry.startTime;
            }).observe({ type: 'largest-contentful-paint', buffered: true });
            new PerformanceObserver(list => {
                for (const entry of list.getEntries()) if (!entry.hadRecentInput) window.auditVitals.cls += entry.value;
            }).observe({ type: 'layout-shift', buffered: true });
        });
        await page.goto(url, { waitUntil: 'load' });
        await page.screenshot({ path: testInfo.outputPath(`${name}-360.png`), fullPage: true });
        const audit = await page.evaluate(() => ({
            ...window.auditVitals,
            width: innerWidth,
            scrollWidth: document.documentElement.scrollWidth,
            reducedMotion: matchMedia('(prefers-reduced-motion: reduce)').matches,
            navigation: performance.getEntriesByType('navigation')[0]?.toJSON(),
            // Lab LCP/CLS only: INP and field p75 require real interactions and traffic.
        }));
        await testInfo.attach('lab-performance', { body: JSON.stringify(audit, null, 2), contentType: 'application/json' });
        const styles = await page.evaluate(() => {
            const luminance = rgb => rgb.slice(0, 3).map(v => v / 255).map(v => v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4).reduce((s, v, i) => s + v * [.2126, .7152, .0722][i], 0);
            const parse = color => (color.match(/[\d.]+/g) || []).map(Number);
            const samples = [...document.querySelectorAll('a,button,p,label')].filter(el => el.getClientRects().length && el.textContent.trim() && el.children.length === 0).slice(0, 100);
            return samples.flatMap(el => {
                const s = getComputedStyle(el);
                const fg = parse(s.color);
                let bg = [], parent = el;
                while (parent) {
                    const ps = getComputedStyle(parent);
                    if (ps.backgroundImage !== 'none' || Number(ps.opacity) !== 1) return [];
                    bg = parse(ps.backgroundColor);
                    if (bg.length === 3 || bg[3] === 1) break;
                    if (bg[3] > 0) return [];
                    parent = parent.parentElement;
                }
                if (!parent || fg.length < 3 || (fg.length === 4 && fg[3] !== 1)) return [];
                const a = luminance(fg), b = luminance(bg);
                const ratio = (Math.max(a, b) + .05) / (Math.min(a, b) + .05);
                const large = parseFloat(s.fontSize) >= 24 || (parseFloat(s.fontSize) >= 18.66 && Number(s.fontWeight) >= 700);
                return [{ text: el.textContent.trim().slice(0, 70), ratio: Number(ratio.toFixed(2)), threshold: large ? 3 : 4.5, transition: s.transitionDuration, animation: s.animationDuration }];
            });
        });
        fs.writeFileSync(testInfo.outputPath('audit.json'), JSON.stringify({ ...audit, contrastSamples: styles, contrastCandidates: styles.filter(s => s.ratio < s.threshold) }, null, 2));
        expect.soft(audit.scrollWidth).toBeLessThanOrEqual(361);
        expect(audit.reducedMotion).toBe(true);
        await page.keyboard.press('Tab');
        expect(await page.evaluate(() => document.activeElement !== document.body)).toBe(true);
        // 720px screen at 200% browser zoom has a 360px CSS layout viewport.
        // This is reflow coverage, not a physical browser zoom/keyboard certification.
        await page.setViewportSize({ width: 360, height: 400 });
        await page.screenshot({ path: testInfo.outputPath(`${name}-200-percent-reflow.png`), fullPage: false });
        expect.soft(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(361);
    });
}

test('category menu opens and restores focus using keyboard only', async ({ page }) => {
    await page.goto('/');
    const trigger = page.locator('#ecCategoryTrigger');
    for (let i = 0; i < 60 && !(await trigger.evaluate(el => el === document.activeElement)); i++) {
        await page.keyboard.press('Tab');
    }
    await expect(trigger).toBeFocused();
    await page.keyboard.press('Enter');
    await expect(page.locator('#ecCategoryDropdown')).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(page.locator('#ecCategoryDropdown')).toBeHidden();
    await expect(trigger).toBeFocused();
});
