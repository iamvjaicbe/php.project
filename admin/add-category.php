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
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $display_order = intval($_POST['display_order']);
        $status = $_POST['status'];
        
        // Generate slug from name
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        
        $pdo = getDatabase();
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, display_order, status) VALUES (?, ?, ?, ?, ?)");
        
        $result = $stmt->execute([$name, $slug, $description, $display_order, $status]);
        
        if ($result) {
            $success = 'Category added successfully!';
            $_POST = []; // Clear form
        }
        
    } catch (Exception $e) {
        $error = 'Error adding category: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .admin-container { display: flex; min-height: 100vh; }
        .admin-nav { width: 280px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1); padding: 2rem 0; }
        .nav-brand { padding: 0 2rem 2rem; border-bottom: 1px solid rgba(0, 0, 0, 0.1); margin-bottom: 2rem; }
        .nav-brand h2 { color: #2d3748; font-weight: 700; font-size: 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
        .nav-menu { list-style: none; padding: 0; margin: 0; }
        .nav-item { margin-bottom: 0.5rem; }
        .nav-link { display: flex; align-items: center; gap: 0.75rem; color: #4a5568; text-decoration: none; padding: 0.875rem 2rem; transition: all 0.3s ease; font-weight: 500; }
        .nav-link:hover, .nav-link.active { background: linear-gradient(90deg, #667eea, #764ba2); color: white; transform: translateX(5px); }
        .nav-link i { width: 20px; text-align: center; }
        .admin-main { flex: 1; padding: 2rem; background: rgba(255, 255, 255, 0.1); }
        .page-header { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 2rem; border-radius: 20px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1); margin-bottom: 2rem; }
        .page-title { color: #2d3748; font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .page-subtitle { color: #718096; font-size: 1rem; }
        .form-container { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 2.5rem; border-radius: 20px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1); }
        .form-group { margin-bottom: 1.5rem; }
        .form-label { display: block; color: #2d3748; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .form-input, .form-select, .form-textarea { width: 100%; padding: 0.875rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(5px); }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); background: rgba(255, 255, 255, 0.95); }
        .form-textarea { min-height: 120px; resize: vertical; }
        .button-group { display: flex; gap: 1rem; margin-top: 2rem; justify-content: flex-end; }
        .btn { padding: 0.875rem 2rem; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6); }
        .btn-secondary { background: rgba(255, 255, 255, 0.9); color: #4a5568; border: 2px solid #e2e8f0; }
        .btn-secondary:hover { background: rgba(255, 255, 255, 1); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
        .alert { padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 500; }
        .alert-success { background: linear-gradient(135deg, #48bb78, #38a169); color: white; border: none; }
        .alert-error { background: linear-gradient(135deg, #f56565, #e53e3e); color: white; border: none; }
    </style>
</head>
<body>
    <div class="admin-container">
        <nav class="admin-nav">
            <div class="nav-brand">
                <h2><i class="fas fa-store"></i> Admin Panel</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                <li class="nav-item"><a href="products.php" class="nav-link"><i class="fas fa-box"></i> Products</a></li>
                <li class="nav-item"><a href="categories.php" class="nav-link active"><i class="fas fa-tags"></i> Categories</a></li>
                <li class="nav-item"><a href="orders.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Settings</a></li>
                <li class="nav-item"><a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i> View Store</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
        
        <main class="admin-main">
            <div class="page-header">
                <h1 class="page-title">Add New Category</h1>
                <p class="page-subtitle">Create a new product category</p>
            </div>
            
            <div class="form-container">
                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required placeholder="Enter category name">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-textarea" placeholder="Category description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="display_order" min="0" class="form-input" value="<?= htmlspecialchars($_POST['display_order'] ?? '0') ?>" placeholder="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" <?= (($_POST['status'] ?? 'active') == 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (($_POST['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="button-group">
                        <a href="categories.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Category</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
