<?php
/**
 * OAuth Callback Handler - QuantrazGG
 * Handles the OAuth redirect from Supabase
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

// Check for error
if (isset($_GET['error'])) {
    $error = $_GET['error_description'] ?? $_GET['error'];
    echo "<script>alert('Authentication failed: " . addslashes($error) . "'); window.location.href='/index.php';</script>";
    exit;
}

// Check for tokens in URL fragment (client-side)
// Supabase returns tokens in the URL hash, which needs client-side handling
?>
<!DOCTYPE html>
<html>
<head>
    <title>Authenticating...</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1A1A2E;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .loader {
            text-align: center;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid #333;
            border-top: 3px solid #00D9FF;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loader">
        <div class="spinner"></div>
        <p>Completing sign in...</p>
    </div>
    
    <script>
        // Handle the OAuth callback
        // Supabase returns tokens in the URL hash
        const hash = window.location.hash.substring(1);
        const params = new URLSearchParams(hash);
        
        const accessToken = params.get('access_token');
        const refreshToken = params.get('refresh_token');
        const error = params.get('error');
        
        if (error) {
            alert('Authentication failed: ' + (params.get('error_description') || error));
            window.location.href = '/index.php';
        } else if (accessToken) {
            // Send tokens to server to establish session
            fetch('/auth/session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    access_token: accessToken,
                    refresh_token: refreshToken
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/menu.php';
                } else {
                    alert('Failed to establish session: ' + data.error);
                    window.location.href = '/index.php';
                }
            })
            .catch(err => {
                console.error('Session error:', err);
                window.location.href = '/index.php';
            });
        } else {
            // No tokens, redirect to login
            window.location.href = '/index.php';
        }
    </script>
</body>
</html>
