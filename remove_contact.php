<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$contact_id = $_POST['contact_id'] ?? null;

if (empty($contact_id)) {
    echo json_encode(['success' => false, 'message' => 'Contact ID required']);
    exit();
}

try {
    if (removeContact($_SESSION['user_id'], $contact_id)) {
        echo json_encode(['success' => true, 'message' => 'Contact removed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove contact']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
