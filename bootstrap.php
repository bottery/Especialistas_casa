<?php
/**
 * Autoloader simple para desarrollo sin Composer
 * Este archivo carga todas las clases necesarias automáticamente
 */

spl_autoload_register(function ($class) {
    // Convertir namespace a ruta de archivo
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    
    // Verificar si la clase usa el namespace App
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, mover al siguiente autoloader registrado
        return;
    }
    
    // Obtener el nombre de clase relativo
    $relative_class = substr($class, $len);
    
    // Reemplazar namespace separators con directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Si el archivo existe, cargarlo
    if (file_exists($file)) {
        require $file;
    }
});

// Cargar variables de entorno manualmente
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parsear línea
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remover comillas
            $value = trim($value, '"\'');
            
            // Establecer variable de entorno
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
}

// Helper function para obtener variables de entorno
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return $value;
    }
}

// Helper function para obtener la ruta base
if (!function_exists('base_path')) {
    function base_path($path = '') {
        return __DIR__ . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

// Helper function para redirigir
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit;
    }
}

// Helper function para responder JSON
if (!function_exists('json_response')) {
    function json_response($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Cargar configuraciones
$GLOBALS['config'] = [
    'app' => require base_path('config/app.php'),
    'database' => require base_path('config/database.php'),
    'jwt' => require base_path('config/jwt.php'),
    'mail' => require base_path('config/mail.php'),
    'services' => require base_path('config/services.php'),
];

if (!function_exists('config')) {
    function config($key, $default = null) {
        $keys = explode('.', $key);
        $value = $GLOBALS['config'];
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}
