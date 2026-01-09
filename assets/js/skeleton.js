/**
 * Skeleton Loading Helper
 * Utilities for showing skeleton loading states
 */

(function(window) {
    'use strict';

    /**
     * Skeleton Templates
     */
    const templates = {
        /**
         * Project Card Skeleton
         */
        projectCard() {
            return `
                <div class="skeleton-project-card">
                    <div class="skeleton-project-header">
                        <div class="skeleton-project-title">
                            <div class="skeleton skeleton-title"></div>
                        </div>
                        <div class="skeleton skeleton-badge"></div>
                    </div>
                    <div class="skeleton-project-description">
                        <div class="skeleton skeleton-text skeleton-text-long"></div>
                        <div class="skeleton skeleton-text skeleton-text-medium"></div>
                    </div>
                    <div class="skeleton-project-stats">
                        <div class="skeleton-project-stat">
                            <div class="skeleton skeleton-text-short"></div>
                            <div class="skeleton skeleton-text-short"></div>
                        </div>
                        <div class="skeleton-project-stat">
                            <div class="skeleton skeleton-text-short"></div>
                            <div class="skeleton skeleton-text-short"></div>
                        </div>
                        <div class="skeleton-project-stat">
                            <div class="skeleton skeleton-text-short"></div>
                            <div class="skeleton skeleton-text-short"></div>
                        </div>
                    </div>
                    <div class="skeleton-project-footer">
                        <div class="skeleton-project-avatars">
                            <div class="skeleton skeleton-avatar-sm"></div>
                            <div class="skeleton skeleton-avatar-sm"></div>
                            <div class="skeleton skeleton-avatar-sm"></div>
                        </div>
                        <div class="skeleton skeleton-button"></div>
                    </div>
                </div>
            `;
        },

        /**
         * Stat Card Skeleton
         */
        statCard() {
            return `
                <div class="skeleton-stat-card">
                    <div class="skeleton-stat-header">
                        <div class="skeleton skeleton-stat-label"></div>
                        <div class="skeleton skeleton-icon"></div>
                    </div>
                    <div class="skeleton skeleton-stat-value"></div>
                    <div class="skeleton skeleton-stat-change"></div>
                </div>
            `;
        },

        /**
         * Team Member Card Skeleton
         */
        memberCard() {
            return `
                <div class="skeleton-member-card">
                    <div class="skeleton-member-header">
                        <div class="skeleton skeleton-avatar-md"></div>
                        <div class="skeleton-member-info">
                            <div class="skeleton skeleton-member-name"></div>
                            <div class="skeleton skeleton-member-email"></div>
                        </div>
                        <div class="skeleton skeleton-badge"></div>
                    </div>
                    <div class="skeleton-member-stats">
                        <div class="skeleton-member-stat">
                            <div class="skeleton skeleton-text-short"></div>
                            <div class="skeleton skeleton-text-short"></div>
                        </div>
                        <div class="skeleton-member-stat">
                            <div class="skeleton skeleton-text-short"></div>
                            <div class="skeleton skeleton-text-short"></div>
                        </div>
                    </div>
                </div>
            `;
        },

        /**
         * Task Card Skeleton
         */
        taskCard() {
            return `
                <div class="skeleton-task-card">
                    <div class="skeleton-task-header">
                        <div class="skeleton skeleton-task-title"></div>
                        <div class="skeleton skeleton-badge"></div>
                    </div>
                    <div class="skeleton-task-description">
                        <div class="skeleton skeleton-text skeleton-text-long"></div>
                        <div class="skeleton skeleton-text skeleton-text-medium"></div>
                    </div>
                    <div class="skeleton-task-footer">
                        <div class="skeleton skeleton-avatar-sm"></div>
                        <div class="skeleton skeleton-text-short"></div>
                        <div class="skeleton skeleton-badge"></div>
                    </div>
                </div>
            `;
        },

        /**
         * Form Skeleton
         */
        form(fields = 3) {
            let html = '<div class="skeleton-form">';
            for (let i = 0; i < fields; i++) {
                html += `
                    <div class="skeleton-form-group">
                        <div class="skeleton skeleton-form-label"></div>
                        <div class="skeleton skeleton-input"></div>
                    </div>
                `;
            }
            html += `
                <div class="skeleton-form-buttons">
                    <div class="skeleton skeleton-button"></div>
                    <div class="skeleton skeleton-button"></div>
                </div>
            </div>
            `;
            return html;
        },

        /**
         * Table Row Skeleton
         */
        tableRow(columns = 4) {
            let html = '<tr class="skeleton-table-row">';
            for (let i = 0; i < columns; i++) {
                html += `
                    <td class="skeleton-table-cell">
                        <div class="skeleton skeleton-table-cell-content"></div>
                    </td>
                `;
            }
            html += '</tr>';
            return html;
        }
    };

    /**
     * Skeleton Manager
     */
    class SkeletonManager {
        /**
         * Show skeleton in a container
         */
        show(container, template, count = 1) {
            if (typeof container === 'string') {
                container = document.querySelector(container);
            }

            if (!container) {
                // console.error('Skeleton container not found');
                return;
            }

            // Get template function
            const templateFn = typeof template === 'function' ? template : templates[template];
            if (!templateFn) {
                // console.error('Skeleton template not found:', template);
                return;
            }

            // Generate skeleton HTML
            let html = '';
            for (let i = 0; i < count; i++) {
                html += templateFn();
            }

            // Store original content
            container.setAttribute('data-skeleton-original', container.innerHTML);
            container.classList.add('loading');

            // Replace with skeleton
            container.innerHTML = html;
        }

        /**
         * Hide skeleton and restore content
         */
        hide(container, newContent = null) {
            if (typeof container === 'string') {
                container = document.querySelector(container);
            }

            if (!container) {
                // console.error('Skeleton container not found');
                return;
            }

            container.classList.remove('loading');

            if (newContent) {
                container.innerHTML = newContent;
            } else {
                const original = container.getAttribute('data-skeleton-original');
                if (original) {
                    container.innerHTML = original;
                    container.removeAttribute('data-skeleton-original');
                }
            }
        }

        /**
         * Get template HTML
         */
        getTemplate(template, count = 1) {
            const templateFn = typeof template === 'function' ? template : templates[template];
            if (!templateFn) {
                // console.error('Skeleton template not found:', template);
                return '';
            }

            let html = '';
            for (let i = 0; i < count; i++) {
                html += templateFn();
            }
            return html;
        }

        /**
         * Simulate loading with skeleton
         */
        async simulate(container, template, loadFn, minDuration = 500) {
            this.show(container, template);

            const startTime = Date.now();
            let content;

            try {
                content = await loadFn();
            } catch (error) {
                // console.error('Loading error:', error);
                this.hide(container);
                throw error;
            }

            // Ensure minimum duration for better UX
            const elapsed = Date.now() - startTime;
            if (elapsed < minDuration) {
                await new Promise(resolve => setTimeout(resolve, minDuration - elapsed));
            }

            this.hide(container, content);
            return content;
        }
    }

    // Create global instance
    const skeletonManager = new SkeletonManager();

    // Export to window
    window.Skeleton = {
        show: (container, template, count) => skeletonManager.show(container, template, count),
        hide: (container, newContent) => skeletonManager.hide(container, newContent),
        getTemplate: (template, count) => skeletonManager.getTemplate(template, count),
        simulate: (container, template, loadFn, minDuration) =>
            skeletonManager.simulate(container, template, loadFn, minDuration),
        templates: templates
    };

})(window);
