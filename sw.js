self.addEventListener('install', function(e) {
  self.skipWaiting();
});

self.addEventListener('activate', function(e) {
  self.registration.unregister()
    .then(function() {
      return self.clients.matchAll();
    })
    .then(function(clients) {
      clients.forEach(client => client.navigate(client.url));
    });
});

// Limpiar todas las cachés
caches.keys().then(function(names) {
    for (let name of names) caches.delete(name);
});
