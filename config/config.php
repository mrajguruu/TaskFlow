<?php
/**
 * Application Configuration
 * Global settings and constants for TaskFlow
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE && !defined('NO_SESSION')) {
    session_start();
}

// Application Settings
define('APP_NAME', 'TaskFlow');
define('APP_VERSION', '1.0.0');

// Dynamic APP_URL - works on localhost and mobile
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = $protocol . '://' . $host . rtrim(str_replace('\\', '/', $scriptPath), '/');

// Remove /config, /dashboard, /auth, /includes from path if present
$baseUrl = preg_replace('#/(config|dashboard|auth|includes).*$#', '', $baseUrl);

define('APP_URL', $baseUrl);

// Path Settings
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('AVATAR_PATH', UPLOAD_PATH . '/avatars');
define('ATTACHMENT_PATH', UPLOAD_PATH . '/attachments');

// Upload Settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB in bytes
define('MAX_FILES_PER_TASK', 5);
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip']);

// Pagination Settings
define('TASKS_PER_PAGE', 20);
define('PROJECTS_PER_PAGE', 12);
define('COMMENTS_PER_PAGE', 10);
define('ACTIVITY_LOG_ITEMS', 15);

// Session Settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Security Settings
define('PASSWORD_MIN_LENGTH', 8);
define('USERNAME_MIN_LENGTH', 4);
define('USERNAME_MAX_LENGTH', 20);

// Date/Time Settings
date_default_timezone_set('UTC');
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'M d, Y');
define('DISPLAY_DATETIME_FORMAT', 'M d, Y H:i');

// Status Constants
define('TASK_STATUS_TODO', 'todo');
define('TASK_STATUS_IN_PROGRESS', 'in_progress');
define('TASK_STATUS_COMPLETED', 'completed');

define('TASK_PRIORITY_LOW', 'low');
define('TASK_PRIORITY_MEDIUM', 'medium');
define('TASK_PRIORITY_HIGH', 'high');

define('PROJECT_STATUS_ACTIVE', 'active');
define('PROJECT_STATUS_COMPLETED', 'completed');
define('PROJECT_STATUS_ARCHIVED', 'archived');

define('USER_ROLE_ADMIN', 'admin');
define('USER_ROLE_MEMBER', 'member');

define('PROJECT_ROLE_OWNER', 'owner');
define('PROJECT_ROLE_MEMBER', 'member');

// Environment (set to 'production' when deploying)
define('ENVIRONMENT', 'development');

// Error Reporting
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Database connection - works with environment variables (Render/TiDB) or local config
if (file_exists(__DIR__ . '/database.php')) {
    // Local development - use database.php file
    require_once __DIR__ . '/database.php';
} else {
    // Production (Render) - use environment variables directly
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
    define('DB_NAME', getenv('DB_NAME') ?: 'taskflow');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_CHARSET', 'utf8mb4');

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];

    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}