<?php

/**
 * Rutas API
 * Todas las rutas de la API REST
 */

use App\Controllers\AuthController;
use App\Controllers\PacienteController;
use App\Controllers\ProfesionalController;
use App\Controllers\MedicoController;
use App\Controllers\AdminController;
use App\Controllers\ConfiguracionPagosController;
use App\Controllers\PagosTransferenciaController;
use App\Controllers\AsignacionProfesionalController;

// Obtener ruta y método
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remover prefijo /api
$path = str_replace('/api', '', $path);

// ============================================
// RUTAS DE AUTENTICACIÓN (Públicas)
// ============================================
if ($path === '/register' && $method === 'POST') {
    $controller = new AuthController();
    $controller->register();
    exit;
}

if ($path === '/login' && $method === 'POST') {
    $controller = new AuthController();
    $controller->login();
    exit;
}

if ($path === '/refresh-token' && $method === 'POST') {
    $controller = new AuthController();
    $controller->refreshToken();
    exit;
}

if ($path === '/logout' && $method === 'POST') {
    $controller = new AuthController();
    $controller->logout();
    exit;
}

// ============================================
// RUTAS PÚBLICAS - SERVICIOS
// ============================================
if ($path === '/servicios' && $method === 'GET') {
    require_once __DIR__ . '/../app/Models/Servicio.php';
    $servicioModel = new App\Models\Servicio();
    $servicios = $servicioModel->getActive();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'servicios' => $servicios]);
    exit;
}

if (preg_match('#^/profesionales$#', $path) && $method === 'GET') {
    require_once __DIR__ . '/../app/Models/Usuario.php';
    $usuarioModel = new App\Models\Usuario();
    
    $servicioId = $_GET['servicio_id'] ?? null;
    
    if ($servicioId) {
        $profesionales = $usuarioModel->query(
            "SELECT u.id, u.nombre, u.apellido, u.email, u.telefono, u.especialidad, u.estado
             FROM usuarios u
             INNER JOIN profesional_servicios ps ON u.id = ps.profesional_id
             WHERE ps.servicio_id = ? AND u.rol IN ('medico', 'enfermera', 'veterinario') AND u.estado = 'activo'
             ORDER BY u.nombre",
            [$servicioId]
        );
    } else {
        $profesionales = $usuarioModel->query(
            "SELECT id, nombre, apellido, email, telefono, especialidad, rol 
             FROM usuarios 
             WHERE rol IN ('medico', 'enfermera', 'veterinario') AND estado = 'activo'
             ORDER BY nombre"
        );
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'profesionales' => $profesionales]);
    exit;
}

// ============================================
// RUTAS GENERALES - SOLICITUDES
// ============================================
if ($path === '/solicitudes' && $method === 'POST') {
    $controller = new PacienteController();
    $controller->requestService();
    exit;
}

// ============================================
// RUTAS DE PACIENTE (Autenticadas)
// ============================================
if (strpos($path, '/paciente/') === 0) {
    if ($path === '/paciente/stats' && $method === 'GET') {
        $controller = new PacienteController();
        $controller->getStats();
        exit;
    }
    
    if ($path === '/paciente/solicitudes' && $method === 'GET') {
        $controller = new PacienteController();
        $controller->getHistory();
        exit;
    }
    
    if ($path === '/paciente/servicios' && $method === 'GET') {
        $controller = new PacienteController();
        $controller->listServices();
        exit;
    }
    
    if ($path === '/paciente/solicitar' && $method === 'POST') {
        $controller = new PacienteController();
        $controller->requestService();
        exit;
    }
    
    if ($path === '/paciente/historial' && $method === 'GET') {
        $controller = new PacienteController();
        $controller->getHistory();
        exit;
    }
    
    if ($path === '/paciente/solicitud' && $method === 'GET') {
        $controller = new PacienteController();
        $controller->getRequestDetail();
        exit;
    }
    
    if ($path === '/paciente/cancelar' && $method === 'POST') {
        $controller = new PacienteController();
        $controller->cancelRequest();
        exit;
    }
    
    if ($path === '/paciente/upload' && $method === 'POST') {
        $controller = new PacienteController();
        $controller->uploadDocuments();
        exit;
    }

    // Calificar servicio
    if (preg_match('#^/paciente/calificar/(\d+)$#', $path, $matches) && $method === 'POST') {
        $controller = new PacienteController();
        $controller->calificarServicio((int)$matches[1]);
        exit;
    }
}

// ============================================
// RUTAS DE PROFESIONAL (Autenticadas)
// ============================================
if (strpos($path, '/profesional/') === 0) {
    if ($path === '/profesional/stats' && $method === 'GET') {
        $controller = new ProfesionalController();
        $controller->getStats();
        exit;
    }
    
    if ($path === '/profesional/solicitudes' && $method === 'GET') {
        $controller = new ProfesionalController();
        $controller->getSolicitudes();
        exit;
    }
    
    if (preg_match('#^/profesional/solicitudes/(\d+)/aceptar$#', $path, $matches) && $method === 'POST') {
        $controller = new ProfesionalController();
        $controller->aceptarSolicitud((int)$matches[1]);
        exit;
    }
    
    if (preg_match('#^/profesional/solicitudes/(\d+)/rechazar$#', $path, $matches) && $method === 'POST') {
        $controller = new ProfesionalController();
        $controller->rechazarSolicitud((int)$matches[1]);
        exit;
    }
    
    if (preg_match('#^/profesional/solicitudes/(\d+)/iniciar$#', $path, $matches) && $method === 'POST') {
        $controller = new ProfesionalController();
        $controller->iniciarServicio((int)$matches[1]);
        exit;
    }
    
    if (preg_match('#^/profesional/solicitudes/(\d+)/completar$#', $path, $matches) && $method === 'POST') {
        $controller = new ProfesionalController();
        $controller->completarServicio((int)$matches[1]);
        exit;
    }
}

// ============================================
// RUTAS DE MÉDICO/PROFESIONAL (Autenticadas - LEGACY)
// ============================================
if (strpos($path, '/medico/') === 0) {
    if ($path === '/medico/servicios' && $method === 'GET') {
        // $controller = new MedicoController();
        // $controller->getServices();
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
    
    if ($path === '/medico/confirmar' && $method === 'POST') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
    
    if ($path === '/medico/rechazar' && $method === 'POST') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
    
    if ($path === '/medico/iniciar' && $method === 'POST') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
    
    if ($path === '/medico/completar' && $method === 'POST') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
    
    if ($path === '/medico/reporte' && $method === 'POST') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
}

// ============================================
// RUTAS DE ADMINISTRADOR (Autenticadas)
// ============================================
if (strpos($path, '/admin/') === 0) {
    // Estadísticas del admin
    if ($path === '/admin/stats' && $method === 'GET') {
        $controller = new AdminController();
        $controller->getStats();
        exit;
    }

    // Solicitudes pendientes de asignación
    if ($path === '/admin/solicitudes/pendientes' && $method === 'GET') {
        $controller = new AdminController();
        $controller->getSolicitudesPendientes();
        exit;
    }

    // Profesionales disponibles para asignación
    if ($path === '/admin/profesionales' && $method === 'GET') {
        $controller = new AdminController();
        $controller->getProfesionalesDisponibles();
        exit;
    }

    // Asignar profesional a solicitud
    if (preg_match('#^/admin/solicitudes/(\d+)/asignar$#', $path, $matches) && $method === 'POST') {
        $controller = new AdminController();
        $controller->asignarProfesional((int)$matches[1]);
        exit;
    }
    
    // Endpoints legacy
    if ($path === '/admin/dashboard' && $method === 'GET') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
    
    if ($path === '/admin/usuarios' && $method === 'GET') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
    
    if ($path === '/admin/aprobar-usuario' && $method === 'POST') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
    
    if ($path === '/admin/pagos' && $method === 'GET') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
    
    if ($path === '/admin/aprobar-pago' && $method === 'POST') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
}

// ============================================
// RUTAS DE SUPER ADMINISTRADOR (Autenticadas)
// ============================================
if (strpos($path, '/superadmin/') === 0) {
    if ($path === '/superadmin/config' && $method === 'GET') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
    
    if ($path === '/superadmin/config' && $method === 'POST') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
    
    if ($path === '/superadmin/logs' && $method === 'GET') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
    
    if ($path === '/superadmin/integraciones' && $method === 'POST') {
        sendResponse(['message' => 'Endpoint en desarrollo'], 501);
        exit;
    }
}

// ============================================
// RUTA NO ENCONTRADA
// ============================================
// Commented out - causes duplicate responses
// Uncomment and move to end if needed
/*
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Endpoint no encontrado',
    'path' => $path,
    'method' => $method
]);
*/

/**
 * Función auxiliar para enviar respuestas
 */
function sendResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, ...$data]);
}

// ==========================================
// SUPER ADMIN ROUTES
// ==========================================

if ($path === '/superadmin/dashboard' && $method === 'GET') {
    $controller = new \App\Controllers\SuperAdminController();
    $controller->dashboard();
    exit;
}

if ($path === '/superadmin/usuarios' && $method === 'GET') {
    $controller = new \App\Controllers\SuperAdminController();
    $controller->getUsuarios();
    exit;
}

if ($path === '/superadmin/usuarios/estado' && $method === 'PUT') {
    $controller = new \App\Controllers\SuperAdminController();
    $controller->updateUsuarioEstado();
    exit;
}

if ($path === '/superadmin/usuarios/delete' && $method === 'DELETE') {
    $controller = new \App\Controllers\SuperAdminController();
    $controller->deleteUsuario();
    exit;
}

if ($path === '/superadmin/configuraciones' && $method === 'GET') {
    $controller = new \App\Controllers\SuperAdminController();
    $controller->getConfiguraciones();
    exit;
}

if ($path === '/superadmin/configuraciones' && $method === 'PUT') {
    $controller = new \App\Controllers\SuperAdminController();
    $controller->updateConfiguracion();
    exit;
}

if ($path === '/superadmin/test-onesignal' && $method === 'POST') {
    $controller = new \App\Controllers\SuperAdminController();
    $controller->testOneSignal();
    exit;
}

if ($path === '/superadmin/acciones-masivas' && $method === 'POST') {
    $controller = new \App\Controllers\SuperAdminController();
    $controller->accionesMasivas();
    exit;
}

if ($path === '/superadmin/usuario-detalle' && $method === 'GET') {
    $controller = new \App\Controllers\SuperAdminController();
    $controller->getUsuarioDetalle();
    exit;
}

if ($path === '/superadmin/exportar-usuarios' && $method === 'GET') {
    $controller = new \App\Controllers\SuperAdminController();
    $controller->exportarUsuarios();
    exit;
}

// ==========================================
// FINANZAS ROUTES
// ==========================================

if ($path === '/finanzas/dashboard' && $method === 'GET') {
    $controller = new \App\Controllers\FinanzasController();
    $controller->getDashboard();
    exit;
}

if ($path === '/finanzas/actualizar-pago' && $method === 'PUT') {
    $controller = new \App\Controllers\FinanzasController();
    $controller->actualizarEstadoPago();
    exit;
}

if ($path === '/finanzas/procesar-retiro' && $method === 'POST') {
    $controller = new \App\Controllers\FinanzasController();
    $controller->procesarRetiro();
    exit;
}

if ($path === '/finanzas/exportar' && $method === 'GET') {
    $controller = new \App\Controllers\FinanzasController();
    $controller->exportarReporte();
    exit;
}

// ==========================================
// ANALYTICS ROUTES
// ==========================================

if ($path === '/analytics/charts' && $method === 'GET') {
    $controller = new \App\Controllers\AnalyticsController();
    $controller->getChartData();
    exit;
}

// ==========================================
// CONTENIDO ROUTES
// ==========================================

if ($path === '/contenido/dashboard' && $method === 'GET') {
    $controller = new \App\Controllers\ContenidoController();
    $controller->getDashboard();
    exit;
}

if ($path === '/contenido/servicios' && $method === 'GET') {
    $controller = new \App\Controllers\ContenidoController();
    $controller->getServicios();
    exit;
}

if ($path === '/contenido/servicio' && $method === 'POST') {
    $controller = new \App\Controllers\ContenidoController();
    $controller->guardarServicio();
    exit;
}

if ($path === '/contenido/servicio' && $method === 'DELETE') {
    $controller = new \App\Controllers\ContenidoController();
    $controller->eliminarServicio();
    exit;
}

if ($path === '/contenido/banners' && $method === 'GET') {
    $controller = new \App\Controllers\ContenidoController();
    $controller->getBanners();
    exit;
}

if ($path === '/contenido/banner' && $method === 'POST') {
    $controller = new \App\Controllers\ContenidoController();
    $controller->guardarBanner();
    exit;
}

if ($path === '/contenido/banner' && $method === 'DELETE') {
    $controller = new \App\Controllers\ContenidoController();
    $controller->eliminarBanner();
    exit;
}

if ($path === '/contenido/faqs' && $method === 'GET') {
    $controller = new \App\Controllers\ContenidoController();
    $controller->getFAQs();
    exit;
}

if ($path === '/contenido/faq' && $method === 'POST') {
    $controller = new \App\Controllers\ContenidoController();
    $controller->guardarFAQ();
    exit;
}

if ($path === '/contenido/faq' && $method === 'DELETE') {
    $controller = new \App\Controllers\ContenidoController();
    $controller->eliminarFAQ();
    exit;
}

if ($path === '/contenido/contenido' && $method === 'GET') {
    $controller = new \App\Controllers\ContenidoController();
    $controller->getContenido();
    exit;
}

if ($path === '/contenido/contenido' && $method === 'POST') {
    $controller = new \App\Controllers\ContenidoController();
    $controller->guardarContenido();
    exit;
}

// ==========================================
// SEGURIDAD Y NOTIFICACIONES ROUTES
// ==========================================

if ($path === '/seguridad/dashboard' && $method === 'GET') {
    $controller = new \App\Controllers\NotificacionesController();
    $controller->getDashboard();
    exit;
}

if ($path === '/seguridad/logs' && $method === 'GET') {
    $controller = new \App\Controllers\NotificacionesController();
    $controller->getLogs();
    exit;
}

if ($path === '/seguridad/logs/exportar' && $method === 'GET') {
    $controller = new \App\Controllers\NotificacionesController();
    $controller->exportarLogs();
    exit;
}

if ($path === '/seguridad/sesiones' && $method === 'GET') {
    $controller = new \App\Controllers\NotificacionesController();
    $controller->getSesiones();
    exit;
}

if ($path === '/seguridad/sesion/cerrar' && $method === 'POST') {
    $controller = new \App\Controllers\NotificacionesController();
    $controller->cerrarSesion();
    exit;
}

if ($path === '/seguridad/notificaciones' && $method === 'GET') {
    $controller = new \App\Controllers\NotificacionesController();
    $controller->getNotificaciones();
    exit;
}

if ($path === '/seguridad/notificacion' && $method === 'POST') {
    $controller = new \App\Controllers\NotificacionesController();
    $controller->enviarNotificacion();
    exit;
}

if ($path === '/seguridad/notificacion-masiva' && $method === 'POST') {
    $controller = new \App\Controllers\NotificacionesController();
    $controller->enviarNotificacionMasiva();
    exit;
}

if ($path === '/seguridad/notificacion/leida' && $method === 'PUT') {
    $controller = new \App\Controllers\NotificacionesController();
    $controller->marcarLeida();
    exit;
}

if ($path === '/seguridad/ips' && $method === 'GET') {
    $controller = new \App\Controllers\NotificacionesController();
    $controller->getIPsBloqueadas();
    exit;
}

if ($path === '/seguridad/ip/bloquear' && $method === 'POST') {
    $controller = new \App\Controllers\NotificacionesController();
    $controller->bloquearIP();
    exit;
}

if ($path === '/seguridad/ip/desbloquear' && $method === 'DELETE') {
    $controller = new \App\Controllers\NotificacionesController();
    $controller->desbloquearIP();
    exit;
}

// ============================================
// RUTAS DE NOTIFICACIONES EN TIEMPO REAL
// ============================================
if ($path === '/notifications' && $method === 'GET') {
    $controller = new \App\Controllers\NotificationsController();
    $controller->index();
    exit;
}

if (preg_match('#^/notifications/(\d+)/read$#', $path, $matches) && $method === 'POST') {
    $controller = new \App\Controllers\NotificationsController();
    $controller->markAsRead($matches[1]);
    exit;
}

if ($path === '/notifications/read-all' && $method === 'POST') {
    $controller = new \App\Controllers\NotificationsController();
    $controller->markAllAsRead();
    exit;
}

// ============================================
// RUTAS DE CHAT EN TIEMPO REAL
// ============================================
if ($path === '/chat/start' && $method === 'POST') {
    $controller = new \App\Controllers\ChatController();
    $controller->start();
    exit;
}

if (preg_match('#^/chat/(\d+)/messages$#', $path, $matches) && $method === 'GET') {
    $controller = new \App\Controllers\ChatController();
    $controller->getMessages($matches[1]);
    exit;
}

if ($path === '/chat/send' && $method === 'POST') {
    $controller = new \App\Controllers\ChatController();
    $controller->send();
    exit;
}

if (preg_match('#^/chat/(\d+)/poll$#', $path, $matches) && $method === 'GET') {
    $controller = new \App\Controllers\ChatController();
    $controller->poll($matches[1]);
    exit;
}

if (preg_match('#^/chat/(\d+)/typing$#', $path, $matches) && $method === 'POST') {
    $controller = new \App\Controllers\ChatController();
    $controller->typing($matches[1]);
    exit;
}

// ============================================
// RUTAS DE CALENDARIO / CITAS
// ============================================
if ($path === '/citas' && $method === 'GET') {
    require_once __DIR__ . '/../app/Models/SolicitudServicio.php';
    $solicitudModel = new App\Models\SolicitudServicio();
    $userId = $_SESSION['user']->id ?? null;
    
    // Obtener solicitudes con fecha programada
    $citas = $solicitudModel->query(
        "SELECT s.id, s.servicio_id, sv.nombre as servicio, s.paciente_id, 
                u_pac.nombre as paciente_nombre, s.profesional_id, 
                u_prof.nombre as profesional_nombre, s.estado, s.direccion, 
                s.notas, s.fecha_programada, s.duracion_estimada, s.fecha_servicio
         FROM solicitudes_servicio s
         LEFT JOIN servicios sv ON s.servicio_id = sv.id
         LEFT JOIN usuarios u_pac ON s.paciente_id = u_pac.id
         LEFT JOIN usuarios u_prof ON s.profesional_id = u_prof.id
         WHERE (s.paciente_id = ? OR s.profesional_id = ?)
         AND s.fecha_programada IS NOT NULL
         ORDER BY s.fecha_programada",
        [$userId, $userId]
    );
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'citas' => $citas]);
    exit;
}

if (preg_match('#^/citas/(\d+)/reschedule$#', $path, $matches) && $method === 'PUT') {
    require_once __DIR__ . '/../app/Models/SolicitudServicio.php';
    $solicitudModel = new App\Models\SolicitudServicio();
    
    
    $data = json_decode(file_get_contents('php://input'), true);
    $fechaProgramada = $data['fecha_programada'] ?? null;
    
    if ($fechaProgramada) {
        $solicitudModel->update($matches[1], [
            'fecha_programada' => $fechaProgramada
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Cita reprogramada']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Fecha requerida']);
    }
    exit;
}

// ============================================
// RUTAS DE CONFIGURACIÓN DE PAGOS (Superadmin)
// ============================================

// GET /api/admin/configuracion-pagos - Obtener configuración
if ($path === '/admin/configuracion-pagos' && $method === 'GET') {
    $controller = new ConfiguracionPagosController();
    $controller->getConfiguracion();
    exit;
}

// PUT /api/admin/configuracion-pagos - Actualizar configuración
if ($path === '/admin/configuracion-pagos' && $method === 'PUT') {
    $controller = new ConfiguracionPagosController();
    $controller->updateConfiguracion();
    exit;
}

// POST /api/admin/configuracion-pagos/qr - Subir QR
if ($path === '/admin/configuracion-pagos/qr' && $method === 'POST') {
    $controller = new ConfiguracionPagosController();
    $controller->uploadQR();
    exit;
}

// DELETE /api/admin/configuracion-pagos/qr - Eliminar QR
if ($path === '/admin/configuracion-pagos/qr' && $method === 'DELETE') {
    $controller = new ConfiguracionPagosController();
    $controller->deleteQR();
    exit;
}

// ============================================
// RUTAS DE PAGOS POR TRANSFERENCIA (Admin/Paciente)
// ============================================

// GET /api/admin/pagos/pendientes - Listar pagos pendientes
if ($path === '/admin/pagos/pendientes' && $method === 'GET') {
    $controller = new PagosTransferenciaController();
    $controller->getPagosPendientes();
    exit;
}

// GET /api/admin/pagos/{id} - Detalle de pago
if (preg_match('#^/admin/pagos/(\d+)$#', $path, $matches) && $method === 'GET') {
    $controller = new PagosTransferenciaController();
    $controller->getDetallePago((int)$matches[1]);
    exit;
}

// POST /api/admin/pagos/{id}/aprobar - Aprobar pago
if (preg_match('#^/admin/pagos/(\d+)/aprobar$#', $path, $matches) && $method === 'POST') {
    $controller = new PagosTransferenciaController();
    $controller->aprobarPago((int)$matches[1]);
    exit;
}

// POST /api/admin/pagos/{id}/rechazar - Rechazar pago
if (preg_match('#^/admin/pagos/(\d+)/rechazar$#', $path, $matches) && $method === 'POST') {
    $controller = new PagosTransferenciaController();
    $controller->rechazarPago((int)$matches[1]);
    exit;
}

// POST /api/pagos/{id}/comprobante - Subir comprobante (Paciente)
if (preg_match('#^/pagos/(\d+)/comprobante$#', $path, $matches) && $method === 'POST') {
    $controller = new PagosTransferenciaController();
    $controller->uploadComprobante((int)$matches[1]);
    exit;
}

// ============================================
// RUTAS DE ASIGNACIÓN DE PROFESIONALES (Admin)
// ============================================

// GET /api/admin/solicitudes/pendientes - Solicitudes sin asignar
if ($path === '/admin/solicitudes/pendientes' && $method === 'GET') {
    $controller = new AsignacionProfesionalController();
    $controller->getSolicitudesPendientes();
    exit;
}

// GET /api/admin/profesionales/disponibles - Profesionales ordenados por calificación
if ($path === '/admin/profesionales/disponibles' && $method === 'GET') {
    $controller = new AsignacionProfesionalController();
    $controller->getProfesionalesDisponibles();
    exit;
}

// POST /api/admin/solicitudes/{id}/asignar - Asignar profesional
if (preg_match('#^/admin/solicitudes/(\d+)/asignar$#', $path, $matches) && $method === 'POST') {
    $controller = new AsignacionProfesionalController();
    $controller->asignarProfesional((int)$matches[1]);
    exit;
}

// POST /api/admin/solicitudes/{id}/reasignar - Reasignar profesional
if (preg_match('#^/admin/solicitudes/(\d+)/reasignar$#', $path, $matches) && $method === 'POST') {
    $controller = new AsignacionProfesionalController();
    $controller->reasignarProfesional((int)$matches[1]);
    exit;
}


