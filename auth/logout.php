<?php
/**
 * Logout Handler
 * Destroys user session and redirects to login
 */

session_start();
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Log activity before logout if user is logged in
if (isLoggedIn()) {
    try {
        $userId = getCurrentUserId();
        logActivity($pdo, $userId, 'user_logout', 'User logged out');
    } catch (Exception $e) {
        error_log("Error logging logout activity: " . $e->getMessage());
    }
}

// Logout user
logoutUser();

// Redirect to login page
redirect('login.php');
