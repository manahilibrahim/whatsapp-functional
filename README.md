# WhatsApp Clone - Contact-Based Messaging

A complete WhatsApp clone built with PHP, MySQL, and vanilla JavaScript. Features contact-based messaging where users can only see and chat with their saved contacts.

## Features

- **Contact-Based System**: No global user list - users only see saved contacts
- **Real-Time Messaging**: 1-to-1 conversations with instant message delivery
- **Save Contact Flow**: Banner appears when messaging unsaved contacts
- **Online Status**: Live typing indicators and online/offline status
- **WhatsApp-like UI**: Green bubbles for sent messages, gray for received
- **Mobile Responsive**: Works on desktop and mobile devices
- **Secure**: Password hashing, PDO prepared statements, session management

## Database Schema

### Tables

1. **users** - User accounts
   - `id`, `name`, `email`, `phone`, `password`, `created_at`

2. **contacts** - Saved contacts only
   - `id`, `owner_id`, `contact_user_id`, `label`, `status`, `created_at`

3. **messages** - 1-to-1 conversations
   - `id`, `sender_id`, `receiver_id`, `body`, `created_at`

4. **user_activity** - Online status and typing indicators
   - `id`, `user_id`, `status`, `last_seen`, `last_activity`

## Setup Instructions

### Step 1: Database Setup

1. **Import Schema:**
   ```sql
   -- Run schema.sql in your MySQL database
   -- This creates all necessary tables
   ```

2. **Update Database Credentials:**
   - Edit `config.php`
   - Update host, database name, username, password

### Step 2: Upload Files

Upload all files to your web server:

```
whatsapp-clone/
├── config.php          # Database configuration
├── login.php           # User login
├── signup.php          # User registration
├── logout.php          # Session logout
├── index.php           # Main chat interface
├── add_contact.php     # Add contact endpoint
├── remove_contact.php  # Remove contact endpoint
├── send.php            # Send message endpoint
├── fetch.php           # Fetch messages endpoint
├── update_activity.php # Update user status
├── app.js              # Frontend JavaScript
├── style.css           # WhatsApp-like styling
├── schema.sql          # Database structure
└── README.md           # This file
```

### Step 3: Configure

1. **Edit `config.php`:**
   ```php
   $host = 'your-host';
   $dbname = 'your-database';
   $username = 'your-username';
   $password = 'your-password';
   ```

2. **Set proper file permissions:**
   ```bash
   chmod 644 *.php
   chmod 644 *.css
   chmod 644 *.js
   ```

### Step 4: Test

1. **Visit your domain:**
   ```
   https://yourdomain.com/whatsapp-clone/
   ```

2. **Create accounts:**
   - Sign up with email or phone + password
   - Create multiple test accounts

3. **Add contacts:**
   - Use the + button to add contacts by email/phone
   - Only saved contacts appear in the list

4. **Start messaging:**
   - Click on a contact to open chat
   - Send messages - they appear in real-time
   - Test with multiple browser tabs

## How It Works

### Contact System

1. **No Global User List**: Users only see their saved contacts
2. **Add Contact**: Enter email/phone to add someone as contact
3. **Save Contact Banner**: Appears when messaging unsaved users
4. **Contact Management**: Remove contacts with ❌ button

### Messaging Flow

1. **1-to-1 Only**: All conversations are between 2 users
2. **Real-Time Updates**: Messages refresh every 1 second
3. **Message Bubbles**: Green for sent, gray for received
4. **Typing Indicators**: Shows when someone is typing
5. **Online Status**: Green dots show who's online

### Security Features

- **Password Hashing**: Uses `password_hash()` and `password_verify()`
- **PDO Prepared Statements**: Prevents SQL injection
- **Session Management**: Secure user authentication
- **Input Validation**: All inputs are validated and escaped
- **XSS Protection**: Output is escaped with `htmlspecialchars()`

## File Structure

### Backend (PHP)

- **config.php**: Database connection and helper functions
- **login.php/signup.php**: User authentication
- **index.php**: Main chat interface
- **add_contact.php**: Add new contact
- **remove_contact.php**: Remove contact
- **send.php**: Send message endpoint
- **fetch.php**: Get messages endpoint
- **update_activity.php**: Update user status

### Frontend (JavaScript/CSS)

- **app.js**: Real-time messaging, contact management
- **style.css**: WhatsApp-like responsive design

### Database

- **schema.sql**: Complete database structure

## API Endpoints

### Authentication
- `POST /login.php` - User login
- `POST /signup.php` - User registration
- `GET /logout.php` - User logout

### Contacts
- `POST /add_contact.php` - Add contact
- `POST /remove_contact.php` - Remove contact

### Messaging
- `POST /send.php` - Send message
- `GET /fetch.php?receiver_id=X` - Get messages
- `POST /update_activity.php` - Update user status

## Browser Support

- Chrome, Firefox, Safari, Edge
- Mobile responsive design
- Works on all modern browsers

## Troubleshooting

### Common Issues

1. **Database Connection Error:**
   - Check credentials in `config.php`
   - Ensure database exists
   - Verify MySQL is running

2. **Messages Not Loading:**
   - Check browser console for errors
   - Verify `fetch.php` is accessible
   - Check database permissions

3. **Contacts Not Saving:**
   - Check `add_contact.php` permissions
   - Verify database tables exist
   - Check for JavaScript errors

4. **Styling Issues:**
   - Ensure `style.css` is uploaded
   - Check file permissions (644)
   - Clear browser cache

### Debug Mode

Add this to any PHP file for debugging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Sample Data

After running `schema.sql`, you can test with:
- Email: `alice@example.com` / Password: `password`
- Email: `bob@example.com` / Password: `password`
- Email: `charlie@example.com` / Password: `password`

## License

This project is for educational purposes. Use responsibly.

---

**Ready to use!** Upload files, import schema, configure database, and start messaging! 🚀