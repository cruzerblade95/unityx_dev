<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class NOWPaymentsService
{
    private $client;
    private $apiKey;
    private $baseUrl;
    private $jwtToken;

    public function __construct()
    {
        $this->apiKey = config('services.nowpayments.api_key');
        $this->baseUrl = config('services.nowpayments.sandbox', true)
            ? 'https://api-sandbox.nowpayments.io'
            : 'https://api.nowpayments.io';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
            // IMPORTANT: SSL verification disabled for Windows development environment
            // For production, remove these options and ensure proper SSL certificate configuration
            'verify' => false,
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ],
        ]);
    }

    /**
     * Authenticate and get JWT token
     */
    private function authenticate()
    {
        if ($this->jwtToken) {
            return $this->jwtToken;
        }

        try {
            $response = $this->client->post('/v1/auth', [
                'json' => [
                    'email' => config('services.nowpayments.email'),
                    'password' => config('services.nowpayments.password')
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['token'])) {
                $this->jwtToken = $result['token'];
                Log::info('NOWPayments: Successfully authenticated and got JWT token');
                return $this->jwtToken;
            }

            Log::error('NOWPayments Authentication failed: No token in response', $result);
            return false;

        } catch (RequestException $e) {
            Log::error('NOWPayments Authentication Error: ' . $e->getMessage());
            if ($e->hasResponse()) {
                Log::error('Auth Response Status: ' . $e->getResponse()->getStatusCode());
                Log::error('Auth Response Body: ' . $e->getResponse()->getBody());
            }
            return false;
        }
    }

    /**
     * Get client with JWT token for authenticated requests
     */
    private function getAuthenticatedClient()
    {
        $token = $this->authenticate();

        if (!$token) {
            return false;
        }

        return new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'x-api-key' => $this->apiKey,  // Include API key as well
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ]);
    }

    /**
     * Get all payments (payment history) - try multiple API versions and endpoints
     */
    public function listPayments($params = [])
    {
        try {
            $client = $this->getAuthenticatedClient();

            if (!$client) {
                Log::error('NOWPayments: Failed to get authenticated client');
                return false;
            }

            // Try different endpoints that might work
            $endpoints = [
                '/payments',           // Root level payments endpoint
                '/payment',            // Root level payment endpoint
                '/v1/payment',         // V1 payment endpoint
                '/v1/payments',        // V1 payments endpoint
                '/v2/payment',         // V2 payment endpoint
                '/v2/payments'         // V2 payments endpoint
            ];

            // Start with no default parameters
            $defaultParams = [];
            $params = array_merge($defaultParams, $params);

            foreach ($endpoints as $endpoint) {
                try {
                    Log::info("Trying NOWPayments endpoint: {$endpoint}");

                    $response = $client->get($endpoint, [
                        'query' => $params
                    ]);

                    $result = json_decode($response->getBody()->getContents(), true);

                    // Log the successful response
                    Log::info('NOWPayments API Success', [
                        'endpoint' => $endpoint,
                        'status_code' => $response->getStatusCode(),
                        'response_structure' => array_keys($result ?? [])
                    ]);

                    return $result;

                } catch (RequestException $e) {
                    Log::warning("NOWPayments endpoint {$endpoint} failed", [
                        'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'no_response',
                        'message' => $e->getMessage()
                    ]);

                    // Continue to next endpoint
                    continue;
                }
            }

            // If all endpoints failed, return false
            Log::error('All NOWPayments endpoints failed');
            return false;

        } catch (RequestException $e) {
            Log::error('NOWPayments List Payments Error: ' . $e->getMessage());
            if ($e->hasResponse()) {
                Log::error('Response Status: ' . $e->getResponse()->getStatusCode());
                Log::error('Response Body: ' . $e->getResponse()->getBody());
            }
            return false;
        }
    }

    /**
     * Get available currencies
     */
    public function getAvailableCurrencies()
    {
        try {
            $response = $this->client->get('/v1/currencies');
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('NOWPayments API Error: ' . $e->getMessage());
            Log::error('Response: ' . ($e->hasResponse() ? $e->getResponse()->getBody() : 'No response'));
            echo "Error: " . $e->getMessage() . "\n";
            if ($e->hasResponse()) {
                echo "Response: " . $e->getResponse()->getBody() . "\n";
            }
            return false;
        }
    }

    /**
     * Get exchange rate
     */
    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        try {
            $response = $this->client->get("/v1/estimate", [
                'query' => [
                    'amount' => 1,
                    'currency_from' => strtolower($fromCurrency),
                    'currency_to' => strtolower($toCurrency),
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('NOWPayments Exchange Rate Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a payment
     */
    public function createPayment($amount, $priceCurrency = 'USD', $payCurrency = 'btc', $orderId = null, $orderDescription = null)
    {
        try {
            $data = [
                'price_amount' => $amount,
                'price_currency' => strtolower($priceCurrency),
                'pay_currency' => strtolower($payCurrency),
                'order_id' => $orderId ?: uniqid('deposit_'),
                'order_description' => $orderDescription ?: 'Deposit to Unity X Wallet',
                // Re-enable webhook URLs now that the main issue is fixed
                'ipn_callback_url' => route('nowpayments.webhook'),
                'success_url' => route('user.deposit.success'),
                'cancel_url' => route('user.deposit.cancel'),
            ];

            $response = $this->client->post('/v1/payment', [
                'json' => $data
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            // Log successful response for debugging
            Log::info('NOWPayments Create Payment Success', [
                'status_code' => $response->getStatusCode(),
                'response' => $result,
                'request_data' => $data
            ]);

            return $result;
        } catch (RequestException $e) {
            Log::error('NOWPayments Create Payment Error: ' . $e->getMessage());
            Log::error('Request Data: ' . json_encode($data ?? []));
            if ($e->hasResponse()) {
                Log::error('Response: ' . $e->getResponse()->getBody());
            }
            return false;
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus($paymentId)
    {
        try {
            // Try multiple endpoints for payment status
            $endpoints = [
                "/v1/payment/{$paymentId}",
                "/v2/payment/{$paymentId}",
                "/payment/{$paymentId}",
                "/payments/{$paymentId}"
            ];

            // First try with basic client (no auth)
            foreach ($endpoints as $endpoint) {
                try {
                    Log::info("Trying NOWPayments payment status endpoint: {$endpoint}");
                    $response = $this->client->get($endpoint);
                    $result = json_decode($response->getBody()->getContents(), true);

                    // Log the response structure for debugging
                    Log::info('NOWPayments Payment Status Success', [
                        'endpoint' => $endpoint,
                        'payment_id' => $paymentId,
                        'status_code' => $response->getStatusCode(),
                        'response_keys' => array_keys($result ?? [])
                    ]);

                    if ($result && isset($result['payment_id'])) {
                        return $result;
                    }
                } catch (RequestException $e) {
                    Log::warning("NOWPayments payment status endpoint {$endpoint} failed", [
                        'payment_id' => $paymentId,
                        'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'no_response',
                        'message' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            // If basic client fails, try with authenticated client
            $authClient = $this->getAuthenticatedClient();
            if ($authClient) {
                foreach ($endpoints as $endpoint) {
                    try {
                        Log::info("Trying authenticated NOWPayments payment status endpoint: {$endpoint}");
                        $response = $authClient->get($endpoint);
                        $result = json_decode($response->getBody()->getContents(), true);

                        Log::info('NOWPayments Authenticated Payment Status Success', [
                            'endpoint' => $endpoint,
                            'payment_id' => $paymentId,
                            'status_code' => $response->getStatusCode(),
                            'response_keys' => array_keys($result ?? [])
                        ]);

                        if ($result && isset($result['payment_id'])) {
                            return $result;
                        }
                    } catch (RequestException $e) {
                        Log::warning("NOWPayments authenticated payment status endpoint {$endpoint} failed", [
                            'payment_id' => $paymentId,
                            'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'no_response',
                            'message' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
            }

            Log::error('NOWPayments: All payment status endpoints failed', ['payment_id' => $paymentId]);
            return false;

        } catch (RequestException $e) {
            Log::error('NOWPayments Payment Status Error: ' . $e->getMessage(), [
                'payment_id' => $paymentId
            ]);
            if ($e->hasResponse()) {
                Log::error('Payment Status Response Status: ' . $e->getResponse()->getStatusCode());
                Log::error('Payment Status Response Body: ' . $e->getResponse()->getBody());
            }
            return false;
        }
    }

    /**
     * Get detailed payment information with transaction history
     */
    public function getDetailedPaymentInfo($paymentId)
    {
        try {
            // Try to get payment status first
            $paymentData = $this->getPaymentStatus($paymentId);
            
            if (!$paymentData) {
                Log::warning('Could not retrieve basic payment data for detailed info', ['payment_id' => $paymentId]);
                return false;
            }

            // Try additional endpoints for more detailed transaction information
            $detailEndpoints = [
                "/v1/payment/{$paymentId}/logs",
                "/v1/payment/{$paymentId}/history",
                "/v1/transaction/{$paymentId}",
                "/v2/payment/{$paymentId}/details"
            ];

            $additionalData = [];
            
            // Try to fetch additional transaction details
            foreach ($detailEndpoints as $endpoint) {
                try {
                    Log::info("Trying detailed payment endpoint: {$endpoint}");
                    $response = $this->client->get($endpoint);
                    $result = json_decode($response->getBody()->getContents(), true);
                    
                    if ($result && is_array($result)) {
                        $additionalData[basename($endpoint)] = $result;
                        Log::info("Successfully fetched data from {$endpoint}", [
                            'payment_id' => $paymentId,
                            'data_keys' => array_keys($result)
                        ]);
                    }
                } catch (RequestException $e) {
                    Log::debug("Detail endpoint {$endpoint} failed (expected for some endpoints)", [
                        'payment_id' => $paymentId,
                        'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'no_response'
                    ]);
                    continue;
                }
            }

            // Merge additional data with main payment data
            if (!empty($additionalData)) {
                $paymentData['additional_details'] = $additionalData;
            }

            return $paymentData;

        } catch (\Exception $e) {
            Log::error('Error fetching detailed payment info: ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get minimum payment amount
     */
    public function getMinimumPaymentAmount($currency)
    {
        try {
            $response = $this->client->get("/v1/min-amount", [
                'query' => [
                    'currency_from' => 'usd',
                    'currency_to' => strtolower($currency),
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('NOWPayments Minimum Amount Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        $expectedSignature = hash_hmac('sha512', $payload, config('services.nowpayments.ipn_secret'));
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Test API connection and status
     */
    public function getApiStatus()
    {
        try {
            $response = $this->client->get('/v1/status');
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('NOWPayments API Status Error: ' . $e->getMessage());
            if ($e->hasResponse()) {
                Log::error('Response Status: ' . $e->getResponse()->getStatusCode());
                Log::error('Response Body: ' . $e->getResponse()->getBody());
            }
            return false;
        }
    }

    /**
     * Try to get account/dashboard data
     */
    public function getDashboardData()
    {
        try {
            $client = $this->getAuthenticatedClient();

            if (!$client) {
                Log::error('NOWPayments: Failed to get authenticated client');
                return false;
            }

            // Try dashboard-related endpoints
            $endpoints = [
                '/v1/account',
                '/v1/user',
                '/v1/stats',
                '/v1/statistics',
                '/v1/dashboard'
            ];

            foreach ($endpoints as $endpoint) {
                try {
                    Log::info("Trying NOWPayments dashboard endpoint: {$endpoint}");

                    $response = $client->get($endpoint);

                    $result = json_decode($response->getBody()->getContents(), true);

                    Log::info('NOWPayments Dashboard Success', [
                        'endpoint' => $endpoint,
                        'status_code' => $response->getStatusCode(),
                        'response_structure' => array_keys($result ?? [])
                    ]);

                    return $result;

                } catch (RequestException $e) {
                    Log::warning("NOWPayments dashboard endpoint {$endpoint} failed", [
                        'status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'no_response',
                        'message' => substr($e->getMessage(), 0, 200)
                    ]);
                    continue;
                }
            }

            return false;

        } catch (RequestException $e) {
            Log::error('NOWPayments Dashboard Error: ' . $e->getMessage());
            return false;
        }
    }
}
