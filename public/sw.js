importScripts("https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.sw.js");

const CACHE_NAME = 'pak-malik-cache-v4';
const STATIC_ASSETS = [
    '/',
    '/offline'
];

self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            );
        })
    );
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    // Cache-First untuk aset statis (CSS, JS, Images, Fonts) yang dihasilkan Vite (build)
    if (url.pathname.match(/\.(css|js|png|jpg|jpeg|svg|woff2|woff)$/i) || url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(event.request).then(response => {
                return response || fetch(event.request).then(fetchRes => {
                    return caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, fetchRes.clone());
                        return fetchRes;
                    });
                });
            })
        );
        return;
    }

    // Network-First untuk permintaan navigasi/data (HTML, API, Livewire)
    event.respondWith(
        fetch(event.request)
            .then(response => {
                // Jangan simpan cache untuk request yang error
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                return response;
            })
            .catch(() => {
                // Jika jaringan mati dan meminta halaman HTML, kembalikan offline page
                if (event.request.mode === 'navigate' || (event.request.method === 'GET' && event.request.headers.get('accept').includes('text/html'))) {
                    return caches.match('/offline');
                }
                // Jika tidak, coba ambil dari cache
                return caches.match(event.request);
            })
    );
});
