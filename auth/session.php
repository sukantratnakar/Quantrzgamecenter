<?php
/**
 * Session Handler - QuantrazGG
 * Establishes server-side session from OAuth tokens
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

header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON body
$input = json_decode(file_get_contents('php://input'), true);
$accessToken = $input['access_token'] ?? null;
$refreshToken = $input['refresh_token'] ?? null;

if (!$accessToken) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Access token required']);
    exit;
}

require_once __DIR__ . '/../lib/SupabaseAuth.php';

try {
    $auth = new SupabaseAuth();
    
    // Get user info from token
    $supabaseUrl = getenv('SUPABASE_URL');
    $anonKey = getenv('SUPABASE_ANON_KEY');
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $supabaseUrl . '/auth/v1/user');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $anonKey,
        'Authorization: Bearer ' . $accessToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid token']);
        exit;
    }
    
    $user = json_decode($response, true);
    
    // Set session
    session_start();
    $_SESSION['supabase_access_token'] = $accessToken;
    $_SESSION['supabase_refresh_token'] = $refreshToken;
    $_SESSION['supabase_expires_at'] = time() + 3600;
    $_SESSION['supabase_user'] = $user;
    $_SESSION['email'] = $user['email']; // Legacy compatibility
    
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['user_metadata']['full_name'] ?? $user['email']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
