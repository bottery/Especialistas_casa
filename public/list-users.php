<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Services\Database;
header('Content-Type: application/json');
try {
    $db = Database::getInstance();
    // intentar usar método select si existe
    if (method_exists($db, 'select')) {
        $users = $db->select('SELECT id, nombre, email, rol, estado, created_at FROM usuarios ORDER BY id ASC');
    } else {
        $pdo = $db->getConnection();
        $stmt = $pdo->query('SELECT id, nombre, email, rol, estado, created_at FROM usuarios ORDER BY id ASC');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode(['success' => true, 'count' => count($users), 'users' => $users], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
