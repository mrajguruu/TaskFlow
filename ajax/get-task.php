<?php
/**
 * Get Task Details (AJAX)
 * Returns task information for modal display
 */

require_once '../config/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$currentUser = getCurrentUser($pdo);
$userId = $currentUser['id'];
$isAdmin = isAdmin();

// Get task ID
$taskId = $_GET['id'] ?? 0;

if (!$taskId) {
    echo json_encode(['success' => false, 'message' => 'Task ID required']);
    exit;
}

try {
    // Get task details
    $stmt = $pdo->prepare("
        SELECT t.*,
               u.full_name as assignee_name,
               u.avatar as assignee_avatar,
               creator.full_name as creator_name,
               p.name as project_name
        FROM tasks t
        LEFT JOIN users u ON t.assigned_to = u.id
        LEFT JOIN users creator ON t.created_by = creator.id
        LEFT JOIN projects p ON t.project_id = p.id
        WHERE t.id = ?
    ");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    if (!$task) {
        echo json_encode(['success' => false, 'message' => 'Task not found']);
        exit;
    }

    // Check access permission (user must be admin or member of the project)
    if (!$isAdmin && !checkProjectAccess($userId, $task['project_id'], $pdo)) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'task' => $task
    ]);

} catch (PDOException $e) {
    error_log("Get task error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
