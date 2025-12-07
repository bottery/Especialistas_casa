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
use App\Controllers\HealthController;

// Obtener ruta y método
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remover prefijo BASE_URL si existe (ej: /VitaHome)
if (defined('BASE_URL') && BASE_URL !== '' && strpos($path, BASE_URL) === 0) {
    $path = substr($path, strlen(BASE_URL));
}

// Remover prefijo /api
$path = preg_replace('#^/api#', '', $path);
if ($path === '' || $path === false) {
    $path = '/';
}

// Debug log
error_log("API Route Debug - Original URI: {$_SERVER['REQUEST_URI']}, BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'undefined') . ", Final path: $path, Method: $method");

// ============================================
// HEALTH CHECK (Sin autenticación)
// ============================================
if ($path === '/health' && $method === 'GET') {
    $controller = new HealthController();
    $controller->check();
    exit;
}

if ($path === '/ping' && $method === 'GET') {
    $controller = new HealthController();
    $controller->ping();
    exit;
}

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

if ($path === '/auth/refresh' && $method === 'POST') {
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

// Obtener datos bancarios públicos para formularios de pago
if ($path === '/configuracion/pagos' && $method === 'GET') {
    $controller = new ConfiguracionPagosController();
    $controller->getDatosBancariosPublico();
    exit;
}

// Obtener especialidades médicas disponibles (público)
if ($path === '/especialidades' && $method === 'GET') {
    require_once __DIR__ . '/../app/Services/Database.php';
    $db = App\Services\Database::getInstance();
    
    $tipo = $_GET['tipo'] ?? null;
    
    // Si se especifica un tipo, filtrar por ese tipo
    if ($tipo) {
        $query = "SELECT DISTINCT pp.especialidad 
                  FROM perfiles_profesionales pp
                  INNER JOIN usuarios u ON pp.usuario_id = u.id
                  WHERE u.tipo_profesional = ?
                    AND pp.especialidad IS NOT NULL 
                    AND pp.especialidad != ''
                    AND u.estado = 'activo'
                  ORDER BY pp.especialidad ASC";
        $result = $db->select($query, [$tipo]);
    } else {
        // Obtener todas las especialidades de profesionales activos
        $query = "SELECT DISTINCT pp.especialidad 
                  FROM perfiles_profesionales pp
                  INNER JOIN usuarios u ON pp.usuario_id = u.id
                  WHERE pp.especialidad IS NOT NULL 
                    AND pp.especialidad != ''
                    AND u.estado = 'activo'
                  ORDER BY pp.especialidad ASC";
        $result = $db->select($query);
    }
    
    $especialidades = array_column($result, 'especialidad');
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'especialidades' => $especialidades]);
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
    
    // Obtener reporte final de servicio completado
    if (preg_match('#^/paciente/reporte/(\d+)$#', $path, $matches) && $method === 'GET') {
        $controller = new PacienteController();
        $controller->obtenerReporteFinal((int)$matches[1]);
        exit;
    }
    
    // Obtener servicios pendientes de calificar (OBLIGATORIO)
    if ($path === '/paciente/servicios-pendientes-calificar' && $method === 'GET') {
        $controller = new PacienteController();
        $controller->getServiciosPendientesCalificar();
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
    
    if (preg_match('#^/profesional/solicitudes/(\d+)/calificar-paciente$#', $path, $matches) && $method === 'POST') {
        $controller = new ProfesionalController();
        $controller->calificarPaciente((int)$matches[1]);
        exit;
    }
    
    if ($path === '/profesional/servicios-pendientes-calificar' && $method === 'GET') {
        $controller = new ProfesionalController();
        $controller->getServiciosPendientesCalificarPaciente();
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
    // ========== ESPECIALIDADES ==========
    // Listar todas las especialidades
    if ($path === '/admin/especialidades' && $method === 'GET') {
        $controller = new AdminController(false); // Sin auth por ahora para debugging
        $controller->getEspecialidades();
        exit;
    }
    
    // Obtener especialidades por tipo profesional
    if (preg_match('#^/admin/especialidades/tipo/([a-z]+)$#', $path, $matches) && $method === 'GET') {
        require_once __DIR__ . '/../app/Models/Especialidad.php';
        
        $especialidades = \App\Models\Especialidad::getPorTipo($matches[1]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $especialidades]);
        exit;
    }
    
    // Obtener estadísticas de especialidades
    if ($path === '/admin/especialidades/estadisticas' && $method === 'GET') {
        require_once __DIR__ . '/../app/Models/Especialidad.php';
        
        $stats = \App\Models\Especialidad::getEstadisticas();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $stats]);
        exit;
    }
    
    // Crear nueva especialidad
    if ($path === '/admin/especialidades' && $method === 'POST') {
        require_once __DIR__ . '/../app/Models/Especialidad.php';
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = \App\Models\Especialidad::crear($data);
        
        header('Content-Type: application/json');
        if ($id) {
            echo json_encode(['success' => true, 'data' => ['id' => $id]]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No se pudo crear la especialidad']);
        }
        exit;
    }
    
    // Actualizar especialidad
    if (preg_match('#^/admin/especialidades/(\d+)$#', $path, $matches) && $method === 'PUT') {
        require_once __DIR__ . '/../app/Models/Especialidad.php';
        
        $data = json_decode(file_get_contents('php://input'), true);
        $success = \App\Models\Especialidad::actualizar((int)$matches[1], $data);
        
        header('Content-Type: application/json');
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No se pudo actualizar la especialidad']);
        }
        exit;
    }
    
    // Eliminar especialidad (soft delete)
    if (preg_match('#^/admin/especialidades/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        require_once __DIR__ . '/../app/Models/Especialidad.php';
        
        $success = \App\Models\Especialidad::eliminar((int)$matches[1]);
        
        header('Content-Type: application/json');
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No se pudo eliminar la especialidad']);
        }
        exit;
    }
    
    // ========== FIN ESPECIALIDADES ==========
    
    // ========== DISPONIBILIDAD ==========
    // Obtener disponibilidad de un profesional
    if (preg_match('#^/admin/profesionales/(\d+)/disponibilidad$#', $path, $matches) && $method === 'GET') {
        require_once __DIR__ . '/../app/Models/Disponibilidad.php';
        
        $disponibilidad = \App\Models\Disponibilidad::getDisponibilidadSemanal((int)$matches[1]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $disponibilidad]);
        exit;
    }
    
    // Guardar disponibilidad semanal
    if (preg_match('#^/admin/profesionales/(\d+)/disponibilidad$#', $path, $matches) && $method === 'POST') {
        require_once __DIR__ . '/../app/Models/Disponibilidad.php';
        
        $data = json_decode(file_get_contents('php://input'), true);
        $success = \App\Models\Disponibilidad::guardarDisponibilidadSemanal((int)$matches[1], $data['horarios']);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit;
    }
    
    // Obtener próximos horarios disponibles
    if (preg_match('#^/admin/profesionales/(\d+)/horarios-disponibles$#', $path, $matches) && $method === 'GET') {
        require_once __DIR__ . '/../app/Models/Disponibilidad.php';
        
        $duracion = (int)($_GET['duracion'] ?? 60);
        $dias = (int)($_GET['dias'] ?? 7);
        
        $horarios = \App\Models\Disponibilidad::getProximosHorariosDisponibles((int)$matches[1], $duracion, $dias);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $horarios]);
        exit;
    }
    
    // Buscar profesionales disponibles en fecha/hora específica
    if ($path === '/admin/profesionales/disponibles' && $method === 'GET') {
        require_once __DIR__ . '/../app/Models/Disponibilidad.php';
        
        $fechaHora = new DateTime($_GET['fecha_hora'] ?? 'now');
        $especialidadId = isset($_GET['especialidad_id']) ? (int)$_GET['especialidad_id'] : null;
        $duracion = (int)($_GET['duracion'] ?? 60);
        
        $profesionales = \App\Models\Disponibilidad::getProfesionalesDisponibles($fechaHora, $especialidadId, $duracion);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $profesionales]);
        exit;
    }
    
    // Crear bloqueo de disponibilidad (vacaciones, ausencias)
    if (preg_match('#^/admin/profesionales/(\d+)/bloqueos$#', $path, $matches) && $method === 'POST') {
        require_once __DIR__ . '/../app/Models/Disponibilidad.php';
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = \App\Models\Disponibilidad::crearBloqueoNoDisponible(
            (int)$matches[1],
            new DateTime($data['fecha_inicio']),
            new DateTime($data['fecha_fin']),
            $data['motivo'] ?? null,
            $data['tipo'] ?? 'otro'
        );
        
        header('Content-Type: application/json');
        echo json_encode(['success' => (bool)$id, 'data' => ['id' => $id]]);
        exit;
    }
    
    // Actualizar disponibilidad inmediata (toggle "Disponible ahora")
    if (preg_match('#^/admin/profesionales/(\d+)/disponibilidad-inmediata$#', $path, $matches) && $method === 'PATCH') {
        require_once __DIR__ . '/../app/Models/Disponibilidad.php';
        
        $data = json_decode(file_get_contents('php://input'), true);
        $success = \App\Models\Disponibilidad::actualizarDisponibilidadInmediata((int)$matches[1], $data['disponible']);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit;
    }
    
    // ========== FIN DISPONIBILIDAD ==========
    
    // ========== SOLICITUDES KANBAN ==========
    // Obtener todas las solicitudes para vista Kanban
    if ($path === '/admin/solicitudes/todas' && $method === 'GET') {
        $controller = new AdminController(false); // Sin auth por ahora para debugging
        $controller->getSolicitudesTodas();
        exit;
    }
    
    // Cambiar estado de una solicitud
    if (preg_match('#^/admin/solicitudes/(\d+)/estado$#', $path, $matches) && $method === 'PATCH') {
        require_once __DIR__ . '/../app/Services/NotificacionService.php';
        
        $db = \App\Services\Database::getInstance();
        $data = json_decode(file_get_contents('php://input'), true);
        $solicitudId = (int)$matches[1];
        $estadoKanban = $data['estado']; // Estado que viene del Kanban
        
        // Mapear estados del Kanban a estados de la DB
        $mapeoEstados = [
            'pendiente' => 'pagado',
            'asignada' => 'asignado',
            'en_camino' => 'asignado', // No hay estado en_camino en DB
            'en_proceso' => 'en_proceso',
            'completada' => 'completado'
        ];
        
        $estadoDB = $mapeoEstados[$estadoKanban] ?? $estadoKanban;
        
        // Obtener solicitud actual
        $solicitud = $db->selectOne("SELECT * FROM solicitudes WHERE id = ?", [$solicitudId]);
        
        if (!$solicitud) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Solicitud no encontrada']);
            exit;
        }
        
        // Actualizar estado
        $success = $db->query(
            "UPDATE solicitudes SET estado = ?, updated_at = NOW() WHERE id = ?",
            [$estadoDB, $solicitudId]
        );
        
        if ($success) {
            // Registrar hora según el estado (usar el estado del Kanban)
            switch ($estadoKanban) {
                case 'asignada':
                    $db->query("UPDATE solicitudes SET hora_asignacion = NOW() WHERE id = ?", [$solicitudId]);
                    break;
                case 'en_camino':
                    $db->query("UPDATE solicitudes SET hora_salida = NOW() WHERE id = ?", [$solicitudId]);
                    // Notificar al paciente
                    \App\Services\NotificacionService::crearDesdePlantilla(
                        $solicitud['paciente_id'],
                        'profesional_en_camino',
                        ['profesional' => 'Profesional', 'tiempo' => $solicitud['tiempo_estimado_llegada'] ?? 30],
                        $solicitudId
                    );
                    break;
                case 'en_proceso':
                    $db->query("UPDATE solicitudes SET hora_inicio_servicio = NOW() WHERE id = ?", [$solicitudId]);
                    \App\Services\NotificacionService::crearDesdePlantilla(
                        $solicitud['paciente_id'],
                        'servicio_iniciado',
                        ['profesional' => 'Profesional', 'servicio' => 'el servicio'],
                        $solicitudId
                    );
                    break;
                case 'completada':
                    $db->query("UPDATE solicitudes SET fecha_completada = NOW() WHERE id = ?", [$solicitudId]);
                    \App\Services\NotificacionService::crearDesdePlantilla(
                        $solicitud['paciente_id'],
                        'servicio_completado',
                        [],
                        $solicitudId
                    );
                    break;
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al actualizar estado']);
        }
        exit;
    }
    
    // ========== FIN SOLICITUDES KANBAN ==========
    
    // Estadísticas del admin
    if ($path === '/admin/stats' && $method === 'GET') {
        $controller = new AdminController();
        $controller->getStats();
        exit;
    }

    // Solicitudes pendientes de confirmación de pago
    if ($path === '/admin/pagos/pendientes-confirmacion' && $method === 'GET') {
        $controller = new PagosTransferenciaController();
        $controller->getSolicitudesPendientesConfirmacion();
        exit;
    }

    // Aprobar pago de solicitud
    if (preg_match('#^/admin/solicitudes/(\d+)/aprobar-pago$#', $path, $matches) && $method === 'POST') {
        $controller = new PagosTransferenciaController();
        $controller->aprobarPago((int)$matches[1]);
        exit;
    }

    // Rechazar pago de solicitud
    if (preg_match('#^/admin/solicitudes/(\d+)/rechazar-pago$#', $path, $matches) && $method === 'POST') {
        $controller = new PagosTransferenciaController();
        $controller->rechazarPago((int)$matches[1]);
        exit;
    }

    // Obtener QR de pago de una solicitud
    if (preg_match('#^/admin/pagos/(\d+)/qr$#', $path, $matches) && $method === 'GET') {
        $controller = new ConfiguracionPagosController();
        $controller->obtenerQRPago();
        exit;
    }

    // Solicitudes pendientes de asignación
    if ($path === '/admin/solicitudes/pendientes' && $method === 'GET') {
        $controller = new AdminController(false); // Sin auth para debugging
        $controller->getSolicitudesPendientes();
        exit;
    }

    // Solicitudes en proceso (asignadas)
    if ($path === '/admin/solicitudes/en-proceso' && $method === 'GET') {
        $controller = new AdminController(false); // Sin auth para debugging
        $controller->getSolicitudesEnProceso();
        exit;
    }

    // Profesionales disponibles para asignación
    if ($path === '/admin/profesionales' && $method === 'GET') {
        // Si no tiene servicio_id, es una llamada general del dashboard
        if (!isset($_GET['servicio_id'])) {
            $controller = new AdminController(false); // Sin auth por ahora para debugging
            $controller->getProfesionales();
            exit;
        }
        
        // Si tiene servicio_id, es para asignación (mantener lógica existente)
        require_once __DIR__ . '/../app/Services/Database.php';
        
        try {
            $db = App\Services\Database::getInstance();
            
            if (!isset($_GET['servicio_id']) || empty($_GET['servicio_id'])) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['error' => 'servicio_id es requerido', 'get' => $_GET]);
                exit;
            }
            
            $servicioId = (int)$_GET['servicio_id'];
            $especialidad = $_GET['especialidad'] ?? null;
            
            // Obtener tipo de servicio
            $servicio = $db->selectOne("SELECT tipo FROM servicios WHERE id = ?", [$servicioId]);
            
            if (!$servicio) {
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['error' => 'Servicio no encontrado', 'servicio_id' => $servicioId]);
                exit;
            }
            
            $tipoServicio = $servicio['tipo'];
            
            // Query para profesionales con JOIN a perfiles
            // Nota: Los profesionales tienen rol = tipo_profesional (medico, enfermera, etc.)
            $query = "
                SELECT 
                    u.id,
                    u.nombre,
                    u.apellido,
                    u.email,
                    u.telefono,
                    u.ciudad,
                    u.tipo_profesional,
                    COALESCE(pp.especialidad, u.tipo_profesional) as especialidad,
                    u.puntuacion_promedio,
                    u.total_calificaciones,
                    u.servicios_completados
                FROM usuarios u
                LEFT JOIN perfiles_profesionales pp ON u.id = pp.usuario_id
                WHERE u.tipo_profesional = ?
                    AND u.estado = 'activo'
            ";
            
            $params = [$tipoServicio];
            
            // Filtro por especialidad
            if ($tipoServicio !== 'ambulancia') {
                if ($especialidad && !empty($especialidad) && strtolower($especialidad) !== 'general') {
                    // Normalizar especialidad (quitar acentos para comparación más flexible)
                    $especialidadNormalizada = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $especialidad);
                    // Extraer solo las primeras letras significativas (ej: "Pediatr" de "Pediatría")
                    $especialidadBase = preg_replace('/[^a-zA-Z]/', '', $especialidadNormalizada);
                    if (strlen($especialidadBase) > 6) {
                        $especialidadBase = substr($especialidadBase, 0, 7); // "Pediatr", "Cardiol", etc.
                    }
                    
                    // Buscar coincidencia flexible
                    $query .= " AND pp.especialidad LIKE ?";
                    $params[] = "%{$especialidadBase}%";
                    
                    error_log("Filtro especialidad: original='$especialidad', base='$especialidadBase'");
                } else {
                    // Si no hay especialidad o es "General", mostrar solo médicos generales
                    $query .= " AND (pp.especialidad LIKE '%General%' OR pp.especialidad LIKE '%Familiar%')";
                }
            }
            
            $query .= " ORDER BY u.puntuacion_promedio DESC, u.servicios_completados DESC";
            
            $profesionales = $db->select($query, $params);
            
            header('Content-Type: application/json');
            echo json_encode(['profesionales' => $profesionales, 'total' => count($profesionales)]);
        } catch (\Exception $e) {
            error_log("Error en endpoint profesionales: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
        exit;
    }

    // Crear nuevo profesional
    if ($path === '/admin/profesionales' && $method === 'POST') {
        require_once __DIR__ . '/../app/Services/Database.php';
        $db = App\Services\Database::getInstance();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validaciones
        if (empty($data['nombre']) || empty($data['apellido']) || empty($data['email']) || empty($data['password']) || empty($data['tipo_profesional'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'Campos obligatorios faltantes']);
            exit;
        }
        
        // Verificar email único
        $checkEmail = $db->select("SELECT id FROM usuarios WHERE email = ?", [$data['email']]);
        if (!empty($checkEmail)) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'El email ya está registrado']);
            exit;
        }
        
        // Hash de la contraseña
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // El rol siempre es 'profesional' para todos los tipos
        $rol = 'profesional';
        
        // Preparar query INSERT con parámetros
        $query = "INSERT INTO usuarios (
                    email, password, rol, nombre, apellido, tipo_profesional, profesion, especialidad,
                    telefono, telefono_whatsapp, direccion, direccion_consultorio, hoja_vida_url, estado
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['email'],
            $passwordHash,
            $rol,
            $data['nombre'],
            $data['apellido'],
            $data['tipo_profesional'],
            $data['profesion'] ?? '',
            $data['especialidad'] ?? '',
            $data['telefono'] ?? '',
            $data['telefono_whatsapp'] ?? '',
            $data['direccion'] ?? '',
            $data['direccion_consultorio'] ?? '',
            $data['hoja_vida_url'] ?? '',
            $data['estado'] ?? 'activo'
        ];
        
        if ($db->execute($query, $params)) {
            $profesionalId = $db->lastInsertId();
            
            // Auto-asignar servicio según tipo
            $servicio = $db->select("SELECT id FROM servicios WHERE tipo = ? LIMIT 1", [$data['tipo_profesional']]);
            if (!empty($servicio)) {
                $servicioId = $servicio[0]['id'];
                $db->execute("INSERT INTO profesional_servicios (profesional_id, servicio_id) VALUES (?, ?)", [$profesionalId, $servicioId]);
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Profesional creado exitosamente', 'id' => $profesionalId]);
        } else {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear profesional']);
        }
        exit;
    }

    // Actualizar profesional
    if (preg_match('#^/admin/profesionales/(\d+)$#', $path, $matches) && $method === 'PUT') {
        require_once __DIR__ . '/../app/Services/Database.php';
        $db = App\Services\Database::getInstance();
        
        $profesionalId = (int)$matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Construir query de actualización con parámetros preparados
        $updates = [];
        $params = [];
        
        if (isset($data['nombre'])) {
            $updates[] = "nombre = ?";
            $params[] = $data['nombre'];
        }
        if (isset($data['apellido'])) {
            $updates[] = "apellido = ?";
            $params[] = $data['apellido'];
        }
        if (isset($data['tipo_profesional'])) {
            $updates[] = "tipo_profesional = ?";
            $params[] = $data['tipo_profesional'];
        }
        if (isset($data['profesion'])) {
            $updates[] = "profesion = ?";
            $params[] = $data['profesion'];
        }
        if (isset($data['especialidad'])) {
            $updates[] = "especialidad = ?";
            $params[] = $data['especialidad'];
        }
        if (isset($data['telefono'])) {
            $updates[] = "telefono = ?";
            $params[] = $data['telefono'];
        }
        if (isset($data['telefono_whatsapp'])) {
            $updates[] = "telefono_whatsapp = ?";
            $params[] = $data['telefono_whatsapp'];
        }
        if (isset($data['direccion'])) {
            $updates[] = "direccion = ?";
            $params[] = $data['direccion'];
        }
        if (isset($data['direccion_consultorio'])) {
            $updates[] = "direccion_consultorio = ?";
            $params[] = $data['direccion_consultorio'];
        }
        if (isset($data['hoja_vida_url'])) {
            $updates[] = "hoja_vida_url = ?";
            $params[] = $data['hoja_vida_url'];
        }
        if (isset($data['estado'])) {
            $updates[] = "estado = ?";
            $params[] = $data['estado'];
        }
        
        // Si se proporciona password, actualizarlo
        if (isset($data['password']) && !empty($data['password'])) {
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            $updates[] = "password = ?";
            $params[] = $passwordHash;
        }
        
        if (empty($updates)) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'No hay datos para actualizar']);
            exit;
        }
        
        // Agregar ID al final de los parámetros
        $params[] = $profesionalId;
        
        $updateStr = implode(', ', $updates);
        $query = "UPDATE usuarios SET $updateStr WHERE id = ?";
        
        if ($db->execute($query, $params)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Profesional actualizado exitosamente']);
        } else {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar profesional']);
        }
        exit;
    }

    // Asignar profesional a solicitud
    if (preg_match('#^/admin/solicitudes/(\d+)/asignar$#', $path, $matches) && $method === 'POST') {
        $controller = new AdminController(false); // Sin auth para debugging
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
    
    // Obtener reportes de servicios completados
    if ($path === '/admin/reportes' && $method === 'GET') {
        $controller = new AdminController();
        $controller->obtenerReportes();
        exit;
    }
    
    // Ver reporte específico
    if (preg_match('#^/admin/reportes/(\d+)$#', $path, $matches) && $method === 'GET') {
        $controller = new AdminController();
        $controller->verReporte((int)$matches[1]);
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

if ($path === '/superadmin/reportes' && $method === 'GET') {
    $controller = new \App\Controllers\SuperAdminController();
    $controller->obtenerReportes();
    exit;
}

if (preg_match('#^/superadmin/reportes/(\d+)$#', $path, $matches) && $method === 'GET') {
    $solicitudId = (int)$matches[1];
    $controller = new \App\Controllers\SuperAdminController();
    $controller->verReporte($solicitudId);
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


