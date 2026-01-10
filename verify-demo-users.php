<?php
/**
 * Verify Demo Users
 * Quick check to see if demo users (IDs 1-8) exist
 */

require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><title>Verify Demo Users</title><style>body{font-family:system-ui;max-width:1000px;margin:50px auto;padding:20px}h1{color:#2563eb}table{width:100%;border-collapse:collapse;margin:20px 0}th,td{border:1px solid #ddd;padding:12px;text-align:left}th{background:#2563eb;color:white}.protected{background:#d1fae5;font-weight:bold}.missing{background:#fee2e2;color:#dc2626}.exists{color:#059669}.not-found{color:#dc2626}</style></head><body>";

echo "<h1>🔍 Demo Users Verification</h1>";

try {
    // Check demo users (IDs 1-8)
    $stmt = $pdo->query("SELECT id, username, email, full_name, role, created_at FROM users WHERE id <= 8 ORDER BY id");
    $demoUsers = $stmt->fetchAll();

    // Check total users
    $totalStmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $totalUsers = $totalStmt->fetch()['count'];

    // Check temp users (IDs > 8)
    $tempStmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE id > 8");
    $tempUsers = $tempStmt->fetch()['count'];

    echo "<div style='background:#e0f2fe;padding:20px;border-radius:8px;margin:20px 0'>";
    echo "<h2 style='margin:0 0 15px 0;color:#0284c7'>📊 User Statistics</h2>";
    echo "<div style='display:grid;grid-template-columns:repeat(3,1fr);gap:15px'>";

    echo "<div style='background:white;padding:15px;border-radius:8px;border:2px solid #bae6fd'>";
    echo "<div style='font-size:32px;font-weight:900;color:#0284c7'>{$totalUsers}</div>";
    echo "<div style='color:#64748b;font-weight:600'>Total Users</div>";
    echo "</div>";

    echo "<div style='background:white;padding:15px;border-radius:8px;border:2px solid #86efac'>";
    echo "<div style='font-size:32px;font-weight:900;color:#059669'>" . count($demoUsers) . "</div>";
    echo "<div style='color:#64748b;font-weight:600'>Demo Users (Protected)</div>";
    echo "</div>";

    echo "<div style='background:white;padding:15px;border-radius:8px;border:2px solid #fbbf24'>";
    echo "<div style='font-size:32px;font-weight:900;color:#d97706'>{$tempUsers}</div>";
    echo "<div style='color:#64748b;font-weight:600'>Temp Users (Will be deleted)</div>";
    echo "</div>";

    echo "</div></div>";

    // Demo users table
    echo "<h2>🛡️ Demo Users (IDs 1-8) - Protected from Deletion</h2>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Role</th><th>Created</th><th>Status</th></tr>";

    $expectedDemoUsers = [
        1 => ['admin', 'admin@taskflow.com', 'Admin User', 'admin'],
        2 => ['johndoe', 'john@taskflow.com', 'John Doe', 'member'],
        3 => ['sarahjohnson', 'sarah@taskflow.com', 'Sarah Johnson', 'member'],
        4 => ['mikechen', 'mike@taskflow.com', 'Mike Chen', 'member'],
        5 => ['emmawilson', 'emma@taskflow.com', 'Emma Wilson', 'member'],
        6 => ['alexbrown', 'alex@taskflow.com', 'Alex Brown', 'member'],
        7 => ['liuchen', 'liu@taskflow.com', 'Liu Chen', 'member'],
        8 => ['rachelgreen', 'rachel@taskflow.com', 'Rachel Green', 'member']
    ];

    $foundIds = [];
    foreach ($demoUsers as $user) {
        $foundIds[] = $user['id'];
        echo "<tr class='protected'>";
        echo "<td><strong>{$user['id']}</strong></td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td><span style='background:#" . ($user['role'] === 'admin' ? 'fbbf24' : '93c5fd') . ";padding:4px 12px;border-radius:6px;font-size:12px;font-weight:700'>{$user['role']}</span></td>";
        echo "<td>" . date('Y-m-d H:i', strtotime($user['created_at'])) . "</td>";
        echo "<td class='exists'>✅ EXISTS</td>";
        echo "</tr>";
    }

    // Check for missing demo users
    for ($i = 1; $i <= 8; $i++) {
        if (!in_array($i, $foundIds)) {
            $expected = $expectedDemoUsers[$i];
            echo "<tr class='missing'>";
            echo "<td><strong>{$i}</strong></td>";
            echo "<td>{$expected[0]}</td>";
            echo "<td>{$expected[1]}</td>";
            echo "<td>{$expected[2]}</td>";
            echo "<td>{$expected[3]}</td>";
            echo "<td>-</td>";
            echo "<td class='not-found'>❌ MISSING</td>";
            echo "</tr>";
        }
    }

    echo "</table>";

    // Show temp users if any
    if ($tempUsers > 0) {
        echo "<h2>⚠️ Temporary Users (IDs > 8) - Will be Auto-Deleted</h2>";
        $tempStmt = $pdo->query("SELECT id, username, email, full_name, created_at FROM users WHERE id > 8 ORDER BY id");
        $tempUsersList = $tempStmt->fetchAll();

        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Created</th><th>Age</th><th>Will Delete In</th></tr>";

        foreach ($tempUsersList as $user) {
            $createdTime = strtotime($user['created_at']);
            $currentTime = time();
            $ageMinutes = floor(($currentTime - $createdTime) / 60);
            $ageHours = floor($ageMinutes / 60);
            $remainingMinutes = 60 - ($ageMinutes % 60);

            $ageDisplay = $ageHours > 0 ? "{$ageHours}h " . ($ageMinutes % 60) . "m" : "{$ageMinutes}m";
            $deleteIn = $ageMinutes >= 60 ? "Next cron run" : "{$remainingMinutes}m";
            $rowColor = $ageMinutes >= 60 ? '#fef3c7' : '#ffffff';

            echo "<tr style='background:{$rowColor}'>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['full_name']}</td>";
            echo "<td>" . date('Y-m-d H:i', $createdTime) . "</td>";
            echo "<td><strong>{$ageDisplay}</strong></td>";
            echo "<td><strong>{$deleteIn}</strong></td>";
            echo "</tr>";
        }

        echo "</table>";

        echo "<div style='background:#fef3c7;padding:15px;border-radius:8px;margin:15px 0;border:2px solid #fbbf24'>";
        echo "<h3 style='margin:0 0 10px 0;color:#92400e'>⏰ Cleanup Schedule</h3>";
        echo "<p style='margin:5px 0;color:#78350f'><strong>Cron runs:</strong> Every 2 hours (00:00, 02:00, 04:00, 06:00, etc.)</p>";
        echo "<p style='margin:5px 0;color:#78350f'><strong>Deletes:</strong> Users older than 1 hour (excluding IDs 1-8)</p>";
        echo "<p style='margin:5px 0;color:#78350f'><strong>Next run:</strong> Check cron-job.org dashboard</p>";
        echo "</div>";
    }

    // Summary
    if (count($demoUsers) === 8) {
        echo "<div style='background:#d1fae5;padding:20px;border-radius:8px;margin:20px 0;border:2px solid #86efac'>";
        echo "<h2 style='margin:0 0 10px 0;color:#059669'>✅ All Demo Users Present!</h2>";
        echo "<p style='margin:5px 0;color:#065f46'>All 8 demo users (IDs 1-8) are in the database and protected from deletion.</p>";
        echo "</div>";
    } else {
        echo "<div style='background:#fee2e2;padding:20px;border-radius:8px;margin:20px 0;border:2px solid #fca5a5'>";
        echo "<h2 style='margin:0 0 10px 0;color:#dc2626'>⚠️ Missing Demo Users</h2>";
        echo "<p style='margin:5px 0;color:#991b1b'>Only " . count($demoUsers) . " of 8 demo users found. You need to re-import sample data.</p>";
        echo "</div>";
    }

} catch (PDOException $e) {
    echo "<div style='background:#fee2e2;padding:15px;border-radius:8px;color:#dc2626'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<p style='margin-top:30px'>";
echo "<a href='dashboard/index.php' style='background:#2563eb;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:bold;margin-right:10px'>Go to Dashboard</a>";
echo "<a href='create-test-users.php' style='background:#f59e0b;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:bold'>Create Test Users</a>";
echo "</p>";

echo "</body></html>";
?>
