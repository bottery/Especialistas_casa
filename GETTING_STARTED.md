# 🚀 GUÍA DE INICIO RÁPIDO

## Estado Actual

✅ **Proyecto creado y configurado**
⏳ **PHP 8.2 se está instalando (puede tomar 5-10 minutos)**
✅ **Servidor temporal funcionando en http://localhost:8000**

## Próximos Pasos

### 1. Esperar a que termine la instalación de PHP
La instalación de PHP 8.2 con Homebrew está en progreso. Puedes ver el progreso en la terminal.

### 2. Una vez instalado PHP, ejecuta:

```bash
# Agregar PHP al PATH
export PATH="/usr/local/opt/php@8.2/bin:$PATH"

# Verificar instalación
php -v

# Iniciar el servidor
cd /Users/papo/especialistas-en-casa
./start.sh
```

### 3. Instalar MySQL (si aún no lo tienes)

```bash
brew install mysql
brew services start mysql
```

### 4. Crear la base de datos

```bash
# Conectar a MySQL
mysql -u root -p

# Crear base de datos
CREATE DATABASE especialistas_casa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Importar esquema
mysql -u root -p especialistas_casa < database/schema.sql
```

### 5. Configurar .env (Ya está creado)

El archivo `.env` ya está configurado con valores predeterminados.
Solo necesitas actualizar:

- `DB_PASSWORD` - Tu contraseña de MySQL
- `JWT_SECRET` - Ya tiene un valor aleatorio seguro

### 6. Acceder al sistema

Una vez iniciado el servidor PHP:

- **Web**: http://localhost:8000
- **Login Admin**: 
  - Email: `superadmin@especialistas.com`
  - Password: `SuperAdmin2024!`

## Comandos Útiles

```bash
# Iniciar servidor de desarrollo
./start.sh

# O manualmente:
cd public && php -S localhost:8000

# Ver logs
tail -f storage/logs/*.log

# Ejecutar composer (cuando esté disponible)
composer install
```

## Verificar Todo Funciona

### 1. Test de la API

```bash
# Probar registro
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan",
    "apellido": "Pérez",
    "email": "juan@test.com",
    "password": "12345678",
    "telefono": "3001234567",
    "tipo_usuario": "paciente"
  }'

# Probar login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "superadmin@especialistas.com",
    "password": "SuperAdmin2024!"
  }'
```

### 2. Abrir en navegador

Simplemente ve a: **http://localhost:8000**

## Estructura del Proyecto

```
especialistas-en-casa/
├── app/              # Código PHP (MVC)
├── config/           # Configuraciones
├── database/         # Schema SQL
├── public/           # Punto de entrada web
├── resources/        # Vistas y assets
├── routes/           # Definición de rutas
├── storage/          # Logs y cache
├── .env              # Variables de entorno
└── start.sh          # Script de inicio rápido
```

## Documentación

- `README.md` - Documentación completa
- `QUICKSTART.md` - Guía de inicio en 5 minutos
- `API_EXAMPLES.md` - Ejemplos de uso de API
- `DEPLOYMENT.md` - Guía de despliegue

## Solución de Problemas

### PHP no encontrado después de instalar
```bash
export PATH="/usr/local/opt/php@8.2/bin:$PATH"
# Agregar a ~/.zshrc para hacerlo permanente
echo 'export PATH="/usr/local/opt/php@8.2/bin:$PATH"' >> ~/.zshrc
```

### Error de permisos en storage
```bash
chmod -R 755 storage
```

### Error de conexión a base de datos
- Verifica que MySQL esté corriendo: `brew services list`
- Verifica credenciales en `.env`
- Verifica que la base de datos existe: `mysql -u root -p -e "SHOW DATABASES;"`

## ¿Necesitas Ayuda?

Consulta la documentación completa en:
- `README.md` - Guía técnica completa
- `PROJECT_SUMMARY.md` - Resumen del proyecto
- `INSTALLATION_CHECKLIST.md` - Checklist de verificación

---

**Tiempo estimado de instalación completa**: 15-20 minutos
**Estado actual**: ⏳ Instalando PHP 8.2...
