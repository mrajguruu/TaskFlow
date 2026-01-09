<?php
/**
 * Registration Page
 * Handles new user registration
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
$formData = [];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Store form data for re-population on error
    $formData = compact('fullName', 'username', 'email');

    // Validate inputs
    if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (strlen($fullName) < 3) {
        $error = 'Full name must be at least 3 characters long';
    } elseif (!validateUsername($username)) {
        $error = 'Username must be 4-20 characters and contain only letters, numbers, and underscores';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        $passwordValidation = validatePassword($password);
        if (!$passwordValidation['valid']) {
            $error = $passwordValidation['message'];
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } else {
            try {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'Username already taken';
                } else {
                    // Check if email already exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = 'Email already registered';
                    } else {
                        // Create new user
                        $hashedPassword = hashPassword($password);
                        $stmt = $pdo->prepare("
                            INSERT INTO users (username, email, password, full_name, role)
                            VALUES (?, ?, ?, ?, 'member')
                        ");

                        if ($stmt->execute([$username, $email, $hashedPassword, $fullName])) {
                            $userId = $pdo->lastInsertId();

                            // Log activity
                            logActivity($pdo, $userId, 'user_registered', 'New user registered');

                            $success = 'Registration successful! Redirecting to login...';

                            // Redirect to login after 2 seconds
                            header("refresh:2;url=login.php");
                        } else {
                            $error = 'Registration failed. Please try again.';
                        }
                    }
                }
            } catch (PDOException $e) {
                $error = 'An error occurred. Please try again later.';
                error_log("Registration error: " . $e->getMessage());
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
    <title>Register - TaskFlow</title>

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
    <div class="auth-split-container register-mode">
        <!-- LEFT PANEL - BRAND SHOWCASE (70%) -->
        <div class="auth-brand-panel">
            <div class="brand-content">
                <div class="brand-message">
                    <span class="brand-tagline">Modern Task Management</span>
                    <h1 class="brand-title">Join TaskFlow.<br>Get Things Done.</h1>
                    <p class="brand-subtitle">Create your account and start collaborating<br>with your team in minutes</p>
                </div>

                <div class="brand-illustration">
                    <img src="../assets/images/illustration.png" alt="TaskFlow - Modern Task Management">
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL - REGISTRATION FORM (30%) -->
        <div class="auth-form-panel">
            <div class="form-container">
                <div class="form-header">
                    <h2>Create Account</h2>
                    <p>Join TaskFlow to manage your projects</p>
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

                <form method="POST" action="" class="auth-form" id="registerForm">
                    <div class="form-group">
                        <label for="full_name" class="form-label">Full Name <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                class="form-input"
                                placeholder="Enter your full name"
                                value="<?= sanitize($formData['fullName'] ?? '') ?>"
                                required
                                minlength="3"
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username" class="form-label">Username <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-input"
                                placeholder="Choose a username"
                                value="<?= sanitize($formData['username'] ?? '') ?>"
                                required
                                pattern="[a-zA-Z0-9_]{4,20}"
                            >
                        </div>
                        <small class="form-help">4-20 characters, letters, numbers, and underscores only</small>
                    </div>

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
                                placeholder="Enter your email"
                                value="<?= sanitize($formData['email'] ?? '') ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password <span class="required">*</span></label>
                        <div class="input-wrapper password-wrapper">
                            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Create a password"
                                required
                                minlength="8"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <small class="form-help">Minimum 8 characters with uppercase, lowercase, and number</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>
                        <div class="input-wrapper password-wrapper">
                            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-input"
                                placeholder="Confirm your password"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        Create Account
                    </button>
                </form>

                <!-- Footer -->
                <div class="form-footer">
                    Already have an account? <a href="login.php">Sign In</a>
                </div>

            </div>
        </div>
    </div>

    <!-- Dark Mode Script -->
    <script src="../assets/js/dark-mode.js"></script>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const button = passwordInput.parentElement.querySelector('.password-toggle');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                button.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
            } else {
                passwordInput.type = 'password';
                button.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
            }
        }
    </script>
</body>
</html>
