<?php
/**
 * Controlador para gestión de configuración de pagos (QR, datos bancarios)
 * Solo accesible por superadmin
 */

namespace App\Controllers;

use App\Services\Database;

class ConfiguracionPagosController extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener configuración actual de pagos
     * GET /api/admin/configuracion-pagos
     */
    public function getConfiguracion(): void
    {
        try {
            // Solo superadmin puede acceder
            $this->requireAuth(['superadmin']);
            
            $result = $this->db->select("SELECT * FROM configuracion_pagos WHERE id = 1 LIMIT 1");
            $config = $result[0] ?? null;
            
            if (!$config) {
                // Crear configuración por defecto si no existe
                $this->db->insert("INSERT INTO configuracion_pagos (id, activo) VALUES (1, 1)", []);
                $config = [
                    'id' => 1,
                    'qr_imagen_path' => null,
                    'banco_nombre' => null,
                    'banco_cuenta' => null,
                    'banco_tipo_cuenta' => null,
                    'banco_titular' => null,
                    'instrucciones_transferencia' => 'Realiza tu transferencia y envía el comprobante por WhatsApp.',
                    'whatsapp_contacto' => '+57 300 123 4567',
                    'activo' => 1
                ];
            }
            
            $this->sendSuccess($config);
        } catch (\Exception $e) {
            error_log("Error obteniendo configuración: " . $e->getMessage());
            $this->sendError("Error al obtener configuración", 500);
        }
    }
    
    /**
     * Actualizar configuración de pagos
     * PUT /api/admin/configuracion-pagos
     */
    public function updateConfiguracion(): void
    {
        try {
            $this->requireAuth(['superadmin']);
            
            $data = $this->getRequestData();
            $userId = $_SESSION['user_id'] ?? null;
            
            $updates = [];
            $params = [];
            
            if (isset($data['banco_nombre'])) {
                $updates[] = "banco_nombre = ?";
                $params[] = $data['banco_nombre'];
            }
            
            if (isset($data['banco_cuenta'])) {
                $updates[] = "banco_cuenta = ?";
                $params[] = $data['banco_cuenta'];
            }
            
            if (isset($data['banco_tipo_cuenta'])) {
                $updates[] = "banco_tipo_cuenta = ?";
                $params[] = $data['banco_tipo_cuenta'];
            }
            
            if (isset($data['banco_titular'])) {
                $updates[] = "banco_titular = ?";
                $params[] = $data['banco_titular'];
            }
            
            if (isset($data['instrucciones_transferencia'])) {
                $updates[] = "instrucciones_transferencia = ?";
                $params[] = $data['instrucciones_transferencia'];
            }
            
            if (isset($data['whatsapp_contacto'])) {
                $updates[] = "whatsapp_contacto = ?";
                $params[] = $data['whatsapp_contacto'];
            }
            
            if (isset($data['activo'])) {
                $updates[] = "activo = ?";
                $params[] = $data['activo'] ? 1 : 0;
            }
            
            if (empty($updates)) {
                $this->sendError("No hay datos para actualizar", 400);
                return;
            }
            
            if ($userId) {
                $updates[] = "updated_by = ?";
                $params[] = $userId;
            }
            
            $sql = "UPDATE configuracion_pagos SET " . implode(', ', $updates) . " WHERE id = 1";
            $this->db->query($sql, $params);
            
            $this->sendSuccess(['message' => 'Configuración actualizada exitosamente']);
            
        } catch (\Exception $e) {
            error_log("Error actualizando configuración: " . $e->getMessage());
            $this->sendError("Error al actualizar configuración", 500);
        }
    }
    
    /**
     * Subir imagen QR para transferencias
     * POST /api/admin/configuracion-pagos/qr
     */
    public function uploadQR(): void
    {
        try {
            $this->requireAuth(['superadmin']);
            
            if (!isset($_FILES['qr_imagen']) || $_FILES['qr_imagen']['error'] !== UPLOAD_ERR_OK) {
                $this->sendError("No se recibió imagen QR válida", 400);
                return;
            }
            
            $file = $_FILES['qr_imagen'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            // Validar tipo de archivo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $this->sendError("Tipo de archivo no permitido. Solo se aceptan imágenes JPG y PNG", 400);
                return;
            }
            
            // Validar tamaño
            if ($file['size'] > $maxSize) {
                $this->sendError("La imagen es demasiado grande. Máximo 5MB", 400);
                return;
            }
            
            // Crear directorio si no existe
            $uploadDir = __DIR__ . '/../../public/uploads/pagos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'qr_transferencia_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                $this->sendError("Error al guardar la imagen", 500);
                return;
            }
            
            $userId = $_SESSION['user_id'] ?? null;
            $relativePath = '/uploads/pagos/' . $filename;
            
            // Eliminar QR anterior si existe
            $oldConfig = $this->db->select("SELECT qr_imagen_path FROM configuracion_pagos WHERE id = 1");
            if (!empty($oldConfig) && $oldConfig[0]['qr_imagen_path']) {
                $oldFile = __DIR__ . '/../../public' . $oldConfig[0]['qr_imagen_path'];
                if (file_exists($oldFile) && strpos($oldFile, '/uploads/') !== false) {
                    unlink($oldFile);
                }
            }
            
            $this->db->query(
                "UPDATE configuracion_pagos SET qr_imagen_path = ?, updated_by = ? WHERE id = 1",
                [$relativePath, $userId]
            );
            
            $this->sendSuccess([
                'message' => 'Código QR actualizado exitosamente',
                'qr_path' => $relativePath
            ]);
            
        } catch (\Exception $e) {
            error_log("Error subiendo QR: " . $e->getMessage());
            $this->sendError("Error al subir código QR: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Eliminar imagen QR
     * DELETE /api/admin/configuracion-pagos/qr
     */
    public function deleteQR(): void
    {
        try {
            $this->requireAuth(['superadmin']);
            
            // Obtener ruta actual
            $config = $this->db->select("SELECT qr_imagen_path FROM configuracion_pagos WHERE id = 1");
            
            if (!empty($config) && $config[0]['qr_imagen_path']) {
                $filepath = __DIR__ . '/../../public' . $config[0]['qr_imagen_path'];
                if (file_exists($filepath) && strpos($filepath, '/uploads/') !== false) {
                    unlink($filepath);
                }
                
                // Actualizar BD
                $userId = $_SESSION['user_id'] ?? null;
                $this->db->query(
                    "UPDATE configuracion_pagos SET qr_imagen_path = NULL, updated_by = ? WHERE id = 1",
                    [$userId]
                );
            }
            
            $this->sendSuccess(['message' => 'Código QR eliminado exitosamente']);
            
        } catch (\Exception $e) {
            error_log("Error eliminando QR: " . $e->getMessage());
            $this->sendError("Error al eliminar código QR", 500);
        }
    }
    
    /**
     * Obtener datos bancarios públicos (para mostrar en formularios de pago)
     * GET /api/configuracion/pagos
     */
    public function getDatosBancariosPublico(): void
    {
        try {
            $result = $this->db->select("
                SELECT 
                    banco_nombre,
                    banco_cuenta,
                    banco_tipo_cuenta,
                    banco_titular,
                    instrucciones_transferencia,
                    whatsapp_contacto,
                    qr_imagen_path
                FROM configuracion_pagos 
                WHERE id = 1 AND activo = 1 
                LIMIT 1
            ");
            $config = $result[0] ?? null;
            
            if (!$config) {
                // Datos por defecto si no hay configuración
                $config = [
                    'banco_nombre' => 'Bancolombia',
                    'banco_tipo_cuenta' => 'Ahorros',
                    'banco_cuenta' => 'Consultar administrador',
                    'banco_titular' => 'VitaHome S.A.S',
                    'instrucciones_transferencia' => 'Realiza tu transferencia y sube el comprobante desde Mis Solicitudes.',
                    'whatsapp_contacto' => '+57 300 123 4567',
                    'qr_imagen_path' => null
                ];
            }
            
            $this->sendSuccess(['configuracion' => $config]);
        } catch (\Exception $e) {
            error_log("Error obteniendo datos bancarios: " . $e->getMessage());
            $this->sendError("Error al obtener datos bancarios", 500);
        }
    }
}
