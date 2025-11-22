<?php

namespace App\Models;

use App\Services\Database;
use PDO;

class Especialidad extends Model
{
    protected $table = 'especialidades';
    
    /**
     * Obtener todas las especialidades activas
     */
    public static function getActivas(string $tipoProfesional = null): array
    {
        $db = Database::getInstance();
        
        $query = "SELECT * FROM especialidades WHERE activo = 1";
        $params = [];
        
        if ($tipoProfesional) {
            $query .= " AND tipo_profesional = ?";
            $params[] = $tipoProfesional;
        }
        
        $query .= " ORDER BY orden ASC, nombre ASC";
        
        return $db->fetchAll($query, $params);
    }
    
    /**
     * Obtener especialidades por tipo profesional
     */
    public static function getPorTipo(string $tipoProfesional): array
    {
        return self::getActivas($tipoProfesional);
    }
    
    /**
     * Obtener una especialidad por ID
     */
    public static function getById(int $id): ?array
    {
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT * FROM especialidades WHERE id = ?",
            [$id]
        );
        
        return $result ?: null;
    }
    
    /**
     * Obtener especialidades de un profesional
     */
    public static function getByProfesional(int $profesionalId): array
    {
        $db = Database::getInstance();
        
        $query = "
            SELECT 
                e.*,
                pe.es_principal,
                pe.años_experiencia,
                pe.certificaciones
            FROM especialidades e
            INNER JOIN profesional_especialidades pe ON e.id = pe.especialidad_id
            WHERE pe.profesional_id = ?
            ORDER BY pe.es_principal DESC, e.nombre ASC
        ";
        
        return $db->fetchAll($query, [$profesionalId]);
    }
    
    /**
     * Obtener la especialidad principal de un profesional
     */
    public static function getPrincipalByProfesional(int $profesionalId): ?array
    {
        $db = Database::getInstance();
        
        $query = "
            SELECT 
                e.*,
                pe.años_experiencia,
                pe.certificaciones
            FROM especialidades e
            INNER JOIN profesional_especialidades pe ON e.id = pe.especialidad_id
            WHERE pe.profesional_id = ? AND pe.es_principal = 1
            LIMIT 1
        ";
        
        $result = $db->fetch($query, [$profesionalId]);
        return $result ?: null;
    }
    
    /**
     * Asignar especialidad a un profesional
     */
    public static function asignarAProfesional(
        int $profesionalId, 
        int $especialidadId, 
        bool $esPrincipal = false,
        int $añosExperiencia = 0,
        string $certificaciones = null
    ): bool {
        $db = Database::getInstance();
        
        // Si es principal, quitar flag principal de otras especialidades
        if ($esPrincipal) {
            $db->query(
                "UPDATE profesional_especialidades SET es_principal = 0 WHERE profesional_id = ?",
                [$profesionalId]
            );
        }
        
        $query = "
            INSERT INTO profesional_especialidades 
            (profesional_id, especialidad_id, es_principal, años_experiencia, certificaciones)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                es_principal = VALUES(es_principal),
                años_experiencia = VALUES(años_experiencia),
                certificaciones = VALUES(certificaciones)
        ";
        
        return $db->query($query, [
            $profesionalId,
            $especialidadId,
            $esPrincipal ? 1 : 0,
            $añosExperiencia,
            $certificaciones
        ]);
    }
    
    /**
     * Remover especialidad de un profesional
     */
    public static function removerDeProfesional(int $profesionalId, int $especialidadId): bool
    {
        $db = Database::getInstance();
        
        return $db->query(
            "DELETE FROM profesional_especialidades WHERE profesional_id = ? AND especialidad_id = ?",
            [$profesionalId, $especialidadId]
        );
    }
    
    /**
     * Obtener profesionales con una especialidad específica
     */
    public static function getProfesionales(int $especialidadId, bool $soloActivos = true): array
    {
        $db = Database::getInstance();
        
        $query = "
            SELECT 
                u.*,
                pe.es_principal,
                pe.años_experiencia,
                pe.certificaciones
            FROM usuarios u
            INNER JOIN profesional_especialidades pe ON u.id = pe.profesional_id
            WHERE pe.especialidad_id = ?
                AND u.rol = 'profesional'
        ";
        
        $params = [$especialidadId];
        
        if ($soloActivos) {
            $query .= " AND u.estado = 'activo'";
        }
        
        $query .= " ORDER BY pe.es_principal DESC, u.puntuacion_promedio DESC";
        
        return $db->fetchAll($query, $params);
    }
    
    /**
     * Crear nueva especialidad
     */
    public static function crear(array $datos): ?int
    {
        $db = Database::getInstance();
        
        $query = "
            INSERT INTO especialidades 
            (nombre, tipo_profesional, descripcion, icono, activo, orden)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        
        $success = $db->query($query, [
            $datos['nombre'],
            $datos['tipo_profesional'],
            $datos['descripcion'] ?? null,
            $datos['icono'] ?? '🩺',
            $datos['activo'] ?? 1,
            $datos['orden'] ?? 0
        ]);
        
        return $success ? $db->lastInsertId() : null;
    }
    
    /**
     * Actualizar especialidad
     */
    public static function actualizar(int $id, array $datos): bool
    {
        $db = Database::getInstance();
        
        $query = "
            UPDATE especialidades SET
                nombre = ?,
                tipo_profesional = ?,
                descripcion = ?,
                icono = ?,
                activo = ?,
                orden = ?
            WHERE id = ?
        ";
        
        return $db->query($query, [
            $datos['nombre'],
            $datos['tipo_profesional'],
            $datos['descripcion'] ?? null,
            $datos['icono'] ?? '🩺',
            $datos['activo'] ?? 1,
            $datos['orden'] ?? 0,
            $id
        ]);
    }
    
    /**
     * Eliminar especialidad (soft delete)
     */
    public static function eliminar(int $id): bool
    {
        $db = Database::getInstance();
        
        return $db->query(
            "UPDATE especialidades SET activo = 0 WHERE id = ?",
            [$id]
        );
    }
    
    /**
     * Estadísticas de especialidades
     */
    public static function getEstadisticas(): array
    {
        $db = Database::getInstance();
        
        $query = "
            SELECT 
                e.id,
                e.nombre,
                e.tipo_profesional,
                e.icono,
                COUNT(DISTINCT pe.profesional_id) as total_profesionales,
                COUNT(DISTINCT s.id) as total_solicitudes,
                COALESCE(AVG(s.calificacion_paciente), 0) as calificacion_promedio
            FROM especialidades e
            LEFT JOIN profesional_especialidades pe ON e.id = pe.especialidad_id
            LEFT JOIN solicitudes s ON e.id = s.especialidad_id
            WHERE e.activo = 1
            GROUP BY e.id, e.nombre, e.tipo_profesional, e.icono
            ORDER BY total_solicitudes DESC
        ";
        
        return $db->fetchAll($query);
    }
}
