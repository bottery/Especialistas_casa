<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Especialistas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50" x-data="adminDashboard()">
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
                                class="relative p-2 text-gray-600 hover:text-gray-900 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span x-show="stats.pendientes_asignacion > 0" 
                                  class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
                                  x-text="stats.pendientes_asignacion"></span>
                        </button>
                    </div>
                    <span class="text-gray-700 font-medium"><?= htmlspecialchars($_SESSION['user']->nombre ?? '') ?></span>
                    <a href="/logout" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Estadísticas -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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

        <!-- Tabla de Solicitudes Pendientes -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">📋 Solicitudes Pendientes de Asignación</h2>
                <button @click="cargarSolicitudes()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                    🔄 Actualizar
                </button>
            </div>

            <!-- Loading -->
            <div x-show="loading" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
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
                        🎯 Asignar Profesional
                    </h3>
                    <button @click="cerrarModalAsignacion()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Información de la Solicitud -->
                <div x-show="solicitudSeleccionada" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Paciente</p>
                            <p class="font-semibold text-gray-900" x-text="solicitudSeleccionada?.paciente_nombre + ' ' + solicitudSeleccionada?.paciente_apellido"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Servicio</p>
                            <p class="font-semibold text-gray-900" x-text="solicitudSeleccionada?.servicio_nombre"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Especialidad</p>
                            <p class="font-semibold text-gray-900" x-text="solicitudSeleccionada?.especialidad || 'No especificada'"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Fecha</p>
                            <p class="font-semibold text-gray-900" x-text="solicitudSeleccionada?.fecha_programada"></p>
                        </div>
                    </div>
                </div>

                <!-- Lista de Profesionales -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Profesionales Disponibles</h4>
                    
                    <div x-show="loadingProfesionales" class="flex justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>

                    <div x-show="!loadingProfesionales" class="max-h-96 overflow-y-auto space-y-3">
                        <template x-if="profesionalesDisponibles.length === 0">
                            <p class="text-center text-gray-500 py-8">No hay profesionales disponibles para este servicio</p>
                        </template>

                        <template x-for="prof in profesionalesDisponibles" :key="prof.id">
                            <div @click="seleccionarProfesional(prof)" 
                                 :class="profesionalSeleccionado?.id === prof.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'"
                                 class="border-2 rounded-lg p-4 cursor-pointer transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg">
                                                    <span x-text="prof.nombre[0] + prof.apellido[0]"></span>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <h5 class="text-lg font-semibold text-gray-900" x-text="prof.nombre + ' ' + prof.apellido"></h5>
                                                <p class="text-sm text-gray-600" x-text="prof.servicios"></p>
                                            </div>
                                        </div>
                                        <div class="mt-3 grid grid-cols-3 gap-4">
                                            <div class="bg-yellow-50 rounded-lg p-2 text-center">
                                                <div class="flex items-center justify-center space-x-1">
                                                    <span class="text-yellow-500">⭐</span>
                                                    <span class="text-lg font-bold text-gray-900" x-text="parseFloat(prof.puntuacion_promedio).toFixed(1)"></span>
                                                </div>
                                                <p class="text-xs text-gray-600 mt-1" x-text="prof.total_calificaciones + ' reseñas'"></p>
                                            </div>
                                            <div class="bg-green-50 rounded-lg p-2 text-center">
                                                <p class="text-lg font-bold text-gray-900" x-text="prof.servicios_completados"></p>
                                                <p class="text-xs text-gray-600 mt-1">Servicios</p>
                                            </div>
                                            <div class="bg-blue-50 rounded-lg p-2 text-center">
                                                <p class="text-lg font-bold text-gray-900" x-text="prof.experiencia_anos + ' años'"></p>
                                                <p class="text-xs text-gray-600 mt-1">Experiencia</p>
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
                loading: false,
                notificacionesAbiertas: false,
                
                // Modal de asignación
                modalAsignacionAbierto: false,
                solicitudSeleccionada: null,
                profesionalesDisponibles: [],
                loadingProfesionales: false,
                profesionalSeleccionado: null,
                motivoAsignacion: '',
                asignando: false,

                async init() {
                    await this.cargarStats();
                    await this.cargarSolicitudes();
                    
                    // Auto-refresh cada 30 segundos
                    setInterval(() => {
                        this.cargarStats();
                        this.cargarSolicitudes();
                    }, 30000);
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
                        }
                    } catch (error) {
                        console.error('Error al cargar estadísticas:', error);
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
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.solicitudes = data.solicitudes || [];
                        }
                    } catch (error) {
                        console.error('Error al cargar solicitudes:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async abrirModalAsignacion(solicitud) {
                    this.solicitudSeleccionada = solicitud;
                    this.modalAsignacionAbierto = true;
                    this.profesionalSeleccionado = null;
                    this.motivoAsignacion = '';
                    
                    await this.cargarProfesionales(solicitud.servicio_id, solicitud.especialidad);
                },

                async cargarProfesionales(servicioId, especialidad) {
                    this.loadingProfesionales = true;
                    try {
                        const token = localStorage.getItem('token');
                        let url = `/api/admin/profesionales?servicio_id=${servicioId}`;
                        if (especialidad) {
                            url += `&especialidad=${encodeURIComponent(especialidad)}`;
                        }
                        
                        const response = await fetch(url, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.profesionalesDisponibles = data.profesionales || [];
                        }
                    } catch (error) {
                        console.error('Error al cargar profesionales:', error);
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
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
