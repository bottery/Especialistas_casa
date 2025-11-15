<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Services\Database;

/**
 * Controlador para Super Administrador
 */
class SuperAdminController
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

            $this->sendSuccess(['stats' => $stats]);
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
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM servicios WHERE estado = 'activo'");
        return (int) ($result['total'] ?? 0);
    }

    private function getSolicitudesPendientes(): int
    {
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente'");
        return (int) ($result['total'] ?? 0);
    }

    private function getIngresosMes(): float
    {
        $result = $this->db->selectOne("
            SELECT SUM(monto) as total 
            FROM pagos 
            WHERE estado IN ('completado', 'aprobado') 
            AND MONTH(created_at) = MONTH(CURDATE())
            AND YEAR(created_at) = YEAR(CURDATE())
        ");
        return (float) ($result['total'] ?? 0);
    }

    private function getSolicitudesCompletadas(): int
    {
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'completada'");
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
        $result = $this->db->selectOne("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'profesional' AND estado = 'activo'");
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

    private function sendSuccess($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
        exit;
    }

    private function sendError(string $message, int $status = 400): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}
