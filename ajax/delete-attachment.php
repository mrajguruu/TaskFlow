<?php
/**
 * AJAX: Delete Task Attachment
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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$attachmentId = (int)($input['attachment_id'] ?? 0);

if (empty($attachmentId)) {
    echo jsonResponse(false, null, 'Attachment ID is required');
    exit;
}

try {
    // Get attachment details and check permissions
    $stmt = $pdo->prepare("
        SELECT a.*, t.project_id, p.owner_id as project_owner
        FROM task_attachments a
        JOIN tasks t ON a.task_id = t.id
        JOIN projects p ON t.project_id = p.id
        WHERE a.id = ?
    ");
    $stmt->execute([$attachmentId]);
    $attachment = $stmt->fetch();

    if (!$attachment) {
        echo jsonResponse(false, null, 'Attachment not found');
        exit;
    }

    // Check if user has access to delete (owner, project member, admin, or uploader)
    $hasAccess = false;

    // Check if user uploaded the file
    if ($attachment['uploaded_by'] == $userId) {
        $hasAccess = true;
    }

    // Check if user is project owner
    if (!$hasAccess && $attachment['project_owner'] == $userId) {
        $hasAccess = true;
    }

    // Check if user is a project member
    if (!$hasAccess) {
        $memberCheck = $pdo->prepare("
            SELECT id FROM project_members
            WHERE project_id = ? AND user_id = ?
        ");
        $memberCheck->execute([$attachment['project_id'], $userId]);
        if ($memberCheck->fetch()) {
            $hasAccess = true;
        }
    }

    // Check if user is admin
    if (!$hasAccess && isAdmin()) {
        $hasAccess = true;
    }

    if (!$hasAccess) {
        echo jsonResponse(false, null, 'You do not have permission to delete this attachment');
        exit;
    }

    // Delete file from filesystem
    $filePath = ATTACHMENT_PATH . '/' . $attachment['filename'];
    if (file_exists($filePath)) {
        @unlink($filePath);
    }

    // Delete database record
    $stmt = $pdo->prepare("DELETE FROM task_attachments WHERE id = ?");
    $success = $stmt->execute([$attachmentId]);

    if (!$success) {
        echo jsonResponse(false, null, 'Failed to delete attachment');
        exit;
    }

    // Log activity
    logActivity($pdo, $userId, 'task_attachment_deleted', "Deleted attachment '{$attachment['original_name']}'", $attachment['project_id'], $attachment['task_id']);

    echo jsonResponse(true, null, 'Attachment deleted successfully');

} catch (PDOException $e) {
    error_log("Delete attachment error: " . $e->getMessage());
    echo jsonResponse(false, null, 'An error occurred. Please try again later.');
} catch (Exception $e) {
    error_log("Delete attachment error: " . $e->getMessage());
    echo jsonResponse(false, null, 'An error occurred. Please try again later.');
}
