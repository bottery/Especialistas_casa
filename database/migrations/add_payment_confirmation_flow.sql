-- Migración: Sistema de confirmación de pagos por transferencia
-- Fecha: 2025-11-15
-- Descripción: Agregar QR bancario, comprobantes, nuevos estados

-- 1. Crear tabla para configuración del QR bancario
CREATE TABLE IF NOT EXISTS `configuracion_pagos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `qr_imagen_path` VARCHAR(255) NULL COMMENT 'Ruta de la imagen QR para transferencias',
    `banco_nombre` VARCHAR(100) NULL,
    `banco_cuenta` VARCHAR(50) NULL,
    `banco_tipo_cuenta` VARCHAR(50) NULL,
    `banco_titular` VARCHAR(150) NULL,
    `instrucciones_transferencia` TEXT NULL,
    `whatsapp_contacto` VARCHAR(20) DEFAULT '+57 300 123 4567',
    `activo` TINYINT(1) DEFAULT 1,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` INT UNSIGNED NULL,
    FOREIGN KEY (`updated_by`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuración inicial
INSERT INTO `configuracion_pagos` (id, instrucciones_transferencia, whatsapp_contacto, activo)
VALUES (1, 'Realiza tu transferencia y envía el comprobante por WhatsApp para confirmar tu pago.', '+57 300 123 4567', 1)
ON DUPLICATE KEY UPDATE id=id;

-- 2. Modificar tabla solicitudes: agregar nuevo estado
ALTER TABLE `solicitudes` 
MODIFY COLUMN `estado` ENUM(
    'pendiente_confirmacion_pago',  -- NUEVO: Esperando confirmación de transferencia
    'pendiente',                     -- Pago confirmado, esperando asignación de profesional
    'confirmada',                    -- Profesional asignado y aceptó
    'en_progreso',                   -- Servicio en ejecución
    'completada',                    -- Servicio finalizado
    'cancelada',                     -- Cancelada por usuario o sistema
    'rechazada'                      -- Rechazada por profesional
) DEFAULT 'pendiente';

-- 3. Modificar tabla pagos: agregar campos para comprobante y confirmación
-- Verificar y agregar columnas una por una (MySQL no soporta IF NOT EXISTS en ALTER COLUMN)
SET @exist_comprobante_imagen := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'especialistas_casa' 
    AND TABLE_NAME = 'pagos' 
    AND COLUMN_NAME = 'comprobante_imagen'
);

SET @sql_comprobante := IF(@exist_comprobante_imagen = 0,
    'ALTER TABLE `pagos` ADD COLUMN `comprobante_imagen` VARCHAR(255) NULL COMMENT "Imagen del comprobante subido por usuario" AFTER `comprobante`',
    'SELECT "comprobante_imagen ya existe" AS info'
);
PREPARE stmt FROM @sql_comprobante;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist_numero_referencia := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'especialistas_casa' 
    AND TABLE_NAME = 'pagos' 
    AND COLUMN_NAME = 'numero_referencia'
);

SET @sql_numero_referencia := IF(@exist_numero_referencia = 0,
    'ALTER TABLE `pagos` ADD COLUMN `numero_referencia` VARCHAR(100) NULL COMMENT "Número de referencia de la transacción" AFTER `comprobante_imagen`',
    'SELECT "numero_referencia ya existe" AS info'
);
PREPARE stmt FROM @sql_numero_referencia;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist_observaciones_admin := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'especialistas_casa' 
    AND TABLE_NAME = 'pagos' 
    AND COLUMN_NAME = 'observaciones_admin'
);

SET @sql_observaciones := IF(@exist_observaciones_admin = 0,
    'ALTER TABLE `pagos` ADD COLUMN `observaciones_admin` TEXT NULL COMMENT "Observaciones del admin al aprobar/rechazar" AFTER `notas`',
    'SELECT "observaciones_admin ya existe" AS info'
);
PREPARE stmt FROM @sql_observaciones;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist_fecha_rechazo := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'especialistas_casa' 
    AND TABLE_NAME = 'pagos' 
    AND COLUMN_NAME = 'fecha_rechazo'
);

SET @sql_fecha_rechazo := IF(@exist_fecha_rechazo = 0,
    'ALTER TABLE `pagos` ADD COLUMN `fecha_rechazo` TIMESTAMP NULL AFTER `fecha_aprobacion`',
    'SELECT "fecha_rechazo ya existe" AS info'
);
PREPARE stmt FROM @sql_fecha_rechazo;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist_motivo_rechazo := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'especialistas_casa' 
    AND TABLE_NAME = 'pagos' 
    AND COLUMN_NAME = 'motivo_rechazo'
);

SET @sql_motivo_rechazo := IF(@exist_motivo_rechazo = 0,
    'ALTER TABLE `pagos` ADD COLUMN `motivo_rechazo` TEXT NULL AFTER `fecha_rechazo`',
    'SELECT "motivo_rechazo ya existe" AS info'
);
PREPARE stmt FROM @sql_motivo_rechazo;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Modificar estados de pago para incluir más detalle
ALTER TABLE `pagos`
MODIFY COLUMN `estado` ENUM(
    'pendiente',           -- Pago creado, esperando comprobante
    'comprobante_subido',  -- Usuario subió comprobante, esperando revisión admin
    'aprobado',            -- Admin aprobó el pago
    'rechazado',           -- Admin rechazó el pago
    'reembolsado'          -- Pago reembolsado
) DEFAULT 'pendiente';

-- 4. Agregar índices para optimizar consultas (ignorar si ya existen)
SET @exist_idx1 := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = 'especialistas_casa' AND TABLE_NAME = 'solicitudes' AND INDEX_NAME = 'idx_solicitudes_estado');
SET @sql_idx1 := IF(@exist_idx1 = 0, 'CREATE INDEX `idx_solicitudes_estado` ON `solicitudes`(`estado`, `fecha_solicitud`)', 'SELECT "idx_solicitudes_estado ya existe" AS info');
PREPARE stmt FROM @sql_idx1; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist_idx2 := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = 'especialistas_casa' AND TABLE_NAME = 'pagos' AND INDEX_NAME = 'idx_pagos_estado_metodo');
SET @sql_idx2 := IF(@exist_idx2 = 0, 'CREATE INDEX `idx_pagos_estado_metodo` ON `pagos`(`estado`, `metodo_pago`, `fecha_pago`)', 'SELECT "idx_pagos_estado_metodo ya existe" AS info');
PREPARE stmt FROM @sql_idx2; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5. Crear tabla de historial de cambios de estado (auditoría)
CREATE TABLE IF NOT EXISTS `solicitud_estado_historial` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `solicitud_id` INT UNSIGNED NOT NULL,
    `estado_anterior` VARCHAR(50) NULL,
    `estado_nuevo` VARCHAR(50) NOT NULL,
    `cambiado_por` INT UNSIGNED NULL COMMENT 'Usuario que cambió el estado',
    `motivo` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`cambiado_por`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
    INDEX `idx_solicitud` (`solicitud_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Crear vista para facilitar consultas de pagos pendientes
DROP VIEW IF EXISTS `v_pagos_pendientes_confirmacion`;

CREATE VIEW `v_pagos_pendientes_confirmacion` AS
SELECT 
    p.id as pago_id,
    p.solicitud_id,
    p.monto,
    p.comprobante_imagen,
    p.numero_referencia,
    p.fecha_pago,
    p.estado as estado_pago,
    s.estado as estado_solicitud,
    s.fecha_programada,
    srv.nombre as servicio_nombre,
    srv.tipo as servicio_tipo,
    u.id as paciente_id,
    u.nombre as paciente_nombre,
    u.email as paciente_email,
    u.telefono as paciente_telefono
FROM pagos p
INNER JOIN solicitudes s ON p.solicitud_id = s.id
INNER JOIN usuarios u ON s.paciente_id = u.id
INNER JOIN servicios srv ON s.servicio_id = srv.id
WHERE p.metodo_pago = 'transferencia' 
  AND p.estado IN ('pendiente', 'comprobante_subido')
  AND s.estado = 'pendiente_confirmacion_pago'
ORDER BY p.fecha_pago DESC;

-- 7. Trigger para registrar cambios de estado automáticamente
DROP TRIGGER IF EXISTS `after_solicitud_estado_change`;

DELIMITER $$

CREATE TRIGGER `after_solicitud_estado_change`
AFTER UPDATE ON `solicitudes`
FOR EACH ROW
BEGIN
    IF OLD.estado != NEW.estado THEN
        INSERT INTO solicitud_estado_historial (solicitud_id, estado_anterior, estado_nuevo, cambiado_por)
        VALUES (NEW.id, OLD.estado, NEW.estado, NULL);
    END IF;
END$$

DELIMITER ;

SELECT 'Migración completada: Sistema de confirmación de pagos' AS status;
