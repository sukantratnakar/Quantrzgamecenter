<?php
/**
 * Game Selection Menu - QuantrazGG
 * Protected page - requires authentication
 */
require_once __DIR__ . '/lib/auth-middleware.php';

// $auth, $currentUser, $userEmail, $userRole are now available
$displayName = $currentUser['user_metadata']['display_name'] ?? 
               $currentUser['user_metadata']['full_name'] ?? 
               explode('@', $userEmail)[0];
?>
<script>
    var userEmail = "<?php echo htmlspecialchars($userEmail); ?>";
    var userName = "<?php echo htmlspecialchars($displayName); ?>";
    var userRole = "<?php echo htmlspecialchars($userRole); ?>";
</script>
<?php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/tit-logo.svg" type="image/png">
    <title>Quantraz Game Center</title>
    <link rel="stylesheet" href="styles/design-system.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            position: relative;
            overflow: hidden;
        }
        
        /* Background pattern */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, var(--accent-subtle) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, var(--accent-subtle) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: var(--space-8);
        }
        
        .logo-container {
            margin-bottom: var(--space-12);
        }
        
        .logo-container img {
            max-width: 500px;
            width: 100%;
            height: auto;
            filter: drop-shadow(0 0 30px var(--accent-glow));
        }
        
        .title {
            font-family: var(--font-display);
            font-size: var(--text-4xl);
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--space-2);
            text-transform: uppercase;
            letter-spacing: 4px;
        }
        
        .subtitle {
            font-size: var(--text-lg);
            color: var(--text-muted);
            margin-bottom: var(--space-12);
        }
        
        .button-container {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-6);
            justify-content: center;
        }
        
        .game-button {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: var(--space-8) var(--space-10);
            min-width: 280px;
            background: var(--bg-card);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            cursor: pointer;
            transition: all var(--transition-base);
            text-decoration: none;
        }
        
        .game-button:hover {
            background: var(--bg-card-hover);
            border-color: var(--accent);
            transform: translateY(-4px);
            box-shadow: var(--shadow-glow);
        }
        
        .game-button-icon {
            font-size: 48px;
            margin-bottom: var(--space-4);
        }
        
        .game-button-title {
            font-family: var(--font-display);
            font-size: var(--text-xl);
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--space-2);
        }
        
        .game-button-desc {
            font-size: var(--text-sm);
            color: var(--text-muted);
        }
        
        .footer {
            position: fixed;
            bottom: var(--space-6);
            left: 0;
            right: 0;
            text-align: center;
            color: var(--text-muted);
            font-size: var(--text-sm);
        }
        
        .footer a {
            color: var(--accent);
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .logo-container img {
                max-width: 300px;
            }
            
            .title {
                font-size: var(--text-2xl);
                letter-spacing: 2px;
            }
            
            .game-button {
                min-width: 100%;
                padding: var(--space-6);
            }
            
            .button-container {
                padding: 0 var(--space-4);
            }
        }
    </style>
</head>
<body>
    <!-- User Header -->
    <div class="user-header">
        <div class="user-info">
            <span class="user-avatar"><?php echo strtoupper(substr($displayName, 0, 1)); ?></span>
            <span class="user-name"><?php echo htmlspecialchars($displayName); ?></span>
            <?php if ($userRole !== 'player'): ?>
                <span class="user-badge"><?php echo ucfirst($userRole); ?></span>
            <?php endif; ?>
        </div>
        <a href="/auth/logout.php" class="logout-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Sign Out
        </a>
    </div>
    
    <style>
        .user-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
            background: rgba(26, 26, 46, 0.95);
            border-bottom: 1px solid rgba(0, 217, 255, 0.2);
            z-index: 100;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: var(--bg-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }
        
        .user-name {
            color: white;
            font-weight: 500;
        }
        
        .user-badge {
            padding: 4px 8px;
            background: rgba(0, 217, 255, 0.2);
            color: var(--accent);
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            color: white;
        }
        
        /* Adjust body to account for fixed header */
        body {
            padding-top: 70px !important;
        }
    </style>
    
    <div class="container">
        <div class="logo-container">
            <img src="images/Game_Center_White_Logo.png" alt="Quantraz Game Center">
        </div>
        
        <h1 class="title">Select Your Game</h1>
        <p class="subtitle">Choose a game mode to begin your journey</p>
        
        <div class="button-container">
            <button class="game-button" onclick="move(1)">
                <span class="game-button-icon">🎯</span>
                <span class="game-button-title">Understanding Tinergy</span>
                <span class="game-button-desc">Learn the fundamentals</span>
            </button>
            
            <button class="game-button" onclick="move(2)">
                <span class="game-button-icon">🎰</span>
                <span class="game-button-title">Classic Game</span>
                <span class="game-button-desc">Standard roulette experience</span>
            </button>
        </div>
    </div>
    
    <div class="footer">
        Powered by <a href="https://quantraz.com" target="_blank">Quantraz Inc</a>
    </div>
    
    <script>
        function move(where){
            console.log("moved");
            switch (where) {
                case 1:
                    window.location.href = 'testmenu.php?type=';
                    break;
                case 2:
                    window.location.href = 'testmenu.php?type=standard';
                    break;
            }
        }
    </script>
</body>
</html>
