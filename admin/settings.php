<?php
require_once '../config.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDatabase();
        
        foreach ($_POST as $key => $value) {
            if ($key !== 'submit') {
                $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
                $stmt->execute([$key, $value, $value]);
            }
        }
        
        $success = 'Settings updated successfully!';
    } catch (Exception $e) {
        $error = 'Error updating settings: ' . $e->getMessage();
    }
}

// Get current settings
try {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT `key`, `value` FROM settings");
    $stmt->execute();
    $settings_data = $stmt->fetchAll();
    
    $settings = [];
    foreach ($settings_data as $setting) {
        $settings[$setting['key']] = $setting['value'];
    }
} catch (Exception $e) {
    $settings = [];
    $error = 'Error loading settings: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Settings - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f8f9fa; }
        .admin-container { display: flex; min-height: 100vh; }
        .admin-nav { width: 250px; background: #343a40; color: white; padding: 20px 0; }
        .admin-nav h2 { padding: 0 20px 20px; border-bottom: 1px solid #495057; margin-bottom: 20px; }
        .admin-nav ul { list-style: none; margin: 0; padding: 0; }
        .admin-nav li { margin-bottom: 8px; }
        .admin-nav a { display: block; color: white; text-decoration: none; padding: 12px 20px; }
        .admin-nav a:hover { background: rgba(255,255,255,0.1); }
        .admin-main { flex: 1; padding: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group textarea { width: 100%; max-width: 500px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <nav class="admin-nav">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../index.php" target="_blank">View Store</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <main class="admin-main">
            <h1>Shop Settings</h1>
            
            <?php if ($success): ?>
                <div class="success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="shop_name">Shop Name</label>
                    <input type="text" id="shop_name" name="shop_name" value="<?= htmlspecialchars($settings['shop_name'] ?? 'MyShop') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="currency_symbol">Currency Symbol</label>
                    <input type="text" id="currency_symbol" name="currency_symbol" value="<?= htmlspecialchars($settings['currency_symbol'] ?? '$') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="whatsapp_number">WhatsApp Number (include country code)</label>
                    <input type="text" id="whatsapp_number" name="whatsapp_number" value="<?= htmlspecialchars($settings['whatsapp_number'] ?? '+1234567890') ?>" placeholder="+1234567890">
                </div>
                
                <div class="form-group">
                    <label for="banner_text">Promotional Banner Text</label>
                    <textarea id="banner_text" name="banner_text" rows="3"><?= htmlspecialchars($settings['banner_text'] ?? '🎉 Welcome to our store! Free shipping on orders over $50!') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="primary_color">Primary Color</label>
                    <input type="color" id="primary_color" name="primary_color" value="<?= htmlspecialchars($settings['primary_color'] ?? '#007bff') ?>">
                </div>
                
                <div class="form-group">
                    <label for="secondary_color">Secondary Color</label>
                    <input type="color" id="secondary_color" name="secondary_color" value="<?= htmlspecialchars($settings['secondary_color'] ?? '#6c757d') ?>">
                </div>
                
                <button type="submit" name="submit" class="btn">Update Settings</button>
            </form>
        </main>
    </div>
</body>
</html>
