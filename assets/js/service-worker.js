const sw = '👷 Service Worker';

self.addEventListener('install', event => {
	console.log(sw, event);
	if (typeof cacheList != 'undefined') {
		event.waitUntil(
			caches.open(cacheVersion).then((cache) => {
				return cache.addAll(cacheList);
			})
		)
	} else {
		self.skipWaiting()
	}
});

self.addEventListener('activate', event => {
	console.log(sw, event);
	event.waitUntil(
		caches.keys().then((keys) => {
			keys.map((key) => {
				if (cacheVersion !== key) {
					caches.delete(key)
				}
			})
			return self.clients.claim()
		})
	)
});

self.addEventListener('fetch', event => {
	const requestFallback = event.request.clone();
	event.respondWith(
		caches.match(event.request).then((cachedResponse) => {
			return cachedResponse || fetch(event.request).then((networkResponse) => {
				/*
				let clonedResponse = networkResponse.clone();
				caches.open(cacheVersion).then((cache) => {
					cache.put(event.request, clonedResponse);
				});
				*/
				return networkResponse
			});
		}).catch(() => {
			console.log(sw, event);
		})
	)
});