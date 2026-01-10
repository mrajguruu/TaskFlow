<?php
/**
 * Create Test Users for Cron Job Testing
 * Creates 10 temporary test users to verify cleanup script
 *
 * ⚠️ DELETE THIS FILE AFTER TESTING!
 */

require_once 'config/config.php';
require_once 'includes/functions.php';

// Prevent direct browser access without confirmation
$confirmation = $_GET['confirm'] ?? '';
if ($confirmation !== 'yes') {
    die('<h1>⚠️ Confirmation Required</h1><p>Are you sure you want to create 10 test users?</p><p><a href="?confirm=yes" style="background:#2563eb;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:bold;">Yes, Create Test Users</a></p>');
}

echo "<!DOCTYPE html><html><head><title>Create Test Users</title><style>body{font-family:system-ui;max-width:900px;margin:50px auto;padding:20px}h1{color:#2563eb}.success{color:#059669;padding:10px;background:#d1fae5;border-radius:5px;margin:10px 0}.error{color:#dc2626;padding:10px;background:#fee2e2;border-radius:5px;margin:10px 0}.info{color:#0284c7;padding:10px;background:#e0f2fe;border-radius:5px;margin:10px 0}table{width:100%;border-collapse:collapse;margin:20px 0}th,td{border:1px solid #ddd;padding:10px;text-align:left}th{background:#2563eb;color:white}pre{background:#f3f4f6;padding:10px;border-radius:5px;font-size:12px}</style></head><body>";

echo "<h1>🧪 Creating Test Users for Cron Job Testing</h1>";

try {
    $password = 'testuser123';
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $createdUsers = [];
    $errors = [];

    for ($i = 1; $i <= 10; $i++) {
        $username = "testuser{$i}";
        $email = "testuser{$i}@example.com";
        $fullName = "Test User {$i}";

        try {
            // Check if user already exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $checkStmt->execute([$email, $username]);

            if ($checkStmt->fetch()) {
                $errors[] = "User {$username} already exists - skipped";
                continue;
            }

            // Create user
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, full_name, avatar, role, created_at, last_login)
                VALUES (?, ?, ?, ?, 'default-avatar.png', 'member', NOW(), NOW())
            ");

            $stmt->execute([
                $username,
                $email,
                $passwordHash,
                $fullName
            ]);

            $userId = $pdo->lastInsertId();

            $createdUsers[] = [
                'id' => $userId,
                'username' => $username,
                'email' => $email,
                'full_name' => $fullName
            ];

        } catch (PDOException $e) {
            $errors[] = "Failed to create {$username}: " . $e->getMessage();
        }
    }

    // Display results
    if (!empty($createdUsers)) {
        echo "<div class='success'>";
        echo "<h2>✅ Successfully Created " . count($createdUsers) . " Test Users</h2>";
        echo "</div>";

        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Password</th></tr>";

        foreach ($createdUsers as $user) {
            echo "<tr>";
            echo "<td><strong>{$user['id']}</strong></td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['full_name']}</td>";
            echo "<td><code>testuser123</code></td>";
            echo "</tr>";
        }

        echo "</table>";

        echo "<div class='info'>";
        echo "<h3>📋 Test User Details:</h3>";
        echo "<ul>";
        echo "<li><strong>Count:</strong> " . count($createdUsers) . " users</li>";
        echo "<li><strong>Password:</strong> <code>testuser123</code> (same for all)</li>";
        echo "<li><strong>Role:</strong> member</li>";
        echo "<li><strong>Created:</strong> " . date('Y-m-d H:i:s') . "</li>";
        echo "</ul>";
        echo "</div>";

        echo "<div class='info'>";
        echo "<h3>⏰ Cron Job Testing Instructions:</h3>";
        echo "<ol>";
        echo "<li><strong>Wait 1 hour</strong> after creating these users</li>";
        echo "<li><strong>Manually trigger</strong> the cron job:</li>";
        echo "<pre>https://taskflow-wbld.onrender.com/cron/cleanup-temp-users.php?token=YOUR_TOKEN</pre>";
        echo "<li><strong>Expected result:</strong> All " . count($createdUsers) . " test users should be deleted</li>";
        echo "<li><strong>Demo users (IDs 1-8)</strong> will remain protected</li>";
        echo "</ol>";
        echo "</div>";

        // Show cron schedule
        echo "<div class='info'>";
        echo "<h3>🔄 Automatic Cron Schedule (Every 2 Hours)</h3>";
        echo "<p><strong>Cron expression:</strong> <code>0 */2 * * *</code></p>";
        echo "<p><strong>Runs at:</strong> 00:00, 02:00, 04:00, 06:00, 08:00, 10:00, 12:00, 14:00, 16:00, 18:00, 20:00, 22:00</p>";
        echo "<p><strong>Cleanup age:</strong> Users older than 1 hour</p>";
        echo "<p><strong>Protected:</strong> Demo users (IDs 1-8) are never deleted</p>";
        echo "</div>";
    }

    if (!empty($errors)) {
        echo "<div class='error'>";
        echo "<h3>⚠️ Some Users Could Not Be Created</h3>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>{$error}</li>";
        }
        echo "</ul>";
        echo "</div>";
    }

    if (empty($createdUsers) && empty($errors)) {
        echo "<div class='error'>";
        echo "<h3>❌ No Users Created</h3>";
        echo "<p>Something went wrong. Check database connection.</p>";
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='background:#fef3c7;padding:15px;border-radius:5px;margin:15px 0;color:#92400e'>";
echo "<h3>⚠️ IMPORTANT</h3>";
echo "<ul>";
echo "<li><strong>DELETE this file after testing:</strong> create-test-users.php</li>";
echo "<li><strong>Test users will be auto-deleted</strong> after 1 hour by the cron job</li>";
echo "<li><strong>Demo users (1-8) are protected</strong> and will never be deleted</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='dashboard/index.php' style='background:#2563eb;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:bold;margin:10px 0'>Go to Dashboard</a></p>";

echo "</body></html>";
?>
