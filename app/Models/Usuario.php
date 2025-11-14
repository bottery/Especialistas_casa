<?php

namespace App\Models;

use App\Services\Database;

class Usuario extends Model
{
    protected $table = 'usuarios';
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * Crear un nuevo usuario
     */
    public function crear(array $datos): int
    {
        $hash = password_hash($datos['password'], PASSWORD_BCRYPT);
        
        $query = "INSERT INTO {$this->table} 
                  (nombre, apellido, email, password, telefono, documento, direccion, ciudad, rol) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($query, [
            $datos['nombre'],
            $datos['apellido'],
            $datos['email'],
            $hash,
            $datos['telefono'] ?? null,
            $datos['documento'] ?? null,
            $datos['direccion'] ?? null,
            $datos['ciudad'] ?? null,
            $datos['rol'] ?? 'paciente'
        ]);
    }

    /**
     * Buscar usuario por email
     */
    public static function findByEmail(string $email): ?array
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
        $result = $db->select($query, [$email]);
        return $result[0] ?? null;
    }

    /**
     * Buscar usuario por ID
     */
    public static function findById(int $id): ?array
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM usuarios WHERE id = ? LIMIT 1";
        $result = $db->select($query, [$id]);
        return $result[0] ?? null;
    }

    /**
     * Actualizar usuario
     */
    public function actualizar(int $id, array $datos): bool
    {
        $campos = [];
        $valores = [];
        
        foreach ($datos as $campo => $valor) {
            if ($campo !== 'password' || !empty($valor)) {
                $campos[] = "$campo = ?";
                $valores[] = ($campo === 'password') ? password_hash($valor, PASSWORD_BCRYPT) : $valor;
            }
        }
        
        $valores[] = $id;
        $query = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id = ?";
        
        return $this->db->update($query, $valores) > 0;
    }

    /**
     * Eliminar usuario
     */
    public static function deleteUsuario(int $id): bool
    {
        $db = Database::getInstance();
        $query = "DELETE FROM usuarios WHERE id = ?";
        return $db->execute($query, [$id]) > 0;
    }

    /**
     * Verificar password
     */
    public static function verificarPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Obtener estadísticas por rol
     */
    public function getEstadisticasPorRol(): array
    {
        $query = "SELECT rol, COUNT(*) as total 
                  FROM {$this->table}
                  GROUP BY rol";
        return $this->query($query);
    }

    /**
     * Obtener todos los usuarios
     */
    public static function getAll(): array
    {
        $db = Database::getInstance();
        $query = "SELECT id, nombre, apellido, email, telefono, rol, estado, created_at 
                  FROM usuarios 
                  ORDER BY created_at DESC";
        return $db->select($query);
    }

    /**
     * Actualizar estado del usuario
     */
    public static function updateEstado(int $id, string $estado): bool
    {
        $db = Database::getInstance();
        $query = "UPDATE usuarios SET estado = ?, updated_at = NOW() WHERE id = ?";
        return $db->update($query, [$estado, $id]) > 0;
    }

    /**
     * Obtener usuarios con filtros
     */
    public static function getAllWithFilters(string $search = '', string $rol = '', string $estado = '', int $limit = 50, int $offset = 0): array
    {
        $db = Database::getInstance();
        $params = [];
        
        $query = "SELECT id, nombre, apellido, email, telefono, rol, estado, created_at FROM usuarios WHERE 1=1";
        
        if ($search) {
            $query .= " AND (nombre LIKE ? OR apellido LIKE ? OR email LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if ($rol) {
            $query .= " AND rol = ?";
            $params[] = $rol;
        }
        
        if ($estado) {
            $query .= " AND estado = ?";
            $params[] = $estado;
        }
        
        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $db->select($query, $params);
    }
    
    /**
     * Contar usuarios con filtros
     */
    public static function countWithFilters(string $search = '', string $rol = '', string $estado = ''): int
    {
        $db = Database::getInstance();
        $params = [];
        
        $query = "SELECT COUNT(*) as total FROM usuarios WHERE 1=1";
        
        if ($search) {
            $query .= " AND (nombre LIKE ? OR apellido LIKE ? OR email LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if ($rol) {
            $query .= " AND rol = ?";
            $params[] = $rol;
        }
        
        if ($estado) {
            $query .= " AND estado = ?";
            $params[] = $estado;
        }
        
        $result = $db->select($query, $params);
        return $result[0]['total'] ?? 0;
    }
    
    /**
     * Acciones masivas sobre usuarios
     */
    public static function accionesMasivas(string $accion, array $ids): int
    {
        $db = Database::getInstance();
        $afectados = 0;
        
        foreach ($ids as $id) {
            switch ($accion) {
                case 'aprobar':
                    $db->update('usuarios', ['estado' => 'activo'], ['id' => $id]);
                    $afectados++;
                    break;
                case 'suspender':
                    $db->update('usuarios', ['estado' => 'inactivo'], ['id' => $id]);
                    $afectados++;
                    break;
                case 'eliminar':
                    $usuario = self::findById($id);
                    if ($usuario && $usuario['rol'] !== 'superadmin') {
                        $query = "DELETE FROM usuarios WHERE id = ?"; $db->execute($query, [$id]);
                        $afectados++;
                    }
                    break;
            }
        }
        
        return $afectados;
    }
    
    /**
     * Obtener detalle completo de un usuario
     */
    public static function getDetalleCompleto(int $id): array
    {
        $db = Database::getInstance();
        
        $usuario = self::findById($id);
        
        if (!$usuario) {
            throw new \Exception('Usuario no encontrado');
        }
        
        $perfil = $db->select(
            "SELECT * FROM perfiles_profesionales WHERE usuario_id = ?",
            [$id]
        );
        
        $solicitudes = $db->select(
            "SELECT s.*, serv.nombre as servicio_nombre 
             FROM solicitudes s
             LEFT JOIN servicios serv ON s.servicio_id = serv.id
             WHERE s.paciente_id = ? OR s.profesional_id = ?
             ORDER BY s.created_at DESC
             LIMIT 10",
            [$id, $id]
        );
        
        $logs = $db->select(
            "SELECT * FROM logs_auditoria 
             WHERE usuario_id = ? 
             ORDER BY created_at DESC 
             LIMIT 20",
            [$id]
        );
        
        return [
            'usuario' => $usuario,
            'perfil' => $perfil[0] ?? null,
            'solicitudes' => $solicitudes,
            'logs' => $logs
        ];
    }
}
