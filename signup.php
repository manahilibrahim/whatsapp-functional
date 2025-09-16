<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name)) {
        $error = 'Name is required';
    } elseif (empty($email) && empty($phone)) {
        $error = 'Email or phone number is required';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        try {
            // Check if email already exists
            if (!empty($email)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email already registered';
                }
            }
            
            // Check if phone already exists
            if (empty($error) && !empty($phone)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
                $stmt->execute([$phone]);
                if ($stmt->fetch()) {
                    $error = 'Phone number already registered';
                }
            }
            
            if (empty($error)) {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $email ?: null, $phone ?: null, $hashed_password]);
                
                $success = 'Account created successfully! You can now login.';
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - WhatsApp Clone</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="whatsapp-logo">
                <h1>WhatsApp</h1>
                <div class="subtitle">Create your account</div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Enter your full name">
                </div>
                
                <div class="form-group">
                    <label for="email">Email (optional)</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number (optional)</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="Enter your phone number">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Create a password">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
                </div>
                
                <button type="submit" class="btn-primary">Sign Up</button>
            </form>
            
            <p class="auth-link">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>