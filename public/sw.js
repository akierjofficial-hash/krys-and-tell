/* Krys & Tell PWA Service Worker (safe defaults for Laravel) */

const CACHE_VERSION = 'kt-pwa-v1';
const CORE_ASSETS = [
  '/',
  '/offline.html',
  '/manifest.json',
  '/favicon.ico',
  '/images/pwa/icon-192.png',
  '/images/pwa/icon-512.png',
  '/images/pwa/maskable-192.png',
  '/images/pwa/maskable-512.png',
  '/images/pwa/apple-touch-icon.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches
      .open(CACHE_VERSION)
      .then((cache) => cache.addAll(CORE_ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) =>
        Promise.all(keys.map((key) => (key !== CACHE_VERSION ? caches.delete(key) : null)))
      )
      .then(() => self.clients.claim())
  );
});

function isStaticAsset(request) {
  const dest = request.destination;
  return dest === 'style' || dest === 'script' || dest === 'image' || dest === 'font';
}

self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Only handle same-origin GET requests
  if (req.method !== 'GET' || url.origin !== self.location.origin) return;

  // Navigations: network-first, fallback to offline page
  if (req.mode === 'navigate') {
    event.respondWith(fetch(req).catch(() => caches.match('/offline.html')));
    return;
  }

  // Static assets: stale-while-revalidate
  if (isStaticAsset(req) || CORE_ASSETS.includes(url.pathname)) {
    event.respondWith(
      caches.match(req).then((cached) => {
        const fetchPromise = fetch(req)
          .then((response) => {
            const copy = response.clone();
            caches.open(CACHE_VERSION).then((cache) => cache.put(req, copy));
            return response;
          })
          .catch(() => cached);

        return cached || fetchPromise;
      })
    );
  }
});
