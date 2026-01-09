<?php
/**
 * Get Task Details
 * AJAX endpoint to fetch complete task information
 */

session_start();
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    echo jsonResponse(false, null, 'Not authenticated');
    exit;
}

// Check if task_id is provided
if (!isset($_GET['task_id'])) {
    echo jsonResponse(false, null, 'Task ID is required');
    exit;
}

$task_id = intval($_GET['task_id']);
$user_id = getCurrentUserId();

try {
    // Get task details
    $stmt = $pdo->prepare("
        SELECT
            t.*,
            p.name as project_name,
            p.id as project_id,
            assigned_user.full_name as assigned_to_name,
            assigned_user.email as assigned_to_email,
            creator.full_name as created_by_name
        FROM tasks t
        LEFT JOIN projects p ON t.project_id = p.id
        LEFT JOIN users assigned_user ON t.assigned_to = assigned_user.id
        LEFT JOIN users creator ON t.created_by = creator.id
        WHERE t.id = ?
    ");

    $stmt->execute([$task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        echo jsonResponse(false, null, 'Task not found');
        exit;
    }

    // Check if user has access to this project (handles both members and admins)
    if (!checkProjectAccess($user_id, $task['project_id'], $pdo)) {
        echo jsonResponse(false, null, 'Access denied');
        exit;
    }

    // Get list of available users (project members)
    $users_stmt = $pdo->prepare("
        SELECT DISTINCT u.id, u.full_name as name, u.email
        FROM users u
        INNER JOIN project_members pm ON u.id = pm.user_id
        WHERE pm.project_id = ?
        ORDER BY u.full_name ASC
    ");

    $users_stmt->execute([$task['project_id']]);
    $available_users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add available users to task data
    $task['available_users'] = $available_users;

    // Get task attachments
    $attachments_stmt = $pdo->prepare("
        SELECT a.*, u.full_name as uploaded_by_name
        FROM task_attachments a
        LEFT JOIN users u ON a.uploaded_by = u.id
        WHERE a.task_id = ?
        ORDER BY a.uploaded_at DESC
    ");
    $attachments_stmt->execute([$task_id]);
    $attachments = $attachments_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add attachments to task data
    $task['attachments'] = $attachments;

    // Format the response properly
    echo jsonResponse(true, ['task' => $task], 'Task loaded successfully');

} catch (PDOException $e) {
    error_log("Database error in get-task-details.php: " . $e->getMessage());
    echo jsonResponse(false, null, 'Database error occurred');
}
?>
