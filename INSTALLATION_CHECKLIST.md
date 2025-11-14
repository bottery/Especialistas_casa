# ✅ CHECKLIST DE INSTALACIÓN Y VERIFICACIÓN
# Especialistas en Casa

## 📋 PRE-INSTALACIÓN

### Verificar Requisitos del Sistema
- [ ] PHP 8.2 o superior instalado
- [ ] MySQL 8.0 o superior instalado  
- [ ] Composer instalado
- [ ] Servidor web configurado (Apache/Nginx) o PHP CLI
- [ ] Acceso al terminal/consola
- [ ] Permisos de escritura en el directorio

### Comandos de Verificación
```bash
# Verificar PHP
php --version
# Debe mostrar: PHP 8.2.x

# Verificar extensiones PHP requeridas
php -m | grep -E 'pdo|mysql|mbstring|json|openssl|curl'

# Verificar MySQL
mysql --version
# Debe mostrar: mysql  Ver 8.0.x

# Verificar Composer
composer --version
# Debe mostrar: Composer version 2.x
```

---

## 🚀 INSTALACIÓN PASO A PASO

### 1. Obtener el Proyecto
- [ ] Proyecto descargado/clonado
- [ ] Ubicado en directorio correcto
- [ ] Permisos de lectura verificados

```bash
cd /ruta/al/proyecto/especialistas-en-casa
ls -la
# Debes ver: composer.json, .env.example, app/, database/, etc.
```

### 2. Instalar Dependencias
- [ ] Composer ejecutado exitosamente
- [ ] Carpeta vendor/ creada
- [ ] Sin errores de dependencias

```bash
composer install --no-dev --optimize-autoloader
# Esperar a que termine...
# Verificar:
ls vendor/
# Debe mostrar: autoload.php, firebase/, phpmailer/, etc.
```

### 3. Configurar Variables de Entorno
- [ ] Archivo .env creado desde .env.example
- [ ] JWT_SECRET generado
- [ ] Credenciales de BD configuradas
- [ ] URLs configuradas correctamente

```bash
# Copiar plantilla
cp .env.example .env

# Generar JWT_SECRET
php -r "echo 'JWT_SECRET=' . bin2hex(random_bytes(32)) . PHP_EOL;"
# Copiar el resultado al .env

# Editar .env
nano .env  # o vim .env
```

**Verificar configuración mínima en .env:**
```env
APP_ENV=production          ✓
APP_DEBUG=false            ✓
APP_URL=https://tudominio.com  ✓

DB_HOST=localhost          ✓
DB_DATABASE=especialistas_casa  ✓
DB_USERNAME=tu_usuario     ✓
DB_PASSWORD=tu_password    ✓

JWT_SECRET=[64 caracteres] ✓
```

### 4. Crear Base de Datos
- [ ] Base de datos creada
- [ ] Usuario con permisos creado
- [ ] Charset UTF8MB4 configurado

```bash
# Conectar a MySQL
mysql -u root -p

# Crear base de datos
CREATE DATABASE especialistas_casa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Crear usuario (si es necesario)
CREATE USER 'especialistas'@'localhost' IDENTIFIED BY 'password_segura';
GRANT ALL PRIVILEGES ON especialistas_casa.* TO 'especialistas'@'localhost';
FLUSH PRIVILEGES;

# Verificar
SHOW DATABASES LIKE 'especialistas%';
# Debe mostrar: especialistas_casa

exit;
```

### 5. Importar Esquema
- [ ] schema.sql importado sin errores
- [ ] 12 tablas creadas
- [ ] Datos iniciales insertados
- [ ] Usuarios admin creados

```bash
# Importar esquema
mysql -u especialistas -p especialistas_casa < database/schema.sql

# Verificar tablas
mysql -u especialistas -p -e "USE especialistas_casa; SHOW TABLES;"

# Debe mostrar:
# configuraciones
# facturas
# historial_medico
# logs_auditoria
# notificaciones
# pagos
# perfiles_profesionales
# servicios
# sesiones
# solicitudes
# usuarios

# Verificar usuarios admin
mysql -u especialistas -p -e "USE especialistas_casa; SELECT email, rol FROM usuarios;"

# Debe mostrar:
# superadmin@especialistas.com | superadmin
# admin@especialistas.com      | admin
```

### 6. Configurar Permisos
- [ ] Carpeta storage con permisos 755/777
- [ ] Archivo .env con permisos 644
- [ ] Scripts ejecutables

```bash
# Permisos de storage
chmod -R 755 storage
chmod -R 777 storage/logs storage/cache storage/sessions storage/uploads

# Permisos de .env
chmod 644 .env

# Scripts ejecutables
chmod +x install.sh setup.sh

# Verificar
ls -la storage/
# Todos deben tener: drwxrwxrwx o drwxr-xr-x
```

### 7. Configurar Servidor Web

#### Opción A: PHP Built-in Server (desarrollo)
- [ ] Servidor iniciado
- [ ] Accesible en navegador
- [ ] Sin errores

```bash
cd public
php -S localhost:8000
# Abrir: http://localhost:8000
```

#### Opción B: Apache
- [ ] Virtual host configurado
- [ ] Apunta a /public
- [ ] mod_rewrite activo
- [ ] .htaccess funcionando

```bash
# Verificar mod_rewrite
apache2ctl -M | grep rewrite
# Debe mostrar: rewrite_module (shared)

# Activar si no está activo
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Opción C: Nginx
- [ ] Server block configurado
- [ ] PHP-FPM funcionando
- [ ] Configuración testeada

```bash
# Verificar configuración
sudo nginx -t
# Debe mostrar: syntax is ok

# Verificar PHP-FPM
sudo systemctl status php8.2-fpm
# Debe mostrar: active (running)
```

---

## ✅ VERIFICACIÓN POST-INSTALACIÓN

### 1. Verificar Acceso Web
- [ ] Página de inicio carga correctamente
- [ ] CSS (TailwindCSS) aplicado
- [ ] JavaScript funcional
- [ ] Sin errores 404

```
Abrir en navegador: http://localhost:8000
o https://tudominio.com

Verificar:
✓ Logo y título "Especialistas en Casa"
✓ Navbar visible
✓ Sección hero con servicios
✓ Footer presente
✓ Diseño responsive
```

### 2. Verificar API - Registro
- [ ] Endpoint /api/register funcional
- [ ] Respuesta JSON correcta
- [ ] Usuario creado en BD

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@test.com",
    "password": "Test123!",
    "nombre": "Test",
    "apellido": "User",
    "rol": "paciente"
  }'

# Respuesta esperada:
# {"success":true,"message":"Usuario registrado exitosamente","user_id":3}
```

### 3. Verificar API - Login
- [ ] Endpoint /api/login funcional
- [ ] Token JWT generado
- [ ] Usuario puede iniciar sesión

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "superadmin@especialistas.com",
    "password": "SuperAdmin2024!"
  }'

# Respuesta esperada:
# {"success":true,"access_token":"eyJ0eXAi...","user":{...}}
```

### 4. Verificar API - Servicios
- [ ] Endpoint protegido funcional
- [ ] JWT validado correctamente
- [ ] Datos retornados

```bash
# Usar token del login anterior
TOKEN="tu_token_jwt_aqui"

curl -X GET http://localhost:8000/api/paciente/servicios \
  -H "Authorization: Bearer $TOKEN"

# Respuesta esperada:
# {"success":true,"servicios":[{...},{...}]}
```

### 5. Verificar Base de Datos
- [ ] Conexión exitosa desde la aplicación
- [ ] Consultas funcionando
- [ ] Datos persistiendo

```bash
# Verificar logs
tail -f storage/logs/error.log
# No debe mostrar errores de conexión

# Verificar que el registro creó el usuario
mysql -u especialistas -p -e \
  "USE especialistas_casa; SELECT email, rol, estado FROM usuarios WHERE email='test@test.com';"

# Debe mostrar el usuario recién creado
```

### 6. Verificar Seguridad
- [ ] HTTPS configurado (producción)
- [ ] Headers de seguridad presentes
- [ ] Archivos sensibles protegidos

```bash
# Verificar headers (si está en servidor público)
curl -I https://tudominio.com

# Debe incluir:
# X-Frame-Options: DENY
# X-Content-Type-Options: nosniff
# X-XSS-Protection: 1; mode=block

# Verificar que archivos sensibles no son accesibles
curl http://localhost:8000/.env
# Debe retornar: 403 Forbidden o 404

curl http://localhost:8000/database/schema.sql
# Debe retornar: 403 Forbidden o 404
```

### 7. Verificar Logs
- [ ] Directorio logs/ escribible
- [ ] Sin errores críticos
- [ ] Auditoría funcionando

```bash
# Verificar que puede escribir logs
tail -f storage/logs/error.log
# Hacer una petición errónea intencionalmente
curl -X POST http://localhost:8000/api/login
# Debería generar una entrada en el log

# Verificar logs de auditoría
mysql -u especialistas -p -e \
  "USE especialistas_casa; SELECT COUNT(*) as total FROM logs_auditoria;"
```

---

## 🔒 CHECKLIST DE SEGURIDAD PRODUCCIÓN

### Antes de Lanzar a Producción
- [ ] APP_DEBUG=false en .env
- [ ] JWT_SECRET único y seguro (64+ caracteres)
- [ ] Contraseñas admin cambiadas
- [ ] HTTPS configurado y forzado
- [ ] Firewall configurado
- [ ] Backups automáticos configurados
- [ ] Permisos de archivos correctos
- [ ] .env no accesible públicamente
- [ ] Logs rotativos configurados
- [ ] Rate limiting implementado
- [ ] SQL injection protegido (verificado)
- [ ] XSS protegido (verificado)
- [ ] CSRF protegido (verificado)

### Comandos de Verificación de Seguridad

```bash
# Verificar .env no es accesible
curl http://tudominio.com/.env
# Debe retornar: 403 o 404

# Verificar permisos
find . -type f -perm 0777
# No debe mostrar archivos (solo carpetas storage)

# Verificar contraseñas no están en código
grep -r "password.*=" app/ config/ | grep -v "password_hash"
# No debe mostrar contraseñas en claro

# Verificar JWT_SECRET está configurado
grep "JWT_SECRET" .env
# Debe mostrar una clave de 64+ caracteres
```

---

## 📊 CHECKLIST DE FUNCIONALIDADES

### Funcionalidades Core
- [ ] Registro de usuarios funciona
- [ ] Login/Logout funciona
- [ ] Listar servicios funciona
- [ ] Solicitar servicio funciona
- [ ] Ver historial funciona
- [ ] Cancelar servicio funciona
- [ ] Tokens JWT válidos por 1 hora
- [ ] Refresh tokens funcionan

### Panel de Paciente
- [ ] Dashboard accesible
- [ ] Puede ver servicios disponibles
- [ ] Puede solicitar servicios
- [ ] Ve su historial completo
- [ ] Puede cancelar servicios pendientes

### Panel de Administrador
- [ ] Login como admin funciona
- [ ] Ve usuarios pendientes
- [ ] Puede aprobar usuarios
- [ ] Ve pagos pendientes
- [ ] Puede aprobar pagos

### Notificaciones
- [ ] Emails se envían correctamente
- [ ] OneSignal configurado (si aplica)
- [ ] Notificaciones en BD se crean

---

## 🎯 CHECKLIST FINAL

### ¿El Sistema Está Listo?
- [ ] ✅ Todos los requisitos instalados
- [ ] ✅ Base de datos configurada
- [ ] ✅ API respondiendo correctamente
- [ ] ✅ Frontend cargando
- [ ] ✅ Autenticación funcionando
- [ ] ✅ Sin errores en logs
- [ ] ✅ Seguridad verificada
- [ ] ✅ Backups configurados
- [ ] ✅ Documentación revisada
- [ ] ✅ Usuarios de prueba creados

---

## 📞 SOPORTE EN CASO DE ERRORES

### Problemas Comunes

#### Error: "No se puede conectar a la base de datos"
```bash
# Verificar credenciales en .env
cat .env | grep DB_

# Probar conexión manual
mysql -h DB_HOST -u DB_USERNAME -pDB_PASSWORD

# Verificar que MySQL está corriendo
sudo systemctl status mysql
```

#### Error: "JWT Token inválido"
```bash
# Verificar JWT_SECRET en .env
grep JWT_SECRET .env

# Regenerar si es necesario
php -r "echo bin2hex(random_bytes(32));"
```

#### Error: "Permission denied" en storage
```bash
# Dar permisos correctos
sudo chmod -R 777 storage
sudo chown -R www-data:www-data storage  # Apache
sudo chown -R nginx:nginx storage        # Nginx
```

#### Error: "500 Internal Server Error"
```bash
# Revisar logs
tail -f storage/logs/error.log
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx

# Habilitar debug temporalmente
# En .env: APP_DEBUG=true
# NO OLVIDAR DESACTIVAR EN PRODUCCIÓN
```

---

## ✅ CONFIRMACIÓN FINAL

Una vez completados todos los checks anteriores:

```
┌──────────────────────────────────────────────────────┐
│                                                      │
│  ✅ INSTALACIÓN COMPLETADA EXITOSAMENTE             │
│                                                      │
│  Sistema: Especialistas en Casa                     │
│  Versión: 1.0.0                                     │
│  Estado: OPERACIONAL                                │
│                                                      │
│  URL: http://tudominio.com                          │
│  API: http://tudominio.com/api                      │
│                                                      │
│  Admin: superadmin@especialistas.com                │
│  (Cambiar contraseña inmediatamente)                │
│                                                      │
└──────────────────────────────────────────────────────┘
```

**¡Felicidades! El sistema está listo para usar.** 🎉

---

**Documento:** Checklist de Instalación  
**Versión:** 1.0  
**Fecha:** Noviembre 2025  
**Proyecto:** Especialistas en Casa
