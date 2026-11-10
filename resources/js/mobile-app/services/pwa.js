import { trackMobileEvent } from './telemetry';

const notifyUpdate = (registration) => {
    window.__mobilePwaUpdate = () => registration.waiting?.postMessage({ type: 'SKIP_WAITING' });
    window.dispatchEvent(new CustomEvent('mobile-spa:update-available'));
    trackMobileEvent('service_worker_updated', { install_state: 'update_available' });
};

export const registerMobileServiceWorker = () => {
    if (!('serviceWorker' in navigator)) return;

    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/app-service-worker.js', {
                scope: '/app/',
                updateViaCache: 'none',
            });

            if (registration.waiting) notifyUpdate(registration);

            registration.addEventListener('updatefound', () => {
                const worker = registration.installing;
                worker?.addEventListener('statechange', () => {
                    if (worker.state === 'installed' && navigator.serviceWorker.controller) {
                        notifyUpdate(registration);
                    }
                });
            });
        } catch {
            window.dispatchEvent(new CustomEvent('mobile-spa:service-worker-error'));
        }
    }, { once: true });
};
