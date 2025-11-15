-- ============================================
-- Actualizar métodos de pago a solo PSE y Transferencia
-- Fecha: 2025-11-14
-- ============================================

USE especialistas_casa;

-- Actualizar columna metodo_pago_preferido en solicitudes
ALTER TABLE solicitudes 
MODIFY COLUMN metodo_pago_preferido ENUM('pse', 'transferencia') DEFAULT 'pse';

-- Actualizar registros existentes que tengan valores antiguos
UPDATE solicitudes 
SET metodo_pago_preferido = 'transferencia' 
WHERE metodo_pago_preferido IN ('efectivo', 'tarjeta');

SELECT 'Métodos de pago actualizados correctamente' AS mensaje;
