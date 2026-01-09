<?php
/**
 * TaskFlow - Task Management System
 * Main Entry Point
 *
 * This file serves as the landing page and redirects users to appropriate pages
 * based on their authentication status.
 */

session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Redirect to dashboard if logged in
    header('Location: dashboard/index.php');
    exit;
} else {
    // Redirect to login page if not logged in
    header('Location: auth/login.php');
    exit;
}
