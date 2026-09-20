const { defineConfig } = require('@playwright/test');
const path = require('node:path');

const database = path.resolve(__dirname, '..', '..', 'storage', 'framework', 'testing', 'browser-e2e.sqlite');
const port = process.env.PLAYWRIGHT_PORT || '8765';
const baseURL = `http://127.0.0.1:${port}`;

module.exports = defineConfig({
    testDir: __dirname,
    testMatch: '**/*.spec.cjs',
    fullyParallel: false,
    workers: 1,
    globalTimeout: 600_000,
    timeout: 120_000,
    expect: { timeout: 10_000 },
    globalSetup: path.resolve(__dirname, 'global-setup.cjs'),
    outputDir: path.resolve(__dirname, '..', '..', 'test-results'),
    reporter: [['list']],
    use: {
        baseURL,
        channel: 'chrome',
        headless: true,
        viewport: { width: 1440, height: 1000 },
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
});
