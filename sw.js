// Service Worker
self.addEventListener("install", event => {
    event.waitUntil(
        caches.open("v1").then(cache => {
            return cache.addAll([
                "/assets/img/apple-touch-icon.png",
                "/assets/img/favicon.ico",
                "/assets/img/icon.png",
                "/assets/img/icon-192x192.png",
                "/assets/img/icon-512x512.png",
                "/assets/js/student-setting.js",
                "/assets/js/student-login.js",
                "/assets/js/student-home.js",
                "/assets/css/style.css",
                "/assets/css/style-login.css"
            ]);
        })
    );
});

self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});