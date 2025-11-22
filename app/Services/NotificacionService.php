<?php

namespace App\Services;

use App\Services\Database;
use PDO;

class NotificacionService
{
    /**
     * Crear una notificación
     */
    public static function crear(
        int $usuarioId,
        string $tipo,
        string $titulo,
        string $mensaje,
        ?int $solicitudId = null,
        ?array $datosAdicionales = null
    ): ?int {
        $db = Database::getInstance();
        
        $query = "
            INSERT INTO notificaciones 
            (usuario_id, tipo, titulo, mensaje, solicitud_id, datos_adicionales)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        
        try {
            $notificacionId = $db->insert($query, [
                $usuarioId,
                $tipo,
                $titulo,
                $mensaje,
                $solicitudId,
                $datosAdicionales ? json_encode($datosAdicionales) : null
            ]);
            
            // Enviar notificación push si está habilitado
            self::enviarPush($usuarioId, $titulo, $mensaje, $notificacionId);
            
            return $notificacionId;
        } catch (\Exception $e) {
            error_log("Error al crear notificación: " . $e->getMessage());
            return null;
        }
        
        return null;
    }
    
    /**
     * Crear notificación desde plantilla
     */
    public static function crearDesdePlantilla(
        int $usuarioId,
        string $codigoPlantilla,
        array $variables = [],
        ?int $solicitudId = null,
        string $rolDestinatario = 'paciente'
    ): ?int {
        $db = Database::getInstance();
        
        $plantilla = $db->selectOne(
            "SELECT * FROM plantillas_notificaciones WHERE codigo = ? AND activo = 1",
            [$codigoPlantilla]
        );
        
        if (!$plantilla) {
            error_log("Plantilla no encontrada: {$codigoPlantilla}");
            return null;
        }
        
        $tituloField = "titulo_{$rolDestinatario}";
        $mensajeField = "mensaje_{$rolDestinatario}";
        
        $titulo = $plantilla[$tituloField] ?? $plantilla['titulo_paciente'];
        $mensaje = $plantilla[$mensajeField] ?? $plantilla['mensaje_paciente'];
        
        // Reemplazar variables en el texto
        foreach ($variables as $key => $value) {
            $titulo = str_replace("{{{$key}}}", $value, $titulo);
            $mensaje = str_replace("{{{$key}}}", $value, $mensaje);
        }
        
        return self::crear(
            $usuarioId,
            $plantilla['tipo'],
            $titulo,
            $mensaje,
            $solicitudId,
            $variables
        );
    }
    
    /**
     * Marcar notificación como leída
     */
    public static function marcarComoLeida(int $notificacionId): bool
    {
        $db = Database::getInstance();
        
        return $db->query(
            "UPDATE notificaciones SET leida = 1, fecha_leida = NOW() WHERE id = ?",
            [$notificacionId]
        );
    }
    
    /**
     * Marcar todas las notificaciones de un usuario como leídas
     */
    public static function marcarTodasComoLeidas(int $usuarioId): bool
    {
        $db = Database::getInstance();
        
        return $db->query(
            "UPDATE notificaciones SET leida = 1, fecha_leida = NOW() WHERE usuario_id = ? AND leida = 0",
            [$usuarioId]
        );
    }
    
    /**
     * Obtener notificaciones no leídas de un usuario
     */
    public static function getNoLeidas(int $usuarioId, int $limite = 10): array
    {
        $db = Database::getInstance();
        
        return $db->fetchAll(
            "SELECT * FROM notificaciones 
             WHERE usuario_id = ? AND leida = 0
             ORDER BY created_at DESC
             LIMIT ?",
            [$usuarioId, $limite]
        );
    }
    
    /**
     * Obtener todas las notificaciones de un usuario
     */
    public static function getAll(int $usuarioId, int $limite = 50, int $offset = 0): array
    {
        $db = Database::getInstance();
        
        return $db->fetchAll(
            "SELECT * FROM notificaciones 
             WHERE usuario_id = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?",
            [$usuarioId, $limite, $offset]
        );
    }
    
    /**
     * Contar notificaciones no leídas
     */
    public static function contarNoLeidas(int $usuarioId): int
    {
        $db = Database::getInstance();
        
        $result = $db->selectOne(
            "SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ? AND leida = 0",
            [$usuarioId]
        );
        
        return (int)($result['total'] ?? 0);
    }
    
    /**
     * Enviar notificación push (OneSignal)
     */
    private static function enviarPush(int $usuarioId, string $titulo, string $mensaje, int $notificacionId): void
    {
        $db = Database::getInstance();
        
        // Obtener token y preferencias del usuario
        $usuario = $db->selectOne(
            "SELECT token_dispositivo, notificaciones_push FROM usuarios WHERE id = ?",
            [$usuarioId]
        );
        
        if (!$usuario || !$usuario['notificaciones_push'] || !$usuario['token_dispositivo']) {
            return;
        }
        
        try {
            OneSignalService::enviarNotificacion(
                [$usuario['token_dispositivo']],
                $titulo,
                $mensaje,
                ['notificacion_id' => $notificacionId]
            );
        } catch (\Exception $e) {
            error_log("Error enviando push: " . $e->getMessage());
        }
    }
    
    /**
     * Calcular tiempo estimado de llegada
     */
    public static function calcularTiempoEstimadoLlegada(
        string $tipoProfesional,
        float $distanciaKm
    ): array {
        $db = Database::getInstance();
        
        $config = $db->selectOne(
            "SELECT * FROM configuracion_tiempos WHERE tipo_profesional = ?",
            [$tipoProfesional]
        );
        
        if (!$config) {
            // Valores por defecto
            $config = [
                'tiempo_preparacion_min' => 15,
                'velocidad_desplazamiento_km_h' => 40,
                'tiempo_buffer_min' => 10
            ];
        }
        
        $tiempoPreparacion = (int)$config['tiempo_preparacion_min'];
        $velocidad = (float)$config['velocidad_desplazamiento_km_h'];
        $buffer = (int)$config['tiempo_buffer_min'];
        
        $tiempoDesplazamiento = ($distanciaKm / $velocidad) * 60; // en minutos
        $tiempoTotal = $tiempoPreparacion + $tiempoDesplazamiento + $buffer;
        
        return [
            'tiempo_preparacion' => $tiempoPreparacion,
            'tiempo_desplazamiento' => round($tiempoDesplazamiento),
            'tiempo_buffer' => $buffer,
            'tiempo_total_minutos' => round($tiempoTotal),
            'distancia_km' => $distanciaKm
        ];
    }
}
