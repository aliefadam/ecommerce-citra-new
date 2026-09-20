const { mkdirSync, writeFileSync } = require('node:fs');
const path = require('node:path');
const { spawn, spawnSync } = require('node:child_process');

const root = path.resolve(__dirname, '..');
const testingDirectory = path.resolve(root, 'storage', 'framework', 'testing');
const database = path.resolve(testingDirectory, 'browser-e2e.sqlite');
const port = Number(process.env.PLAYWRIGHT_PORT || '8765');
const baseURL = `http://127.0.0.1:${port}`;
const e2eEnv = {
    ...process.env,
    APP_ENV: 'e2e',
    APP_URL: baseURL,
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: database,
    SESSION_DRIVER: 'database',
    CACHE_STORE: 'array',
    MAIL_MAILER: 'array',
    E2E_DATABASE_PREPARED: '1',
};

function stopServer(server) {
    if (!server?.pid) return;
    server.kill('SIGTERM');
}

async function waitForServer(server) {
    const deadline = Date.now() + 120_000;
    while (Date.now() < deadline) {
        if (server.exitCode !== null) {
            throw new Error(`E2E web server exited early with code ${server.exitCode}.`);
        }
        try {
            const response = await fetch(baseURL, { signal: AbortSignal.timeout(2_000) });
            if (response.status < 500) return;
        } catch (_) {}
        await new Promise((resolve) => setTimeout(resolve, 500));
    }
    throw new Error('E2E web server did not become ready within 120 seconds.');
}

async function main() {
    if (!Number.isInteger(port) || port < 1024 || port > 65535) {
        throw new Error('PLAYWRIGHT_PORT must be between 1024 and 65535.');
    }

    mkdirSync(testingDirectory, { recursive: true });
    writeFileSync(database, '');
    const migrate = spawnSync('php', ['artisan', 'migrate:fresh', '--seed', '--force'], {
        cwd: root, env: e2eEnv, encoding: 'utf8', windowsHide: true, timeout: 120_000,
    });
    if (migrate.status !== 0) {
        throw new Error(`Failed to prepare E2E database.\n${migrate.stdout}\n${migrate.stderr}`);
    }

    const serverRouter = path.resolve(root, 'vendor', 'laravel', 'framework', 'src', 'Illuminate', 'Foundation', 'resources', 'server.php');
    const server = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', '.', serverRouter], {
        cwd: path.resolve(root, 'public'), env: e2eEnv, stdio: 'ignore', windowsHide: true,
    });

    let result;
    try {
        await waitForServer(server);
        const playwrightCli = require.resolve('@playwright/test/cli');
        result = spawnSync(process.execPath, [playwrightCli, 'test', '--config=tests/Browser/playwright.config.cjs', ...process.argv.slice(2)], {
            cwd: root,
            env: e2eEnv,
            stdio: 'inherit',
            windowsHide: true,
            timeout: 660_000,
        });
    } finally {
        stopServer(server);
    }

    if (result?.error) {
        process.stderr.write(`E2E runner failed: ${result.error.message}\n`);
    }
    process.exit(result?.status ?? 1);
}

main().catch((error) => {
    process.stderr.write(`${error.stack || error.message}\n`);
    process.exit(1);
});
