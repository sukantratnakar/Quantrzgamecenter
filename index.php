<?php
/**
 * QuantrazGG - Login/Signup Page
 * Integrated with Supabase Auth
 */

// Load environment
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

require_once __DIR__ . '/lib/SupabaseAuth.php';

// Check if already authenticated
try {
    $auth = new SupabaseAuth();
    if ($auth->isAuthenticated()) {
        header('Location: /menu.php');
        exit;
    }
} catch (Exception $e) {
    // Auth not configured, continue to show login
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Quantraz Game Center - Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/design-system.css">
    <link rel="stylesheet" href="styles/form.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="icon" href="images/tit-logo.svg" type="image/png">
    <style>
        body {
            background-image: url('images/bg.svg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        
        /* Google Sign-In Button */
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 12px 16px;
            margin-bottom: 20px;
            background: white;
            border: 1px solid #dadce0;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #3c4043;
            cursor: pointer;
            transition: background-color 0.2s, box-shadow 0.2s;
        }
        
        .google-btn:hover {
            background: #f8f9fa;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .google-btn img {
            width: 18px;
            height: 18px;
        }
        
        /* Divider */
        .auth-divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: rgba(255,255,255,0.6);
            font-size: 12px;
        }
        
        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.2);
        }
        
        .auth-divider span {
            padding: 0 12px;
        }
        
        /* Password Toggle */
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255,255,255,0.6);
            cursor: pointer;
            padding: 4px;
            font-size: 12px;
        }
        
        .password-toggle:hover {
            color: white;
        }
        
        /* Form improvements */
        .form-container {
            max-width: 400px;
            margin: 40px auto;
            padding: 30px;
            background: rgba(26, 26, 46, 0.95);
            border-radius: 16px;
            border: 1px solid rgba(0, 217, 255, 0.2);
        }
        
        .form-title {
            text-align: center;
            color: white;
            margin-bottom: 24px;
            font-size: 24px;
        }
        
        .submit-btn, .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            border: none;
            border-radius: 8px;
            color: var(--bg-primary);
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .submit-btn:hover, .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 217, 255, 0.3);
        }
        
        .form-footer {
            text-align: center;
            margin-top: 16px;
            color: rgba(255,255,255,0.6);
            font-size: 14px;
        }
        
        .form-footer a {
            color: var(--accent);
            text-decoration: none;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        /* Error message */
        .error-message {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
            display: none;
        }
        
        /* Hide signup form by default */
        .signup-form {
            display: none;
        }
    </style>
</head>
<body>
    <header>
        <h1 style="color: #CBC493;">Quantraz Game Center</h1>
    </header>

    <section class="form-container">
        <!-- Error Message -->
        <div class="error-message" id="error-message"></div>
        
        <!-- Login Form (Default) -->
        <form class="login-form" action="/auth/login.php" method="post" id="login-form">
            <h2 class="form-title">Welcome Back</h2>
            
            <!-- Google Sign-In -->
            <a href="/auth/google.php" class="google-btn">
                <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" fill="#4285F4"/>
                    <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z" fill="#34A853"/>
                    <path d="M3.964 10.71A5.41 5.41 0 013.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 000 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                    <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 00.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
                </svg>
                Continue with Google
            </a>
            
            <div class="auth-divider">
                <span>or sign in with email</span>
            </div>
            
            <div class="form-group">
                <input type="email" id="login-email" name="email" placeholder=" " required>
                <label for="login-email">Email</label>
            </div>
            
            <div class="form-group password-wrapper">
                <input type="password" id="login-password" name="password" placeholder=" " required>
                <label for="login-password">Password</label>
                <button type="button" class="password-toggle" onclick="togglePassword('login-password', this)">Show</button>
            </div>
            
            <button class="login-btn" type="submit">Sign In</button>
            
            <div class="form-footer">
                <a href="/auth/forgot-password.php">Forgot password?</a>
            </div>
            
            <div class="form-footer" style="margin-top: 24px;">
                Don't have an account? <a href="#" onclick="showSignUpForm(); return false;">Sign up</a>
            </div>
        </form>
        
        <!-- Signup Form -->
        <form class="signup-form" action="/auth/signup.php" method="post" id="signup-form">
            <h2 class="form-title">Create Account</h2>
            
            <!-- Google Sign-In -->
            <a href="/auth/google.php" class="google-btn">
                <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" fill="#4285F4"/>
                    <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z" fill="#34A853"/>
                    <path d="M3.964 10.71A5.41 5.41 0 013.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 000 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                    <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 00.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
                </svg>
                Continue with Google
            </a>
            
            <div class="auth-divider">
                <span>or sign up with email</span>
            </div>
            
            <div class="form-group">
                <input type="email" id="signup-email" name="email" placeholder=" " required>
                <label for="signup-email">Email</label>
            </div>
            
            <div class="form-group">
                <input type="text" id="signup-username" name="username" placeholder=" " required>
                <label for="signup-username">Display Name</label>
            </div>
            
            <div class="form-group password-wrapper">
                <input type="password" id="signup-password" name="password" placeholder=" " required minlength="8">
                <label for="signup-password">Password (min 8 characters)</label>
                <button type="button" class="password-toggle" onclick="togglePassword('signup-password', this)">Show</button>
            </div>
            
            <div class="form-group password-wrapper">
                <input type="password" id="signup-confirm" name="confirm_password" placeholder=" " required>
                <label for="signup-confirm">Confirm Password</label>
                <button type="button" class="password-toggle" onclick="togglePassword('signup-confirm', this)">Show</button>
            </div>
            
            <div class="terms-group" style="margin-bottom: 16px;">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms" style="color: white; font-size: 14px;">
                    I agree to the <a href="/terms" style="color: var(--accent);">Terms of Service</a>
                </label>
            </div>
            
            <button class="submit-btn" type="submit">Create Account</button>
            
            <div class="form-footer" style="margin-top: 24px;">
                Already have an account? <a href="#" onclick="showLoginForm(); return false;">Sign in</a>
            </div>
        </form>
    </section>

    <footer style="text-align: center; padding: 20px; color: rgba(255,255,255,0.4);">
        <p>© 2026 Quantraz Inc. All rights reserved.</p>
    </footer>

    <script>
        function showSignUpForm() {
            document.querySelector('.signup-form').style.display = 'block';
            document.querySelector('.login-form').style.display = 'none';
            document.getElementById('error-message').style.display = 'none';
        }

        function showLoginForm() {
            document.querySelector('.signup-form').style.display = 'none';
            document.querySelector('.login-form').style.display = 'block';
            document.getElementById('error-message').style.display = 'none';
        }
        
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = 'Hide';
            } else {
                input.type = 'password';
                button.textContent = 'Show';
            }
        }
        
        // Password match validation
        document.getElementById('signup-form').addEventListener('submit', function(e) {
            const password = document.getElementById('signup-password').value;
            const confirm = document.getElementById('signup-confirm').value;
            
            if (password !== confirm) {
                e.preventDefault();
                showError('Passwords do not match');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                showError('Password must be at least 8 characters');
                return false;
            }
        });
        
        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
        
        // Check for error in URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('error')) {
            showError(urlParams.get('error'));
        }
        
        // Check for signup flag
        if (urlParams.get('signup') === 'true') {
            showSignUpForm();
        }
    </script>
</body>
</html>
