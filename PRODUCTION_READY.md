# 🚀 GUÍA DE DESPLIEGUE A PRODUCCIÓN
# Especialistas en Casa

## ✅ PRE-REQUISITOS

Antes de desplegar, asegúrate de tener:

- [x] **Servidor Web**: Apache 2.4+ o Nginx 1.18+
- [x] **PHP**: 8.2 o superior
- [x] **MySQL**: 8.0 o superior
- [x] **Composer**: Instalado
- [x] **SSL**: Certificado SSL válido
- [x] **Extensiones PHP**:
  - pdo, pdo_mysql
  - mbstring, json
  - curl, openssl
  - gd, fileinfo
  - zip, xml

---

## 🔧 CONFIGURACIÓN INICIAL

### 1. Preparar Servidor

```bash
# Clonar repositorio
git clone <tu-repositorio> /var/www/especialistas-en-casa
cd /var/www/especialistas-en-casa

# Establecer permisos
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
```

### 2. Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar configuración
nano .env
```

**Configuraciones críticas en .env:**

```bash
# PRODUCCIÓN - NO CAMBIAR
APP_ENV=production
APP_DEBUG=false

# Base de datos
DB_HOST=localhost
DB_DATABASE=especialistas_casa
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password_seguro

# JWT Secret (generar uno nuevo)
JWT_SECRET=$(php -r "echo bin2hex(random_bytes(32));")

# CORS - Solo dominios permitidos
CORS_ALLOWED_ORIGINS=https://tudominio.com,https://www.tudominio.com

# Correo (configurar con tu proveedor real)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu@email.com
MAIL_PASSWORD=tu_password_app

# OneSignal (si tienes cuenta)
ONESIGNAL_APP_ID=tu_app_id
ONESIGNAL_REST_API_KEY=tu_api_key
```

### 3. Generar JWT Secret Seguro

```bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;" > jwt_secret.txt
# Copiar el contenido a JWT_SECRET en .env
```

---

## 📦 INSTALACIÓN

### Opción A: Script Automatizado (Recomendado)

```bash
bash scripts/deploy.sh
```

### Opción B: Instalación Manual

```bash
# 1. Instalar dependencias
php composer.phar install --no-dev --optimize-autoloader

# 2. Crear estructura de directorios
mkdir -p storage/{logs,cache,sessions,uploads,backups}
mkdir -p storage/cache/rate-limits

# 3. Establecer permisos
chmod -R 755 storage
chmod -R 755 public
chmod 644 .env

# 4. Importar base de datos
mysql -u root -p < database/schema.sql

# 5. Aplicar migraciones
mysql -u root -p especialistas_casa < database/migrations/optimize_indexes.sql

# 6. Optimizar para producción
bash scripts/optimize-js.sh
```

---

## 🌐 CONFIGURACIÓN DEL SERVIDOR WEB

### Apache

```bash
# El archivo .htaccess en /public ya está optimizado
# Solo asegúrate que DocumentRoot apunte a /public

# /etc/apache2/sites-available/especialistas-en-casa.conf
<VirtualHost *:443>
    ServerName tudominio.com
    DocumentRoot /var/www/especialistas-en-casa/public
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/tudominio.com.crt
    SSLCertificateKeyFile /etc/ssl/private/tudominio.com.key
    
    <Directory /var/www/especialistas-en-casa/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

# Habilitar módulos
sudo a2enmod rewrite ssl headers deflate expires
sudo a2ensite especialistas-en-casa
sudo systemctl restart apache2
```

### Nginx

```bash
# Copiar configuración
sudo cp nginx.conf /etc/nginx/sites-available/especialistas-en-casa

# Editar y ajustar rutas/dominio
sudo nano /etc/nginx/sites-available/especialistas-en-casa

# Activar sitio
sudo ln -s /etc/nginx/sites-available/especialistas-en-casa /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## ⏰ CONFIGURAR CRON JOBS

```bash
# Editar crontab
crontab -e

# Agregar tareas programadas:

# Backup diario a las 2 AM
0 2 * * * /var/www/especialistas-en-casa/scripts/backup-db.sh >> /var/www/especialistas-en-casa/storage/logs/cron-backup.log 2>&1

# Limpieza de logs a las 3 AM
0 3 * * * /var/www/especialistas-en-casa/scripts/clean-logs.sh >> /var/www/especialistas-en-casa/storage/logs/cron-clean.log 2>&1

# Limpiar tokens expirados cada hora
0 * * * * mysql -u root -p'password' especialistas_casa -e "CALL clean_expired_tokens();" >> /var/www/especialistas-en-casa/storage/logs/cron-tokens.log 2>&1

# Limpiar rate limits cada 6 horas
0 */6 * * * find /var/www/especialistas-en-casa/storage/cache/rate-limits -type f -mmin +360 -delete
```

---

## 🔒 CHECKLIST DE SEGURIDAD

Antes de ir a producción, verifica:

- [ ] **APP_DEBUG=false** en .env
- [ ] **JWT_SECRET** único y fuerte
- [ ] **Contraseñas de DB** seguras
- [ ] **SSL/HTTPS** configurado y funcionando
- [ ] **CORS** configurado solo con dominios permitidos
- [ ] **Permisos** correctos (755 para directorios, 644 para archivos)
- [ ] **.env** no accesible vía web
- [ ] **Firewall** configurado (solo puertos 80, 443, 22)
- [ ] **Backups automáticos** configurados
- [ ] **Logs** rotando correctamente
- [ ] **Rate limiting** activo
- [ ] **Headers de seguridad** configurados

---

## 🧪 VERIFICACIÓN POST-DESPLIEGUE

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
    "timestamp": "2025-11-16 10:30:00",
    "checks": {
      "database": {"healthy": true, "message": "Database connection OK"},
      "storage": {"healthy": true, "message": "All storage directories OK"},
      "php": {"healthy": true, "message": "PHP configuration OK", "version": "8.2.x"},
      "dependencies": {"healthy": true, "message": "All dependencies installed"}
    }
  }
}
```

### 2. Verificar Funcionalidades

```bash
# Test de login
curl -X POST https://tudominio.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"admin123"}'

# Test de registro
curl -X POST https://tudominio.com/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "email":"test@example.com",
    "password":"Test123!",
    "nombre":"Test",
    "apellido":"User",
    "rol":"paciente"
  }'

# Test de servicios públicos
curl https://tudominio.com/api/servicios
```

### 3. Verificar Headers de Seguridad

```bash
curl -I https://tudominio.com
```

Debe incluir:
- `Strict-Transport-Security`
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`

---

## 📊 MONITOREO

### Logs a Revisar

```bash
# Logs de aplicación
tail -f storage/logs/error-$(date +%Y-%m-%d).log

# Logs de servidor web (Apache)
tail -f /var/log/apache2/especialistas-en-casa-error.log

# Logs de servidor web (Nginx)
tail -f /var/log/nginx/especialistas-en-casa-error.log

# Logs de PHP-FPM
tail -f /var/log/php8.2-fpm.log

# Logs de MySQL
tail -f /var/log/mysql/error.log
```

### Métricas Importantes

```bash
# Espacio en disco
df -h

# Tamaño de backups
du -sh storage/backups/

# Conexiones MySQL activas
mysql -e "SHOW PROCESSLIST;"

# Uso de memoria
free -h
```

---

## 🚨 SOLUCIÓN DE PROBLEMAS

### Error: "Database connection failed"

```bash
# Verificar credenciales en .env
grep DB_ .env

# Probar conexión manualmente
mysql -h localhost -u usuario -p database_name
```

### Error: "Permission denied"

```bash
# Corregir permisos
sudo chown -R www-data:www-data storage
sudo chmod -R 755 storage
```

### Error: "JWT Secret not configured"

```bash
# Generar y configurar nuevo secret
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
# Copiar a JWT_SECRET en .env
```

### Error 500

```bash
# Verificar logs
tail -100 storage/logs/error-$(date +%Y-%m-%d).log

# Verificar PHP errors
tail -100 /var/log/php8.2-fpm.log
```

---

## 🔄 ACTUALIZACIONES

```bash
# 1. Hacer backup
bash scripts/backup-db.sh

# 2. Poner en modo mantenimiento (opcional)
touch storage/maintenance.flag

# 3. Actualizar código
git pull origin main

# 4. Actualizar dependencias
php composer.phar install --no-dev --optimize-autoloader

# 5. Aplicar migraciones si hay
mysql -u root -p especialistas_casa < database/migrations/nueva_migracion.sql

# 6. Limpiar caché
rm -rf storage/cache/*

# 7. Optimizar
php composer.phar dump-autoload --optimize

# 8. Quitar modo mantenimiento
rm storage/maintenance.flag

# 9. Verificar
curl https://tudominio.com/api/health
```

---

## 📞 CONTACTO Y SOPORTE

Para problemas o consultas:

- **Email**: soporte@especialistasencasa.com
- **Documentación**: Ver archivos README.md y API_EXAMPLES.md
- **Logs**: Siempre revisar `storage/logs/` primero

---

## ✨ MEJORAS IMPLEMENTADAS

Este despliegue incluye:

✅ Router profesional eficiente  
✅ BaseController con métodos compartidos  
✅ Manejo global de errores  
✅ Validador centralizado  
✅ Rate limiting anti-bruteforce  
✅ CORS restrictivo  
✅ Validación profunda de archivos (MIME type)  
✅ Sistema de blacklist de tokens  
✅ Health check endpoint  
✅ Backups automáticos  
✅ Rotación de logs  
✅ Índices optimizados en BD  
✅ Headers de seguridad completos  
✅ Compresión GZIP  
✅ Cache de assets estáticos  

---

**¡El sistema está listo para producción! 🎉**

Para verificar el estado: `curl https://tudominio.com/api/health`
