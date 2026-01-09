/**
 * Password Strength Meter
 * Enhanced password validation with visual feedback
 * Version: 1.0.0
 */

class PasswordStrength {
    constructor(inputSelector, options = {}) {
        this.input = typeof inputSelector === 'string'
            ? document.querySelector(inputSelector)
            : inputSelector;

        if (!this.input) {
            // console.error('PasswordStrength: Input not found');
            return;
        }

        this.options = {
            showStrengthBar: true,
            showChecklist: true,
            showLabel: true,
            minLength: 8,
            requireUppercase: true,
            requireLowercase: true,
            requireNumbers: true,
            requireSpecialChars: false,
            containerClass: 'password-strength-container',
            ...options
        };

        this.init();
    }

    init() {
        this.createUI();
        this.attachEvents();
    }

    createUI() {
        const formGroup = this.input.closest('.form-group');
        if (!formGroup) return;

        // Create container
        const container = document.createElement('div');
        container.className = this.options.containerClass;

        // Create strength bar
        if (this.options.showStrengthBar) {
            const strengthBar = document.createElement('div');
            strengthBar.className = 'password-strength';
            strengthBar.innerHTML = '<div class="password-strength-bar" data-strength-bar></div>';
            container.appendChild(strengthBar);

            if (this.options.showLabel) {
                const label = document.createElement('div');
                label.className = 'password-strength-label';
                label.setAttribute('data-strength-label', '');
                container.appendChild(label);
            }
        }

        // Create checklist
        if (this.options.showChecklist) {
            const checklist = document.createElement('div');
            checklist.className = 'validation-checklist';
            checklist.setAttribute('data-checklist', '');

            const checks = [];

            checks.push({
                key: 'length',
                label: `At least ${this.options.minLength} characters`
            });

            if (this.options.requireUppercase) {
                checks.push({
                    key: 'uppercase',
                    label: 'One uppercase letter (A-Z)'
                });
            }

            if (this.options.requireLowercase) {
                checks.push({
                    key: 'lowercase',
                    label: 'One lowercase letter (a-z)'
                });
            }

            if (this.options.requireNumbers) {
                checks.push({
                    key: 'number',
                    label: 'One number (0-9)'
                });
            }

            if (this.options.requireSpecialChars) {
                checks.push({
                    key: 'special',
                    label: 'One special character (!@#$%^&*)'
                });
            }

            checks.forEach(check => {
                const item = document.createElement('div');
                item.className = 'validation-checklist-item';
                item.setAttribute('data-check', check.key);
                item.textContent = check.label;
                checklist.appendChild(item);
            });

            container.appendChild(checklist);
        }

        // Insert after input or password toggle wrapper
        const insertAfter = this.input.closest('.form-group-with-icon') || this.input;
        insertAfter.parentNode.insertBefore(container, insertAfter.nextSibling);

        this.container = container;
    }

    attachEvents() {
        this.input.addEventListener('input', () => this.check());
        this.input.addEventListener('focus', () => this.showUI());
        this.input.addEventListener('blur', () => this.hideUI());
    }

    showUI() {
        if (this.container) {
            this.container.style.display = 'block';
        }
    }

    hideUI() {
        // Keep visible if there's a value
        if (!this.input.value && this.container) {
            this.container.style.display = 'none';
        }
    }

    check() {
        const password = this.input.value;
        const result = this.calculateStrength(password);

        this.updateUI(result);

        return result;
    }

    calculateStrength(password) {
        const checks = {
            length: password.length >= this.options.minLength,
            uppercase: this.options.requireUppercase ? /[A-Z]/.test(password) : true,
            lowercase: this.options.requireLowercase ? /[a-z]/.test(password) : true,
            number: this.options.requireNumbers ? /[0-9]/.test(password) : true,
            special: this.options.requireSpecialChars ? /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password) : true
        };

        const passedChecks = Object.values(checks).filter(v => v === true).length;
        const totalChecks = Object.keys(checks).length;

        // Calculate strength score (0-4)
        let score = 0;

        if (checks.length) score++;
        if (checks.uppercase) score++;
        if (checks.lowercase) score++;
        if (checks.number) score++;
        if (checks.special) score++;

        // Additional scoring for length
        if (password.length >= 12) score += 0.5;
        if (password.length >= 16) score += 0.5;

        // Deduct points for common patterns
        if (this.hasCommonPatterns(password)) {
            score -= 1;
        }

        // Normalize score to 0-4
        score = Math.max(0, Math.min(4, score));

        // Determine strength level
        let strength = 'weak';
        let strengthText = '';
        let percentage = 0;

        if (password.length === 0) {
            strength = 'none';
            strengthText = '';
            percentage = 0;
        } else if (score < 2) {
            strength = 'weak';
            strengthText = 'Weak';
            percentage = 25;
        } else if (score < 3) {
            strength = 'fair';
            strengthText = 'Fair';
            percentage = 50;
        } else if (score < 4) {
            strength = 'good';
            strengthText = 'Good';
            percentage = 75;
        } else {
            strength = 'strong';
            strengthText = 'Strong';
            percentage = 100;
        }

        const allChecksPassed = passedChecks === totalChecks;

        return {
            score,
            strength,
            strengthText,
            percentage,
            checks,
            allChecksPassed,
            isValid: password.length > 0 && allChecksPassed
        };
    }

    hasCommonPatterns(password) {
        const commonPatterns = [
            /^123456/,
            /^password/i,
            /^qwerty/i,
            /^abc123/i,
            /^(.)\1{3,}/, // Repeated characters
            /^12345678/,
            /^1q2w3e4r/i
        ];

        return commonPatterns.some(pattern => pattern.test(password));
    }

    updateUI(result) {
        if (!this.container) return;

        // Update strength bar
        const bar = this.container.querySelector('[data-strength-bar]');
        if (bar) {
            bar.className = 'password-strength-bar';
            bar.style.width = result.percentage + '%';

            if (result.strength === 'weak') {
                bar.classList.add('weak');
            } else if (result.strength === 'fair') {
                bar.classList.add('weak');
            } else if (result.strength === 'good') {
                bar.classList.add('medium');
            } else if (result.strength === 'strong') {
                bar.classList.add('strong');
            }
        }

        // Update label
        const label = this.container.querySelector('[data-strength-label]');
        if (label) {
            label.textContent = result.strengthText;
            label.className = 'password-strength-label';

            if (result.strength === 'weak' || result.strength === 'fair') {
                label.classList.add('weak');
            } else if (result.strength === 'good') {
                label.classList.add('medium');
            } else if (result.strength === 'strong') {
                label.classList.add('strong');
            }
        }

        // Update checklist
        const checklist = this.container.querySelector('[data-checklist]');
        if (checklist) {
            Object.entries(result.checks).forEach(([key, passed]) => {
                const item = checklist.querySelector(`[data-check="${key}"]`);
                if (item) {
                    item.classList.remove('valid', 'invalid');
                    if (this.input.value.length > 0) {
                        item.classList.add(passed ? 'valid' : 'invalid');
                    }
                }
            });
        }
    }

    // Public API
    getStrength() {
        return this.check();
    }

    isValid() {
        const result = this.check();
        return result.isValid;
    }

    reset() {
        this.input.value = '';
        this.updateUI({
            score: 0,
            strength: 'none',
            strengthText: '',
            percentage: 0,
            checks: {},
            allChecksPassed: false,
            isValid: false
        });
    }
}

// Password generator utility
const PasswordGenerator = {
    generate(length = 16, options = {}) {
        const defaults = {
            lowercase: true,
            uppercase: true,
            numbers: true,
            special: true,
            excludeSimilar: true, // Exclude similar characters like i, l, 1, L, o, 0, O
            excludeAmbiguous: true // Exclude ambiguous characters like {, }, [, ], (, ), /, \, ', ", `, ~, ,, ;, :, ., <, >
        };

        const settings = { ...defaults, ...options };

        let charset = '';
        let password = '';

        // Build character set
        if (settings.lowercase) {
            charset += settings.excludeSimilar ? 'abcdefghjkmnpqrstuvwxyz' : 'abcdefghijklmnopqrstuvwxyz';
        }

        if (settings.uppercase) {
            charset += settings.excludeSimilar ? 'ABCDEFGHJKMNPQRSTUVWXYZ' : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if (settings.numbers) {
            charset += settings.excludeSimilar ? '23456789' : '0123456789';
        }

        if (settings.special) {
            const specialChars = settings.excludeAmbiguous
                ? '!@#$%^&*_+-='
                : '!@#$%^&*()_+-=[]{}|;:,.<>?';
            charset += specialChars;
        }

        if (charset.length === 0) {
            charset = 'abcdefghijklmnopqrstuvwxyz0123456789';
        }

        // Generate password
        for (let i = 0; i < length; i++) {
            const randomIndex = Math.floor(Math.random() * charset.length);
            password += charset[randomIndex];
        }

        return password;
    },

    copyToClipboard(text) {
        if (navigator.clipboard) {
            return navigator.clipboard.writeText(text).then(() => {
                if (window.Toast) {
                    Toast.success('Password copied to clipboard!');
                }
                return true;
            }).catch(() => {
                return this.fallbackCopy(text);
            });
        } else {
            return this.fallbackCopy(text);
        }
    },

    fallbackCopy(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            document.execCommand('copy');
            document.body.removeChild(textArea);
            if (window.Toast) {
                Toast.success('Password copied to clipboard!');
            }
            return Promise.resolve(true);
        } catch (err) {
            document.body.removeChild(textArea);
            if (window.Toast) {
                Toast.error('Failed to copy password');
            }
            return Promise.resolve(false);
        }
    }
};

// Auto-initialize password strength meters
document.addEventListener('DOMContentLoaded', function() {
    const passwordInputs = document.querySelectorAll('input[type="password"][data-strength="true"]');
    passwordInputs.forEach(input => {
        new PasswordStrength(input);
    });
});

// Export for global use
window.PasswordStrength = PasswordStrength;
window.PasswordGenerator = PasswordGenerator;
