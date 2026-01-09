<?php
/**
 * Header Template
 * Included on all dashboard pages
 */

if (!defined('APP_NAME')) {
    die('Direct access not permitted');
}

requireLogin();
$currentUser = getCurrentUser($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - <?= APP_NAME ?></title>

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= APP_URL ?>/assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= APP_URL ?>/assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= APP_URL ?>/assets/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?= APP_URL ?>/assets/images/favicon/site.webmanifest">
    <link rel="shortcut icon" href="<?= APP_URL ?>/assets/images/favicon/favicon.ico">
    <meta name="theme-color" content="#2563eb">
    <meta name="msapplication-TileColor" content="#2563eb">
    <meta name="msapplication-config" content="<?= APP_URL ?>/assets/images/favicon/browserconfig.xml">

    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/mobile-nav.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/toast.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/skeleton.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/responsive-fixes.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/form-validation.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/form-autosave.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dark-mode.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dark-mode-dashboard.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/user-menu-logout.css">
    <?php if (isset($extraCSS)): ?>
        <?php foreach ($extraCSS as $css): ?>
            <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Apply theme immediately to prevent flash -->
    <script>
        (function() {
            try {
                // Clean up old localStorage keys that cause conflicts
                const oldTheme = localStorage.getItem('theme');
                const oldTaskflowTheme = localStorage.getItem('taskflow_theme');

                // Get theme from unified settings
                const settingsStr = localStorage.getItem('taskflow_settings');
                let theme = 'light'; // default
                let settings = {};

                if (settingsStr) {
                    settings = JSON.parse(settingsStr);
                    theme = settings.theme || 'light';
                } else {
                    // Migrate from old storage if exists
                    if (oldTheme || oldTaskflowTheme) {
                        theme = oldTheme || oldTaskflowTheme;
                        settings.theme = theme;
                        localStorage.setItem('taskflow_settings', JSON.stringify(settings));
                    }
                }

                // Remove old keys to prevent conflicts
                if (oldTheme) localStorage.removeItem('theme');
                if (oldTaskflowTheme) localStorage.removeItem('taskflow_theme');

                // Apply theme immediately
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark-mode');
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else if (theme === 'auto') {
                    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (prefersDark) {
                        document.documentElement.classList.add('dark-mode');
                        document.documentElement.setAttribute('data-theme', 'dark');
                    } else {
                        document.documentElement.setAttribute('data-theme', 'light');
                    }
                } else {
                    // Light theme - don't add dark-mode class
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } catch (e) {
                // Silently fail - theme will default to light
            }
        })();
    </script>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="header-left">
                <a href="<?= APP_URL ?>/dashboard/index.php" class="logo">
                    <div class="logo-icon-wrapper">
                        <img src="<?= APP_URL ?>/assets/images/taskflow_icon.png" alt="TaskFlow" width="400" height="64">
                    </div>
                </a>
            </div>

            <nav class="header-nav">
                <a href="<?= APP_URL ?>/dashboard/index.php" class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="9"/>
                        <rect x="14" y="3" width="7" height="5"/>
                        <rect x="14" y="12" width="7" height="9"/>
                        <rect x="3" y="16" width="7" height="5"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="<?= APP_URL ?>/dashboard/projects.php" class="nav-link <?= ($activePage ?? '') === 'projects' ? 'active' : '' ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span>Projects</span>
                </a>
                <a href="<?= APP_URL ?>/dashboard/team.php" class="nav-link <?= ($activePage ?? '') === 'team' ? 'active' : '' ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Team</span>
                </a>
            </nav>

            <div class="header-right">
                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="user-menu">
                    <button class="user-menu-trigger" onclick="toggleUserMenu()">
                        <?= getUserAvatar($currentUser['avatar'], $currentUser['full_name'], 'sm') ?>
                        <span class="user-name"><?= sanitize($currentUser['full_name']) ?></span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="dropdown-arrow">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div class="user-menu-dropdown hidden" id="userMenuDropdown">
                        <div class="user-menu-header">
                            <div class="user-info">
                                <strong><?= sanitize($currentUser['full_name']) ?></strong>
                                <small><?= sanitize($currentUser['email']) ?></small>
                            </div>
                        </div>
                        <div class="user-menu-items">
                            <a href="<?= APP_URL ?>/dashboard/profile.php" class="user-menu-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <span>Profile</span>
                            </a>
                            <a href="<?= APP_URL ?>/dashboard/settings.php" class="user-menu-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="3"/>
                                    <path d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"/>
                                </svg>
                                <span>Settings</span>
                            </a>
                            <a href="<?= APP_URL ?>/auth/logout.php" class="user-menu-item logout">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Overlay -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay"></div>

    <!-- Mobile Navigation Drawer -->
    <aside class="mobile-nav-drawer" id="mobileNavDrawer">
        <div class="mobile-nav-header">
            <a href="<?= APP_URL ?>/dashboard/index.php" class="mobile-nav-logo">
                <img src="<?= APP_URL ?>/assets/images/taskflow_icon.png" alt="TaskFlow" width="200" height="56">
            </a>
            <button class="mobile-nav-close" id="mobileNavClose" aria-label="Close menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="mobile-nav-user">
            <div class="mobile-nav-user-info">
                <?= getUserAvatar($currentUser['avatar'], $currentUser['full_name'], 'md') ?>
                <div class="mobile-nav-user-details">
                    <div class="mobile-nav-user-name"><?= sanitize($currentUser['full_name']) ?></div>
                    <div class="mobile-nav-user-email"><?= sanitize($currentUser['email']) ?></div>
                </div>
            </div>
        </div>

        <nav class="mobile-nav-items">
            <div class="mobile-nav-section">
                <div class="mobile-nav-section-title">Main</div>
                <a href="<?= APP_URL ?>/dashboard/index.php" class="mobile-nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="9"/>
                        <rect x="14" y="3" width="7" height="5"/>
                        <rect x="14" y="12" width="7" height="9"/>
                        <rect x="3" y="16" width="7" height="5"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="<?= APP_URL ?>/dashboard/projects.php" class="mobile-nav-link <?= ($activePage ?? '') === 'projects' ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span>Projects</span>
                </a>
                <a href="<?= APP_URL ?>/dashboard/team.php" class="mobile-nav-link <?= ($activePage ?? '') === 'team' ? 'active' : '' ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Team</span>
                </a>
            </div>
        </nav>

        <div class="mobile-nav-footer">
            <div class="mobile-nav-footer-links">
                <a href="<?= APP_URL ?>/dashboard/profile.php" class="mobile-nav-footer-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Profile</span>
                </a>
                <a href="<?= APP_URL ?>/dashboard/settings.php" class="mobile-nav-footer-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"/>
                    </svg>
                    <span>Settings</span>
                </a>
                <a href="<?= APP_URL ?>/auth/logout.php" class="mobile-nav-footer-link logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </aside>

    <main class="main-content">
