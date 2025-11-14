<?php

/**
 * Configuración principal de la aplicación
 */

return [
    'name' => $_ENV['APP_NAME'] ?? 'Especialistas en Casa',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Bogota',
    'locale' => $_ENV['DEFAULT_LOCALE'] ?? 'es',
    
    'session' => [
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 120),
        'secure' => true,
        'httponly' => true,
        'samesite' => 'strict'
    ],
    
    'csrf' => [
        'token_lifetime' => (int)($_ENV['CSRF_TOKEN_LIFETIME'] ?? 7200)
    ],
    
    'security' => [
        'data_encryption' => filter_var($_ENV['ENABLE_DATA_ENCRYPTION'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'audit_log' => filter_var($_ENV['ENABLE_AUDIT_LOG'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'data_retention_days' => (int)($_ENV['DATA_RETENTION_DAYS'] ?? 2555)
    ],
    
    'uploads' => [
        'max_size' => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760), // 10MB
        'allowed_types' => explode(',', $_ENV['ALLOWED_FILE_TYPES'] ?? 'jpg,jpeg,png,pdf,doc,docx'),
        'path' => __DIR__ . '/../storage/uploads/'
    ]
];
