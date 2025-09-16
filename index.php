<?php
require_once 'config.php';
requireLogin();

$current_user = getCurrentUser();
$contacts = getUserContacts($current_user['id']);

// Get selected contact
$selected_contact_id = $_GET['contact'] ?? null;
$selected_contact = null;
$has_contact = false;

if ($selected_contact_id) {
    // Check if user has this contact saved
    $has_contact = hasContact($current_user['id'], $selected_contact_id);
    
    if ($has_contact) {
        $stmt = $pdo->prepare("SELECT id, name, email, phone FROM users WHERE id = ?");
        $stmt->execute([$selected_contact_id]);
        $selected_contact = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Clone - <?= htmlspecialchars($current_user['name']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body data-current-user-id="<?= $current_user['id'] ?>">
    <div class="app">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">Chats</div>
                <div class="sidebar-actions">
                    <button class="icon-btn" id="add-contact-btn" title="New Chat">✏️</button>
                    <button class="icon-btn" title="Menu">⋮</button>
                </div>
            </div>
            
            <!-- Search -->
            <div class="search-box">
                <input type="text" id="search-contacts" placeholder="Search or start a new chat" class="search-input">
            </div>
            
            <div class="contacts-list" id="contacts-list">
                <?php if (empty($contacts)): ?>
                    <div class="no-contacts">
                        <div class="no-contacts-icon">💬</div>
                        <h3>No contacts yet</h3>
                        <p>Add contacts to start chatting</p>
                        <button class="btn" id="add-first-contact">Add Contact</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($contacts as $contact): ?>
                        <div class="contact-item <?= $selected_contact_id == $contact['id'] ? 'active' : '' ?>" 
                             data-contact-id="<?= $contact['id'] ?>">
                            <div class="contact-avatar">
                                <?= strtoupper(substr($contact['name'], 0, 2)) ?>
                                <div class="online-indicator <?= $contact['online_status'] === 'online' ? 'online' : '' ?>"></div>
                            </div>
                            <div class="contact-info">
                                <div class="contact-name">
                                    <?= htmlspecialchars($contact['label'] ?: $contact['name']) ?>
                                </div>
                                <div class="contact-details">
                                    <?= $contact['email'] ? htmlspecialchars($contact['email']) : htmlspecialchars($contact['phone']) ?>
                                </div>
                            </div>
                            <div class="contact-time">
                                <?= date('H:i', strtotime($contact['last_message_time'] ?? 'now')) ?>
                            </div>
                            <div class="contact-actions">
                                <button class="remove-contact-btn" 
                                        data-contact-id="<?= $contact['id'] ?>" 
                                        title="Remove contact">❌</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Chat Area -->
        <div class="chat">
            <!-- Chat Header -->
            <div id="chat-header" style="<?= $selected_contact ? 'display: flex;' : 'display: none;' ?>">
                <div class="chat-avatar">
                    <?= $selected_contact ? strtoupper(substr($selected_contact['name'], 0, 2)) : '??' ?>
                    <div class="online-indicator online"></div>
                </div>
                <div class="chat-info">
                    <div class="chat-name"><?= $selected_contact ? htmlspecialchars($selected_contact['name']) : 'Select a contact' ?></div>
                    <div class="chat-status">last seen today at <?= date('g:i A') ?></div>
                </div>
                <div class="sidebar-actions">
                    <button class="icon-btn" title="Video Call">📹</button>
                    <button class="icon-btn" title="Voice Call">📞</button>
                    <button class="icon-btn" title="Search">🔎</button>
                    <button class="icon-btn" title="More">⋮</button>
                </div>
            </div>
            
            <!-- Typing Indicator -->
            <div id="typing" style="display: none;"></div>
            
            <!-- Messages -->
            <div class="messages" id="messages">
                <!-- Messages will be loaded here via AJAX -->
            </div>
            
            <!-- Message Composer -->
            <div class="composer" style="<?= $selected_contact ? 'display: flex;' : 'display: none;' ?>">
                <button class="icon-btn" title="Emoji">😊</button>
                <input type="text" id="message-input" placeholder="Type a message..." class="message-input" 
                       data-receiver-id="<?= $selected_contact['id'] ?? '' ?>">
                <button class="icon-btn" title="Attach">📎</button>
                <button id="send-btn" class="send-btn">➤</button>
            </div>
            
            <!-- Welcome Screen -->
            <div class="welcome-screen" style="<?= $selected_contact ? 'display: none;' : 'display: block;' ?>">
                <div class="welcome-icon">💬</div>
                <h2>Welcome to WhatsApp Clone</h2>
                <p>Add contacts to start chatting</p>
                <button class="btn" id="welcome-add-contact">Add Contact</button>
            </div>
        </div>
    </div>
    
    <!-- Add Contact Modal -->
    <div class="modal" id="add-contact-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Contact</h3>
                <button class="close-btn" id="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="add-contact-form">
                    <div class="form-group">
                        <label for="email_or_phone">Email or Phone Number</label>
                        <input type="text" id="email_or_phone" name="email_or_phone" required 
                               placeholder="Enter email or phone number">
                    </div>
                    <div class="form-group">
                        <label for="label">Label (optional)</label>
                        <input type="text" id="label" name="label" 
                               placeholder="Custom name for this contact">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Contact</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Save Contact Banner -->
    <div class="save-banner" id="save-banner" style="display: none;">
        <div class="save-banner-content">
            <div class="save-banner-text">
                <strong>Save Contact</strong>
                <span id="save-banner-name"></span>
            </div>
            <button class="btn btn-primary" id="save-banner-btn">Save</button>
        </div>
    </div>
    
    <script src="app.js"></script>
</body>
</html>