-- ============================================
-- Migración: Agregar índices optimizados
-- Fecha: 2025-11-16
-- Descripción: Optimizar consultas frecuentes
-- ============================================

-- Índices para tabla usuarios
CREATE INDEX IF NOT EXISTS idx_usuarios_documento ON usuarios(documento_tipo, documento_numero);
CREATE INDEX IF NOT EXISTS idx_usuarios_estado_rol ON usuarios(estado, rol);
CREATE INDEX IF NOT EXISTS idx_usuarios_verificado ON usuarios(verificado);
CREATE INDEX IF NOT EXISTS idx_usuarios_created_at ON usuarios(created_at);

-- Índices para tabla solicitudes
CREATE INDEX IF NOT EXISTS idx_solicitudes_paciente_estado ON solicitudes(paciente_id, estado);
CREATE INDEX IF NOT EXISTS idx_solicitudes_profesional_estado ON solicitudes(profesional_id, estado);
CREATE INDEX IF NOT EXISTS idx_solicitudes_servicio ON solicitudes(servicio_id);
CREATE INDEX IF NOT EXISTS idx_solicitudes_fecha ON solicitudes(fecha_programada);
CREATE INDEX IF NOT EXISTS idx_solicitudes_estado_fecha ON solicitudes(estado, fecha_programada);
CREATE INDEX IF NOT EXISTS idx_solicitudes_created_at ON solicitudes(created_at);

-- Índices para tabla pagos
CREATE INDEX IF NOT EXISTS idx_pagos_solicitud ON pagos(solicitud_id);
CREATE INDEX IF NOT EXISTS idx_pagos_estado ON pagos(estado);
CREATE INDEX IF NOT EXISTS idx_pagos_metodo_estado ON pagos(metodo_pago, estado);
CREATE INDEX IF NOT EXISTS idx_pagos_fecha ON pagos(fecha_pago);

-- Índices para tabla notificaciones
CREATE INDEX IF NOT EXISTS idx_notificaciones_usuario_leido ON notificaciones(usuario_id, leido);
CREATE INDEX IF NOT EXISTS idx_notificaciones_tipo ON notificaciones(tipo);
CREATE INDEX IF NOT EXISTS idx_notificaciones_created_at ON notificaciones(created_at);

-- Índices para tabla logs_auditoria
CREATE INDEX IF NOT EXISTS idx_logs_usuario ON logs_auditoria(usuario_id);
CREATE INDEX IF NOT EXISTS idx_logs_accion ON logs_auditoria(accion);
CREATE INDEX IF NOT EXISTS idx_logs_tabla ON logs_auditoria(tabla);
CREATE INDEX IF NOT EXISTS idx_logs_created_at ON logs_auditoria(created_at);

-- Índices para tabla historial_medico
CREATE INDEX IF NOT EXISTS idx_historial_paciente ON historial_medico(paciente_id);
CREATE INDEX IF NOT EXISTS idx_historial_profesional ON historial_medico(profesional_id);
CREATE INDEX IF NOT EXISTS idx_historial_tipo ON historial_medico(tipo_registro);

-- Índices para tabla profesional_servicios
CREATE INDEX IF NOT EXISTS idx_prof_servicios_profesional ON profesional_servicios(profesional_id);
CREATE INDEX IF NOT EXISTS idx_prof_servicios_servicio ON profesional_servicios(servicio_id);

-- Índices para tabla servicios
CREATE INDEX IF NOT EXISTS idx_servicios_tipo ON servicios(tipo);
CREATE INDEX IF NOT EXISTS idx_servicios_activo ON servicios(activo);
CREATE INDEX IF NOT EXISTS idx_servicios_tipo_activo ON servicios(tipo, activo);

-- Índices para tabla sesiones
CREATE INDEX IF NOT EXISTS idx_sesiones_usuario ON sesiones(usuario_id);
CREATE INDEX IF NOT EXISTS idx_sesiones_expires_at ON sesiones(expires_at);
CREATE INDEX IF NOT EXISTS idx_sesiones_token ON sesiones(token);

-- ============================================
-- Tabla para blacklist de tokens JWT
-- ============================================
CREATE TABLE IF NOT EXISTS `token_blacklist` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `token_hash` VARCHAR(64) NOT NULL UNIQUE,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_token_hash` (`token_hash`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Procedimiento: Limpiar tokens expirados
-- ============================================
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS clean_expired_tokens()
BEGIN
    DELETE FROM token_blacklist WHERE expires_at < NOW();
END //
DELIMITER ;

-- ============================================
-- Procedimiento: Limpiar logs antiguos
-- ============================================
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS clean_old_audit_logs(IN retention_days INT)
BEGIN
    DELETE FROM logs_auditoria 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL retention_days DAY);
END //
DELIMITER ;

-- ============================================
-- Trigger: Auditar cambios en usuarios
-- ============================================
DELIMITER //
CREATE TRIGGER IF NOT EXISTS usuarios_after_update
AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF OLD.estado != NEW.estado OR OLD.rol != NEW.rol THEN
        INSERT INTO logs_auditoria (usuario_id, accion, tabla, registro_id, datos_anteriores, datos_nuevos)
        VALUES (
            NEW.id,
            'update',
            'usuarios',
            NEW.id,
            JSON_OBJECT('estado', OLD.estado, 'rol', OLD.rol),
            JSON_OBJECT('estado', NEW.estado, 'rol', NEW.rol)
        );
    END IF;
END //
DELIMITER ;

-- ============================================
-- Tabla: rate_limits (para rate limiting)
-- ============================================
CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key_hash` VARCHAR(64) NOT NULL,
    `attempts` INT UNSIGNED DEFAULT 0,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_key_hash` (`key_hash`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Optimizaciones adicionales
-- ============================================

-- Analizar tablas para optimizar queries
ANALYZE TABLE usuarios;
ANALYZE TABLE solicitudes;
ANALYZE TABLE pagos;
ANALYZE TABLE servicios;
ANALYZE TABLE notificaciones;

-- Optimizar tablas
OPTIMIZE TABLE usuarios;
OPTIMIZE TABLE solicitudes;
OPTIMIZE TABLE pagos;
OPTIMIZE TABLE servicios;
OPTIMIZE TABLE notificaciones;
