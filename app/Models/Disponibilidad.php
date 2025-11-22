<?php

namespace App\Models;

use App\Services\Database;
use PDO;
use DateTime;
use DateInterval;

class Disponibilidad extends Model
{
    protected $table = 'disponibilidad_profesional';
    
    /**
     * Obtener disponibilidad semanal de un profesional
     */
    public static function getDisponibilidadSemanal(int $profesionalId): array
    {
        $db = Database::getInstance();
        
        return $db->fetchAll(
            "SELECT * FROM disponibilidad_profesional 
             WHERE profesional_id = ? AND activo = 1
             ORDER BY 
                FIELD(dia_semana, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'),
                hora_inicio",
            [$profesionalId]
        );
    }
    
    /**
     * Verificar si un profesional está disponible en un día/hora específico
     */
    public static function estaDisponible(int $profesionalId, DateTime $fechaHora): bool
    {
        $db = Database::getInstance();
        
        $diaSemana = self::getDiaSemanaNombre($fechaHora->format('N'));
        $hora = $fechaHora->format('H:i:s');
        
        // Verificar si tiene disponibilidad general en ese día y hora
        $disponibilidadGeneral = $db->fetch(
            "SELECT id FROM disponibilidad_profesional 
             WHERE profesional_id = ? 
             AND dia_semana = ? 
             AND hora_inicio <= ? 
             AND hora_fin >= ?
             AND activo = 1",
            [$profesionalId, $diaSemana, $hora, $hora]
        );
        
        if (!$disponibilidadGeneral) {
            return false;
        }
        
        // Verificar que no esté en un bloque no disponible
        $bloqueado = $db->fetch(
            "SELECT id FROM bloques_no_disponibles 
             WHERE profesional_id = ? 
             AND fecha_inicio <= ? 
             AND fecha_fin >= ?",
            [$profesionalId, $fechaHora->format('Y-m-d H:i:s'), $fechaHora->format('Y-m-d H:i:s')]
        );
        
        return !$bloqueado;
    }
    
    /**
     * Obtener profesionales disponibles en una fecha/hora específica
     */
    public static function getProfesionalesDisponibles(
        DateTime $fechaHora,
        int $especialidadId = null,
        int $duracionMinutos = 60
    ): array {
        $db = Database::getInstance();
        
        $diaSemana = self::getDiaSemanaNombre($fechaHora->format('N'));
        $hora = $fechaHora->format('H:i:s');
        $fechaHoraFin = (clone $fechaHora)->add(new DateInterval("PT{$duracionMinutos}M"));
        
        $query = "
            SELECT DISTINCT
                u.id,
                u.nombre,
                u.apellidos,
                u.tipo_profesional,
                u.puntuacion_promedio,
                u.total_calificaciones,
                u.disponible_ahora,
                u.tiempo_respuesta_promedio,
                dp.hora_inicio,
                dp.hora_fin
            FROM usuarios u
            INNER JOIN disponibilidad_profesional dp ON u.id = dp.profesional_id
        ";
        
        $params = [];
        
        // Filtrar por especialidad si se proporciona
        if ($especialidadId) {
            $query .= " INNER JOIN profesional_especialidades pe ON u.id = pe.profesional_id";
        }
        
        $query .= "
            WHERE u.rol = 'profesional'
                AND u.estado = 'activo'
                AND dp.dia_semana = ?
                AND dp.hora_inicio <= ?
                AND dp.hora_fin >= ?
                AND dp.activo = 1
        ";
        
        $params[] = $diaSemana;
        $params[] = $hora;
        $params[] = $fechaHoraFin->format('H:i:s');
        
        if ($especialidadId) {
            $query .= " AND pe.especialidad_id = ?";
            $params[] = $especialidadId;
        }
        
        // Excluir profesionales con bloqueos en ese horario
        $query .= "
            AND NOT EXISTS (
                SELECT 1 FROM bloques_no_disponibles bnd
                WHERE bnd.profesional_id = u.id
                AND bnd.fecha_inicio <= ?
                AND bnd.fecha_fin >= ?
            )
        ";
        
        $params[] = $fechaHoraFin->format('Y-m-d H:i:s');
        $params[] = $fechaHora->format('Y-m-d H:i:s');
        
        $query .= " ORDER BY u.disponible_ahora DESC, u.puntuacion_promedio DESC";
        
        return $db->fetchAll($query, $params);
    }
    
    /**
     * Obtener próximos horarios disponibles de un profesional
     */
    public static function getProximosHorariosDisponibles(
        int $profesionalId,
        int $duracionMinutos = 60,
        int $diasAdelante = 7
    ): array {
        $db = Database::getInstance();
        $horarios = [];
        
        $disponibilidadSemanal = self::getDisponibilidadSemanal($profesionalId);
        $bloqueos = $db->fetchAll(
            "SELECT fecha_inicio, fecha_fin FROM bloques_no_disponibles 
             WHERE profesional_id = ? 
             AND fecha_fin >= NOW()
             ORDER BY fecha_inicio",
            [$profesionalId]
        );
        
        $fechaActual = new DateTime();
        $fechaLimite = (clone $fechaActual)->add(new DateInterval("P{$diasAdelante}D"));
        
        while ($fechaActual < $fechaLimite) {
            $diaSemana = self::getDiaSemanaNombre($fechaActual->format('N'));
            
            // Buscar disponibilidad para este día
            foreach ($disponibilidadSemanal as $disp) {
                if ($disp['dia_semana'] === $diaSemana) {
                    $horaInicio = new DateTime($fechaActual->format('Y-m-d') . ' ' . $disp['hora_inicio']);
                    $horaFin = new DateTime($fechaActual->format('Y-m-d') . ' ' . $disp['hora_fin']);
                    
                    // Generar bloques de tiempo
                    $horaActual = clone $horaInicio;
                    while ($horaActual < $horaFin) {
                        $horaFinBloque = (clone $horaActual)->add(new DateInterval("PT{$duracionMinutos}M"));
                        
                        if ($horaFinBloque <= $horaFin) {
                            // Verificar que no esté bloqueado
                            $bloqueado = false;
                            foreach ($bloqueos as $bloqueo) {
                                $inicioBloqueo = new DateTime($bloqueo['fecha_inicio']);
                                $finBloqueo = new DateTime($bloqueo['fecha_fin']);
                                
                                if ($horaActual < $finBloqueo && $horaFinBloque > $inicioBloqueo) {
                                    $bloqueado = true;
                                    break;
                                }
                            }
                            
                            if (!$bloqueado && $horaActual > new DateTime()) {
                                $horarios[] = [
                                    'fecha' => $horaActual->format('Y-m-d'),
                                    'hora_inicio' => $horaActual->format('H:i'),
                                    'hora_fin' => $horaFinBloque->format('H:i'),
                                    'disponible' => true
                                ];
                            }
                        }
                        
                        $horaActual->add(new DateInterval("PT{$duracionMinutos}M"));
                    }
                }
            }
            
            $fechaActual->add(new DateInterval('P1D'));
        }
        
        return $horarios;
    }
    
    /**
     * Guardar disponibilidad semanal de un profesional
     */
    public static function guardarDisponibilidadSemanal(int $profesionalId, array $horarios): bool
    {
        $db = Database::getInstance();
        
        // Eliminar disponibilidad existente
        $db->query("DELETE FROM disponibilidad_profesional WHERE profesional_id = ?", [$profesionalId]);
        
        // Insertar nueva disponibilidad
        foreach ($horarios as $horario) {
            $db->query(
                "INSERT INTO disponibilidad_profesional 
                 (profesional_id, dia_semana, hora_inicio, hora_fin, activo) 
                 VALUES (?, ?, ?, ?, ?)",
                [
                    $profesionalId,
                    $horario['dia_semana'],
                    $horario['hora_inicio'],
                    $horario['hora_fin'],
                    $horario['activo'] ?? true
                ]
            );
        }
        
        return true;
    }
    
    /**
     * Crear bloque no disponible (vacaciones, ausencia, etc.)
     */
    public static function crearBloqueoNoDisponible(
        int $profesionalId,
        DateTime $fechaInicio,
        DateTime $fechaFin,
        string $motivo = null,
        string $tipo = 'otro'
    ): ?int {
        $db = Database::getInstance();
        
        $success = $db->query(
            "INSERT INTO bloques_no_disponibles 
             (profesional_id, fecha_inicio, fecha_fin, motivo, tipo)
             VALUES (?, ?, ?, ?, ?)",
            [
                $profesionalId,
                $fechaInicio->format('Y-m-d H:i:s'),
                $fechaFin->format('Y-m-d H:i:s'),
                $motivo,
                $tipo
            ]
        );
        
        return $success ? $db->lastInsertId() : null;
    }
    
    /**
     * Actualizar estado de disponibilidad inmediata
     */
    public static function actualizarDisponibilidadInmediata(int $profesionalId, bool $disponible): bool
    {
        $db = Database::getInstance();
        
        return $db->query(
            "UPDATE usuarios 
             SET disponible_ahora = ?, ultima_actividad = NOW() 
             WHERE id = ?",
            [$disponible ? 1 : 0, $profesionalId]
        );
    }
    
    /**
     * Convertir número de día a nombre en español
     */
    private static function getDiaSemanaNombre(int $numDia): string
    {
        $dias = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            7 => 'domingo'
        ];
        
        return $dias[$numDia] ?? 'lunes';
    }
}
