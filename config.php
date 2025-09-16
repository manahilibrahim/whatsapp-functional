<?php
// Database configuration for WhatsApp Clone
$host = 'localhost';
$dbname = 'dbvjulbjni0v2v';
$username = 'uhgpgzzavnqyn';
$password = 'dm0rmmpqqknx';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Get current user data
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT id, name, email, phone FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Get user's saved contacts
function getUserContacts($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.phone, c.label, c.status,
               CASE WHEN ua.status IS NOT NULL THEN ua.status ELSE 'offline' END as online_status,
               ua.last_seen
        FROM contacts c
        JOIN users u ON c.contact_user_id = u.id
        LEFT JOIN user_activity ua ON u.id = ua.user_id
        WHERE c.owner_id = ? AND c.status = 'active'
        ORDER BY ua.last_seen DESC, u.name ASC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Check if user has contact saved
function hasContact($owner_id, $contact_user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id FROM contacts WHERE owner_id = ? AND contact_user_id = ?");
    $stmt->execute([$owner_id, $contact_user_id]);
    return $stmt->fetch() ? true : false;
}

// Add contact
function addContact($owner_id, $contact_user_id, $label = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO contacts (owner_id, contact_user_id, label) VALUES (?, ?, ?)");
        $stmt->execute([$owner_id, $contact_user_id, $label]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Remove contact
function removeContact($owner_id, $contact_user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE owner_id = ? AND contact_user_id = ?");
        $stmt->execute([$owner_id, $contact_user_id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}
?>