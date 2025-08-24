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

            Log::info('NOWPayments webhook received', [
                'payment_id' => $data['payment_id'],
                'status' => $paymentStatus,
                'deposit_id' => $deposit->id
            ]);

            // Update deposit status based on payment status
            switch ($paymentStatus) {
                case 'finished':
                case 'confirmed':
                    $this->processSuccessfulPayment($deposit, $data);
                    break;

                case 'failed':
                case 'expired':
                case 'refunded':
                    $deposit->update(['status' => 'failed']);
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

    private function processSuccessfulPayment(Deposit $deposit, array $data)
    {
        // Update deposit status
        $deposit->update([
            'status' => 'completed',
            'crypto_amount' => $data['pay_amount'] ?? $deposit->crypto_amount,
            'payment_address' => $data['pay_address'] ?? $deposit->payment_address,
        ]);

        // Update user wallet
        $user = $deposit->user;

        // Check if user has a wallet, create one if not
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['deposit' => 0, 'withdrawal' => 0, 'investment' => 0, 'bonus' => 0]
        );

        // Add the net amount to user's deposit wallet
        $wallet->deposit += $deposit->net_amount;
        $wallet->save();

        // Add bonus to upline
        Helpers::depositBonus($user->id, $deposit->net_amount);

        Log::info('Payment processed successfully', [
            'deposit_id' => $deposit->id,
            'user_id' => $user->id,
            'amount' => $deposit->net_amount,
            'new_wallet_balance' => $wallet->deposit
        ]);

        // Here you can also send notification to user about successful deposit
        // You can implement email notification or other notification methods
    }
}
