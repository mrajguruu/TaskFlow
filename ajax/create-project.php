<?php
/**
 * AJAX: Create New Project
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

// Validate input
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$startDate = $_POST['start_date'] ?? null;
$endDate = $_POST['end_date'] ?? null;

if (empty($name)) {
    echo jsonResponse(false, null, 'Project name is required');
    exit;
}

if (strlen($name) > 150) {
    echo jsonResponse(false, null, 'Project name is too long (max 150 characters)');
    exit;
}

// Validate dates
if ($startDate && $endDate && strtotime($endDate) < strtotime($startDate)) {
    echo jsonResponse(false, null, 'End date cannot be before start date');
    exit;
}

try {
    $pdo->beginTransaction();

    // Create project
    $stmt = $pdo->prepare("
        INSERT INTO projects (name, description, owner_id, start_date, end_date, status)
        VALUES (?, ?, ?, ?, ?, 'active')
    ");

    $stmt->execute([
        $name,
        $description ?: null,
        $userId,
        $startDate ?: null,
        $endDate ?: null
    ]);

    $projectId = $pdo->lastInsertId();

    // Add creator as owner member
    $stmt = $pdo->prepare("
        INSERT INTO project_members (project_id, user_id, role)
        VALUES (?, ?, 'owner')
    ");
    $stmt->execute([$projectId, $userId]);

    // Log activity
    logActivity($pdo, $userId, 'project_created', "Created project \"$name\"", $projectId);

    $pdo->commit();

    echo jsonResponse(true, ['project_id' => $projectId], 'Project created successfully');

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Create project error: " . $e->getMessage());
    echo jsonResponse(false, null, 'Failed to create project');
}
