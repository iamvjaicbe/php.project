<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

try {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
    $stmt->execute();
    $product_count = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM categories WHERE status = 'active'");
    $stmt->execute();
    $category_count = $stmt->fetchColumn();
} catch (PDOException $e) {
    $product_count = 0;
    $category_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container">
        <nav class="admin-nav">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../index.php" target="_blank">View Store</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <main class="admin-main">
            <h1>Dashboard</h1>
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Products</h3>
                    <div class="stat-number"><?= $product_count ?></div>
                </div>
                <div class="stat-card">
                    <h3>Categories</h3>
                    <div class="stat-number"><?= $category_count ?></div>
                </div>
            </div>
            <p>Welcome to your e-commerce admin panel!</p>
        </main>
    </div>
</body>
</html>