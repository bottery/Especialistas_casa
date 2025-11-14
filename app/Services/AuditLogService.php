<?php

namespace App\Services;

use App\Services\Database;

/**
 * Servicio de logs de auditoría para cumplimiento HIPAA
 */
class AuditLogService
{
    private $db;
    private $enabled;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $config = require __DIR__ . '/../../config/app.php';
        $this->enabled = $config['security']['audit_log'];
    }

    /**
     * Registrar acción en el log de auditoría
     */
    public function log(
        ?int $usuarioId,
        string $accion,
        ?string $tabla = null,
        ?int $registroId = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null
    ): bool {
        if (!$this->enabled) {
            return true;
        }

        try {
            $query = "INSERT INTO logs_auditoria 
                      (usuario_id, accion, tabla, registro_id, datos_anteriores, datos_nuevos, ip_address, user_agent)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $params = [
                $usuarioId,
                $accion,
                $tabla,
                $registroId,
                $datosAnteriores ? json_encode($datosAnteriores) : null,
                $datosNuevos ? json_encode($datosNuevos) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ];

            $this->db->insert($query, $params);
            return true;
        } catch (\Exception $e) {
            error_log("Error al registrar log de auditoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log de inicio de sesión
     */
    public function logLogin(int $usuarioId, bool $exitoso): void
    {
        $this->log(
            $usuarioId,
            $exitoso ? 'login_exitoso' : 'login_fallido',
            'usuarios',
            $usuarioId
        );
    }

    /**
     * Log de acceso a historial médico
     */
    public function logMedicalRecordAccess(int $usuarioId, int $pacienteId): void
    {
        $this->log(
            $usuarioId,
            'acceso_historial_medico',
            'historial_medico',
            $pacienteId
        );
    }

    /**
     * Log de modificación de datos sensibles
     */
    public function logDataModification(
        int $usuarioId,
        string $tabla,
        int $registroId,
        array $datosAnteriores,
        array $datosNuevos
    ): void {
        $this->log(
            $usuarioId,
            'modificacion_datos',
            $tabla,
            $registroId,
            $datosAnteriores,
            $datosNuevos
        );
    }

    /**
     * Obtener logs de un usuario
     */
    public function getUserLogs(int $usuarioId, int $limit = 100): array
    {
        $query = "SELECT * FROM logs_auditoria 
                  WHERE usuario_id = ? 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        return $this->db->select($query, [$usuarioId, $limit]);
    }

    /**
     * Limpiar logs antiguos (según política de retención)
     */
    public function cleanOldLogs(): int
    {
        $config = require __DIR__ . '/../../config/app.php';
        $retentionDays = $config['security']['data_retention_days'];

        $query = "DELETE FROM logs_auditoria 
                  WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->db->delete($query, [$retentionDays]);
    }
}
