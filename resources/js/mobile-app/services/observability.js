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
    window.addEventListener('error', () => emit('client_error', { version }));
    window.addEventListener('unhandledrejection', () => emit('unhandled_rejection', { version }));

    router.beforeEach((to) => {
        performance.mark('mobile-route-start');
        emit('route_started', { route: to.name, version });
    });

    router.afterEach((to) => {
        performance.mark('mobile-route-end');
        performance.measure('mobile-route-transition', 'mobile-route-start', 'mobile-route-end');
        emit('route_viewed', { route: to.name, version });
    });
};
