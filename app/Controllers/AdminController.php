<?php

namespace App\Controllers;

class AdminController extends BaseController
{
    private $user;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->user = $_SESSION['user'] ?? null;
        
        if (!$this->user || $this->user->rol !== 'admin') {
            header('Location: /login');
            exit;
        }

        global $pdo;
        $this->db = $pdo;
    }

    /**
     * Obtener todas las solicitudes pendientes de asignación
     */
    public function getSolicitudesPendientes(): void
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    s.*,
                    srv.nombre as servicio_nombre,
                    srv.tipo as servicio_tipo,
                    srv.precio_base,
                    p.nombre as paciente_nombre,
                    p.apellido as paciente_apellido,
                    p.email as paciente_email,
                    p.telefono as paciente_telefono,
                    CASE 
                        WHEN s.metodo_pago_preferido = 'pse' THEN 'Confirmado'
                        WHEN s.pagado = TRUE THEN 'Confirmado'
                        ELSE 'Pendiente confirmación'
                    END as estado_pago
                FROM solicitudes s
                INNER JOIN servicios srv ON s.servicio_id = srv.id
                INNER JOIN usuarios p ON s.paciente_id = p.id
                WHERE s.estado = 'pendiente_asignacion'
                ORDER BY s.created_at DESC
            ");
            
            $stmt->execute();
            $solicitudes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->sendSuccess([
                'solicitudes' => $solicitudes
            ]);
        } catch (\Exception $e) {
            error_log("Error al obtener solicitudes pendientes: " . $e->getMessage());
            $this->sendError("Error al cargar solicitudes", 500);
        }
    }

    /**
     * Obtener profesionales disponibles y rankeados para un servicio
     */
    public function getProfesionalesDisponibles(): void
    {
        try {
            $servicioId = $_GET['servicio_id'] ?? null;
            $especialidad = $_GET['especialidad'] ?? null;
            
            if (!$servicioId) {
                $this->sendError("servicio_id es requerido", 400);
                return;
            }

            // Obtener profesionales con ranking por calificación y servicios completados
            $query = "
                SELECT 
                    u.id,
                    u.nombre,
                    u.apellido,
                    u.email,
                    u.telefono,
                    u.puntuacion_promedio,
                    u.total_calificaciones,
                    u.servicios_completados,
                    ps.experiencia_anos,
                    ps.certificaciones,
                    GROUP_CONCAT(DISTINCT srv.nombre SEPARATOR ', ') as servicios
                FROM usuarios u
                INNER JOIN profesional_servicios ps ON u.id = ps.profesional_id
                INNER JOIN servicios srv ON ps.servicio_id = srv.id
                WHERE u.rol = 'profesional' 
                    AND u.activo = TRUE
                    AND ps.servicio_id = :servicio_id
            ";
            
            $params = ['servicio_id' => $servicioId];
            
            // Filtro adicional por especialidad si es proporcionado
            if ($especialidad) {
                $query .= " AND ps.especialidad LIKE :especialidad";
                $params['especialidad'] = "%{$especialidad}%";
            }
            
            $query .= "
                GROUP BY u.id, u.nombre, u.apellido, u.email, u.telefono, 
                         u.puntuacion_promedio, u.total_calificaciones, u.servicios_completados,
                         ps.experiencia_anos, ps.certificaciones
                ORDER BY u.puntuacion_promedio DESC, u.servicios_completados DESC, u.total_calificaciones DESC
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $profesionales = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->sendSuccess([
                'profesionales' => $profesionales,
                'total' => count($profesionales)
            ]);
        } catch (\Exception $e) {
            error_log("Error al obtener profesionales: " . $e->getMessage());
            $this->sendError("Error al cargar profesionales", 500);
        }
    }

    /**
     * Asignar un profesional a una solicitud
     */
    public function asignarProfesional(int $solicitudId): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $profesionalId = $data['profesional_id'] ?? null;
            $motivo = $data['motivo'] ?? '';
            
            if (!$profesionalId) {
                $this->sendError("profesional_id es requerido", 400);
                return;
            }

            // Verificar que la solicitud existe y está pendiente de asignación
            $stmt = $this->db->prepare("
                SELECT * FROM solicitudes 
                WHERE id = :id AND estado = 'pendiente_asignacion'
            ");
            $stmt->execute(['id' => $solicitudId]);
            $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                $this->sendError("Solicitud no encontrada o ya fue asignada", 404);
                return;
            }

            // Verificar que el profesional existe y está activo
            $stmt = $this->db->prepare("
                SELECT id, nombre, apellido, activo 
                FROM usuarios 
                WHERE id = :id AND rol = 'profesional' AND activo = TRUE
            ");
            $stmt->execute(['id' => $profesionalId]);
            $profesional = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$profesional) {
                $this->sendError("Profesional no encontrado o inactivo", 404);
                return;
            }

            // Iniciar transacción
            $this->db->beginTransaction();

            try {
                // Actualizar solicitud con profesional asignado
                $stmt = $this->db->prepare("
                    UPDATE solicitudes 
                    SET profesional_id = :profesional_id,
                        estado = 'pendiente',
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    'id' => $solicitudId,
                    'profesional_id' => $profesionalId
                ]);

                // Registrar asignación en tabla de auditoría
                $stmt = $this->db->prepare("
                    INSERT INTO asignaciones_profesional 
                    (solicitud_id, profesional_id, asignado_por, motivo, fecha_asignacion)
                    VALUES (:solicitud_id, :profesional_id, :asignado_por, :motivo, CURRENT_TIMESTAMP)
                ");
                
                $stmt->execute([
                    'solicitud_id' => $solicitudId,
                    'profesional_id' => $profesionalId,
                    'asignado_por' => $this->user->id,
                    'motivo' => $motivo
                ]);

                $this->db->commit();

                // TODO: Enviar notificación al profesional asignado

                $this->sendSuccess([
                    'message' => 'Profesional asignado exitosamente',
                    'solicitud_id' => $solicitudId,
                    'profesional' => [
                        'id' => $profesional['id'],
                        'nombre' => $profesional['nombre'] . ' ' . $profesional['apellido']
                    ]
                ]);
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            error_log("Error al asignar profesional: " . $e->getMessage());
            $this->sendError("Error al procesar la asignación", 500);
        }
    }

    /**
     * Obtener estadísticas del sistema
     */
    public function getStats(): void
    {
        try {
            // Solicitudes pendientes de asignación
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM solicitudes 
                WHERE estado = 'pendiente_asignacion'
            ");
            $pendientesAsignacion = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Solicitudes en proceso
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM solicitudes 
                WHERE estado IN ('pendiente', 'confirmada', 'en_progreso')
            ");
            $enProceso = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Solicitudes completadas hoy
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM solicitudes 
                WHERE estado = 'finalizada' 
                AND DATE(fecha_completada) = CURDATE()
            ");
            $completadasHoy = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Ingresos totales del mes
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(monto_total), 0) as total 
                FROM solicitudes 
                WHERE estado IN ('finalizada', 'pendiente_calificacion')
                AND MONTH(created_at) = MONTH(CURDATE())
                AND YEAR(created_at) = YEAR(CURDATE())
            ");
            $ingresosDelMes = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Total de profesionales activos
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM usuarios 
                WHERE rol = 'profesional' AND activo = TRUE
            ");
            $profesionalesActivos = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Total de pacientes
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM usuarios 
                WHERE rol = 'paciente'
            ");
            $totalPacientes = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $this->sendSuccess([
                'pendientes_asignacion' => (int)$pendientesAsignacion,
                'en_proceso' => (int)$enProceso,
                'completadas_hoy' => (int)$completadasHoy,
                'ingresos_del_mes' => (float)$ingresosDelMes,
                'profesionales_activos' => (int)$profesionalesActivos,
                'total_pacientes' => (int)$totalPacientes
            ]);
        } catch (\Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            $this->sendError("Error al cargar estadísticas", 500);
        }
    }

    /**
     * Ver dashboard del admin
     */
    public function dashboard(): void
    {
        require_once __DIR__ . '/../../resources/views/admin/dashboard.php';
    }

    /**
     * Enviar respuesta exitosa
     */
    private function sendSuccess($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Enviar respuesta de error
     */
    private function sendError(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }
}
