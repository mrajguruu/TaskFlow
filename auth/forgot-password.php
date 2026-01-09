<!-- forgot-password.php - WITH DEMO PROTECTION -->
<?php
/**
 * Forgot Password Page
 * Smart demo protection + temporary user cleanup
 */

session_start();
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    redirect('../dashboard/index.php');
}

$error = '';
$success = '';
$email = '';
$resetLink = '';
$showDemoLink = false;
$userEmail = '';
$isDemoAccount = false;

// Demo account email list (IDs 1-8 from sample-data.sql)
$DEMO_EMAILS = [
    'admin@taskflow.com',
    'john@taskflow.com',
    'sarah@taskflow.com',
    'mike@taskflow.com',
    'emma@taskflow.com',
    'alex@taskflow.com',
    'liu@taskflow.com',
    'rachel@taskflow.com'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if this is a demo account
        $isDemoAccount = in_array(strtolower($email), array_map('strtolower', $DEMO_EMAILS));
        
        if ($isDemoAccount) {
            // Show specific error for demo accounts
            $error = 'Demo accounts are protected and cannot be reset. Please create a new account from the Register page to test the password reset feature.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, username, email, full_name, created_at FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    // Check if this is a temporary test account (created in last 1 hour)
                    $createdTime = strtotime($user['created_at']);
                    $currentTime = time();
                    $accountAge = ($currentTime - $createdTime) / 3600; // hours
                    
                    if ($accountAge > 1) {
                        // Account is older than 1 hour but not a demo account - allow reset
                        // (This shouldn't happen in demo, but good for production)
                    }
                    
                    // Generate reset token
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    $stmt = $pdo->prepare("
                        INSERT INTO password_resets (user_id, token, expiry)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE token = ?, expiry = ?, used = 0
                    ");
                    $stmt->execute([$user['id'], $token, $expiry, $token, $expiry]);

                    $resetLink = APP_URL . "/auth/reset-password.php?token=" . $token;
                    logActivity($pdo, $user['id'], 'password_reset_requested', 'Password reset requested');
                    
                    $showDemoLink = true;
                    $success = 'Password reset link generated for ' . $user['full_name'] . '!';
                    $userEmail = $user['email'];
                    
                    error_log("Password reset link for {$email} (User ID: {$user['id']}): {$resetLink}");
                } else {
                    // Security: Generic message
                    $success = 'If an account exists with this email, you will receive a password reset link shortly.';
                }
            } catch (PDOException $e) {
                $error = 'An error occurred. Please try again later.';
                error_log("Forgot password error: " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - TaskFlow</title>

    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="../assets/images/favicon/site.webmanifest">
    <link rel="shortcut icon" href="../assets/images/favicon/favicon.ico">
    <meta name="theme-color" content="#2563eb">

    <link rel="stylesheet" href="../assets/css/main.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/auth.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/auth-mobile.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/dark-mode.css?v=<?= time() ?>">
    
    <style>
        /* Demo Protection Alert */
        .alert-demo {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            color: #92400e;
        }

        /* Enhanced Demo Link Box */
        .demo-reset-container {
            margin-top: 28px;
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .demo-reset-link {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.2);
            position: relative;
            overflow: hidden;
        }

        .demo-reset-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #d97706, #f59e0b);
            background-size: 200% 100%;
            animation: shimmer 2s linear infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .demo-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 2px dashed rgba(245, 158, 11, 0.3);
        }

        .demo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .demo-icon svg {
            width: 22px;
            height: 22px;
        }

        .demo-title h4 {
            font-size: 16px;
            font-weight: 700;
            color: #92400e;
            margin: 0 0 4px 0;
        }

        .demo-title p {
            font-size: 13px;
            color: #b45309;
            margin: 0;
        }

        .user-info-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.8);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #92400e;
            border: 1px solid rgba(245, 158, 11, 0.3);
            margin-bottom: 16px;
        }

        .demo-link-box {
            background: #ffffff;
            padding: 16px 18px;
            border-radius: 12px;
            border: 2px solid #fbbf24;
            margin-bottom: 14px;
            word-break: break-all;
            font-family: 'SF Mono', 'Monaco', 'Courier New', monospace;
            font-size: 13px;
            color: #78350f;
            line-height: 1.6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .copy-link-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);
        }

        .copy-link-btn:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
        }

        .demo-warning {
            background: rgba(255, 255, 255, 0.6);
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px dashed #f59e0b;
            margin-top: 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .warning-icon {
            width: 20px;
            height: 20px;
            color: #d97706;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .warning-text {
            flex: 1;
            font-size: 12px;
            color: #92400e;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-secondary {
            padding: 16px 24px;
            background: #ffffff;
            color: #374151;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            transform: translateY(-1px);
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            padding: 18px 26px;
            border-radius: 14px;
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 14px;
            font-weight: 600;
            font-size: 15px;
            z-index: 9999;
            animation: toastIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            max-width: 420px;
            min-width: 320px;
            color: #ffffff;
        }

        .toast.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 12px 48px rgba(16, 185, 129, 0.4);
        }

        .toast.error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 12px 48px rgba(239, 68, 68, 0.4);
        }

        .toast.toast-hide {
            animation: toastOut 0.3s ease-out forwards;
        }

        .toast svg {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }

        @keyframes toastIn {
            from { opacity: 0; transform: translateX(150px) scale(0.8); }
            to { opacity: 1; transform: translateX(0) scale(1); }
        }

        @keyframes toastOut {
            to { opacity: 0; transform: translateX(150px) scale(0.8); }
        }

        @media (max-width: 768px) {
            .toast {
                top: 16px;
                right: 16px;
                left: 16px;
                max-width: none;
                min-width: auto;
            }
            .action-buttons { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="auth-split-container">
        <div class="auth-brand-panel">
            <div class="brand-content">
                <div class="brand-message">
                    <span class="brand-tagline">Modern Task Management</span>
                    <h1 class="brand-title">Reset Your<br>Password</h1>
                    <p class="brand-subtitle">Enter your email address and we'll send you<br>a link to reset your password</p>
                </div>
                <div class="brand-illustration">
                    <img src="../assets/images/illustration.png" alt="TaskFlow - Password Recovery">
                </div>
            </div>
        </div>

        <div class="auth-form-panel">
            <div class="form-container">
                <div class="form-header">
                    <h2>Forgot Password?</h2>
                    <p>No worries, we'll send you reset instructions</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert <?= $isDemoAccount ? 'alert-demo' : 'alert-error' ?>">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <?php if ($isDemoAccount): ?>
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            <?php else: ?>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            <?php endif; ?>
                        </svg>
                        <span><?= sanitize($error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success && !$showDemoLink): ?>
                    <div class="alert alert-success">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span><?= sanitize($success) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!$showDemoLink): ?>
                <form method="POST" action="" class="auth-form" id="forgotPasswordForm">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                placeholder="Enter your email address"
                                value="<?= sanitize($email) ?>"
                                required
                                autofocus
                            >
                        </div>
                        <small class="form-help">🔒 Demo accounts are protected. Create a new account to test password reset.</small>
                    </div>

                    <button type="submit" class="btn-primary" id="submitBtn">
                        <span class="btn-text">Send Reset Link</span>
                        <span class="spinner"></span>
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($showDemoLink): ?>
                    <div class="alert alert-success">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span><?= sanitize($success) ?></span>
                    </div>

                    <div class="demo-reset-container">
                        <div class="demo-reset-link">
                            <div class="demo-header">
                                <div class="demo-icon">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="demo-title">
                                    <h4>🔐 Secure Reset Link Generated</h4>
                                    <p>User-specific token • Expires in 1 hour • Single use</p>
                                </div>
                            </div>

                            <?php if ($userEmail): ?>
                            <div class="user-info-badge">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                                Reset link for: <strong><?= sanitize($userEmail) ?></strong>
                            </div>
                            <?php endif; ?>

                            <div class="demo-link-box" id="resetLinkBox">
                                <?= htmlspecialchars($resetLink) ?>
                            </div>

                            <button type="button" class="copy-link-btn" onclick="copyResetLink()">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z"/>
                                    <path d="M3 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L10.414 13H15v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5zM15 11h2a1 1 0 110 2h-2v-2z"/>
                                </svg>
                                Copy Reset Link to Clipboard
                            </button>

                            <div class="demo-warning">
                                <svg class="warning-icon" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="warning-text">
                                    <strong>📧 Development Mode Active</strong>
                                    This link is shown for testing. In production, it would be emailed. Demo accounts (IDs 1-8) are protected from password changes.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="<?= htmlspecialchars($resetLink) ?>" class="btn-primary" style="text-decoration: none; flex: 1; text-align: center;">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Go to Reset Password
                        </a>
                        <a href="login.php" class="btn-secondary">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Login
                        </a>
                    </div>
                <?php endif; ?>

                <div class="auth-footer">
                    Remember your password? <a href="login.php">Sign In</a>
                </div>

            </div>
        </div>
    </div>

    <script src="../assets/js/dark-mode.js"></script>

    <script>
        function showToast(message, type = 'success', duration = 3500) {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            const icon = type === 'success' 
                ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>'
                : '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>';
            
            toast.innerHTML = `
                <svg viewBox="0 0 20 20" fill="currentColor">${icon}</svg>
                <div class="toast-message">${message}</div>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('toast-hide');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        function copyResetLink() {
            const linkBox = document.getElementById('resetLinkBox');
            const link = linkBox.textContent.trim();
            const btn = event.currentTarget;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(link).then(() => {
                    showToast('✓ Reset link copied to clipboard!', 'success');
                    btn.innerHTML = `
                        <svg fill="currentColor" viewBox="0 0 20 20" style="width:20px;height:20px">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Copied Successfully!
                    `;
                    setTimeout(() => {
                        btn.innerHTML = `
                            <svg fill="currentColor" viewBox="0 0 20 20" style="width:20px;height:20px">
                                <path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z"/>
                                <path d="M3 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L10.414 13H15v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5zM15 11h2a1 1 0 110 2h-2v-2z"/>
                            </svg>
                            Copy Reset Link to Clipboard
                        `;
                    }, 2000);
                }).catch(() => fallbackCopy(link));
            } else {
                fallbackCopy(link);
            }
        }

        function fallbackCopy(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                showToast('✓ Reset link copied to clipboard!', 'success');
            } catch (err) {
                showToast('⚠ Failed to copy. Please copy manually.', 'error', 4000);
            }
            document.body.removeChild(textArea);
        }

        (function() {
            const form = document.getElementById('forgotPasswordForm');
            if (!form) return;

            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', function() {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });

            window.addEventListener('pageshow', function(event) {
                if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                    submitBtn.classList.remove('loading');
                    submitBtn.disable<function_results>OK</function_calls>