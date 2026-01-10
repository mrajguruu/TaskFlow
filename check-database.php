<?php
/**
 * Database Status Checker
 * Shows what's in your TiDB database and helps debug login issues
 */

require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><title>Database Check</title><style>body{font-family:system-ui;max-width:900px;margin:50px auto;padding:20px}h1{color:#2563eb}.success{color:#059669;padding:15px;background:#d1fae5;border-radius:5px;margin:15px 0}.error{color:#dc2626;padding:15px;background:#fee2e2;border-radius:5px;margin:15px 0}.info{color:#0284c7;padding:15px;background:#e0f2fe;border-radius:5px;margin:15px 0}table{width:100%;border-collapse:collapse;margin:20px 0}th,td{border:1px solid #ddd;padding:12px;text-align:left}th{background:#2563eb;color:white}tr:nth-child(even){background:#f3f4f6}.warning{background:#fef3c7;padding:15px;border-radius:5px;margin:15px 0;color:#92400e}</style></head><body>";

echo "<h1>🔍 TaskFlow Database Status</h1>";

try {
    // Check connection
    echo "<div class='success'>✅ Database connection successful!</div>";

    // Count all tables
    echo "<h2>📊 Database Contents</h2>";

    $tables = ['users', 'projects', 'tasks', 'comments', 'project_members', 'task_assignments', 'attachments', 'activity_log'];

    echo "<table>";
    echo "<tr><th>Table</th><th>Row Count</th><th>Status</th></tr>";

    $totalRows = 0;
    $tableStats = [];

    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `{$table}`");
            $count = $stmt->fetch()['count'];
            $totalRows += $count;
            $tableStats[$table] = $count;

            $status = $count > 0 ? "✅ Has data" : "⚠️ Empty";
            $statusClass = $count > 0 ? "success" : "warning";

            echo "<tr>";
            echo "<td><strong>{$table}</strong></td>";
            echo "<td>{$count}</td>";
            echo "<td><span style='color:" . ($count > 0 ? '#059669' : '#92400e') . "'>{$status}</span></td>";
            echo "</tr>";
        } catch (PDOException $e) {
            echo "<tr>";
            echo "<td><strong>{$table}</strong></td>";
            echo "<td colspan='2' style='color:#dc2626'>❌ Table doesn't exist</td>";
            echo "</tr>";
        }
    }

    echo "</table>";

    if ($totalRows === 0) {
        echo "<div class='error'>";
        echo "<h3>❌ No Data Found</h3>";
        echo "<p>Your database tables exist but are empty. You need to import sample data.</p>";
        echo "<p><strong>Solution:</strong> Delete the security check in <code>import-database.php</code> or run the import SQL manually.</p>";
        echo "</div>";
    } else {
        echo "<div class='success'>";
        echo "<h3>✅ Database has {$totalRows} total rows of data</h3>";
        echo "</div>";
    }

    // Check for admin user specifically
    echo "<h2>👤 Admin User Check</h2>";

    if (isset($tableStats['users']) && $tableStats['users'] > 0) {
        $adminStmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE email = ?");
        $adminStmt->execute(['admin@taskflow.com']);
        $admin = $adminStmt->fetch();

        if ($admin) {
            echo "<div class='success'>";
            echo "<h3>✅ Admin user found!</h3>";
            echo "<table>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            echo "<tr><td>ID</td><td>{$admin['id']}</td></tr>";
            echo "<tr><td>Username</td><td>{$admin['username']}</td></tr>";
            echo "<tr><td>Email</td><td>{$admin['email']}</td></tr>";
            echo "<tr><td>Role</td><td>{$admin['role']}</td></tr>";
            echo "</table>";
            echo "</div>";

            // Test password
            $passStmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
            $passStmt->execute(['admin@taskflow.com']);
            $passData = $passStmt->fetch();

            $testPassword = 'password123';
            $isValid = password_verify($testPassword, $passData['password']);

            if ($isValid) {
                echo "<div class='success'>";
                echo "<h3>✅ Password Verification: PASSED</h3>";
                echo "<p>Password 'password123' works correctly!</p>";
                echo "<p><strong>You should be able to login now:</strong></p>";
                echo "<p><a href='auth/login.php' style='background:#2563eb;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block'>Go to Login Page</a></p>";
                echo "</div>";
            } else {
                echo "<div class='error'>";
                echo "<h3>❌ Password Verification: FAILED</h3>";
                echo "<p>The password hash in the database doesn't match 'password123'.</p>";
                echo "<p><strong>Password hash preview:</strong> <code>" . substr($passData['password'], 0, 30) . "...</code></p>";
                echo "<p><strong>Expected hash:</strong> <code>\$2y\$10\$92IXUNpkjO0rOQ5byMi...</code></p>";
                echo "</div>";
            }

        } else {
            echo "<div class='error'>";
            echo "<h3>❌ Admin user (admin@taskflow.com) not found</h3>";
            echo "<p>Users table has {$tableStats['users']} users, but admin@taskflow.com is missing.</p>";
            echo "</div>";

            // Show what users exist
            $allUsersStmt = $pdo->query("SELECT id, email FROM users LIMIT 10");
            $allUsers = $allUsersStmt->fetchAll();

            if ($allUsers) {
                echo "<div class='info'>";
                echo "<h4>Existing users in database:</h4>";
                echo "<ul>";
                foreach ($allUsers as $u) {
                    echo "<li>ID {$u['id']}: {$u['email']}</li>";
                }
                echo "</ul>";
                echo "</div>";
            }
        }
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ Users table is empty</h3>";
        echo "<p>You need to import sample data to be able to login.</p>";
        echo "</div>";
    }

} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<div class='warning'>";
echo "<h3>⚠️ Security Notice</h3>";
echo "<p>Delete this file (<code>check-database.php</code>) after debugging!</p>";
echo "</div>";

echo "</body></html>";
?>
