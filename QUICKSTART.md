# 🚀 GUÍA DE INICIO RÁPIDO
# Especialistas en Casa

## ⚡ Instalación Rápida (5 minutos)

### 1. Requisitos Previos
Antes de comenzar, asegúrate de tener instalado:
- PHP 8.2 o superior
- MySQL 8.0 o superior
- Composer
- Servidor web (Apache/Nginx) o PHP built-in server

### 2. Instalación Automática

```bash
# Dar permisos de ejecución al script
chmod +x install.sh

# Ejecutar instalación
./install.sh
```

### 3. Configuración Manual (alternativa)

```bash
# Instalar dependencias
composer install

# Crear archivo de configuración
cp .env.example .env

# Generar clave JWT
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;" > jwt_key.txt
# Copiar la clave generada al JWT_SECRET en .env

# Crear directorios
mkdir -p storage/logs storage/cache storage/sessions storage/uploads
chmod -R 755 storage
```

### 4. Configurar Base de Datos

**Editar .env:**
```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=especialistas_casa
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

**Crear base de datos:**
```bash
mysql -u root -p
```

```sql
CREATE DATABASE especialistas_casa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

**Importar esquema:**
```bash
mysql -u root -p especialistas_casa < database/schema.sql
```

### 5. Iniciar Servidor de Desarrollo

**Opción A: PHP Built-in Server (más rápido para desarrollo)**
```bash
cd public
php -S localhost:8000
```

**Opción B: Apache**
```apache
<VirtualHost *:80>
    ServerName especialistas.local
    DocumentRoot /ruta/al/proyecto/public
    
    <Directory /ruta/al/proyecto/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Opción C: Nginx**
```nginx
server {
    listen 80;
    server_name especialistas.local;
    root /ruta/al/proyecto/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 6. Acceder a la Aplicación

Abre tu navegador en:
- **Desarrollo:** http://localhost:8000
- **Apache/Nginx:** http://especialistas.local

### 7. Credenciales por Defecto

**Super Administrador:**
- Email: `superadmin@especialistas.com`
- Password: `SuperAdmin2024!`

**Administrador:**
- Email: `admin@especialistas.com`
- Password: `Admin2024!`

**⚠️ IMPORTANTE:** Cambiar estas contraseñas inmediatamente en producción.

---

## 🧪 Probar la API

### Registrar un nuevo paciente
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "paciente@test.com",
    "password": "Password123!",
    "nombre": "Juan",
    "apellido": "Pérez",
    "rol": "paciente"
  }'
```

### Iniciar sesión
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "paciente@test.com",
    "password": "Password123!"
  }'
```

### Listar servicios disponibles
```bash
curl -X GET http://localhost:8000/api/paciente/servicios \
  -H "Authorization: Bearer TU_TOKEN_JWT"
```

---

## 📱 Estructura de URLs

### Rutas Web
- `/` - Página principal
- `/login` - Iniciar sesión
- `/register` - Registro
- `/paciente/dashboard` - Dashboard del paciente
- `/medico/dashboard` - Dashboard del médico
- `/admin/dashboard` - Dashboard del administrador

### Rutas API
- `POST /api/register` - Registro
- `POST /api/login` - Login
- `GET /api/paciente/servicios` - Listar servicios
- `POST /api/paciente/solicitar` - Solicitar servicio
- `GET /api/paciente/historial` - Ver historial
- Consulta `routes/api.php` para más endpoints

---

## 🔧 Configuración Adicional

### Configurar Email (Mailtrap para desarrollo)
```env
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_username
MAIL_PASSWORD=tu_password
```

### Configurar OneSignal (Notificaciones Push)
```env
ONESIGNAL_APP_ID=tu_app_id
ONESIGNAL_REST_API_KEY=tu_api_key
```

### Configurar PSE (Pagos)
```env
PSE_MERCHANT_ID=tu_merchant_id
PSE_API_KEY=tu_api_key
PSE_API_SECRET=tu_api_secret
PSE_SANDBOX=true
```

---

## 🐛 Solución de Problemas

### Error: "JWT_SECRET no está configurado"
```bash
# Generar nueva clave
php -r "echo bin2hex(random_bytes(32));"
# Copiar al .env en JWT_SECRET=
```

### Error: "No se puede conectar a la base de datos"
- Verificar credenciales en .env
- Verificar que MySQL esté corriendo: `mysql --version`
- Verificar que la base de datos exista

### Error: "Permission denied" en storage
```bash
chmod -R 755 storage
```

### Error 404 en todas las rutas
- Verificar que mod_rewrite esté activado (Apache)
- Verificar archivo .htaccess en /public

---

## 📚 Documentación Completa

Consulta el archivo **README.md** para documentación completa sobre:
- Arquitectura del sistema
- Todos los endpoints API
- Seguridad y cumplimiento
- Testing
- Deployment en producción

---

## 🆘 Soporte

- **Email:** soporte@especialistasencasa.com
- **Documentación:** README.md
- **Errores:** Revisar `storage/logs/error.log`

---

**¡Listo! Tu aplicación está funcionando.** 🎉
