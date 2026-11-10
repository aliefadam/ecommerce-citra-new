import { trackMobileEvent } from './telemetry';

const emit = (name, detail = {}) => {
    window.dispatchEvent(new CustomEvent('mobile-spa:telemetry', {
        detail: {
            name,
            occurredAt: new Date().toISOString(),
            ...detail,
        },
    }));
};

export const installClientObservability = ({ router, version }) => {
    window.addEventListener('error', () => trackMobileEvent('route_error', { error_type: 'client_error' }, router.currentRoute.value.name));
    window.addEventListener('unhandledrejection', () => trackMobileEvent('route_error', { error_type: 'unhandled_rejection' }, router.currentRoute.value.name));

    router.beforeEach((to) => {
        performance.mark('mobile-route-start');
        window.dispatchEvent(new CustomEvent('mobile-spa:route-started'));
        emit('route_started', { route: to.name, version });
    });

    router.afterEach((to) => {
        performance.mark('mobile-route-end');
        performance.measure('mobile-route-transition', 'mobile-route-start', 'mobile-route-end');
        window.dispatchEvent(new CustomEvent('mobile-spa:route-finished'));
        emit('route_viewed', { route: to.name, version });
        const measure = performance.getEntriesByName('mobile-route-transition').at(-1);
        trackMobileEvent('route_viewed', { duration_ms: Math.round(measure?.duration || 0) }, to.name);
        const routeEvents = {
            product: 'product_viewed',
            cart: 'cart_viewed',
            checkout: 'checkout_started',
            payment: 'payment_initiated',
        };
        if (routeEvents[to.name]) trackMobileEvent(routeEvents[to.name], {}, to.name);
    });
};
