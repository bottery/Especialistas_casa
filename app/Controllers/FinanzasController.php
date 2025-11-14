<?php

namespace App\Controllers;

use App\Services\Database;

class FinanzasController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Dashboard financiero
     */
    public function getDashboard(): void
    {
        try {
            $periodo = $_GET['periodo'] ?? 'mes'; // mes, semana, hoy, año
            
            $data = [
                'resumen' => $this->getResumenFinanciero($periodo),
                'pagos_recientes' => $this->getPagosRecientes(20),
                'retiros_pendientes' => $this->getRetirosPendientes(),
                'transacciones_diarias' => $this->getTransaccionesDiarias(30),
                'metodos_pago' => $this->getEstadisticasMetodosPago($periodo)
            ];

            $this->sendSuccess($data);
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Obtener resumen financiero
     */
    private function getResumenFinanciero(string $periodo): array
    {
        $where = $this->getWherePeriodo($periodo);
        
        $query = "
            SELECT 
                COUNT(*) as total_transacciones,
                SUM(CASE WHEN estado = 'aprobado' THEN monto ELSE 0 END) as ingresos_totales,
                SUM(CASE WHEN estado = 'pendiente' THEN monto ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'rechazado' THEN monto ELSE 0 END) as rechazados,
                AVG(CASE WHEN estado = 'aprobado' THEN monto ELSE NULL END) as ticket_promedio
            FROM pagos 
            WHERE $where
        ";
        
        $resultado = $this->db->select($query)[0] ?? [];
        
        // Calcular comisión de plataforma (15%)
        $ingresos = floatval($resultado['ingresos_totales'] ?? 0);
        $resultado['comision_plataforma'] = $ingresos * 0.15;
        $resultado['ingresos_netos'] = $ingresos * 0.85;
        
        return $resultado;
    }

    /**
     * Obtener pagos recientes
     */
    private function getPagosRecientes(int $limit = 20): array
    {
        $query = "
            SELECT 
                p.*,
                u.nombre as usuario_nombre,
                u.apellido as usuario_apellido,
                s.id as solicitud_id
            FROM pagos p
            LEFT JOIN usuarios u ON p.usuario_id = u.id
            LEFT JOIN solicitudes s ON p.solicitud_id = s.id
            ORDER BY p.created_at DESC
            LIMIT ?
        ";
        
        return $this->db->select($query, [$limit]);
    }

    /**
     * Obtener retiros pendientes
     */
    private function getRetirosPendientes(): array
    {
        // Para retiros, buscar en una tabla hipotética o calcular de solicitudes completadas
        $query = "
            SELECT 
                prof.id as profesional_id,
                u.nombre,
                u.apellido,
                u.email,
                COUNT(s.id) as servicios_completados,
                SUM(s.monto_profesional) as monto_pendiente
            FROM perfiles_profesionales prof
            LEFT JOIN usuarios u ON prof.usuario_id = u.id
            LEFT JOIN solicitudes s ON s.profesional_id = prof.id AND s.estado = 'completada' AND s.pagado = 1
            WHERE s.monto_profesional IS NOT NULL
            GROUP BY prof.id, u.nombre, u.apellido, u.email
            HAVING monto_pendiente > 0
            ORDER BY monto_pendiente DESC
        ";
        
        return $this->db->select($query);
    }

    /**
     * Obtener transacciones diarias
     */
    private function getTransaccionesDiarias(int $dias = 30): array
    {
        $query = "
            SELECT 
                DATE(created_at) as fecha,
                COUNT(*) as cantidad,
                SUM(CASE WHEN estado = 'aprobado' THEN monto ELSE 0 END) as total
            FROM pagos
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY fecha ASC
        ";
        
        return $this->db->select($query, [$dias]);
    }

    /**
     * Estadísticas por método de pago
     */
    private function getEstadisticasMetodosPago(string $periodo): array
    {
        $where = $this->getWherePeriodo($periodo);
        
        $query = "
            SELECT 
                metodo_pago,
                COUNT(*) as cantidad,
                SUM(monto) as total,
                AVG(monto) as promedio
            FROM pagos
            WHERE $where AND estado = 'aprobado'
            GROUP BY metodo_pago
        ";
        
        return $this->db->select($query);
    }

    /**
     * Aprobar o rechazar pago
     */
    public function actualizarEstadoPago(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;
            $estado = $input['estado'] ?? null;
            $notas = $input['notas'] ?? '';
            
            if (!$id || !$estado) {
                $this->sendError('ID y estado requeridos', 400);
                return;
            }
            
            if (!in_array($estado, ['aprobado', 'rechazado', 'pendiente', 'reembolsado'])) {
                $this->sendError('Estado inválido', 400);
                return;
            }
            
            $query = "UPDATE pagos SET estado = ?, notas = ?, fecha_aprobacion = NOW() WHERE id = ?";
            $this->db->update($query, [$estado, $notas, $id]);
            
            $this->sendSuccess(['message' => 'Estado actualizado correctamente']);
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Procesar retiro para profesional
     */
    public function procesarRetiro(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $profesional_id = $input['profesional_id'] ?? null;
            $monto = $input['monto'] ?? null;
            $metodo = $input['metodo'] ?? 'transferencia';
            
            if (!$profesional_id || !$monto) {
                $this->sendError('Profesional ID y monto requeridos', 400);
                return;
            }
            
            // Aquí se registraría el retiro en una tabla de retiros
            // Por ahora, solo marcamos las solicitudes como pagadas al profesional
            
            $query = "
                UPDATE solicitudes 
                SET monto_profesional = 0, updated_at = NOW()
                WHERE profesional_id = ? AND estado = 'completada' AND pagado = 1
            ";
            
            $afectadas = $this->db->update($query, [$profesional_id]);
            
            // Registrar en logs
            $logQuery = "
                INSERT INTO logs_auditoria (usuario_id, accion, detalles, ip_address, created_at)
                VALUES (?, 'retiro_procesado', ?, ?, NOW())
            ";
            
            $this->db->insert($logQuery, [
                $profesional_id,
                json_encode(['monto' => $monto, 'metodo' => $metodo]),
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            $this->sendSuccess([
                'message' => 'Retiro procesado correctamente',
                'solicitudes_actualizadas' => $afectadas
            ]);
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Exportar reporte financiero
     */
    public function exportarReporte(): void
    {
        try {
            $formato = $_GET['formato'] ?? 'csv'; // csv o excel
            $periodo = $_GET['periodo'] ?? 'mes';
            $where = $this->getWherePeriodo($periodo);
            
            $query = "
                SELECT 
                    p.id,
                    p.monto,
                    p.metodo_pago,
                    p.estado,
                    p.referencia_pago,
                    p.fecha_pago,
                    p.created_at,
                    u.nombre as usuario_nombre,
                    u.apellido as usuario_apellido,
                    u.email as usuario_email,
                    s.id as solicitud_id
                FROM pagos p
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN solicitudes s ON p.solicitud_id = s.id
                WHERE $where
                ORDER BY p.created_at DESC
            ";
            
            $datos = $this->db->select($query);
            
            if ($formato === 'csv') {
                $this->exportarCSV($datos, "reporte_financiero_{$periodo}_" . date('Y-m-d'));
            } else {
                $this->exportarExcel($datos, "reporte_financiero_{$periodo}_" . date('Y-m-d'));
            }
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Exportar a CSV
     */
    private function exportarCSV(array $datos, string $filename): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
        
        // Encabezados
        fputcsv($output, [
            'ID', 'Monto', 'Método Pago', 'Estado', 'Referencia', 
            'Fecha Pago', 'Fecha Registro', 'Usuario', 'Email', 'Solicitud ID'
        ]);
        
        // Datos
        foreach ($datos as $fila) {
            fputcsv($output, [
                $fila['id'],
                $fila['monto'],
                $fila['metodo_pago'],
                $fila['estado'],
                $fila['referencia_pago'] ?? 'N/A',
                $fila['fecha_pago'],
                $fila['created_at'],
                ($fila['usuario_nombre'] ?? '') . ' ' . ($fila['usuario_apellido'] ?? ''),
                $fila['usuario_email'] ?? 'N/A',
                $fila['solicitud_id'] ?? 'N/A'
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Exportar a Excel (formato CSV mejorado)
     */
    private function exportarExcel(array $datos, string $filename): void
    {
        // Por ahora, mismo formato CSV pero con extensión .xls
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}.xls\"");
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados con formato
        fputcsv($output, [
            'ID', 'Monto', 'Método Pago', 'Estado', 'Referencia', 
            'Fecha Pago', 'Fecha Registro', 'Usuario', 'Email', 'Solicitud ID'
        ], "\t");
        
        foreach ($datos as $fila) {
            fputcsv($output, [
                $fila['id'],
                $fila['monto'],
                $fila['metodo_pago'],
                $fila['estado'],
                $fila['referencia_pago'] ?? 'N/A',
                $fila['fecha_pago'],
                $fila['created_at'],
                ($fila['usuario_nombre'] ?? '') . ' ' . ($fila['usuario_apellido'] ?? ''),
                $fila['usuario_email'] ?? 'N/A',
                $fila['solicitud_id'] ?? 'N/A'
            ], "\t");
        }
        
        fclose($output);
        exit;
    }

    /**
     * Helper: WHERE clause por periodo
     */
    private function getWherePeriodo(string $periodo): string
    {
        switch ($periodo) {
            case 'hoy':
                return "DATE(created_at) = CURDATE()";
            case 'semana':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'mes':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case 'año':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            default:
                return "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }
    }

    private function sendSuccess($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
        exit;
    }

    private function sendError(string $message, int $status = 400): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}
