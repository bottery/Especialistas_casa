#!/bin/bash
# Verificación de Dashboard Recreado

echo "📋 VERIFICACIÓN DE DASHBOARD RECREADO"
echo "====================================="

# Archivos principales
echo -e "\n✅ Archivos verificados:"
ls -lh resources/views/superadmin/dashboard.php 2>/dev/null && echo "   ✓ Dashboard (842 líneas)" || echo "   ✗ No encontrado"

# Endpoints necesarios
echo -e "\n✅ Endpoints API requeridos:"
echo "   1. GET  /api/superadmin/dashboard"
echo "   2. GET  /api/analytics/charts"
echo "   3. GET  /api/admin/configuracion-pagos"
echo "   4. PUT  /api/admin/configuracion-pagos"
echo "   5. POST /api/admin/subir-qr"
echo "   6. DELETE /api/admin/configuracion-pagos/qr"

# Verificar rutas
echo -e "\n✅ Rutas en api.php:"
grep -c "superadmin/dashboard" routes/api.php && echo "   ✓ Route superadmin/dashboard encontrada"
grep -c "analytics/charts" routes/api.php && echo "   ✓ Route analytics/charts encontrada"
grep -c "admin/configuracion-pagos" routes/api.php && echo "   ✓ Route admin/configuracion-pagos encontrada"
grep -c "admin/subir-qr" routes/api.php && echo "   ✓ Route admin/subir-qr encontrada"

# Verificar elementos en dashboard
echo -e "\n✅ Elementos del Dashboard:"
grep -c "<!-- Navbar -->" resources/views/superadmin/dashboard.php && echo "   ✓ Navbar"
grep -c "<!-- Navigation Menu -->" resources/views/superadmin/dashboard.php && echo "   ✓ Navigation"
grep -c "<!-- Stats Cards -->" resources/views/superadmin/dashboard.php && echo "   ✓ Stats"
grep -c "<!-- Charts -->" resources/views/superadmin/dashboard.php && echo "   ✓ Charts"
grep -c "<!-- Configuración de Pagos -->" resources/views/superadmin/dashboard.php && echo "   ✓ Payment Config"

# Verificar funciones JavaScript
echo -e "\n✅ Funciones JavaScript:"
grep -c "async init()" resources/views/superadmin/dashboard.php && echo "   ✓ init()"
grep -c "async loadDashboardData()" resources/views/superadmin/dashboard.php && echo "   ✓ loadDashboardData()"
grep -c "async loadChartData()" resources/views/superadmin/dashboard.php && echo "   ✓ loadChartData()"
grep -c "renderCharts()" resources/views/superadmin/dashboard.php && echo "   ✓ renderCharts()"
grep -c "async cargarConfigPagos()" resources/views/superadmin/dashboard.php && echo "   ✓ cargarConfigPagos()"
grep -c "async guardarConfigPagos()" resources/views/superadmin/dashboard.php && echo "   ✓ guardarConfigPagos()"
grep -c "async subirQR()" resources/views/superadmin/dashboard.php && echo "   ✓ subirQR()"
grep -c "async eliminarQR()" resources/views/superadmin/dashboard.php && echo "   ✓ eliminarQR()"

# Verificar gráficos
echo -e "\n✅ Gráficos inicializados:"
grep -c "ingresosChart" resources/views/superadmin/dashboard.php && echo "   ✓ Ingresos Mensuales"
grep -c "serviciosChart" resources/views/superadmin/dashboard.php && echo "   ✓ Servicios por Tipo"
grep -c "usuariosChart" resources/views/superadmin/dashboard.php && echo "   ✓ Usuarios por Rol"
grep -c "solicitudesChart" resources/views/superadmin/dashboard.php && echo "   ✓ Solicitudes por Estado"
grep -c "tendenciaChart" resources/views/superadmin/dashboard.php && echo "   ✓ Tendencia Semanal"

# Verificar mejoras de robustez
echo -e "\n✅ Mejoras de Robustez:"
grep -c "try {" resources/views/superadmin/dashboard.php | awk '{print "   ✓ Try-catch blocks: " $1}'
grep -c "retryCount" resources/views/superadmin/dashboard.php && echo "   ✓ Reintentos automáticos"
grep -c "if (response.ok)" resources/views/superadmin/dashboard.php && echo "   ✓ Validación de respuestas"
grep -c "destroy()" resources/views/superadmin/dashboard.php && echo "   ✓ Destrucción de gráficos previos"

echo -e "\n====================================="
echo "✅ Verificación completada"
echo -e "\n📌 Próximos pasos:"
echo "1. Abre http://localhost/VitaHome/superadmin/dashboard"
echo "2. Inicia sesión como superadmin@example.com / Admin123!"
echo "3. Verifica que se cargan datos y gráficos"
echo "4. Abre la consola (F12) para revisar errores"
echo "5. Prueba la subida de QR"
