# Sistema de Diagnóstico y Captura de Errores

## Descripción General

Se ha implementado un sistema completo de captura de errores en el dashboard que permite analizar y diagnosticar problemas de forma eficiente.

## Características Implementadas

### 1. **Logger de Errores Global** (`window.errorLog`)

El objeto `window.errorLog` captura automáticamente:
- Errores no capturados en el navegador
- Promises rechazadas
- Llamadas a API fallidas
- Errores en renderizado de gráficos
- Eventos de inicialización

#### Métodos disponibles:
```javascript
window.errorLog.error(message, details)      // Registra errores críticos
window.errorLog.warn(message, details)       // Registra advertencias
window.errorLog.info(message, details)       // Registra información
window.errorLog.debug(message, details)      // Registra información de depuración
window.errorLog.getLogs()                    // Obtiene todos los logs
window.errorLog.sendToServer()               // Envía logs al servidor
```

### 2. **Panel de Diagnóstico Interactivo**

#### Cómo activarlo:
- **Atajo de teclado**: `Ctrl + Shift + D`
- Se abrirá un panel en la parte superior del dashboard

#### Información mostrada:
- Total de logs capturados
- Cantidad de errores y advertencias
- Últimos 10 logs con timestamp
- Nivel de severidad (ERROR, WARN, INFO, DEBUG) color-codificado

#### Acciones disponibles:
- **Limpiar Logs**: Elimina todos los logs de la sesión actual
- **Enviar al Servidor**: Envía logs al endpoint `/api/admin/error-logs`
- **Descargar JSON**: Descarga los logs en formato JSON para análisis externo

### 3. **Captura Automática de Eventos**

#### Eventos capturados automáticamente:

**Inicialización:**
```
[INFO] Dashboard iniciado
[INFO] Usuario cargado (nombre, rol)
[INFO] Datos del dashboard cargados
[INFO] Datos de gráficos cargados
[INFO] Configuración de pagos cargada
```

**Errores de API:**
```
[ERROR] Error al cargar datos del dashboard
  - message: HTTP 500: Internal Server Error
  - stack: (stack trace)
  - retryCount: número de reintentos
```

**Problemas de renderizado:**
```
[WARN] Canvas ingresosChart no encontrado en DOM
[WARN] Canvas serviciosChart tiene tamaño 0x0
[ERROR] No se pudo obtener contexto 2D del canvas usuariosChart
```

**Errores globales:**
```
[ERROR] Error global no capturado
  - message: Cannot read properties of null
  - filename: dashboard.php
  - lineno: 520
  - colno: 15
  - stack: (stack trace)
```

### 4. **Endpoint de Servidor**

**Ruta**: `POST /api/admin/error-logs`

**Descripción**: Recibe y almacena logs de errores del cliente

**Solicitud:**
```json
{
  "logs": [
    {
      "timestamp": "2024-12-11T15:30:45.123Z",
      "level": "ERROR",
      "message": "Error loading charts",
      "details": {
        "message": "Canvas not found",
        "canvasId": "ingresosChart"
      }
    }
  ]
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Logs registrados"
}
```

**Almacenamiento**: Los logs se guardan en `storage/logs/client-errors-YYYY-MM-DD.log`

### 5. **Funciones de Depuración Mejoradas**

Todas las funciones críticas incluyen logging detallado:

#### `loadDashboardData()`:
```javascript
[DEBUG] Obteniendo datos del dashboard...
[DEBUG] Respuesta recibida
[INFO] Dashboard data cargado exitosamente
  - totalUsuarios: 9
  - serviciosActivos: 8
  - solicitudesPendientes: 6
```

#### `loadChartData()`:
```javascript
[DEBUG] Obteniendo datos de gráficos...
[DEBUG] Datos de gráficos recibidos
[DEBUG] Iniciando renderizado de gráficos...
[INFO] Gráficos renderizados exitosamente
```

#### `renderCharts(data)`:
```javascript
[DEBUG] Limpiando gráficos anteriores...
[DEBUG] Creando gráfico ingresosChart
  - canvasSize: 800x250
  - contextType: CanvasRenderingContext2D
[ERROR] Error creando gráfico serviciosChart
  - message: Canvas not found
  - stack: ...
```

#### `cargarConfigPagos()`:
```javascript
[DEBUG] Cargando configuración de pagos...
[INFO] Configuración de pagos cargada
  - banco: Banco Ejemplo
  - tieneQR: true
```

## Cómo Usar para Diagnosticar Problemas

### Paso 1: Reproducir el problema
1. Abre el dashboard
2. Realiza la acción que causa el error
3. Observa si hay mensajes de error visibles

### Paso 2: Acceder al panel de diagnóstico
1. Presiona `Ctrl + Shift + D`
2. Se abrirá el panel con todos los logs capturados

### Paso 3: Analizar los logs
- Busca logs de nivel `ERROR` (en rojo)
- Revisa los detalles de cada error
- Anota el timestamp exacto del error

### Paso 4: Obtener más información

**Opción A - Enviar al servidor:**
1. Haz clic en "Enviar al Servidor"
2. Los logs se guardarán en el servidor
3. Consulta `storage/logs/client-errors-YYYY-MM-DD.log`

**Opción B - Descargar JSON:**
1. Haz clic en "Descargar JSON"
2. Se descargará un archivo JSON con todos los logs
3. Usa herramientas JSON para análisis detallado

**Opción C - Inspeccionar en consola:**
1. Abre la consola del navegador (F12)
2. Escribe: `window.errorLog.getLogs()`
3. Veras la lista de objetos con toda la información

## Ejemplos de Diagnóstico

### Problema: Los gráficos no se renderizan

**Logs esperados:**
```
[INFO] Cargando datos de gráficos...
[INFO] Datos de gráficos recibidos
[ERROR] Error renderizando gráficos
  - message: Cannot read properties of null
  - stack: ...
[WARN] Canvas ingresosChart no encontrado en DOM
```

**Solución sugerida:**
- Verificar que los elementos canvas existan en el HTML
- Verificar que el CSS no oculta los canvas (display: none)
- Asegurar que Chart.js esté cargado correctamente

### Problema: Error HTTP al cargar datos

**Logs esperados:**
```
[ERROR] Error al cargar datos del dashboard
  - message: HTTP 401: Unauthorized
  - retryCount: 3
  - maxRetries: 3
[ERROR] Máximo de reintentos alcanzado para dashboard
```

**Solución sugerida:**
- Verificar que el token JWT es válido
- Verificar que no ha expirado
- Revisar permisos del usuario (debe ser superadmin)

### Problema: Configuración de pagos no carga

**Logs esperados:**
```
[DEBUG] Cargando configuración de pagos...
[WARN] Configuración de pagos no disponible
  - status: 404
```

**Solución sugerida:**
- Verificar que existe el endpoint `/api/admin/configuracion-pagos`
- Confirmar que el usuario tiene permisos para acceder
- Verificar que la base de datos tiene datos de configuración

## Información Capturada en Cada Log

Cada entrada de log contiene:

```json
{
  "timestamp": "2024-12-11T15:30:45.123Z",
  "level": "ERROR|WARN|INFO|DEBUG",
  "message": "Descripción legible del evento",
  "details": {
    "campo1": "valor1",
    "campo2": "valor2",
    "stack": "stack trace si aplica"
  },
  "userAgent": "Mozilla/5.0...",
  "url": "https://localhost/VitaHome/superadmin/dashboard"
}
```

## Archivo de Logs del Servidor

**Ubicación**: `storage/logs/client-errors-YYYY-MM-DD.log`

**Formato:**
```
[2024-12-11 15:30:45] ERROR: Error al cargar gráficos | Details: {"message":"Canvas not found","canvasId":"ingresosChart"}
[2024-12-11 15:30:46] WARN: Reintentando... intento 1/3 | Details: {}
[2024-12-11 15:30:47] INFO: Dashboard data cargado exitosamente | Details: {"totalUsuarios":9}
```

## Mejores Prácticas

1. **Revisar logs regularmente** para identificar patrones de error
2. **Limpiar logs** cuando ya no sean necesarios para mantener el panel legible
3. **Descargar logs** antes de limpiar si necesitas guardar historial
4. **Compartir JSON exportado** con el equipo técnico para análisis
5. **Monitorear advertencias** - pueden indicar problemas futuros

## Atajos de Teclado

| Combinación | Acción |
|---|---|
| `Ctrl + Shift + D` | Abrir/Cerrar panel de diagnóstico |

## Notas Técnicas

- Los logs se almacenan en memoria del navegador (max 50 logs)
- Los logs se pierden al recargar la página
- El envío al servidor requiere autenticación válida
- El panel de diagnóstico es solo visible para usuarios autenticados
- Los errores globales se capturan incluso antes de que se cargue Alpine.js

## Solución de Problemas del Sistema de Diagnóstico

### El panel no se abre
- Asegúrate de estar autenticado
- Presiona exactamente `Ctrl + Shift + D` (no confundir con otras teclas)
- Verifica en la consola: `console.log(window.errorLog)`

### Los logs no se guardan en el servidor
- Verifica que tienes permisos de admin
- Revisa que existe el endpoint `/api/admin/error-logs`
- Verifica logs del servidor en `storage/logs/`

### El JSON descargado está vacío
- Asegúrate de que la sesión esté activa
- Reproduce el error para generar logs
- Comprueba que exista `window.errorLog`

## Contacto y Soporte

Para reportar problemas con el sistema de diagnóstico:
1. Descarga los logs en JSON
2. Incluye el navegador y versión
3. Describe los pasos para reproducir el problema
4. Adjunta el archivo JSON

---

**Última actualización**: 2024-12-11
**Versión**: 1.0
