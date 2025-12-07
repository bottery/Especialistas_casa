<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Especialistas en Casa</title>
    <script>const BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50" x-data="usuariosApp()" x-init="init()">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="<?= url('/superadmin/dashboard.php') ?>" class="flex items-center space-x-2">
                        <svg class="h-8 w-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xl font-bold text-gray-900">Especialistas en Casa</span>
                    </a>
                    <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded-full">USUARIOS</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="<?= url('/superadmin/dashboard.php') ?>" class="text-gray-600 hover:text-gray-900">← Volver al Dashboard</a>
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
                <a href="<?= url('/superadmin/dashboard') ?>" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="<?= url('/superadmin/usuarios') ?>" class="flex items-center space-x-2 px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 font-medium whitespace-nowrap">
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
        
        <!-- Header con acciones -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Gestión de Usuarios</h1>
                <p class="text-gray-600 mt-1">Total: <span x-text="total"></span> usuarios</p>
            </div>
            <div class="flex space-x-3">
                <button @click="exportarUsuarios()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exportar CSV
                </button>
            </div>
        </div>

        <!-- Mensajes -->
        <div x-show="message" x-transition class="mb-6 p-4 rounded-lg shadow-lg" :class="messageType === 'success' ? 'bg-green-50 text-green-800 border-l-4 border-green-500' : 'bg-red-50 text-red-800 border-l-4 border-red-500'">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <p x-text="message" class="font-medium"></p>
                </div>
                <button @click="message = ''" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Búsqueda -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input 
                        type="text" 
                        x-model="filtros.search" 
                        @input="debounceSearch()"
                        placeholder="Nombre, apellido o email..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                </div>

                <!-- Filtro por Rol -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                    <select 
                        x-model="filtros.rol" 
                        @change="cargarUsuarios()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                        <option value="">Todos</option>
                        <option value="paciente">Paciente</option>
                        <option value="profesional">Profesional</option>
                        <option value="admin">Admin</option>
                        <option value="superadmin">SuperAdmin</option>
                    </select>
                </div>

                <!-- Filtro por Estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select 
                        x-model="filtros.estado" 
                        @change="cargarUsuarios()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                        <option value="">Todos</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="suspendido">Suspendido</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Acciones masivas -->
        <div x-show="seleccionados.length > 0" x-transition class="bg-indigo-50 border-l-4 border-indigo-500 p-4 mb-6 rounded-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="font-medium text-indigo-800" x-text="seleccionados.length + ' usuario(s) seleccionado(s)'"></span>
                </div>
                <div class="flex space-x-2">
                    <button @click="accionMasiva('aprobar')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Aprobar
                    </button>
                    <button @click="accionMasiva('suspender')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Suspender
                    </button>
                    <button @click="accionMasiva('eliminar')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Eliminar
                    </button>
                    <button @click="seleccionados = []" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input 
                                    type="checkbox" 
                                    @change="toggleTodos($event.target.checked)"
                                    class="w-4 h-4 text-indigo-600 rounded"
                                >
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registro</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-if="loading">
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex justify-center items-center">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                        <span class="ml-3 text-gray-600">Cargando usuarios...</span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        
                        <template x-if="!loading && usuarios.length === 0">
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    No se encontraron usuarios
                                </td>
                            </tr>
                        </template>

                        <template x-for="usuario in usuarios" :key="usuario.id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <input 
                                        type="checkbox" 
                                        :value="usuario.id"
                                        x-model="seleccionados"
                                        class="w-4 h-4 text-indigo-600 rounded"
                                    >
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <span class="text-indigo-600 font-semibold" x-text="(usuario.nombre[0] + usuario.apellido[0]).toUpperCase()"></span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900" x-text="usuario.nombre + ' ' + usuario.apellido"></div>
                                            <div class="text-sm text-gray-500" x-text="usuario.telefono || 'Sin teléfono'"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="usuario.email"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                          :class="{
                                              'bg-purple-100 text-purple-800': usuario.rol === 'superadmin',
                                              'bg-blue-100 text-blue-800': usuario.rol === 'admin',
                                              'bg-green-100 text-green-800': usuario.rol === 'profesional',
                                              'bg-gray-100 text-gray-800': usuario.rol === 'paciente'
                                          }"
                                          x-text="usuario.rol"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                          :class="{
                                              'bg-green-100 text-green-800': usuario.estado === 'activo',
                                              'bg-red-100 text-red-800': usuario.estado === 'inactivo',
                                              'bg-yellow-100 text-yellow-800': usuario.estado === 'pendiente',
                                              'bg-gray-100 text-gray-800': usuario.estado === 'suspendido'
                                          }"
                                          x-text="usuario.estado"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(usuario.created_at)"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="verDetalle(usuario.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Ver</button>
                                    <button @click="editarEstado(usuario.id, usuario.estado)" class="text-yellow-600 hover:text-yellow-900 mr-3">Editar</button>
                                    <button @click="eliminarUsuario(usuario.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
                <div class="text-sm text-gray-700">
                    Mostrando <span class="font-medium" x-text="usuarios.length"></span> de <span class="font-medium" x-text="total"></span> usuarios
                </div>
                <div class="flex space-x-2">
                    <button 
                        @click="paginaAnterior()" 
                        :disabled="offset === 0"
                        :class="offset === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200'"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium bg-white"
                    >
                        Anterior
                    </button>
                    <button 
                        @click="paginaSiguiente()" 
                        :disabled="offset + limit >= total"
                        :class="offset + limit >= total ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200'"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium bg-white"
                    >
                        Siguiente
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal de Detalle -->
    <div x-show="modalDetalle" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="modalDetalle = false" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-2xl font-bold text-gray-900">Detalle del Usuario</h3>
                        <button @click="modalDetalle = false" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div x-show="loadingDetalle" class="flex justify-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                    </div>
                    
                    <div x-show="!loadingDetalle && detalle" class="space-y-6">
                        <!-- Info básica -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-lg mb-3">Información Personal</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div><span class="font-medium">Nombre:</span> <span x-text="detalle?.usuario?.nombre + ' ' + detalle?.usuario?.apellido"></span></div>
                                <div><span class="font-medium">Email:</span> <span x-text="detalle?.usuario?.email"></span></div>
                                <div><span class="font-medium">Teléfono:</span> <span x-text="detalle?.usuario?.telefono || 'N/A'"></span></div>
                                <div><span class="font-medium">Rol:</span> <span x-text="detalle?.usuario?.rol"></span></div>
                            </div>
                        </div>

                        <!-- Solicitudes recientes -->
                        <div>
                            <h4 class="font-semibold text-lg mb-3">Últimas Solicitudes</h4>
                            <div class="bg-gray-50 rounded-lg p-4 max-h-64 overflow-y-auto">
                                <template x-if="detalle?.solicitudes && detalle.solicitudes.length > 0">
                                    <div class="space-y-2">
                                        <template x-for="sol in detalle.solicitudes" :key="sol.id">
                                            <div class="flex justify-between items-center py-2 border-b">
                                                <div>
                                                    <span class="font-medium" x-text="sol.servicio_nombre"></span>
                                                    <span class="text-sm text-gray-500 ml-2" x-text="formatDate(sol.created_at)"></span>
                                                </div>
                                                <span class="px-2 py-1 text-xs rounded-full" 
                                                      :class="{
                                                          'bg-green-100 text-green-800': sol.estado === 'completada',
                                                          'bg-yellow-100 text-yellow-800': sol.estado === 'pendiente',
                                                          'bg-blue-100 text-blue-800': sol.estado === 'en_progreso'
                                                      }"
                                                      x-text="sol.estado"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!detalle?.solicitudes || detalle.solicitudes.length === 0">
                                    <p class="text-gray-500 text-center py-4">No hay solicitudes</p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="modalDetalle = false" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
window.usuariosApp = function() {
    return {
        loading: false,
        loadingDetalle: false,
        usuarios: [],
        total: 0,
        message: '',
        messageType: 'success',
        seleccionados: [],
        modalDetalle: false,
        detalle: null,
        filtros: {
            search: '',
            rol: '',
            estado: ''
        },
        limit: 50,
        offset: 0,
        searchTimeout: null,

        async init() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = BASE_URL + '/login';
                return;
            }
            await this.cargarUsuarios();
        },

        async cargarUsuarios() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                const params = new URLSearchParams({
                    search: this.filtros.search,
                    rol: this.filtros.rol,
                    estado: this.filtros.estado,
                    limit: this.limit,
                    offset: this.offset
                });

                const response = await fetch(`${BASE_URL}/api/superadmin/usuarios?${params}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.usuarios = data.usuarios || [];
                    this.total = data.total || 0;
                } else {
                    this.showMessage('Error al cargar usuarios', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showMessage('Error al cargar usuarios', 'error');
            } finally {
                this.loading = false;
            }
        },

        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.offset = 0;
                this.cargarUsuarios();
            }, 500);
        },

        async accionMasiva(accion) {
            if (!confirm(`¿Estás seguro de ${accion} ${this.seleccionados.length} usuario(s)?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('token');
                const response = await fetch(BASE_URL + '/api/superadmin/acciones-masivas', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        accion: accion,
                        ids: this.seleccionados
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    this.showMessage(data.message, 'success');
                    this.seleccionados = [];
                    await this.cargarUsuarios();
                } else {
                    this.showMessage('Error al realizar la acción', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showMessage('Error al realizar la acción', 'error');
            }
        },

        async verDetalle(id) {
            this.modalDetalle = true;
            this.loadingDetalle = true;
            
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`${BASE_URL}/api/superadmin/usuario-detalle?id=${id}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.detalle = data.usuario;
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loadingDetalle = false;
            }
        },

        async editarEstado(id, estadoActual) {
            const nuevoEstado = prompt(`Estado actual: ${estadoActual}\nNuevo estado (activo/inactivo/pendiente/suspendido):`, estadoActual);
            if (!nuevoEstado || nuevoEstado === estadoActual) return;

            try {
                const token = localStorage.getItem('token');
                const response = await fetch(BASE_URL + '/api/superadmin/usuarios/estado', {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id, estado: nuevoEstado })
                });

                if (response.ok) {
                    this.showMessage('Estado actualizado', 'success');
                    await this.cargarUsuarios();
                } else {
                    this.showMessage('Error al actualizar estado', 'error');
                }
            } catch (error) {
                this.showMessage('Error al actualizar estado', 'error');
            }
        },

        async eliminarUsuario(id) {
            if (!confirm('¿Estás seguro de eliminar este usuario?')) return;

            try {
                const token = localStorage.getItem('token');
                const response = await fetch(BASE_URL + '/api/superadmin/usuarios/delete', {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });

                if (response.ok) {
                    this.showMessage('Usuario eliminado', 'success');
                    await this.cargarUsuarios();
                } else {
                    const data = await response.json();
                    this.showMessage(data.message || 'Error al eliminar', 'error');
                }
            } catch (error) {
                this.showMessage('Error al eliminar usuario', 'error');
            }
        },

        exportarUsuarios() {
            const params = new URLSearchParams({
                search: this.filtros.search,
                rol: this.filtros.rol,
                estado: this.filtros.estado
            });
            window.location.href = `${BASE_URL}/api/superadmin/exportar-usuarios?${params}`;
        },

        toggleTodos(checked) {
            if (checked) {
                this.seleccionados = this.usuarios.map(u => u.id);
            } else {
                this.seleccionados = [];
            }
        },

        paginaAnterior() {
            if (this.offset >= this.limit) {
                this.offset -= this.limit;
                this.cargarUsuarios();
            }
        },

        paginaSiguiente() {
            if (this.offset + this.limit < this.total) {
                this.offset += this.limit;
                this.cargarUsuarios();
            }
        },

        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('es-CO', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },

        showMessage(msg, type = 'success') {
            this.message = msg;
            this.messageType = type;
            setTimeout(() => {
                this.message = '';
            }, 5000);
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
