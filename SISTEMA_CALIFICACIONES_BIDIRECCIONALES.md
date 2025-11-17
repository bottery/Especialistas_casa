# Sistema de Calificaciones Bidireccionales

## 📋 Resumen

Sistema de evaluación mutua obligatoria entre pacientes y profesionales para mantener la calidad del servicio en ambas direcciones.

## 🎯 Características Principales

### 1. **Calificación del Paciente al Profesional** (OBLIGATORIA ⚠️)
- **Quién**: Pacientes que han recibido un servicio completado
- **Cuándo**: Después de que el profesional completa el servicio
- **Obligatoriedad**: El paciente DEBE calificar antes de poder solicitar nuevos servicios
- **Rango**: 1 a 5 estrellas
- **Comentarios**: Opcional (texto libre)

### 2. **Calificación del Profesional al Paciente** (OPCIONAL)
- **Quién**: Profesionales que han completado un servicio
- **Cuándo**: Después de completar el servicio
- **Obligatoriedad**: Opcional (recomendada)
- **Rango**: 1 a 5 estrellas
- **Comentarios**: Opcional (texto libre)
- **Propósito**: Evaluar cooperación, puntualidad, trato del paciente

## 📊 Esquema de Base de Datos

### Tabla: `solicitudes`

```sql
-- Calificación Paciente → Profesional
calificacion_paciente INT NULL                    -- 1-5 estrellas
comentario_paciente TEXT NULL                      -- Opinión del paciente
fecha_calificacion TIMESTAMP NULL                  -- Fecha de calificación
calificado BOOLEAN DEFAULT FALSE                   -- Flag de calificación completada

-- Calificación Profesional → Paciente
calificacion_profesional INT NULL                  -- 1-5 estrellas
comentario_profesional TEXT NULL                   -- Opinión del profesional
fecha_calificacion_profesional TIMESTAMP NULL      -- Fecha de calificación
```

### Tabla: `usuarios`

```sql
-- Estadísticas como Profesional
puntuacion_promedio DECIMAL(3,2) DEFAULT 5.00     -- Promedio de calificaciones recibidas
total_calificaciones INT DEFAULT 0                 -- Total de calificaciones
servicios_completados INT DEFAULT 0                -- Servicios completados

-- Estadísticas como Paciente
puntuacion_promedio_paciente DECIMAL(3,2) DEFAULT 5.00  -- Promedio como paciente
total_calificaciones_paciente INT DEFAULT 0             -- Total de calificaciones recibidas
```

## 🔌 API Endpoints

### **Paciente**

#### 1. Calificar Profesional (OBLIGATORIO)
```http
POST /api/paciente/calificar/{solicitud_id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "calificacion": 5,
  "comentario": "Excelente servicio, muy profesional"
}
```

**Validaciones:**
- La solicitud debe estar en estado `completado`
- `fecha_completada` no debe ser NULL
- `calificado` debe ser FALSE (no calificada previamente)
- Calificación debe estar entre 1 y 5

**Respuesta exitosa:**
```json
{
  "message": "✅ Gracias por tu evaluación",
  "solicitud_id": 34,
  "puntuacion_profesional": 4.85
}
```

#### 2. Obtener Servicios Pendientes de Calificar
```http
GET /api/paciente/servicios-pendientes-calificar
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "pendientes": [
    {
      "id": 34,
      "fecha_completada": "2024-01-15 14:30:00",
      "servicio_nombre": "Consulta Médica General",
      "profesional_nombre": "Juan",
      "profesional_apellido": "Pérez",
      "reporte_profesional": "...",
      "diagnostico": "..."
    }
  ],
  "total": 1,
  "obligatorio": true,
  "mensaje": "⚠️ Debes calificar 1 servicio(s) completado(s) antes de continuar"
}
```

### **Profesional**

#### 1. Calificar Paciente (OPCIONAL)
```http
POST /api/profesional/solicitudes/{solicitud_id}/calificar-paciente
Authorization: Bearer {token}
Content-Type: application/json

{
  "calificacion": 5,
  "comentario": "Paciente puntual y cooperativo"
}
```

**Validaciones:**
- La solicitud debe pertenecer al profesional autenticado
- Estado debe ser `completado`
- `calificacion_profesional` debe ser NULL (no calificado previamente)
- Calificación debe estar entre 1 y 5

**Respuesta exitosa:**
```json
{
  "message": "✅ Gracias por tu evaluación del paciente",
  "solicitud_id": 34,
  "puntuacion_paciente": 4.90
}
```

#### 2. Obtener Servicios Pendientes de Calificar al Paciente
```http
GET /api/profesional/servicios-pendientes-calificar
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "pendientes": [
    {
      "id": 34,
      "fecha_completada": "2024-01-15 14:30:00",
      "servicio_nombre": "Consulta Médica General",
      "paciente_nombre": "María",
      "paciente_apellido": "González",
      "puntuacion_promedio_paciente": 5.00,
      "total_calificaciones_paciente": 0
    }
  ],
  "total": 1
}
```

### **Admin / SuperAdmin**

#### Ver Reporte Completo (con calificaciones bidireccionales)
```http
GET /api/admin/reportes/{solicitud_id}
GET /api/superadmin/reportes/{solicitud_id}
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "reporte": {
    "solicitud_id": 34,
    "paciente": {
      "nombre": "María González",
      "puntuacion_promedio": 4.90,
      "total_calificaciones": 10
    },
    "profesional": {
      "nombre": "Dr. Juan Pérez",
      "puntuacion_promedio": 4.85,
      "total_calificaciones": 127
    },
    "calificacion_paciente_a_profesional": {
      "calificado": true,
      "puntuacion": 5,
      "comentario": "Excelente servicio",
      "fecha": "2024-01-15 15:00:00"
    },
    "calificacion_profesional_a_paciente": {
      "calificado": true,
      "puntuacion": 5,
      "comentario": "Paciente muy cooperativo",
      "fecha": "2024-01-15 15:30:00"
    }
  }
}
```

## 🔄 Flujo de Trabajo

### Proceso de Calificación Bidireccional

```
1. Profesional completa el servicio
   ├─> Estado: en_proceso → completado
   ├─> Guarda: reporte_profesional, diagnostico, notas
   └─> fecha_completada = CURRENT_TIMESTAMP

2. Profesional PUEDE calificar al paciente (Opcional)
   ├─> Endpoint: POST /api/profesional/solicitudes/{id}/calificar-paciente
   ├─> Guarda: calificacion_profesional, comentario_profesional
   └─> Actualiza: puntuacion_promedio_paciente del usuario

3. Paciente DEBE calificar al profesional (Obligatorio ⚠️)
   ├─> Endpoint: POST /api/paciente/calificar/{id}
   ├─> Guarda: calificacion_paciente, comentario_paciente
   ├─> Marca: calificado = TRUE
   ├─> Actualiza: puntuacion_promedio del profesional
   └─> Bloqueo: Sin calificar, no puede solicitar nuevos servicios

4. Admin/SuperAdmin revisan ambas calificaciones
   ├─> Dashboard: Visualiza reportes con calificaciones bidireccionales
   └─> Control de calidad en ambas direcciones
```

## ⚡ Lógica de Obligatoriedad

### En PacienteController

```php
public function __construct()
{
    parent::__construct();
    // ...
    
    // Verificar calificaciones obligatorias pendientes
    $this->verificarCalificacionesPendientes();
}

private function verificarCalificacionesPendientes(): void
{
    $stmt = $this->db->prepare("
        SELECT COUNT(*) as pendientes
        FROM solicitudes
        WHERE paciente_id = :paciente_id
            AND estado = 'completado'
            AND calificado = FALSE
            AND fecha_completada IS NOT NULL
    ");
    
    $stmt->execute(['paciente_id' => $this->user->id]);
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    $this->calificacionesPendientes = (int)$result['pendientes'];
}
```

### Recomendación de Implementación Frontend

```javascript
// Al cargar el dashboard del paciente
async function checkPendingRatings() {
    const response = await fetch('/api/paciente/servicios-pendientes-calificar', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    
    const data = await response.json();
    
    if (data.obligatorio && data.total > 0) {
        // Mostrar modal bloqueante
        showMandatoryRatingModal(data.pendientes);
        
        // Bloquear acceso a "Solicitar Servicio" hasta calificar
        disableNewRequestButton();
    }
}
```

## 📈 Cálculo de Promedios

### Cuando Paciente Califica Profesional

```php
// Recalcular promedio del profesional
$stmt = $this->db->prepare("
    SELECT 
        AVG(calificacion_paciente) as promedio,
        COUNT(*) as total
    FROM solicitudes 
    WHERE profesional_id = :profesional_id 
        AND calificacion_paciente IS NOT NULL
");

$stmt->execute(['profesional_id' => $profesional_id]);
$stats = $stmt->fetch(\PDO::FETCH_ASSOC);

// Actualizar usuario
UPDATE usuarios 
SET puntuacion_promedio = :promedio,
    total_calificaciones = :total
WHERE id = :profesional_id
```

### Cuando Profesional Califica Paciente

```php
// Recalcular promedio del paciente
$stmt = $this->db->prepare("
    SELECT 
        AVG(calificacion_profesional) as promedio,
        COUNT(*) as total
    FROM solicitudes 
    WHERE paciente_id = :paciente_id 
        AND calificacion_profesional IS NOT NULL
");

$stmt->execute(['paciente_id' => $paciente_id]);
$stats = $stmt->fetch(\PDO::FETCH_ASSOC);

// Actualizar usuario
UPDATE usuarios 
SET puntuacion_promedio_paciente = :promedio,
    total_calificaciones_paciente = :total
WHERE id = :paciente_id
```

## 🎨 Recomendaciones de UI

### Dashboard Paciente
```
┌─────────────────────────────────────────┐
│ ⚠️ ATENCIÓN                             │
│                                         │
│ Tienes 1 servicio completado sin       │
│ calificar. Debes calificar antes de    │
│ solicitar nuevos servicios.            │
│                                         │
│ [Calificar Ahora]                      │
└─────────────────────────────────────────┘
```

### Modal de Calificación Obligatoria
```
┌─────────────────────────────────────────┐
│ Califica tu Servicio Completado        │
│─────────────────────────────────────────│
│                                         │
│ Servicio: Consulta Médica General      │
│ Profesional: Dr. Juan Pérez            │
│ Fecha: 15/01/2024 14:30               │
│                                         │
│ ¿Cómo calificarías este servicio?     │
│ ⭐⭐⭐⭐⭐                               │
│                                         │
│ Comentario (opcional):                 │
│ ┌─────────────────────────────────┐   │
│ │                                 │   │
│ └─────────────────────────────────┘   │
│                                         │
│         [Cancelar] [Calificar]         │
└─────────────────────────────────────────┘
```

### Dashboard Profesional (Opcional)
```
Servicios Completados Recientes

┌─────────────────────────────────────────┐
│ Consulta - María González               │
│ 15/01/2024 14:30                       │
│                                         │
│ Paciente calificó: ⭐⭐⭐⭐⭐          │
│                                         │
│ [Calificar Paciente] ← Opcional        │
└─────────────────────────────────────────┘
```

## 🔒 Validaciones de Seguridad

### Prevenir Doble Calificación
```sql
-- Paciente → Profesional
WHERE calificado = FALSE

-- Profesional → Paciente
WHERE calificacion_profesional IS NULL
```

### Verificar Propiedad
```sql
-- Solo el paciente de la solicitud puede calificar
WHERE paciente_id = :user_id

-- Solo el profesional asignado puede calificar
WHERE profesional_id = :user_id
```

### Verificar Estado
```sql
-- Solo servicios completados válidos
WHERE estado = 'completado' 
    AND fecha_completada IS NOT NULL
```

## 📊 Métricas Disponibles

### Por Usuario

**Como Profesional:**
- `puntuacion_promedio`: Promedio recibido de pacientes
- `total_calificaciones`: Total de calificaciones recibidas
- `servicios_completados`: Servicios finalizados

**Como Paciente:**
- `puntuacion_promedio_paciente`: Promedio recibido de profesionales
- `total_calificaciones_paciente`: Total de calificaciones recibidas

### Por Solicitud
- Calificación bidireccional completa
- Comentarios de ambas partes
- Fechas de cada calificación

## ✅ Estado de Implementación

- ✅ Esquema de base de datos
- ✅ ProfesionalController: `calificarPaciente()`, `getServiciosPendientesCalificarPaciente()`
- ✅ PacienteController: `calificarServicio()` (existente), `getServiciosPendientesCalificar()`, verificación obligatoria
- ✅ AdminController: `verReporte()` actualizado con calificaciones bidireccionales
- ✅ SuperAdminController: `verReporte()` actualizado con calificaciones bidireccionales
- ✅ Rutas API configuradas
- ⏳ Interfaz de usuario (pendiente)
- ⏳ Modal de calificación obligatoria (pendiente)
- ⏳ Bloqueo de nuevas solicitudes sin calificar (pendiente en frontend)

## 🚀 Próximos Pasos

1. **Frontend - Dashboard Paciente**
   - Implementar verificación al cargar dashboard
   - Modal bloqueante para calificaciones pendientes
   - Bloquear botón "Solicitar Servicio" si hay pendientes

2. **Frontend - Dashboard Profesional**
   - Sección "Servicios Pendientes de Evaluar al Paciente"
   - Modal para calificar paciente (opcional pero recomendado)

3. **Frontend - Admin Dashboard**
   - Actualizar modal de reportes para mostrar ambas calificaciones
   - Sección separada para cada dirección de calificación
   - Iconos visuales: 👤→⭐→👨‍⚕️ y 👨‍⚕️→⭐→👤

4. **Notificaciones**
   - Email/Push cuando se recibe una calificación
   - Recordatorio al paciente para calificar (24h después)
   - Recordatorio al profesional (opcional, 48h después)

## 📝 Notas Importantes

- La calificación del paciente al profesional es **OBLIGATORIA**
- La calificación del profesional al paciente es **OPCIONAL** (pero recomendada)
- Los promedios se recalculan automáticamente en cada calificación
- Las transacciones garantizan consistencia de datos
- Los admins pueden ver ambas perspectivas en los reportes
