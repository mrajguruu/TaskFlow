<?php
/**
 * Single Project View - Kanban Board
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require user to be logged in
requireLogin();

// Page settings
$pageTitle = 'Project Board';
$activePage = 'projects';
$extraCSS = ['kanban.css', 'project-board.css', 'task-modal.css'];
$extraJS = ['task-modal.js', 'drag-drop.js'];

// Get current user
$currentUser = getCurrentUser($pdo);
$userId = $currentUser['id'];
$isAdmin = isAdmin();

// Get project ID
$projectId = $_GET['id'] ?? 0;

if (!$projectId) {
    header('Location: projects.php');
    exit;
}

try {
    // Get project details
    $stmt = $pdo->prepare("
        SELECT p.*,
               u.full_name as owner_name,
               (SELECT COUNT(*) FROM tasks WHERE project_id = p.id) as total_tasks,
               (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status = 'completed') as completed_tasks,
               (SELECT role FROM project_members WHERE project_id = p.id AND user_id = ?) as user_role
        FROM projects p
        LEFT JOIN users u ON p.owner_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$userId, $projectId]);
    $project = $stmt->fetch();

    if (!$project) {
        header('Location: projects.php');
        exit;
    }

    // Check access permission
    if (!$isAdmin && !checkProjectAccess($userId, $projectId, $pdo)) {
        die('Access denied. You are not a member of this project.');
    }

    // Get project members
    $stmt = $pdo->prepare("
        SELECT pm.*, u.full_name, u.email, u.avatar
        FROM project_members pm
        JOIN users u ON pm.user_id = u.id
        WHERE pm.project_id = ?
        ORDER BY pm.role DESC, u.full_name ASC
    ");
    $stmt->execute([$projectId]);
    $members = $stmt->fetchAll();

    // Get tasks grouped by status
    $stmt = $pdo->prepare("
        SELECT t.*, u.full_name as assignee_name, u.avatar as assignee_avatar,
               creator.full_name as creator_name
        FROM tasks t
        LEFT JOIN users u ON t.assigned_to = u.id
        LEFT JOIN users creator ON t.created_by = creator.id
        WHERE t.project_id = ?
        ORDER BY t.position ASC, t.created_at DESC
    ");
    $stmt->execute([$projectId]);
    $allTasks = $stmt->fetchAll();

    // Group tasks by status
    $tasks = [
        'todo' => [],
        'in_progress' => [],
        'completed' => []
    ];

    foreach ($allTasks as $task) {
        $tasks[$task['status']][] = $task;
    }

    // Calculate progress
    $progress = calculateProgress($project['completed_tasks'], $project['total_tasks']);

} catch (PDOException $e) {
    error_log("Project view error: " . $e->getMessage());
    die('Error loading project');
}

// Include header
include '../includes/header.php';
?>

<!-- Enhanced Project Board Header -->
<div class="project-board-header">
    <div class="project-board-nav">
        <a href="projects.php" class="back-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Back to Projects
        </a>
    </div>

    <div class="project-board-title-row">
        <div class="project-board-info">
            <h1 class="project-board-title"><?= sanitize($project['name']) ?></h1>
            <?php if ($project['description']): ?>
                <p class="project-board-description"><?= sanitize($project['description']) ?></p>
            <?php endif; ?>
        </div>
        <button onclick="openCreateTaskModal()" class="btn btn-primary btn-with-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New Task
        </button>
    </div>

    <div class="project-board-meta-grid">
        <div class="meta-card">
            <div class="meta-icon meta-icon-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div class="meta-content">
                <div class="meta-label">Owner</div>
                <div class="meta-value"><?= sanitize($project['owner_name']) ?></div>
            </div>
        </div>

        <div class="meta-card">
            <div class="meta-icon meta-icon-info">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
            </div>
            <div class="meta-content">
                <div class="meta-label">Status</div>
                <div class="meta-value"><?= getStatusBadge($project['status']) ?></div>
            </div>
        </div>

        <div class="meta-card">
            <div class="meta-icon meta-icon-success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="meta-content">
                <div class="meta-label">Progress</div>
                <div class="meta-value"><?= $progress ?>% <span class="meta-subtext">(<?= $project['completed_tasks'] ?>/<?= $project['total_tasks'] ?>)</span></div>
            </div>
        </div>

        <div class="meta-card">
            <div class="meta-icon meta-icon-warning">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="meta-content">
                <div class="meta-label">Team</div>
                <div class="meta-value"><?= count($members) ?> members</div>
            </div>
        </div>

        <?php if ($project['end_date']): ?>
            <div class="meta-card <?= isOverdue($project['end_date']) ? 'meta-card-danger' : '' ?>">
                <div class="meta-icon <?= isOverdue($project['end_date']) ? 'meta-icon-danger' : 'meta-icon-primary' ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div class="meta-content">
                    <div class="meta-label">Due Date</div>
                    <div class="meta-value">
                        <?= formatDate($project['end_date']) ?>
                        <?php if (isOverdue($project['end_date'])): ?>
                            <span class="overdue-badge">Overdue</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="project-progress-bar-container">
        <div class="progress-bar-enhanced">
            <div class="progress-fill-enhanced" style="width: <?= $progress ?>%"></div>
        </div>
        <div class="progress-stats">
            <span class="progress-stat"><strong><?= $project['completed_tasks'] ?></strong> completed</span>
            <span class="progress-stat"><strong><?= count($tasks['in_progress']) ?></strong> in progress</span>
            <span class="progress-stat"><strong><?= count($tasks['todo']) ?></strong> to do</span>
        </div>
    </div>
</div>

<!-- Enhanced Kanban Board -->
<div class="kanban-board-enhanced">
    <!-- TODO Column -->
    <div class="kanban-column-enhanced kanban-todo" data-status="todo">
        <div class="kanban-column-header-enhanced">
            <div class="column-title-wrapper">
                <div class="column-icon column-icon-info">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 16v-4M12 8h.01"/>
                    </svg>
                </div>
                <span class="column-title">To Do</span>
            </div>
            <span class="column-count count-info"><?= count($tasks['todo']) ?></span>
        </div>
        <div class="kanban-tasks-enhanced" id="tasks-todo">
            <?php if (empty($tasks['todo'])): ?>
                <div class="kanban-empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <p>No tasks yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($tasks['todo'] as $task): ?>
                    <?php include 'task-card.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button class="add-task-btn-enhanced" onclick="openCreateTaskModal('todo')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Task
        </button>
    </div>

    <!-- IN PROGRESS Column -->
    <div class="kanban-column-enhanced kanban-in-progress" data-status="in_progress">
        <div class="kanban-column-header-enhanced">
            <div class="column-title-wrapper">
                <div class="column-icon column-icon-warning">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 4 23 10 17 10"/>
                        <polyline points="1 20 1 14 7 14"/>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                    </svg>
                </div>
                <span class="column-title">In Progress</span>
            </div>
            <span class="column-count count-warning"><?= count($tasks['in_progress']) ?></span>
        </div>
        <div class="kanban-tasks-enhanced" id="tasks-in_progress">
            <?php if (empty($tasks['in_progress'])): ?>
                <div class="kanban-empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polyline points="23 4 23 10 17 10"/>
                        <polyline points="1 20 1 14 7 14"/>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                    </svg>
                    <p>No tasks in progress</p>
                </div>
            <?php else: ?>
                <?php foreach ($tasks['in_progress'] as $task): ?>
                    <?php include 'task-card.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button class="add-task-btn-enhanced" onclick="openCreateTaskModal('in_progress')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Task
        </button>
    </div>

    <!-- COMPLETED Column -->
    <div class="kanban-column-enhanced kanban-completed" data-status="completed">
        <div class="kanban-column-header-enhanced">
            <div class="column-title-wrapper">
                <div class="column-icon column-icon-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
                <span class="column-title">Completed</span>
            </div>
            <span class="column-count count-success"><?= count($tasks['completed']) ?></span>
        </div>
        <div class="kanban-tasks-enhanced" id="tasks-completed">
            <?php if (empty($tasks['completed'])): ?>
                <div class="kanban-empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <p>No completed tasks</p>
                </div>
            <?php else: ?>
                <?php foreach ($tasks['completed'] as $task): ?>
                    <?php include 'task-card.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button class="add-task-btn-enhanced" onclick="openCreateTaskModal('completed')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Task
        </button>
    </div>
</div>

<!-- Task Details Modal is loaded via task-modal.js -->

<!-- Create Task Modal -->
<div class="modal-overlay hidden" id="createTaskModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Create New Task</h2>
            <button class="modal-close" onclick="closeCreateTaskModal()">&times;</button>
        </div>
        <form id="createTaskForm" onsubmit="submitCreateTask(event)">
            <input type="hidden" name="project_id" value="<?= $projectId ?>">
            <input type="hidden" name="status" id="task_status" value="todo">

            <div class="modal-body">
                <div class="form-group">
                    <label for="task_title" class="form-label">Task Title *</label>
                    <input
                        type="text"
                        id="task_title"
                        name="title"
                        class="form-input"
                        placeholder="Enter task title"
                        required
                        maxlength="255"
                    >
                </div>

                <div class="form-group">
                    <label for="task_description" class="form-label">Description</label>
                    <textarea
                        id="task_description"
                        name="description"
                        class="form-textarea"
                        placeholder="Describe the task..."
                        rows="3"
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="form-group">
                        <label for="task_priority" class="form-label">Priority *</label>
                        <select id="task_priority" name="priority" class="form-select" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="task_assigned_to" class="form-label">Assign To</label>
                        <select id="task_assigned_to" name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            <?php foreach ($members as $member): ?>
                                <option value="<?= $member['user_id'] ?>">
                                    <?= sanitize($member['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="task_due_date" class="form-label">Due Date</label>
                    <input
                        type="date"
                        id="task_due_date"
                        name="due_date"
                        class="form-input"
                        min="<?= date('Y-m-d') ?>"
                    >
                </div>

                <div id="createTaskError" class="alert alert-error hidden"></div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeCreateTaskModal()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" id="createTaskBtn">
                    Create Task
                </button>
            </div>
        </form>
    </div>
</div>

<!-- drag-drop.js is loaded via $extraJS -->
<script>
let defaultTaskStatus = 'todo';

function openCreateTaskModal(status = 'todo') {
    defaultTaskStatus = status;
    document.getElementById('task_status').value = status;
    document.getElementById('createTaskModal').classList.remove('hidden');
    document.getElementById('createTaskForm').reset();
    document.getElementById('createTaskError').classList.add('hidden');
}

function closeCreateTaskModal() {
    document.getElementById('createTaskModal').classList.add('hidden');
}

function submitCreateTask(e) {
    e.preventDefault();

    const btn = document.getElementById('createTaskBtn');
    const errorDiv = document.getElementById('createTaskError');

    btn.disabled = true;
    btn.textContent = 'Creating...';
    errorDiv.classList.add('hidden');

    const formData = new FormData(e.target);

    fetch('<?= APP_URL ?>/ajax/create-task.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreateTaskModal();

            // Show success toast
            if (typeof showToast === 'function') {
                showToast('Task created successfully!', 'success');
            }

            // Reload page after toast duration
            setTimeout(() => {
                window.location.reload();
            }, 8000);
        } else {
            errorDiv.textContent = data.message || 'Failed to create task';
            errorDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Create Task';

            // Show error toast
            if (typeof showToast === 'function') {
                showToast(data.message || 'Failed to create task', 'error');
            }
        }
    })
    .catch(error => {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Create Task';

        // Show error toast
        if (typeof showToast === 'function') {
            showToast('An error occurred. Please try again.', 'error');
        }
    });
}

// Close modal when clicking outside
document.getElementById('createTaskModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateTaskModal();
    }
});

// Task modal functionality is handled by task-modal.js
// viewTask() function is defined in drag-drop.js
</script>

<?php include '../includes/footer.php'; ?>
