<?php
/**
 * Google OAuth Handler - QuantrazGG
 * Redirects to Supabase Google OAuth flow
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
    
    // Build the OAuth URL with redirect
    $redirectTo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
                  . '://' . $_SERVER['HTTP_HOST'] . '/auth/callback.php';
    
    $authUrl = $auth->getGoogleAuthUrl($redirectTo);
    
    // Redirect to Supabase Google OAuth
    header('Location: ' . $authUrl);
    exit;
    
} catch (Exception $e) {
    echo "<script>alert('Google sign-in unavailable: " . addslashes($e->getMessage()) . "'); window.location.href='/index.php';</script>";
    exit;
}
