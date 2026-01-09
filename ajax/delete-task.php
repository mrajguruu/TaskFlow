<?php
/**
 * Delete Task
 * AJAX endpoint to delete a task
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
if (!isset($_POST['task_id'])) {
    echo jsonResponse(false, null, 'Task ID is required');
    exit;
}

$task_id = intval($_POST['task_id']);
$user_id = getCurrentUserId();

try {
    // Get task details
    $stmt = $pdo->prepare("
        SELECT t.*, p.owner_id as project_owner
        FROM tasks t
        LEFT JOIN projects p ON t.project_id = p.id
        WHERE t.id = ?
    ");

    $stmt->execute([$task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        echo jsonResponse(false, null, 'Task not found');
        exit;
    }

    // Check if user has permission (project owner or admin)
    if ($task['project_owner'] != $user_id && !isAdmin()) {
        echo jsonResponse(false, null, 'Permission denied');
        exit;
    }

    // Protect demo data (tasks with IDs 1-119)
    if ($task_id <= 119) {
        echo jsonResponse(false, null, 'Demo tasks cannot be deleted. Create your own tasks to test deletion functionality.');
        exit;
    }

    // Delete the task
    $delete_stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $delete_stmt->execute([$task_id]);

    // Log activity
    logActivity($pdo, $user_id, 'task_deleted', "Deleted task: {$task['title']}", $task['project_id'], null);

    echo jsonResponse(true, null, 'Task deleted successfully');

} catch (PDOException $e) {
    error_log("Database error in delete-task.php: " . $e->getMessage());
    echo jsonResponse(false, null, 'Database error occurred');
}
?>
