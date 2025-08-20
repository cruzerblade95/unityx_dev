<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NOWPaymentsService;

class TestNOWPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nowpayments:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test NOWPayments API integration';

    protected $nowPayments;

    public function __construct(NOWPaymentsService $nowPayments)
    {
        parent::__construct();
        $this->nowPayments = $nowPayments;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing NOWPayments integration...');

        // Test 1: Get available currencies
        $this->info('1. Testing available currencies...');
        $this->line('API Key: ' . substr(config('services.nowpayments.api_key'), 0, 8) . '...');
        $this->line('Sandbox Mode: ' . (config('services.nowpayments.sandbox') ? 'true' : 'false'));
        
        $currencies = $this->nowPayments->getAvailableCurrencies();
        
        if ($currencies) {
            $this->info('✓ Successfully connected to NOWPayments API');
            $this->info('Available currencies: ' . count($currencies['currencies']) . ' currencies found');
            $this->line('First 10 currencies: ' . implode(', ', array_slice($currencies['currencies'], 0, 10)));
        } else {
            $this->error('✗ Failed to connect to NOWPayments API');
            $this->error('Please check your API key and internet connection');
            return 1;
        }

        // Test 2: Get exchange rate
        $this->info('2. Testing exchange rate (1 USD to BTC)...');
        $rate = $this->nowPayments->getExchangeRate('USD', 'BTC');
        
        if ($rate) {
            $this->info('✓ Exchange rate retrieved successfully');
            $this->line('1 USD = ' . $rate['estimated_amount'] . ' BTC');
        } else {
            $this->warn('⚠ Could not retrieve exchange rate (might be due to sandbox mode)');
        }

        // Test 3: Get minimum payment amount
        $this->info('3. Testing minimum payment amount for BTC...');
        $minAmount = $this->nowPayments->getMinimumPaymentAmount('BTC');
        
        if ($minAmount) {
            $this->info('✓ Minimum amount retrieved successfully');
            $this->line('Minimum BTC amount: ' . $minAmount['min_amount'] . ' BTC');
        } else {
            $this->warn('⚠ Could not retrieve minimum amount');
        }

        $this->info('');
        $this->info('NOWPayments integration test completed!');
        $this->info('Your integration is ready to use.');
        $this->info('');
        $this->comment('Next steps:');
        $this->comment('1. Set your production API keys in .env file');
        $this->comment('2. Set NOWPAYMENTS_SANDBOX=false for production');
        $this->comment('3. Configure your webhook URL in NOWPayments dashboard');
        $this->comment('4. Test with a small deposit amount');

        return 0;
    }
}
