<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - Especialistas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/js/toast.js"></script>
    <link rel="stylesheet" href="/css/skeleton.css">
    <link rel="stylesheet" href="/css/timeline.css">
    <link rel="stylesheet" href="/css/breadcrumbs.css">
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
        
        // Filtros y búsqueda
        searchQuery: '',
        filterEstado: '',
        filterServicio: '',
        
        // Paginación
        itemsPorPagina: 5,
        paginaActual: 1,
        
        // Modal detalle con timeline
        modalDetalleAbierto: false,
        solicitudDetalle: null,
        
        // Modal de calificación
        modalCalificacionAbierto: false,
        solicitudACalificar: null,
        calificacion: 0,
        comentario: '',
        enviandoCalificacion: false,

        async init() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '/login';
                return;
            }

            const userData = JSON.parse(localStorage.getItem('usuario') || '{}');
            this.usuario = userData;

            await this.cargarDatos();
            
            // Verificar si hay servicios pendientes de calificar
            this.verificarCalificacionesPendientes();
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
            const solicitud = this.solicitudes.find(s => s.id === id);
            if (solicitud) {
                this.solicitudDetalle = solicitud;
                this.modalDetalleAbierto = true;
            }
        },

        cerrarModalDetalle() {
            this.modalDetalleAbierto = false;
            this.solicitudDetalle = null;
        },

        getTimelineStates(estado) {
            const estados = {
                'pendiente': ['completed', 'pending', 'pending', 'pending', 'pending'],
                'asignado': ['completed', 'completed', 'pending', 'pending', 'pending'],
                'confirmado': ['completed', 'completed', 'completed', 'pending', 'pending'],
                'en_progreso': ['completed', 'completed', 'completed', 'active', 'pending'],
                'completado': ['completed', 'completed', 'completed', 'completed', 'completed'],
                'pendiente_calificacion': ['completed', 'completed', 'completed', 'completed', 'completed'],
                'rechazado': ['completed', 'rejected', 'rejected', 'rejected', 'rejected']
            };
            return estados[estado] || ['pending', 'pending', 'pending', 'pending', 'pending'];
        },

        verDetalle_old(id) {
            window.location.href = `/paciente/solicitud/${id}`;
        },

        getEstadoColor(estado) {
            const colores = {
                'pendiente': 'bg-yellow-100 text-yellow-800',
                'pendiente_asignacion': 'bg-orange-100 text-orange-800',
                'confirmada': 'bg-blue-100 text-blue-800',
                'en_progreso': 'bg-indigo-100 text-indigo-800',
                'completada': 'bg-green-100 text-green-800',
                'pendiente_calificacion': 'bg-purple-100 text-purple-800',
                'finalizada': 'bg-gray-100 text-gray-800',
                'cancelada': 'bg-red-100 text-red-800'
            };
            return colores[estado] || 'bg-gray-100 text-gray-800';
        },
        
        getEstadoTexto(estado) {
            const textos = {
                'pendiente': 'Pendiente',
                'pendiente_asignacion': 'Esperando Asignación',
                'confirmada': 'Confirmada',
                'en_progreso': 'En Progreso',
                'completada': 'Completada',
                'pendiente_calificacion': 'Califica el Servicio',
                'finalizada': 'Finalizada',
                'cancelada': 'Cancelada'
            };
            return textos[estado] || estado;
        },

        verificarCalificacionesPendientes() {
            const pendientes = this.solicitudes.filter(s => s.estado === 'pendiente_calificacion');
            if (pendientes.length > 0) {
                setTimeout(() => {
                    ToastNotification.warning(
                        `Tienes ${pendientes.length} servicio(s) pendiente(s) de calificar. Haz clic en el botón "⭐ Calificar Servicio" para continuar.`,
                        8000
                    );
                }, 1000);
            }
        },

        abrirModalCalificacion(solicitud) {
            this.solicitudACalificar = solicitud;
            this.calificacion = 0;
            this.comentario = '';
            this.modalCalificacionAbierto = true;
        },

        cerrarModalCalificacion() {
            this.modalCalificacionAbierto = false;
            this.solicitudACalificar = null;
            this.calificacion = 0;
            this.comentario = '';
        },

        seleccionarCalificacion(valor) {
            this.calificacion = valor;
        },

        async enviarCalificacion() {
            if (this.calificacion < 1 || this.calificacion > 5) {
                ToastNotification.warning('Por favor selecciona una calificación del 1 al 5');
                return;
            }

            this.enviandoCalificacion = true;
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`/api/paciente/calificar/${this.solicitudACalificar.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({
                        calificacion: this.calificacion,
                        comentario: this.comentario
                    })
                });

                if (response.ok) {
                    ToastNotification.success('¡Gracias por tu calificación! Tu opinión nos ayuda a mejorar.');
                    this.cerrarModalCalificacion();
                    await this.cargarDatos();
                } else {
                    const error = await response.json();
                    ToastNotification.error(error.message || 'No se pudo enviar la calificación');
                }
            } catch (error) {
                console.error('Error:', error);
                ToastNotification.error('Error al enviar la calificación. Verifica tu conexión.');
            } finally {
                this.enviandoCalificacion = false;
            }
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

        get solicitudesFiltradas() {
            let filtered = this.solicitudes;
            
            // Filtrar por búsqueda
            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(s => 
                    s.servicio?.toLowerCase().includes(query) ||
                    s.descripcion?.toLowerCase().includes(query) ||
                    s.profesional_nombre?.toLowerCase().includes(query) ||
                    s.id?.toString().includes(query)
                );
            }
            
            // Filtrar por estado
            if (this.filterEstado) {
                filtered = filtered.filter(s => s.estado === this.filterEstado);
            }
            
            // Filtrar por servicio
            if (this.filterServicio) {
                filtered = filtered.filter(s => s.servicio_tipo === this.filterServicio);
            }
            
            return filtered;
        },

        limpiarFiltros() {
            this.searchQuery = '';
            this.filterEstado = '';
            this.filterServicio = '';
            this.paginaActual = 1;
        },

        get solicitudesPaginadas() {
            const inicio = (this.paginaActual - 1) * this.itemsPorPagina;
            const fin = inicio + this.itemsPorPagina;
            return this.solicitudesFiltradas.slice(inicio, fin);
        },

        get totalPaginas() {
            return Math.ceil(this.solicitudesFiltradas.length / this.itemsPorPagina);
        },

        cambiarPagina(pagina) {
            if (pagina >= 1 && pagina <= this.totalPaginas) {
                this.paginaActual = pagina;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        get paginasVisibles() {
            const paginas = [];
            const total = this.totalPaginas;
            const actual = this.paginaActual;
            
            if (total <= 7) {
                for (let i = 1; i <= total; i++) paginas.push(i);
            } else {
                if (actual <= 4) {
                    for (let i = 1; i <= 5; i++) paginas.push(i);
                    paginas.push('...');
                    paginas.push(total);
                } else if (actual >= total - 3) {
                    paginas.push(1);
                    paginas.push('...');
                    for (let i = total - 4; i <= total; i++) paginas.push(i);
                } else {
                    paginas.push(1);
                    paginas.push('...');
                    for (let i = actual - 1; i <= actual + 1; i++) paginas.push(i);
                    paginas.push('...');
                    paginas.push(total);
                }
            }
            return paginas;
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
                    <button @click="window.location.href='/paciente/dashboard'" class="text-gray-600 hover:text-indigo-600 transition" title="Inicio">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </button>
                    <button @click="logout()" class="text-gray-600 hover:text-red-600 transition" title="Cerrar sesión">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumbs -->
        <nav class="breadcrumb mb-6">
            <div class="breadcrumb-item">
                <svg class="breadcrumb-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
                <a href="/paciente/dashboard">Inicio</a>
            </div>
            <span class="breadcrumb-separator">/</span>
            <div class="breadcrumb-item active">Mi Panel</div>
        </nav>

        <!-- Skeletons para estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" x-show="loading">
            <div class="skeleton-stat-card"></div>
            <div class="skeleton-stat-card"></div>
            <div class="skeleton-stat-card"></div>
        </div>

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

        <!-- Barra de búsqueda y filtros -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6" x-show="!loading">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Búsqueda -->
                <div class="md:col-span-2">
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="searchQuery" 
                            placeholder="Buscar por servicio, descripción, profesional..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                        >
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Filtro Estado -->
                <div>
                    <select x-model="filterEstado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="asignado">Asignado</option>
                        <option value="confirmado">Confirmado</option>
                        <option value="en_progreso">En progreso</option>
                        <option value="completado">Completado</option>
                        <option value="rechazado">Rechazado</option>
                    </select>
                </div>

                <!-- Filtro Servicio -->
                <div>
                    <select x-model="filterServicio" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">Todos los servicios</option>
                        <option value="medico">Médico</option>
                        <option value="enfermera">Enfermera</option>
                        <option value="veterinario">Veterinario</option>
                        <option value="laboratorio">Laboratorio</option>
                        <option value="ambulancia">Ambulancia</option>
                    </select>
                </div>
            </div>

            <!-- Botón limpiar filtros -->
            <div class="mt-3 flex justify-between items-center" x-show="searchQuery || filterEstado || filterServicio">
                <p class="text-sm text-gray-600">
                    <span x-text="solicitudesFiltradas.length"></span> resultados encontrados
                </p>
                <button 
                    @click="limpiarFiltros()" 
                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center space-x-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>Limpiar filtros</span>
                </button>
            </div>
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

        <!-- Skeleton para lista de solicitudes -->
        <div x-show="loading" class="space-y-4">
            <div class="skeleton-list-item"></div>
            <div class="skeleton-list-item"></div>
            <div class="skeleton-list-item"></div>
            <div class="skeleton-list-item"></div>
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
                    <template x-for="solicitud in solicitudesPaginadas" :key="solicitud.id">
                        <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition cursor-pointer" @click="verDetalle(solicitud.id)">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900" x-text="solicitud.servicio_nombre"></h3>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium" :class="getEstadoColor(solicitud.estado)" x-text="getEstadoTexto(solicitud.estado)"></span>
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
                                
                                <!-- Botón Calificar -->
                                <div x-show="solicitud.estado === 'pendiente_calificacion'" class="mt-3">
                                    <button @click="abrirModalCalificacion(solicitud)" 
                                            class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium text-sm">
                                        ⭐ Calificar Servicio
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Paginación -->
                    <div x-show="totalPaginas > 1" class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
                        <div class="text-sm text-gray-600">
                            Mostrando 
                            <span x-text="((paginaActual - 1) * itemsPorPagina) + 1"></span>
                            a
                            <span x-text="Math.min(paginaActual * itemsPorPagina, solicitudesFiltradas.length)"></span>
                            de
                            <span x-text="solicitudesFiltradas.length"></span>
                            resultados
                        </div>

                        <div class="flex items-center space-x-2">
                            <button 
                                @click="cambiarPagina(paginaActual - 1)"
                                :disabled="paginaActual === 1"
                                :class="paginaActual === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 transition"
                            >
                                ← Anterior
                            </button>

                            <template x-for="pagina in paginasVisibles" :key="pagina">
                                <button 
                                    x-show="pagina !== '...'"
                                    @click="cambiarPagina(pagina)"
                                    :class="paginaActual === pagina ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium transition"
                                    x-text="pagina"
                                ></button>
                                <span x-show="pagina === '...'" class="px-2 text-gray-500">...</span>
                            </template>

                            <button 
                                @click="cambiarPagina(paginaActual + 1)"
                                :disabled="paginaActual === totalPaginas"
                                :class="paginaActual === totalPaginas ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 transition"
                            >
                                Siguiente →
                            </button>
                        </div>
                    </div>
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

    <!-- Modal de Calificación -->
    <div x-show="modalCalificacionAbierto" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div @click="cerrarModalCalificacion()" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <div class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">
                        ⭐ Califica el Servicio
                    </h3>
                    <button @click="cerrarModalCalificacion()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Información del Servicio -->
                <div x-show="solicitudACalificar" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-600 mb-1">Servicio</p>
                    <p class="font-semibold text-gray-900" x-text="solicitudACalificar?.servicio_nombre"></p>
                    <p class="text-sm text-gray-600 mt-2">Profesional</p>
                    <p class="font-medium text-gray-800" x-text="solicitudACalificar?.profesional_nombre"></p>
                </div>

                <!-- Sistema de Estrellas -->
                <div class="mb-6">
                    <p class="text-sm font-medium text-gray-700 mb-3">¿Cómo calificarías este servicio?</p>
                    <div class="flex justify-center space-x-2">
                        <template x-for="i in 5" :key="i">
                            <button @click="seleccionarCalificacion(i)" 
                                    type="button"
                                    class="transition-transform hover:scale-110 focus:outline-none">
                                <svg :class="i <= calificacion ? 'text-yellow-400' : 'text-gray-300'" 
                                     class="w-12 h-12" 
                                     fill="currentColor" 
                                     viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </button>
                        </template>
                    </div>
                    <p class="text-center text-sm text-gray-600 mt-2" x-show="calificacion > 0">
                        <span x-show="calificacion === 1">😞 Muy malo</span>
                        <span x-show="calificacion === 2">😕 Malo</span>
                        <span x-show="calificacion === 3">😐 Regular</span>
                        <span x-show="calificacion === 4">😊 Bueno</span>
                        <span x-show="calificacion === 5">😍 Excelente</span>
                    </p>
                </div>

                <!-- Comentario -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Cuéntanos más sobre tu experiencia (opcional)
                    </label>
                    <textarea x-model="comentario" 
                              rows="4" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                              placeholder="Describe qué te gustó o qué se podría mejorar..."></textarea>
                </div>

                <!-- Botones -->
                <div class="flex justify-end space-x-3">
                    <button @click="cerrarModalCalificacion()" 
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium">
                        Cancelar
                    </button>
                    <button @click="enviarCalificacion()" 
                            :disabled="calificacion === 0 || enviandoCalificacion"
                            :class="calificacion === 0 || enviandoCalificacion ? 'opacity-50 cursor-not-allowed' : 'hover:bg-purple-700'"
                            class="px-6 py-2 bg-purple-600 text-white rounded-lg transition font-medium">
                        <span x-show="!enviandoCalificacion">✅ Enviar Calificación</span>
                        <span x-show="enviandoCalificacion">Enviando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle con Timeline -->
    <div x-show="modalDetalleAbierto" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
         @click.self="cerrarModalDetalle()">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900">Detalle de la Solicitud</h3>
                <button @click="cerrarModalDetalle()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6" x-show="solicitudDetalle">
                <!-- Información básica -->
                <div class="mb-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h4 class="text-2xl font-bold text-gray-900" x-text="solicitudDetalle?.servicio_nombre"></h4>
                            <p class="text-sm text-gray-500 mt-1">Solicitud #<span x-text="solicitudDetalle?.id"></span></p>
                        </div>
                        <span :class="getEstadoBadgeClass(solicitudDetalle?.estado)" 
                              class="px-3 py-1 rounded-full text-xs font-semibold"
                              x-text="getEstadoTexto(solicitudDetalle?.estado)">
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Fecha programada</p>
                            <p class="font-medium" x-text="formatDate(solicitudDetalle?.fecha_programada)"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Modalidad</p>
                            <p class="font-medium capitalize" x-text="solicitudDetalle?.modalidad"></p>
                        </div>
                        <div x-show="solicitudDetalle?.profesional_nombre">
                            <p class="text-gray-500">Profesional</p>
                            <p class="font-medium" x-text="solicitudDetalle?.profesional_nombre"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Monto</p>
                            <p class="font-medium text-indigo-600" x-text="formatMonto(solicitudDetalle?.monto_total)"></p>
                        </div>
                    </div>

                    <div class="mt-4" x-show="solicitudDetalle?.descripcion">
                        <p class="text-gray-500 text-sm mb-1">Descripción</p>
                        <p class="text-gray-700" x-text="solicitudDetalle?.descripcion"></p>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="mb-6">
                    <h5 class="font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Historial del Servicio
                    </h5>

                    <div class="timeline-container">
                        <template x-data="{ states: getTimelineStates(solicitudDetalle?.estado) }">
                            <!-- Solicitud Creada -->
                            <div class="timeline-step" :class="states[0]">
                                <div class="timeline-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Solicitud Creada</div>
                                    <div class="timeline-description">Tu solicitud ha sido registrada</div>
                                    <div class="timeline-timestamp">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span x-text="formatDate(solicitudDetalle?.created_at)"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Profesional Asignado -->
                            <div class="timeline-step" :class="states[1]">
                                <div class="timeline-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Profesional Asignado</div>
                                    <div class="timeline-description" x-show="solicitudDetalle?.profesional_nombre">
                                        Asignado a <span class="font-medium" x-text="solicitudDetalle?.profesional_nombre"></span>
                                    </div>
                                    <div class="timeline-description" x-show="!solicitudDetalle?.profesional_nombre">
                                        En espera de asignación
                                    </div>
                                </div>
                            </div>

                            <!-- Servicio Confirmado -->
                            <div class="timeline-step" :class="states[2]">
                                <div class="timeline-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Servicio Confirmado</div>
                                    <div class="timeline-description">El profesional confirmó la cita</div>
                                </div>
                            </div>

                            <!-- En Progreso -->
                            <div class="timeline-step" :class="states[3]">
                                <div class="timeline-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Servicio en Progreso</div>
                                    <div class="timeline-description">El servicio está siendo realizado</div>
                                </div>
                            </div>

                            <!-- Completado -->
                            <div class="timeline-step" :class="states[4]">
                                <div class="timeline-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Servicio Completado</div>
                                    <div class="timeline-description">El servicio fue finalizado exitosamente</div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Botón calificar si está pendiente -->
                <div x-show="solicitudDetalle?.estado === 'pendiente_calificacion'" class="text-center">
                    <button @click="abrirModalCalificacion(solicitudDetalle); cerrarModalDetalle();" 
                            class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium">
                        ⭐ Calificar Servicio
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
