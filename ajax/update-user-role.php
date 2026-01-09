<?php
/**
 * AJAX: Update User Role (Admin Only)
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
$newRole = $input['role'] ?? '';

// Validate
if (!$userId || !in_array($newRole, ['admin', 'member'])) {
    echo jsonResponse(false, null, 'Invalid input');
    exit;
}

// Prevent self-demotion
if ($userId == $currentUserId) {
    echo jsonResponse(false, null, 'You cannot change your own role');
    exit;
}

try {
    // Update role
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$newRole, $userId]);

    // Get user name for logging
    $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userName = $stmt->fetchColumn();

    // Log activity
    $action = $newRole === 'admin' ? 'user_promoted' : 'user_demoted';
    $description = "Changed {$userName}'s role to {$newRole}";
    logActivity($pdo, $currentUserId, $action, $description);

    echo jsonResponse(true, null, 'User role updated successfully');

} catch (PDOException $e) {
    error_log("Update user role error: " . $e->getMessage());
    echo jsonResponse(false, null, 'Failed to update user role');
}
