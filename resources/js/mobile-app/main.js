import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import { createMobileRouter } from './router';
import { installClientObservability } from './services/observability';
import { registerMobileServiceWorker } from './services/pwa';
import { useSessionStore } from './stores/session';
import '../../css/mobile-app.css';

const root = document.querySelector('#mobile-app');

if (root) {
    const config = Object.freeze({
        appName: root.dataset.appName || 'Ecommerce Citra',
        appVersion: root.dataset.appVersion || 'commerce-3',
        apiBase: root.dataset.apiBase || '/api/mobile/v1',
    });

    const app = createApp(App, { config });
    const pinia = createPinia();
    const router = createMobileRouter();

    app.provide('mobileConfig', config);
    app.use(pinia);
    app.use(router);

    installClientObservability({ router, version: config.appVersion });
    app.mount(root);

    const session = useSessionStore(pinia);
    window.addEventListener('mobile-spa:unauthenticated', () => {
        const returnPath = router.currentRoute.value.fullPath;
        session.expire();
        if (router.currentRoute.value.meta.requiresAuth) {
            router.replace({ name: 'login', query: { return: returnPath } });
        }
    });
    registerMobileServiceWorker();
}
