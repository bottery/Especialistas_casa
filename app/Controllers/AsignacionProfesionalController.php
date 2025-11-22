<?php
/**
 * Controlador para asignación de profesionales a solicitudes
 * Solo accesible por admin/superadmin
 */

namespace App\Controllers;

class AsignacionProfesionalController extends BaseController
{
    /**
     * Obtener solicitudes pendientes de asignación
     * GET /api/admin/solicitudes/pendientes
     */
    public function getSolicitudesPendientes(): void
    {
        try {
            $this->requireAuth(['admin', 'superadmin']);
            
            global $pdo;
            
            $stmt = $pdo->query("
                SELECT 
                    s.*,
                    srv.nombre as servicio_nombre,
                    srv.tipo as servicio_tipo,
                    srv.precio_base,
                    u.nombre as paciente_nombre,
                    u.email as paciente_email,
                    u.telefono as paciente_telefono,
                    u.documento as paciente_documento,
                    p.estado as estado_pago,
                    p.metodo_pago
                FROM solicitudes s
                INNER JOIN servicios srv ON s.servicio_id = srv.id
                INNER JOIN usuarios u ON s.paciente_id = u.id
                LEFT JOIN pagos p ON s.id = p.solicitud_id
                WHERE s.estado = 'pendiente' 
                  AND s.profesional_id IS NULL
                  AND s.pagado = TRUE
                ORDER BY s.fecha_solicitud ASC
            ");
            
            $solicitudes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->sendSuccess([
                'solicitudes' => $solicitudes,
                'total' => count($solicitudes)
            ]);
            
        } catch (\Exception $e) {
            error_log("Error obteniendo solicitudes pendientes: " . $e->getMessage());
            $this->sendError("Error al obtener solicitudes pendientes", 500);
        }
    }
    
    /**
     * Obtener profesionales disponibles ordenados por calificación
     * GET /api/admin/profesionales/disponibles?servicio_tipo={tipo}&especialidad={especialidad}
     */
    public function getProfesionalesDisponibles(): void
    {
        try {
            $this->requireAuth(['admin', 'superadmin']);
            
            $servicioTipo = $_GET['servicio_tipo'] ?? null;
            $especialidad = $_GET['especialidad'] ?? null;
            $modalidad = $_GET['modalidad'] ?? null;
            
            if (!$servicioTipo) {
                $this->sendError("El tipo de servicio es requerido", 400);
                return;
            }
            
            global $pdo;
            
            // Query para obtener profesionales con su calificación promedio
            $sql = "
                SELECT 
                    u.id,
                    u.nombre,
                    u.email,
                    u.telefono,
                    u.foto_perfil,
                    u.tipo_profesional,
                    pp.especialidad,
                    pp.años_experiencia,
                    pp.tarifa_consulta_virtual,
                    pp.tarifa_consulta_presencial,
                    pp.tarifa_consultorio,
                    pp.aprobado,
                    u.puntuacion_promedio as calificacion_promedio,
                    u.total_calificaciones,
                    0 as servicios_completados
                FROM usuarios u
                INNER JOIN perfiles_profesionales pp ON u.id = pp.usuario_id
                WHERE u.rol = 'profesional'
                  AND u.estado = 'activo'
                  AND pp.aprobado = TRUE
            ";
            
            $params = [];
            
            // Filtrar por especialidad si se proporciona
            if ($especialidad) {
                $sql .= " AND pp.especialidad = :especialidad";
                $params['especialidad'] = $especialidad;
            }
            
            // Filtrar por modalidad si se proporciona
            if ($modalidad) {
                switch ($modalidad) {
                    case 'virtual':
                        $sql .= " AND pp.tarifa_consulta_virtual > 0";
                        break;
                    case 'presencial':
                        $sql .= " AND pp.tarifa_consulta_presencial > 0";
                        break;
                    case 'consultorio':
                        $sql .= " AND pp.tarifa_consultorio > 0";
                        break;
                }
            }
            
            $sql .= " 
                ORDER BY 
                    u.puntuacion_promedio DESC,  -- Mejor calificación primero
                    u.total_calificaciones DESC,    -- Más calificaciones después
                    pp.años_experiencia DESC      -- Más años de experiencia al final
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $profesionales = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Formatear datos
            foreach ($profesionales as &$prof) {
                $prof['calificacion_promedio'] = round((float)$prof['calificacion_promedio'], 1);
                $prof['modalidades_disponibles'] = [];
                
                // Determinar modalidades según tarifas
                if ($prof['tarifa_consulta_virtual'] > 0) {
                    $prof['modalidades_disponibles'][] = 'virtual';
                }
                if ($prof['tarifa_consulta_presencial'] > 0) {
                    $prof['modalidades_disponibles'][] = 'presencial';
                }
                if ($prof['tarifa_consultorio'] > 0) {
                    $prof['modalidades_disponibles'][] = 'consultorio';
                }
                
                if ($prof['modalidad_virtual']) $prof['modalidades_disponibles'][] = 'virtual';
                if ($prof['modalidad_presencial']) $prof['modalidades_disponibles'][] = 'presencial';
                if ($prof['modalidad_consultorio']) $prof['modalidades_disponibles'][] = 'consultorio';
                
                // Calcular disponibilidad (servicios activos pendientes)
                $stmtDisp = $pdo->prepare("
                    SELECT COUNT(*) as servicios_activos
                    FROM solicitudes
                    WHERE profesional_id = :prof_id
                      AND estado IN ('confirmada', 'en_progreso')
                ");
                $stmtDisp->execute(['prof_id' => $prof['id']]);
                $disp = $stmtDisp->fetch(\PDO::FETCH_ASSOC);
                $prof['servicios_activos'] = (int)$disp['servicios_activos'];
            }
            
            $this->sendSuccess([
                'profesionales' => $profesionales,
                'total' => count($profesionales),
                'filtros' => [
                    'servicio_tipo' => $servicioTipo,
                    'especialidad' => $especialidad,
                    'modalidad' => $modalidad
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Error obteniendo profesionales: " . $e->getMessage());
            $this->sendError("Error al obtener profesionales disponibles", 500);
        }
    }
    
    /**
     * Asignar profesional a una solicitud
     * POST /api/admin/solicitudes/{id}/asignar
     */
    public function asignarProfesional(int $solicitudId): void
    {
        try {
            $this->requireAuth(['admin', 'superadmin']);
            
            $data = $this->getRequestData();
            
            if (empty($data['profesional_id'])) {
                $this->sendError("El ID del profesional es requerido", 400);
                return;
            }
            
            global $pdo;
            $adminId = $_SESSION['user_id'];
            
            $pdo->beginTransaction();
            
            // Verificar que la solicitud existe y está pendiente
            $stmt = $pdo->prepare("
                SELECT s.*, u.nombre as paciente_nombre, srv.nombre as servicio_nombre
                FROM solicitudes s
                INNER JOIN usuarios u ON s.paciente_id = u.id
                INNER JOIN servicios srv ON s.servicio_id = srv.id
                WHERE s.id = :solicitud_id 
                  AND s.estado = 'pendiente'
                  AND s.profesional_id IS NULL
                  AND s.pagado = TRUE
            ");
            $stmt->execute(['solicitud_id' => $solicitudId]);
            $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                $pdo->rollBack();
                $this->sendError("Solicitud no encontrada o no está disponible para asignación", 404);
                return;
            }
            
            // Verificar que el profesional existe y está activo
            $stmt = $pdo->prepare("
                SELECT u.*, pp.aprobado as perfil_aprobado
                FROM usuarios u
                INNER JOIN perfiles_profesionales pp ON u.id = pp.usuario_id
                WHERE u.id = :prof_id 
                  AND u.rol = 'profesional'
                  AND u.estado = 'activo'
            ");
            $stmt->execute(['prof_id' => $data['profesional_id']]);
            $profesional = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$profesional || !$profesional['perfil_aprobado']) {
                $pdo->rollBack();
                $this->sendError("Profesional no encontrado o no está activo", 404);
                return;
            }
            
            // Asignar profesional y cambiar estado a 'confirmada'
            $stmt = $pdo->prepare("
                UPDATE solicitudes 
                SET profesional_id = :prof_id,
                    estado = 'confirmada'
                WHERE id = :solicitud_id
            ");
            $stmt->execute([
                'solicitud_id' => $solicitudId,
                'prof_id' => $data['profesional_id']
            ]);
            
            // Registrar en historial
            $stmt = $pdo->prepare("
                INSERT INTO solicitud_estado_historial 
                (solicitud_id, estado_anterior, estado_nuevo, cambiado_por, motivo)
                VALUES (:solicitud_id, 'pendiente', 'confirmada', :admin_id, :motivo)
            ");
            $stmt->execute([
                'solicitud_id' => $solicitudId,
                'admin_id' => $adminId,
                'motivo' => 'Profesional asignado por administrador: ' . $profesional['nombre']
            ]);
            
            // Notificar al profesional
            $stmt = $pdo->prepare("
                INSERT INTO notificaciones 
                (user_id, from_user_id, type, title, message, action_url, created_at)
                VALUES 
                (:prof_id, :admin_id, 'nueva_solicitud', 
                 'Nueva solicitud asignada', 
                 :mensaje,
                 '/profesional/solicitudes',
                 NOW())
            ");
            $stmt->execute([
                'prof_id' => $data['profesional_id'],
                'admin_id' => $adminId,
                'mensaje' => "Se te ha asignado una nueva solicitud de {$solicitud['servicio_nombre']} para {$solicitud['paciente_nombre']}. Fecha programada: {$solicitud['fecha_programada']}"
            ]);
            
            // Notificar al paciente
            $stmt = $pdo->prepare("
                INSERT INTO notificaciones 
                (user_id, from_user_id, type, title, message, action_url, created_at)
                VALUES 
                (:paciente_id, :admin_id, 'servicio_asignado', 
                 'Profesional asignado', 
                 :mensaje,
                 '/mis-solicitudes',
                 NOW())
            ");
            $stmt->execute([
                'paciente_id' => $solicitud['paciente_id'],
                'admin_id' => $adminId,
                'mensaje' => "Tu solicitud de {$solicitud['servicio_nombre']} ha sido asignada a {$profesional['nombre']}. Te contactará pronto."
            ]);
            
            $pdo->commit();
            
            $this->sendSuccess([
                'message' => 'Profesional asignado exitosamente. Se han enviado notificaciones.',
                'solicitud_id' => $solicitudId,
                'profesional_id' => $data['profesional_id'],
                'profesional_nombre' => $profesional['nombre'],
                'nuevo_estado' => 'confirmada'
            ]);
            
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error asignando profesional: " . $e->getMessage());
            $this->sendError("Error al asignar profesional: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Reasignar profesional (si el asignado rechaza o no puede atender)
     * POST /api/admin/solicitudes/{id}/reasignar
     */
    public function reasignarProfesional(int $solicitudId): void
    {
        try {
            $this->requireAuth(['admin', 'superadmin']);
            
            $data = $this->getRequestData();
            
            if (empty($data['profesional_id'])) {
                $this->sendError("El ID del nuevo profesional es requerido", 400);
                return;
            }
            
            if (empty($data['motivo'])) {
                $this->sendError("El motivo de la reasignación es requerido", 400);
                return;
            }
            
            global $pdo;
            $adminId = $_SESSION['user_id'];
            
            $pdo->beginTransaction();
            
            // Obtener solicitud actual
            $stmt = $pdo->prepare("
                SELECT s.*, u.nombre as paciente_nombre, 
                       prof.nombre as profesional_anterior_nombre
                FROM solicitudes s
                INNER JOIN usuarios u ON s.paciente_id = u.id
                LEFT JOIN usuarios prof ON s.profesional_id = prof.id
                WHERE s.id = :solicitud_id
            ");
            $stmt->execute(['solicitud_id' => $solicitudId]);
            $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                $pdo->rollBack();
                $this->sendError("Solicitud no encontrada", 404);
                return;
            }
            
            $profesionalAnteriorId = $solicitud['profesional_id'];
            
            // Verificar nuevo profesional
            $stmt = $pdo->prepare("
                SELECT u.nombre FROM usuarios u
                INNER JOIN perfiles_profesionales pp ON u.id = pp.usuario_id
                WHERE u.id = :prof_id AND u.rol = 'profesional' AND u.estado = 'activo'
            ");
            $stmt->execute(['prof_id' => $data['profesional_id']]);
            $nuevoProfesional = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$nuevoProfesional) {
                $pdo->rollBack();
                $this->sendError("Nuevo profesional no encontrado o no activo", 404);
                return;
            }
            
            // Reasignar
            $stmt = $pdo->prepare("
                UPDATE solicitudes 
                SET profesional_id = :nuevo_prof_id,
                    estado = 'confirmada'
                WHERE id = :solicitud_id
            ");
            $stmt->execute([
                'solicitud_id' => $solicitudId,
                'nuevo_prof_id' => $data['profesional_id']
            ]);
            
            // Registrar en historial
            $stmt = $pdo->prepare("
                INSERT INTO solicitud_estado_historial 
                (solicitud_id, estado_anterior, estado_nuevo, cambiado_por, motivo)
                VALUES (:solicitud_id, :estado_anterior, 'confirmada', :admin_id, :motivo)
            ");
            $stmt->execute([
                'solicitud_id' => $solicitudId,
                'estado_anterior' => $solicitud['estado'],
                'admin_id' => $adminId,
                'motivo' => 'Reasignación: ' . $data['motivo']
            ]);
            
            // Notificar al nuevo profesional
            $stmt = $pdo->prepare("
                INSERT INTO notificaciones 
                (user_id, from_user_id, type, title, message, action_url, created_at)
                VALUES (:prof_id, :admin_id, 'nueva_solicitud', 'Nueva solicitud asignada', 
                        :mensaje, '/profesional/solicitudes', NOW())
            ");
            $stmt->execute([
                'prof_id' => $data['profesional_id'],
                'admin_id' => $adminId,
                'mensaje' => "Se te ha asignado una solicitud (reasignación) para {$solicitud['paciente_nombre']}."
            ]);
            
            // Notificar al profesional anterior si existía
            if ($profesionalAnteriorId) {
                $stmt = $pdo->prepare("
                    INSERT INTO notificaciones 
                    (user_id, from_user_id, type, title, message, created_at)
                    VALUES (:prof_id, :admin_id, 'solicitud_reasignada', 
                            'Solicitud reasignada', :mensaje, NOW())
                ");
                $stmt->execute([
                    'prof_id' => $profesionalAnteriorId,
                    'admin_id' => $adminId,
                    'mensaje' => "La solicitud #{$solicitudId} ha sido reasignada a otro profesional. Motivo: {$data['motivo']}"
                ]);
            }
            
            $pdo->commit();
            
            $this->sendSuccess([
                'message' => 'Solicitud reasignada exitosamente',
                'profesional_nuevo' => $nuevoProfesional['nombre']
            ]);
            
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error reasignando profesional: " . $e->getMessage());
            $this->sendError("Error al reasignar profesional: " . $e->getMessage(), 500);
        }
    }
}
