-- Tabla de relación entre profesionales y servicios
-- Permite que un profesional ofrezca múltiples servicios

CREATE TABLE IF NOT EXISTS `profesional_servicios` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `profesional_id` INT UNSIGNED NOT NULL,
    `servicio_id` INT UNSIGNED NOT NULL,
    `disponible` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`profesional_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`servicio_id`) REFERENCES `servicios`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_profesional_servicio` (`profesional_id`, `servicio_id`),
    INDEX `idx_profesional` (`profesional_id`),
    INDEX `idx_servicio` (`servicio_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar datos de ejemplo (relacionar profesionales existentes con servicios)
-- Asumiendo que tienes servicios y profesionales en la BD

-- Médicos con servicios médicos
INSERT IGNORE INTO profesional_servicios (profesional_id, servicio_id, disponible)
SELECT u.id, s.id, TRUE
FROM usuarios u
CROSS JOIN servicios s
WHERE u.rol = 'medico' AND s.tipo = 'medico' AND u.estado = 'activo' AND s.activo = 1;

-- Enfermeras con servicios de enfermería
INSERT IGNORE INTO profesional_servicios (profesional_id, servicio_id, disponible)
SELECT u.id, s.id, TRUE
FROM usuarios u
CROSS JOIN servicios s
WHERE u.rol = 'enfermera' AND s.tipo = 'enfermera' AND u.estado = 'activo' AND s.activo = 1;

-- Veterinarios con servicios veterinarios
INSERT IGNORE INTO profesional_servicios (profesional_id, servicio_id, disponible)
SELECT u.id, s.id, TRUE
FROM usuarios u
CROSS JOIN servicios s
WHERE u.rol = 'veterinario' AND s.tipo = 'veterinario' AND u.estado = 'activo' AND s.activo = 1;
