<?php

/**
 * Configuración de servicios externos
 */

return [
    'onesignal' => [
        'app_id' => $_ENV['ONESIGNAL_APP_ID'] ?? '',
        'rest_api_key' => $_ENV['ONESIGNAL_REST_API_KEY'] ?? '',
        'enabled' => !empty($_ENV['ONESIGNAL_APP_ID'])
    ],
    
    'pse' => [
        'merchant_id' => $_ENV['PSE_MERCHANT_ID'] ?? '',
        'api_key' => $_ENV['PSE_API_KEY'] ?? '',
        'api_secret' => $_ENV['PSE_API_SECRET'] ?? '',
        'sandbox' => filter_var($_ENV['PSE_SANDBOX'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'enabled' => !empty($_ENV['PSE_MERCHANT_ID'])
    ]
];
