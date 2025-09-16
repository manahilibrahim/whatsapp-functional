<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$peer_user_id = $input['peer_user_id'] ?? null;

if (empty($peer_user_id)) {
    echo json_encode(['ok' => false, 'message' => 'Missing peer_user_id']);
    exit();
}

try {
    // Mark messages as seen for current user (receiver)
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET seen_at = NOW() 
        WHERE receiver_id = ? 
        AND sender_id = ? 
        AND delivered_at IS NOT NULL 
        AND seen_at IS NULL
    ");
    $stmt->execute([$_SESSION['user_id'], $peer_user_id]);
    $updated = $stmt->rowCount();
    
    echo json_encode(['ok' => true, 'updated' => $updated]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'message' => 'Failed to mark as seen: ' . $e->getMessage()]);
}
?>
