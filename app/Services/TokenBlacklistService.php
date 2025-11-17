<?php

namespace App\Services;

use App\Services\Database;

/**
 * Servicio para manejo de blacklist de tokens JWT
 */
class TokenBlacklistService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Agregar token a la blacklist
     */
    public function add(string $token, int $expiresAt): bool
    {
        try {
            $tokenHash = hash('sha256', $token);
            $expiresDate = date('Y-m-d H:i:s', $expiresAt);
            
            $query = "INSERT INTO token_blacklist (token_hash, expira_en) 
                     VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE expira_en = VALUES(expira_en)";
            
            $this->db->insert($query, [$tokenHash, $expiresDate]);
            return true;
        } catch (\Exception $e) {
            error_log("Error adding token to blacklist: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un token está en la blacklist
     */
    public function isBlacklisted(string $token): bool
    {
        try {
            $tokenHash = hash('sha256', $token);
            
            $query = "SELECT COUNT(*) as count 
                     FROM token_blacklist 
                     WHERE token_hash = ? AND expira_en > NOW()";
            
            $result = $this->db->selectOne($query, [$tokenHash]);
            return $result && $result['count'] > 0;
        } catch (\Exception $e) {
            error_log("Error checking blacklist: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpiar tokens expirados
     */
    public function cleanExpired(): int
    {
        try {
            $query = "DELETE FROM token_blacklist WHERE expira_en < NOW()";
            return $this->db->delete($query);
        } catch (\Exception $e) {
            error_log("Error cleaning expired tokens: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Invalidar todos los tokens de un usuario
     */
    public function invalidateUserTokens(int $userId): bool
    {
        // Esto requeriría almacenar el userId con cada token
        // Por simplicidad, se puede implementar a nivel de sesiones
        return true;
    }
}
