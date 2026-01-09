/**
 * Settings Page JavaScript
 * Handle settings navigation and interactions with full functionality
 */

// Settings storage key
const SETTINGS_KEY = 'taskflow_settings';

// Default settings
const defaultSettings = {
    theme: 'light',
    compactMode: false,
    smoothAnimations: true,
    toastEnabled: true,
    toastSuccess: true,
    toastError: true,
    toastWarning: true,
    toastInfo: true,
    toastDuration: 3000,
    emailTaskAssignments: true,
    emailProjectUpdates: true,
    emailCommentsMentions: true,
    emailDueDateReminders: true,
    browserNotifications: false,
    notificationSound: false,
    profileVisibility: 'team',
    showOnlineStatus: true,
    activityTracking: true,
    language: 'en',
    timezone: 'America/New_York',
    dateFormat: 'MM/DD/YYYY',
    timeFormat: '12'
};

// Load settings from localStorage
function loadSettings() {
    const stored = localStorage.getItem(SETTINGS_KEY);
    if (stored) {
        try {
            return { ...defaultSettings, ...JSON.parse(stored) };
        } catch (e) {
            // console.error('Error loading settings:', e);
            return defaultSettings;
        }
    }
    return defaultSettings;
}

// Save settings to localStorage
function saveSettings(settings) {
    try {
        localStorage.setItem(SETTINGS_KEY, JSON.stringify(settings));
        return true;
    } catch (e) {
        // console.error('Error saving settings:', e);
        return false;
    }
}

// Current settings
let currentSettings = loadSettings();

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initSettingsNavigation();
    initThemeSelector();
    initToastSettings();
    initToastTester();
    initNotificationSettings();
    initPrivacySettings();
    initLocalizationSettings();
    initDisplaySettings();
    initSaveButton();

    // Apply current settings
    applyAllSettings();

    // Handle URL hash
    handleSettingsHash();
});

/**
 * Initialize settings sidebar navigation
 */
function initSettingsNavigation() {
    const navItems = document.querySelectorAll('.settings-nav-item');
    const sections = document.querySelectorAll('.settings-section');

    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            const sectionId = this.getAttribute('data-section');

            // Update active nav item
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');

            // Update active section
            sections.forEach(section => section.classList.remove('active'));
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.add('active');
            }

            // Update URL hash
            window.location.hash = sectionId;

            // Scroll to top of settings content
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
}

/**
 * Initialize theme selector
 */
function initThemeSelector() {
    const themeCards = document.querySelectorAll('.theme-card');

    themeCards.forEach(card => {
        card.addEventListener('click', function() {
            const theme = this.getAttribute('data-theme');

            // Update active state
            themeCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');

            // Update settings
            currentSettings.theme = theme;

            // Save theme immediately to localStorage (don't wait for Save button)
            saveSettings(currentSettings);

            // Apply theme immediately
            applyTheme(theme);
        });
    });

    // Set initial active state
    const activeTheme = currentSettings.theme || 'light';
    const activeCard = document.querySelector(`.theme-card[data-theme="${activeTheme}"]`);
    if (activeCard) {
        activeCard.classList.add('active');
    }
}

/**
 * Apply theme
 */
function applyTheme(theme) {
    if (theme === 'dark') {
        document.documentElement.classList.add('dark-mode');
        document.body.classList.add('dark-mode');
        document.documentElement.setAttribute('data-theme', 'dark');
    } else if (theme === 'light') {
        document.documentElement.classList.remove('dark-mode');
        document.body.classList.remove('dark-mode');
        document.documentElement.setAttribute('data-theme', 'light');
    } else if (theme === 'auto') {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (prefersDark) {
            document.documentElement.classList.add('dark-mode');
            document.body.classList.add('dark-mode');
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark-mode');
            document.body.classList.remove('dark-mode');
            document.documentElement.setAttribute('data-theme', 'light');
        }
    }

    // Dispatch event for other scripts (like dark-mode.js)
    window.dispatchEvent(new CustomEvent('themechange', {
        detail: { theme: theme }
    }));
}

/**
 * Initialize toast settings
 */
function initToastSettings() {
    // Toast enabled
    const toastEnabled = document.getElementById('toast-enabled');
    if (toastEnabled) {
        toastEnabled.checked = currentSettings.toastEnabled;
        toastEnabled.addEventListener('change', function() {
            currentSettings.toastEnabled = this.checked;
        });
    }

    // Success toasts
    const toastSuccess = document.getElementById('toast-success');
    if (toastSuccess) {
        toastSuccess.checked = currentSettings.toastSuccess;
        toastSuccess.addEventListener('change', function() {
            currentSettings.toastSuccess = this.checked;
        });
    }

    // Error toasts
    const toastError = document.getElementById('toast-error');
    if (toastError) {
        toastError.checked = currentSettings.toastError;
        toastError.addEventListener('change', function() {
            currentSettings.toastError = this.checked;
        });
    }

    // Warning toasts
    const toastWarning = document.getElementById('toast-warning');
    if (toastWarning) {
        toastWarning.checked = currentSettings.toastWarning;
        toastWarning.addEventListener('change', function() {
            currentSettings.toastWarning = this.checked;
        });
    }

    // Info toasts
    const toastInfo = document.getElementById('toast-info');
    if (toastInfo) {
        toastInfo.checked = currentSettings.toastInfo;
        toastInfo.addEventListener('change', function() {
            currentSettings.toastInfo = this.checked;
        });
    }

    // Toast duration
    const toastDuration = document.getElementById('toast-duration');
    if (toastDuration) {
        toastDuration.value = currentSettings.toastDuration;
        toastDuration.addEventListener('change', function() {
            currentSettings.toastDuration = parseInt(this.value);
        });
    }
}

/**
 * Initialize toast tester
 */
function initToastTester() {
    const testButton = document.getElementById('test-toast-btn');

    if (testButton && typeof showToast === 'function') {
        testButton.addEventListener('click', function() {
            if (currentSettings.toastEnabled && currentSettings.toastSuccess) {
                showToast('Settings updated successfully!', 'success', currentSettings.toastDuration);
            } else if (currentSettings.toastEnabled) {
                showToast('Toast notifications are partially disabled', 'info', currentSettings.toastDuration);
            } else {
                alert('Toast notifications are disabled. Enable them to see the preview.');
            }
        });
    }
}

/**
 * Initialize notification settings
 */
function initNotificationSettings() {
    // Get all notification checkboxes
    const checkboxes = {
        emailTaskAssignments: currentSettings.emailTaskAssignments,
        emailProjectUpdates: currentSettings.emailProjectUpdates,
        emailCommentsMentions: currentSettings.emailCommentsMentions,
        emailDueDateReminders: currentSettings.emailDueDateReminders,
        browserNotifications: currentSettings.browserNotifications,
        notificationSound: currentSettings.notificationSound
    };

    // Initialize each checkbox
    Object.keys(checkboxes).forEach(key => {
        const elements = document.querySelectorAll(`input[type="checkbox"]`);
        elements.forEach(el => {
            if (el.id === key || el.name === key) {
                el.checked = checkboxes[key];
                el.addEventListener('change', function() {
                    currentSettings[key] = this.checked;
                });
            }
        });
    });
}

/**
 * Initialize privacy settings
 */
function initPrivacySettings() {
    // Profile visibility
    const profileVisibility = document.querySelector('select.settings-select');
    if (profileVisibility && profileVisibility.querySelector('option[value="team"]')) {
        profileVisibility.value = currentSettings.profileVisibility;
        profileVisibility.addEventListener('change', function() {
            currentSettings.profileVisibility = this.value;
        });
    }

    // Show online status
    const showOnlineStatus = document.querySelector('input[type="checkbox"]');
    if (showOnlineStatus) {
        showOnlineStatus.checked = currentSettings.showOnlineStatus;
    }

    // Activity tracking
    const activityTracking = document.querySelectorAll('input[type="checkbox"]')[1];
    if (activityTracking) {
        activityTracking.checked = currentSettings.activityTracking;
    }
}

/**
 * Initialize localization settings
 */
function initLocalizationSettings() {
    const selects = document.querySelectorAll('.settings-select');

    selects.forEach(select => {
        const firstOption = select.querySelector('option');
        if (!firstOption) return;

        // Language
        if (firstOption.value === 'en') {
            select.value = currentSettings.language;
            select.addEventListener('change', function() {
                currentSettings.language = this.value;
            });
        }
        // Timezone
        else if (firstOption.value === 'UTC') {
            select.value = currentSettings.timezone;
            select.addEventListener('change', function() {
                currentSettings.timezone = this.value;
            });
        }
        // Date format
        else if (firstOption.value === 'MM/DD/YYYY') {
            select.value = currentSettings.dateFormat;
            select.addEventListener('change', function() {
                currentSettings.dateFormat = this.value;
            });
        }
        // Time format
        else if (firstOption.value === '12') {
            select.value = currentSettings.timeFormat;
            select.addEventListener('change', function() {
                currentSettings.timeFormat = this.value;
            });
        }
    });
}

/**
 * Initialize display settings
 */
function initDisplaySettings() {
    // Find compact mode and smooth animations checkboxes
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');

    checkboxes.forEach((checkbox, index) => {
        const label = checkbox.closest('.settings-item');
        if (!label) return;

        const labelText = label.querySelector('.settings-item-label');
        if (!labelText) return;

        // Compact mode
        if (labelText.textContent.includes('Compact Mode')) {
            checkbox.checked = currentSettings.compactMode;
            checkbox.addEventListener('change', function() {
                currentSettings.compactMode = this.checked;
                saveSettings(currentSettings); // Save immediately
                applyCompactMode(this.checked);
            });
        }
        // Smooth animations
        else if (labelText.textContent.includes('Smooth Animations')) {
            checkbox.checked = currentSettings.smoothAnimations;
            checkbox.addEventListener('change', function() {
                currentSettings.smoothAnimations = this.checked;
                saveSettings(currentSettings); // Save immediately
                applySmoothAnimations(this.checked);
            });
        }
    });
}

/**
 * Apply compact mode
 */
function applyCompactMode(enabled) {
    if (enabled) {
        document.documentElement.classList.add('compact-mode');
    } else {
        document.documentElement.classList.remove('compact-mode');
    }
}

/**
 * Apply smooth animations
 */
function applySmoothAnimations(enabled) {
    if (enabled) {
        document.documentElement.classList.remove('reduce-motion');
    } else {
        document.documentElement.classList.add('reduce-motion');
    }
}

/**
 * Initialize save button
 */
function initSaveButton() {
    const saveButton = document.querySelector('.settings-save-section .btn-primary');

    if (saveButton) {
        saveButton.addEventListener('click', function(e) {
            e.preventDefault();

            // Save settings
            const saved = saveSettings(currentSettings);

            if (saved) {
                // Show success message
                if (typeof showToast === 'function' && currentSettings.toastEnabled && currentSettings.toastSuccess) {
                    showToast('Settings saved successfully!', 'success', currentSettings.toastDuration);
                } else {
                    alert('Settings saved successfully!');
                }

                // Apply all settings
                applyAllSettings();
            } else {
                // Show error message
                if (typeof showToast === 'function' && currentSettings.toastEnabled && currentSettings.toastError) {
                    showToast('Failed to save settings. Please try again.', 'error', currentSettings.toastDuration);
                } else {
                    alert('Failed to save settings. Please try again.');
                }
            }
        });
    }
}

/**
 * Apply all settings to the app
 */
function applyAllSettings() {
    // Apply theme
    applyTheme(currentSettings.theme);

    // Apply compact mode
    applyCompactMode(currentSettings.compactMode);

    // Apply smooth animations
    applySmoothAnimations(currentSettings.smoothAnimations);

    // Store toast settings globally
    if (window.taskflowSettings === undefined) {
        window.taskflowSettings = {};
    }
    window.taskflowSettings = currentSettings;
}

/**
 * Handle URL hash to show specific section
 */
function handleSettingsHash() {
    const hash = window.location.hash.substring(1);

    if (hash) {
        const navItem = document.querySelector(`.settings-nav-item[data-section="${hash}"]`);
        if (navItem) {
            navItem.click();
        }
    }
}

/**
 * Listen for hash changes
 */
window.addEventListener('hashchange', handleSettingsHash);

/**
 * Export settings for use in other scripts
 */
window.getTaskflowSettings = function() {
    return loadSettings();
};

window.updateTaskflowSetting = function(key, value) {
    const settings = loadSettings();
    settings[key] = value;
    saveSettings(settings);
    currentSettings = settings;
    applyAllSettings();
};
