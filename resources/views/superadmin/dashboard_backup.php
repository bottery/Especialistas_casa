<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Super Admin - VitaHome</title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/images/vitahome-icon.svg">
    <script>const BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';</script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; color: #333; }
        header { background: #fff; border-bottom: 1px solid #ddd; padding: 15px 20px; }
        nav { background: #fff; border-bottom: 1px solid #ddd; padding: 10px 20px; display: flex; gap: 20px; }
        nav a { text-decoration: none; color: #333; padding: 8px 12px; border: 1px solid #ddd; }
        nav a:hover { background: #f0f0f0; }
        main { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .stat-card { background: #fff; border: 1px solid #ddd; padding: 20px; text-align: center; }
        .stat-card h3 { font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 10px; }
        .stat-card .number { font-size: 32px; font-weight: bold; color: #000; }
        .section { background: #fff; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; }
        .section h2 { font-size: 18px; margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        table th { background: #f9f9f9; border: 1px solid #ddd; padding: 12px; text-align: left; font-weight: bold; }
        table td { border: 1px solid #ddd; padding: 12px; }
        table tr:nth-child(even) { background: #f9f9f9; }
        .btn { display: inline-block; padding: 8px 16px; background: #333; color: #fff; text-decoration: none; border: none; cursor: pointer; font-size: 14px; }
        .btn:hover { background: #555; }
        .loading { text-align: center; padding: 20px; }
        .error { background: #fee; color: #c33; border: 1px solid #fcc; padding: 15px; margin-bottom: 20px; }
        .success { background: #efe; color: #3c3; border: 1px solid #cfc; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 style="font-size: 24px;">VitaHome - Super Admin</h1>
            <div>
                <span id="usuario-nombre">Admin</span>
                <button onclick="logout()" class="btn" style="margin-left: 20px;">Salir</button>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav>
        <a href="<?= url('/superadmin/dashboard') ?>">Dashboard</a>
        <a href="<?= url('/superadmin/usuarios') ?>">Usuarios</a>
        <a href="<?= url('/superadmin/finanzas') ?>">Finanzas</a>
        <a href="<?= url('/superadmin/seguridad') ?>">Seguridad</a>
        <a href="<?= url('/superadmin/configuracion') ?>">Configuración</a>
    </nav>

    <main>
        <!-- Mensajes -->
        <div id="message-container"></div>

        <!-- Cargando -->
        <div id="loading" class="loading" style="display: none;">Cargando datos...</div>

        <!-- Contenido -->
        <div id="content" style="display: none;">
            
            <!-- Estadísticas principales -->
            <div class="grid">
                <div class="stat-card">
                    <h3>Total Usuarios</h3>
                    <div class="number" id="stat-usuarios">0</div>
                </div>
                <div class="stat-card">
                    <h3>Servicios Activos</h3>
                    <div class="number" id="stat-servicios">0</div>
                </div>
                <div class="stat-card">
                    <h3>Solicitudes Pendientes</h3>
                    <div class="number" id="stat-pendientes">0</div>
                </div>
                <div class="stat-card">
                    <h3>Ingresos Mes</h3>
                    <div class="number" id="stat-ingresos">$0</div>
                </div>
                <div class="stat-card">
                    <h3>Solicitudes Completadas</h3>
                    <div class="number" id="stat-completadas">0</div>
                </div>
                <div class="stat-card">
                    <h3>Pagos Hoy</h3>
                    <div class="number" id="stat-pagos">0</div>
                </div>
                <div class="stat-card">
                    <h3>Nuevos Usuarios Hoy</h3>
                    <div class="number" id="stat-nuevos">0</div>
                </div>
                <div class="stat-card">
                    <h3>Profesionales Activos</h3>
                    <div class="number" id="stat-profesionales">0</div>
                </div>
            </div>

            <!-- Configuración de Pagos -->
            <div class="section">
                <h2>Configuración de Pagos por Transferencia</h2>
                <table>
                    <tr>
                        <th>Campo</th>
                        <th>Valor</th>
                        <th>Acción</th>
                    </tr>
                    <tr>
                        <td>Banco</td>
                        <td><input type="text" id="banco_nombre" style="width: 100%; padding: 5px; font-size: 14px;"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Tipo de Cuenta</td>
                        <td>
                            <select id="banco_tipo_cuenta" style="width: 100%; padding: 5px; font-size: 14px;">
                                <option value="Ahorros">Ahorros</option>
                                <option value="Corriente">Corriente</option>
                            </select>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Número de Cuenta</td>
                        <td><input type="text" id="banco_cuenta" style="width: 100%; padding: 5px; font-size: 14px;"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Titular de Cuenta</td>
                        <td><input type="text" id="banco_titular" style="width: 100%; padding: 5px; font-size: 14px;"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>WhatsApp de Contacto</td>
                        <td><input type="text" id="whatsapp_contacto" style="width: 100%; padding: 5px; font-size: 14px;"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <label>Instrucciones de Transferencia:</label><br><br>
                            <textarea id="instrucciones_transferencia" style="width: 100%; height: 100px; padding: 10px; font-size: 14px;"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <button onclick="guardarConfigPagos()" class="btn">Guardar Configuración</button>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </main>

<script>
    const BASE_URL_VAR = BASE_URL;

    function showMessage(msg, type = 'success') {
        const container = document.getElementById('message-container');
        const div = document.createElement('div');
        div.className = type === 'success' ? 'success' : 'error';
        div.textContent = msg;
        container.innerHTML = '';
        container.appendChild(div);
        setTimeout(() => div.remove(), 5000);
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('es-CO').format(num);
    }

    async function loadDashboard() {
        try {
            document.getElementById('loading').style.display = 'block';
            const token = localStorage.getItem('token');
            
            // Verificar autenticación
            if (!token) {
                window.location.href = BASE_URL_VAR + '/login';
                return;
            }

            const userData = JSON.parse(localStorage.getItem('usuario') || '{}');
            if (userData.rol !== 'superadmin') {
                window.location.href = BASE_URL_VAR + '/login';
                return;
            }

            document.getElementById('usuario-nombre').textContent = userData.nombre || 'Admin';

            // Cargar estadísticas
            const response = await fetch(BASE_URL_VAR + '/api/superadmin/dashboard', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            const stats = data.data || data;

            // Actualizar estadísticas
            document.getElementById('stat-usuarios').textContent = stats.totalUsuarios || 0;
            document.getElementById('stat-servicios').textContent = stats.serviciosActivos || 0;
            document.getElementById('stat-pendientes').textContent = stats.solicitudesPendientes || 0;
            document.getElementById('stat-ingresos').textContent = '$' + formatNumber(stats.ingresosMes || 0);
            document.getElementById('stat-completadas').textContent = stats.solicitudesCompletadas || 0;
            document.getElementById('stat-pagos').textContent = stats.pagosHoy || 0;
            document.getElementById('stat-nuevos').textContent = stats.nuevosUsuariosHoy || 0;
            document.getElementById('stat-profesionales').textContent = stats.profesionalesActivos || 0;

            // Cargar configuración de pagos
            await cargarConfigPagos(token);

            document.getElementById('loading').style.display = 'none';
            document.getElementById('content').style.display = 'block';

        } catch (error) {
            console.error('Error cargando dashboard:', error);
            showMessage('Error al cargar dashboard: ' + error.message, 'error');
            document.getElementById('loading').style.display = 'none';
        }
    }

    async function cargarConfigPagos(token) {
        try {
            const response = await fetch(BASE_URL_VAR + '/api/admin/configuracion-pagos', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                const data = result.data || result;
                
                document.getElementById('banco_nombre').value = data.banco_nombre || '';
                document.getElementById('banco_tipo_cuenta').value = data.banco_tipo_cuenta || 'Ahorros';
                document.getElementById('banco_cuenta').value = data.banco_cuenta || '';
                document.getElementById('banco_titular').value = data.banco_titular || '';
                document.getElementById('whatsapp_contacto').value = data.whatsapp_contacto || '';
                document.getElementById('instrucciones_transferencia').value = data.instrucciones_transferencia || '';
            }
        } catch (error) {
            console.error('Error cargando config de pagos:', error);
        }
    }

    async function guardarConfigPagos() {
        try {
            const token = localStorage.getItem('token');
            
            const config = {
                banco_nombre: document.getElementById('banco_nombre').value,
                banco_tipo_cuenta: document.getElementById('banco_tipo_cuenta').value,
                banco_cuenta: document.getElementById('banco_cuenta').value,
                banco_titular: document.getElementById('banco_titular').value,
                whatsapp_contacto: document.getElementById('whatsapp_contacto').value,
                instrucciones_transferencia: document.getElementById('instrucciones_transferencia').value
            };

            const response = await fetch(BASE_URL_VAR + '/api/admin/configuracion-pagos', {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(config)
            });

            if (response.ok) {
                showMessage('Configuración guardada correctamente', 'success');
            } else {
                const error = await response.json();
                showMessage(error.message || 'Error al guardar', 'error');
            }
        } catch (error) {
            console.error('Error guardando config:', error);
            showMessage('Error al guardar: ' + error.message, 'error');
        }
    }

    function logout() {
        localStorage.removeItem('token');
        localStorage.removeItem('usuario');
        window.location.href = BASE_URL_VAR + '/login';
    }

    // Cargar dashboard al iniciar
    document.addEventListener('DOMContentLoaded', loadDashboard);
</script>

</body>
</html>
        <div x-show="diagnosticPanel" x-transition class="mb-6 bg-gray-900 text-gray-100 rounded-lg shadow-2xl border border-gray-700 p-6 max-h-96 overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-yellow-400">🔍 Panel de Diagnóstico</h3>
                <button @click="diagnosticPanel = false" class="text-gray-400 hover:text-gray-200">✕</button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 text-sm">
                <div class="bg-gray-800 p-3 rounded border border-gray-700">
                    <p class="text-gray-400 text-xs uppercase">Total Logs</p>
                    <p class="text-white font-bold text-lg" x-text="errorLog.logs.length">0</p>
                </div>
                <div class="bg-gray-800 p-3 rounded border border-gray-700">
                    <p class="text-gray-400 text-xs uppercase">Errores</p>
                    <p class="text-red-400 font-bold text-lg" x-text="errorLog.logs.filter(l => l.level === 'ERROR').length">0</p>
                </div>
                <div class="bg-gray-800 p-3 rounded border border-gray-700">
                    <p class="text-gray-400 text-xs uppercase">Advertencias</p>
                    <p class="text-yellow-400 font-bold text-lg" x-text="errorLog.logs.filter(l => l.level === 'WARN').length">0</p>
                </div>
            </div>
            
            <div class="bg-gray-800 p-3 rounded border border-gray-700 mb-4">
                <p class="text-gray-400 text-xs uppercase mb-2">Últimos Logs</p>
                <div class="space-y-1 font-mono text-xs max-h-40 overflow-y-auto">
                    <template x-for="log in errorLog.logs.slice(-10)" :key="log.timestamp">
                        <div :class="log.level === 'ERROR' ? 'text-red-400' : log.level === 'WARN' ? 'text-yellow-400' : 'text-blue-400'">
                            <span x-text="`[${log.level}] ${new Date(log.timestamp).toLocaleTimeString()}: ${log.message}`"></span>
                        </div>
                    </template>
                </div>
            </div>
            
            <div class="flex gap-2">
                <button @click="errorLog.logs = []" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-medium">
                    Limpiar Logs
                </button>
                <button @click="errorLog.sendToServer()" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-medium">
                    Enviar al Servidor
                </button>
                <button @click="downloadLogs()" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-sm font-medium">
                    Descargar JSON
                </button>
            </div>
        </div>
        
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
                                    <div class="mt-2 flex gap-2 justify-center">
                                        <label class="cursor-pointer bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-1 px-3 rounded text-sm inline-flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                            </svg>
                                            Cambiar
                                            <input type="file" class="hidden" accept="image/*" @change="subirQR($event)">
                                        </label>
                                        <button @click="eliminarQR()" class="bg-red-50 hover:bg-red-100 text-red-600 font-medium py-1 px-3 rounded text-sm inline-flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!configPagos.qr_imagen_path">
                                <div class="bg-gray-200 w-40 h-40 mx-auto rounded-lg flex items-center justify-center">
                                    <span class="text-gray-500 text-sm">Sin QR</span>
                                </div>
                            </template>
                            <div class="mt-3">
                                <template x-if="!configPagos.qr_imagen_path">
                                    <label class="cursor-pointer bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg inline-flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Subir nuevo QR
                                        <input type="file" class="hidden" accept="image/*" @change="subirQR($event)">
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

<script>
// Sistema de logging y captura de errores
window.errorLog = {
    logs: [],
    logEvent(level, message, details = {}) {
        const timestamp = new Date().toISOString();
        const logEntry = {
            timestamp,
            level,
            message,
            details,
            userAgent: navigator.userAgent,
            url: window.location.href
        };
        
        this.logs.push(logEntry);
        
        // Limitar a últimos 50 logs
        if (this.logs.length > 50) {
            this.logs.shift();
        }
        
        // Mostrar en consola con estilo
        const style = {
            'ERROR': 'background: #fee; color: #c33; font-weight: bold; padding: 5px 10px; border-radius: 3px;',
            'WARN': 'background: #ffe; color: #663; font-weight: bold; padding: 5px 10px; border-radius: 3px;',
            'INFO': 'background: #eef; color: #336; font-weight: bold; padding: 5px 10px; border-radius: 3px;',
            'DEBUG': 'background: #f0f0f0; color: #666; font-weight: normal; padding: 5px 10px; border-radius: 3px;'
        };
        
        console.log(
            `%c[${level}] ${timestamp}`,
            style[level] || 'color: #666;'
        );
        console.log(`   ${message}`, details);
    },
    
    error(message, details) { this.logEvent('ERROR', message, details); },
    warn(message, details) { this.logEvent('WARN', message, details); },
    info(message, details) { this.logEvent('INFO', message, details); },
    debug(message, details) { this.logEvent('DEBUG', message, details); },
    
    getLogs() { return this.logs; },
    
    async sendToServer() {
        try {
            const token = localStorage.getItem('token');
            await fetch(BASE_URL + '/api/admin/error-logs', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ logs: this.logs })
            });
        } catch (e) {
            console.error('No se pudieron enviar logs al servidor:', e);
        }
    }
};

// Capturar errores globales
window.addEventListener('error', (event) => {
    window.errorLog.error('Error global no capturado', {
        message: event.message,
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno,
        stack: event.error?.stack
    });
});

window.addEventListener('unhandledrejection', (event) => {
    window.errorLog.error('Promise rechazada no manejada', {
        reason: event.reason?.message || String(event.reason),
        stack: event.reason?.stack
    });
});

// Atajos de teclado para diagnóstico
document.addEventListener('keydown', (e) => {
    // Ctrl+Shift+D para abrir/cerrar panel de diagnóstico
    if (e.ctrlKey && e.shiftKey && e.key === 'D') {
        e.preventDefault();
        const app = document.querySelector('[x-data]')?.__Alpine?.data;
        if (app) {
            app.diagnosticPanel = !app.diagnosticPanel;
        }
    }
});

window.dashboardApp = function() {
    return {
        loading: false,
        message: '',
        messageType: 'success',
        currentTab: 'dashboard',
        diagnosticPanel: false,
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
        retryCount: 0,
        maxRetries: 3,
        
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
            try {
                window.errorLog.info('Iniciando dashboard...');
                
                // Verificar autenticación
                const token = localStorage.getItem('token');
                if (!token) {
                    window.errorLog.warn('No hay token de autenticación');
                    window.location.href = BASE_URL + '/login';
                    return;
                }

                const userData = JSON.parse(localStorage.getItem('usuario') || '{}');
                this.usuario = userData;
                window.errorLog.info('Usuario cargado', { usuario: userData.nombre, rol: userData.rol });

                // Verificar rol de superadmin
                if (userData.rol !== 'superadmin') {
                    window.errorLog.error('Acceso denegado: rol insuficiente', { rol: userData.rol });
                    this.showMessage('Acceso denegado: solo superadmin', 'error');
                    setTimeout(() => window.location.href = BASE_URL + '/login', 2000);
                    return;
                }

                // Cargar datos
                window.errorLog.info('Cargando datos del dashboard...');
                await this.loadDashboardData();
                
                window.errorLog.info('Cargando datos de gráficos...');
                await this.loadChartData();
                
                window.errorLog.info('Cargando configuración de pagos...');
                await this.cargarConfigPagos();
                
                window.errorLog.info('Dashboard inicializado correctamente');

            } catch (error) {
                window.errorLog.error('Error fatal en init', {
                    message: error.message,
                    stack: error.stack
                });
                this.showMessage('Error inicializando dashboard', 'error');
            }
        },

        async loadDashboardData() {
            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                window.errorLog.debug('Obteniendo datos del dashboard...');
                
                const response = await fetch(BASE_URL + '/api/superadmin/dashboard', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    cache: 'no-cache'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                window.errorLog.debug('Respuesta recibida', { response: data });
                
                // Manejar diferentes estructuras de respuesta
                if (data.stats) {
                    this.stats = data.stats;
                } else if (data.data && typeof data.data === 'object' && !Array.isArray(data.data)) {
                    this.stats = data.data;
                } else if (data.totalUsuarios !== undefined) {
                    this.stats = data;
                }
                
                if (data.actividad_reciente) {
                    this.actividadReciente = data.actividad_reciente;
                }

                // Asegurar que los números sean válidos
                Object.keys(this.stats).forEach(key => {
                    if (typeof this.stats[key] !== 'number') {
                        this.stats[key] = parseInt(this.stats[key]) || 0;
                    }
                });

                window.errorLog.info('Dashboard data cargado exitosamente', this.stats);
                this.loading = false;
                
            } catch (error) {
                window.errorLog.error('Error al cargar datos del dashboard', {
                    message: error.message,
                    stack: error.stack,
                    retryCount: this.retryCount,
                    maxRetries: this.maxRetries
                });
                
                if (this.retryCount < this.maxRetries) {
                    this.retryCount++;
                    window.errorLog.warn(`Reintentando... intento ${this.retryCount}/${this.maxRetries}`);
                    setTimeout(() => this.loadDashboardData(), 1000);
                } else {
                    window.errorLog.error('Máximo de reintentos alcanzado para dashboard');
                    this.showMessage('Error al cargar datos del dashboard', 'error');
                    this.loading = false;
                }
            }
        },

        async loadChartData() {
            try {
                const token = localStorage.getItem('token');
                window.errorLog.debug('Obteniendo datos de gráficos...');
                
                const response = await fetch(BASE_URL + '/api/analytics/charts', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    cache: 'no-cache'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();
                window.errorLog.debug('Datos de gráficos recibidos', result);
                
                const data = result.data || result;
                
                // Esperar a que Alpine renderice el DOM y los canvas sean visibles
                setTimeout(() => {
                    try {
                        window.errorLog.debug('Iniciando renderizado de gráficos...');
                        this.renderCharts(data);
                        window.errorLog.info('Gráficos renderizados exitosamente');
                    } catch (error) {
                        window.errorLog.error('Error renderizando gráficos', {
                            message: error.message,
                            stack: error.stack
                        });
                    }
                }, 300);
                
            } catch (error) {
                window.errorLog.error('Error cargando datos de gráficos', {
                    message: error.message,
                    stack: error.stack
                });
                // No mostrar error al usuario, los gráficos son secundarios
            }
        },

        renderCharts(data) {
            try {
                window.errorLog.debug('Limpiando gráficos anteriores...');
                
                // Destruir gráficos existentes
                Object.values(this.charts).forEach(chart => {
                    if (chart && typeof chart.destroy === 'function') {
                        try { chart.destroy(); } catch (e) {}
                    }
                });
                this.charts = {};

                // Helper para crear gráficos de forma segura
                const createChart = (canvasId, chartConfig) => {
                    try {
                        const canvas = document.getElementById(canvasId);
                        if (!canvas) {
                            window.errorLog.warn(`Canvas ${canvasId} no encontrado en DOM`);
                            return null;
                        }
                        
                        // Verificar que el canvas está visible
                        const rect = canvas.getBoundingClientRect();
                        if (rect.width === 0 || rect.height === 0) {
                            window.errorLog.warn(`Canvas ${canvasId} tiene tamaño 0x0`, {
                                width: rect.width,
                                height: rect.height,
                                display: window.getComputedStyle(canvas).display
                            });
                            return null;
                        }
                        
                        // Obtener contexto 2D
                        const ctx = canvas.getContext('2d');
                        if (!ctx) {
                            window.errorLog.error(`No se pudo obtener contexto 2D del canvas ${canvasId}`);
                            return null;
                        }
                        
                        window.errorLog.debug(`Creando gráfico ${canvasId}`, {
                            canvasSize: `${rect.width}x${rect.height}`,
                            contextType: ctx.constructor.name
                        });
                        
                        return new Chart(ctx, chartConfig);
                    } catch (e) {
                        window.errorLog.error(`Error creando gráfico ${canvasId}`, {
                            message: e.message,
                            stack: e.stack
                        });
                        return null;
                    }
                };

                // Ingresos Mensuales - Line Chart
                if (data.ingresos_mensuales && data.ingresos_mensuales.length > 0) {
                    this.charts.ingresos = createChart('ingresosChart', {
                        type: 'line',
                        data: {
                            labels: data.ingresos_mensuales.map(d => d.mes || d.fecha || ''),
                            datasets: [{
                                label: 'Ingresos ($)',
                                data: data.ingresos_mensuales.map(d => {
                                    const val = parseFloat(d.total || d.cantidad || 0);
                                    return isNaN(val) ? 0 : val;
                                }),
                                borderColor: 'rgb(99, 102, 241)',
                                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: 'rgb(99, 102, 241)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: true, labels: { color: '#666', font: { size: 12 } } },
                                filler: { propagate: true }
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { color: '#666' } },
                                x: { ticks: { color: '#666' } }
                            }
                        }
                    });
                }

                // Servicios Por Tipo - Doughnut Chart
                if (data.servicios_por_tipo && data.servicios_por_tipo.length > 0) {
                    this.charts.servicios = createChart('serviciosChart', {
                        type: 'doughnut',
                        data: {
                            labels: data.servicios_por_tipo.map(d => d.tipo || d.nombre || ''),
                            datasets: [{
                                data: data.servicios_por_tipo.map(d => {
                                    const val = parseInt(d.cantidad || 0);
                                    return isNaN(val) ? 0 : val;
                                }),
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
                                ],
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { 
                                    position: 'right',
                                    labels: { color: '#666', font: { size: 12 }, padding: 15 }
                                }
                            }
                        }
                    });
                }

                // Usuarios Por Rol - Doughnut Chart
                if (data.usuarios_por_rol && data.usuarios_por_rol.length > 0) {
                    this.charts.usuarios = createChart('usuariosChart', {
                        type: 'doughnut',
                        data: {
                            labels: data.usuarios_por_rol.map(d => d.rol || d.nombre || ''),
                            datasets: [{
                                data: data.usuarios_por_rol.map(d => {
                                    const val = parseInt(d.cantidad || 0);
                                    return isNaN(val) ? 0 : val;
                                }),
                                backgroundColor: [
                                    'rgb(139, 92, 246)',
                                    'rgb(59, 130, 246)',
                                    'rgb(16, 185, 129)',
                                    'rgb(251, 191, 36)',
                                    'rgb(239, 68, 68)'
                                ],
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { 
                                    position: 'right',
                                    labels: { color: '#666', font: { size: 12 }, padding: 15 }
                                }
                            }
                        }
                    });
                }

                // Solicitudes Por Estado - Bar Chart
                if (data.solicitudes_por_estado && data.solicitudes_por_estado.length > 0) {
                    this.charts.solicitudes = createChart('solicitudesChart', {
                        type: 'bar',
                        data: {
                            labels: data.solicitudes_por_estado.map(d => d.estado || d.nombre || ''),
                            datasets: [{
                                label: 'Cantidad',
                                data: data.solicitudes_por_estado.map(d => {
                                    const val = parseInt(d.cantidad || 0);
                                    return isNaN(val) ? 0 : val;
                                }),
                                backgroundColor: [
                                    'rgb(251, 191, 36)',
                                    'rgb(59, 130, 246)',
                                    'rgb(16, 185, 129)',
                                    'rgb(239, 68, 68)',
                                    'rgb(156, 163, 175)'
                                ],
                                borderRadius: 4,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { color: '#666' } },
                                x: { ticks: { color: '#666' } }
                            }
                        }
                    });
                }

                // Tendencia Semanal - Line Chart
                if (data.tendencia_semanal && data.tendencia_semanal.length > 0) {
                    this.charts.tendencia = createChart('tendenciaChart', {
                        type: 'line',
                        data: {
                            labels: data.tendencia_semanal.map(d => d.fecha || d.dia || ''),
                            datasets: [{
                                label: 'Solicitudes',
                                data: data.tendencia_semanal.map(d => {
                                    const val = parseInt(d.cantidad || 0);
                                    return isNaN(val) ? 0 : val;
                                }),
                                borderColor: 'rgb(16, 185, 129)',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: 'rgb(16, 185, 129)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: true, labels: { color: '#666', font: { size: 12 } } }
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { color: '#666' } },
                                x: { ticks: { color: '#666' } }
                            }
                        }
                    });
                }

            } catch (error) {
                console.error('Error general en renderCharts:', error);
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
                window.errorLog.debug('Cargando configuración de pagos...');
                
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
                    window.errorLog.info('Configuración de pagos cargada', {
                        banco: this.configPagos.banco_nombre,
                        tieneQR: !!this.configPagos.qr_imagen_path
                    });
                } else {
                    window.errorLog.warn('Configuración de pagos no disponible', { status: response.status });
                }
            } catch (error) {
                window.errorLog.error('Error cargando configuración de pagos', {
                    message: error.message,
                    stack: error.stack
                });
            }
        },

        async guardarConfigPagos() {
            this.guardandoConfig = true;
            try {
                const token = localStorage.getItem('token');
                window.errorLog.debug('Guardando configuración de pagos...');
                
                const response = await fetch(BASE_URL + '/api/admin/configuracion-pagos', {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.configPagos)
                });

                if (response.ok) {
                    window.errorLog.info('Configuración de pagos guardada exitosamente');
                    this.showMessage('✅ Configuración de pagos actualizada correctamente', 'success');
                } else {
                    const error = await response.json();
                    window.errorLog.error('Error al guardar configuración de pagos', error);
                    this.showMessage(error.message || 'Error al guardar configuración', 'error');
                }
            } catch (error) {
                window.errorLog.error('Error de conexión al guardar configuración de pagos', {
                    message: error.message,
                    stack: error.stack
                });
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

            // Validar tamaño (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                this.showMessage('La imagen no debe superar los 5MB', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('qr_imagen', file);

            try {
                const token = localStorage.getItem('token');
                const response = await fetch(BASE_URL + '/api/admin/subir-qr', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    body: formData
                });

                if (response.ok) {
                    const result = await response.json();
                    this.configPagos.qr_imagen_path = result.data?.qr_imagen_path || result.qr_imagen_path;
                    this.showMessage('✅ QR subido correctamente', 'success');
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

        async eliminarQR() {
            if (!confirm('¿Está seguro de que desea eliminar el QR actual?')) return;

            try {
                const token = localStorage.getItem('token');
                const response = await fetch(BASE_URL + '/api/admin/configuracion-pagos/qr', {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                if (response.ok) {
                    this.configPagos.qr_imagen_path = '';
                    this.showMessage('✅ QR eliminado correctamente', 'success');
                } else {
                    const error = await response.json();
                    this.showMessage(error.message || 'Error al eliminar QR', 'error');
                }
            } catch (error) {
                console.error('Error eliminando QR:', error);
                this.showMessage('Error de conexión al eliminar QR', 'error');
            }
        },

        logout() {
            localStorage.removeItem('token');
            localStorage.removeItem('usuario');
            window.location.href = BASE_URL + '/login';
        },

        downloadLogs() {
            try {
                const logsJson = JSON.stringify(window.errorLog.logs, null, 2);
                const blob = new Blob([logsJson], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `dashboard-logs-${new Date().toISOString().split('T')[0]}.json`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                this.showMessage('✅ Logs descargados correctamente', 'success');
            } catch (error) {
                window.errorLog.error('Error descargando logs', {
                    message: error.message,
                    stack: error.stack
                });
                this.showMessage('Error al descargar logs', 'error');
            }
        }
    }
}
</script>

</body>
</html>
