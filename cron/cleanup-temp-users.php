<?php
/**
 * TaskFlow - Automated User Cleanup Script
 *
 * This script deletes temporary users created more than X hours ago
 * Triggered by cron job
 * Protected users (demo accounts) are never deleted
 */

// Set headers
header('Content-Type: application/json');

// Prevent direct browser access
if (php_sapi_name() !== 'cli' && !isset($_GET['token'])) {
    http_response_code(403);
    die(json_encode(['error' => 'Direct access forbidden']));
}

try {
    // Load configuration - works with environment variables (Render) or local files
    $secretsPath = __DIR__ . '/../config/secrets.php';
    $dbPath = __DIR__ . '/../config/database.php';

    // Load secrets from file or environment variables
    if (file_exists($secretsPath)) {
        $secrets = require $secretsPath;
    } else {
        // Production (Render) - use environment variables
        $secrets = [
            'cleanup_token' => getenv('CLEANUP_TOKEN'),
            'protected_user_ids' => [1, 2, 3, 4, 5, 6, 7, 8],
            'cleanup_age_hours' => 1,
            'min_interval_seconds' => 600,
            'max_delete_per_run' => 100,
        ];
    }

    // Validate token
    $providedToken = $_GET['token'] ?? $_SERVER['HTTP_X_CLEANUP_TOKEN'] ?? getenv('CLEANUP_TOKEN') ?? '';

    if (empty($providedToken) || empty($secrets['cleanup_token']) || !hash_equals($secrets['cleanup_token'], $providedToken)) {
        http_response_code(403);
        throw new Exception('Invalid or missing cleanup token');
    }

    // Rate limiting - prevent running too frequently
    $lastRunFile = __DIR__ . '/../logs/last-cleanup-run.txt';
    $minInterval = $secrets['min_interval_seconds'] ?? 600; // 10 minutes default

    if (file_exists($lastRunFile)) {
        $lastRun = (int)file_get_contents($lastRunFile);
        $timeSinceLastRun = time() - $lastRun;

        if ($timeSinceLastRun < $minInterval) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'error' => 'Rate limited',
                'message' => "Cleanup ran {$timeSinceLastRun}s ago. Min interval: {$minInterval}s",
                'next_allowed_in' => $minInterval - $timeSinceLastRun
            ]);
            exit;
        }
    }

    // Update last run timestamp
    @file_put_contents($lastRunFile, time()); // @ suppresses warning if logs dir doesn't exist

    // Load database configuration or use environment variables
    if (file_exists($dbPath)) {
        require $dbPath;
    } else {
        // Production (Render) - use environment variables
        define('DB_HOST', getenv('DB_HOST'));
        define('DB_PORT', getenv('DB_PORT') ?: '4000');
        define('DB_NAME', getenv('DB_NAME'));
        define('DB_USER', getenv('DB_USER'));
        define('DB_PASS', getenv('DB_PASS'));
    }

    // Connect to database using constants from database.php (with port support for TiDB)
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // Configuration
    $cleanupAgeHours = $secrets['cleanup_age_hours'] ?? 1;
    $protectedUserIds = $secrets['protected_user_ids'] ?? [1, 2, 3, 4, 5, 6, 7, 8];
    $maxDeletePerRun = $secrets['max_delete_per_run'] ?? 100;

    // Find users to delete
    $placeholders = implode(',', array_fill(0, count($protectedUserIds), '?'));

    $sql = "SELECT id, username, email, created_at
            FROM users
            WHERE id NOT IN ($placeholders)
            AND created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)
            ORDER BY created_at ASC
            LIMIT ?";

    $params = array_merge($protectedUserIds, [$cleanupAgeHours, $maxDeletePerRun]);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $usersToDelete = $stmt->fetchAll();

    $deletedCount = 0;
    $deletedUsers = [];

    if (count($usersToDelete) > 0) {
        // Begin transaction
        $pdo->beginTransaction();

        try {
            foreach ($usersToDelete as $user) {
                // Delete user (CASCADE will handle related records)
                $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $deleteStmt->execute([$user['id']]);

                $deletedCount++;
                $deletedUsers[] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'created_at' => $user['created_at']
                ];
            }

            // Commit transaction
            $pdo->commit();

        } catch (Exception $e) {
            // Rollback on error
            $pdo->rollBack();
            throw $e;
        }
    }

    // Log the cleanup
    $logFile = $secrets['log_file'] ?? __DIR__ . '/../logs/cleanup.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logEntry = sprintf(
        "[%s] Cleanup completed: %d user(s) deleted (age > %d hours)\n",
        date('Y-m-d H:i:s'),
        $deletedCount,
        $cleanupAgeHours
    );

    if ($secrets['enable_detailed_logging'] ?? false) {
        $logEntry .= "Deleted: " . json_encode($deletedUsers) . "\n";
    }

    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'deleted_count' => $deletedCount,
        'cleanup_age_hours' => $cleanupAgeHours,
        'protected_users' => count($protectedUserIds),
        'timestamp' => date('Y-m-d H:i:s'),
        'users' => ($secrets['enable_detailed_logging'] ?? false) ? $deletedUsers : null
    ]);

} catch (Exception $e) {
    // Log error
    $errorLog = __DIR__ . '/../logs/cleanup-error.log';
    $logDir = dirname($errorLog);

    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $errorEntry = sprintf(
        "[%s] ERROR: %s in %s:%d\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    @file_put_contents($errorLog, $errorEntry, FILE_APPEND);

    // Error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

exit;
