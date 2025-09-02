<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NOWPaymentsService;
use App\Services\NotificationService;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            $totalAmount = Deposit::sum('amount');

            return view('admin.nowpayments.index', compact(
                'totalDeposits',
                'pendingDeposits',
                'completedDeposits',
                'totalAmount'
            ));

        } catch (\Exception $e) {
            Log::error('NOWPayments Controller Error: ' . $e->getMessage());
            return view('admin.nowpayments.index', [
                'totalDeposits' => 0,
                'pendingDeposits' => 0,
                'completedDeposits' => 0,
                'totalAmount' => 0
            ]);
        }
    }

    /**
     * Get the list of deposits for DataTables
     */
    public function list(Request $request)
    {
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
                    if ($deposit->payment_id) {
                        return '<code class="bg-light p-1">' . $deposit->payment_id . '</code><br><small class="text-muted">NOWPayments</small>';
                    }

                    if ($deposit->payment_method) {
                        return '<span class="badge badge-info">' . ucfirst($deposit->payment_method) . '</span>';
                    }

                    return '<span class="badge badge-warning">Pending</span>';
                })
                ->addColumn('status', function ($deposit) {
                    $statusClass = $deposit->status === 'completed' ? 'success' :
                                  ($deposit->status === 'pending' ? 'warning' : 'secondary');
                    return '<span class="badge bg-' . $statusClass . '">' . ucfirst($deposit->status) . '</span>';
                })
                ->addColumn('created_at', function ($deposit) {
                    return $deposit->created_at->format('M d, Y H:i') . '<br><small class="text-muted">' . $deposit->created_at->diffForHumans() . '</small>';
                })
                ->addColumn('actions', function ($deposit) {
                    $actions = '<button class="btn btn-sm btn-outline-info" onclick="showDepositDetails(' . $deposit->id . ', this)">
                                    <i class="fas fa-info-circle"></i> Details
                                </button>';

                    if ($deposit->status === 'pending') {
                        $actions .= ' <button class="btn btn-sm btn-success ml-1" onclick="approvePayment(' . $deposit->id . ', this)">
                                        <i class="fas fa-check"></i> Approve
                                    </button>';

                        if ($deposit->payment_method === 'manual' || !$deposit->payment_id) {
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

            // Add bonus to upline
            Helpers::insertAiTradeV2($deposit->user_id, $deposit->net_amount);
            Helpers::depositBonus($deposit->user_id, $deposit->net_amount);

            // Create success notification for the user
            NotificationService::createDepositSuccessNotification(
                $deposit->user_id,
                $deposit->net_amount,
                $deposit->id
            );

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
            $deposit->rejected_by = auth()->user()->id ?? null;
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
                'rejected_by' => auth()->user()->id ?? null
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
                    $newStatus = $payment['payment_status'] ?? $deposit->status;

                    $deposit->status = $newStatus;
                    $deposit->save();

                    Log::info('Updated local deposit status', [
                        'payment_id' => $paymentId,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus
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
                $nowpaymentsData = $this->nowPayments->getPaymentStatus($deposit->payment_id);
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
}
