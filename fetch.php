<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

$receiver_id = $_GET['receiver_id'] ?? null;

if (empty($receiver_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing receiver ID']);
    exit();
}

try {
    // Mark messages as delivered for current user (receiver)
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET delivered_at = NOW() 
        WHERE receiver_id = ? AND sender_id = ? AND delivered_at IS NULL
    ");
    $stmt->execute([$_SESSION['user_id'], $receiver_id]);
    
    // Get last 50 messages between current user and receiver
    $stmt = $pdo->prepare("
        SELECT m.*, u.name as sender_name 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at DESC 
        LIMIT 50
    ");
    $stmt->execute([$_SESSION['user_id'], $receiver_id, $receiver_id, $_SESSION['user_id']]);
    $messages = $stmt->fetchAll();
    
    // Reverse to show oldest first
    $messages = array_reverse($messages);
    
    echo json_encode(['success' => true, 'messages' => $messages, 'count' => count($messages)]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch messages: ' . $e->getMessage()]);
}
?>