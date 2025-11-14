-- ============================================
-- Base de datos: Especialistas en Casa
-- Versión: 1.0.0
-- PHP: 8.2+ | MySQL: 8.0+
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- TABLA: usuarios
-- Almacena todos los usuarios del sistema
-- ============================================
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `rol` ENUM('paciente', 'medico', 'enfermera', 'veterinario', 'laboratorio', 'ambulancia', 'admin', 'superadmin') NOT NULL,
    `nombre` VARCHAR(255) NOT NULL,
    `apellido` VARCHAR(255) NOT NULL,
    `telefono` VARCHAR(20),
    `direccion` TEXT,
    `ciudad` VARCHAR(100),
    `documento_tipo` ENUM('CC', 'TI', 'CE', 'PP', 'NIT') DEFAULT 'CC',
    `documento_numero` VARCHAR(50),
    `fecha_nacimiento` DATE,
    `genero` ENUM('masculino', 'femenino', 'otro', 'prefiero_no_decir'),
    `foto_perfil` VARCHAR(255),
    `estado` ENUM('pendiente', 'activo', 'inactivo', 'bloqueado') DEFAULT 'pendiente',
    `verificado` BOOLEAN DEFAULT FALSE,
    `ultimo_acceso` TIMESTAMP NULL,
    `remember_token` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_rol` (`rol`),
    INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: perfiles_profesionales
-- Datos específicos de médicos y profesionales
-- ============================================
CREATE TABLE IF NOT EXISTS `perfiles_profesionales` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT UNSIGNED NOT NULL,
    `especialidad` VARCHAR(255) NOT NULL,
    `registro_profesional` VARCHAR(100) NOT NULL,
    `universidad` VARCHAR(255),
    `años_experiencia` INT UNSIGNED,
    `descripcion` TEXT,
    `tarifa_consulta_virtual` DECIMAL(10, 2),
    `tarifa_consulta_presencial` DECIMAL(10, 2),
    `tarifa_consultorio` DECIMAL(10, 2),
    `horario_disponibilidad` JSON,
    `documento_registro` VARCHAR(255),
    `documento_cedula` VARCHAR(255),
    `documento_diploma` VARCHAR(255),
    `contrato_firmado` VARCHAR(255),
    `cuenta_bancaria` VARCHAR(50),
    `banco` VARCHAR(100),
    `tipo_cuenta` ENUM('ahorros', 'corriente'),
    `aprobado` BOOLEAN DEFAULT FALSE,
    `fecha_aprobacion` TIMESTAMP NULL,
    `aprobado_por` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_especialidad` (`especialidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: servicios
-- Tipos de servicios ofrecidos
-- ============================================
CREATE TABLE IF NOT EXISTS `servicios` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(255) NOT NULL,
    `descripcion` TEXT,
    `tipo` ENUM('medico', 'enfermera', 'veterinario', 'laboratorio', 'ambulancia') NOT NULL,
    `modalidad` ENUM('virtual', 'presencial', 'consultorio') NOT NULL,
    `precio_base` DECIMAL(10, 2) NOT NULL,
    `duracion_estimada` INT UNSIGNED COMMENT 'Duración en minutos',
    `activo` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tipo` (`tipo`),
    INDEX `idx_modalidad` (`modalidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: solicitudes
-- Solicitudes de servicios realizadas por pacientes
-- ============================================
CREATE TABLE IF NOT EXISTS `solicitudes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `paciente_id` INT UNSIGNED NOT NULL,
    `servicio_id` INT UNSIGNED NOT NULL,
    `profesional_id` INT UNSIGNED,
    `modalidad` ENUM('virtual', 'presencial', 'consultorio') NOT NULL,
    `fecha_solicitud` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_programada` DATETIME NOT NULL,
    `direccion_servicio` TEXT,
    `sintomas` TEXT,
    `observaciones` TEXT,
    `documentos_adjuntos` JSON,
    `estado` ENUM('pendiente', 'confirmada', 'en_progreso', 'completada', 'cancelada', 'rechazada') DEFAULT 'pendiente',
    `monto_total` DECIMAL(10, 2) NOT NULL,
    `monto_profesional` DECIMAL(10, 2),
    `monto_plataforma` DECIMAL(10, 2),
    `pagado` BOOLEAN DEFAULT FALSE,
    `pago_id` INT UNSIGNED,
    `resultado` TEXT,
    `reporte_medico` TEXT,
    `receta` TEXT,
    `archivos_resultado` JSON,
    `fecha_completada` TIMESTAMP NULL,
    `cancelado_por` INT UNSIGNED,
    `razon_cancelacion` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`paciente_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`servicio_id`) REFERENCES `servicios`(`id`),
    FOREIGN KEY (`profesional_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
    INDEX `idx_paciente_id` (`paciente_id`),
    INDEX `idx_profesional_id` (`profesional_id`),
    INDEX `idx_estado` (`estado`),
    INDEX `idx_fecha_programada` (`fecha_programada`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: pagos
-- Registro de todos los pagos
-- ============================================
CREATE TABLE IF NOT EXISTS `pagos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `solicitud_id` INT UNSIGNED NOT NULL,
    `usuario_id` INT UNSIGNED NOT NULL,
    `metodo_pago` ENUM('pse', 'transferencia') NOT NULL,
    `monto` DECIMAL(10, 2) NOT NULL,
    `estado` ENUM('pendiente', 'aprobado', 'rechazado', 'reembolsado') DEFAULT 'pendiente',
    `referencia_pago` VARCHAR(255),
    `comprobante` VARCHAR(255),
    `fecha_pago` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `aprobado_por` INT UNSIGNED,
    `fecha_aprobacion` TIMESTAMP NULL,
    `notas` TEXT,
    `datos_transaccion` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    INDEX `idx_solicitud_id` (`solicitud_id`),
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_estado` (`estado`),
    INDEX `idx_metodo_pago` (`metodo_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: facturas
-- Facturas digitales generadas automáticamente
-- ============================================
CREATE TABLE IF NOT EXISTS `facturas` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `numero_factura` VARCHAR(50) NOT NULL UNIQUE,
    `solicitud_id` INT UNSIGNED NOT NULL,
    `pago_id` INT UNSIGNED NOT NULL,
    `usuario_id` INT UNSIGNED NOT NULL,
    `fecha_emision` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `subtotal` DECIMAL(10, 2) NOT NULL,
    `iva` DECIMAL(10, 2) DEFAULT 0,
    `total` DECIMAL(10, 2) NOT NULL,
    `archivo_pdf` VARCHAR(255),
    `enviado_email` BOOLEAN DEFAULT FALSE,
    `fecha_envio` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`pago_id`) REFERENCES `pagos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    INDEX `idx_numero_factura` (`numero_factura`),
    INDEX `idx_usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: historial_medico
-- Historial médico de cada paciente
-- ============================================
CREATE TABLE IF NOT EXISTS `historial_medico` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `paciente_id` INT UNSIGNED NOT NULL,
    `solicitud_id` INT UNSIGNED,
    `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `tipo` ENUM('consulta', 'diagnostico', 'tratamiento', 'examen', 'vacuna', 'cirugia', 'otro') NOT NULL,
    `titulo` VARCHAR(255) NOT NULL,
    `descripcion` TEXT,
    `diagnostico` TEXT,
    `tratamiento` TEXT,
    `medicamentos` JSON,
    `alergias` JSON,
    `archivos_adjuntos` JSON,
    `profesional_id` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`paciente_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`profesional_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
    INDEX `idx_paciente_id` (`paciente_id`),
    INDEX `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: notificaciones
-- Sistema de notificaciones
-- ============================================
CREATE TABLE IF NOT EXISTS `notificaciones` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT UNSIGNED NOT NULL,
    `tipo` ENUM('email', 'push', 'sistema') NOT NULL,
    `titulo` VARCHAR(255) NOT NULL,
    `mensaje` TEXT NOT NULL,
    `datos` JSON,
    `leida` BOOLEAN DEFAULT FALSE,
    `fecha_leida` TIMESTAMP NULL,
    `enviada` BOOLEAN DEFAULT FALSE,
    `fecha_envio` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_leida` (`leida`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: configuraciones
-- Configuraciones del sistema
-- ============================================
CREATE TABLE IF NOT EXISTS `configuraciones` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `clave` VARCHAR(255) NOT NULL UNIQUE,
    `valor` TEXT,
    `tipo` ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    `categoria` VARCHAR(100),
    `descripcion` TEXT,
    `editable_admin` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_clave` (`clave`),
    INDEX `idx_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: logs_auditoria
-- Registro de auditoría para cumplimiento HIPAA
-- ============================================
CREATE TABLE IF NOT EXISTS `logs_auditoria` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT UNSIGNED,
    `accion` VARCHAR(255) NOT NULL,
    `tabla` VARCHAR(100),
    `registro_id` INT UNSIGNED,
    `datos_anteriores` JSON,
    `datos_nuevos` JSON,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_accion` (`accion`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: sesiones
-- Control de sesiones activas
-- ============================================
CREATE TABLE IF NOT EXISTS `sesiones` (
    `id` VARCHAR(255) PRIMARY KEY,
    `usuario_id` INT UNSIGNED,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `payload` TEXT NOT NULL,
    `last_activity` INT UNSIGNED NOT NULL,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERCIÓN DE DATOS INICIALES
-- ============================================

-- Usuarios administradores por defecto
INSERT INTO `usuarios` (`email`, `password`, `rol`, `nombre`, `apellido`, `estado`, `verificado`) VALUES
('superadmin@especialistas.com', '$2y$12$LQv3c1yycse4nFpWNmxPXed0vGdGVlLSr3YcJLvVJzGxpYDZzDPxC', 'superadmin', 'Super', 'Administrador', 'activo', TRUE),
('admin@especialistas.com', '$2y$12$LQv3c1yycse4nFpWNmxPXed0vGdGVlLSr3YcJLvVJzGxpYDZzDPxC', 'admin', 'Administrador', 'Sistema', 'activo', TRUE);

-- Servicios base
INSERT INTO `servicios` (`nombre`, `descripcion`, `tipo`, `modalidad`, `precio_base`, `duracion_estimada`) VALUES
('Consulta Médica Virtual', 'Consulta médica general por videollamada', 'medico', 'virtual', 50000.00, 30),
('Consulta Médica a Domicilio', 'Consulta médica general en su hogar', 'medico', 'presencial', 80000.00, 45),
('Consulta en Consultorio', 'Consulta médica general en consultorio', 'medico', 'consultorio', 60000.00, 30),
('Toma de Muestras a Domicilio', 'Servicio de laboratorio en casa', 'laboratorio', 'presencial', 40000.00, 20),
('Atención de Enfermería', 'Servicio de enfermería a domicilio', 'enfermera', 'presencial', 45000.00, 30),
('Consulta Veterinaria Virtual', 'Consulta veterinaria por videollamada', 'veterinario', 'virtual', 45000.00, 25),
('Consulta Veterinaria a Domicilio', 'Atención veterinaria en casa', 'veterinario', 'presencial', 70000.00, 40),
('Servicio de Ambulancia', 'Traslado médico de emergencia', 'ambulancia', 'presencial', 150000.00, 60);

-- Configuraciones iniciales
INSERT INTO `configuraciones` (`clave`, `valor`, `tipo`, `categoria`, `descripcion`, `editable_admin`) VALUES
('comision_plataforma', '15', 'number', 'pagos', 'Porcentaje de comisión de la plataforma', TRUE),
('iva_servicios', '19', 'number', 'pagos', 'Porcentaje de IVA aplicable', TRUE),
('tiempo_cancelacion', '24', 'number', 'servicios', 'Horas previas para cancelar sin penalización', TRUE),
('max_servicios_simultaneos', '5', 'number', 'servicios', 'Máximo de servicios simultáneos por profesional', TRUE),
('modo_mantenimiento', 'false', 'boolean', 'sistema', 'Activar modo de mantenimiento', TRUE),
('registro_pacientes_activo', 'true', 'boolean', 'sistema', 'Permitir registro de nuevos pacientes', TRUE),
('onesignal_configurado', 'false', 'boolean', 'notificaciones', 'OneSignal está configurado', FALSE),
('pse_configurado', 'false', 'boolean', 'pagos', 'PSE está configurado', FALSE);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- FIN DEL ESQUEMA
-- ============================================
