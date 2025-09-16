<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? 'online';

try {
    // Update user activity
    $stmt = $pdo->prepare("
        INSERT INTO user_activity (user_id, status, last_activity) 
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
        status = VALUES(status),
        last_activity = NOW()
    ");
    $stmt->execute([$_SESSION['user_id'], $action]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>