<?php

/**
 * Configuración de JWT
 */

return [
    'secret' => $_ENV['JWT_SECRET'] ?? '',
    'expiration' => (int)($_ENV['JWT_EXPIRATION'] ?? 3600), // 1 hora
    'algorithm' => 'HS256',
    'issuer' => $_ENV['APP_URL'] ?? 'http://localhost',
    'audience' => $_ENV['APP_URL'] ?? 'http://localhost'
];
