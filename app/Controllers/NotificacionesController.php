<?php

namespace App\Controllers;

use App\Services\Database;
use PDO;

/**
 * Controlador para notificaciones y seguridad
 */
class NotificacionesController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Dashboard de notificaciones y seguridad
     */
    public function getDashboard()
    {
        try {
            $data = [
                'estadisticas' => $this->getEstadisticas(),
                'notificaciones_recientes' => $this->getNotificacionesRecientes(10),
                'logs_recientes' => $this->getLogsRecientes(10),
                'sesiones_activas' => $this->getSesionesActivas(),
                'alertas_seguridad' => $this->getAlertasSeguridad()
            ];

            $this->sendJson(['success' => true] + $data);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener logs de auditoría con filtros
     */
    public function getLogs()
    {
        try {
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);
            $tipo = $_GET['tipo'] ?? null;
            $usuario_id = $_GET['usuario_id'] ?? null;
            $fecha_desde = $_GET['fecha_desde'] ?? null;
            $fecha_hasta = $_GET['fecha_hasta'] ?? null;

            $where = [];
            $params = [];

            if ($tipo) {
                $where[] = "tipo = :tipo";
                $params[':tipo'] = $tipo;
            }

            if ($usuario_id) {
                $where[] = "usuario_id = :usuario_id";
                $params[':usuario_id'] = $usuario_id;
            }

            if ($fecha_desde) {
                $where[] = "created_at >= :fecha_desde";
                $params[':fecha_desde'] = $fecha_desde;
            }

            if ($fecha_hasta) {
                $where[] = "created_at <= :fecha_hasta";
                $params[':fecha_hasta'] = $fecha_hasta . ' 23:59:59';
            }

            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // Total
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM logs_auditoria $whereClause");
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];

            // Datos
            $sql = "SELECT l.*, u.nombre, u.apellido, u.email 
                    FROM logs_auditoria l
                    LEFT JOIN usuarios u ON l.usuario_id = u.id
                    $whereClause
                    ORDER BY l.created_at DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $logs = $stmt->fetchAll();

            $this->sendJson([
                'success' => true,
                'logs' => $logs,
                'total' => (int)$total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener sesiones activas
     */
    public function getSesiones()
    {
        try {
            $sql = "SELECT s.*, u.nombre, u.apellido, u.email, u.rol
                    FROM sesiones s
                    INNER JOIN usuarios u ON s.usuario_id = u.id
                    WHERE s.activa = 1 
                    AND s.expira_en > NOW()
                    ORDER BY s.ultimo_acceso DESC";
            
            $stmt = $this->db->query($sql);
            $sesiones = $stmt->fetchAll();

            $this->sendJson(['success' => true, 'sesiones' => $sesiones]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cerrar sesión de usuario
     */
    public function cerrarSesion()
    {
        try {
            $data = $this->getJsonInput();
            $sesion_id = $data['sesion_id'];

            $stmt = $this->db->prepare("UPDATE sesiones SET activa = 0 WHERE id = :id");
            $stmt->execute([':id' => $sesion_id]);

            // Log de auditoría
            $this->registrarLog('seguridad', 'Sesión cerrada manualmente', ['sesion_id' => $sesion_id]);

            $this->sendJson(['success' => true, 'message' => 'Sesión cerrada']);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Gestión de notificaciones push
     */
    public function getNotificaciones()
    {
        try {
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);
            $usuario_id = $_GET['usuario_id'] ?? null;
            $leida = $_GET['leida'] ?? null;

            $where = [];
            $params = [];

            if ($usuario_id) {
                $where[] = "usuario_id = :usuario_id";
                $params[':usuario_id'] = $usuario_id;
            }

            if ($leida !== null) {
                $where[] = "leida = :leida";
                $params[':leida'] = (int)$leida;
            }

            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // Total
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM notificaciones $whereClause");
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];

            // Datos
            $sql = "SELECT n.*, u.nombre, u.apellido 
                    FROM notificaciones n
                    LEFT JOIN usuarios u ON n.usuario_id = u.id
                    $whereClause
                    ORDER BY n.created_at DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $notificaciones = $stmt->fetchAll();

            $this->sendJson([
                'success' => true,
                'notificaciones' => $notificaciones,
                'total' => (int)$total
            ]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Enviar notificación push
     */
    public function enviarNotificacion()
    {
        try {
            $data = $this->getJsonInput();
            
            $campos = [
                'usuario_id' => $data['usuario_id'] ?? null,
                'tipo' => $data['tipo'] ?? 'general',
                'titulo' => $data['titulo'],
                'mensaje' => $data['mensaje'],
                'icono' => $data['icono'] ?? null,
                'enlace' => $data['enlace'] ?? null,
                'datos' => isset($data['datos']) ? json_encode($data['datos']) : null,
                'leida' => 0
            ];

            $keys = array_keys($campos);
            $sql = "INSERT INTO notificaciones (" . implode(', ', $keys) . ") 
                    VALUES (:" . implode(', :', $keys) . ")";
            
            $stmt = $this->db->prepare($sql);
            foreach ($campos as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute();

            $id = $this->db->lastInsertId();

            // Si hay OneSignal configurado, enviar push
            $this->enviarPushOneSignal($campos);

            $this->sendJson([
                'success' => true,
                'message' => 'Notificación enviada',
                'id' => (int)$id
            ]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Enviar notificación masiva
     */
    public function enviarNotificacionMasiva()
    {
        try {
            $data = $this->getJsonInput();
            $rol = $data['rol'] ?? null;
            $activos = $data['solo_activos'] ?? true;

            // Obtener usuarios
            $where = [];
            $params = [];

            if ($rol) {
                $where[] = "rol = :rol";
                $params[':rol'] = $rol;
            }

            if ($activos) {
                $where[] = "estado = 'activo'";
            }

            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $stmt = $this->db->prepare("SELECT id FROM usuarios $whereClause");
            $stmt->execute($params);
            $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Insertar notificaciones
            $enviadas = 0;
            foreach ($usuarios as $usuario_id) {
                $stmt = $this->db->prepare("
                    INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, leida) 
                    VALUES (:usuario_id, :tipo, :titulo, :mensaje, 0)
                ");
                $stmt->execute([
                    ':usuario_id' => $usuario_id,
                    ':tipo' => $data['tipo'] ?? 'general',
                    ':titulo' => $data['titulo'],
                    ':mensaje' => $data['mensaje']
                ]);
                $enviadas++;
            }

            $this->sendJson([
                'success' => true,
                'message' => "Notificación enviada a $enviadas usuarios"
            ]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarLeida()
    {
        try {
            $data = $this->getJsonInput();
            $id = $data['id'];

            $stmt = $this->db->prepare("UPDATE notificaciones SET leida = 1, leida_en = NOW() WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $this->sendJson(['success' => true, 'message' => 'Notificación marcada como leída']);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Lista negra de IPs
     */
    public function getIPsBloqueadas()
    {
        try {
            $stmt = $this->db->query("
                SELECT * FROM ips_bloqueadas 
                ORDER BY created_at DESC
            ");
            $ips = $stmt->fetchAll();

            $this->sendJson(['success' => true, 'ips' => $ips]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function bloquearIP()
    {
        try {
            $data = $this->getJsonInput();

            $stmt = $this->db->prepare("
                INSERT INTO ips_bloqueadas (ip, razon, bloqueado_por) 
                VALUES (:ip, :razon, :bloqueado_por)
            ");
            $stmt->execute([
                ':ip' => $data['ip'],
                ':razon' => $data['razon'],
                ':bloqueado_por' => $data['bloqueado_por']
            ]);

            $this->registrarLog('seguridad', 'IP bloqueada', ['ip' => $data['ip'], 'razon' => $data['razon']]);

            $this->sendJson(['success' => true, 'message' => 'IP bloqueada correctamente']);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function desbloquearIP()
    {
        try {
            $data = $this->getJsonInput();
            
            $stmt = $this->db->prepare("DELETE FROM ips_bloqueadas WHERE id = :id");
            $stmt->execute([':id' => $data['id']]);

            $this->registrarLog('seguridad', 'IP desbloqueada', ['id' => $data['id']]);

            $this->sendJson(['success' => true, 'message' => 'IP desbloqueada']);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Exportar logs
     */
    public function exportarLogs()
    {
        try {
            $formato = $_GET['formato'] ?? 'csv';
            $tipo = $_GET['tipo'] ?? null;

            $where = $tipo ? "WHERE tipo = :tipo" : "";
            $sql = "SELECT l.*, u.nombre, u.apellido, u.email 
                    FROM logs_auditoria l
                    LEFT JOIN usuarios u ON l.usuario_id = u.id
                    $where
                    ORDER BY l.created_at DESC
                    LIMIT 5000";
            
            $stmt = $this->db->prepare($sql);
            if ($tipo) {
                $stmt->bindValue(':tipo', $tipo);
            }
            $stmt->execute();
            $logs = $stmt->fetchAll();

            if ($formato === 'csv') {
                $this->exportarCSV($logs, 'logs_auditoria_' . date('Y-m-d'));
            } else {
                $this->exportarJSON($logs, 'logs_auditoria_' . date('Y-m-d'));
            }
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Métodos auxiliares
    private function getEstadisticas()
    {
        $stats = [];

        // Total logs últimas 24h
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM logs_auditoria WHERE created_at >= NOW() - INTERVAL 24 HOUR");
        $stats['logs_24h'] = $stmt->fetch()['total'];

        // Sesiones activas
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM sesiones WHERE activa = 1 AND expira_en > NOW()");
        $stats['sesiones_activas'] = $stmt->fetch()['total'];

        // Notificaciones sin leer
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM notificaciones WHERE leida = 0");
        $stats['notificaciones_sin_leer'] = $stmt->fetch()['total'];

        // IPs bloqueadas
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM ips_bloqueadas");
        $stats['ips_bloqueadas'] = $stmt->fetch()['total'];

        return $stats;
    }

    private function getNotificacionesRecientes($limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT n.*, u.nombre, u.apellido 
            FROM notificaciones n
            LEFT JOIN usuarios u ON n.usuario_id = u.id
            ORDER BY n.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getLogsRecientes($limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT l.*, u.nombre, u.apellido 
            FROM logs_auditoria l
            LEFT JOIN usuarios u ON l.usuario_id = u.id
            ORDER BY l.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getSesionesActivas()
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total
            FROM sesiones 
            WHERE activa = 1 AND expira_en > NOW()
        ");
        return $stmt->fetch()['total'];
    }

    private function getAlertasSeguridad()
    {
        $alertas = [];

        // Múltiples intentos de login fallidos
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM logs_auditoria 
            WHERE tipo = 'auth' 
            AND descripcion LIKE '%fallido%'
            AND created_at >= NOW() - INTERVAL 1 HOUR
        ");
        $intentos = $stmt->fetch()['total'];
        if ($intentos > 10) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => "$intentos intentos de login fallidos en la última hora"
            ];
        }

        return $alertas;
    }

    private function enviarPushOneSignal($notificacion)
    {
        // Implementación con OneSignal API
        // Por ahora solo registrar en logs
        $this->registrarLog('notificacion', 'Push enviado', $notificacion);
    }

    private function registrarLog($tipo, $descripcion, $datos = [])
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO logs_auditoria (tipo, descripcion, datos, ip) 
                VALUES (:tipo, :descripcion, :datos, :ip)
            ");
            $stmt->execute([
                ':tipo' => $tipo,
                ':descripcion' => $descripcion,
                ':datos' => json_encode($datos),
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (\Exception $e) {
            // No fallar si no se puede registrar log
        }
    }

    private function exportarCSV($data, $filename)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }

    private function exportarJSON($data, $filename)
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.json"');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private function getJsonInput()
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    private function sendJson($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
