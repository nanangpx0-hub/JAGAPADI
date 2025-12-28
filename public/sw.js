/**
 * Service Worker for JAGAPADI
 * Provides offline functionality and caching
 */

const CACHE_NAME = 'jagapadi-v1.0.0';
const OFFLINE_URL = '/offline.html';

// Resources to cache immediately (using relative paths)
const STATIC_CACHE_URLS = [
    './',
    './dashboard',
    './laporan',
    'https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js'
];

// Install event - cache static resources
self.addEventListener('install', event => {
    console.log('Service Worker installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Caching static resources');
                // Cache resources one by one to avoid failures
                return Promise.allSettled(
                    STATIC_CACHE_URLS.map(url => 
                        cache.add(new Request(url, { credentials: 'same-origin' }))
                            .catch(error => console.log('Failed to cache:', url, error))
                    )
                );
            })
            .then(() => {
                console.log('Static resources cached successfully');
            })
            .catch(error => {
                console.error('Cache initialization failed:', error);
            })
    );
    
    // Skip waiting to activate immediately
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    console.log('Service Worker activating...');
    
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            // Take control of all clients immediately
            return self.clients.claim();
        })
    );
});

// Fetch event - serve from cache with network fallback
self.addEventListener('fetch', event => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }
    
    // Skip chrome-extension and other non-http requests
    if (!event.request.url.startsWith('http')) {
        return;
    }
    
    event.respondWith(
        caches.match(event.request)
            .then(cachedResponse => {
                // Return cached version if available
                if (cachedResponse) {
                    console.log('Serving from cache:', event.request.url);
                    return cachedResponse;
                }
                
                // Otherwise fetch from network
                return fetch(event.request)
                    .then(response => {
                        // Don't cache non-successful responses
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }
                        
                        // Cache successful responses
                        const responseToCache = response.clone();
                        caches.open(CACHE_NAME)
                            .then(cache => {
                                // Only cache GET requests for same origin
                                if (event.request.url.startsWith(self.location.origin)) {
                                    cache.put(event.request, responseToCache);
                                }
                            });
                        
                        return response;
                    })
                    .catch(error => {
                        console.log('Network request failed:', error);
                        
                        // Return offline page for navigation requests
                        if (event.request.mode === 'navigate') {
                            return caches.match(OFFLINE_URL) || 
                                   new Response('Offline - Please check your internet connection', {
                                       status: 503,
                                       statusText: 'Service Unavailable',
                                       headers: { 'Content-Type': 'text/plain' }
                                   });
                        }
                        
                        // Return a generic offline response for other requests
                        return new Response('Offline', {
                            status: 503,
                            statusText: 'Service Unavailable'
                        });
                    });
            })
    );
});

// Background sync for form submissions
self.addEventListener('sync', event => {
    console.log('Background sync triggered:', event.tag);
    
    if (event.tag === 'laporan-submit') {
        event.waitUntil(syncLaporanSubmissions());
    }
});

// Handle background sync for laporan submissions
async function syncLaporanSubmissions() {
    try {
        // Get pending submissions from IndexedDB
        const pendingSubmissions = await getPendingSubmissions();
        
        for (const submission of pendingSubmissions) {
            try {
                const response = await fetch('/laporan/create', {
                    method: 'POST',
                    body: submission.data,
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    // Remove from pending submissions
                    await removePendingSubmission(submission.id);
                    console.log('Synced submission:', submission.id);
                } else {
                    console.error('Failed to sync submission:', submission.id);
                }
            } catch (error) {
                console.error('Error syncing submission:', error);
            }
        }
    } catch (error) {
        console.error('Background sync failed:', error);
    }
}

// IndexedDB helpers for offline form submissions
async function getPendingSubmissions() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('jagapadi-offline', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['submissions'], 'readonly');
            const store = transaction.objectStore('submissions');
            const getAllRequest = store.getAll();
            
            getAllRequest.onsuccess = () => resolve(getAllRequest.result);
            getAllRequest.onerror = () => reject(getAllRequest.error);
        };
        
        request.onupgradeneeded = () => {
            const db = request.result;
            if (!db.objectStoreNames.contains('submissions')) {
                db.createObjectStore('submissions', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

async function removePendingSubmission(id) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('jagapadi-offline', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['submissions'], 'readwrite');
            const store = transaction.objectStore('submissions');
            const deleteRequest = store.delete(id);
            
            deleteRequest.onsuccess = () => resolve();
            deleteRequest.onerror = () => reject(deleteRequest.error);
        };
    });
}

// Push notification handling
self.addEventListener('push', event => {
    console.log('Push notification received');
    
    const options = {
        body: event.data ? event.data.text() : 'New notification from JAGAPADI',
        icon: '/public/manifest.json',
        badge: '/public/manifest.json',
        vibrate: [200, 100, 200],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Open App',
                icon: '/public/manifest.json'
            },
            {
                action: 'close',
                title: 'Close',
                icon: '/public/manifest.json'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification('JAGAPADI', options)
    );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
    console.log('Notification clicked:', event.action);
    
    event.notification.close();
    
    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});

// Message handling from main thread
self.addEventListener('message', event => {
    console.log('Service Worker received message:', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_NAME });
    }
});

// Periodic background sync (if supported)
self.addEventListener('periodicsync', event => {
    console.log('Periodic sync triggered:', event.tag);
    
    if (event.tag === 'content-sync') {
        event.waitUntil(syncContent());
    }
});

async function syncContent() {
    try {
        // Sync critical app data in background
        const response = await fetch('/api/sync', {
            credentials: 'same-origin'
        });
        
        if (response.ok) {
            console.log('Content synced successfully');
        }
    } catch (error) {
        console.error('Content sync failed:', error);
    }
}

console.log('Service Worker loaded successfully');