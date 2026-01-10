<?php
/**
 * Check Admin Status
 * Simple diagnostic to check if admin user is properly recognized
 *
 * ⚠️ DELETE THIS FILE AFTER DEBUGGING!
 */

require_once 'config/config.php';
require_once 'includes/auth.php';
requireLogin(); // Must be logged in

echo "<!DOCTYPE html><html><head><title>Admin Status Check</title><style>body{font-family:system-ui;max-width:900px;margin:50px auto;padding:20px}h1{color:#2563eb}.success{color:#059669;padding:15px;background:#d1fae5;border-radius:5px;margin:15px 0}.error{color:#dc2626;padding:15px;background:#fee2e2;border-radius:5px;margin:15px 0}.info{color:#0284c7;padding:15px;background:#e0f2fe;border-radius:5px;margin:15px 0}table{width:100%;border-collapse:collapse;margin:20px 0}th,td{border:1px solid #ddd;padding:12px;text-align:left}th{background:#2563eb;color:white}</style></head><body>";

echo "<h1>🔍 Admin Status Check</h1>";

try {
    $currentUser = getCurrentUser($pdo);
    $isAdmin = isAdmin();

    echo "<div class='info'>";
    echo "<h3>Session Variables</h3>";
    echo "<table>";
    echo "<tr><th>Key</th><th>Value</th></tr>";
    echo "<tr><td>user_id</td><td>" . ($_SESSION['user_id'] ?? '<em style=\"color:#dc2626\">NOT SET</em>') . "</td></tr>";
    echo "<tr><td>username</td><td>" . ($_SESSION['username'] ?? '<em style=\"color:#dc2626\">NOT SET</em>') . "</td></tr>";
    echo "<tr><td>user_role</td><td>" . ($_SESSION['user_role'] ?? '<em style=\"color:#dc2626\">NOT SET</em>') . "</td></tr>";
    echo "<tr><td>full_name</td><td>" . ($_SESSION['full_name'] ?? '<em style=\"color:#dc2626\">NOT SET</em>') . "</td></tr>";
    echo "<tr><td>logged_in</td><td>" . (($_SESSION['logged_in'] ?? false) ? 'TRUE' : 'FALSE') . "</td></tr>";
    echo "</table>";
    echo "</div>";

    if ($currentUser) {
        echo "<div class='success'>";
        echo "<h3>Current User from Database</h3>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($currentUser as $key => $value) {
            echo "<tr><td>{$key}</td><td>" . htmlspecialchars($value ?? 'NULL') . "</td></tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ getCurrentUser() returned NULL</h3>";
        echo "</div>";
    }

    echo "<div class='" . ($isAdmin ? "success" : "error") . "'>";
    echo "<h3>isAdmin() Result: " . ($isAdmin ? "TRUE ✅" : "FALSE ❌") . "</h3>";
    if (!$isAdmin && isset($_SESSION['user_role'])) {
        echo "<p>Session role is: <strong>{$_SESSION['user_role']}</strong></p>";
        echo "<p>Expected: <strong>" . USER_ROLE_ADMIN . "</strong></p>";
    }
    echo "</div>";

    // Check USER_ROLE_ADMIN constant
    echo "<div class='info'>";
    echo "<h3>Constants Check</h3>";
    echo "<p>USER_ROLE_ADMIN = <strong>" . (defined('USER_ROLE_ADMIN') ? USER_ROLE_ADMIN : '<em style=\"color:#dc2626\">NOT DEFINED</em>') . "</strong></p>";
    echo "<p>USER_ROLE_USER = <strong>" . (defined('USER_ROLE_USER') ? USER_ROLE_USER : '<em style=\"color:#dc2626\">NOT DEFINED</em>') . "</strong></p>";
    echo "</div>";

    // Check projects count for admin
    echo "<h2>Projects Visibility Test</h2>";

    $totalProjectsStmt = $pdo->query("SELECT COUNT(*) as count FROM projects");
    $totalProjects = $totalProjectsStmt->fetch()['count'];

    echo "<div class='info'>";
    echo "<h3>Total Projects in Database: {$totalProjects}</h3>";
    echo "</div>";

    $userProjectsStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT p.id) as count
        FROM projects p
        LEFT JOIN project_members pm ON p.id = pm.project_id
        WHERE pm.user_id = ?
    ");
    $userProjectsStmt->execute([$_SESSION['user_id']]);
    $userProjects = $userProjectsStmt->fetch()['count'];

    echo "<div class='info'>";
    echo "<h3>Projects You're a Member Of: {$userProjects}</h3>";
    echo "</div>";

    if ($currentUser && $currentUser['role'] === 'admin') {
        echo "<div class='success'>";
        echo "<h3>✅ Database role is 'admin'</h3>";
        echo "<p>You SHOULD see all {$totalProjects} projects on the projects page.</p>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ Database role is NOT 'admin'</h3>";
        echo "<p>Role: " . ($currentUser['role'] ?? 'NULL') . "</p>";
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
echo "<p><a href='dashboard/projects.php' style='background:#2563eb;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin:10px 0'>Go to Projects Page</a></p>";
echo "<p><strong>DELETE this file after debugging:</strong> check-admin-status.php</p>";
echo "</div>";

echo "</body></html>";
?>
