// Service Worker para PWA - Offline Support
const CACHE_NAME = 'especialistas-v1.0.0';
const OFFLINE_URL = '/offline.html';

const CACHE_ASSETS = [
    '/',
    '/offline.html',
    '/css/skeleton.css',
    '/css/timeline.css',
    '/css/breadcrumbs.css',
    '/css/progress.css',
    '/css/fab.css',
    '/css/dark-mode.css',
    '/js/toast.js',
    '/js/validator.js',
    '/js/confirmation-modal.js',
    '/js/dark-mode.js',
    '/js/keyboard-shortcuts.js',
    'https://cdn.tailwindcss.com',
    'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js'
];

// Install event - cache assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Caching app shell');
                return cache.addAll(CACHE_ASSETS);
            })
            .then(() => {
                console.log('[SW] Installation complete');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[SW] Installation failed:', error);
            })
    );
});

// Activate event - cleanup old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('[SW] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('[SW] Activation complete');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip cross-origin requests
    if (url.origin !== location.origin) {
        // For CDN resources, try cache first
        if (url.hostname.includes('cdn.')) {
            event.respondWith(
                caches.match(request)
                    .then((response) => response || fetch(request))
            );
        }
        return;
    }

    // Skip API calls - always fetch fresh
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request)
                .catch(() => {
                    return new Response(
                        JSON.stringify({ error: 'Sin conexión', offline: true }),
                        { 
                            headers: { 'Content-Type': 'application/json' },
                            status: 503
                        }
                    );
                })
        );
        return;
    }

    // For HTML pages, use network first, fallback to cache
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Clone and cache the response
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseClone);
                    });
                    return response;
                })
                .catch(() => {
                    // Try cache
                    return caches.match(request)
                        .then((response) => {
                            if (response) return response;
                            
                            // Fallback to offline page
                            return caches.match(OFFLINE_URL);
                        });
                })
        );
        return;
    }

    // For other resources (CSS, JS, images), cache first
    event.respondWith(
        caches.match(request)
            .then((response) => {
                if (response) {
                    return response;
                }

                return fetch(request)
                    .then((response) => {
                        // Check if valid response
                        if (!response || response.status !== 200 || response.type === 'error') {
                            return response;
                        }

                        // Clone and cache
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });

                        return response;
                    });
            })
    );
});

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    console.log('[SW] Background sync:', event.tag);
    
    if (event.tag === 'sync-requests') {
        event.waitUntil(syncPendingRequests());
    }
});

async function syncPendingRequests() {
    try {
        // Get pending requests from IndexedDB or localStorage
        const pending = await getPendingRequests();
        
        for (const request of pending) {
            try {
                await fetch(request.url, {
                    method: request.method,
                    headers: request.headers,
                    body: request.body
                });
                
                // Remove from pending
                await removePendingRequest(request.id);
            } catch (error) {
                console.error('[SW] Failed to sync request:', error);
            }
        }
    } catch (error) {
        console.error('[SW] Sync failed:', error);
    }
}

function getPendingRequests() {
    // Placeholder - implement with IndexedDB
    return Promise.resolve([]);
}

function removePendingRequest(id) {
    // Placeholder - implement with IndexedDB
    return Promise.resolve();
}

// Push notifications
self.addEventListener('push', (event) => {
    console.log('[SW] Push received:', event);
    
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'Especialistas en Casa';
    const options = {
        body: data.body || 'Nueva notificación',
        icon: '/images/icon-192.png',
        badge: '/images/badge-72.png',
        tag: data.tag || 'notification',
        data: data.url || '/',
        vibrate: [200, 100, 200]
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification clicked:', event);
    
    event.notification.close();
    
    const url = event.notification.data || '/';
    
    event.waitUntil(
        clients.openWindow(url)
    );
});

console.log('[SW] Service Worker loaded');
