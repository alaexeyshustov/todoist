const CACHE = 'todos-v1';
// Only cache static assets — not '/' (authenticated HTML that could be
// served stale after logout).
const SHELL = ['/manifest.json'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.addAll(SHELL))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle same-origin GET requests
    if (request.method !== 'GET' || url.origin !== location.origin) return;

    if (url.pathname.startsWith('/api/')) {
        // API reads: network first, cache only successful responses,
        // return a structured offline response when no cache is available.
        event.respondWith(
            fetch(request)
                .then((res) => {
                    if (res.ok) {
                        const clone = res.clone();
                        caches.open(CACHE).then((cache) => cache.put(request, clone));
                    }
                    return res;
                })
                .catch(async () => {
                    const cached = await caches.match(request);
                    return cached ?? new Response(
                        JSON.stringify({ message: 'Offline — cached data unavailable.' }),
                        { status: 503, headers: { 'Content-Type': 'application/json' } }
                    );
                })
        );
        return;
    }

    // Static assets: cache first, then network
    event.respondWith(
        caches.match(request).then((cached) => cached ?? fetch(request))
    );
});
