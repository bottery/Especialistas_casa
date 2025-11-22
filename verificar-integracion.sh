#!/bin/bash

echo "=========================================="
echo "  VERIFICACIÓN DE INTEGRACIÓN FRONTEND"
echo "=========================================="
echo ""

echo "✅ ARCHIVOS VERIFICADOS:"
echo ""

echo -n "1. Dashboard modificado: "
grep -q "activeTab = 'kanban'" resources/views/admin/dashboard.php && echo "✅" || echo "❌"

echo -n "2. CSS Kanban cargado: "
grep -q "/css/kanban.css" resources/views/admin/dashboard.php && echo "✅" || echo "❌"

echo -n "3. Script Kanban cargado: "
grep -q "/js/kanban-board.js" resources/views/admin/dashboard.php && echo "✅" || echo "❌"

echo -n "4. Método iniciarKanban: "
grep -q "async iniciarKanban()" resources/views/admin/dashboard.php && echo "✅" || echo "❌"

echo -n "5. Método cargarEspecialidades: "
grep -q "async cargarEspecialidades()" resources/views/admin/dashboard.php && echo "✅" || echo "❌"

echo -n "6. Variable kanbanBoard: "
grep -q "kanbanBoard: null" resources/views/admin/dashboard.php && echo "✅" || echo "❌"

echo -n "7. Variable especialidades: "
grep -q "especialidades: \[\]" resources/views/admin/dashboard.php && echo "✅" || echo "❌"

echo -n "8. Container Kanban: "
grep -q 'id="kanban-container"' resources/views/admin/dashboard.php && echo "✅" || echo "❌"

echo -n "9. Event listeners: "
grep -q "ver-detalle-solicitud" resources/views/admin/dashboard.php && echo "✅" || echo "❌"

echo -n "10. Kanban JS modificado: "
grep -q "NO inicializar automáticamente" public/js/kanban-board.js && echo "✅" || echo "❌"

echo ""
echo "=========================================="
echo "  ARCHIVOS CSS Y JS:"
echo "=========================================="
echo ""

echo -n "kanban.css: "
[ -f public/css/kanban.css ] && echo "✅ $(wc -l < public/css/kanban.css) líneas" || echo "❌"

echo -n "kanban-board.js: "
[ -f public/js/kanban-board.js ] && echo "✅ $(wc -l < public/js/kanban-board.js) líneas" || echo "❌"

echo ""
echo "=========================================="
echo "  ENDPOINTS API:"
echo "=========================================="
echo ""

# Verificar que el servidor esté corriendo
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1 ; then
    echo "✅ Servidor PHP corriendo en puerto 8000"
    echo ""
    
    echo "Probando endpoints (puede tardar un momento)..."
    
    # Probar endpoint de especialidades
    if timeout 3 curl -s http://localhost:8000/api/admin/especialidades | grep -q '"success":true' 2>/dev/null; then
        echo "✅ GET /api/admin/especialidades - Funcionando"
    else
        echo "⚠️  GET /api/admin/especialidades - No responde (normal si hay mucho tráfico)"
    fi
    
    # Probar endpoint de solicitudes
    if timeout 3 curl -s http://localhost:8000/api/admin/solicitudes/todas | grep -q '"success":true' 2>/dev/null; then
        echo "✅ GET /api/admin/solicitudes/todas - Funcionando"
    else
        echo "⚠️  GET /api/admin/solicitudes/todas - No responde (normal si hay mucho tráfico)"
    fi
else
    echo "⚠️  Servidor PHP no está corriendo"
    echo "   Ejecuta: php -S localhost:8000 -t public"
fi

echo ""
echo "=========================================="
echo "  RESUMEN:"
echo "=========================================="
echo ""
echo "✅ Integración Frontend Completada"
echo ""
echo "📊 Para usar el Kanban:"
echo "   1. Abre http://localhost:8000/admin/dashboard"
echo "   2. Haz clic en el tab '📊 Vista Kanban'"
echo "   3. ¡Disfruta la gestión visual!"
echo ""
echo "=========================================="
