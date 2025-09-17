<?php

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

echo "Testing MYR Conversion Rate...\n";
echo "================================\n";

try {
    $rate = App\Models\WithdrawalSettings::getMyrConversionRate();
    echo "✅ MYR Rate: " . $rate . "\n";

    // Test conversion
    $testAmount = 100;
    $convertedAmount = $testAmount * $rate;
    echo "✅ Test: $" . $testAmount . " USD = RM " . number_format($convertedAmount, 2) . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nTesting complete.\n";
