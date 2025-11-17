<?php

namespace App\Core;

/**
 * Manejador global de errores y excepciones
 */
class ErrorHandler
{
    private static $logPath;
    private static $debug;

    /**
     * Inicializar manejador de errores
     */
    public static function init(): void
    {
        self::$logPath = __DIR__ . '/../../storage/logs/';
        $config = require __DIR__ . '/../../config/app.php';
        self::$debug = $config['debug'];

        // Registrar manejadores
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Manejar errores PHP
     */
    public static function handleError($severity, $message, $file, $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Manejar excepciones no capturadas
     */
    public static function handleException(\Throwable $e): void
    {
        self::logException($e);

        if (self::$debug) {
            self::sendDebugResponse($e);
        } else {
            self::sendProductionResponse();
        }
    }

    /**
     * Manejar errores fatales en shutdown
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::logError('Fatal Error', $error);
            
            if (!headers_sent()) {
                if (self::$debug) {
                    http_response_code(500);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'error' => 'Fatal Error',
                        'message' => $error['message'],
                        'file' => $error['file'],
                        'line' => $error['line']
                    ]);
                } else {
                    self::sendProductionResponse();
                }
            }
        }
    }

    /**
     * Registrar excepción en logs
     */
    private static function logException(\Throwable $e): void
    {
        $message = sprintf(
            "[%s] %s: %s in %s:%d\nStack trace:\n%s\n",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        self::writeLog('error', $message);
    }

    /**
     * Registrar error en logs
     */
    private static function logError(string $type, array $error): void
    {
        $message = sprintf(
            "[%s] %s: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            $type,
            $error['message'],
            $error['file'],
            $error['line']
        );

        self::writeLog('error', $message);
    }

    /**
     * Escribir en archivo de log
     */
    private static function writeLog(string $level, string $message): void
    {
        $filename = self::$logPath . $level . '-' . date('Y-m-d') . '.log';
        
        // Crear directorio si no existe
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }

        file_put_contents($filename, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Enviar respuesta de debug (desarrollo)
     */
    private static function sendDebugResponse(\Throwable $e): void
    {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
        }

        echo json_encode([
            'success' => false,
            'error' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ], JSON_PRETTY_PRINT);
        
        exit;
    }

    /**
     * Enviar respuesta de producción (genérica)
     */
    private static function sendProductionResponse(): void
    {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
        }

        echo json_encode([
            'success' => false,
            'message' => 'Ha ocurrido un error interno. Por favor, intente más tarde.'
        ]);
        
        exit;
    }

    /**
     * Log personalizado (para uso en aplicación)
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = sprintf(
            "[%s] [%s] %s%s",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $contextStr
        );

        self::writeLog($level, $logMessage);
    }
}
