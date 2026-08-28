const path = require('node:path');

async function main() {
    const setup = require(path.resolve(__dirname, '..', 'tests', 'Browser', 'global-setup.cjs'));
    await setup();
    process.stdout.write('Database E2E untuk manual book sudah siap.\n');
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});
