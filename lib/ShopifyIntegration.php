<?php
/**
 * Shopify Integration for QuantrazGG
 * 
 * Allows teams to spend their virtual bank balance on real products
 * via the Quantraz Shopify store
 * 
 * Features:
 * - Browse products from Shopify
 * - Create discount codes based on bank balance
 * - Track redemptions
 */

class ShopifyIntegration {
    private string $shopUrl;
    private string $accessToken;
    private string $apiVersion = '2024-01';
    
    public function __construct() {
        // Load from secrets file
        $secretsFile = getenv('HOME') . '/.secrets/shopify-api.json';
        if (file_exists($secretsFile)) {
            $secrets = json_decode(file_get_contents($secretsFile), true);
            $this->shopUrl = $secrets['shop_url'] ?? 'quantraz.myshopify.com';
            $this->accessToken = $secrets['access_token'] ?? '';
        } else {
            throw new Exception('Shopify credentials not found');
        }
    }
    
    /**
     * Get products from Shopify store
     */
    public function getProducts(int $limit = 10, ?string $collection = null): array {
        $endpoint = "/admin/api/{$this->apiVersion}/products.json?limit={$limit}&status=active";
        
        if ($collection) {
            $endpoint .= "&collection_id={$collection}";
        }
        
        $response = $this->request('GET', $endpoint);
        return $response['products'] ?? [];
    }
    
    /**
     * Get a single product
     */
    public function getProduct(string $productId): ?array {
        $endpoint = "/admin/api/{$this->apiVersion}/products/{$productId}.json";
        $response = $this->request('GET', $endpoint);
        return $response['product'] ?? null;
    }
    
    /**
     * Get collections (for categorizing products)
     */
    public function getCollections(): array {
        $endpoint = "/admin/api/{$this->apiVersion}/custom_collections.json";
        $response = $this->request('GET', $endpoint);
        return $response['custom_collections'] ?? [];
    }
    
    /**
     * Create a discount code for a team
     * 
     * @param string $teamId Team ID
     * @param int $discountAmount Amount in dollars
     * @param int $validDays Days until expiry
     * @return array Discount code details
     */
    public function createTeamDiscount(string $teamId, int $discountAmount, int $validDays = 30): array {
        // Generate unique code
        $code = 'QGG-' . strtoupper(substr($teamId, 0, 8)) . '-' . rand(1000, 9999);
        
        // Create price rule first
        $priceRuleEndpoint = "/admin/api/{$this->apiVersion}/price_rules.json";
        $priceRuleData = [
            'price_rule' => [
                'title' => "QuantrazGG Team Reward - {$code}",
                'target_type' => 'line_item',
                'target_selection' => 'all',
                'allocation_method' => 'across',
                'value_type' => 'fixed_amount',
                'value' => "-{$discountAmount}.00",
                'customer_selection' => 'all',
                'once_per_customer' => true,
                'usage_limit' => 1,
                'starts_at' => date('c'),
                'ends_at' => date('c', strtotime("+{$validDays} days"))
            ]
        ];
        
        $priceRuleResponse = $this->request('POST', $priceRuleEndpoint, $priceRuleData);
        
        if (!isset($priceRuleResponse['price_rule']['id'])) {
            throw new Exception('Failed to create price rule');
        }
        
        $priceRuleId = $priceRuleResponse['price_rule']['id'];
        
        // Create discount code
        $discountEndpoint = "/admin/api/{$this->apiVersion}/price_rules/{$priceRuleId}/discount_codes.json";
        $discountData = [
            'discount_code' => [
                'code' => $code
            ]
        ];
        
        $discountResponse = $this->request('POST', $discountEndpoint, $discountData);
        
        return [
            'code' => $code,
            'amount' => $discountAmount,
            'price_rule_id' => $priceRuleId,
            'expires_at' => date('Y-m-d', strtotime("+{$validDays} days")),
            'shop_url' => "https://{$this->shopUrl}"
        ];
    }
    
    /**
     * Check if a discount code has been used
     */
    public function checkDiscountUsage(string $priceRuleId): array {
        $endpoint = "/admin/api/{$this->apiVersion}/price_rules/{$priceRuleId}/discount_codes.json";
        $response = $this->request('GET', $endpoint);
        
        $discountCode = $response['discount_codes'][0] ?? null;
        
        if (!$discountCode) {
            return ['used' => false, 'usage_count' => 0];
        }
        
        return [
            'used' => $discountCode['usage_count'] > 0,
            'usage_count' => $discountCode['usage_count'],
            'code' => $discountCode['code']
        ];
    }
    
    /**
     * Delete a discount code (cleanup)
     */
    public function deleteDiscount(string $priceRuleId): bool {
        $endpoint = "/admin/api/{$this->apiVersion}/price_rules/{$priceRuleId}.json";
        try {
            $this->request('DELETE', $endpoint);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get recent orders (for tracking game-related purchases)
     */
    public function getRecentOrders(int $limit = 10): array {
        $endpoint = "/admin/api/{$this->apiVersion}/orders.json?limit={$limit}&status=any";
        $response = $this->request('GET', $endpoint);
        return $response['orders'] ?? [];
    }
    
    /**
     * Make API request to Shopify
     */
    private function request(string $method, string $endpoint, ?array $data = null): array {
        $url = "https://{$this->shopUrl}{$endpoint}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Shopify-Access-Token: ' . $this->accessToken
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Shopify API error: {$error}");
        }
        
        if ($httpCode >= 400) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['errors'] ?? "HTTP {$httpCode}";
            throw new Exception("Shopify API error: " . json_encode($errorMessage));
        }
        
        return json_decode($response, true) ?? [];
    }
}

/**
 * Redemption Tracker
 * Records when teams redeem their bank balance for store credit
 */
class RedemptionTracker {
    private $db;
    
    public function __construct($supabaseDb) {
        $this->db = $supabaseDb;
    }
    
    /**
     * Record a redemption
     */
    public function recordRedemption(string $teamId, int $amount, string $discountCode, string $priceRuleId): array {
        // Deduct from team bank
        $team = $this->db->from('teams')
            ->select('bank_balance')
            ->eq('id', $teamId)
            ->single()
            ->execute();
        
        $currentBalance = $team['data']['bank_balance'] ?? 0;
        
        if ($amount > $currentBalance) {
            throw new Exception('Insufficient bank balance');
        }
        
        $newBalance = $currentBalance - $amount;
        
        // Update team balance
        $this->db->from('teams')
            ->update(['bank_balance' => $newBalance])
            ->eq('id', $teamId)
            ->execute();
        
        // Record transaction
        $this->db->from('transactions')->insert([
            'team_id' => $teamId,
            'amount' => -$amount,
            'type' => 'transfer',
            'description' => "Redeemed for Shopify discount code: {$discountCode}",
            'balance_after' => $newBalance
        ])->execute();
        
        return [
            'success' => true,
            'amount_redeemed' => $amount,
            'new_balance' => $newBalance,
            'discount_code' => $discountCode
        ];
    }
}
