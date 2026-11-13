/* Arandia College eLMS — service worker
 * Keeps the app installable and shows a friendly page when offline.
 * It deliberately does NOT cache PHP pages or API calls, so grades, quizzes,
 * messages and login sessions are always live from the server.
 * Bump CACHE_VERSION whenever you change offline.html or the icons.
 */
const CACHE_VERSION = 'elms-v1';
const OFFLINE_URL = 'offline.html';
const PRECACHE = [
  OFFLINE_URL,
  'picture/logo.png',
  'picture/pwa/icon-192.png',
  'picture/pwa/icon-512.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_VERSION)
      .then((cache) => cache.addAll(PRECACHE))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k !== CACHE_VERSION).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  // Page navigations: always network; if it fails (offline), show offline.html
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).catch(() => caches.match(OFFLINE_URL))
    );
    return;
  }

  // Only the precached static files are served from cache; everything else goes to the network
  const url = new URL(req.url);
  if (url.origin === self.location.origin && PRECACHE.some((p) => url.pathname.endsWith('/' + p))) {
    event.respondWith(caches.match(req).then((hit) => hit || fetch(req)));
  }
});
