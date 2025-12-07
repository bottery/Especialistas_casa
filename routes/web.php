<?php

/**
 * Rutas Web
 * Todas las rutas para las vistas HTML
 */

// Obtener ruta y limpiar el prefijo del subdirectorio
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Detectar el subdirectorio base (ej: /VitaHome/public)
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname(dirname($scriptName)); // Quita /public/index.php
if ($basePath !== '/' && $basePath !== '\\') {
    $path = str_replace($basePath, '', $path);
}
// También quitar /public si quedó
$path = preg_replace('#^/public#', '', $path);
// Normalizar: asegurar que empiece con /
if (empty($path) || $path === '/public') {
    $path = '/';
}

// Función para cargar vistas
function view(string $viewName, array $data = []): void
{
    extract($data);
    $viewPath = __DIR__ . "/../resources/views/{$viewName}.php";
    
    if (file_exists($viewPath)) {
        include $viewPath;
    } else {
        http_response_code(404);
        echo "Vista no encontrada: {$viewName}";
    }
    exit;
}

// ============================================
// RUTAS PÚBLICAS
// ============================================
if ($path === '/' || $path === '') {
    view('home');
}

if ($path === '/login') {
    view('auth/login');
}

if ($path === '/register') {
    view('auth/register');
}

// ============================================
// RUTAS DE PACIENTE
// ============================================
if ($path === '/paciente/dashboard') {
    view('paciente/dashboard');
}

if ($path === '/paciente/nueva-solicitud') {
    view('paciente/nueva-solicitud');
}

if ($path === '/paciente/servicios') {
    view('paciente/servicios');
}

if ($path === '/paciente/historial') {
    view('paciente/historial');
}

// ============================================
// RUTAS DE PROFESIONALES/ESPECIALISTAS
// ============================================
if ($path === '/profesional/dashboard') {
    view('profesional/dashboard');
}

// Redirigir rutas antiguas de roles específicos al dashboard unificado
if (in_array($path, ['/medico/dashboard', '/enfermera/dashboard', '/veterinario/dashboard', '/laboratorio/dashboard', '/ambulancia/dashboard'])) {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/profesional/dashboard');
    exit;
}

if ($path === '/profesional/servicios' || $path === '/medico/servicios') {
    view('profesional/servicios');
}

// ============================================
// RUTAS DE ADMINISTRADOR
// ============================================
if ($path === '/admin/dashboard') {
    $controller = new \App\Controllers\AdminController(false); // Sin autenticación JWT
    $controller->dashboard();
}

if ($path === '/admin/usuarios') {
    view('admin/usuarios');
}

if ($path === '/admin/pagos') {
    view('admin/pagos');
}

// ============================================
// RUTAS DE SUPER ADMINISTRADOR
// ============================================
if ($path === '/superadmin/dashboard') {
    view('superadmin/dashboard');
}

if ($path === '/superadmin/configuracion') {
    view('superadmin/configuracion');
}

if ($path === '/superadmin/usuarios') {
    view('superadmin/usuarios');
}

if ($path === '/superadmin/finanzas') {
    view('superadmin/finanzas');
}

if ($path === '/superadmin/seguridad') {
    view('superadmin/seguridad');
}

if ($path === '/superadmin/contenido') {
    view('superadmin/contenido');
}

// ============================================
// PÁGINA NO ENCONTRADA
// ============================================
http_response_code(404);
view('errors/404');
