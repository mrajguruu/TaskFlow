/**
 * Drag and Drop Functionality for Kanban Board
 */

document.addEventListener('DOMContentLoaded', function() {
    initDragAndDrop();
});

function initDragAndDrop() {
    const taskCards = document.querySelectorAll('.task-card');
    const columns = document.querySelectorAll('.kanban-tasks-enhanced');

    // Make task cards draggable
    taskCards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);

        // Add click event to open task modal
        card.addEventListener('click', function(e) {
            // Don't trigger if dragging
            if (!this.classList.contains('dragging')) {
                const taskId = this.dataset.taskId;
                const projectId = this.dataset.projectId || getProjectIdFromUrl();
                if (window.taskModal && taskId) {
                    window.taskModal.open(taskId, projectId);
                }
            }
        });

        // Add pointer cursor
        card.style.cursor = 'pointer';
    });

    // Make columns droppable
    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('dragleave', handleDragLeave);
        column.addEventListener('drop', handleDrop);
    });
}

// Helper function to get project ID from URL
function getProjectIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}

let draggedElement = null;

function handleDragStart(e) {
    draggedElement = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
}

function handleDragEnd(e) {
    this.classList.remove('dragging');

    // Remove drag-over class from all columns
    document.querySelectorAll('.kanban-column-enhanced').forEach(col => {
        col.classList.remove('drag-over');
    });
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }

    e.dataTransfer.dropEffect = 'move';

    // Add visual feedback
    const column = e.currentTarget.closest('.kanban-column-enhanced');
    if (column) {
        column.classList.add('drag-over');
    }

    return false;
}

function handleDragLeave(e) {
    const column = e.currentTarget.closest('.kanban-column-enhanced');
    if (column) {
        column.classList.remove('drag-over');
    }
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }

    const column = e.currentTarget.closest('.kanban-column-enhanced');
    column.classList.remove('drag-over');

    if (draggedElement) {
        const taskId = draggedElement.dataset.taskId;
        const oldStatus = draggedElement.dataset.status;
        const newStatus = column.dataset.status;

        if (oldStatus !== newStatus) {
            // Update task status via AJAX
            updateTaskStatus(taskId, newStatus, draggedElement);
        }
    }

    return false;
}

function updateTaskStatus(taskId, newStatus, cardElement) {
    // Show loading state
    cardElement.style.opacity = '0.5';

    const requestData = {
        task_id: parseInt(taskId),
        status: newStatus
    };

    fetch('/TaskFlow/ajax/update-task-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        try {
            return JSON.parse(text);
        } catch (e) {
            // console.error('JSON parse error:', e);
            throw new Error('Invalid JSON response from server');
        }
    })
    .then(data => {
        if (data.success) {
            // Move card to new column
            const newColumn = document.getElementById(`tasks-${newStatus}`);
            if (newColumn) {
                // Remove empty state if exists
                const emptyState = newColumn.querySelector('.kanban-empty');
                if (emptyState) {
                    emptyState.remove();
                }

                // Move the card
                newColumn.appendChild(cardElement);
                cardElement.dataset.status = newStatus;
                cardElement.style.opacity = '1';

                // Update column counts
                updateColumnCounts();

                // Show success message
                if (typeof showToast === 'function') {
                    showToast(data.message || 'Task moved successfully', 'success');
                }
            }
        } else {
            // Revert on error
            cardElement.style.opacity = '1';
            const errorMessage = data.message || 'Failed to update task';
            if (typeof showToast === 'function') {
                showToast(errorMessage, 'error');
            }
        }
    })
    .catch(error => {
        cardElement.style.opacity = '1';
        // console.error('Update task status error:', error);
        if (typeof showToast === 'function') {
            showToast('An error occurred: ' + error.message, 'error');
        }
    });
}

function updateColumnCounts() {
    document.querySelectorAll('.kanban-column-enhanced').forEach(column => {
        const status = column.dataset.status;
        const taskContainer = document.getElementById(`tasks-${status}`);
        const count = taskContainer.querySelectorAll('.task-card').length;
        const countBadge = column.querySelector('.column-count');

        if (countBadge) {
            countBadge.textContent = count;
        }

        // Add empty state if no tasks
        if (count === 0 && !taskContainer.querySelector('.kanban-empty')) {
            const emptyDiv = document.createElement('div');
            emptyDiv.className = 'kanban-empty';
            emptyDiv.textContent = 'No tasks';
            taskContainer.appendChild(emptyDiv);
        }
    });
}

// View task details
function viewTask(taskId, projectId) {
    if (window.taskModal) {
        const projId = projectId || getProjectIdFromUrl();
        window.taskModal.open(taskId, projId);
    } else {
        // console.error('Task modal not initialized');
    }
}

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
