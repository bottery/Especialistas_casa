# 🌐 DESPLIEGUE EN HOSTING COMPARTIDO
# Especialistas en Casa

## 📋 REQUISITOS MÍNIMOS DEL HOSTING

Verifica que tu hosting tenga:

- ✅ **PHP 8.2+** (8.1 mínimo)
- ✅ **MySQL 8.0+** (5.7 mínimo)
- ✅ **Acceso SSH** (opcional pero recomendado)
- ✅ **cPanel o similar**
- ✅ **Extensiones PHP**:
  - pdo, pdo_mysql
  - mbstring, json
  - curl, openssl
  - fileinfo, gd

---

## 🚀 PASOS DE INSTALACIÓN

### 1. Subir Archivos

#### Opción A: FTP/SFTP (FileZilla, Cyberduck)

```
1. Conectar a tu hosting vía FTP
2. Ir a public_html/ o www/
3. Subir TODOS los archivos del proyecto
4. Asegurarte que .env NO se suba (está en .gitignore)
```

**Estructura final en el servidor:**
```
/home/usuario/
├── public_html/           (o www/)
│   ├── index.php
│   ├── .htaccess
│   ├── css/
│   ├── js/
│   └── ...
├── app/
├── config/
├── database/
├── routes/
├── storage/
├── vendor/
└── .env
```

⚠️ **IMPORTANTE**: Solo `public_html/` debe ser accesible por web.

#### Opción B: SSH (si está disponible)

```bash
# Conectar por SSH
ssh usuario@tudominio.com

# Ir a directorio home
cd ~

# Clonar repositorio
git clone tu-repositorio.git app

# Mover public a public_html
mv app/public/* public_html/
```

---

### 2. Configurar .env

Crear archivo `.env` en la raíz (al mismo nivel que `public_html/`):

```bash
# Opción 1: Por FTP - crear archivo .env y copiar contenido de .env.example

# Opción 2: Por SSH
cd ~
cp .env.example .env
nano .env
```

**Configurar valores**:

```bash
# PRODUCCIÓN
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

# BASE DE DATOS (obtener de cPanel)
DB_HOST=localhost              # O IP que proporcione el hosting
DB_DATABASE=usuario_nombredb   # Usuario_nombredb en hosting compartido
DB_USERNAME=usuario_dbuser
DB_PASSWORD=tu_password_db

# JWT SECRET - Generar uno nuevo
JWT_SECRET=tu_secret_de_64_caracteres_aqui

# CORS
CORS_ALLOWED_ORIGINS=https://tudominio.com,https://www.tudominio.com

# MAIL (usar SMTP del hosting o Gmail)
MAIL_HOST=mail.tudominio.com
MAIL_PORT=587
MAIL_USERNAME=noreply@tudominio.com
MAIL_PASSWORD=tu_password
```

**Generar JWT Secret**:
```bash
# Por SSH:
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"

# O usar: https://generate-secret.vercel.app/32
```

---

### 3. Instalar Dependencias

#### Si tienes SSH:

```bash
cd ~
php composer.phar install --no-dev --optimize-autoloader
```

#### Si NO tienes SSH:

1. Instalar en tu computadora local:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. Subir carpeta `vendor/` completa por FTP
   - ⚠️ Puede tardar (2000+ archivos)
   - Usar compresión: comprimir `vendor/` en `.zip`, subir y descomprimir en servidor

3. Alternativa: `composer.phar` ya está en el proyecto
   ```bash
   # Vía SSH
   php composer.phar install --no-dev
   ```

---

### 4. Crear Base de Datos

#### Vía cPanel:

1. **MySQL Databases** > **Create New Database**
   - Nombre: `especialistas_casa`

2. **MySQL Users** > **Add New User**
   - Usuario: crear usuario
   - Password: generar contraseña fuerte

3. **Add User To Database**
   - Usuario creado -> Database creada
   - Permisos: **ALL PRIVILEGES**

4. **Copiar credenciales al .env**

#### Importar Schema:

**Opción 1 - phpMyAdmin:**
```
1. Abrir phpMyAdmin en cPanel
2. Seleccionar base de datos
3. Tab "Import"
4. Seleccionar database/schema.sql
5. Click "Go"
6. Importar database/migrations/optimize_indexes.sql
```

**Opción 2 - SSH:**
```bash
mysql -h localhost -u usuario -p basedatos < database/schema.sql
mysql -h localhost -u usuario -p basedatos < database/migrations/optimize_indexes.sql
```

---

### 5. Configurar Permisos

#### Vía cPanel File Manager:

```
storage/          -> 755
storage/logs/     -> 755
storage/cache/    -> 755
storage/uploads/  -> 755
storage/sessions/ -> 755
.env              -> 644
```

#### Vía SSH:

```bash
cd ~
chmod -R 755 storage
chmod 644 .env
chmod 755 public_html
```

---

### 6. Ajustar Rutas (Crítico)

Si `public_html/` NO es la raíz, ajustar paths:

#### public_html/index.php:

```php
// Cambiar:
require_once __DIR__ . '/../vendor/autoload.php';

// Por:
require_once '/home/usuario/vendor/autoload.php';

// Y:
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');

// Por:
$dotenv = Dotenv\Dotenv::createImmutable('/home/usuario');
```

---

### 7. Verificar .htaccess

Asegurarte que `public_html/.htaccess` tenga:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

Si el hosting usa **LiteSpeed** en vez de Apache, puede funcionar igual.

---

### 8. Configurar SSL (HTTPS)

#### Vía cPanel:

1. **SSL/TLS Status**
2. **Run AutoSSL** (Let's Encrypt gratis)
3. Esperar activación (5-10 min)

#### Verificar:

```bash
curl -I https://tudominio.com
# Debe devolver 200 OK
```

---

### 9. Configurar Cron Jobs

#### Vía cPanel > Cron Jobs:

**Backup diario (2:00 AM):**
```
0 2 * * * /usr/bin/php /home/usuario/scripts/backup-db.sh
```

**Limpieza de logs (3:00 AM):**
```
0 3 * * * /usr/bin/php /home/usuario/scripts/clean-logs.sh
```

**Limpiar tokens expirados (cada hora):**
```
0 * * * * mysql -h localhost -u usuario -p'password' basedatos -e "CALL clean_expired_tokens();"
```

---

## 🧪 VERIFICACIÓN

### 1. Health Check

```bash
curl https://tudominio.com/api/health
```

Debe retornar:
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    ...
  }
}
```

### 2. Test Frontend

Abrir en navegador:
- `https://tudominio.com` - Home page
- `https://tudominio.com/login` - Login
- `https://tudominio.com/register` - Registro

### 3. Test API

```bash
# Servicios públicos
curl https://tudominio.com/api/servicios

# Login
curl -X POST https://tudominio.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"test123"}'
```

---

## 🚨 PROBLEMAS COMUNES

### Error: "500 Internal Server Error"

**Solución:**
1. Verificar permisos de `storage/`
2. Revisar error log en cPanel
3. Verificar que `.htaccess` existe
4. Comprobar que `vendor/` está completo

### Error: "Database connection failed"

**Solución:**
1. Verificar credenciales en `.env`
2. Usar host correcto (puede no ser `localhost`)
3. Verificar que usuario tiene permisos
4. Probar conexión en phpMyAdmin

### Error: "JWT Secret not configured"

**Solución:**
1. Generar secret con: `php -r "echo bin2hex(random_bytes(32));"`
2. Copiar a `JWT_SECRET` en `.env`
3. Asegurarse que `.env` está en la raíz correcta

### Error: "Class not found"

**Solución:**
1. Reinstalar vendor: `php composer.phar install`
2. Verificar rutas en `index.php`
3. Limpiar cache: `rm -rf storage/cache/*`

### Error: "Too many redirects"

**Solución:**
1. Comentar líneas de HTTPS en `.htaccess` si SSL no está activo
2. Verificar configuración de SSL en cPanel

---

## 📊 MONITOREO EN HOSTING COMPARTIDO

### Logs

**Vía cPanel:**
- **Error Log** en cPanel
- `storage/logs/error-YYYY-MM-DD.log`

**Vía SSH:**
```bash
tail -f ~/storage/logs/error-$(date +%Y-%m-%d).log
```

### Uso de Recursos

En cPanel:
- **Resource Usage** - Ver uso de CPU/RAM
- **Database** - Ver tamaño de BD
- **Disk Usage** - Ver espacio usado

---

## 💡 OPTIMIZACIONES ADICIONALES

### Cache de OPcache

Si disponible, habilitar en cPanel > PHP Settings:
- `opcache.enable = 1`
- `opcache.memory_consumption = 128`
- `opcache.max_accelerated_files = 10000`

### Compresión

Verificar que `.htaccess` tiene:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript application/json
</IfModule>
```

### Cache Browser

Headers en `.htaccess`:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
</IfModule>
```

---

## 📞 SOPORTE HOSTING

Si tienes problemas con:
- Permisos
- Extensiones PHP
- Límites de memoria
- Cron jobs

**Contactar soporte del hosting** con estos datos:
- Versión PHP: 8.2+
- Extensiones necesarias: pdo, pdo_mysql, mbstring, curl, openssl
- Memory limit: 256M recomendado
- Max execution time: 300 segundos

---

## ✅ CHECKLIST HOSTING COMPARTIDO

- [ ] Archivos subidos correctamente
- [ ] .env configurado (DB, JWT, CORS)
- [ ] vendor/ instalado
- [ ] Base de datos creada e importada
- [ ] Permisos configurados (755/644)
- [ ] SSL/HTTPS activo
- [ ] .htaccess funcionando
- [ ] Cron jobs configurados
- [ ] Health check OK
- [ ] Frontend cargando
- [ ] API respondiendo

---

## 🎉 ¡LISTO!

Tu sistema **Especialistas en Casa** está ahora en producción en hosting compartido.

**Verificación final:**
```
✅ https://tudominio.com/api/health
✅ https://tudominio.com
✅ https://tudominio.com/login
```

Para actualizaciones futuras, repetir pasos 1-3 (subir archivos, actualizar .env si necesario, actualizar vendor).

---

**Última actualización**: 16 de noviembre de 2025
