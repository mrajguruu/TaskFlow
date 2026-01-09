/**
 * Toast Notification System
 * Provides non-intrusive feedback to users
 */

(function(window) {
    'use strict';

    /**
     * Toast Configuration
     */
    const config = {
        duration: 7000, // Default duration in milliseconds (fallback only)
        maxToasts: 5,   // Maximum number of toasts to show at once
        icons: {
            success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>',
            error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
        }
    };

    /**
     * Toast Manager
     */
    class ToastManager {
        constructor() {
            this.container = null;
            this.toasts = [];
            this.init();
        }

        /**
         * Initialize toast container
         */
        init() {
            // Create container if it doesn't exist
            this.container = document.getElementById('toastContainer');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'toastContainer';
                this.container.className = 'toast-container';
                document.body.appendChild(this.container);
            }
        }

        /**
         * Load settings directly from localStorage
         */
        getSettings() {
            try {
                const stored = localStorage.getItem('taskflow_settings');
                if (stored) {
                    return JSON.parse(stored);
                }
            } catch (e) {
                // console.error('Error loading settings:', e);
            }
            return null;
        }

        /**
         * Check if toasts are enabled based on settings
         */
        isToastEnabled(type) {
            const settings = this.getSettings();

            if (!settings) return true; // Default to enabled if no settings

            // Check master toggle
            if (settings.toastEnabled === false) {
                return false;
            }

            // Check specific type
            if (type === 'success' && settings.toastSuccess === false) return false;
            if (type === 'error' && settings.toastError === false) return false;
            if (type === 'warning' && settings.toastWarning === false) return false;
            if (type === 'info' && settings.toastInfo === false) return false;

            return true;
        }

        /**
         * Show a toast
         */
        show(options) {
            // Validate options
            if (!options.message && !options.title) {
                return null;
            }

            const toastType = options.type || 'info';

            // Check if this type of toast is enabled
            if (!this.isToastEnabled(toastType)) {
                return null;
            }

            // Get duration from settings if not provided
            const settings = this.getSettings();
            const defaultDuration = settings?.toastDuration || config.duration;

            // Set defaults
            const toast = {
                id: this.generateId(),
                type: toastType,
                title: options.title || '',
                message: options.message || '',
                duration: options.duration !== undefined ? options.duration : defaultDuration,
                dismissible: options.dismissible !== false,
                showProgress: options.showProgress !== false
            };

            // Check max toasts limit
            if (this.toasts.length >= config.maxToasts) {
                this.remove(this.toasts[0].id);
            }

            // Create and add toast
            const element = this.createToastElement(toast);
            this.container.appendChild(element);
            this.toasts.push({ ...toast, element });

            // Auto dismiss if duration > 0
            if (toast.duration > 0) {
                this.startAutoDismiss(toast.id, toast.duration);
            }

            return toast.id;
        }

        /**
         * Create toast DOM element
         */
        createToastElement(toast) {
            const div = document.createElement('div');
            div.className = `toast toast-${toast.type}`;
            div.setAttribute('data-toast-id', toast.id);
            div.setAttribute('role', 'alert');
            div.setAttribute('aria-live', 'polite');

            // Build HTML
            let html = `
                <div class="toast-icon">${config.icons[toast.type]}</div>
                <div class="toast-content">
            `;

            if (toast.title) {
                html += `<div class="toast-title">${this.escapeHtml(toast.title)}</div>`;
            }

            if (toast.message) {
                html += `<div class="toast-message">${this.escapeHtml(toast.message)}</div>`;
            }

            html += `</div>`;

            if (toast.dismissible) {
                html += `
                    <button class="toast-close" aria-label="Close">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                `;
            }

            if (toast.showProgress && toast.duration > 0) {
                html += `
                    <div class="toast-progress">
                        <div class="toast-progress-bar" style="width: 100%"></div>
                    </div>
                `;
            }

            div.innerHTML = html;

            // Add click handler for close button
            if (toast.dismissible) {
                const closeBtn = div.querySelector('.toast-close');
                closeBtn.addEventListener('click', () => this.remove(toast.id));
            }

            return div;
        }

        /**
         * Start auto-dismiss timer with progress
         */
        startAutoDismiss(toastId, duration) {
            const toast = this.toasts.find(t => t.id === toastId);
            if (!toast) return;

            const progressBar = toast.element.querySelector('.toast-progress-bar');
            const startTime = Date.now();

            // Update progress bar
            const updateProgress = () => {
                const elapsed = Date.now() - startTime;
                const remaining = Math.max(0, duration - elapsed);
                const progress = (remaining / duration) * 100;

                if (progressBar) {
                    progressBar.style.width = progress + '%';
                }

                if (remaining > 0) {
                    requestAnimationFrame(updateProgress);
                } else {
                    this.remove(toastId);
                }
            };

            requestAnimationFrame(updateProgress);
        }

        /**
         * Remove a toast
         */
        remove(toastId) {
            const index = this.toasts.findIndex(t => t.id === toastId);
            if (index === -1) return;

            const toast = this.toasts[index];

            // Add hiding class for animation
            toast.element.classList.add('hiding');

            // Remove from DOM after animation
            setTimeout(() => {
                if (toast.element.parentNode) {
                    toast.element.parentNode.removeChild(toast.element);
                }
                this.toasts.splice(index, 1);
            }, 200);
        }

        /**
         * Remove all toasts
         */
        clear() {
            this.toasts.forEach(toast => {
                if (toast.element.parentNode) {
                    toast.element.parentNode.removeChild(toast.element);
                }
            });
            this.toasts = [];
        }

        /**
         * Generate unique ID
         */
        generateId() {
            return 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        }

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        /**
         * Convenience methods
         */
        success(message, title) {
            return this.show({ type: 'success', title: title || 'Success', message });
        }

        error(message, title) {
            return this.show({ type: 'error', title: title || 'Error', message });
        }

        warning(message, title) {
            return this.show({ type: 'warning', title: title || 'Warning', message });
        }

        info(message, title) {
            return this.show({ type: 'info', title: title || 'Info', message });
        }
    }

    // Create global instance
    const toastManager = new ToastManager();

    // Export to window
    window.Toast = {
        show: (options) => toastManager.show(options),
        success: (message, title) => toastManager.success(message, title),
        error: (message, title) => toastManager.error(message, title),
        warning: (message, title) => toastManager.warning(message, title),
        info: (message, title) => toastManager.info(message, title),
        remove: (id) => toastManager.remove(id),
        clear: () => toastManager.clear()
    };

    // Simplified showToast helper function for backward compatibility
    window.showToast = function(message, type, duration) {
        type = type || 'info';
        const options = {
            type: type,
            message: message
        };

        // Only set duration if explicitly provided
        if (duration !== undefined) {
            options.duration = duration;
        }

        return toastManager.show(options);
    };

})(window);
