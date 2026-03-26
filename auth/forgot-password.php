<?php
/**
 * Forgot Password - QuantrazGG
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

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../lib/SupabaseAuth.php';
    
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $message = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address';
    } else {
        try {
            $auth = new SupabaseAuth();
            $redirectTo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
                          . '://' . $_SERVER['HTTP_HOST'] . '/auth/reset-password.php';
            
            $result = $auth->resetPassword($email, $redirectTo);
            
            if ($result['success']) {
                $success = true;
                $message = 'If an account exists with this email, you will receive a password reset link.';
            } else {
                $message = $result['error'];
            }
        } catch (Exception $e) {
            $message = 'Service temporarily unavailable. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password - Quantraz Game Center</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styles/design-system.css">
    <link rel="stylesheet" href="/styles/form.css">
    <link rel="icon" href="/images/tit-logo.svg" type="image/png">
    <style>
        body {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .form-container {
            max-width: 400px;
            width: 100%;
            padding: 40px;
            background: rgba(26, 26, 46, 0.95);
            border-radius: 16px;
            border: 1px solid rgba(0, 217, 255, 0.2);
        }
        
        .form-title {
            text-align: center;
            color: white;
            margin-bottom: 8px;
            font-size: 24px;
        }
        
        .form-subtitle {
            text-align: center;
            color: rgba(255,255,255,0.6);
            margin-bottom: 24px;
            font-size: 14px;
        }
        
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .message.error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
        }
        
        .message.success {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.5);
            color: #86efac;
        }
        
        .submit-btn {
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
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 217, 255, 0.3);
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: var(--accent);
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1 class="form-title">Reset Password</h1>
        <p class="form-subtitle">Enter your email to receive a password reset link</p>
        
        <?php if ($message): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
            <form method="post">
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder=" " required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <label for="email">Email Address</label>
                </div>
                
                <button type="submit" class="submit-btn">Send Reset Link</button>
            </form>
        <?php endif; ?>
        
        <a href="/index.php" class="back-link">← Back to Sign In</a>
    </div>
</body>
</html>
