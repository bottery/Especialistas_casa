#!/bin/bash

# Script para verificar que el dashboard ahora muestra los datos correctamente

echo "========================================"
echo "🧪 VERIFICACIÓN POST-CORRECCIÓN"
echo "========================================"

# Conectar a MySQL y obtener datos
echo -e "\n1️⃣ Verificando datos en base de datos:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

mysql -u root especialistas_casa -e "
SELECT 
    (SELECT COUNT(*) FROM usuarios) as 'Total Usuarios',
    (SELECT COUNT(*) FROM servicios WHERE activo = 1) as 'Servicios Activos',
    (SELECT COUNT(*) FROM solicitudes WHERE estado IN ('pendiente', 'pendiente_pago', 'asignado')) as 'Pendientes',
    (SELECT COUNT(*) FROM solicitudes WHERE estado IN ('completada', 'completado')) as 'Completadas',
    (SELECT COUNT(*) FROM usuarios WHERE rol IN ('medico', 'enfermera', 'veterinario', 'laboratorio', 'ambulancia') AND estado = 'activo') as 'Profesionales Activos',
    (SELECT COUNT(*) FROM pagos WHERE DATE(created_at) = CURDATE()) as 'Pagos Hoy';
" 2>/dev/null || echo "❌ Error conectando a MySQL"

echo -e "\n2️⃣ Estado de los Controllers:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Verificar SuperAdminController
if grep -q "extends BaseController" app/Controllers/SuperAdminController.php 2>/dev/null; then
    echo "✅ SuperAdminController extiende BaseController"
else
    echo "❌ SuperAdminController NO extiende BaseController"
fi

# Verificar AnalyticsController
if grep -q "extends BaseController" app/Controllers/AnalyticsController.php 2>/dev/null; then
    echo "✅ AnalyticsController extiende BaseController"
else
    echo "❌ AnalyticsController NO extiende BaseController"
fi

echo -e "\n3️⃣ Verificando queries SQL:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if grep -q "WHERE activo = 1" app/Controllers/SuperAdminController.php; then
    echo "✅ Query de servicios activos corregida"
else
    echo "❌ Query de servicios activos NO corregida"
fi

if grep -q "pendiente_pago" app/Controllers/SuperAdminController.php; then
    echo "✅ Query de solicitudes pendientes corregida"
else
    echo "❌ Query de solicitudes pendientes NO corregida"
fi

echo -e "\n========================================"
echo "✅ Verificación completada"
echo "========================================"
echo -e "\n📝 Próximo paso:"
echo "   1. Abre: http://localhost/VitaHome/superadmin/dashboard"
echo "   2. Login: superadmin@example.com / Admin123!"
echo "   3. Las estadísticas deben mostrar datos reales (no 0)"
