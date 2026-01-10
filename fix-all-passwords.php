<?php
/**
 * Fix All User Passwords
 * Sets all users' passwords to 'password123' with correct BCrypt hash
 *
 * ⚠️ DELETE THIS FILE AFTER RUNNING!
 */

require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><title>Fix All Passwords</title><style>body{font-family:system-ui;max-width:900px;margin:50px auto;padding:20px}h1{color:#2563eb}.success{color:#059669;padding:15px;background:#d1fae5;border-radius:5px;margin:15px 0}.error{color:#dc2626;padding:15px;background:#fee2e2;border-radius:5px;margin:15px 0}.info{color:#0284c7;padding:15px;background:#e0f2fe;border-radius:5px;margin:15px 0}table{width:100%;border-collapse:collapse;margin:20px 0}th,td{border:1px solid #ddd;padding:12px;text-align:left}th{background:#2563eb;color:white}</style></head><body>";

echo "<h1>🔐 Fix All User Passwords</h1>";
echo "<p>Setting all users' passwords to: <strong>password123</strong></p>";

try {
    // Get all users
    $usersStmt = $pdo->query("SELECT id, username, email, role FROM users ORDER BY id");
    $users = $usersStmt->fetchAll();

    echo "<div class='info'>";
    echo "<h3>Found " . count($users) . " users to update</h3>";
    echo "</div>";

    // Generate new password hash
    $newPassword = 'password123';
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);

    echo "<div class='info'>";
    echo "<h3>Generated Password Hash</h3>";
    echo "<p><code>" . htmlspecialchars($newHash) . "</code></p>";
    echo "</div>";

    // Update all users
    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updated = 0;
    $failed = 0;

    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th></tr>";

    foreach ($users as $user) {
        try {
            $result = $updateStmt->execute([$newHash, $user['id']]);

            if ($result) {
                echo "<tr style='background:#d1fae5'>";
                echo "<td>{$user['id']}</td>";
                echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td><strong>" . htmlspecialchars($user['role']) . "</strong></td>";
                echo "<td style='color:#059669'>✅ Updated</td>";
                echo "</tr>";
                $updated++;
            } else {
                echo "<tr style='background:#fee2e2'>";
                echo "<td>{$user['id']}</td>";
                echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                echo "<td style='color:#dc2626'>❌ Failed</td>";
                echo "</tr>";
                $failed++;
            }
        } catch (PDOException $e) {
            echo "<tr style='background:#fee2e2'>";
            echo "<td>{$user['id']}</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td style='color:#dc2626'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</td>";
            echo "</tr>";
            $failed++;
        }
    }

    echo "</table>";

    // Verify all passwords work
    echo "<h2>Password Verification</h2>";

    $verifyStmt = $pdo->query("SELECT id, username, email, password FROM users ORDER BY id");
    $verifyUsers = $verifyStmt->fetchAll();

    $verified = 0;
    $verifyFailed = 0;

    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Verification</th></tr>";

    foreach ($verifyUsers as $user) {
        $isValid = password_verify($newPassword, $user['password']);

        if ($isValid) {
            echo "<tr style='background:#d1fae5'>";
            echo "<td>{$user['id']}</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td style='color:#059669'>✅ Password works!</td>";
            echo "</tr>";
            $verified++;
        } else {
            echo "<tr style='background:#fee2e2'>";
            echo "<td>{$user['id']}</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td style='color:#dc2626'>❌ Verification failed</td>";
            echo "</tr>";
            $verifyFailed++;
        }
    }

    echo "</table>";

    // Final summary
    if ($verified === count($users) && $failed === 0) {
        echo "<div class='success'>";
        echo "<h2>🎉 All Passwords Fixed!</h2>";
        echo "<p><strong>Updated:</strong> {$updated} users</p>";
        echo "<p><strong>Verified:</strong> {$verified} users</p>";
        echo "<p><strong>All users can now login with:</strong> password123</p>";
        echo "</div>";

        echo "<div class='info'>";
        echo "<h3>Demo User Credentials</h3>";
        echo "<table>";
        echo "<tr><th>Email</th><th>Password</th><th>Role</th></tr>";

        $displayStmt = $pdo->query("SELECT email, role FROM users ORDER BY id");
        $displayUsers = $displayStmt->fetchAll();

        foreach ($displayUsers as $u) {
            echo "<tr>";
            echo "<td><code>" . htmlspecialchars($u['email']) . "</code></td>";
            echo "<td><code>password123</code></td>";
            echo "<td><strong>" . htmlspecialchars($u['role']) . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";

        echo "<div class='success'>";
        echo "<p><a href='auth/login.php' style='background:#2563eb;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;display:inline-block;font-weight:bold'>Test Login Now</a></p>";
        echo "</div>";

    } else {
        echo "<div class='error'>";
        echo "<h3>⚠️ Some Passwords Failed</h3>";
        echo "<p><strong>Updated:</strong> {$updated}</p>";
        echo "<p><strong>Failed:</strong> {$failed}</p>";
        echo "<p><strong>Verified:</strong> {$verified}</p>";
        echo "<p><strong>Verify Failed:</strong> {$verifyFailed}</p>";
        echo "</div>";
    }

} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='background:#fef3c7;padding:15px;border-radius:5px;margin:15px 0;color:#92400e'>";
echo "<h3>⚠️ IMPORTANT</h3>";
echo "<p><strong>DELETE this file after running:</strong> fix-all-passwords.php</p>";
echo "</div>";

echo "</body></html>";
?>
