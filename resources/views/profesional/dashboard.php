<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Profesional - Especialistas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/skeleton.css">
    <link rel="stylesheet" href="/css/breadcrumbs.css">
    <script src="/js/confirmation-modal.js"></script>
<script>
window.profesionalDashboard = function() {
    return {
        loading: false,
        usuario: {},
        stats: {
            solicitudesPendientes: 0,
            solicitudesEnProgreso: 0,
            solicitudesCompletadas: 0,
            ingresosDelMes: 0
        },
        solicitudes: [],
        solicitudesPendientes: [],
        solicitudesActivas: [],
        solicitudesCompletadas: [],
        activeTab: 'pendientes',
        notificaciones: 0,
        audio: null,
        
        // Filtros y búsqueda
        searchQuery: '',
        filterModalidad: '',
        filterFecha: '',
        
        // Paginación
        itemsPorPagina: 5,
        paginaActual: 1,

        async init() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '/login';
                return;
            }

            const userData = JSON.parse(localStorage.getItem('usuario') || '{}');
            this.usuario = userData;

            // Inicializar audio para notificaciones
            this.audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZSA0PWqnp8K1aFwlCouH1xXEpBiuAzvLZijoIGGS57OihUBALTqPi8bllHAU2jdXzzn0vBSh7yvHajjwJElyx6e6mUhENS6Lf9MJsJgUuhM/z1YY2Bhxqvu7mnEkODlag6PCsVhYKQ5/h88dyKgYrgM3y2Ik4CBdju+zjnlARDU+k4/K4Yx0FN43V88x8LgUme8rx2o48CRFbr+vuplIRDUuh3/PDbCYELoTP89WGNgYcab7v5pxJDg1WoOjwrFYWCkOf4fPHcioGK4HN8tiIOQcXY7vs455PEA1PpOPyuGMeBTaN1fPMfC4FJnvK8dqOPAkRW6/r7qZSEQ1Lod/zw2wmBC6Ez/PVhjYGHGm+7+acSQ4NVqDo8KxWFgpDn+Hzx3IqBiuBzfLYiDkHF2O77OOeUBANT6Tj8rhjHgU2jdXzzHwuBSZ7yvHajjwJEVuv6+6mUhENS6Hf88NsJgQuhM/z1YY2Bhxpvu/mnEkODVag6PCsVhYKQ5/h88dyKgYrgc3y2Ig5BxdjveznmVEPDU+k4/K4Yx4FNo3V88x8LgUme8rx2o48CRFbr+vuplIRDUuh3/PDbCYELoTP89WGNgYcab7v5pxJDg1WoOjwrFYWCkOf4fPHcioGK4HN8tiIOQcXY7vs455PEA1PpOPyuGMeBTaN1fPMfC4FJnvK8dqOPAkRW6/r7qZSEQ1Lod/zw2wmBC6Ez/PVhjYGHGm+7+acSQ4NVqDo8KxWFgpDn+Hzx3IqBiuBzfLYiDkHF2O77OOeUBANT6Tj8rhjHgU2jdXzzHwuBSZ7yvHajjwJEVuv6+6mUhENS6Hf88NsJgQuhM/z1YY2Bhxpvu/mnEkODVag6PCsVhYKQ5/h88dyKgYrgc3y2Ig5BxdjveznmVEPDU+k4/K4Yx4FNo3V88x8LgUme8rx2o48CRFbr+vuplIRDUuh3/PDbCYELoTP89WGNgYcab7v5pxJDg1WoOjwrFYWCkOf4fPHcioGK4HN8tiIOQcXY7vs455PEA1PpOPyuGMeBTaN1fPMfC4FJnvK8dqOPAkRW6/r7qZSEQ1Lod/zw2wmBC6Ez/PVhjYGHGm+7+acSQ4NVqDo8KxWFgpDn+Hzx3IqBiuBzfLYiDkHF2O77OOeUBANT6Tj8rhjHgU2jdXzzHwuBSZ7yvHajjwJEVuv6+6mUhENS6Hf88NsJgQuhM/z1YY2Bhxpvu/mnEkODVag6PCsVhYKQ5/h88dyKgYrgc3y2Ig5BxdjveznmVEPDU+k4/K4Yx4FNo3V88x8LgUme8rx2o48CRFbr+vuplIRDUuh3/PDbCYELoTP89WGNgYcab7v5pxJDg1WoOjwrFYWCkOf4fPHcioGK4HN8tiIOQcXY7vs455PEA1PpOPyuGMeBTaN1fPMfC4FJnvK8dqOPAkRW6/r7qZSEQ1Lod/zw2wmBC6Ez/PVhjYGHGm+7+acSQ==');

            await this.cargarDatos();
            this.iniciarPollingNotificaciones();
        },

        async cargarDatos() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                
                // Cargar estadísticas
                const statsResponse = await fetch('/api/profesional/stats', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                if (statsResponse.ok) {
                    const data = await statsResponse.json();
                    this.stats = data.stats || this.stats;
                }

                // Cargar solicitudes
                const solicitudesResponse = await fetch('/api/profesional/solicitudes', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                if (solicitudesResponse.ok) {
                    const data = await solicitudesResponse.json();
                    this.solicitudes = data.solicitudes || [];
                    this.filtrarSolicitudes();
                    
                    // Actualizar contador de notificaciones
                    this.notificaciones = this.solicitudesPendientes.length;
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        filtrarSolicitudes() {
            this.solicitudesPendientes = this.solicitudes.filter(s => 
                s.estado === 'pendiente' && (s.pagado || s.metodo_pago_preferido === 'pse')
            );
            this.solicitudesActivas = this.solicitudes.filter(s => 
                s.estado === 'confirmada' || s.estado === 'en_progreso'
            );
            this.solicitudesCompletadas = this.solicitudes.filter(s => 
                s.estado === 'completada'
            );
        },

        iniciarPollingNotificaciones() {
            setInterval(async () => {
                const notificacionesAnteriores = this.notificaciones;
                await this.cargarDatos();
                
                // Reproducir sonido si hay nuevas notificaciones
                if (this.notificaciones > notificacionesAnteriores) {
                    this.reproducirNotificacion();
                    this.mostrarNotificacionBrowser();
                }
            }, 30000); // Cada 30 segundos
        },

        reproducirNotificacion() {
            if (this.audio) {
                this.audio.play().catch(e => console.log('Audio bloqueado por el navegador'));
            }
        },

        mostrarNotificacionBrowser() {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('Nueva solicitud', {
                    body: 'Tienes una nueva solicitud de servicio pendiente',
                    icon: '/favicon.ico',
                    badge: '/favicon.ico'
                });
            }
        },

        solicitarPermisosNotificacion() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },

        async aceptarSolicitud(solicitudId) {
            const result = await ConfirmModal.show({
                title: '¿Aceptar solicitud?',
                message: '¿Deseas aceptar esta solicitud y comprometerte a realizarla?',
                confirmText: 'Aceptar',
                cancelText: 'Cancelar',
                type: 'info'
            });
            
            if (!result.confirmed) return;

            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`/api/profesional/solicitudes/${solicitudId}/aceptar`, {
                    method: 'POST',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (response.ok) {
                    alert('✅ Solicitud aceptada exitosamente');
                    await this.cargarDatos();
                } else {
                    alert(data.message || 'Error al aceptar la solicitud');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            } finally {
                this.loading = false;
            }
        },

        async rechazarSolicitud(solicitudId) {
            const result = await ConfirmModal.confirmReject('esta solicitud');
            if (!result.confirmed) return;
            
            const motivo = result.reason;

            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`/api/profesional/solicitudes/${solicitudId}/rechazar`, {
                    method: 'POST',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ motivo })
                });

                const data = await response.json();
                
                if (response.ok) {
                    alert('Solicitud rechazada');
                    await this.cargarDatos();
                } else {
                    alert(data.message || 'Error al rechazar la solicitud');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            } finally {
                this.loading = false;
            }
        },

        async iniciarServicio(solicitudId) {
            const result = await ConfirmModal.show({
                title: '¿Iniciar servicio?',
                message: '¿Confirmas que vas a iniciar este servicio ahora?',
                confirmText: 'Iniciar',
                cancelText: 'Cancelar',
                type: 'info'
            });
            
            if (!result.confirmed) return;

            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`/api/profesional/solicitudes/${solicitudId}/iniciar`, {
                    method: 'POST',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (response.ok) {
                    alert('✅ Servicio iniciado');
                    await this.cargarDatos();
                } else {
                    alert(data.message || 'Error al iniciar el servicio');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            } finally {
                this.loading = false;
            }
        },

        async completarServicio(solicitudId) {
            const notas = prompt('Notas finales del servicio (opcional):');
            if (notas === null) return;

            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`/api/profesional/solicitudes/${solicitudId}/completar`, {
                    method: 'POST',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ notas })
                });

                const data = await response.json();
                
                if (response.ok) {
                    alert('✅ Servicio completado exitosamente');
                    await this.cargarDatos();
                } else {
                    alert(data.message || 'Error al completar el servicio');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            } finally {
                this.loading = false;
            }
        },

        verDetalle(solicitud) {
            alert(`Detalles de la solicitud:\n\nServicio: ${solicitud.servicio_nombre}\nPaciente: ${solicitud.paciente_nombre}\nFecha: ${this.formatDate(solicitud.fecha_programada)}\nMonto: ${this.formatMonto(solicitud.monto_total)}\nSíntomas: ${solicitud.sintomas || 'No especificado'}`);
        },

        get solicitudesPendientesFiltradas() {
            return this.filtrarSolicitudes(this.solicitudesPendientes);
        },

        get solicitudesActivasFiltradas() {
            return this.filtrarSolicitudes(this.solicitudesActivas);
        },

        get solicitudesCompletadasFiltradas() {
            return this.filtrarSolicitudes(this.solicitudesCompletadas);
        },

        filtrarSolicitudes(lista) {
            let filtered = lista;
            
            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(s => 
                    s.paciente_nombre?.toLowerCase().includes(query) ||
                    s.descripcion?.toLowerCase().includes(query) ||
                    s.servicio_nombre?.toLowerCase().includes(query) ||
                    s.id?.toString().includes(query)
                );
            }
            
            if (this.filterModalidad) {
                filtered = filtered.filter(s => s.modalidad === this.filterModalidad);
            }
            
            if (this.filterFecha) {
                filtered = filtered.filter(s => s.fecha_programada?.startsWith(this.filterFecha));
            }
            
            return filtered;
        },

        limpiarFiltros() {
            this.searchQuery = '';
            this.filterModalidad = '';
            this.filterFecha = '';
            this.paginaActual = 1;
        },

        paginatedList(lista) {
            const inicio = (this.paginaActual - 1) * this.itemsPorPagina;
            const fin = inicio + this.itemsPorPagina;
            return lista.slice(inicio, fin);
        },

        get totalPaginas() {
            const lista = this.activeTab === 'pendientes' ? this.solicitudesPendientesFiltradas :
                         this.activeTab === 'activas' ? this.solicitudesActivasFiltradas :
                         this.solicitudesCompletadasFiltradas;
            return Math.ceil(lista.length / this.itemsPorPagina);
        },

        cambiarPagina(pagina) {
            if (pagina >= 1 && pagina <= this.totalPaginas) {
                this.paginaActual = pagina;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        getEstadoColor(estado) {
            const colores = {
                'pendiente': 'bg-yellow-100 text-yellow-800 border-yellow-300',
                'confirmada': 'bg-blue-100 text-blue-800 border-blue-300',
                'en_progreso': 'bg-indigo-100 text-indigo-800 border-indigo-300',
                'completada': 'bg-green-100 text-green-800 border-green-300',
                'rechazada': 'bg-red-100 text-red-800 border-red-300'
            };
            return colores[estado] || 'bg-gray-100 text-gray-800 border-gray-300';
        },

        getEstadoTexto(estado) {
            const textos = {
                'pendiente': '⏳ Pendiente',
                'confirmada': '✅ Confirmada',
                'en_progreso': '🔄 En Progreso',
                'completada': '✔️ Completada',
                'rechazada': '❌ Rechazada'
            };
            return textos[estado] || estado;
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
<body class="bg-gray-50" x-data="profesionalDashboard()" x-init="init(); solicitarPermisosNotificacion()">
    <!-- Header -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3">
                    <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">Dashboard Profesional</h1>
                        <p class="text-xs text-gray-500" x-text="usuario.nombre"></p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Notificaciones -->
                    <div class="relative">
                        <button @click="activeTab = 'pendientes'" class="relative p-2 text-gray-600 hover:text-green-600 transition" title="Notificaciones">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span x-show="notificaciones > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full" x-text="notificaciones"></span>
                        </button>
                    </div>
                    
                    <button @click="window.location.href='/profesional/dashboard'" class="text-gray-600 hover:text-green-600 transition" title="Inicio">
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
                <a href="/profesional/dashboard">Inicio</a>
            </div>
            <span class="breadcrumb-separator">/</span>
            <div class="breadcrumb-item active">Dashboard Profesional</div>
        </nav>

        <!-- Skeletons para estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" x-show="loading">
            <div class="skeleton-stat-card"></div>
            <div class="skeleton-stat-card"></div>
            <div class="skeleton-stat-card"></div>
            <div class="skeleton-stat-card"></div>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" x-show="!loading">
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Pendientes</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" x-text="stats.solicitudesPendientes">0</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">En Progreso</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" x-text="stats.solicitudesEnProgreso">0</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Completadas</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2" x-text="stats.solicitudesCompletadas">0</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Ingresos del Mes</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2" x-text="formatMonto(stats.ingresosDelMes)">$0</p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de búsqueda y filtros -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6" x-show="!loading">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="searchQuery" 
                            placeholder="Buscar por paciente, servicio, descripción..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                        >
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <select x-model="filterModalidad" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm">
                        <option value="">Todas las modalidades</option>
                        <option value="presencial">Presencial</option>
                        <option value="telemedicina">Telemedicina</option>
                    </select>
                </div>

                <div>
                    <input 
                        type="date" 
                        x-model="filterFecha" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm"
                    >
                </div>
            </div>

            <div class="mt-3 flex justify-end" x-show="searchQuery || filterModalidad || filterFecha">
                <button 
                    @click="limpiarFiltros()" 
                    class="text-sm text-green-600 hover:text-green-800 font-medium flex items-center space-x-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>Limpiar filtros</span>
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button @click="activeTab = 'pendientes'" 
                            :class="activeTab === 'pendientes' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="relative py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Pendientes de Aceptar
                        <span x-show="solicitudesPendientes.length > 0" class="ml-2 bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-semibold" x-text="solicitudesPendientes.length"></span>
                    </button>
                    <button @click="activeTab = 'activas'" 
                            :class="activeTab === 'activas' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Activas
                        <span x-show="solicitudesActivas.length > 0" class="ml-2 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold" x-text="solicitudesActivas.length"></span>
                    </button>
                    <button @click="activeTab = 'completadas'" 
                            :class="activeTab === 'completadas' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        Historial
                    </button>
                </nav>
            </div>
        </div>

        <!-- Skeleton para lista de solicitudes -->
        <div x-show="loading" class="space-y-4">
            <div class="skeleton-list-item"></div>
            <div class="skeleton-list-item"></div>
            <div class="skeleton-list-item"></div>
            <div class="skeleton-list-item"></div>
        </div>

        <!-- Solicitudes Pendientes -->
        <div x-show="activeTab === 'pendientes' && !loading">
            <div x-show="solicitudesPendientes.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Sin solicitudes pendientes</h3>
                <p class="mt-1 text-sm text-gray-500">No tienes solicitudes esperando tu aceptación</p>
            </div>

            <div class="grid gap-6">
                <template x-for="solicitud in paginatedList(solicitudesPendientesFiltradas)" :key="solicitud.id">
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900" x-text="solicitud.servicio_nombre"></h3>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full border" 
                                          :class="getEstadoColor(solicitud.estado)"
                                          x-text="getEstadoTexto(solicitud.estado)"></span>
                                </div>
                                <p class="text-sm text-gray-600 mb-1">
                                    <span class="font-medium">Paciente:</span> 
                                    <span x-text="solicitud.paciente_nombre"></span>
                                </p>
                                <p class="text-sm text-gray-600 mb-1">
                                    <span class="font-medium">Fecha:</span> 
                                    <span x-text="formatDate(solicitud.fecha_programada)"></span>
                                </p>
                                <p class="text-sm text-gray-600 mb-1">
                                    <span class="font-medium">Modalidad:</span> 
                                    <span x-text="solicitud.modalidad" class="capitalize"></span>
                                </p>
                                <p class="text-sm text-gray-600 mb-1">
                                    <span class="font-medium">Monto:</span> 
                                    <span class="text-green-600 font-semibold" x-text="formatMonto(solicitud.monto_total)"></span>
                                </p>
                                <p x-show="solicitud.sintomas" class="text-sm text-gray-600 mt-2">
                                    <span class="font-medium">Síntomas:</span> 
                                    <span x-text="solicitud.sintomas"></span>
                                </p>
                                <p x-show="solicitud.direccion_servicio" class="text-sm text-gray-600 mt-1">
                                    <span class="font-medium">📍 Dirección:</span> 
                                    <span x-text="solicitud.direccion_servicio"></span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex gap-3 pt-4 border-t">
                            <button @click="aceptarSolicitud(solicitud.id)" 
                                    class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition font-medium">
                                ✅ Aceptar
                            </button>
                            <button @click="rechazarSolicitud(solicitud.id)" 
                                    class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition font-medium">
                                ❌ Rechazar
                            </button>
                            <button @click="verDetalle(solicitud)" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                👁️ Ver
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Paginación -->
                <div x-show="totalPaginas > 1" class="flex justify-center mt-6 pt-4 border-t border-gray-200">
                    <div class="flex items-center space-x-2">
                        <button 
                            @click="cambiarPagina(paginaActual - 1)"
                            :disabled="paginaActual === 1"
                            :class="paginaActual === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        >
                            ←
                        </button>
                        <span class="text-sm text-gray-600">
                            Página <span x-text="paginaActual"></span> de <span x-text="totalPaginas"></span>
                        </span>
                        <button 
                            @click="cambiarPagina(paginaActual + 1)"
                            :disabled="paginaActual === totalPaginas"
                            :class="paginaActual === totalPaginas ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        >
                            →
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Solicitudes Activas -->
        <div x-show="activeTab === 'activas' && !loading">
            <div x-show="solicitudesActivas.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Sin servicios activos</h3>
                <p class="mt-1 text-sm text-gray-500">No tienes servicios en curso actualmente</p>
            </div>

            <div class="grid gap-6">
                <template x-for="solicitud in paginatedList(solicitudesActivasFiltradas)" :key="solicitud.id">
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900" x-text="solicitud.servicio_nombre"></h3>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full border" 
                                          :class="getEstadoColor(solicitud.estado)"
                                          x-text="getEstadoTexto(solicitud.estado)"></span>
                                </div>
                                <p class="text-sm text-gray-600 mb-1">
                                    <span class="font-medium">Paciente:</span> 
                                    <span x-text="solicitud.paciente_nombre"></span>
                                </p>
                                <p class="text-sm text-gray-600 mb-1">
                                    <span class="font-medium">Fecha:</span> 
                                    <span x-text="formatDate(solicitud.fecha_programada)"></span>
                                </p>
                                <p x-show="solicitud.direccion_servicio" class="text-sm text-gray-600 mt-1">
                                    <span class="font-medium">📍</span> 
                                    <span x-text="solicitud.direccion_servicio"></span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex gap-3 pt-4 border-t">
                            <button x-show="solicitud.estado === 'confirmada'" 
                                    @click="iniciarServicio(solicitud.id)" 
                                    class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-medium">
                                ▶️ Iniciar Servicio
                            </button>
                            <button x-show="solicitud.estado === 'en_progreso'" 
                                    @click="completarServicio(solicitud.id)" 
                                    class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition font-medium">
                                ✔️ Completar
                            </button>
                            <button @click="verDetalle(solicitud)" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                👁️ Ver
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Historial -->
        <div x-show="activeTab === 'completadas' && !loading">
            <div x-show="solicitudesCompletadas.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Sin historial</h3>
                <p class="mt-1 text-sm text-gray-500">Aún no has completado ningún servicio</p>
            </div>

            <div class="grid gap-4">
                <template x-for="solicitud in paginatedList(solicitudesCompletadasFiltradas)" :key="solicitud.id">
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                        <div class="flex justify-between items-center">
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-gray-900" x-text="solicitud.servicio_nombre"></h3>
                                <p class="text-xs text-gray-600">
                                    <span x-text="solicitud.paciente_nombre"></span> - 
                                    <span x-text="formatDate(solicitud.fecha_programada)"></span>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-green-600" x-text="formatMonto(solicitud.monto_total)"></p>
                                <span class="text-xs text-gray-500">✔️ Completada</span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</body>
</html>
