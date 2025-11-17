#!/bin/bash

# ============================================
# Script de backup automático de base de datos
# Especialistas en Casa
# ============================================

# Configuración
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/Users/papo/especialistas-en-casa/storage/backups"
DB_NAME="especialistas_casa"
DB_USER="root"
DB_PASS=""
RETENTION_DAYS=30

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}===========================================
${NC}"
echo -e "${YELLOW}Backup de Base de Datos - Especialistas en Casa${NC}"
echo -e "${YELLOW}===========================================
${NC}"

# Crear directorio de backups si no existe
if [ ! -d "$BACKUP_DIR" ]; then
    mkdir -p "$BACKUP_DIR"
    echo -e "${GREEN}✓${NC} Directorio de backups creado"
fi

# Archivo de backup
BACKUP_FILE="$BACKUP_DIR/backup_${DB_NAME}_${TIMESTAMP}.sql"
BACKUP_FILE_GZ="${BACKUP_FILE}.gz"

# Realizar backup
echo -e "${YELLOW}Iniciando backup de la base de datos...${NC}"

if [ -z "$DB_PASS" ]; then
    mysqldump -u "$DB_USER" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null
else
    mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null
fi

# Verificar si el backup fue exitoso
if [ $? -eq 0 ]; then
    # Comprimir backup
    gzip "$BACKUP_FILE"
    
    # Obtener tamaño
    SIZE=$(du -h "$BACKUP_FILE_GZ" | cut -f1)
    
    echo -e "${GREEN}✓ Backup completado exitosamente${NC}"
    echo -e "  Archivo: $(basename $BACKUP_FILE_GZ)"
    echo -e "  Tamaño: $SIZE"
    echo -e "  Ubicación: $BACKUP_DIR"
else
    echo -e "${RED}✗ Error al crear backup${NC}"
    exit 1
fi

# Limpiar backups antiguos
echo -e "${YELLOW}
Limpiando backups antiguos (más de ${RETENTION_DAYS} días)...${NC}"
find "$BACKUP_DIR" -name "backup_*.sql.gz" -type f -mtime +$RETENTION_DAYS -delete
echo -e "${GREEN}✓ Limpieza completada${NC}"

# Mostrar estadísticas
BACKUP_COUNT=$(ls -1 "$BACKUP_DIR"/backup_*.sql.gz 2>/dev/null | wc -l)
TOTAL_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)

echo -e "
${YELLOW}Estadísticas:${NC}"
echo -e "  Backups totales: $BACKUP_COUNT"
echo -e "  Espacio usado: $TOTAL_SIZE"

echo -e "
${GREEN}Backup finalizado con éxito${NC}"
echo -e "${YELLOW}===========================================
${NC}"
