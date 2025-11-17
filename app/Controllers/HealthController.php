<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Services\Database;

/**
 * Health Check Controller para monitoreo del sistema
 */
class HealthController extends BaseController
{
    /**
     * Verificar salud del sistema
     */
    public function check(): void
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'php' => $this->checkPhp(),
            'dependencies' => $this->checkDependencies()
        ];

        $allHealthy = !in_array(false, array_column($checks, 'healthy'));
        
        $response = [
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'checks' => $checks
        ];

        $statusCode = $allHealthy ? 200 : 503;
        $this->sendSuccess($response, $statusCode);
    }

    /**
     * Verificar conexión a base de datos
     */
    private function checkDatabase(): array
    {
        try {
            $db = Database::getInstance();
            $result = $db->selectOne("SELECT 1 as test");
            
            return [
                'healthy' => $result && $result['test'] == 1,
                'message' => 'Database connection OK'
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar directorios de storage
     */
    private function checkStorage(): array
    {
        $directories = [
            'logs' => __DIR__ . '/../../storage/logs',
            'cache' => __DIR__ . '/../../storage/cache',
            'uploads' => __DIR__ . '/../../storage/uploads',
            'sessions' => __DIR__ . '/../../storage/sessions'
        ];

        $issues = [];
        foreach ($directories as $name => $path) {
            if (!is_dir($path)) {
                $issues[] = "$name directory does not exist";
            } elseif (!is_writable($path)) {
                $issues[] = "$name directory is not writable";
            }
        }

        return [
            'healthy' => empty($issues),
            'message' => empty($issues) ? 'All storage directories OK' : implode(', ', $issues)
        ];
    }

    /**
     * Verificar configuración de PHP
     */
    private function checkPhp(): array
    {
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl', 'openssl'];
        $missing = [];

        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        $issues = [];
        if (!empty($missing)) {
            $issues[] = 'Missing extensions: ' . implode(', ', $missing);
        }

        return [
            'healthy' => empty($issues),
            'message' => empty($issues) ? 'PHP configuration OK' : implode(', ', $issues),
            'version' => PHP_VERSION
        ];
    }

    /**
     * Verificar dependencias de Composer
     */
    private function checkDependencies(): array
    {
        $vendorPath = __DIR__ . '/../../vendor';
        
        if (!is_dir($vendorPath)) {
            return [
                'healthy' => false,
                'message' => 'Vendor directory not found. Run composer install'
            ];
        }

        $requiredPackages = [
            'firebase/php-jwt',
            'phpmailer/phpmailer',
            'guzzlehttp/guzzle',
            'vlucas/phpdotenv'
        ];

        $missing = [];
        foreach ($requiredPackages as $package) {
            $packagePath = $vendorPath . '/' . $package;
            if (!is_dir($packagePath)) {
                $missing[] = $package;
            }
        }

        return [
            'healthy' => empty($missing),
            'message' => empty($missing) ? 'All dependencies installed' : 'Missing: ' . implode(', ', $missing)
        ];
    }

    /**
     * Health check simple (solo status code)
     */
    public function ping(): void
    {
        $this->sendSuccess(['status' => 'ok', 'timestamp' => time()]);
    }
}
