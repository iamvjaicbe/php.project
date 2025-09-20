<?php
require_once '../config.php';

// Start session
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (!empty($username) && !empty($password)) {
        try {
            $pdo = getDatabase();
            $stmt = $pdo->prepare("SELECT id, username, password_hash, status FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                echo "<!-- Debug: User found, status: " . $admin['status'] . " -->";
                
                if ($admin['status'] === 'active') {
                    echo "<!-- Debug: Testing password verification -->";
                    
                    if (password_verify($password, $admin['password_hash'])) {
                        echo "<!-- Debug: Password verified successfully -->";
                        
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_username'] = $admin['username'];
                        
                        header('Location: index.php');
                        exit;
                    } else {
                        $error = 'Invalid password';
                        echo "<!-- Debug: Password verification failed -->";
                        echo "<!-- Debug: Hash: " . substr($admin['password_hash'], 0, 20) . "... -->";
                    }
                } else {
                    $error = 'Account is inactive';
                }
            } else {
                $error = 'User not found';
                echo "<!-- Debug: No user found with username: $username -->";
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
            echo "<!-- Debug: Database error: " . $e->getMessage() . " -->";
        }
    } else {
        $error = 'Please fill in all fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - E-Commerce</title>
    <style>
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #007bff, #17a2b8); margin: 0; padding: 20px; }
        .login-container { max-width: 400px; margin: 100px auto; }
        .login-form { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input { width: 100%; padding: 12px; border: 2px solid #f8f9fa; border-radius: 8px; font-size: 16px; box-sizing: border-box; }
        .form-group input:focus { outline: none; border-color: #007bff; }
        .btn { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .error-message { background: #dc3545; color: white; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .login-help { margin-top: 20px; text-align: center; font-size: 14px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h1>Admin Login</h1>
            
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="admin" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" value="admin123" required>
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
            
            <div class="login-help">
                <p><strong>Test Credentials:</strong></p>
                <p>Username: admin</p>
                <p>Password: admin123</p>
            </div>
        </div>
    </div>
</body>
</html>
