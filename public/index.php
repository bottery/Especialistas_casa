<?php

/**
 * Punto de entrada de la aplicación
 * Especialistas en Casa
 */

// Configurar zona horaria
date_default_timezone_set('America/Bogota');

// Cargar funciones helper primero
require_once __DIR__ . '/../app/helpers.php';

// Cargar autoloader (intentar Composer primero, luego bootstrap manual)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    // Cargar variables de entorno con Dotenv
    if (file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
    }
} else {
    require_once __DIR__ . '/../bootstrap.php';
}

// Inicializar manejador de errores
App\Core\ErrorHandler::init();

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Configurar headers de seguridad
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CORS - Obtener dominios permitidos
$allowedOrigins = explode(',', env('CORS_ALLOWED_ORIGINS', ''));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins) || env('APP_ENV') === 'local') {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

// Obtener la ruta solicitada
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remover query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Determinar si es una petición API o web
if (strpos($path, '/api/') === 0) {
    // Cargar rutas API
    require_once __DIR__ . '/../routes/api.php';
} else {
    // Cargar rutas web
    require_once __DIR__ . '/../routes/web.php';
}
