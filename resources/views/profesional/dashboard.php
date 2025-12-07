<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Especialista - VitaHome</title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/images/vitahome-icon.svg">
    <script>const BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/vitahome-brand.css">
    <link rel="stylesheet" href="<?= url('/css/skeleton.css') ?>">
    <link rel="stylesheet" href="<?= url('/css/breadcrumbs.css') ?>">
    <link rel="stylesheet" href="<?= url('/css/progress.css') ?>">
    <link rel="stylesheet" href="<?= url('/css/dark-mode.css') ?>">
    <script src="<?= asset('/js/dark-mode.js') ?>"></script>
    <script src="<?= asset('/js/keyboard-shortcuts.js') ?>"></script>
    <script src="<?= asset('/js/confirmation-modal.js') ?>"></script>
    <script src="<?= asset('/js/toast.js') ?>"></script>
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
        
        // Modal de detalles
        mostrarModalDetalle: false,
        solicitudDetalle: null,
        
        // Modal de completar servicio
        mostrarModalCompletar: false,
        solicitudACompletar: null,
        formCompletar: {
            reporte: '',
            diagnostico: '',
            notas: ''
        },
        
        // Modal de calificación al paciente
        mostrarModalCalificacion: false,
        solicitudACalificar: null,
        calificacionPaciente: 0,
        comentarioCalificacion: '',
        enviandoCalificacion: false,
        
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
                window.location.href = BASE_URL + '/login';
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
                const statsResponse = await fetch(BASE_URL + '/api/profesional/stats', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                if (statsResponse.ok) {
                    const response = await statsResponse.json();
                    this.stats = response.stats || response.data?.stats || this.stats;
                }

                // Cargar solicitudes
                const solicitudesResponse = await fetch(BASE_URL + '/api/profesional/solicitudes', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                if (solicitudesResponse.ok) {
                    const response = await solicitudesResponse.json();
                    // La respuesta puede venir como {success: true, data: {solicitudes: []}} o {success: true, solicitudes: []}
                    const solicitudesData = response.solicitudes || response.data?.solicitudes || [];
                    // Convertir a array normal para evitar problemas con Proxy
                    this.solicitudes = Array.isArray(solicitudesData) ? [...solicitudesData] : [];
                    console.log('Solicitudes cargadas:', this.solicitudes.length);
                    this.separarPorEstado();
                    console.log('Separadas - Pendientes:', this.solicitudesPendientes.length, 'Activas:', this.solicitudesActivas.length);
                    
                    // Actualizar contador de notificaciones
                    this.notificaciones = this.solicitudesPendientes.length;
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        separarPorEstado() {
            // Crear arrays nuevos para evitar problemas con el Proxy de Alpine.js
            this.solicitudesPendientes = [];
            this.solicitudesActivas = [];
            this.solicitudesCompletadas = [];
            
            // Filtrar manualmente con logs de debug
            console.log('=== SEPARANDO POR ESTADO ===');
            console.log('Total solicitudes a procesar:', this.solicitudes.length);
            
            for (let i = 0; i < this.solicitudes.length; i++) {
                const solicitud = this.solicitudes[i];
                const estado = String(solicitud.estado || '').trim();
                console.log(`Solicitud ${solicitud.id}: estado="${estado}"`);
                
                if (estado === 'asignado') {
                    console.log('  ✓ Agregada a PENDIENTES');
                    this.solicitudesPendientes.push(solicitud);
                } else if (estado === 'en_proceso') {
                    console.log('  ✓ Agregada a ACTIVAS');
                    this.solicitudesActivas.push(solicitud);
                } else if (estado === 'completado') {
                    console.log('  ✓ Agregada a COMPLETADAS');
                    this.solicitudesCompletadas.push(solicitud);
                } else {
                    console.log(`  ✗ Estado no reconocido: "${estado}"`);
                }
            }
            
            console.log('=== RESULTADO ===');
            console.log('Pendientes:', this.solicitudesPendientes.length);
            console.log('Activas:', this.solicitudesActivas.length);
            console.log('Completadas:', this.solicitudesCompletadas.length);
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
                const response = await fetch(`${BASE_URL}/api/profesional/solicitudes/${solicitudId}/aceptar`, {
                    method: 'POST',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (response.ok) {
                    ToastNotification.success('Solicitud aceptada exitosamente');
                    await this.cargarDatos();
                } else {
                    ToastNotification.error(data.message || 'Error al aceptar la solicitud');
                }
            } catch (error) {
                console.error('Error:', error);
                ToastNotification.error('Error al procesar la solicitud');
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
                const response = await fetch(`${BASE_URL}/api/profesional/solicitudes/${solicitudId}/rechazar`, {
                    method: 'POST',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ motivo })
                });

                const data = await response.json();
                
                if (response.ok) {
                    ToastNotification.success('Solicitud rechazada correctamente');
                    await this.cargarDatos();
                } else {
                    ToastNotification.error(data.message || 'Error al rechazar la solicitud');
                }
            } catch (error) {
                console.error('Error:', error);
                ToastNotification.error('Error al procesar la solicitud');
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
                const response = await fetch(`${BASE_URL}/api/profesional/solicitudes/${solicitudId}/iniciar`, {
                    method: 'POST',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (response.ok) {
                    ToastNotification.success('Servicio iniciado correctamente');
                    await this.cargarDatos();
                } else {
                    ToastNotification.error(data.message || 'Error al iniciar el servicio');
                }
            } catch (error) {
                console.error('Error:', error);
                ToastNotification.error('Error al procesar la solicitud');
            } finally {
                this.loading = false;
            }
        },

        async completarServicio(solicitudId) {
            // Abrir modal de completar servicio
            this.solicitudACompletar = this.solicitudesActivas.find(s => s.id === solicitudId);
            this.mostrarModalCompletar = true;
        },
        
        async enviarCompletarServicio() {
            // Validar campos requeridos
            if (!this.formCompletar.reporte.trim()) {
                ToastNotification.warning('El reporte del servicio es obligatorio');
                return;
            }
            if (!this.formCompletar.diagnostico.trim()) {
                ToastNotification.warning('El diagnóstico o conclusión es obligatorio');
                return;
            }

            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`${BASE_URL}/api/profesional/solicitudes/${this.solicitudACompletar.id}/completar`, {
                    method: 'POST',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.formCompletar)
                });

                const data = await response.json();
                
                if (response.ok) {
                    ToastNotification.success('Servicio completado exitosamente');
                    this.cerrarModalCompletar();
                    await this.cargarDatos();
                } else {
                    ToastNotification.error(data.message || 'Error al completar el servicio');
                }
            } catch (error) {
                console.error('Error:', error);
                ToastNotification.error('Error al procesar la solicitud');
            } finally {
                this.loading = false;
            }
        },
        
        cerrarModalCompletar() {
            this.mostrarModalCompletar = false;
            this.solicitudACompletar = null;
            this.formCompletar = {
                reporte: '',
                diagnostico: '',
                notas: ''
            };
        },

        verDetalle(solicitud) {
            this.solicitudDetalle = solicitud;
            this.mostrarModalDetalle = true;
        },
        
        cerrarModalDetalle() {
            this.mostrarModalDetalle = false;
            this.solicitudDetalle = null;
        },

        // === FUNCIONES DE CALIFICACIÓN AL PACIENTE ===
        abrirModalCalificacion(solicitud) {
            this.solicitudACalificar = solicitud;
            this.calificacionPaciente = 0;
            this.comentarioCalificacion = '';
            this.mostrarModalCalificacion = true;
        },

        cerrarModalCalificacion() {
            this.mostrarModalCalificacion = false;
            this.solicitudACalificar = null;
            this.calificacionPaciente = 0;
            this.comentarioCalificacion = '';
        },

        seleccionarCalificacion(valor) {
            this.calificacionPaciente = valor;
        },

        async enviarCalificacion() {
            if (this.calificacionPaciente < 1 || this.calificacionPaciente > 5) {
                ToastNotification.warning('Por favor selecciona una calificación del 1 al 5');
                return;
            }

            this.enviandoCalificacion = true;
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`${BASE_URL}/api/profesional/solicitudes/${this.solicitudACalificar.id}/calificar-paciente`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        calificacion: this.calificacionPaciente,
                        comentario: this.comentarioCalificacion
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    ToastNotification.success('¡Gracias por calificar al paciente!');
                    this.cerrarModalCalificacion();
                    await this.cargarDatos();
                } else {
                    ToastNotification.error(data.message || 'Error al enviar la calificación');
                }
            } catch (error) {
                console.error('Error:', error);
                ToastNotification.error('Error de conexión al enviar la calificación');
            } finally {
                this.enviandoCalificacion = false;
            }
        },

        puedeCalificarPaciente(solicitud) {
            return solicitud.estado === 'completado' && !solicitud.calificacion_profesional;
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
                'asignado': 'bg-yellow-100 text-yellow-800 border-yellow-300',
                'confirmada': 'bg-blue-100 text-blue-800 border-blue-300',
                'en_proceso': 'bg-teal-100 text-teal-800 border-indigo-300',
                'completado': 'bg-green-100 text-green-800 border-green-300',
                'cancelado': 'bg-red-100 text-red-800 border-red-300',
                'rechazada': 'bg-red-100 text-red-800 border-red-300'
            };
            return colores[estado] || 'bg-gray-100 text-gray-800 border-gray-300';
        },

        getEstadoTexto(estado) {
            const textos = {
                'pendiente': '⏳ Pendiente',
                'asignado': '📌 Asignado',
                'confirmada': '✅ Confirmada',
                'en_proceso': '🔄 En Proceso',
                'completado': '✔️ Completado',
                'cancelado': '❌ Cancelado',
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
            window.location.href = BASE_URL + '/login';
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
                        <h1 class="text-lg font-semibold text-gray-900">Dashboard Especialista</h1>
                        <p class="text-xs text-gray-500" x-text="usuario.nombre"></p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Notificaciones con badge mejorado -->
                    <div class="notification-badge notification-badge-pulse" :data-count="solicitudesPendientes.length">
                        <button @click="activeTab = 'pendientes'" class="p-2 text-gray-600 hover:text-green-600 transition" title="Solicitudes pendientes">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Toggle Dark Mode -->
                    <button @click="window.darkMode.toggle()" class="text-gray-600 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition" title="Cambiar tema">
                        <svg x-show="!window.darkMode.isDark()" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg x-show="window.darkMode.isDark()" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>
                    
                    <button @click="window.location.href = BASE_URL + '/profesional/dashboard'" class="text-gray-600 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition" title="Inicio">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </button>
                    
                    <button @click="logout()" class="text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition" title="Cerrar sesión">
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
                <a href="<?= url('/profesional/dashboard') ?>">Inicio</a>
            </div>
            <span class="breadcrumb-separator">/</span>
            <div class="breadcrumb-item active">Dashboard Especialista</div>
        </nav>

        <!-- Skeletons para estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" x-show="loading">
            <div class="skeleton-stat-card"></div>
            <div class="skeleton-stat-card"></div>
            <div class="skeleton-stat-card"></div>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" x-show="!loading">
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
                        <p class="text-gray-500 text-sm font-medium">En Proceso</p>
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
                            <button x-show="solicitud.estado === 'en_proceso'" 
                                    @click="completarServicio(solicitud.id)" 
                                    class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition font-medium">
                                ✔️ Completar Servicio
                            </button>
                            <button @click="verDetalle(solicitud)" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                👁️ Ver Detalles
                            </button>
                        </div>
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
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-gray-900" x-text="solicitud.servicio_nombre"></h3>
                                <p class="text-xs text-gray-600">
                                    <span x-text="solicitud.paciente_nombre"></span> - 
                                    <span x-text="formatDate(solicitud.fecha_programada)"></span>
                                </p>
                                <!-- Mostrar si ya calificó al paciente -->
                                <div class="mt-2">
                                    <template x-if="solicitud.calificacion_profesional">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                            ✅ Paciente calificado: 
                                            <template x-for="i in 5">
                                                <span :class="i <= solicitud.calificacion_profesional ? 'text-yellow-500' : 'text-gray-300'">★</span>
                                            </template>
                                        </span>
                                    </template>
                                    <template x-if="!solicitud.calificacion_profesional">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">
                                            ⏳ Pendiente calificar paciente
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <div class="text-right flex flex-col items-end gap-2">
                                <p class="text-sm font-semibold text-green-600" x-text="formatMonto(solicitud.monto_total)"></p>
                                <span class="text-xs text-gray-500">✔️ Completada</span>
                                <!-- Botón para calificar al paciente -->
                                <template x-if="puedeCalificarPaciente(solicitud)">
                                    <button @click="abrirModalCalificacion(solicitud)"
                                            class="mt-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg transition-colors">
                                        ⭐ Calificar Paciente
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Modal de Detalles -->
    <div x-show="mostrarModalDetalle" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="cerrarModalDetalle()"></div>
            
            <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 z-10"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100">
                
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-2xl font-bold text-gray-900">Detalles del Servicio</h2>
                    <button @click="cerrarModalDetalle()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4" x-show="solicitudDetalle">
                    <!-- Información del Servicio -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">📋 Información del Servicio</h3>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-gray-500">Servicio</p>
                                <p class="font-medium" x-text="solicitudDetalle?.servicio_nombre"></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Tipo</p>
                                <p class="font-medium capitalize" x-text="solicitudDetalle?.servicio_tipo"></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Modalidad</p>
                                <p class="font-medium capitalize" x-text="solicitudDetalle?.modalidad"></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Monto</p>
                                <p class="font-medium text-green-600" x-text="formatMonto(solicitudDetalle?.monto_total)"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Paciente -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">👤 Información del Paciente</h3>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-gray-500">Nombre</p>
                                <p class="font-medium" x-text="solicitudDetalle?.paciente_nombre + ' ' + solicitudDetalle?.paciente_apellido"></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Teléfono</p>
                                <p class="font-medium" x-text="solicitudDetalle?.paciente_telefono || 'No especificado'"></p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-gray-500">Email</p>
                                <p class="font-medium" x-text="solicitudDetalle?.paciente_email"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles de la Cita -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">📅 Detalles de la Cita</h3>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-gray-500">Fecha Programada</p>
                                <p class="font-medium" x-text="formatDate(solicitudDetalle?.fecha_programada)"></p>
                            </div>
                            <div x-show="solicitudDetalle?.hora_programada">
                                <p class="text-gray-500">Hora</p>
                                <p class="font-medium" x-text="solicitudDetalle?.hora_programada"></p>
                            </div>
                            <div class="col-span-2" x-show="solicitudDetalle?.direccion_servicio">
                                <p class="text-gray-500">Dirección</p>
                                <p class="font-medium" x-text="solicitudDetalle?.direccion_servicio"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Síntomas / Observaciones -->
                    <div class="bg-gray-50 rounded-lg p-4" x-show="solicitudDetalle?.sintomas || solicitudDetalle?.observaciones">
                        <h3 class="font-semibold text-gray-900 mb-3">📝 Información Adicional</h3>
                        <div class="space-y-3 text-sm">
                            <div x-show="solicitudDetalle?.sintomas">
                                <p class="text-gray-500">Síntomas</p>
                                <p class="font-medium" x-text="solicitudDetalle?.sintomas"></p>
                            </div>
                            <div x-show="solicitudDetalle?.observaciones">
                                <p class="text-gray-500">Observaciones</p>
                                <p class="font-medium" x-text="solicitudDetalle?.observaciones"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Estado Actual -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">📊 Estado</h3>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                  :class="{
                                      'bg-yellow-100 text-yellow-800': solicitudDetalle?.estado === 'asignado',
                                      'bg-blue-100 text-blue-800': solicitudDetalle?.estado === 'en_proceso',
                                      'bg-green-100 text-green-800': solicitudDetalle?.estado === 'completado'
                                  }"
                                  x-text="solicitudDetalle?.estado === 'asignado' ? 'Pendiente de Aceptar' : 
                                         solicitudDetalle?.estado === 'en_proceso' ? 'En Proceso' : 
                                         'Completado'">
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button @click="cerrarModalDetalle()" 
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Completar Servicio -->
    <div x-show="mostrarModalCompletar" 
         x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50 p-4"
         @click.self="cerrarModalCompletar()">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto"
             @click.stop>
            <div class="p-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">✅ Completar Servicio</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Servicio: <span x-text="solicitudACompletar?.servicio_nombre" class="font-medium"></span>
                        </p>
                        <p class="text-sm text-gray-600">
                            Paciente: <span x-text="solicitudACompletar?.paciente_nombre" class="font-medium"></span>
                        </p>
                    </div>
                    <button @click="cerrarModalCompletar()" 
                            class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Formulario -->
                <div class="space-y-6">
                    <!-- Reporte del Servicio (OBLIGATORIO) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            📋 Reporte del Servicio <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            x-model="formCompletar.reporte"
                            rows="5"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                            placeholder="Describe detalladamente el servicio prestado, procedimientos realizados, hallazgos relevantes...&#10;&#10;Ejemplo: Se realizó consulta general. El paciente presentó síntomas de gripe común. Se examinaron vías respiratorias, temperatura y presión arterial. Todos los signos vitales dentro de parámetros normales..."
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">Este reporte será visible para el paciente y la plataforma</p>
                    </div>

                    <!-- Diagnóstico o Conclusiones (OBLIGATORIO) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            🩺 Diagnóstico / Conclusiones <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            x-model="formCompletar.diagnostico"
                            rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                            placeholder="Diagnóstico médico o conclusiones profesionales...&#10;&#10;Ejemplo: Diagnóstico: Rinofaringitis aguda (Gripe común)&#10;&#10;Evolución esperada: Mejoría en 5-7 días con el tratamiento indicado."
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">Diagnóstico final o conclusiones del servicio</p>
                    </div>

                    <!-- Notas Adicionales (OPCIONAL) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            📝 Notas Adicionales (Opcional)
                        </label>
                        <textarea 
                            x-model="formCompletar.notas"
                            rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                            placeholder="Recomendaciones, medicamentos recetados, indicaciones de seguimiento...&#10;&#10;Ejemplo:&#10;- Paracetamol 500mg cada 8 horas por 5 días&#10;- Abundantes líquidos&#10;- Reposo relativo&#10;- Control en 7 días si persisten síntomas"
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">Información adicional, recetas, recomendaciones</p>
                    </div>

                    <!-- Aviso Importante -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Importante:</strong> Una vez completado el servicio, el paciente podrá ver este reporte y calificar tu atención. Asegúrate de incluir toda la información relevante.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="mt-8 flex gap-3">
                    <button @click="cerrarModalCompletar()" 
                            class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                        ❌ Cancelar
                    </button>
                    <button @click="enviarCompletarServicio()" 
                            :disabled="loading"
                            :class="loading ? 'opacity-50 cursor-not-allowed' : ''"
                            class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                        <span x-show="!loading">✅ Completar Servicio</span>
                        <span x-show="loading">⏳ Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Calificación al Paciente -->
    <div x-show="mostrarModalCalificacion" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="cerrarModalCalificacion()"></div>
            
            <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6 z-10"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100">
                
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">⭐ Calificar al Paciente</h2>
                    <button @click="cerrarModalCalificacion()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Info del servicio -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6" x-show="solicitudACalificar">
                    <p class="text-sm text-gray-600">Servicio completado:</p>
                    <p class="font-semibold text-gray-900" x-text="solicitudACalificar?.servicio_nombre"></p>
                    <p class="text-sm text-gray-600 mt-1">
                        Paciente: <span class="font-medium" x-text="solicitudACalificar?.paciente_nombre"></span>
                    </p>
                </div>

                <!-- Estrellas de calificación -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">¿Cómo calificarías al paciente?</label>
                    <div class="flex justify-center gap-2">
                        <template x-for="i in 5" :key="i">
                            <button @click="seleccionarCalificacion(i)" 
                                    class="text-4xl transition-transform hover:scale-110 focus:outline-none"
                                    :class="i <= calificacionPaciente ? 'text-yellow-400' : 'text-gray-300'">
                                ★
                            </button>
                        </template>
                    </div>
                    <p class="text-center text-sm text-gray-500 mt-2" x-show="calificacionPaciente > 0">
                        <span x-text="calificacionPaciente"></span> de 5 estrellas
                    </p>
                </div>

                <!-- Criterios de evaluación -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-xs font-medium text-blue-800 mb-2">💡 Criterios para calificar:</p>
                    <ul class="text-xs text-blue-700 space-y-1">
                        <li>• Puntualidad y disponibilidad</li>
                        <li>• Claridad en la comunicación</li>
                        <li>• Seguimiento de indicaciones</li>
                        <li>• Trato respetuoso</li>
                    </ul>
                </div>

                <!-- Comentario opcional -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Comentario (opcional)</label>
                    <textarea x-model="comentarioCalificacion" 
                              rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                              placeholder="Comparte tu experiencia con este paciente..."></textarea>
                </div>

                <!-- Botones -->
                <div class="flex gap-3">
                    <button @click="cerrarModalCalificacion()" 
                            class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                        Cancelar
                    </button>
                    <button @click="enviarCalificacion()" 
                            :disabled="enviandoCalificacion || calificacionPaciente === 0"
                            :class="(enviandoCalificacion || calificacionPaciente === 0) ? 'opacity-50 cursor-not-allowed' : ''"
                            class="flex-1 px-4 py-3 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition font-medium">
                        <span x-show="!enviandoCalificacion">⭐ Enviar Calificación</span>
                        <span x-show="enviandoCalificacion">Enviando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
