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

            // Validar que no haya servicios pendientes de calificar
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as pendientes 
                FROM solicitudes 
                WHERE paciente_id = ? AND estado = 'pendiente_calificacion'
            ");
            $stmt->execute([$this->user->id]);
            $result = $stmt->fetch();
            
            if ($result['pendientes'] > 0) {
                $this->sendError("Debes calificar tu último servicio antes de solicitar uno nuevo", 400);
                return;
            }

            // Validar datos requeridos básicos
            $required = ['servicio_id', 'modalidad', 'fecha_programada', 'metodo_pago_preferido'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $this->sendError("El campo {$field} es requerido", 400);
                    return;
                }
            }
            
            // Validar método de pago
            if (!in_array($data['metodo_pago_preferido'], ['pse', 'transferencia'])) {
                $this->sendError("Método de pago no válido. Solo se acepta PSE o Transferencia", 400);
                return;
            }

            // Verificar que el servicio existe
            $servicio = $this->servicioModel->find($data['servicio_id']);
            if (!$servicio) {
                $this->sendError("Servicio no encontrado", 404);
                return;
            }

            // Validaciones específicas por tipo de servicio
            $tipo = $data['servicio_tipo'] ?? $servicio['tipo'];
            $validationError = $this->validateServiceType($tipo, $data);
            if ($validationError) {
                $this->sendError($validationError, 400);
                return;
            }

            // Preparar datos de la solicitud con todos los campos posibles
            $solicitudData = [
                'paciente_id' => $this->user->id,
                'servicio_id' => $data['servicio_id'],
                'profesional_id' => null, // Admin asignará después
                'modalidad' => $data['modalidad'],
                'fecha_programada' => $data['fecha_programada'],
                'direccion_servicio' => $data['direccion_servicio'] ?? null,
                'sintomas' => $data['sintomas'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'monto_total' => $servicio['precio_base'],
                // Si es transferencia: pendiente_confirmacion_pago, si es PSE: pendiente (listo para asignar)
                'estado' => $data['metodo_pago_preferido'] === 'transferencia' ? 'pendiente_confirmacion_pago' : 'pendiente',
                'pagado' => $data['metodo_pago_preferido'] === 'pse' ? true : false,
                
                // Información de contacto
                'telefono_contacto' => $data['telefono_contacto'] ?? null,
                'urgencia' => $data['urgencia'] ?? 'normal',
                'metodo_pago_preferido' => $data['metodo_pago_preferido'],
                
                // Campos específicos - Médico
                'especialidad' => $data['especialidad'] ?? null,
                'rango_horario' => $data['rango_horario'] ?? null,
                'requiere_aprobacion' => $data['requiere_aprobacion'] ?? false,
                
                // Campos específicos - Ambulancia
                'tipo_ambulancia' => $data['tipo_ambulancia'] ?? null,
                'origen' => $data['origen'] ?? null,
                'destino' => $data['destino'] ?? null,
                'tipo_emergencia' => $data['tipo_emergencia'] ?? null,
                'condicion_paciente' => $data['condicion_paciente'] ?? null,
                'numero_acompanantes' => $data['numero_acompanantes'] ?? 0,
                'contacto_emergencia' => $data['contacto_emergencia'] ?? null,
                
                // Campos específicos - Enfermería
                'tipo_cuidado' => $data['tipo_cuidado'] ?? null,
                'intensidad_horaria' => $data['intensidad_horaria'] ?? null,
                'duracion_tipo' => $data['duracion_tipo'] ?? null,
                'duracion_cantidad' => $data['duracion_cantidad'] ?? null,
                'turno' => $data['turno'] ?? null,
                'genero_preferido' => $data['genero_preferido'] ?? 'indistinto',
                'necesidades_especiales' => $data['necesidades_especiales'] ?? null,
                'condicion_paciente_detalle' => $data['condicion_paciente_detalle'] ?? null,
                
                // Campos específicos - Veterinaria
                'tipo_mascota' => $data['tipo_mascota'] ?? null,
                'nombre_mascota' => $data['nombre_mascota'] ?? null,
                'edad_mascota' => $data['edad_mascota'] ?? null,
                'raza_tamano' => $data['raza_tamano'] ?? null,
                'motivo_veterinario' => $data['motivo_veterinario'] ?? null,
                'historial_vacunas' => $data['historial_vacunas'] ?? null,
                
                // Campos específicos - Laboratorio
                'examenes_solicitados' => $data['examenes_solicitados'] ?? null,
                'requiere_ayuno' => $data['requiere_ayuno'] ?? false,
                'preparacion_especial' => $data['preparacion_especial'] ?? null,
                'email_resultados' => $data['email_resultados'] ?? null,
                
                // Campos específicos - Fisioterapia
                'tipo_tratamiento' => $data['tipo_tratamiento'] ?? null,
                'numero_sesiones' => $data['numero_sesiones'] ?? null,
                'frecuencia_sesiones' => $data['frecuencia_sesiones'] ?? null,
                'zona_tratamiento' => $data['zona_tratamiento'] ?? null,
                'lesion_condicion' => $data['lesion_condicion'] ?? null,
                
                // Campos específicos - Psicología
                'tipo_sesion_psico' => $data['tipo_sesion_psico'] ?? null,
                'motivo_consulta_psico' => $data['motivo_consulta_psico'] ?? null,
                'primera_vez' => $data['primera_vez'] ?? true,
                'observaciones_privadas' => $data['observaciones_privadas'] ?? null,
                
                // Campos específicos - Nutrición
                'tipo_consulta_nutri' => $data['tipo_consulta_nutri'] ?? null,
                'objetivos_nutri' => $data['objetivos_nutri'] ?? null,
                'peso_actual' => $data['peso_actual'] ?? null,
                'altura_actual' => $data['altura_actual'] ?? null,
                'condiciones_medicas' => $data['condiciones_medicas'] ?? null,
                'incluye_plan_alimenticio' => $data['incluye_plan_alimenticio'] ?? true
            ];

            // Procesar documentos adjuntos si existen
            if (!empty($data['documentos'])) {
                $solicitudData['documentos_adjuntos'] = json_encode($data['documentos']);
            }

            // Crear solicitud
            $solicitudId = $this->solicitudModel->createRequest($solicitudData);
            
            // Si es transferencia, crear registro de pago pendiente
            $pagoId = null;
            if ($data['metodo_pago_preferido'] === 'transferencia') {
                $pagoId = $this->createPendingPayment($solicitudId, $servicio['precio_base']);
            }

            // Obtener configuración de pagos para mostrar QR
            $configPagos = null;
            if ($data['metodo_pago_preferido'] === 'transferencia') {
                global $pdo;
                $stmt = $pdo->query("SELECT * FROM configuracion_pagos WHERE id = 1 LIMIT 1");
                $configPagos = $stmt->fetch(\PDO::FETCH_ASSOC);
            }

            // Mensaje personalizado según tipo y método de pago
            $mensaje = $this->getSuccessMessage($tipo, $data['metodo_pago_preferido'], $data['requiere_aprobacion'] ?? false);

            $response = [
                'message' => $mensaje,
                'solicitud_id' => $solicitudId,
                'monto_total' => $servicio['precio_base'],
                'estado' => $solicitudData['estado'],
                'metodo_pago' => $data['metodo_pago_preferido'],
                'requiere_confirmacion_pago' => $data['metodo_pago_preferido'] === 'transferencia'
            ];
            
            // Agregar info de transferencia si aplica
            if ($data['metodo_pago_preferido'] === 'transferencia' && $configPagos) {
                $response['pago_id'] = $pagoId;
                $response['datos_transferencia'] = [
                    'qr_imagen' => $configPagos['qr_imagen_path'],
                    'banco_nombre' => $configPagos['banco_nombre'],
                    'banco_cuenta' => $configPagos['banco_cuenta'],
                    'banco_tipo_cuenta' => $configPagos['banco_tipo_cuenta'],
                    'banco_titular' => $configPagos['banco_titular'],
                    'instrucciones' => $configPagos['instrucciones_transferencia'],
                    'whatsapp_contacto' => $configPagos['whatsapp_contacto']
                ];
            }

            $this->sendSuccess($response, 201);
        } catch (\Exception $e) {
            error_log("Error al crear solicitud: " . $e->getMessage());
            $this->sendError("Error al crear solicitud: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Validar datos específicos por tipo de servicio
     */
    private function validateServiceType(string $tipo, array $data): ?string
    {
        switch ($tipo) {
            case 'medico':
                if (empty($data['rango_horario'])) {
                    return "El rango horario es requerido para servicios médicos";
                }
                if (empty($data['sintomas'])) {
                    return "Los síntomas son requeridos para servicios médicos";
                }
                break;
                
            case 'ambulancia':
                if (empty($data['origen']) || empty($data['destino'])) {
                    return "Las direcciones de origen y destino son requeridas";
                }
                if (empty($data['condicion_paciente'])) {
                    return "La condición del paciente es requerida";
                }
                break;
                
            case 'enfermera':
                if (empty($data['tipo_cuidado']) || empty($data['duracion_cantidad'])) {
                    return "El tipo de cuidado y duración son requeridos";
                }
                if (empty($data['direccion_servicio'])) {
                    return "La dirección del servicio es requerida";
                }
                break;
                
            case 'veterinario':
                if (empty($data['tipo_mascota']) || empty($data['nombre_mascota'])) {
                    return "La información de la mascota es requerida";
                }
                if (empty($data['rango_horario'])) {
                    return "El rango horario es requerido";
                }
                break;
                
            case 'laboratorio':
                if (empty($data['examenes_solicitados'])) {
                    return "Debe seleccionar al menos un examen";
                }
                if (empty($data['email_resultados'])) {
                    return "El email para resultados es requerido";
                }
                if (empty($data['direccion_servicio'])) {
                    return "La dirección para toma de muestras es requerida";
                }
                break;
        }
        
        return null;
    }
    
    /**
     * Obtener mensaje de éxito personalizado
     */
    private function getSuccessMessage(string $tipo, string $metodoPago, bool $requiereAprobacion): string
    {
        // Mensaje especial para transferencia
        if ($metodoPago === 'transferencia') {
            return "Solicitud creada exitosamente. Por favor realiza la transferencia según los datos bancarios mostrados y sube tu comprobante de pago. Tu solicitud será activada una vez confirmemos el pago.";
        }
        
        // Mensaje especial para PSE
        if ($metodoPago === 'pse') {
            return "Solicitud creada y pago procesado exitosamente con PSE. Un administrador asignará un profesional pronto y serás notificado.";
        }
        
        if ($requiereAprobacion) {
            return "Solicitud enviada. El profesional la revisará y confirmará en las próximas horas.";
        }
        
        $mensajes = [
            'medico' => 'Solicitud médica creada. Recibirás confirmación del médico pronto.',
            'ambulancia' => 'Solicitud de ambulancia creada exitosamente.',
            'enfermera' => 'Solicitud de enfermería creada. Un profesional te contactará pronto.',
            'veterinario' => 'Solicitud veterinaria creada. El veterinario confirmará la cita.',
            'laboratorio' => 'Solicitud de laboratorio creada. Te contactaremos para coordinar la toma de muestras.',
            'fisioterapia' => 'Solicitud de fisioterapia creada exitosamente.',
            'psicologia' => 'Sesión de psicología agendada exitosamente.',
            'nutricion' => 'Consulta nutricional agendada exitosamente.'
        ];
        
        return $mensajes[$tipo] ?? 'Solicitud creada exitosamente.';
    }
    
    /**
     * Crear registro de pago pendiente para transferencias
     * Retorna el ID del pago creado
     */
    private function createPendingPayment(int $solicitudId, float $monto): int
    {
        try {
            global $pdo;
            
            $stmt = $pdo->prepare("
                INSERT INTO pagos (solicitud_id, usuario_id, metodo_pago, monto, estado, notas)
                VALUES (:solicitud_id, :usuario_id, 'transferencia', :monto, 'pendiente', 
                        'Esperando comprobante de transferencia del usuario')
            ");
            
            $stmt->execute([
                'solicitud_id' => $solicitudId,
                'usuario_id' => $this->user->id,
                'monto' => $monto
            ]);
            
            return (int)$pdo->lastInsertId();
        } catch (\Exception $e) {
            error_log("Error al crear pago pendiente: " . $e->getMessage());
            throw $e;
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
     * Obtener estadísticas del paciente
     */
    public function getStats(): void
    {
        try {
            $stats = [
                'solicitudesActivas' => 0,
                'solicitudesCompletadas' => 0,
                'proximaCita' => null
            ];
            
            // Contar solicitudes activas (pendiente, confirmada, en_progreso)
            $query = "SELECT COUNT(*) as total FROM solicitudes 
                      WHERE paciente_id = ? AND estado IN ('pendiente', 'confirmada', 'en_progreso')";
            $result = $this->solicitudModel->query($query, [$this->user->id]);
            $stats['solicitudesActivas'] = $result[0]['total'] ?? 0;
            
            // Contar solicitudes completadas
            $query = "SELECT COUNT(*) as total FROM solicitudes 
                      WHERE paciente_id = ? AND estado = 'completada'";
            $result = $this->solicitudModel->query($query, [$this->user->id]);
            $stats['solicitudesCompletadas'] = $result[0]['total'] ?? 0;
            
            // Obtener próxima cita
            $query = "SELECT s.*, srv.nombre as servicio_nombre 
                      FROM solicitudes s
                      INNER JOIN servicios srv ON s.servicio_id = srv.id
                      WHERE s.paciente_id = ? 
                      AND s.estado IN ('pendiente', 'confirmada') 
                      AND s.fecha_programada > NOW()
                      ORDER BY s.fecha_programada ASC
                      LIMIT 1";
            $result = $this->solicitudModel->query($query, [$this->user->id]);
            $stats['proximaCita'] = $result[0] ?? null;
            
            $this->sendSuccess($stats);
        } catch (\Exception $e) {
            error_log("Error al obtener stats: " . $e->getMessage());
            $this->sendError("Error al obtener estadísticas", 500);
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
     * Calificar un servicio completado
     */
    public function calificarServicio(int $solicitudId): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $calificacion = $data['calificacion'] ?? null;
            $comentario = $data['comentario'] ?? '';
            
            // Validar calificación
            if (!$calificacion || $calificacion < 1 || $calificacion > 5) {
                $this->sendError("La calificación debe estar entre 1 y 5", 400);
                return;
            }

            global $pdo;
            
            // Verificar que la solicitud existe, pertenece al paciente y está pendiente de calificación
            $stmt = $pdo->prepare("
                SELECT s.*, u.id as profesional_id, u.nombre, u.apellido
                FROM solicitudes s
                INNER JOIN usuarios u ON s.profesional_id = u.id
                WHERE s.id = :id 
                    AND s.paciente_id = :paciente_id 
                    AND s.estado = 'pendiente_calificacion'
                    AND s.calificado = FALSE
            ");
            
            $stmt->execute([
                'id' => $solicitudId,
                'paciente_id' => $this->user->id
            ]);
            
            $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                $this->sendError("Solicitud no encontrada o ya fue calificada", 404);
                return;
            }

            // Iniciar transacción
            $pdo->beginTransaction();

            try {
                // Actualizar solicitud con calificación
                $stmt = $pdo->prepare("
                    UPDATE solicitudes 
                    SET calificacion_paciente = :calificacion,
                        comentario_paciente = :comentario,
                        fecha_calificacion = CURRENT_TIMESTAMP,
                        calificado = TRUE,
                        estado = 'finalizada',
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    'id' => $solicitudId,
                    'calificacion' => $calificacion,
                    'comentario' => $comentario
                ]);

                // Recalcular puntuación promedio del profesional
                $stmt = $pdo->prepare("
                    SELECT 
                        AVG(calificacion_paciente) as promedio,
                        COUNT(*) as total
                    FROM solicitudes 
                    WHERE profesional_id = :profesional_id 
                        AND calificado = TRUE
                        AND calificacion_paciente IS NOT NULL
                ");
                
                $stmt->execute(['profesional_id' => $solicitud['profesional_id']]);
                $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                // Actualizar estadísticas del profesional
                $stmt = $pdo->prepare("
                    UPDATE usuarios 
                    SET puntuacion_promedio = :promedio,
                        total_calificaciones = :total,
                        servicios_completados = servicios_completados + 1
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    'id' => $solicitud['profesional_id'],
                    'promedio' => round($stats['promedio'], 2),
                    'total' => $stats['total']
                ]);

                $pdo->commit();

                $this->sendSuccess([
                    'message' => '¡Gracias por tu calificación!',
                    'solicitud_id' => $solicitudId,
                    'nueva_puntuacion' => round($stats['promedio'], 2)
                ]);
            } catch (\Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            error_log("Error al calificar servicio: " . $e->getMessage());
            $this->sendError("Error al procesar la calificación", 500);
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
