<?php
/**
 * Controlador para gestión de pagos por transferencia
 * Confirmación y rechazo de pagos por admin/superadmin
 */

namespace App\Controllers;

use App\Models\Solicitud;
use App\Middleware\AuthMiddleware;
use App\Services\Database;

class PagosTransferenciaController
{
    private $authMiddleware;
    private $user;
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->authMiddleware = new AuthMiddleware();
    }

    /**
     * Verificar autenticación con roles específicos
     */
    private function requireAuth(array $roles): void
    {
        $this->user = $this->authMiddleware->checkRole($roles);
        if (!$this->user) {
            exit;
        }
    }

    /**
     * Enviar respuesta JSON de éxito
     */
    private function sendSuccess($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
    }

    /**
     * Enviar respuesta JSON de error
     */
    private function sendError(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
    }

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

    /**
     * Obtener solicitudes con estado pendiente_confirmacion_pago
     * GET /api/admin/pagos/pendientes-confirmacion
     */
    public function getSolicitudesPendientesConfirmacion(): void
    {
        try {
            $this->requireAuth(['admin', 'superadmin']);
            
            $stmt = $this->db->query("
                SELECT 
                    s.id,
                    s.servicio_id,
                    s.paciente_id,
                    s.estado,
                    s.monto_total,
                    s.fecha_programada,
                    s.metodo_pago_preferido,
                    srv.nombre as servicio_nombre,
                    srv.tipo as servicio_tipo,
                    u.nombre as paciente_nombre,
                    u.telefono as paciente_telefono,
                    u.email as paciente_email
                FROM solicitudes s
                INNER JOIN servicios srv ON s.servicio_id = srv.id
                INNER JOIN usuarios u ON s.paciente_id = u.id
                WHERE s.estado = 'pendiente_pago'
                ORDER BY s.created_at DESC
            ");
            
            $solicitudes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->sendSuccess([
                'solicitudes' => $solicitudes,
                'total' => count($solicitudes)
            ]);
            
        } catch (\Exception $e) {
            error_log("Error obteniendo solicitudes pendientes de confirmación: " . $e->getMessage());
            $this->sendError("Error al obtener solicitudes", 500);
        }
    }

    /**
     * Aprobar pago de una solicitud
     * POST /api/admin/solicitudes/{id}/aprobar-pago
     */
    public function aprobarPago(int $solicitudId): void
    {
        try {
            $this->requireAuth(['admin', 'superadmin']);
            
            // Verificar que la solicitud existe y está pendiente de confirmación
            $stmt = $this->db->prepare("
                SELECT id, estado, paciente_id, monto_total 
                FROM solicitudes 
                WHERE id = ? AND estado = 'pendiente_pago'
            ");
            $stmt->execute([$solicitudId]);
            $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                $this->sendError("Solicitud no encontrada o no está pendiente de confirmación", 404);
                return;
            }
            
            // Actualizar estado de la solicitud a 'pagado' (lista para asignar)
            $stmt = $this->db->prepare("
                UPDATE solicitudes 
                SET estado = 'pagado', 
                    pagado = 1,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$solicitudId]);

            // Marcar el/los pagos asociados como 'aprobado' (el admin confirma que el dinero está en la cuenta)
            try {
                $adminId = $this->user->id ?? null;
                $updatePago = $this->db->prepare("UPDATE pagos SET estado = 'aprobado', aprobado_por = ?, fecha_aprobacion = NOW() WHERE solicitud_id = ?");
                $updatePago->execute([$adminId, $solicitudId]);
                error_log("[PagosTransferencia] Pago(s) para solicitud {$solicitudId} marcados como aprobado por " . ($adminId ?? 'unknown'));
            } catch (\Exception $e) {
                error_log("[PagosTransferencia] Error marcando pagos como aprobados: " . $e->getMessage());
            }
            
            // Intentar crear notificación (opcional, no falla si hay error)
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO notificaciones 
                    (usuario_id, tipo, titulo, mensaje, created_at)
                    VALUES (?, 'sistema', 'Pago Aprobado', 
                            'Tu pago ha sido aprobado. Pronto asignaremos un profesional a tu solicitud.',
                            NOW())
                ");
                $stmt->execute([$solicitud['paciente_id']]);
            } catch (\Exception $e) {
                // Ignorar error de notificación
                error_log("No se pudo crear notificación: " . $e->getMessage());
            }
            
            $this->sendSuccess([
                'message' => 'Pago aprobado exitosamente. La solicitud está lista para asignar profesional.'
            ]);
            
        } catch (\Exception $e) {
            error_log("Error al aprobar pago: " . $e->getMessage());
            $this->sendError("Error al aprobar pago", 500);
        }
    }

    /**
     * Rechazar pago de una solicitud
     * POST /api/admin/solicitudes/{id}/rechazar-pago
     */
    public function rechazarPago(int $solicitudId): void
    {
        try {
            $this->requireAuth(['admin', 'superadmin']);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $motivo = $data['motivo'] ?? 'No especificado';
            
            // Verificar que la solicitud existe y está pendiente de confirmación
            $stmt = $this->db->prepare("
                SELECT id, estado, paciente_id 
                FROM solicitudes 
                WHERE id = ? AND estado = 'pendiente_confirmacion_pago'
            ");
            $stmt->execute([$solicitudId]);
            $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                $this->sendError("Solicitud no encontrada o no está pendiente de confirmación", 404);
                return;
            }
            
            // Iniciar transacción
            $this->db->beginTransaction();
            
            try {
                // Actualizar estado de la solicitud a 'cancelada'
                $stmt = $this->db->prepare("
                    UPDATE solicitudes 
                    SET estado = 'cancelada',
                        cancelado_por = ?,
                        razon_cancelacion = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$this->user->id, 'Pago rechazado: ' . $motivo, $solicitudId]);
                
                // Registrar en historial
                $stmt = $this->db->prepare("
                    INSERT INTO solicitud_estado_historial 
                    (solicitud_id, estado_anterior, estado_nuevo, cambiado_por, motivo, created_at)
                    VALUES (?, 'pendiente_confirmacion_pago', 'cancelada', ?, ?, NOW())
                ");
                $stmt->execute([$solicitudId, $this->user->id, 'Pago rechazado: ' . $motivo]);
                
                // Crear notificación para el paciente
                $mensajeNotif = 'Tu pago ha sido rechazado. Motivo: ' . $motivo . '. Por favor contacta con soporte.';
                $stmt = $this->db->prepare("
                    INSERT INTO notificaciones 
                    (usuario_id, tipo, titulo, mensaje, created_at)
                    VALUES (?, 'sistema', 'Pago Rechazado', ?, NOW())
                ");
                $stmt->execute([$solicitud['paciente_id'], $mensajeNotif]);
                
                $this->db->commit();
                
                $this->sendSuccess([
                    'message' => 'Pago rechazado. Se ha notificado al paciente.'
                ]);
                
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            error_log("Error al rechazar pago: " . $e->getMessage());
            $this->sendError("Error al rechazar pago", 500);
        }
    }
}
