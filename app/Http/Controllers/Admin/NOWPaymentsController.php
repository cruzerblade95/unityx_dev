<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NOWPaymentsService;
use App\Services\NotificationService;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Helpers;

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
            // Get statistics for the dashboard cards
            $totalDeposits = Deposit::count();
            $pendingDeposits = Deposit::where('status', 'pending')->count();
            $completedDeposits = Deposit::where('status', 'completed')->count();
            $partiallyPaidDeposits = Deposit::where('status', 'partially_paid')->count();
            $expiredDeposits = Deposit::where('status', 'expired')->count();
            $failedDeposits = Deposit::where('status', 'failed')->count();
            $totalAmount = Deposit::sum('amount');

            return view('admin.nowpayments.index', compact(
                'totalDeposits',
                'pendingDeposits',
                'completedDeposits',
                'partiallyPaidDeposits',
                'expiredDeposits',
                'failedDeposits',
                'totalAmount'
            ));

        } catch (\Exception $e) {
            Log::error('NOWPayments Controller Error: ' . $e->getMessage());
            return view('admin.nowpayments.index', [
                'totalDeposits' => 0,
                'pendingDeposits' => 0,
                'completedDeposits' => 0,
                'partiallyPaidDeposits' => 0,
                'expiredDeposits' => 0,
                'failedDeposits' => 0,
                'totalAmount' => 0
            ]);
        }
    }

    /**
     * Get the list of deposits for DataTables
     */
    public function list(Request $request)
    {
        // Clean any output buffer to prevent BOM characters in JSON response
        if (ob_get_level()) {
            ob_end_clean();
        }

        try {
            $deposits = Deposit::with('user:id,firstname,lastname,email')
                ->select('deposits.*')
                ->orderBy('created_at', 'desc');

            // Apply status filter if provided and not empty
            if ($request->has('status_filter') && !empty($request->status_filter)) {
                $deposits->where('status', $request->status_filter);
            }

            // Apply payment method filter if provided and not empty
            if ($request->has('payment_method_filter') && !empty($request->payment_method_filter)) {
                $paymentMethodFilter = $request->payment_method_filter;

                if ($paymentMethodFilter === 'nowpayments') {
                    // Show records that have a payment_id (NOWPayments transactions)
                    $deposits->whereNotNull('payment_id')->where('payment_id', '!=', '');
                } elseif ($paymentMethodFilter === 'manual') {
                    // Show records without payment_id or with empty payment_id (Manual deposits)
                    $deposits->where(function($query) {
                        $query->whereNull('payment_id')
                              ->orWhere('payment_id', '');
                    });
                } else {
                    // Filter by specific payment method
                    $deposits->where('payment_method', $paymentMethodFilter);
                }
            }

            return DataTables::of($deposits)
                ->addIndexColumn()
                ->addColumn('user', function ($deposit) {
                    if ($deposit->user) {
                        return $deposit->user->firstname . ' ' . $deposit->user->lastname . '<br><small class="text-muted">' . $deposit->user->email . '</small>';
                    }
                    return '<span class="text-muted">Unknown User</span>';
                })
                ->addColumn('amount', function ($deposit) {
                    return '<span class="font-weight-bold text-success">$' . number_format($deposit->amount, 2) . '</span>';
                })
                ->addColumn('payment_id', function ($deposit) {
                    // Check for XEUSD payment method first
                    if ($deposit->payment_method === 'xeusd') {
                        if ($deposit->payment_id) {
                            // Show transaction hash with BSCScan link
                            $shortTxHash = substr($deposit->payment_id, 0, 10) . '...' . substr($deposit->payment_id, -8);
                            return '<a href="https://bscscan.com/tx/' . $deposit->payment_id . '" target="_blank" class="text-decoration-none">' .
                                   '<code class="bg-light p-1">' . $shortTxHash . '</code></a>' .
                                   '<br><span class="badge bg-success">XEUSD</span>';
                        } else {
                            return '<span class="badge bg-success">XEUSD</span>';
                        }
                    }

                    // Check for manual deposit
                    if ($deposit->payment_method === 'manual') {
                        return '<span class="badge bg-secondary">Manual</span>';
                    }

                    // Check for NOWPayments or crypto deposits
                    if ($deposit->payment_method === 'nowpayments' || $deposit->payment_method === 'crypto' || $deposit->payment_id) {
                        if ($deposit->payment_id) {
                            return '<code class="bg-light p-1">' . $deposit->payment_id . '</code><br><small class="text-muted">NOWPayments</small>';
                        } else {
                            return '<span class="badge bg-info">NOWPayments</span>';
                        }
                    }

                    // For any other payment method
                    if ($deposit->payment_method) {
                        return '<span class="badge bg-info">' . ucfirst($deposit->payment_method) . '</span>';
                    }

                    return '<span class="badge bg-warning">Pending</span>';
                })
                ->addColumn('status', function ($deposit) {
                    $statusClass = match($deposit->status) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'partially_paid' => 'info',
                        'cancelled' => 'secondary',
                        'expired' => 'danger',
                        'failed' => 'danger',
                        'processing' => 'primary',
                        default => 'secondary'
                    };

                    $statusLabel = match($deposit->status) {
                        'partially_paid' => 'Partially Paid',
                        'expired' => 'Expired',
                        default => ucfirst($deposit->status)
                    };

                    return '<span class="badge bg-' . $statusClass . '">' . $statusLabel . '</span>';
                })
                ->addColumn('created_at', function ($deposit) {
                    return $deposit->created_at->format('M d, Y H:i') . '<br><small class="text-muted">' . $deposit->created_at->diffForHumans() . '</small>';
                })
                ->addColumn('actions', function ($deposit) {
                    $actions = '<button class="btn btn-sm btn-outline-info" onclick="showDepositDetails(' . $deposit->id . ', this)">
                                    <i class="fas fa-info-circle"></i> Details
                                </button>';

                    // Add refresh status button ONLY for NOWPayments/crypto deposits (not XEUSD)
                    if ($deposit->payment_id && $deposit->payment_method !== 'xeusd') {
                        $actions .= ' <button class="btn btn-sm btn-outline-warning ml-1" onclick="refreshPaymentStatus(\'' . $deposit->payment_id . '\', ' . $deposit->id . ', this)">
                                        <i class="fas fa-sync"></i> Refresh
                                    </button>';
                    }

                    if ($deposit->status === 'pending') {
                        $actions .= ' <button class="btn btn-sm btn-success ml-1" onclick="approvePayment(' . $deposit->id . ', this)">
                                        <i class="fas fa-check"></i> Approve
                                    </button>';

                        if ($deposit->payment_method === 'manual' || $deposit->payment_method === 'xeusd' || !$deposit->payment_id) {
                            $userName = $deposit->user ? $deposit->user->firstname . ' ' . $deposit->user->lastname : 'Unknown User';
                            $actions .= ' <button class="btn btn-sm btn-danger ml-1" onclick="showRejectModal(' . $deposit->id . ', \'' . $userName . '\', ' . $deposit->amount . ')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>';
                        }
                    }

                    return $actions;
                })
                ->rawColumns(['user', 'amount', 'payment_id', 'status', 'created_at', 'actions'])
                ->make(true);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load deposits'], 500);
        }
    }

    public function approveDeposit($depositId)
    {
        try {
            Log::info('=== APPROVE DEPOSIT CALLED ===', [
                'deposit_id' => $depositId,
                'timestamp' => now()
            ]);

            $deposit = Deposit::findOrFail($depositId);

            Log::info('Deposit loaded', [
                'id' => $deposit->id,
                'payment_method' => $deposit->payment_method,
                'status' => $deposit->status,
                'amount' => $deposit->amount,
                'net_amount' => $deposit->net_amount,
                'payment_id' => $deposit->payment_id
            ]);

            if ($deposit->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is already completed'
                ]);
            }

            // For NOWPayments deposits, try to get the outcome amount
            $creditAmount = $deposit->net_amount; // Default fallback
            $outcomeAmount = null;

            // Only query NOWPayments API for NOWPayments/crypto deposits
            if (($deposit->payment_method === 'nowpayments' || $deposit->payment_method === 'crypto') && $deposit->payment_id) {
                // Try to get updated payment data from NOWPayments
                $nowpaymentsData = $this->nowPayments->getPaymentStatus($deposit->payment_id);
                if ($nowpaymentsData && isset($nowpaymentsData['outcome_amount'])) {
                    $outcomeAmount = round((float)$nowpaymentsData['outcome_amount'], 2);
                    $creditAmount = $outcomeAmount;

                    Log::info('Using outcome amount for manual approval', [
                        'deposit_id' => $deposit->id,
                        'outcome_amount' => $outcomeAmount,
                        'original_net_amount' => $deposit->net_amount
                    ]);
                }
            } elseif ($deposit->payment_method === 'xeusd') {
                // For XEUSD deposits, use the amount (which is already verified on blockchain)
                $creditAmount = $deposit->amount; // XEUSD has no fees, use full amount

                Log::info('Approving XEUSD deposit', [
                    'deposit_id' => $deposit->id,
                    'amount' => $creditAmount,
                    'tx_hash' => $deposit->payment_id
                ]);
            }

            // Validate credit amount is positive
            if ($creditAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credit amount: ' . $creditAmount
                ]);
            }

            // Start database transaction for atomic operation
            DB::beginTransaction();

            try {
                // Update deposit status
                $deposit->status = 'completed';
                $deposit->save();

                // Update user wallet balance (add to deposit balance)
                if ($deposit->user && $deposit->user->wallet) {
                    $originalBalance = $deposit->user->wallet->deposit;
                    $deposit->user->wallet->deposit += $creditAmount;
                    $deposit->user->wallet->save();

                    // Verify the credit was applied
                    $wallet = $deposit->user->wallet->fresh();
                    $actualCreditedAmount = $wallet->deposit - $originalBalance;

                    if (abs($actualCreditedAmount - $creditAmount) > 0.01) {
                        throw new \Exception("Wallet credit verification failed - expected: {$creditAmount}, actual: {$actualCreditedAmount}");
                    }
                } else {
                    throw new \Exception('User or wallet not found');
                }

                // Add bonus to upline (use the credited amount)
                Helpers::insertAiTradeV2($deposit->user_id, $creditAmount);
                Helpers::depositBonus($deposit->user_id, $creditAmount);

                // Process deposit promo bonus if campaign is active
                $promoBonusResult = Helpers::processDepositPromoBonus($deposit->user_id, $creditAmount, $deposit->id);

                // Create success notification for the user
                NotificationService::createDepositSuccessNotification(
                    $deposit->user_id,
                    $creditAmount,
                    $deposit->id
                );

                // Commit transaction if all operations succeeded
                DB::commit();

                Log::info('Deposit approved manually', [
                    'deposit_id' => $deposit->id,
                    'user_id' => $deposit->user_id,
                    'credited_amount' => $creditAmount,
                    'outcome_amount_used' => $outcomeAmount,
                    'original_net_amount' => $deposit->net_amount,
                    'was_nowpayments_deposit' => $deposit->payment_id ? true : false
                ]);

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Error in manual deposit approval transaction: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction failed: ' . $e->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment approved successfully',
                'credited_amount' => $creditAmount,
                'outcome_amount_used' => $outcomeAmount !== null
            ]);

        } catch (\Throwable $e) {
            Log::error('=== ERROR APPROVING DEPOSIT ===', [
                'deposit_id' => $depositId,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error approving payment: ' . $e->getMessage(),
                'debug_info' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    public function rejectDeposit($depositId, Request $request)
    {
        try {
            $deposit = Deposit::findOrFail($depositId);

            if ($deposit->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reject a completed payment'
                ]);
            }

            if ($deposit->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is already rejected'
                ]);
            }

            // Validate reason
            $request->validate([
                'reason' => 'required|string|min:10|max:500'
            ]);

            // Update deposit status
            $deposit->status = 'rejected';
            $deposit->rejection_reason = $request->reason;
            $deposit->rejected_at = now();
            $deposit->rejected_by = Auth::id();
            $deposit->save();

            // Create rejection notification for the user
            NotificationService::createDepositRejectedNotification(
                $deposit->user_id,
                $deposit->amount,
                $request->reason,
                $deposit->id
            );

            Log::info('Deposit rejected manually', [
                'deposit_id' => $deposit->id,
                'user_id' => $deposit->user_id,
                'amount' => $deposit->amount,
                'reason' => $request->reason,
                'rejected_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deposit rejected successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error rejecting deposit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting deposit: ' . $e->getMessage()
            ], 500);
        }
    }

    public function refreshStatus($paymentId)
    {
        try {
            // First check if this payment ID exists in our database
            $deposit = Deposit::where('payment_id', $paymentId)->first();

            // Log the refresh attempt
            Log::info('Admin refreshing payment status', [
                'payment_id' => $paymentId,
                'deposit_exists' => $deposit ? 'yes' : 'no',
                'deposit_status' => $deposit ? $deposit->status : 'N/A'
            ]);

            $payment = $this->nowPayments->getPaymentStatus($paymentId);

            if ($payment) {
                // Log successful API response
                Log::info('NOWPayments API returned payment data', [
                    'payment_id' => $paymentId,
                    'payment_status' => $payment['payment_status'] ?? 'unknown',
                    'payment_data_keys' => array_keys($payment)
                ]);

                // Update local deposit record if it exists
                if ($deposit) {
                    $oldStatus = $deposit->status;
                    $nowpaymentsStatus = $payment['payment_status'] ?? $deposit->status;

                    // Map NOWPayments status to our local status
                    $newStatus = match($nowpaymentsStatus) {
                        'finished', 'confirmed' => 'completed',
                        'failed', 'refunded' => 'failed',
                        'expired' => 'expired',
                        'partially_paid' => 'partially_paid',
                        'sending', 'confirming' => 'processing',
                        'waiting' => 'pending',
                        default => $nowpaymentsStatus
                    };

                    // Handle wallet crediting if transitioning to completed from partially_paid
                    if ($oldStatus === 'partially_paid' && $newStatus === 'completed' && $oldStatus !== $newStatus) {
                        // Get outcome amount and credit user wallet
                        $outcomeAmount = isset($payment['outcome_amount']) ? round((float)$payment['outcome_amount'], 2) : null;
                        $creditAmount = $outcomeAmount ?? $deposit->net_amount;

                        // Check if user has a wallet, create one if not
                        $wallet = \App\Models\Wallet::firstOrCreate(
                            ['user_id' => $deposit->user_id],
                            ['deposit' => 0, 'withdrawal' => 0, 'investment' => 0, 'bonus' => 0]
                        );

                        // Credit the wallet
                        $wallet->deposit += $creditAmount;
                        $wallet->save();

                        // Add bonus to upline
                        Helpers::insertAiTradeV2($deposit->user_id, $creditAmount);
                        Helpers::depositBonus($deposit->user_id, $creditAmount);
                        Helpers::processDepositPromoBonus($deposit->user_id, $creditAmount, $deposit->id);

                        Log::info('Partially paid deposit completed via refresh - user credited', [
                            'deposit_id' => $deposit->id,
                            'user_id' => $deposit->user_id,
                            'credited_amount' => $creditAmount,
                            'outcome_amount' => $outcomeAmount,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus
                        ]);
                    }

                    $deposit->status = $newStatus;
                    $deposit->save();

                    Log::info('Updated local deposit status', [
                        'payment_id' => $paymentId,
                        'old_status' => $oldStatus,
                        'nowpayments_status' => $nowpaymentsStatus,
                        'new_status' => $newStatus,
                        'wallet_credited' => ($oldStatus === 'partially_paid' && $newStatus === 'completed')
                    ]);
                } else {
                    Log::warning('Payment found in NOWPayments but not in local database', [
                        'payment_id' => $paymentId
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'payment' => $payment,
                    'local_deposit_updated' => $deposit ? true : false
                ]);
            }

            Log::warning('NOWPayments API returned null/false for payment', [
                'payment_id' => $paymentId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment not found in NOWPayments system',
                'payment_id' => $paymentId,
                'local_deposit_exists' => $deposit ? true : false
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error refreshing payment status', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error refreshing payment status: ' . $e->getMessage(),
                'payment_id' => $paymentId
            ], 500);
        }
    }

    /**
     * Test NOWPayments API connection and configuration
     */
    public function testApiConnection()
    {
        try {
            // Test basic API connectivity
            $testResult = $this->nowPayments->getApiStatus();

            Log::info('NOWPayments API test result', [
                'result' => $testResult,
                'config_check' => [
                    'api_key_set' => config('services.nowpayments.api_key') ? 'yes' : 'no',
                    'email_set' => config('services.nowpayments.email') ? 'yes' : 'no',
                    'password_set' => config('services.nowpayments.password') ? 'yes' : 'no',
                    'sandbox_mode' => config('services.nowpayments.sandbox', true) ? 'yes' : 'no'
                ]
            ]);

            return response()->json([
                'success' => true,
                'test_result' => $testResult,
                'api_configured' => config('services.nowpayments.api_key') ? true : false,
                'sandbox_mode' => config('services.nowpayments.sandbox', true)
            ]);

        } catch (\Exception $e) {
            Log::error('NOWPayments API test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'API test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show detailed information about a specific deposit
     */
    public function showDepositDetails($depositId)
    {
        try {
            $deposit = Deposit::with('user:id,firstname,lastname,email,username')->find($depositId);

            if (!$deposit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit not found'
                ], 404);
            }

            // Prepare deposit details
            $depositDetails = [
                'id' => $deposit->id,
                'payment_id' => $deposit->payment_id,
                'amount' => $deposit->amount,
                'net_amount' => $deposit->net_amount,
                'tax_amount' => $deposit->tax_amount,
                'tax_percent' => $deposit->tax_percent,
                'status' => $deposit->status,
                'reference' => $deposit->reference,
                'note' => $deposit->note,
                'payment_method' => $deposit->payment_method,
                'payment_url' => $deposit->payment_url,
                'crypto_currency' => $deposit->crypto_currency,
                'crypto_amount' => $deposit->crypto_amount,
                'payment_address' => $deposit->payment_address,
                'unique_address' => $deposit->unique_address,
                'address_memo' => $deposit->address_memo,
                'address_expires_at' => $deposit->address_expires_at,
                'created_at' => $deposit->created_at,
                'updated_at' => $deposit->updated_at,
                'user' => [
                    'id' => $deposit->user->id,
                    'name' => $deposit->user->firstname . ' ' . $deposit->user->lastname,
                    'email' => $deposit->user->email,
                    'username' => $deposit->user->username ?? 'N/A'
                ]
            ];

            // If payment_id exists, try to get NOWPayments data
            $nowpaymentsData = null;
            if ($deposit->payment_id) {
                // Try to get detailed payment information
                $nowpaymentsData = $this->nowPayments->getDetailedPaymentInfo($deposit->payment_id);

                // If detailed info fails, fall back to basic status
                if (!$nowpaymentsData) {
                    $nowpaymentsData = $this->nowPayments->getPaymentStatus($deposit->payment_id);
                }

                // Log what data we received for debugging
                if ($nowpaymentsData) {
                    Log::info('NOWPayments data retrieved successfully', [
                        'payment_id' => $deposit->payment_id,
                        'available_fields' => array_keys($nowpaymentsData),
                        'status' => $nowpaymentsData['payment_status'] ?? 'unknown',
                        'sample_data' => [
                            'payment_id' => $nowpaymentsData['payment_id'] ?? null,
                            'order_id' => $nowpaymentsData['order_id'] ?? null,
                            'price_amount' => $nowpaymentsData['price_amount'] ?? null,
                            'pay_amount' => $nowpaymentsData['pay_amount'] ?? null,
                            'actually_paid' => $nowpaymentsData['actually_paid'] ?? null,
                            'payin_hash' => $nowpaymentsData['payin_hash'] ?? null,
                            'payout_hash' => $nowpaymentsData['payout_hash'] ?? null,
                        ]
                    ]);
                } else {
                    Log::warning('Failed to retrieve NOWPayments data', [
                        'payment_id' => $deposit->payment_id,
                        'deposit_id' => $deposit->id
                    ]);
                }
            }

            Log::info('Deposit details requested', [
                'deposit_id' => $depositId,
                'payment_id' => $deposit->payment_id,
                'user_id' => $deposit->user_id,
                'has_nowpayments_data' => $nowpaymentsData ? 'yes' : 'no'
            ]);

            return response()->json([
                'success' => true,
                'deposit' => $depositDetails,
                'nowpayments_data' => $nowpaymentsData,
                'has_payment_id' => $deposit->payment_id ? true : false,
                'payment_found_in_nowpayments' => $nowpaymentsData ? true : false
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching deposit details', [
                'deposit_id' => $depositId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching deposit details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh status for all NOWPayments deposits
     */
    public function refreshAllStatuses()
    {
        try {
            Log::info('Starting bulk refresh of payment statuses', [
                'user_id' => Auth::id() ?? 'unknown',
                'timestamp' => now(),
                'environment' => app()->environment()
            ]);

            // Get all deposits that have payment_id (NOWPayments deposits)
            $deposits = Deposit::whereNotNull('payment_id')
                ->where('payment_id', '!=', '')
                ->get();

            $totalCount = $deposits->count();
            $updatedCount = 0;
            $updates = [];
            $errors = [];

            Log::info('Found deposits for bulk refresh', [
                'total_deposits' => $totalCount,
                'deposit_ids' => $deposits->pluck('id')->toArray()
            ]);

            if ($totalCount === 0) {
                return response()->json([
                    'success' => true,
                    'total_count' => 0,
                    'updated_count' => 0,
                    'updates' => [],
                    'message' => 'No NOWPayments deposits found to refresh'
                ]);
            }

            foreach ($deposits as $deposit) {
                try {
                    Log::info('Processing deposit for status refresh', [
                        'deposit_id' => $deposit->id,
                        'payment_id' => $deposit->payment_id,
                        'current_status' => $deposit->status
                    ]);

                    $payment = $this->nowPayments->getPaymentStatus($deposit->payment_id);

                    if ($payment && isset($payment['payment_status'])) {
                        $oldStatus = $deposit->status;
                        $nowpaymentsStatus = $payment['payment_status'];

                        // Map NOWPayments status to our local status
                        $newStatus = match($nowpaymentsStatus) {
                            'finished', 'confirmed' => 'completed',
                            'failed', 'refunded' => 'failed',
                            'expired' => 'expired',
                            'partially_paid' => 'partially_paid',
                            'sending', 'confirming' => 'processing',
                            'waiting' => 'pending',
                            default => $nowpaymentsStatus
                        };

                        // Only update if status has changed
                        if ($oldStatus !== $newStatus) {
                            // Handle wallet crediting if transitioning to completed from partially_paid
                            if ($oldStatus === 'partially_paid' && $newStatus === 'completed') {
                                // Get outcome amount and credit user wallet
                                $outcomeAmount = isset($payment['outcome_amount']) ? round((float)$payment['outcome_amount'], 2) : null;
                                $creditAmount = $outcomeAmount ?? $deposit->net_amount;

                                // Check if user has a wallet, create one if not
                                $wallet = \App\Models\Wallet::firstOrCreate(
                                    ['user_id' => $deposit->user_id],
                                    ['deposit' => 0, 'withdrawal' => 0, 'investment' => 0, 'bonus' => 0]
                                );

                                // Credit the wallet
                                $wallet->deposit += $creditAmount;
                                $wallet->save();

                                // Add bonus to upline
                                Helpers::insertAiTradeV2($deposit->user_id, $creditAmount);
                                Helpers::depositBonus($deposit->user_id, $creditAmount);
                                Helpers::processDepositPromoBonus($deposit->user_id, $creditAmount, $deposit->id);

                                Log::info('Partially paid deposit completed via bulk refresh - user credited', [
                                    'deposit_id' => $deposit->id,
                                    'user_id' => $deposit->user_id,
                                    'credited_amount' => $creditAmount,
                                    'outcome_amount' => $outcomeAmount,
                                    'old_status' => $oldStatus,
                                    'new_status' => $newStatus
                                ]);
                            }

                            $deposit->status = $newStatus;
                            $deposit->save();
                            $updatedCount++;

                            $updates[] = [
                                'payment_id' => $deposit->payment_id,
                                'deposit_id' => $deposit->id,
                                'old_status' => $oldStatus,
                                'new_status' => $newStatus,
                                'nowpayments_status' => $nowpaymentsStatus,
                                'wallet_credited' => ($oldStatus === 'partially_paid' && $newStatus === 'completed')
                            ];

                            Log::info('Updated deposit status during bulk refresh', [
                                'deposit_id' => $deposit->id,
                                'payment_id' => $deposit->payment_id,
                                'old_status' => $oldStatus,
                                'new_status' => $newStatus,
                                'nowpayments_status' => $nowpaymentsStatus
                            ]);
                        } else {
                            Log::info('No status change needed for deposit', [
                                'deposit_id' => $deposit->id,
                                'payment_id' => $deposit->payment_id,
                                'status' => $oldStatus
                            ]);
                        }
                    } else {
                        Log::warning('NOWPayments API returned null/empty data for payment', [
                            'deposit_id' => $deposit->id,
                            'payment_id' => $deposit->payment_id,
                            'api_response' => $payment
                        ]);

                        $errors[] = [
                            'deposit_id' => $deposit->id,
                            'payment_id' => $deposit->payment_id,
                            'error' => 'No payment data from NOWPayments API'
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to refresh individual payment status', [
                        'deposit_id' => $deposit->id,
                        'payment_id' => $deposit->payment_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    $errors[] = [
                        'deposit_id' => $deposit->id,
                        'payment_id' => $deposit->payment_id,
                        'error' => $e->getMessage()
                    ];
                    // Continue with next deposit even if one fails
                }
            }

            Log::info('Completed bulk refresh of payment statuses', [
                'total_count' => $totalCount,
                'updated_count' => $updatedCount,
                'error_count' => count($errors),
                'updates' => $updates,
                'errors' => $errors
            ]);

            return response()->json([
                'success' => true,
                'total_count' => $totalCount,
                'updated_count' => $updatedCount,
                'error_count' => count($errors),
                'updates' => $updates,
                'errors' => $errors,
                'message' => "Refreshed {$updatedCount} out of {$totalCount} payment statuses" .
                           (count($errors) > 0 ? " with " . count($errors) . " errors" : "")
            ]);

        } catch (\Exception $e) {
            Log::error('Error during bulk refresh of payment statuses', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id() ?? 'unknown',
                'environment' => app()->environment()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error refreshing payment statuses: ' . $e->getMessage(),
                'error_details' => [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => basename($e->getFile())
                ]
            ], 500);
        }
    }

    /**
     * Debug method to test NOWPayments connectivity
     */
    public function debugConnectivity()
    {
        try {
            Log::info('Debug: Testing NOWPayments connectivity', [
                'environment' => app()->environment(),
                'config_check' => [
                    'api_key_set' => config('services.nowpayments.api_key') ? 'yes' : 'no',
                    'api_key_length' => config('services.nowpayments.api_key') ? strlen(config('services.nowpayments.api_key')) : 0,
                    'sandbox_mode' => config('services.nowpayments.sandbox', true) ? 'yes' : 'no',
                    'base_url' => config('services.nowpayments.base_url', 'not_set')
                ]
            ]);

            // Test basic API status
            $apiStatus = $this->nowPayments->getApiStatus();

            Log::info('Debug: NOWPayments API status result', [
                'api_status' => $apiStatus
            ]);

            return response()->json([
                'success' => true,
                'environment' => app()->environment(),
                'api_status' => $apiStatus,
                'config' => [
                    'api_key_set' => config('services.nowpayments.api_key') ? true : false,
                    'sandbox_mode' => config('services.nowpayments.sandbox', true),
                    'base_url' => config('services.nowpayments.base_url')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Debug: Error testing NOWPayments connectivity', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'environment' => app()->environment()
            ], 500);
        }
    }
}
