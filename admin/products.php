<?php
require_once '../config.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Get products with category info
try {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    $products = [];
    $error = 'Error loading products: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin</title>
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
        .btn { padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 20px; }
        .btn:hover { background: #0056b3; }
        .table { width: 100%; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .status-active { color: #28a745; font-weight: 600; }
        .status-inactive { color: #dc3545; font-weight: 600; }
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
            <h1>Products Management</h1>
            
            <a href="add-product.php" class="btn">+ Add New Product</a>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category_name'] ?? 'No Category') ?></td>
                            <td>$<?= number_format($product['price'], 2) ?></td>
                            <td><?= $product['stock_quantity'] ?></td>
                            <td>
                                <span class="status-<?= $product['status'] ?>"><?= ucfirst($product['status']) ?></span>
                            </td>
                            <td><?= date('M j, Y', strtotime($product['created_at'])) ?></td>
                            <td>
                                <a href="edit-product.php?id=<?= $product['id'] ?>" style="color: #007bff; text-decoration: none;">Edit</a> |
                                <a href="delete-product.php?id=<?= $product['id'] ?>" style="color: #dc3545; text-decoration: none;" onclick="return confirm('Delete this product?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #6c757d;">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
