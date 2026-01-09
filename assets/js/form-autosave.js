/**
 * Form Auto-Save System
 * Automatically saves form data to prevent data loss
 * Version: 1.0.0
 */

class FormAutoSave {
    constructor(formSelector, options = {}) {
        this.form = typeof formSelector === 'string'
            ? document.querySelector(formSelector)
            : formSelector;

        if (!this.form) {
            // console.error('FormAutoSave: Form not found');
            return;
        }

        this.options = {
            storagePrefix: 'taskflow_autosave_',
            saveDelay: 1000, // milliseconds
            excludeFields: ['password', 'confirm_password', 'current_password'], // Don't save passwords
            showIndicator: true,
            onSave: null,
            onRestore: null,
            clearOnSubmit: true,
            ...options
        };

        this.storageKey = this.options.storagePrefix + (this.form.id || this.form.name || 'form');
        this.saveTimer = null;
        this.fields = new Map();

        this.init();
    }

    init() {
        // Find all form fields
        const inputs = this.form.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), textarea, select');

        inputs.forEach(input => {
            const fieldName = input.name || input.id;

            // Skip excluded fields
            if (this.options.excludeFields.includes(fieldName) || this.options.excludeFields.includes(input.type)) {
                return;
            }

            this.fields.set(fieldName, input);

            // Add event listeners
            input.addEventListener('input', () => this.scheduleSave());
            input.addEventListener('change', () => this.scheduleSave());
        });

        // Create save indicator
        if (this.options.showIndicator) {
            this.createSaveIndicator();
        }

        // Restore saved data
        this.restore();

        // Clear on submit
        if (this.options.clearOnSubmit) {
            this.form.addEventListener('submit', () => {
                this.clear();
            });
        }

        // Warn before leaving with unsaved changes
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges()) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        });
    }

    createSaveIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'autosave-indicator';
        indicator.innerHTML = `
            <span class="autosave-icon"></span>
            <span class="autosave-text"></span>
        `;

        // Insert at the top of the form
        this.form.insertBefore(indicator, this.form.firstChild);

        this.indicator = indicator;
    }

    scheduleSave() {
        // Clear existing timer
        if (this.saveTimer) {
            clearTimeout(this.saveTimer);
        }

        // Show "Saving..." indicator
        this.updateIndicator('saving');

        // Schedule save
        this.saveTimer = setTimeout(() => {
            this.save();
        }, this.options.saveDelay);
    }

    save() {
        const formData = {};

        this.fields.forEach((input, fieldName) => {
            let value;

            if (input.type === 'checkbox') {
                value = input.checked;
            } else if (input.type === 'radio') {
                if (input.checked) {
                    value = input.value;
                }
            } else {
                value = input.value;
            }

            if (value !== undefined) {
                formData[fieldName] = value;
            }
        });

        try {
            const dataToSave = {
                data: formData,
                timestamp: Date.now(),
                url: window.location.pathname
            };

            localStorage.setItem(this.storageKey, JSON.stringify(dataToSave));

            this.updateIndicator('saved');

            // Callback
            if (typeof this.options.onSave === 'function') {
                this.options.onSave(formData);
            }

            return true;
        } catch (e) {
            // console.error('FormAutoSave: Failed to save data', e);
            this.updateIndicator('error');
            return false;
        }
    }

    restore() {
        try {
            const savedData = localStorage.getItem(this.storageKey);

            if (!savedData) {
                return false;
            }

            const { data, timestamp, url } = JSON.parse(savedData);

            // Check if saved data is from the same page
            if (url !== window.location.pathname) {
                return false;
            }

            // Check if data is not too old (24 hours)
            const age = Date.now() - timestamp;
            const maxAge = 24 * 60 * 60 * 1000; // 24 hours

            if (age > maxAge) {
                this.clear();
                return false;
            }

            // Restore values
            let restoredCount = 0;

            Object.entries(data).forEach(([fieldName, value]) => {
                const input = this.fields.get(fieldName);

                if (!input) return;

                if (input.type === 'checkbox') {
                    input.checked = value;
                } else if (input.type === 'radio') {
                    if (input.value === value) {
                        input.checked = true;
                    }
                } else {
                    input.value = value;
                }

                restoredCount++;
            });

            if (restoredCount > 0) {
                this.showRestoreNotification(timestamp);

                // Callback
                if (typeof this.options.onRestore === 'function') {
                    this.options.onRestore(data);
                }

                return true;
            }

            return false;
        } catch (e) {
            // console.error('FormAutoSave: Failed to restore data', e);
            return false;
        }
    }

    showRestoreNotification(timestamp) {
        const timeAgo = this.getTimeAgo(timestamp);

        // Show in indicator
        if (this.indicator) {
            this.updateIndicator('restored', `Draft restored (${timeAgo})`);

            setTimeout(() => {
                this.updateIndicator('idle');
            }, 5000);
        }

        // Show toast notification if available
        if (window.Toast) {
            Toast.info(`Draft restored from ${timeAgo}`, 'Auto-saved data recovered');
        }
    }

    updateIndicator(state, customMessage = null) {
        if (!this.indicator) return;

        const icon = this.indicator.querySelector('.autosave-icon');
        const text = this.indicator.querySelector('.autosave-text');

        this.indicator.className = 'autosave-indicator autosave-' + state;

        switch (state) {
            case 'saving':
                icon.textContent = '⟳';
                text.textContent = 'Saving draft...';
                break;
            case 'saved':
                icon.textContent = '✓';
                text.textContent = customMessage || 'Draft saved';
                setTimeout(() => this.updateIndicator('idle'), 3000);
                break;
            case 'restored':
                icon.textContent = '↻';
                text.textContent = customMessage || 'Draft restored';
                break;
            case 'error':
                icon.textContent = '✗';
                text.textContent = 'Failed to save draft';
                setTimeout(() => this.updateIndicator('idle'), 5000);
                break;
            case 'idle':
            default:
                icon.textContent = '';
                text.textContent = '';
                break;
        }
    }

    clear() {
        try {
            localStorage.removeItem(this.storageKey);
            this.updateIndicator('idle');
            return true;
        } catch (e) {
            // console.error('FormAutoSave: Failed to clear data', e);
            return false;
        }
    }

    hasUnsavedChanges() {
        try {
            const savedData = localStorage.getItem(this.storageKey);
            return savedData !== null;
        } catch (e) {
            return false;
        }
    }

    getTimeAgo(timestamp) {
        const seconds = Math.floor((Date.now() - timestamp) / 1000);

        if (seconds < 60) {
            return 'just now';
        }

        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) {
            return minutes === 1 ? '1 minute ago' : `${minutes} minutes ago`;
        }

        const hours = Math.floor(minutes / 60);
        if (hours < 24) {
            return hours === 1 ? '1 hour ago' : `${hours} hours ago`;
        }

        const days = Math.floor(hours / 24);
        return days === 1 ? '1 day ago' : `${days} days ago`;
    }

    // Public API
    forceSave() {
        if (this.saveTimer) {
            clearTimeout(this.saveTimer);
        }
        return this.save();
    }

    forceRestore() {
        return this.restore();
    }

    clearDraft() {
        return this.clear();
    }

    hasDraft() {
        return this.hasUnsavedChanges();
    }
}

// Auto-initialize forms with data-autosave attribute
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-autosave="true"]');
    forms.forEach(form => {
        new FormAutoSave(form);
    });
});

// Export for global use
window.FormAutoSave = FormAutoSave;
