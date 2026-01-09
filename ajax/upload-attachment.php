<?php
/**
 * AJAX: Upload Task Attachment
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
$taskId = (int)($_POST['task_id'] ?? 0);

// Validate task ID
if (empty($taskId)) {
    echo jsonResponse(false, null, 'Task ID is required');
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $error = $_FILES['file']['error'] ?? 'Unknown error';
    echo jsonResponse(false, null, 'File upload failed: ' . $error);
    exit;
}

try {
    // Get task and project details to check permissions
    $stmt = $pdo->prepare("
        SELECT t.*, p.owner_id as project_owner
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

    // Check if user has access to this project
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
        echo jsonResponse(false, null, 'You do not have access to this task');
        exit;
    }

    // Check number of existing attachments
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM task_attachments WHERE task_id = ?");
    $stmt->execute([$taskId]);
    $count = $stmt->fetch()['count'];

    if ($count >= MAX_FILES_PER_TASK) {
        echo jsonResponse(false, null, 'Maximum ' . MAX_FILES_PER_TASK . ' files allowed per task');
        exit;
    }

    // Validate file upload
    $validation = validateFileUpload($_FILES['file']);
    if (!$validation['valid']) {
        echo jsonResponse(false, null, $validation['error']);
        exit;
    }

    // Get file info
    $originalName = basename($_FILES['file']['name']);
    $fileSize = $_FILES['file']['size'];
    $fileType = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // Generate secure filename
    $secureFilename = generateSecureFilename($fileType);

    // Ensure upload directory exists
    if (!is_dir(ATTACHMENT_PATH)) {
        mkdir(ATTACHMENT_PATH, 0755, true);
    }

    // Move uploaded file
    $targetPath = ATTACHMENT_PATH . '/' . $secureFilename;
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        echo jsonResponse(false, null, 'Failed to save uploaded file');
        exit;
    }

    // Insert attachment record
    $stmt = $pdo->prepare("
        INSERT INTO task_attachments (task_id, filename, original_name, file_size, file_type, uploaded_by)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $success = $stmt->execute([
        $taskId,
        $secureFilename,
        $originalName,
        $fileSize,
        $fileType,
        $userId
    ]);

    if (!$success) {
        // Clean up uploaded file
        @unlink($targetPath);
        echo jsonResponse(false, null, 'Failed to save attachment record');
        exit;
    }

    $attachmentId = $pdo->lastInsertId();

    // Log activity
    logActivity($pdo, $userId, 'task_attachment_added', "Added attachment '{$originalName}' to task", $task['project_id'], $taskId);

    // Return attachment data
    echo jsonResponse(true, [
        'attachment' => [
            'id' => $attachmentId,
            'filename' => $secureFilename,
            'original_name' => $originalName,
            'file_size' => $fileSize,
            'file_type' => $fileType,
            'uploaded_by' => $userId,
            'uploaded_at' => date('Y-m-d H:i:s')
        ]
    ], 'File uploaded successfully');

} catch (PDOException $e) {
    error_log("Upload attachment error: " . $e->getMessage());
    echo jsonResponse(false, null, 'An error occurred. Please try again later.');
} catch (Exception $e) {
    error_log("Upload attachment error: " . $e->getMessage());
    echo jsonResponse(false, null, 'An error occurred. Please try again later.');
}
