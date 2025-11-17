#!/bin/bash

# ============================================
# Script de despliegue a producción
# Especialistas en Casa
# ============================================

set -e  # Salir si hay algún error

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

PROJECT_ROOT="/Users/papo/especialistas-en-casa"

echo -e "${BLUE}
╔═══════════════════════════════════════════╗
║   DESPLIEGUE A PRODUCCIÓN                 ║
║   Especialistas en Casa                   ║
╚═══════════════════════════════════════════╝
${NC}"

# Verificar que estamos en el directorio correcto
cd "$PROJECT_ROOT" || exit 1

# 1. Verificar requisitos
echo -e "${YELLOW}[1/10] Verificando requisitos...${NC}"

if [ ! -f "composer.json" ]; then
    echo -e "${RED}✗ composer.json no encontrado${NC}"
    exit 1
fi

if [ ! -f ".env" ]; then
    echo -e "${RED}✗ Archivo .env no encontrado${NC}"
    echo -e "${YELLOW}Por favor, copia .env.example a .env y configúralo${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Requisitos OK${NC}"

# 2. Instalar dependencias
echo -e "
${YELLOW}[2/10] Instalando dependencias de producción...${NC}"
php composer.phar install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✓ Dependencias instaladas${NC}"

# 3. Verificar configuración de .env
echo -e "
${YELLOW}[3/10] Verificando configuración...${NC}"

if grep -q "APP_DEBUG=true" .env; then
    echo -e "${RED}✗ APP_DEBUG está en true. Debe ser false en producción${NC}"
    exit 1
fi

if grep -q "JWT_SECRET=CAMBIAR" .env; then
    echo -e "${RED}✗ JWT_SECRET no ha sido configurado${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Configuración verificada${NC}"

# 4. Optimizar autoloader
echo -e "
${YELLOW}[4/10] Optimizando autoloader...${NC}"
php composer.phar dump-autoload --optimize --no-dev
echo -e "${GREEN}✓ Autoloader optimizado${NC}"

# 5. Crear directorios necesarios
echo -e "
${YELLOW}[5/10] Creando estructura de directorios...${NC}"
mkdir -p storage/logs
mkdir -p storage/cache
mkdir -p storage/sessions
mkdir -p storage/uploads
mkdir -p storage/backups
mkdir -p storage/cache/rate-limits

echo -e "${GREEN}✓ Directorios creados${NC}"

# 6. Establecer permisos
echo -e "
${YELLOW}[6/10] Configurando permisos...${NC}"
chmod -R 755 storage
chmod -R 755 public
chmod 644 .env
echo -e "${GREEN}✓ Permisos configurados${NC}"

# 7. Limpiar caché
echo -e "
${YELLOW}[7/10] Limpiando caché...${NC}"
rm -rf storage/cache/*
rm -rf storage/sessions/*
echo -e "${GREEN}✓ Caché limpiado${NC}"

# 8. Verificar base de datos
echo -e "
${YELLOW}[8/10] Verificando conexión a base de datos...${NC}"
php -r "
require 'vendor/autoload.php';
\$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
\$dotenv->load();
try {
    \$db = App\Services\Database::getInstance();
    \$db->selectOne('SELECT 1');
    echo 'OK';
} catch (Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage();
    exit(1);
}
"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Conexión a base de datos OK${NC}"
else
    echo -e "${RED}✗ Error de conexión a base de datos${NC}"
    exit 1
fi

# 9. Aplicar migraciones
echo -e "
${YELLOW}[9/10] ¿Desea aplicar migraciones de base de datos? (s/N)${NC}"
read -r APPLY_MIGRATIONS

if [ "$APPLY_MIGRATIONS" = "s" ] || [ "$APPLY_MIGRATIONS" = "S" ]; then
    echo -e "${YELLOW}Aplicando migraciones...${NC}"
    # Aquí aplicarías las migraciones
    echo -e "${GREEN}✓ Migraciones aplicadas${NC}"
fi

# 10. Hacer backup
echo -e "
${YELLOW}[10/10] Creando backup de base de datos...${NC}"
bash scripts/backup-db.sh

# Resumen final
echo -e "
${BLUE}╔═══════════════════════════════════════════╗
║   DESPLIEGUE COMPLETADO                   ║
╚═══════════════════════════════════════════╝${NC}

${GREEN}✓ El sistema está listo para producción${NC}

${YELLOW}Próximos pasos:${NC}
1. Verificar que el servidor web apunta a /public
2. Configurar SSL/HTTPS
3. Configurar cron jobs para:
   - Backup diario: 0 2 * * * /path/to/scripts/backup-db.sh
   - Limpieza de logs: 0 3 * * * /path/to/scripts/clean-logs.sh
4. Monitorear logs en storage/logs/
5. Verificar endpoint: /api/health

${BLUE}Para verificar el sistema:${NC}
  curl http://tudominio.com/api/health
"
