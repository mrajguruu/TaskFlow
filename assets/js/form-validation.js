/**
 * Form Validation System
 * Real-time inline validation with visual feedback
 * Version: 1.0.0
 */

class FormValidator {
    constructor(formSelector, options = {}) {
        this.form = typeof formSelector === 'string'
            ? document.querySelector(formSelector)
            : formSelector;

        if (!this.form) {
            // console.error('FormValidator: Form not found');
            return;
        }

        this.options = {
            validateOnBlur: true,
            validateOnInput: true,
            validateOnSubmit: true,
            showSuccessIcons: true,
            debounceDelay: 300,
            scrollToError: true,
            ...options
        };

        this.fields = new Map();
        this.debounceTimers = new Map();

        this.init();
    }

    init() {
        // Find all inputs, textareas, and selects
        const inputs = this.form.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), textarea, select');

        inputs.forEach(input => {
            this.setupField(input);
        });

        // Handle form submission
        if (this.options.validateOnSubmit) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    setupField(input) {
        const fieldData = {
            element: input,
            rules: this.extractRules(input),
            valid: null,
            touched: false
        };

        this.fields.set(input.name || input.id, fieldData);

        // Create validation message container if it doesn't exist
        this.createMessageContainer(input);

        // Event listeners
        if (this.options.validateOnBlur) {
            input.addEventListener('blur', () => this.validateField(input));
        }

        if (this.options.validateOnInput) {
            input.addEventListener('input', () => this.debouncedValidate(input));
        }

        // Special handling for checkboxes and radios
        if (input.type === 'checkbox' || input.type === 'radio') {
            input.addEventListener('change', () => this.validateField(input));
        }
    }

    extractRules(input) {
        const rules = {};

        // Required
        if (input.hasAttribute('required')) {
            rules.required = true;
        }

        // Type-based rules
        if (input.type === 'email') {
            rules.email = true;
        }

        if (input.type === 'url') {
            rules.url = true;
        }

        if (input.type === 'number') {
            if (input.hasAttribute('min')) rules.min = parseFloat(input.getAttribute('min'));
            if (input.hasAttribute('max')) rules.max = parseFloat(input.getAttribute('max'));
        }

        // Length rules
        if (input.hasAttribute('minlength')) {
            rules.minlength = parseInt(input.getAttribute('minlength'));
        }

        if (input.hasAttribute('maxlength')) {
            rules.maxlength = parseInt(input.getAttribute('maxlength'));
        }

        // Pattern
        if (input.hasAttribute('pattern')) {
            rules.pattern = new RegExp(input.getAttribute('pattern'));
        }

        // Custom validation attributes
        if (input.hasAttribute('data-match')) {
            rules.match = input.getAttribute('data-match');
        }

        if (input.hasAttribute('data-validate')) {
            rules.custom = input.getAttribute('data-validate');
        }

        return rules;
    }

    createMessageContainer(input) {
        const formGroup = input.closest('.form-group');
        if (!formGroup) return;

        let messageContainer = formGroup.querySelector('.validation-message');
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.className = 'validation-message';

            // Insert after input or after input wrapper
            const wrapper = input.closest('.form-group-with-icon') || input;
            wrapper.parentNode.insertBefore(messageContainer, wrapper.nextSibling);
        }
    }

    debouncedValidate(input) {
        const key = input.name || input.id;

        // Clear existing timer
        if (this.debounceTimers.has(key)) {
            clearTimeout(this.debounceTimers.get(key));
        }

        // Set new timer
        const timer = setTimeout(() => {
            this.validateField(input);
        }, this.options.debounceDelay);

        this.debounceTimers.set(key, timer);
    }

    validateField(input, showMessage = true) {
        const fieldData = this.fields.get(input.name || input.id);
        if (!fieldData) return true;

        fieldData.touched = true;
        const value = input.value.trim();
        const rules = fieldData.rules;
        let isValid = true;
        let message = '';

        // Required validation
        if (rules.required) {
            if (input.type === 'checkbox' || input.type === 'radio') {
                if (!input.checked) {
                    isValid = false;
                    message = 'This field is required';
                }
            } else if (!value) {
                isValid = false;
                message = 'This field is required';
            }
        }

        // Skip other validations if empty and not required
        if (!value && !rules.required) {
            fieldData.valid = true;
            if (showMessage) this.updateFieldUI(input, true, '');
            return true;
        }

        // Email validation
        if (isValid && rules.email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                message = 'Please enter a valid email address';
            }
        }

        // URL validation
        if (isValid && rules.url) {
            try {
                new URL(value);
            } catch {
                isValid = false;
                message = 'Please enter a valid URL';
            }
        }

        // Length validation
        if (isValid && rules.minlength && value.length < rules.minlength) {
            isValid = false;
            message = `Must be at least ${rules.minlength} characters`;
        }

        if (isValid && rules.maxlength && value.length > rules.maxlength) {
            isValid = false;
            message = `Must not exceed ${rules.maxlength} characters`;
        }

        // Number range validation
        if (isValid && rules.min !== undefined && parseFloat(value) < rules.min) {
            isValid = false;
            message = `Must be at least ${rules.min}`;
        }

        if (isValid && rules.max !== undefined && parseFloat(value) > rules.max) {
            isValid = false;
            message = `Must not exceed ${rules.max}`;
        }

        // Pattern validation
        if (isValid && rules.pattern && !rules.pattern.test(value)) {
            isValid = false;
            message = input.getAttribute('data-pattern-message') || 'Invalid format';
        }

        // Match validation (for confirm password, etc.)
        if (isValid && rules.match) {
            const matchField = this.form.querySelector(`[name="${rules.match}"], #${rules.match}`);
            if (matchField && value !== matchField.value.trim()) {
                isValid = false;
                message = 'Fields do not match';
            }
        }

        // Custom validation
        if (isValid && rules.custom) {
            const customValidator = window[rules.custom];
            if (typeof customValidator === 'function') {
                const result = customValidator(value, input);
                if (result !== true) {
                    isValid = false;
                    message = typeof result === 'string' ? result : 'Invalid value';
                }
            }
        }

        fieldData.valid = isValid;

        if (showMessage) {
            this.updateFieldUI(input, isValid, message);
        }

        return isValid;
    }

    updateFieldUI(input, isValid, message) {
        const formGroup = input.closest('.form-group');
        if (!formGroup) return;

        const messageContainer = formGroup.querySelector('.validation-message');

        // Update input classes
        input.classList.remove('is-valid', 'is-invalid');

        if (isValid) {
            input.classList.add('is-valid');
            if (messageContainer) {
                messageContainer.textContent = '';
                messageContainer.classList.remove('show', 'error');
            }
        } else {
            input.classList.add('is-invalid');
            if (messageContainer) {
                messageContainer.textContent = message;
                messageContainer.classList.add('show', 'error');
            }
        }

        // Update form group classes
        formGroup.classList.remove('has-success', 'has-error');
        if (isValid && this.options.showSuccessIcons) {
            formGroup.classList.add('has-success');
        } else if (!isValid) {
            formGroup.classList.add('has-error');
        }
    }

    handleSubmit(e) {
        let isFormValid = true;
        let firstInvalidField = null;

        // Validate all fields
        this.fields.forEach((fieldData, key) => {
            const isValid = this.validateField(fieldData.element, true);
            if (!isValid) {
                isFormValid = false;
                if (!firstInvalidField) {
                    firstInvalidField = fieldData.element;
                }
            }
        });

        if (!isFormValid) {
            e.preventDefault();

            // Scroll to first error
            if (this.options.scrollToError && firstInvalidField) {
                firstInvalidField.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                firstInvalidField.focus();
            }

            // Show toast notification if available
            if (window.Toast) {
                Toast.error('Please fix the errors in the form before submitting');
            }
        }

        return isFormValid;
    }

    // Public API
    validate() {
        let isFormValid = true;

        this.fields.forEach((fieldData) => {
            const isValid = this.validateField(fieldData.element, true);
            if (!isValid) {
                isFormValid = false;
            }
        });

        return isFormValid;
    }

    reset() {
        this.fields.forEach((fieldData) => {
            fieldData.valid = null;
            fieldData.touched = false;
            fieldData.element.classList.remove('is-valid', 'is-invalid');

            const formGroup = fieldData.element.closest('.form-group');
            if (formGroup) {
                formGroup.classList.remove('has-success', 'has-error');
                const messageContainer = formGroup.querySelector('.validation-message');
                if (messageContainer) {
                    messageContainer.textContent = '';
                    messageContainer.classList.remove('show', 'error');
                }
            }
        });
    }

    addCustomRule(fieldName, validator, message) {
        const fieldData = this.fields.get(fieldName);
        if (fieldData) {
            if (!fieldData.rules.customRules) {
                fieldData.rules.customRules = [];
            }
            fieldData.rules.customRules.push({ validator, message });
        }
    }
}

// Helper validators that can be used globally
const Validators = {
    username(value) {
        const usernameRegex = /^[a-zA-Z0-9_]{4,20}$/;
        if (!usernameRegex.test(value)) {
            return '4-20 characters, letters, numbers, and underscores only';
        }
        return true;
    },

    password(value) {
        if (value.length < 8) {
            return 'Password must be at least 8 characters';
        }
        if (!/[A-Z]/.test(value)) {
            return 'Password must contain an uppercase letter';
        }
        if (!/[a-z]/.test(value)) {
            return 'Password must contain a lowercase letter';
        }
        if (!/[0-9]/.test(value)) {
            return 'Password must contain a number';
        }
        return true;
    },

    phone(value) {
        const phoneRegex = /^[\d\s\-\+\(\)]+$/;
        if (!phoneRegex.test(value) || value.replace(/\D/g, '').length < 10) {
            return 'Please enter a valid phone number';
        }
        return true;
    },

    zipCode(value) {
        const zipRegex = /^\d{5}(-\d{4})?$/;
        if (!zipRegex.test(value)) {
            return 'Please enter a valid ZIP code';
        }
        return true;
    }
};

// Auto-initialize forms with data-validate attribute
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach(form => {
        new FormValidator(form);
    });
});

// Export for global use
window.FormValidator = FormValidator;
window.Validators = Validators;
