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
            .stat-card.warning { border-color: #f5c26b; background: linear-gradient(180deg,#fff8ed,#fff); }
            .stat-card.warning .number { color: #b85b00; }
            .payments-legend { font-size: 13px; color: #555; margin: 10px 0 20px; }
            .payments-legend strong { color: #222; }
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
        <pre id="raw-json" style="display:none; background:#fff; border:1px solid #ddd; padding:10px; margin:10px 0; white-space:pre-wrap; max-height:300px; overflow:auto;"></pre>

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
                    <h3>Ingresos Pendientes</h3>
                    <div class="number" id="stat-ingresos-pendientes">$0</div>
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

                <div class="payments-legend" id="payments-legend">
                    <strong>Aclaración:</strong> <span id="legend-aprobados">Aprobados = en cuenta</span> · <span id="legend-pendientes">Pendientes = en espera de aprobación por el admin</span>
                </div>

            <!-- Configuración de Pagos -->
            <div class="section">
                <h2>Configuración de Pagos por Transferencia</h2>
                <table>
                    <tr>
                        <th>Campo</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td>Banco</td>
                        <td><input type="text" id="banco_nombre" style="width: 100%; padding: 5px; font-size: 14px;"></td>
                    </tr>
                    <tr>
                        <td>Tipo de Cuenta</td>
                        <td>
                            <select id="banco_tipo_cuenta" style="width: 100%; padding: 5px; font-size: 14px;">
                                <option value="Ahorros">Ahorros</option>
                                <option value="Corriente">Corriente</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Número de Cuenta</td>
                        <td><input type="text" id="banco_cuenta" style="width: 100%; padding: 5px; font-size: 14px;"></td>
                    </tr>
                    <tr>
                        <td>Titular de Cuenta</td>
                        <td><input type="text" id="banco_titular" style="width: 100%; padding: 5px; font-size: 14px;"></td>
                    </tr>
                    <tr>
                        <td>WhatsApp de Contacto</td>
                        <td><input type="text" id="whatsapp_contacto" style="width: 100%; padding: 5px; font-size: 14px;"></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <label>Instrucciones de Transferencia:</label><br><br>
                            <textarea id="instrucciones_transferencia" style="width: 100%; height: 100px; padding: 10px; font-size: 14px;"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
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
            const loadingEl = document.getElementById('loading');
            const contentEl = document.getElementById('content');
            
            loadingEl.style.display = 'block';
            contentEl.style.display = 'none';
            
            const token = localStorage.getItem('token');
            
            // Verificar autenticación
            if (!token) {
                showMessage('No hay sesión. Redireccionando...', 'error');
                setTimeout(() => {
                    window.location.href = BASE_URL_VAR + '/login';
                }, 1000);
                return;
            }

            const userData = JSON.parse(localStorage.getItem('usuario') || '{}');
            if (userData.rol !== 'superadmin') {
                showMessage('Acceso denegado: requiere rol superadmin', 'error');
                setTimeout(() => {
                    window.location.href = BASE_URL_VAR + '/login';
                }, 1000);
                return;
            }

            document.getElementById('usuario-nombre').textContent = userData.nombre || 'Admin';

            // Cargar estadísticas con reintentos
            let stats = null;
            let intentos = 0;
            const maxIntentos = 3;
            
            while (intentos < maxIntentos && !stats) {
                try {
                    const response = await fetch(BASE_URL_VAR + '/api/superadmin/dashboard', {
                        method: 'GET',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        console.error('Error HTTP:', response.status);
                        if (intentos < maxIntentos - 1) {
                            intentos++;
                            await new Promise(r => setTimeout(r, 500));
                            continue;
                        }
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const data = await response.json();
                    console.log('Datos recibidos:', data);
                    try {
                        const urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.get('debug') === '1') {
                            const raw = document.getElementById('raw-json');
                            raw.style.display = 'block';
                            raw.textContent = JSON.stringify(data, null, 2);
                        }
                    } catch (e) {
                        console.error('No se pudo mostrar debug JSON:', e);
                    }
                    
                    // Intentar extraer stats de diferentes estructuras
                    if (data.data && typeof data.data === 'object') {
                        stats = data.data;
                    } else if (data.stats) {
                        stats = data.stats;
                    } else if (data.totalUsuarios !== undefined) {
                        stats = data;
                    } else {
                        throw new Error('Estructura de datos no reconocida');
                    }
                    
                    console.log('Stats extraído:', stats);
                    break;
                    
                } catch (err) {
                    console.error(`Intento ${intentos + 1} fallido:`, err);
                    intentos++;
                    if (intentos < maxIntentos) {
                        await new Promise(r => setTimeout(r, 500));
                    }
                }
            }

            if (!stats) {
                throw new Error('No se pudieron cargar las estadísticas después de ' + maxIntentos + ' intentos');
            }

            // Actualizar estadísticas con valores seguros
            const setStatValue = (id, value) => {
                const el = document.getElementById(id);
                if (el) {
                    el.textContent = value;
                }
            };

            setStatValue('stat-usuarios', Math.max(0, parseInt(stats.totalUsuarios) || 0));
            setStatValue('stat-servicios', Math.max(0, parseInt(stats.serviciosActivos) || 0));
            setStatValue('stat-pendientes', Math.max(0, parseInt(stats.solicitudesPendientes) || 0));
            setStatValue('stat-ingresos', '$' + formatNumber(Math.max(0, parseFloat(stats.ingresosMes) || 0)));
            // Mostrar ingresos pendientes si existen
            const pendientes = Math.max(0, parseFloat(stats.ingresosPendientes) || 0);
            const pendEl = document.getElementById('stat-ingresos-pendientes');
            if (pendEl) {
                pendEl.textContent = '$' + formatNumber(pendientes);
                // Resaltar tarjeta si hay pendientes
                const card = pendEl.closest('.stat-card');
                if (card) {
                    if (pendientes > 0) {
                        card.classList.add('warning');
                    } else {
                        card.classList.remove('warning');
                    }
                }
            }
            setStatValue('stat-completadas', Math.max(0, parseInt(stats.solicitudesCompletadas) || 0));
            setStatValue('stat-pagos', Math.max(0, parseInt(stats.pagosHoy) || 0));
            setStatValue('stat-nuevos', Math.max(0, parseInt(stats.nuevosUsuariosHoy) || 0));
            setStatValue('stat-profesionales', Math.max(0, parseInt(stats.profesionalesActivos) || 0));

            console.log('Estadísticas actualizadas correctamente');

            // Cargar configuración de pagos
            await cargarConfigPagos(token);

            loadingEl.style.display = 'none';
            contentEl.style.display = 'block';

        } catch (error) {
            console.error('Error cargando dashboard:', error);
            showMessage('Error: ' + error.message, 'error');
            document.getElementById('loading').style.display = 'none';
            document.getElementById('content').style.display = 'block';
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
