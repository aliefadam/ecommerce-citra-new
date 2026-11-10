const cookieValue = (name) => document.cookie
    .split('; ')
    .find((entry) => entry.startsWith(`${name}=`))
    ?.slice(name.length + 1);

const csrfToken = () => {
    const cookie = cookieValue('XSRF-TOKEN');

    return cookie ? decodeURIComponent(cookie) : '';
};

export class ApiError extends Error {
    constructor(message, { status = 0, errors = {}, requestId = null } = {}) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.errors = errors;
        this.requestId = requestId;
    }
}

const parseResponse = async (response) => {
    const contentType = response.headers.get('content-type') || '';
    const body = response.status === 204
        ? null
        : (contentType.includes('application/json') ? await response.json() : null);

    if (!response.ok) {
        if (response.status === 401) {
            window.dispatchEvent(new CustomEvent('mobile-spa:unauthenticated'));
        }

        throw new ApiError(body?.message || 'Permintaan tidak dapat diproses.', {
            status: response.status,
            errors: body?.errors || {},
            requestId: response.headers.get('X-Request-ID'),
        });
    }

    return body;
};

export const request = async (url, options = {}) => {
    const method = (options.method || 'GET').toUpperCase();
    const headers = new Headers(options.headers || {});
    headers.set('Accept', 'application/json');
    headers.set('X-Requested-With', 'XMLHttpRequest');

    if (!['GET', 'HEAD', 'OPTIONS'].includes(method)) {
        const token = csrfToken();
        if (token) headers.set('X-XSRF-TOKEN', token);
    }

    if (options.body && !(options.body instanceof FormData)) {
        headers.set('Content-Type', 'application/json');
    }

    const response = await fetch(url, {
        ...options,
        method,
        headers,
        credentials: 'same-origin',
        body: options.body && !(options.body instanceof FormData)
            ? JSON.stringify(options.body)
            : options.body,
    });

    return parseResponse(response);
};

export const prepareCsrf = () => request('/sanctum/csrf-cookie');
