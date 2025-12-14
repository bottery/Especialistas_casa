<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Super Admin - Especialistas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="bg-gray-50" x-data="dashboardApp()" x-init="init()">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <svg class="h-8 w-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xl font-bold text-gray-900">Especialistas en Casa</span>
                    </div>
                    <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded-full">SUPER ADMIN</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Notificaciones -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span x-show="notificaciones.length > 0" class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500"></span>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50">
                            <div class="p-4">
                                <h3 class="font-semibold mb-2">Notificaciones</h3>
                                <div x-show="notificaciones.length === 0" class="text-gray-500 text-sm py-4 text-center">
                                    No hay notificaciones
                                </div>
                                <template x-for="notif in notificaciones" :key="notif.id">
                                    <div class="py-2 border-b text-sm">
                                        <p class="font-medium" x-text="notif.titulo"></p>
                                        <p class="text-gray-600 text-xs" x-text="notif.mensaje"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <span class="text-gray-700 font-medium" x-text="userName"></span>
                    <button @click="logout" class="text-gray-700 hover:text-red-600 transition font-medium">
                        Cerrar Sesión
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Panel de Control</h1>
            <p class="text-gray-600 mt-1">Gestión completa del sistema</p>
        </div>

        <!-- Mensajes -->
        <div x-show="message" x-transition class="mb-6 p-4 rounded-lg shadow-lg" :class="messageType === 'success' ? 'bg-green-50 text-green-800 border-l-4 border-green-500' : 'bg-red-50 text-red-800 border-l-4 border-red-500'">
            <div class="flex items-center">
                <svg x-show="messageType === 'success'" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <svg x-show="messageType === 'error'" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p x-text="message" class="font-medium"></p>
            </div>
        </div>

        <!-- Stats Cards con animación -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Usuarios -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Usuarios</p>
                        <p class="text-4xl font-bold mt-2" x-text="stats.totalUsuarios">0</p>
                        <p class="text-blue-100 text-xs mt-1">
                            <span x-text="stats.nuevosHoy || 0"></span> nuevos hoy
                        </p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-full p-4">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Servicios Activos -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Servicios Activos</p>
                        <p class="text-4xl font-bold mt-2" x-text="stats.serviciosActivos">0</p>
                        <p class="text-green-100 text-xs mt-1">En progreso ahora</p>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-full p-4">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Solicitudes Pendientes -->
            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm font-medium">Pendientes</p>
                        <p class="text-4xl font-bold mt-2" x-text="stats.solicitudesPendientes">0</p>
                        <p class="text-yellow-100 text-xs mt-1">Requieren atención</p>
                    </div>
                    <div class="bg-yellow-400 bg-opacity-30 rounded-full p-4">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Ingresos del Mes -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Ingresos Mes</p>
                        <p class="text-4xl font-bold mt-2">$<span x-text="formatNumber(stats.ingresosMes)">0</span></p>
                        <p class="text-purple-100 text-xs mt-1">
                            <span x-text="stats.crecimiento || '+0'"></span>% vs mes anterior
                        </p>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 rounded-full p-4">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos de Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Gráfico de Ingresos -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Ingresos Últimos 7 Días</h3>
                <canvas id="ingresosChart" height="200"></canvas>
            </div>

            <!-- Gráfico de Servicios -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Servicios Más Solicitados</h3>
                <canvas id="serviciosChart" height="200"></canvas>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-lg mb-8">
            <div class="border-b border-gray-200">
                <nav class="flex flex-wrap -mb-px">
                    <button @click="currentTab = 'dashboard'" :class="currentTab === 'dashboard' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        📊 Dashboard
                    </button>
                    <button @click="currentTab = 'usuarios'" :class="currentTab === 'usuarios' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        👥 Usuarios
                    </button>
                    <button @click="currentTab = 'profesionales'" :class="currentTab === 'profesionales' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        👨‍⚕️ Profesionales
                    </button>
                    <button @click="currentTab = 'servicios'" :class="currentTab === 'servicios' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        🏥 Servicios
                    </button>
                    <button @click="currentTab = 'finanzas'" :class="currentTab === 'finanzas' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        💰 Finanzas
                    </button>
                    <button @click="currentTab = 'integraciones'" :class="currentTab === 'integraciones' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        �� Integraciones
                    </button>
                    <button @click="currentTab = 'logs'" :class="currentTab === 'logs' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        📋 Logs
                    </button>
                    <button @click="currentTab = 'configuracion'" :class="currentTab === 'configuracion' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                        ⚙️ Configuración
                    </button>
                </nav>
            </div>

            <div class="p-6"> 
            </div>
        </div>
    </div>

<script>
// Sistema de logging y captura de errores (copiado desde dashboard_backup)
window.errorLog = {
    logs: [],
    logEvent(level, message, details = {}) {
        const timestamp = new Date().toISOString();
        const logEntry = { timestamp, level, message, details, userAgent: navigator.userAgent, url: window.location.href };
        this.logs.push(logEntry);
        if (this.logs.length > 50) this.logs.shift();
        const style = { 'ERROR': 'background: #fee; color: #c33; font-weight: bold; padding: 5px 10px; border-radius: 3px;', 'WARN': 'background: #ffe; color: #663; font-weight: bold; padding: 5px 10px; border-radius: 3px;', 'INFO': 'background: #eef; color: #336; font-weight: bold; padding: 5px 10px; border-radius: 3px;', 'DEBUG': 'background: #f0f0f0; color: #666; font-weight: normal; padding: 5px 10px; border-radius: 3px;' };
        console.log(`%c[${level}] ${timestamp}`, style[level] || 'color: #666;');
        console.log(`   ${message}`, details);
    },
    error(message, details) { this.logEvent('ERROR', message, details); },
    warn(message, details) { this.logEvent('WARN', message, details); },
    info(message, details) { this.logEvent('INFO', message, details); },
    debug(message, details) { this.logEvent('DEBUG', message, details); },
    getLogs() { return this.logs; },
    async sendToServer() { try { const token = localStorage.getItem('token'); await fetch(BASE_URL + '/api/admin/error-logs', { method: 'POST', headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' }, body: JSON.stringify({ logs: this.logs }) }); } catch (e) { console.error('No se pudieron enviar logs al servidor:', e); } }
};

window.addEventListener('error', (event) => { window.errorLog.error('Error global no capturado', { message: event.message, filename: event.filename, lineno: event.lineno, colno: event.colno, stack: event.error?.stack }); });
window.addEventListener('unhandledrejection', (event) => { window.errorLog.error('Promise rechazada no manejada', { reason: event.reason?.message || String(event.reason), stack: event.reason?.stack }); });

document.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.shiftKey && e.key === 'D') {
        e.preventDefault();
        const app = document.querySelector('[x-data]')?.__Alpine?.data;
        if (app) app.diagnosticPanel = !app.diagnosticPanel;
    }
});

// Incluir la implementación principal de la app (charts, QR, config)
// Reuse the robust implementation from dashboard_backup
// For brevity we call the existing dashboardApp from backup if present
if (!window.dashboardApp) {
    window.dashboardApp = function() {
        return {
            loading: false,
            message: '',
            messageType: 'success',
            currentTab: 'dashboard',
            diagnosticPanel: false,
            usuario: {},
            stats: { totalUsuarios:0, serviciosActivos:0, solicitudesPendientes:0, ingresosMes:0, solicitudesCompletadas:0, pagosHoy:0, nuevosUsuariosHoy:0, profesionalesActivos:0 },
            actividadReciente: { solicitudes: [], pagos: [] },
            charts: {}, retryCount:0, maxRetries:3,
            configPagos: { banco_nombre:'', banco_cuenta:'', banco_tipo_cuenta:'Ahorros', banco_titular:'', qr_imagen_path:'', whatsapp_contacto:'', instrucciones_transferencia:'' },
            guardandoConfig:false,
            async init(){ try{ window.errorLog.info('Iniciando dashboard...'); const token = localStorage.getItem('token'); if(!token){ window.errorLog.warn('No hay token'); window.location.href = BASE_URL + '/login'; return; } const userData = JSON.parse(localStorage.getItem('usuario')||'{}'); this.usuario = userData; if(userData.rol !== 'superadmin'){ this.showMessage('Acceso denegado: solo superadmin','error'); setTimeout(()=>window.location.href=BASE_URL+'/login',2000); return; } await this.loadDashboardData(); await this.loadChartData(); await this.cargarConfigPagos(); window.errorLog.info('Dashboard inicializado'); }catch(e){ window.errorLog.error('Error init',{message:e.message,stack:e.stack}); this.showMessage('Error inicializando dashboard','error'); } },
            async loadDashboardData(){ this.loading=true; try{ const token = localStorage.getItem('token'); const response = await fetch(BASE_URL + '/api/superadmin/dashboard',{ method:'GET', headers: { 'Authorization': `Bearer ${token}`, 'Content-Type':'application/json','Accept':'application/json' }, cache:'no-cache' }); if(!response.ok) throw new Error(`HTTP ${response.status}`); const data = await response.json(); if(data.stats) this.stats = data.stats; else if(data.data && typeof data.data==='object' && !Array.isArray(data.data)) this.stats = data.data; else if(data.totalUsuarios!==undefined) this.stats = data; if(data.actividad_reciente) this.actividadReciente = data.actividad_reciente; Object.keys(this.stats).forEach(k=>{ if(typeof this.stats[k] !== 'number') this.stats[k]=parseInt(this.stats[k])||0; }); this.loading=false; }catch(e){ window.errorLog.error('Error al cargar dashboard',{message:e.message}); if(this.retryCount < this.maxRetries){ this.retryCount++; setTimeout(()=>this.loadDashboardData(),1000); } else { this.showMessage('Error al cargar datos del dashboard','error'); this.loading=false; } } },
            async loadChartData(){ try{ const token = localStorage.getItem('token'); const response = await fetch(BASE_URL + '/api/analytics/charts',{ method:'GET', headers: { 'Authorization': `Bearer ${token}`, 'Content-Type':'application/json','Accept':'application/json' }, cache:'no-cache' }); if(!response.ok) throw new Error(`HTTP ${response.status}`); const result = await response.json(); const data = result.data || result; setTimeout(()=>{ try{ this.renderCharts(data); }catch(e){ window.errorLog.error('Error render charts',{message:e.message}); } },300); }catch(e){ window.errorLog.error('Error cargando charts',{message:e.message}); } },
            renderCharts(data){ try{ Object.values(this.charts).forEach(c=>{ if(c && typeof c.destroy==='function'){ try{c.destroy()}catch{} } }); this.charts={}; const createChart=(canvasId,chartConfig)=>{ try{ const canvas=document.getElementById(canvasId); if(!canvas) return null; const rect=canvas.getBoundingClientRect(); if(rect.width===0||rect.height===0) return null; const ctx=canvas.getContext('2d'); if(!ctx) return null; return new Chart(ctx,chartConfig); }catch(e){ window.errorLog.error(`Error creando chart ${canvasId}`, {message:e.message}); return null; } };
                if(data.ingresos_mensuales && data.ingresos_mensuales.length>0){ this.charts.ingresos = createChart('ingresosChart',{ type:'line', data:{ labels:data.ingresos_mensuales.map(d=>d.mes||d.fecha||''), datasets:[{ label:'Ingresos ($)', data:data.ingresos_mensuales.map(d=>{ const val=parseFloat(d.total||d.cantidad||0); return isNaN(val)?0:val; }), borderColor:'rgb(99,102,241)', backgroundColor:'rgba(99,102,241,0.1)', tension:0.4, fill:true, pointBackgroundColor:'rgb(99,102,241)', pointBorderColor:'#fff', pointBorderWidth:2 }] }, options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:true, labels:{ color:'#666', font:{ size:12 } } }, filler:{ propagate:true } }, scales:{ y:{ beginAtZero:true, ticks:{ color:'#666' } }, x:{ ticks:{ color:'#666' } } } } }); }

                if(data.servicios_por_tipo && data.servicios_por_tipo.length>0){ this.charts.servicios = createChart('serviciosChart',{ type:'doughnut', data:{ labels:data.servicios_por_tipo.map(d=>d.tipo||d.nombre||''), datasets:[{ data:data.servicios_por_tipo.map(d=>{ const val=parseInt(d.cantidad||0); return isNaN(val)?0:val; }), backgroundColor:['rgb(59,130,246)','rgb(16,185,129)','rgb(251,191,36)','rgb(239,68,68)','rgb(139,92,246)','rgb(236,72,153)','rgb(20,184,166)','rgb(249,115,22)','rgb(156,163,175)','rgb(14,165,233)'], borderColor:'#fff', borderWidth:2 }] }, options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'right', labels:{ color:'#666', font:{ size:12 }, padding:15 } } } }); }
            }catch(e){ console.error('Error general en renderCharts',e); } },
            formatNumber(num){ return new Intl.NumberFormat('es-CO').format(num); },
            showMessage(msg,type='success'){ this.message=msg; this.messageType=type; setTimeout(()=>this.message='',5000); },
            async cargarConfigPagos(){ try{ const token = localStorage.getItem('token'); const response = await fetch(BASE_URL + '/api/admin/configuracion-pagos',{ method:'GET', headers:{ 'Authorization':`Bearer ${token}`, 'Content-Type':'application/json' } }); if(response.ok){ const result=await response.json(); const data=result.data||result; this.configPagos = { banco_nombre:data.banco_nombre||'', banco_cuenta:data.banco_cuenta||'', banco_tipo_cuenta:data.banco_tipo_cuenta||'Ahorros', banco_titular:data.banco_titular||'', qr_imagen_path:data.qr_imagen_path||'', whatsapp_contacto:data.whatsapp_contacto||'', instrucciones_transferencia:data.instrucciones_transferencia||'' }; window.errorLog.info('Configuración de pagos cargada',{banco:this.configPagos.banco_nombre,tieneQR:!!this.configPagos.qr_imagen_path}); }else{ window.errorLog.warn('Configuración de pagos no disponible',{status:response.status}); } }catch(e){ window.errorLog.error('Error cargando config pagos',{message:e.message}); } },
            async guardarConfigPagos(){ this.guardandoConfig=true; try{ const token=localStorage.getItem('token'); const response=await fetch(BASE_URL + '/api/admin/configuracion-pagos',{ method:'PUT', headers:{ 'Authorization':`Bearer ${token}`, 'Content-Type':'application/json' }, body:JSON.stringify(this.configPagos) }); if(response.ok){ this.showMessage('✅ Configuración de pagos actualizada correctamente','success'); }else{ const error=await response.json(); this.showMessage(error.message||'Error al guardar configuración','error'); } }catch(e){ this.showMessage('Error de conexión al guardar','error'); }finally{ this.guardandoConfig=false; } },
            async subirQR(event){ const file=event.target.files[0]; if(!file) return; if(!file.type.startsWith('image/')){ this.showMessage('Por favor seleccione una imagen válida','error'); return; } if(file.size>5*1024*1024){ this.showMessage('La imagen no debe superar los 5MB','error'); return; } const formData=new FormData(); formData.append('qr_imagen',file); try{ const token=localStorage.getItem('token'); const response=await fetch(BASE_URL + '/api/admin/subir-qr',{ method:'POST', headers:{ 'Authorization':`Bearer ${token}` }, body:formData }); if(response.ok){ const result=await response.json(); this.configPagos.qr_imagen_path = result.data?.qr_imagen_path || result.qr_imagen_path; this.showMessage('✅ QR subido correctamente','success'); }else{ const error=await response.json(); this.showMessage(error.message||'Error al subir QR','error'); } }catch(e){ this.showMessage('Error de conexión al subir QR','error'); } event.target.value=''; },
            async eliminarQR(){ if(!confirm('¿Está seguro de que desea eliminar el QR actual?')) return; try{ const token=localStorage.getItem('token'); const response=await fetch(BASE_URL + '/api/admin/configuracion-pagos/qr',{ method:'DELETE', headers:{ 'Authorization':`Bearer ${token}` } }); if(response.ok){ this.configPagos.qr_imagen_path=''; this.showMessage('✅ QR eliminado correctamente','success'); }else{ const error=await response.json(); this.showMessage(error.message||'Error al eliminar QR','error'); } }catch(e){ this.showMessage('Error de conexión al eliminar QR','error'); } },
            logout(){ localStorage.removeItem('token'); localStorage.removeItem('usuario'); window.location.href = BASE_URL + '/login'; },
            downloadLogs(){ try{ const logsJson = JSON.stringify(window.errorLog.logs, null, 2); const blob = new Blob([logsJson], { type: 'application/json' }); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = `dashboard-logs-${new Date().toISOString().split('T')[0]}.json`; document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url); this.showMessage('✅ Logs descargados correctamente','success'); }catch(e){ this.showMessage('Error al descargar logs','error'); } }
        }
    }
}
</script>

</body>
</html>
