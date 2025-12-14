<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Usuario;
use App\Services\Database;

/**
 * Controlador para Super Administrador
 */
class SuperAdminController extends BaseController
{
    private $db;
    private $usuarioModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Dashboard principal
     */
    public function dashboard(): void
    {
        try {
            $stats = [];
            
            try { $stats['totalUsuarios'] = $this->getTotalUsuarios(); } catch(\Exception $e) { $stats['totalUsuarios'] = 0; }
            try { $stats['serviciosActivos'] = $this->getServiciosActivos(); } catch(\Exception $e) { $stats['serviciosActivos'] = 0; }
            try { $stats['solicitudesPendientes'] = $this->getSolicitudesPendientes(); } catch(\Exception $e) { $stats['solicitudesPendientes'] = 0; }
            try { $stats['ingresosMes'] = $this->getIngresosMes(); } catch(\Exception $e) { $stats['ingresosMes'] = 0; }
            try { $stats['solicitudesCompletadas'] = $this->getSolicitudesCompletadas(); } catch(\Exception $e) { $stats['solicitudesCompletadas'] = 0; }
            try { $stats['pagosHoy'] = $this->getPagosHoy(); } catch(\Exception $e) { $stats['pagosHoy'] = 0; }
            try { $stats['nuevosUsuariosHoy'] = $this->getNuevosUsuariosHoy(); } catch(\Exception $e) { $stats['nuevosUsuariosHoy'] = 0; }
            try { $stats['profesionalesActivos'] = $this->getProfesionalesActivos(); } catch(\Exception $e) { $stats['profesionalesActivos'] = 0; }

            // Devolver las métricas directamente en el objeto `data`
            // para que la vista JS pueda leer `data.totalUsuarios`, etc.
            $this->sendSuccess($stats);
        } catch (\Exception $e) {
            $this->sendError($e->getMessage() . ' - Line: ' . $e->getLine(), 500);
        }
    }    /**
     * Obtener lista de usuarios
     */
    public function getUsuarios(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $search = $_GET['search'] ?? $input['search'] ?? '';
            $rol = $_GET['rol'] ?? $input['rol'] ?? '';
            $estado = $_GET['estado'] ?? $input['estado'] ?? '';
            $limit = (int)($_GET['limit'] ?? $input['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? $input['offset'] ?? 0);
            
            $usuarios = Usuario::getAllWithFilters($search, $rol, $estado, $limit, $offset);
            $total = Usuario::countWithFilters($search, $rol, $estado);
            
            $this->sendSuccess([
                'usuarios' => $usuarios,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Actualizar estado de usuario
     */
    public function updateUsuarioEstado(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id']) || empty($data['estado'])) {
                $this->sendError("ID y estado son requeridos", 400);
                return;
            }

            $result = $this->usuarioModel->updateEstado($data['id'], $data['estado']);
            
            if ($result) {
                $this->sendSuccess(['message' => 'Estado actualizado correctamente']);
            } else {
                $this->sendError("Error al actualizar estado", 500);
            }
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Eliminar usuario
     */
    public function deleteUsuario(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                $this->sendError("ID es requerido", 400);
                return;
            }

            $result = $this->usuarioModel->delete($data['id']);
            
            if ($result) {
                $this->sendSuccess(['message' => 'Usuario eliminado correctamente']);
            } else {
                $this->sendError("Error al eliminar usuario", 500);
            }
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Obtener configuraciones del sistema
     */
    public function getConfiguraciones(): void
    {
        try {
            $query = "SELECT * FROM configuraciones WHERE tipo IN ('general', 'servicios_externos')";
            $configs = $this->db->select($query);
            
            // Formatear configuraciones
            $formattedConfigs = [];
            foreach ($configs as $config) {
                $formattedConfigs[$config['clave']] = [
                    'id' => $config['id'],
                    'valor' => $config['valor'],
                    'tipo' => $config['tipo']
                ];
            }
            
            $this->sendSuccess(['configuraciones' => $formattedConfigs]);
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Actualizar configuración
     */
    public function updateConfiguracion(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['clave']) || !isset($data['valor'])) {
                $this->sendError("Clave y valor son requeridos", 400);
                return;
            }

            // Verificar si la configuración existe
            $query = "SELECT id FROM configuraciones WHERE clave = ?";
            $existing = $this->db->selectOne($query, [$data['clave']]);

            if ($existing) {
                // Actualizar
                $updateQuery = "UPDATE configuraciones SET valor = ?, updated_at = NOW() WHERE clave = ?";
                $this->db->update($updateQuery, [$data['valor'], $data['clave']]);
            } else {
                // Insertar nueva configuración
                $insertQuery = "INSERT INTO configuraciones (clave, valor, tipo) VALUES (?, ?, 'servicios_externos')";
                $this->db->insert($insertQuery, [$data['clave'], $data['valor']]);
            }
            
            $this->sendSuccess(['message' => 'Configuración actualizada correctamente']);
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Probar conexión de OneSignal
     */
    public function testOneSignal(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['app_id']) || empty($data['api_key'])) {
                $this->sendError("App ID y API Key son requeridos", 400);
                return;
            }

            // Hacer una petición de prueba a OneSignal
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/apps/{$data['app_id']}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Basic {$data['api_key']}",
                "Content-Type: application/json"
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $this->sendSuccess(['message' => 'Conexión exitosa con OneSignal', 'data' => json_decode($response, true)]);
            } else {
                $this->sendError("Error al conectar con OneSignal. Código: {$httpCode}", 400);
            }
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    // Métodos privados para estadísticas

    private function getTotalUsuarios(): int
    {
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM usuarios");
        return (int) ($result['total'] ?? 0);
    }

    private function getServiciosActivos(): int
    {
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM servicios WHERE activo = 1");
        return (int) ($result['total'] ?? 0);
    }

    private function getSolicitudesPendientes(): int
    {
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM solicitudes WHERE estado IN ('pendiente', 'pendiente_pago', 'asignado')");
        return (int) ($result['total'] ?? 0);
    }

    private function getIngresosMes(): float
    {
        $result = $this->db->selectOne("\
            SELECT SUM(monto) as total 
            FROM pagos 
            WHERE MONTH(created_at) = MONTH(CURDATE())
            AND YEAR(created_at) = YEAR(CURDATE())
        ");
        return (float) ($result['total'] ?? 0);
    }

    private function getSolicitudesCompletadas(): int
    {
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM solicitudes WHERE estado IN ('completada', 'completado')");
        return (int) ($result['total'] ?? 0);
    }

    private function getPagosHoy(): int
    {
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM pagos WHERE DATE(created_at) = CURDATE()");
        return (int) ($result['total'] ?? 0);
    }

    private function getNuevosUsuariosHoy(): int
    {
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM usuarios WHERE DATE(created_at) = CURDATE()");
        return (int) ($result['total'] ?? 0);
    }

    private function getProfesionalesActivos(): int
    {
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM usuarios WHERE rol IN ('medico', 'enfermera', 'veterinario', 'laboratorio', 'ambulancia') AND estado = 'activo'");
        return (int) ($result['total'] ?? 0);
    }

    private function getActividadReciente(): array
    {
        // Últimas 10 solicitudes
        $solicitudes = $this->db->select("
            SELECT s.*, 
                   u.nombre as cliente_nombre, 
                   u.email as cliente_email,
                   p.nombre as profesional_nombre
            FROM solicitudes s
            JOIN usuarios u ON s.paciente_id = u.id
            LEFT JOIN usuarios p ON s.profesional_id = p.id
            ORDER BY s.created_at DESC
            LIMIT 10
        ");

        // Últimos 10 pagos
        $pagos = $this->db->select("
            SELECT p.*, 
                   u.nombre as usuario_nombre,
                   u.email as usuario_email
            FROM pagos p
            JOIN usuarios u ON p.usuario_id = u.id
            ORDER BY p.created_at DESC
            LIMIT 10
        ");

        return [
            'solicitudes' => $solicitudes ?? [],
            'pagos' => $pagos ?? []
        ];
    }

    /**
     * Obtener lista de reportes de servicios completados
     */
    public function obtenerReportes(): void
    {
        try {
            $conn = $this->db->getConnection();
            
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
                    prof.rol as tipo_profesional,
                    pp.especialidad,
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
                LEFT JOIN perfiles_profesionales pp ON prof.id = pp.usuario_id
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
            
            $stmt = $conn->prepare($query);
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
            $conn = $this->db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT 
                    s.*,
                    p.nombre as paciente_nombre,
                    p.apellido as paciente_apellido,
                    p.email as paciente_email,
                    p.telefono as paciente_telefono,
                    0 as puntuacion_promedio_paciente,
                    0 as total_calificaciones_paciente,
                    prof.nombre as profesional_nombre,
                    prof.apellido as profesional_apellido,
                    prof.rol as tipo_profesional,
                    pp.especialidad,
                    prof.puntuacion_promedio,
                    prof.total_calificaciones,
                    0 as servicios_completados,
                    srv.nombre as servicio_nombre,
                    srv.tipo as servicio_tipo,
                    srv.descripcion as servicio_descripcion
                FROM solicitudes s
                INNER JOIN usuarios p ON s.paciente_id = p.id
                INNER JOIN usuarios prof ON s.profesional_id = prof.id
                LEFT JOIN perfiles_profesionales pp ON prof.id = pp.usuario_id
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
}
