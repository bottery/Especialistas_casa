<?php

namespace App\Controllers;

use App\Models\Solicitud;
use App\Middleware\AuthMiddleware;

/**
 * Controlador de Profesional
 */
class ProfesionalController
{
    private $solicitudModel;
    private $authMiddleware;
    private $user;

    public function __construct()
    {
        $this->solicitudModel = new Solicitud();
        $this->authMiddleware = new AuthMiddleware();
        
        // Verificar autenticación
        $this->user = $this->authMiddleware->checkRole(['profesional']);
        if (!$this->user) {
            exit;
        }
    }

    /**
     * Obtener estadísticas del profesional
     */
    public function getStats(): void
    {
        try {
            global $pdo;
            
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(CASE WHEN estado = 'pendiente' AND (pagado = TRUE OR metodo_pago_preferido = 'pse') THEN 1 END) as solicitudesPendientes,
                    COUNT(CASE WHEN estado IN ('confirmada', 'en_progreso') THEN 1 END) as solicitudesEnProgreso,
                    COUNT(CASE WHEN estado = 'completada' THEN 1 END) as solicitudesCompletadas,
                    COALESCE(SUM(CASE WHEN estado = 'completada' AND MONTH(fecha_completada) = MONTH(CURDATE()) AND YEAR(fecha_completada) = YEAR(CURDATE()) THEN monto_profesional END), 0) as ingresosDelMes
                FROM solicitudes
                WHERE profesional_id = :profesional_id
            ");
            
            $stmt->execute(['profesional_id' => $this->user->id]);
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $this->sendSuccess(['stats' => $stats]);
        } catch (\Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            $this->sendError("Error al obtener estadísticas", 500);
        }
    }

    /**
     * Obtener solicitudes del profesional
     */
    public function getSolicitudes(): void
    {
        try {
            global $pdo;
            
            $stmt = $pdo->prepare("
                SELECT 
                    s.*,
                    srv.nombre as servicio_nombre,
                    srv.tipo as servicio_tipo,
                    u.nombre as paciente_nombre,
                    u.apellido as paciente_apellido,
                    u.email as paciente_email,
                    u.telefono as paciente_telefono
                FROM solicitudes s
                INNER JOIN servicios srv ON s.servicio_id = srv.id
                INNER JOIN usuarios u ON s.paciente_id = u.id
                WHERE s.profesional_id = :profesional_id
                ORDER BY 
                    CASE s.estado
                        WHEN 'pendiente' THEN 1
                        WHEN 'confirmada' THEN 2
                        WHEN 'en_progreso' THEN 3
                        ELSE 4
                    END,
                    s.fecha_programada ASC
            ");
            
            $stmt->execute(['profesional_id' => $this->user->id]);
            $solicitudes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Formatear nombres completos
            foreach ($solicitudes as &$solicitud) {
                $solicitud['paciente_nombre'] = trim($solicitud['paciente_nombre'] . ' ' . $solicitud['paciente_apellido']);
            }
            
            $this->sendSuccess(['solicitudes' => $solicitudes]);
        } catch (\Exception $e) {
            error_log("Error al obtener solicitudes: " . $e->getMessage());
            $this->sendError("Error al obtener solicitudes", 500);
        }
    }

    /**
     * Aceptar una solicitud
     */
    public function aceptarSolicitud(int $solicitudId): void
    {
        try {
            global $pdo;
            
            // Verificar que la solicitud existe y pertenece al profesional
            $stmt = $pdo->prepare("
                SELECT * FROM solicitudes 
                WHERE id = :id AND profesional_id = :profesional_id AND estado = 'pendiente'
            ");
            $stmt->execute([
                'id' => $solicitudId,
                'profesional_id' => $this->user->id
            ]);
            
            $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                $this->sendError("Solicitud no encontrada o no está pendiente", 404);
                return;
            }

            // Verificar que el pago esté confirmado (PSE o transferencia aprobada)
            if (!$solicitud['pagado'] && $solicitud['metodo_pago_preferido'] !== 'pse') {
                $this->sendError("Esta solicitud requiere confirmación de pago antes de ser aceptada", 400);
                return;
            }
            
            // Actualizar estado
            $stmt = $pdo->prepare("
                UPDATE solicitudes 
                SET estado = 'confirmada',
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            
            $stmt->execute(['id' => $solicitudId]);
            
            // TODO: Enviar notificación al paciente
            
            $this->sendSuccess([
                'message' => 'Solicitud aceptada exitosamente',
                'solicitud_id' => $solicitudId
            ]);
        } catch (\Exception $e) {
            error_log("Error al aceptar solicitud: " . $e->getMessage());
            $this->sendError("Error al procesar la solicitud", 500);
        }
    }

    /**
     * Rechazar una solicitud
     */
    public function rechazarSolicitud(int $solicitudId): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $motivo = $data['motivo'] ?? 'Sin motivo especificado';
            
            global $pdo;
            
            // Verificar que la solicitud existe y pertenece al profesional
            $stmt = $pdo->prepare("
                SELECT * FROM solicitudes 
                WHERE id = :id AND profesional_id = :profesional_id AND estado = 'pendiente'
            ");
            $stmt->execute([
                'id' => $solicitudId,
                'profesional_id' => $this->user->id
            ]);
            
            if (!$stmt->fetch()) {
                $this->sendError("Solicitud no encontrada o no está pendiente", 404);
                return;
            }
            
            // Actualizar estado
            $stmt = $pdo->prepare("
                UPDATE solicitudes 
                SET estado = 'rechazada',
                    razon_cancelacion = :motivo,
                    cancelado_por = :profesional_id,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $solicitudId,
                'motivo' => $motivo,
                'profesional_id' => $this->user->id
            ]);
            
            // TODO: Enviar notificación al paciente y procesar reembolso si aplica
            
            $this->sendSuccess([
                'message' => 'Solicitud rechazada',
                'solicitud_id' => $solicitudId
            ]);
        } catch (\Exception $e) {
            error_log("Error al rechazar solicitud: " . $e->getMessage());
            $this->sendError("Error al procesar la solicitud", 500);
        }
    }

    /**
     * Iniciar un servicio
     */
    public function iniciarServicio(int $solicitudId): void
    {
        try {
            global $pdo;
            
            // Verificar que la solicitud existe, pertenece al profesional y está confirmada
            $stmt = $pdo->prepare("
                SELECT * FROM solicitudes 
                WHERE id = :id AND profesional_id = :profesional_id AND estado = 'confirmada'
            ");
            $stmt->execute([
                'id' => $solicitudId,
                'profesional_id' => $this->user->id
            ]);
            
            if (!$stmt->fetch()) {
                $this->sendError("Solicitud no encontrada o no está confirmada", 404);
                return;
            }
            
            // Actualizar estado
            $stmt = $pdo->prepare("
                UPDATE solicitudes 
                SET estado = 'en_progreso',
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            
            $stmt->execute(['id' => $solicitudId]);
            
            $this->sendSuccess([
                'message' => 'Servicio iniciado exitosamente',
                'solicitud_id' => $solicitudId
            ]);
        } catch (\Exception $e) {
            error_log("Error al iniciar servicio: " . $e->getMessage());
            $this->sendError("Error al procesar la solicitud", 500);
        }
    }

    /**
     * Completar un servicio
     */
    public function completarServicio(int $solicitudId): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $notas = $data['notas'] ?? '';
            
            global $pdo;
            
            // Verificar que la solicitud existe, pertenece al profesional y está en progreso
            $stmt = $pdo->prepare("
                SELECT * FROM solicitudes 
                WHERE id = :id AND profesional_id = :profesional_id AND estado = 'en_progreso'
            ");
            $stmt->execute([
                'id' => $solicitudId,
                'profesional_id' => $this->user->id
            ]);
            
            $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                $this->sendError("Solicitud no encontrada o no está en progreso", 404);
                return;
            }
            
            // Calcular montos (80% para profesional, 20% para plataforma)
            $montoTotal = $solicitud['monto_total'];
            $montoProfesional = $montoTotal * 0.80;
            $montoPlataforma = $montoTotal * 0.20;
            
            // Actualizar estado a pendiente_calificacion (requiere calificación del paciente)
            $stmt = $pdo->prepare("
                UPDATE solicitudes 
                SET estado = 'pendiente_calificacion',
                    resultado = :notas,
                    monto_profesional = :monto_profesional,
                    monto_plataforma = :monto_plataforma,
                    fecha_completada = CURRENT_TIMESTAMP,
                    calificado = FALSE,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $solicitudId,
                'notas' => $notas,
                'monto_profesional' => $montoProfesional,
                'monto_plataforma' => $montoPlataforma
            ]);
            
            // Notificar al paciente que debe calificar el servicio
            
            $this->sendSuccess([
                'message' => 'Servicio completado. El paciente debe calificar antes de finalizar.',
                'solicitud_id' => $solicitudId,
                'monto_profesional' => $montoProfesional,
                'estado' => 'pendiente_calificacion'
            ]);
        } catch (\Exception $e) {
            error_log("Error al completar servicio: " . $e->getMessage());
            $this->sendError("Error al procesar la solicitud", 500);
        }
    }

    /**
     * Enviar respuesta exitosa
     */
    private function sendSuccess(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
        exit;
    }

    /**
     * Enviar respuesta de error
     */
    private function sendError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}
