<?php

namespace App\Models;

/**
 * Modelo Solicitud
 */
class Solicitud extends Model
{
    protected $table = 'solicitudes';
    protected $fillable = [
        'paciente_id', 'servicio_id', 'profesional_id', 'modalidad',
        'fecha_programada', 'direccion_servicio', 'sintomas', 'observaciones',
        'documentos_adjuntos', 'estado', 'monto_total', 'monto_profesional',
        'monto_plataforma', 'pagado', 'pago_id', 'resultado', 'reporte_medico',
        'receta', 'archivos_resultado', 'cancelado_por', 'razon_cancelacion',
        // Campos específicos por tipo de servicio
        'especialidad', 'rango_horario', 'requiere_aprobacion',
        'tipo_ambulancia', 'origen', 'destino', 'tipo_emergencia', 'condicion_paciente',
        'numero_acompanantes', 'contacto_emergencia',
        'tipo_cuidado', 'intensidad_horaria', 'duracion_tipo', 'duracion_cantidad',
        'turno', 'genero_preferido', 'necesidades_especiales', 'condicion_paciente_detalle',
        'tipo_mascota', 'nombre_mascota', 'edad_mascota', 'raza_tamano',
        'motivo_veterinario', 'historial_vacunas',
        'examenes_solicitados', 'requiere_ayuno', 'preparacion_especial', 'email_resultados',
        'tipo_tratamiento', 'numero_sesiones', 'frecuencia_sesiones', 'zona_tratamiento', 'lesion_condicion',
        'tipo_sesion_psico', 'motivo_consulta_psico', 'primera_vez', 'observaciones_privadas',
        'tipo_consulta_nutri', 'objetivos_nutri', 'peso_actual', 'altura_actual',
        'condiciones_medicas', 'incluye_plan_alimenticio',
        'telefono_contacto', 'urgencia', 'metodo_pago_preferido'
    ];

    /**
     * Crear nueva solicitud
     */
    public function createRequest(array $data): int
    {
        // Calcular montos
        $comisionPlataforma = $this->getCommission();
        $data['monto_profesional'] = $data['monto_total'] * (1 - $comisionPlataforma / 100);
        $data['monto_plataforma'] = $data['monto_total'] - $data['monto_profesional'];

        return $this->create($data);
    }

    /**
     * Obtener solicitudes por paciente
     */
    public function getByPatient(int $pacienteId): array
    {
        $query = "SELECT s.*, srv.nombre as servicio_nombre, srv.tipo,
                  u.nombre as profesional_nombre, u.apellido as profesional_apellido
                  FROM {$this->table} s
                  INNER JOIN servicios srv ON s.servicio_id = srv.id
                  LEFT JOIN usuarios u ON s.profesional_id = u.id
                  WHERE s.paciente_id = ?
                  ORDER BY s.fecha_solicitud DESC";
        return $this->query($query, [$pacienteId]);
    }

    /**
     * Obtener solicitudes por profesional
     */
    public function getByProfessional(int $profesionalId, ?string $estado = null): array
    {
        $query = "SELECT s.*, srv.nombre as servicio_nombre, srv.tipo,
                  u.nombre as paciente_nombre, u.apellido as paciente_apellido,
                  u.telefono as paciente_telefono
                  FROM {$this->table} s
                  INNER JOIN servicios srv ON s.servicio_id = srv.id
                  INNER JOIN usuarios u ON s.paciente_id = u.id
                  WHERE s.profesional_id = ?";
        
        $params = [$profesionalId];
        
        if ($estado) {
            $query .= " AND s.estado = ?";
            $params[] = $estado;
        }
        
        $query .= " ORDER BY s.fecha_programada ASC";
        return $this->query($query, $params);
    }

    /**
     * Obtener solicitudes pendientes de asignación
     */
    public function getPendingAssignment(): array
    {
        $query = "SELECT s.*, srv.nombre as servicio_nombre, srv.tipo,
                  u.nombre as paciente_nombre, u.apellido as paciente_apellido
                  FROM {$this->table} s
                  INNER JOIN servicios srv ON s.servicio_id = srv.id
                  INNER JOIN usuarios u ON s.paciente_id = u.id
                  WHERE s.estado = 'pendiente' AND s.profesional_id IS NULL
                  ORDER BY s.fecha_programada ASC";
        return $this->query($query);
    }

    /**
     * Asignar profesional
     */
    public function assignProfessional(int $solicitudId, int $profesionalId): bool
    {
        $updated = $this->update($solicitudId, [
            'profesional_id' => $profesionalId,
            'estado' => 'asignado'
        ]);
        return $updated > 0;
    }

    /**
     * Confirmar solicitud
     */
    public function confirm(int $id): bool
    {
        $updated = $this->update($id, ['estado' => 'asignado']);
        return $updated > 0;
    }

    /**
     * Rechazar solicitud
     */
    public function reject(int $id, string $razon): bool
    {
        $updated = $this->update($id, [
            'estado' => 'rechazada',
            'razon_cancelacion' => $razon
        ]);
        return $updated > 0;
    }

    /**
     * Iniciar servicio
     */
    public function start(int $id): bool
    {
        $updated = $this->update($id, ['estado' => 'en_progreso']);
        return $updated > 0;
    }

    /**
     * Completar servicio
     */
    public function complete(int $id, array $data = []): bool
    {
        $updateData = [
            'estado' => 'completado',
            'fecha_completada' => date('Y-m-d H:i:s')
        ];

        if (!empty($data['resultado'])) {
            $updateData['resultado'] = $data['resultado'];
        }
        if (!empty($data['reporte_medico'])) {
            $updateData['reporte_medico'] = $data['reporte_medico'];
        }
        if (!empty($data['receta'])) {
            $updateData['receta'] = $data['receta'];
        }
        if (!empty($data['archivos_resultado'])) {
            $updateData['archivos_resultado'] = json_encode($data['archivos_resultado']);
        }

        $updated = $this->update($id, $updateData);
        return $updated > 0;
    }

    /**
     * Cancelar solicitud
     */
    public function cancel(int $id, int $canceladoPor, string $razon): bool
    {
        $updated = $this->update($id, [
            'estado' => 'cancelada',
            'cancelado_por' => $canceladoPor,
            'razon_cancelacion' => $razon
        ]);
        return $updated > 0;
    }

    /**
     * Marcar como pagado
     */
    public function markAsPaid(int $id, int $pagoId): bool
    {
        $updated = $this->update($id, [
            'pagado' => true,
            'pago_id' => $pagoId
        ]);
        return $updated > 0;
    }

    /**
     * Obtener comisión de la plataforma
     */
    private function getCommission(): float
    {
        $query = "SELECT valor FROM configuraciones WHERE clave = 'comision_plataforma' LIMIT 1";
        $result = $this->queryOne($query);
        return $result ? (float)$result['valor'] : 15.0;
    }

    /**
     * Obtener estadísticas
     */
    public function getStats(?int $profesionalId = null): array
    {
        $query = "SELECT 
                    estado,
                    COUNT(*) as total,
                    SUM(monto_total) as monto_total
                  FROM {$this->table}";
        
        $params = [];
        if ($profesionalId) {
            $query .= " WHERE profesional_id = ?";
            $params[] = $profesionalId;
        }
        
        $query .= " GROUP BY estado";
        return $this->query($query, $params);
    }
}
