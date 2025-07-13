const CACHE_NAME = 'kasirbraga-v1.0.0';
const STATIC_CACHE = 'kasirbraga-static-v1.0.0';

// Static assets to cache (only existing files)
const STATIC_ASSETS = [
  '/',
  '/manifest.json',  
  '/assets/icon-192x192.png',
  '/assets/icon-512x512.png',
  '/assets/logo-150x75.png',
  '/favicon.png',
];

// Routes that should never be cached (dynamic routes)
const DYNAMIC_ROUTES = [
  '/login',
  '/logout', 
  '/dashboard',
  '/admin/',
  '/staf/',
  '/api/',
  '/livewire/'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('Service Worker: Installing...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('Service Worker: Caching static assets');
        // Only cache assets that exist, fail silently for missing ones
        return Promise.allSettled(
          STATIC_ASSETS.map(url => 
            cache.add(url).catch(err => {
              console.warn(`Failed to cache ${url}:`, err);
              return null;
            })
          )
        );
      })
      .then(() => {
        console.log('Service Worker: Static assets cached');
        self.skipWaiting();
      })
      .catch((error) => {
        console.error('Service Worker: Failed to cache static assets:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('Service Worker: Activating...');
  
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== STATIC_CACHE && cacheName !== CACHE_NAME) {
              console.log('Service Worker: Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('Service Worker: Activated');
        return self.clients.claim();
      })
  );
});

// Fetch event - serve from cache or network
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip dynamic routes - always fetch from network
  const isDynamicRoute = DYNAMIC_ROUTES.some(route => 
    url.pathname.startsWith(route)
  );
  
  if (isDynamicRoute) {
    console.log('Service Worker: Skipping cache for dynamic route:', url.pathname);
    return; // Let browser handle normally
  }
  
  // Skip CSRF tokens and session-related requests
  if (url.pathname.includes('csrf') || 
      url.pathname.includes('session') ||
      url.searchParams.has('_token') ||
      request.headers.get('X-CSRF-TOKEN')) {
    return; // Let browser handle normally
  }
  
  // Cache strategy: Cache First for static assets, Network First for everything else
  if (isStaticAsset(request.url)) {
    event.respondWith(cacheFirst(request));
  } else {
    event.respondWith(networkFirst(request));
  }
});

// Check if URL is a static asset
function isStaticAsset(url) {
  const staticExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.woff', '.woff2'];
  const urlObj = new URL(url);
  
  return staticExtensions.some(ext => urlObj.pathname.endsWith(ext)) ||
         STATIC_ASSETS.includes(urlObj.pathname) ||
         urlObj.hostname.includes('cdn.jsdelivr.net');
}

// Cache First strategy for static assets
async function cacheFirst(request) {
  try {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      console.log('Service Worker: Serving from cache:', request.url);
      return cachedResponse;
    }
    
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(STATIC_CACHE);
      cache.put(request, networkResponse.clone());
      console.log('Service Worker: Cached new asset:', request.url);
    }
    
    return networkResponse;
  } catch (error) {
    console.error('Service Worker: Cache first failed:', error);
    
    // Try to return cached version as fallback
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline page or basic response
    return new Response('Asset not available offline', {
      status: 503,
      statusText: 'Service Unavailable'
    });
  }
}

// Network First strategy for dynamic content
async function networkFirst(request) {
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      // Don't cache dynamic responses, just return them
      return networkResponse;
    }
    
    throw new Error('Network response not ok');
  } catch (error) {
    console.log('Service Worker: Network failed, checking cache:', request.url);
    
    // Check cache as fallback (for static assets only)
    if (isStaticAsset(request.url)) {
      const cachedResponse = await caches.match(request);
      if (cachedResponse) {
        return cachedResponse;
      }
    }
    
    // For dynamic routes, don't serve stale content
    throw error;
  }
}

// Background sync for offline actions (future enhancement)
self.addEventListener('sync', (event) => {
  if (event.tag === 'background-sync') {
    console.log('Service Worker: Background sync triggered');
    // Future: Handle offline transaction queue
  }
});

// Push notifications (future enhancement)
self.addEventListener('push', (event) => {
  if (event.data) {
    const data = event.data.json();
    console.log('Service Worker: Push notification received:', data);
    
    event.waitUntil(
      self.registration.showNotification(data.title, {
        body: data.body,
        icon: '/assets/icon-192x192.png',
        badge: '/assets/icon-192x192.png',
        tag: 'kasirbraga-notification'
      })
    );
  }
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  
  event.waitUntil(
    clients.openWindow(event.notification.data?.url || '/')
  );
}); 