<?php
/**
 * Supabase PHP Client for QuantrazGG
 * 
 * A lightweight PHP client for Supabase REST API
 * Supports: SELECT, INSERT, UPDATE, DELETE, RPC
 * 
 * Usage:
 *   $supabase = new Supabase();
 *   $users = $supabase->from('profiles')->select('*')->execute();
 *   $supabase->from('teams')->insert(['name' => 'Team A'])->execute();
 */

class Supabase {
    private string $url;
    private string $key;
    private string $table = '';
    private array $query = [];
    private string $method = 'GET';
    private ?array $body = null;
    private array $headers = [];
    
    public function __construct(?string $url = null, ?string $key = null) {
        // Load from environment if not provided
        $this->url = $url ?? getenv('SUPABASE_URL') ?: $_ENV['SUPABASE_URL'] ?? '';
        $this->key = $key ?? getenv('SUPABASE_SERVICE_ROLE_KEY') ?: getenv('SUPABASE_ANON_KEY') ?: $_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? $_ENV['SUPABASE_ANON_KEY'] ?? '';
        
        if (empty($this->url) || empty($this->key)) {
            throw new Exception('Supabase URL and Key are required. Set SUPABASE_URL and SUPABASE_ANON_KEY environment variables.');
        }
        
        $this->headers = [
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation'
        ];
    }
    
    /**
     * Select table to query
     */
    public function from(string $table): self {
        $clone = clone $this;
        $clone->table = $table;
        $clone->query = [];
        $clone->method = 'GET';
        $clone->body = null;
        return $clone;
    }
    
    /**
     * SELECT columns
     */
    public function select(string $columns = '*'): self {
        $this->query['select'] = $columns;
        $this->method = 'GET';
        return $this;
    }
    
    /**
     * INSERT data
     */
    public function insert(array $data): self {
        $this->method = 'POST';
        $this->body = $data;
        return $this;
    }
    
    /**
     * UPDATE data
     */
    public function update(array $data): self {
        $this->method = 'PATCH';
        $this->body = $data;
        return $this;
    }
    
    /**
     * DELETE (requires filter)
     */
    public function delete(): self {
        $this->method = 'DELETE';
        return $this;
    }
    
    /**
     * UPSERT data
     */
    public function upsert(array $data, string $onConflict = ''): self {
        $this->method = 'POST';
        $this->body = $data;
        $this->headers['Prefer'] = 'return=representation,resolution=merge-duplicates';
        if ($onConflict) {
            $this->query['on_conflict'] = $onConflict;
        }
        return $this;
    }
    
    /**
     * Filter: eq (equals)
     */
    public function eq(string $column, $value): self {
        $this->query[$column] = 'eq.' . $value;
        return $this;
    }
    
    /**
     * Filter: neq (not equals)
     */
    public function neq(string $column, $value): self {
        $this->query[$column] = 'neq.' . $value;
        return $this;
    }
    
    /**
     * Filter: gt (greater than)
     */
    public function gt(string $column, $value): self {
        $this->query[$column] = 'gt.' . $value;
        return $this;
    }
    
    /**
     * Filter: gte (greater than or equal)
     */
    public function gte(string $column, $value): self {
        $this->query[$column] = 'gte.' . $value;
        return $this;
    }
    
    /**
     * Filter: lt (less than)
     */
    public function lt(string $column, $value): self {
        $this->query[$column] = 'lt.' . $value;
        return $this;
    }
    
    /**
     * Filter: lte (less than or equal)
     */
    public function lte(string $column, $value): self {
        $this->query[$column] = 'lte.' . $value;
        return $this;
    }
    
    /**
     * Filter: like (pattern match)
     */
    public function like(string $column, string $pattern): self {
        $this->query[$column] = 'like.' . $pattern;
        return $this;
    }
    
    /**
     * Filter: ilike (case-insensitive like)
     */
    public function ilike(string $column, string $pattern): self {
        $this->query[$column] = 'ilike.' . $pattern;
        return $this;
    }
    
    /**
     * Filter: in (array of values)
     */
    public function in(string $column, array $values): self {
        $this->query[$column] = 'in.(' . implode(',', $values) . ')';
        return $this;
    }
    
    /**
     * Filter: is (null check)
     */
    public function is(string $column, $value): self {
        $this->query[$column] = 'is.' . ($value === null ? 'null' : $value);
        return $this;
    }
    
    /**
     * Order results
     */
    public function order(string $column, bool $ascending = true, bool $nullsFirst = false): self {
        $dir = $ascending ? 'asc' : 'desc';
        $nulls = $nullsFirst ? '.nullsfirst' : '.nullslast';
        $this->query['order'] = $column . '.' . $dir . $nulls;
        return $this;
    }
    
    /**
     * Limit results
     */
    public function limit(int $count): self {
        $this->query['limit'] = $count;
        return $this;
    }
    
    /**
     * Offset for pagination
     */
    public function offset(int $count): self {
        $this->query['offset'] = $count;
        return $this;
    }
    
    /**
     * Range (offset + limit shorthand)
     */
    public function range(int $from, int $to): self {
        $this->headers['Range'] = $from . '-' . $to;
        $this->headers['Range-Unit'] = 'items';
        return $this;
    }
    
    /**
     * Get single row
     */
    public function single(): self {
        $this->headers['Accept'] = 'application/vnd.pgrst.object+json';
        $this->query['limit'] = 1;
        return $this;
    }
    
    /**
     * Maybe single (returns null if not found)
     */
    public function maybeSingle(): self {
        $this->headers['Accept'] = 'application/vnd.pgrst.object+json';
        $this->query['limit'] = 1;
        return $this;
    }
    
    /**
     * Execute the query
     */
    public function execute(): array {
        $endpoint = rtrim($this->url, '/') . '/rest/v1/' . $this->table;
        
        // Build query string
        if (!empty($this->query)) {
            $endpoint .= '?' . http_build_query($this->query);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        
        // Set headers
        $headerArray = [];
        foreach ($this->headers as $key => $value) {
            $headerArray[] = "$key: $value";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        
        // Set body for POST/PATCH
        if ($this->body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->body));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Supabase request failed: $error");
        }
        
        $data = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $message = $data['message'] ?? $data['error'] ?? 'Unknown error';
            throw new Exception("Supabase error ($httpCode): $message");
        }
        
        return [
            'data' => $data,
            'status' => $httpCode,
            'error' => null
        ];
    }
    
    /**
     * Call a Postgres function (RPC)
     */
    public function rpc(string $function, array $params = []): array {
        $endpoint = rtrim($this->url, '/') . '/rest/v1/rpc/' . $function;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        
        $headerArray = [];
        foreach ($this->headers as $key => $value) {
            $headerArray[] = "$key: $value";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'data' => json_decode($response, true),
            'status' => $httpCode
        ];
    }
    
    /**
     * Auth: Sign up with email
     */
    public function signUp(string $email, string $password, array $metadata = []): array {
        $endpoint = rtrim($this->url, '/') . '/auth/v1/signup';
        
        $body = [
            'email' => $email,
            'password' => $password
        ];
        
        if (!empty($metadata)) {
            $body['data'] = $metadata;
        }
        
        return $this->authRequest($endpoint, $body);
    }
    
    /**
     * Auth: Sign in with email
     */
    public function signIn(string $email, string $password): array {
        $endpoint = rtrim($this->url, '/') . '/auth/v1/token?grant_type=password';
        
        return $this->authRequest($endpoint, [
            'email' => $email,
            'password' => $password
        ]);
    }
    
    /**
     * Auth: Sign out
     */
    public function signOut(string $accessToken): array {
        $endpoint = rtrim($this->url, '/') . '/auth/v1/logout';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'data' => json_decode($response, true),
            'status' => $httpCode
        ];
    }
    
    /**
     * Auth: Get user from token
     */
    public function getUser(string $accessToken): array {
        $endpoint = rtrim($this->url, '/') . '/auth/v1/user';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'data' => json_decode($response, true),
            'status' => $httpCode
        ];
    }
    
    /**
     * Helper for auth requests
     */
    private function authRequest(string $endpoint, array $body): array {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $this->key,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'data' => json_decode($response, true),
            'status' => $httpCode
        ];
    }
}
