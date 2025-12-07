<?php
/**
 * Controlador para gestión de configuración de pagos (QR, datos bancarios)
 * Solo accesible por superadmin
 */

namespace App\Controllers;

class ConfiguracionPagosController extends BaseController
{
    /**
     * Obtener configuración actual de pagos
     * GET /api/admin/configuracion-pagos
     */
    public function getConfiguracion(): void
    {
        try {
            // Solo superadmin puede acceder
            $this->requireAuth(['superadmin']);
            
            global $pdo;
            
            $stmt = $pdo->query("SELECT * FROM configuracion_pagos WHERE id = 1 LIMIT 1");
            $config = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$config) {
                // Crear configuración por defecto si no existe
                $pdo->exec("INSERT INTO configuracion_pagos (id, activo) VALUES (1, 1)");
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
            
            global $pdo;
            $userId = $_SESSION['user_id'];
            
            $updates = [];
            $params = ['id' => 1, 'user_id' => $userId];
            
            if (isset($data['banco_nombre'])) {
                $updates[] = "banco_nombre = :banco_nombre";
                $params['banco_nombre'] = $data['banco_nombre'];
            }
            
            if (isset($data['banco_cuenta'])) {
                $updates[] = "banco_cuenta = :banco_cuenta";
                $params['banco_cuenta'] = $data['banco_cuenta'];
            }
            
            if (isset($data['banco_tipo_cuenta'])) {
                $updates[] = "banco_tipo_cuenta = :banco_tipo_cuenta";
                $params['banco_tipo_cuenta'] = $data['banco_tipo_cuenta'];
            }
            
            if (isset($data['banco_titular'])) {
                $updates[] = "banco_titular = :banco_titular";
                $params['banco_titular'] = $data['banco_titular'];
            }
            
            if (isset($data['instrucciones_transferencia'])) {
                $updates[] = "instrucciones_transferencia = :instrucciones";
                $params['instrucciones'] = $data['instrucciones_transferencia'];
            }
            
            if (isset($data['whatsapp_contacto'])) {
                $updates[] = "whatsapp_contacto = :whatsapp";
                $params['whatsapp'] = $data['whatsapp_contacto'];
            }
            
            if (isset($data['activo'])) {
                $updates[] = "activo = :activo";
                $params['activo'] = $data['activo'] ? 1 : 0;
            }
            
            if (empty($updates)) {
                $this->sendError("No hay datos para actualizar", 400);
                return;
            }
            
            $updates[] = "updated_by = :user_id";
            
            $sql = "UPDATE configuracion_pagos SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
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
            
            // Actualizar en base de datos
            global $pdo;
            $userId = $_SESSION['user_id'];
            $relativePath = '/uploads/pagos/' . $filename;
            
            // Eliminar QR anterior si existe
            $stmt = $pdo->query("SELECT qr_imagen_path FROM configuracion_pagos WHERE id = 1");
            $oldConfig = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($oldConfig && $oldConfig['qr_imagen_path']) {
                $oldFile = __DIR__ . '/../../public' . $oldConfig['qr_imagen_path'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            
            $stmt = $pdo->prepare("
                UPDATE configuracion_pagos 
                SET qr_imagen_path = :path, updated_by = :user_id 
                WHERE id = 1
            ");
            $stmt->execute([
                'path' => $relativePath,
                'user_id' => $userId
            ]);
            
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
            
            global $pdo;
            
            // Obtener ruta actual
            $stmt = $pdo->query("SELECT qr_imagen_path FROM configuracion_pagos WHERE id = 1");
            $config = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($config && $config['qr_imagen_path']) {
                $filepath = __DIR__ . '/../../public' . $config['qr_imagen_path'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                
                // Actualizar BD
                $userId = $_SESSION['user_id'];
                $stmt = $pdo->prepare("
                    UPDATE configuracion_pagos 
                    SET qr_imagen_path = NULL, updated_by = :user_id 
                    WHERE id = 1
                ");
                $stmt->execute(['user_id' => $userId]);
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
            global $pdo;
            
            $stmt = $pdo->query("
                SELECT 
                    banco_nombre,
                    banco_cuenta,
                    banco_tipo_cuenta,
                    banco_titular,
                    instrucciones_transferencia,
                    whatsapp_contacto
                FROM configuracion_pagos 
                WHERE id = 1 AND activo = 1 
                LIMIT 1
            ");
            $config = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$config) {
                // Datos por defecto si no hay configuración
                $config = [
                    'banco_nombre' => 'Bancolombia',
                    'banco_tipo_cuenta' => 'Ahorros',
                    'banco_cuenta' => 'Consultar administrador',
                    'banco_titular' => 'Especialistas en Casa',
                    'instrucciones_transferencia' => 'Realiza tu transferencia y sube el comprobante desde Mis Solicitudes.',
                    'whatsapp_contacto' => null
                ];
            }
            
            $this->sendSuccess(['configuracion' => $config]);
        } catch (\Exception $e) {
            error_log("Error obteniendo datos bancarios: " . $e->getMessage());
            $this->sendError("Error al obtener datos bancarios", 500);
        }
    }
}
