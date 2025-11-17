# Arquitectura de Roles y Tipos de Profesionales

## Implementación

### Campos en la tabla `usuarios`:

1. **`rol`**: Define permisos y acceso al sistema
   - `paciente` - Usuario final que solicita servicios
   - `profesional` - Especialista que presta servicios (unificado)
   - `admin` - Administrador del sistema
   - `superadmin` - Super administrador

2. **`tipo_profesional`**: Define la especialidad del profesional
   - `medico` - Médico general o especialista
   - `enfermera` - Profesional de enfermería
   - `veterinario` - Médico veterinario
   - `laboratorio` - Técnico de laboratorio
   - `ambulancia` - Operador de ambulancia

### Ventajas de esta arquitectura:

✅ **Un solo dashboard para todos los especialistas** (`/profesional/dashboard`)
✅ **Permisos simplificados**: Solo verificas rol `profesional`
✅ **Filtrado por especialidad**: Puedes filtrar servicios por `tipo_profesional`
✅ **Escalabilidad**: Fácil agregar nuevos tipos sin afectar permisos
✅ **Reportes precisos**: Puedes generar estadísticas por tipo

### Ejemplos de uso:

#### Verificar permisos (Middleware):
```php
$user = $authMiddleware->checkRole(['profesional']); // Permite TODOS los especialistas
```

#### Filtrar por especialidad:
```sql
SELECT * FROM usuarios 
WHERE rol = 'profesional' 
AND tipo_profesional = 'medico';
```

#### Asignar servicio según tipo:
```php
if ($usuario->tipo_profesional === 'veterinario') {
    // Mostrar solo solicitudes veterinarias
}
```

### Flujo de registro:

1. Usuario selecciona tipo en el formulario: "Médico", "Veterinario", etc.
2. Backend recibe: `rol: "medico"`
3. Backend procesa:
   - `tipo_profesional = "medico"`
   - `rol = "profesional"`
4. Usuario creado con ambos campos

### Dashboards por rol:

| Rol          | Dashboard                 | Acceso |
|--------------|---------------------------|--------|
| paciente     | `/paciente/dashboard`     | ✅ |
| profesional  | `/profesional/dashboard`  | ✅ Todos los especialistas |
| admin        | `/admin/dashboard`        | ✅ |
| superadmin   | `/superadmin/dashboard`   | ✅ |

### Migración aplicada:

```sql
ALTER TABLE usuarios ADD COLUMN tipo_profesional ENUM(...);
UPDATE usuarios SET tipo_profesional = rol WHERE rol IN (...);
UPDATE usuarios SET rol = 'profesional' WHERE tipo_profesional IS NOT NULL;
ALTER TABLE usuarios MODIFY COLUMN rol ENUM('paciente', 'profesional', 'admin', 'superadmin');
```

## Uso en el código

### AuthController (Registro):
```php
$esProfesional = in_array($data['rol'], ['medico', 'enfermera', ...]);
if ($esProfesional) {
    $data['tipo_profesional'] = $data['rol'];
    $data['rol'] = 'profesional';
}
```

### ProfesionalController:
```php
$this->user = $this->authMiddleware->checkRole(['profesional']);
// $this->user->tipo_profesional contiene: 'medico', 'veterinario', etc.
```

### Frontend (Login):
```javascript
const rol = data.user.rol; // 'profesional'
window.location.href = `/${rol}/dashboard`; // /profesional/dashboard
```

---

**Fecha de implementación**: 16 Noviembre 2025  
**Estado**: ✅ Activo y funcional
