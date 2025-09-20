<?php
require_once '../config.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$product_id = $_GET['id'] ?? 0;

if ($product_id) {
    try {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $result = $stmt->execute([$product_id]);
        
        if ($result) {
            $_SESSION['success'] = 'Product deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete product.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error deleting product: ' . $e->getMessage();
    }
}

header('Location: products.php');
exit;
?>
