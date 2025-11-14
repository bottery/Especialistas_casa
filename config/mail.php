<?php

/**
 * Configuración de correo electrónico
 */

return [
    'driver' => $_ENV['MAIL_MAILER'] ?? 'smtp',
    'host' => $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io',
    'port' => (int)($_ENV['MAIL_PORT'] ?? 2525),
    'username' => $_ENV['MAIL_USERNAME'] ?? '',
    'password' => $_ENV['MAIL_PASSWORD'] ?? '',
    'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
    'from' => [
        'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@especialistasencasa.com',
        'name' => $_ENV['MAIL_FROM_NAME'] ?? 'Especialistas en Casa'
    ]
];
