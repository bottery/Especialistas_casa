# 🔍 Guía Rápida: Sistema de Diagnóstico

## Inicio Rápido (2 minutos)

### Activar el Panel
```
1. Abre el dashboard
2. Presiona: Ctrl + Shift + D
3. ¡Listo! El panel aparece en la parte superior
```

### Ver Logs en Tiempo Real
- Cada acción registra automáticamente un log
- Los últimos 10 logs se muestran en el panel
- Color rojo = ERROR, Amarillo = WARN, Azul = INFO

### Qué Hacer si Hay Error

**Opción 1: Enviar al Servidor** (Automático)
```
Panel → "Enviar al Servidor"
↓
Logs guardados en: storage/logs/client-errors-YYYY-MM-DD.log
↓
Archivo con timestamp de cada evento
```

**Opción 2: Descargar para Análisis** (Manual)
```
Panel → "Descargar JSON"
↓
Se descarga: dashboard-logs-YYYY-MM-DD.json
↓
Abre en editor de texto para análisis
```

**Opción 3: Inspeccionar en Consola** (Técnico)
```
Abre Consola (F12)
Escribe: window.errorLog.getLogs()
Enter
↓
Ve lista detallada de todos los logs
```

## Comandos de Consola

```javascript
// Ver todos los logs
window.errorLog.getLogs()

// Registrar un log manual
window.errorLog.info('Mensaje', { dato1: 'valor' })

// Registrar un error manual
window.errorLog.error('Error manual', { code: 500 })

// Enviar logs al servidor manualmente
window.errorLog.sendToServer()

// Limpiar todos los logs
window.errorLog.logs = []

// Ver estadísticas
{
  total: window.errorLog.logs.length,
  errores: window.errorLog.logs.filter(l => l.level === 'ERROR').length,
  advertencias: window.errorLog.logs.filter(l => l.level === 'WARN').length
}
```

## Problemas Comunes

### "Los gráficos no se muestran"
```
Ctrl+Shift+D → Busca logs con "Canvas"
├─ "Canvas no encontrado" → Falta elemento HTML
├─ "tamaño 0x0" → Canvas oculto
└─ "No se pudo obtener contexto" → Problema de Chart.js
```

### "Error HTTP 401/403"
```
Ctrl+Shift+D → Busca "HTTP 401" o "HTTP 403"
├─ Token inválido → Vuelve a iniciar sesión
├─ Permisos insuficientes → Necesita ser superadmin
└─ Token expirado → Recarga la página
```

### "Datos no cargan"
```
Ctrl+Shift+D → Busca "Error al cargar"
├─ Ver el mensaje exacto del error
├─ Nota el status code HTTP
└─ Revisa detalles para contexto
```

## Interpretación de Niveles

| Nivel | Significado | Acción |
|-------|------------|--------|
| 🔴 ERROR | Algo salió mal | Investigar inmediatamente |
| 🟡 WARN | Comportamiento raro | Monitorear |
| 🔵 INFO | Evento normal importante | Registrado para auditoría |
| ⚪ DEBUG | Detalles técnicos | Solo para análisis profundo |

## Casos de Uso Típicos

### Caso 1: Usuario reporta "Dashboard no carga"
```
1. Abre panel (Ctrl+Shift+D)
2. ¿Hay logs ERROR?
   SÍ → Ver mensaje exacto → Reportar a desarrollo
   NO → Revisar si WARN o DEBUG dan pista
3. Descargar JSON
4. Enviar junto con reporte
```

### Caso 2: Algunos gráficos faltan
```
1. Abre panel (Ctrl+Shift+D)
2. Busca nombre del gráfico faltante
3. ¿Hay WARN sobre Canvas?
   SÍ → Problema de HTML/CSS
   NO → Problema de datos
4. Ver detalles en panel
```

### Caso 3: "Sesión expirada"
```
1. Abre panel (Ctrl+Shift+D)
2. Busca "401" o "Unauthorized"
3. ¡Lógico! Vuelve a iniciar sesión
4. Panel se reinicia automáticamente
```

## Archivo de Logs del Servidor

**Ubicación**: `storage/logs/client-errors-2024-12-11.log`

**Formato**:
```
[2024-12-11 15:30:45] ERROR: Error al cargar dashboard | Details: {"message":"HTTP 500","retry":1}
[2024-12-11 15:30:46] WARN: Reintentando carga... | Details: {}
[2024-12-11 15:30:47] INFO: Dashboard cargado | Details: {"usuarios":9}
```

**Leerlo**:
```bash
tail -f storage/logs/client-errors-2024-12-11.log    # Últimas líneas
grep ERROR storage/logs/client-errors-2024-12-11.log # Solo errores
wc -l storage/logs/client-errors-2024-12-11.log      # Contar logs
```

## Atajos

| Atajo | Acción |
|-------|--------|
| `Ctrl + Shift + D` | Abre/Cierra panel de diagnóstico |
| `F12` | Abre consola del navegador |
| `Ctrl + Shift + K` | Solo consola (atajo del navegador) |

## Exportar Logs para Soporte

### Método 1: JSON (Recomendado)
```
1. Panel → "Descargar JSON"
2. Envía el archivo al equipo técnico
3. Ellos lo analizan con herramientas
```

### Método 2: Screenshot
```
1. Abre panel (Ctrl+Shift+D)
2. Toma screenshot
3. Envía junto con descripción
```

### Método 3: Copiar Texto
```
1. Panel → Selecciona los logs que ves
2. Ctrl+C para copiar
3. Pega en email o reporte
```

## Preguntas Frecuentes

**P: ¿Se pierden los logs al recargar?**
A: Sí, están en memoria. Descargar JSON antes de recargar si es importante.

**P: ¿Puedo ver logs de sesiones anteriores?**
A: Los nuevos están en `storage/logs/client-errors-YYYY-MM-DD.log`

**P: ¿Es seguro compartir los logs?**
A: Contienen URLs y detalles de sesión. Revisa antes de compartir públicamente.

**P: ¿Puedo crear mis propios logs?**
A: Sí: `window.errorLog.info('Mi log', { dato: 'valor' })`

**P: ¿Qué pasa si hay 50+ logs?**
A: Se mantienen solo los últimos 50 para no sobrecargar memoria.

## Soporte

¿Problema con el sistema de diagnóstico?

1. Verifica que estés autenticado
2. Presiona F12 → Consola
3. Escribe: `typeof window.errorLog`
4. Deberías ver: `"object"`
5. Si no, hay problema con la carga de JavaScript

## Más Información

- 📖 Documentación completa: `DIAGNOSTICO.md`
- 📋 Resumen de cambios: `RESUMEN_DIAGNOSTICO.md`
- 🔧 Changelog: Este archivo

---

**Última actualización:** 2024-12-11
**Status:** ✅ Funcional
**Versión:** 1.0
