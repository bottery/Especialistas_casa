-- Migración: Corrección de problemas de encoding
-- Fecha: 2025-01-01
-- Descripción: Corrige caracteres mal codificados en especialidades y nombres

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Corregir especialidades con encoding incorrecto en solicitudes
UPDATE solicitudes 
SET especialidad = REPLACE(especialidad, '?a', 'ía')
WHERE especialidad LIKE '%?a%';

UPDATE solicitudes 
SET especialidad = REPLACE(especialidad, '?o', 'ío')
WHERE especialidad LIKE '%?o%';

UPDATE solicitudes 
SET especialidad = REPLACE(especialidad, '?n', 'ñ')
WHERE especialidad LIKE '%?n%';

UPDATE solicitudes 
SET especialidad = REPLACE(especialidad, '?', 'í')
WHERE especialidad LIKE '%Pediatr?%';

-- Corregir especialidades con encoding incorrecto en perfiles_profesionales
UPDATE perfiles_profesionales 
SET especialidad = REPLACE(especialidad, '?a', 'ía')
WHERE especialidad LIKE '%?a%';

UPDATE perfiles_profesionales 
SET especialidad = REPLACE(especialidad, '?o', 'ío')
WHERE especialidad LIKE '%?o%';

UPDATE perfiles_profesionales 
SET especialidad = REPLACE(especialidad, '?', 'í')
WHERE especialidad LIKE '%Pediatr?%';

-- Decodificar entidades HTML en nombres de usuarios
UPDATE usuarios 
SET nombre = REPLACE(nombre, '&#039;', "'")
WHERE nombre LIKE '%&#039;%';

UPDATE usuarios 
SET apellido = REPLACE(apellido, '&#039;', "'")
WHERE apellido LIKE '%&#039;%';

UPDATE usuarios 
SET nombre = REPLACE(nombre, '&quot;', '"')
WHERE nombre LIKE '%&quot;%';

UPDATE usuarios 
SET apellido = REPLACE(apellido, '&quot;', '"')
WHERE apellido LIKE '%&quot;%';

UPDATE usuarios 
SET nombre = REPLACE(nombre, '&amp;', '&')
WHERE nombre LIKE '%&amp;%';

UPDATE usuarios 
SET apellido = REPLACE(apellido, '&amp;', '&')
WHERE apellido LIKE '%&amp;%';

-- Verificación
SELECT 'Usuarios con entidades HTML' as check_type, COUNT(*) as count 
FROM usuarios WHERE nombre LIKE '%&#%' OR apellido LIKE '%&#%';

SELECT 'Solicitudes con encoding incorrecto' as check_type, COUNT(*) as count 
FROM solicitudes WHERE especialidad LIKE '%?%';

SELECT 'Perfiles con encoding incorrecto' as check_type, COUNT(*) as count 
FROM perfiles_profesionales WHERE especialidad LIKE '%?%';
