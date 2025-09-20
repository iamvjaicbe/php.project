<?php
require_once '../config.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$category_id = $_GET['id'] ?? 0;

if ($category_id) {
    try {
        $pdo = getDatabase();
        
        // Check if category has products
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $product_count = $stmt->fetchColumn();
        
        if ($product_count > 0) {
            $_SESSION['error'] = 'Cannot delete category with existing products. Move products first.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $result = $stmt->execute([$category_id]);
            
            if ($result) {
                $_SESSION['success'] = 'Category deleted successfully!';
            } else {
                $_SESSION['error'] = 'Failed to delete category.';
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error deleting category: ' . $e->getMessage();
    }
}

header('Location: categories.php');
exit;
?>
