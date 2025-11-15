#!/bin/bash

# Script de prueba para el flujo completo de solicitudes

BASE_URL="http://localhost:8000/api"

echo "======================================"
echo "PRUEBA DE FLUJO COMPLETO DE SOLICITUD"
echo "======================================"
echo ""

# 1. Obtener token de usuario paciente
echo "1. Login como paciente..."
TOKEN=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "paciente@test.com",
    "password": "123456"
  }' | jq -r '.token // empty')

if [ -z "$TOKEN" ]; then
  echo "❌ Error: No se pudo obtener token. Asegúrate de tener un usuario paciente."
  echo "   Crear con: INSERT INTO usuarios (email, password, rol, nombre, apellido, estado) VALUES ('paciente@test.com', '\$2y\$10\$abcdefg', 'paciente', 'Juan', 'Pérez', 'activo');"
  exit 1
fi

echo "✅ Token obtenido"
echo ""

# 2. Listar servicios disponibles
echo "2. Listar servicios disponibles..."
SERVICIOS=$(curl -s "$BASE_URL/servicios" | jq -r '.servicios[0:3] | .[] | "\(.id): \(.nombre) - \(.tipo)"')
echo "$SERVICIOS"
echo ""

# 3. Obtener profesionales
echo "3. Listar profesionales..."
PROFESIONALES=$(curl -s "$BASE_URL/profesionales?servicio_id=1" | jq -r '.profesionales | length')
echo "✅ Profesionales disponibles: $PROFESIONALES"
echo ""

# 4. Crear solicitud de MÉDICO
echo "4. Crear solicitud de MÉDICO ESPECIALISTA..."
SOLICITUD_MEDICO=$(curl -s -X POST "$BASE_URL/solicitudes" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "servicio_id": 1,
    "servicio_tipo": "medico",
    "modalidad": "virtual",
    "fecha_programada": "2025-11-16 10:00:00",
    "especialidad": "Medicina General",
    "rango_horario": "manana",
    "sintomas": "Dolor de cabeza persistente y fiebre",
    "telefono_contacto": "3001234567",
    "urgencia": "normal",
    "requiere_aprobacion": true
  }')

echo "$SOLICITUD_MEDICO" | jq '.'
echo ""

# 5. Crear solicitud de AMBULANCIA
echo "5. Crear solicitud de AMBULANCIA..."
SOLICITUD_AMBULANCIA=$(curl -s -X POST "$BASE_URL/solicitudes" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "servicio_id": 10,
    "servicio_tipo": "ambulancia",
    "modalidad": "presencial",
    "fecha_programada": "2025-11-15 14:30:00",
    "hora_programada": "14:30",
    "tipo_ambulancia": "medicalizada",
    "origen": "Calle 123 #45-67, Bogotá",
    "destino": "Hospital San Ignacio, Calle 40 #10-20",
    "tipo_emergencia": "urgente",
    "condicion_paciente": "Paciente estable, requiere traslado con oxígeno",
    "numero_acompanantes": 1,
    "contacto_emergencia": "María López - 3009876543",
    "telefono_contacto": "3001234567",
    "urgencia": "urgente"
  }')

echo "$SOLICITUD_AMBULANCIA" | jq '.'
echo ""

# 6. Crear solicitud de LABORATORIO
echo "6. Crear solicitud de LABORATORIO..."
SOLICITUD_LAB=$(curl -s -X POST "$BASE_URL/solicitudes" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "servicio_id": 15,
    "servicio_tipo": "laboratorio",
    "modalidad": "presencial",
    "fecha_programada": "2025-11-16 07:00:00",
    "direccion_servicio": "Calle 80 #12-34, Apto 501",
    "examenes_solicitados": "[\"Hemograma completo\", \"Glucosa\", \"Perfil lipídico\"]",
    "requiere_ayuno": true,
    "email_resultados": "paciente@test.com",
    "telefono_contacto": "3001234567"
  }')

echo "$SOLICITUD_LAB" | jq '.'
echo ""

# 7. Ver estadísticas del paciente
echo "7. Ver estadísticas del paciente..."
STATS=$(curl -s "$BASE_URL/paciente/stats" \
  -H "Authorization: Bearer $TOKEN")

echo "$STATS" | jq '.'
echo ""

# 8. Ver historial de solicitudes
echo "8. Ver historial de solicitudes..."
HISTORIAL=$(curl -s "$BASE_URL/paciente/solicitudes" \
  -H "Authorization: Bearer $TOKEN")

echo "$HISTORIAL" | jq '.solicitudes | length' | xargs echo "✅ Total solicitudes:"
echo ""

echo "======================================"
echo "PRUEBA COMPLETADA"
echo "======================================"
