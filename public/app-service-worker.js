// Naikkan versi ini setiap rilis agar cache aset lama di semua perangkat dibuang.
const CACHE_NAME = 'ecommerce-citra-mobile-rollout-v7';
const OFFLINE_URL = '/mobile-app-offline.html';

self.addEventListener('install', (event) => {
    // Langsung aktif: HTML selalu diambil dari jaringan dan aset Vite ber-hash,
    // jadi tidak perlu menunggu user menekan "Perbarui".
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.add(OFFLINE_URL))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('ecommerce-citra-mobile-') && key !== CACHE_NAME)
                    .map((key) => caches.delete(key)),
            ))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') self.skipWaiting();
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) return;
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/sanctum/')) return;

    if (request.mode === 'navigate' && url.pathname.startsWith('/app')) {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    if (url.pathname.startsWith('/build/assets/') && ['script', 'style', 'font'].includes(request.destination)) {
        event.respondWith(
            caches.open(CACHE_NAME).then(async (cache) => {
                const cached = await cache.match(request);
                if (cached) return cached;

                const response = await fetch(request);
                if (response.ok) cache.put(request, response.clone());

                return response;
            }),
        );
    }
});
