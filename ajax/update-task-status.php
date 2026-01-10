<?php
/**
 * AJAX: Update Task Status (Drag & Drop)
 */

// Disable error display for clean JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Clean output buffer to prevent any stray output
if (ob_get_level()) {
    ob_clean();
}

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    echo jsonResponse(false, null, 'Unauthorized');
    exit;
}

$userId = getCurrentUserId();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$taskId = $input['task_id'] ?? 0;
$newStatus = $input['status'] ?? '';

// Validate
$allowedStatuses = ['todo', 'in_progress', 'completed'];
if (!in_array($newStatus, $allowedStatuses)) {
    echo jsonResponse(false, null, 'Invalid status');
    exit;
}

try {
    // Get task and project details
    $stmt = $pdo->prepare("
        SELECT t.*, p.name as project_name, p.owner_id as project_owner
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        WHERE t.id = ?
    ");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    if (!$task) {
        echo jsonResponse(false, null, 'Task not found');
        exit;
    }

    // Protect demo data (tasks with IDs 1-119)
    if ($taskId <= 119) {
        echo jsonResponse(false, null, 'Demo tasks cannot be moved or updated. Create your own tasks to test this functionality.');
        exit;
    }

    // Check if user has access to this project
    // User has access if they are:
    // 1. The project owner/creator
    // 2. A project member
    // 3. An admin
    $hasAccess = false;

    // Check if user is project owner
    if ($task['project_owner'] == $userId) {
        $hasAccess = true;
    }

    // Check if user is a project member
    if (!$hasAccess) {
        $memberCheck = $pdo->prepare("
            SELECT id FROM project_members
            WHERE project_id = ? AND user_id = ?
        ");
        $memberCheck->execute([$task['project_id'], $userId]);
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

    // Update status
    $stmt = $pdo->prepare("
        UPDATE tasks
        SET status = ?,
            completed_at = CASE WHEN ? = 'completed' THEN NOW() ELSE NULL END,
            updated_at = NOW()
        WHERE id = ?
    ");

    $success = $stmt->execute([$newStatus, $newStatus, $taskId]);

    if (!$success) {
        echo jsonResponse(false, null, 'Failed to update task status');
        exit;
    }

    // Log activity
    try {
        $statusLabels = [
            'todo' => 'To Do',
            'in_progress' => 'In Progress',
            'completed' => 'Completed'
        ];
        $description = "Moved task \"{$task['title']}\" to {$statusLabels[$newStatus]}";
        logActivity($pdo, $userId, 'task_status_changed', $description, $task['project_id'], $taskId);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
        // Don't fail the request if activity logging fails
    }

    echo jsonResponse(true, null, 'Task status updated');

} catch (PDOException $e) {
    error_log("Update task status error: " . $e->getMessage());
    echo jsonResponse(false, null, 'Failed to update task status');
} catch (Exception $e) {
    error_log("Update task status error: " . $e->getMessage());
    echo jsonResponse(false, null, 'An error occurred');
}
