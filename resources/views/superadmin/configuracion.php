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
            smtp_encryption: 'tls'
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
