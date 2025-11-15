// PWA Installer - Register Service Worker and handle installation
class PWAInstaller {
    constructor() {
        this.deferredPrompt = null;
        this.isInstalled = false;
        this.init();
    }

    init() {
        this.checkInstallation();
        this.registerServiceWorker();
        this.setupInstallPrompt();
        this.addManifestLink();
    }

    checkInstallation() {
        // Check if already installed
        if (window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true) {
            this.isInstalled = true;
            console.log('[PWA] App is installed');
        }
    }

    async registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            console.warn('[PWA] Service Workers not supported');
            return;
        }

        try {
            const registration = await navigator.serviceWorker.register('/sw.js', {
                scope: '/'
            });

            console.log('[PWA] Service Worker registered:', registration.scope);

            // Check for updates
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                console.log('[PWA] New Service Worker found');

                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        // New version available
                        this.showUpdateNotification();
                    }
                });
            });

        } catch (error) {
            console.error('[PWA] Service Worker registration failed:', error);
        }
    }

    addManifestLink() {
        // Add manifest link if not present
        if (!document.querySelector('link[rel="manifest"]')) {
            const link = document.createElement('link');
            link.rel = 'manifest';
            link.href = '/manifest.json';
            document.head.appendChild(link);
        }

        // Add theme color
        if (!document.querySelector('meta[name="theme-color"]')) {
            const meta = document.createElement('meta');
            meta.name = 'theme-color';
            meta.content = '#6366f1';
            document.head.appendChild(meta);
        }
    }

    setupInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            console.log('[PWA] Install prompt ready');
            
            // Show custom install button if exists
            this.showInstallButton();
        });

        window.addEventListener('appinstalled', () => {
            console.log('[PWA] App installed');
            this.isInstalled = true;
            this.hideInstallButton();
            this.deferredPrompt = null;
        });
    }

    showInstallButton() {
        // Create install button if not in standalone and prompt available
        if (this.isInstalled || !this.deferredPrompt) return;

        let installBtn = document.getElementById('pwa-install-btn');
        
        if (!installBtn) {
            installBtn = document.createElement('button');
            installBtn.id = 'pwa-install-btn';
            installBtn.className = 'fixed bottom-4 right-4 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-lg hover:bg-indigo-700 transition z-50 flex items-center gap-2';
            installBtn.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Instalar App
            `;
            installBtn.onclick = () => this.promptInstall();
            document.body.appendChild(installBtn);
        }

        installBtn.style.display = 'flex';
    }

    hideInstallButton() {
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.style.display = 'none';
        }
    }

    async promptInstall() {
        if (!this.deferredPrompt) {
            console.warn('[PWA] Install prompt not available');
            return;
        }

        this.deferredPrompt.prompt();
        
        const { outcome } = await this.deferredPrompt.userChoice;
        console.log('[PWA] User choice:', outcome);

        if (outcome === 'accepted') {
            this.hideInstallButton();
        }

        this.deferredPrompt = null;
    }

    showUpdateNotification() {
        // Show notification about new version
        if (window.ToastNotification) {
            const toast = new ToastNotification('Nueva versión disponible', 'info', {
                duration: 0,
                action: {
                    text: 'Actualizar',
                    callback: () => {
                        window.location.reload();
                    }
                }
            });
            toast.show();
        } else {
            const update = confirm('Nueva versión disponible. ¿Actualizar ahora?');
            if (update) {
                window.location.reload();
            }
        }
    }

    // Check online/offline status
    setupOnlineStatus() {
        const updateOnlineStatus = () => {
            if (navigator.onLine) {
                console.log('[PWA] Online');
                document.body.classList.remove('offline');
                this.showOnlineNotification();
            } else {
                console.log('[PWA] Offline');
                document.body.classList.add('offline');
                this.showOfflineNotification();
            }
        };

        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        
        updateOnlineStatus();
    }

    showOfflineNotification() {
        if (window.ToastNotification) {
            new ToastNotification('Sin conexión - Algunas funciones no están disponibles', 'warning', {
                duration: 3000
            }).show();
        }
    }

    showOnlineNotification() {
        if (window.ToastNotification) {
            new ToastNotification('Conexión restablecida', 'success', {
                duration: 2000
            }).show();
        }
    }
}

// Auto-init
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.pwaInstaller = new PWAInstaller();
        window.pwaInstaller.setupOnlineStatus();
    });
} else {
    window.pwaInstaller = new PWAInstaller();
    window.pwaInstaller.setupOnlineStatus();
}
