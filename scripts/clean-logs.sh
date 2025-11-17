#!/bin/bash

# ============================================
# Script de limpieza y rotación de logs
# Especialistas en Casa
# ============================================

LOG_DIR="/Users/papo/especialistas-en-casa/storage/logs"
RETENTION_DAYS=30
MAX_SIZE_MB=100

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}===========================================
${NC}"
echo -e "${YELLOW}Limpieza de Logs - Especialistas en Casa${NC}"
echo -e "${YELLOW}===========================================
${NC}"

# Verificar que el directorio existe
if [ ! -d "$LOG_DIR" ]; then
    echo -e "${RED}✗ Directorio de logs no encontrado${NC}"
    exit 1
fi

cd "$LOG_DIR" || exit 1

# Contar logs antes
LOGS_BEFORE=$(find . -name "*.log" -type f | wc -l)
SIZE_BEFORE=$(du -sh . | cut -f1)

echo -e "${YELLOW}Estado actual:${NC}"
echo -e "  Archivos de log: $LOGS_BEFORE"
echo -e "  Espacio usado: $SIZE_BEFORE"
echo ""

# Eliminar logs antiguos
echo -e "${YELLOW}Eliminando logs con más de ${RETENTION_DAYS} días...${NC}"
DELETED=$(find . -name "*.log" -type f -mtime +$RETENTION_DAYS | wc -l)
find . -name "*.log" -type f -mtime +$RETENTION_DAYS -delete

if [ $DELETED -gt 0 ]; then
    echo -e "${GREEN}✓ Eliminados $DELETED archivos antiguos${NC}"
else
    echo -e "  No hay logs antiguos para eliminar"
fi

# Comprimir logs de más de 7 días
echo -e "
${YELLOW}Comprimiendo logs de más de 7 días...${NC}"
COMPRESSED=0

for log_file in $(find . -name "*.log" -type f -mtime +7 ! -name "*.gz"); do
    gzip "$log_file"
    COMPRESSED=$((COMPRESSED + 1))
done

if [ $COMPRESSED -gt 0 ]; then
    echo -e "${GREEN}✓ Comprimidos $COMPRESSED archivos${NC}"
else
    echo -e "  No hay logs para comprimir"
fi

# Verificar tamaño de logs individuales
echo -e "
${YELLOW}Verificando tamaño de archivos...${NC}"
LARGE_FILES=0

while IFS= read -r file; do
    SIZE_KB=$(du -k "$file" | cut -f1)
    SIZE_MB=$((SIZE_KB / 1024))
    
    if [ $SIZE_MB -gt $MAX_SIZE_MB ]; then
        echo -e "${YELLOW}  ⚠ Archivo grande encontrado: $(basename $file) (${SIZE_MB}MB)${NC}"
        LARGE_FILES=$((LARGE_FILES + 1))
        
        # Truncar archivo si es muy grande
        if [ $SIZE_MB -gt 500 ]; then
            echo -e "    Truncando archivo..."
            tail -n 10000 "$file" > "${file}.tmp"
            mv "${file}.tmp" "$file"
            echo -e "${GREEN}    ✓ Archivo truncado${NC}"
        fi
    fi
done < <(find . -name "*.log" -type f)

if [ $LARGE_FILES -eq 0 ]; then
    echo -e "  Todos los archivos están dentro del límite"
fi

# Limpiar archivos vacíos
echo -e "
${YELLOW}Eliminando archivos vacíos...${NC}"
EMPTY=$(find . -name "*.log" -type f -empty | wc -l)
find . -name "*.log" -type f -empty -delete

if [ $EMPTY -gt 0 ]; then
    echo -e "${GREEN}✓ Eliminados $EMPTY archivos vacíos${NC}"
else
    echo -e "  No hay archivos vacíos"
fi

# Estadísticas finales
echo -e "
${YELLOW}Estado final:${NC}"
LOGS_AFTER=$(find . -name "*.log" -o -name "*.log.gz" | wc -l)
SIZE_AFTER=$(du -sh . | cut -f1)

echo -e "  Archivos de log: $LOGS_AFTER"
echo -e "  Espacio usado: $SIZE_AFTER"

echo -e "
${GREEN}Limpieza completada con éxito${NC}"
echo -e "${YELLOW}===========================================
${NC}"
