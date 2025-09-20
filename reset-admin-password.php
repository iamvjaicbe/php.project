<?php
require_once 'config.php';

echo "<h2>Admin Password Reset Tool</h2>";

try {
    $pdo = getDatabase();
    
    // Get current admin user
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<h3>Current Admin User:</h3>";
        echo "<p>Username: " . $admin['username'] . "</p>";
        echo "<p>Email: " . $admin['email'] . "</p>";
        echo "<p>Status: " . $admin['status'] . "</p>";
        echo "<p>Created: " . $admin['created_at'] . "</p>";
        
        // Test current password
        $test_password = 'admin123';
        echo "<h3>Testing Current Password:</h3>";
        
        if (password_verify($test_password, $admin['password_hash'])) {
            echo "<p style='color: green;'>✅ Current password works with 'admin123'</p>";
        } else {
            echo "<p style='color: red;'>❌ Current password doesn't work</p>";
            echo "<p>Creating new password hash...</p>";
            
            // Create new password hash
            $new_password = 'admin123';
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
            $result = $stmt->execute([$new_hash]);
            
            if ($result) {
                echo "<p style='color: green;'>✅ Password updated successfully!</p>";
                echo "<p><strong>New Credentials:</strong></p>";
                echo "<p>Username: admin</p>";
                echo "<p>Password: admin123</p>";
                
                // Test new password
                if (password_verify($new_password, $new_hash)) {
                    echo "<p style='color: green;'>✅ New password verified successfully!</p>";
                } else {
                    echo "<p style='color: red;'>❌ New password verification failed</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Failed to update password</p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>❌ Admin user not found</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<br><hr><br>";
echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";
?>
