let deferredInstallPrompt = null;

const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true;

const syncInstalledState = () => {
    document.documentElement.dataset.pwaInstalled = String(isStandalone());
};

syncInstalledState();

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
});

document.addEventListener('click', async (event) => {
    const trigger = event.target.closest('[data-pwa-install]');
    if (!trigger || !deferredInstallPrompt) return;

    event.preventDefault();
    deferredInstallPrompt.prompt();
    const choice = await deferredInstallPrompt.userChoice;
    deferredInstallPrompt = null;

    if (choice.outcome === 'accepted') {
        document.documentElement.dataset.pwaInstalled = 'true';
    }
});

window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    document.documentElement.dataset.pwaInstalled = 'true';
});

window.matchMedia('(display-mode: standalone)').addEventListener?.('change', syncInstalledState);

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js?v=2', {
            scope: '/',
            updateViaCache: 'none',
        })
            .then((registration) => registration.update())
            .catch((error) => {
                console.warn('PWA service worker registration failed.', error);
            });
    });
}
