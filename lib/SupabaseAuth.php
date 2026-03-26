<?php
/**
 * SupabaseAuth - Authentication Helper for QuantrazGG
 * 
 * Handles:
 * - Email/Password authentication
 * - Session management with JWT
 * - User profile management
 * - Role-based access control
 * 
 * Usage:
 *   $auth = new SupabaseAuth();
 *   $result = $auth->signIn($email, $password);
 *   if ($auth->isAuthenticated()) { ... }
 */

class SupabaseAuth {
    private string $url;
    private string $anonKey;
    private string $serviceKey;
    
    // Session keys
    const SESSION_USER = 'supabase_user';
    const SESSION_TOKEN = 'supabase_access_token';
    const SESSION_REFRESH = 'supabase_refresh_token';
    const SESSION_EXPIRES = 'supabase_expires_at';
    
    public function __construct() {
        // Load from environment
        $this->url = getenv('SUPABASE_URL') ?: $_ENV['SUPABASE_URL'] ?? '';
        $this->anonKey = getenv('SUPABASE_ANON_KEY') ?: $_ENV['SUPABASE_ANON_KEY'] ?? '';
        $this->serviceKey = getenv('SUPABASE_SERVICE_ROLE_KEY') ?: $_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? '';
        
        if (empty($this->url) || empty($this->anonKey)) {
            throw new Exception('Supabase URL and Anon Key are required');
        }
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Sign up with email and password
     */
    public function signUp(string $email, string $password, array $metadata = []): array {
        $endpoint = $this->url . '/auth/v1/signup';
        
        $body = [
            'email' => $email,
            'password' => $password
        ];
        
        if (!empty($metadata)) {
            $body['data'] = $metadata;
        }
        
        $response = $this->authRequest($endpoint, $body);
        
        if ($response['status'] === 200 && isset($response['data']['user'])) {
            // Auto sign-in after signup
            $this->setSession($response['data']);
            return [
                'success' => true,
                'user' => $response['data']['user'],
                'message' => 'Account created successfully'
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['data']['error_description'] ?? $response['data']['msg'] ?? 'Signup failed',
            'code' => $response['data']['error'] ?? 'unknown'
        ];
    }
    
    /**
     * Sign in with email and password
     */
    public function signIn(string $email, string $password): array {
        $endpoint = $this->url . '/auth/v1/token?grant_type=password';
        
        $response = $this->authRequest($endpoint, [
            'email' => $email,
            'password' => $password
        ]);
        
        if ($response['status'] === 200 && isset($response['data']['access_token'])) {
            $this->setSession($response['data']);
            return [
                'success' => true,
                'user' => $response['data']['user'],
                'message' => 'Login successful'
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['data']['error_description'] ?? 'Invalid credentials',
            'code' => $response['data']['error'] ?? 'invalid_credentials'
        ];
    }
    
    /**
     * Sign out
     */
    public function signOut(): bool {
        $token = $_SESSION[self::SESSION_TOKEN] ?? null;
        
        if ($token) {
            $endpoint = $this->url . '/auth/v1/logout';
            $this->authRequest($endpoint, [], $token);
        }
        
        // Clear session
        unset($_SESSION[self::SESSION_USER]);
        unset($_SESSION[self::SESSION_TOKEN]);
        unset($_SESSION[self::SESSION_REFRESH]);
        unset($_SESSION[self::SESSION_EXPIRES]);
        
        return true;
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool {
        if (!isset($_SESSION[self::SESSION_TOKEN])) {
            return false;
        }
        
        // Check if token is expired
        $expiresAt = $_SESSION[self::SESSION_EXPIRES] ?? 0;
        if ($expiresAt < time()) {
            // Try to refresh
            return $this->refreshSession();
        }
        
        return true;
    }
    
    /**
     * Get current user
     */
    public function getUser(): ?array {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return $_SESSION[self::SESSION_USER] ?? null;
    }
    
    /**
     * Get current user's email
     */
    public function getEmail(): ?string {
        $user = $this->getUser();
        return $user['email'] ?? null;
    }
    
    /**
     * Get current user's ID
     */
    public function getUserId(): ?string {
        $user = $this->getUser();
        return $user['id'] ?? null;
    }
    
    /**
     * Get user's role from profile
     */
    public function getRole(): string {
        $user = $this->getUser();
        return $user['user_metadata']['role'] ?? 'player';
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool {
        return $this->getRole() === $role;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin(): bool {
        $role = $this->getRole();
        return in_array($role, ['admin', 'super_admin']);
    }
    
    /**
     * Check if user is trainer
     */
    public function isTrainer(): bool {
        $role = $this->getRole();
        return in_array($role, ['trainer', 'admin', 'super_admin']);
    }
    
    /**
     * Get access token for API calls
     */
    public function getAccessToken(): ?string {
        if (!$this->isAuthenticated()) {
            return null;
        }
        return $_SESSION[self::SESSION_TOKEN] ?? null;
    }
    
    /**
     * Request password reset
     */
    public function resetPassword(string $email, string $redirectTo = ''): array {
        $endpoint = $this->url . '/auth/v1/recover';
        
        $body = ['email' => $email];
        if ($redirectTo) {
            $body['redirect_to'] = $redirectTo;
        }
        
        $response = $this->authRequest($endpoint, $body);
        
        if ($response['status'] === 200) {
            return [
                'success' => true,
                'message' => 'Password reset email sent'
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['data']['error_description'] ?? 'Failed to send reset email'
        ];
    }
    
    /**
     * Update user password (when authenticated)
     */
    public function updatePassword(string $newPassword): array {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'Not authenticated'];
        }
        
        $endpoint = $this->url . '/auth/v1/user';
        
        $response = $this->authRequest($endpoint, [
            'password' => $newPassword
        ], $token, 'PUT');
        
        if ($response['status'] === 200) {
            return [
                'success' => true,
                'message' => 'Password updated successfully'
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['data']['error_description'] ?? 'Failed to update password'
        ];
    }
    
    /**
     * Update user metadata
     */
    public function updateUser(array $metadata): array {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'Not authenticated'];
        }
        
        $endpoint = $this->url . '/auth/v1/user';
        
        $response = $this->authRequest($endpoint, [
            'data' => $metadata
        ], $token, 'PUT');
        
        if ($response['status'] === 200) {
            // Update session
            $_SESSION[self::SESSION_USER] = $response['data'];
            return [
                'success' => true,
                'user' => $response['data']
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['data']['error_description'] ?? 'Failed to update user'
        ];
    }
    
    /**
     * Get Google OAuth URL
     */
    public function getGoogleAuthUrl(string $redirectTo = ''): string {
        $params = [
            'provider' => 'google'
        ];
        
        if ($redirectTo) {
            $params['redirect_to'] = $redirectTo;
        }
        
        return $this->url . '/auth/v1/authorize?' . http_build_query($params);
    }
    
    /**
     * Handle OAuth callback
     */
    public function handleOAuthCallback(): array {
        // Get tokens from URL hash (client-side) or query params
        $accessToken = $_GET['access_token'] ?? null;
        $refreshToken = $_GET['refresh_token'] ?? null;
        
        if (!$accessToken) {
            return [
                'success' => false,
                'error' => 'No access token received'
            ];
        }
        
        // Get user info
        $user = $this->getUserFromToken($accessToken);
        
        if ($user) {
            $this->setSession([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => 3600,
                'user' => $user
            ]);
            
            return [
                'success' => true,
                'user' => $user
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Failed to get user info'
        ];
    }
    
    /**
     * Refresh the session using refresh token
     */
    private function refreshSession(): bool {
        $refreshToken = $_SESSION[self::SESSION_REFRESH] ?? null;
        
        if (!$refreshToken) {
            return false;
        }
        
        $endpoint = $this->url . '/auth/v1/token?grant_type=refresh_token';
        
        $response = $this->authRequest($endpoint, [
            'refresh_token' => $refreshToken
        ]);
        
        if ($response['status'] === 200 && isset($response['data']['access_token'])) {
            $this->setSession($response['data']);
            return true;
        }
        
        // Refresh failed, clear session
        $this->signOut();
        return false;
    }
    
    /**
     * Set session data from auth response
     */
    private function setSession(array $data): void {
        $_SESSION[self::SESSION_TOKEN] = $data['access_token'];
        $_SESSION[self::SESSION_REFRESH] = $data['refresh_token'] ?? null;
        $_SESSION[self::SESSION_EXPIRES] = time() + ($data['expires_in'] ?? 3600);
        $_SESSION[self::SESSION_USER] = $data['user'] ?? null;
        
        // Also set legacy session for backward compatibility
        if (isset($data['user']['email'])) {
            $_SESSION['email'] = $data['user']['email'];
        }
    }
    
    /**
     * Get user from access token
     */
    private function getUserFromToken(string $token): ?array {
        $endpoint = $this->url . '/auth/v1/user';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . $token
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    /**
     * Make auth API request
     */
    private function authRequest(string $endpoint, array $body, ?string $token = null, string $method = 'POST'): array {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $headers = [
            'apikey: ' . $this->anonKey,
            'Content-Type: application/json'
        ];
        
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'data' => json_decode($response, true) ?? [],
            'status' => $httpCode
        ];
    }
    
    /**
     * Require authentication - redirect if not authenticated
     */
    public function requireAuth(string $redirectTo = '/index.php'): void {
        if (!$this->isAuthenticated()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }
    
    /**
     * Require specific role - redirect if not authorized
     */
    public function requireRole(string $role, string $redirectTo = '/unauthorized.php'): void {
        $this->requireAuth();
        
        if (!$this->hasRole($role) && !$this->isAdmin()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }
    
    /**
     * Require trainer role
     */
    public function requireTrainer(string $redirectTo = '/unauthorized.php'): void {
        $this->requireAuth();
        
        if (!$this->isTrainer()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }
    
    /**
     * Require admin role
     */
    public function requireAdmin(string $redirectTo = '/unauthorized.php'): void {
        $this->requireAuth();
        
        if (!$this->isAdmin()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }
}
