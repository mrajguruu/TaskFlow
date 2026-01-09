<?php
/**
 * Team Page
 * View all team members
 * Admins can manage users
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require user to be logged in
requireLogin();

// Page settings
$pageTitle = 'Team';
$activePage = 'team';
$extraCSS = ['team.css'];

// Get current user
$currentUser = getCurrentUser($pdo);
$userId = $currentUser['id'];
$isAdmin = isAdmin();

try {
    if ($isAdmin) {
        // Admin sees ALL users in the system
        $stmt = $pdo->prepare("
            SELECT u.*,
                   COUNT(DISTINCT pm.project_id) as project_count,
                   COUNT(DISTINCT t.id) as task_count,
                   COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) as completed_count
            FROM users u
            LEFT JOIN project_members pm ON u.id = pm.user_id
            LEFT JOIN tasks t ON u.id = t.assigned_to
            GROUP BY u.id
            ORDER BY u.role DESC, u.full_name ASC
        ");
        $stmt->execute();
    } else {
        // Regular users see only teammates (users in same projects)
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.*,
                   COUNT(DISTINCT pm2.project_id) as project_count,
                   COUNT(DISTINCT t.id) as task_count,
                   COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) as completed_count
            FROM users u
            JOIN project_members pm1 ON u.id = pm1.user_id
            LEFT JOIN project_members pm2 ON u.id = pm2.user_id
            LEFT JOIN tasks t ON u.id = t.assigned_to
            WHERE pm1.project_id IN (
                SELECT project_id FROM project_members WHERE user_id = ?
            )
            GROUP BY u.id
            ORDER BY u.full_name ASC
        ");
        $stmt->execute([$userId]);
    }

    $teamMembers = $stmt->fetchAll();

    // Get total stats (admin only)
    if ($isAdmin) {
        $totalUsers = count($teamMembers);
        $adminCount = count(array_filter($teamMembers, fn($m) => $m['role'] === 'admin'));
        $memberCount = $totalUsers - $adminCount;
    }

} catch (PDOException $e) {
    error_log("Team page error: " . $e->getMessage());
    $teamMembers = [];
}

// Include header
include '../includes/header.php';
?>

<!-- Team Page Header -->
<div class="team-page-header">
    <div class="team-header-content">
        <div class="team-header-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div>
            <h1 class="team-page-title">
                <?= $isAdmin ? 'Team Management (Admin)' : 'Team Members' ?>
            </h1>
            <p class="team-page-subtitle">
                <?= $isAdmin ? 'Manage all users in the system' : 'Your teammates across all projects' ?>
            </p>
        </div>
    </div>
</div>

<?php if ($isAdmin): ?>
    <!-- Admin Stats -->
    <div class="team-stats-grid">
        <div class="team-stat-card">
            <div class="team-stat-header">
                <div class="team-stat-icon team-stat-icon-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div class="team-stat-content">
                    <div class="team-stat-value"><?= $totalUsers ?></div>
                    <div class="team-stat-label">Total Users</div>
                </div>
            </div>
        </div>
        <div class="team-stat-card">
            <div class="team-stat-header">
                <div class="team-stat-icon team-stat-icon-warning">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <div class="team-stat-content">
                    <div class="team-stat-value"><?= $adminCount ?></div>
                    <div class="team-stat-label">Administrators</div>
                </div>
            </div>
        </div>
        <div class="team-stat-card">
            <div class="team-stat-header">
                <div class="team-stat-icon team-stat-icon-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div class="team-stat-content">
                    <div class="team-stat-value"><?= $memberCount ?></div>
                    <div class="team-stat-label">Members</div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Team Members Grid -->
<div class="team-members-grid">
    <?php foreach ($teamMembers as $member): ?>
        <div class="team-member-card">
            <div class="team-member-header">
                <div class="team-member-avatar-wrapper">
                    <?= getUserAvatar($member['avatar'], $member['full_name'], 'lg') ?>
                </div>

                <div class="team-member-info">
                    <div class="team-member-name-row">
                        <h3 class="team-member-name"><?= sanitize($member['full_name']) ?></h3>
                        <?php if ($member['role'] === 'admin'): ?>
                            <span class="team-member-role-badge">Admin</span>
                        <?php endif; ?>
                    </div>
                    <p class="team-member-email"><?= sanitize($member['email']) ?></p>
                    <p class="team-member-username">@<?= sanitize($member['username']) ?></p>
                </div>
            </div>

            <!-- User Stats -->
            <div class="team-member-stats">
                <div class="team-member-stat">
                    <div class="team-member-stat-value"><?= $member['project_count'] ?></div>
                    <div class="team-member-stat-label">Projects</div>
                </div>
                <div class="team-member-stat">
                    <div class="team-member-stat-value"><?= $member['task_count'] ?></div>
                    <div class="team-member-stat-label">Tasks</div>
                </div>
                <div class="team-member-stat">
                    <div class="team-member-stat-value success"><?= $member['completed_count'] ?></div>
                    <div class="team-member-stat-label">Done</div>
                </div>
            </div>

            <!-- Activity Info -->
            <div class="team-member-activity">
                Joined <?= timeAgo($member['created_at']) ?>
                <?php if ($member['last_login']): ?>
                    • Last active <?= timeAgo($member['last_login']) ?>
                <?php endif; ?>
            </div>

            <?php if ($member['id'] === $userId): ?>
                <!-- Current User Badge -->
                <div class="current-user-badge">
                    This is you
                </div>
            <?php endif; ?>

            <?php if ($isAdmin && $member['id'] !== $userId): ?>
                <!-- Admin Actions -->
                <div class="team-member-actions">
                    <?php if ($member['role'] === 'member'): ?>
                        <button onclick="makeAdmin(<?= $member['id'] ?>, '<?= sanitize($member['full_name']) ?>')"
                                class="btn btn-secondary btn-sm">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                <path d="M2 17l10 5 10-5"/>
                                <path d="M2 12l10 5 10-5"/>
                            </svg>
                            Make Admin
                        </button>
                    <?php else: ?>
                        <button onclick="removeAdmin(<?= $member['id'] ?>, '<?= sanitize($member['full_name']) ?>')"
                                class="btn btn-ghost btn-sm">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 11H7M12 17V7"/>
                            </svg>
                            Remove Admin
                        </button>
                    <?php endif; ?>
                    <button onclick="deleteUser(<?= $member['id'] ?>, '<?= sanitize($member['full_name']) ?>')"
                            class="btn btn-danger btn-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                        Delete
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($isAdmin): ?>
<script>
function makeAdmin(userId, userName) {
    if (!confirm(`Promote "${userName}" to Administrator?\n\nAdmins can:\n• View and delete all projects\n• Manage all users\n• Access system-wide settings`)) {
        return;
    }

    fetch('<?= APP_URL ?>/ajax/update-user-role.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId, role: 'admin' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`${userName} is now an Administrator!`);
            window.location.reload();
        } else {
            alert(data.message || 'Failed to update user role');
        }
    })
    .catch(error => alert('An error occurred'));
}

function removeAdmin(userId, userName) {
    if (!confirm(`Remove admin privileges from "${userName}"?\n\nThey will become a regular member.`)) {
        return;
    }

    fetch('<?= APP_URL ?>/ajax/update-user-role.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId, role: 'member' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`${userName} is now a regular Member`);
            window.location.reload();
        } else {
            alert(data.message || 'Failed to update user role');
        }
    })
    .catch(error => alert('An error occurred'));
}

function deleteUser(userId, userName) {
    if (!confirm(`⚠️ DELETE USER: "${userName}"?\n\nThis will:\n• Remove them from all projects\n• Delete all their tasks\n• Remove all their activity\n\nThis CANNOT be undone!`)) {
        return;
    }

    const confirmText = prompt(`Type "${userName}" to confirm deletion:`);
    if (confirmText !== userName) {
        alert('Name did not match. Deletion cancelled.');
        return;
    }

    fetch('<?= APP_URL ?>/ajax/delete-user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User deleted successfully');
            window.location.reload();
        } else {
            alert(data.message || 'Failed to delete user');
        }
    })
    .catch(error => alert('An error occurred'));
}
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
