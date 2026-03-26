<?php
/**
 * QuantrazGG Access API
 * Central API for game access, sessions, and data
 * 
 * Endpoints:
 *   GET  /api/games          - List available games
 *   GET  /api/games/:id      - Get game details
 *   POST /api/sessions       - Create game session
 *   GET  /api/sessions/:id   - Get session details
 *   POST /api/sessions/:id/join   - Join a session
 *   POST /api/sessions/:id/action - Submit game action
 *   GET  /api/teams          - List user's teams
 *   GET  /api/leaderboard    - Get leaderboard
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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

require_once __DIR__ . '/../lib/Supabase.php';
require_once __DIR__ . '/../lib/SupabaseAuth.php';

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('/^\/api/', '', $path);
$pathParts = array_filter(explode('/', $path));
$pathParts = array_values($pathParts);

// Get auth token
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = null;
if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    $token = $matches[1];
}

// Initialize
$db = new Supabase();
$auth = null;
$currentUser = null;

// Try to authenticate
if ($token) {
    try {
        session_start();
        $_SESSION['supabase_access_token'] = $token;
        $auth = new SupabaseAuth();
        if ($auth->isAuthenticated()) {
            $currentUser = $auth->getUser();
        }
    } catch (Exception $e) {
        // Continue without auth
    }
}

// Helper functions
function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function requireAuth() {
    global $currentUser;
    if (!$currentUser) {
        respond(['error' => 'Authentication required'], 401);
    }
}

function getRequestBody() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

// Route handling
try {
    // GET /api/games
    if ($method === 'GET' && ($pathParts[0] ?? '') === 'games' && !isset($pathParts[1])) {
        $result = $db->from('games')
            ->select('id,name,slug,description,category,min_players,max_players,duration_minutes,is_active')
            ->eq('is_active', 'true')
            ->execute();
        respond(['games' => $result['data']]);
    }
    
    // GET /api/games/:id
    if ($method === 'GET' && ($pathParts[0] ?? '') === 'games' && isset($pathParts[1])) {
        $gameId = $pathParts[1];
        $result = $db->from('games')
            ->select('*')
            ->eq('id', $gameId)
            ->single()
            ->execute();
        
        if (empty($result['data'])) {
            respond(['error' => 'Game not found'], 404);
        }
        respond(['game' => $result['data']]);
    }
    
    // POST /api/sessions - Create session
    if ($method === 'POST' && ($pathParts[0] ?? '') === 'sessions' && !isset($pathParts[1])) {
        requireAuth();
        
        $body = getRequestBody();
        $gameId = $body['game_id'] ?? null;
        $config = $body['config'] ?? [];
        
        if (!$gameId) {
            respond(['error' => 'game_id is required'], 400);
        }
        
        // Verify game exists
        $game = $db->from('games')->select('id')->eq('id', $gameId)->single()->execute();
        if (empty($game['data'])) {
            respond(['error' => 'Game not found'], 404);
        }
        
        // Create session
        $result = $db->from('game_sessions')->insert([
            'game_id' => $gameId,
            'trainer_id' => $currentUser['id'],
            'status' => 'pending',
            'config' => json_encode($config)
        ])->execute();
        
        respond(['session' => $result['data'][0] ?? null], 201);
    }
    
    // GET /api/sessions/:id
    if ($method === 'GET' && ($pathParts[0] ?? '') === 'sessions' && isset($pathParts[1]) && !isset($pathParts[2])) {
        $sessionId = $pathParts[1];
        
        $result = $db->from('game_sessions')
            ->select('*')
            ->eq('id', $sessionId)
            ->single()
            ->execute();
        
        if (empty($result['data'])) {
            respond(['error' => 'Session not found'], 404);
        }
        
        // Get participants
        $participants = $db->from('session_participants')
            ->select('*')
            ->eq('session_id', $sessionId)
            ->execute();
        
        $session = $result['data'];
        $session['participants'] = $participants['data'] ?? [];
        
        respond(['session' => $session]);
    }
    
    // POST /api/sessions/:id/join
    if ($method === 'POST' && ($pathParts[0] ?? '') === 'sessions' && isset($pathParts[1]) && ($pathParts[2] ?? '') === 'join') {
        requireAuth();
        
        $sessionId = $pathParts[1];
        $body = getRequestBody();
        $teamId = $body['team_id'] ?? null;
        
        if (!$teamId) {
            respond(['error' => 'team_id is required'], 400);
        }
        
        // Check session exists and is joinable
        $session = $db->from('game_sessions')
            ->select('id,status')
            ->eq('id', $sessionId)
            ->single()
            ->execute();
        
        if (empty($session['data'])) {
            respond(['error' => 'Session not found'], 404);
        }
        
        if (!in_array($session['data']['status'], ['pending', 'active'])) {
            respond(['error' => 'Session is not accepting participants'], 400);
        }
        
        // Add participant
        $result = $db->from('session_participants')->upsert([
            'session_id' => $sessionId,
            'team_id' => $teamId,
            'score' => 0,
            'bank_change' => 0
        ], 'session_id,team_id')->execute();
        
        respond(['participant' => $result['data'][0] ?? null], 201);
    }
    
    // POST /api/sessions/:id/action - Submit game action
    if ($method === 'POST' && ($pathParts[0] ?? '') === 'sessions' && isset($pathParts[1]) && ($pathParts[2] ?? '') === 'action') {
        requireAuth();
        
        $sessionId = $pathParts[1];
        $body = getRequestBody();
        $action = $body['action'] ?? null;
        $teamId = $body['team_id'] ?? null;
        $data = $body['data'] ?? [];
        
        if (!$action || !$teamId) {
            respond(['error' => 'action and team_id are required'], 400);
        }
        
        // Get session and game config
        $session = $db->from('game_sessions')
            ->select('*')
            ->eq('id', $sessionId)
            ->single()
            ->execute();
        
        if (empty($session['data']) || $session['data']['status'] !== 'active') {
            respond(['error' => 'Session not active'], 400);
        }
        
        // Process action based on type
        $result = processGameAction($db, $sessionId, $teamId, $action, $data);
        respond($result);
    }
    
    // POST /api/sessions/:id/start
    if ($method === 'POST' && ($pathParts[0] ?? '') === 'sessions' && isset($pathParts[1]) && ($pathParts[2] ?? '') === 'start') {
        requireAuth();
        
        $sessionId = $pathParts[1];
        
        $db->from('game_sessions')
            ->update([
                'status' => 'active',
                'started_at' => date('c')
            ])
            ->eq('id', $sessionId)
            ->eq('trainer_id', $currentUser['id'])
            ->execute();
        
        respond(['success' => true, 'message' => 'Session started']);
    }
    
    // POST /api/sessions/:id/end
    if ($method === 'POST' && ($pathParts[0] ?? '') === 'sessions' && isset($pathParts[1]) && ($pathParts[2] ?? '') === 'end') {
        requireAuth();
        
        $sessionId = $pathParts[1];
        
        $db->from('game_sessions')
            ->update([
                'status' => 'completed',
                'ended_at' => date('c')
            ])
            ->eq('id', $sessionId)
            ->eq('trainer_id', $currentUser['id'])
            ->execute();
        
        respond(['success' => true, 'message' => 'Session ended']);
    }
    
    // GET /api/teams
    if ($method === 'GET' && ($pathParts[0] ?? '') === 'teams') {
        requireAuth();
        
        $result = $db->from('team_members')
            ->select('team_id')
            ->eq('user_id', $currentUser['id'])
            ->execute();
        
        $teamIds = array_column($result['data'] ?? [], 'team_id');
        
        if (empty($teamIds)) {
            respond(['teams' => []]);
        }
        
        $teams = $db->from('teams')
            ->select('*')
            ->in('id', $teamIds)
            ->execute();
        
        respond(['teams' => $teams['data'] ?? []]);
    }
    
    // GET /api/leaderboard
    if ($method === 'GET' && ($pathParts[0] ?? '') === 'leaderboard') {
        $gameId = $_GET['game_id'] ?? null;
        $period = $_GET['period'] ?? 'all_time';
        $limit = min((int)($_GET['limit'] ?? 10), 100);
        
        $query = $db->from('leaderboard')
            ->select('*')
            ->eq('period', $period)
            ->order('score', false)
            ->limit($limit);
        
        if ($gameId) {
            $query = $query->eq('game_id', $gameId);
        }
        
        $result = $query->execute();
        respond(['leaderboard' => $result['data'] ?? []]);
    }
    
    // GET /api/me
    if ($method === 'GET' && ($pathParts[0] ?? '') === 'me') {
        requireAuth();
        respond(['user' => [
            'id' => $currentUser['id'],
            'email' => $currentUser['email'],
            'role' => $currentUser['user_metadata']['role'] ?? 'player',
            'name' => $currentUser['user_metadata']['full_name'] ?? null
        ]]);
    }
    
    // 404 for unmatched routes
    respond(['error' => 'Not found', 'path' => $path], 404);
    
} catch (Exception $e) {
    respond(['error' => $e->getMessage()], 500);
}

/**
 * Process game-specific actions
 */
function processGameAction($db, $sessionId, $teamId, $action, $data) {
    switch ($action) {
        case 'spin':
            // Wheel spin action
            $result = rand(0, 100);
            $winAmount = $result > 50 ? rand(10, 100) : -rand(5, 50);
            
            // Update team bank
            $team = $db->from('teams')->select('bank_balance')->eq('id', $teamId)->single()->execute();
            $currentBalance = $team['data']['bank_balance'] ?? 0;
            $newBalance = max(0, $currentBalance + $winAmount);
            
            $db->from('teams')
                ->update(['bank_balance' => $newBalance])
                ->eq('id', $teamId)
                ->execute();
            
            // Record transaction
            $db->from('transactions')->insert([
                'team_id' => $teamId,
                'session_id' => $sessionId,
                'amount' => $winAmount,
                'type' => $winAmount >= 0 ? 'win' : 'loss',
                'description' => 'Wheel spin',
                'balance_after' => $newBalance
            ])->execute();
            
            // Update participant score
            $db->from('session_participants')
                ->update([
                    'score' => $result,
                    'bank_change' => $winAmount
                ])
                ->eq('session_id', $sessionId)
                ->eq('team_id', $teamId)
                ->execute();
            
            return [
                'success' => true,
                'result' => $result,
                'win_amount' => $winAmount,
                'new_balance' => $newBalance
            ];
            
        case 'bet':
            $amount = (int)($data['amount'] ?? 0);
            if ($amount <= 0) {
                return ['error' => 'Invalid bet amount'];
            }
            
            $team = $db->from('teams')->select('bank_balance')->eq('id', $teamId)->single()->execute();
            $currentBalance = $team['data']['bank_balance'] ?? 0;
            
            if ($amount > $currentBalance) {
                return ['error' => 'Insufficient funds'];
            }
            
            $newBalance = $currentBalance - $amount;
            $db->from('teams')
                ->update(['bank_balance' => $newBalance])
                ->eq('id', $teamId)
                ->execute();
            
            $db->from('transactions')->insert([
                'team_id' => $teamId,
                'session_id' => $sessionId,
                'amount' => -$amount,
                'type' => 'bet',
                'description' => 'Placed bet',
                'balance_after' => $newBalance
            ])->execute();
            
            return [
                'success' => true,
                'bet_amount' => $amount,
                'new_balance' => $newBalance
            ];
            
        case 'answer':
            // Quiz answer
            $correct = $data['correct'] ?? false;
            $points = $correct ? ($data['points'] ?? 10) : 0;
            
            $db->from('session_participants')
                ->update(['score' => $points])
                ->eq('session_id', $sessionId)
                ->eq('team_id', $teamId)
                ->execute();
            
            return [
                'success' => true,
                'correct' => $correct,
                'points' => $points
            ];
            
        default:
            return ['error' => 'Unknown action: ' . $action];
    }
}
