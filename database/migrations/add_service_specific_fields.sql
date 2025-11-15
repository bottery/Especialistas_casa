-- Migración: Agregar campos específicos por tipo de servicio
-- Fecha: 2025-11-14

ALTER TABLE `solicitudes`
-- Campos para Médicos Especialistas
ADD COLUMN `especialidad` VARCHAR(100) COMMENT 'Especialidad médica requerida',
ADD COLUMN `rango_horario` ENUM('manana', 'tarde') COMMENT 'Médico: mañana (8am-12pm) o tarde (2pm-6pm)',
ADD COLUMN `requiere_aprobacion` BOOLEAN DEFAULT FALSE COMMENT 'Si requiere aprobación del profesional',

-- Campos para Ambulancia
ADD COLUMN `tipo_ambulancia` ENUM('basica', 'medicalizada') COMMENT 'Tipo de ambulancia',
ADD COLUMN `origen` TEXT COMMENT 'Dirección de origen/recogida',
ADD COLUMN `destino` TEXT COMMENT 'Dirección de destino/entrega',
ADD COLUMN `tipo_emergencia` ENUM('urgente', 'programado') COMMENT 'Urgencia del traslado',
ADD COLUMN `condicion_paciente` TEXT COMMENT 'Estado del paciente (estable, crítico, etc)',
ADD COLUMN `numero_acompanantes` INT UNSIGNED DEFAULT 0 COMMENT 'Número de acompañantes',
ADD COLUMN `contacto_emergencia` VARCHAR(255) COMMENT 'Nombre y teléfono de contacto',

-- Campos para Enfermería
ADD COLUMN `tipo_cuidado` VARCHAR(100) COMMENT 'Tipo: cuidado general, inyecciones, curaciones, etc',
ADD COLUMN `intensidad_horaria` ENUM('12h', '24h') COMMENT 'Horas continuas por turno',
ADD COLUMN `duracion_tipo` ENUM('dias', 'semanas', 'meses') COMMENT 'Unidad de duración',
ADD COLUMN `duracion_cantidad` INT UNSIGNED COMMENT 'Cantidad de días/semanas/meses',
ADD COLUMN `turno` ENUM('diurno', 'nocturno', 'mixto') COMMENT 'Horario del turno',
ADD COLUMN `genero_preferido` ENUM('masculino', 'femenino', 'indistinto') DEFAULT 'indistinto',
ADD COLUMN `necesidades_especiales` TEXT COMMENT 'Sondas, oxígeno, medicación, etc',
ADD COLUMN `condicion_paciente_detalle` TEXT COMMENT 'Movilidad, alzheimer, diabetes, etc',

-- Campos para Veterinaria
ADD COLUMN `tipo_mascota` VARCHAR(50) COMMENT 'Perro, gato, ave, etc',
ADD COLUMN `nombre_mascota` VARCHAR(100),
ADD COLUMN `edad_mascota` VARCHAR(50),
ADD COLUMN `raza_tamano` VARCHAR(100) COMMENT 'Raza y/o tamaño',
ADD COLUMN `motivo_veterinario` VARCHAR(100) COMMENT 'Vacunación, enfermedad, revisión, emergencia',
ADD COLUMN `historial_vacunas` TEXT COMMENT 'Historial de vacunación',

-- Campos para Laboratorio
ADD COLUMN `examenes_solicitados` JSON COMMENT 'Array de exámenes requeridos',
ADD COLUMN `requiere_ayuno` BOOLEAN DEFAULT FALSE COMMENT 'Si requiere ayuno',
ADD COLUMN `preparacion_especial` TEXT COMMENT 'Instrucciones de preparación',
ADD COLUMN `orden_medica_url` TEXT COMMENT 'URL del archivo de orden médica',
ADD COLUMN `email_resultados` VARCHAR(255) COMMENT 'Email para enviar resultados',

-- Campos para Fisioterapia
ADD COLUMN `tipo_tratamiento` VARCHAR(100) COMMENT 'Rehabilitación, deportiva, geriátrica, neurológica',
ADD COLUMN `numero_sesiones` INT UNSIGNED COMMENT 'Cantidad de sesiones',
ADD COLUMN `frecuencia_sesiones` ENUM('diario', 'interdiario', 'semanal') COMMENT 'Frecuencia de sesiones',
ADD COLUMN `zona_tratamiento` VARCHAR(100) COMMENT 'Parte del cuerpo a tratar',
ADD COLUMN `lesion_condicion` TEXT COMMENT 'Descripción de lesión o condición',
ADD COLUMN `orden_medica_fisio` TEXT COMMENT 'Orden médica para fisioterapia',

-- Campos para Psicología
ADD COLUMN `tipo_sesion_psico` ENUM('individual', 'pareja', 'familiar', 'infantil') COMMENT 'Tipo de sesión psicológica',
ADD COLUMN `motivo_consulta_psico` VARCHAR(100) COMMENT 'Ansiedad, depresión, duelo, etc',
ADD COLUMN `primera_vez` BOOLEAN DEFAULT TRUE COMMENT 'Primera consulta',
ADD COLUMN `observaciones_privadas` TEXT COMMENT 'Información confidencial',

-- Campos para Nutrición
ADD COLUMN `tipo_consulta_nutri` VARCHAR(100) COMMENT 'Pérdida peso, deportiva, enfermedades, etc',
ADD COLUMN `objetivos_nutri` TEXT COMMENT 'Objetivos del plan nutricional',
ADD COLUMN `peso_actual` DECIMAL(5,2) COMMENT 'Peso en kg',
ADD COLUMN `altura_actual` DECIMAL(5,2) COMMENT 'Altura en metros',
ADD COLUMN `condiciones_medicas` TEXT COMMENT 'Diabetes, hipertensión, alergias',
ADD COLUMN `incluye_plan_alimenticio` BOOLEAN DEFAULT TRUE COMMENT 'Si incluye plan de comidas',

-- Campos comunes adicionales
ADD COLUMN `telefono_contacto` VARCHAR(20),
ADD COLUMN `urgencia` ENUM('normal', 'urgente') DEFAULT 'normal',
ADD COLUMN `metodo_pago_preferido` ENUM('efectivo', 'tarjeta', 'transferencia');

-- Índices para mejorar búsquedas
CREATE INDEX `idx_tipo_ambulancia` ON `solicitudes`(`tipo_ambulancia`);
CREATE INDEX `idx_tipo_emergencia` ON `solicitudes`(`tipo_emergencia`);
CREATE INDEX `idx_urgencia` ON `solicitudes`(`urgencia`);
