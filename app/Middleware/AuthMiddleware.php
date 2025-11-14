<?php

namespace App\Middleware;

use App\Services\JWTService;

/**
 * Middleware de autenticación JWT
 */
class AuthMiddleware
{
    private $jwtService;

    public function __construct()
    {
        $this->jwtService = new JWTService();
    }

    /**
     * Verificar autenticación
     */
    public function handle(): ?object
    {
        $userData = $this->jwtService->validateRequest();

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
