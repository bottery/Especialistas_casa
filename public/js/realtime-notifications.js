// Sistema de Notificaciones en Tiempo Real con Polling
class RealtimeNotifications {
    constructor(options = {}) {
        this.options = {
            pollInterval: options.pollInterval || 30000, // 30 segundos
            endpoint: options.endpoint || '/api/notifications',
            onNewNotification: options.onNewNotification || (() => {}),
            onError: options.onError || ((err) => console.error(err)),
            maxRetries: options.maxRetries || 3
        };

        this.isPolling = false;
        this.retryCount = 0;
        this.lastNotificationId = null;
        this.notifications = [];
        this.pollTimer = null;
    }

    start() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        console.log('[Realtime] Starting polling...');
        this.poll();
    }

    stop() {
        this.isPolling = false;
        if (this.pollTimer) {
            clearTimeout(this.pollTimer);
            this.pollTimer = null;
        }
        console.log('[Realtime] Stopped polling');
    }

    async poll() {
        if (!this.isPolling) return;

        try {
            const token = localStorage.getItem('token');
            if (!token) {
                console.warn('[Realtime] No auth token, stopping');
                this.stop();
                return;
            }

            const params = new URLSearchParams();
            if (this.lastNotificationId) {
                params.append('since', this.lastNotificationId);
            }

            const response = await fetch(`${this.options.endpoint}?${params}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            
            if (data.notifications && data.notifications.length > 0) {
                this.handleNewNotifications(data.notifications);
            }

            // Reset retry count on success
            this.retryCount = 0;

        } catch (error) {
            console.error('[Realtime] Poll error:', error);
            this.retryCount++;
            
            if (this.retryCount >= this.options.maxRetries) {
                this.options.onError(error);
                this.stop();
                return;
            }
        }

        // Schedule next poll
        if (this.isPolling) {
            this.pollTimer = setTimeout(() => this.poll(), this.options.pollInterval);
        }
    }

    handleNewNotifications(notifications) {
        notifications.forEach(notification => {
            // Update last ID
            if (!this.lastNotificationId || notification.id > this.lastNotificationId) {
                this.lastNotificationId = notification.id;
            }

            // Add to local storage
            this.notifications.unshift(notification);
            
            // Show notification
            this.showNotification(notification);
            
            // Callback
            this.options.onNewNotification(notification);
        });

        // Limit stored notifications
        if (this.notifications.length > 50) {
            this.notifications = this.notifications.slice(0, 50);
        }

        // Save to localStorage
        this.saveNotifications();
    }

    showNotification(notification) {
        // Browser notification
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(notification.title || 'Nueva notificación', {
                body: notification.message,
                icon: '/images/icon-192.png',
                badge: '/images/badge-72.png',
                tag: `notification-${notification.id}`,
                requireInteraction: notification.priority === 'high'
            });
        }

        // Toast notification
        if (window.ToastNotification) {
            const type = this.getNotificationType(notification.type);
            new ToastNotification(
                notification.message,
                type,
                {
                    duration: 5000,
                    action: notification.action_url ? {
                        text: 'Ver',
                        callback: () => window.location.href = notification.action_url
                    } : null
                }
            ).show();
        }

        // Update badge count
        this.updateBadgeCount();
    }

    getNotificationType(type) {
        const typeMap = {
            'nueva_solicitud': 'info',
            'asignacion': 'info',
            'completado': 'success',
            'cancelado': 'warning',
            'mensaje': 'info',
            'error': 'error'
        };
        return typeMap[type] || 'info';
    }

    updateBadgeCount() {
        const unreadCount = this.notifications.filter(n => !n.read).length;
        
        // Update DOM badges
        const badges = document.querySelectorAll('.notification-badge-count');
        badges.forEach(badge => {
            badge.textContent = unreadCount;
            badge.style.display = unreadCount > 0 ? 'flex' : 'none';
        });

        // Update page title
        if (unreadCount > 0) {
            document.title = `(${unreadCount}) ${document.title.replace(/^\(\d+\)\s*/, '')}`;
        } else {
            document.title = document.title.replace(/^\(\d+\)\s*/, '');
        }
    }

    async requestPermission() {
        if (!('Notification' in window)) {
            console.warn('[Realtime] Browser notifications not supported');
            return false;
        }

        if (Notification.permission === 'granted') {
            return true;
        }

        if (Notification.permission !== 'denied') {
            const permission = await Notification.requestPermission();
            return permission === 'granted';
        }

        return false;
    }

    markAsRead(notificationId) {
        const notification = this.notifications.find(n => n.id === notificationId);
        if (notification) {
            notification.read = true;
            this.saveNotifications();
            this.updateBadgeCount();
        }

        // Send to server
        const token = localStorage.getItem('token');
        fetch(`${this.options.endpoint}/${notificationId}/read`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` }
        }).catch(err => console.error('[Realtime] Mark read error:', err));
    }

    markAllAsRead() {
        this.notifications.forEach(n => n.read = true);
        this.saveNotifications();
        this.updateBadgeCount();

        // Send to server
        const token = localStorage.getItem('token');
        fetch(`${this.options.endpoint}/read-all`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` }
        }).catch(err => console.error('[Realtime] Mark all read error:', err));
    }

    getNotifications(limit = 20) {
        return this.notifications.slice(0, limit);
    }

    getUnreadCount() {
        return this.notifications.filter(n => !n.read).length;
    }

    clearNotifications() {
        this.notifications = [];
        this.saveNotifications();
        this.updateBadgeCount();
    }

    saveNotifications() {
        try {
            localStorage.setItem('notifications', JSON.stringify(this.notifications));
        } catch (e) {
            console.error('[Realtime] Save error:', e);
        }
    }

    loadNotifications() {
        try {
            const saved = localStorage.getItem('notifications');
            if (saved) {
                this.notifications = JSON.parse(saved);
                this.updateBadgeCount();
            }
        } catch (e) {
            console.error('[Realtime] Load error:', e);
        }
    }
}

// Auto-init para dashboards
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRealtimeNotifications);
} else {
    initRealtimeNotifications();
}

function initRealtimeNotifications() {
    // Solo iniciar si hay token (usuario logueado)
    if (!localStorage.getItem('token')) return;

    window.realtimeNotifications = new RealtimeNotifications({
        pollInterval: 30000, // 30 segundos
        onNewNotification: (notification) => {
            console.log('[Realtime] New notification:', notification);
            
            // Actualizar contador en header si existe
            const event = new CustomEvent('newNotification', { detail: notification });
            document.dispatchEvent(event);
        }
    });

    // Pedir permiso para notificaciones
    window.realtimeNotifications.requestPermission();
    
    // Cargar notificaciones guardadas
    window.realtimeNotifications.loadNotifications();
    
    // Iniciar polling
    window.realtimeNotifications.start();
}
