#!/bin/bash

# ============================================
# Script para eliminar console.logs en producción
# Especialistas en Casa
# ============================================

JS_DIR="/Users/papo/especialistas-en-casa/public/js"
BACKUP_DIR="/Users/papo/especialistas-en-casa/storage/backups/js-backup-$(date +%Y%m%d_%H%M%S)"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}===========================================
${NC}"
echo -e "${YELLOW}Optimizando JavaScript para Producción${NC}"
echo -e "${YELLOW}===========================================
${NC}"

# Crear backup
echo -e "${YELLOW}Creando backup...${NC}"
mkdir -p "$BACKUP_DIR"
cp -r "$JS_DIR" "$BACKUP_DIR/"
echo -e "${GREEN}✓ Backup creado en $BACKUP_DIR${NC}"

# Contar archivos
TOTAL_FILES=$(find "$JS_DIR" -name "*.js" ! -name "*.min.js" | wc -l)
echo -e "
${YELLOW}Procesando $TOTAL_FILES archivos JavaScript...${NC}"

PROCESSED=0
MODIFIED=0

# Procesar cada archivo JS
for file in "$JS_DIR"/*.js; do
    if [ -f "$file" ] && [[ ! "$file" =~ \.min\.js$ ]]; then
        FILENAME=$(basename "$file")
        
        # Contar console.logs antes
        BEFORE=$(grep -c "console\." "$file" || true)
        
        if [ $BEFORE -gt 0 ]; then
            # Eliminar console statements pero preservar estructuras de código
            sed -i.bak -E '
                # Eliminar líneas completas con solo console
                /^[[:space:]]*console\.(log|warn|error|info|debug|trace|dir|table|time|timeEnd|group|groupEnd)\(.*\);?[[:space:]]*$/d
                # Comentar console statements en líneas mixtas
                s/([[:space:]]*)console\.(log|warn|error|info|debug|trace|dir|table|time|timeEnd|group|groupEnd)\([^)]*\);?/\1\/\/ console removed/g
            ' "$file"
            
            # Contar después
            AFTER=$(grep -c "console\." "$file" || true)
            
            if [ $BEFORE -ne $AFTER ]; then
                echo -e "  ${GREEN}✓${NC} $FILENAME - Eliminados $((BEFORE - AFTER)) console statements"
                MODIFIED=$((MODIFIED + 1))
                rm "${file}.bak"
            else
                # Restaurar si no hubo cambios
                mv "${file}.bak" "$file" 2>/dev/null || true
            fi
        fi
        
        PROCESSED=$((PROCESSED + 1))
    fi
done

echo -e "
${GREEN}Proceso completado:${NC}"
echo -e "  Archivos procesados: $PROCESSED"
echo -e "  Archivos modificados: $MODIFIED"
echo -e "  Backup guardado en: $BACKUP_DIR"
echo -e "
${YELLOW}===========================================
${NC}"
