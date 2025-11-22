#!/bin/bash

echo "=========================================="
echo "  VERIFICACIÓN DE MEJORAS V2.0"
echo "=========================================="
echo ""

echo "📊 Tablas Creadas:"
mysql -u root especialistas_casa -e "
SELECT TABLE_NAME, TABLE_ROWS 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'especialistas_casa' 
AND TABLE_NAME IN (
    'especialidades',
    'profesional_especialidades',
    'disponibilidad_profesional',
    'bloques_no_disponibles',
    'notificaciones',
    'plantillas_notificaciones',
    'configuracion_tiempos'
)
ORDER BY TABLE_NAME;
"

echo ""
echo "✅ Especialidades insertadas:"
mysql -u root especialistas_casa -e "
SELECT tipo_profesional, COUNT(*) as total
FROM especialidades
GROUP BY tipo_profesional;
"

echo ""
echo "📋 Plantillas de Notificaciones:"
mysql -u root especialistas_casa -e "
SELECT codigo, tipo
FROM plantillas_notificaciones
ORDER BY id;
"

echo ""
echo "📁 Archivos PHP Creados:"
echo -n "  Especialidad.php: "
[ -f app/Models/Especialidad.php ] && echo "✅" || echo "❌"

echo -n "  Disponibilidad.php: "
[ -f app/Models/Disponibilidad.php ] && echo "✅" || echo "❌"

echo -n "  NotificacionService.php: "
[ -f app/Services/NotificacionService.php ] && echo "✅" || echo "❌"

echo ""
echo "🎨 Archivos Frontend Creados:"
echo -n "  kanban-board.js: "
[ -f public/js/kanban-board.js ] && echo "✅" || echo "❌"

echo -n "  kanban.css: "
[ -f public/css/kanban.css ] && echo "✅" || echo "❌"

echo ""
echo "=========================================="
echo "  Verificación Completada"
echo "=========================================="
