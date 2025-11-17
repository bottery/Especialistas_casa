<?php

namespace App\Core;

/**
 * Controlador base con métodos compartidos
 */
abstract class BaseController
{
    /**
     * Enviar respuesta JSON exitosa
     */
    protected function sendSuccess($data = [], int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        
        // Si $data ya tiene 'success', enviarlo directamente
        if (is_array($data) && isset($data['message'])) {
            echo json_encode(array_merge(['success' => true], $data));
        } else {
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        }
        exit;
    }

    /**
     * Enviar respuesta JSON de error
     */
    protected function sendError(string $message, int $status = 400, $errors = null): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        echo json_encode($response);
        exit;
    }

    /**
     * Obtener datos JSON del body de la request
     */
    protected function getJsonInput(): ?array
    {
        $input = file_get_contents('php://input');
        return $input ? json_decode($input, true) : null;
    }

    /**
     * Validar campos requeridos
     */
    protected function validateRequired(array $data, array $required): bool
    {
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $this->sendError(
                "Campos requeridos faltantes: " . implode(', ', $missing),
                400,
                ['missing_fields' => $missing]
            );
            return false;
        }
        
        return true;
    }

    /**
     * Sanitizar string
     */
    protected function sanitizeString(?string $str): string
    {
        if ($str === null) {
            return '';
        }
        return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validar email
     */
    protected function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Obtener parámetro GET
     */
    protected function getParam(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Obtener parámetro POST
     */
    protected function getPostParam(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Verificar si la request es AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Log de error
     */
    protected function logError(string $message, array $context = []): void
    {
        $contextStr = !empty($context) ? json_encode($context) : '';
        error_log("[" . get_class($this) . "] $message $contextStr");
    }

    /**
     * Redireccionar
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: $url");
        exit;
    }
}
