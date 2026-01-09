<?php
/**
 * General Helper Functions
 * Utility functions used throughout the application
 */

/**
 * Generate asset URL
 *
 * @param string $path Path to asset
 * @return string Full URL to asset
 */
function asset($path) {
    return APP_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Sanitize input to prevent XSS attacks
 *
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 *
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Format date for display
 *
 * @param string $date Date string
 * @param string $format Format string (default: DISPLAY_DATE_FORMAT)
 * @return string Formatted date
 */
function formatDate($date, $format = DISPLAY_DATE_FORMAT) {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 *
 * @param string $datetime Datetime string
 * @return string Formatted datetime
 */
function formatDateTime($datetime) {
    return formatDate($datetime, DISPLAY_DATETIME_FORMAT);
}

/**
 * Get time ago string (e.g., "2 hours ago")
 *
 * @param string $datetime Datetime string
 * @return string Time ago string
 */
function timeAgo($datetime) {
    if (empty($datetime)) {
        return 'N/A';
    }

    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($diff / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}

/**
 * Check if date is overdue
 *
 * @param string $date Date string
 * @return bool True if overdue, false otherwise
 */
function isOverdue($date) {
    if (empty($date) || $date === '0000-00-00') {
        return false;
    }
    return strtotime($date) < strtotime('today');
}

/**
 * Get status badge HTML
 *
 * @param string $status Status value
 * @return string Badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'todo' => '<span class="badge badge-todo">To Do</span>',
        'in_progress' => '<span class="badge badge-progress">In Progress</span>',
        'completed' => '<span class="badge badge-completed">Completed</span>',
        'active' => '<span class="badge badge-active">Active</span>',
        'archived' => '<span class="badge badge-archived">Archived</span>'
    ];
    return $badges[$status] ?? '<span class="badge">' . sanitize($status) . '</span>';
}

/**
 * Get priority badge HTML
 *
 * @param string $priority Priority value
 * @return string Badge HTML
 */
function getPriorityBadge($priority) {
    $badges = [
        'low' => '<span class="badge badge-low">Low</span>',
        'medium' => '<span class="badge badge-medium">Medium</span>',
        'high' => '<span class="badge badge-high">High</span>'
    ];
    return $badges[$priority] ?? '<span class="badge">' . sanitize($priority) . '</span>';
}

/**
 * Get role badge HTML
 *
 * @param string $role User role
 * @return string Badge HTML
 */
function getRoleBadge($role) {
    $badges = [
        'admin' => '<span class="badge badge-warning">Admin</span>',
        'member' => '<span class="badge badge-info">Member</span>'
    ];
    return $badges[$role] ?? '<span class="badge">' . sanitize($role) . '</span>';
}

/**
 * Get priority indicator (colored dot)
 *
 * @param string $priority Priority value
 * @return string Indicator HTML
 */
function getPriorityIndicator($priority) {
    $class = 'priority-' . $priority;
    return '<span class="priority-indicator ' . $class . '"></span>';
}

/**
 * Format file size for display
 *
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Get file type icon
 *
 * @param string $fileType File extension
 * @return string Icon HTML/emoji
 */
function getFileTypeIcon($fileType) {
    $icons = [
        'pdf' => '📄',
        'doc' => '📝',
        'docx' => '📝',
        'xls' => '📊',
        'xlsx' => '📊',
        'jpg' => '🖼️',
        'jpeg' => '🖼️',
        'png' => '🖼️',
        'gif' => '🖼️',
        'zip' => '📦',
        'txt' => '📃'
    ];
    return $icons[$fileType] ?? '📎';
}

/**
 * Calculate project progress percentage
 *
 * @param int $completed Number of completed tasks
 * @param int $total Total number of tasks
 * @return float Progress percentage
 */
function calculateProgress($completed, $total) {
    if ($total == 0) {
        return 0;
    }
    return round(($completed / $total) * 100, 1);
}

/**
 * Generate progress bar HTML
 *
 * @param float $percentage Progress percentage
 * @param string $color Color class (optional)
 * @return string Progress bar HTML
 */
function getProgressBar($percentage, $color = 'primary') {
    $width = max(0, min(100, $percentage));
    return '
    <div class="progress-bar">
        <div class="progress-fill progress-' . sanitize($color) . '" style="width: ' . $width . '%"></div>
    </div>
    ';
}

/**
 * Get user avatar HTML
 *
 * @param string $avatar Avatar filename
 * @param string $fullName User's full name
 * @param string $size Size class (sm, md, lg)
 * @return string Avatar HTML
 */
function getUserAvatar($avatar, $fullName, $size = 'md') {
    $avatarPath = APP_URL . '/uploads/avatars/' . ($avatar ?: 'default-avatar.png');
    $initial = strtoupper(substr($fullName, 0, 1));
    $sizeClass = 'avatar-' . $size;

    // If avatar doesn't exist, show initial
    if (empty($avatar) || $avatar === 'default-avatar.png') {
        return '<div class="avatar-placeholder ' . $sizeClass . '">' . $initial . '</div>';
    }

    return '<img src="' . $avatarPath . '" alt="' . sanitize($fullName) . '" class="avatar ' . $sizeClass . '">';
}

/**
 * Validate file upload
 *
 * @param array $file $_FILES array element
 * @return array ['valid' => bool, 'message' => string, 'extension' => string]
 */
function validateFileUpload($file) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['valid' => false, 'message' => 'No file uploaded'];
    }

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'message' => 'File upload error'];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['valid' => false, 'message' => 'File too large (max ' . formatFileSize(MAX_FILE_SIZE) . ')'];
    }

    // Check file type
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_FILE_TYPES)) {
        return ['valid' => false, 'message' => 'File type not allowed'];
    }

    return [
        'valid' => true,
        'message' => 'File is valid',
        'extension' => $extension
    ];
}

/**
 * Generate secure random filename
 *
 * @param string $extension File extension
 * @return string Random filename
 */
function generateSecureFilename($extension) {
    return bin2hex(random_bytes(16)) . '.' . $extension;
}

/**
 * Log activity to database
 *
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @param string $action Action performed
 * @param string $description Action description
 * @param int|null $projectId Project ID (optional)
 * @param int|null $taskId Task ID (optional)
 * @return bool Success status
 */
function logActivity($pdo, $userId, $action, $description, $projectId = null, $taskId = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_id, project_id, task_id, action, description)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$userId, $projectId, $taskId, $action, $description]);
    } catch (PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Get JSON response
 *
 * @param bool $success Success status
 * @param mixed $data Data to return
 * @param string $message Message
 * @return string JSON response
 */
function jsonResponse($success, $data = null, $message = '') {
    header('Content-Type: application/json');
    return json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
}

/**
 * Truncate text to specified length
 *
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add (default: '...')
 * @return string Truncated text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length - strlen($suffix)) . $suffix;
}

/**
 * Generate CSRF token
 *
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 *
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF input field HTML
 *
 * @return string Hidden input field HTML
 */
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}
