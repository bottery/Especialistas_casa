-- Migración: Separar ROL de TIPO DE PROFESIONAL
-- Fecha: 16 Nov 2025
--
-- CAMBIO ARQUITECTÓNICO:
-- - rol: Define permisos y acceso (paciente, profesional, admin, superadmin)
-- - tipo_profesional: Define qué tipo de servicio presta (medico, enfermera, veterinario, etc.)

-- 1. Agregar columna tipo_profesional
ALTER TABLE usuarios 
ADD COLUMN tipo_profesional ENUM('medico', 'enfermera', 'veterinario', 'laboratorio', 'ambulancia') NULL 
AFTER rol;

-- 2. Migrar datos existentes: copiar el rol actual a tipo_profesional
UPDATE usuarios 
SET tipo_profesional = rol 
WHERE rol IN ('medico', 'enfermera', 'veterinario', 'laboratorio', 'ambulancia');

-- 3. Cambiar el rol de todos los especialistas a 'profesional'
UPDATE usuarios 
SET rol = 'profesional' 
WHERE rol IN ('medico', 'enfermera', 'veterinario', 'laboratorio', 'ambulancia');

-- 4. Modificar ENUM del campo rol (eliminar tipos específicos)
ALTER TABLE usuarios 
MODIFY COLUMN rol ENUM('paciente', 'profesional', 'admin', 'superadmin') NOT NULL;

-- 5. Verificar migración
SELECT 
    COUNT(*) as total_profesionales,
    tipo_profesional,
    rol
FROM usuarios 
WHERE rol = 'profesional'
GROUP BY tipo_profesional, rol;

-- 6. Ejemplos de queries después de la migración:
-- Ver todos los profesionales:
-- SELECT * FROM usuarios WHERE rol = 'profesional';

-- Ver solo médicos:
-- SELECT * FROM usuarios WHERE rol = 'profesional' AND tipo_profesional = 'medico';

-- Ver todos los tipos de profesionales activos:
-- SELECT tipo_profesional, COUNT(*) FROM usuarios WHERE rol = 'profesional' AND estado = 'activo' GROUP BY tipo_profesional;

SELECT 'Migración completada: Rol unificado como PROFESIONAL' as resultado;
