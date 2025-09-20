<?php
require_once '../config.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin</title>
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
        .info-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
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
            <h1>Orders Management</h1>
            
            <div class="info-box">
                <h3>📋 Order Management Coming Soon</h3>
                <p>Orders are currently shared via WhatsApp.</p>
                <p>This section will display order history and management tools.</p>
            </div>
        </main>
    </div>
</body>
</html>
