<?php
/**
 * Debug Web3 Balance Checking
 *
 * This script debugs the balance checking to see what's going wrong
 * Run: php debug-web3-balance.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=================================================\n";
echo "Web3 Balance Debug\n";
echo "=================================================\n\n";

$rpcUrl = config('web3.bsc.mainnet.rpc_url');
$contractAddress = config('web3.xeusd.contract_address');
$ownerAddress = config('web3.xeusd.owner_wallet.address');

echo "Configuration:\n";
echo "  RPC URL: {$rpcUrl}\n";
echo "  Contract: {$contractAddress}\n";
echo "  Owner: {$ownerAddress}\n\n";

// Test 1: Check BNB Balance
echo "Test 1: Checking BNB Balance\n";
echo "----------------------------------------\n";

try {
    $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
        ->timeout(30)
        ->post($rpcUrl, [
            'jsonrpc' => '2.0',
            'method' => 'eth_getBalance',
            'params' => [$ownerAddress, 'latest'],
            'id' => 1
        ]);

    echo "HTTP Status: " . $response->status() . "\n";

    if (!$response->successful()) {
        echo "❌ Request failed!\n";
        echo "Response body: " . $response->body() . "\n";
    } else {
        $data = $response->json();
        echo "Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";

        if (isset($data['result'])) {
            $balanceWei = hexdec($data['result']);
            $balanceBNB = floatval(bcdiv(strval($balanceWei), '1000000000000000000', 18));
            echo "✓ BNB Balance: {$balanceBNB} BNB\n";
        } elseif (isset($data['error'])) {
            echo "❌ RPC Error: " . json_encode($data['error']) . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Check XEUSD Balance
echo "Test 2: Checking XEUSD Balance\n";
echo "----------------------------------------\n";

try {
    $balanceOfABI = '0x70a08231'; // balanceOf(address)
    $data = $balanceOfABI . str_pad(substr($ownerAddress, 2), 64, '0', STR_PAD_LEFT);

    echo "Contract call data: {$data}\n\n";

    $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
        ->timeout(30)
        ->post($rpcUrl, [
            'jsonrpc' => '2.0',
            'method' => 'eth_call',
            'params' => [
                [
                    'to' => $contractAddress,
                    'data' => $data
                ],
                'latest'
            ],
            'id' => 1
        ]);

    echo "HTTP Status: " . $response->status() . "\n";

    if (!$response->successful()) {
        echo "❌ Request failed!\n";
        echo "Response body: " . $response->body() . "\n";
    } else {
        $responseData = $response->json();
        echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";

        if (isset($responseData['result'])) {
            $balanceWei = hexdec($responseData['result']);
            $balanceXEUSD = floatval(bcdiv(strval($balanceWei), '1000000000000000000', 18));
            echo "✓ XEUSD Balance: {$balanceXEUSD} XEUSD\n";
        } elseif (isset($responseData['error'])) {
            echo "❌ RPC Error: " . json_encode($responseData['error']) . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=================================================\n";
echo "Verify on BSCScan:\n";
echo "https://bscscan.com/address/{$ownerAddress}\n";
echo "https://bscscan.com/token/{$contractAddress}?a={$ownerAddress}\n";
echo "=================================================\n";
