<?php
/**
 * Signup Handler - QuantrazGG
 * Handles new user registration via Supabase Auth
 */

// Load environment
$envFile = __DIR__ . '/../.env';
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

require_once __DIR__ . '/../lib/SupabaseAuth.php';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $displayName = trim($_POST['username'] ?? $_POST['display_name'] ?? '');
    $terms = isset($_POST['terms']);
    
    // Validate input
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($displayName)) {
        $errors[] = 'Display name is required';
    }
    
    if (!$terms) {
        $errors[] = 'You must agree to the terms of service';
    }
    
    if (!empty($errors)) {
        respondError(implode('. ', $errors));
    }
    
    try {
        $auth = new SupabaseAuth();
        
        // Create user with metadata
        $result = $auth->signUp($email, $password, [
            'full_name' => $displayName,
            'display_name' => $displayName,
            'role' => 'player' // Default role
        ]);
        
        if ($result['success']) {
            // Check if AJAX request
            if (isAjax()) {
                respondJson([
                    'success' => true,
                    'message' => 'Account created successfully! Please check your email to verify your account.',
                    'redirect' => '/menu.php'
                ]);
            }
            
            // Regular form submission - redirect
            header('Location: /menu.php');
            exit;
        } else {
            respondError($result['error']);
        }
    } catch (Exception $e) {
        respondError('Registration service unavailable: ' . $e->getMessage());
    }
}

// Handle GET - redirect to index
header('Location: /index.php');
exit;

// Helper functions
function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function respondJson(array $data): void {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function respondError(string $message): void {
    if (isAjax()) {
        http_response_code(400);
        respondJson([
            'success' => false,
            'error' => $message
        ]);
    }
    
    // Regular form - show alert and redirect
    echo "<script>alert('" . addslashes($message) . "'); window.location.href='/index.php';</script>";
    exit;
}
