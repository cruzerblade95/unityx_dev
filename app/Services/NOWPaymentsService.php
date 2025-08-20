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
        ]);
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
            $response = $this->client->get("/estimate", [
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
                'ipn_callback_url' => route('nowpayments.webhook'),
                'success_url' => route('user.deposit.success'),
                'cancel_url' => route('user.deposit.cancel'),
            ];

            $response = $this->client->post('/payment', [
                'json' => $data
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('NOWPayments Create Payment Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus($paymentId)
    {
        try {
            $response = $this->client->get("/payment/{$paymentId}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('NOWPayments Payment Status Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get minimum payment amount
     */
    public function getMinimumPaymentAmount($currency)
    {
        try {
            $response = $this->client->get("/min-amount", [
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
}
