<?php
/**
 * Master Admin Dashboard - QuantrazGG
 * Overview with stats, quick actions, and navigation
 */
require_once __DIR__ . '/../lib/auth-middleware.php';

// Require admin role
$auth->requireAdmin('/unauthorized.php');

// Get Supabase client for data
require_once __DIR__ . '/../lib/Supabase.php';
$db = new Supabase();

// Fetch dashboard stats
$stats = [
    'users' => 0,
    'teams' => 0,
    'games' => 0,
    'sessions' => 0,
    'activeToday' => 0
];

try {
    // Count users (from profiles)
    $result = $db->from('profiles')->select('id')->execute();
    $stats['users'] = count($result['data'] ?? []);
    
    // Count teams
    $result = $db->from('teams')->select('id')->execute();
    $stats['teams'] = count($result['data'] ?? []);
    
    // Count active games
    $result = $db->from('games')->select('id')->eq('is_active', 'true')->execute();
    $stats['games'] = count($result['data'] ?? []);
    
    // Count game sessions
    $result = $db->from('game_sessions')->select('id')->execute();
    $stats['sessions'] = count($result['data'] ?? []);
    
    // Active sessions today
    $today = date('Y-m-d');
    $result = $db->from('game_sessions')->select('id')->gte('created_at', $today)->execute();
    $stats['activeToday'] = count($result['data'] ?? []);
    
} catch (Exception $e) {
    // Stats will show 0 if error
}

// Recent activity
$recentSessions = [];
try {
    $result = $db->from('game_sessions')
        ->select('id,status,created_at,game_id')
        ->order('created_at', false)
        ->limit(5)
        ->execute();
    $recentSessions = $result['data'] ?? [];
} catch (Exception $e) {
    // Empty array on error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - QuantrazGG</title>
    <link rel="stylesheet" href="/styles/design-system.css">
    <link rel="icon" href="/images/tit-logo.svg" type="image/png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: var(--bg-primary);
            color: white;
            font-family: var(--font-body);
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 260px;
            background: var(--bg-secondary);
            border-right: 1px solid rgba(0, 217, 255, 0.1);
            padding: 24px 0;
            overflow-y: auto;
        }
        
        .sidebar-logo {
            padding: 0 24px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 24px;
        }
        
        .sidebar-logo h1 {
            font-family: var(--font-display);
            font-size: 20px;
            color: var(--accent);
        }
        
        .sidebar-logo span {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
        }
        
        .nav-section {
            padding: 0 12px;
            margin-bottom: 24px;
        }
        
        .nav-section-title {
            font-size: 11px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            padding: 0 12px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 14px;
        }
        
        .nav-item:hover {
            background: rgba(0, 217, 255, 0.1);
            color: white;
        }
        
        .nav-item.active {
            background: rgba(0, 217, 255, 0.2);
            color: var(--accent);
        }
        
        .nav-item svg {
            width: 20px;
            height: 20px;
            opacity: 0.7;
        }
        
        .nav-item.active svg {
            opacity: 1;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 24px;
            min-height: 100vh;
        }
        
        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        
        .page-title {
            font-family: var(--font-display);
            font-size: 28px;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--bg-primary);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 24px;
            border: 1px solid rgba(0, 217, 255, 0.1);
        }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(0, 217, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
        }
        
        .stat-value {
            font-family: var(--font-display);
            font-size: 32px;
            font-weight: 700;
            color: white;
        }
        
        .stat-label {
            font-size: 14px;
            color: rgba(255,255,255,0.6);
            margin-top: 4px;
        }
        
        .stat-change {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .stat-change.positive {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
        }
        
        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }
        
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Cards */
        .card {
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid rgba(0, 217, 255, 0.1);
            overflow: hidden;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .card-title {
            font-size: 16px;
            font-weight: 600;
        }
        
        .card-body {
            padding: 24px;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 20px;
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.2);
            border-radius: 10px;
            color: white;
            text-decoration: none;
            transition: all 0.2s;
            text-align: center;
        }
        
        .action-btn:hover {
            background: rgba(0, 217, 255, 0.1);
            border-color: var(--accent);
            transform: translateY(-2px);
        }
        
        .action-btn svg {
            width: 24px;
            height: 24px;
            color: var(--accent);
        }
        
        .action-btn span {
            font-size: 13px;
        }
        
        /* Activity List */
        .activity-list {
            list-style: none;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
        }
        
        .activity-dot.completed {
            background: #22c55e;
        }
        
        .activity-dot.pending {
            background: #f59e0b;
        }
        
        .activity-text {
            flex: 1;
            font-size: 14px;
            color: rgba(255,255,255,0.8);
        }
        
        .activity-time {
            font-size: 12px;
            color: rgba(255,255,255,0.4);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <h1>QuantrazGG</h1>
            <span>Admin Dashboard</span>
        </div>
        
        <nav class="nav-section">
            <div class="nav-section-title">Overview</div>
            <a href="/admin/" class="nav-item active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/>
                    <rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/>
                </svg>
                Dashboard
            </a>
        </nav>
        
        <nav class="nav-section">
            <div class="nav-section-title">Management</div>
            <a href="/admin/users.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Users
            </a>
            <a href="/admin/teams.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <circle cx="19" cy="11" r="2"/>
                    <path d="M19 8v1"/>
                    <path d="M19 13v1"/>
                </svg>
                Teams
            </a>
            <a href="/admin/games.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M8 12h8"/>
                    <path d="M12 8v8"/>
                </svg>
                Games
            </a>
            <a href="/admin/sessions.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4"/>
                    <path d="M12 18v4"/>
                    <path d="M4.93 4.93l2.83 2.83"/>
                    <path d="M16.24 16.24l2.83 2.83"/>
                    <path d="M2 12h4"/>
                    <path d="M18 12h4"/>
                    <path d="M4.93 19.07l2.83-2.83"/>
                    <path d="M16.24 7.76l2.83-2.83"/>
                </svg>
                Sessions
            </a>
        </nav>
        
        <nav class="nav-section">
            <div class="nav-section-title">Academy</div>
            <a href="/admin/courses.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
                Courses
            </a>
            <a href="/admin/trainers.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
                Trainers
            </a>
        </nav>
        
        <nav class="nav-section">
            <div class="nav-section-title">System</div>
            <a href="/admin/settings.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                Settings
            </a>
            <a href="/auth/logout.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Sign Out
            </a>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="page-header">
            <h1 class="page-title">Dashboard</h1>
            <div class="user-menu">
                <span style="color: rgba(255,255,255,0.6);"><?php echo htmlspecialchars($userEmail); ?></span>
                <div class="user-avatar"><?php echo strtoupper(substr($displayName, 0, 1)); ?></div>
            </div>
        </header>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($stats['users']); ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <circle cx="19" cy="11" r="2"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($stats['teams']); ?></div>
                <div class="stat-label">Teams</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 12h8"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($stats['games']); ?></div>
                <div class="stat-label">Active Games</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83"/>
                        </svg>
                    </div>
                    <span class="stat-change positive">+<?php echo $stats['activeToday']; ?> today</span>
                </div>
                <div class="stat-value"><?php echo number_format($stats['sessions']); ?></div>
                <div class="stat-label">Game Sessions</div>
            </div>
        </div>
        
        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Sessions</h2>
                    <a href="/admin/sessions.php" style="color: var(--accent); font-size: 14px; text-decoration: none;">View All →</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentSessions)): ?>
                        <p style="color: rgba(255,255,255,0.5); text-align: center; padding: 20px;">No sessions yet</p>
                    <?php else: ?>
                        <ul class="activity-list">
                            <?php foreach ($recentSessions as $session): ?>
                                <li class="activity-item">
                                    <span class="activity-dot <?php echo $session['status']; ?>"></span>
                                    <span class="activity-text">
                                        Session #<?php echo substr($session['id'], 0, 8); ?> 
                                        <span style="color: rgba(255,255,255,0.5);">(<?php echo ucfirst($session['status']); ?>)</span>
                                    </span>
                                    <span class="activity-time"><?php echo date('M j, H:i', strtotime($session['created_at'])); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="/admin/users.php?action=add" class="action-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="8.5" cy="7" r="4"/>
                                <line x1="20" y1="8" x2="20" y2="14"/>
                                <line x1="23" y1="11" x2="17" y2="11"/>
                            </svg>
                            <span>Add User</span>
                        </a>
                        <a href="/admin/teams.php?action=add" class="action-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <line x1="23" y1="11" x2="17" y2="11"/>
                                <line x1="20" y1="8" x2="20" y2="14"/>
                            </svg>
                            <span>Create Team</span>
                        </a>
                        <a href="/admin/sessions.php?action=new" class="action-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polygon points="10 8 16 12 10 16 10 8"/>
                            </svg>
                            <span>Start Session</span>
                        </a>
                        <a href="/admin/games.php" class="action-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33"/>
                            </svg>
                            <span>Manage Games</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
