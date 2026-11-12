import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import { createMobileRouter } from './router';
import { installClientObservability } from './services/observability';
import { registerMobileServiceWorker } from './services/pwa';
import { configureTelemetry, trackMobileEvent } from './services/telemetry';
import { useSessionStore } from './stores/session';
import '../../css/mobile-app.css';

const root = document.querySelector('#mobile-app');

if (root) {
    // Membuka /app = memilih versi aplikasi; hapus preferensi "versi web" di perangkat ini.
    try { localStorage.removeItem('boq:prefer-web'); } catch { /* abaikan */ }

    const config = Object.freeze({
        appName: root.dataset.appName || 'Ecommerce Citra',
        logoUrl: root.dataset.logoUrl || '/logo/BOQ.CO.ID/BOQ.CO.ID-1.png',
        boqiMascotUrl: root.dataset.boqiMascotUrl || '/imgs/boqi/boqi-mascot.webp',
        supportWhatsapp: root.dataset.supportWhatsapp || '',
        appVersion: root.dataset.appVersion || 'rollout-6',
        apiBase: root.dataset.apiBase || '/api/mobile/v1',
        pilot: root.dataset.pilot === 'true',
        analyticsEnabled: root.dataset.analyticsEnabled === 'true',
    });

    const app = createApp(App, { config });
    const pinia = createPinia();
    const router = createMobileRouter();

    app.provide('mobileConfig', config);
    app.use(pinia);
    app.use(router);

    configureTelemetry({ apiBase: config.apiBase, version: config.appVersion, enabled: config.analyticsEnabled });
    installClientObservability({ router, version: config.appVersion });
    trackMobileEvent('app_opened', { online: navigator.onLine });
    app.mount(root);

    const session = useSessionStore(pinia);
    window.addEventListener('mobile-spa:unauthenticated', () => {
        const returnPath = router.currentRoute.value.fullPath;
        session.expire();
        trackMobileEvent('session_expired', {}, router.currentRoute.value.name);
        if (router.currentRoute.value.meta.requiresAuth) {
            router.replace({ name: 'login', query: { return: returnPath } });
        }
    });
    registerMobileServiceWorker();
}
