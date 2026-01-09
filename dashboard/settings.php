<?php
/**
 * Settings Page
 * Application preferences, notifications, privacy, and account settings
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require user to be logged in
requireLogin();

// Page settings
$pageTitle = 'Settings';
$activePage = 'settings';

// Get current user
$currentUser = getCurrentUser($pdo);
$userId = $currentUser['id'];

// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle different settings updates
    if (isset($_POST['update_preferences'])) {
        // Application preferences update logic would go here
        $successMessage = 'Preferences updated successfully!';
    } elseif (isset($_POST['update_notifications'])) {
        // Notification settings update logic would go here
        $successMessage = 'Notification settings updated successfully!';
    } elseif (isset($_POST['update_privacy'])) {
        // Privacy settings update logic would go here
        $successMessage = 'Privacy settings updated successfully!';
    }
}

// Include header
include '../includes/header.php';
?>

<link rel="stylesheet" href="<?= asset('css/settings.css') ?>">

<div class="settings-container">
    <!-- Settings Header -->
    <div class="settings-header">
        <div class="settings-header-content">
            <h1 class="settings-title">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"/>
                </svg>
                Settings
            </h1>
            <p class="settings-subtitle">Manage your application preferences and account settings</p>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($successMessage): ?>
        <div class="alert alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <?= sanitize($successMessage) ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <?= sanitize($errorMessage) ?>
        </div>
    <?php endif; ?>

    <!-- Settings Layout -->
    <div class="settings-layout">
        <!-- Settings Sidebar Navigation -->
        <aside class="settings-sidebar">
            <nav class="settings-nav">
                <a href="#appearance" class="settings-nav-item active" data-section="appearance">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"/>
                        <path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                    </svg>
                    <span>Appearance</span>
                </a>
                <a href="#notifications" class="settings-nav-item" data-section="notifications">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span>Notifications</span>
                </a>
                <a href="#privacy" class="settings-nav-item" data-section="privacy">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span>Privacy & Security</span>
                </a>
                <a href="#language" class="settings-nav-item" data-section="language">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                    <span>Language & Region</span>
                </a>
                <a href="#data" class="settings-nav-item" data-section="data">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                    <span>Data & Storage</span>
                </a>
            </nav>
        </aside>

        <!-- Settings Content -->
        <main class="settings-content">
            <!-- Appearance Section -->
            <section id="appearance" class="settings-section active">
                <div class="settings-section-header">
                    <h2 class="settings-section-title">Appearance</h2>
                    <p class="settings-section-subtitle">Customize how TaskFlow looks and feels</p>
                </div>

                <div class="settings-group">
                    <div class="settings-group-header">
                        <div class="settings-group-title">Theme</div>
                        <div class="settings-group-description">Choose your preferred color scheme</div>
                    </div>

                    <div class="theme-selector-grid">
                        <button class="theme-card" data-theme="light">
                            <div class="theme-card-preview light-preview">
                                <div class="preview-header"></div>
                                <div class="preview-content">
                                    <div class="preview-sidebar"></div>
                                    <div class="preview-main">
                                        <div class="preview-bar"></div>
                                        <div class="preview-bar short"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="theme-card-info">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="5"/>
                                    <path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                                </svg>
                                <span>Light</span>
                            </div>
                            <div class="theme-card-check">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </div>
                        </button>

                        <button class="theme-card" data-theme="dark">
                            <div class="theme-card-preview dark-preview">
                                <div class="preview-header"></div>
                                <div class="preview-content">
                                    <div class="preview-sidebar"></div>
                                    <div class="preview-main">
                                        <div class="preview-bar"></div>
                                        <div class="preview-bar short"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="theme-card-info">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                                </svg>
                                <span>Dark</span>
                            </div>
                            <div class="theme-card-check">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </div>
                        </button>

                        <button class="theme-card" data-theme="auto">
                            <div class="theme-card-preview auto-preview">
                                <div class="preview-header"></div>
                                <div class="preview-content">
                                    <div class="preview-sidebar"></div>
                                    <div class="preview-main">
                                        <div class="preview-bar"></div>
                                        <div class="preview-bar short"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="theme-card-info">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                    <line x1="8" y1="21" x2="16" y2="21"/>
                                    <line x1="12" y1="17" x2="12" y2="21"/>
                                </svg>
                                <span>Auto</span>
                            </div>
                            <div class="theme-card-check">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="settings-divider"></div>

                <div class="settings-group">
                    <div class="settings-group-header">
                        <div class="settings-group-title">Toast Notifications</div>
                        <div class="settings-group-description">Customize in-app notification toasts</div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Enable Toast Notifications</div>
                            <div class="settings-item-description">Show toast messages for actions and events</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked id="toast-enabled">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Success Toasts</div>
                            <div class="settings-item-description">Show notifications when actions complete successfully</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked id="toast-success">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Error Toasts</div>
                            <div class="settings-item-description">Show notifications when errors occur</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked id="toast-error">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Warning Toasts</div>
                            <div class="settings-item-description">Show warning notifications</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked id="toast-warning">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Info Toasts</div>
                            <div class="settings-item-description">Show informational notifications</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked id="toast-info">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Toast Duration</div>
                            <div class="settings-item-description">How long toasts stay visible</div>
                        </div>
                        <div class="settings-item-control">
                            <select class="settings-select" id="toast-duration">
                                <option value="2000">2 seconds</option>
                                <option value="3000" selected>3 seconds</option>
                                <option value="4000">4 seconds</option>
                                <option value="5000">5 seconds</option>
                                <option value="7000">7 seconds</option>
                            </select>
                        </div>
                    </div>

                    <div class="toast-preview-section">
                        <div class="toast-preview-header">
                            <div class="toast-preview-title">Preview</div>
                            <button type="button" class="btn btn-sm btn-secondary" id="test-toast-btn">Test Toast</button>
                        </div>
                        <div class="toast-preview-container">
                            <div class="toast-preview toast-preview-success">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <div>
                                    <div class="toast-preview-message">Task completed successfully</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="settings-divider"></div>

                <div class="settings-group">
                    <div class="settings-group-header">
                        <div class="settings-group-title">Display Options</div>
                        <div class="settings-group-description">Adjust how content is displayed</div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Compact Mode</div>
                            <div class="settings-item-description">Reduce spacing to show more content</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Smooth Animations</div>
                            <div class="settings-item-description">Enable smooth transitions and animations</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Notifications Section -->
            <section id="notifications" class="settings-section">
                <div class="settings-section-header">
                    <h2 class="settings-section-title">Notifications</h2>
                    <p class="settings-section-subtitle">Manage how you receive notifications</p>
                </div>

                <div class="settings-group">
                    <div class="settings-group-header">
                        <div class="settings-group-title">Email Notifications</div>
                        <div class="settings-group-description">Receive updates via email</div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Task Assignments</div>
                            <div class="settings-item-description">Get notified when you're assigned to a task</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Project Updates</div>
                            <div class="settings-item-description">Receive updates about projects you're a member of</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Comments & Mentions</div>
                            <div class="settings-item-description">When someone mentions you in a comment</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Due Date Reminders</div>
                            <div class="settings-item-description">Reminders for tasks approaching their due date</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="settings-divider"></div>

                <div class="settings-group">
                    <div class="settings-group-header">
                        <div class="settings-group-title">Push Notifications</div>
                        <div class="settings-group-description">Browser notifications</div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Browser Notifications</div>
                            <div class="settings-item-description">Show desktop notifications in your browser</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Notification Sound</div>
                            <div class="settings-item-description">Play a sound for new notifications</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Privacy & Security Section -->
            <section id="privacy" class="settings-section">
                <div class="settings-section-header">
                    <h2 class="settings-section-title">Privacy & Security</h2>
                    <p class="settings-section-subtitle">Control your privacy and security settings</p>
                </div>

                <div class="settings-group">
                    <div class="settings-group-header">
                        <div class="settings-group-title">Privacy</div>
                        <div class="settings-group-description">Control who can see your information</div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Profile Visibility</div>
                            <div class="settings-item-description">Who can see your profile information</div>
                        </div>
                        <div class="settings-item-control">
                            <select class="settings-select">
                                <option value="everyone">Everyone</option>
                                <option value="team" selected>Team Members Only</option>
                                <option value="private">Private</option>
                            </select>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Show Online Status</div>
                            <div class="settings-item-description">Let others see when you're online</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Activity Tracking</div>
                            <div class="settings-item-description">Track your activity for analytics</div>
                        </div>
                        <div class="settings-item-control">
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="settings-divider"></div>

                <div class="settings-group">
                    <div class="settings-group-header">
                        <div class="settings-group-title">Two-Factor Authentication</div>
                        <div class="settings-group-description">Add extra security to your account</div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Enable 2FA</div>
                            <div class="settings-item-description">Add an extra layer of security to your account</div>
                        </div>
                        <div class="settings-item-control">
                            <button class="btn btn-secondary btn-sm">Set Up</button>
                        </div>
                    </div>
                </div>

                <div class="settings-divider"></div>

                <div class="settings-group">
                    <div class="settings-group-header">
                        <div class="settings-group-title">Active Sessions</div>
                        <div class="settings-group-description">Manage your active login sessions</div>
                    </div>

                    <div class="session-list">
                        <div class="session-item">
                            <div class="session-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                    <line x1="8" y1="21" x2="16" y2="21"/>
                                    <line x1="12" y1="17" x2="12" y2="21"/>
                                </svg>
                            </div>
                            <div class="session-info">
                                <div class="session-device">Windows • Chrome</div>
                                <div class="session-details">
                                    <span class="session-current">Current Session</span>
                                    <span>•</span>
                                    <span>Last active: Just now</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Language & Region Section -->
            <section id="language" class="settings-section">
                <div class="settings-section-header">
                    <h2 class="settings-section-title">Language & Region</h2>
                    <p class="settings-section-subtitle">Set your language and regional preferences</p>
                </div>

                <div class="settings-group">
                    <div class="settings-group-header">
                        <div class="settings-group-title">Localization</div>
                        <div class="settings-group-description">Language and regional settings</div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Language</div>
                            <div class="settings-item-description">Choose your preferred language</div>
                        </div>
                        <div class="settings-item-control">
                            <select class="settings-select">
                                <option value="en" selected>English</option>
                                <option value="es">Español</option>
                                <option value="fr">Français</option>
                                <option value="de">Deutsch</option>
                                <option value="ja">日本語</option>
                            </select>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Timezone</div>
                            <div class="settings-item-description">Used for due dates and notifications</div>
                        </div>
                        <div class="settings-item-control">
                            <select class="settings-select">
                                <option value="UTC">UTC (GMT+0)</option>
                                <option value="America/New_York" selected>Eastern Time (GMT-5)</option>
                                <option value="America/Chicago">Central Time (GMT-6)</option>
                                <option value="America/Los_Angeles">Pacific Time (GMT-8)</option>
                                <option value="Europe/London">London (GMT+0)</option>
                                <option value="Asia/Tokyo">Tokyo (GMT+9)</option>
                            </select>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Date Format</div>
                            <div class="settings-item-description">How dates are displayed</div>
                        </div>
                        <div class="settings-item-control">
                            <select class="settings-select">
                                <option value="MM/DD/YYYY" selected>MM/DD/YYYY</option>
                                <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                                <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                            </select>
                        </div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Time Format</div>
                            <div class="settings-item-description">12-hour or 24-hour clock</div>
                        </div>
                        <div class="settings-item-control">
                            <select class="settings-select">
                                <option value="12" selected>12-hour (2:30 PM)</option>
                                <option value="24">24-hour (14:30)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Data & Storage Section -->
            <section id="data" class="settings-section">
                <div class="settings-section-header">
                    <h2 class="settings-section-title">Data & Storage</h2>
                    <p class="settings-section-subtitle">Manage your data and storage preferences</p>
                </div>

                <div class="settings-group">
                    <div class="settings-group-header">
                        <div class="settings-group-title">Cache & Storage</div>
                        <div class="settings-group-description">Manage cached data</div>
                    </div>

                    <div class="settings-item">
                        <div class="settings-item-info">
                            <div class="settings-item-label">Clear Cache</div>
                            <div class="settings-item-description">Clear cached data to free up space</div>
                        </div>
                        <div class="settings-item-control">
                            <button class="btn btn-secondary btn-sm">Clear Cache</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Save Button -->
            <div class="settings-save-section">
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Save All Changes
                </button>
                <a href="index.php" class="btn btn-ghost">Cancel</a>
            </div>
        </main>
    </div>
</div>

<script src="<?= asset('js/settings.js') ?>"></script>

<?php include '../includes/footer.php'; ?>
