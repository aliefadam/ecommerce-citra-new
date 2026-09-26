const CACHE_NAME = 'ecommerce-citra-pwa-v3';
const PWA_ASSETS = [
    '/offline.html',
    '/pwa/boq-icon-192-v2.png',
    '/pwa/boq-icon-512-v2.png',
    '/pwa/boq-icon-maskable-512-v2.png',
    '/pwa/boq-apple-touch-icon-v2.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PWA_ASSETS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('ecommerce-citra-pwa-') && key !== CACHE_NAME)
                    .map((key) => caches.delete(key)),
            ))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match('/offline.html')));
        return;
    }

    if (url.origin === self.location.origin && PWA_ASSETS.includes(url.pathname)) {
        event.respondWith(caches.match(request).then((cached) => cached || fetch(request)));
    }
});
