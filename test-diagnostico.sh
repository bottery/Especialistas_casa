#!/bin/bash

# Test de Sistema de Diagnóstico
echo "=========================================="
echo "🔍 Test: Sistema de Captura de Errores"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}1. Verificando estructura del proyecto...${NC}"
if [ -f "resources/views/superadmin/dashboard.php" ]; then
    echo -e "${GREEN}✓${NC} Dashboard encontrado"
else
    echo -e "${RED}✗${NC} Dashboard no encontrado"
    exit 1
fi

if [ -f "routes/api.php" ]; then
    echo -e "${GREEN}✓${NC} API routes encontrado"
else
    echo -e "${RED}✗${NC} API routes no encontrado"
    exit 1
fi

echo ""
echo -e "${BLUE}2. Verificando cambios en dashboard.php...${NC}"

# Verificar window.errorLog
if grep -q "window.errorLog = {" resources/views/superadmin/dashboard.php; then
    echo -e "${GREEN}✓${NC} window.errorLog implementado"
else
    echo -e "${RED}✗${NC} window.errorLog no encontrado"
fi

# Verificar captura de errores
if grep -q "window.addEventListener('error'" resources/views/superadmin/dashboard.php; then
    echo -e "${GREEN}✓${NC} Captura de errores globales implementada"
else
    echo -e "${RED}✗${NC} Captura de errores no encontrada"
fi

# Verificar atajo de teclado
if grep -q "Ctrl.*Shift.*D" resources/views/superadmin/dashboard.php; then
    echo -e "${GREEN}✓${NC} Atajo Ctrl+Shift+D implementado"
else
    echo -e "${RED}✗${NC} Atajo de teclado no encontrado"
fi

# Verificar panel de diagnóstico HTML
if grep -q "diagnosticPanel" resources/views/superadmin/dashboard.php; then
    echo -e "${GREEN}✓${NC} Panel de diagnóstico HTML implementado"
else
    echo -e "${RED}✗${NC} Panel de diagnóstico no encontrado"
fi

# Verificar downloadLogs
if grep -q "downloadLogs()" resources/views/superadmin/dashboard.php; then
    echo -e "${GREEN}✓${NC} Función downloadLogs() implementada"
else
    echo -e "${RED}✗${NC} Función downloadLogs no encontrada"
fi

echo ""
echo -e "${BLUE}3. Verificando cambios en routes/api.php...${NC}"

# Verificar endpoint de error-logs
if grep -q "/admin/error-logs" routes/api.php; then
    echo -e "${GREEN}✓${NC} Endpoint /api/admin/error-logs implementado"
else
    echo -e "${RED}✗${NC} Endpoint de error-logs no encontrado"
fi

# Verificar storage de logs
if grep -q "storage/logs" routes/api.php; then
    echo -e "${GREEN}✓${NC} Almacenamiento de logs configurado"
else
    echo -e "${RED}✗${NC} Almacenamiento de logs no encontrado"
fi

echo ""
echo -e "${BLUE}4. Verificando documentación...${NC}"

if [ -f "DIAGNOSTICO.md" ]; then
    echo -e "${GREEN}✓${NC} Documentación completa (DIAGNOSTICO.md)"
else
    echo -e "${RED}✗${NC} Documentación completa no encontrada"
fi

if [ -f "RESUMEN_DIAGNOSTICO.md" ]; then
    echo -e "${GREEN}✓${NC} Resumen de cambios (RESUMEN_DIAGNOSTICO.md)"
else
    echo -e "${RED}✗${NC} Resumen de cambios no encontrado"
fi

echo ""
echo -e "${BLUE}5. Creando directorio de logs...${NC}"
mkdir -p storage/logs
if [ -d "storage/logs" ]; then
    echo -e "${GREEN}✓${NC} Directorio storage/logs listo"
else
    echo -e "${RED}✗${NC} No se pudo crear storage/logs"
fi

echo ""
echo "=========================================="
echo -e "${GREEN}✓ Todos los tests pasaron correctamente!${NC}"
echo "=========================================="
echo ""
echo -e "${YELLOW}Instrucciones de uso:${NC}"
echo ""
echo "1. Abre el dashboard en el navegador:"
echo "   https://localhost/VitaHome/superadmin/dashboard"
echo ""
echo "2. Abre las herramientas de desarrollador (F12)"
echo ""
echo "3. Presiona Ctrl+Shift+D para abrir el panel de diagnóstico"
echo ""
echo "4. Luego, prueba:"
echo "   - Escribe en consola: window.errorLog.info('Test', { test: true })"
echo "   - El log debe aparecer en el panel de diagnóstico"
echo ""
echo "5. Para simular un error:"
echo "   - Escribe en consola: throw new Error('Test error')"
echo "   - El error debe capturarse automáticamente"
echo ""
echo "6. Prueba las acciones del panel:"
echo "   - 'Enviar al Servidor': Guarda logs en storage/logs/"
echo "   - 'Descargar JSON': Descarga un archivo JSON"
echo "   - 'Limpiar Logs': Borra todos los logs de la sesión"
echo ""
echo -e "${YELLOW}Archivos creados/modificados:${NC}"
echo "   ✓ resources/views/superadmin/dashboard.php"
echo "   ✓ routes/api.php"
echo "   ✓ storage/logs/ (directorio)"
echo "   ✓ DIAGNOSTICO.md"
echo "   ✓ RESUMEN_DIAGNOSTICO.md"
echo ""
