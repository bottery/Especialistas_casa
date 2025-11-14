<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Financiero - Especialistas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="bg-gray-50" x-data="finanzasApp()" x-init="init()">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="/superadmin/dashboard.php" class="flex items-center space-x-2">
                        <svg class="h-8 w-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xl font-bold text-gray-900">Finanzas</span>
                    </a>
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">DASHBOARD</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Filtro periodo -->
                    <select x-model="periodo" @change="cargarDatos()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="hoy">Hoy</option>
                        <option value="semana">Última Semana</option>
                        <option value="mes">Último Mes</option>
                        <option value="año">Último Año</option>
                    </select>
                    <a href="/superadmin/dashboard.php" class="text-gray-600 hover:text-gray-900">← Dashboard</a>
                    <button @click="logout()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Salir
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Navigation Menu -->
    <div class="bg-white border-b border-gray-200 sticky top-16 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8 overflow-x-auto py-4">
                <a href="/superadmin/dashboard" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="/superadmin/usuarios" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                    <span>Usuarios</span>
                </a>
                <a href="/superadmin/finanzas" class="flex items-center space-x-2 px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                    <span>Finanzas</span>
                </a>
                <a href="/superadmin/seguridad" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/>
                    </svg>
                    <span>Seguridad</span>
                </a>
                <a href="/superadmin/configuracion" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                    </svg>
                    <span>Configuración</span>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Loading -->
        <div x-show="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <!-- Mensajes -->
        <div x-show="message" x-transition class="mb-6 p-4 rounded-lg" :class="messageType === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
            <p x-text="message"></p>
        </div>

        <div x-show="!loading">
            <!-- Resumen Financiero -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Ingresos Totales</p>
                            <p class="text-3xl font-bold mt-2">$<span x-text="formatNumber(resumen.ingresos_totales || 0)">0</span></p>
                            <p class="text-green-100 text-xs mt-1" x-text="resumen.total_transacciones + ' transacciones'"></p>
                        </div>
                        <div class="bg-green-400 bg-opacity-30 rounded-full p-4">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Comisión Plataforma (15%)</p>
                            <p class="text-3xl font-bold mt-2">$<span x-text="formatNumber(resumen.comision_plataforma || 0)">0</span></p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-30 rounded-full p-4">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm font-medium">Pagos Pendientes</p>
                            <p class="text-3xl font-bold mt-2">$<span x-text="formatNumber(resumen.pendientes || 0)">0</span></p>
                        </div>
                        <div class="bg-yellow-400 bg-opacity-30 rounded-full p-4">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Ticket Promedio</p>
                            <p class="text-3xl font-bold mt-2">$<span x-text="formatNumber(resumen.ticket_promedio || 0)">0</span></p>
                        </div>
                        <div class="bg-purple-400 bg-opacity-30 rounded-full p-4">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Transacciones Diarias</h3>
                        <button @click="exportarReporte('csv')" class="text-sm text-indigo-600 hover:text-indigo-800">Exportar CSV</button>
                    </div>
                    <canvas id="transaccionesChart" height="250"></canvas>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Métodos de Pago</h3>
                    <canvas id="metodosChart" height="250"></canvas>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <button @click="exportarReporte('csv')" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow text-left">
                    <div class="flex items-center space-x-4">
                        <div class="bg-green-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Exportar CSV</p>
                            <p class="text-sm text-gray-500">Descargar reporte detallado</p>
                        </div>
                    </div>
                </button>

                <button @click="exportarReporte('excel')" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow text-left">
                    <div class="flex items-center space-x-4">
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Exportar Excel</p>
                            <p class="text-sm text-gray-500">Formato para análisis</p>
                        </div>
                    </div>
                </button>

                <button @click="modalRetiros = true" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow text-left">
                    <div class="flex items-center space-x-4">
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Retiros Pendientes</p>
                            <p class="text-sm text-gray-500" x-text="retiros.length + ' profesionales'">0 profesionales</p>
                        </div>
                    </div>
                </button>
            </div>

            <!-- Tabla de pagos recientes -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Transacciones Recientes</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="pago in pagos" :key="pago.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900" x-text="'#' + pago.id"></td>
                                    <td class="px-6 py-4 text-sm text-gray-900" x-text="(pago.usuario_nombre || '') + ' ' + (pago.usuario_apellido || '')"></td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">$<span x-text="formatNumber(pago.monto)"></span></td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full" 
                                              :class="pago.metodo_pago === 'pse' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'"
                                              x-text="pago.metodo_pago.toUpperCase()"></span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                              :class="{
                                                  'bg-green-100 text-green-800': pago.estado === 'aprobado',
                                                  'bg-yellow-100 text-yellow-800': pago.estado === 'pendiente',
                                                  'bg-red-100 text-red-800': pago.estado === 'rechazado'
                                              }"
                                              x-text="pago.estado"></span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500" x-text="formatDate(pago.created_at)"></td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <template x-if="pago.estado === 'pendiente'">
                                            <div class="flex justify-end space-x-2">
                                                <button @click="actualizarEstado(pago.id, 'aprobado')" class="text-green-600 hover:text-green-900">Aprobar</button>
                                                <button @click="actualizarEstado(pago.id, 'rechazado')" class="text-red-600 hover:text-red-900">Rechazar</button>
                                            </div>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Retiros -->
    <div x-show="modalRetiros" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div @click="modalRetiros = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            
            <div class="relative bg-white rounded-lg max-w-4xl w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold">Retiros Pendientes</h3>
                    <button @click="modalRetiros = false" class="text-gray-400 hover:text-gray-500">✕</button>
                </div>
                
                <div class="space-y-4">
                    <template x-for="retiro in retiros" :key="retiro.profesional_id">
                        <div class="border rounded-lg p-4 flex justify-between items-center">
                            <div>
                                <p class="font-semibold" x-text="retiro.nombre + ' ' + retiro.apellido"></p>
                                <p class="text-sm text-gray-500" x-text="retiro.email"></p>
                                <p class="text-xs text-gray-400" x-text="retiro.servicios_completados + ' servicios completados'"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-green-600">$<span x-text="formatNumber(retiro.monto_pendiente)"></span></p>
                                <button @click="procesarRetiro(retiro.profesional_id, retiro.monto_pendiente)" class="mt-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                                    Procesar Retiro
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="retiros.length === 0">
                        <p class="text-center text-gray-500 py-8">No hay retiros pendientes</p>
                    </template>
                </div>
            </div>
        </div>
    </div>

<script>
function finanzasApp() {
    return {
        loading: false,
        message: '',
        messageType: 'success',
        periodo: 'mes',
        resumen: {},
        pagos: [],
        retiros: [],
        transacciones: [],
        metodos: [],
        modalRetiros: false,
        charts: {},

        async init() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '/login.php';
                return;
            }
            await this.cargarDatos();
        },

        async cargarDatos() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`/api/finanzas/dashboard?periodo=${this.periodo}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.resumen = data.resumen || {};
                    this.pagos = data.pagos_recientes || [];
                    this.retiros = data.retiros_pendientes || [];
                    this.transacciones = data.transacciones_diarias || [];
                    this.metodos = data.metodos_pago || [];
                    
                    setTimeout(() => this.renderCharts(), 100);
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        renderCharts() {
            // Transacciones diarias
            const ctx1 = document.getElementById('transaccionesChart');
            if (ctx1 && this.transacciones.length > 0) {
                if (this.charts.transacciones) this.charts.transacciones.destroy();
                this.charts.transacciones = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: this.transacciones.map(t => t.fecha),
                        datasets: [{
                            label: 'Ingresos ($)',
                            data: this.transacciones.map(t => parseFloat(t.total)),
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            // Métodos de pago
            const ctx2 = document.getElementById('metodosChart');
            if (ctx2 && this.metodos.length > 0) {
                if (this.charts.metodos) this.charts.metodos.destroy();
                this.charts.metodos = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: this.metodos.map(m => m.metodo_pago.toUpperCase()),
                        datasets: [{
                            data: this.metodos.map(m => parseFloat(m.total)),
                            backgroundColor: ['rgb(59, 130, 246)', 'rgb(139, 92, 246)']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        },

        async actualizarEstado(id, estado) {
            if (!confirm(`¿${estado === 'aprobado' ? 'Aprobar' : 'Rechazar'} este pago?`)) return;

            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/finanzas/actualizar-pago', {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id, estado })
                });

                if (response.ok) {
                    this.showMessage('Estado actualizado', 'success');
                    await this.cargarDatos();
                } else {
                    this.showMessage('Error al actualizar', 'error');
                }
            } catch (error) {
                this.showMessage('Error al actualizar', 'error');
            }
        },

        async procesarRetiro(profesionalId, monto) {
            if (!confirm(`¿Procesar retiro de $${this.formatNumber(monto)}?`)) return;

            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/finanzas/procesar-retiro', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ profesional_id: profesionalId, monto, metodo: 'transferencia' })
                });

                if (response.ok) {
                    this.showMessage('Retiro procesado correctamente', 'success');
                    this.modalRetiros = false;
                    await this.cargarDatos();
                } else {
                    this.showMessage('Error al procesar retiro', 'error');
                }
            } catch (error) {
                this.showMessage('Error al procesar retiro', 'error');
            }
        },

        exportarReporte(formato) {
            const params = new URLSearchParams({ formato, periodo: this.periodo });
            window.location.href = `/api/finanzas/exportar?${params}`;
        },

        formatNumber(num) {
            return new Intl.NumberFormat('es-CO').format(num || 0);
        },

        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('es-CO', {
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
            window.location.href = '/login.php';
        }
    }
}
</script>

</body>
</html>
