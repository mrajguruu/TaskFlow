<?php
/**
 * Authentication Helper Functions
 * Handles user authentication, authorization, and session management
 */

/**
 * Check if user is logged in
 *
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Require user to be logged in, redirect to login if not
 *
 * @param string $redirect_url URL to redirect after login
 * @return void
 */
function requireLogin($redirect_url = null) {
    if (!isLoggedIn()) {
        if ($redirect_url) {
            $_SESSION['redirect_after_login'] = $redirect_url;
        }
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Get current logged in user ID
 *
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

/**
 * Get current logged in user data
 *
 * @param PDO $pdo Database connection
 * @return array|null User data or null if not logged in
 */
function getCurrentUser($pdo) {
    $userId = getCurrentUserId();
    if (!$userId) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT id, username, email, full_name, avatar, role, created_at, last_login FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Check if current user is admin
 *
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === USER_ROLE_ADMIN;
}

/**
 * Login user and create session
 *
 * @param int $userId User ID
 * @param array $userData User data from database
 * @param bool $remember Whether to set remember me cookie
 * @return void
 */
function loginUser($userId, $userData, $remember = false) {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $userData['username'];
    $_SESSION['user_role'] = $userData['role'];
    $_SESSION['full_name'] = $userData['full_name'];
    $_SESSION['avatar'] = $userData['avatar'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    // Set remember me cookie if requested (30 days)
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
        // TODO: Store token in database for validation
    }
}

/**
 * Logout user and destroy session
 *
 * @return void
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Delete remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }

    // Destroy the session
    session_destroy();
}

/**
 * Check if user has access to a project
 *
 * @param int $userId User ID
 * @param int $projectId Project ID
 * @param PDO $pdo Database connection
 * @return bool True if user has access, false otherwise
 */
function checkProjectAccess($userId, $projectId, $pdo) {
    // Check if user is admin - admins have access to all projects
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userRole = $stmt->fetchColumn();

    if ($userRole === 'admin') {
        return true;
    }

    // Check if user is the project owner/creator
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE id = ? AND owner_id = ?");
    $stmt->execute([$projectId, $userId]);
    if ($stmt->fetchColumn() > 0) {
        return true;
    }

    // Check if user is a member of the project
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM project_members WHERE user_id = ? AND project_id = ?");
    $stmt->execute([$userId, $projectId]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Check if user is project owner
 *
 * @param int $userId User ID
 * @param int $projectId Project ID
 * @param PDO $pdo Database connection
 * @return bool True if user is owner, false otherwise
 */
function isProjectOwner($userId, $projectId, $pdo) {
    $stmt = $pdo->prepare("SELECT role FROM project_members WHERE user_id = ? AND project_id = ?");
    $stmt->execute([$userId, $projectId]);
    $role = $stmt->fetchColumn();
    return $role === PROJECT_ROLE_OWNER;
}

/**
 * Check if user can edit a task
 *
 * @param int $userId User ID
 * @param int $taskId Task ID
 * @param PDO $pdo Database connection
 * @return bool True if user can edit, false otherwise
 */
function canEditTask($userId, $taskId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT t.created_by, pm.role
        FROM tasks t
        JOIN project_members pm ON t.project_id = pm.project_id
        WHERE t.id = ? AND pm.user_id = ?
    ");
    $stmt->execute([$taskId, $userId]);
    $result = $stmt->fetch();

    // Can edit if creator or project owner
    return $result && ($result['created_by'] == $userId || $result['role'] === PROJECT_ROLE_OWNER);
}

/**
 * Check if user can delete a task
 *
 * @param int $userId User ID
 * @param int $taskId Task ID
 * @param PDO $pdo Database connection
 * @return bool True if user can delete, false otherwise
 */
function canDeleteTask($userId, $taskId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT t.created_by, pm.role
        FROM tasks t
        JOIN project_members pm ON t.project_id = pm.project_id
        WHERE t.id = ? AND pm.user_id = ?
    ");
    $stmt->execute([$taskId, $userId]);
    $result = $stmt->fetch();

    // Can delete if creator or project owner
    return $result && ($result['created_by'] == $userId || $result['role'] === PROJECT_ROLE_OWNER);
}

/**
 * Validate username format
 *
 * @param string $username Username to validate
 * @return bool True if valid, false otherwise
 */
function validateUsername($username) {
    $length = strlen($username);
    return $length >= USERNAME_MIN_LENGTH &&
           $length <= USERNAME_MAX_LENGTH &&
           preg_match('/^[a-zA-Z0-9_]+$/', $username);
}

/**
 * Validate email format
 *
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 *
 * @param string $password Password to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validatePassword($password) {
    $length = strlen($password);

    if ($length < PASSWORD_MIN_LENGTH) {
        return [
            'valid' => false,
            'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'
        ];
    }

    if (!preg_match('/[A-Z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one uppercase letter'
        ];
    }

    if (!preg_match('/[a-z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one lowercase letter'
        ];
    }

    if (!preg_match('/[0-9]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one number'
        ];
    }

    return [
        'valid' => true,
        'message' => 'Password is strong'
    ];
}

/**
 * Hash password securely
 *
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 *
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if password matches, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if session has timed out
 *
 * @return bool True if timed out, false otherwise
 */
function checkSessionTimeout() {
    if (isset($_SESSION['login_time'])) {
        $elapsed = time() - $_SESSION['login_time'];
        if ($elapsed >= SESSION_TIMEOUT) {
            return true;
        }
        // Update login time to extend session
        $_SESSION['login_time'] = time();
    }
    return false;
}
