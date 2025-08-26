<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Helpers;
use Exception;

class WalletManagementController extends Controller
{
    /**
     * Display the wallet management page
     */
    public function index(Request $request)
    {
        return view('admin.wallet.index');
    }

    /**
     * Return DataTables data for AJAX requests
     */
    public function list(Request $request)
    {
        try {
            $wallets = Wallet::with('user')->select('wallets.*');

            return DataTables::of($wallets)
                ->addIndexColumn()
                ->addColumn('user', function ($wallet) {
                    $user = $wallet->user;
                    if (!$user) {
                        return 'N/A';
                    }
                    $firstName = $user->firstname ?? '';
                    $lastName = $user->lastname ?? '';
                    $fullName = trim($firstName . ' ' . $lastName);
                    return !empty($fullName) ? $fullName : ($user->username ?? $user->email ?? 'N/A');
                })
                ->addColumn('deposit', function ($wallet) {
                    return '$' . number_format((float)($wallet->deposit ?? 0), 2);
                })
                ->addColumn('profit', function ($wallet) {
                    return '$' . number_format((float)($wallet->profit ?? 0), 2);
                })
                ->addColumn('bonus', function ($wallet) {
                    return '$' . number_format((float)($wallet->bonus ?? 0), 2);
                })
                ->addColumn('cash', function ($wallet) {
                    return '$' . number_format((float)($wallet->cash ?? 0), 2);
                })
                ->addColumn('total_balance', function ($wallet) {
                    $deposit = (float)($wallet->deposit ?? 0);
                    $profit = (float)($wallet->profit ?? 0);
                    $bonus = (float)($wallet->bonus ?? 0);
                    $cash = (float)($wallet->cash ?? 0);
                    $total = $deposit + $profit + $bonus + $cash;
                    return '$' . number_format($total, 2);
                })
                ->addColumn('updated_at', function ($wallet) {
                    return $wallet->updated_at ? $wallet->updated_at->format('M d, Y H:i') : 'N/A';
                })
                ->addColumn('actions', function ($wallet) {
                    $user = $wallet->user;
                    if (!$user) {
                        return '<span class="text-muted">No User</span>';
                    }
                    return '
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="tabler-dots-vertical"></i> Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item btn-view-user" data-wallet-id="' . $wallet->id . '"><i class="tabler-eye me-2"></i>View User</button></li>
                                <li><button class="dropdown-item btn-adjust-wallet" data-wallet-id="' . $wallet->id . '" data-user-name="' . htmlspecialchars($user->firstname . ' ' . $user->lastname) . '"><i class="tabler-wallet me-2"></i>Adjust Wallet</button></li>
                                <li><button class="dropdown-item btn-wallet-history" data-wallet-id="' . $wallet->id . '"><i class="tabler-history me-2"></i>Transaction History</button></li>
                            </ul>
                        </div>';
                })
                ->rawColumns(['actions'])
                ->make(true);

        } catch (Exception $e) {
            Log::error('Wallet DataTables error: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while loading wallet data.'
            ], 500);
        }
    }

    /**
     * Create wallet for a user
     */
    public function createWallet(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $user = User::findOrFail($request->user_id);

            DB::beginTransaction();

            // Check if wallet already exists
            if ($user->wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet already exists for this user.'
                ], 400);
            }

            // Create new wallet with default values
            $wallet = new Wallet([
                'user_id' => $user->id,
                'deposit' => 0,
                'profit' => 0,
                'bonus' => 0,
                'cash' => 0
            ]);
            $wallet->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Wallet created successfully for ' . ($user->name ?: $user->username),
                'wallet' => $wallet
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Wallet creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the wallet.'
            ], 500);
        }
    }

    /**
     * Adjust wallet balance (add or deduct amount)
     */
    public function adjustBalance(Request $request)
    {
        try {
            $request->validate([
                'wallet_id' => 'required|integer|exists:wallets,id',
                'type' => 'required|string|in:deposit,profit,bonus,cash',
                'action' => 'required|string|in:add,deduct',
                'amount' => 'required|numeric|min:0.01',
                'reason' => 'required|string|max:255',
                'trigger_deposit_bonus' => 'nullable|boolean'
            ]);

            $wallet = Wallet::findOrFail($request->wallet_id);
            $amount = (float) $request->amount;
            $type = $request->type;
            $action = $request->action;
            $reason = $request->reason;
            $triggerDepositBonus = $request->has('trigger_deposit_bonus') && $request->trigger_deposit_bonus;

            DB::beginTransaction();

            // Store original balance for logging
            $originalBalance = $wallet->$type;

            if ($action === 'add') {
                $wallet->$type += $amount;
                $message = "Added $" . number_format($amount, 2) . " to {$type} balance";

                // Only trigger deposit bonus if checkbox is checked and it's a deposit
                if ($type === 'deposit') {
                    Helpers::insertAiTradeV2($wallet->user_id, $amount);
                    if($triggerDepositBonus){
                      Helpers::depositBonus($wallet->user_id, $amount);
                    }
                }

            } else {
                // Check if sufficient balance for deduction
                if ($wallet->$type < $amount) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient {$type} balance. Current balance: $" . number_format($wallet->$type, 2)
                    ], 400);
                }
                $wallet->$type -= $amount;
                $message = "Deducted $" . number_format($amount, 2) . " from {$type} balance";
            }

            $wallet->save();

            // Log the transaction
            $this->logWalletTransaction($wallet, $type, $action, $amount, $originalBalance, $wallet->$type, $reason);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'wallet' => $wallet->fresh(['user'])
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Wallet adjustment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adjusting the wallet balance.'
            ], 500);
        }
    }

    /**
     * Get wallet details
     */
    public function getWallet($id)
    {
        try {
            $wallet = Wallet::with('user')->findOrFail($id);
            $user = $wallet->user;

            return response()->json([
                'success' => true,
                'wallet' => [
                    'id' => $wallet->id,
                    'user_name' => $user ? trim($user->firstname . ' ' . $user->lastname) : 'N/A',
                    'deposit' => number_format($wallet->deposit, 2),
                    'profit' => number_format($wallet->profit, 2),
                    'bonus' => number_format($wallet->bonus, 2),
                    'cash' => number_format($wallet->cash, 2),
                    'total_balance' => number_format($wallet->total_balance, 2)
                ],
                'user' => [
                    'id' => $user ? $user->id : null,
                    'first_name' => $user ? $user->firstname : 'N/A',
                    'last_name' => $user ? $user->lastname : 'N/A',
                    'email' => $user ? $user->email : 'N/A',
                    'username' => $user ? $user->username : 'N/A',
                    'created_at' => $user ? $user->created_at : null,
                    'status' => $user ? 'active' : 'inactive' // You can modify this based on your user status field
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Get wallet error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found.'
            ], 404);
        }
    }

    /**
     * Get wallet transaction history
     */
    public function getTransactionHistory($walletId)
    {
        try {
            $wallet = Wallet::with('user')->findOrFail($walletId);
            $user = $wallet->user;

            // For now, we'll create sample transaction data
            // In a production system, you'd have a dedicated transactions table
            $transactions = [
                [
                    'id' => 1,
                    'type' => 'add',
                    'amount' => 100.00,
                    'balance_type' => 'deposit',
                    'balance_before' => 500.00,
                    'balance_after' => 600.00,
                    'reason' => 'Admin adjustment - Bonus credit',
                    'admin_name' => 'Admin User',
                    'created_at' => now()->subDays(2)
                ],
                [
                    'id' => 2,
                    'type' => 'deduct',
                    'amount' => 50.00,
                    'balance_type' => 'cash',
                    'balance_before' => 200.00,
                    'balance_after' => 150.00,
                    'reason' => 'Admin adjustment - Correction',
                    'admin_name' => 'Admin User',
                    'created_at' => now()->subDays(5)
                ],
                [
                    'id' => 3,
                    'type' => 'add',
                    'amount' => 250.00,
                    'balance_type' => 'profit',
                    'balance_before' => 1000.00,
                    'balance_after' => 1250.00,
                    'reason' => 'Trading profit allocation',
                    'admin_name' => 'System',
                    'created_at' => now()->subWeek()
                ]
            ];

            return response()->json([
                'success' => true,
                'user_name' => $user ? trim($user->firstname . ' ' . $user->lastname) : 'N/A',
                'transactions' => $transactions
            ]);

        } catch (Exception $e) {
            Log::error('Transaction history error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch transaction history.'
            ], 500);
        }
    }

    /**
     * Log wallet transaction for audit trail
     */
    private function logWalletTransaction($wallet, $type, $action, $amount, $beforeBalance, $afterBalance, $reason)
    {
        $admin = Auth::user();

        Log::info('Wallet Transaction', [
            'admin_id' => $admin ? $admin->id : null,
            'admin_name' => $admin ? ($admin->name ?? $admin->username) : 'System',
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'user_name' => $wallet->user ? trim($wallet->user->firstname . ' ' . $wallet->user->lastname) : 'N/A',
            'type' => $type,
            'action' => $action,
            'amount' => $amount,
            'before_balance' => $beforeBalance,
            'after_balance' => $afterBalance,
            'reason' => $reason,
            'timestamp' => now()
        ]);
    }
}
