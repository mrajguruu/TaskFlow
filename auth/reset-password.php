<?php
/**
 * Reset Password Page
 * Handles password reset with token verification
 */

session_start();
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('../dashboard/index.php');
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$validToken = false;
$userId = null;

// Validate token
if (!empty($token)) {
    try {
        $stmt = $pdo->prepare("
            SELECT pr.user_id, pr.expiry, u.email
            FROM password_resets pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.token = ? AND pr.used = 0
        ");
        $stmt->execute([$token]);
        $resetData = $stmt->fetch();

        if ($resetData) {
            if (strtotime($resetData['expiry']) > time()) {
                $validToken = true;
                $userId = $resetData['user_id'];
            } else {
                $error = 'This password reset link has expired. Please request a new one.';
            }
        } else {
            $error = 'Invalid or already used password reset link.';
        }
    } catch (PDOException $e) {
        $error = 'An error occurred. Please try again later.';
        error_log("Token validation error: " . $e->getMessage());
    }
} else {
    $error = 'No reset token provided.';
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirmPassword)) {
        $error = 'Please enter and confirm your new password';
    } else {
        $passwordValidation = validatePassword($password);
        if (!$passwordValidation['valid']) {
            $error = $passwordValidation['message'];
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } else {
            try {
                // Update password
                $hashedPassword = hashPassword($password);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $userId]);

                // Mark token as used
                $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                $stmt->execute([$token]);

                // Log activity
                logActivity($pdo, $userId, 'password_reset_completed', 'Password was reset');

                $success = 'Your password has been reset successfully! Redirecting to login...';
                header("refresh:3;url=login.php");
            } catch (PDOException $e) {
                $error = 'An error occurred. Please try again later.';
                error_log("Password reset error: " . $e->getMessage());
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
    <title>Reset Password - TaskFlow</title>

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
        /* Password Strength Indicator */
        .password-strength {
            margin-top: 8px;
            display: none;
        }

        .password-strength.active {
            display: block;
        }

        .strength-bar {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 6px;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-fill.weak {
            width: 33%;
            background: #ef4444;
        }

        .strength-fill.medium {
            width: 66%;
            background: #f59e0b;
        }

        .strength-fill.strong {
            width: 100%;
            background: #10b981;
        }

        .strength-text {
            font-size: 12px;
            font-weight: 600;
        }

        .strength-text.weak {
            color: #ef4444;
        }

        .strength-text.medium {
            color: #f59e0b;
        }

        .strength-text.strong {
            color: #10b981;
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(16, 185, 129, 0.4), 0 4px 12px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 15px;
            z-index: 9999;
            animation: toastIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            max-width: 420px;
            min-width: 300px;
        }

        .toast.toast-hide {
            animation: toastOut 0.3s ease-out forwards;
        }

        .toast svg {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
        }

        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateX(120px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        @keyframes toastOut {
            to {
                opacity: 0;
                transform: translateX(120px) scale(0.9);
            }
        }

        @media (max-width: 768px) {
            .toast {
                top: 16px;
                right: 16px;
                left: 16px;
                max-width: none;
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="auth-split-container">
        <!-- Brand Panel -->
        <div class="auth-brand-panel">
            <div class="brand-content">
                <div class="brand-message">
                    <span class="brand-tagline">Modern Task Management</span>
                    <h1 class="brand-title">Create New<br>Password</h1>
                    <p class="brand-subtitle">Choose a strong password to keep<br>your account secure</p>
                </div>

                <div class="brand-illustration">
                    <img src="../assets/images/illustration.png" alt="TaskFlow - Create New Password">
                </div>
            </div>
        </div>

        <!-- Form Panel -->
        <div class="auth-form-panel">
            <div class="form-container">
                <div class="form-header">
                    <h2>Reset Password</h2>
                    <p>Enter your new password below</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span><?= sanitize($error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span><?= sanitize($success) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($validToken && !$success): ?>
                <form method="POST" action="" class="auth-form" id="resetPasswordForm">
                    <div class="form-group">
                        <label for="password" class="form-label">New Password <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Create a strong password"
                                required
                                minlength="8"
                                autofocus
                            >
                            <span class="password-toggle" id="passwordToggle" onclick="togglePassword('password')">
                                <svg id="eyeIcon" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                                <svg id="eyeOffIcon" viewBox="0 0 20 20" fill="currentColor" style="display: none;">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                </svg>
                            </span>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <div class="strength-text" id="strengthText"></div>
                        </div>
                        <small class="form-help">Minimum 8 characters with uppercase, lowercase, and number</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-input"
                                placeholder="Confirm your new password"
                                required
                            >
                            <span class="password-toggle" id="confirmPasswordToggle" onclick="togglePassword('confirm_password')">
                                <svg id="eyeIcon2" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                                <svg id="eyeOffIcon2" viewBox="0 0 20 20" fill="currentColor" style="display: none;">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" id="submitBtn">
                        Reset Password
                    </button>
                </form>
                <?php elseif (!$validToken): ?>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="forgot-password.php" class="btn-primary" style="display: inline-block; text-decoration: none; padding: 16px 32px; width: auto; margin-bottom: 12px;">
                            Request New Reset Link
                        </a>
                        <br>
                        <a href="login.php" style="color: #2563eb; text-decoration: none; font-weight: 600;">
                            Back to Login
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Footer -->
                <div class="auth-footer">
                    Remember your password? <a href="login.php">Sign In</a>
                </div>

            </div>
        </div>
    </div>

    <script src="../assets/js/dark-mode.js"></script>

    <script>
        // Password toggle
        function togglePassword(fieldId) {
            const input = document.getElementById(fieldId);
            const isPassword = fieldId === 'password';
            const eyeIcon = document.getElementById(isPassword ? 'eyeIcon' : 'eyeIcon2');
            const eyeOffIcon = document.getElementById(isPassword ? 'eyeOffIcon' : 'eyeOffIcon2');

            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                input.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        }

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthIndicator = document.getElementById('passwordStrength');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            if (password.length === 0) {
                strengthIndicator.classList.remove('active');
                return;
            }

            strengthIndicator.classList.add('active');
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            strengthFill.className = 'strength-fill';
            strengthText.className = 'strength-text';

            if (strength <= 2) {
                strengthFill.classList.add('weak');
                strengthText.classList.add('weak');
                strengthText.textContent = 'Weak password';
            } else if (strength === 3) {
                strengthFill.classList.add('medium');
                strengthText.classList.add('medium');
                strengthText.textContent = 'Medium strength';
            } else {
                strengthFill.classList.add('strong');
                strengthText.classList.add('strong');
                strengthText.textContent = 'Strong password';
            }
        });

        // Form submission
        const form = document.getElementById('resetPasswordForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });
        }
    </script>
</body>
</html>