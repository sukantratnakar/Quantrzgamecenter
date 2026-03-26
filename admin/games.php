<?php
/**
 * Games Management - QuantrazGG Admin
 */
require_once __DIR__ . '/../lib/auth-middleware.php';
$auth->requireAdmin('/unauthorized.php');

require_once __DIR__ . '/../lib/Supabase.php';
$db = new Supabase();

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'toggle_active') {
        $gameId = $_POST['game_id'] ?? '';
        $isActive = $_POST['is_active'] === 'true';
        
        try {
            $db->from('games')
                ->update(['is_active' => !$isActive])
                ->eq('id', $gameId)
                ->execute();
            $message = 'Game status updated';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Failed to update: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
    
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? 'custom';
        
        if ($name && $slug) {
            try {
                $db->from('games')->insert([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'category' => $category,
                    'is_active' => true
                ])->execute();
                $message = 'Game added successfully';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Failed to add game: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Fetch games
$games = [];
try {
    $result = $db->from('games')->select('*')->order('name')->execute();
    $games = $result['data'] ?? [];
} catch (Exception $e) {
    $message = 'Failed to load games';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games Management - QuantrazGG Admin</title>
    <link rel="stylesheet" href="/styles/design-system.css">
    <link rel="icon" href="/images/tit-logo.svg">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: var(--bg-primary); color: white; font-family: var(--font-body); min-height: 100vh; }
        
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 260px; background: var(--bg-secondary); border-right: 1px solid rgba(0, 217, 255, 0.1); padding: 24px 0; }
        .sidebar-logo { padding: 0 24px 24px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 24px; }
        .sidebar-logo h1 { font-family: var(--font-display); font-size: 20px; color: var(--accent); }
        .sidebar-logo span { font-size: 12px; color: rgba(255,255,255,0.5); }
        .nav-section { padding: 0 12px; margin-bottom: 24px; }
        .nav-section-title { font-size: 11px; text-transform: uppercase; color: rgba(255,255,255,0.4); padding: 0 12px; margin-bottom: 8px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 8px; font-size: 14px; }
        .nav-item:hover { background: rgba(0, 217, 255, 0.1); color: white; }
        .nav-item.active { background: rgba(0, 217, 255, 0.2); color: var(--accent); }
        .nav-item svg { width: 20px; height: 20px; }
        
        .main-content { margin-left: 260px; padding: 24px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .page-title { font-family: var(--font-display); font-size: 28px; }
        
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; text-decoration: none; transition: all 0.2s; border: none; }
        .btn-primary { background: var(--accent); color: var(--bg-primary); }
        .btn-primary:hover { background: var(--accent-soft); }
        
        .message { padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; }
        .message.success { background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5); color: #86efac; }
        .message.error { background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5; }
        
        .games-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        
        .game-card { background: var(--bg-card); border-radius: 12px; border: 1px solid rgba(0, 217, 255, 0.1); overflow: hidden; }
        .game-card-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: flex-start; }
        .game-icon { width: 48px; height: 48px; border-radius: 12px; background: rgba(0, 217, 255, 0.1); display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .game-status { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .game-status.active { background: rgba(34, 197, 94, 0.2); color: #86efac; }
        .game-status.inactive { background: rgba(239, 68, 68, 0.2); color: #fca5a5; }
        .game-card-body { padding: 20px; }
        .game-name { font-size: 18px; font-weight: 600; margin-bottom: 8px; }
        .game-description { font-size: 14px; color: rgba(255,255,255,0.6); margin-bottom: 16px; }
        .game-meta { display: flex; gap: 16px; font-size: 12px; color: rgba(255,255,255,0.5); }
        .game-card-footer { padding: 16px 20px; background: rgba(0,0,0,0.2); display: flex; justify-content: space-between; }
        .toggle-btn { padding: 8px 16px; background: rgba(255,255,255,0.1); border: none; border-radius: 6px; color: white; font-size: 12px; cursor: pointer; }
        .toggle-btn:hover { background: rgba(255,255,255,0.2); }
        
        .category-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; background: rgba(99, 102, 241, 0.2); color: #a5b4fc; }
        
        /* Modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal { background: var(--bg-card); border-radius: 12px; padding: 24px; max-width: 500px; width: 90%; }
        .modal h3 { margin-bottom: 20px; }
        .modal-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; }
        
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; color: rgba(255,255,255,0.7); }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px; background: var(--bg-input); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; font-size: 14px;
        }
        .form-group textarea { min-height: 80px; resize: vertical; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-logo">
            <h1>QuantrazGG</h1>
            <span>Admin Dashboard</span>
        </div>
        <nav class="nav-section">
            <div class="nav-section-title">Overview</div>
            <a href="/admin/" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
        </nav>
        <nav class="nav-section">
            <div class="nav-section-title">Management</div>
            <a href="/admin/users.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Users
            </a>
            <a href="/admin/teams.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Teams
            </a>
            <a href="/admin/games.php" class="nav-item active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                Games
            </a>
        </nav>
        <nav class="nav-section">
            <div class="nav-section-title">System</div>
            <a href="/auth/logout.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sign Out
            </a>
        </nav>
    </aside>
    
    <main class="main-content">
        <header class="page-header">
            <h1 class="page-title">Games Management</h1>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('active')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Game
            </button>
        </header>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div class="games-grid">
            <?php foreach ($games as $game): 
                $icons = ['wheel' => '🎰', 'quiz' => '❓', 'auction' => '💰', 'simulation' => '📊', 'custom' => '🎮'];
                $icon = $icons[$game['category']] ?? '🎮';
            ?>
                <div class="game-card">
                    <div class="game-card-header">
                        <div class="game-icon"><?php echo $icon; ?></div>
                        <span class="game-status <?php echo $game['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $game['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <div class="game-card-body">
                        <h3 class="game-name"><?php echo htmlspecialchars($game['name']); ?></h3>
                        <p class="game-description"><?php echo htmlspecialchars($game['description'] ?? 'No description'); ?></p>
                        <div class="game-meta">
                            <span class="category-badge"><?php echo ucfirst($game['category']); ?></span>
                            <span><?php echo $game['min_players']; ?>-<?php echo $game['max_players']; ?> players</span>
                            <span><?php echo $game['duration_minutes']; ?> min</span>
                        </div>
                    </div>
                    <div class="game-card-footer">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="toggle_active">
                            <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
                            <input type="hidden" name="is_active" value="<?php echo $game['is_active'] ? 'true' : 'false'; ?>">
                            <button type="submit" class="toggle-btn">
                                <?php echo $game['is_active'] ? 'Deactivate' : 'Activate'; ?>
                            </button>
                        </form>
                        <a href="/admin/games/edit.php?id=<?php echo $game['id']; ?>" class="toggle-btn">Edit</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    
    <!-- Add Game Modal -->
    <div class="modal-overlay" id="addModal">
        <div class="modal">
            <h3>Add New Game</h3>
            <form method="post">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Game Name</label>
                    <input type="text" name="name" required placeholder="e.g., Team Challenge">
                </div>
                <div class="form-group">
                    <label>Slug (URL-friendly)</label>
                    <input type="text" name="slug" required placeholder="e.g., team-challenge">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="wheel">Wheel</option>
                        <option value="quiz">Quiz</option>
                        <option value="auction">Auction</option>
                        <option value="simulation">Simulation</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Brief description of the game"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn" onclick="document.getElementById('addModal').classList.remove('active')" style="background: transparent; border: 1px solid rgba(255,255,255,0.2);">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Game</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.querySelectorAll('.modal-overlay').forEach(o => {
            o.addEventListener('click', e => { if (e.target === o) o.classList.remove('active'); });
        });
    </script>
</body>
</html>
