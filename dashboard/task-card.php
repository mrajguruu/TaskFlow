<?php
/**
 * Task Card Component
 * Displayed in Kanban columns
 * $task variable must be available
 */
?>
<div class="task-card priority-<?= $task['priority'] ?>"
     draggable="true"
     data-task-id="<?= $task['id'] ?>"
     data-project-id="<?= $projectId ?>"
     data-status="<?= $task['status'] ?>">

    <div class="task-card-header">
        <div class="task-card-title" onclick="viewTask(<?= $task['id'] ?>)">
            <?= sanitize($task['title']) ?>
        </div>
    </div>

    <?php if ($task['description']): ?>
        <div class="task-card-description">
            <?= truncate(sanitize($task['description']), 100) ?>
        </div>
    <?php endif; ?>

    <div class="task-card-footer">
        <div class="task-card-meta">
            <?= getPriorityBadge($task['priority']) ?>
            <?php if ($task['due_date']): ?>
                <span class="task-card-due <?= isOverdue($task['due_date']) && $task['status'] !== 'completed' ? 'overdue' : '' ?>">
                    📅 <?= formatDate($task['due_date']) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($task['assignee_name']): ?>
        <div class="task-card-assignee">
            <?= getUserAvatar($task['assignee_avatar'], $task['assignee_name'], 'sm') ?>
            <span><?= sanitize($task['assignee_name']) ?></span>
        </div>
    <?php endif; ?>
</div>
