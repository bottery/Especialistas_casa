<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Super Admin - VitaHome</title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/images/vitahome-icon.svg">
    <script>const BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/vitahome-brand.css">
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
                        <svg class="h-8 w-8 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xl font-bold text-gray-900">Especialistas en Casa</span>
                    </div>
                    <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded-full">SUPER ADMIN</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600" x-text="'Bienvenido, ' + (usuario.nombre || 'Admin')"></span>
                    <button @click="logout()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
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
                <a href="<?= url('/superadmin/dashboard') ?>" class="flex items-center space-x-2 px-3 py-2 rounded-lg bg-teal-50 text-teal-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="<?= url('/superadmin/usuarios') ?>" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                    <span>Usuarios</span>
                </a>
                <a href="<?= url('/superadmin/finanzas') ?>" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                    <span>Finanzas</span>
                </a>
                <a href="<?= url('/superadmin/seguridad') ?>" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/>
                    </svg>
                    <span>Seguridad</span>
                </a>
                <a href="<?= url('/superadmin/configuracion') ?>" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                    </svg>
                    <span>Configuración</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Loading -->
        <div x-show="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-teal-600"></div>
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

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" x-show="!loading">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Usuarios</p>
                        <p class="text-4xl font-bold mt-2" x-text="stats.totalUsuarios || 0">0</p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-full p-4">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Servicios Activos</p>
                        <p class="text-4xl font-bold mt-2" x-text="stats.serviciosActivos || 0">0</p>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-full p-4">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm font-medium">Pendientes</p>
                        <p class="text-4xl font-bold mt-2" x-text="stats.solicitudesPendientes || 0">0</p>
                    </div>
                    <div class="bg-yellow-400 bg-opacity-30 rounded-full p-4">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Ingresos Mes</p>
                        <p class="text-3xl font-bold mt-2">$<span x-text="formatNumber(stats.ingresosMes || 0)">0</span></p>
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

        <!-- Segunda fila de estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" x-show="!loading">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Completadas</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2" x-text="stats.solicitudesCompletadas || 0">0</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Pagos Hoy</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2" x-text="stats.pagosHoy || 0">0</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-teal-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Nuevos Hoy</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2" x-text="stats.nuevosUsuariosHoy || 0">0</p>
                    </div>
                    <div class="bg-teal-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Profesionales</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2" x-text="stats.profesionalesActivos || 0">0</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8" x-show="!loading">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Ingresos Mensuales (12 meses)</h3>
                <div style="height: 250px; position: relative;">
                    <canvas id="ingresosChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Servicios Por Tipo</h3>
                <div style="height: 250px; position: relative;">
                    <canvas id="serviciosChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Usuarios Por Rol</h3>
                <div style="height: 250px; position: relative;">
                    <canvas id="usuariosChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Solicitudes Por Estado</h3>
                <div style="height: 250px; position: relative;">
                    <canvas id="solicitudesChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 lg:col-span-2">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Tendencia Semanal (Últimos 30 días)</h3>
                <div style="height: 150px; position: relative;">
                    <canvas id="tendenciaChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Configuración de Pagos -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8" x-show="!loading">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                    Configuración de Pagos por Transferencia
                </h3>
                <span class="text-xs text-gray-500">Solo tú puedes modificar estos datos</span>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Formulario de datos bancarios -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Banco</label>
                        <input type="text" x-model="configPagos.banco_nombre" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                               placeholder="Ej: Bancolombia">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Cuenta</label>
                        <select x-model="configPagos.banco_tipo_cuenta" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            <option value="Ahorros">Ahorros</option>
                            <option value="Corriente">Corriente</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de Cuenta</label>
                        <input type="text" x-model="configPagos.banco_cuenta" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                               placeholder="Ej: 123-456789-00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titular de la Cuenta</label>
                        <input type="text" x-model="configPagos.banco_titular" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                               placeholder="Ej: VitaHome S.A.S">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp de Contacto</label>
                        <input type="text" x-model="configPagos.whatsapp_contacto" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                               placeholder="Ej: +57 300 123 4567">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Instrucciones de Transferencia</label>
                        <textarea x-model="configPagos.instrucciones_transferencia" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                  placeholder="Instrucciones para el usuario..."></textarea>
                    </div>
                    <button @click="guardarConfigPagos()" 
                            :disabled="guardandoConfig"
                            class="w-full bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-4 rounded-lg transition-colors disabled:opacity-50">
                        <span x-show="!guardandoConfig">💾 Guardar Configuración</span>
                        <span x-show="guardandoConfig">Guardando...</span>
                    </button>
                </div>
                
                <!-- Vista previa y QR -->
                <div class="space-y-4">
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="font-medium text-gray-800 mb-3">👁️ Vista Previa (lo que ve el usuario)</h4>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-xs font-semibold text-blue-900 mb-2">📋 Datos para transferencia:</p>
                            <p class="text-xs text-blue-800">🏦 Banco: <strong x-text="configPagos.banco_nombre || 'Sin configurar'"></strong></p>
                            <p class="text-xs text-blue-800">📁 Tipo: <strong x-text="configPagos.banco_tipo_cuenta || 'Sin configurar'"></strong></p>
                            <p class="text-xs text-blue-800">🔢 Cuenta: <strong x-text="configPagos.banco_cuenta || 'Sin configurar'"></strong></p>
                            <p class="text-xs text-blue-800">👤 Titular: <strong x-text="configPagos.banco_titular || 'Sin configurar'"></strong></p>
                            <p class="text-xs text-blue-800 mt-2">📱 WhatsApp: <strong x-text="configPagos.whatsapp_contacto || 'Sin configurar'"></strong></p>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="font-medium text-gray-800 mb-3">📱 Código QR de Pago</h4>
                        <div class="text-center">
                            <template x-if="configPagos.qr_imagen_path">
                                <div>
                                    <img :src="BASE_URL + configPagos.qr_imagen_path" alt="QR de pago" class="w-40 h-40 mx-auto border-2 border-teal-200 rounded-lg">
                                    <p class="text-xs text-gray-500 mt-2">QR actual configurado</p>
                                </div>
                            </template>
                            <template x-if="!configPagos.qr_imagen_path">
                                <div class="bg-gray-200 w-40 h-40 mx-auto rounded-lg flex items-center justify-center">
                                    <span class="text-gray-500 text-sm">Sin QR</span>
                                </div>
                            </template>
                            <div class="mt-3">
                                <label class="cursor-pointer bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg inline-flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Subir nuevo QR
                                    <input type="file" class="hidden" accept="image/*" @change="subirQR($event)">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

<script>
window.dashboardApp = function() {
    return {
        loading: false,
        message: '',
        messageType: 'success',
        currentTab: 'dashboard',
        usuario: {},
        stats: {
            totalUsuarios: 0,
            serviciosActivos: 0,
            solicitudesPendientes: 0,
            ingresosMes: 0,
            solicitudesCompletadas: 0,
            pagosHoy: 0,
            nuevosUsuariosHoy: 0,
            profesionalesActivos: 0
        },
        actividadReciente: {
            solicitudes: [],
            pagos: []
        },
        charts: {},
        
        // Configuración de pagos
        configPagos: {
            banco_nombre: '',
            banco_cuenta: '',
            banco_tipo_cuenta: 'Ahorros',
            banco_titular: '',
            qr_imagen_path: '',
            whatsapp_contacto: '',
            instrucciones_transferencia: ''
        },
        guardandoConfig: false,

        async init() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = BASE_URL + '/login';
                return;
            }

            const userData = JSON.parse(localStorage.getItem('usuario') || '{}');
            this.usuario = userData;

            if (userData.rol !== 'superadmin') {
                this.showMessage('Acceso denegado', 'error');
                setTimeout(() => window.location.href = BASE_URL + '/login', 2000);
                return;
            }

            await this.loadDashboardData();
            await this.loadChartData();
            await this.cargarConfigPagos();
        },

        async loadDashboardData() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(BASE_URL + '/api/superadmin/dashboard', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.stats = data.stats || data.data || data;
                    this.actividadReciente = data.actividad_reciente || { solicitudes: [], pagos: [] };
                }
            } catch (error) {
                console.error('Error cargando dashboard:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadChartData() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(BASE_URL + '/api/analytics/charts', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    const result = await response.json();
                    const data = result.data || result;
                    this.renderCharts(data);
                }
            } catch (error) {
                console.error('Error cargando gráficos:', error);
            }
        },

        renderCharts(data) {
            // Ingresos Mensuales - Line Chart
            if (data.ingresos_mensuales && data.ingresos_mensuales.length > 0) {
                const ctx = document.getElementById('ingresosChart');
                if (ctx) {
                    if (this.charts.ingresos) this.charts.ingresos.destroy();
                    this.charts.ingresos = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.ingresos_mensuales.map(d => d.mes),
                            datasets: [{
                                label: 'Ingresos ($)',
                                data: data.ingresos_mensuales.map(d => parseFloat(d.total)),
                                borderColor: 'rgb(99, 102, 241)',
                                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }
            }

            // Servicios Por Tipo - Pie Chart
            if (data.servicios_por_tipo && data.servicios_por_tipo.length > 0) {
                const ctx = document.getElementById('serviciosChart');
                if (ctx) {
                    if (this.charts.servicios) this.charts.servicios.destroy();
                    this.charts.servicios = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: data.servicios_por_tipo.map(d => d.tipo),
                            datasets: [{
                                data: data.servicios_por_tipo.map(d => parseInt(d.cantidad)),
                                backgroundColor: [
                                    'rgb(59, 130, 246)',
                                    'rgb(16, 185, 129)',
                                    'rgb(251, 191, 36)',
                                    'rgb(239, 68, 68)',
                                    'rgb(139, 92, 246)',
                                    'rgb(236, 72, 153)',
                                    'rgb(20, 184, 166)',
                                    'rgb(249, 115, 22)',
                                    'rgb(156, 163, 175)',
                                    'rgb(14, 165, 233)'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'right' }
                            }
                        }
                    });
                }
            }

            // Usuarios Por Rol - Doughnut Chart
            if (data.usuarios_por_rol && data.usuarios_por_rol.length > 0) {
                const ctx = document.getElementById('usuariosChart');
                if (ctx) {
                    if (this.charts.usuarios) this.charts.usuarios.destroy();
                    this.charts.usuarios = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.usuarios_por_rol.map(d => d.rol),
                            datasets: [{
                                data: data.usuarios_por_rol.map(d => parseInt(d.cantidad)),
                                backgroundColor: [
                                    'rgb(139, 92, 246)',
                                    'rgb(59, 130, 246)',
                                    'rgb(16, 185, 129)',
                                    'rgb(251, 191, 36)'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'right' }
                            }
                        }
                    });
                }
            }

            // Solicitudes Por Estado - Bar Chart
            if (data.solicitudes_por_estado && data.solicitudes_por_estado.length > 0) {
                const ctx = document.getElementById('solicitudesChart');
                if (ctx) {
                    if (this.charts.solicitudes) this.charts.solicitudes.destroy();
                    this.charts.solicitudes = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.solicitudes_por_estado.map(d => d.estado),
                            datasets: [{
                                label: 'Cantidad',
                                data: data.solicitudes_por_estado.map(d => parseInt(d.cantidad)),
                                backgroundColor: [
                                    'rgb(251, 191, 36)',
                                    'rgb(59, 130, 246)',
                                    'rgb(16, 185, 129)',
                                    'rgb(239, 68, 68)',
                                    'rgb(156, 163, 175)'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }
            }

            // Tendencia Semanal - Line Chart
            if (data.tendencia_semanal && data.tendencia_semanal.length > 0) {
                const ctx = document.getElementById('tendenciaChart');
                if (ctx) {
                    if (this.charts.tendencia) this.charts.tendencia.destroy();
                    this.charts.tendencia = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.tendencia_semanal.map(d => d.fecha),
                            datasets: [{
                                label: 'Solicitudes',
                                data: data.tendencia_semanal.map(d => parseInt(d.cantidad)),
                                borderColor: 'rgb(16, 185, 129)',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }
            }
        },

        formatNumber(num) {
            return new Intl.NumberFormat('es-CO').format(num);
        },

        showMessage(msg, type = 'success') {
            this.message = msg;
            this.messageType = type;
            setTimeout(() => {
                this.message = '';
            }, 5000);
        },

        // Funciones de configuración de pagos
        async cargarConfigPagos() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(BASE_URL + '/api/admin/configuracion-pagos', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    const result = await response.json();
                    const data = result.data || result;
                    this.configPagos = {
                        banco_nombre: data.banco_nombre || '',
                        banco_cuenta: data.banco_cuenta || '',
                        banco_tipo_cuenta: data.banco_tipo_cuenta || 'Ahorros',
                        banco_titular: data.banco_titular || '',
                        qr_imagen_path: data.qr_imagen_path || '',
                        whatsapp_contacto: data.whatsapp_contacto || '',
                        instrucciones_transferencia: data.instrucciones_transferencia || ''
                    };
                }
            } catch (error) {
                console.error('Error cargando configuración de pagos:', error);
            }
        },

        async guardarConfigPagos() {
            this.guardandoConfig = true;
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(BASE_URL + '/api/admin/configuracion-pagos', {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.configPagos)
                });

                if (response.ok) {
                    this.showMessage('Configuración de pagos actualizada correctamente', 'success');
                } else {
                    const error = await response.json();
                    this.showMessage(error.message || 'Error al guardar configuración', 'error');
                }
            } catch (error) {
                console.error('Error guardando configuración:', error);
                this.showMessage('Error de conexión al guardar', 'error');
            } finally {
                this.guardandoConfig = false;
            }
        },

        async subirQR(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validar tipo de archivo
            if (!file.type.startsWith('image/')) {
                this.showMessage('Por favor seleccione una imagen válida', 'error');
                return;
            }

            // Validar tamaño (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                this.showMessage('La imagen no debe superar los 2MB', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('qr_imagen', file);

            try {
                const token = localStorage.getItem('token');
                const response = await fetch(BASE_URL + '/api/admin/configuracion-pagos/qr', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    body: formData
                });

                if (response.ok) {
                    const result = await response.json();
                    this.configPagos.qr_imagen_path = result.data?.qr_imagen_path || result.qr_imagen_path;
                    this.showMessage('QR subido correctamente', 'success');
                } else {
                    const error = await response.json();
                    this.showMessage(error.message || 'Error al subir QR', 'error');
                }
            } catch (error) {
                console.error('Error subiendo QR:', error);
                this.showMessage('Error de conexión al subir QR', 'error');
            }

            // Limpiar el input
            event.target.value = '';
        },

        logout() {
            localStorage.removeItem('token');
            localStorage.removeItem('usuario');
            window.location.href = BASE_URL + '/login';
        }
    }
}
</script>

</body>
</html>
