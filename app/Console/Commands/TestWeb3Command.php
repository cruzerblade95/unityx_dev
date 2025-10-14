<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Web3Service;
use Exception;

class TestWeb3Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xeusd:test-web3 {--address=0x1234567890123456789012345678901234567890} {--amount=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test XEUSD Web3 service functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testing XEUSD Web3 Service');
        $this->newLine();

        try {
            $web3Service = new Web3Service();

            // Get network info
            $this->info('📡 Network Information:');
            $networkInfo = $web3Service->getNetworkInfo();
            foreach ($networkInfo as $key => $value) {
                $this->line("   {$key}: {$value}");
            }
            $this->newLine();

            // Get owner wallet balance
            $this->info('💰 Owner Wallet Balance:');
            $balance = $web3Service->getOwnerWalletBalance();
            $this->line("   Balance: {$balance} XEUSD");
            $this->newLine();

            // Test transaction
            $address = $this->option('address');
            $amount = (float) $this->option('amount');

            $this->info("🔄 Testing Transfer:");
            $this->line("   To: {$address}");
            $this->line("   Amount: \${$amount} XEUSD");

            $result = $web3Service->sendXEUSD($address, $amount);

            if ($result['success']) {
                $this->info('✅ Transfer Successful!');
                $this->line("   Transaction Hash: {$result['transaction_hash']}");
                $this->line("   Amount Sent: \${$result['amount_sent']}");
                $this->line("   Recipient: {$result['recipient']}");
                if (isset($result['note'])) {
                    $this->warn("   Note: {$result['note']}");
                }

                // Test transaction status check
                $this->newLine();
                $this->info('🔍 Checking Transaction Status:');
                $status = $web3Service->checkTransaction($result['transaction_hash']);

                foreach ($status as $key => $value) {
                    $this->line("   {$key}: {$value}");
                }
            } else {
                $this->error('❌ Transfer Failed!');
                $this->line("   Error: {$result['error']}");
            }

        } catch (Exception $e) {
            $this->error("💥 Error: " . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('🏁 Test completed');
        return 0;
    }
}
