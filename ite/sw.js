const CACHE_NAME = 'gara-pwa-cache-v8';
const OFFLINE_URL = './offline';


const PRECACHE_ASSETS = [
  OFFLINE_URL,
  './login',
  './favicon.ico',
  './favicon.png',
  './assets/login.css',
  './assets/student/css/root.css',
  './assets/student/css/style.css',
  './assets/student/js/htmx_init.js',
  './logo/FARA_BLACK.svg',
  './logo/GARA_WHITE.svg'
];


const CDN_MATCHERS = [
  'cdn.jsdelivr.net',
  'cdnjs.cloudflare.com',
  'fonts.googleapis.com',
  'fonts.gstatic.com',
  'unpkg.com'
];


self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[Service Worker] Pre-caching core assets...');
      return cache.addAll(PRECACHE_ASSETS);
    }).then(() => self.skipWaiting())
  );
});


self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});


self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);

  
  if (request.method !== 'GET' || 
      url.pathname.includes('/api/') || 
      url.pathname.includes('/livewire/') ||
      url.pathname.includes('/debug.php') ||
      url.pathname.includes('/exam/') ||
      url.pathname.includes('/asesmen/') ||
      url.pathname.includes('/ruang-ujian/')) {
    return;
  }

  
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((response) => {
          
          
          if (response.status === 200) {
            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(request, responseToCache);
            });
          }
          return response;
        })
        .catch(() => {
          
          return caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse;
            }
            
            return caches.match(OFFLINE_URL);
          });
        })
    );
    return;
  }

  
  const isCdn = CDN_MATCHERS.some((host) => url.hostname.includes(host));
  if (isCdn) {
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        if (cachedResponse) {
          return cachedResponse;
        }
        return fetch(request).then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseToCache = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(request, responseToCache);
            });
          }
          return networkResponse;
        });
      })
    );
    return;
  }

  
  const staticExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.eot', '.json'];
  const isStaticAsset = staticExtensions.some(ext => url.pathname.toLowerCase().endsWith(ext)) ||
                        url.pathname.includes('/assets/') || 
                        url.pathname.includes('/img/') || 
                        url.pathname.includes('/css/') || 
                        url.pathname.includes('/js/') ||
                        url.pathname.includes('/icons/') ||
                        url.pathname.includes('/helpers/') ||
                        url.pathname.includes('/splash/');
                        
  if (isStaticAsset) {
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        const fetchPromise = fetch(request).then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseToCache = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(request, responseToCache);
            });
          }
          return networkResponse;
        }).catch((error) => {
          // Tangkap error jika network gagal (misal server mati/offline)
          // Mencegah munculnya "Uncaught (in promise) TypeError: Failed to fetch"
          console.warn('[Service Worker] Asset fetch failed (Offline Mode):', request.url);
        });
        
        return cachedResponse || fetchPromise;
      })
    );
  }
});
