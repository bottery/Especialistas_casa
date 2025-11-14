<?php

namespace App\Middleware;

/**
 * Middleware CSRF Protection
 */
class CsrfMiddleware
{
    private $tokenLifetime;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/app.php';
        $this->tokenLifetime = $config['csrf']['token_lifetime'];
    }

    /**
     * Generar token CSRF
     */
    public function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();

        return $token;
    }

    /**
     * Verificar token CSRF
     */
    public function verifyToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }

        // Verificar expiración del token
        if (time() - $_SESSION['csrf_token_time'] > $this->tokenLifetime) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }

        // Verificar que el token coincida
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Validar request POST
     */
    public function validateRequest(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!$this->verifyToken($token)) {
            $this->sendError("Token CSRF inválido o expirado");
            return false;
        }

        return true;
    }

    /**
     * Enviar error
     */
    private function sendError(string $message): void
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
