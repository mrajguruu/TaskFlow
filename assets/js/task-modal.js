/**
 * Task Details Modal
 * Handle task viewing and inline editing
 */

class TaskModal {
    constructor() {
        this.modal = null;
        this.overlay = null;
        this.currentTaskId = null;
        this.currentProjectId = null;
        this.isEditMode = false;
        this.init();
    }

    init() {
        // Create modal structure if it doesn't exist
        if (!document.getElementById('task-modal-overlay')) {
            this.createModalStructure();
        }

        this.modal = document.getElementById('task-modal');
        this.overlay = document.getElementById('task-modal-overlay');

        // Bind events
        this.bindEvents();
    }

    createModalStructure() {
        const modalHTML = `
            <div class="task-modal-overlay" id="task-modal-overlay">
                <div class="task-modal" id="task-modal">
                    <!-- Modal content will be dynamically loaded -->
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    bindEvents() {
        // Close on overlay click
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) {
                this.close();
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.overlay.classList.contains('active')) {
                this.close();
            }
        });
    }

    async open(taskId, projectId) {
        this.currentTaskId = taskId;
        this.currentProjectId = projectId;
        this.isEditMode = false;

        // Show overlay
        this.overlay.classList.add('active');

        // Load task details
        await this.loadTaskDetails();
    }

    close() {
        this.overlay.classList.remove('active');
        this.currentTaskId = null;
        this.currentProjectId = null;
        this.isEditMode = false;
    }

    async loadTaskDetails() {
        try {
            // Show loading state
            this.modal.innerHTML = `
                <div class="task-modal-loading">
                    <p>Loading task details...</p>
                </div>
            `;

            // Fetch task details
            const response = await fetch(`../ajax/get-task-details.php?task_id=${this.currentTaskId}`);
            const text = await response.text();
            const data = JSON.parse(text);

            if (data.success && data.data && data.data.task) {
                this.renderTaskDetails(data.data.task);
            } else {
                this.showError(data.message || 'Failed to load task details');
            }
        } catch (error) {
            // console.error('Error loading task:', error);
            this.showError('An error occurred while loading task details: ' + error.message);
        }
    }

    renderTaskDetails(task) {
        const modalHTML = `
            <div class="task-modal-header">
                <div class="task-modal-title-section">
                    <h2 class="task-modal-title">${this.escapeHtml(task.title)}</h2>
                    <div class="task-modal-meta">
                        <span class="task-modal-meta-item">
                            📅 Created ${this.formatDate(task.created_at)}
                        </span>
                        ${task.assigned_to_name ? `
                            <span class="task-modal-meta-item">
                                👤 ${this.escapeHtml(task.assigned_to_name)}
                            </span>
                        ` : ''}
                    </div>
                </div>
                <button class="task-modal-close" id="modal-close-btn">
                    ✕
                </button>
            </div>

            <div class="task-modal-body" id="modal-body">
                <div class="task-details-grid">
                    <div class="task-detail-item">
                        <div class="task-detail-label">Status</div>
                        <div class="task-detail-value">
                            <span class="task-status-badge ${this.getStatusClass(task.status)}">
                                ${this.formatStatusDisplay(task.status)}
                            </span>
                        </div>
                        <div class="task-detail-edit">
                            <select id="edit-status" class="task-edit-input">
                                <option value="todo" ${task.status === 'todo' ? 'selected' : ''}>To Do</option>
                                <option value="in_progress" ${task.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                <option value="completed" ${task.status === 'completed' ? 'selected' : ''}>Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="task-detail-item">
                        <div class="task-detail-label">Priority</div>
                        <div class="task-detail-value">
                            <span class="task-priority-badge ${this.getPriorityClass(task.priority)}">
                                ${this.formatPriorityDisplay(task.priority)}
                            </span>
                        </div>
                        <div class="task-detail-edit">
                            <select id="edit-priority" class="task-edit-input">
                                <option value="low" ${task.priority === 'low' ? 'selected' : ''}>Low</option>
                                <option value="medium" ${task.priority === 'medium' ? 'selected' : ''}>Medium</option>
                                <option value="high" ${task.priority === 'high' ? 'selected' : ''}>High</option>
                            </select>
                        </div>
                    </div>

                    <div class="task-detail-item">
                        <div class="task-detail-label">Due Date</div>
                        <div class="task-detail-value">
                            ${task.due_date ? this.formatDate(task.due_date) : '<span style="color: #9ca3af;">No due date</span>'}
                        </div>
                        <div class="task-detail-edit">
                            <input type="date" id="edit-due-date" class="task-edit-input" value="${task.due_date || ''}">
                        </div>
                    </div>

                    <div class="task-detail-item">
                        <div class="task-detail-label">Assigned To</div>
                        <div class="task-detail-value">
                            ${task.assigned_to_name || '<span style="color: #9ca3af;">Unassigned</span>'}
                        </div>
                        <div class="task-detail-edit">
                            <select id="edit-assigned-to" class="task-edit-input">
                                <option value="">Unassigned</option>
                                ${task.available_users ? task.available_users.map(user => `
                                    <option value="${user.id}" ${task.assigned_to == user.id ? 'selected' : ''}>
                                        ${this.escapeHtml(user.name)}
                                    </option>
                                `).join('') : ''}
                            </select>
                        </div>
                    </div>
                </div>

                <div class="task-description-section">
                    <h3>Description</h3>
                    <div class="task-detail-value">
                        <div class="task-description ${!task.description ? 'empty' : ''}">
                            ${task.description ? this.escapeHtml(task.description) : 'No description provided'}
                        </div>
                    </div>
                    <div class="task-detail-edit">
                        <textarea id="edit-description" class="task-edit-input" placeholder="Enter task description...">${task.description || ''}</textarea>
                    </div>
                </div>

                <div class="task-attachments-section">
                    <h3>📎 Attachments (${task.attachments ? task.attachments.length : 0}/5)</h3>
                    <div class="attachments-list" id="attachments-list">
                        ${task.attachments && task.attachments.length > 0 ? task.attachments.map(att => this.renderAttachment(att)).join('') : '<p class="no-attachments">No attachments yet</p>'}
                    </div>
                    <div class="attachment-upload-area">
                        <input type="file" id="attachment-upload-input" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip" style="display: none;">
                        <button class="btn-upload-attachment" id="upload-attachment-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                            </svg>
                            Upload File
                        </button>
                        <small style="color: #6b7280; display: block; margin-top: 8px;">Max 10MB • PDF, DOC, XLS, Images, ZIP</small>
                    </div>
                </div>
            </div>

            <div class="task-modal-footer">
                <div>
                    <button class="task-modal-btn task-modal-btn-danger" id="delete-task-btn">
                        🗑️ Delete Task
                    </button>
                </div>
                <div class="task-modal-actions">
                    <button class="task-modal-btn task-modal-btn-secondary" id="cancel-btn">
                        Cancel
                    </button>
                    <button class="task-modal-btn task-modal-btn-primary" id="edit-save-btn">
                        ✏️ Edit Task
                    </button>
                </div>
            </div>
        `;

        this.modal.innerHTML = modalHTML;

        // Bind action buttons
        document.getElementById('modal-close-btn').addEventListener('click', () => this.close());
        document.getElementById('cancel-btn').addEventListener('click', () => {
            if (this.isEditMode) {
                this.cancelEdit();
            } else {
                this.close();
            }
        });
        document.getElementById('edit-save-btn').addEventListener('click', () => this.handleEditSave());
        document.getElementById('delete-task-btn').addEventListener('click', () => this.handleDelete());

        // Bind attachment upload
        document.getElementById('upload-attachment-btn').addEventListener('click', () => {
            document.getElementById('attachment-upload-input').click();
        });
        document.getElementById('attachment-upload-input').addEventListener('change', (e) => this.handleFileUpload(e));
    }

    renderAttachment(attachment) {
        const fileIcon = this.getFileIconSVG(attachment.file_type);
        const fileSize = this.formatFileSize(attachment.file_size);

        return `
            <div class="attachment-item" data-attachment-id="${attachment.id}">
                <div class="attachment-icon">${fileIcon}</div>
                <div class="attachment-info">
                    <div class="attachment-name">${this.escapeHtml(attachment.original_name)}</div>
                    <div class="attachment-meta">${fileSize} • ${this.formatDate(attachment.uploaded_at)}</div>
                </div>
                <div class="attachment-actions">
                    <a href="../uploads/attachments/${attachment.filename}" download="${attachment.original_name}" class="btn-attachment-download" title="Download">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                    </a>
                    <button class="btn-attachment-delete attachment-delete-only" onclick="taskModal.deleteAttachment(${attachment.id})" title="Delete" style="display: none;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;
    }

    getFileIconSVG(fileType) {
        const type = fileType?.toLowerCase();

        // PDF
        if (type === 'pdf') {
            return `<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <path d="M9 13h6"/>
                <path d="M9 17h6"/>
            </svg>`;
        }

        // Word Documents
        if (type === 'doc' || type === 'docx') {
            return `<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>`;
        }

        // Excel Spreadsheets
        if (type === 'xls' || type === 'xlsx') {
            return `<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="8" y1="13" x2="16" y2="13"/>
                <line x1="8" y1="17" x2="16" y2="17"/>
                <line x1="12" y1="11" x2="12" y2="19"/>
            </svg>`;
        }

        // Images
        if (type === 'jpg' || type === 'jpeg' || type === 'png' || type === 'gif') {
            return `<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
            </svg>`;
        }

        // ZIP Archives
        if (type === 'zip') {
            return `<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="12" y1="11" x2="12" y2="17"/>
                <line x1="9" y1="14" x2="15" y2="14"/>
            </svg>`;
        }

        // Text Files
        if (type === 'txt') {
            return `<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>`;
        }

        // Default Generic File
        return `<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
            <polyline points="13 2 13 9 20 9"/>
        </svg>`;
    }

    async handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Check file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB');
            event.target.value = '';
            return;
        }

        // Create form data
        const formData = new FormData();
        formData.append('file', file);
        formData.append('task_id', this.currentTaskId);

        // Show uploading state
        const uploadBtn = document.getElementById('upload-attachment-btn');
        const originalHTML = uploadBtn.innerHTML;
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<span>Uploading...</span>';

        try {
            const response = await fetch('../ajax/upload-attachment.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Reload task details to show new attachment
                await this.loadTaskDetails();
                event.target.value = ''; // Clear file input
            } else {
                alert(data.message || 'Failed to upload file');
            }
        } catch (error) {
            alert('An error occurred while uploading the file');
            console.error('Upload error:', error);
        } finally {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = originalHTML;
            event.target.value = '';
        }
    }

    async deleteAttachment(attachmentId) {
        if (!confirm('Are you sure you want to delete this attachment?')) {
            return;
        }

        try {
            const response = await fetch('../ajax/delete-attachment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    attachment_id: attachmentId
                })
            });

            const data = await response.json();

            if (data.success) {
                // Reload task details to update attachments list
                await this.loadTaskDetails();
            } else {
                alert(data.message || 'Failed to delete attachment');
            }
        } catch (error) {
            alert('An error occurred while deleting the attachment');
            console.error('Delete error:', error);
        }
    }

    handleEditSave() {
        if (!this.isEditMode) {
            // Switch to edit mode
            this.enterEditMode();
        } else {
            // Save changes
            this.saveChanges();
        }
    }

    enterEditMode() {
        this.isEditMode = true;
        const modalBody = document.getElementById('modal-body');
        const editSaveBtn = document.getElementById('edit-save-btn');

        modalBody.classList.add('edit-mode');
        editSaveBtn.innerHTML = '💾 Save Changes';

        // Show attachment delete buttons in edit mode
        document.querySelectorAll('.attachment-delete-only').forEach(btn => {
            btn.style.display = 'flex';
        });
    }

    cancelEdit() {
        // Hide attachment delete buttons when exiting edit mode
        document.querySelectorAll('.attachment-delete-only').forEach(btn => {
            btn.style.display = 'none';
        });

        // Reload task details to discard changes
        this.loadTaskDetails();
    }

    async saveChanges() {
        const editSaveBtn = document.getElementById('edit-save-btn');
        editSaveBtn.disabled = true;
        editSaveBtn.innerHTML = '⏳ Saving...';

        try {
            const formData = new FormData();
            formData.append('task_id', this.currentTaskId);
            formData.append('status', document.getElementById('edit-status').value);
            formData.append('priority', document.getElementById('edit-priority').value);
            formData.append('due_date', document.getElementById('edit-due-date').value);
            formData.append('assigned_to', document.getElementById('edit-assigned-to').value);
            formData.append('description', document.getElementById('edit-description').value);

            const response = await fetch('../ajax/update-task.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Close modal
                this.close();

                // Show success message
                if (typeof showToast === 'function') {
                    showToast('Task updated successfully!', 'success');
                }

                // Reload page to show changes after toast is visible
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                throw new Error(data.message || 'Failed to update task');
            }
        } catch (error) {
            // console.error('Error saving task:', error);
            if (typeof showToast === 'function') {
                showToast(error.message || 'Failed to save changes', 'error');
            } else {
                alert(error.message || 'Failed to save changes');
            }

            editSaveBtn.disabled = false;
            editSaveBtn.innerHTML = '💾 Save Changes';
        }
    }

    async handleDelete() {
        if (!confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
            return;
        }

        const deleteBtn = document.getElementById('delete-task-btn');
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

        try {
            const formData = new FormData();
            formData.append('task_id', this.currentTaskId);

            const response = await fetch('../ajax/delete-task.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Close modal
                this.close();

                // Show success message
                if (typeof showToast === 'function') {
                    showToast('Task deleted successfully!', 'success');
                }

                // Reload page to show changes after toast is visible
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                throw new Error(data.message || 'Failed to delete task');
            }
        } catch (error) {
            // console.error('Error deleting task:', error);
            if (typeof showToast === 'function') {
                showToast(error.message || 'Failed to delete task', 'error');
            } else {
                alert(error.message || 'Failed to delete task');
            }

            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '<i class="far fa-trash-alt"></i> Delete Task';
        }
    }

    showError(message) {
        this.modal.innerHTML = `
            <div class="task-modal-error">
                <div class="task-modal-error-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="task-modal-error-message">${this.escapeHtml(message)}</div>
                <button class="task-modal-btn task-modal-btn-primary" onclick="window.taskModal.close()">
                    Close
                </button>
            </div>
        `;
    }

    // Helper methods
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }

    getStatusClass(status) {
        const statusMap = {
            'todo': 'todo',
            'in_progress': 'in-progress',
            'completed': 'done'
        };
        return statusMap[status] || 'todo';
    }

    getPriorityClass(priority) {
        return priority.toLowerCase();
    }

    formatStatusDisplay(status) {
        const statusMap = {
            'todo': 'To Do',
            'in_progress': 'In Progress',
            'completed': 'Completed'
        };
        return statusMap[status] || status;
    }

    formatPriorityDisplay(priority) {
        return priority.charAt(0).toUpperCase() + priority.slice(1);
    }
}

// Initialize task modal when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.taskModal = new TaskModal();
});

// Export for use in other scripts
window.TaskModal = TaskModal;
