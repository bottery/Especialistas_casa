<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Solicitud;
use App\Middleware\AuthMiddleware;
use App\Services\Database;

/**
 * Controlador de Profesional
 */
class ProfesionalController extends BaseController
{
    private $solicitudModel;
    private $authMiddleware;
    private $user;
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->solicitudModel = new Solicitud();
        $this->authMiddleware = new AuthMiddleware();
        
        // Verificar autenticación para rol 'profesional' (incluye todos los especialistas)
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
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(CASE WHEN estado = 'asignado' THEN 1 END) as solicitudesPendientes,
                    COUNT(CASE WHEN estado = 'en_proceso' THEN 1 END) as solicitudesEnProgreso,
                    COUNT(CASE WHEN estado = 'completado' THEN 1 END) as solicitudesCompletadas
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
            $stmt = $this->db->prepare("
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
                        WHEN 'asignado' THEN 1
                        WHEN 'en_proceso' THEN 2
                        WHEN 'completado' THEN 3
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
            // Verificar que la solicitud existe y pertenece al profesional
            $stmt = $this->db->prepare("
                SELECT * FROM solicitudes 
                WHERE id = :id AND profesional_id = :profesional_id AND estado = 'asignado'
            ");
            $stmt->execute([
                'id' => $solicitudId,
                'profesional_id' => $this->user->id
            ]);
            
            $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                $this->sendError("Solicitud no encontrada o no está asignada a ti", 404);
                return;
            }

            // Verificar que el pago esté confirmado
            if (!$solicitud['pagado']) {
                $this->sendError("Esta solicitud requiere confirmación de pago antes de ser aceptada", 400);
                return;
            }
            
            // Actualizar estado a en_proceso (el profesional acepta e inicia el servicio)
            $stmt = $this->db->prepare("
                UPDATE solicitudes 
                SET estado = 'en_proceso',
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
            
            // Verificar que la solicitud existe y pertenece al profesional
            $stmt = $this->db->prepare("
                SELECT * FROM solicitudes 
                WHERE id = :id AND profesional_id = :profesional_id AND estado = 'pendiente'
            ");
            $stmt->execute([
                'id' => $solicitudId,
                'profesional_id' => $this->user->id
            ]);
            
            if (!$stmt->fetch()) {
                $this->sendError("Solicitud no encontrada o no está asignada", 404);
                return;
            }
            
            // Actualizar estado
            $stmt = $this->db->prepare("
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
     * Completar un servicio
     */
    public function completarServicio(int $solicitudId): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $reporte = $data['reporte'] ?? '';
            $diagnostico = $data['diagnostico'] ?? '';
            $notas = $data['notas'] ?? '';
            
            // Verificar que la solicitud existe, pertenece al profesional y está en proceso
            $stmt = $this->db->prepare("
                SELECT * FROM solicitudes 
                WHERE id = :id AND profesional_id = :profesional_id AND estado = 'en_proceso'
            ");
            $stmt->execute([
                'id' => $solicitudId,
                'profesional_id' => $this->user->id
            ]);
            
            $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                $this->sendError("Solicitud no encontrada o no está en proceso", 404);
                return;
            }
            
            // Actualizar profesional: incrementar servicios completados
            $stmtProf = $this->db->prepare("
                UPDATE usuarios 
                SET servicios_completados = servicios_completados + 1
                WHERE id = :id
            ");
            $stmtProf->execute(['id' => $this->user->id]);
            
            // Actualizar estado a completado con reporte profesional
            $stmt = $this->db->prepare("
                UPDATE solicitudes 
                SET estado = 'completado',
                    reporte_profesional = :reporte,
                    diagnostico = :diagnostico,
                    resultado = :notas,
                    fecha_completada = CURRENT_TIMESTAMP,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $solicitudId,
                'reporte' => $reporte,
                'diagnostico' => $diagnostico,
                'notas' => $notas
            ]);
            
            $this->sendSuccess([
                'message' => 'Servicio completado exitosamente. El paciente ahora puede calificarte.',
                'solicitud_id' => $solicitudId
            ]);
        } catch (\Exception $e) {
            error_log("Error al completar servicio: " . $e->getMessage());
            $this->sendError("Error al procesar la solicitud", 500);
        }
    }

    /**
     * Calificar al paciente después de completar el servicio
     */
    public function calificarPaciente(int $solicitudId): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $calificacion = $data['calificacion'] ?? null;
            $comentario = $data['comentario'] ?? '';
            
            // Validar calificación
            if (!$calificacion || $calificacion < 1 || $calificacion > 5) {
                $this->sendError("La calificación debe estar entre 1 y 5", 400);
                return;
            }
            
            // Verificar que la solicitud existe, pertenece al profesional y está completada
            $stmt = $this->db->prepare("
                SELECT s.*, u.id as paciente_id, u.nombre, u.apellido
                FROM solicitudes s
                INNER JOIN usuarios u ON s.paciente_id = u.id
                WHERE s.id = :id 
                    AND s.profesional_id = :profesional_id 
                    AND s.estado = 'completado'
                    AND s.calificacion_profesional IS NULL
            ");
            
            $stmt->execute([
                'id' => $solicitudId,
                'profesional_id' => $this->user->id
            ]);
            
            $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                $this->sendError("Solicitud no encontrada o ya calificaste a este paciente", 404);
                return;
            }

            // Iniciar transacción
            $this->db->beginTransaction();

            try {
                // Guardar calificación del profesional al paciente
                $stmt = $this->db->prepare("
                    UPDATE solicitudes 
                    SET calificacion_profesional = :calificacion,
                        comentario_profesional = :comentario,
                        fecha_calificacion_profesional = CURRENT_TIMESTAMP,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    'id' => $solicitudId,
                    'calificacion' => $calificacion,
                    'comentario' => $comentario
                ]);

                // Recalcular puntuación promedio del paciente
                $stmt = $this->db->prepare("
                    SELECT 
                        AVG(calificacion_profesional) as promedio,
                        COUNT(*) as total
                    FROM solicitudes 
                    WHERE paciente_id = :paciente_id 
                        AND calificacion_profesional IS NOT NULL
                ");
                
                $stmt->execute(['paciente_id' => $solicitud['paciente_id']]);
                $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                // Actualizar estadísticas del paciente
                $stmt = $this->db->prepare("
                    UPDATE usuarios 
                    SET puntuacion_promedio_paciente = :promedio,
                        total_calificaciones_paciente = :total
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    'id' => $solicitud['paciente_id'],
                    'promedio' => round($stats['promedio'], 2),
                    'total' => $stats['total']
                ]);

                $this->db->commit();

                $this->sendSuccess([
                    'message' => '✅ Gracias por tu evaluación del paciente',
                    'solicitud_id' => $solicitudId,
                    'puntuacion_paciente' => round($stats['promedio'], 2)
                ]);
            } catch (\Exception $e) {
                $this->db->rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            error_log("Error al calificar paciente: " . $e->getMessage());
            $this->sendError("Error al procesar la calificación", 500);
        }
    }

    /**
     * Obtener servicios pendientes de calificar al paciente
     */
    public function getServiciosPendientesCalificarPaciente(): void
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    s.id,
                    s.fecha_completada,
                    s.servicio_id,
                    srv.nombre as servicio_nombre,
                    u.nombre as paciente_nombre,
                    u.apellido as paciente_apellido,
                    u.puntuacion_promedio_paciente,
                    u.total_calificaciones_paciente
                FROM solicitudes s
                INNER JOIN servicios srv ON s.servicio_id = srv.id
                INNER JOIN usuarios u ON s.paciente_id = u.id
                WHERE s.profesional_id = :profesional_id
                    AND s.estado = 'completado'
                    AND s.calificacion_profesional IS NULL
                ORDER BY s.fecha_completada DESC
            ");
            
            $stmt->execute(['profesional_id' => $this->user->id]);
            $pendientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->sendSuccess([
                'pendientes' => $pendientes,
                'total' => count($pendientes)
            ]);
        } catch (\Exception $e) {
            error_log("Error al obtener servicios pendientes: " . $e->getMessage());
            $this->sendError("Error al cargar datos", 500);
        }
    }
}
