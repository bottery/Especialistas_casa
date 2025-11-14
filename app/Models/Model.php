<?php

namespace App\Models;

use App\Services\Database;

/**
 * Modelo base
 * Todos los modelos heredan de esta clase
 */
abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener todos los registros
     */
    public function all(): array
    {
        $query = "SELECT * FROM {$this->table}";
        return $this->db->select($query);
    }

    /**
     * Buscar por ID
     */
    public function find(int $id): ?array
    {
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->db->selectOne($query, [$id]);
    }

    /**
     * Buscar por campo específico
     */
    public function findBy(string $field, $value): ?array
    {
        $query = "SELECT * FROM {$this->table} WHERE {$field} = ? LIMIT 1";
        return $this->db->selectOne($query, [$value]);
    }

    /**
     * Obtener múltiples registros por campo
     */
    public function where(string $field, $value): array
    {
        $query = "SELECT * FROM {$this->table} WHERE {$field} = ?";
        return $this->db->select($query, [$value]);
    }

    /**
     * Crear un nuevo registro
     */
    public function create(array $data): int
    {
        $data = $this->filterFillable($data);
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        return $this->db->insert($query, array_values($data));
    }

    /**
     * Actualizar un registro
     */
    public function update(int $id, array $data): int
    {
        $data = $this->filterFillable($data);
        $fields = array_keys($data);
        $setParts = array_map(fn($field) => "{$field} = ?", $fields);

        $query = sprintf(
            "UPDATE %s SET %s WHERE {$this->primaryKey} = ?",
            $this->table,
            implode(', ', $setParts)
        );

        $params = array_merge(array_values($data), [$id]);
        return $this->db->update($query, $params);
    }

    /**
     * Eliminar un registro
     */
    public function delete(int $id): int
    {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->delete($query, [$id]);
    }

    /**
     * Contar registros
     */
    public function count(array $where = []): int
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($where)) {
            $conditions = array_map(fn($key) => "{$key} = ?", array_keys($where));
            $query .= " WHERE " . implode(' AND ', $conditions);
            $result = $this->db->selectOne($query, array_values($where));
        } else {
            $result = $this->db->selectOne($query);
        }

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Filtrar campos permitidos
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Ocultar campos sensibles
     */
    protected function hideFields(array $data): array
    {
        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }
        return $data;
    }

    /**
     * Ejecutar consulta personalizada
     */
    public function query(string $sql, array $params = []): array
    {
        return $this->db->select($sql, $params);
    }

    /**
     * Ejecutar consulta personalizada y obtener un registro
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        return $this->db->selectOne($sql, $params);
    }
}
