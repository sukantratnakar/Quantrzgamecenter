<?php
/**
 * Unauthorized Access Page - QuantrazGG
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized - QuantrazGG</title>
    <link rel="stylesheet" href="/styles/design-system.css">
    <link rel="icon" href="/images/tit-logo.svg">
    <style>
        body {
            background: var(--bg-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-body);
        }
        
        .container {
            text-align: center;
            padding: 40px;
        }
        
        .icon {
            width: 80px;
            height: 80px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        
        .icon svg {
            width: 40px;
            height: 40px;
            color: #ef4444;
        }
        
        h1 {
            color: white;
            font-family: var(--font-display);
            font-size: 32px;
            margin-bottom: 12px;
        }
        
        p {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 32px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--accent);
            color: var(--bg-primary);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn:hover {
            background: var(--accent-soft);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
            </svg>
        </div>
        <h1>Access Denied</h1>
        <p>You don't have permission to access this page.<br>Please contact an administrator if you believe this is an error.</p>
        <a href="/menu.php" class="btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/>
            </svg>
            Back to Game Center
        </a>
    </div>
</body>
</html>
