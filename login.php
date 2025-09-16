<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($login) || empty($password)) {
        $error = 'Please enter both login and password';
    } else {
        try {
            // Check if login is email or phone
            $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
            $field = $isEmail ? 'email' : 'phone';
            
            $stmt = $pdo->prepare("SELECT id, name, email, phone, password FROM users WHERE $field = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid login credentials';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WhatsApp Clone</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="whatsapp-logo">
                <h1>WhatsApp</h1>
                <div class="subtitle">Login to your account</div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="login">Email or Phone Number</label>
                    <input type="text" id="login" name="login" required value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" placeholder="Enter your email or phone">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn-primary">Login</button>
            </form>
            
            <p class="auth-link">
                Don't have an account? <a href="signup.php">Sign up here</a>
            </p>
        </div>
    </div>
</body>
</html>