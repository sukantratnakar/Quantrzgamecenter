<?php
/**
 * Trainer Dashboard - QuantrazGG
 * For trainers to manage their teams, sessions, and courses
 */
require_once __DIR__ . '/../lib/auth-middleware.php';
$auth->requireTrainer('/unauthorized.php');

require_once __DIR__ . '/../lib/Supabase.php';
$db = new Supabase();

$trainerId = $auth->getUserId();

// Fetch trainer's stats
$stats = [
    'teams' => 0,
    'activeSessions' => 0,
    'totalParticipants' => 0,
    'courses' => 0
];

try {
    // Teams managed by this trainer
    $result = $db->from('teams')->select('id')->eq('trainer_id', $trainerId)->execute();
    $stats['teams'] = count($result['data'] ?? []);
    
    // Active sessions by this trainer
    $result = $db->from('game_sessions')
        ->select('id')
        ->eq('trainer_id', $trainerId)
        ->eq('status', 'active')
        ->execute();
    $stats['activeSessions'] = count($result['data'] ?? []);
    
    // Courses by this trainer
    $result = $db->from('courses')->select('id')->eq('trainer_id', $trainerId)->execute();
    $stats['courses'] = count($result['data'] ?? []);
    
} catch (Exception $e) {
    // Stats will show 0 on error
}

// Fetch trainer's teams
$teams = [];
try {
    $result = $db->from('teams')
        ->select('*')
        ->eq('trainer_id', $trainerId)
        ->order('created_at', false)
        ->execute();
    $teams = $result['data'] ?? [];
} catch (Exception $e) {}

// Fetch available games
$games = [];
try {
    $result = $db->from('games')
        ->select('*')
        ->eq('is_active', 'true')
        ->execute();
    $games = $result['data'] ?? [];
} catch (Exception $e) {}

// Fetch active sessions
$activeSessions = [];
try {
    $result = $db->from('game_sessions')
        ->select('*')
        ->eq('trainer_id', $trainerId)
        ->in('status', ['active', 'pending'])
        ->order('created_at', false)
        ->limit(5)
        ->execute();
    $activeSessions = $result['data'] ?? [];
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard - QuantrazGG</title>
    <link rel="stylesheet" href="/styles/design-system.css">
    <link rel="icon" href="/images/tit-logo.svg">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: var(--bg-primary); color: white; font-family: var(--font-body); min-height: 100vh; }
        
        /* Header */
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: var(--bg-secondary);
            border-bottom: 1px solid rgba(0, 217, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 100;
        }
        
        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header-logo h1 {
            font-family: var(--font-display);
            font-size: 20px;
            color: var(--accent);
        }
        
        .header-logo span {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            padding: 4px 8px;
            background: rgba(0, 217, 255, 0.1);
            border-radius: 4px;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--bg-primary);
        }
        
        .logout-link {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 14px;
        }
        
        .logout-link:hover {
            color: white;
        }
        
        /* Main Content */
        .main-content {
            padding: 88px 24px 24px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .welcome-section {
            margin-bottom: 32px;
        }
        
        .welcome-title {
            font-family: var(--font-display);
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .welcome-subtitle {
            color: rgba(255,255,255,0.6);
        }
        
        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(0, 217, 255, 0.1);
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
            margin-bottom: 12px;
        }
        
        .stat-value {
            font-family: var(--font-display);
            font-size: 28px;
            font-weight: 700;
        }
        
        .stat-label {
            font-size: 13px;
            color: rgba(255,255,255,0.6);
            margin-top: 4px;
        }
        
        /* Grid Layout */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        
        @media (max-width: 1200px) {
            .dashboard-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 768px) {
            .dashboard-grid { grid-template-columns: 1fr; }
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
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .card-title {
            font-size: 15px;
            font-weight: 600;
        }
        
        .card-link {
            font-size: 13px;
            color: var(--accent);
            text-decoration: none;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Team List */
        .team-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .team-item:last-child {
            border-bottom: none;
        }
        
        .team-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .team-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(99, 102, 241, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        
        .team-name {
            font-weight: 500;
        }
        
        .team-bank {
            font-size: 14px;
            color: var(--accent);
        }
        
        /* Game Buttons */
        .game-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .game-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 16px;
            background: rgba(0, 217, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.2);
            border-radius: 10px;
            color: white;
            text-decoration: none;
            transition: all 0.2s;
            text-align: center;
        }
        
        .game-btn:hover {
            background: rgba(0, 217, 255, 0.1);
            border-color: var(--accent);
            transform: translateY(-2px);
        }
        
        .game-btn-icon {
            font-size: 24px;
        }
        
        .game-btn-name {
            font-size: 13px;
            font-weight: 500;
        }
        
        /* Session Item */
        .session-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .session-item:last-child {
            border-bottom: none;
        }
        
        .session-status {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        
        .session-status.active {
            background: #22c55e;
            box-shadow: 0 0 8px rgba(34, 197, 94, 0.5);
        }
        
        .session-status.pending {
            background: #f59e0b;
        }
        
        .session-info {
            flex: 1;
        }
        
        .session-id {
            font-size: 13px;
            font-weight: 500;
        }
        
        .session-time {
            font-size: 11px;
            color: rgba(255,255,255,0.5);
        }
        
        .session-action {
            padding: 6px 12px;
            background: var(--accent);
            color: var(--bg-primary);
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 32px 16px;
            color: rgba(255,255,255,0.5);
        }
        
        .empty-state svg {
            width: 48px;
            height: 48px;
            margin-bottom: 12px;
            opacity: 0.3;
        }
        
        /* Quick Start Button */
        .quick-start-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: var(--bg-primary);
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .quick-start-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 217, 255, 0.3);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="top-header">
        <div class="header-logo">
            <h1>QuantrazGG</h1>
            <span>Trainer</span>
        </div>
        <div class="header-actions">
            <a href="/menu.php" class="logout-link">← Game Center</a>
            <div class="user-menu">
                <div class="user-avatar"><?php echo strtoupper(substr($displayName, 0, 1)); ?></div>
            </div>
            <a href="/auth/logout.php" class="logout-link">Sign Out</a>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars(explode(' ', $displayName)[0]); ?>!</h1>
            <p class="welcome-subtitle">Manage your teams, start sessions, and track progress</p>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                </div>
                <div class="stat-value"><?php echo $stats['teams']; ?></div>
                <div class="stat-label">My Teams</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polygon points="10 8 16 12 10 16 10 8"/>
                    </svg>
                </div>
                <div class="stat-value"><?php echo $stats['activeSessions']; ?></div>
                <div class="stat-label">Active Sessions</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </div>
                <div class="stat-value"><?php echo $stats['courses']; ?></div>
                <div class="stat-label">My Courses</div>
            </div>
        </div>
        
        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- My Teams -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">My Teams</h2>
                    <a href="/trainer/teams.php" class="card-link">Manage →</a>
                </div>
                <div class="card-body">
                    <?php if (empty($teams)): ?>
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                            </svg>
                            <p>No teams yet</p>
                            <a href="/trainer/teams.php?action=add" class="session-action" style="margin-top: 12px; display: inline-block;">Create Team</a>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($teams, 0, 4) as $team): ?>
                            <div class="team-item">
                                <div class="team-info">
                                    <div class="team-icon">👥</div>
                                    <span class="team-name"><?php echo htmlspecialchars($team['name']); ?></span>
                                </div>
                                <span class="team-bank">$<?php echo number_format($team['bank_balance']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Start Game -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Start a Game</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($games)): ?>
                        <div class="empty-state">
                            <p>No games available</p>
                        </div>
                    <?php else: ?>
                        <div class="game-grid">
                            <?php 
                            $icons = ['wheel' => '🎰', 'quiz' => '❓', 'auction' => '💰', 'simulation' => '📊', 'custom' => '🎮'];
                            foreach (array_slice($games, 0, 4) as $game): 
                                $icon = $icons[$game['category']] ?? '🎮';
                            ?>
                                <a href="/trainer/session.php?game=<?php echo $game['id']; ?>" class="game-btn">
                                    <span class="game-btn-icon"><?php echo $icon; ?></span>
                                    <span class="game-btn-name"><?php echo htmlspecialchars($game['name']); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Active Sessions -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Active Sessions</h2>
                    <a href="/trainer/sessions.php" class="card-link">View All →</a>
                </div>
                <div class="card-body">
                    <?php if (empty($activeSessions)): ?>
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polygon points="10 8 16 12 10 16 10 8"/>
                            </svg>
                            <p>No active sessions</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activeSessions as $session): ?>
                            <div class="session-item">
                                <span class="session-status <?php echo $session['status']; ?>"></span>
                                <div class="session-info">
                                    <div class="session-id">Session #<?php echo substr($session['id'], 0, 8); ?></div>
                                    <div class="session-time"><?php echo date('M j, H:i', strtotime($session['created_at'])); ?></div>
                                </div>
                                <a href="/trainer/session.php?id=<?php echo $session['id']; ?>" class="session-action">
                                    <?php echo $session['status'] === 'active' ? 'Resume' : 'Start'; ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Start -->
        <div style="margin-top: 32px;">
            <a href="/trainer/session.php?quick=true" class="quick-start-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="5 3 19 12 5 21 5 3"/>
                </svg>
                Quick Start Session
            </a>
        </div>
    </main>
</body>
</html>
