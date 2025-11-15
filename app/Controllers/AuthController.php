<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Services\JWTService;
use App\Services\MailService;

/**
 * Controlador de Autenticación
 */
class AuthController
{
    private $usuarioModel;
    private $jwtService;
    private $mailService;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->jwtService = new JWTService();
        $this->mailService = new MailService();
    }

    /**
     * Registro de nuevo usuario
     */
    public function register(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar datos requeridos
            $required = ['email', 'password', 'nombre', 'apellido', 'rol'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $this->sendError("El campo {$field} es requerido", 400);
                    return;
                }
            }

            // Validar email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->sendError("Email inválido", 400);
                return;
            }

            // Verificar si el email ya existe
            if ($this->usuarioModel->findByEmail($data['email'])) {
                $this->sendError("El email ya está registrado", 409);
                return;
            }

            // Validar rol
            $rolesPermitidos = ['paciente', 'medico', 'enfermera', 'veterinario', 'laboratorio', 'ambulancia'];
            if (!in_array($data['rol'], $rolesPermitidos)) {
                $this->sendError("Rol inválido", 400);
                return;
            }

            // Los pacientes se activan automáticamente, los demás requieren aprobación
            $data['estado'] = $data['rol'] === 'paciente' ? 'activo' : 'pendiente';
            $data['verificado'] = $data['rol'] === 'paciente';

            // Crear usuario
            $userId = $this->usuarioModel->createUser($data);

            if ($userId) {
                // Enviar correo de bienvenida
                $this->mailService->sendWelcomeEmail($data['email'], $data['nombre']);

                $this->sendSuccess([
                    'message' => 'Usuario registrado exitosamente',
                    'user_id' => $userId,
                    'requires_approval' => $data['estado'] === 'pendiente'
                ], 201);
            } else {
                $this->sendError("Error al registrar usuario", 500);
            }
        } catch (\Exception $e) {
            error_log("Error en registro: " . $e->getMessage());
            $this->sendError("Error interno del servidor", 500);
        }
    }

    /**
     * Inicio de sesión
     */
    public function login(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar datos
            if (empty($data['email']) || empty($data['password'])) {
                $this->sendError("Email y contraseña son requeridos", 400);
                return;
            }

            // Verificar credenciales
            $user = $this->usuarioModel->verifyCredentials($data['email'], $data['password']);

            if (!$user) {
                $this->sendError("Credenciales inválidas", 401);
                return;
            }

            // Verificar estado del usuario
            if ($user['estado'] === 'bloqueado') {
                $this->sendError("Usuario bloqueado. Contacte al administrador", 403);
                return;
            }

            if ($user['estado'] === 'pendiente') {
                $this->sendError("Usuario pendiente de aprobación", 403);
                return;
            }

            if ($user['estado'] === 'inactivo') {
                $this->sendError("Usuario inactivo", 403);
                return;
            }

            // Actualizar último acceso
            $this->usuarioModel->updateLastAccess($user['id']);

            // Establecer sesión PHP para vistas web
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user'] = (object) $user;
            $_SESSION['logged_in'] = true;

            // Generar tokens
            $tokenPayload = [
                'id' => $user['id'],
                'email' => $user['email'],
                'rol' => $user['rol'],
                'nombre' => $user['nombre']
            ];

            $accessToken = $this->jwtService->generateToken($tokenPayload);
            $refreshToken = $this->jwtService->generateRefreshToken($tokenPayload);

            $this->sendSuccess([
                'message' => 'Inicio de sesión exitoso',
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $this->sendError("Error interno del servidor", 500);
        }
    }

    /**
     * Renovar token
     */
    public function refreshToken(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['refresh_token'])) {
                $this->sendError("Refresh token requerido", 400);
                return;
            }

            $userData = $this->jwtService->verifyToken($data['refresh_token']);

            if (!$userData) {
                $this->sendError("Refresh token inválido o expirado", 401);
                return;
            }

            // Generar nuevo access token
            $tokenPayload = [
                'id' => $userData->id,
                'email' => $userData->email,
                'rol' => $userData->rol,
                'nombre' => $userData->nombre
            ];

            $accessToken = $this->jwtService->generateToken($tokenPayload);

            $this->sendSuccess([
                'access_token' => $accessToken,
                'token_type' => 'Bearer'
            ]);
        } catch (\Exception $e) {
            error_log("Error al renovar token: " . $e->getMessage());
            $this->sendError("Error interno del servidor", 500);
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout(): void
    {
        // En JWT stateless, el logout se maneja en el cliente eliminando el token
        // Aquí se puede implementar una blacklist de tokens si se requiere
        $this->sendSuccess(['message' => 'Sesión cerrada exitosamente']);
    }

    /**
     * Enviar respuesta exitosa
     */
    private function sendSuccess(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, ...$data]);
    }

    /**
     * Enviar respuesta de error
     */
    private function sendError(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
    }
}
