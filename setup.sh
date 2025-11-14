# Crear directorio storage y subdirectorios
mkdir -p storage/logs
mkdir -p storage/cache
mkdir -p storage/sessions
mkdir -p storage/uploads

# Establecer permisos
chmod -R 755 storage

# Crear archivos .gitkeep para mantener las carpetas en git
touch storage/logs/.gitkeep
touch storage/cache/.gitkeep
touch storage/sessions/.gitkeep
touch storage/uploads/.gitkeep

echo "Estructura de directorios creada exitosamente"
