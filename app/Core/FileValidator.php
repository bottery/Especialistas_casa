<?php

namespace App\Core;

/**
 * Validador de archivos con verificación profunda de MIME type
 */
class FileValidator
{
    private array $allowedExtensions = [];
    private array $allowedMimeTypes = [];
    private int $maxFileSize;
    private array $errors = [];

    /**
     * Extensiones y MIME types permitidos por defecto
     */
    private const MIME_TYPES = [
        'jpg' => ['image/jpeg', 'image/pjpeg'],
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'txt' => ['text/plain'],
        'csv' => ['text/csv', 'text/plain'],
        'zip' => ['application/zip', 'application/x-zip-compressed']
    ];

    public function __construct(?array $allowedExtensions = null, ?int $maxFileSize = null)
    {
        $config = require __DIR__ . '/../../config/app.php';
        
        $this->allowedExtensions = $allowedExtensions ?? $config['uploads']['allowed_types'];
        $this->maxFileSize = $maxFileSize ?? $config['uploads']['max_size'];
        
        // Construir lista de MIME types permitidos
        foreach ($this->allowedExtensions as $ext) {
            if (isset(self::MIME_TYPES[$ext])) {
                $this->allowedMimeTypes = array_merge(
                    $this->allowedMimeTypes,
                    self::MIME_TYPES[$ext]
                );
            }
        }
        
        $this->allowedMimeTypes = array_unique($this->allowedMimeTypes);
    }

    /**
     * Validar archivo subido
     */
    public function validate(array $file): bool
    {
        $this->errors = [];

        // Verificar que no haya errores en la subida
        if (!isset($file['error']) || is_array($file['error'])) {
            $this->errors[] = 'Parámetros de archivo inválidos';
            return false;
        }

        // Verificar código de error
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->errors[] = 'El archivo excede el tamaño máximo permitido';
                return false;
            case UPLOAD_ERR_PARTIAL:
                $this->errors[] = 'El archivo solo se subió parcialmente';
                return false;
            case UPLOAD_ERR_NO_FILE:
                $this->errors[] = 'No se subió ningún archivo';
                return false;
            default:
                $this->errors[] = 'Error desconocido al subir el archivo';
                return false;
        }

        // Verificar tamaño
        if ($file['size'] > $this->maxFileSize) {
            $maxSizeMB = round($this->maxFileSize / 1048576, 2);
            $this->errors[] = "El archivo excede el tamaño máximo de {$maxSizeMB}MB";
            return false;
        }

        // Verificar extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            $this->errors[] = "Extensión de archivo no permitida. Permitidas: " . 
                             implode(', ', $this->allowedExtensions);
            return false;
        }

        // Verificar MIME type real del archivo
        if (!$this->verifyMimeType($file['tmp_name'])) {
            $this->errors[] = 'Tipo de archivo no válido o no coincide con la extensión';
            return false;
        }

        // Verificar que el archivo existe y es válido
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->errors[] = 'Error de seguridad: archivo inválido';
            return false;
        }

        return true;
    }

    /**
     * Verificar MIME type real usando finfo
     */
    private function verifyMimeType(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return in_array($mimeType, $this->allowedMimeTypes);
    }

    /**
     * Sanitizar nombre de archivo
     */
    public function sanitizeFilename(string $filename): string
    {
        // Obtener extensión
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        
        // Limpiar nombre base
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        $basename = substr($basename, 0, 200); // Limitar longitud
        
        return $basename . '.' . $extension;
    }

    /**
     * Generar nombre único para archivo
     */
    public function generateUniqueFilename(string $originalFilename): string
    {
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        $basename = substr($basename, 0, 50);
        
        $uniqueId = uniqid('', true);
        $timestamp = time();
        
        return "{$basename}_{$timestamp}_{$uniqueId}.{$extension}";
    }

    /**
     * Obtener errores de validación
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener primer error
     */
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Mover archivo subido a destino
     */
    public function move(array $file, string $destination, ?string $newFilename = null): ?string
    {
        if (!$this->validate($file)) {
            return null;
        }

        // Crear directorio si no existe
        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Generar nombre si no se proporcionó
        if ($newFilename === null) {
            $newFilename = $this->generateUniqueFilename($file['name']);
        } else {
            $newFilename = $this->sanitizeFilename($newFilename);
        }

        $fullPath = rtrim($destination, '/') . '/' . $newFilename;

        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            chmod($fullPath, 0644);
            return $fullPath;
        }

        $this->errors[] = 'Error al mover el archivo al destino';
        return null;
    }

    /**
     * Validar múltiples archivos
     */
    public function validateMultiple(array $files): array
    {
        $results = [];
        
        foreach ($files as $key => $file) {
            $results[$key] = $this->validate($file);
        }
        
        return $results;
    }
}
