const { mkdirSync, writeFileSync } = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');

module.exports = async () => {
    if (process.env.E2E_DATABASE_PREPARED === '1') {
        return;
    }

    const root = path.resolve(__dirname, '..', '..');
    const testingDirectory = path.resolve(root, 'storage', 'framework', 'testing');
    const database = path.resolve(testingDirectory, 'browser-e2e.sqlite');
    const port = process.env.PLAYWRIGHT_PORT || '8765';

    if (!database.startsWith(testingDirectory + path.sep)) {
        throw new Error('Refusing to prepare an E2E database outside storage/framework/testing.');
    }

    mkdirSync(testingDirectory, { recursive: true });
    writeFileSync(database, '');

    const result = spawnSync('php', ['artisan', 'migrate:fresh', '--seed', '--force'], {
        cwd: root,
        env: {
            ...process.env,
            APP_ENV: 'e2e',
            APP_URL: `http://127.0.0.1:${port}`,
            DB_CONNECTION: 'sqlite',
            DB_DATABASE: database,
            SESSION_DRIVER: 'database',
            CACHE_STORE: 'array',
            MAIL_MAILER: 'array',
        },
        encoding: 'utf8',
    });

    if (result.status !== 0) {
        throw new Error(`Failed to prepare E2E database.\n${result.stdout}\n${result.stderr}`);
    }
};
