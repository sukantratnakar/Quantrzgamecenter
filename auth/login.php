<?php
/**
 * Login Handler - QuantrazGG
 * Handles email/password login via Supabase Auth
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
    
    // Validate input
    if (empty($email) || empty($password)) {
        respondError('Email and password are required');
    }
    
    try {
        $auth = new SupabaseAuth();
        $result = $auth->signIn($email, $password);
        
        if ($result['success']) {
            // Check if AJAX request
            if (isAjax()) {
                respondJson([
                    'success' => true,
                    'message' => 'Login successful',
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
        respondError('Authentication service unavailable: ' . $e->getMessage());
    }
}

// Handle GET request (show form or return status)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once __DIR__ . '/../lib/SupabaseAuth.php';
    $auth = new SupabaseAuth();
    
    // If already authenticated, redirect
    if ($auth->isAuthenticated()) {
        header('Location: /menu.php');
        exit;
    }
    
    // Show login page
    header('Location: /index.php');
    exit;
}

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
