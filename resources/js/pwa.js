let deferredInstallPrompt = null;

const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true;

const syncInstalledState = () => {
    document.documentElement.dataset.pwaInstalled = String(isStandalone());
};

syncInstalledState();

// PWA versi lama dipasang dari manifest root (start_url "/"). Saat dibuka dalam mode
// standalone di ponsel, arahkan ke Mobile SPA kecuali user memilih versi web (?web=1).
const PREFER_WEB_KEY = 'boq:prefer-web';
const redirectStandaloneMobileToSpa = () => {
    const params = new URLSearchParams(window.location.search);
    try {
        if (params.get('web') === '1') {
            localStorage.setItem(PREFER_WEB_KEY, '1');
            return;
        }
        if (localStorage.getItem(PREFER_WEB_KEY) === '1') return;
    } catch {
        if (params.get('web') === '1') return;
    }

    const isMobile = /Mobi|iPhone|iPod|Android.+Mobile/i.test(window.navigator.userAgent);
    const spaEnabled = document.querySelector('meta[name="mobile-spa"]')?.content === 'enabled';
    if (isStandalone() && isMobile && spaEnabled && window.location.pathname === '/') {
        window.location.replace('/app');
    }
};

redirectStandaloneMobileToSpa();

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
        navigator.serviceWorker.register('/service-worker.js?v=3', {
            scope: '/',
            updateViaCache: 'none',
        })
            .then((registration) => registration.update())
            .catch((error) => {
                console.warn('PWA service worker registration failed.', error);
            });
    });
}
