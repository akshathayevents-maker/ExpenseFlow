/**
 * ExpenseFlow Service Worker
 * Strategy:
 *   Static assets (CSS/JS/images/fonts) → stale-while-revalidate (fast loads)
 *   HTML navigation requests            → network-first + offline.html fallback
 *   POST / auth / CSRF routes           → network-only, never cached
 *
 * Cache invalidation: bump CACHE_VERSION on every deploy.
 */

const CACHE_VERSION = 'ef-v1';
const STATIC_CACHE  = `${CACHE_VERSION}-static`;
const OFFLINE_URL   = '/offline.html';

// Assets to precache immediately on install
const PRECACHE_ASSETS = [
  OFFLINE_URL,
];

// Never cache these — auth-sensitive, dynamic, or POST
const BYPASS_PATTERNS = [
  /\/logout/,
  /\/login/,
  /\/pay-login/,     // payment-page login-redirect helper
  /\/register/,
  /_token/,
  /sanctum/,
  /livewire/,
  /nova/,
  // Payment action endpoints (POST) — always network-only.
  // The show page (/pay/{id}) is fine as network-first HTML.
  /\/pay\/\d+\/mark-paid/,
  /\/pay\/\d+\/reject/,
  /\/pay\/\d+\/proof/,
];

// Static asset extensions — cache these aggressively
const STATIC_EXTENSIONS = /\.(css|js|woff2?|ttf|otf|eot|png|jpg|jpeg|gif|svg|ico|webp|avif)(\?.*)?$/i;

/* ─── Install ──────────────────────────────────────────────────── */
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => cache.addAll(PRECACHE_ASSETS))
      .then(() => self.skipWaiting())
  );
});

/* ─── Activate ─────────────────────────────────────────────────── */
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(
        keys
          .filter(key => key !== STATIC_CACHE)
          .map(key => caches.delete(key))
      ))
      .then(() => self.clients.claim())
  );
});

/* ─── Fetch ────────────────────────────────────────────────────── */
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Only handle same-origin requests
  if (url.origin !== self.location.origin) return;

  // Never cache non-GET
  if (request.method !== 'GET') return;

  // Never cache bypass patterns
  if (BYPASS_PATTERNS.some(p => p.test(url.pathname))) return;

  // Static assets → stale-while-revalidate
  if (STATIC_EXTENSIONS.test(url.pathname) || url.pathname.startsWith('/build/')) {
    event.respondWith(staleWhileRevalidate(request));
    return;
  }

  // HTML navigation → network-first with offline fallback
  if (request.mode === 'navigate' || request.headers.get('Accept')?.includes('text/html')) {
    event.respondWith(networkFirstWithOfflineFallback(request));
    return;
  }
});

/* ─── Strategy: stale-while-revalidate ────────────────────────── */
async function staleWhileRevalidate(request) {
  const cache = await caches.open(STATIC_CACHE);
  const cached = await cache.match(request);

  const networkFetch = fetch(request)
    .then(response => {
      if (response.ok) cache.put(request, response.clone());
      return response;
    })
    .catch(() => cached); // network failed, use cached

  // Return cached immediately; update cache in background
  return cached || networkFetch;
}

/* ─── Strategy: network-first + offline fallback ──────────────── */
async function networkFirstWithOfflineFallback(request) {
  try {
    const response = await fetch(request);
    // Don't cache HTML — it's auth-gated and dynamic
    return response;
  } catch {
    // Network unavailable — serve offline page
    const cache = await caches.open(STATIC_CACHE);
    const offline = await cache.match(OFFLINE_URL);
    return offline || new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } });
  }
}
