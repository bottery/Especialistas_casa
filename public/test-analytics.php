<?php
/**
 * Test de Analytics - Para debug
 */
require_once __DIR__ . '/../bootstrap.php';

use App\Services\Database;

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    
    // Ingresos mensuales
    $ingresos = $db->select("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as mes,
            SUM(monto) as total
        FROM pagos 
        WHERE estado = 'aprobado' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY mes ASC
    ");
    
    // Servicios por tipo
    $servicios = $db->select("
        SELECT 
            s.nombre as tipo,
            COUNT(sol.id) as cantidad
        FROM servicios s
        LEFT JOIN solicitudes sol ON s.id = sol.servicio_id
        WHERE sol.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
        GROUP BY s.id, s.nombre
        ORDER BY cantidad DESC
        LIMIT 10
    ");
    
    // Usuarios por rol
    $usuarios = $db->select("
        SELECT 
            rol,
            COUNT(*) as cantidad
        FROM usuarios
        GROUP BY rol
    ");
    
    // Solicitudes por estado
    $solicitudes = $db->select("
        SELECT 
            estado,
            COUNT(*) as cantidad
        FROM solicitudes
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        GROUP BY estado
    ");
    
    // Tendencia semanal
    $tendencia = $db->select("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m-%d') as fecha,
            COUNT(*) as cantidad
        FROM solicitudes
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
        ORDER BY fecha ASC
    ");
    
    echo json_encode([
        'success' => true,
        'data' => [
            'ingresos_mensuales' => $ingresos,
            'servicios_por_tipo' => $servicios,
            'usuarios_por_rol' => $usuarios,
            'solicitudes_por_estado' => $solicitudes,
            'tendencia_semanal' => $tendencia
        ],
        'counts' => [
            'ingresos' => count($ingresos),
            'servicios' => count($servicios),
            'usuarios' => count($usuarios),
            'solicitudes' => count($solicitudes),
            'tendencia' => count($tendencia)
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
