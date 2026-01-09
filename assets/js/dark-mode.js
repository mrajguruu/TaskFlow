/**
 * Dark Mode System
 * Theme switcher with persistent storage
 * Version: 2.0.0 - Unified with Settings System
 */

class DarkMode {
    constructor(options = {}) {
        this.options = {
            storageKey: 'taskflow_settings', // Changed to use unified settings
            toggleButtonSelector: '[data-theme-toggle]',
            defaultTheme: 'light', // 'light', 'dark', or 'auto'
            enableTransitions: true,
            ...options
        };

        // Get theme from storage - don't fall back to default if inline script already applied theme
        const storedTheme = this.getStoredTheme();
        const alreadyApplied = document.documentElement.hasAttribute('data-theme');

        if (alreadyApplied && !storedTheme) {
            // Inline script applied theme but no stored theme - sync from DOM
            const dataTheme = document.documentElement.getAttribute('data-theme');
            this.theme = dataTheme === 'dark' ? 'dark' : 'light';
        } else {
            this.theme = storedTheme || this.options.defaultTheme;
        }

        this.init();
    }

    init() {
        // Check if theme is already applied by inline script in header
        const alreadyApplied = document.documentElement.hasAttribute('data-theme');

        // Apply initial theme only if not already applied to prevent flash
        if (!alreadyApplied) {
            this.applyTheme(this.theme, false);
        }

        // Setup toggle buttons
        this.setupToggleButtons();

        // Listen for system theme changes if in auto mode
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (this.theme === 'auto') {
                    this.applyTheme('auto', true);
                }
            });
        }

        // Listen for theme changes from settings page
        window.addEventListener('themechange', (e) => {
            if (e.detail && e.detail.theme) {
                this.theme = e.detail.theme;
                this.applyTheme(this.theme, true);

                // Update toggle buttons
                const toggleButtons = document.querySelectorAll(this.options.toggleButtonSelector);
                toggleButtons.forEach(button => this.updateToggleButton(button));
            }
        });
    }

    setupToggleButtons() {
        const toggleButtons = document.querySelectorAll(this.options.toggleButtonSelector);

        toggleButtons.forEach(button => {
            button.addEventListener('click', () => this.toggle());

            // Update button state
            this.updateToggleButton(button);
        });
    }

    toggle() {
        const newTheme = this.theme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);

        // Show toast notification if available
        if (window.Toast) {
            const message = newTheme === 'dark' ? 'Dark mode enabled' : 'Light mode enabled';
            Toast.info(message);
        }
    }

    setTheme(theme) {
        this.theme = theme;
        this.applyTheme(theme, true);
        this.storeTheme(theme);

        // Update all toggle buttons
        const toggleButtons = document.querySelectorAll(this.options.toggleButtonSelector);
        toggleButtons.forEach(button => this.updateToggleButton(button));

        // Update global settings if available
        if (window.updateTaskflowSetting) {
            window.updateTaskflowSetting('theme', theme);
        }
    }

    applyTheme(theme, withTransition = true) {
        const effectiveTheme = this.getEffectiveTheme(theme);

        // Add transition class temporarily
        if (withTransition && this.options.enableTransitions) {
            document.documentElement.classList.add('theme-transition');

            setTimeout(() => {
                document.documentElement.classList.remove('theme-transition');
            }, 300);
        }

        // Apply theme to document
        if (effectiveTheme === 'dark') {
            document.documentElement.classList.add('dark-mode');
            document.body.classList.add('dark-mode');
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark-mode');
            document.body.classList.remove('dark-mode');
            document.documentElement.setAttribute('data-theme', 'light');
        }

        // Update meta theme-color for mobile browsers
        this.updateMetaThemeColor(effectiveTheme);

        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('themechange', {
            detail: { theme: effectiveTheme }
        }));
    }

    getEffectiveTheme(theme) {
        if (theme === 'auto') {
            // Check system preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                return 'dark';
            }
            return 'light';
        }
        return theme;
    }

    updateToggleButton(button) {
        const effectiveTheme = this.getEffectiveTheme(this.theme);

        // Update icon if it exists
        const icon = button.querySelector('[data-theme-icon]');
        if (icon) {
            icon.textContent = effectiveTheme === 'dark' ? '☀️' : '🌙';
        }

        // Update text if it exists
        const text = button.querySelector('[data-theme-text]');
        if (text) {
            text.textContent = effectiveTheme === 'dark' ? 'Light Mode' : 'Dark Mode';
        }

        // Update aria-label
        button.setAttribute('aria-label', effectiveTheme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
    }

    updateMetaThemeColor(theme) {
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');

        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.name = 'theme-color';
            document.head.appendChild(metaThemeColor);
        }

        // Set appropriate color based on theme
        metaThemeColor.content = theme === 'dark' ? '#1f2937' : '#ffffff';
    }

    storeTheme(theme) {
        try {
            // Get current settings
            const settingsStr = localStorage.getItem('taskflow_settings');
            let settings = {};

            if (settingsStr) {
                settings = JSON.parse(settingsStr);
            }

            // Update theme in settings
            settings.theme = theme;

            // Save back to localStorage
            localStorage.setItem('taskflow_settings', JSON.stringify(settings));

            // Also update global settings object if available
            if (window.taskflowSettings) {
                window.taskflowSettings.theme = theme;
            }
        } catch (e) {
            // console.warn('Failed to store theme preference:', e);
        }
    }

    getStoredTheme() {
        try {
            // First try to get from unified settings
            const settingsStr = localStorage.getItem('taskflow_settings');
            if (settingsStr) {
                const settings = JSON.parse(settingsStr);
                if (settings.theme) {
                    return settings.theme;
                }
            }

            // Fallback to old storage key for backward compatibility
            const oldTheme = localStorage.getItem('taskflow_theme') || localStorage.getItem('theme');
            if (oldTheme) {
                // Migrate to new storage
                this.storeTheme(oldTheme);
                return oldTheme;
            }

            return null;
        } catch (e) {
            // console.warn('Failed to retrieve theme preference:', e);
            return null;
        }
    }

    // Public API
    getCurrentTheme() {
        return this.theme;
    }

    getEffectiveCurrentTheme() {
        return this.getEffectiveTheme(this.theme);
    }

    isDark() {
        return this.getEffectiveTheme(this.theme) === 'dark';
    }

    isLight() {
        return this.getEffectiveTheme(this.theme) === 'light';
    }
}

// Auto-initialize dark mode
document.addEventListener('DOMContentLoaded', function() {
    window.darkMode = new DarkMode();
});

// Export for global use
window.DarkMode = DarkMode;
