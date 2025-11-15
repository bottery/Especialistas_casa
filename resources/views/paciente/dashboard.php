<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - Especialistas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
<script>
window.pacienteDashboard = function() {
    return {
        loading: false,
        usuario: {},
        stats: {
            solicitudesActivas: 0,
            solicitudesCompletadas: 0,
            proximaCita: null
        },
        solicitudes: [],
        historialMedico: [],
        activeTab: 'solicitudes',

        async init() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '/login';
                return;
            }

            const userData = JSON.parse(localStorage.getItem('usuario') || '{}');
            this.usuario = userData;

            await this.cargarDatos();
        },

        async cargarDatos() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                
                // Cargar estadísticas
                const statsResponse = await fetch('/api/paciente/stats', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                if (statsResponse.ok) {
                    const data = await statsResponse.json();
                    this.stats = data.stats || this.stats;
                }

                // Cargar solicitudes
                const solicitudesResponse = await fetch('/api/paciente/solicitudes', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                if (solicitudesResponse.ok) {
                    const data = await solicitudesResponse.json();
                    this.solicitudes = data.solicitudes || [];
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        async nuevaSolicitud() {
            window.location.href = '/paciente/nueva-solicitud';
        },

        verDetalle(id) {
            window.location.href = `/paciente/solicitud/${id}`;
        },

        getEstadoColor(estado) {
            const colores = {
                'pendiente': 'bg-yellow-100 text-yellow-800',
                'confirmada': 'bg-blue-100 text-blue-800',
                'en_progreso': 'bg-indigo-100 text-indigo-800',
                'completada': 'bg-green-100 text-green-800',
                'cancelada': 'bg-red-100 text-red-800'
            };
            return colores[estado] || 'bg-gray-100 text-gray-800';
        },

        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('es-CO', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatMonto(monto) {
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0
            }).format(monto);
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
<body class="bg-gray-50" x-data="pacienteDashboard()" x-init="init()">
    <!-- Header -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3">
                    <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">Mi Panel</h1>
                        <p class="text-xs text-gray-500" x-text="usuario.nombre"></p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="/" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </a>
                    <button @click="logout()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Tarjetas de estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" x-show="!loading">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Solicitudes Activas</p>
                        <p class="text-3xl font-bold text-indigo-600 mt-2" x-text="stats.solicitudesActivas || 0">0</p>
                    </div>
                    <div class="bg-indigo-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Completadas</p>
                        <p class="text-3xl font-bold text-green-600 mt-2" x-text="stats.solicitudesCompletadas || 0">0</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                <p class="text-sm text-indigo-100 mb-2">Próxima Cita</p>
                <template x-if="stats.proximaCita">
                    <div>
                        <p class="text-lg font-semibold" x-text="formatDate(stats.proximaCita.fecha)"></p>
                        <p class="text-sm text-indigo-100 mt-1" x-text="stats.proximaCita.servicio"></p>
                    </div>
                </template>
                <template x-if="!stats.proximaCita">
                    <p class="text-indigo-100">No hay citas programadas</p>
                </template>
            </div>
        </div>

        <!-- Botón Nueva Solicitud -->
        <div class="mb-6">
            <button @click="nuevaSolicitud()" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Nueva Solicitud</span>
            </button>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex space-x-8">
                <button @click="activeTab = 'solicitudes'" 
                        :class="activeTab === 'solicitudes' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="py-4 px-1 border-b-2 font-medium text-sm">
                    Mis Solicitudes
                </button>
                <button @click="activeTab = 'historial'" 
                        :class="activeTab === 'historial' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="py-4 px-1 border-b-2 font-medium text-sm">
                    Historial Médico
                </button>
            </nav>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <!-- Contenido Tabs -->
        <div x-show="!loading">
            <!-- Tab: Solicitudes -->
            <div x-show="activeTab === 'solicitudes'">
                <div x-show="solicitudes.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500 text-lg">No tienes solicitudes aún</p>
                    <button @click="nuevaSolicitud()" class="mt-4 text-indigo-600 hover:text-indigo-700 font-medium">
                        Crear tu primera solicitud
                    </button>
                </div>

                <div x-show="solicitudes.length > 0" class="space-y-4">
                    <template x-for="solicitud in solicitudes" :key="solicitud.id">
                        <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition cursor-pointer" @click="verDetalle(solicitud.id)">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900" x-text="solicitud.servicio_nombre"></h3>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium" :class="getEstadoColor(solicitud.estado)" x-text="solicitud.estado"></span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">
                                        <span class="font-medium">Fecha programada:</span>
                                        <span x-text="formatDate(solicitud.fecha_programada)"></span>
                                    </p>
                                    <p class="text-sm text-gray-600" x-show="solicitud.profesional_nombre">
                                        <span class="font-medium">Profesional:</span>
                                        <span x-text="solicitud.profesional_nombre"></span>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Modalidad:</span>
                                        <span x-text="solicitud.modalidad"></span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-bold text-gray-900" x-text="formatMonto(solicitud.monto_total)"></p>
                                    <p class="text-xs text-gray-500 mt-1" x-show="solicitud.pagado">✓ Pagado</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Tab: Historial Médico -->
            <div x-show="activeTab === 'historial'">
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500 text-lg">Tu historial médico estará disponible próximamente</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
