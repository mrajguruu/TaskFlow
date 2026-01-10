<?php
/**
 * Fix Admin Project Access
 * Makes admin user (ID: 1) an owner of ALL projects
 *
 * ⚠️ DELETE THIS FILE AFTER RUNNING!
 */

require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><title>Fix Admin Access</title><style>body{font-family:system-ui;max-width:800px;margin:50px auto;padding:20px}h1{color:#2563eb}.success{color:#059669;padding:15px;background:#d1fae5;border-radius:5px;margin:15px 0}.error{color:#dc2626;padding:15px;background:#fee2e2;border-radius:5px;margin:15px 0}.info{color:#0284c7;padding:15px;background:#e0f2fe;border-radius:5px;margin:15px 0}</style></head><body>";

echo "<h1>🔧 Fix Admin Project Access</h1>";

try {
    // Get all projects
    $projectsStmt = $pdo->query("SELECT id, name FROM projects ORDER BY id");
    $allProjects = $projectsStmt->fetchAll();

    echo "<div class='info'>";
    echo "<h3>Found " . count($allProjects) . " total projects:</h3>";
    echo "<ul>";
    foreach ($allProjects as $project) {
        echo "<li>ID {$project['id']}: {$project['name']}</li>";
    }
    echo "</ul>";
    echo "</div>";

    // Check current admin access
    $currentAccessStmt = $pdo->prepare("
        SELECT project_id, role
        FROM project_members
        WHERE user_id = 1
        ORDER BY project_id
    ");
    $currentAccessStmt->execute();
    $currentAccess = $currentAccessStmt->fetchAll();

    $currentProjectIds = array_column($currentAccess, 'project_id');

    echo "<div class='info'>";
    echo "<h3>Admin currently has access to " . count($currentAccess) . " projects</h3>";
    if (count($currentAccess) > 0) {
        echo "<ul>";
        foreach ($currentAccess as $access) {
            echo "<li>Project ID {$access['project_id']} (Role: {$access['role']})</li>";
        }
        echo "</ul>";
    }
    echo "</div>";

    // Add admin to missing projects
    $added = 0;
    $updated = 0;

    foreach ($allProjects as $project) {
        $projectId = $project['id'];

        if (in_array($projectId, $currentProjectIds)) {
            // Already has access - ensure they're owner
            $updateStmt = $pdo->prepare("
                UPDATE project_members
                SET role = 'owner'
                WHERE user_id = 1 AND project_id = ?
            ");
            $updateStmt->execute([$projectId]);
            echo "<div class='info'>✅ Updated role to 'owner' for: <strong>{$project['name']}</strong></div>";
            $updated++;
        } else {
            // Add admin as owner
            $insertStmt = $pdo->prepare("
                INSERT INTO project_members (project_id, user_id, role, joined_at)
                VALUES (?, 1, 'owner', NOW())
            ");
            $insertStmt->execute([$projectId]);
            echo "<div class='success'>✅ Added admin as owner to: <strong>{$project['name']}</strong></div>";
            $added++;
        }

        flush();
    }

    // Verify final access
    $finalAccessStmt = $pdo->prepare("
        SELECT p.id, p.name, pm.role
        FROM projects p
        JOIN project_members pm ON p.id = pm.project_id
        WHERE pm.user_id = 1
        ORDER BY p.id
    ");
    $finalAccessStmt->execute();
    $finalAccess = $finalAccessStmt->fetchAll();

    echo "<div class='success'>";
    echo "<h2>✅ Admin Access Fixed!</h2>";
    echo "<p><strong>Added to:</strong> {$added} projects</p>";
    echo "<p><strong>Updated role:</strong> {$updated} projects</p>";
    echo "<p><strong>Total access:</strong> " . count($finalAccess) . " / " . count($allProjects) . " projects</p>";
    echo "<h3>Final Project List:</h3>";
    echo "<ul>";
    foreach ($finalAccess as $access) {
        echo "<li><strong>{$access['name']}</strong> (Role: {$access['role']})</li>";
    }
    echo "</ul>";
    echo "</div>";

    echo "<div class='success'>";
    echo "<h3>🎉 All Done!</h3>";
    echo "<p><a href='dashboard/projects.php' style='background:#2563eb;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;display:inline-block;font-weight:bold'>View Projects Page</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<hr>";
echo "<div style='background:#fef3c7;padding:15px;border-radius:5px;margin:15px 0;color:#92400e'>";
echo "<h3>⚠️ IMPORTANT</h3>";
echo "<p><strong>DELETE this file after testing:</strong> fix-admin-projects.php</p>";
echo "</div>";

echo "</body></html>";
?>
