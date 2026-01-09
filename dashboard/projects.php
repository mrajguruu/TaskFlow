<?php
/**
 * Projects Page
 * List all projects with search, filter, and create functionality
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require user to be logged in
requireLogin();

// Page settings
$pageTitle = 'Projects';
$activePage = 'projects';

// Get current user
$currentUser = getCurrentUser($pdo);
$userId = $currentUser['id'];
$isAdmin = isAdmin();

// Handle search and filters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

try {
    // Base query
    $sql = "
        SELECT p.*,
               COUNT(DISTINCT t.id) as task_count,
               COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) as completed_count,
               COUNT(DISTINCT pm.user_id) as member_count,
               u.full_name as owner_name,
               (SELECT role FROM project_members WHERE project_id = p.id AND user_id = ?) as user_role
        FROM projects p
        LEFT JOIN users u ON p.owner_id = u.id
        LEFT JOIN tasks t ON p.id = t.project_id
        LEFT JOIN project_members pm ON p.id = pm.project_id
    ";

    // Admin sees all projects, regular users see only their projects
    if (!$isAdmin) {
        $sql .= " WHERE p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)";
    }

    // Add search
    if (!empty($search)) {
        $sql .= ($isAdmin ? " WHERE" : " AND") . " (p.name LIKE ? OR p.description LIKE ?)";
    }

    // Add status filter
    if (!empty($statusFilter)) {
        $sql .= (strpos($sql, 'WHERE') !== false ? " AND" : " WHERE") . " p.status = ?";
    }

    $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $params = [];
    if (!$isAdmin) {
        $params[] = $userId; // For user_role subquery
        $params[] = $userId; // For WHERE clause
    } else {
        $params[] = $userId; // For user_role subquery only
    }

    if (!empty($search)) {
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (!empty($statusFilter)) {
        $params[] = $statusFilter;
    }

    $stmt->execute($params);
    $projects = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Projects page error: " . $e->getMessage());
    $projects = [];
}

// Include header
include '../includes/header.php';
?>

<link rel="stylesheet" href="<?= asset('css/projects.css') ?>">

<!-- Projects Page Header -->
<div class="projects-page-header">
    <div class="projects-header-content">
        <div class="projects-header-icon">
            <?php if ($isAdmin): ?>
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            <?php else: ?>
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                </svg>
            <?php endif; ?>
        </div>
        <div>
            <h1 class="projects-page-title">
                <?= $isAdmin ? 'All Projects (Admin View)' : 'My Projects' ?>
            </h1>
            <p class="projects-page-subtitle">
                <?= $isAdmin ? 'Managing all projects in the system' : 'Projects you are a member of' ?>
            </p>
        </div>
    </div>
    <button onclick="openCreateProjectModal()" class="btn btn-primary btn-with-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        New Project
    </button>
</div>

<!-- Enhanced Search and Filters -->
<div class="search-filter-bar">
    <form method="GET" action="" class="search-filter-form">
        <div class="search-input-wrapper">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search projects by name or description..."
                value="<?= sanitize($search) ?>"
            >
        </div>
        <div class="filter-group">
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="archived" <?= $statusFilter === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
            <button type="submit" class="btn btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                Search
            </button>
            <?php if (!empty($search) || !empty($statusFilter)): ?>
                <a href="projects.php" class="btn btn-ghost">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Clear
                </a>
            <?php endif; ?>
        </div>
    </form>
    <div class="results-count">
        <span class="count-number"><?= count($projects) ?></span>
        <span class="count-label"><?= count($projects) === 1 ? 'project' : 'projects' ?> found</span>
    </div>
</div>

<?php if (empty($projects)): ?>
    <div class="card text-center p-4">
        <h3>No Projects Found</h3>
        <p class="text-muted mb-3">
            <?= !empty($search) ? 'No projects match your search.' : 'Create your first project to get started!' ?>
        </p>
        <button onclick="openCreateProjectModal()" class="btn btn-primary">
            Create Your First Project
        </button>
    </div>
<?php else: ?>
    <!-- Enhanced Projects Grid -->
    <div class="projects-grid-enhanced">
        <?php foreach ($projects as $project): ?>
            <?php
                $progress = calculateProgress($project['completed_count'], $project['task_count']);
                $statusClass = $project['status'] === 'active' ? 'success' : ($project['status'] === 'archived' ? 'secondary' : 'info');
            ?>
            <div class="project-card-enhanced">
                <!-- Card Header -->
                <div class="project-card-enhanced-header">
                    <div class="project-title-row">
                        <h3 class="project-title-enhanced">
                            <a href="project.php?id=<?= $project['id'] ?>">
                                <?= sanitize($project['name']) ?>
                            </a>
                        </h3>
                        <?= getStatusBadge($project['status']) ?>
                    </div>

                    <?php if ($isAdmin): ?>
                        <div class="project-owner-info">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span>Owner: <?= sanitize($project['owner_name']) ?></span>
                            <?php if ($project['user_role']): ?>
                                <span class="role-badge">Your Role: <strong><?= ucfirst($project['user_role']) ?></strong></span>
                            <?php else: ?>
                                <span class="not-member-badge">Not a member</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <p class="project-description-enhanced">
                    <?= truncate(sanitize($project['description'] ?? 'No description provided'), 120) ?>
                </p>

                <!-- Stats Grid -->
                <div class="project-stats-enhanced">
                    <div class="stat-item-enhanced">
                        <div class="stat-icon-mini stat-icon-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 11l3 3L22 4"/>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                        </div>
                        <div class="stat-content-mini">
                            <div class="stat-value-mini"><?= $project['task_count'] ?></div>
                            <div class="stat-label-mini">Tasks</div>
                        </div>
                    </div>
                    <div class="stat-item-enhanced">
                        <div class="stat-icon-mini stat-icon-success">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div class="stat-content-mini">
                            <div class="stat-value-mini"><?= $project['member_count'] ?></div>
                            <div class="stat-label-mini">Members</div>
                        </div>
                    </div>
                    <div class="stat-item-enhanced">
                        <div class="stat-icon-mini stat-icon-info">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <div class="stat-content-mini">
                            <div class="stat-value-mini"><?= $progress ?>%</div>
                            <div class="stat-label-mini">Progress</div>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="progress-bar-enhanced">
                    <div class="progress-fill-enhanced" style="width: <?= $progress ?>%"></div>
                </div>

                <!-- Due Date (if exists) -->
                <?php if (!empty($project['end_date'])): ?>
                    <div class="project-due-date <?= isOverdue($project['end_date']) && $project['status'] === 'active' ? 'overdue' : '' ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <span>Due: <?= formatDate($project['end_date']) ?></span>
                        <?php if (isOverdue($project['end_date']) && $project['status'] === 'active'): ?>
                            <span class="overdue-label">Overdue</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="project-actions-enhanced">
                    <a href="project.php?id=<?= $project['id'] ?>" class="btn btn-primary btn-sm btn-with-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        View Board
                    </a>

                    <?php if ($isAdmin || $project['user_role'] === 'owner'): ?>
                        <button onclick="editProject(<?= $project['id'] ?>)" class="btn btn-ghost btn-sm btn-with-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                            Edit
                        </button>
                    <?php endif; ?>

                    <?php if ($isAdmin): ?>
                        <button onclick="deleteProject(<?= $project['id'] ?>, '<?= sanitize($project['name']) ?>')" class="btn btn-danger btn-sm btn-with-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                            Delete
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Create Project Modal -->
<div class="modal-overlay hidden" id="createProjectModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Create New Project</h2>
            <button class="modal-close" onclick="closeCreateProjectModal()">&times;</button>
        </div>
        <form id="createProjectForm" onsubmit="submitCreateProject(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label for="project_name" class="form-label">Project Name *</label>
                    <input
                        type="text"
                        id="project_name"
                        name="name"
                        class="form-input"
                        placeholder="Enter project name"
                        required
                        maxlength="150"
                    >
                </div>

                <div class="form-group">
                    <label for="project_description" class="form-label">Description</label>
                    <textarea
                        id="project_description"
                        name="description"
                        class="form-textarea"
                        placeholder="Describe your project..."
                        rows="4"
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="form-group">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            class="form-input"
                        >
                    </div>

                    <div class="form-group">
                        <label for="end_date" class="form-label">End Date</label>
                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            class="form-input"
                        >
                    </div>
                </div>

                <div id="createProjectError" class="alert alert-error hidden"></div>
                <div id="createProjectSuccess" class="alert alert-success hidden"></div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeCreateProjectModal()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" id="createProjectBtn">
                    Create Project
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Project Modal -->
<div class="modal-overlay hidden" id="editProjectModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Edit Project</h2>
            <button class="modal-close" onclick="closeEditProjectModal()">&times;</button>
        </div>
        <form id="editProjectForm" onsubmit="submitEditProject(event)">
            <input type="hidden" id="edit_project_id" name="project_id">
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_project_name" class="form-label">Project Name *</label>
                    <input
                        type="text"
                        id="edit_project_name"
                        name="name"
                        class="form-input"
                        placeholder="Enter project name"
                        required
                        maxlength="150"
                    >
                </div>

                <div class="form-group">
                    <label for="edit_project_description" class="form-label">Description</label>
                    <textarea
                        id="edit_project_description"
                        name="description"
                        class="form-textarea"
                        placeholder="Describe your project..."
                        rows="4"
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="form-group">
                        <label for="edit_start_date" class="form-label">Start Date</label>
                        <input
                            type="date"
                            id="edit_start_date"
                            name="start_date"
                            class="form-input"
                        >
                    </div>

                    <div class="form-group">
                        <label for="edit_end_date" class="form-label">End Date</label>
                        <input
                            type="date"
                            id="edit_end_date"
                            name="end_date"
                            class="form-input"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_project_status" class="form-label">Status</label>
                    <select id="edit_project_status" name="status" class="form-input">
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>

                <div id="editProjectError" class="alert alert-error hidden"></div>
                <div id="editProjectSuccess" class="alert alert-success hidden"></div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeEditProjectModal()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" id="editProjectBtn">
                    Update Project
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateProjectModal() {
    document.getElementById('createProjectModal').classList.remove('hidden');
    document.getElementById('createProjectForm').reset();
    document.getElementById('createProjectError').classList.add('hidden');
    document.getElementById('createProjectSuccess').classList.add('hidden');
}

function closeCreateProjectModal() {
    document.getElementById('createProjectModal').classList.add('hidden');
}

function submitCreateProject(e) {
    e.preventDefault();

    const btn = document.getElementById('createProjectBtn');
    const errorDiv = document.getElementById('createProjectError');
    const successDiv = document.getElementById('createProjectSuccess');

    btn.disabled = true;
    btn.textContent = 'Creating...';
    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    const formData = new FormData(e.target);

    fetch('<?= APP_URL ?>/ajax/create-project.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            successDiv.textContent = 'Project created successfully! Redirecting...';
            successDiv.classList.remove('hidden');
            setTimeout(() => {
                window.location.href = 'project.php?id=' + data.project_id;
            }, 1000);
        } else {
            errorDiv.textContent = data.message || 'Failed to create project';
            errorDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Create Project';
        }
    })
    .catch(error => {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Create Project';
    });
}

function deleteProject(projectId, projectName) {
    if (!confirm(`Are you sure you want to delete "${projectName}"?\n\nThis will delete all tasks, comments, and attachments. This action cannot be undone!`)) {
        return;
    }

    fetch('<?= APP_URL ?>/ajax/delete-project.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ project_id: projectId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Project deleted successfully');
            window.location.reload();
        } else {
            alert(data.message || 'Failed to delete project');
        }
    })
    .catch(error => {
        alert('An error occurred');
    });
}

function editProject(projectId) {
    // Fetch project data
    fetch('<?= APP_URL ?>/ajax/get-project.php?id=' + projectId)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const project = data.data;

            // Populate form fields
            document.getElementById('edit_project_id').value = project.id;
            document.getElementById('edit_project_name').value = project.name;
            document.getElementById('edit_project_description').value = project.description || '';
            document.getElementById('edit_start_date').value = project.start_date || '';
            document.getElementById('edit_end_date').value = project.end_date || '';
            document.getElementById('edit_project_status').value = project.status;

            // Clear alerts
            document.getElementById('editProjectError').classList.add('hidden');
            document.getElementById('editProjectSuccess').classList.add('hidden');

            // Show modal
            document.getElementById('editProjectModal').classList.remove('hidden');
        } else {
            alert(data.message || 'Failed to load project details');
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        console.error('Error:', error);
    });
}

function closeEditProjectModal() {
    document.getElementById('editProjectModal').classList.add('hidden');
}

function submitEditProject(e) {
    e.preventDefault();

    const btn = document.getElementById('editProjectBtn');
    const errorDiv = document.getElementById('editProjectError');
    const successDiv = document.getElementById('editProjectSuccess');

    btn.disabled = true;
    btn.textContent = 'Updating...';
    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    const formData = new FormData(e.target);

    fetch('<?= APP_URL ?>/ajax/update-project.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            successDiv.textContent = 'Project updated successfully! Reloading...';
            successDiv.classList.remove('hidden');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            errorDiv.textContent = data.message || 'Failed to update project';
            errorDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Update Project';
        }
    })
    .catch(error => {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Update Project';
    });
}

// Close modals when clicking outside
document.getElementById('createProjectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateProjectModal();
    }
});

document.getElementById('editProjectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditProjectModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
