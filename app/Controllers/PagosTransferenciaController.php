<?php
/**
 * Controlador para gestión de pagos por transferencia
 * Confirmación y rechazo de pagos por admin/superadmin
 */

namespace App\Controllers;

use App\Models\Solicitud;

class PagosTransferenciaController extends BaseController
{
    /**
     * Listar pagos pendientes de confirmación
     * GET /api/admin/pagos/pendientes
     */
    public function getPagosPendientes(): void
    {
        try {
            $this->requireAuth(['admin', 'superadmin']);
            
            global $pdo;
            
            // Usar la vista creada en la migración
            $stmt = $pdo->query("
                SELECT * FROM v_pagos_pendientes_confirmacion
                ORDER BY fecha_pago DESC
            ");
            
            $pagos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->sendSuccess([
                'pagos' => $pagos,
                'total' => count($pagos)
            ]);
            
        } catch (\Exception $e) {
            error_log("Error obteniendo pagos pendientes: " . $e->getMessage());
            $this->sendError("Error al obtener pagos pendientes", 500);
        }
    }
    
    /**
     * Obtener detalle de un pago específico
     * GET /api/admin/pagos/{id}
     */
    public function getDetallePago(int $pagoId): void
    {
        try {
            $this->requireAuth(['admin', 'superadmin']);
            
            global $pdo;
            
            $stmt = $pdo->prepare("
                SELECT 
                    p.*,
                    s.id as solicitud_id,
                    s.estado as solicitud_estado,
                    s.fecha_programada,
                    s.modalidad,
                    s.direccion_servicio,
                    s.observaciones,
                    srv.nombre as servicio_nombre,
                    srv.tipo as servicio_tipo,
                    srv.precio_base,
                    u.id as paciente_id,
                    u.nombre as paciente_nombre,
                    u.email as paciente_email,
                    u.telefono as paciente_telefono,
                    u.documento as paciente_documento
                FROM pagos p
                INNER JOIN solicitudes s ON p.solicitud_id = s.id
                INNER JOIN usuarios u ON s.paciente_id = u.id
                INNER JOIN servicios srv ON s.servicio_id = srv.id
                WHERE p.id = :pago_id
            ");
            
            $stmt->execute(['pago_id' => $pagoId]);
            $pago = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$pago) {
                $this->sendError("Pago no encontrado", 404);
                return;
            }
            
            $this->sendSuccess($pago);
            
        } catch (\Exception $e) {
            error_log("Error obteniendo detalle de pago: " . $e->getMessage());
            $this->sendError("Error al obtener detalle del pago", 500);
        }
    }
    
    /**
     * Aprobar pago por transferencia
     * POST /api/admin/pagos/{id}/aprobar
     */
    public function aprobarPago(int $pagoId): void
    {
        try {
            $this->requireAuth(['admin', 'superadmin']);
            
            $data = $this->getRequestData();
            $observaciones = $data['observaciones'] ?? null;
            
            global $pdo;
            $userId = $_SESSION['user_id'];
            
            $pdo->beginTransaction();
            
            // Verificar que el pago existe y está pendiente
            $stmt = $pdo->prepare("
                SELECT p.*, s.id as solicitud_id, s.paciente_id
                FROM pagos p
                INNER JOIN solicitudes s ON p.solicitud_id = s.id
                WHERE p.id = :pago_id 
                AND p.metodo_pago = 'transferencia'
                AND p.estado IN ('pendiente', 'comprobante_subido')
            ");
            $stmt->execute(['pago_id' => $pagoId]);
            $pago = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$pago) {
                $pdo->rollBack();
                $this->sendError("Pago no encontrado o no está pendiente de aprobación", 404);
                return;
            }
            
            // Actualizar estado del pago a aprobado
            $stmt = $pdo->prepare("
                UPDATE pagos 
                SET estado = 'aprobado',
                    aprobado_por = :admin_id,
                    fecha_aprobacion = NOW(),
                    observaciones_admin = :observaciones
                WHERE id = :pago_id
            ");
            $stmt->execute([
                'pago_id' => $pagoId,
                'admin_id' => $userId,
                'observaciones' => $observaciones
            ]);
            
            // Actualizar solicitud: de 'pendiente_confirmacion_pago' a 'pendiente' (listo para asignar)
            $stmt = $pdo->prepare("
                UPDATE solicitudes 
                SET estado = 'pendiente',
                    pagado = TRUE,
                    pago_id = :pago_id
                WHERE id = :solicitud_id
            ");
            $stmt->execute([
                'solicitud_id' => $pago['solicitud_id'],
                'pago_id' => $pagoId
            ]);
            
            // Registrar en historial de estados
            $stmt = $pdo->prepare("
                INSERT INTO solicitud_estado_historial 
                (solicitud_id, estado_anterior, estado_nuevo, cambiado_por, motivo)
                VALUES (:solicitud_id, 'pendiente_confirmacion_pago', 'pendiente', :user_id, :motivo)
            ");
            $stmt->execute([
                'solicitud_id' => $pago['solicitud_id'],
                'user_id' => $userId,
                'motivo' => 'Pago por transferencia aprobado por administrador'
            ]);
            
            // Crear notificación para el paciente
            $stmt = $pdo->prepare("
                INSERT INTO notificaciones 
                (user_id, from_user_id, type, title, message, action_url, created_at)
                VALUES 
                (:user_id, :admin_id, 'pago_aprobado', 
                 'Pago confirmado', 
                 'Tu pago por transferencia ha sido confirmado. Tu solicitud está siendo procesada.',
                 '/mis-solicitudes',
                 NOW())
            ");
            $stmt->execute([
                'user_id' => $pago['paciente_id'],
                'admin_id' => $userId
            ]);
            
            $pdo->commit();
            
            $this->sendSuccess([
                'message' => 'Pago aprobado exitosamente. La solicitud está lista para asignar profesional.',
                'pago_id' => $pagoId,
                'solicitud_id' => $pago['solicitud_id'],
                'nuevo_estado' => 'pendiente'
            ]);
            
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error aprobando pago: " . $e->getMessage());
            $this->sendError("Error al aprobar pago: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Rechazar pago por transferencia
     * POST /api/admin/pagos/{id}/rechazar
     */
    public function rechazarPago(int $pagoId): void
    {
        try {
            $this->requireAuth(['admin', 'superadmin']);
            
            $data = $this->getRequestData();
            
            if (empty($data['motivo'])) {
                $this->sendError("El motivo del rechazo es requerido", 400);
                return;
            }
            
            global $pdo;
            $userId = $_SESSION['user_id'];
            
            $pdo->beginTransaction();
            
            // Verificar que el pago existe y está pendiente
            $stmt = $pdo->prepare("
                SELECT p.*, s.id as solicitud_id, s.paciente_id
                FROM pagos p
                INNER JOIN solicitudes s ON p.solicitud_id = s.id
                WHERE p.id = :pago_id 
                AND p.metodo_pago = 'transferencia'
                AND p.estado IN ('pendiente', 'comprobante_subido')
            ");
            $stmt->execute(['pago_id' => $pagoId]);
            $pago = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$pago) {
                $pdo->rollBack();
                $this->sendError("Pago no encontrado o no está pendiente de revisión", 404);
                return;
            }
            
            // Actualizar estado del pago a rechazado
            $stmt = $pdo->prepare("
                UPDATE pagos 
                SET estado = 'rechazado',
                    aprobado_por = :admin_id,
                    fecha_rechazo = NOW(),
                    motivo_rechazo = :motivo,
                    observaciones_admin = :observaciones
                WHERE id = :pago_id
            ");
            $stmt->execute([
                'pago_id' => $pagoId,
                'admin_id' => $userId,
                'motivo' => $data['motivo'],
                'observaciones' => $data['observaciones'] ?? null
            ]);
            
            // Actualizar solicitud a cancelada
            $stmt = $pdo->prepare("
                UPDATE solicitudes 
                SET estado = 'cancelada',
                    cancelado_por = :admin_id,
                    razon_cancelacion = :razon
                WHERE id = :solicitud_id
            ");
            $stmt->execute([
                'solicitud_id' => $pago['solicitud_id'],
                'admin_id' => $userId,
                'razon' => 'Pago rechazado: ' . $data['motivo']
            ]);
            
            // Registrar en historial
            $stmt = $pdo->prepare("
                INSERT INTO solicitud_estado_historial 
                (solicitud_id, estado_anterior, estado_nuevo, cambiado_por, motivo)
                VALUES (:solicitud_id, 'pendiente_confirmacion_pago', 'cancelada', :user_id, :motivo)
            ");
            $stmt->execute([
                'solicitud_id' => $pago['solicitud_id'],
                'user_id' => $userId,
                'motivo' => 'Pago rechazado: ' . $data['motivo']
            ]);
            
            // Notificar al paciente
            $stmt = $pdo->prepare("
                INSERT INTO notificaciones 
                (user_id, from_user_id, type, title, message, action_url, created_at)
                VALUES 
                (:user_id, :admin_id, 'pago_rechazado', 
                 'Pago no confirmado', 
                 :mensaje,
                 '/mis-solicitudes',
                 NOW())
            ");
            $stmt->execute([
                'user_id' => $pago['paciente_id'],
                'admin_id' => $userId,
                'mensaje' => 'Tu pago no pudo ser confirmado. Motivo: ' . $data['motivo'] . '. Por favor contacta con soporte.'
            ]);
            
            $pdo->commit();
            
            $this->sendSuccess([
                'message' => 'Pago rechazado. Se ha notificado al paciente.',
                'pago_id' => $pagoId
            ]);
            
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error rechazando pago: " . $e->getMessage());
            $this->sendError("Error al rechazar pago: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Subir comprobante de pago (usuario paciente)
     * POST /api/pagos/{id}/comprobante
     */
    public function uploadComprobante(int $pagoId): void
    {
        try {
            $this->requireAuth(['paciente']);
            
            global $pdo;
            $userId = $_SESSION['user_id'];
            
            // Verificar que el pago pertenece al usuario
            $stmt = $pdo->prepare("
                SELECT p.*, s.paciente_id 
                FROM pagos p
                INNER JOIN solicitudes s ON p.solicitud_id = s.id
                WHERE p.id = :pago_id AND s.paciente_id = :user_id
            ");
            $stmt->execute(['pago_id' => $pagoId, 'user_id' => $userId]);
            $pago = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$pago) {
                $this->sendError("Pago no encontrado o no autorizado", 404);
                return;
            }
            
            if ($pago['estado'] !== 'pendiente') {
                $this->sendError("Este pago ya ha sido procesado", 400);
                return;
            }
            
            if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
                $this->sendError("No se recibió comprobante válido", 400);
                return;
            }
            
            $file = $_FILES['comprobante'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
            $maxSize = 10 * 1024 * 1024; // 10MB
            
            // Validar tipo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $this->sendError("Tipo de archivo no permitido. Solo JPG, PNG o PDF", 400);
                return;
            }
            
            // Validar tamaño
            if ($file['size'] > $maxSize) {
                $this->sendError("El archivo es demasiado grande. Máximo 10MB", 400);
                return;
            }
            
            // Crear directorio
            $uploadDir = __DIR__ . '/../../public/uploads/comprobantes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'comprobante_' . $pagoId . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                $this->sendError("Error al guardar el comprobante", 500);
                return;
            }
            
            // Actualizar BD
            $relativePath = '/uploads/comprobantes/' . $filename;
            $numeroReferencia = $_POST['numero_referencia'] ?? null;
            
            $stmt = $pdo->prepare("
                UPDATE pagos 
                SET comprobante_imagen = :comprobante,
                    numero_referencia = :referencia,
                    estado = 'comprobante_subido'
                WHERE id = :pago_id
            ");
            $stmt->execute([
                'comprobante' => $relativePath,
                'referencia' => $numeroReferencia,
                'pago_id' => $pagoId
            ]);
            
            $this->sendSuccess([
                'message' => 'Comprobante subido exitosamente. El administrador revisará tu pago pronto.',
                'comprobante_path' => $relativePath
            ]);
            
        } catch (\Exception $e) {
            error_log("Error subiendo comprobante: " . $e->getMessage());
            $this->sendError("Error al subir comprobante: " . $e->getMessage(), 500);
        }
    }
}
