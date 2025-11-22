<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\Database;

class AdminController
{
    private $user;
    private $db;
    private $authMiddleware;

    public function __construct($requireAuth = true)
    {
        $this->db = Database::getInstance()->getConnection();
        
        // Solo verificar JWT token para endpoints API
        if ($requireAuth) {
            $this->authMiddleware = new AuthMiddleware();
            
            // Verificar autenticación
            $this->user = $this->authMiddleware->checkRole(['admin']);
            if (!$this->user) {
                exit;
            }
        }
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
                        WHEN s.pagado = TRUE THEN 'Confirmado'
                        ELSE 'Pendiente confirmación'
                    END as estado_pago
                FROM solicitudes s
                INNER JOIN servicios srv ON s.servicio_id = srv.id
                INNER JOIN usuarios p ON s.paciente_id = p.id
                WHERE s.estado = 'pagado' AND s.profesional_id IS NULL
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
     * Obtener solicitudes en proceso (asignadas pero no completadas)
     */
    public function getSolicitudesEnProceso(): void
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
                    p.documento_numero as paciente_documento,
                    prof.nombre as profesional_nombre,
                    prof.apellido as profesional_apellido,
                    prof.email as profesional_email,
                    prof.telefono as profesional_telefono,
                    prof.especialidad as profesional_especialidad,
                    5.0 as calificacion_promedio
                FROM solicitudes s
                INNER JOIN servicios srv ON s.servicio_id = srv.id
                INNER JOIN usuarios p ON s.paciente_id = p.id
                INNER JOIN usuarios prof ON s.profesional_id = prof.id
                WHERE s.estado IN ('asignado', 'en_proceso')
                ORDER BY s.fecha_programada ASC, s.created_at DESC
            ");
            
            $stmt->execute();
            $solicitudes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->sendSuccess([
                'solicitudes' => $solicitudes
            ]);
        } catch (\Exception $e) {
            error_log("Error al obtener solicitudes en proceso: " . $e->getMessage());
            $this->sendError("Error al cargar solicitudes en proceso", 500);
        }
    }

    /**
     * Obtener todas las especialidades
     * GET /api/admin/especialidades
     */
    public function getAllEspecialidades(): void
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    nombre,
                    tipo_profesional,
                    descripcion,
                    icono,
                    activo,
                    orden
                FROM especialidades
                WHERE activo = TRUE
                ORDER BY orden ASC, nombre ASC
            ");
            
            $stmt->execute();
            $especialidades = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->sendSuccess([
                'success' => true,
                'data' => $especialidades
            ]);
        } catch (\Exception $e) {
            error_log("Error al obtener especialidades: " . $e->getMessage());
            $this->sendError("Error al cargar especialidades", 500);
        }
    }

    /**
     * Obtener todos los profesionales
     * GET /api/admin/profesionales
     */
    public function getProfesionales(): void
    {
        try {
            // Filtros opcionales
            $tipo = $_GET['tipo'] ?? null;
            $especialidad = $_GET['especialidad'] ?? null;
            $disponible = isset($_GET['disponible']) ? filter_var($_GET['disponible'], FILTER_VALIDATE_BOOLEAN) : null;
            
            $query = "
                SELECT DISTINCT
                    u.id,
                    u.nombre,
                    u.apellido,
                    u.email,
                    u.telefono,
                    u.tipo_profesional,
                    u.especialidad,
                    u.puntuacion_promedio,
                    u.total_calificaciones,
                    u.estado,
                    u.disponible_ahora
                FROM usuarios u
                WHERE u.rol = 'profesional'
                    AND u.rol NOT IN ('admin', 'superadmin')
            ";
            
            $params = [];
            
            if ($tipo) {
                $query .= " AND u.tipo_profesional = ?";
                $params[] = $tipo;
            }
            
            if ($especialidad) {
                $query .= " AND u.especialidad = ?";
                $params[] = $especialidad;
            }
            
            if ($disponible !== null) {
                $query .= " AND u.disponible_ahora = ?";
                $params[] = $disponible ? 1 : 0;
            }
            
            $query .= " ORDER BY u.puntuacion_promedio DESC, u.nombre ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $profesionales = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->sendSuccess([
                'success' => true,
                'profesionales' => $profesionales,
                'total' => count($profesionales)
            ]);
        } catch (\Exception $e) {
            error_log("Error al obtener profesionales: " . $e->getMessage());
            $this->sendError("Error al cargar profesionales", 500);
        }
    }

    /**
     * Obtener todas las solicitudes
     * GET /api/admin/solicitudes/todas
     */
    public function getAllSolicitudes(): void
    {
        try {
            // Filtros opcionales
            $estado = $_GET['estado'] ?? null;
            $especialidad = $_GET['especialidad'] ?? null;
            $profesionalId = $_GET['profesional_id'] ?? null;
            $busqueda = $_GET['busqueda'] ?? null;
            
            $query = "
                SELECT 
                    s.id,
                    s.paciente_id,
                    s.profesional_id,
                    s.servicio_id,
                    s.estado,
                    s.fecha_solicitud,
                    s.fecha_programada,
                    s.fecha_completada,
                    s.direccion_servicio,
                    s.pagado,
                    s.monto_total,
                    s.sintomas,
                    s.observaciones,
                    pac.nombre as paciente_nombre,
                    pac.apellido as paciente_apellido,
                    pac.telefono as paciente_telefono,
                    pro.nombre as profesional_nombre,
                    pro.apellido as profesional_apellido,
                    pro.tipo_profesional,
                    pro.especialidad as profesional_especialidad,
                    srv.nombre as servicio_nombre,
                    srv.tipo as servicio_tipo
                FROM solicitudes s
                LEFT JOIN usuarios pac ON s.paciente_id = pac.id
                LEFT JOIN usuarios pro ON s.profesional_id = pro.id
                LEFT JOIN servicios srv ON s.servicio_id = srv.id
                WHERE 1=1
            ";
            
            $params = [];
            
            if ($estado) {
                $query .= " AND s.estado = ?";
                $params[] = $estado;
            }
            
            if ($especialidad) {
                $query .= " AND pro.especialidad = ?";
                $params[] = $especialidad;
            }
            
            if ($profesionalId) {
                $query .= " AND s.profesional_id = ?";
                $params[] = $profesionalId;
            }
            
            if ($busqueda) {
                $query .= " AND (
                    pac.nombre LIKE ? OR 
                    pac.apellido LIKE ? OR 
                    pro.nombre LIKE ? OR 
                    pro.apellido LIKE ? OR 
                    s.direccion_servicio LIKE ?
                )";
                $busquedaParam = "%{$busqueda}%";
                $params[] = $busquedaParam;
                $params[] = $busquedaParam;
                $params[] = $busquedaParam;
                $params[] = $busquedaParam;
                $params[] = $busquedaParam;
            }
            
            $query .= " ORDER BY 
                CASE s.estado
                    WHEN 'pendiente' THEN 1
                    WHEN 'asignada' THEN 2
                    WHEN 'en_camino' THEN 3
                    WHEN 'en_proceso' THEN 4
                    WHEN 'completada' THEN 5
                    ELSE 6
                END,
                s.fecha_solicitud DESC
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $solicitudes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->sendSuccess([
                'success' => true,
                'data' => $solicitudes
            ]);
        } catch (\Exception $e) {
            error_log("Error al obtener solicitudes: " . $e->getMessage());
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
            
            // Obtener el tipo de servicio para determinar qué rol buscar
            $stmt = $this->db->prepare("SELECT tipo FROM servicios WHERE id = ?");
            $stmt->execute([$servicioId]);
            $servicio = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$servicio) {
                $this->sendError("Servicio no encontrado", 404);
                return;
            }
            
            $tipoServicio = $servicio['tipo'];

            // Obtener profesionales con ranking por calificación y servicios completados
            $query = "
                SELECT 
                    u.id,
                    u.nombre,
                    u.apellido,
                    u.email,
                    u.telefono,
                    u.ciudad,
                    u.tipo_profesional,
                    u.especialidad,
                    u.puntuacion_promedio,
                    u.total_calificaciones,
                    u.servicios_completados
                FROM usuarios u
                WHERE u.rol = 'profesional'
                    AND u.tipo_profesional = :tipo_profesional
                    AND u.estado = 'activo'
            ";
            
            $params = ['tipo_profesional' => $tipoServicio];
            
            // Filtro adicional por especialidad si es proporcionado (excepto para ambulancias)
            if ($especialidad && $tipoServicio !== 'ambulancia') {
                $query .= " AND u.especialidad LIKE :especialidad";
                $params['especialidad'] = "%{$especialidad}%";
            }
            
            $query .= "
                ORDER BY u.puntuacion_promedio DESC, u.servicios_completados DESC, u.total_calificaciones DESC
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $profesionales = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->sendSuccess([
                'profesionales' => $profesionales,
                'total' => count($profesionales),
                'tipo_servicio' => $tipoServicio
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

            // Verificar que la solicitud existe y está pagada sin asignar
            $stmt = $this->db->prepare("
                SELECT * FROM solicitudes 
                WHERE id = :id AND estado = 'pagado' AND profesional_id IS NULL
            ");
            $stmt->execute(['id' => $solicitudId]);
            $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                $this->sendError("Solicitud no encontrada o ya fue asignada", 404);
                return;
            }

            // Verificar que el profesional existe y está activo
            $stmt = $this->db->prepare("
                SELECT id, nombre, apellido, rol, estado 
                FROM usuarios 
                WHERE id = :id 
                AND rol = 'profesional'
                AND estado = 'activo'
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
                // Actualizar solicitud con profesional asignado y cambiar estado a 'asignado'
                $stmt = $this->db->prepare("
                    UPDATE solicitudes 
                    SET profesional_id = :profesional_id,
                        estado = 'asignado',
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    'id' => $solicitudId,
                    'profesional_id' => $profesionalId
                ]);

                // Registrar en historial de estados (opcional - no bloquea si falla)
                try {
                    $stmt = $this->db->prepare("
                        INSERT INTO solicitud_estado_historial 
                        (solicitud_id, estado_anterior, estado_nuevo, cambiado_por, motivo)
                        VALUES (:solicitud_id, 'pagado', 'asignado', :asignado_por, :motivo)
                    ");
                    
                    $asignadoPor = $this->user ? $this->user->id : 1; // Default a admin si no hay auth
                    
                    $stmt->execute([
                        'solicitud_id' => $solicitudId,
                        'asignado_por' => $asignadoPor,
                        'motivo' => $motivo ?: 'Profesional asignado por administrador'
                    ]);
                } catch (\Exception $e) {
                    // No bloquear la asignación si falla el historial
                    error_log("Error al registrar historial: " . $e->getMessage());
                }

                // Crear notificación para el paciente
                try {
                    $stmt = $this->db->prepare("
                        INSERT INTO notificaciones 
                        (usuario_id, tipo, titulo, mensaje)
                        VALUES (:usuario_id, 'sistema', 'Profesional Asignado', :mensaje)
                    ");
                    
                    $mensajeNotif = 'Se ha asignado un profesional a tu solicitud. Te contactaremos pronto.';
                    $stmt->execute([
                        'usuario_id' => $solicitud['paciente_id'],
                        'mensaje' => $mensajeNotif
                    ]);
                } catch (\Exception $e) {
                    error_log("Error al crear notificación para paciente: " . $e->getMessage());
                }

                // Crear notificación para el profesional
                try {
                    $stmt = $this->db->prepare("
                        INSERT INTO notificaciones 
                        (usuario_id, tipo, titulo, mensaje, datos)
                        VALUES (:usuario_id, 'sistema', 'Nueva Solicitud Asignada', :mensaje, :datos)
                    ");
                    
                    $mensajeProfesional = sprintf(
                        'Se te ha asignado una nueva solicitud de servicio. Por favor revisa los detalles y acepta la solicitud. Servicio: %s',
                        $solicitud['especialidad'] ?? 'Servicio médico'
                    );
                    
                    $datos = json_encode(['solicitud_id' => $solicitudId, 'tipo' => 'asignacion']);
                    
                    $stmt->execute([
                        'usuario_id' => $profesionalId,
                        'mensaje' => $mensajeProfesional,
                        'datos' => $datos
                    ]);
                } catch (\Exception $e) {
                    error_log("Error al crear notificación para profesional: " . $e->getMessage());
                }

                $this->db->commit();

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
            // Solicitudes pendientes de asignación (pagadas sin profesional)
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM solicitudes 
                WHERE estado = 'pagado' AND profesional_id IS NULL
            ");
            $pendientesAsignacion = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Solicitudes en proceso (asignadas o en ejecución)
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM solicitudes 
                WHERE estado IN ('asignado', 'en_proceso')
            ");
            $enProceso = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Solicitudes completadas hoy
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM solicitudes 
                WHERE estado = 'completado'
                AND DATE(updated_at) = CURDATE()
            ");
            $completadasHoy = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Ingresos totales del mes (todas las solicitudes con pago confirmado)
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(monto_total), 0) as total 
                FROM solicitudes 
                WHERE pagado = 1
                AND MONTH(created_at) = MONTH(CURDATE())
                AND YEAR(created_at) = YEAR(CURDATE())
            ");
            $ingresosDelMes = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Total de profesionales activos
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM usuarios 
                WHERE rol IN ('medico', 'enfermera', 'veterinario', 'laboratorio', 'ambulancia')
                AND estado = 'activo'
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
    /**
     * Mostrar dashboard del administrador
     */
    public function dashboard(): void
    {
        // Verificar sesión PHP (no JWT) para vistas web
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['user']->rol !== 'admin') {
            header('Location: /login');
            exit;
        }
        
        require_once __DIR__ . '/../../resources/views/admin/dashboard.php';
        exit;
    }

    /**
     * Obtener lista de reportes de servicios completados
     */
    public function obtenerReportes(): void
    {
        try {
            // Filtros opcionales
            $fecha_desde = $_GET['fecha_desde'] ?? null;
            $fecha_hasta = $_GET['fecha_hasta'] ?? null;
            $profesional_id = $_GET['profesional_id'] ?? null;
            $calificado = $_GET['calificado'] ?? null;
            $estado = $_GET['estado'] ?? 'completado';
            
            $query = "
                SELECT 
                    s.id,
                    s.fecha_programada,
                    s.fecha_completada,
                    s.estado,
                    s.calificado,
                    s.calificacion_paciente,
                    s.comentario_paciente,
                    s.fecha_calificacion,
                    s.reporte_profesional,
                    s.diagnostico,
                    s.resultado as notas_adicionales,
                    p.nombre as paciente_nombre,
                    p.apellido as paciente_apellido,
                    p.email as paciente_email,
                    p.telefono as paciente_telefono,
                    prof.nombre as profesional_nombre,
                    prof.apellido as profesional_apellido,
                    prof.tipo_profesional,
                    prof.especialidad,
                    prof.puntuacion_promedio,
                    prof.total_calificaciones,
                    srv.nombre as servicio_nombre,
                    srv.tipo as servicio_tipo,
                    s.modalidad,
                    s.monto_total,
                    s.monto_profesional,
                    s.monto_plataforma
                FROM solicitudes s
                INNER JOIN usuarios p ON s.paciente_id = p.id
                INNER JOIN usuarios prof ON s.profesional_id = prof.id
                INNER JOIN servicios srv ON s.servicio_id = srv.id
                WHERE s.estado = 'completado' AND s.fecha_completada IS NOT NULL
            ";
            
            $params = [];
            
            if ($fecha_desde) {
                $query .= " AND DATE(s.fecha_completada) >= :fecha_desde";
                $params['fecha_desde'] = $fecha_desde;
            }
            
            if ($fecha_hasta) {
                $query .= " AND DATE(s.fecha_completada) <= :fecha_hasta";
                $params['fecha_hasta'] = $fecha_hasta;
            }
            
            if ($profesional_id) {
                $query .= " AND s.profesional_id = :profesional_id";
                $params['profesional_id'] = $profesional_id;
            }
            
            if ($calificado !== null) {
                $query .= " AND s.calificado = :calificado";
                $params['calificado'] = (bool)$calificado;
            }
            
            if ($estado && $estado !== 'todos') {
                $query .= " AND s.estado = :estado";
                $params['estado'] = $estado;
            }
            
            $query .= " ORDER BY s.fecha_completada DESC LIMIT 100";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $reportes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Calcular estadísticas adicionales
            $stats = [
                'total' => count($reportes),
                'con_calificacion' => 0,
                'sin_calificacion' => 0,
                'promedio_calificacion' => 0,
                'total_ingresos' => 0
            ];
            
            $suma_calificaciones = 0;
            foreach ($reportes as $reporte) {
                if ($reporte['calificado']) {
                    $stats['con_calificacion']++;
                    $suma_calificaciones += $reporte['calificacion_paciente'];
                } else {
                    $stats['sin_calificacion']++;
                }
                $stats['total_ingresos'] += $reporte['monto_total'];
            }
            
            if ($stats['con_calificacion'] > 0) {
                $stats['promedio_calificacion'] = round($suma_calificaciones / $stats['con_calificacion'], 2);
            }
            
            $this->sendSuccess([
                'reportes' => $reportes,
                'estadisticas' => $stats
            ]);
        } catch (\Exception $e) {
            error_log("Error al obtener reportes: " . $e->getMessage());
            $this->sendError("Error al obtener reportes", 500);
        }
    }

    /**
     * Ver reporte detallado de un servicio específico
     */
    public function verReporte(int $solicitudId): void
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    s.*,
                    p.nombre as paciente_nombre,
                    p.apellido as paciente_apellido,
                    p.email as paciente_email,
                    p.telefono as paciente_telefono,
                    p.puntuacion_promedio_paciente,
                    p.total_calificaciones_paciente,
                    prof.nombre as profesional_nombre,
                    prof.apellido as profesional_apellido,
                    prof.tipo_profesional,
                    prof.especialidad,
                    prof.puntuacion_promedio,
                    prof.total_calificaciones,
                    prof.servicios_completados,
                    srv.nombre as servicio_nombre,
                    srv.tipo as servicio_tipo,
                    srv.descripcion as servicio_descripcion
                FROM solicitudes s
                INNER JOIN usuarios p ON s.paciente_id = p.id
                INNER JOIN usuarios prof ON s.profesional_id = prof.id
                INNER JOIN servicios srv ON s.servicio_id = srv.id
                WHERE s.id = :id AND s.estado = 'completado' AND s.fecha_completada IS NOT NULL
            ");
            
            $stmt->execute(['id' => $solicitudId]);
            $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                $this->sendError("Reporte no encontrado", 404);
                return;
            }
            
            $this->sendSuccess([
                'reporte' => [
                    'solicitud_id' => $solicitud['id'],
                    'estado' => $solicitud['estado'],
                    'fecha_solicitud' => $solicitud['fecha_solicitud'],
                    'fecha_programada' => $solicitud['fecha_programada'],
                    'fecha_completada' => $solicitud['fecha_completada'],
                    'paciente' => [
                        'nombre' => $solicitud['paciente_nombre'] . ' ' . $solicitud['paciente_apellido'],
                        'email' => $solicitud['paciente_email'],
                        'telefono' => $solicitud['paciente_telefono'],
                        'puntuacion_promedio' => $solicitud['puntuacion_promedio_paciente'],
                        'total_calificaciones' => $solicitud['total_calificaciones_paciente']
                    ],
                    'profesional' => [
                        'nombre' => $solicitud['profesional_nombre'] . ' ' . $solicitud['profesional_apellido'],
                        'tipo' => $solicitud['tipo_profesional'],
                        'especialidad' => $solicitud['especialidad'],
                        'puntuacion_promedio' => $solicitud['puntuacion_promedio'],
                        'total_calificaciones' => $solicitud['total_calificaciones'],
                        'servicios_completados' => $solicitud['servicios_completados']
                    ],
                    'servicio' => [
                        'nombre' => $solicitud['servicio_nombre'],
                        'tipo' => $solicitud['servicio_tipo'],
                        'descripcion' => $solicitud['servicio_descripcion'],
                        'modalidad' => $solicitud['modalidad']
                    ],
                    'reporte_profesional' => $solicitud['reporte_profesional'],
                    'diagnostico' => $solicitud['diagnostico'],
                    'notas_adicionales' => $solicitud['resultado'],
                    'finanzas' => [
                        'monto_total' => $solicitud['monto_total'],
                        'monto_profesional' => $solicitud['monto_profesional'],
                        'monto_plataforma' => $solicitud['monto_plataforma'],
                        'pagado' => (bool)$solicitud['pagado']
                    ],
                    'calificacion_paciente_a_profesional' => [
                        'calificado' => (bool)$solicitud['calificado'],
                        'puntuacion' => $solicitud['calificacion_paciente'],
                        'comentario' => $solicitud['comentario_paciente'],
                        'fecha' => $solicitud['fecha_calificacion']
                    ],
                    'calificacion_profesional_a_paciente' => [
                        'calificado' => !is_null($solicitud['calificacion_profesional']),
                        'puntuacion' => $solicitud['calificacion_profesional'],
                        'comentario' => $solicitud['comentario_profesional'],
                        'fecha' => $solicitud['fecha_calificacion_profesional']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error al ver reporte: " . $e->getMessage());
            $this->sendError("Error al obtener el reporte", 500);
        }
    }

    /**
     * Obtener todas las especialidades
     */
    public function getEspecialidades(): void
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    nombre,
                    icono,
                    descripcion,
                    activo,
                    created_at
                FROM especialidades
                WHERE activo = 1
                ORDER BY nombre ASC
            ");
            
            $stmt->execute();
            $especialidades = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->sendSuccess([
                'success' => true,
                'data' => $especialidades
            ]);

        } catch (\Exception $e) {
            error_log("Error al obtener especialidades: " . $e->getMessage());
            $this->sendError('Error al obtener especialidades: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener todas las solicitudes para la vista Kanban
     */
    public function getSolicitudesTodas(): void
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
                    p.direccion as paciente_direccion,
                    prof.nombre as profesional_nombre,
                    prof.apellido as profesional_apellido,
                    prof.telefono as profesional_telefono,
                    esp.nombre as especialidad_nombre,
                    esp.icono as especialidad_icono,
                    CASE 
                        WHEN s.estado = 'pendiente_pago' THEN 'pendiente'
                        WHEN s.estado = 'pagado' AND s.profesional_id IS NULL THEN 'pendiente'
                        WHEN s.estado = 'pagado' AND s.profesional_id IS NOT NULL THEN 'asignada'
                        WHEN s.estado = 'asignado' THEN 'asignada'
                        WHEN s.estado = 'en_proceso' THEN 'en_proceso'
                        WHEN s.estado = 'completado' THEN 'completada'
                        ELSE s.estado
                    END as estado_kanban,
                    CASE 
                        WHEN s.pagado = TRUE THEN 'Confirmado'
                        ELSE 'Pendiente confirmación'
                    END as estado_pago
                FROM solicitudes s
                INNER JOIN servicios srv ON s.servicio_id = srv.id
                INNER JOIN usuarios p ON s.paciente_id = p.id
                LEFT JOIN usuarios prof ON s.profesional_id = prof.id
                LEFT JOIN especialidades esp ON s.especialidad_id = esp.id
                WHERE s.estado NOT IN ('cancelado')
                ORDER BY 
                    FIELD(s.estado, 'pendiente_pago', 'pagado', 'asignado', 'en_proceso', 'completado'),
                    s.fecha_solicitud DESC
            ");
            
            $stmt->execute();
            $solicitudes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Agrupar por estado (usar estado_kanban)
            $agrupadas = [
                'pendiente' => [],
                'asignada' => [],
                'en_camino' => [],
                'en_proceso' => [],
                'completada' => []
            ];

            foreach ($solicitudes as $solicitud) {
                $estado = $solicitud['estado_kanban'];
                if (isset($agrupadas[$estado])) {
                    // Usar estado_kanban para la UI
                    $solicitud['estado_original'] = $solicitud['estado'];
                    $solicitud['estado'] = $estado;
                    $agrupadas[$estado][] = $solicitud;
                }
            }

            $this->sendSuccess([
                'success' => true,
                'data' => $solicitudes,
                'agrupadas' => $agrupadas,
                'total' => count($solicitudes)
            ]);

        } catch (\Exception $e) {
            error_log("Error al obtener solicitudes: " . $e->getMessage());
            $this->sendError('Error al obtener solicitudes: ' . $e->getMessage(), 500);
        }
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
