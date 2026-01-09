<?php
/**
 * User Profile Page
 * Edit profile information and settings
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require user to be logged in
requireLogin();

// Page settings
$pageTitle = 'My Profile';
$activePage = 'profile';

// Get current user
$currentUser = getCurrentUser($pdo);
$userId = $currentUser['id'];

// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($fullName) || empty($email)) {
        $errorMessage = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Invalid email format.';
    } else {
        try {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                $errorMessage = 'Email is already taken by another user.';
            } else {
                // Update profile
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                $stmt->execute([$fullName, $email, $userId]);

                // Update password if provided
                if (!empty($newPassword)) {
                    if (empty($currentPassword)) {
                        $errorMessage = 'Current password is required to set a new password.';
                    } elseif (!password_verify($currentPassword, $currentUser['password'])) {
                        $errorMessage = 'Current password is incorrect.';
                    } elseif (strlen($newPassword) < 6) {
                        $errorMessage = 'New password must be at least 6 characters.';
                    } elseif ($newPassword !== $confirmPassword) {
                        $errorMessage = 'New passwords do not match.';
                    } else {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$hashedPassword, $userId]);
                        $successMessage = 'Profile and password updated successfully!';
                    }
                } else {
                    $successMessage = 'Profile updated successfully!';
                }

                // Refresh user data
                $currentUser = getCurrentUser($pdo);
            }
        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            $errorMessage = 'An error occurred while updating your profile.';
        }
    }
}

// Get user statistics
try {
    // Total projects
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT p.id)
        FROM projects p
        JOIN project_members pm ON p.id = pm.project_id
        WHERE pm.user_id = ?
    ");
    $stmt->execute([$userId]);
    $totalProjects = $stmt->fetchColumn();

    // Total tasks
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        JOIN project_members pm ON p.id = pm.project_id
        WHERE pm.user_id = ?
    ");
    $stmt->execute([$userId]);
    $totalTasks = $stmt->fetchColumn();

    // Completed tasks
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        JOIN project_members pm ON p.id = pm.project_id
        WHERE pm.user_id = ? AND t.status = 'completed'
    ");
    $stmt->execute([$userId]);
    $completedTasks = $stmt->fetchColumn();

    // Member since
    $memberSince = date('F Y', strtotime($currentUser['created_at']));

} catch (PDOException $e) {
    error_log("Profile stats error: " . $e->getMessage());
    $totalProjects = $totalTasks = $completedTasks = 0;
    $memberSince = 'Unknown';
}

// Include header
include '../includes/header.php';
?>

<link rel="stylesheet" href="<?= asset('css/profile.css') ?>">

<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-header-content">
            <div class="profile-avatar-large">
                <?= getUserAvatar($currentUser['avatar'], $currentUser['full_name'], 'xl') ?>
            </div>
            <div class="profile-header-info">
                <h1 class="profile-name"><?= sanitize($currentUser['full_name']) ?></h1>
                <p class="profile-email"><?= sanitize($currentUser['email']) ?></p>
                <div class="profile-badges">
                    <?= getRoleBadge($currentUser['role']) ?>
                    <span class="badge badge-gray">Member since <?= $memberSince ?></span>
                </div>
            </div>
        </div>
        <div class="profile-stats-mini">
            <div class="stat-mini">
                <div class="stat-mini-value"><?= $totalProjects ?></div>
                <div class="stat-mini-label">Projects</div>
            </div>
            <div class="stat-mini">
                <div class="stat-mini-value"><?= $completedTasks ?></div>
                <div class="stat-mini-label">Completed</div>
            </div>
            <div class="stat-mini">
                <div class="stat-mini-value"><?= $totalTasks ?></div>
                <div class="stat-mini-label">Total Tasks</div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($successMessage): ?>
        <div class="alert alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <?= sanitize($successMessage) ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <?= sanitize($errorMessage) ?>
        </div>
    <?php endif; ?>

    <!-- Profile Content -->
    <div class="profile-grid">
        <!-- Left Column: Edit Profile Form -->
        <div class="profile-left-column">
            <div class="profile-section">
                <div class="section-header-profile">
                    <h2 class="section-title-profile">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        Profile Information
                    </h2>
                    <p class="section-subtitle-profile">Update your account details</p>
                </div>

                <form method="POST" class="profile-form" id="profileForm">
                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        class="form-input"
                        value="<?= sanitize($currentUser['full_name']) ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        value="<?= sanitize($currentUser['email']) ?>"
                        required
                    >
                </div>

                <div class="form-divider"></div>

                <div class="section-header-profile">
                    <h3 class="section-title-profile">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        Change Password
                    </h3>
                    <p class="section-subtitle-profile">Leave blank to keep current password</p>
                </div>

                <div class="form-group">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        class="form-input"
                        placeholder="Enter current password"
                    >
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password</label>
                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            class="form-input"
                            placeholder="Enter new password"
                        >
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="form-input"
                            placeholder="Confirm new password"
                        >
                    </div>
                </div>
                </form>
            </div>

            <!-- Form Actions Outside Container -->
            <div class="profile-form-actions-container">
                <button type="submit" form="profileForm" name="update_profile" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Save Changes
                </button>
                <a href="index.php" class="btn btn-ghost">Cancel</a>
            </div>
        </div>

        <!-- Right Column: Account Information Sidebar -->
        <div class="profile-sidebar">
            <!-- Account Details -->
            <div class="profile-section">
                <div class="section-header-profile">
                    <h3 class="section-title-profile">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        Account Details
                    </h3>
                </div>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">User ID</span>
                        <span class="info-value">#<?= str_pad($currentUser['id'], 5, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Role</span>
                        <span class="info-value"><?= ucfirst($currentUser['role']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Member Since</span>
                        <span class="info-value"><?= $memberSince ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Login</span>
                        <span class="info-value"><?= !empty($currentUser['last_login']) ? timeAgo($currentUser['last_login']) : 'Never' ?></span>
                    </div>
                </div>
            </div>

            <!-- Activity Summary -->
            <div class="profile-section">
                <div class="section-header-profile">
                    <h3 class="section-title-profile">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                        Activity Summary
                    </h3>
                </div>
                <div class="activity-summary">
                    <div class="activity-summary-item">
                        <div class="activity-icon activity-icon-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                            </svg>
                        </div>
                        <div class="activity-details">
                            <div class="activity-value"><?= $totalProjects ?></div>
                            <div class="activity-label">Total Projects</div>
                        </div>
                    </div>
                    <div class="activity-summary-item">
                        <div class="activity-icon activity-icon-success">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                        <div class="activity-details">
                            <div class="activity-value"><?= $completedTasks ?></div>
                            <div class="activity-label">Tasks Completed</div>
                        </div>
                    </div>
                    <div class="activity-summary-item">
                        <div class="activity-icon activity-icon-warning">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 11l3 3L22 4"/>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                        </div>
                        <div class="activity-details">
                            <div class="activity-value"><?= $totalTasks - $completedTasks ?></div>
                            <div class="activity-label">Tasks Remaining</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danger Zone - Full Width -->
    <div class="danger-zone-container">
        <div class="danger-zone-header">
            <div class="danger-zone-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <h3 class="danger-zone-title">Delete Account</h3>
        </div>
        <div class="danger-zone-content">
            <div class="danger-zone-text-section">
                <p class="danger-zone-text">Permanently delete your account and all associated data from TaskFlow.</p>
                <p class="danger-zone-warning">This action cannot be undone. All your projects, tasks, and activity will be permanently removed.</p>
            </div>
            <button class="btn btn-danger" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
                Delete Account
            </button>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
