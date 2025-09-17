<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\User;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WithdrawalManagementController extends Controller
{
    public function index()
    {
        // Get statistics for the dashboard cards
        $totalWithdrawals = Withdrawal::count();
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $approvedWithdrawals = Withdrawal::where('status', 'approved')->count();
        $rejectedWithdrawals = Withdrawal::where('status', 'rejected')->count();

        // Calculate net amounts for each status
        $totalNetAmount = Withdrawal::sum('net_amount');
        $pendingNetAmount = Withdrawal::where('status', 'pending')->sum('net_amount');
        $approvedNetAmount = Withdrawal::where('status', 'approved')->sum('net_amount');
        $rejectedNetAmount = Withdrawal::where('status', 'rejected')->sum('net_amount');

        // Keep original amount calculations for backward compatibility
        $totalAmount = Withdrawal::sum('amount');
        $pendingAmount = Withdrawal::where('status', 'pending')->sum('amount');

        return view('admin.withdrawal.index', compact(
            'totalWithdrawals',
            'pendingWithdrawals',
            'approvedWithdrawals',
            'rejectedWithdrawals',
            'totalNetAmount',
            'pendingNetAmount',
            'approvedNetAmount',
            'rejectedNetAmount',
            'totalAmount',
            'pendingAmount'
        ));
    }

    public function list(Request $request)
    {
        Log::info('Withdrawal list method called', ['request_data' => $request->all()]);

        $withdrawals = Withdrawal::with(['user'])
            ->select([
                'withdrawals.id',
                'withdrawals.user_id',
                'withdrawals.amount',
                'withdrawals.tax_amount',
                'withdrawals.net_amount',
                'withdrawals.status',
                'withdrawals.bank_name',
                'withdrawals.account_number',
                'withdrawals.withdrawal_type',
                'withdrawals.wallet_type',
                'withdrawals.reference',
                'withdrawals.note',
                'withdrawals.created_at'
            ]);

        Log::info('Withdrawal query built', ['count' => $withdrawals->count()]);

        // Apply filters
        if ($request->filled('status')) {
            $withdrawals->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $withdrawals->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $withdrawals->whereDate('created_at', '<=', $request->date_to);
        }

        try {
            $result = DataTables::of($withdrawals)
                ->addColumn('user_info', function ($withdrawal) {
                    $user = $withdrawal->user;
                    if (!$user) {
                        return [
                            'id' => null,
                            'username' => 'Unknown',
                            'email' => 'Unknown',
                            'firstname' => 'Unknown',
                            'lastname' => 'User'
                        ];
                    }
                    return [
                        'id' => $user->id,
                        'username' => $user->username ?? 'Unknown',
                        'email' => $user->email ?? 'Unknown',
                        'firstname' => $user->firstname ?? 'Unknown',
                        'lastname' => $user->lastname ?? 'User'
                    ];
                })
                ->addColumn('bank_info', function ($withdrawal) {
                    $user = $withdrawal->user;

                    switch ($withdrawal->withdrawal_type) {
                        case 'crypto':
                            // For crypto withdrawals, show wallet address based on currency
                            $walletAddress = null;
                            $currency = $withdrawal->bank_name ?? 'Unknown';

                            if ($user) {
                                switch (strtolower($currency)) {
                                    case 'usdt':
                                    case 'usdt bep20':
                                        $walletAddress = $user->usdt_bep20_address;
                                        break;
                                    case 'usdc':
                                    case 'usdc bep20':
                                        $walletAddress = $user->usdc_bep20_address;
                                        break;
                                    case 'fdusd':
                                    case 'fdusd bep20':
                                        $walletAddress = $user->fdusd_bep20_address;
                                        break;
                                    default:
                                        $walletAddress = $withdrawal->account_number;
                                }
                            }

                            return [
                                'type' => 'Crypto',
                                'currency' => $currency,
                                'wallet_address' => $walletAddress ?
                                    substr($walletAddress, 0, 6) . '...' . substr($walletAddress, -6) :
                                    'Not provided'
                            ];

                        case 'etc_black':
                            // For crypto bank card withdrawals
                            return [
                                'type' => 'Crypto Bank Card',
                                'card_number' => $user && $user->etc_black_card_number ?
                                    '****-****-****-' . substr($user->etc_black_card_number, -4) :
                                    'Not provided',
                                'wallet_address' => $user && $user->card_wallet_address ?
                                    substr($user->card_wallet_address, 0, 6) . '...' . substr($user->card_wallet_address, -6) :
                                    'Not provided'
                            ];

                        case 'gold':
                            // For gold withdrawals, show bank information
                            return [
                                'type' => 'Gold Bank',
                                'bank_name' => $withdrawal->bank_name ?? 'Not provided',
                                'account_number' => $withdrawal->account_number ?
                                    '****' . substr($withdrawal->account_number, -4) :
                                    'Not provided'
                            ];

                        default:
                            return [
                                'type' => 'Unknown',
                                'info' => 'N/A'
                            ];
                    }
                })
                ->addColumn('action', function ($withdrawal) {
                    $actions = '<div class="btn-group" role="group">';

                    if ($withdrawal->status === 'pending') {
                        // Different approve button for gold withdrawals
                        if ($withdrawal->withdrawal_type === 'gold') {
                            $actions .= '<button type="button" class="btn btn-warning btn-sm gold-approve-btn"
                                            data-id="' . $withdrawal->id . '"
                                            data-bs-toggle="modal"
                                            data-bs-target="#branchAssignmentModal">
                                            <i class="fas fa-coins"></i> Assign Branch
                                        </button>';
                        } else {
                            $actions .= '<button type="button" class="btn btn-success btn-sm approve-btn"
                                            data-id="' . $withdrawal->id . '"
                                            data-bs-toggle="modal"
                                            data-bs-target="#approveModal">
                                            <i class="fas fa-check"></i> Approve
                                        </button>';
                        }

                        $actions .= '<button type="button" class="btn btn-danger btn-sm reject-btn"
                                        data-id="' . $withdrawal->id . '"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">
                                        <i class="fas fa-times"></i> Reject
                                    </button>';
                    }

                    // Add receipt download button for approved gold withdrawals only
                    if ($withdrawal->withdrawal_type === 'gold' && $withdrawal->status === 'approved') {
                        $actions .= '<a href="' . route('admin.withdrawals.receipt', $withdrawal->id) . '"
                                        class="btn btn-secondary btn-sm" target="_blank">
                                        <i class="fas fa-receipt"></i> Receipt
                                    </a>';
                    }

                    $actions .= '<button type="button" class="btn btn-info btn-sm view-btn"
                                    data-id="' . $withdrawal->id . '"
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewModal">
                                    <i class="fas fa-eye"></i> View
                                </button>';

                    $actions .= '</div>';

                    return $actions;
                })
                ->filterColumn('user_info', function ($query, $keyword) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('username', 'like', "%{$keyword}%")
                          ->orWhere('email', 'like', "%{$keyword}%")
                          ->orWhere('firstname', 'like', "%{$keyword}%")
                          ->orWhere('lastname', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['action'])
                ->make(true);

            Log::info('DataTables result generated successfully');
            return $result;

        } catch (\Exception $e) {
            Log::error('DataTables error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function approve(Request $request, $id)
    {
        try {
            $withdrawal = Withdrawal::with('user.wallet')->findOrFail($id);

            if ($withdrawal->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending withdrawals can be approved.'
                ], 400);
            }

            // Check if user has sufficient floating balance for this withdrawal type
            $wallet = $withdrawal->user->wallet;
            $floatingAmount = $wallet ? $wallet->getFloatingAmountForWithdrawal($withdrawal->withdrawal_type) : 0;

            if (!$wallet || $floatingAmount < $withdrawal->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: Floating balance mismatch for ' . $withdrawal->withdrawal_type . ' withdrawals. Please contact system administrator.'
                ], 400);
            }

            // Remove the amount from the appropriate floating field
            if (!$wallet->deductFloatingForWithdrawal($withdrawal->amount, $withdrawal->withdrawal_type)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: Failed to process floating amount for ' . $withdrawal->withdrawal_type . ' withdrawal. Please contact system administrator.'
                ], 400);
            }

            $withdrawal->update([
                'status' => 'approved'
            ]);

            // Check if user has telegram verification before sending notification
            $telegramVerification = DB::table('telegram_verifications')
                ->where('user_id', $withdrawal->user_id)
                ->whereNotNull('telegram_username')
                ->where('telegram_username', '!=', '')
                ->first();

            if ($telegramVerification) {
                // Send Telegram notification only if user has telegram_username
                $title = "✅ Withdrawal Approved";
                $message = "Your withdrawal request has been approved!\n\n" .
                          "💰 Amount: $" . number_format($withdrawal->amount, 2) . "\n" .
                          "💰 Tax: $" . number_format($withdrawal->tax_amount, 2) . "\n" .
                          "💵 Net Amount: $" . number_format($withdrawal->net_amount, 2) . "\n" .
                          "🏦 Withdrawal Type: {$withdrawal->withdrawal_type}\n" .
                          "📄 Reference: {$withdrawal->reference}\n\n" .
                          "The funds will be processed for withdrawal.";

                TelegramNotificationService::sendNotificationToTelegram(
                    $withdrawal->user_id,
                    $title,
                    $message
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal approved successfully! Amount has been processed for withdrawal.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving withdrawal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approveGold(Request $request, $id)
    {
        try {
            // Validate request data
            $request->validate([
                'branch_key' => 'required|string',
                'branch_name' => 'required|string',
                'manager_name' => 'required|string',
                'manager_phone' => 'required|string',
                'admin_notes' => 'nullable|string|max:1000'
            ]);

            $withdrawal = Withdrawal::with('user.wallet')->findOrFail($id);

            if ($withdrawal->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending withdrawals can be approved.'
                ], 400);
            }

            if ($withdrawal->withdrawal_type !== 'gold') {
                return response()->json([
                    'success' => false,
                    'message' => 'This method is only for gold withdrawals.'
                ], 400);
            }

            // Check if user has sufficient floating balance for this withdrawal type
            $wallet = $withdrawal->user->wallet;
            $floatingAmount = $wallet ? $wallet->getFloatingAmountForWithdrawal($withdrawal->withdrawal_type) : 0;

            if (!$wallet || $floatingAmount < $withdrawal->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: Floating balance mismatch for gold withdrawals. Please contact system administrator.'
                ], 400);
            }

            // Remove the amount from the appropriate floating field
            if (!$wallet->deductFloatingForWithdrawal($withdrawal->amount, $withdrawal->withdrawal_type)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: Failed to process floating amount for gold withdrawal. Please contact system administrator.'
                ], 400);
            }

            // Update withdrawal with branch assignment
            $withdrawal->update([
                'status' => 'approved',
                'branch_name' => $request->branch_name,
                'branch_manager_name' => $request->manager_name,
                'branch_manager_phone' => $request->manager_phone,
                'branch_selected' => true,
                'branch_assigned_at' => now(),
                'assigned_by_admin_id' => auth('admin')->id(),
                'note' => $request->admin_notes ? 'Admin Notes: ' . $request->admin_notes : null
            ]);

            // Check if user has telegram verification before sending notification
            $telegramVerification = DB::table('telegram_verifications')
                ->where('user_id', $withdrawal->user_id)
                ->whereNotNull('telegram_username')
                ->where('telegram_username', '!=', '')
                ->first();

            if ($telegramVerification) {
                // Send Telegram notification with branch details
                $title = "🥇 Gold Withdrawal Approved & Branch Assigned";
                $message = "Your gold redemption request has been approved!\n\n" .
                          "💰 Amount: $" . number_format($withdrawal->amount, 2) . "\n" .
                          "💰 Processing Fee: $" . number_format($withdrawal->tax_amount, 2) . "\n" .
                          "🥇 Net Gold Value: $" . number_format($withdrawal->net_amount, 2) . "\n\n" .
                          "📍 Assigned Branch: " . $request->branch_name . "\n" .
                          "👤 Branch Manager: " . $request->manager_name . "\n" .
                          "📞 Contact: " . $request->manager_phone . "\n\n" .
                          "Please contact the branch manager to arrange your gold pickup. Bring your ID and withdrawal reference #" . $withdrawal->id . ".";

                TelegramNotificationService::sendNotificationToTelegram(
                    $withdrawal->user_id,
                    $title,
                    $message
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Gold withdrawal approved successfully! Branch has been assigned and user will be notified.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving gold withdrawal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            // Validate rejection reason
            $request->validate([
                'rejection_reason' => 'required|string|min:5|max:500'
            ]);

            $withdrawal = Withdrawal::with('user.wallet')->findOrFail($id);

            if ($withdrawal->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending withdrawals can be rejected.'
                ], 400);
            }

            // Return the floating amount back to profit balance for the specific withdrawal type
            $wallet = $withdrawal->user->wallet;
            if ($wallet && !$wallet->releaseAmountForWithdrawal($withdrawal->amount, $withdrawal->withdrawal_type)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: Failed to release floating amount back to profit for ' . $withdrawal->withdrawal_type . ' withdrawal. Please contact system administrator.'
                ], 400);
            }

            $withdrawal->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason
            ]);

            // Check if user has telegram verification before sending notification
            $telegramVerification = DB::table('telegram_verifications')
                ->where('user_id', $withdrawal->user_id)
                ->whereNotNull('telegram_username')
                ->where('telegram_username', '!=', '')
                ->first();

            if ($telegramVerification) {
                // Send Telegram notification only if user has telegram_username
                $title = "❌ Withdrawal Rejected";
                $message = "Your withdrawal request has been rejected.\n\n" .
                          "💰 Amount: $" . number_format($withdrawal->amount, 2) . "\n" .
                          "💰 Tax: $" . number_format($withdrawal->tax_amount, 2) . "\n" .
                          "💵 Net Amount: $" . number_format($withdrawal->net_amount, 2) . "\n" .
                          "🏦 Withdrawal Type: {$withdrawal->withdrawal_type}\n" .
                          "📄 Reference: {$withdrawal->reference}\n\n" .
                          "❌ Rejection Reason: {$request->rejection_reason}\n\n" .
                          "💳 The amount has been returned to your profit balance.\n" .
                          "📞 For more information, please contact support.";

                TelegramNotificationService::sendNotificationToTelegram(
                    $withdrawal->user_id,
                    $title,
                    $message
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal rejected successfully! Amount returned to user\'s profit balance.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting withdrawal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $withdrawal = Withdrawal::with('user')->findOrFail($id);
            $user = $withdrawal->user;

            // Prepare withdrawal type specific data
            $withdrawalDetails = [];

            switch ($withdrawal->withdrawal_type) {
                case 'crypto':
                    $currency = $withdrawal->bank_name ?? 'Unknown';
                    $walletAddress = null;

                    if ($user) {
                        switch (strtolower($currency)) {
                            case 'usdt':
                            case 'usdt bep20':
                                $walletAddress = $user->usdt_bep20_address;
                                break;
                            case 'usdc':
                            case 'usdc bep20':
                                $walletAddress = $user->usdc_bep20_address;
                                break;
                            case 'fdusd':
                            case 'fdusd bep20':
                                $walletAddress = $user->fdusd_bep20_address;
                                break;
                            default:
                                $walletAddress = $withdrawal->account_number;
                        }
                    }

                    $withdrawalDetails = [
                        'type' => 'Crypto Withdrawal',
                        'currency' => $currency,
                        'wallet_address' => $walletAddress ?? 'Not provided',
                        'network' => 'BEP20'
                    ];
                    break;

                case 'etc_black':
                    $withdrawalDetails = [
                        'type' => 'Crypto Bank Card Withdrawal',
                        'card_number' => $user && $user->etc_black_card_number ?
                            '****-****-****-' . substr($user->etc_black_card_number, -4) :
                            'Not provided',
                        'cardholder_name' => $user->cardholder_name ?? 'Not provided',
                        'account_number' => $user && $user->account_number ?
                            '****' . substr($user->account_number, -4) :
                            'Not provided',
                        'card_wallet_address' => $user->card_wallet_address ?? 'Not provided'
                    ];
                    break;

                case 'gold':
                    $withdrawalDetails = [
                        'type' => 'Gold Bank Withdrawal',
                        'bank_name' => $withdrawal->bank_name ?? 'Not provided',
                        'account_number' => $withdrawal->account_number ?? 'Not provided',
                        'account_holder' => $user ? $user->firstname . ' ' . $user->lastname : 'Not provided'
                    ];
                    break;

                default:
                    $withdrawalDetails = [
                        'type' => 'Unknown Withdrawal Type',
                        'info' => 'N/A'
                    ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'tax_amount' => $withdrawal->tax_amount,
                    'net_amount' => $withdrawal->net_amount,
                    'status' => $withdrawal->status,
                    'withdrawal_type' => $withdrawal->withdrawal_type,
                    'wallet_type' => $withdrawal->wallet_type ?? 'profit',
                    'bank_name' => $withdrawal->bank_name,
                    'account_number' => $withdrawal->account_number,
                    'reference' => $withdrawal->reference,
                    'note' => $withdrawal->note,
                    'created_at' => $withdrawal->created_at->format('M d, Y H:i'),
                    'rejection_reason' => $withdrawal->rejection_reason,
                    'withdrawal_details' => $withdrawalDetails,
                    'user' => [
                        'id' => $withdrawal->user->id,
                        'username' => $withdrawal->user->username,
                        'email' => $withdrawal->user->email,
                        'firstname' => $withdrawal->user->firstname,
                        'lastname' => $withdrawal->user->lastname
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching withdrawal details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function receipt($id)
    {
        try {
            $withdrawal = Withdrawal::with('user')->findOrFail($id);

            // Validate that this is an approved gold withdrawal with branch assigned
            if ($withdrawal->withdrawal_type !== 'gold') {
                abort(404, 'Receipt not available for this withdrawal type.');
            }

            if ($withdrawal->status !== 'approved' || !$withdrawal->branch_selected) {
                abort(404, 'Receipt only available for approved gold withdrawals with assigned branch.');
            }

            // Get MYR conversion rate once in the controller to avoid database calls in the view
            $myrRate = \App\Models\WithdrawalSettings::getMyrConversionRate();

            return view('admin.withdrawal.receipt', compact('withdrawal', 'myrRate'));

        } catch (\Exception $e) {
            abort(500, 'Error generating receipt: ' . $e->getMessage());
        }
    }
}
