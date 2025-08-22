<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NOWPaymentsService;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NOWPaymentsController extends Controller
{
    protected $nowPayments;

    public function __construct(NOWPaymentsService $nowPayments)
    {
        $this->nowPayments = $nowPayments;
    }

    /**
     * Display a listing of payments from NOWPayments.
     */
    public function index(Request $request)
    {
        try {
            // Get ALL local deposits (including those without NOWPayments payment_id)
            $localPayments = Deposit::with('user:id,firstname,lastname,email')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Try to enrich local payments with NOWPayments data (only for those with payment_id)
            $enrichedPayments = [];
            foreach ($localPayments as $deposit) {
                if ($deposit->payment_id) {
                    $paymentData = $this->nowPayments->getPaymentStatus($deposit->payment_id);
                    
                    $enrichedPayments[] = [
                        'local_deposit' => $deposit,
                        'nowpayments_data' => $paymentData ?: null,
                        'payment_id' => $deposit->payment_id,
                        'order_id' => $paymentData ? ($paymentData['order_id'] ?? null) : null,
                        'payment_status' => $paymentData ? ($paymentData['payment_status'] ?? 'unknown') : 'api_error',
                        'amount' => $deposit->amount,
                        'user' => $deposit->user,
                        'created_at' => $deposit->created_at
                    ];
                }
            }
            
            // Try to fetch NOWPayments bulk data (this will likely fail due to restrictions)
            $apiPayments = $this->nowPayments->listPayments();
            $apiError = null;
            
            if ($apiPayments === false) {
                $apiError = 'NOWPayments payment list API is restricted. Showing individual payment data instead.';
            }
            
            return view('admin.nowpayments.index', [
                'localPayments' => $localPayments,
                'enrichedPayments' => $enrichedPayments,
                'apiPayments' => $apiPayments,
                'apiError' => $apiError
            ]);
            
        } catch (\Exception $e) {
            Log::error('NOWPayments Controller Error: ' . $e->getMessage());
            return view('admin.nowpayments.index', [
                'localPayments' => collect(),
                'enrichedPayments' => [],
                'apiPayments' => null,
                'apiError' => 'An error occurred while fetching payments'
            ]);
        }
    }

    public function approveDeposit($depositId)
    {
        try {
            $deposit = Deposit::findOrFail($depositId);
            
            if ($deposit->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is already completed'
                ]);
            }
            
            // Update deposit status
            $deposit->status = 'completed';
            $deposit->save();
            
            // Update user wallet balance (add to deposit balance)
            if ($deposit->user && $deposit->user->wallet) {
                $deposit->user->wallet->deposit += $deposit->net_amount;
                $deposit->user->wallet->save();
            }
            
            Log::info('Deposit approved manually', [
                'deposit_id' => $deposit->id,
                'user_id' => $deposit->user_id,
                'amount' => $deposit->net_amount
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Payment approved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error approving deposit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function refreshStatus($paymentId)
    {
        try {
            $payment = $this->nowPayments->getPaymentStatus($paymentId);
            
            if ($payment) {
                // Update local deposit record
                $deposit = Deposit::where('payment_id', $paymentId)->first();
                if ($deposit) {
                    $deposit->status = $payment['payment_status'] ?? $deposit->status;
                    $deposit->save();
                }
                
                return response()->json([
                    'success' => true,
                    'payment' => $payment
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error refreshing payment status: ' . $e->getMessage()
            ], 500);
        }
    }
}
