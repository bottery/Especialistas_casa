# Estados de Solicitud - Flujo Actualizado

## Estados Disponibles

### 1. `pendiente_pago`
- **Descripción**: Solicitud creada, esperando confirmación de pago
- **Cuándo**: Cuando el paciente crea una solicitud con método de pago por transferencia
- **Siguiente estado**: `pagado` (cuando el admin aprueba el pago)
- **Acciones admin**: Aprobar o rechazar pago

### 2. `pagado`
- **Descripción**: Pago confirmado, lista para asignar profesional
- **Cuándo**: Después de que el admin aprueba el pago
- **Siguiente estado**: `asignado` (cuando el admin asigna un profesional)
- **Acciones admin**: Asignar profesional disponible

### 3. `asignado`
- **Descripción**: Profesional asignado, esperando inicio del servicio
- **Cuándo**: Cuando el admin asigna un profesional a la solicitud
- **Siguiente estado**: `en_proceso` (cuando el profesional inicia el servicio)
- **Acciones profesional**: Iniciar servicio, contactar paciente

### 4. `en_proceso`
- **Descripción**: Servicio en ejecución activa
- **Cuándo**: Cuando el profesional marca que comenzó el servicio
- **Siguiente estado**: `completado` (cuando el profesional finaliza)
- **Acciones profesional**: Finalizar servicio

### 5. `completado`
- **Descripción**: Servicio finalizado exitosamente
- **Cuándo**: Cuando el profesional marca el servicio como terminado
- **Siguiente estado**: Ninguno (estado final)
- **Acciones paciente**: Calificar servicio

### 6. `cancelado`
- **Descripción**: Solicitud cancelada (por admin, paciente o sistema)
- **Cuándo**: Cancelación por cualquier motivo
- **Siguiente estado**: Ninguno (estado final)
- **Acciones**: Ninguna

## Flujo Completo

```
Paciente crea solicitud
         ↓
   pendiente_pago
         ↓
   Admin aprueba pago
         ↓
      pagado
         ↓
   Admin asigna profesional
         ↓
     asignado
         ↓
   Profesional inicia servicio
         ↓
    en_proceso
         ↓
   Profesional finaliza servicio
         ↓
    completado
```

## Contadores en Dashboard Admin

- **Pendientes Asignación**: Solicitudes en estado `pagado` sin profesional
- **Servicios en Proceso**: Solicitudes en estado `asignado` o `en_proceso`
- **Completadas Hoy**: Solicitudes en estado `completado` actualizadas hoy
- **Ingresos del Mes**: Suma de `monto_total` donde `pagado = 1` del mes actual

## Cambios Realizados

1. ✅ Migrado estados antiguos a nuevos
2. ✅ Actualizado ENUM en tabla solicitudes
3. ✅ Actualizado AdminController (queries de stats, asignación, listados)
4. ✅ Actualizado PagosTransferenciaController (aprobación de pagos)
5. ⏳ Pendiente: Actualizar vistas (badges de estado en frontend)
