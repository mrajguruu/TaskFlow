<?php
/**
 * Password Fix Script
 * Updates admin password to 'password123'
 *
 * ⚠️ DELETE THIS FILE AFTER RUNNING!
 */

require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><title>Fix Password</title><style>body{font-family:system-ui;max-width:800px;margin:50px auto;padding:20px}h1{color:#2563eb}.success{color:#059669;padding:15px;background:#d1fae5;border-radius:5px;margin:15px 0}.error{color:#dc2626;padding:15px;background:#fee2e2;border-radius:5px;margin:15px 0}.info{color:#0284c7;padding:15px;background:#e0f2fe;border-radius:5px;margin:15px 0}.warning{background:#fef3c7;padding:15px;border-radius:5px;margin:15px 0;color:#92400e}</style></head><body>";

echo "<h1>🔧 Password Fix Script</h1>";

try {
    // Check if admin exists
    $checkStmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
    $checkStmt->execute(['admin@taskflow.com']);
    $admin = $checkStmt->fetch();

    if (!$admin) {
        echo "<div class='error'>❌ Admin user not found!</div>";
        exit;
    }

    echo "<div class='info'>✅ Found admin user (ID: {$admin['id']}, Email: {$admin['email']})</div>";

    // Generate correct password hash for 'password123'
    $newPassword = 'password123';
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);

    echo "<div class='info'>🔐 Generated new password hash</div>";

    // Update password
    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $result = $updateStmt->execute([$newHash, 'admin@taskflow.com']);

    if ($result) {
        echo "<div class='success'>";
        echo "<h2>✅ Password Updated Successfully!</h2>";
        echo "<p><strong>Email:</strong> admin@taskflow.com</p>";
        echo "<p><strong>New Password:</strong> password123</p>";
        echo "</div>";

        // Verify it works
        $verifyStmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
        $verifyStmt->execute(['admin@taskflow.com']);
        $verifyData = $verifyStmt->fetch();

        $testVerify = password_verify($newPassword, $verifyData['password']);

        if ($testVerify) {
            echo "<div class='success'>";
            echo "<h3>✅ Password Verification: PASSED</h3>";
            echo "<p>You can now login!</p>";
            echo "<p><a href='auth/login.php' style='background:#2563eb;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;display:inline-block;font-weight:bold'>Go to Login Page</a></p>";
            echo "</div>";
        } else {
            echo "<div class='error'>❌ Verification test failed. Something went wrong.</div>";
        }

    } else {
        echo "<div class='error'>❌ Failed to update password</div>";
    }

} catch (PDOException $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<hr>";
echo "<div class='warning'>";
echo "<h3>⚠️ IMPORTANT: Next Steps</h3>";
echo "<ol>";
echo "<li><strong>Test Login:</strong> Try logging in with admin@taskflow.com / password123</li>";
echo "<li><strong>DELETE THIS FILE:</strong> Remove <code>fix-password.php</code> from your repository immediately!</li>";
echo "<li><strong>Also delete:</strong> <code>check-database.php</code>, <code>test-db.php</code>, <code>import-database.php</code></li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
