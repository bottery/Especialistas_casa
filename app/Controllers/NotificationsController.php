<?php

namespace App\Controllers;

class NotificationsController extends BaseController
{
    private $db;

    public function __construct($requireAuth = true)
    {
        parent::__construct($requireAuth);
        $this->db = \App\Config\Database::getInstance()->getConnection();
    }

    // GET /api/notifications - Obtener notificaciones
    public function index()
    {
        try {
            $userId = $_SESSION['user']->id ?? null;
            $since = $_GET['since'] ?? 0;

            if (!$userId) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }

            $query = "SELECT n.*, 
                             u.nombre as from_user_name
                      FROM notificaciones n
                      LEFT JOIN usuarios u ON n.from_user_id = u.id
                      WHERE n.user_id = :user_id 
                      AND n.id > :since
                      ORDER BY n.created_at DESC
                      LIMIT 50";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'user_id' => $userId,
                'since' => $since
            ]);

            $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'notifications' => $notifications,
                'count' => count($notifications)
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // POST /api/notifications/{id}/read - Marcar como leída
    public function markAsRead($id)
    {
        try {
            $userId = $_SESSION['user']->id ?? null;

            $query = "UPDATE notificaciones 
                      SET `read` = 1, read_at = NOW()
                      WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id' => $id,
                'user_id' => $userId
            ]);

            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // POST /api/notifications/read-all - Marcar todas como leídas
    public function markAllAsRead()
    {
        try {
            $userId = $_SESSION['user']->id ?? null;

            $query = "UPDATE notificaciones 
                      SET `read` = 1, read_at = NOW()
                      WHERE user_id = :user_id AND `read` = 0";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute(['user_id' => $userId]);

            echo json_encode([
                'success' => true,
                'updated' => $stmt->rowCount()
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Método helper para crear notificación
    public static function create($userId, $type, $title, $message, $actionUrl = null, $fromUserId = null)
    {
        try {
            $db = \App\Config\Database::getInstance()->getConnection();
            
            $query = "INSERT INTO notificaciones 
                      (user_id, type, title, message, action_url, from_user_id, created_at) 
                      VALUES (:user_id, :type, :title, :message, :action_url, :from_user_id, NOW())";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
                'from_user_id' => $fromUserId
            ]);

            return $db->lastInsertId();

        } catch (\Exception $e) {
            error_log('Error creating notification: ' . $e->getMessage());
            return false;
        }
    }
}
