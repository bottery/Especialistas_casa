<?php

namespace App\Services;

use PDO;
use PDOException;

/**
 * Clase Database
 * Maneja la conexión a la base de datos usando PDO
 */
class Database
{
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct()
    {
        $this->config = require __DIR__ . '/../../config/database.php';
        $this->connect();
    }

    /**
     * Obtener instancia única (Singleton)
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establecer conexión a la base de datos
     */
    private function connect(): void
    {
        $dbConfig = $this->config['connections'][$this->config['default']];

        try {
            // Usar socket Unix si está disponible, sino usar host/port
            if (!empty($dbConfig['unix_socket'])) {
                $dsn = sprintf(
                    "%s:unix_socket=%s;dbname=%s;charset=%s",
                    $dbConfig['driver'],
                    $dbConfig['unix_socket'],
                    $dbConfig['database'],
                    $dbConfig['charset']
                );
            } else {
                $dsn = sprintf(
                    "%s:host=%s;port=%s;dbname=%s;charset=%s",
                    $dbConfig['driver'],
                    $dbConfig['host'],
                    $dbConfig['port'],
                    $dbConfig['database'],
                    $dbConfig['charset']
                );
            }

            $this->connection = new PDO(
                $dsn,
                $dbConfig['username'],
                $dbConfig['password'],
                $dbConfig['options']
            );
        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new \Exception("No se pudo conectar a la base de datos");
        }
    }

    /**
     * Obtener la conexión PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Ejecutar una consulta SELECT
     */
    public function select(string $query, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en consulta SELECT: " . $e->getMessage());
            throw new \Exception("Error al ejecutar consulta");
        }
    }

    /**
     * Ejecutar una consulta SELECT y devolver un único registro
     */
    public function selectOne(string $query, array $params = []): ?array
    {
        $results = $this->select($query, $params);
        return $results[0] ?? null;
    }

    /**
     * Ejecutar una consulta INSERT
     */
    public function insert(string $query, array $params = []): int
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return (int) $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en consulta INSERT: " . $e->getMessage());
            throw new \Exception("Error al insertar datos");
        }
    }

    /**
     * Ejecutar una consulta UPDATE
     */
    public function update(string $query, array $params = []): int
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error en consulta UPDATE: " . $e->getMessage());
            throw new \Exception("Error al actualizar datos");
        }
    }

    /**
     * Ejecutar una consulta DELETE
     */
    public function delete(string $query, array $params = []): int
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error en consulta DELETE: " . $e->getMessage());
            throw new \Exception("Error al eliminar datos");
        }
    }

    /**
     * Ejecutar una consulta personalizada
     */
    public function query(string $query, array $params = []): bool
    {
        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error en consulta: " . $e->getMessage());
            throw new \Exception("Error al ejecutar consulta");
        }
    }

    /**
     * Ejecutar una consulta (alias de query para compatibilidad)
     */
    public function execute(string $query, array $params = []): bool
    {
        return $this->query($query, $params);
    }

    /**
     * Obtener el ID del último registro insertado
     */
    public function lastInsertId(): int
    {
        return (int) $this->connection->lastInsertId();
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollback(): bool
    {
        return $this->connection->rollback();
    }

    /**
     * Prevenir clonación del objeto
     */
    private function __clone() {}

    /**
     * Prevenir deserialización del objeto
     */
    public function __wakeup() {}
}
