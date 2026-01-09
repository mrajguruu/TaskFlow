<?php
/**
 * Database Configuration Template
 *
 * IMPORTANT: Copy this file to 'database.php' and update with your actual credentials
 * The database.php file is gitignored for security
 *
 * For Render.com: Uses environment variables automatically
 * For Localhost: Define constants manually
 */

// Use environment variables if available (Render/TiDB), otherwise use constants
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'taskflow');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// PDO options for better security and error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
];

// Create PDO connection with port support (for TiDB Cloud)
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // In production, log this error instead of displaying
    die("Database connection failed: " . $e->getMessage());
}
