-- Migración: Unificación de Dashboard de Especialistas
-- Fecha: 16 Nov 2025
-- 
-- NOTA IMPORTANTE:
-- Los roles en BD se mantienen específicos (medico, enfermera, veterinario, laboratorio, ambulancia)
-- pero todos redirigen al mismo dashboard: /profesional/dashboard
-- 
-- Esto permite:
-- 1. Identificar el tipo de especialista en reportes y estadísticas
-- 2. Filtrar servicios según el tipo de profesional
-- 3. Mantener un solo dashboard unificado para todos los especialistas

-- No se requieren cambios en la estructura de la base de datos
-- Los cambios son solo en la lógica de redirección del frontend

-- MAPEO DE ROLES:
-- medico          -> /profesional/dashboard
-- enfermera       -> /profesional/dashboard
-- veterinario     -> /profesional/dashboard
-- laboratorio     -> /profesional/dashboard
-- ambulancia      -> /profesional/dashboard
-- paciente        -> /paciente/dashboard
-- admin           -> /admin/dashboard
-- superadmin      -> /superadmin/dashboard

SELECT 'Migración completada: Sistema de roles unificado' as mensaje;
