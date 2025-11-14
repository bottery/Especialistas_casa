<?php

namespace App\Controllers;

use App\Models\Servicio;
use App\Models\Solicitud;
use App\Models\Usuario;
use App\Middleware\AuthMiddleware;

/**
 * Controlador de Paciente
 */
class PacienteController
{
    private $servicioModel;
    private $solicitudModel;
    private $authMiddleware;
    private $user;

    public function __construct()
    {
        $this->servicioModel = new Servicio();
        $this->solicitudModel = new Solicitud();
        $this->authMiddleware = new AuthMiddleware();
        
        // Verificar autenticación
        $this->user = $this->authMiddleware->checkRole(['paciente']);
        if (!$this->user) {
            exit;
        }
    }

    /**
     * Listar servicios disponibles
     */
    public function listServices(): void
    {
        try {
            $tipo = $_GET['tipo'] ?? null;
            $modalidad = $_GET['modalidad'] ?? null;

            if ($tipo) {
                $servicios = $this->servicioModel->getByType($tipo);
            } elseif ($modalidad) {
                $servicios = $this->servicioModel->getByModality($modalidad);
            } else {
                $servicios = $this->servicioModel->getActive();
            }

            $this->sendSuccess(['servicios' => $servicios]);
        } catch (\Exception $e) {
            $this->sendError("Error al obtener servicios", 500);
        }
    }

    /**
     * Solicitar un servicio
     */
    public function requestService(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar datos requeridos
            $required = ['servicio_id', 'modalidad', 'fecha_programada'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $this->sendError("El campo {$field} es requerido", 400);
                    return;
                }
            }

            // Verificar que el servicio existe
            $servicio = $this->servicioModel->find($data['servicio_id']);
            if (!$servicio) {
                $this->sendError("Servicio no encontrado", 404);
                return;
            }

            // Preparar datos de la solicitud
            $solicitudData = [
                'paciente_id' => $this->user->id,
                'servicio_id' => $data['servicio_id'],
                'modalidad' => $data['modalidad'],
                'fecha_programada' => $data['fecha_programada'],
                'sintomas' => $data['sintomas'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'direccion_servicio' => $data['direccion_servicio'] ?? null,
                'monto_total' => $servicio['precio_base'],
                'estado' => 'pendiente'
            ];

            // Procesar documentos adjuntos si existen
            if (!empty($data['documentos'])) {
                $solicitudData['documentos_adjuntos'] = json_encode($data['documentos']);
            }

            // Crear solicitud
            $solicitudId = $this->solicitudModel->createRequest($solicitudData);

            $this->sendSuccess([
                'message' => 'Solicitud creada exitosamente',
                'solicitud_id' => $solicitudId,
                'monto_total' => $servicio['precio_base']
            ], 201);
        } catch (\Exception $e) {
            error_log("Error al crear solicitud: " . $e->getMessage());
            $this->sendError("Error al crear solicitud", 500);
        }
    }

    /**
     * Ver historial de servicios
     */
    public function getHistory(): void
    {
        try {
            $solicitudes = $this->solicitudModel->getByPatient($this->user->id);
            $this->sendSuccess(['solicitudes' => $solicitudes]);
        } catch (\Exception $e) {
            $this->sendError("Error al obtener historial", 500);
        }
    }

    /**
     * Ver detalle de una solicitud
     */
    public function getRequestDetail(): void
    {
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                $this->sendError("ID de solicitud requerido", 400);
                return;
            }

            $solicitud = $this->solicitudModel->find($id);

            if (!$solicitud || $solicitud['paciente_id'] != $this->user->id) {
                $this->sendError("Solicitud no encontrada", 404);
                return;
            }

            $this->sendSuccess(['solicitud' => $solicitud]);
        } catch (\Exception $e) {
            $this->sendError("Error al obtener detalle", 500);
        }
    }

    /**
     * Cancelar una solicitud
     */
    public function cancelRequest(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['solicitud_id'] ?? null;
            $razon = $data['razon'] ?? 'Cancelado por el paciente';

            if (!$id) {
                $this->sendError("ID de solicitud requerido", 400);
                return;
            }

            $solicitud = $this->solicitudModel->find($id);

            if (!$solicitud || $solicitud['paciente_id'] != $this->user->id) {
                $this->sendError("Solicitud no encontrada", 404);
                return;
            }

            // Solo se puede cancelar si está pendiente o confirmada
            if (!in_array($solicitud['estado'], ['pendiente', 'confirmada'])) {
                $this->sendError("No se puede cancelar esta solicitud", 400);
                return;
            }

            $success = $this->solicitudModel->cancel($id, $this->user->id, $razon);

            if ($success) {
                $this->sendSuccess(['message' => 'Solicitud cancelada exitosamente']);
            } else {
                $this->sendError("Error al cancelar solicitud", 500);
            }
        } catch (\Exception $e) {
            $this->sendError("Error al cancelar solicitud", 500);
        }
    }

    /**
     * Subir documentos
     */
    public function uploadDocuments(): void
    {
        try {
            // Implementar lógica de upload de archivos
            $this->sendSuccess(['message' => 'Funcionalidad de upload en desarrollo']);
        } catch (\Exception $e) {
            $this->sendError("Error al subir documentos", 500);
        }
    }

    /**
     * Enviar respuesta exitosa
     */
    private function sendSuccess(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, ...$data]);
    }

    /**
     * Enviar respuesta de error
     */
    private function sendError(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
    }
}
