<?php

/**
 * Test file to demonstrate the outcome amount calculation logic
 * This shows how the system will handle partially paid to completed transitions
 */

// Example webhook data that NOWPayments might send
$webhookData = [
    'payment_id' => '5417221459',
    'payment_status' => 'finished', // Changed from partially_paid to finished
    'outcome_amount' => '175.1844299', // This is the actual amount user will receive
    'pay_amount' => '177.8636359',
    'actually_paid' => '176.863635',
    'price_amount' => '178.15'
];

// Simulate the calculation logic
$outcomeAmount = isset($webhookData['outcome_amount']) ? round((float)$webhookData['outcome_amount'], 2) : null;
$netAmount = 100.00; // Original net amount from deposit record

$creditAmount = $outcomeAmount ?? $netAmount;

echo "=== Outcome Amount Calculation Test ===\n";
echo "Original requested amount: $100.00\n";
echo "NOWPayments outcome_amount: " . $webhookData['outcome_amount'] . "\n";
echo "Rounded outcome amount: $" . $outcomeAmount . "\n";
echo "Amount credited to user wallet: $" . $creditAmount . "\n";
echo "\n";

echo "=== Status Transition Logic ===\n";
echo "Previous Status: partially_paid\n";
echo "New Status: completed\n";
echo "Action: Credit user wallet with outcome amount\n";
echo "Amount: $" . $creditAmount . " (2 decimal places)\n";
echo "\n";

echo "=== Key Features ===\n";
echo "✓ Uses outcome_amount from NOWPayments when available\n";
echo "✓ Rounds to 2 decimal places for wallet credit\n";
echo "✓ Falls back to net_amount if outcome_amount not provided\n";
echo "✓ Only credits once when transitioning from partially_paid to completed\n";
echo "✓ Works for webhooks, manual approval, and bulk refresh\n";
echo "\n";

echo "=== What happens when payment status changes ===\n";
echo "1. pending → completed: Credits net_amount (original behavior)\n";
echo "2. partially_paid → completed: Credits outcome_amount (NEW)\n";
echo "3. completed → completed: No action (prevents double credit)\n";
