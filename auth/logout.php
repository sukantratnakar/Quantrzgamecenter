<?php
/**
 * Logout Handler - QuantrazGG
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

try {
    $auth = new SupabaseAuth();
    $auth->signOut();
} catch (Exception $e) {
    // Ignore errors, just clear session
    session_start();
    session_destroy();
}

// Redirect to login page
header('Location: /index.php');
exit;
