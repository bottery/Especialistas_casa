<?php

namespace App\Core;

/**
 * Rate Limiter para protección contra fuerza bruta
 */
class RateLimiter
{
    private string $storageDir;
    private int $maxAttempts;
    private int $decayMinutes;

    public function __construct(int $maxAttempts = 5, int $decayMinutes = 15)
    {
        $this->storageDir = __DIR__ . '/../../storage/cache/rate-limits/';
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
        
        // Crear directorio si no existe
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * Verificar si se excedió el límite
     */
    public function tooManyAttempts(string $key): bool
    {
        $attempts = $this->getAttempts($key);
        return $attempts >= $this->maxAttempts;
    }

    /**
     * Incrementar intentos
     */
    public function hit(string $key, int $decayMinutes = null): int
    {
        $decayMinutes = $decayMinutes ?? $this->decayMinutes;
        $file = $this->getFilePath($key);
        
        $data = $this->getData($file);
        $data['attempts'] = ($data['attempts'] ?? 0) + 1;
        $data['expires_at'] = time() + ($decayMinutes * 60);
        
        file_put_contents($file, json_encode($data), LOCK_EX);
        
        return $data['attempts'];
    }

    /**
     * Obtener intentos actuales
     */
    public function getAttempts(string $key): int
    {
        $file = $this->getFilePath($key);
        $data = $this->getData($file);
        
        if (isset($data['expires_at']) && $data['expires_at'] < time()) {
            $this->clear($key);
            return 0;
        }
        
        return $data['attempts'] ?? 0;
    }

    /**
     * Obtener segundos restantes hasta disponible
     */
    public function availableIn(string $key): int
    {
        $file = $this->getFilePath($key);
        $data = $this->getData($file);
        
        if (!isset($data['expires_at'])) {
            return 0;
        }
        
        $remaining = $data['expires_at'] - time();
        return max(0, $remaining);
    }

    /**
     * Limpiar intentos de una clave
     */
    public function clear(string $key): void
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Resetear todos los límites (limpieza)
     */
    public function clearAll(): void
    {
        $files = glob($this->storageDir . '*.json');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Limpiar límites expirados
     */
    public function cleanExpired(): void
    {
        $files = glob($this->storageDir . '*.json');
        $now = time();
        
        foreach ($files as $file) {
            $data = $this->getData($file);
            if (isset($data['expires_at']) && $data['expires_at'] < $now) {
                unlink($file);
            }
        }
    }

    /**
     * Obtener ruta del archivo para una clave
     */
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        return $this->storageDir . $hash . '.json';
    }

    /**
     * Obtener datos del archivo
     */
    private function getData(string $file): array
    {
        if (!file_exists($file)) {
            return [];
        }
        
        $content = file_get_contents($file);
        return json_decode($content, true) ?? [];
    }

    /**
     * Generar clave basada en IP y acción
     */
    public static function key(string $action, ?string $identifier = null): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $identifier = $identifier ?? $ip;
        return "rate_limit:{$action}:{$identifier}";
    }

    /**
     * Verificar y aplicar rate limit
     */
    public function attempt(string $key): bool
    {
        if ($this->tooManyAttempts($key)) {
            $this->sendRateLimitResponse($key);
            return false;
        }
        
        $this->hit($key);
        return true;
    }

    /**
     * Enviar respuesta de rate limit excedido
     */
    private function sendRateLimitResponse(string $key): void
    {
        $retryAfter = $this->availableIn($key);
        
        http_response_code(429);
        header('Content-Type: application/json');
        header("Retry-After: $retryAfter");
        
        echo json_encode([
            'success' => false,
            'message' => 'Demasiados intentos. Por favor, intente de nuevo más tarde.',
            'retry_after' => $retryAfter
        ]);
        
        exit;
    }
}
