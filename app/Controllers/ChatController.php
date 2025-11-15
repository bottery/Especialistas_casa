<?php

namespace App\Controllers;

class ChatController extends BaseController
{
    private $db;

    public function __construct($requireAuth = true)
    {
        parent::__construct($requireAuth);
        $this->db = \App\Config\Database::getInstance()->getConnection();
    }

    // POST /api/chat/start - Iniciar chat
    public function start()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $solicitudId = $data['solicitud_id'] ?? null;
            $participantId = $data['participant_id'] ?? null;
            $userId = $_SESSION['user']->id ?? null;

            if (!$solicitudId || !$participantId) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos']);
                return;
            }

            // Verificar si ya existe un chat
            $query = "SELECT id FROM chats 
                      WHERE solicitud_id = :solicitud_id 
                      AND ((user1_id = :user1 AND user2_id = :user2) 
                           OR (user1_id = :user2 AND user2_id = :user1))";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'solicitud_id' => $solicitudId,
                'user1' => $userId,
                'user2' => $participantId
            ]);

            $chat = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($chat) {
                echo json_encode(['chat_id' => $chat['id']]);
                return;
            }

            // Crear nuevo chat
            $query = "INSERT INTO chats (solicitud_id, user1_id, user2_id, created_at) 
                      VALUES (:solicitud_id, :user1_id, :user2_id, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'solicitud_id' => $solicitudId,
                'user1_id' => $userId,
                'user2_id' => $participantId
            ]);

            $chatId = $this->db->lastInsertId();

            echo json_encode(['chat_id' => $chatId]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // GET /api/chat/{chatId}/messages - Obtener mensajes
    public function getMessages($chatId)
    {
        try {
            $userId = $_SESSION['user']->id ?? null;

            // Verificar que el usuario pertenece al chat
            if (!$this->userBelongsToChat($userId, $chatId)) {
                http_response_code(403);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }

            $query = "SELECT m.*, u.nombre as user_name
                      FROM chat_messages m
                      JOIN usuarios u ON m.user_id = u.id
                      WHERE m.chat_id = :chat_id
                      ORDER BY m.created_at ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute(['chat_id' => $chatId]);

            $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['messages' => $messages]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // POST /api/chat/send - Enviar mensaje
    public function send()
    {
        try {
            $chatId = $_POST['chat_id'] ?? null;
            $text = $_POST['text'] ?? null;
            $userId = $_SESSION['user']->id ?? null;

            if (!$chatId || !$text) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos']);
                return;
            }

            // Verificar que el usuario pertenece al chat
            if (!$this->userBelongsToChat($userId, $chatId)) {
                http_response_code(403);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }

            // Insertar mensaje
            $query = "INSERT INTO chat_messages (chat_id, user_id, text, created_at) 
                      VALUES (:chat_id, :user_id, :text, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'chat_id' => $chatId,
                'user_id' => $userId,
                'text' => $text
            ]);

            $messageId = $this->db->lastInsertId();

            // Actualizar última actividad del chat
            $this->updateChatActivity($chatId);

            // Obtener el mensaje completo
            $query = "SELECT m.*, u.nombre as user_name
                      FROM chat_messages m
                      JOIN usuarios u ON m.user_id = u.id
                      WHERE m.id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $messageId]);
            $message = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Enviar notificación al otro usuario
            $this->notifyOtherUser($chatId, $userId, $text);

            echo json_encode(['message' => $message]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // GET /api/chat/{chatId}/poll - Polling para nuevos mensajes
    public function poll($chatId)
    {
        try {
            $userId = $_SESSION['user']->id ?? null;
            $since = $_GET['since'] ?? 0;

            if (!$this->userBelongsToChat($userId, $chatId)) {
                http_response_code(403);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }

            // Obtener nuevos mensajes
            $query = "SELECT m.*, u.nombre as user_name
                      FROM chat_messages m
                      JOIN usuarios u ON m.user_id = u.id
                      WHERE m.chat_id = :chat_id AND m.id > :since
                      ORDER BY m.created_at ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'chat_id' => $chatId,
                'since' => $since
            ]);

            $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Obtener estado de escritura
            $typing = $this->getTypingStatus($chatId, $userId);

            echo json_encode([
                'messages' => $messages,
                'typing' => $typing
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // POST /api/chat/{chatId}/typing - Indicador de escritura
    public function typing($chatId)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $isTyping = $data['is_typing'] ?? false;
            $userId = $_SESSION['user']->id ?? null;

            if (!$this->userBelongsToChat($userId, $chatId)) {
                http_response_code(403);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }

            // Actualizar estado en cache (Redis o tabla temporal)
            // Por ahora usamos una tabla simple
            $query = "INSERT INTO chat_typing (chat_id, user_id, is_typing, updated_at)
                      VALUES (:chat_id, :user_id, :is_typing, NOW())
                      ON DUPLICATE KEY UPDATE is_typing = :is_typing, updated_at = NOW()";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'chat_id' => $chatId,
                'user_id' => $userId,
                'is_typing' => $isTyping ? 1 : 0
            ]);

            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Helpers
    private function userBelongsToChat($userId, $chatId)
    {
        $query = "SELECT id FROM chats 
                  WHERE id = :chat_id 
                  AND (user1_id = :user_id OR user2_id = :user_id)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'chat_id' => $chatId,
            'user_id' => $userId
        ]);

        return $stmt->fetch() !== false;
    }

    private function updateChatActivity($chatId)
    {
        $query = "UPDATE chats SET last_activity = NOW() WHERE id = :chat_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['chat_id' => $chatId]);
    }

    private function getTypingStatus($chatId, $currentUserId)
    {
        // Obtener usuarios que están escribiendo (excluyendo el usuario actual)
        // Solo si actualizaron hace menos de 5 segundos
        $query = "SELECT user_id, is_typing 
                  FROM chat_typing 
                  WHERE chat_id = :chat_id 
                  AND user_id != :user_id 
                  AND is_typing = 1
                  AND updated_at > DATE_SUB(NOW(), INTERVAL 5 SECOND)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'chat_id' => $chatId,
            'user_id' => $currentUserId
        ]);

        $typing = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return [
            'is_typing' => $typing !== false,
            'user_id' => $typing['user_id'] ?? null
        ];
    }

    private function notifyOtherUser($chatId, $senderId, $text)
    {
        // Obtener el otro usuario del chat
        $query = "SELECT CASE 
                         WHEN user1_id = :sender_id THEN user2_id 
                         ELSE user1_id 
                         END as recipient_id
                  FROM chats WHERE id = :chat_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'chat_id' => $chatId,
            'sender_id' => $senderId
        ]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result) {
            $preview = strlen($text) > 50 ? substr($text, 0, 50) . '...' : $text;
            \App\Controllers\NotificationsController::create(
                $result['recipient_id'],
                'mensaje',
                'Nuevo mensaje',
                $preview,
                '/chat/' . $chatId,
                $senderId
            );
        }
    }
}
