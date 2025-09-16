// WhatsApp Clone JavaScript - Contact-based messaging
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-contacts');
    const contactsList = document.getElementById('contacts-list');
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');
    const messagesDiv = document.getElementById('messages');
    const addContactBtn = document.getElementById('add-contact-btn');
    const addContactModal = document.getElementById('add-contact-modal');
    const addContactForm = document.getElementById('add-contact-form');
    const closeModalBtn = document.getElementById('close-modal');
    const saveBanner = document.getElementById('save-banner');
    const saveBannerBtn = document.getElementById('save-banner-btn');
    
    let currentReceiverId = null;
    let pollInterval = null;
    let activityInterval = null;
    let typingTimeout = null;
    let isChatFocused = true;
    
    // Contact search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const contacts = contactsList.querySelectorAll('.contact-item');
            
            contacts.forEach(contact => {
                const name = contact.querySelector('.contact-name').textContent.toLowerCase();
                const details = contact.querySelector('.contact-details').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || details.includes(searchTerm)) {
                    contact.style.display = 'flex';
                } else {
                    contact.style.display = 'none';
                }
            });
        });
    }
    
    // Contact selection
    if (contactsList) {
        contactsList.addEventListener('click', function(e) {
            const contactItem = e.target.closest('.contact-item');
            const removeBtn = e.target.closest('.remove-contact-btn');
            
            if (removeBtn) {
                e.stopPropagation();
                const contactId = removeBtn.dataset.contactId;
                removeContact(contactId, removeBtn);
            } else if (contactItem) {
                const contactId = contactItem.dataset.contactId;
                const contactName = contactItem.querySelector('.contact-name').textContent;
                
                // Update active state
                document.querySelectorAll('.contact-item').forEach(item => {
                    item.classList.remove('active');
                });
                contactItem.classList.add('active');
                
                // Load chat with this contact
                loadChat(contactId, contactName);
            }
        });
    }
    
    // Message sending
    if (sendBtn && messageInput) {
        sendBtn.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
        
        // Typing indicators
        messageInput.addEventListener('input', function() {
            if (currentReceiverId) {
                updateActivity('typing');
                clearTimeout(typingTimeout);
                typingTimeout = setTimeout(() => {
                    updateActivity('online');
                }, 2000);
            }
        });
    }
    
    // Add contact modal
    if (addContactBtn) {
        addContactBtn.addEventListener('click', () => {
            addContactModal.style.display = 'flex';
        });
    }
    
    // Add first contact button
    const addFirstContactBtn = document.getElementById('add-first-contact');
    if (addFirstContactBtn) {
        addFirstContactBtn.addEventListener('click', () => {
            addContactModal.style.display = 'flex';
        });
    }
    
    // Welcome add contact button
    const welcomeAddContactBtn = document.getElementById('welcome-add-contact');
    if (welcomeAddContactBtn) {
        welcomeAddContactBtn.addEventListener('click', () => {
            addContactModal.style.display = 'flex';
        });
    }
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            addContactModal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside
    if (addContactModal) {
        addContactModal.addEventListener('click', (e) => {
            if (e.target === addContactModal) {
                addContactModal.style.display = 'none';
            }
        });
    }
    
    // Add contact form
    if (addContactForm) {
        addContactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            addContact();
        });
    }
    
    // Save banner button
    if (saveBannerBtn) {
        saveBannerBtn.addEventListener('click', function() {
            const contactId = this.dataset.contactId;
            const contactName = this.dataset.contactName;
            saveContactFromBanner(contactId, contactName);
        });
    }
    
    function loadChat(receiverId, receiverName) {
        console.log('loadChat called with:', receiverId, receiverName);
        currentReceiverId = receiverId;
        
        // Update URL without page reload
        const url = new URL(window.location);
        url.searchParams.set('contact', receiverId);
        window.history.pushState({}, '', url);
        
        // Show chat interface and hide welcome screen
        const welcomeScreen = document.querySelector('.welcome-screen');
        const chatHeader = document.getElementById('chat-header');
        const composer = document.querySelector('.composer');
        const typing = document.getElementById('typing');
        
        if (welcomeScreen) welcomeScreen.style.display = 'none';
        if (chatHeader) chatHeader.style.display = 'flex';
        if (composer) composer.style.display = 'flex';
        if (typing) typing.style.display = 'block';
        
        // Update chat header with contact info
        if (chatHeader) {
            const chatName = chatHeader.querySelector('.chat-name');
            const chatAvatar = chatHeader.querySelector('.chat-avatar');
            
            if (chatName) chatName.textContent = receiverName;
            if (chatAvatar) chatAvatar.textContent = receiverName.split(' ').map(n => n[0]).join('').toUpperCase();
        }
        
        // Re-attach event listeners for message input and send button
        const messageInput = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');
        
        console.log('Re-attaching event listeners...');
        console.log('messageInput found:', !!messageInput);
        console.log('sendBtn found:', !!sendBtn);
        
        if (sendBtn && messageInput) {
            // Remove existing listeners to avoid duplicates
            sendBtn.replaceWith(sendBtn.cloneNode(true));
            messageInput.replaceWith(messageInput.cloneNode(true));
            
            // Get fresh references
            const newSendBtn = document.getElementById('send-btn');
            const newMessageInput = document.getElementById('message-input');
            
            // Attach new listeners
            newSendBtn.addEventListener('click', sendMessage);
            newMessageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
            
            // Typing indicators
            newMessageInput.addEventListener('input', function() {
                if (currentReceiverId) {
                    updateActivity('typing');
                    clearTimeout(typingTimeout);
                    typingTimeout = setTimeout(() => {
                        updateActivity('online');
                    }, 1000);
                }
            });
            
            console.log('Event listeners re-attached successfully');
        }
        
        // Clear existing messages
        if (messagesDiv) {
            messagesDiv.innerHTML = '';
        }
        
        // Stop previous polling
        if (pollInterval) {
            clearInterval(pollInterval);
        }
        
        // Load messages immediately
        fetchMessages();
        
        // Start polling for new messages every 1 second
        pollInterval = setInterval(fetchMessages, 1000);
        
        // Start activity updates
        if (!activityInterval) {
            activityInterval = setInterval(() => updateActivity('online'), 30000);
        }
        
        // Focus message input
        if (messageInput) {
            messageInput.focus();
        }
        
        // Check if user has this contact saved
        checkContactStatus(receiverId);
        
        console.log('Loading chat with:', receiverId, receiverName);
    }
    
    // Mark messages as seen
    function markAsSeen() {
        if (currentReceiverId && isChatFocused) {
            fetch('mark_seen.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({peer_user_id: currentReceiverId})
            }).catch(err => console.log('Mark seen error:', err));
        }
    }
    
    // Get message status HTML
    function getMessageStatus(message) {
        if (message.sender_id == getCurrentUserId()) {
            // My message - show status
            if (message.seen_at) {
                return '<span class="status blue">✓✓</span>';
            } else if (message.delivered_at) {
                return '<span class="status">✓✓</span>';
            } else {
                return '<span class="status">✓</span>';
            }
        }
        return '';
    }
    
    function fetchMessages() {
        if (!currentReceiverId) {
            console.log('No receiver ID set');
            return;
        }
        
        fetch(`fetch.php?receiver_id=${currentReceiverId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayMessages(data.messages);
                    // Mark messages as seen after fetching
                    markAsSeen();
                } else {
                    console.error('Fetch failed:', data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching messages:', error);
            });
    }
    
    function displayMessages(messages) {
        if (!messagesDiv) {
            console.log('Messages div not found');
            return;
        }
        
        messagesDiv.innerHTML = '';
        
        if (messages.length === 0) {
            messagesDiv.innerHTML = '<div class="no-messages">No messages yet. Start the conversation!</div>';
            return;
        }
        
        messages.forEach(message => {
            const messageDiv = document.createElement('div');
            const isOutgoing = message.sender_id == getCurrentUserId();
            messageDiv.className = `msg ${isOutgoing ? 'me' : 'other'}`;
            
            const time = new Date(message.created_at).toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            
            const status = getMessageStatus(message);
            
            messageDiv.innerHTML = `
                <div class="message-bubble">${escapeHtml(message.body)}</div>
                <div class="time">${time}${status}</div>
            `;
            
            messagesDiv.appendChild(messageDiv);
        });
        
        // Scroll to bottom
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
    
    function sendMessage() {
        console.log('=== SEND MESSAGE DEBUG ===');
        console.log('sendMessage called');
        
        // Get fresh references to elements
        const messageInput = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');
        
        console.log('messageInput element:', messageInput);
        console.log('sendBtn element:', sendBtn);
        console.log('currentReceiverId:', currentReceiverId);
        
        if (!messageInput || !currentReceiverId) {
            console.log('ERROR: Missing messageInput or currentReceiverId');
            console.log('messageInput exists:', !!messageInput);
            console.log('currentReceiverId exists:', !!currentReceiverId);
            return;
        }
        
        const message = messageInput.value.trim();
        console.log('Message text:', message);
        if (!message) {
            console.log('ERROR: Empty message');
            return;
        }
        
        // Disable send button
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.textContent = 'Sending...';
        }
        
        const formData = new FormData();
        formData.append('receiver_id', currentReceiverId);
        formData.append('message', message);
        
        console.log('Sending AJAX request to send.php...');
        console.log('FormData contents:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }
        
        fetch('send.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response received:', response);
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                console.log('Message sent successfully!');
                messageInput.value = '';
                fetchMessages(); // Refresh messages
            } else {
                console.log('Send failed:', data.message);
                showNotification('Failed to send message: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('AJAX Error:', error);
            showNotification('Failed to send message', 'error');
        })
        .finally(() => {
            console.log('Re-enabling send button...');
            // Re-enable send button
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.textContent = '➤';
            }
        });
    }
    
    function addContact() {
        const formData = new FormData(addContactForm);
        
        fetch('add_contact.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Contact added successfully!', 'success');
                addContactModal.style.display = 'none';
                addContactForm.reset();
                location.reload(); // Refresh to show new contact
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error adding contact:', error);
            showNotification('Failed to add contact', 'error');
        });
    }
    
    function removeContact(contactId, button) {
        if (!confirm('Remove this contact?')) return;
        
        const formData = new FormData();
        formData.append('contact_id', contactId);
        
        fetch('remove_contact.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Contact removed', 'success');
                button.closest('.contact-item').remove();
                
                // If this was the active chat, clear it
                if (currentReceiverId == contactId) {
                    currentReceiverId = null;
                    if (messagesDiv) messagesDiv.innerHTML = '';
                    if (pollInterval) clearInterval(pollInterval);
                }
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error removing contact:', error);
            showNotification('Failed to remove contact', 'error');
        });
    }
    
    function checkContactStatus(receiverId) {
        // This would check if the current user has this contact saved
        // For now, we'll show the save banner if not saved
        const hasContact = document.querySelector(`[data-contact-id="${receiverId}"]`);
        if (!hasContact) {
            showSaveBanner(receiverId);
        }
    }
    
    function showSaveBanner(contactId) {
        // Get contact name from URL or other source
        const contactName = 'this contact';
        saveBannerBtn.dataset.contactId = contactId;
        saveBannerBtn.dataset.contactName = contactName;
        document.getElementById('save-banner-name').textContent = contactName;
        saveBanner.style.display = 'block';
    }
    
    function saveContactFromBanner(contactId, contactName) {
        const formData = new FormData();
        formData.append('contact_user_id', contactId);
        formData.append('label', contactName);
        
        fetch('add_contact.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Contact saved!', 'success');
                saveBanner.style.display = 'none';
                location.reload(); // Refresh to show new contact
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error saving contact:', error);
            showNotification('Failed to save contact', 'error');
        });
    }
    
    function updateActivity(status) {
        const formData = new FormData();
        formData.append('action', status);
        
        fetch('update_activity.php', {
            method: 'POST',
            body: formData
        })
        .catch(error => console.error('Activity update error:', error));
    }
    
    function getCurrentUserId() {
        const userIdElement = document.querySelector('[data-current-user-id]');
        return userIdElement ? userIdElement.dataset.currentUserId : null;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.3s ease;
            max-width: 300px;
        `;
        
        if (type === 'success') {
            notification.style.background = '#25d366';
        } else {
            notification.style.background = '#ff4444';
        }
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Window focus handling
    window.addEventListener('focus', function() {
        isChatFocused = true;
        markAsSeen();
    });
    
    window.addEventListener('blur', function() {
        isChatFocused = false;
    });
    
    // Initialize chat if contact is selected
    const urlParams = new URLSearchParams(window.location.search);
    const contactId = urlParams.get('contact');
    if (contactId) {
        // Wait a bit for contacts to load, then try to load the chat
        setTimeout(() => {
            const contactItem = document.querySelector(`[data-contact-id="${contactId}"]`);
            if (contactItem) {
                const contactName = contactItem.querySelector('.contact-name').textContent;
                loadChat(contactId, contactName);
            } else {
                console.log('Contact not found:', contactId);
            }
        }, 100);
    }
    
    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        if (pollInterval) clearInterval(pollInterval);
        if (activityInterval) clearInterval(activityInterval);
        if (typingTimeout) clearTimeout(typingTimeout);
        updateActivity('offline');
    });
});