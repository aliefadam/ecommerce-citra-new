const { defineConfig } = require('@playwright/test');
const path = require('node:path');

const database = path.resolve(__dirname, '..', '..', 'storage', 'framework', 'testing', 'browser-e2e.sqlite');

module.exports = defineConfig({
    testDir: __dirname,
    testMatch: '**/*.spec.cjs',
    fullyParallel: false,
    workers: 1,
    timeout: 120_000,
    expect: { timeout: 10_000 },
    globalSetup: path.resolve(__dirname, 'global-setup.cjs'),
    reporter: [['list']],
    use: {
        baseURL: 'http://127.0.0.1:8765',
        channel: 'chrome',
        headless: true,
        viewport: { width: 1440, height: 1000 },
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=8765',
        cwd: path.resolve(__dirname, '..', '..'),
        url: 'http://127.0.0.1:8765',
        reuseExistingServer: false,
        timeout: 120_000,
        env: {
            APP_ENV: 'e2e',
            APP_URL: 'http://127.0.0.1:8765',
            DB_CONNECTION: 'sqlite',
            DB_DATABASE: database,
            SESSION_DRIVER: 'database',
            CACHE_STORE: 'array',
            MAIL_MAILER: 'array',
        },
    },
});
