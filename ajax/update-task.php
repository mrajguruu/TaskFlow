<?php
/**
 * Update Task
 * AJAX endpoint to update task details (from modal)
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
    // Get task and check access
    $stmt = $pdo->prepare("SELECT t.*, p.id as project_id FROM tasks t LEFT JOIN projects p ON t.project_id = p.id WHERE t.id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        echo jsonResponse(false, null, 'Task not found');
        exit;
    }

    // Check access
    if (!checkProjectAccess($user_id, $task['project_id'], $pdo)) {
        echo jsonResponse(false, null, 'Access denied');
        exit;
    }

    // Prepare update data
    $updates = [];
    $params = [];

    // Status
    if (isset($_POST['status'])) {
        $status = $_POST['status'];
        $allowed_statuses = ['todo', 'in_progress', 'completed'];
        if (in_array($status, $allowed_statuses)) {
            $updates[] = "status = ?";
            $params[] = $status;
        }
    }

    // Priority
    if (isset($_POST['priority'])) {
        $priority = $_POST['priority'];
        $allowed_priorities = ['low', 'medium', 'high'];
        if (in_array($priority, $allowed_priorities)) {
            $updates[] = "priority = ?";
            $params[] = $priority;
        }
    }

    // Due date
    if (isset($_POST['due_date'])) {
        $due_date = $_POST['due_date'];
        if (empty($due_date)) {
            $updates[] = "due_date = NULL";
        } else {
            $updates[] = "due_date = ?";
            $params[] = $due_date;
        }
    }

    // Assigned to
    if (isset($_POST['assigned_to'])) {
        $assigned_to = $_POST['assigned_to'];
        if (empty($assigned_to)) {
            $updates[] = "assigned_to = NULL";
        } else {
            // Verify user exists
            $user_check = $pdo->prepare("SELECT id FROM users WHERE id = ?");
            $user_check->execute([intval($assigned_to)]);
            if ($user_check->fetch()) {
                $updates[] = "assigned_to = ?";
                $params[] = intval($assigned_to);
            }
        }
    }

    // Description
    if (isset($_POST['description'])) {
        $description = trim($_POST['description']);
        $updates[] = "description = ?";
        $params[] = $description;
    }

    // If no updates, return error
    if (empty($updates)) {
        echo jsonResponse(false, null, 'No fields to update');
        exit;
    }

    // Add updated_at
    $updates[] = "updated_at = NOW()";

    // Build and execute update query
    $sql = "UPDATE tasks SET " . implode(", ", $updates) . " WHERE id = ?";
    $params[] = $task_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo jsonResponse(true, null, 'Task updated successfully');

} catch (PDOException $e) {
    error_log("Database error in update-task.php: " . $e->getMessage());
    echo jsonResponse(false, null, 'Database error occurred');
}
?>
