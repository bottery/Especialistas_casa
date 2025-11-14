<?php

namespace App\Controllers;

use App\Services\Database;
use PDO;

/**
 * Controlador para gestión de contenido
 */
class ContenidoController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Dashboard de contenido
     */
    public function getDashboard()
    {
        try {
            $data = [
                'servicios' => $this->getEstadisticasServicios(),
                'banners' => $this->getBannersActivos(),
                'faqs' => $this->getEstadisticasFAQs(),
                'contenido_estatico' => $this->getContenidoEstatico()
            ];

            $this->sendJson(['success' => true] + $data);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener todos los servicios con paginación
     */
    public function getServicios()
    {
        try {
            $limit = (int)($_GET['limit'] ?? 20);
            $offset = (int)($_GET['offset'] ?? 0);
            $search = $_GET['search'] ?? '';
            $activo = $_GET['activo'] ?? null;

            $where = [];
            $params = [];

            if ($search) {
                $where[] = "(nombre LIKE :search OR descripcion LIKE :search)";
                $params[':search'] = "%$search%";
            }

            if ($activo !== null) {
                $where[] = "activo = :activo";
                $params[':activo'] = (int)$activo;
            }

            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // Total
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM servicios $whereClause");
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];

            // Datos
            $sql = "SELECT id, nombre, descripcion, precio_min, precio_max, duracion_minutos, 
                           icono, activo, destacado, orden, categoria, created_at 
                    FROM servicios 
                    $whereClause 
                    ORDER BY orden ASC, nombre ASC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $servicios = $stmt->fetchAll();

            $this->sendJson([
                'success' => true,
                'servicios' => $servicios,
                'total' => (int)$total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Crear o actualizar servicio
     */
    public function guardarServicio()
    {
        try {
            $data = $this->getJsonInput();
            $id = $data['id'] ?? null;

            $campos = [
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'],
                'precio_min' => $data['precio_min'],
                'precio_max' => $data['precio_max'],
                'duracion_minutos' => $data['duracion_minutos'] ?? 60,
                'icono' => $data['icono'] ?? null,
                'categoria' => $data['categoria'] ?? 'general',
                'activo' => $data['activo'] ?? 1,
                'destacado' => $data['destacado'] ?? 0,
                'orden' => $data['orden'] ?? 0,
                'requisitos' => $data['requisitos'] ?? null,
                'incluye' => $data['incluye'] ?? null,
                'no_incluye' => $data['no_incluye'] ?? null
            ];

            if ($id) {
                // Actualizar
                $sets = [];
                foreach ($campos as $key => $value) {
                    $sets[] = "$key = :$key";
                }
                $sql = "UPDATE servicios SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                foreach ($campos as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->execute();
                
                $message = 'Servicio actualizado correctamente';
            } else {
                // Crear
                $keys = array_keys($campos);
                $sql = "INSERT INTO servicios (" . implode(', ', $keys) . ") 
                        VALUES (:" . implode(', :', $keys) . ")";
                $stmt = $this->db->prepare($sql);
                foreach ($campos as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->execute();
                $id = $this->db->lastInsertId();
                
                $message = 'Servicio creado correctamente';
            }

            $this->sendJson([
                'success' => true,
                'message' => $message,
                'id' => (int)$id
            ]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar servicio
     */
    public function eliminarServicio()
    {
        try {
            $data = $this->getJsonInput();
            $id = $data['id'];

            // Verificar si tiene solicitudes asociadas
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM solicitudes WHERE servicio_id = :id");
            $stmt->execute([':id' => $id]);
            $tiene_solicitudes = $stmt->fetch()['total'] > 0;

            if ($tiene_solicitudes) {
                // Solo desactivar
                $stmt = $this->db->prepare("UPDATE servicios SET activo = 0, updated_at = NOW() WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $message = 'Servicio desactivado (tiene solicitudes asociadas)';
            } else {
                // Eliminar permanentemente
                $stmt = $this->db->prepare("DELETE FROM servicios WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $message = 'Servicio eliminado correctamente';
            }

            $this->sendJson(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Gestión de banners
     */
    public function getBanners()
    {
        try {
            $sql = "SELECT id, titulo, subtitulo, imagen_url, enlace, orden, activo, 
                           fecha_inicio, fecha_fin, created_at 
                    FROM banners 
                    ORDER BY orden ASC, created_at DESC";
            
            $stmt = $this->db->query($sql);
            $banners = $stmt->fetchAll();

            $this->sendJson(['success' => true, 'banners' => $banners]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function guardarBanner()
    {
        try {
            $data = $this->getJsonInput();
            $id = $data['id'] ?? null;

            $campos = [
                'titulo' => $data['titulo'],
                'subtitulo' => $data['subtitulo'] ?? null,
                'imagen_url' => $data['imagen_url'],
                'enlace' => $data['enlace'] ?? null,
                'orden' => $data['orden'] ?? 0,
                'activo' => $data['activo'] ?? 1,
                'fecha_inicio' => $data['fecha_inicio'] ?? null,
                'fecha_fin' => $data['fecha_fin'] ?? null
            ];

            if ($id) {
                $sets = [];
                foreach ($campos as $key => $value) {
                    $sets[] = "$key = :$key";
                }
                $sql = "UPDATE banners SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                foreach ($campos as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->execute();
            } else {
                $keys = array_keys($campos);
                $sql = "INSERT INTO banners (" . implode(', ', $keys) . ") 
                        VALUES (:" . implode(', :', $keys) . ")";
                $stmt = $this->db->prepare($sql);
                foreach ($campos as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->execute();
                $id = $this->db->lastInsertId();
            }

            $this->sendJson(['success' => true, 'message' => 'Banner guardado', 'id' => (int)$id]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function eliminarBanner()
    {
        try {
            $data = $this->getJsonInput();
            $stmt = $this->db->prepare("DELETE FROM banners WHERE id = :id");
            $stmt->execute([':id' => $data['id']]);
            
            $this->sendJson(['success' => true, 'message' => 'Banner eliminado']);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Gestión de FAQs
     */
    public function getFAQs()
    {
        try {
            $categoria = $_GET['categoria'] ?? null;
            
            $where = $categoria ? "WHERE categoria = :categoria" : "";
            $sql = "SELECT id, pregunta, respuesta, categoria, orden, activo, created_at 
                    FROM faqs 
                    $where
                    ORDER BY categoria ASC, orden ASC";
            
            $stmt = $this->db->prepare($sql);
            if ($categoria) {
                $stmt->bindValue(':categoria', $categoria);
            }
            $stmt->execute();
            $faqs = $stmt->fetchAll();

            $this->sendJson(['success' => true, 'faqs' => $faqs]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function guardarFAQ()
    {
        try {
            $data = $this->getJsonInput();
            $id = $data['id'] ?? null;

            $campos = [
                'pregunta' => $data['pregunta'],
                'respuesta' => $data['respuesta'],
                'categoria' => $data['categoria'] ?? 'general',
                'orden' => $data['orden'] ?? 0,
                'activo' => $data['activo'] ?? 1
            ];

            if ($id) {
                $sets = [];
                foreach ($campos as $key => $value) {
                    $sets[] = "$key = :$key";
                }
                $sql = "UPDATE faqs SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                foreach ($campos as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->execute();
            } else {
                $keys = array_keys($campos);
                $sql = "INSERT INTO faqs (" . implode(', ', $keys) . ") 
                        VALUES (:" . implode(', :', $keys) . ")";
                $stmt = $this->db->prepare($sql);
                foreach ($campos as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->execute();
                $id = $this->db->lastInsertId();
            }

            $this->sendJson(['success' => true, 'message' => 'FAQ guardada', 'id' => (int)$id]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function eliminarFAQ()
    {
        try {
            $data = $this->getJsonInput();
            $stmt = $this->db->prepare("DELETE FROM faqs WHERE id = :id");
            $stmt->execute([':id' => $data['id']]);
            
            $this->sendJson(['success' => true, 'message' => 'FAQ eliminada']);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Contenido estático (términos, políticas, etc)
     */
    public function getContenido()
    {
        try {
            $tipo = $_GET['tipo'] ?? null;
            
            if ($tipo) {
                $stmt = $this->db->prepare("SELECT * FROM contenido_estatico WHERE tipo = :tipo");
                $stmt->execute([':tipo' => $tipo]);
                $contenido = $stmt->fetch();
            } else {
                $stmt = $this->db->query("SELECT tipo, titulo, updated_at FROM contenido_estatico ORDER BY tipo");
                $contenido = $stmt->fetchAll();
            }

            $this->sendJson(['success' => true, 'contenido' => $contenido]);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function guardarContenido()
    {
        try {
            $data = $this->getJsonInput();
            
            $stmt = $this->db->prepare("
                INSERT INTO contenido_estatico (tipo, titulo, contenido, updated_at) 
                VALUES (:tipo, :titulo, :contenido, NOW())
                ON DUPLICATE KEY UPDATE 
                    titulo = :titulo, 
                    contenido = :contenido, 
                    updated_at = NOW()
            ");
            
            $stmt->execute([
                ':tipo' => $data['tipo'],
                ':titulo' => $data['titulo'],
                ':contenido' => $data['contenido']
            ]);

            $this->sendJson(['success' => true, 'message' => 'Contenido guardado']);
        } catch (\Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Métodos auxiliares
    private function getEstadisticasServicios()
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(activo = 1) as activos,
                SUM(destacado = 1) as destacados
            FROM servicios
        ");
        return $stmt->fetch();
    }

    private function getBannersActivos()
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM banners 
            WHERE activo = 1 
            AND (fecha_inicio IS NULL OR fecha_inicio <= NOW())
            AND (fecha_fin IS NULL OR fecha_fin >= NOW())
        ");
        return $stmt->fetch()['total'];
    }

    private function getEstadisticasFAQs()
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(activo = 1) as activas,
                COUNT(DISTINCT categoria) as categorias
            FROM faqs
        ");
        return $stmt->fetch();
    }

    private function getContenidoEstatico()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM contenido_estatico");
        return $stmt->fetch()['total'];
    }

    private function getJsonInput()
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    private function sendJson($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
