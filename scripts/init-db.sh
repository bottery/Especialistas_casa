#!/bin/bash

###############################################################################
# Script de Inicialización de Base de Datos
# Especialistas en Casa
###############################################################################

echo "🗄️  Inicializando Base de Datos..."
echo ""

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Cargar variables de entorno
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
else
    echo -e "${RED}❌ Archivo .env no encontrado${NC}"
    exit 1
fi

# Validar que MySQL esté instalado
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}❌ MySQL no está instalado${NC}"
    echo "Instala MySQL con: brew install mysql"
    exit 1
fi

echo -e "${YELLOW}📋 Configuración:${NC}"
echo "  - Host: ${DB_HOST}"
echo "  - Puerto: ${DB_PORT}"
echo "  - Base de datos: ${DB_DATABASE}"
echo "  - Usuario: ${DB_USERNAME}"
echo ""

# Solicitar contraseña si no está en .env
if [ -z "$DB_PASSWORD" ]; then
    echo -e "${YELLOW}⚠️  DB_PASSWORD está vacío, intentando sin contraseña...${NC}"
    MYSQL_CMD="mysql -h${DB_HOST} -P${DB_PORT} -u${DB_USERNAME}"
else
    MYSQL_CMD="mysql -h${DB_HOST} -P${DB_PORT} -u${DB_USERNAME} -p${DB_PASSWORD}"
fi

# Crear base de datos
echo -e "${YELLOW}1. Creando base de datos...${NC}"
$MYSQL_CMD <<EOF
CREATE DATABASE IF NOT EXISTS ${DB_DATABASE} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EOF

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Base de datos creada${NC}"
else
    echo -e "${RED}❌ Error al crear la base de datos${NC}"
    exit 1
fi

# Importar schema
echo -e "${YELLOW}2. Importando schema principal...${NC}"
$MYSQL_CMD "${DB_DATABASE}" < database/schema.sql

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Schema importado${NC}"
else
    echo -e "${RED}❌ Error al importar schema${NC}"
    exit 1
fi

# Aplicar migraciones
echo -e "${YELLOW}3. Aplicando migraciones...${NC}"

MIGRATIONS=(
    "database/migrations/create_profesional_servicios_table.sql"
    "database/migrations/add_service_specific_fields.sql"
    "database/migrations/add_payment_confirmation_flow.sql"
    "database/migrations/add_calificaciones_system.sql"
    "database/migrations/optimize_indexes.sql"
)

for migration in "${MIGRATIONS[@]}"; do
    if [ -f "$migration" ]; then
        echo -e "  - Aplicando $(basename $migration)..."
        $MYSQL_CMD "${DB_DATABASE}" < "$migration" 2>/dev/null
        echo -e "${GREEN}    ✓ Aplicada${NC}"
    fi
done

# Crear usuario de prueba (SuperAdmin)
echo -e "${YELLOW}4. Creando usuario de prueba (SuperAdmin)...${NC}"
$MYSQL_CMD "${DB_DATABASE}" <<EOF
INSERT IGNORE INTO usuarios (email, password, nombre, apellido, telefono, rol, estado, verificado, created_at)
VALUES (
    'admin@especialistas.com',
    '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'Super',
    'Admin',
    '3001234567',
    'superadmin',
    'activo',
    1,
    NOW()
);
EOF

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Usuario creado${NC}"
    echo -e "   📧 Email: admin@especialistas.com"
    echo -e "   🔑 Contraseña: password"
else
    echo -e "${YELLOW}⚠️  Usuario ya existe o error al crear${NC}"
fi

echo ""
echo -e "${GREEN}🎉 Base de datos inicializada correctamente${NC}"
echo ""
echo "Puedes iniciar sesión con:"
echo "  📧 admin@especialistas.com"
echo "  🔑 password"
echo ""
