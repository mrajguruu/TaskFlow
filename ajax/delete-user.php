<?php
/**
 * AJAX: Delete User (Admin Only)
 */

session_start();
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isAdmin()) {
    echo jsonResponse(false, null, 'Unauthorized - Admin access required');
    exit;
}

$currentUserId = getCurrentUserId();

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['user_id'] ?? 0;

if (!$userId) {
    echo jsonResponse(false, null, 'Invalid user ID');
    exit;
}

// Prevent self-deletion
if ($userId == $currentUserId) {
    echo jsonResponse(false, null, 'You cannot delete your own account');
    exit;
}

try {
    // Get user info before deletion
    $stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo jsonResponse(false, null, 'User not found');
        exit;
    }

    // Protect demo users (IDs 1-8)
    if ($userId <= 8) {
        echo jsonResponse(false, null, 'Demo users cannot be deleted. Create your own users to test deletion functionality.');
        exit;
    }

    // Delete user (CASCADE will handle related records)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);

    // Log activity
    $description = "Deleted user: {$user['full_name']} ({$user['email']})";
    logActivity($pdo, $currentUserId, 'user_deleted', $description);

    echo jsonResponse(true, null, 'User deleted successfully');

} catch (PDOException $e) {
    error_log("Delete user error: " . $e->getMessage());
    echo jsonResponse(false, null, 'Failed to delete user');
}
