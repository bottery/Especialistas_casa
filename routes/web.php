<?php

/**
 * Rutas Web
 * Todas las rutas para las vistas HTML
 */

// Obtener ruta
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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
// RUTAS DE MÉDICO/PROFESIONAL
// ============================================
if ($path === '/medico/dashboard') {
    view('medico/dashboard');
}

if ($path === '/medico/servicios') {
    view('medico/servicios');
}

// ============================================
// RUTAS DE ADMINISTRADOR
// ============================================
if ($path === '/admin/dashboard') {
    view('admin/dashboard');
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
