# 🚀 GUÍA DE DESPLIEGUE
# Especialistas en Casa - Producción

## 📋 PRE-REQUISITOS DEL SERVIDOR

### Requisitos Mínimos
- **PHP:** 8.2 o superior
- **MySQL:** 8.0 o superior
- **Memoria:** 512 MB RAM (recomendado 1GB+)
- **Espacio:** 500 MB mínimo
- **SSL:** Certificado válido (requerido para producción)

### Extensiones PHP Requeridas
```
- openssl
- pdo_mysql
- mbstring
- json
- curl
- gd
- zip
```

---

## 🌐 DESPLIEGUE EN HOSTING COMPARTIDO

### 1. Preparar Archivos Localmente

```bash
# Clonar o descargar el proyecto
cd especialistas-en-casa

# Instalar dependencias de producción
composer install --no-dev --optimize-autoloader

# Limpiar archivos innecesarios
rm -rf .git
rm -rf tests
rm install.sh setup.sh
```

### 2. Comprimir Proyecto

```bash
# Crear archivo comprimido
tar -czf especialistas-en-casa.tar.gz .
```

### 3. Subir al Servidor

**Opción A: FTP/SFTP**
- Subir `especialistas-en-casa.tar.gz` a la raíz del hosting
- Extraer: `tar -xzf especialistas-en-casa.tar.gz`

**Opción B: cPanel File Manager**
- Subir archivo comprimido
- Extraer desde el File Manager

### 4. Configurar .env en Servidor

```bash
# Copiar plantilla
cp .env.example .env

# Editar con nano o vim
nano .env
```

**Configuración de Producción:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_HOST=localhost
DB_DATABASE=tu_base_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña_segura

JWT_SECRET=tu_clave_jwt_de_64_caracteres_generada

MAIL_HOST=smtp.tuservidor.com
MAIL_USERNAME=noreply@tudominio.com
MAIL_PASSWORD=tu_contraseña
```

### 5. Configurar Base de Datos

**En cPanel > MySQL:**
```sql
CREATE DATABASE nombre_bd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'usuario'@'localhost' IDENTIFIED BY 'contraseña_segura';
GRANT ALL PRIVILEGES ON nombre_bd.* TO 'usuario'@'localhost';
FLUSH PRIVILEGES;
```

**Importar Esquema:**
```bash
mysql -u usuario -p nombre_bd < database/schema.sql
```

### 6. Configurar Dominio

**En cPanel > Dominios:**
- Agregar dominio o subdominio
- Apuntar DocumentRoot a: `/public_html/especialistas-en-casa/public`

### 7. Permisos de Archivos

```bash
# Permisos correctos
chmod -R 755 .
chmod -R 777 storage
chmod 644 .env
```

### 8. SSL (HTTPS)

**En cPanel > SSL/TLS:**
- Instalar certificado Let's Encrypt gratuito
- O subir certificado comprado
- Forzar HTTPS en .htaccess:

```apache
# Descomentar en public/.htaccess
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 🐳 DESPLIEGUE CON VPS/SERVIDOR DEDICADO

### Opción 1: Ubuntu Server con Apache

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar LAMP Stack
sudo apt install apache2 mysql-server php8.2 php8.2-{cli,fpm,mysql,xml,mbstring,curl,gd,zip} -y

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Clonar proyecto
cd /var/www/html
sudo git clone <tu-repo> especialistas-en-casa
cd especialistas-en-casa

# Instalar dependencias
composer install --no-dev --optimize-autoloader

# Configurar permisos
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 storage

# Configurar .env
sudo cp .env.example .env
sudo nano .env

# Configurar Virtual Host
sudo nano /etc/apache2/sites-available/especialistas.conf
```

**Virtual Host de Apache:**
```apache
<VirtualHost *:80>
    ServerName tudominio.com
    ServerAlias www.tudominio.com
    DocumentRoot /var/www/html/especialistas-en-casa/public
    
    <Directory /var/www/html/especialistas-en-casa/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/especialistas_error.log
    CustomLog ${APACHE_LOG_DIR}/especialistas_access.log combined
</VirtualHost>
```

```bash
# Activar sitio y mod_rewrite
sudo a2ensite especialistas.conf
sudo a2enmod rewrite
sudo systemctl restart apache2

# Configurar MySQL
sudo mysql_secure_installation
sudo mysql -u root -p < database/schema.sql

# Instalar SSL con Certbot
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d tudominio.com -d www.tudominio.com
```

### Opción 2: Nginx con PHP-FPM

```bash
# Instalar Nginx y PHP
sudo apt install nginx php8.2-fpm -y

# Configurar Nginx
sudo nano /etc/nginx/sites-available/especialistas
```

**Configuración Nginx:**
```nginx
server {
    listen 80;
    server_name tudominio.com www.tudominio.com;
    root /var/www/html/especialistas-en-casa/public;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Activar sitio
sudo ln -s /etc/nginx/sites-available/especialistas /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# SSL con Certbot
sudo certbot --nginx -d tudominio.com
```

---

## 🔒 SEGURIDAD POST-DESPLIEGUE

### 1. Cambiar Credenciales por Defecto

```sql
-- Conectar a MySQL
mysql -u root -p nombre_bd

-- Cambiar contraseñas de admin
UPDATE usuarios 
SET password = '$2y$12$NUEVA_CONTRASEÑA_HASHEADA' 
WHERE email IN ('superadmin@especialistas.com', 'admin@especialistas.com');

-- O desde la interfaz web después de login
```

### 2. Configurar Firewall

```bash
# UFW en Ubuntu
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 3306/tcp  # Solo si MySQL es externo
sudo ufw enable
```

### 3. Configurar Backups Automáticos

```bash
# Crear script de backup
sudo nano /usr/local/bin/backup-especialistas.sh
```

```bash
#!/bin/bash
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/especialistas"
DB_NAME="especialistas_casa"
DB_USER="usuario"
DB_PASS="contraseña"

# Crear directorio
mkdir -p $BACKUP_DIR

# Backup de base de datos
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$TIMESTAMP.sql.gz

# Backup de archivos
tar -czf $BACKUP_DIR/files_$TIMESTAMP.tar.gz /var/www/html/especialistas-en-casa/storage

# Eliminar backups antiguos (más de 30 días)
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completado: $TIMESTAMP"
```

```bash
# Dar permisos
sudo chmod +x /usr/local/bin/backup-especialistas.sh

# Programar en crontab (diario a las 2 AM)
sudo crontab -e
# Agregar: 0 2 * * * /usr/local/bin/backup-especialistas.sh
```

### 4. Monitoreo de Logs

```bash
# Ver logs en tiempo real
sudo tail -f /var/log/apache2/especialistas_error.log
sudo tail -f storage/logs/error.log

# Limpiar logs antiguos
find storage/logs -type f -mtime +90 -delete
```

---

## ⚙️ OPTIMIZACIONES DE PRODUCCIÓN

### 1. PHP Configuration (php.ini)

```ini
; Ajustar límites
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300

; Optimizar OPcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### 2. MySQL Optimization

```sql
-- Optimizar tablas
OPTIMIZE TABLE usuarios, solicitudes, pagos, facturas;

-- Crear índices adicionales si es necesario
CREATE INDEX idx_fecha_programada ON solicitudes(fecha_programada);
CREATE INDEX idx_estado_pagado ON solicitudes(estado, pagado);
```

### 3. Habilitar Caché

```bash
# En .env
CACHE_DRIVER=file
```

---

## 📊 MONITOREO Y MANTENIMIENTO

### Tareas Periódicas

**Diarias:**
- Revisar logs de errores
- Verificar backups
- Monitorear uso de recursos

**Semanales:**
- Revisar estadísticas de uso
- Limpiar logs antiguos
- Actualizar dependencias de seguridad

**Mensuales:**
- Optimizar base de datos
- Revisar rendimiento
- Actualizar certificados SSL

### Comandos Útiles

```bash
# Ver uso de disco
df -h

# Ver uso de memoria
free -m

# Ver procesos PHP
ps aux | grep php

# Reiniciar servicios
sudo systemctl restart apache2
sudo systemctl restart mysql
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

---

## 🆘 TROUBLESHOOTING EN PRODUCCIÓN

### Error 500
- Revisar logs: `storage/logs/error.log`
- Verificar permisos de storage
- Verificar .env configurado correctamente

### Base de datos no conecta
- Verificar credenciales en .env
- Verificar que MySQL esté corriendo
- Revisar firewall

### Emails no se envían
- Verificar credenciales SMTP en .env
- Revisar logs de PHPMailer
- Probar con Mailtrap primero

---

## ✅ CHECKLIST DE DESPLIEGUE

- [ ] Servidor configurado (PHP 8.2+, MySQL 8+)
- [ ] Proyecto subido y extraído
- [ ] .env configurado con credenciales reales
- [ ] Base de datos creada e importada
- [ ] Permisos de archivos configurados (755/777)
- [ ] Dominio apuntando al directorio /public
- [ ] SSL instalado y forzado (HTTPS)
- [ ] Contraseñas admin cambiadas
- [ ] JWT_SECRET único generado
- [ ] Backups automáticos configurados
- [ ] Firewall configurado
- [ ] Logs monitoreados
- [ ] Integraciones externas configuradas (OneSignal, PSE)
- [ ] Testing completo realizado
- [ ] Documentación actualizada

---

**¡Tu aplicación está lista para producción!** 🚀

Para soporte adicional, consulta:
- README.md
- PROJECT_SUMMARY.md
- QUICKSTART.md
