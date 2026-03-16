self.addEventListener('install', (e) => {
  e.waitUntil(caches.open('basco-v1').then((cache) => {
    return cache.addAll(['scanner.php', 'includes/header.php', 'js/app-offline.js']);
  }));
});
self.addEventListener('fetch', (e) => {
  e.respondWith(caches.match(e.request).then((res) => res || fetch(e.request)));
});