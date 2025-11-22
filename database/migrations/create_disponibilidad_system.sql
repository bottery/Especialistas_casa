-- ============================================
-- MIGRACIÓN: Sistema de Disponibilidad en Tiempo Real
-- Fecha: 2025-11-17
-- ============================================

-- 1. Crear tabla de disponibilidad
CREATE TABLE IF NOT EXISTS disponibilidad_profesional (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profesional_id INT UNSIGNED NOT NULL,
    dia_semana ENUM('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profesional_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_profesional (profesional_id),
    INDEX idx_dia (dia_semana),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Crear tabla de bloques de tiempo específicos (excepciones, vacaciones, etc.)
CREATE TABLE IF NOT EXISTS bloques_no_disponibles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profesional_id INT UNSIGNED NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    motivo VARCHAR(255),
    tipo ENUM('vacaciones', 'personal', 'capacitacion', 'otro') DEFAULT 'otro',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profesional_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_profesional (profesional_id),
    INDEX idx_fechas (fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Agregar columnas de disponibilidad inmediata a usuarios
ALTER TABLE usuarios 
ADD COLUMN disponible_ahora BOOLEAN DEFAULT FALSE COMMENT 'Disponible para atención inmediata',
ADD COLUMN ultima_actividad TIMESTAMP NULL COMMENT 'Última vez que el profesional estuvo activo',
ADD COLUMN tiempo_respuesta_promedio INT DEFAULT 30 COMMENT 'Tiempo promedio de respuesta en minutos';

-- 4. Agregar columna de duración estimada a servicios
ALTER TABLE servicios 
ADD COLUMN duracion_estimada INT DEFAULT 60 COMMENT 'Duración estimada del servicio en minutos';

-- 5. Actualizar solicitudes para incluir tiempo estimado
ALTER TABLE solicitudes 
ADD COLUMN fecha_estimada_inicio DATETIME NULL COMMENT 'Fecha y hora estimada de inicio del servicio',
ADD COLUMN fecha_estimada_fin DATETIME NULL COMMENT 'Fecha y hora estimada de finalización',
ADD COLUMN duracion_real INT NULL COMMENT 'Duración real del servicio en minutos';

-- 6. Insertar disponibilidad por defecto para profesionales existentes (8am - 6pm de lunes a viernes)
INSERT INTO disponibilidad_profesional (profesional_id, dia_semana, hora_inicio, hora_fin)
SELECT 
    u.id,
    d.dia,
    '08:00:00',
    '18:00:00'
FROM usuarios u
CROSS JOIN (
    SELECT 'lunes' as dia UNION ALL
    SELECT 'martes' UNION ALL
    SELECT 'miercoles' UNION ALL
    SELECT 'jueves' UNION ALL
    SELECT 'viernes'
) d
WHERE u.rol = 'profesional'
AND NOT EXISTS (
    SELECT 1 FROM disponibilidad_profesional dp 
    WHERE dp.profesional_id = u.id AND dp.dia_semana = d.dia
);

-- 7. Actualizar duración estimada de servicios comunes
UPDATE servicios SET duracion_estimada = 30 WHERE nombre LIKE '%consulta%' OR nombre LIKE '%revisión%';
UPDATE servicios SET duracion_estimada = 90 WHERE nombre LIKE '%procedimiento%' OR nombre LIKE '%cirugía menor%';
UPDATE servicios SET duracion_estimada = 120 WHERE nombre LIKE '%cirugía%' AND nombre NOT LIKE '%menor%';
UPDATE servicios SET duracion_estimada = 45 WHERE nombre LIKE '%terapia%' OR nombre LIKE '%fisioterapia%';

SELECT 'Sistema de disponibilidad creado exitosamente' as resultado;
