<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Notificaciones y Seguridad - Especialistas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50" x-data="seguridadApp()" x-init="init()">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <svg class="h-8 w-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-xl font-bold text-gray-900">Notificaciones y Seguridad</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" x-text="stats.notificaciones_sin_leer" x-show="stats.notificaciones_sin_leer > 0"></span>
                        <button class="p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                            </svg>
                        </button>
                    </div>
                    <a href="/superadmin/dashboard" class="text-gray-600 hover:text-gray-900">← Dashboard</a>
                    <button @click="logout()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Salir</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Logs 24h</p>
                        <p class="text-3xl font-bold mt-2" x-text="stats.logs_24h || 0">0</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Sesiones Activas</p>
                        <p class="text-3xl font-bold mt-2" x-text="stats.sesiones_activas || 0">0</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Sin Leer</p>
                        <p class="text-3xl font-bold mt-2" x-text="stats.notificaciones_sin_leer || 0">0</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">IPs Bloqueadas</p>
                        <p class="text-3xl font-bold mt-2" x-text="stats.ips_bloqueadas || 0">0</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button @click="tab = 'notificaciones'" :class="tab === 'notificaciones' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Notificaciones
                </button>
                <button @click="tab = 'logs'" :class="tab === 'logs' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Logs de Auditoría
                </button>
                <button @click="tab = 'sesiones'" :class="tab === 'sesiones' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Sesiones Activas
                </button>
                <button @click="tab = 'ips'" :class="tab === 'ips' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    IPs Bloqueadas
                </button>
            </nav>
        </div>

        <!-- Mensajes -->
        <div x-show="message" x-transition class="mb-6 p-4 rounded-lg" :class="messageType === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
            <p x-text="message"></p>
        </div>

        <!-- Tab: Notificaciones -->
        <div x-show="tab === 'notificaciones'" x-transition>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Centro de Notificaciones</h2>
                <button @click="nuevaNotificacion()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium">
                    + Enviar Notificación
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-lg divide-y">
                <template x-for="notif in notificaciones" :key="notif.id">
                    <div class="p-6 flex items-start justify-between" :class="!notif.leida ? 'bg-blue-50' : ''">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="{
                                    'bg-blue-100 text-blue-800': notif.tipo === 'sistema',
                                    'bg-green-100 text-green-800': notif.tipo === 'pago',
                                    'bg-yellow-100 text-yellow-800': notif.tipo === 'alerta',
                                    'bg-gray-100 text-gray-800': notif.tipo === 'general'
                                }" x-text="notif.tipo"></span>
                                <span class="text-xs text-gray-500" x-text="formatFecha(notif.created_at)"></span>
                            </div>
                            <h3 class="font-bold text-lg" x-text="notif.titulo"></h3>
                            <p class="text-gray-600 mt-1" x-text="notif.mensaje"></p>
                            <p class="text-xs text-gray-500 mt-2" x-show="notif.nombre">Para: <span x-text="notif.nombre + ' ' + (notif.apellido || '')"></span></p>
                        </div>
                        <button x-show="!notif.leida" @click="marcarLeida(notif.id)" class="ml-4 text-indigo-600 hover:text-indigo-900 text-sm whitespace-nowrap">
                            Marcar leída
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Tab: Logs -->
        <div x-show="tab === 'logs'" x-transition>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Logs de Auditoría</h2>
                <div class="flex space-x-2">
                    <select x-model="filtroTipo" @change="cargarLogs()" class="px-4 py-2 border rounded-lg text-sm">
                        <option value="">Todos los tipos</option>
                        <option value="auth">Auth</option>
                        <option value="usuario">Usuario</option>
                        <option value="pago">Pago</option>
                        <option value="seguridad">Seguridad</option>
                        <option value="sistema">Sistema</option>
                    </select>
                    <button @click="exportarLogs()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                        Exportar CSV
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="log in logs" :key="log.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="{
                                        'bg-blue-100 text-blue-800': log.tipo === 'auth',
                                        'bg-green-100 text-green-800': log.tipo === 'pago',
                                        'bg-red-100 text-red-800': log.tipo === 'seguridad',
                                        'bg-purple-100 text-purple-800': log.tipo === 'usuario',
                                        'bg-gray-100 text-gray-800': log.tipo === 'sistema'
                                    }" x-text="log.tipo"></span>
                                </td>
                                <td class="px-6 py-4 text-sm" x-text="log.descripcion"></td>
                                <td class="px-6 py-4 text-sm" x-text="log.nombre ? log.nombre + ' ' + (log.apellido || '') : '-'"></td>
                                <td class="px-6 py-4 text-sm text-gray-500" x-text="log.ip || '-'"></td>
                                <td class="px-6 py-4 text-sm text-gray-500" x-text="formatFecha(log.created_at)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Sesiones -->
        <div x-show="tab === 'sesiones'" x-transition>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Sesiones Activas</h2>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dispositivo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Último Acceso</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="sesion in sesiones" :key="sesion.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="font-medium text-sm" x-text="sesion.nombre + ' ' + (sesion.apellido || '')"></div>
                                        <div class="text-xs text-gray-500" x-text="sesion.email"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm" x-text="sesion.ip"></td>
                                <td class="px-6 py-4 text-sm text-gray-500" x-text="(sesion.user_agent || '').substring(0, 50)"></td>
                                <td class="px-6 py-4 text-sm text-gray-500" x-text="formatFecha(sesion.ultimo_acceso)"></td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="cerrarSesion(sesion.id)" class="text-red-600 hover:text-red-900 text-sm">
                                        Cerrar Sesión
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: IPs Bloqueadas -->
        <div x-show="tab === 'ips'" x-transition>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">IPs Bloqueadas</h2>
                <button @click="bloquearIP()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium">
                    + Bloquear IP
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Razón</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bloqueado Por</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="ip in ips" :key="ip.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono text-sm" x-text="ip.ip"></td>
                                <td class="px-6 py-4 text-sm" x-text="ip.razon"></td>
                                <td class="px-6 py-4 text-sm" x-text="ip.bloqueado_por"></td>
                                <td class="px-6 py-4 text-sm text-gray-500" x-text="formatFecha(ip.created_at)"></td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="desbloquearIP(ip.id)" class="text-green-600 hover:text-green-900 text-sm">
                                        Desbloquear
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Notificación -->
    <div x-show="modalNotificacion" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div @click="modalNotificacion = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            
            <div class="relative bg-white rounded-lg max-w-2xl w-full p-6">
                <h3 class="text-2xl font-bold mb-4">Enviar Notificación</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipo</label>
                        <select x-model="notifForm.tipo" class="w-full px-3 py-2 border rounded-lg">
                            <option value="general">General</option>
                            <option value="sistema">Sistema</option>
                            <option value="pago">Pago</option>
                            <option value="alerta">Alerta</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Título</label>
                        <input type="text" x-model="notifForm.titulo" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Mensaje</label>
                        <textarea x-model="notifForm.mensaje" rows="4" class="w-full px-3 py-2 border rounded-lg"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Destinatario</label>
                        <select x-model="notifForm.modo" @change="notifForm.usuario_id = null" class="w-full px-3 py-2 border rounded-lg mb-2">
                            <option value="individual">Usuario Específico</option>
                            <option value="masivo">Envío Masivo</option>
                        </select>
                        
                        <input x-show="notifForm.modo === 'individual'" type="number" x-model="notifForm.usuario_id" placeholder="ID del usuario" class="w-full px-3 py-2 border rounded-lg">
                        
                        <select x-show="notifForm.modo === 'masivo'" x-model="notifForm.rol" class="w-full px-3 py-2 border rounded-lg">
                            <option value="">Todos los usuarios</option>
                            <option value="paciente">Solo Pacientes</option>
                            <option value="profesional">Solo Profesionales</option>
                            <option value="admin">Solo Admins</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="modalNotificacion = false" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button @click="enviarNotificacion()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Enviar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bloquear IP -->
    <div x-show="modalBloquearIP" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div @click="modalBloquearIP = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            
            <div class="relative bg-white rounded-lg max-w-lg w-full p-6">
                <h3 class="text-2xl font-bold mb-4">Bloquear IP</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Dirección IP</label>
                        <input type="text" x-model="ipForm.ip" placeholder="192.168.1.1" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Razón</label>
                        <textarea x-model="ipForm.razon" rows="3" class="w-full px-3 py-2 border rounded-lg"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="modalBloquearIP = false" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button @click="guardarBloqueoIP()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Bloquear</button>
                </div>
            </div>
        </div>
    </div>

<script>
function seguridadApp() {
    return {
        tab: 'notificaciones',
        message: '',
        messageType: 'success',
        stats: {},
        notificaciones: [],
        logs: [],
        sesiones: [],
        ips: [],
        filtroTipo: '',
        
        modalNotificacion: false,
        modalBloquearIP: false,
        notifForm: { modo: 'individual', tipo: 'general' },
        ipForm: {},

        async init() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '/login';
                return;
            }
            await this.cargarDashboard();
            await this.cargarNotificaciones();
            await this.cargarLogs();
            await this.cargarSesiones();
            await this.cargarIPs();
        },

        async cargarDashboard() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/seguridad/dashboard', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.stats = data.estadisticas || {};
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async cargarNotificaciones() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/seguridad/notificaciones', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.notificaciones = data.notificaciones || [];
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async cargarLogs() {
            try {
                const token = localStorage.getItem('token');
                const params = new URLSearchParams({ limit: 50 });
                if (this.filtroTipo) params.append('tipo', this.filtroTipo);
                
                const response = await fetch(`/api/seguridad/logs?${params}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.logs = data.logs || [];
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async cargarSesiones() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/seguridad/sesiones', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.sesiones = data.sesiones || [];
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async cargarIPs() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/seguridad/ips', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.ips = data.ips || [];
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        nuevaNotificacion() {
            this.notifForm = { modo: 'individual', tipo: 'general' };
            this.modalNotificacion = true;
        },

        async enviarNotificacion() {
            try {
                const token = localStorage.getItem('token');
                const endpoint = this.notifForm.modo === 'individual' 
                    ? '/api/seguridad/notificacion'
                    : '/api/seguridad/notificacion-masiva';
                
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.notifForm)
                });

                if (response.ok) {
                    this.showMessage('Notificación enviada correctamente', 'success');
                    this.modalNotificacion = false;
                    await this.cargarNotificaciones();
                } else {
                    this.showMessage('Error al enviar notificación', 'error');
                }
            } catch (error) {
                this.showMessage('Error al enviar notificación', 'error');
            }
        },

        async marcarLeida(id) {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/seguridad/notificacion/leida', {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });

                if (response.ok) {
                    await this.cargarNotificaciones();
                    await this.cargarDashboard();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async cerrarSesion(id) {
            if (!confirm('¿Cerrar esta sesión?')) return;
            
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/seguridad/sesion/cerrar', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ sesion_id: id })
                });

                if (response.ok) {
                    this.showMessage('Sesión cerrada', 'success');
                    await this.cargarSesiones();
                }
            } catch (error) {
                this.showMessage('Error al cerrar sesión', 'error');
            }
        },

        bloquearIP() {
            this.ipForm = { bloqueado_por: 'SuperAdmin' };
            this.modalBloquearIP = true;
        },

        async guardarBloqueoIP() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/seguridad/ip/bloquear', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.ipForm)
                });

                if (response.ok) {
                    this.showMessage('IP bloqueada correctamente', 'success');
                    this.modalBloquearIP = false;
                    await this.cargarIPs();
                    await this.cargarDashboard();
                } else {
                    this.showMessage('Error al bloquear IP', 'error');
                }
            } catch (error) {
                this.showMessage('Error al bloquear IP', 'error');
            }
        },

        async desbloquearIP(id) {
            if (!confirm('¿Desbloquear esta IP?')) return;
            
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/seguridad/ip/desbloquear', {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });

                if (response.ok) {
                    this.showMessage('IP desbloqueada', 'success');
                    await this.cargarIPs();
                    await this.cargarDashboard();
                }
            } catch (error) {
                this.showMessage('Error al desbloquear IP', 'error');
            }
        },

        exportarLogs() {
            const params = new URLSearchParams({ formato: 'csv' });
            if (this.filtroTipo) params.append('tipo', this.filtroTipo);
            window.location.href = `/api/seguridad/logs/exportar?${params}`;
        },

        formatFecha(fecha) {
            return new Date(fecha).toLocaleString('es-CO', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
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

</body>
</html>
