const CACHE_NAME = 'pv-v1';
const ASSETS = [
    'menu.html',
    'buscar_prod.html',
    'productos.php',
    'icon-192.png',
    'icon-512.png',
    'favicon.ico'
];

// Instalar el service worker y almacenar en caché los activos
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(ASSETS))
    );
});

// Activar el service worker y limpiar cachés antiguas
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(keys
                .filter(key => key !== CACHE_NAME)
                .map(key => caches.delete(key))
            );
        })
    );
});

// Estrategia de red con caída a caché para recursos dinámicos, 
// o caché con caída a red para estaticos.
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
    );
});
