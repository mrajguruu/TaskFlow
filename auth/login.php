<?php
/**
 * Login Page - Split Screen Layout
 * Professional two-panel authentication design
 * Version: 3.0.0
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

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, username, email, password, full_name, avatar, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && verifyPassword($password, $user['password'])) {
                // Login successful
                loginUser($user['id'], $user, $remember);

                // Update last login time
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);

                // Log activity
                logActivity($pdo, $user['id'], 'user_login', 'User logged in');

                // Redirect to dashboard or intended page
                $redirect = $_SESSION['redirect_after_login'] ?? '../dashboard/index.php';
                unset($_SESSION['redirect_after_login']);
                redirect($redirect);
            } else {
                $error = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TaskFlow</title>

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="../assets/images/favicon/site.webmanifest">
    <link rel="shortcut icon" href="../assets/images/favicon/favicon.ico">
    <meta name="theme-color" content="#2563eb">
    <meta name="msapplication-TileColor" content="#2563eb">

    <link rel="stylesheet" href="../assets/css/main.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/auth.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/auth-mobile.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/dark-mode.css?v=<?= time() ?>">
</head>
<body>
    <div class="auth-split-container">
        <!-- LEFT PANEL - LOGIN FORM -->
        <div class="auth-form-panel">
            <div class="form-container">
                <div class="form-header">
                    <h2>Sign in to TaskFlow</h2>
                    <p>Enter your credentials to access your account</p>
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

                <form method="POST" action="" class="auth-form" id="loginForm" novalidate>
                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                placeholder="name@company.com"
                                value="<?= sanitize($_POST['email'] ?? '') ?>"
                                required
                                autofocus
                            >
                            <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                        </div>
                        <div id="emailError" class="error-message" style="display: none;">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span></span>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-wrapper">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Enter your password"
                                required
                            >
                            <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="password-toggle" id="passwordToggle">
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
                        <div id="passwordError" class="error-message" style="display: none;">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span></span>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" name="remember" id="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span class="btn-text">Sign In</span>
                        <span class="spinner"></span>
                    </button>
                </form>

                <!-- Footer -->
                <div class="auth-footer">
                    Don't have an account? <a href="register.php">Create Account</a>
                </div>

            </div>
        </div>

        <!-- RIGHT PANEL - BRAND SHOWCASE WITH ILLUSTRATION -->
        <div class="auth-brand-panel">
            <div class="brand-content">
                <div class="brand-message">
                    <span class="brand-tagline">Modern Task Management</span>
                    <h1 class="brand-title">Organize. Collaborate.<br>Achieve More.</h1>
                    <p class="brand-subtitle">TaskFlow brings teams together with powerful<br>kanban boards and real-time collaboration</p>
                </div>

                <div class="brand-illustration">
                    <img src="../assets/images/illustration.png" alt="TaskFlow - Modern Task Management">
                </div>
            </div>
        </div>
    </div>

    <!-- Dark Mode Script -->
    <script src="../assets/js/dark-mode.js"></script>

    <!-- Login Form Validation & Features -->
    <script>
        // Form validation and features
        (function() {
            const form = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const submitBtn = document.getElementById('submitBtn');
            const passwordToggle = document.getElementById('passwordToggle');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');

            // Email validation
            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }

            // Real-time email validation
            emailInput.addEventListener('blur', function() {
                const email = this.value.trim();

                if (email === '') {
                    showError(emailInput, emailError, 'Email is required');
                } else if (!validateEmail(email)) {
                    showError(emailInput, emailError, 'Please enter a valid email address');
                } else {
                    showSuccess(emailInput, emailError);
                }
            });

            emailInput.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    const email = this.value.trim();
                    if (email !== '' && validateEmail(email)) {
                        showSuccess(this, emailError);
                    }
                }
            });

            // Real-time password validation
            passwordInput.addEventListener('blur', function() {
                const password = this.value;

                if (password === '') {
                    showError(passwordInput, passwordError, 'Password is required');
                } else if (password.length < 6) {
                    showError(passwordInput, passwordError, 'Password must be at least 6 characters');
                } else {
                    showSuccess(passwordInput, passwordError);
                }
            });

            passwordInput.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    const password = this.value;
                    if (password.length >= 6) {
                        showSuccess(this, passwordError);
                    }
                }
            });

            // Show error
            function showError(input, errorElement, message) {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                errorElement.querySelector('span').textContent = message;
                errorElement.style.display = 'flex';
            }

            // Show success
            function showSuccess(input, errorElement) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                errorElement.style.display = 'none';
            }

            // Password toggle
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;

                if (type === 'text') {
                    eyeIcon.style.display = 'none';
                    eyeOffIcon.style.display = 'block';
                } else {
                    eyeIcon.style.display = 'block';
                    eyeOffIcon.style.display = 'none';
                }
            });

            // Form submission with loading state
            form.addEventListener('submit', function(e) {
                const email = emailInput.value.trim();
                const password = passwordInput.value;
                let hasError = false;

                // Validate email
                if (email === '') {
                    showError(emailInput, emailError, 'Email is required');
                    hasError = true;
                } else if (!validateEmail(email)) {
                    showError(emailInput, emailError, 'Please enter a valid email address');
                    hasError = true;
                }

                // Validate password
                if (password === '') {
                    showError(passwordInput, passwordError, 'Password is required');
                    hasError = true;
                } else if (password.length < 6) {
                    showError(passwordInput, passwordError, 'Password must be at least 6 characters');
                    hasError = true;
                }

                if (hasError) {
                    e.preventDefault();
                    return false;
                }

                // Show loading state
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });

            // Remove loading state if page shows error (back navigation)
            window.addEventListener('pageshow', function(event) {
                if (event.persisted || performance.navigation.type === 2) {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                }
            });

            // Clear loading on back/forward cache
            if (performance.navigation.type === performance.navigation.TYPE_BACK_FORWARD) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
        })();
    </script>
</body>
</html>