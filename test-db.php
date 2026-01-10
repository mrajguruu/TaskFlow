<?php
/**
 * Database Connection Test Script
 * Use this to verify database connectivity and password hashes
 * DELETE THIS FILE after testing!
 */

require_once 'config/config.php';

try {
    echo "<h2>Database Connection Test</h2>";
    echo "<p>✅ Database connected successfully!</p>";

    // Test query - get admin user
    $stmt = $pdo->prepare("SELECT id, email, username, LEFT(password, 20) as password_preview FROM users WHERE email = ?");
    $stmt->execute(['admin@taskflow.com']);
    $user = $stmt->fetch();

    if ($user) {
        echo "<p>✅ Admin user found!</p>";
        echo "<pre>";
        echo "ID: " . $user['id'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Password hash (first 20 chars): " . $user['password_preview'] . "...\n";
        echo "</pre>";

        // Test password verification
        $stmt2 = $pdo->prepare("SELECT password FROM users WHERE email = ?");
        $stmt2->execute(['admin@taskflow.com']);
        $fullUser = $stmt2->fetch();

        $testPassword = 'password123';
        $isValid = password_verify($testPassword, $fullUser['password']);

        echo "<p>Password verification test for 'password123': " . ($isValid ? "✅ VALID" : "❌ INVALID") . "</p>";

    } else {
        echo "<p>❌ Admin user not found in database!</p>";
    }

    // Count total users
    $countStmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $countStmt->fetch();
    echo "<p>Total users in database: " . $count['count'] . "</p>";

    // List all user emails
    $usersStmt = $pdo->query("SELECT id, email FROM users ORDER BY id");
    $users = $usersStmt->fetchAll();
    echo "<h3>All users:</h3><ul>";
    foreach ($users as $u) {
        echo "<li>ID {$u['id']}: {$u['email']}</li>";
    }
    echo "</ul>";

} catch (PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>⚠️ DELETE THIS FILE after testing! (test-db.php)</strong></p>";
?>
