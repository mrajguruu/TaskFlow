<?php
/**
 * AJAX: Delete Project
 * Admin can delete any project, owners can delete their own
 */

session_start();
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    echo jsonResponse(false, null, 'Unauthorized');
    exit;
}

$userId = getCurrentUserId();
$isAdmin = isAdmin();

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$projectId = $input['project_id'] ?? 0;

if (!$projectId) {
    echo jsonResponse(false, null, 'Invalid project ID');
    exit;
}

try {
    // Check permission
    $stmt = $pdo->prepare("
        SELECT p.*, pm.role
        FROM projects p
        LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.user_id = ?
        WHERE p.id = ?
    ");
    $stmt->execute([$userId, $projectId]);
    $project = $stmt->fetch();

    if (!$project) {
        echo jsonResponse(false, null, 'Project not found');
        exit;
    }

    // Check if user can delete (must be admin or project owner)
    if (!$isAdmin && $project['role'] !== 'owner') {
        echo jsonResponse(false, null, 'You do not have permission to delete this project');
        exit;
    }

    // Protect demo data (projects with IDs 1-7)
    if ($projectId <= 7) {
        echo jsonResponse(false, null, 'Demo projects cannot be deleted. Create your own projects to test deletion functionality.');
        exit;
    }

    // Delete project (CASCADE will handle tasks, members, etc.)
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);

    // Log activity
    logActivity($pdo, $userId, 'project_deleted', "Deleted project \"{$project['name']}\"");

    echo jsonResponse(true, null, 'Project deleted successfully');

} catch (PDOException $e) {
    error_log("Delete project error: " . $e->getMessage());
    echo jsonResponse(false, null, 'Failed to delete project');
}
