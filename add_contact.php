<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$email_or_phone = trim($_POST['email_or_phone'] ?? '');
$label = trim($_POST['label'] ?? '');

if (empty($email_or_phone)) {
    echo json_encode(['success' => false, 'message' => 'Email or phone is required']);
    exit();
}

try {
    // Check if it's email or phone
    $isEmail = filter_var($email_or_phone, FILTER_VALIDATE_EMAIL);
    $field = $isEmail ? 'email' : 'phone';
    
    // Find user by email or phone
    $stmt = $pdo->prepare("SELECT id, name, email, phone FROM users WHERE $field = ? AND id != ?");
    $stmt->execute([$email_or_phone, $_SESSION['user_id']]);
    $contact_user = $stmt->fetch();
    
    if (!$contact_user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    // Check if already a contact
    if (hasContact($_SESSION['user_id'], $contact_user['id'])) {
        echo json_encode(['success' => false, 'message' => 'Contact already exists']);
        exit();
    }
    
    // Add contact
    if (addContact($_SESSION['user_id'], $contact_user['id'], $label)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Contact added successfully',
            'contact' => [
                'id' => $contact_user['id'],
                'name' => $contact_user['name'],
                'email' => $contact_user['email'],
                'phone' => $contact_user['phone'],
                'label' => $label
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add contact']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
