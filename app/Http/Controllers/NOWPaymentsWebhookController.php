<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Wallet;
use App\Services\NOWPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helpers;

class NOWPaymentsWebhookController extends Controller
{
    protected $nowPayments;

    public function __construct(NOWPaymentsService $nowPayments)
    {
        $this->nowPayments = $nowPayments;
    }

    public function handle(Request $request)
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('x-nowpayments-sig');

            // Verify webhook signature
            if (!$this->nowPayments->verifyWebhookSignature($payload, $signature)) {
                Log::warning('NOWPayments webhook signature verification failed');
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $data = json_decode($payload, true);

            if (!$data || !isset($data['payment_id'])) {
                Log::warning('NOWPayments webhook: Invalid payload', ['payload' => $payload]);
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            // Find the deposit by payment_id
            $deposit = Deposit::where('payment_id', $data['payment_id'])->first();

            if (!$deposit) {
                Log::warning('NOWPayments webhook: Deposit not found', ['payment_id' => $data['payment_id']]);
                return response()->json(['error' => 'Deposit not found'], 404);
            }

            $paymentStatus = $data['payment_status'];
            $previousStatus = $deposit->status;

            Log::info('NOWPayments webhook received', [
                'payment_id' => $data['payment_id'],
                'status' => $paymentStatus,
                'previous_status' => $previousStatus,
                'deposit_id' => $deposit->id,
                'outcome_amount' => $data['outcome_amount'] ?? 'not_provided'
            ]);

            // Update deposit status based on payment status
            switch ($paymentStatus) {
                case 'finished':
                case 'confirmed':
                    try {
                        $this->processSuccessfulPayment($deposit, $data, $previousStatus);
                        Log::info('NOWPayments webhook: Payment processed successfully', [
                            'payment_id' => $data['payment_id'],
                            'deposit_id' => $deposit->id,
                            'user_id' => $deposit->user_id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('NOWPayments webhook: Failed to process successful payment', [
                            'payment_id' => $data['payment_id'],
                            'deposit_id' => $deposit->id,
                            'error' => $e->getMessage(),
                            'previous_status' => $previousStatus
                        ]);

                        // Don't update status to completed if processing failed
                        // This allows for manual intervention or retry
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Payment processing failed: ' . $e->getMessage()
                        ], 500);
                    }
                    break;

                case 'failed':
                case 'refunded':
                    $deposit->update(['status' => 'failed']);
                    break;

                case 'expired':
                    $deposit->update(['status' => 'expired']);
                    break;

                case 'partially_paid':
                    $deposit->update(['status' => 'partially_paid']);
                    break;

                case 'sending':
                case 'confirming':
                    $deposit->update(['status' => 'processing']);
                    break;

                default:
                    Log::info('NOWPayments webhook: Unknown status', ['status' => $paymentStatus]);
                    break;
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('NOWPayments webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    private function processSuccessfulPayment(Deposit $deposit, array $data, string $previousStatus)
    {
        // Start database transaction to ensure atomic operations
        \DB::beginTransaction();

        try {
            // Determine the amount to credit based on the outcome_amount or fallback to net_amount
            $outcomeAmount = isset($data['outcome_amount']) ? round((float)$data['outcome_amount'], 2) : null;
            $creditAmount = $outcomeAmount ?? $deposit->net_amount;

            // Validate credit amount is positive
            if ($creditAmount <= 0) {
                Log::error('Invalid credit amount for deposit', [
                    'deposit_id' => $deposit->id,
                    'outcome_amount' => $outcomeAmount,
                    'net_amount' => $deposit->net_amount,
                    'calculated_credit' => $creditAmount
                ]);
                throw new \Exception("Invalid credit amount: {$creditAmount}");
            }

            // Validate user exists and has proper relationship
            $user = $deposit->user;
            if (!$user) {
                Log::error('User not found for deposit', ['deposit_id' => $deposit->id]);
                throw new \Exception("User not found for deposit ID: {$deposit->id}");
            }

            // Update deposit status and related fields
            $deposit->update([
                'status' => 'completed',
                'crypto_amount' => $data['pay_amount'] ?? $deposit->crypto_amount,
                'payment_address' => $data['pay_address'] ?? $deposit->payment_address,
            ]);

            // Only credit wallet if the payment wasn't already completed
            // This prevents double-crediting for status changes within completed states
            if ($previousStatus !== 'completed') {
                // Check if user has a wallet, create one if not
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['deposit' => 0, 'withdrawal' => 0, 'investment' => 0, 'bonus' => 0]
                );

                // Store original balance for verification
                $originalBalance = $wallet->deposit;

                // Credit the wallet with the appropriate amount
                $wallet->deposit += $creditAmount;
                $wallet->save();

                // Verify wallet was actually credited by reloading from database
                $wallet = $wallet->fresh();
                $actualCreditedAmount = $wallet->deposit - $originalBalance;

                if (abs($actualCreditedAmount - $creditAmount) > 0.01) { // Allow for small rounding differences
                    Log::error('Wallet credit verification failed', [
                        'deposit_id' => $deposit->id,
                        'user_id' => $user->id,
                        'expected_credit' => $creditAmount,
                        'actual_credit' => $actualCreditedAmount,
                        'original_balance' => $originalBalance,
                        'new_balance' => $wallet->deposit
                    ]);
                    throw new \Exception("Wallet credit verification failed - expected: {$creditAmount}, actual: {$actualCreditedAmount}");
                }

                // Add AI trade and bonus to upline (use the credited amount for calculations)
                Helpers::insertAiTradeV2($user->id, $creditAmount);
                Helpers::depositBonus($user->id, $creditAmount);
                Helpers::processDepositPromoBonus($user->id, $creditAmount, $deposit->id);

                Log::info('Payment processed successfully', [
                    'deposit_id' => $deposit->id,
                    'user_id' => $user->id,
                    'previous_status' => $previousStatus,
                    'credited_amount' => $creditAmount,
                    'actual_credited_amount' => $actualCreditedAmount,
                    'outcome_amount_from_nowpayments' => $outcomeAmount,
                    'net_amount_fallback' => $deposit->net_amount,
                    'original_wallet_balance' => $originalBalance,
                    'new_wallet_balance' => $wallet->deposit,
                    'was_partial_payment' => $previousStatus === 'partially_paid',
                    'verification_passed' => true
                ]);

                // Log special case for partially paid to completed transitions
                if ($previousStatus === 'partially_paid') {
                    Log::info('Partially paid deposit completed - user credited with outcome amount', [
                        'deposit_id' => $deposit->id,
                        'user_id' => $user->id,
                        'outcome_amount' => $outcomeAmount,
                        'original_requested_amount' => $deposit->amount,
                        'net_amount' => $deposit->net_amount,
                        'verified_credit' => $actualCreditedAmount
                    ]);
                }
            } else {
                Log::info('Payment already completed - no wallet credit applied', [
                    'deposit_id' => $deposit->id,
                    'previous_status' => $previousStatus,
                    'current_status' => 'completed'
                ]);
            }

            // Commit transaction if all operations succeeded
            \DB::commit();

            // Here you can also send notification to user about successful deposit
            // You can implement email notification or other notification methods

        } catch (\Exception $e) {
            // Rollback transaction on any failure
            \DB::rollBack();

            Log::error('Payment processing failed - transaction rolled back', [
                'deposit_id' => $deposit->id,
                'error_message' => $e->getMessage(),
                'previous_status' => $previousStatus,
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw exception to be handled by calling function
            throw $e;
        }
    }
}
