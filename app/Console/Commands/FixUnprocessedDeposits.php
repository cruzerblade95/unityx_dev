<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deposit;
use App\Models\Wallet;
use App\Services\NOWPaymentsService;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FixUnprocessedDeposits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deposits:fix-unprocessed
                            {--dry-run : Show what would be done without actually doing it}
                            {--payment-id= : Fix a specific payment ID}
                            {--since= : Only check deposits since this date (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix completed deposits that were not properly credited to user wallets';

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
        $this->info('🔍 Scanning for unprocessed completed deposits...');

        $isDryRun = $this->option('dry-run');
        $paymentId = $this->option('payment-id');
        $since = $this->option('since');

        // Build query
        $query = Deposit::where('status', 'completed')
                       ->where('payment_method', 'crypto')
                       ->whereNotNull('payment_id');

        if ($paymentId) {
            $query->where('payment_id', $paymentId);
        }

        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        $deposits = $query->with(['user', 'user.wallet'])->orderBy('created_at', 'desc')->get();

        if ($deposits->isEmpty()) {
            $this->info('✅ No completed deposits found matching criteria.');
            return;
        }

        $this->info("Found {$deposits->count()} completed deposit(s) to check.");

        $processed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($deposits as $deposit) {
            $username = $deposit->user ? $deposit->user->username : 'N/A';
            $this->line("📄 Checking Deposit ID: {$deposit->id} | User: {$username} | Amount: \${$deposit->net_amount}");

            // Check if user has wallet
            if (!$deposit->user) {
                $this->error("  ❌ User not found for deposit {$deposit->id}");
                $errors++;
                continue;
            }

            $wallet = $deposit->user->wallet;
            if (!$wallet) {
                $this->warn("  ⚠️  No wallet found for user " . $deposit->user->username);

                if (!$isDryRun) {
                    $this->info("  🔧 Creating wallet for user " . $deposit->user->username);
                    $wallet = Wallet::create([
                        'user_id' => $deposit->user->id,
                        'deposit' => 0,
                        'withdrawal' => 0,
                        'investment' => 0,
                        'bonus' => 0
                    ]);
                }
            }

            // Try to determine if this deposit was already processed
            $suspiciouslyProcessed = $this->checkIfAlreadyProcessed($deposit);

            if ($suspiciouslyProcessed) {
                $this->info("  ✅ Deposit appears to have been processed already (wallet balance suggests crediting occurred)");
                $skipped++;
                continue;
            }

            // Get fresh payment data from NOWPayments
            $creditAmount = $deposit->net_amount;
            if ($deposit->payment_id) {
                $paymentData = $this->nowPayments->getPaymentStatus($deposit->payment_id);
                if ($paymentData && isset($paymentData['outcome_amount'])) {
                    $outcomeAmount = round((float)$paymentData['outcome_amount'], 2);
                    if ($outcomeAmount > 0) {
                        $creditAmount = $outcomeAmount;
                        $this->info("  💰 Using NOWPayments outcome amount: \${$outcomeAmount}");
                    }
                }
            }

            if ($creditAmount <= 0) {
                $this->error("  ❌ Invalid credit amount: \${$creditAmount}");
                $errors++;
                continue;
            }

            if ($isDryRun) {
                $this->info("  🔍 [DRY RUN] Would credit \${$creditAmount} to " . $deposit->user->username . "'s wallet");
                $processed++;
            } else {
                // Actually process the credit
                $result = $this->processDepositCredit($deposit, $creditAmount);
                if ($result['success']) {
                    $this->info("  ✅ Successfully credited \${$creditAmount} to " . $deposit->user->username . "'s wallet");
                    $processed++;
                } else {
                    $this->error("  ❌ Failed to process: " . $result['error']);
                    $errors++;
                }
            }
        }

        // Summary
        $this->newLine();
        $this->info('📊 Summary:');
        $this->info("  Processed: {$processed}");
        $this->info("  Skipped: {$skipped}");
        $this->info("  Errors: {$errors}");

        if ($isDryRun) {
            $this->warn('🔍 This was a dry run. No actual changes were made.');
            $this->info('💡 Run without --dry-run to apply the fixes.');
        }
    }

    private function checkIfAlreadyProcessed(Deposit $deposit): bool
    {
        // This is a heuristic check to see if the deposit might have already been processed
        // Look for signs that the wallet was credited around the time of the deposit

        $wallet = $deposit->user->wallet;
        if (!$wallet) {
            return false; // No wallet = definitely not processed
        }

        // Check if wallet has enough balance to suggest this deposit was processed
        // This is not foolproof but helps avoid double-crediting
        $totalWalletBalance = $wallet->deposit + $wallet->profit + $wallet->bonus + $wallet->cash;

        // If total balance is significantly higher than this deposit amount,
        // it might have been processed already
        if ($totalWalletBalance >= $deposit->net_amount) {
            // Additional check: look at wallet update time vs deposit creation time
            if ($wallet->updated_at && $wallet->updated_at >= $deposit->created_at) {
                return true; // Likely already processed
            }
        }

        return false;
    }

    private function processDepositCredit(Deposit $deposit, float $creditAmount): array
    {
        DB::beginTransaction();

        try {
            $wallet = $deposit->user->wallet;
            $originalBalance = $wallet->deposit;

            // Credit the wallet
            $wallet->deposit += $creditAmount;
            $wallet->save();

            // Verify the credit
            $wallet = $wallet->fresh();
            $actualCreditedAmount = $wallet->deposit - $originalBalance;

            if (abs($actualCreditedAmount - $creditAmount) > 0.01) {
                throw new \Exception("Credit verification failed - expected: {$creditAmount}, actual: {$actualCreditedAmount}");
            }

            // Process bonuses
            Helpers::insertAiTradeV2($deposit->user->id, $creditAmount);
            Helpers::depositBonus($deposit->user->id, $creditAmount);
            Helpers::processDepositPromoBonus($deposit->user->id, $creditAmount, $deposit->id);

            DB::commit();

            Log::info('Fixed unprocessed deposit via command', [
                'deposit_id' => $deposit->id,
                'user_id' => $deposit->user->id,
                'credited_amount' => $creditAmount,
                'verification_passed' => true
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to fix unprocessed deposit', [
                'deposit_id' => $deposit->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
