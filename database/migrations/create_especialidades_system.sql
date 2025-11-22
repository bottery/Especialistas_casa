-- ============================================
-- MIGRACIÓN: Sistema de Especialidades Controladas
-- Fecha: 2025-11-17
-- ============================================

-- 1. Crear tabla de especialidades
CREATE TABLE IF NOT EXISTS especialidades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    tipo_profesional ENUM('medico', 'enfermera', 'veterinario', 'laboratorio', 'ambulancia') NOT NULL,
    descripcion TEXT,
    icono VARCHAR(50) DEFAULT '🩺',
    activo BOOLEAN DEFAULT TRUE,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo_profesional),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Crear tabla de relación profesional-especialidades (muchos a muchos)
CREATE TABLE IF NOT EXISTS profesional_especialidades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profesional_id INT UNSIGNED NOT NULL,
    especialidad_id INT UNSIGNED NOT NULL,
    es_principal BOOLEAN DEFAULT FALSE COMMENT 'Especialidad principal del profesional',
    años_experiencia INT DEFAULT 0,
    certificaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profesional_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE CASCADE,
    UNIQUE KEY unique_prof_esp (profesional_id, especialidad_id),
    INDEX idx_profesional (profesional_id),
    INDEX idx_especialidad (especialidad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Insertar especialidades médicas iniciales
INSERT INTO especialidades (nombre, tipo_profesional, descripcion, icono, orden) VALUES
('Medicina General', 'medico', 'Atención médica general y consultas de primera línea', '👨‍⚕️', 1),
('Cardiología', 'medico', 'Especialista en corazón y sistema cardiovascular', '❤️', 2),
('Dermatología', 'medico', 'Cuidado de la piel, cabello y uñas', '🧴', 3),
('Pediatría', 'medico', 'Atención médica para niños y adolescentes', '👶', 4),
('Ginecología', 'medico', 'Salud reproductiva femenina', '🤰', 5),
('Medicina Interna', 'medico', 'Diagnóstico y tratamiento de enfermedades internas', '🩺', 6),
('Traumatología', 'medico', 'Lesiones y problemas del sistema musculoesquelético', '🦴', 7),
('Neurología', 'medico', 'Sistema nervioso y cerebro', '🧠', 8),
('Oftalmología', 'medico', 'Cuidado de los ojos y visión', '👁️', 9),
('Psiquiatría', 'medico', 'Salud mental y trastornos psiquiátricos', '🧘', 10);

-- 4. Insertar especialidades de enfermería
INSERT INTO especialidades (nombre, tipo_profesional, descripcion, icono, orden) VALUES
('Enfermería General', 'enfermera', 'Cuidados de enfermería generales', '💉', 1),
('Cuidados Intensivos', 'enfermera', 'Atención en UCI y pacientes críticos', '🏥', 2),
('Enfermería Pediátrica', 'enfermera', 'Cuidados especializados para niños', '👶', 3),
('Enfermería Geriátrica', 'enfermera', 'Atención a adultos mayores', '👴', 4),
('Enfermería Domiciliaria', 'enfermera', 'Cuidados en el hogar del paciente', '🏠', 5);

-- 5. Insertar especialidades veterinarias
INSERT INTO especialidades (nombre, tipo_profesional, descripcion, icono, orden) VALUES
('Veterinaria General', 'veterinario', 'Atención veterinaria general', '🐕', 1),
('Medicina Felina', 'veterinario', 'Especialista en gatos', '🐈', 2),
('Medicina Canina', 'veterinario', 'Especialista en perros', '🐕', 3),
('Animales Exóticos', 'veterinario', 'Aves, reptiles y otros exóticos', '🦜', 4),
('Cirugía Veterinaria', 'veterinario', 'Procedimientos quirúrgicos veterinarios', '⚕️', 5);

-- 6. Insertar tipos de servicios de laboratorio
INSERT INTO especialidades (nombre, tipo_profesional, descripcion, icono, orden) VALUES
('Análisis Clínicos', 'laboratorio', 'Pruebas de sangre y orina', '🔬', 1),
('Microbiología', 'laboratorio', 'Cultivos y análisis bacteriológicos', '🦠', 2),
('Imagenología', 'laboratorio', 'Rayos X, ecografías, resonancias', '📷', 3),
('Patología', 'laboratorio', 'Análisis de tejidos y biopsias', '🔬', 4);

-- 7. Insertar tipos de ambulancia
INSERT INTO especialidades (nombre, tipo_profesional, descripcion, icono, orden) VALUES
('Ambulancia Básica', 'ambulancia', 'Traslado de pacientes no críticos', '🚑', 1),
('Ambulancia Medicalizada', 'ambulancia', 'Traslado con soporte médico avanzado', '🚑', 2),
('Ambulancia UCI Móvil', 'ambulancia', 'Cuidados intensivos durante traslado', '🚑', 3);

-- 8. Migrar datos existentes (especialidad de usuarios a profesional_especialidades)
-- Insertar como especialidad principal
INSERT INTO profesional_especialidades (profesional_id, especialidad_id, es_principal)
SELECT 
    u.id as profesional_id,
    e.id as especialidad_id,
    TRUE as es_principal
FROM usuarios u
INNER JOIN especialidades e ON e.nombre = u.especialidad
WHERE u.rol = 'profesional' 
AND u.especialidad IS NOT NULL
AND u.especialidad != ''
ON DUPLICATE KEY UPDATE es_principal = TRUE;

-- 9. Actualizar solicitudes (cambiar especialidad_solicitada por ID)
-- Primero agregar nueva columna
ALTER TABLE solicitudes 
ADD COLUMN especialidad_id INT UNSIGNED NULL AFTER especialidad_solicitada,
ADD FOREIGN KEY fk_solicitud_especialidad (especialidad_id) REFERENCES especialidades(id) ON DELETE SET NULL;

-- Migrar datos de texto a ID
UPDATE solicitudes s
INNER JOIN especialidades e ON e.nombre LIKE CONCAT('%', s.especialidad_solicitada, '%')
SET s.especialidad_id = e.id
WHERE s.especialidad_solicitada IS NOT NULL;

SELECT 'Migración de especialidades completada exitosamente' as resultado;
