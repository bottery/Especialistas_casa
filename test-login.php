<?php
// Test directo del login
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

use App\Controllers\AuthController;

// Simular request POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Crear input stream simulado
$data = json_encode([
    'email' => 'admin@especialistas.com',
    'password' => 'password'
]);

// Ejecutar login
$controller = new AuthController();

// Capturar output
ob_start();
file_put_contents('php://input', $data);
$controller->login();
$output = ob_get_clean();

echo $output;
