<?php

namespace App\Models;

/**
 * Modelo Servicio
 */
class Servicio extends Model
{
    protected $table = 'servicios';
    protected $fillable = [
        'nombre', 'descripcion', 'tipo', 'modalidad', 'precio_base',
        'duracion_estimada', 'activo'
    ];

    /**
     * Obtener servicios activos
     */
    public function getActive(): array
    {
        return $this->where('activo', 1);
    }

    /**
     * Obtener servicios por tipo
     */
    public function getByType(string $tipo): array
    {
        $query = "SELECT * FROM {$this->table} WHERE tipo = ? AND activo = 1";
        return $this->query($query, [$tipo]);
    }

    /**
     * Obtener servicios por modalidad
     */
    public function getByModality(string $modalidad): array
    {
        $query = "SELECT * FROM {$this->table} WHERE modalidad = ? AND activo = 1";
        return $this->query($query, [$modalidad]);
    }

    /**
     * Buscar servicios
     */
    public function search(string $keyword): array
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE (nombre LIKE ? OR descripcion LIKE ?) 
                  AND activo = 1";
        $param = "%{$keyword}%";
        return $this->query($query, [$param, $param]);
    }
}
