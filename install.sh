#!/bin/bash

# =================================================
# Script de instalación - Especialistas en Casa
# =================================================

echo "🏥 Instalando Especialistas en Casa..."
echo ""

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Verificar PHP
echo "📋 Verificando requisitos..."
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP no está instalado${NC}"
    echo "Por favor instala PHP 8.2 o superior"
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo -e "${GREEN}✓${NC} PHP $PHP_VERSION encontrado"

# Verificar Composer
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}⚠ Composer no está instalado${NC}"
    echo "Descargando Composer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    mv composer.phar /usr/local/bin/composer
    echo -e "${GREEN}✓${NC} Composer instalado"
else
    echo -e "${GREEN}✓${NC} Composer encontrado"
fi

# Instalar dependencias
echo ""
echo "📦 Instalando dependencias..."
composer install --no-dev --optimize-autoloader

# Crear directorios
echo ""
echo "📁 Creando estructura de directorios..."
mkdir -p storage/logs storage/cache storage/sessions storage/uploads
chmod -R 755 storage

# Crear archivo .env si no existe
if [ ! -f .env ]; then
    echo ""
    echo "⚙️  Configurando variables de entorno..."
    cp .env.example .env
    
    # Generar JWT secret
    JWT_SECRET=$(openssl rand -hex 32)
    if [ -z "$JWT_SECRET" ]; then
        JWT_SECRET=$(php -r "echo bin2hex(random_bytes(32));")
    fi
    
    # Reemplazar en .env
    sed -i.bak "s/JWT_SECRET=.*/JWT_SECRET=$JWT_SECRET/" .env
    rm -f .env.bak
    
    echo -e "${GREEN}✓${NC} Archivo .env creado"
    echo -e "${YELLOW}⚠ IMPORTANTE: Edita el archivo .env con tus credenciales de base de datos${NC}"
else
    echo -e "${YELLOW}⚠ El archivo .env ya existe, no se sobrescribirá${NC}"
fi

# Verificar MySQL
echo ""
echo "🗄️  Verificando MySQL..."
if command -v mysql &> /dev/null; then
    echo -e "${GREEN}✓${NC} MySQL encontrado"
    echo -e "${YELLOW}📋 Recuerda:${NC}"
    echo "   1. Crear la base de datos: CREATE DATABASE especialistas_casa;"
    echo "   2. Importar el esquema: mysql -u root -p especialistas_casa < database/schema.sql"
else
    echo -e "${YELLOW}⚠ MySQL no detectado. Asegúrate de tenerlo instalado.${NC}"
fi

# Verificar permisos
echo ""
echo "🔐 Configurando permisos..."
chmod -R 755 storage
chmod +x setup.sh

echo ""
echo -e "${GREEN}✅ ¡Instalación completada!${NC}"
echo ""
echo "📋 Próximos pasos:"
echo "   1. Editar .env con tus credenciales"
echo "   2. Crear la base de datos"
echo "   3. Importar el esquema SQL"
echo "   4. Configurar tu servidor web (Apache/Nginx)"
echo "   5. Acceder a la aplicación"
echo ""
echo "📚 Consulta README.md para más información"
echo ""
