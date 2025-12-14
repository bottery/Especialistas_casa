#!/bin/bash

echo "🧪 Testing Dashboard Superadmin"
echo "=============================="

# Variables
BASE_URL="http://localhost/VitaHome"
TOKEN_FILE="/tmp/superadmin_token.txt"
EMAIL="superadmin@example.com"
PASSWORD="Admin123!"

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "\n${YELLOW}1. Autenticando como superadmin...${NC}"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
  echo -e "${RED}❌ Error en autenticación${NC}"
  echo "Response: $LOGIN_RESPONSE"
  exit 1
fi

echo -e "${GREEN}✅ Autenticación exitosa${NC}"
echo "Token: ${TOKEN:0:20}..."
echo $TOKEN > $TOKEN_FILE

echo -e "\n${YELLOW}2. Probando /api/superadmin/dashboard${NC}"
DASHBOARD_RESPONSE=$(curl -s -X GET "$BASE_URL/api/superadmin/dashboard" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

# Check if response contains expected fields
if echo "$DASHBOARD_RESPONSE" | grep -q "totalUsuarios\|stats"; then
  echo -e "${GREEN}✅ Dashboard endpoint responde correctamente${NC}"
  echo "Stats: $(echo $DASHBOARD_RESPONSE | grep -o '"totalUsuarios":"[^"]*' | cut -d'"' -f4)"
else
  echo -e "${RED}❌ Dashboard endpoint no responde correctamente${NC}"
  echo "Response: $DASHBOARD_RESPONSE"
fi

echo -e "\n${YELLOW}3. Probando /api/analytics/charts${NC}"
CHARTS_RESPONSE=$(curl -s -X GET "$BASE_URL/api/analytics/charts" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

# Check for expected chart data
if echo "$CHARTS_RESPONSE" | grep -q "ingresos_mensuales\|servicios_por_tipo\|usuarios_por_rol"; then
  echo -e "${GREEN}✅ Charts endpoint responde correctamente${NC}"
  echo "Contiene: ingresos_mensuales, servicios_por_tipo, usuarios_por_rol"
else
  echo -e "${RED}❌ Charts endpoint no responde correctamente${NC}"
  echo "Response: $CHARTS_RESPONSE"
fi

echo -e "\n${YELLOW}4. Probando /api/admin/configuracion-pagos (GET)${NC}"
CONFIG_RESPONSE=$(curl -s -X GET "$BASE_URL/api/admin/configuracion-pagos" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

if echo "$CONFIG_RESPONSE" | grep -q "banco_nombre\|data"; then
  echo -e "${GREEN}✅ Configuración de pagos endpoint responde correctamente${NC}"
  echo "Banco: $(echo $CONFIG_RESPONSE | grep -o '"banco_nombre":"[^"]*' | cut -d'"' -f4)"
else
  echo -e "${YELLOW}⚠️  Configuración de pagos vacía o no configurada${NC}"
fi

echo -e "\n${YELLOW}5. Probando actualización de configuración (PUT)${NC}"
PUT_RESPONSE=$(curl -s -X PUT "$BASE_URL/api/admin/configuracion-pagos" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "banco_nombre": "Banco Test",
    "banco_cuenta": "123456789",
    "banco_tipo_cuenta": "Ahorros",
    "banco_titular": "VitaHome Test",
    "whatsapp_contacto": "+57 300 123 4567",
    "instrucciones_transferencia": "Transferencia a cuenta en Banco Test"
  }')

if echo "$PUT_RESPONSE" | grep -q "success\|actualizada\|updated"; then
  echo -e "${GREEN}✅ Configuración actualizada correctamente${NC}"
else
  echo -e "${YELLOW}⚠️  Respuesta: $(echo $PUT_RESPONSE | head -c 100)...${NC}"
fi

echo -e "\n${YELLOW}6. Accesibilidad del dashboard.php${NC}"
DASHBOARD_PAGE=$(curl -s -w "%{http_code}" -o /dev/null "$BASE_URL/superadmin/dashboard")

if [ "$DASHBOARD_PAGE" = "200" ]; then
  echo -e "${GREEN}✅ Dashboard página carga correctamente (HTTP 200)${NC}"
else
  echo -e "${RED}❌ Dashboard página error (HTTP $DASHBOARD_PAGE)${NC}"
fi

echo -e "\n${YELLOW}=============================${NC}"
echo -e "${GREEN}🎉 Testing completado${NC}"
echo -e "\n${YELLOW}Notas:${NC}"
echo "- Abre http://localhost/VitaHome/superadmin/dashboard en tu navegador"
echo "- Abre la consola del desarrollador (F12) para ver si hay errores"
echo "- Verifica que las estadísticas se cargan"
echo "- Verifica que los gráficos se renderizan"
echo "- Prueba subir un QR"

# Cleanup
rm -f $TOKEN_FILE
