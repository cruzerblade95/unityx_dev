<?php
/**
 * Test Web3 Integration
 *
 * This script tests the XEUSD blockchain integration
 * Run: php test-web3.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=================================================\n";
echo "XEUSD Web3 Integration Test\n";
echo "=================================================\n\n";

try {
    $web3 = new \App\Services\Web3Service();

    // Get network info
    $networkInfo = $web3->getNetworkInfo();
    echo "Network Configuration:\n";
    echo "  Environment: " . $networkInfo['network'] . "\n";
    echo "  Chain ID: " . $networkInfo['chain_id'] . "\n";
    echo "  RPC URL: " . $networkInfo['rpc_url'] . "\n";
    echo "  Contract: " . $networkInfo['contract_address'] . "\n";
    echo "  Owner Wallet: " . $networkInfo['owner_wallet'] . "\n";
    echo "  Status: " . $networkInfo['status'] . "\n\n";

    // Check balances
    echo "Wallet Balances:\n";
    echo "  Checking XEUSD balance...\n";
    $xeusdBalance = $web3->getOwnerWalletBalance();
    echo "  ✓ XEUSD Balance: " . number_format($xeusdBalance, 2) . " XEUSD\n";

    echo "  Checking BNB balance...\n";
    $bnbBalance = $web3->getOwnerBNBBalance();
    echo "  ✓ BNB Balance: " . number_format($bnbBalance, 6) . " BNB\n\n";

    // Balance warnings
    if ($bnbBalance < 0.001) {
        echo "⚠️  WARNING: Low BNB balance! You need at least 0.001 BNB for gas fees.\n";
        echo "   Send more BNB to: " . $networkInfo['owner_wallet'] . "\n\n";
    } elseif ($bnbBalance < 0.005) {
        echo "⚠️  NOTICE: BNB balance is low but sufficient for a few transactions.\n";
        echo "   Current: " . number_format($bnbBalance, 6) . " BNB (~" . floor($bnbBalance / 0.0005) . " transactions)\n";
        echo "   Consider adding more for continuous operation.\n\n";
    }

    if ($xeusdBalance < 100) {
        echo "⚠️  WARNING: Low XEUSD balance! Consider adding more tokens.\n";
        echo "   Current: " . number_format($xeusdBalance, 2) . " XEUSD\n\n";
    }

    // Ready status
    if ($bnbBalance >= 0.001 && $xeusdBalance >= 1) {
        echo "✅ System ready to process withdrawals!\n";
        echo "   XEUSD Available: " . number_format($xeusdBalance, 2) . " XEUSD\n";
        echo "   BNB for Gas: " . number_format($bnbBalance, 6) . " BNB (~" . floor($bnbBalance / 0.0005) . " transactions)\n";
        echo "   Estimated cost per transaction: ~0.0003-0.0005 BNB\n";
    } else {
        echo "❌ System NOT ready. Please fund your wallet:\n";
        if ($bnbBalance < 0.001) {
            echo "   - Need BNB for gas fees\n";
        }
        if ($xeusdBalance < 1) {
            echo "   - Need XEUSD tokens to send\n";
        }
    }    echo "\n=================================================\n";
    echo "View your wallet on BSCScan:\n";
    if ($networkInfo['network'] === 'mainnet') {
        echo "https://bscscan.com/address/" . $networkInfo['owner_wallet'] . "\n";
    } else {
        echo "https://testnet.bscscan.com/address/" . $networkInfo['owner_wallet'] . "\n";
    }
    echo "=================================================\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. .env file has all required Web3 configuration\n";
    echo "2. Node.js is installed (run: node --version)\n";
    echo "3. Web3 scripts are installed (run: cd scripts/web3 && npm install)\n";
    echo "4. RPC URL is accessible\n";
    exit(1);
}
