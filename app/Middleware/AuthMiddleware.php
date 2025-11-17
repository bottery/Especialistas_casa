<?php

namespace App\Middleware;

use App\Services\JWTService;
use App\Services\TokenBlacklistService;

/**
 * Middleware de autenticación JWT
 */
class AuthMiddleware
{
    private $jwtService;
    private $tokenBlacklist;

    public function __construct()
    {
        $this->jwtService = new JWTService();
        $this->tokenBlacklist = new TokenBlacklistService();
    }

    /**
     * Verificar autenticación
     */
    public function handle(): ?object
    {
        // Obtener token
        $token = $this->jwtService->getTokenFromHeader();
        
        if (!$token) {
            $this->unauthorized("Token no proporcionado");
            return null;
        }

        // Verificar si el token está en la blacklist
        if ($this->tokenBlacklist->isBlacklisted($token)) {
            $this->unauthorized("Token inválido o revocado");
            return null;
        }

        // Validar token
        $userData = $this->jwtService->verifyToken($token);

        if (!$userData) {
            $this->unauthorized("Token inválido o expirado");
            return null;
        }

        return $userData;
    }

    /**
     * Verificar rol específico
     */
    public function checkRole(array $allowedRoles): ?object
    {
        $userData = $this->handle();

        if (!$userData) {
            return null;
        }

        if (!in_array($userData->rol, $allowedRoles)) {
            $this->forbidden("No tiene permisos para acceder a este recurso");
            return null;
        }

        return $userData;
    }

    /**
     * Respuesta no autorizado
     */
    private function unauthorized(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    /**
     * Respuesta prohibido
     */
    private function forbidden(string $message): void
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}
