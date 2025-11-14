<?php

namespace App\Services;

use App\Vendor\JWT;
use Exception;

/**
 * Servicio de autenticación JWT
 */
class JWTService
{
    private $config;
    private $secret;
    private $algorithm;
    private $expiration;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/jwt.php';
        $this->secret = $this->config['secret'];
        $this->algorithm = $this->config['algorithm'];
        $this->expiration = $this->config['expiration'];

        if (empty($this->secret)) {
            throw new Exception("JWT_SECRET no está configurado en .env");
        }
    }

    /**
     * Generar un token JWT
     */
    public function generateToken(array $payload): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->expiration;

        $token = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'iss' => $this->config['issuer'],
            'aud' => $this->config['audience'],
            'data' => $payload
        ];

        return JWT::encode($token, $this->secret, $this->algorithm);
    }

    /**
     * Verificar y decodificar un token JWT
     */
    public function verifyToken(string $token): ?object
    {
        try {
            $decoded = JWT::decode($token, $this->secret, $this->algorithm);
            return (object)$decoded['data'];
        } catch (Exception $e) {
            error_log("Error al verificar token JWT: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extraer token del header Authorization
     */
    public function getTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Validar token y obtener datos del usuario
     */
    public function validateRequest(): ?object
    {
        $token = $this->getTokenFromHeader();
        
        if (!$token) {
            return null;
        }

        return $this->verifyToken($token);
    }

    /**
     * Generar token de refresh
     */
    public function generateRefreshToken(array $payload): string
    {
        $issuedAt = time();
        $expire = $issuedAt + (86400 * 30); // 30 días

        $token = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'iss' => $this->config['issuer'],
            'aud' => $this->config['audience'],
            'type' => 'refresh',
            'data' => $payload
        ];

        return JWT::encode($token, $this->secret, $this->algorithm);
    }
}
