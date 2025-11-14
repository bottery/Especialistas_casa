<?php

namespace App\Services;

use GuzzleHttp\Client;

/**
 * Servicio de notificaciones OneSignal
 */
class OneSignalService
{
    private $appId;
    private $restApiKey;
    private $enabled;
    private $client;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/services.php';
        $this->appId = $config['onesignal']['app_id'];
        $this->restApiKey = $config['onesignal']['rest_api_key'];
        $this->enabled = $config['onesignal']['enabled'];

        if ($this->enabled) {
            $this->client = new Client([
                'base_uri' => 'https://onesignal.com/api/v1/',
                'headers' => [
                    'Authorization' => 'Basic ' . $this->restApiKey,
                    'Content-Type' => 'application/json'
                ]
            ]);
        }
    }

    /**
     * Enviar notificación push
     */
    public function sendNotification(array $userIds, string $title, string $message, array $data = []): bool
    {
        if (!$this->enabled) {
            error_log("OneSignal no está configurado");
            return false;
        }

        try {
            $payload = [
                'app_id' => $this->appId,
                'include_external_user_ids' => $userIds,
                'headings' => ['en' => $title, 'es' => $title],
                'contents' => ['en' => $message, 'es' => $message],
                'data' => $data
            ];

            $response = $this->client->post('notifications', [
                'json' => $payload
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            error_log("Error al enviar notificación OneSignal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notificar nueva solicitud al profesional
     */
    public function notifyNewRequest(int $profesionalId, array $requestData): bool
    {
        return $this->sendNotification(
            [(string)$profesionalId],
            "Nueva solicitud de servicio",
            "Tienes una nueva solicitud de servicio. Revisa los detalles.",
            ['type' => 'new_request', 'request_id' => $requestData['id']]
        );
    }

    /**
     * Notificar confirmación de servicio al paciente
     */
    public function notifyServiceConfirmed(int $pacienteId, array $requestData): bool
    {
        return $this->sendNotification(
            [(string)$pacienteId],
            "Servicio confirmado",
            "Tu solicitud de servicio ha sido confirmada.",
            ['type' => 'service_confirmed', 'request_id' => $requestData['id']]
        );
    }

    /**
     * Notificar servicio completado
     */
    public function notifyServiceCompleted(int $pacienteId, array $requestData): bool
    {
        return $this->sendNotification(
            [(string)$pacienteId],
            "Servicio completado",
            "Tu servicio ha sido completado. Revisa los resultados.",
            ['type' => 'service_completed', 'request_id' => $requestData['id']]
        );
    }
}
