<?php
/**
 * Debug Projects Query
 * Tests the exact SQL query used in projects.php to see what's wrong
 *
 * ⚠️ DELETE THIS FILE AFTER DEBUGGING!
 */

require_once 'config/config.php';
require_once 'includes/auth.php';

echo "<!DOCTYPE html><html><head><title>Debug Projects Query</title><style>body{font-family:system-ui;max-width:1200px;margin:50px auto;padding:20px}h1{color:#2563eb}.success{color:#059669;padding:15px;background:#d1fae5;border-radius:5px;margin:15px 0}.error{color:#dc2626;padding:15px;background:#fee2e2;border-radius:5px;margin:15px 0}.info{color:#0284c7;padding:15px;background:#e0f2fe;border-radius:5px;margin:15px 0}table{width:100%;border-collapse:collapse;margin:20px 0}th,td{border:1px solid #ddd;padding:12px;text-align:left}th{background:#2563eb;color:white}pre{background:#f3f4f6;padding:15px;border-radius:5px;overflow-x:auto}</style></head><body>";

echo "<h1>🔍 Debug Projects Query</h1>";

// Fake login as admin
$_SESSION['user_id'] = 1;

try {
    $currentUser = getCurrentUser($pdo);
    $userId = $currentUser['id'];
    $isAdmin = isAdmin();

    echo "<div class='info'>";
    echo "<h3>Current User Info</h3>";
    echo "<p><strong>User ID:</strong> {$userId}</p>";
    echo "<p><strong>Email:</strong> {$currentUser['email']}</p>";
    echo "<p><strong>Role:</strong> {$currentUser['role']}</p>";
    echo "<p><strong>isAdmin():</strong> " . ($isAdmin ? 'TRUE' : 'FALSE') . "</p>";
    echo "</div>";

    // Test the exact query from projects.php
    $search = '';
    $statusFilter = '';

    $sql = "
        SELECT p.*,
               COUNT(DISTINCT t.id) as task_count,
               COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) as completed_count,
               COUNT(DISTINCT pm.user_id) as member_count,
               u.full_name as owner_name,
               (SELECT role FROM project_members WHERE project_id = p.id AND user_id = ?) as user_role
        FROM projects p
        LEFT JOIN users u ON p.owner_id = u.id
        LEFT JOIN tasks t ON p.id = t.project_id
        LEFT JOIN project_members pm ON p.id = pm.project_id
    ";

    // Admin sees all projects, regular users see only their projects
    if (!$isAdmin) {
        $sql .= " WHERE p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)";
    }

    $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

    echo "<div class='info'>";
    echo "<h3>SQL Query Being Executed</h3>";
    echo "<pre>" . htmlspecialchars($sql) . "</pre>";
    echo "</div>";

    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $params = [];
    if (!$isAdmin) {
        $params[] = $userId; // For user_role subquery
        $params[] = $userId; // For WHERE clause
    } else {
        $params[] = $userId; // For user_role subquery only
    }

    echo "<div class='info'>";
    echo "<h3>Parameters Being Bound</h3>";
    echo "<pre>" . print_r($params, true) . "</pre>";
    echo "</div>";

    $stmt->execute($params);
    $projects = $stmt->fetchAll();

    echo "<div class='" . (count($projects) > 0 ? "success" : "error") . "'>";
    echo "<h3>Query Result: " . count($projects) . " projects found</h3>";
    echo "</div>";

    if (count($projects) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Owner</th><th>Tasks</th><th>Members</th><th>Your Role</th></tr>";
        foreach ($projects as $project) {
            echo "<tr>";
            echo "<td>{$project['id']}</td>";
            echo "<td>" . htmlspecialchars($project['name']) . "</td>";
            echo "<td>" . htmlspecialchars($project['owner_name']) . "</td>";
            echo "<td>{$project['task_count']}</td>";
            echo "<td>{$project['member_count']}</td>";
            echo "<td>" . ($project['user_role'] ?? '<em style=\"color:#999\">Not a member</em>') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Also check raw data
    echo "<h2>Raw Database Check</h2>";

    $projectsStmt = $pdo->query("SELECT COUNT(*) as count FROM projects");
    $totalProjects = $projectsStmt->fetch()['count'];

    $membersStmt = $pdo->prepare("
        SELECT p.id, p.name, pm.role
        FROM projects p
        LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.user_id = ?
        ORDER BY p.id
    ");
    $membersStmt->execute([$userId]);
    $allProjects = $membersStmt->fetchAll();

    echo "<div class='info'>";
    echo "<h3>All Projects in Database ({$totalProjects} total)</h3>";
    echo "<table>";
    echo "<tr><th>Project ID</th><th>Project Name</th><th>Admin's Role</th></tr>";
    foreach ($allProjects as $proj) {
        echo "<tr>";
        echo "<td>{$proj['id']}</td>";
        echo "<td>" . htmlspecialchars($proj['name']) . "</td>";
        echo "<td>" . ($proj['role'] ? "<strong>{$proj['role']}</strong>" : "<em style='color:#dc2626'>NO ACCESS</em>") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='background:#fef3c7;padding:15px;border-radius:5px;margin:15px 0;color:#92400e'>";
echo "<h3>⚠️ IMPORTANT</h3>";
echo "<p><strong>DELETE this file after debugging:</strong> debug-projects-query.php</p>";
echo "</div>";

echo "</body></html>";
?>
