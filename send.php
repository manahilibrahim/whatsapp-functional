<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$receiver_id = $_POST['receiver_id'] ?? null;
$message = trim($_POST['message'] ?? '');

if (empty($receiver_id) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    // Verify receiver exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid receiver']);
        exit();
    }
    
    // Insert message
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, body, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], $receiver_id, $message]);
    
    // Update user activity
    $stmt = $pdo->prepare("
        INSERT INTO user_activity (user_id, status, last_activity) 
        VALUES (?, 'online', NOW())
        ON DUPLICATE KEY UPDATE 
        status = 'online',
        last_activity = NOW()
    ");
    $stmt->execute([$_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Message sent']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
?>