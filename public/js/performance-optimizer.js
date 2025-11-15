// Performance Optimization Utilities
class PerformanceOptimizer {
    constructor() {
        this.init();
    }

    init() {
        this.lazyLoadImages();
        this.deferNonCriticalCSS();
        this.prefetchLinks();
        this.monitorPerformance();
    }

    // Lazy load images
    lazyLoadImages() {
        if ('loading' in HTMLImageElement.prototype) {
            // Browser supports native lazy loading
            const images = document.querySelectorAll('img[data-src]');
            images.forEach(img => {
                img.src = img.dataset.src;
                img.loading = 'lazy';
                if (img.dataset.srcset) {
                    img.srcset = img.dataset.srcset;
                }
            });
        } else {
            // Fallback to Intersection Observer
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        if (img.dataset.srcset) {
                            img.srcset = img.dataset.srcset;
                        }
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });

            const lazyImages = document.querySelectorAll('img.lazy, img[data-src]');
            lazyImages.forEach(img => imageObserver.observe(img));
        }
    }

    // Defer non-critical CSS
    deferNonCriticalCSS() {
        const links = document.querySelectorAll('link[rel="stylesheet"][data-defer]');
        links.forEach(link => {
            link.media = 'print';
            link.onload = function() {
                this.media = 'all';
            };
        });
    }

    // Prefetch links on hover
    prefetchLinks() {
        const prefetchedLinks = new Set();

        document.addEventListener('mouseover', (e) => {
            const link = e.target.closest('a');
            if (!link || !link.href) return;

            const url = new URL(link.href, window.location.href);
            
            // Same origin only
            if (url.origin !== window.location.origin) return;
            
            // Skip if already prefetched
            if (prefetchedLinks.has(url.href)) return;

            // Create prefetch link
            const prefetchLink = document.createElement('link');
            prefetchLink.rel = 'prefetch';
            prefetchLink.href = url.href;
            document.head.appendChild(prefetchLink);

            prefetchedLinks.add(url.href);
        }, { passive: true });
    }

    // Monitor performance metrics
    monitorPerformance() {
        if (!window.performance || !window.performance.getEntriesByType) return;

        window.addEventListener('load', () => {
            setTimeout(() => {
                const perfData = window.performance.timing;
                const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
                const connectTime = perfData.responseEnd - perfData.requestStart;
                const renderTime = perfData.domComplete - perfData.domLoading;

                console.log('[Performance] Page load:', pageLoadTime + 'ms');
                console.log('[Performance] Connect:', connectTime + 'ms');
                console.log('[Performance] Render:', renderTime + 'ms');

                // Paint metrics
                const paintEntries = window.performance.getEntriesByType('paint');
                paintEntries.forEach(entry => {
                    console.log(`[Performance] ${entry.name}:`, Math.round(entry.startTime) + 'ms');
                });

                // Send to analytics if needed
                this.reportPerformance({
                    pageLoad: pageLoadTime,
                    connect: connectTime,
                    render: renderTime
                });
            }, 0);
        });
    }

    reportPerformance(metrics) {
        // Send to analytics service
        // Example: Google Analytics, custom endpoint, etc.
        console.log('[Performance] Metrics:', metrics);
    }

    // Debounce utility
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Throttle utility
    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    // Request Animation Frame wrapper
    rafThrottle(callback) {
        let requestId = null;
        
        return function(...args) {
            if (requestId === null) {
                requestId = requestAnimationFrame(() => {
                    callback.apply(this, args);
                    requestId = null;
                });
            }
        };
    }

    // Optimize scroll events
    optimizeScroll(callback) {
        return this.rafThrottle(callback);
    }

    // Reduce layout thrashing
    batchDOMReads(readCallbacks, writeCallbacks) {
        // Read phase
        const readResults = readCallbacks.map(cb => cb());
        
        // Write phase
        requestAnimationFrame(() => {
            writeCallbacks.forEach((cb, index) => {
                cb(readResults[index]);
            });
        });
    }

    // Preconnect to external domains
    preconnect(domains) {
        domains.forEach(domain => {
            const link = document.createElement('link');
            link.rel = 'preconnect';
            link.href = domain;
            link.crossOrigin = 'anonymous';
            document.head.appendChild(link);
        });
    }

    // Resource hints
    addResourceHints() {
        // Preconnect to CDNs
        this.preconnect([
            'https://cdn.tailwindcss.com',
            'https://cdn.jsdelivr.net'
        ]);
    }
}

// Auto-init
window.performanceOptimizer = new PerformanceOptimizer();
window.performanceOptimizer.addResourceHints();

// Export utilities
window.debounce = window.performanceOptimizer.debounce.bind(window.performanceOptimizer);
window.throttle = window.performanceOptimizer.throttle.bind(window.performanceOptimizer);
