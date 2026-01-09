<?php
/**
 * AJAX: Get Project Details
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
$projectId = (int)($_GET['id'] ?? 0);

if (empty($projectId)) {
    echo jsonResponse(false, null, 'Project ID is required');
    exit;
}

try {
    // Get project details
    $stmt = $pdo->prepare("
        SELECT p.*
        FROM projects p
        WHERE p.id = ?
    ");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch();

    if (!$project) {
        echo jsonResponse(false, null, 'Project not found');
        exit;
    }

    // Check if user has access to this project
    $hasAccess = false;

    // Check if user is project owner
    if ($project['owner_id'] == $userId) {
        $hasAccess = true;
    }

    // Check if user is a project member
    if (!$hasAccess) {
        $memberCheck = $pdo->prepare("
            SELECT id FROM project_members
            WHERE project_id = ? AND user_id = ?
        ");
        $memberCheck->execute([$projectId, $userId]);
        if ($memberCheck->fetch()) {
            $hasAccess = true;
        }
    }

    // Check if user is admin
    if (!$hasAccess && isAdmin()) {
        $hasAccess = true;
    }

    if (!$hasAccess) {
        echo jsonResponse(false, null, 'You do not have access to this project');
        exit;
    }

    // Return project data
    echo jsonResponse(true, [
        'id' => $project['id'],
        'name' => $project['name'],
        'description' => $project['description'],
        'start_date' => $project['start_date'],
        'end_date' => $project['end_date'],
        'status' => $project['status'],
        'owner_id' => $project['owner_id']
    ]);

} catch (PDOException $e) {
    error_log("Get project error: " . $e->getMessage());
    echo jsonResponse(false, null, 'An error occurred. Please try again later.');
}
