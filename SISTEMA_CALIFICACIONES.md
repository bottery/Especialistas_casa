# Sistema de Calificaciones y Reportes Finales

## ✅ Implementación Completada

### 🗄️ Base de Datos
Se agregaron los siguientes campos a la tabla `solicitudes`:
- `calificacion_paciente` INT - Calificación de 1 a 5
- `comentario_paciente` TEXT - Comentario del paciente
- `fecha_calificacion` TIMESTAMP - Cuándo se calificó
- `calificado` BOOLEAN - Si ya fue calificado
- `reporte_profesional` TEXT - **Reporte final del profesional**
- `diagnostico` TEXT - **Diagnóstico o conclusiones del profesional**

Se agregaron los siguientes campos a la tabla `usuarios`:
- `puntuacion_promedio` DECIMAL(3,2) - Promedio de calificaciones
- `total_calificaciones` INT - Total de servicios calificados
- `servicios_completados` INT - Total de servicios completados

---

## 📋 Flujo Completo del Servicio

### 1️⃣ **Profesional acepta la solicitud**
- Estado: `asignado` → `en_proceso`
- Endpoint: `POST /api/profesional/solicitudes/{id}/aceptar`

### 2️⃣ **Profesional completa el servicio**
- Estado: `en_proceso` → `completado`
- Endpoint: `POST /api/profesional/solicitudes/{id}/completar`
- **Campos requeridos:**
  ```json
  {
    "reporte": "Reporte detallado del servicio prestado",
    "diagnostico": "Diagnóstico o conclusiones médicas/profesionales",
    "notas": "Notas adicionales opcionales"
  }
  ```
- ✅ Incrementa automáticamente `servicios_completados` del profesional

### 3️⃣ **Paciente califica el servicio**
- Estado: Se mantiene en `completado`
- Campo: `calificado` cambia a TRUE
- Endpoint: `POST /api/paciente/calificar/{id}`
- **Campos requeridos:**
  ```json
  {
    "calificacion": 5,  // 1 a 5
    "comentario": "Excelente servicio, muy profesional"
  }
  ```
- ✅ Recalcula automáticamente `puntuacion_promedio` del profesional
- ✅ Incrementa `total_calificaciones` del profesional

---

## 🔌 API Endpoints Implementados

### Para Pacientes

#### 📄 Obtener reporte final de servicio
```http
GET /api/paciente/reporte/{solicitud_id}
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "success": true,
  "reporte": {
    "solicitud_id": 34,
    "fecha_servicio": "2025-11-20 03:51:00",
    "fecha_completado": "2025-11-16 23:00:00",
    "profesional": {
      "nombre": "Dr. Carlos Rodríguez",
      "tipo": "medico",
      "especialidad": "Medicina General",
      "puntuacion": 5.00,
      "total_calificaciones": 15
    },
    "reporte_profesional": "El paciente presentó síntomas de...",
    "diagnostico": "Gripe común con complicaciones respiratorias leves",
    "notas_adicionales": "Se recetó paracetamol cada 8 horas",
    "calificacion": {
      "calificado": true,
      "puntuacion": 5,
      "comentario": "Excelente atención",
      "fecha": "2025-11-16 23:15:00"
    }
  }
}
```

#### ⭐ Calificar servicio
```http
POST /api/paciente/calificar/{solicitud_id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "calificacion": 5,
  "comentario": "Excelente servicio"
}
```

**Validaciones:**
- La solicitud debe estar en estado `completado`
- El campo `calificado` debe ser FALSE
- La calificación debe estar entre 1 y 5
- Solo el paciente dueño de la solicitud puede calificar

**Respuesta:**
```json
{
  "success": true,
  "message": "¡Gracias por tu calificación!",
  "solicitud_id": 34,
  "nueva_puntuacion": 4.85
}
```

---

### Para Administradores

#### 📊 Obtener lista de reportes
```http
GET /api/admin/reportes
Authorization: Bearer {token}

# Filtros opcionales:
?fecha_desde=2025-11-01
&fecha_hasta=2025-11-30
&profesional_id=4
&calificado=true
```

**Respuesta:**
```json
{
  "reportes": [
    {
      "id": 34,
      "fecha_programada": "2025-11-20 03:51:00",
      "fecha_completada": "2025-11-16 23:00:00",
      "estado": "completado",
      "calificado": true,
      "calificacion_paciente": 5,
      "paciente_nombre": "Juan",
      "paciente_apellido": "Pérez",
      "paciente_email": "paciente@test.com",
      "profesional_nombre": "Carlos",
      "profesional_apellido": "Rodríguez",
      "tipo_profesional": "medico",
      "especialidad": "Medicina General",
      "puntuacion_promedio": 4.85,
      "servicio_nombre": "Consulta en Consultorio",
      "servicio_tipo": "medico",
      "monto_total": "60000.00",
      "monto_profesional": "51000.00",
      "monto_plataforma": "9000.00"
    }
  ]
}
```

#### 📄 Ver reporte detallado
```http
GET /api/admin/reportes/{solicitud_id}
Authorization: Bearer {token}
```

**Respuesta:** Similar al endpoint del paciente, pero incluye información financiera adicional:
```json
{
  "reporte": {
    "solicitud_id": 34,
    "estado": "completado",
    "fecha_solicitud": "2025-11-15 10:52:04",
    "fecha_programada": "2025-11-20 03:51:00",
    "fecha_completada": "2025-11-16 23:00:00",
    "paciente": {
      "nombre": "Juan Pérez",
      "email": "paciente@test.com",
      "telefono": "3135770442"
    },
    "profesional": {
      "nombre": "Dr. Carlos Rodríguez",
      "tipo": "medico",
      "especialidad": "Medicina General",
      "puntuacion_promedio": 4.85,
      "total_calificaciones": 15,
      "servicios_completados": 42
    },
    "servicio": {
      "nombre": "Consulta en Consultorio",
      "tipo": "medico",
      "descripcion": "Consulta médica general",
      "modalidad": "presencial"
    },
    "reporte_profesional": "El paciente presentó...",
    "diagnostico": "Gripe común...",
    "notas_adicionales": "Se recetó...",
    "finanzas": {
      "monto_total": "60000.00",
      "monto_profesional": "51000.00",
      "monto_plataforma": "9000.00",
      "pagado": true
    },
    "calificacion": {
      "calificado": true,
      "puntuacion": 5,
      "comentario": "Excelente atención",
      "fecha": "2025-11-16 23:15:00"
    }
  }
}
```

---

## 🎯 Casos de Uso

### Para el Paciente
1. **Ver reporte del profesional:**
   - Acceder a diagnóstico, reporte y recomendaciones
   - Ver credenciales y calificación del profesional
   - Historial médico del servicio prestado

2. **Calificar el servicio:**
   - Dar puntuación de 1 a 5 estrellas
   - Dejar comentario sobre la experiencia
   - Ayudar a otros pacientes a elegir profesionales

### Para la Plataforma (Admin)
1. **Control de calidad:**
   - Revisar reportes de todos los servicios
   - Identificar profesionales con baja calificación
   - Filtrar por fecha, profesional o estado de calificación

2. **Análisis de negocio:**
   - Ver ingresos por servicio
   - Identificar profesionales más solicitados
   - Monitorear servicios completados vs cancelados

3. **Auditoría:**
   - Verificar que los profesionales están entregando reportes completos
   - Validar diagnósticos y tratamientos
   - Resolver disputas entre pacientes y profesionales

---

## 🔄 Actualización Automática de Estadísticas

### Al completar un servicio:
- ✅ Incrementa `servicios_completados` del profesional
- ✅ Guarda `reporte_profesional` y `diagnostico`
- ✅ Registra `fecha_completada`

### Al calificar un servicio:
- ✅ Calcula promedio de todas las calificaciones del profesional
- ✅ Actualiza `puntuacion_promedio` (redondeo a 2 decimales)
- ✅ Incrementa `total_calificaciones`
- ✅ Marca `calificado = TRUE`
- ✅ Registra `fecha_calificacion`

---

## ⚠️ Consideraciones Importantes

### Seguridad
- ✅ Solo el paciente dueño puede calificar su servicio
- ✅ Solo el profesional asignado puede completar el servicio
- ✅ No se puede calificar dos veces la misma solicitud
- ✅ Solo se pueden calificar servicios completados

### Validaciones
- Calificación debe estar entre 1 y 5
- El servicio debe estar en estado `completado`
- El campo `calificado` debe ser FALSE antes de calificar
- El profesional debe proporcionar `reporte` y `diagnostico` al completar

### Transacciones
- La calificación usa transacciones de BD para garantizar consistencia
- Si falla el recálculo del promedio, se hace rollback completo
- Los campos del profesional se actualizan atómicamente

---

## 📝 Próximas Mejoras Sugeridas

1. **Notificaciones:**
   - Notificar al paciente cuando el profesional complete el servicio
   - Recordatorio automático para calificar (24h después de completado)

2. **Sistema de badges:**
   - "Mejor calificado del mes"
   - "100 servicios completados"
   - "Puntualidad perfecta"

3. **Análisis de sentimiento:**
   - Analizar comentarios para detectar problemas
   - Palabras clave positivas/negativas

4. **Exportación:**
   - PDF del reporte para el paciente
   - CSV de reportes para análisis en Excel

---

## 🧪 Testing

### Credenciales de prueba:
- **Paciente:** paciente@test.com / password (ID: 8)
- **Profesional:** medico1@test.com / password (ID: 4)
- **Admin:** admin@especialistas.com / password

### Flujo de prueba completo:
1. Login como profesional (medico1@test.com)
2. Aceptar solicitud #34 (si aún está en estado `asignado`)
3. Completar solicitud con reporte y diagnóstico
4. Logout y login como paciente (paciente@test.com)
5. Ver reporte del servicio
6. Calificar el servicio
7. Login como admin para ver reportes

---

## 📌 Resumen de Archivos Modificados

### Backend
- ✅ `app/Controllers/ProfesionalController.php` - Método `completarServicio()` actualizado
- ✅ `app/Controllers/PacienteController.php` - Métodos `calificarServicio()` y `obtenerReporteFinal()` corregidos
- ✅ `app/Controllers/AdminController.php` - Métodos `obtenerReportes()` y `verReporte()` agregados
- ✅ `routes/api.php` - Rutas agregadas

### Base de Datos
- ✅ Campos agregados a `solicitudes`
- ✅ Campos agregados a `usuarios`
- ✅ Índices optimizados para consultas de calificación

---

## 🎉 Sistema Listo para Producción

El sistema de calificaciones está completamente funcional y listo para usar. Incluye:
- ✅ Reportes profesionales detallados
- ✅ Sistema de calificación de 5 estrellas
- ✅ Actualización automática de estadísticas
- ✅ Endpoints para pacientes, profesionales y administradores
- ✅ Validaciones de seguridad completas
- ✅ Transacciones para garantizar consistencia de datos
