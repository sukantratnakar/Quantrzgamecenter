<?php
/**
 * User Management - QuantrazGG Admin
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
    
    if ($action === 'update_role') {
        $userId = $_POST['user_id'] ?? '';
        $newRole = $_POST['role'] ?? '';
        
        if ($userId && in_array($newRole, ['player', 'trainer', 'admin'])) {
            try {
                $db->from('profiles')
                    ->update(['role' => $newRole])
                    ->eq('id', $userId)
                    ->execute();
                $message = 'User role updated successfully';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Failed to update role: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
    
    if ($action === 'delete') {
        $userId = $_POST['user_id'] ?? '';
        if ($userId && $userId !== $auth->getUserId()) {
            try {
                $db->from('profiles')
                    ->delete()
                    ->eq('id', $userId)
                    ->execute();
                $message = 'User deleted successfully';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Failed to delete user: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Fetch users
$users = [];
$searchQuery = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';

try {
    $query = $db->from('profiles')->select('*')->order('created_at', false);
    
    if ($roleFilter) {
        $query = $query->eq('role', $roleFilter);
    }
    
    $result = $query->execute();
    $users = $result['data'] ?? [];
    
    // Filter by search if provided
    if ($searchQuery) {
        $users = array_filter($users, function($user) use ($searchQuery) {
            return stripos($user['email'], $searchQuery) !== false ||
                   stripos($user['full_name'] ?? '', $searchQuery) !== false;
        });
    }
} catch (Exception $e) {
    $message = 'Failed to load users: ' . $e->getMessage();
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - QuantrazGG Admin</title>
    <link rel="stylesheet" href="/styles/design-system.css">
    <link rel="icon" href="/images/tit-logo.svg">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: var(--bg-primary); color: white; font-family: var(--font-body); min-height: 100vh; }
        
        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0; width: 260px;
            background: var(--bg-secondary); border-right: 1px solid rgba(0, 217, 255, 0.1);
            padding: 24px 0; overflow-y: auto;
        }
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
        
        .toolbar { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
        .search-box { flex: 1; min-width: 200px; position: relative; }
        .search-box input {
            width: 100%; padding: 12px 16px 12px 44px; background: var(--bg-card);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; font-size: 14px;
        }
        .search-box input::placeholder { color: rgba(255,255,255,0.4); }
        .search-box svg { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.4); }
        
        .filter-select {
            padding: 12px 16px; background: var(--bg-card); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; color: white; font-size: 14px; cursor: pointer;
        }
        
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; text-decoration: none; transition: all 0.2s; }
        .btn-primary { background: var(--accent); color: var(--bg-primary); border: none; }
        .btn-primary:hover { background: var(--accent-soft); }
        
        .message { padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; }
        .message.success { background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5); color: #86efac; }
        .message.error { background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5; }
        
        .card { background: var(--bg-card); border-radius: 12px; border: 1px solid rgba(0, 217, 255, 0.1); overflow: hidden; }
        
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 16px; background: rgba(0,0,0,0.2); font-size: 12px; text-transform: uppercase; color: rgba(255,255,255,0.6); font-weight: 600; }
        td { padding: 16px; border-top: 1px solid rgba(255,255,255,0.05); }
        tr:hover td { background: rgba(0, 217, 255, 0.05); }
        
        .user-cell { display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent-dark)); display: flex; align-items: center; justify-content: center; font-weight: 600; color: var(--bg-primary); font-size: 14px; }
        .user-info { display: flex; flex-direction: column; }
        .user-name { font-weight: 500; }
        .user-email { font-size: 12px; color: rgba(255,255,255,0.5); }
        
        .role-badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .role-badge.player { background: rgba(99, 102, 241, 0.2); color: #a5b4fc; }
        .role-badge.trainer { background: rgba(34, 197, 94, 0.2); color: #86efac; }
        .role-badge.admin { background: rgba(239, 68, 68, 0.2); color: #fca5a5; }
        .role-badge.super_admin { background: rgba(245, 158, 11, 0.2); color: #fcd34d; }
        
        .actions { display: flex; gap: 8px; }
        .action-btn { padding: 6px 12px; background: rgba(255,255,255,0.1); border: none; border-radius: 4px; color: white; font-size: 12px; cursor: pointer; }
        .action-btn:hover { background: rgba(255,255,255,0.2); }
        .action-btn.danger { color: #fca5a5; }
        .action-btn.danger:hover { background: rgba(239, 68, 68, 0.2); }
        
        .empty-state { text-align: center; padding: 60px 20px; color: rgba(255,255,255,0.5); }
        .empty-state svg { width: 64px; height: 64px; margin-bottom: 16px; opacity: 0.3; }
        
        /* Modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal { background: var(--bg-card); border-radius: 12px; padding: 24px; max-width: 400px; width: 90%; }
        .modal h3 { margin-bottom: 16px; }
        .modal-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; }
        
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; color: rgba(255,255,255,0.7); }
        .form-group select { width: 100%; padding: 12px; background: var(--bg-input); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; }
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
            <a href="/admin/" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
        </nav>
        <nav class="nav-section">
            <div class="nav-section-title">Management</div>
            <a href="/admin/users.php" class="nav-item active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Users
            </a>
            <a href="/admin/teams.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Teams
            </a>
            <a href="/admin/games.php" class="nav-item">
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
            <h1 class="page-title">User Management</h1>
        </header>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <!-- Toolbar -->
        <div class="toolbar">
            <div class="search-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <form method="get" style="display: contents;">
                    <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <input type="hidden" name="role" value="<?php echo htmlspecialchars($roleFilter); ?>">
                </form>
            </div>
            <select class="filter-select" onchange="filterByRole(this.value)">
                <option value="">All Roles</option>
                <option value="player" <?php echo $roleFilter === 'player' ? 'selected' : ''; ?>>Players</option>
                <option value="trainer" <?php echo $roleFilter === 'trainer' ? 'selected' : ''; ?>>Trainers</option>
                <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admins</option>
            </select>
        </div>
        
        <!-- Users Table -->
        <div class="card">
            <div class="table-container">
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                        <p>No users found</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($user['full_name'] ?? $user['email'], 0, 1)); ?>
                                            </div>
                                            <div class="user-info">
                                                <span class="user-name"><?php echo htmlspecialchars($user['full_name'] ?? 'No name'); ?></span>
                                                <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="role-badge <?php echo $user['role'] ?? 'player'; ?>">
                                            <?php echo ucfirst($user['role'] ?? 'player'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button class="action-btn" onclick="editRole('<?php echo $user['id']; ?>', '<?php echo $user['role'] ?? 'player'; ?>')">
                                                Edit Role
                                            </button>
                                            <?php if ($user['id'] !== $auth->getUserId()): ?>
                                                <button class="action-btn danger" onclick="confirmDelete('<?php echo $user['id']; ?>')">
                                                    Delete
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Edit Role Modal -->
    <div class="modal-overlay" id="roleModal">
        <div class="modal">
            <h3>Change User Role</h3>
            <form method="post">
                <input type="hidden" name="action" value="update_role">
                <input type="hidden" name="user_id" id="editUserId">
                <div class="form-group">
                    <label>Select Role</label>
                    <select name="role" id="editRole">
                        <option value="player">Player</option>
                        <option value="trainer">Trainer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn" onclick="closeModal('roleModal')" style="background: transparent; border: 1px solid rgba(255,255,255,0.2);">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirm Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <h3>Delete User?</h3>
            <p style="color: rgba(255,255,255,0.6); margin-bottom: 16px;">This action cannot be undone. The user will lose access to all their data.</p>
            <form method="post">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" id="deleteUserId">
                <div class="modal-actions">
                    <button type="button" class="btn" onclick="closeModal('deleteModal')" style="background: transparent; border: 1px solid rgba(255,255,255,0.2);">Cancel</button>
                    <button type="submit" class="btn" style="background: #ef4444;">Delete User</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function filterByRole(role) {
            const url = new URL(window.location);
            if (role) {
                url.searchParams.set('role', role);
            } else {
                url.searchParams.delete('role');
            }
            window.location = url;
        }
        
        function editRole(userId, currentRole) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editRole').value = currentRole;
            document.getElementById('roleModal').classList.add('active');
        }
        
        function confirmDelete(userId) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteModal').classList.add('active');
        }
        
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
        
        // Close modal on outside click
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', e => {
                if (e.target === overlay) closeModal(overlay.id);
            });
        });
    </script>
</body>
</html>
