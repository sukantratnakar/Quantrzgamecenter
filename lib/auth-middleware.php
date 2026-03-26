<?php
/**
 * Auth Middleware - Include at top of protected pages
 * 
 * Usage:
 *   require_once __DIR__ . '/lib/auth-middleware.php';
 *   // $auth is now available with the authenticated user
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

require_once __DIR__ . '/SupabaseAuth.php';

// Initialize auth
$auth = new SupabaseAuth();

// Check authentication
if (!$auth->isAuthenticated()) {
    // Not authenticated - redirect to login
    header('Location: /index.php');
    exit;
}

// User is authenticated - $auth is available for use
// Get current user
$currentUser = $auth->getUser();
$userEmail = $auth->getEmail();
$userId = $auth->getUserId();
$userRole = $auth->getRole();

// Legacy compatibility - set session email
if (!isset($_SESSION['email']) && $userEmail) {
    $_SESSION['email'] = $userEmail;
}
