<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Wallet;
use App\Services\NOWPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
                    $this->processSuccessfulPayment($deposit, $data, $previousStatus);
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
        // Determine the amount to credit based on the outcome_amount or fallback to net_amount
        $outcomeAmount = isset($data['outcome_amount']) ? round((float)$data['outcome_amount'], 2) : null;
        $creditAmount = $outcomeAmount ?? $deposit->net_amount;
        
        // Update deposit status and related fields
        $deposit->update([
            'status' => 'completed',
            'crypto_amount' => $data['pay_amount'] ?? $deposit->crypto_amount,
            'payment_address' => $data['pay_address'] ?? $deposit->payment_address,
        ]);

        // Only credit wallet if the payment wasn't already completed
        // This prevents double-crediting for status changes within completed states
        if ($previousStatus !== 'completed') {
            $user = $deposit->user;

            // Check if user has a wallet, create one if not
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['deposit' => 0, 'withdrawal' => 0, 'investment' => 0, 'bonus' => 0]
            );

            // Credit the wallet with the appropriate amount
            $wallet->deposit += $creditAmount;
            $wallet->save();

            // Add AI trade and bonus to upline (use the credited amount for calculations)
            Helpers::insertAiTradeV2($user->id, $creditAmount);
            Helpers::depositBonus($user->id, $creditAmount);

            Log::info('Payment processed successfully', [
                'deposit_id' => $deposit->id,
                'user_id' => $user->id,
                'previous_status' => $previousStatus,
                'credited_amount' => $creditAmount,
                'outcome_amount_from_nowpayments' => $outcomeAmount,
                'net_amount_fallback' => $deposit->net_amount,
                'new_wallet_balance' => $wallet->deposit,
                'was_partial_payment' => $previousStatus === 'partially_paid'
            ]);

            // Log special case for partially paid to completed transitions
            if ($previousStatus === 'partially_paid') {
                Log::info('Partially paid deposit completed - user credited with outcome amount', [
                    'deposit_id' => $deposit->id,
                    'user_id' => $user->id,
                    'outcome_amount' => $outcomeAmount,
                    'original_requested_amount' => $deposit->amount,
                    'net_amount' => $deposit->net_amount
                ]);
            }
        } else {
            Log::info('Payment already completed - no wallet credit applied', [
                'deposit_id' => $deposit->id,
                'previous_status' => $previousStatus,
                'current_status' => 'completed'
            ]);
        }

        // Here you can also send notification to user about successful deposit
        // You can implement email notification or other notification methods
    }
}
