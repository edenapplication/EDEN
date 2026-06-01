const CACHE_NAME = 'eden-admin-v1';

// Ressources à mettre en cache pour le démarrage hors-ligne
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/images/icon-192.png',
    '/images/icon-512.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
];

// ✅ Installation — mise en cache des ressources statiques
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS).catch(() => {
                // Ignorer les erreurs d'assets CDN
            });
        })
    );
    self.skipWaiting();
});

// ✅ Activation — nettoyer les anciens caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

// ✅ Fetch — stratégie Network First pour les pages, Cache First pour les assets
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Ne pas intercepter les requêtes non-GET
    if (event.request.method !== 'GET') return;

    // Assets statiques (CSS, JS, images) → Cache First
    if (
        url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|woff2?|ico)$/) ||
        url.hostname.includes('cdn.jsdelivr.net')
    ) {
        event.respondWith(
            caches.match(event.request).then(cached => {
                return cached || fetch(event.request).then(response => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Pages HTML → Network First (toujours à jour), fallback cache
    event.respondWith(
        fetch(event.request)
            .then(response => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});

// ✅ Message pour forcer la mise à jour
self.addEventListener('message', event => {
    if (event.data === 'skipWaiting') self.skipWaiting();
});