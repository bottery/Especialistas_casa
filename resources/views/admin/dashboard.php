<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Especialistas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
        
        /* Estilos para las pestañas */
        [x-cloak] { 
            display: none !important; 
        }
        
        /* Animación de entrada de tabs */
        .tab-content-enter {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Mejora visual de las pestañas activas */
        .tab-active {
            position: relative;
        }
        
        .tab-active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: currentColor;
            border-radius: 2px 2px 0 0;
        }
    </style>
    <script src="/js/auth-interceptor.js"></script>
    <link rel="stylesheet" href="/css/skeleton.css">
    <link rel="stylesheet" href="/css/dark-mode.css">
    <link rel="stylesheet" href="/css/kanban.css">
    <script src="/js/dark-mode.js"></script>
    <script src="/js/keyboard-shortcuts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50" x-data="adminDashboard()">
    <!-- Toast Notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2" x-data="{ toasts: [] }" @show-toast.window="toasts.push($event.detail); setTimeout(() => toasts.shift(), 5000)">
        <template x-for="(toast, index) in toasts" :key="index">
            <div class="flex items-center gap-3 px-6 py-4 rounded-lg shadow-lg max-w-md animate-slide-in"
                 :class="{
                     'bg-green-500 text-white': toast.type === 'success',
                     'bg-red-500 text-white': toast.type === 'error',
                     'bg-blue-500 text-white': toast.type === 'info',
                     'bg-yellow-500 text-white': toast.type === 'warning'
                 }">
                <span x-text="toast.message"></span>
                <button @click="toasts.splice(index, 1)" class="ml-auto text-white hover:opacity-75">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Navbar -->
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/admin/dashboard" class="text-2xl font-bold text-blue-600">
                        🏥 Admin Panel
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button @click="notificacionesAbiertas = !notificacionesAbiertas" 
                                class="relative p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span x-show="stats.pendientes_asignacion > 0" 
                                  class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
                                  x-text="stats.pendientes_asignacion"></span>
                        </button>
                    </div>
                    
                    <!-- Toggle Dark Mode -->
                    <button @click="window.darkMode.toggle()" class="p-2 text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition" title="Cambiar tema">
                        <svg x-show="!window.darkMode.isDark()" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg x-show="window.darkMode.isDark()" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>
                    
                    <span class="text-gray-700 dark:text-gray-300 font-medium"><?= htmlspecialchars($_SESSION['user']->nombre ?? '') ?></span>
                    <a href="/logout" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Estadísticas -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Skeletons para estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" x-show="loading">
            <div class="skeleton-stat-card"></div>
            <div class="skeleton-stat-card"></div>
            <div class="skeleton-stat-card"></div>
            <div class="skeleton-stat-card"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" x-show="!loading">
            <!-- Pendientes Asignación -->
            <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm font-medium">Pendientes Asignación</p>
                        <p class="text-3xl font-bold mt-2" x-text="stats.pendientes_asignacion">0</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- En Proceso -->
            <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Servicios en Proceso</p>
                        <p class="text-3xl font-bold mt-2" x-text="stats.en_proceso">0</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Completadas Hoy -->
            <div class="bg-gradient-to-br from-green-400 to-green-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Completadas Hoy</p>
                        <p class="text-3xl font-bold mt-2" x-text="stats.completadas_hoy">0</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Ingresos del Mes -->
            <div class="bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Ingresos del Mes</p>
                        <p class="text-3xl font-bold mt-2" x-text="'$' + stats.ingresos_del_mes.toLocaleString('es-CO')">$0</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficas de Estadísticas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Gráfica de Solicitudes por Día -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">📊 Solicitudes por Día</h3>
                <div style="height: 250px;">
                    <canvas id="solicitudesChart"></canvas>
                </div>
            </div>

            <!-- Gráfica de Servicios Más Solicitados -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">🎯 Servicios Más Solicitados</h3>
                <div style="height: 250px;">
                    <canvas id="serviciosChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Sistema de Pestañas para Solicitudes -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6" x-data="{ activeTab: 'en-proceso' }">
            <!-- Tabs Header -->
            <div class="border-b border-gray-200 bg-gray-50">
                <nav class="flex -mb-px overflow-x-auto">
                    <button @click="activeTab = 'en-proceso'; cargarSolicitudesEnProceso()" 
                            :class="activeTab === 'en-proceso' ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                        <span>⚡</span>
                        <span>En Proceso</span>
                        <span x-show="solicitudesEnProceso.length > 0" 
                              class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-semibold"
                              :class="activeTab === 'en-proceso' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'"
                              x-text="solicitudesEnProceso.length"></span>
                    </button>
                    
                    <button @click="activeTab = 'pendientes-pago'; cargarSolicitudesPendientesPago()" 
                            :class="activeTab === 'pendientes-pago' ? 'border-yellow-500 text-yellow-600 bg-yellow-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                        <span>💳</span>
                        <span>Pendientes de Pago</span>
                        <span x-show="solicitudesPendientesPago.length > 0" 
                              class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-semibold"
                              :class="activeTab === 'pendientes-pago' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600'"
                              x-text="solicitudesPendientesPago.length"></span>
                    </button>
                    
                    <button @click="activeTab = 'pendientes-asignacion'; cargarSolicitudes()" 
                            :class="activeTab === 'pendientes-asignacion' ? 'border-purple-500 text-purple-600 bg-purple-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                        <span>📋</span>
                        <span>Pendientes Asignación</span>
                        <span x-show="stats.pendientes_asignacion > 0" 
                              class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-semibold"
                              :class="activeTab === 'pendientes-asignacion' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-600'"
                              x-text="stats.pendientes_asignacion"></span>
                    </button>
                    
                    <button @click="activeTab = 'completados'; cargarReportes()" 
                            :class="activeTab === 'completados' ? 'border-green-500 text-green-600 bg-green-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                        <span>✅</span>
                        <span>Servicios Completados</span>
                        <span x-show="reportes.length > 0" 
                              class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-semibold"
                              :class="activeTab === 'completados' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                              x-text="reportes.length"></span>
                    </button>
                    
                    <button @click="activeTab = 'profesionales'; cargarListaProfesionales()" 
                            :class="activeTab === 'profesionales' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                        <span>👨‍⚕️</span>
                        <span>Gestión de Profesionales</span>
                        <span x-show="profesionales.length > 0" 
                              class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-semibold"
                              :class="activeTab === 'profesionales' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-600'"
                              x-text="profesionales.length"></span>
                    </button>
                    
                    <!-- Tab Kanban - Oculto temporalmente -->
                    <button @click="activeTab = 'kanban'; iniciarKanban()" 
                            :class="activeTab === 'kanban' ? 'border-pink-500 text-pink-600 bg-pink-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2"
                            style="display: none;">
                        <span>📊</span>
                        <span>Vista Kanban</span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content: Solicitudes En Proceso -->
            <div x-show="activeTab === 'en-proceso'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-blue-50">
                    <h3 class="text-lg font-semibold text-gray-800">Solicitudes En Proceso</h3>
                    <button @click="cargarSolicitudesEnProceso()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                        🔄 Actualizar
                    </button>
                </div>

            <div x-show="!loading && solicitudesEnProceso.length === 0">
                <div class="text-center py-8">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No hay solicitudes en proceso</p>
                </div>
            </div>

            <div x-show="!loading && solicitudesEnProceso.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paciente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profesional</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servicio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="solicitud in solicitudesEnProceso" :key="solicitud.id">
                            <tr class="hover:bg-blue-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="'#' + solicitud.id"></td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900" x-text="solicitud.paciente_nombre + ' ' + solicitud.paciente_apellido"></div>
                                    <div class="text-sm text-gray-500" x-text="solicitud.paciente_telefono"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900" x-text="solicitud.profesional_nombre + ' ' + solicitud.profesional_apellido"></div>
                                    <div class="text-sm text-gray-500" x-text="solicitud.profesional_telefono"></div>
                                    <div class="text-xs text-blue-600 flex items-center mt-1">
                                        ⭐ <span x-text="solicitud.calificacion_promedio"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900" x-text="solicitud.servicio_nombre"></div>
                                    <div class="text-sm text-gray-500" x-text="solicitud.especialidad || solicitud.servicio_tipo"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div x-text="new Date(solicitud.fecha_programada).toLocaleDateString('es-CO')"></div>
                                    <div x-text="solicitud.hora_programada || 'Sin hora'"></div>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900" x-text="'$' + new Intl.NumberFormat('es-CO').format(solicitud.monto_total)"></td>
                                <td class="px-6 py-4 text-sm">
                                    <button @click="verDetallesSolicitud(solicitud)" 
                                            class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                                        👁️ Ver Detalles
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Content: Solicitudes Pendientes de Confirmación de Pago -->
        <div x-show="activeTab === 'pendientes-pago'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-yellow-50">
                <h3 class="text-lg font-semibold text-gray-800">Solicitudes Pendientes de Confirmación de Pago</h3>
                <button @click="cargarSolicitudesPendientesPago()" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition text-sm">
                    🔄 Actualizar
                </button>
            </div>

            <div x-show="!loading && solicitudesPendientesPago.length === 0">
                <div class="text-center py-8">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No hay solicitudes pendientes de confirmación de pago</p>
                </div>
            </div>

            <div x-show="!loading && solicitudesPendientesPago.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paciente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servicio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprobante</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="solicitud in solicitudesPendientesPago" :key="solicitud.id">
                            <tr class="hover:bg-yellow-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="'#' + solicitud.id"></td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900" x-text="solicitud.paciente_nombre"></div>
                                    <div class="text-sm text-gray-500" x-text="solicitud.paciente_telefono"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900" x-text="solicitud.servicio_nombre"></div>
                                    <div class="text-sm text-gray-500" x-text="solicitud.servicio_tipo"></div>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900" x-text="'$' + new Intl.NumberFormat('es-CO').format(solicitud.monto_total)"></td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                        Verificar por WhatsApp
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <button @click="aprobarPago(solicitud.id)" 
                                            class="px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                                        ✓ Aprobar
                                    </button>
                                    <button @click="rechazarPago(solicitud.id)" 
                                            class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
                                        ✗ Rechazar
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Content: Solicitudes Pendientes de Asignación -->
        <div x-show="activeTab === 'pendientes-asignacion'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-purple-50">
                <h3 class="text-lg font-semibold text-gray-800">Solicitudes Pendientes de Asignación</h3>
                <button @click="cargarSolicitudes()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
                    🔄 Actualizar
                </button>
            </div>

            <!-- Skeleton para tabla -->
            <div x-show="loading" class="p-6 space-y-4">
                <div class="skeleton-table-row"></div>
                <div class="skeleton-table-row"></div>
                <div class="skeleton-table-row"></div>
                <div class="skeleton-table-row"></div>
                <div class="skeleton-table-row"></div>
            </div>

            <!-- Lista de Solicitudes -->
            <div x-show="!loading">
                <template x-if="solicitudes.length === 0">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay solicitudes pendientes</h3>
                        <p class="mt-1 text-sm text-gray-500">Todas las solicitudes han sido asignadas</p>
                    </div>
                </template>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servicio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pago</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="solicitud in solicitudes" :key="solicitud.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="'#' + solicitud.id"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900" x-text="solicitud.paciente_nombre + ' ' + solicitud.paciente_apellido"></div>
                                        <div class="text-sm text-gray-500" x-text="solicitud.paciente_telefono"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900" x-text="solicitud.servicio_nombre"></div>
                                        <div class="text-sm text-gray-500" x-text="solicitud.especialidad || solicitud.servicio_tipo"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div x-text="new Date(solicitud.fecha_programada).toLocaleDateString('es-CO')"></div>
                                        <div x-text="solicitud.hora_programada || 'Sin hora'"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="solicitud.estado_pago === 'Confirmado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" 
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                              x-text="solicitud.estado_pago"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="'$' + parseFloat(solicitud.monto_total).toLocaleString('es-CO')"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button @click="abrirModalAsignacion(solicitud)" 
                                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                                            👤 Asignar
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Content: Servicios Completados -->
        <div x-show="activeTab === 'completados'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-green-50">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Servicios Completados - Reportes y Evaluaciones</h3>
                    <p class="text-sm text-gray-600 mt-1">Control de calidad: Revisa reportes de profesionales y calificaciones de pacientes</p>
                </div>
                <button @click="cargarReportes()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                    🔄 Actualizar
                </button>
            </div>

            <!-- Filtros de Búsqueda -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                        <input type="date" x-model="filtrosReportes.fecha_desde" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                        <input type="date" x-model="filtrosReportes.fecha_hasta" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado Calificación</label>
                        <select x-model="filtrosReportes.calificado" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <option value="">Todos</option>
                            <option value="1">Con calificación</option>
                            <option value="0">Sin calificación</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button @click="cargarReportes()" 
                                class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            🔍 Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estadísticas de Reportes -->
            <div x-show="estadisticasReportes" class="px-6 py-4 bg-blue-50 border-b border-gray-200">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600" x-text="estadisticasReportes.total"></p>
                        <p class="text-xs text-gray-600">Total Reportes</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600" x-text="estadisticasReportes.con_calificacion"></p>
                        <p class="text-xs text-gray-600">Con Calificación</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-yellow-600" x-text="estadisticasReportes.sin_calificacion"></p>
                        <p class="text-xs text-gray-600">Sin Calificación</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-purple-600" x-text="estadisticasReportes.promedio_calificacion + ' ⭐'"></p>
                        <p class="text-xs text-gray-600">Promedio</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-indigo-600" x-text="'$' + estadisticasReportes.total_ingresos.toLocaleString('es-CO')"></p>
                        <p class="text-xs text-gray-600">Total Ingresos</p>
                    </div>
                </div>
            </div>

            <!-- Tabla de Reportes -->
            <div x-show="!loadingReportes && reportes.length === 0">
                <div class="text-center py-8">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No hay reportes disponibles</p>
                </div>
            </div>

            <div x-show="!loadingReportes && reportes.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paciente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profesional</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servicio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Calificación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="reporte in reportes" :key="reporte.id">
                            <tr class="hover:bg-green-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="'#' + reporte.id"></td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900" x-text="reporte.paciente_nombre + ' ' + reporte.paciente_apellido"></div>
                                    <div class="text-sm text-gray-500" x-text="reporte.paciente_email"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900" x-text="reporte.profesional_nombre + ' ' + reporte.profesional_apellido"></div>
                                    <div class="text-sm text-gray-500" x-text="reporte.especialidad"></div>
                                    <div class="text-xs text-blue-600 flex items-center mt-1">
                                        ⭐ <span x-text="reporte.puntuacion_promedio"></span> 
                                        <span class="text-gray-400 ml-1" x-text="'(' + reporte.total_calificaciones + ')'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900" x-text="reporte.servicio_nombre"></div>
                                    <div class="text-sm text-gray-500" x-text="reporte.modalidad"></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div x-text="new Date(reporte.fecha_completada).toLocaleDateString('es-CO')"></div>
                                    <div x-text="new Date(reporte.fecha_completada).toLocaleTimeString('es-CO', {hour: '2-digit', minute: '2-digit'})"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <template x-if="reporte.calificado">
                                        <div class="flex flex-col items-start">
                                            <div class="flex items-center">
                                                <span class="text-yellow-500 text-lg mr-1">⭐</span>
                                                <span class="text-sm font-bold text-gray-900" x-text="reporte.calificacion_paciente + '/5'"></span>
                                            </div>
                                            <span class="text-xs text-gray-500" x-show="reporte.comentario_paciente">Con comentario</span>
                                        </div>
                                    </template>
                                    <template x-if="!reporte.calificado">
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                            Sin calificar
                                        </span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900" x-text="'$' + parseFloat(reporte.monto_total).toLocaleString('es-CO')"></td>
                                <td class="px-6 py-4 text-sm">
                                    <button @click="verDetalleReporte(reporte.id)" 
                                            class="px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                                        📄 Ver Reporte
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Content: Gestión de Profesionales -->
        <div x-show="activeTab === 'profesionales'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-indigo-50">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">👨‍⚕️ Gestión de Profesionales</h3>
                    <p class="text-sm text-gray-600 mt-1">Administra médicos, enfermeras, veterinarios y demás profesionales</p>
                </div>
                <div class="flex gap-2">
                    <button @click="cargarProfesionales()" class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition text-sm font-medium">
                        🔄 Actualizar
                    </button>
                    <button @click="abrirModalNuevoProfesional()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium flex items-center gap-2">
                        <span>➕</span> Nuevo Profesional
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                        <input type="text" x-model="filtrosProfesionales.busqueda" @input="cargarProfesionales()"
                               placeholder="Nombre, email, especialidad..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select x-model="filtrosProfesionales.tipo" @change="cargarProfesionales()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            <option value="medico">Médico</option>
                            <option value="enfermera">Enfermera</option>
                            <option value="veterinario">Veterinario</option>
                            <option value="laboratorio">Laboratorio</option>
                            <option value="ambulancia">Ambulancia</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select x-model="filtrosProfesionales.estado" @change="cargarProfesionales()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="bloqueado">Bloqueado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Especialidad</label>
                        <input type="text" x-model="filtrosProfesionales.especialidad" @input="cargarProfesionales()"
                               placeholder="Cardiología, Pediatría..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Tabla de Profesionales -->
            <div x-show="!loadingProfesionales && profesionales.length === 0">
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No se encontraron profesionales</p>
                    <button @click="abrirModalNuevoProfesional()" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        Agregar Primer Profesional
                    </button>
                </div>
            </div>

            <div x-show="!loadingProfesionales && profesionales.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profesional</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo / Especialidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servicios</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Calificación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="prof in profesionales" :key="prof.id">
                            <tr class="hover:bg-indigo-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="'#' + prof.id"></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <span class="text-lg" x-text="prof.tipo_profesional === 'medico' ? '👨‍⚕️' : prof.tipo_profesional === 'enfermera' ? '👩‍⚕️' : prof.tipo_profesional === 'veterinario' ? '🐾' : prof.tipo_profesional === 'laboratorio' ? '🔬' : '🚑'"></span>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900" x-text="prof.nombre + ' ' + prof.apellido"></div>
                                            <div class="text-xs text-gray-500" x-text="prof.profesion || 'Sin especificar'"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 capitalize" x-text="prof.tipo_profesional"></div>
                                    <div class="text-xs text-gray-500" x-text="prof.especialidad || 'General'"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900" x-text="prof.email"></div>
                                    <div class="text-xs text-gray-500" x-text="prof.telefono_whatsapp || prof.telefono || 'Sin teléfono'"></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800" x-text="prof.total_servicios + ' completados'"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <span class="text-yellow-500 text-sm">⭐</span>
                                        <span class="ml-1 text-sm font-medium" x-text="prof.puntuacion_promedio || '5.00'"></span>
                                        <span class="ml-1 text-xs text-gray-400" x-text="'(' + (prof.total_calificaciones || 0) + ')'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full" 
                                          :class="{
                                              'bg-green-100 text-green-800': prof.estado === 'activo',
                                              'bg-gray-100 text-gray-800': prof.estado === 'inactivo',
                                              'bg-red-100 text-red-800': prof.estado === 'bloqueado'
                                          }"
                                          x-text="prof.estado"></span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex gap-2">
                                        <button @click="editarProfesional(prof)" 
                                                class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-xs">
                                            ✏️ Editar
                                        </button>
                                        <button @click="cambiarEstadoProfesional(prof)" 
                                                class="px-3 py-1.5 rounded-lg transition text-xs"
                                                :class="prof.estado === 'activo' ? 'bg-yellow-600 text-white hover:bg-yellow-700' : 'bg-green-600 text-white hover:bg-green-700'"
                                                x-text="prof.estado === 'activo' ? '⏸️ Desactivar' : '▶️ Activar'"></button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab Content: Vista Kanban -->
        <div x-show="activeTab === 'kanban'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-pink-50">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">📊 Vista Kanban - Gestión Visual</h3>
                    <p class="text-sm text-gray-600 mt-1">Arrastra y suelta las tarjetas para cambiar el estado de las solicitudes</p>
                </div>
                <div class="flex gap-2">
                    <button @click="kanbanBoard?.cargarSolicitudes()" class="px-4 py-2 bg-pink-100 text-pink-700 rounded-lg hover:bg-pink-200 transition text-sm font-medium">
                        🔄 Actualizar
                    </button>
                </div>
            </div>

            <!-- Filtros del Kanban -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🔍 Buscar</label>
                        <input type="text" 
                               @input="kanbanBoard?.aplicarFiltro('busqueda', $event.target.value)"
                               placeholder="ID, paciente, profesional, servicio..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🏥 Especialidad</label>
                        <select @change="kanbanBoard?.aplicarFiltro('especialidad', $event.target.value)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                            <option value="">Todas las especialidades</option>
                            <template x-for="esp in especialidades" :key="esp.id">
                                <option :value="esp.nombre" x-text="esp.icono + ' ' + esp.nombre"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">👨‍⚕️ Profesional</label>
                        <select @change="kanbanBoard?.aplicarFiltro('profesional', $event.target.value)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                            <option value="">Todos los profesionales</option>
                            <template x-for="prof in profesionales" :key="prof.id">
                                <option :value="prof.id" x-text="prof.nombre + ' ' + prof.apellido"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Container del Kanban Board -->
            <div id="kanban-container" class="p-6"></div>
        </div>

        </div>
        <!-- Fin del sistema de pestañas -->
    </div>

    <!-- Modal de Detalle de Reporte -->
    <div x-show="modalReporteAbierto" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @click.self="cerrarModalReporte()">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="relative bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="sticky top-0 bg-gradient-to-r from-green-500 to-green-600 px-6 py-4 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-white">📄 Detalle Completo del Reporte</h3>
                        <p class="text-green-100 text-sm" x-show="reporteDetalle" x-text="'Solicitud #' + reporteDetalle.solicitud_id"></p>
                    </div>
                    <button @click="cerrarModalReporte()" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div x-show="reporteDetalle" class="p-6 space-y-6">
                    <!-- Información General -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Datos del Paciente -->
                        <div class="bg-blue-50 rounded-lg p-4">
                            <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                <span class="text-2xl mr-2">👤</span> Paciente
                            </h4>
                            <div class="space-y-2 text-sm">
                                <p><span class="font-semibold">Nombre:</span> <span x-text="reporteDetalle.paciente?.nombre"></span></p>
                                <p><span class="font-semibold">Email:</span> <span x-text="reporteDetalle.paciente?.email"></span></p>
                                <p><span class="font-semibold">Teléfono:</span> <span x-text="reporteDetalle.paciente?.telefono"></span></p>
                            </div>
                        </div>

                        <!-- Datos del Profesional -->
                        <div class="bg-green-50 rounded-lg p-4">
                            <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                                <span class="text-2xl mr-2">👨‍⚕️</span> Profesional
                            </h4>
                            <div class="space-y-2 text-sm">
                                <p><span class="font-semibold">Nombre:</span> <span x-text="reporteDetalle.profesional?.nombre"></span></p>
                                <p><span class="font-semibold">Especialidad:</span> <span x-text="reporteDetalle.profesional?.especialidad"></span></p>
                                <p><span class="font-semibold">Calificación:</span> 
                                    <span class="text-yellow-500" x-text="'⭐ ' + reporteDetalle.profesional?.puntuacion_promedio"></span>
                                    <span class="text-gray-500 text-xs" x-text="'(' + reporteDetalle.profesional?.total_calificaciones + ' evaluaciones)'"></span>
                                </p>
                                <p><span class="font-semibold">Servicios completados:</span> <span x-text="reporteDetalle.profesional?.servicios_completados"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Servicio -->
                    <div class="bg-purple-50 rounded-lg p-4">
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                            <span class="text-2xl mr-2">🏥</span> Servicio Prestado
                        </h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <p><span class="font-semibold">Servicio:</span> <span x-text="reporteDetalle.servicio?.nombre"></span></p>
                            <p><span class="font-semibold">Modalidad:</span> <span x-text="reporteDetalle.servicio?.modalidad"></span></p>
                            <p><span class="font-semibold">Fecha programada:</span> <span x-text="new Date(reporteDetalle.fecha_programada).toLocaleString('es-CO')"></span></p>
                            <p><span class="font-semibold">Fecha completada:</span> <span x-text="new Date(reporteDetalle.fecha_completada).toLocaleString('es-CO')"></span></p>
                        </div>
                    </div>

                    <!-- Reporte del Profesional -->
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                            <span class="text-2xl mr-2">📋</span> Reporte del Profesional
                        </h4>
                        <div class="space-y-3">
                            <div>
                                <p class="font-semibold text-sm text-gray-700 mb-1">Reporte del Servicio:</p>
                                <p class="text-sm text-gray-800 bg-white p-3 rounded border border-yellow-200" x-text="reporteDetalle.reporte_profesional || 'No proporcionado'"></p>
                            </div>
                            <div>
                                <p class="font-semibold text-sm text-gray-700 mb-1">Diagnóstico / Conclusión:</p>
                                <p class="text-sm text-gray-800 bg-white p-3 rounded border border-yellow-200" x-text="reporteDetalle.diagnostico || 'No proporcionado'"></p>
                            </div>
                            <div x-show="reporteDetalle.notas_adicionales">
                                <p class="font-semibold text-sm text-gray-700 mb-1">Notas Adicionales:</p>
                                <p class="text-sm text-gray-800 bg-white p-3 rounded border border-yellow-200" x-text="reporteDetalle.notas_adicionales"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Calificación del Paciente -->
                    <div class="bg-pink-50 rounded-lg p-4">
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                            <span class="text-2xl mr-2">⭐</span> Evaluación del Paciente
                        </h4>
                        <template x-if="reporteDetalle.calificacion?.calificado">
                            <div class="space-y-3">
                                <div class="flex items-center space-x-2">
                                    <span class="text-3xl text-yellow-500">⭐</span>
                                    <span class="text-2xl font-bold text-gray-800" x-text="reporteDetalle.calificacion?.puntuacion + ' / 5'"></span>
                                </div>
                                <div x-show="reporteDetalle.calificacion?.comentario">
                                    <p class="font-semibold text-sm text-gray-700 mb-1">Comentario:</p>
                                    <p class="text-sm text-gray-800 bg-white p-3 rounded border border-pink-200" x-text="reporteDetalle.calificacion?.comentario"></p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    Calificado el <span x-text="new Date(reporteDetalle.calificacion?.fecha).toLocaleString('es-CO')"></span>
                                </p>
                            </div>
                        </template>
                        <template x-if="!reporteDetalle.calificacion?.calificado">
                            <div class="text-center py-4">
                                <span class="inline-block px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                                    ⚠️ El paciente aún no ha calificado este servicio
                                </span>
                            </div>
                        </template>
                    </div>

                    <!-- Información Financiera -->
                    <div class="bg-indigo-50 rounded-lg p-4">
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                            <span class="text-2xl mr-2">💰</span> Información Financiera
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <p class="font-semibold text-gray-700">Monto Total:</p>
                                <p class="text-lg font-bold text-indigo-600" x-text="'$' + parseFloat(reporteDetalle.finanzas?.monto_total).toLocaleString('es-CO')"></p>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-700">Para Profesional:</p>
                                <p class="text-lg font-bold text-green-600" x-text="'$' + parseFloat(reporteDetalle.finanzas?.monto_profesional).toLocaleString('es-CO')"></p>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-700">Para Plataforma:</p>
                                <p class="text-lg font-bold text-blue-600" x-text="'$' + parseFloat(reporteDetalle.finanzas?.monto_plataforma).toLocaleString('es-CO')"></p>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-700">Estado Pago:</p>
                                <p class="text-lg font-bold" :class="reporteDetalle.finanzas?.pagado ? 'text-green-600' : 'text-red-600'" 
                                   x-text="reporteDetalle.finanzas?.pagado ? '✓ Pagado' : '✗ Pendiente'"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="sticky bottom-0 bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
                    <button @click="cerrarModalReporte()" 
                            class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-medium">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Asignación -->
    <div x-show="modalAsignacionAbierto" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div @click="cerrarModalAsignacion()" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <div class="relative inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">
                        <span x-show="solicitudSeleccionada?.servicio_tipo !== 'ambulancia'">🎯 Asignar Profesional</span>
                        <span x-show="solicitudSeleccionada?.servicio_tipo === 'ambulancia'">🚑 Asignar Operador de Ambulancia</span>
                    </h3>
                    <button @click="cerrarModalAsignacion()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Información de la Solicitud -->
                <div x-show="solicitudSeleccionada" class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-6 mb-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                        </svg>
                        Detalles de la Solicitud #<span x-text="solicitudSeleccionada?.id"></span>
                    </h4>
                    
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                        <!-- Paciente -->
                        <div class="bg-white rounded-lg p-3 shadow-sm">
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">👤 Paciente</p>
                            <p class="font-bold text-gray-900" x-text="solicitudSeleccionada?.paciente_nombre + ' ' + solicitudSeleccionada?.paciente_apellido"></p>
                            <p class="text-sm text-gray-600 mt-1" x-text="solicitudSeleccionada?.paciente_telefono"></p>
                            <p class="text-sm text-gray-600" x-text="solicitudSeleccionada?.paciente_email"></p>
                        </div>

                        <!-- Servicio -->
                        <div class="bg-white rounded-lg p-3 shadow-sm">
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">🏥 Servicio</p>
                            <p class="font-bold text-gray-900" x-text="solicitudSeleccionada?.servicio_nombre"></p>
                            <p class="text-sm text-gray-600 mt-1" x-text="solicitudSeleccionada?.servicio_tipo"></p>
                        </div>

                        <!-- Especialidad -->
                        <div class="bg-white rounded-lg p-3 shadow-sm">
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">⭐ Especialidad</p>
                            <p class="font-bold text-gray-900" x-text="solicitudSeleccionada?.especialidad || 'General'"></p>
                            <p class="text-xs text-gray-500 mt-1" x-show="!solicitudSeleccionada?.especialidad">Sin especialidad específica</p>
                        </div>

                        <!-- Fecha y Hora -->
                        <div class="bg-white rounded-lg p-3 shadow-sm">
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">📅 Fecha</p>
                            <p class="font-bold text-gray-900" x-text="new Date(solicitudSeleccionada?.fecha_programada).toLocaleDateString('es-CO', {weekday: 'short', day: 'numeric', month: 'short'})"></p>
                            <p class="text-sm text-gray-600 mt-1" x-text="solicitudSeleccionada?.hora_programada || 'Sin hora específica'"></p>
                        </div>

                        <!-- Modalidad -->
                        <div class="bg-white rounded-lg p-3 shadow-sm">
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">📍 Modalidad</p>
                            <p class="font-bold text-gray-900" x-text="solicitudSeleccionada?.modalidad === 'domicilio' ? 'A domicilio' : solicitudSeleccionada?.modalidad === 'virtual' ? 'Virtual' : 'Consultorio'"></p>
                        </div>

                        <!-- Monto -->
                        <div class="bg-white rounded-lg p-3 shadow-sm">
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">💰 Monto</p>
                            <p class="font-bold text-green-600 text-lg" x-text="'$' + parseFloat(solicitudSeleccionada?.monto_total || 0).toLocaleString('es-CO')"></p>
                        </div>
                    </div>

                    <!-- Dirección y Observaciones -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="bg-white rounded-lg p-3 shadow-sm" x-show="solicitudSeleccionada?.direccion_servicio">
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">🏠 Dirección</p>
                            <p class="text-sm text-gray-900" x-text="solicitudSeleccionada?.direccion_servicio || 'No especificada'"></p>
                        </div>
                        
                        <div class="bg-white rounded-lg p-3 shadow-sm" x-show="solicitudSeleccionada?.observaciones">
                            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">📝 Observaciones</p>
                            <p class="text-sm text-gray-900" x-text="solicitudSeleccionada?.observaciones || 'Sin observaciones'"></p>
                        </div>
                    </div>
                </div>

                <!-- Información de filtrado -->
                <div x-show="solicitudSeleccionada?.especialidad_solicitada && solicitudSeleccionada?.servicio_tipo !== 'ambulancia'" 
                     class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-blue-900">
                                Filtrado por especialidad: <span class="font-bold" x-text="solicitudSeleccionada?.especialidad_solicitada"></span>
                            </p>
                            <p class="text-xs text-blue-700 mt-1">
                                Mostrando solo profesionales con esta especialidad, ordenados por calificación
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Lista de Profesionales -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">
                        <span x-show="solicitudSeleccionada?.servicio_tipo !== 'ambulancia'">👨‍⚕️ Profesionales Disponibles</span>
                        <span x-show="solicitudSeleccionada?.servicio_tipo === 'ambulancia'">🚑 Operadores Disponibles</span>
                    </h4>
                    
                    <div x-show="loadingProfesionales" class="flex justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>

                    <div x-show="!loadingProfesionales" class="max-h-96 overflow-y-auto space-y-3">
                        <template x-if="profesionalesDisponibles.length === 0">
                            <div class="text-center py-12 bg-gray-50 rounded-lg">
                                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <p class="text-gray-600 font-medium">No hay profesionales disponibles</p>
                                <p class="text-sm text-gray-500 mt-1">Intenta cargar de nuevo o contacta a soporte</p>
                            </div>
                        </template>

                        <template x-for="prof in profesionalesDisponibles" :key="prof.id">
                            <div @click="seleccionarProfesional(prof)" 
                                 :class="profesionalSeleccionado?.id === prof.id ? 'border-blue-500 bg-blue-50 shadow-lg' : 'border-gray-200 hover:border-blue-300 hover:shadow-md'"
                                 class="border-2 rounded-xl p-5 cursor-pointer transition-all duration-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-start space-x-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-xl shadow-md">
                                                    <span x-text="prof.nombre[0] + prof.apellido[0]"></span>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h5 class="text-xl font-bold text-gray-900" x-text="prof.nombre + ' ' + prof.apellido"></h5>
                                                    <span x-show="prof.tipo_profesional === 'ambulancia'" class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">🚑 OPERADOR</span>
                                                    <span x-show="prof.tipo_profesional === 'medico'" class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">👨‍⚕️ MÉDICO</span>
                                                    <span x-show="prof.tipo_profesional === 'enfermera'" class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">💉 ENFERMERA</span>
                                                    <span x-show="prof.tipo_profesional === 'veterinario'" class="px-2 py-1 text-xs font-semibold bg-purple-100 text-purple-800 rounded-full">🐾 VETERINARIO</span>
                                                    <span x-show="prof.tipo_profesional === 'laboratorio'" class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">🔬 LAB</span>
                                                </div>
                                                <p class="text-sm font-medium text-blue-600 mb-1" x-text="prof.especialidad || 'Especialidad no especificada'"></p>
                                                <p class="text-xs text-gray-500" x-text="prof.telefono"></p>
                                            </div>
                                        </div>
                                        <div class="mt-4 grid grid-cols-3 gap-3">
                                            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 rounded-lg p-3 text-center">
                                                <div class="flex items-center justify-center space-x-1 mb-1">
                                                    <span class="text-yellow-500 text-lg">⭐</span>
                                                    <span class="text-2xl font-bold text-yellow-700" x-text="parseFloat(prof.puntuacion_promedio).toFixed(1)"></span>
                                                </div>
                                                <p class="text-xs font-medium text-yellow-700" x-text="prof.total_calificaciones + ' reseñas'"></p>
                                            </div>
                                            <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-3 text-center">
                                                <p class="text-2xl font-bold text-green-700 mb-1" x-text="prof.servicios_completados"></p>
                                                <p class="text-xs font-medium text-green-700">Completados</p>
                                            </div>
                                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-3 text-center">
                                                <p class="text-sm font-bold text-blue-700" x-text="prof.ciudad || 'Bogotá'"></p>
                                                <p class="text-xs font-medium text-blue-700">Ubicación</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div x-show="profesionalSeleccionado?.id === prof.id" class="ml-4">
                                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Motivo de Asignación -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo de asignación (opcional)
                    </label>
                    <textarea x-model="motivoAsignacion" 
                              rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Ej: Mejor calificado en cardiología, disponibilidad confirmada..."></textarea>
                </div>

                <!-- Botones -->
                <div class="flex justify-end space-x-3">
                    <button @click="cerrarModalAsignacion()" 
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium">
                        Cancelar
                    </button>
                    <button @click="confirmarAsignacion()" 
                            :disabled="!profesionalSeleccionado || asignando"
                            :class="!profesionalSeleccionado || asignando ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg transition font-medium">
                        <span x-show="!asignando">✅ Confirmar Asignación</span>
                        <span x-show="asignando">Asignando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Detalles de Solicitud -->
    <div x-show="modalDetallesAbierto" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div @click="cerrarModalDetalles()" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <div class="relative inline-block w-full max-w-4xl p-8 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                        <svg class="w-7 h-7 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                        </svg>
                        Detalles Completos - Solicitud #<span x-text="solicitudDetalle?.id"></span>
                    </h3>
                    <button @click="cerrarModalDetalles()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Contenido -->
                <div class="space-y-6">
                    <!-- Estado de la Solicitud -->
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-90">Estado Actual</p>
                                <p class="text-2xl font-bold mt-1" x-text="solicitudDetalle?.estado === 'asignado' ? '✅ Asignado' : solicitudDetalle?.estado === 'en_proceso' ? '⚡ En Proceso' : solicitudDetalle?.estado === 'completado' ? '✔️ Completado' : solicitudDetalle?.estado"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm opacity-90">Monto Total</p>
                                <p class="text-3xl font-bold mt-1" x-text="'$' + new Intl.NumberFormat('es-CO').format(solicitudDetalle?.monto_total || 0)"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Paciente -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <span class="text-2xl mr-2">👤</span> Información del Paciente
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Nombre Completo</p>
                                <p class="text-gray-900 font-medium mt-1" x-text="solicitudDetalle?.paciente_nombre + ' ' + solicitudDetalle?.paciente_apellido"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Documento</p>
                                <p class="text-gray-900 font-medium mt-1" x-text="solicitudDetalle?.paciente_documento || 'No especificado'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Teléfono</p>
                                <p class="text-gray-900 font-medium mt-1" x-text="solicitudDetalle?.paciente_telefono"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Email</p>
                                <p class="text-gray-900 font-medium mt-1 break-all" x-text="solicitudDetalle?.paciente_email"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Profesional -->
                    <div x-show="solicitudDetalle?.profesional_nombre" class="bg-blue-50 rounded-xl p-6">
                        <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <span class="text-2xl mr-2">👨‍⚕️</span> Profesional Asignado
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Nombre Completo</p>
                                <p class="text-gray-900 font-medium mt-1" x-text="solicitudDetalle?.profesional_nombre + ' ' + solicitudDetalle?.profesional_apellido"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Especialidad</p>
                                <p class="text-gray-900 font-medium mt-1" x-text="solicitudDetalle?.profesional_especialidad || 'General'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Teléfono</p>
                                <p class="text-gray-900 font-medium mt-1" x-text="solicitudDetalle?.profesional_telefono"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Calificación</p>
                                <p class="text-gray-900 font-medium mt-1 flex items-center">
                                    ⭐ <span x-text="solicitudDetalle?.calificacion_promedio"></span> / 5.0
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles del Servicio -->
                    <div class="bg-green-50 rounded-xl p-6">
                        <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <span class="text-2xl mr-2">🏥</span> Detalles del Servicio
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Servicio</p>
                                <p class="text-gray-900 font-medium mt-1" x-text="solicitudDetalle?.servicio_nombre"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Tipo</p>
                                <p class="text-gray-900 font-medium mt-1" x-text="solicitudDetalle?.servicio_tipo"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Especialidad Solicitada</p>
                                <p class="text-gray-900 font-medium mt-1" x-text="solicitudDetalle?.especialidad || 'General'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Modalidad</p>
                                <p class="text-gray-900 font-medium mt-1" x-text="solicitudDetalle?.modalidad === 'domicilio' ? '🏠 A Domicilio' : solicitudDetalle?.modalidad === 'virtual' ? '💻 Virtual' : '🏢 Consultorio'"></p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-gray-500 uppercase font-semibold">Fecha y Hora Programada</p>
                                <p class="text-gray-900 font-medium mt-1">
                                    <span x-text="new Date(solicitudDetalle?.fecha_programada).toLocaleDateString('es-CO', {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'})"></span>
                                    <span x-show="solicitudDetalle?.hora_programada"> - <span x-text="solicitudDetalle?.hora_programada"></span></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Dirección y Observaciones -->
                    <div class="grid grid-cols-1 gap-4">
                        <div x-show="solicitudDetalle?.direccion_servicio" class="bg-yellow-50 rounded-xl p-6">
                            <h4 class="text-sm font-bold text-gray-900 mb-2 flex items-center">
                                <span class="text-xl mr-2">📍</span> Dirección del Servicio
                            </h4>
                            <p class="text-gray-900" x-text="solicitudDetalle?.direccion_servicio"></p>
                        </div>
                        
                        <div x-show="solicitudDetalle?.observaciones" class="bg-purple-50 rounded-xl p-6">
                            <h4 class="text-sm font-bold text-gray-900 mb-2 flex items-center">
                                <span class="text-xl mr-2">📝</span> Observaciones del Paciente
                            </h4>
                            <p class="text-gray-900" x-text="solicitudDetalle?.observaciones"></p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end mt-6">
                    <button @click="cerrarModalDetalles()" 
                            class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-medium">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Crear/Editar Profesional -->
    <div x-show="modalProfesionalAbierto" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
            <div @click="cerrarModalProfesional()" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-xl">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-indigo-500 to-purple-600">
                    <h3 class="text-2xl font-bold text-white" x-text="profesionalEditando ? '✏️ Editar Profesional' : '➕ Nuevo Profesional'"></h3>
                    <button @click="cerrarModalProfesional()" class="text-white hover:text-gray-200 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <form @submit.prevent="guardarProfesional()" class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                    <!-- Información Personal -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                            <span class="text-lg mr-2">👤</span> Información Personal
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre(s) *</label>
                                <input type="text" x-model="formProfesional.nombre" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Juan Carlos">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Apellidos *</label>
                                <input type="text" x-model="formProfesional.apellido" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                       placeholder="García López">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                <input type="email" x-model="formProfesional.email" required
                                       :disabled="profesionalEditando"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 disabled:bg-gray-100"
                                       placeholder="juan.garcia@ejemplo.com">
                                <p class="text-xs text-gray-500 mt-1" x-show="profesionalEditando">El email no se puede modificar</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña <span x-show="!profesionalEditando" class="text-red-500">*</span></label>
                                <input type="password" x-model="formProfesional.password" 
                                       :required="!profesionalEditando"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Mínimo 6 caracteres">
                                <p class="text-xs text-gray-500 mt-1" x-show="profesionalEditando">Dejar vacío para mantener la actual</p>
                            </div>
                        </div>
                    </div>

                    <!-- Información Profesional -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                            <span class="text-lg mr-2">🩺</span> Información Profesional
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Profesional *</label>
                                <select x-model="formProfesional.tipo_profesional" required
                                        @change="formProfesional.especialidad = ''"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Seleccione...</option>
                                    <option value="medico">Médico</option>
                                    <option value="enfermera">Enfermera</option>
                                    <option value="veterinario">Veterinario</option>
                                    <option value="laboratorio">Técnico de Laboratorio</option>
                                    <option value="ambulancia">Operador de Ambulancia</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Profesión / Título *</label>
                                <input type="text" x-model="formProfesional.profesion" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Ej: Médico Cirujano, Enfermera Jefe">
                            </div>
                            <div x-show="formProfesional.tipo_profesional === 'medico'">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Especialidad Médica *</label>
                                <input type="text" x-model="formProfesional.especialidad" 
                                       :required="formProfesional.tipo_profesional === 'medico'"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Ej: Cardiología, Pediatría, Medicina General">
                                <p class="text-xs text-gray-500 mt-1">Será visible para los pacientes al buscar</p>
                            </div>
                            <div x-show="formProfesional.tipo_profesional !== 'medico' && formProfesional.tipo_profesional !== ''">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Área/Especialidad</label>
                                <input type="text" x-model="formProfesional.especialidad"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Ej: Cuidados Intensivos, Emergencias">
                            </div>
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                            <span class="text-lg mr-2">📱</span> Información de Contacto
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono/WhatsApp *</label>
                                <input type="tel" x-model="formProfesional.telefono_whatsapp" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                       placeholder="+57 300 1234567">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono Adicional</label>
                                <input type="tel" x-model="formProfesional.telefono"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                       placeholder="+57 601 1234567">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dirección Residencial</label>
                                <input type="text" x-model="formProfesional.direccion"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Calle 123 #45-67, Apto 801">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dirección Consultorio/Oficina</label>
                                <input type="text" x-model="formProfesional.direccion_consultorio"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                       placeholder="Carrera 10 #20-30, Consultorio 201">
                            </div>
                        </div>
                    </div>

                    <!-- Documentos -->
                    <div class="bg-purple-50 rounded-lg p-4">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                            <span class="text-lg mr-2">📄</span> Documentos y Archivos
                        </h4>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hoja de Vida Digital (PDF)</label>
                            <div class="flex items-center gap-3">
                                <input type="file" @change="manejarArchivoHojaVida($event)" accept=".pdf"
                                       class="flex-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                <span x-show="formProfesional.hoja_vida_url" class="text-xs text-green-600">✓ Archivo cargado</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Formato: PDF. Tamaño máximo: 5MB</p>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                            <span class="text-lg mr-2">⚙️</span> Estado del Profesional
                        </h4>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado *</label>
                            <select x-model="formProfesional.estado" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="activo">✅ Activo - Puede recibir solicitudes</option>
                                <option value="inactivo">⏸️ Inactivo - No recibe solicitudes</option>
                                <option value="bloqueado">🚫 Bloqueado - Sin acceso al sistema</option>
                            </select>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button type="button" @click="cerrarModalProfesional()" 
                                class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="guardandoProfesional"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!guardandoProfesional" x-text="profesionalEditando ? '💾 Guardar Cambios' : '➕ Crear Profesional'"></span>
                            <span x-show="guardandoProfesional">Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function adminDashboard() {
            return {
                stats: {
                    pendientes_asignacion: 0,
                    en_proceso: 0,
                    completadas_hoy: 0,
                    ingresos_del_mes: 0
                },
                solicitudes: [],
                solicitudesEnProceso: [],
                solicitudesPendientesPago: [],
                loading: true,
                notificacionesAbiertas: false,
                
                // Modal de asignación
                modalAsignacionAbierto: false,
                solicitudSeleccionada: null,
                profesionalesDisponibles: [],
                loadingProfesionales: false,
                profesionalSeleccionado: null,
                motivoAsignacion: '',
                asignando: false,

                // Modal de detalles
                modalDetallesAbierto: false,
                solicitudDetalle: null,

                // Reportes
                reportes: [],
                loadingReportes: false,
                filtrosReportes: {
                    fecha_desde: '',
                    fecha_hasta: '',
                    calificado: ''
                },
                estadisticasReportes: {
                    total: 0,
                    con_calificacion: 0,
                    sin_calificacion: 0,
                    promedio_calificacion: 0,
                    total_ingresos: 0
                },
                modalReporteAbierto: false,
                reporteDetalle: {
                    solicitud_id: 0,
                    paciente: { nombre: '', email: '', telefono: '' },
                    profesional: { nombre: '', especialidad: '', puntuacion_promedio: 0, total_calificaciones: 0, servicios_completados: 0 },
                    servicio: { nombre: '', modalidad: '' },
                    fecha_programada: '',
                    fecha_completada: '',
                    reporte_profesional: '',
                    diagnostico: '',
                    notas_adicionales: '',
                    calificacion: { calificado: false },
                    finanzas: { monto_total: 0, monto_profesional: 0, monto_plataforma: 0, pagado: false }
                },

                // Gestión de Profesionales
                profesionales: [],
                loadingProfesionales: false,
                filtrosProfesionales: {
                    busqueda: '',
                    tipo: '',
                    estado: '',
                    especialidad: ''
                },
                modalProfesionalAbierto: false,
                profesionalEditando: null,
                guardandoProfesional: false,
                formProfesional: {
                    nombre: '',
                    apellido: '',
                    email: '',
                    password: '',
                    tipo_profesional: '',
                    profesion: '',
                    especialidad: '',
                    telefono_whatsapp: '',
                    telefono: '',
                    direccion: '',
                    direccion_consultorio: '',
                    hoja_vida_url: '',
                    estado: 'activo'
                },

                // Variables para gráficas
                solicitudesChartInstance: null,
                serviciosChartInstance: null,

                // Variables para Kanban
                especialidades: [],
                kanbanBoard: null,

                async init() {
                    await this.cargarStats();
                    await this.cargarSolicitudes();
                    await this.cargarSolicitudesEnProceso();
                    await this.cargarSolicitudesPendientesPago();
                    await this.cargarReportes();
                    await this.cargarEspecialidades();
                    
                    // Escuchar eventos del Kanban
                    window.addEventListener('ver-detalle-solicitud', (e) => {
                        this.verDetallesSolicitud({ id: e.detail.solicitudId });
                    });
                    
                    window.addEventListener('asignar-profesional', (e) => {
                        const solicitud = { id: e.detail.solicitudId };
                        this.abrirModalAsignacion(solicitud);
                    });
                    
                    // Auto-refresh cada 30 segundos
                    setInterval(() => {
                        this.cargarStats();
                        this.cargarSolicitudes();
                        this.cargarSolicitudesPendientesPago();
                    }, 30000);
                },

                showToast(message, type = 'info') {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message, type }
                    }));
                },

                async cargarStats() {
                    try {
                        const token = localStorage.getItem('token');
                        const response = await fetch('/api/admin/stats', {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.stats = data;
                            this.inicializarGraficas();
                        }
                    } catch (error) {
                        console.error('Error al cargar estadísticas:', error);
                    }
                },

                inicializarGraficas() {
                    // Destruir gráficas existentes antes de crear nuevas
                    if (this.solicitudesChartInstance) {
                        this.solicitudesChartInstance.destroy();
                    }
                    if (this.serviciosChartInstance) {
                        this.serviciosChartInstance.destroy();
                    }

                    // Gráfica de Solicitudes por Día
                    const ctx1 = document.getElementById('solicitudesChart');
                    if (ctx1) {
                        this.solicitudesChartInstance = new Chart(ctx1, {
                            type: 'line',
                            data: {
                                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                                datasets: [{
                                    label: 'Solicitudes',
                                    data: [12, 19, 15, 25, 22, 18, 10],
                                    borderColor: 'rgb(79, 70, 229)',
                                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // Gráfica de Servicios
                    const ctx2 = document.getElementById('serviciosChart');
                    if (ctx2) {
                        this.serviciosChartInstance = new Chart(ctx2, {
                            type: 'doughnut',
                            data: {
                                labels: ['Médico', 'Enfermería', 'Veterinario', 'Laboratorio', 'Ambulancia'],
                                datasets: [{
                                    data: [35, 25, 20, 12, 8],
                                    backgroundColor: [
                                        'rgba(59, 130, 246, 0.8)',
                                        'rgba(16, 185, 129, 0.8)',
                                        'rgba(245, 158, 11, 0.8)',
                                        'rgba(139, 92, 246, 0.8)',
                                        'rgba(239, 68, 68, 0.8)'
                                    ],
                                    borderWidth: 2,
                                    borderColor: '#fff'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }
                        });
                    }
                },

                async cargarSolicitudes() {
                    this.loading = true;
                    try {
                        const token = localStorage.getItem('token');
                        const response = await fetch('/api/admin/solicitudes/pendientes', {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        
                        console.log('Solicitudes response status:', response.status);
                        
                        if (response.ok) {
                            const data = await response.json();
                            console.log('Solicitudes data:', data);
                            this.solicitudes = data.solicitudes || [];
                        } else {
                            const errorText = await response.text();
                            console.error('Error loading solicitudes:', response.status, errorText);
                        }
                    } catch (error) {
                        console.error('Error al cargar solicitudes:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async cargarSolicitudesPendientesPago() {
                    try {
                        const token = localStorage.getItem('token');
                        const response = await fetch('/api/admin/pagos/pendientes-confirmacion', {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        
                        console.log('Response status:', response.status);
                        
                        if (response.ok) {
                            const data = await response.json();
                            console.log('Solicitudes pendientes de pago:', data);
                            this.solicitudesPendientesPago = data.solicitudes || [];
                            console.log('Array actualizado:', this.solicitudesPendientesPago);
                        } else {
                            console.error('Error en respuesta:', response.status);
                        }
                    } catch (error) {
                        console.error('Error al cargar solicitudes pendientes de pago:', error);
                    }
                },

                async cargarSolicitudesEnProceso() {
                    try {
                        const token = localStorage.getItem('token');
                        const response = await fetch('/api/admin/solicitudes/en-proceso', {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            console.log('Solicitudes en proceso:', data);
                            this.solicitudesEnProceso = data.solicitudes || [];
                        } else {
                            console.error('Error al cargar solicitudes en proceso:', response.status);
                        }
                    } catch (error) {
                        console.error('Error al cargar solicitudes en proceso:', error);
                    }
                },

                verDetallesSolicitud(solicitud) {
                    this.solicitudDetalle = solicitud;
                    this.modalDetallesAbierto = true;
                },

                cerrarModalDetalles() {
                    this.modalDetallesAbierto = false;
                    this.solicitudDetalle = null;
                },

                async aprobarPago(solicitudId) {
                    this.showToast('Procesando aprobación de pago...', 'info');

                    try {
                        const token = localStorage.getItem('token');
                        const response = await fetch(`/api/admin/solicitudes/${solicitudId}/aprobar-pago`, {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json'
                            }
                        });
                        
                        if (response.ok) {
                            this.showToast('✅ Pago aprobado. La solicitud está lista para asignar un profesional.', 'success');
                            await this.cargarSolicitudesPendientesPago();
                            await this.cargarSolicitudes();
                        } else {
                            const error = await response.json();
                            this.showToast('Error: ' + (error.error || 'No se pudo aprobar el pago'), 'error');
                        }
                    } catch (error) {
                        console.error('Error al aprobar pago:', error);
                        this.showToast('Error al aprobar el pago', 'error');
                    }
                },

                async rechazarPago(solicitudId) {
                    const motivo = prompt('¿Por qué rechazas este pago?');
                    if (!motivo) return;

                    this.showToast('Procesando rechazo de pago...', 'info');

                    try {
                        const token = localStorage.getItem('token');
                        const response = await fetch(`/api/admin/solicitudes/${solicitudId}/rechazar-pago`, {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ motivo })
                        });
                        
                        if (response.ok) {
                            this.showToast('❌ Pago rechazado. Se notificará al paciente.', 'warning');
                            await this.cargarSolicitudesPendientesPago();
                        } else {
                            const error = await response.json();
                            this.showToast('Error: ' + (error.error || 'No se pudo rechazar el pago'), 'error');
                        }
                    } catch (error) {
                        console.error('Error al rechazar pago:', error);
                        this.showToast('Error al rechazar el pago', 'error');
                    }
                },

                async verQRPago(solicitudId) {
                    try {
                        const token = localStorage.getItem('token');
                        const response = await fetch(`/api/admin/pagos/${solicitudId}/qr`, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            // Mostrar modal con QR y datos bancarios
                            alert(`QR de pago para solicitud #${solicitudId}\n\nCuenta: ${data.numero_cuenta}\nBanco: ${data.banco}\nTitular: ${data.titular_cuenta}`);
                        }
                    } catch (error) {
                        console.error('Error al obtener QR:', error);
                    }
                },

                async abrirModalAsignacion(solicitud) {
                    console.log('📋 Solicitud completa:', solicitud);
                    console.log('🔍 servicio_id:', solicitud.servicio_id);
                    console.log('🔍 especialidad_solicitada:', solicitud.especialidad_solicitada);
                    
                    this.solicitudSeleccionada = solicitud;
                    this.modalAsignacionAbierto = true;
                    this.profesionalSeleccionado = null;
                    this.motivoAsignacion = '';
                    
                    await this.cargarProfesionales(solicitud.servicio_id, solicitud.especialidad_solicitada);
                },

                async cargarProfesionales(servicioId, especialidad) {
                    this.loadingProfesionales = true;
                    try {
                        const token = localStorage.getItem('token');
                        let url = `/api/admin/profesionales?servicio_id=${servicioId}`;
                        if (especialidad) {
                            url += `&especialidad=${encodeURIComponent(especialidad)}`;
                        }
                        
                        console.log('Cargando profesionales:', { url, servicioId, especialidad });
                        
                        const response = await fetch(url, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        
                        console.log('✅ Response recibida');
                        console.log('Response status:', response.status);
                        console.log('Response ok:', response.ok);
                        
                        if (response.ok) {
                            console.log('📥 Parseando JSON...');
                            const data = await response.json();
                            console.log('✅ JSON parseado exitosamente');
                            console.log('Profesionales en data:', data.profesionales?.length || 0);
                            
                            if (data.profesionales && Array.isArray(data.profesionales)) {
                                this.profesionalesDisponibles = data.profesionales;
                                console.log('Profesionales cargados:', this.profesionalesDisponibles.length);
                            } else {
                                console.error('❌ data.profesionales no es un array:', data);
                                this.profesionalesDisponibles = [];
                            }
                        } else {
                            const errorData = await response.json().catch(() => ({ error: 'Error desconocido' }));
                            console.error('❌ Error en respuesta:', errorData);
                            this.profesionalesDisponibles = [];
                        }
                    } catch (error) {
                        console.error('💥 Error CATCH al cargar profesionales:');
                        console.error('Error type:', error.constructor.name);
                        console.error('Error message:', error.message);
                        console.error('Error stack:', error.stack);
                        this.profesionalesDisponibles = [];
                        
                        // Mostrar mensaje de error al usuario
                        if (window.showToast) {
                            window.showToast('Error al cargar profesionales: ' + error.message, 'error');
                        }
                    } finally {
                        this.loadingProfesionales = false;
                    }
                },

                seleccionarProfesional(prof) {
                    this.profesionalSeleccionado = prof;
                },

                async confirmarAsignacion() {
                    if (!this.profesionalSeleccionado || this.asignando) return;
                    
                    this.asignando = true;
                    try {
                        const token = localStorage.getItem('token');
                        const response = await fetch(`/api/admin/solicitudes/${this.solicitudSeleccionada.id}/asignar`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${token}`
                            },
                            body: JSON.stringify({
                                profesional_id: this.profesionalSeleccionado.id,
                                motivo: this.motivoAsignacion
                            })
                        });
                        
                        if (response.ok) {
                            alert('✅ Profesional asignado exitosamente');
                            this.cerrarModalAsignacion();
                            await this.cargarSolicitudes();
                            await this.cargarStats();
                        } else {
                            const error = await response.json();
                            alert('Error: ' + (error.error || 'No se pudo asignar el profesional'));
                        }
                    } catch (error) {
                        console.error('Error al asignar profesional:', error);
                        alert('Error al procesar la asignación');
                    } finally {
                        this.asignando = false;
                    }
                },

                cerrarModalAsignacion() {
                    this.modalAsignacionAbierto = false;
                    this.solicitudSeleccionada = null;
                    this.profesionalSeleccionado = null;
                    this.profesionalesDisponibles = [];
                    this.motivoAsignacion = '';
                },

                // Funciones para Reportes
                async cargarReportes() {
                    this.loadingReportes = true;
                    try {
                        const token = localStorage.getItem('token');
                        let url = '/api/admin/reportes?';
                        
                        if (this.filtrosReportes.fecha_desde) {
                            url += `fecha_desde=${this.filtrosReportes.fecha_desde}&`;
                        }
                        if (this.filtrosReportes.fecha_hasta) {
                            url += `fecha_hasta=${this.filtrosReportes.fecha_hasta}&`;
                        }
                        if (this.filtrosReportes.calificado !== '') {
                            url += `calificado=${this.filtrosReportes.calificado}&`;
                        }
                        
                        const response = await fetch(url, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.reportes = data.reportes || [];
                            this.estadisticasReportes = data.estadisticas || null;
                            console.log('Reportes cargados:', this.reportes.length);
                        } else {
                            console.error('Error al cargar reportes');
                            this.showToast('Error al cargar reportes', 'error');
                        }
                    } catch (error) {
                        console.error('Error al cargar reportes:', error);
                        this.showToast('Error de conexión al cargar reportes', 'error');
                    } finally {
                        this.loadingReportes = false;
                    }
                },

                async verDetalleReporte(solicitudId) {
                    try {
                        const token = localStorage.getItem('token');
                        const response = await fetch(`/api/admin/reportes/${solicitudId}`, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.reporteDetalle = data.reporte;
                            this.modalReporteAbierto = true;
                            console.log('Detalle de reporte:', this.reporteDetalle);
                        } else {
                            this.showToast('Error al cargar el reporte', 'error');
                        }
                    } catch (error) {
                        console.error('Error al cargar detalle del reporte:', error);
                        this.showToast('Error de conexión', 'error');
                    }
                },

                cerrarModalReporte() {
                    this.modalReporteAbierto = false;
                    this.reporteDetalle = {
                        solicitud_id: 0,
                        paciente: { nombre: '', email: '', telefono: '' },
                        profesional: { nombre: '', especialidad: '', puntuacion_promedio: 0, total_calificaciones: 0, servicios_completados: 0 },
                        servicio: { nombre: '', modalidad: '' },
                        fecha_programada: '',
                        fecha_completada: '',
                        reporte_profesional: '',
                        diagnostico: '',
                        notas_adicionales: '',
                        calificacion: { calificado: false },
                        finanzas: { monto_total: 0, monto_profesional: 0, monto_plataforma: 0, pagado: false }
                    };
                },

                // ============================================
                // FUNCIONES DE GESTIÓN DE PROFESIONALES
                // ============================================

                async cargarListaProfesionales() {
                    this.loadingProfesionales = true;
                    try {
                        const token = localStorage.getItem('token');
                        let url = '/api/admin/profesionales?';
                        
                        if (this.filtrosProfesionales.busqueda) {
                            url += `busqueda=${encodeURIComponent(this.filtrosProfesionales.busqueda)}&`;
                        }
                        if (this.filtrosProfesionales.tipo) {
                            url += `tipo=${this.filtrosProfesionales.tipo}&`;
                        }
                        if (this.filtrosProfesionales.estado) {
                            url += `estado=${this.filtrosProfesionales.estado}&`;
                        }
                        if (this.filtrosProfesionales.especialidad) {
                            url += `especialidad=${encodeURIComponent(this.filtrosProfesionales.especialidad)}&`;
                        }
                        
                        const response = await fetch(url, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.profesionales = data.profesionales || [];
                            console.log('Profesionales cargados:', this.profesionales.length);
                        } else {
                            this.showToast('Error al cargar profesionales', 'error');
                        }
                    } catch (error) {
                        console.error('Error al cargar profesionales:', error);
                        this.showToast('Error de conexión', 'error');
                    } finally {
                        this.loadingProfesionales = false;
                    }
                },

                abrirModalNuevoProfesional() {
                    this.profesionalEditando = null;
                    this.formProfesional = {
                        nombre: '',
                        apellido: '',
                        email: '',
                        password: '',
                        tipo_profesional: '',
                        profesion: '',
                        especialidad: '',
                        telefono_whatsapp: '',
                        telefono: '',
                        direccion: '',
                        direccion_consultorio: '',
                        hoja_vida_url: '',
                        estado: 'activo'
                    };
                    this.modalProfesionalAbierto = true;
                },

                editarProfesional(profesional) {
                    this.profesionalEditando = profesional;
                    this.formProfesional = {
                        nombre: profesional.nombre,
                        apellido: profesional.apellido,
                        email: profesional.email,
                        password: '',
                        tipo_profesional: profesional.tipo_profesional,
                        profesion: profesional.profesion || '',
                        especialidad: profesional.especialidad || '',
                        telefono_whatsapp: profesional.telefono_whatsapp || '',
                        telefono: profesional.telefono || '',
                        direccion: profesional.direccion || '',
                        direccion_consultorio: profesional.direccion_consultorio || '',
                        hoja_vida_url: profesional.hoja_vida_url || '',
                        estado: profesional.estado
                    };
                    this.modalProfesionalAbierto = true;
                },

                async guardarProfesional() {
                    this.guardandoProfesional = true;
                    try {
                        const token = localStorage.getItem('token');
                        const url = this.profesionalEditando 
                            ? `/api/admin/profesionales/${this.profesionalEditando.id}`
                            : '/api/admin/profesionales';
                        
                        const method = this.profesionalEditando ? 'PUT' : 'POST';
                        
                        // Preparar datos
                        const data = { ...this.formProfesional };
                        if (this.profesionalEditando && !data.password) {
                            delete data.password; // No actualizar contraseña si está vacía
                        }
                        
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${token}`
                            },
                            body: JSON.stringify(data)
                        });
                        
                        if (response.ok) {
                            const result = await response.json();
                            this.showToast(result.message || 'Profesional guardado exitosamente', 'success');
                            this.cerrarModalProfesional();
                            await this.cargarListaProfesionales();
                        } else {
                            const error = await response.json();
                            this.showToast(error.error || 'Error al guardar profesional', 'error');
                        }
                    } catch (error) {
                        console.error('Error al guardar profesional:', error);
                        this.showToast('Error de conexión', 'error');
                    } finally {
                        this.guardandoProfesional = false;
                    }
                },

                async cambiarEstadoProfesional(profesional) {
                    const nuevoEstado = profesional.estado === 'activo' ? 'inactivo' : 'activo';
                    const confirmar = confirm(`¿Estás seguro de ${nuevoEstado === 'activo' ? 'activar' : 'desactivar'} a ${profesional.nombre} ${profesional.apellido}?`);
                    
                    if (!confirmar) return;
                    
                    try {
                        const token = localStorage.getItem('token');
                        const response = await fetch(`/api/admin/profesionales/${profesional.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${token}`
                            },
                            body: JSON.stringify({ estado: nuevoEstado })
                        });
                        
                        if (response.ok) {
                            this.showToast(`Profesional ${nuevoEstado === 'activo' ? 'activado' : 'desactivado'} exitosamente`, 'success');
                            await this.cargarListaProfesionales();
                        } else {
                            this.showToast('Error al cambiar estado', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showToast('Error de conexión', 'error');
                    }
                },

                manejarArchivoHojaVida(event) {
                    const file = event.target.files[0];
                    if (file) {
                        if (file.type !== 'application/pdf') {
                            this.showToast('Solo se permiten archivos PDF', 'error');
                            event.target.value = '';
                            return;
                        }
                        if (file.size > 5 * 1024 * 1024) {
                            this.showToast('El archivo no puede superar 5MB', 'error');
                            event.target.value = '';
                            return;
                        }
                        
                        // Aquí deberías implementar la carga al servidor
                        // Por ahora, solo guardamos el nombre
                        this.formProfesional.hoja_vida_url = file.name;
                        this.showToast('Archivo listo para subir', 'info');
                    }
                },

                cerrarModalProfesional() {
                    this.modalProfesionalAbierto = false;
                    this.profesionalEditando = null;
                },

                // FUNCIONES DE KANBAN Y ESPECIALIDADES
                // ============================================

                async cargarEspecialidades() {
                    try {
                        const response = await fetch('/api/admin/especialidades');
                        if (response.ok) {
                            const data = await response.json();
                            this.especialidades = data.data || [];
                            console.log('Especialidades cargadas:', this.especialidades.length);
                        }
                    } catch (error) {
                        console.error('Error al cargar especialidades:', error);
                    }
                },

                async iniciarKanban() {
                    // Esperar a que el DOM se actualice
                    await this.$nextTick();
                    
                    // Cargar profesionales y especialidades si no están cargados
                    if (this.profesionales.length === 0) {
                        await this.cargarListaProfesionales();
                    }
                    if (this.especialidades.length === 0) {
                        await this.cargarEspecialidades();
                    }
                    
                    // Inicializar el Kanban si existe
                    if (typeof KanbanBoard !== 'undefined') {
                        if (!this.kanbanBoard) {
                            this.kanbanBoard = new KanbanBoard();
                            await this.kanbanBoard.init();
                        } else {
                            await this.kanbanBoard.cargarSolicitudes();
                        }
                    } else {
                        console.error('KanbanBoard no está cargado');
                        this.showToast('Error al cargar vista Kanban', 'error');
                    }
                }
            }
        }
    </script>

    <!-- Toast Notifications -->
    <script src="/js/toast.js"></script>
    
    <!-- Script del Kanban Board -->
    <script src="/js/kanban-board.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
