<?php
/**
 * AJAX: Update Project
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

// Get form data
$projectId = (int)($_POST['project_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$startDate = $_POST['start_date'] ?? null;
$endDate = $_POST['end_date'] ?? null;
$status = $_POST['status'] ?? 'active';

// Validate
if (empty($projectId)) {
    echo jsonResponse(false, null, 'Project ID is required');
    exit;
}

if (empty($name)) {
    echo jsonResponse(false, null, 'Project name is required');
    exit;
}

if (strlen($name) > 150) {
    echo jsonResponse(false, null, 'Project name must be less than 150 characters');
    exit;
}

// Validate status
$allowedStatuses = ['active', 'completed', 'archived'];
if (!in_array($status, $allowedStatuses)) {
    echo jsonResponse(false, null, 'Invalid status');
    exit;
}

try {
    // Check if project exists and user has permission to edit
    $stmt = $pdo->prepare("SELECT owner_id FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch();

    if (!$project) {
        echo jsonResponse(false, null, 'Project not found');
        exit;
    }

    // Check if user is owner or admin
    if ($project['owner_id'] != $userId && !isAdmin()) {
        echo jsonResponse(false, null, 'You do not have permission to edit this project');
        exit;
    }

    // Convert empty dates to NULL
    $startDate = !empty($startDate) ? $startDate : null;
    $endDate = !empty($endDate) ? $endDate : null;

    // Validate dates
    if ($startDate && $endDate && strtotime($endDate) < strtotime($startDate)) {
        echo jsonResponse(false, null, 'End date cannot be before start date');
        exit;
    }

    // Update project
    $stmt = $pdo->prepare("
        UPDATE projects
        SET name = ?,
            description = ?,
            start_date = ?,
            end_date = ?,
            status = ?,
            updated_at = NOW()
        WHERE id = ?
    ");

    $success = $stmt->execute([
        $name,
        $description,
        $startDate,
        $endDate,
        $status,
        $projectId
    ]);

    if (!$success) {
        echo jsonResponse(false, null, 'Failed to update project');
        exit;
    }

    // Log activity
    logActivity($pdo, $userId, 'project_updated', "Updated project: {$name}", $projectId);

    echo jsonResponse(true, ['project_id' => $projectId], 'Project updated successfully');

} catch (PDOException $e) {
    error_log("Update project error: " . $e->getMessage());
    echo jsonResponse(false, null, 'An error occurred. Please try again later.');
}
