#!/bin/bash

echo "🗄️  Inicializando Base de Datos..."

# Crear BD (sin contraseña para root local de macOS)
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS especialistas_casa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

if [ $? -ne 0 ]; then
    echo "❌ Error: No se pudo conectar a MySQL"
    echo "Verifica que MySQL esté corriendo: brew services start mysql"
    exit 1
fi

echo "✅ Base de datos creada"

# Importar schema
echo "Importando schema..."
mysql -uroot especialistas_casa < database/schema.sql

echo "✅ Schema importado"

# Aplicar migraciones
echo "Aplicando migraciones..."
mysql -uroot especialistas_casa < database/migrations/optimize_indexes.sql 2>/dev/null

# Crear usuario de prueba
echo "Creando usuario admin..."
mysql -uroot especialistas_casa <<EOF
INSERT IGNORE INTO usuarios (email, password, nombre, apellido, telefono, rol, estado, verificado, created_at)
VALUES (
    'admin@especialistas.com',
    '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Super',
    'Admin',
    '3001234567',
    'superadmin',
    'activo',
    1,
    NOW()
);
EOF

echo ""
echo "🎉 ¡Listo! Inicia sesión con:"
echo "  📧 admin@especialistas.com"
echo "  🔑 password"
