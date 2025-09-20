<?php
require_once '../config.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Get categories for dropdown
try {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        $category_id = $_POST['category_id'];
        $description = trim($_POST['description']);
        $short_description = trim($_POST['short_description']);
        $price = floatval($_POST['price']);
        $stock_quantity = intval($_POST['stock_quantity']);
        $status = $_POST['status'];
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        // Generate slug from name
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        
        // Handle image upload
        $image_filename = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $image_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $image_filename;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    throw new Exception('Failed to upload image');
                }
            } else {
                throw new Exception('Invalid image format. Use JPG, PNG, GIF, or WebP');
            }
        }
        
        $pdo = getDatabase();
        $stmt = $pdo->prepare("
            INSERT INTO products (category_id, name, slug, description, short_description, price, stock_quantity, status, featured, image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $category_id, $name, $slug, $description, $short_description, 
            $price, $stock_quantity, $status, $featured, $image_filename
        ]);
        
        if ($result) {
            $success = 'Product added successfully!';
            // Clear form data
            $_POST = [];
        }
        
    } catch (Exception $e) {
        $error = 'Error adding product: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 260px;
            background: #1e293b;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid #334155;
        }
        
        .sidebar-header h2 {
            color: #f1f5f9;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .nav-item {
            margin-bottom: 4px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #cbd5e1;
            text-decoration: none;
            padding: 12px 20px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: #334155;
            color: #3b82f6;
            border-left-color: #3b82f6;
        }
        
        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }
        
        .admin-main {
            flex: 1;
            margin-left: 260px;
            background: #f8fafc;
        }
        
        .main-header {
            background: #ffffff;
            padding: 20px 30px;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .page-subtitle {
            color: #64748b;
            font-size: 14px;
            margin-top: 4px;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .content-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 32px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-label {
            display: block;
            color: #374151;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #ffffff;
        }
        
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .file-input-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 100%;
        }
        
        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-display {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            background: #f9fafb;
            transition: all 0.3s ease;
        }
        
        .file-input-display:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .file-input-display i {
            color: #6b7280;
            font-size: 20px;
        }
        
        .file-input-text {
            color: #6b7280;
            font-size: 14px;
        }
        
        .image-preview {
            margin-top: 12px;
            display: none;
        }
        
        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-input {
            width: 18px;
            height: 18px;
            accent-color: #3b82f6;
        }
        
        .checkbox-label {
            color: #374151;
            font-size: 14px;
            cursor: pointer;
        }
        
        .button-group {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: #ffffff;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-store"></i> Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-chart-pie"></i> Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="products.php" class="nav-link active">
                        <i class="fas fa-box"></i> Products
                    </a>
                </div>
                <div class="nav-item">
                    <a href="categories.php" class="nav-link">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                </div>
                <div class="nav-item">
                    <a href="orders.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                </div>
                <div class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
                <div class="nav-item">
                    <a href="../index.php" class="nav-link" target="_blank">
                        <i class="fas fa-external-link-alt"></i> View Store
                    </a>
                </div>
                <div class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </nav>
        </aside>
        
        <main class="admin-main">
            <header class="main-header">
                <h1 class="page-title">Add New Product</h1>
                <p class="page-subtitle">Create a new product for your store</p>
            </header>
            
            <div class="main-content">
                <div class="content-card">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required placeholder="Enter product name">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Category *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Price ($) *</label>
                                <input type="number" name="price" step="0.01" min="0" class="form-input" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required placeholder="0.00">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Stock Quantity *</label>
                                <input type="number" name="stock_quantity" min="0" class="form-input" value="<?= htmlspecialchars($_POST['stock_quantity'] ?? '0') ?>" required placeholder="0">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-select" required>
                                    <option value="active" <?= (($_POST['status'] ?? 'active') == 'active') ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= (($_POST['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                    <option value="draft" <?= (($_POST['status'] ?? '') == 'draft') ? 'selected' : '' ?>>Draft</option>
                                </select>
                            </div>
                            
                            <div class="form-group full-width">
                                <label class="form-label">Product Image</label>
                                <div class="file-input-container">
                                    <input type="file" name="image" class="file-input" accept="image/*" onchange="previewImage(this)">
                                    <div class="file-input-display">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span class="file-input-text">Click to upload product image (JPG, PNG, GIF, WebP)</span>
                                    </div>
                                </div>
                                <div class="image-preview" id="imagePreview">
                                    <img id="previewImg" src="" alt="Preview">
                                </div>
                            </div>
                            
                            <div class="form-group full-width">
                                <label class="form-label">Short Description</label>
                                <input type="text" name="short_description" class="form-input" value="<?= htmlspecialchars($_POST['short_description'] ?? '') ?>" placeholder="Brief product description">
                            </div>
                            
                            <div class="form-group full-width">
                                <label class="form-label">Full Description</label>
                                <textarea name="description" class="form-textarea" placeholder="Detailed product description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group full-width">
                                <div class="checkbox-group">
                                    <input type="checkbox" name="featured" class="checkbox-input" <?= isset($_POST['featured']) ? 'checked' : '' ?>>
                                    <label class="checkbox-label">Featured Product</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="button-group">
                            <a href="products.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            const fileText = document.querySelector('.file-input-text');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                    fileText.textContent = input.files[0].name;
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
