<?php
/**
 * Dashboard - Main Page
 * Overview of user's projects, tasks, and activity
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require user to be logged in
requireLogin();

// Page settings
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// Get current user
$currentUser = getCurrentUser($pdo);
$userId = $currentUser['id'];

// Get user statistics
try {
    // Total active projects
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM projects p
        JOIN project_members pm ON p.id = pm.project_id
        WHERE pm.user_id = ? AND p.status = 'active'
    ");
    $stmt->execute([$userId]);
    $totalProjects = $stmt->fetchColumn();

    // Total active tasks assigned to user
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM tasks
        WHERE assigned_to = ? AND status != 'completed'
    ");
    $stmt->execute([$userId]);
    $activeTasks = $stmt->fetchColumn();

    // Tasks completed this week
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM tasks
        WHERE assigned_to = ?
        AND status = 'completed'
        AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$userId]);
    $completedThisWeek = $stmt->fetchColumn();

    // Overall progress
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total,
            COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        JOIN project_members pm ON p.id = pm.project_id
        WHERE pm.user_id = ?
    ");
    $stmt->execute([$userId]);
    $progressData = $stmt->fetch();
    $overallProgress = $progressData['total'] > 0
        ? calculateProgress($progressData['completed'], $progressData['total'])
        : 0;

    // Get user's projects (top 6)
    $stmt = $pdo->prepare("
        SELECT p.*,
               COUNT(DISTINCT t.id) as task_count,
               COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) as completed_count,
               COUNT(DISTINCT pm.user_id) as member_count
        FROM projects p
        JOIN project_members pm ON p.id = pm.project_id
        LEFT JOIN tasks t ON p.id = t.project_id
        WHERE pm.user_id = ? AND p.status = 'active'
        GROUP BY p.id
        ORDER BY p.updated_at DESC
        LIMIT 6
    ");
    $stmt->execute([$userId]);
    $projects = $stmt->fetchAll();

    // Get user's recent tasks (top 10)
    $stmt = $pdo->prepare("
        SELECT t.*, p.name as project_name, u.full_name as assignee_name
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        LEFT JOIN users u ON t.assigned_to = u.id
        JOIN project_members pm ON p.id = pm.project_id
        WHERE pm.user_id = ? AND t.status != 'completed'
        ORDER BY
            CASE t.priority
                WHEN 'high' THEN 1
                WHEN 'medium' THEN 2
                WHEN 'low' THEN 3
            END,
            t.due_date ASC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $recentTasks = $stmt->fetchAll();

    // Get recent activity
    $stmt = $pdo->prepare("
        SELECT al.*, u.full_name, u.avatar, p.name as project_name
        FROM activity_log al
        JOIN users u ON al.user_id = u.id
        LEFT JOIN projects p ON al.project_id = p.id
        WHERE al.project_id IN (
            SELECT project_id FROM project_members WHERE user_id = ?
        )
        ORDER BY al.created_at DESC
        LIMIT 15
    ");
    $stmt->execute([$userId]);
    $activities = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $totalProjects = $activeTasks = $completedThisWeek = 0;
    $overallProgress = 0;
    $projects = $recentTasks = $activities = [];
}

// Include header
include '../includes/header.php';
?>

<!-- Dashboard Header with Greeting -->
<div class="dashboard-welcome">
    <div class="welcome-content">
        <h1 class="welcome-title">Welcome back, <?= sanitize($currentUser['full_name']) ?>!</h1>
        <p class="welcome-subtitle">Here's your productivity overview for today</p>
    </div>
    <div class="welcome-date">
        <div class="date-display">
            <div class="date-day"><?= date('l') ?></div>
            <div class="date-full"><?= date('F j, Y') ?></div>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Cards -->
<div class="stats-grid-enhanced">
    <div class="stat-card-enhanced stat-primary">
        <div class="stat-icon-wrapper">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
            </svg>
        </div>
        <div class="stat-details">
            <div class="stat-value-large"><?= $totalProjects ?></div>
            <div class="stat-label-enhanced">Active Projects</div>
            <div class="stat-description">Currently running</div>
        </div>
    </div>

    <div class="stat-card-enhanced stat-warning">
        <div class="stat-icon-wrapper">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 11l3 3L22 4"/>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
        </div>
        <div class="stat-details">
            <div class="stat-value-large"><?= $activeTasks ?></div>
            <div class="stat-label-enhanced">Active Tasks</div>
            <div class="stat-description">Pending completion</div>
        </div>
    </div>

    <div class="stat-card-enhanced stat-success">
        <div class="stat-icon-wrapper">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div class="stat-details">
            <div class="stat-value-large"><?= $completedThisWeek ?></div>
            <div class="stat-label-enhanced">Completed This Week</div>
            <?php if ($completedThisWeek > 0): ?>
                <div class="stat-trend-badge success">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="18 15 12 9 6 15"/>
                    </svg>
                    Great progress!
                </div>
            <?php else: ?>
                <div class="stat-description">Let's get started</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card-enhanced stat-info">
        <div class="stat-icon-wrapper">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div class="stat-details">
            <div class="stat-value-large"><?= $overallProgress ?>%</div>
            <div class="stat-label-enhanced">Overall Progress</div>
            <div class="progress-bar-mini">
                <div class="progress-fill-mini" style="width: <?= $overallProgress ?>%"></div>
            </div>
        </div>
    </div>
</div>

<!-- My Projects Section -->
<div class="section">
    <div class="section-header">
        <h2 class="section-title">My Projects</h2>
        <a href="projects.php" class="btn btn-ghost btn-sm">View All</a>
    </div>

    <?php if (empty($projects)): ?>
        <div class="card">
            <p class="text-center text-muted">No active projects yet. Create your first project to get started!</p>
            <div class="text-center mt-3">
                <a href="projects.php" class="btn btn-primary">Create Project</a>
            </div>
        </div>
    <?php else: ?>
        <div class="projects-grid">
            <?php foreach ($projects as $project): ?>
                <?php
                    $progress = calculateProgress($project['completed_count'], $project['task_count']);
                ?>
                <div class="project-card">
                    <div class="project-card-header">
                        <h3 class="project-title"><?= sanitize($project['name']) ?></h3>
                    </div>

                    <p class="project-description">
                        <?= sanitize($project['description'] ?? 'No description') ?>
                    </p>

                    <div class="project-stats">
                        <div class="project-stat">
                            <div class="project-stat-value"><?= $project['task_count'] ?></div>
                            <div class="project-stat-label">Tasks</div>
                        </div>
                        <div class="project-stat">
                            <div class="project-stat-value"><?= $project['member_count'] ?></div>
                            <div class="project-stat-label">Members</div>
                        </div>
                        <div class="project-stat">
                            <div class="project-stat-value"><?= $progress ?>%</div>
                            <div class="project-stat-label">Progress</div>
                        </div>
                    </div>

                    <?= getProgressBar($progress) ?>

                    <div class="project-footer mt-2">
                        <a href="project.php?id=<?= $project['id'] ?>" class="btn btn-sm btn-primary">
                            View Project
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Two Column Layout: Tasks & Activity -->
<div class="grid grid-cols-2 gap-4">
    <!-- My Tasks -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title">My Tasks</h2>
        </div>

        <?php if (empty($recentTasks)): ?>
            <div class="card">
                <p class="text-muted">No active tasks assigned to you.</p>
            </div>
        <?php else: ?>
            <div class="task-list">
                <?php foreach ($recentTasks as $task): ?>
                    <div class="task-item">
                        <div class="task-content">
                            <div class="task-title"><?= sanitize($task['title']) ?></div>
                            <div class="task-meta">
                                <?= getPriorityBadge($task['priority']) ?>
                                <span>📁 <?= sanitize($task['project_name']) ?></span>
                                <?php if ($task['due_date']): ?>
                                    <span class="<?= isOverdue($task['due_date']) ? 'text-danger' : '' ?>">
                                        📅 <?= formatDate($task['due_date']) ?>
                                        <?php if (isOverdue($task['due_date'])): ?>
                                            (Overdue)
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Activity -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Recent Activity</h2>
        </div>

        <?php if (empty($activities)): ?>
            <div class="card">
                <p class="text-muted">No recent activity.</p>
            </div>
        <?php else: ?>
            <div class="activity-list">
                <?php foreach ($activities as $activity): ?>
                    <div class="activity-item">
                        <?= getUserAvatar($activity['avatar'], $activity['full_name'], 'sm') ?>
                        <div class="activity-content">
                            <div class="activity-text">
                                <strong><?= sanitize($activity['full_name']) ?></strong>
                                <?= sanitize($activity['description']) ?>
                                <?php if ($activity['project_name']): ?>
                                    in <strong><?= sanitize($activity['project_name']) ?></strong>
                                <?php endif; ?>
                            </div>
                            <div class="activity-time"><?= timeAgo($activity['created_at']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
