/**
 * Auth Interceptor - Auto-refresh de JWT tokens
 * Intercepta todas las peticiones fetch y renueva el token automáticamente si está próximo a expirar
 */

(function() {
    'use strict';

    const TOKEN_REFRESH_THRESHOLD = 5 * 60; // 5 minutos antes de expirar
    let isRefreshing = false;
    let failedQueue = [];

    /**
     * Decodificar JWT token sin verificar firma (solo para leer exp)
     */
    function parseJwt(token) {
        try {
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
            return JSON.parse(jsonPayload);
        } catch (e) {
            return null;
        }
    }

    /**
     * Verificar si el token está próximo a expirar
     */
    function isTokenExpiringSoon(token) {
        const decoded = parseJwt(token);
        if (!decoded || !decoded.exp) return true;
        
        const now = Math.floor(Date.now() / 1000);
        const timeUntilExpiry = decoded.exp - now;
        
        return timeUntilExpiry < TOKEN_REFRESH_THRESHOLD;
    }

    /**
     * Renovar token usando refresh token
     */
    async function refreshToken() {
        const refreshToken = localStorage.getItem('refresh_token') || localStorage.getItem('refreshToken');
        if (!refreshToken) {
            throw new Error('No refresh token available');
        }

        const response = await fetch('/api/auth/refresh', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ refresh_token: refreshToken })
        });

        if (!response.ok) {
            // Si falla el refresh, eliminar tokens y redirigir al login
            localStorage.removeItem('token');
            localStorage.removeItem('refresh_token');
            localStorage.removeItem('refreshToken');
            window.location.href = '/login';
            throw new Error('Failed to refresh token');
        }

        const data = await response.json();
        localStorage.setItem('token', data.token);
        if (data.refresh_token) {
            localStorage.setItem('refresh_token', data.refresh_token);
            localStorage.setItem('refreshToken', data.refresh_token);
        }
        
        return data.token;
    }

    /**
     * Procesar cola de peticiones fallidas
     */
    function processQueue(error, token = null) {
        failedQueue.forEach(prom => {
            if (error) {
                prom.reject(error);
            } else {
                prom.resolve(token);
            }
        });
        
        failedQueue = [];
    }

    /**
     * Interceptor de fetch
     */
    const originalFetch = window.fetch;
    window.fetch = async function(...args) {
        let [url, options = {}] = args;

        // Solo interceptar peticiones a nuestra API
        if (typeof url === 'string' && url.startsWith('/api/')) {
            const token = localStorage.getItem('token');
            
            if (token && isTokenExpiringSoon(token)) {
                // Si ya se está renovando, esperar
                if (isRefreshing) {
                    return new Promise((resolve, reject) => {
                        failedQueue.push({ resolve, reject });
                    }).then(newToken => {
                        // Actualizar header con nuevo token
                        options.headers = options.headers || {};
                        options.headers['Authorization'] = `Bearer ${newToken}`;
                        return originalFetch(url, options);
                    });
                }

                // Iniciar proceso de renovación
                isRefreshing = true;
                
                try {
                    const newToken = await refreshToken();
                    isRefreshing = false;
                    processQueue(null, newToken);
                    
                    // Actualizar header con nuevo token
                    options.headers = options.headers || {};
                    options.headers['Authorization'] = `Bearer ${newToken}`;
                } catch (error) {
                    isRefreshing = false;
                    processQueue(error, null);
                    throw error;
                }
            }
        }

        // Ejecutar fetch original
        const response = await originalFetch(url, options);

        // Si recibimos 401 Unauthorized, intentar renovar token
        if (response.status === 401 && typeof url === 'string' && url.startsWith('/api/')) {
            const token = localStorage.getItem('token');
            if (token && !isRefreshing) {
                isRefreshing = true;
                
                try {
                    const newToken = await refreshToken();
                    isRefreshing = false;
                    processQueue(null, newToken);
                    
                    // Reintentar petición con nuevo token
                    options.headers = options.headers || {};
                    options.headers['Authorization'] = `Bearer ${newToken}`;
                    return originalFetch(url, options);
                } catch (error) {
                    isRefreshing = false;
                    processQueue(error, null);
                    return response;
                }
            }
        }

        return response;
    };

    console.log('✅ Auth interceptor initialized - Auto-refresh enabled');
})();
