// Minimal no-op service worker — only exists so browsers consider the app
// installable ("Add to Home Screen"). No caching, no offline support by design.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));
