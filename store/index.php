<?php
/**
 * Team Store - QuantrazGG
 * Browse products and redeem bank balance for discounts
 */
require_once __DIR__ . '/../lib/auth-middleware.php';

// Get user's team info
require_once __DIR__ . '/../lib/Supabase.php';
$db = new Supabase();

$teamInfo = null;
$message = '';
$messageType = '';

// Get user's primary team
try {
    $membership = $db->from('team_members')
        ->select('team_id')
        ->eq('user_id', $userId)
        ->limit(1)
        ->execute();
    
    if (!empty($membership['data'])) {
        $teamId = $membership['data'][0]['team_id'];
        $team = $db->from('teams')
            ->select('*')
            ->eq('id', $teamId)
            ->single()
            ->execute();
        $teamInfo = $team['data'] ?? null;
    }
} catch (Exception $e) {}

// Handle redemption
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem'])) {
    $redeemAmount = (int)($_POST['amount'] ?? 0);
    
    if (!$teamInfo) {
        $message = 'You must be part of a team to redeem rewards';
        $messageType = 'error';
    } elseif ($redeemAmount < 10) {
        $message = 'Minimum redemption is $10';
        $messageType = 'error';
    } elseif ($redeemAmount > ($teamInfo['bank_balance'] ?? 0)) {
        $message = 'Insufficient bank balance';
        $messageType = 'error';
    } else {
        try {
            require_once __DIR__ . '/../lib/ShopifyIntegration.php';
            
            $shopify = new ShopifyIntegration();
            $tracker = new RedemptionTracker($db);
            
            // Create discount code
            $discount = $shopify->createTeamDiscount($teamInfo['id'], $redeemAmount);
            
            // Record redemption
            $result = $tracker->recordRedemption(
                $teamInfo['id'],
                $redeemAmount,
                $discount['code'],
                $discount['price_rule_id']
            );
            
            $message = "Success! Your discount code is: <strong>{$discount['code']}</strong><br>Use it at {$discount['shop_url']}";
            $messageType = 'success';
            
            // Refresh team info
            $team = $db->from('teams')->select('*')->eq('id', $teamInfo['id'])->single()->execute();
            $teamInfo = $team['data'] ?? $teamInfo;
            
        } catch (Exception $e) {
            $message = 'Failed to create discount: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Try to load products (gracefully fail if Shopify not configured)
$products = [];
try {
    require_once __DIR__ . '/../lib/ShopifyIntegration.php';
    $shopify = new ShopifyIntegration();
    $products = $shopify->getProducts(8);
} catch (Exception $e) {
    // Shopify not configured - show placeholder
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Store - QuantrazGG</title>
    <link rel="stylesheet" href="/styles/design-system.css">
    <link rel="icon" href="/images/tit-logo.svg">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: var(--bg-primary); color: white; font-family: var(--font-body); min-height: 100vh; }
        
        .header {
            background: var(--bg-secondary);
            border-bottom: 1px solid rgba(0, 217, 255, 0.1);
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-title {
            font-family: var(--font-display);
            font-size: 20px;
            color: var(--accent);
        }
        
        .header-nav {
            display: flex;
            gap: 16px;
        }
        
        .header-nav a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
        }
        
        .header-nav a:hover {
            color: white;
        }
        
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 24px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 24px;
        }
        
        .page-title {
            font-family: var(--font-display);
            font-size: 32px;
            margin-bottom: 8px;
        }
        
        .page-subtitle {
            color: rgba(255,255,255,0.6);
        }
        
        /* Bank Card */
        .bank-card {
            background: linear-gradient(135deg, var(--bg-card), var(--bg-secondary));
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(0, 217, 255, 0.2);
            min-width: 280px;
        }
        
        .bank-label {
            font-size: 12px;
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .bank-balance {
            font-family: var(--font-display);
            font-size: 36px;
            color: var(--accent);
            margin-bottom: 16px;
        }
        
        .team-name {
            font-size: 14px;
            color: rgba(255,255,255,0.8);
        }
        
        /* Redeem Section */
        .redeem-section {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
            border: 1px solid rgba(0, 217, 255, 0.1);
        }
        
        .redeem-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .redeem-form {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .redeem-input {
            flex: 1;
            min-width: 150px;
            padding: 12px 16px;
            background: var(--bg-input);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: white;
            font-size: 16px;
        }
        
        .redeem-btn {
            padding: 12px 32px;
            background: var(--accent);
            color: var(--bg-primary);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .redeem-btn:hover {
            background: var(--accent-soft);
            transform: translateY(-2px);
        }
        
        .redeem-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .redeem-note {
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            margin-top: 12px;
        }
        
        /* Message */
        .message {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        
        .message.success {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.5);
            color: #86efac;
        }
        
        .message.error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
        }
        
        /* Products Grid */
        .products-section {
            margin-top: 48px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 24px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 24px;
        }
        
        .product-card {
            background: var(--bg-card);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(0, 217, 255, 0.1);
            transition: all 0.2s;
        }
        
        .product-card:hover {
            transform: translateY(-4px);
            border-color: rgba(0, 217, 255, 0.3);
        }
        
        .product-image {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            background: rgba(0,0,0,0.2);
        }
        
        .product-info {
            padding: 16px;
        }
        
        .product-name {
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .product-price {
            color: var(--accent);
            font-size: 18px;
            font-weight: 700;
        }
        
        /* Placeholder */
        .placeholder-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 24px;
        }
        
        .placeholder-card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            border: 1px dashed rgba(255,255,255,0.2);
        }
        
        .placeholder-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .placeholder-text {
            color: rgba(255,255,255,0.5);
        }
        
        /* No Team State */
        .no-team {
            text-align: center;
            padding: 60px 20px;
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid rgba(0, 217, 255, 0.1);
        }
        
        .no-team-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .no-team-title {
            font-size: 20px;
            margin-bottom: 8px;
        }
        
        .no-team-text {
            color: rgba(255,255,255,0.6);
            margin-bottom: 24px;
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
        }
    </style>
</head>
<body>
    <header class="header">
        <h1 class="header-title">🏪 Team Store</h1>
        <nav class="header-nav">
            <a href="/menu.php">← Game Center</a>
            <a href="/auth/logout.php">Sign Out</a>
        </nav>
    </header>
    
    <main class="main-content">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!$teamInfo): ?>
            <div class="no-team">
                <div class="no-team-icon">👥</div>
                <h2 class="no-team-title">No Team Found</h2>
                <p class="no-team-text">You need to be part of a team to access the store and redeem rewards.</p>
                <a href="/menu.php" class="btn">Back to Game Center</a>
            </div>
        <?php else: ?>
            <div class="page-header">
                <div>
                    <h1 class="page-title">Redeem Your Rewards</h1>
                    <p class="page-subtitle">Convert your game earnings into real discounts at our store</p>
                </div>
                
                <div class="bank-card">
                    <div class="bank-label">Team Bank Balance</div>
                    <div class="bank-balance">$<?php echo number_format($teamInfo['bank_balance'] ?? 0); ?></div>
                    <div class="team-name">👥 <?php echo htmlspecialchars($teamInfo['name']); ?></div>
                </div>
            </div>
            
            <div class="redeem-section">
                <h2 class="redeem-title">💳 Redeem for Store Credit</h2>
                <form method="post" class="redeem-form">
                    <input type="number" name="amount" class="redeem-input" 
                           placeholder="Enter amount ($)" min="10" 
                           max="<?php echo $teamInfo['bank_balance'] ?? 0; ?>"
                           value="">
                    <button type="submit" name="redeem" class="redeem-btn" 
                            <?php echo ($teamInfo['bank_balance'] ?? 0) < 10 ? 'disabled' : ''; ?>>
                        Generate Discount Code
                    </button>
                </form>
                <p class="redeem-note">
                    Minimum redemption: $10 • Discount codes are valid for 30 days • One-time use only
                </p>
            </div>
            
            <div class="products-section">
                <h2 class="section-title">🛍️ Shop Products</h2>
                
                <?php if (empty($products)): ?>
                    <div class="placeholder-grid">
                        <div class="placeholder-card">
                            <div class="placeholder-icon">📦</div>
                            <p class="placeholder-text">Products will appear here when the store is connected</p>
                        </div>
                        <div class="placeholder-card">
                            <div class="placeholder-icon">🎁</div>
                            <p class="placeholder-text">Redeem your earnings for exclusive merchandise</p>
                        </div>
                        <div class="placeholder-card">
                            <div class="placeholder-icon">🏆</div>
                            <p class="placeholder-text">Top performers get bonus rewards</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): 
                            $image = $product['images'][0]['src'] ?? '';
                            $price = $product['variants'][0]['price'] ?? '0.00';
                        ?>
                            <div class="product-card">
                                <img src="<?php echo htmlspecialchars($image); ?>" 
                                     alt="<?php echo htmlspecialchars($product['title']); ?>"
                                     class="product-image">
                                <div class="product-info">
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['title']); ?></h3>
                                    <div class="product-price">$<?php echo number_format((float)$price, 2); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
