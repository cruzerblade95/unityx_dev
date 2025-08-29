<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\NOWPaymentsService;

echo "Testing NOWPayments Integration...\n\n";

try {
    $service = app(NOWPaymentsService::class);

    // Test 1: Get available currencies
    echo "1. Testing API connection...\n";
    $currencies = $service->getAvailableCurrencies();

    if ($currencies && isset($currencies['currencies'])) {
        echo "✓ NOWPayments API connection successful\n";
        echo "Available currencies: " . count($currencies['currencies']) . "\n";
        echo "First 5 currencies: " . implode(', ', array_slice($currencies['currencies'], 0, 5)) . "\n\n";
    } else {
        echo "✗ NOWPayments API connection failed\n";
        echo "Response: " . json_encode($currencies) . "\n";
        exit(1);
    }

    // Test 2: Try creating a payment
    echo "2. Testing payment creation...\n";
    $payment = $service->createPayment(
        101.8, // $100 + 1.8% fee
        'USD',
        'btc',
        'test_order_' . time(),
        'Test Unity X Payment'
    );

    if ($payment) {
        echo "✓ Payment creation successful\n";
        echo "Response structure:\n";
        foreach ($payment as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                echo "  $key: $value\n";
            } else {
                echo "  $key: " . json_encode($value) . "\n";
            }
        }

        // Check for URL fields
        echo "\n3. Checking for payment URLs...\n";
        $urlFields = ['payment_url', 'invoice_url', 'pay_url', 'checkout_url', 'redirect_url'];
        $foundUrl = false;

        foreach ($urlFields as $field) {
            if (isset($payment[$field])) {
                echo "✓ Found URL in '$field': " . $payment[$field] . "\n";
                $foundUrl = true;
            }
        }

        if (!$foundUrl) {
            echo "✗ No payment URL found in response\n";
            echo "Available fields: " . implode(', ', array_keys($payment)) . "\n";
        }

    } else {
        echo "✗ Payment creation failed\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
