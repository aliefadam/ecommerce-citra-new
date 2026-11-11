import { trackMobileEvent } from './telemetry';

const RELOAD_FLAG = 'mobile-spa:sw-reloaded';

// Service worker baru langsung aktif (skipWaiting di SW). Saat controller berganti,
// muat ulang sekali agar halaman memakai build terbaru — tanpa banner "Perbarui".
const reloadOnControllerChange = (hadController) => {
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (!hadController) return;
        try {
            if (sessionStorage.getItem(RELOAD_FLAG)) return;
            sessionStorage.setItem(RELOAD_FLAG, '1');
        } catch {
            // sessionStorage bisa tidak tersedia; tetap reload sekali.
        }
        trackMobileEvent('service_worker_updated', { install_state: 'auto_reloaded' });
        window.location.reload();
    });
};

export const registerMobileServiceWorker = () => {
    if (!('serviceWorker' in navigator)) return;

    try {
        sessionStorage.removeItem(RELOAD_FLAG);
    } catch {
        // abaikan
    }

    reloadOnControllerChange(Boolean(navigator.serviceWorker.controller));

    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/app-service-worker.js', {
                scope: '/app/',
                updateViaCache: 'none',
            });

            // Worker lama yang masih "waiting" dari versi sebelumnya: aktifkan sekarang.
            registration.waiting?.postMessage({ type: 'SKIP_WAITING' });
            registration.update().catch(() => {});
        } catch {
            window.dispatchEvent(new CustomEvent('mobile-spa:service-worker-error'));
        }
    }, { once: true });
};
