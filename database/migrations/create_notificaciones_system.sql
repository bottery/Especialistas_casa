-- ============================================
-- MIGRACIÓN: Sistema de Notificaciones y Tiempo Estimado
-- Fecha: 2025-11-17
-- ============================================

-- 1. Crear tabla de notificaciones
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    tipo ENUM('solicitud_creada', 'profesional_asignado', 'profesional_en_camino', 'servicio_iniciado', 'servicio_completado', 'pago_confirmado', 'pago_rechazado', 'calificacion_recibida', 'recordatorio', 'sistema') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    solicitud_id INT UNSIGNED NULL,
    datos_adicionales JSON NULL COMMENT 'Datos extras como tiempo estimado, ubicación, etc.',
    leida BOOLEAN DEFAULT FALSE,
    fecha_leida TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_leida (leida),
    INDEX idx_tipo (tipo),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Crear tabla de plantillas de notificaciones
CREATE TABLE IF NOT EXISTS plantillas_notificaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    tipo ENUM('solicitud_creada', 'profesional_asignado', 'profesional_en_camino', 'servicio_iniciado', 'servicio_completado', 'pago_confirmado', 'pago_rechazado', 'calificacion_recibida', 'recordatorio', 'sistema') NOT NULL,
    titulo_paciente VARCHAR(255),
    mensaje_paciente TEXT,
    titulo_profesional VARCHAR(255),
    mensaje_profesional TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Agregar columnas de seguimiento de tiempo a solicitudes
ALTER TABLE solicitudes 
ADD COLUMN hora_asignacion TIMESTAMP NULL COMMENT 'Cuando se asignó el profesional',
ADD COLUMN hora_aceptacion TIMESTAMP NULL COMMENT 'Cuando el profesional aceptó',
ADD COLUMN hora_salida TIMESTAMP NULL COMMENT 'Cuando el profesional salió hacia el domicilio',
ADD COLUMN hora_llegada TIMESTAMP NULL COMMENT 'Cuando llegó al domicilio',
ADD COLUMN hora_inicio_servicio TIMESTAMP NULL COMMENT 'Cuando inició el servicio',
ADD COLUMN tiempo_estimado_llegada INT NULL COMMENT 'Minutos estimados de llegada',
ADD COLUMN ultima_ubicacion_profesional POINT NULL COMMENT 'Última ubicación GPS del profesional';

-- 4. Agregar preferencias de notificación a usuarios
ALTER TABLE usuarios 
ADD COLUMN notificaciones_push BOOLEAN DEFAULT TRUE,
ADD COLUMN notificaciones_email BOOLEAN DEFAULT TRUE,
ADD COLUMN notificaciones_sms BOOLEAN DEFAULT FALSE,
ADD COLUMN token_dispositivo VARCHAR(255) NULL COMMENT 'Token para notificaciones push';

-- 5. Insertar plantillas de notificaciones predeterminadas
INSERT INTO plantillas_notificaciones (codigo, tipo, titulo_paciente, mensaje_paciente, titulo_profesional, mensaje_profesional) VALUES
('solicitud_creada', 'solicitud_creada', 
 '✅ Solicitud recibida', 
 'Hemos recibido tu solicitud de {{servicio}}. Estamos buscando el mejor profesional para ti.',
 '🔔 Nueva solicitud', 
 'Nueva solicitud de {{servicio}} en {{direccion}}'),

('profesional_asignado', 'profesional_asignado',
 '👨‍⚕️ Profesional asignado',
 '{{profesional}} ha sido asignado a tu solicitud. Te contactará pronto.',
 '✅ Solicitud asignada',
 'Se te ha asignado una nueva solicitud de {{servicio}}'),

('profesional_en_camino', 'profesional_en_camino',
 '🚗 En camino',
 '{{profesional}} está en camino. Tiempo estimado de llegada: {{tiempo}} minutos',
 NULL,
 NULL),

('servicio_iniciado', 'servicio_iniciado',
 '▶️ Servicio iniciado',
 '{{profesional}} ha iniciado el servicio de {{servicio}}',
 NULL,
 NULL),

('servicio_completado', 'servicio_completado',
 '✅ Servicio completado',
 'El servicio ha sido completado. Por favor califica tu experiencia.',
 '✅ Servicio marcado como completado',
 'Has marcado el servicio como completado. Esperando calificación del paciente.'),

('pago_confirmado', 'pago_confirmado',
 '✅ Pago confirmado',
 'Tu pago ha sido confirmado. Total: ${{monto}}',
 '💰 Pago confirmado',
 'El pago de ${{monto}} ha sido confirmado para tu servicio'),

('pago_rechazado', 'pago_rechazado',
 '❌ Pago rechazado',
 'Tu comprobante de pago fue rechazado. Motivo: {{motivo}}. Por favor sube un nuevo comprobante.',
 NULL,
 NULL),

('recordatorio', 'recordatorio',
 '⏰ Recordatorio',
 'Tienes un servicio programado para {{fecha}} a las {{hora}}',
 '⏰ Recordatorio',
 'Tienes un servicio programado para {{fecha}} a las {{hora}}');

-- 6. Crear tabla de configuración de tiempos estimados
CREATE TABLE IF NOT EXISTS configuracion_tiempos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_profesional ENUM('medico', 'enfermera', 'veterinario', 'laboratorio', 'ambulancia') NOT NULL,
    tiempo_preparacion_min INT DEFAULT 15 COMMENT 'Minutos para prepararse',
    velocidad_desplazamiento_km_h DECIMAL(5,2) DEFAULT 40.00 COMMENT 'Velocidad promedio en km/h',
    tiempo_buffer_min INT DEFAULT 10 COMMENT 'Tiempo extra de seguridad',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tipo (tipo_profesional)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Insertar configuración por defecto de tiempos
INSERT INTO configuracion_tiempos (tipo_profesional, tiempo_preparacion_min, velocidad_desplazamiento_km_h, tiempo_buffer_min) VALUES
('medico', 20, 40.00, 10),
('enfermera', 15, 45.00, 10),
('veterinario', 20, 40.00, 15),
('laboratorio', 30, 35.00, 15),
('ambulancia', 5, 60.00, 5);

SELECT 'Sistema de notificaciones y tiempos creado exitosamente' as resultado;
