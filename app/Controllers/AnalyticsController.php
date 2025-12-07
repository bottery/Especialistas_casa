<?php

namespace App\Controllers;

use App\Services\Database;

class AnalyticsController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getChartData(): void
    {
        try {
            $data = [
                'ingresos_mensuales' => $this->getIngresosMensuales(),
                'servicios_por_tipo' => $this->getServiciosPorTipo(),
                'usuarios_por_rol' => $this->getUsuariosPorRol(),
                'solicitudes_por_estado' => $this->getSolicitudesPorEstado(),
                'tendencia_semanal' => $this->getTendenciaSemanal()
            ];

            $this->sendSuccess($data);
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function getIngresosMensuales(): array
    {
        $query = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as mes,
                SUM(monto) as total
            FROM pagos 
            WHERE estado = 'aprobado' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY mes ASC
        ";
        return $this->db->select($query);
    }

    private function getServiciosPorTipo(): array
    {
        $query = "
            SELECT 
                s.nombre as tipo,
                COUNT(sol.id) as cantidad
            FROM servicios s
            LEFT JOIN solicitudes sol ON s.id = sol.servicio_id
            WHERE sol.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
            GROUP BY s.id, s.nombre
            ORDER BY cantidad DESC
            LIMIT 10
        ";
        return $this->db->select($query);
    }

    private function getUsuariosPorRol(): array
    {
        $query = "
            SELECT 
                rol,
                COUNT(*) as cantidad
            FROM usuarios
            GROUP BY rol
        ";
        return $this->db->select($query);
    }

    private function getSolicitudesPorEstado(): array
    {
        $query = "
            SELECT 
                estado,
                COUNT(*) as cantidad
            FROM solicitudes
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            GROUP BY estado
        ";
        return $this->db->select($query);
    }

    private function getTendenciaSemanal(): array
    {
        $query = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m-%d') as fecha,
                COUNT(*) as cantidad
            FROM solicitudes
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
            ORDER BY fecha ASC
        ";
        return $this->db->select($query);
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
