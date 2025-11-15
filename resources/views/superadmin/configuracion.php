<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Especialistas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
<script>
window.configuracionApp = function() {
    return {
        loading: false,
        message: '',
        messageType: 'success',
        activeTab: 'general',
        config: {
            nombre_sitio: '',
            email_contacto: '',
            telefono: '',
            direccion: '',
            comision_plataforma: 10,
            moneda: 'COP',
            idioma: 'es',
            zona_horaria: 'America/Bogota',
            mantenimiento: false,
            registro_abierto: true,
            verificacion_email: true,
            moderacion_comentarios: true,
            smtp_host: '',
            smtp_port: 587,
            smtp_user: '',
            smtp_password: '',
            smtp_encryption: 'tls',
            // OneSignal
            onesignal_app_id: '',
            onesignal_api_key: '',
            onesignal_safari_web_id: '',
            onesignal_enabled: false,
            // Google Maps
            google_maps_api_key: '',
            google_maps_enabled: false,
            // Google Analytics
            google_analytics_id: '',
            google_analytics_enabled: false,
            // Facebook Pixel
            facebook_pixel_id: '',
            facebook_pixel_enabled: false,
            // Mercado Pago
            mercadopago_public_key: '',
            mercadopago_access_token: '',
            mercadopago_enabled: false,
            // PayPal
            paypal_client_id: '',
            paypal_secret: '',
            paypal_mode: 'sandbox',
            paypal_enabled: false,
            // Twilio (SMS)
            twilio_account_sid: '',
            twilio_auth_token: '',
            twilio_phone_number: '',
            twilio_enabled: false,
            // AWS S3
            aws_access_key: '',
            aws_secret_key: '',
            aws_bucket: '',
            aws_region: 'us-east-1',
            aws_enabled: false
        },

        async init() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '/login';
                return;
            }
            await this.cargarConfiguracion();
        },

        async cargarConfiguracion() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/configuracion', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.config = { ...this.config, ...data };
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        async guardarConfiguracion() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/configuracion', {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.config)
                });

                if (response.ok) {
                    this.showMessage('Configuración guardada correctamente', 'success');
                } else {
                    this.showMessage('Error al guardar configuración', 'error');
                }
            } catch (error) {
                this.showMessage('Error al guardar configuración', 'error');
            } finally {
                this.loading = false;
            }
        },

        showMessage(msg, type = 'success') {
            this.message = msg;
            this.messageType = type;
            setTimeout(() => { this.message = ''; }, 5000);
        },

        logout() {
            localStorage.removeItem('token');
            localStorage.removeItem('usuario');
            window.location.href = '/login';
        }
    }
}
</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50" x-data="configuracionApp()" x-init="init()">
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="/superadmin/dashboard" class="flex items-center space-x-2">
                        <svg class="h-8 w-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        <span class="text-xl font-bold text-gray-900">Configuración</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button @click="logout()" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                        </svg>
                        <span>Cerrar Sesión</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Navegación de pestañas -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="border-b border-gray-200 mb-8">
            <nav class="flex space-x-8">
                <button @click="activeTab = 'general'" 
                        :class="activeTab === 'general' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    General
                </button>
                <button @click="activeTab = 'pagos'" 
                        :class="activeTab === 'pagos' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Pagos
                </button>
                <button @click="activeTab = 'email'" 
                        :class="activeTab === 'email' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Email
                </button>
                <button @click="activeTab = 'seguridad'" 
                        :class="activeTab === 'seguridad' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Seguridad
                </button>
                <button @click="activeTab = 'notificaciones'" 
                        :class="activeTab === 'notificaciones' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Notificaciones
                </button>
                <button @click="activeTab = 'mapas'" 
                        :class="activeTab === 'mapas' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Mapas & Analytics
                </button>
                <button @click="activeTab = 'pasarelas'" 
                        :class="activeTab === 'pasarelas' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Pasarelas de Pago
                </button>
                <button @click="activeTab = 'almacenamiento'" 
                        :class="activeTab === 'almacenamiento' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Almacenamiento
                </button>
            </nav>
        </div>

        <!-- Mensajes -->
        <div x-show="message" x-transition class="mb-6 p-4 rounded-lg" :class="messageType === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
            <p x-text="message"></p>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <!-- Contenido de pestañas -->
        <div x-show="!loading">
            <!-- Tab: General -->
            <div x-show="activeTab === 'general'" class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-6">Configuración General</h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Sitio</label>
                        <input type="text" x-model="config.nombre_sitio" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email de Contacto</label>
                        <input type="email" x-model="config.email_contacto" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                        <input type="text" x-model="config.telefono" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                        <textarea x-model="config.direccion" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Idioma</label>
                            <select x-model="config.idioma" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="es">Español</option>
                                <option value="en">English</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Zona Horaria</label>
                            <select x-model="config.zona_horaria" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="America/Bogota">Bogotá (GMT-5)</option>
                                <option value="America/Mexico_City">Ciudad de México (GMT-6)</option>
                                <option value="America/Buenos_Aires">Buenos Aires (GMT-3)</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" x-model="config.mantenimiento" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Modo mantenimiento</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" x-model="config.registro_abierto" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Permitir registro de nuevos usuarios</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" x-model="config.verificacion_email" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Requerir verificación de email</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" x-model="config.moderacion_comentarios" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Moderar comentarios antes de publicar</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Tab: Pagos -->
            <div x-show="activeTab === 'pagos'" class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-6">Configuración de Pagos</h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Comisión de Plataforma (%)</label>
                        <input type="number" x-model="config.comision_plataforma" min="0" max="100" step="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">Porcentaje que la plataforma retiene de cada transacción</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Moneda</label>
                        <select x-model="config.moneda" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="COP">COP - Peso Colombiano</option>
                            <option value="USD">USD - Dólar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="MXN">MXN - Peso Mexicano</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tab: Email -->
            <div x-show="activeTab === 'email'" class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-6">Configuración de Email (SMTP)</h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Host SMTP</label>
                        <input type="text" x-model="config.smtp_host" placeholder="smtp.gmail.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Puerto</label>
                        <input type="number" x-model="config.smtp_port" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                        <input type="text" x-model="config.smtp_user" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña</label>
                        <input type="password" x-model="config.smtp_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Encriptación</label>
                        <select x-model="config.smtp_encryption" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="">Ninguna</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tab: Seguridad -->
            <div x-show="activeTab === 'seguridad'" class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-6">Configuración de Seguridad</h2>
                
                <div class="space-y-4">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-800">Las opciones de seguridad avanzadas estarán disponibles próximamente.</p>
                    </div>
                </div>
            </div>

            <!-- Tab: Notificaciones (OneSignal) -->
            <div x-show="activeTab === 'notificaciones'" class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold">OneSignal - Push Notifications</h2>
                        <p class="text-sm text-gray-500 mt-1">Configuración para notificaciones push en web y móvil</p>
                    </div>
                    <label class="flex items-center">
                        <input type="checkbox" x-model="config.onesignal_enabled" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Activar</span>
                    </label>
                </div>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">App ID</label>
                        <input type="text" x-model="config.onesignal_app_id" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                        <p class="mt-1 text-xs text-gray-500">Obtén tu App ID desde <a href="https://onesignal.com" target="_blank" class="text-indigo-600 hover:underline">OneSignal Dashboard</a></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">REST API Key</label>
                        <input type="password" x-model="config.onesignal_api_key" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Safari Web ID (Opcional)</label>
                        <input type="text" x-model="config.onesignal_safari_web_id" placeholder="web.onesignal.auto.xxxxxxxx" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                    </div>

                    <div class="p-4 bg-yellow-50 rounded-lg">
                        <h4 class="text-sm font-medium text-yellow-800 mb-2">📱 Configuración Adicional</h4>
                        <ul class="text-xs text-yellow-700 space-y-1">
                            <li>• Agrega el SDK de OneSignal a tu sitio web y aplicaciones móviles</li>
                            <li>• Configura los iconos de notificación en el dashboard de OneSignal</li>
                            <li>• Personaliza los mensajes de bienvenida y suscripción</li>
                        </ul>
                    </div>

                    <!-- Twilio SMS -->
                    <div class="border-t pt-6 mt-8">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold">Twilio - SMS Notifications</h3>
                                <p class="text-sm text-gray-500 mt-1">Envía notificaciones por SMS</p>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" x-model="config.twilio_enabled" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Activar</span>
                            </label>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Account SID</label>
                                <input type="text" x-model="config.twilio_account_sid" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Auth Token</label>
                                <input type="password" x-model="config.twilio_auth_token" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Número de Teléfono</label>
                                <input type="text" x-model="config.twilio_phone_number" placeholder="+57 3XX XXX XXXX" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Mapas & Analytics -->
            <div x-show="activeTab === 'mapas'" class="bg-white rounded-lg shadow-sm p-6">
                <div class="space-y-8">
                    <!-- Google Maps -->
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-xl font-semibold">Google Maps</h2>
                                <p class="text-sm text-gray-500 mt-1">API de mapas para localización de profesionales</p>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" x-model="config.google_maps_enabled" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Activar</span>
                            </label>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                            <input type="text" x-model="config.google_maps_api_key" placeholder="AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                            <p class="mt-1 text-xs text-gray-500">Obtén tu API key desde <a href="https://console.cloud.google.com/google/maps-apis" target="_blank" class="text-indigo-600 hover:underline">Google Cloud Console</a></p>
                        </div>

                        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-800 mb-2">🗺️ APIs Necesarias</h4>
                            <ul class="text-xs text-blue-700 space-y-1">
                                <li>• Maps JavaScript API</li>
                                <li>• Places API</li>
                                <li>• Geocoding API</li>
                                <li>• Distance Matrix API</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Google Analytics -->
                    <div class="border-t pt-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-xl font-semibold">Google Analytics</h2>
                                <p class="text-sm text-gray-500 mt-1">Seguimiento y análisis de tráfico web</p>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" x-model="config.google_analytics_enabled" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Activar</span>
                            </label>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Measurement ID</label>
                            <input type="text" x-model="config.google_analytics_id" placeholder="G-XXXXXXXXXX o UA-XXXXXXXXX-X" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                        </div>
                    </div>

                    <!-- Facebook Pixel -->
                    <div class="border-t pt-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-xl font-semibold">Facebook Pixel</h2>
                                <p class="text-sm text-gray-500 mt-1">Tracking de conversiones y retargeting</p>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" x-model="config.facebook_pixel_enabled" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Activar</span>
                            </label>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pixel ID</label>
                            <input type="text" x-model="config.facebook_pixel_id" placeholder="XXXXXXXXXXXXXXX" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Pasarelas de Pago -->
            <div x-show="activeTab === 'pasarelas'" class="bg-white rounded-lg shadow-sm p-6">
                <div class="space-y-8">
                    <!-- Mercado Pago -->
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-xl font-semibold">Mercado Pago</h2>
                                <p class="text-sm text-gray-500 mt-1">Procesamiento de pagos en Latinoamérica</p>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" x-model="config.mercadopago_enabled" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Activar</span>
                            </label>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Public Key</label>
                                <input type="text" x-model="config.mercadopago_public_key" placeholder="TEST-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Access Token</label>
                                <input type="password" x-model="config.mercadopago_access_token" placeholder="TEST-xxxxxxxxxxxx-xxxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-xxxxxxxx" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-green-50 rounded-lg">
                            <p class="text-xs text-green-700">💳 Usa las credenciales de TEST para desarrollo y las de PROD para producción</p>
                        </div>
                    </div>

                    <!-- PayPal -->
                    <div class="border-t pt-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-xl font-semibold">PayPal</h2>
                                <p class="text-sm text-gray-500 mt-1">Pagos internacionales con PayPal</p>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" x-model="config.paypal_enabled" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Activar</span>
                            </label>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Client ID</label>
                                <input type="text" x-model="config.paypal_client_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Secret</label>
                                <input type="password" x-model="config.paypal_secret" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Modo</label>
                                <select x-model="config.paypal_mode" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="sandbox">Sandbox (Pruebas)</option>
                                    <option value="live">Live (Producción)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Almacenamiento (AWS S3) -->
            <div x-show="activeTab === 'almacenamiento'" class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold">Amazon S3</h2>
                        <p class="text-sm text-gray-500 mt-1">Almacenamiento de archivos e imágenes en la nube</p>
                    </div>
                    <label class="flex items-center">
                        <input type="checkbox" x-model="config.aws_enabled" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Activar</span>
                    </label>
                </div>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Access Key ID</label>
                        <input type="text" x-model="config.aws_access_key" placeholder="AKIAIOSFODNN7EXAMPLE" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Secret Access Key</label>
                        <input type="password" x-model="config.aws_secret_key" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bucket Name</label>
                        <input type="text" x-model="config.aws_bucket" placeholder="mi-bucket-especialistas" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Región</label>
                        <select x-model="config.aws_region" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="us-east-1">US East (N. Virginia)</option>
                            <option value="us-west-2">US West (Oregon)</option>
                            <option value="sa-east-1">South America (São Paulo)</option>
                            <option value="eu-west-1">Europe (Ireland)</option>
                            <option value="ap-southeast-1">Asia Pacific (Singapore)</option>
                        </select>
                    </div>

                    <div class="p-4 bg-blue-50 rounded-lg">
                        <h4 class="text-sm font-medium text-blue-800 mb-2">☁️ Configuración IAM</h4>
                        <ul class="text-xs text-blue-700 space-y-1">
                            <li>• Crea un usuario IAM con permisos S3</li>
                            <li>• Configura políticas de acceso público si es necesario</li>
                            <li>• Habilita CORS en el bucket para uploads desde el navegador</li>
                        </ul>
                    </div>

                    <div class="p-4 bg-yellow-50 rounded-lg">
                        <p class="text-xs text-yellow-700"><strong>Alternativa local:</strong> Si no activas S3, los archivos se guardarán en el servidor local en <code class="bg-yellow-100 px-1 py-0.5 rounded">/storage/uploads</code></p>
                    </div>
                </div>
            </div>

            <!-- Botón guardar -->
            <div class="mt-8">
                <button @click="guardarConfiguracion()" :disabled="loading" class="w-full sm:w-auto px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    <span x-show="!loading">Guardar Configuración</span>
                    <span x-show="loading">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
</body>
</html>
