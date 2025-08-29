<?php
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use App\Services\NOWPaymentsService;

$nowPayments = new NOWPaymentsService();
$currencies = $nowPayments->getAvailableCurrencies();

if ($currencies && isset($currencies['currencies'])) {
    $usdtVariants = array_filter($currencies['currencies'], function($currency) {
        return stripos($currency, 'usdt') !== false;
    });

    echo "NOWPayments USDT variants:\n";
    foreach ($usdtVariants as $variant) {
        echo "- $variant\n";
    }

    echo "\nTotal currencies supported: " . count($currencies['currencies']) . "\n";

    // Also check for other stablecoins
    $stablecoins = array_filter($currencies['currencies'], function($currency) {
        return stripos($currency, 'usdc') !== false ||
               stripos($currency, 'dai') !== false ||
               stripos($currency, 'busd') !== false;
    });

    if (!empty($stablecoins)) {
        echo "\nOther stablecoins:\n";
        foreach ($stablecoins as $coin) {
            echo "- $coin\n";
        }
    }
} else {
    echo "Failed to get currencies\n";
    print_r($currencies);
}
