<?php
/**
 * AJAX: Create New Task
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

// Get input
$projectId = $_POST['project_id'] ?? 0;
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'todo';
$priority = $_POST['priority'] ?? 'medium';
$assignedTo = $_POST['assigned_to'] ?? null;
$dueDate = $_POST['due_date'] ?? null;

// Validate
if (!$projectId) {
    echo jsonResponse(false, null, 'Invalid project');
    exit;
}

if (empty($title)) {
    echo jsonResponse(false, null, 'Task title is required');
    exit;
}

// Check project access
if (!checkProjectAccess($userId, $projectId, $pdo)) {
    echo jsonResponse(false, null, 'Access denied');
    exit;
}

// Validate status
$allowedStatuses = ['todo', 'in_progress', 'completed'];
if (!in_array($status, $allowedStatuses)) {
    $status = 'todo';
}

// Validate priority
$allowedPriorities = ['low', 'medium', 'high'];
if (!in_array($priority, $allowedPriorities)) {
    $priority = 'medium';
}

try {
    // Create task
    $stmt = $pdo->prepare("
        INSERT INTO tasks (project_id, title, description, status, priority, assigned_to, created_by, due_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $projectId,
        $title,
        $description ?: null,
        $status,
        $priority,
        $assignedTo ?: null,
        $userId,
        $dueDate ?: null
    ]);

    $taskId = $pdo->lastInsertId();

    // Log activity
    logActivity($pdo, $userId, 'task_created', "Created task \"$title\"", $projectId, $taskId);

    echo jsonResponse(true, ['task_id' => $taskId], 'Task created successfully');

} catch (PDOException $e) {
    error_log("Create task error: " . $e->getMessage());
    echo jsonResponse(false, null, 'Failed to create task');
}
