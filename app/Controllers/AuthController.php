<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\RateLimiter;
use App\Core\Validator;
use App\Models\Usuario;
use App\Services\JWTService;
use App\Services\MailService;
use App\Services\TokenBlacklistService;

/**
 * Controlador de Autenticación
 */
class AuthController extends BaseController
{
    private $usuarioModel;
    private $jwtService;
    private $mailService;
    private $tokenBlacklist;
    private $rateLimiter;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->jwtService = new JWTService();
        $this->mailService = new MailService();
        $this->tokenBlacklist = new TokenBlacklistService();
        $this->rateLimiter = new RateLimiter();
    }

    /**
     * Registro de nuevo usuario
     */
    public function register(): void
    {
        try {
            // Rate limiting
            $rateLimitKey = RateLimiter::key('register');
            if (!$this->rateLimiter->attempt($rateLimitKey)) {
                return;
            }

            $data = $this->getJsonInput();
            if (!$data) {
                $this->sendError('Datos inválidos', 400);
                return;
            }

            // Validar con Validator
            $validator = Validator::make($data, [
                'email' => 'required|email|unique:usuarios,email',
                'password' => 'required|min:6',
                'nombre' => 'required|min:2|max:255',
                'apellido' => 'required|min:2|max:255',
                'rol' => 'required|in:paciente,medico,enfermera,veterinario,laboratorio,ambulancia'
            ]);

            if ($validator->fails()) {
                $this->sendError('Errores de validación', 422, $validator->errors());
                return;
            }

            // Sanitizar datos
            $data['nombre'] = $this->sanitizeString($data['nombre']);
            $data['apellido'] = $this->sanitizeString($data['apellido']);

            // Determinar rol y tipo_profesional
            $esProfesional = in_array($data['rol'], ['medico', 'enfermera', 'veterinario', 'laboratorio', 'ambulancia']);
            
            if ($esProfesional) {
                $data['tipo_profesional'] = $data['rol']; // Guardar tipo específico
                $data['rol'] = 'profesional'; // Rol unificado
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
            // Rate limiting más estricto para login
            $rateLimitKey = RateLimiter::key('login');
            $limiter = new RateLimiter(5, 15); // 5 intentos cada 15 minutos
            
            if (!$limiter->attempt($rateLimitKey)) {
                return;
            }

            $data = $this->getJsonInput();
            if (!$data || !$this->validateRequired($data, ['email', 'password'])) {
                return;
            }

            // Verificar credenciales
            $user = Usuario::verifyCredentials($data['email'], $data['password']);

            if (!$user) {
                // Incrementar contador de intentos fallidos
                $limiter->hit($rateLimitKey);
                $this->sendError("Credenciales inválidas", 401);
                return;
            }

            // Login exitoso - limpiar rate limit
            $limiter->clear($rateLimitKey);

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
            Usuario::updateLastAccess($user['id']);

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
        try {
            // Obtener token del header
            $token = $this->jwtService->getTokenFromHeader();
            
            if ($token) {
                // Verificar y obtener datos del token
                $tokenData = $this->jwtService->verifyToken($token);
                
                if ($tokenData) {
                    // Agregar token a blacklist hasta su expiración
                    $expiresAt = time() + 3600; // 1 hora por defecto
                    $this->tokenBlacklist->add($token, $expiresAt);
                }
            }
            
            // Cerrar sesión PHP si existe
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            
            $this->sendSuccess(['message' => 'Sesión cerrada exitosamente']);
        } catch (\Exception $e) {
            $this->logError('Error en logout: ' . $e->getMessage());
            $this->sendSuccess(['message' => 'Sesión cerrada']);
        }
    }

}

