<?php
/**
 * PHP E-Commerce Configuration
 */

// Prevent direct access
defined('APP_ROOT') or define('APP_ROOT', dirname(__FILE__));

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce_shop');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Database connection options
$db_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

/**
 * Get database connection
 */
function getDatabase() {
    static $pdo = null;
    
    if ($pdo === null) {
        global $db_options;
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $db_options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format price
 */
function formatPrice($price, $currency_symbol = '$') {
    return $currency_symbol . number_format($price, 2);
}

/**
 * Log message
 */
function logMessage($message, $level = 'INFO') {
    $log_file = APP_ROOT . '/logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$level}: {$message}" . PHP_EOL;
    
    if (!is_dir(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Create required directories
$required_dirs = [APP_ROOT . '/uploads', APP_ROOT . '/cache', APP_ROOT . '/logs'];
foreach ($required_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
