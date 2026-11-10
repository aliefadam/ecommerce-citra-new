let telemetryConfig = null;

const allowedContext = new Set([
    'status', 'step', 'outcome', 'item_count', 'result_count', 'duration_ms', 'online',
    'install_state', 'error_type',
]);

const csrfToken = () => document.cookie
    .split('; ')
    .find((entry) => entry.startsWith('XSRF-TOKEN='))
    ?.slice('XSRF-TOKEN='.length);

export const configureTelemetry = (config) => {
    telemetryConfig = config;
};

export const trackMobileEvent = (event, context = {}, route = null) => {
    if (!telemetryConfig?.enabled || !navigator.onLine) return;
    const safeContext = Object.fromEntries(Object.entries(context).filter(([key]) => allowedContext.has(key)));
    const headers = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
    const token = csrfToken();
    if (token) headers['X-XSRF-TOKEN'] = decodeURIComponent(token);
    else headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content || '';

    fetch(`${telemetryConfig.apiBase}/telemetry`, {
        method: 'POST',
        credentials: 'same-origin',
        keepalive: true,
        headers,
        body: JSON.stringify({
            event,
            version: telemetryConfig.version,
            route,
            context: safeContext,
        }),
    }).catch(() => {});
};
