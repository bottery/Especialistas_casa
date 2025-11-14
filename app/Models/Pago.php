<?php

namespace App\Models;

/**
 * Modelo Pago
 */
class Pago extends Model
{
    protected $table = 'pagos';
    protected $fillable = [
        'solicitud_id', 'usuario_id', 'metodo_pago', 'monto', 'estado',
        'referencia_pago', 'comprobante', 'aprobado_por', 'notas', 'datos_transaccion'
    ];

    /**
     * Crear nuevo pago
     */
    public function createPayment(array $data): int
    {
        if (isset($data['datos_transaccion']) && is_array($data['datos_transaccion'])) {
            $data['datos_transaccion'] = json_encode($data['datos_transaccion']);
        }
        
        return $this->create($data);
    }

    /**
     * Obtener pagos pendientes
     */
    public function getPending(): array
    {
        $query = "SELECT p.*, u.nombre, u.apellido, u.email, s.monto_total
                  FROM {$this->table} p
                  INNER JOIN usuarios u ON p.usuario_id = u.id
                  INNER JOIN solicitudes s ON p.solicitud_id = s.id
                  WHERE p.estado = 'pendiente'
                  ORDER BY p.fecha_pago DESC";
        return $this->query($query);
    }

    /**
     * Aprobar pago
     */
    public function approve(int $id, int $aprobadoPor, ?string $notas = null): bool
    {
        $updateData = [
            'estado' => 'aprobado',
            'aprobado_por' => $aprobadoPor,
            'fecha_aprobacion' => date('Y-m-d H:i:s')
        ];

        if ($notas) {
            $updateData['notas'] = $notas;
        }

        $updated = $this->update($id, $updateData);
        
        if ($updated > 0) {
            // Marcar solicitud como pagada
            $pago = $this->find($id);
            if ($pago) {
                $solicitudModel = new Solicitud();
                $solicitudModel->markAsPaid($pago['solicitud_id'], $id);
            }
        }

        return $updated > 0;
    }

    /**
     * Rechazar pago
     */
    public function reject(int $id, string $razon): bool
    {
        $updated = $this->update($id, [
            'estado' => 'rechazado',
            'notas' => $razon
        ]);
        return $updated > 0;
    }

    /**
     * Obtener pagos por usuario
     */
    public function getByUser(int $usuarioId): array
    {
        return $this->where('usuario_id', $usuarioId);
    }

    /**
     * Obtener estadísticas de pagos
     */
    public function getStats(): array
    {
        $query = "SELECT 
                    metodo_pago,
                    estado,
                    COUNT(*) as total,
                    SUM(monto) as monto_total
                  FROM {$this->table}
                  GROUP BY metodo_pago, estado";
        return $this->query($query);
    }
}
