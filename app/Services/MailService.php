<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Servicio de envío de correos electrónicos
 */
class MailService
{
    private $config;
    private $mailer;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/mail.php';
        $this->setupMailer();
    }

    /**
     * Configurar PHPMailer
     */
    private function setupMailer(): void
    {
        // Si PHPMailer no está disponible (sin Composer), usar log en su lugar
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $this->mailer = null;
            return;
        }

        $this->mailer = new PHPMailer(true);

        try {
            // Configuración del servidor
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['port'];
            $this->mailer->CharSet = 'UTF-8';

            // Configuración del remitente
            $this->mailer->setFrom(
                $this->config['from']['address'],
                $this->config['from']['name']
            );
        } catch (Exception $e) {
            error_log("Error al configurar PHPMailer: " . $e->getMessage());
        }
    }

    /**
     * Enviar correo electrónico
     */
    public function send(string $to, string $subject, string $body, bool $isHTML = true): bool
    {
        // Si no hay mailer disponible, simular envío con log
        if ($this->mailer === null) {
            error_log("MAIL SIMULADO - To: $to, Subject: $subject");
            return true;
        }

        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->isHTML($isHTML);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            if ($isHTML) {
                $this->mailer->AltBody = strip_tags($body);
            }

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    /**
     * Enviar correo de bienvenida
     */
    public function sendWelcomeEmail(string $to, string $nombre): bool
    {
        $subject = "¡Bienvenido a Especialistas en Casa!";
        $body = $this->getTemplate('welcome', [
            'nombre' => $nombre
        ]);

        return $this->send($to, $subject, $body);
    }

    /**
     * Enviar correo de confirmación de servicio
     */
    public function sendServiceConfirmation(string $to, array $data): bool
    {
        $subject = "Confirmación de servicio médico";
        $body = $this->getTemplate('service_confirmation', $data);

        return $this->send($to, $subject, $body);
    }

    /**
     * Enviar correo de factura
     */
    public function sendInvoice(string $to, array $data): bool
    {
        $subject = "Factura de servicio - " . $data['numero_factura'];
        $body = $this->getTemplate('invoice', $data);

        return $this->send($to, $subject, $body);
    }

    /**
     * Obtener plantilla de correo
     */
    private function getTemplate(string $template, array $data): string
    {
        $templatePath = __DIR__ . "/../../resources/views/emails/{$template}.php";
        
        if (!file_exists($templatePath)) {
            return $this->getDefaultTemplate($data);
        }

        ob_start();
        extract($data);
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Plantilla por defecto
     */
    private function getDefaultTemplate(array $data): string
    {
        $content = '';
        foreach ($data as $key => $value) {
            $content .= "<p><strong>" . ucfirst($key) . ":</strong> " . htmlspecialchars($value) . "</p>";
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4F46E5; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Especialistas en Casa</h1>
                </div>
                <div class='content'>
                    {$content}
                </div>
                <div class='footer'>
                    <p>Este es un correo automático, por favor no responder.</p>
                    <p>&copy; 2025 Especialistas en Casa. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
