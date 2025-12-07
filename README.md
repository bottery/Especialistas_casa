# 🏥 VitaHome - Especialistas en Casa

<p align="center">
  <img src="public/images/vitahome-icon.svg" alt="VitaHome Logo" width="120">
</p>

<p align="center">
  <strong>Sistema de gestión de servicios médicos especializados a domicilio</strong><br>
  PHP 8.2 • Arquitectura MVC • TailwindCSS • Alpine.js
</p>

---

## 📚 DOCUMENTACIÓN COMPLETA

Este proyecto incluye documentación exhaustiva dividida en múltiples archivos:

- **[QUICKSTART.md](QUICKSTART.md)** - 🚀 Inicio rápido (5 minutos)
- **[INSTALLATION_CHECKLIST.md](INSTALLATION_CHECKLIST.md)** - ✅ Checklist paso a paso
- **[PROJECT_SUMMARY.md](PROJECT_SUMMARY.md)** - 📊 Resumen ejecutivo del proyecto
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - 🌐 Guía de despliegue en producción
- **[API_EXAMPLES.md](API_EXAMPLES.md)** - 📡 Ejemplos de uso de la API
- **[STRUCTURE.txt](STRUCTURE.txt)** - 📁 Estructura visual del proyecto
- **[FILES_CREATED.md](FILES_CREATED.md)** - 📝 Lista completa de archivos
- **[LICENSE.md](LICENSE.md)** - ⚖️ Licencia y términos de uso

**Este archivo (README.md)** contiene la documentación técnica completa.

## 📋 Características

- ✅ Autenticación JWT segura
- ✅ Gestión multi-rol (Paciente, Médico, Enfermera, Veterinario, Laboratorio, Ambulancia, Admin, SuperAdmin)
- ✅ Sistema de pagos PSE y transferencias bancarias
- ✅ Facturación digital automática
- ✅ Diseño responsive con TailwindCSS
- ✅ Notificaciones push con OneSignal
- ✅ Cumplimiento HIPAA y Habeas Data
- ✅ Compatible con hosting compartido

## 🔧 Requisitos

- PHP >= 8.2
- MySQL >= 8.0
- Composer
- Extensiones PHP: openssl, pdo, mbstring, json, curl, gd

## 🚀 Instalación

### 1. Clonar el repositorio
```bash
git clone <repository-url>
cd especialistas-en-casa
```

### 2. Instalar dependencias
```bash
composer install
```

### 3. Configurar variables de entorno
```bash
cp .env.example .env
# Editar .env con tus credenciales
```

### 4. Generar clave JWT
```bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;" > jwt_secret.txt
# Copiar el contenido al JWT_SECRET en .env
```

### 5. Crear base de datos
```bash
mysql -u root -p
CREATE DATABASE especialistas_casa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### 6. Importar esquema de base de datos
```bash
mysql -u root -p especialistas_casa < database/schema.sql
```

### 7. Crear carpetas necesarias
```bash
mkdir -p storage/logs storage/cache storage/sessions storage/uploads
chmod -R 755 storage
```

### 8. Configurar servidor web

#### Apache (.htaccess incluido)
Apuntar DocumentRoot a `/public`

#### Nginx
```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /ruta/al/proyecto/public;
    
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
}
```

## 👥 Usuarios por Defecto

Después de importar la base de datos, puedes iniciar sesión con:

**Super Administrador:**
- Email: `superadmin@especialistas.com`
- Password: `SuperAdmin2024!`

**Administrador:**
- Email: `admin@especialistas.com`
- Password: `Admin2024!`

**⚠️ IMPORTANTE:** Cambiar estas contraseñas inmediatamente en producción.

## 📂 Estructura del Proyecto

```
/especialistas-en-casa
├── /app                    # Lógica de la aplicación
│   ├── /Controllers        # Controladores MVC
│   ├── /Models            # Modelos de datos
│   ├── /Middleware        # Middleware de autenticación
│   └── /Services          # Servicios (JWT, Mail, Payment, etc.)
├── /config                # Archivos de configuración
├── /database              # Migraciones y esquemas SQL
├── /public                # Carpeta pública (DocumentRoot)
│   ├── /assets           # CSS, JS, imágenes
│   └── index.php         # Punto de entrada
├── /resources             # Vistas y componentes
│   └── /views            # Templates HTML
├── /routes                # Definición de rutas
├── /storage               # Archivos temporales y uploads
└── /vendor               # Dependencias de Composer
```

## 🔐 Seguridad

- **JWT Token**: Todas las peticiones API requieren autenticación
- **CSRF Protection**: Implementado en formularios
- **XSS Protection**: Sanitización de inputs
- **SQL Injection**: Uso de prepared statements
- **Cifrado de contraseñas**: bcrypt con cost 12
- **HTTPS**: Recomendado en producción
- **Rate Limiting**: Control de peticiones por IP

## 📡 API Endpoints

### Autenticación
```
POST   /api/register          # Registrar usuario
POST   /api/login             # Iniciar sesión
POST   /api/logout            # Cerrar sesión
POST   /api/refresh-token     # Renovar token
```

### Paciente
```
GET    /api/paciente/servicios           # Listar servicios
POST   /api/paciente/solicitar           # Solicitar servicio
GET    /api/paciente/historial           # Ver historial
GET    /api/paciente/facturas            # Ver facturas
POST   /api/paciente/upload              # Subir documentos
```

### Médico/Especialista
```
GET    /api/medico/servicios             # Ver servicios asignados
POST   /api/medico/confirmar             # Confirmar servicio
POST   /api/medico/rechazar              # Rechazar servicio
POST   /api/medico/reporte               # Crear reporte médico
POST   /api/medico/receta                # Generar receta
```

### Administrador
```
GET    /api/admin/dashboard              # Estadísticas generales
GET    /api/admin/usuarios               # Listar usuarios
POST   /api/admin/aprobar-usuario        # Aprobar médico/especialista
GET    /api/admin/pagos                  # Ver pagos pendientes
POST   /api/admin/aprobar-pago           # Aprobar pago
GET    /api/admin/servicios              # Ver todos los servicios
```

### Super Administrador
```
GET    /api/superadmin/config            # Ver configuraciones
POST   /api/superadmin/config            # Actualizar configuraciones
GET    /api/superadmin/logs              # Ver logs del sistema
POST   /api/superadmin/integraciones     # Configurar APIs externas
POST   /api/superadmin/modulos           # Activar/desactivar módulos
```

## 🧪 Testing

```bash
vendor/bin/phpunit
```

## 📱 Notificaciones

El sistema envía notificaciones por:
- **Email**: Confirmaciones, recordatorios, alertas
- **Push**: OneSignal para notificaciones en tiempo real

## 💳 Pagos

### PSE
Integración directa con pasarela de pagos PSE colombiana.

### Transferencias
Sistema de validación manual de transferencias bancarias por el administrador.

## 📊 Panel de Control

Cada rol tiene acceso a su propio dashboard con:
- Estadísticas personalizadas
- Acciones rápidas
- Historial de actividad
- Gestión de perfil

## 🌐 Responsive Design

- Mobile First
- Compatible con todos los navegadores modernos
- Modo claro/oscuro
- Interfaz intuitiva y minimalista

## 🔄 Actualizaciones

Para actualizar el sistema:
```bash
git pull origin main
composer update
# Revisar y aplicar migraciones si las hay
```

## 📞 Soporte

Para soporte técnico o reportar bugs, contactar a:
- Email: soporte@especialistasencasa.com
- Issues: GitHub Issues

## 📄 Licencia

Todos los derechos reservados © 2025 Especialistas en Casa

## 🤝 Contribuciones

Este es un proyecto privado. Para contribuir, contactar al equipo de desarrollo.

## ⚠️ Notas Importantes

1. **Producción**: Siempre usar HTTPS
2. **Backups**: Configurar backups automáticos de base de datos
3. **Logs**: Monitorear logs regularmente
4. **Actualizaciones**: Mantener PHP y dependencias actualizadas
5. **Seguridad**: Cambiar todas las credenciales por defecto

## 🛠️ Troubleshooting

### Error: "JWT Token inválido"
- Verificar que JWT_SECRET esté configurado en .env
- Verificar que el token no haya expirado

### Error: "No se puede conectar a la base de datos"
- Verificar credenciales en .env
- Verificar que MySQL esté corriendo
- Verificar permisos del usuario de base de datos

### Error: "No se pueden subir archivos"
- Verificar permisos de carpeta storage/uploads (755)
- Verificar límite de upload en php.ini

---

**Versión:** 1.0.0  
**Fecha:** Noviembre 2025  
**PHP:** 8.2+  
**MySQL:** 8.0+
