-- ============================================
-- Sistema de Calificaciones y Ajustes de Flujo
-- Fecha: 2025-11-14
-- ============================================

USE especialistas_casa;

-- Agregar campos de calificación a solicitudes
ALTER TABLE solicitudes
ADD COLUMN calificacion_paciente INT NULL COMMENT 'Calificación del 1 al 5',
ADD COLUMN comentario_paciente TEXT NULL COMMENT 'Comentario del paciente',
ADD COLUMN fecha_calificacion TIMESTAMP NULL COMMENT 'Fecha de calificación',
ADD COLUMN calificado BOOLEAN DEFAULT FALSE COMMENT 'Si ya fue calificado';

-- Agregar índice para consultas de calificación
ALTER TABLE solicitudes
ADD INDEX idx_calificado (calificado);

-- Agregar campo de puntuación promedio a usuarios profesionales
ALTER TABLE usuarios
ADD COLUMN puntuacion_promedio DECIMAL(3,2) DEFAULT 5.00 COMMENT 'Puntuación promedio (1.00 a 5.00)',
ADD COLUMN total_calificaciones INT DEFAULT 0 COMMENT 'Total de servicios calificados',
ADD COLUMN servicios_completados INT DEFAULT 0 COMMENT 'Total de servicios completados';

-- Cambiar profesional_id para que sea asignado por admin, no por paciente
-- Ya no debe venir pre-seleccionado en la solicitud inicial
ALTER TABLE solicitudes
MODIFY COLUMN profesional_id INT UNSIGNED NULL COMMENT 'Asignado por admin después de creación';

-- Actualizar estados para incluir 'pendiente_asignacion'
ALTER TABLE solicitudes
MODIFY COLUMN estado ENUM(
    'pendiente_asignacion',  -- Nueva: esperando que admin asigne profesional
    'pendiente',             -- Profesional asignado, esperando aceptación
    'confirmada',            -- Profesional aceptó
    'en_progreso',           -- Servicio iniciado
    'completada',            -- Servicio terminado
    'pendiente_calificacion', -- Nueva: esperando calificación del paciente
    'finalizada',            -- Nueva: calificada y cerrada
    'cancelada',
    'rechazada'
) DEFAULT 'pendiente_asignacion';

-- Crear tabla de historial de asignaciones (para auditoría)
CREATE TABLE IF NOT EXISTS `asignaciones_profesional` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `solicitud_id` INT UNSIGNED NOT NULL,
    `profesional_id` INT UNSIGNED NOT NULL,
    `asignado_por` INT UNSIGNED NOT NULL COMMENT 'Admin que asignó',
    `motivo` TEXT NULL COMMENT 'Razón de la asignación',
    `fecha_asignacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`profesional_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`asignado_por`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    INDEX idx_solicitud (solicitud_id),
    INDEX idx_profesional (profesional_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Actualizar solicitudes existentes para que estén pendientes de asignación
UPDATE solicitudes 
SET estado = 'pendiente_asignacion' 
WHERE estado = 'pendiente' AND profesional_id IS NULL;

-- Inicializar puntuaciones de profesionales existentes
UPDATE usuarios 
SET puntuacion_promedio = 5.00, 
    total_calificaciones = 0,
    servicios_completados = 0
WHERE rol IN ('medico', 'profesional', 'enfermera', 'veterinario', 'laboratorio');

SELECT 'Sistema de calificaciones creado exitosamente' AS mensaje;
