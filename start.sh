#!/bin/bash

echo "🏥 Especialistas en Casa - Inicio Rápido"
echo "========================================"
echo ""

# Colores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Función para verificar comando
check_command() {
    if command -v $1 &> /dev/null; then
        echo -e "${GREEN}✓${NC} $1 está instalado"
        return 0
    else
        echo -e "${RED}✗${NC} $1 NO está instalado"
        return 1
    fi
}

# Verificar PHP
echo "Verificando requisitos..."
if check_command php; then
    PHP_VERSION=$(php -v | head -n 1)
    echo "  Versión: $PHP_VERSION"
else
    echo -e "${YELLOW}Instalando PHP...${NC}"
    brew install php@8.2
    export PATH="/usr/local/opt/php@8.2/bin:$PATH"
fi

# Verificar MySQL
if check_command mysql; then
    echo -e "${GREEN}✓${NC} MySQL está instalado"
else
    echo -e "${YELLOW}⚠${NC}  MySQL no está instalado. Instálalo con:"
    echo "  brew install mysql"
fi

echo ""
echo -e "${BLUE}Iniciando servidor PHP en http://localhost:8000${NC}"
echo ""
echo "Presiona Ctrl+C para detener el servidor"
echo ""

cd "$(dirname "$0")/public"
php -S localhost:8000
