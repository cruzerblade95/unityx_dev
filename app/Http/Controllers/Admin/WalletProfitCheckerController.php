<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletProfitTransfer;
use App\Models\WalletProfitSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class WalletProfitCheckerController extends Controller
{
    /**
     * Display the wallet profit checker main page
     */
    public function index()
    {
        $stats = $this->getTransferStats();
        return view('admin.wallet-profit-checker.index', compact('stats'));
    }

    /**
     * Get users data for DataTables
     */
    public function getUsersData(Request $request)
    {
        try {
            Log::info('getUsersData called with parameters: ' . json_encode($request->all()));

            // Clear any output buffer to prevent BOM characters
            if (ob_get_level()) {
                ob_clean();
            }

            $query = User::with('wallet')
                ->whereHas('wallet')
                ->select(['id', 'username', 'email', 'created_at']);

            Log::info('Query built successfully');

            $dataTable = DataTables::of($query)
                ->addColumn('wallet_info', function ($user) {
                    $wallet = $user->wallet;
                    if (!$wallet) {
                        return [
                            'deposit' => '0.00',
                            'profit' => '0.00',
                            'temporary_profit' => '0.00',
                            'total' => '0.00'
                        ];
                    }

                    return [
                        'deposit' => number_format($wallet->deposit ?? 0, 2),
                        'profit' => number_format($wallet->profit ?? 0, 2),
                        'temporary_profit' => number_format($wallet->temporary_profit ?? 0, 2),
                        'total' => number_format(
                            ($wallet->deposit ?? 0) + ($wallet->profit ?? 0) + ($wallet->temporary_profit ?? 0),
                            2
                        )
                    ];
                })
                ->addColumn('profit_ratio', function ($user) {
                    $wallet = $user->wallet;
                    if (!$wallet || $wallet->deposit <= 0) {
                        return [
                            'percentage' => 0,
                            'is_over_threshold' => false,
                            'color_class' => 'text-muted'
                        ];
                    }

                    $percentage = ($wallet->profit / $wallet->deposit) * 100;
                    $threshold = WalletProfitSetting::getProfitThreshold();
                    $isOverThreshold = $percentage > $threshold;

                    return [
                        'percentage' => round($percentage, 2),
                        'is_over_threshold' => $isOverThreshold,
                        'color_class' => $isOverThreshold ? 'text-danger' :
                            ($percentage > ($threshold * 0.8) ? 'text-warning' : 'text-success')
                    ];
                })
                ->addColumn('temp_status', function ($user) {
                    $wallet = $user->wallet;
                    $hasTemp = $wallet && $wallet->temporary_profit > 0;

                    return [
                        'has_temp' => $hasTemp,
                        'amount' => $hasTemp ? number_format($wallet->temporary_profit, 2) : '0.00'
                    ];
                })
                ->addColumn('actions', function ($user) {
                    return $user->id;
                })
                ->filterColumn('username', function ($query, $keyword) {
                    $query->where('username', 'like', "%{$keyword}%");
                })
                ->filterColumn('email', function ($query, $keyword) {
                    $query->where('email', 'like', "%{$keyword}%");
                })
                ->orderColumn('username', function ($query, $order) {
                    $query->orderBy('username', $order);
                })
                ->orderColumn('email', function ($query, $order) {
                    $query->orderBy('email', $order);
                })
                ->rawColumns(['actions']);

            Log::info('DataTable configured, about to make response');

            $response = $dataTable->make(true);

            Log::info('DataTable response created successfully');

            return $response;

        } catch (\Exception $e) {
            Log::error('getUsersData error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to load user data: ' . $e->getMessage(),
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 500);
        }
    }

    /**
     * Get users over profit threshold
     */
    public function getOverThresholdUsers(Request $request)
    {
        // Clear any output buffer to prevent BOM characters
        if (ob_get_level()) {
            ob_clean();
        }

        $threshold = WalletProfitSetting::getProfitThreshold();

        $query = User::with('wallet')
            ->whereHas('wallet', function ($q) use ($threshold) {
                $q->whereColumn('profit', '>', DB::raw("deposit * {$threshold} / 100"));
            })
            ->select(['id', 'username', 'email', 'created_at']);

        return DataTables::of($query)
            ->addColumn('wallet_info', function ($user) {
                $wallet = $user->wallet;
                return [
                    'deposit' => number_format($wallet->deposit ?? 0, 2),
                    'profit' => number_format($wallet->profit ?? 0, 2),
                    'temporary_profit' => number_format($wallet->temporary_profit ?? 0, 2)
                ];
            })
            ->addColumn('profit_ratio', function ($user) {
                $wallet = $user->wallet;
                if (!$wallet || $wallet->deposit <= 0) return 0;

                return round(($wallet->profit / $wallet->deposit) * 100, 2);
            })
            ->addColumn('excess_amount', function ($user) {
                $wallet = $user->wallet;
                if (!$wallet || $wallet->deposit <= 0) return 0;

                $threshold = WalletProfitSetting::getProfitThreshold();
                $allowedProfit = $wallet->deposit * ($threshold / 100);
                $excess = max(0, $wallet->profit - $allowedProfit);

                return number_format($excess, 2);
            })
            ->addColumn('actions', function ($user) {
                return $user->id;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Get over threshold users data for DataTables (alternative method name)
     */
    public function getOverThresholdData(Request $request)
    {
        return $this->getOverThresholdUsers($request);
    }

    /**
     * Transfer profit to temporary wallet for selected users
     */
    public function transferProfitToTemp(Request $request)
    {
        // Clear any output buffer to prevent BOM characters
        if (ob_get_level()) {
            ob_clean();
        }

        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id'
        ]);

        $batchId = Str::uuid();
        $transfers = [];
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->user_ids as $userId) {
                $user = User::with('wallet')->find($userId);
                if (!$user || !$user->wallet) {
                    $errors[] = "User {$userId}: No wallet found";
                    continue;
                }

                $wallet = $user->wallet;
                if ($wallet->profit <= 0) {
                    $errors[] = "User {$user->username}: No profit balance to transfer";
                    continue;
                }

                // Record original balances
                $originalProfit = $wallet->profit;
                $originalDeposit = $wallet->deposit;
                $originalTemp = $wallet->temporary_profit;

                // Create transfer record
                $transfer = WalletProfitTransfer::create([
                    'user_id' => $userId,
                    'amount' => $originalProfit,
                    'transfer_type' => 'profit_to_temp',
                    'status' => 'pending',
                    'batch_id' => $batchId,
                    'processed_by' => auth()->id(),
                    'original_profit_balance' => $originalProfit,
                    'original_deposit_balance' => $originalDeposit,
                    'original_temp_balance' => $originalTemp,
                    'processed_at' => now()
                ]);

                // Update wallet balances
                $wallet->update([
                    'profit' => 0,
                    'temporary_profit' => $originalTemp + $originalProfit
                ]);

                // Update transfer status
                $transfer->update(['status' => 'completed']);

                $transfers[] = [
                    'user_id' => $userId,
                    'username' => $user->username,
                    'amount_transferred' => $originalProfit
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profit transferred to temporary wallet successfully',
                'batch_id' => $batchId,
                'transfers' => $transfers,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profit to temp transfer failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Transfer failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transfer all temporary balances to deposit wallets
     */
    public function transferTempToDeposit(Request $request)
    {
        // Clear any output buffer to prevent BOM characters
        if (ob_get_level()) {
            ob_clean();
        }

        $batchId = Str::uuid();
        $transfers = [];

        DB::beginTransaction();
        try {
            $usersWithTemp = User::with('wallet')
                ->whereHas('wallet', function ($q) {
                    $q->where('temporary_profit', '>', 0);
                })
                ->get();

            if ($usersWithTemp->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users found with temporary profit balance'
                ]);
            }

            foreach ($usersWithTemp as $user) {
                $wallet = $user->wallet;
                $tempAmount = $wallet->temporary_profit;

                // Create transfer record
                $transfer = WalletProfitTransfer::create([
                    'user_id' => $user->id,
                    'amount' => $tempAmount,
                    'transfer_type' => 'temp_to_deposit',
                    'status' => 'pending',
                    'batch_id' => $batchId,
                    'processed_by' => auth()->id(),
                    'original_profit_balance' => $wallet->profit,
                    'original_deposit_balance' => $wallet->deposit,
                    'original_temp_balance' => $tempAmount,
                    'processed_at' => now()
                ]);

                // Update wallet balances
                $wallet->update([
                    'deposit' => $wallet->deposit + $tempAmount,
                    'temporary_profit' => 0
                ]);

                // Update transfer status
                $transfer->update(['status' => 'completed']);

                $transfers[] = [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'amount_transferred' => $tempAmount
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All temporary balances transferred to deposit successfully',
                'batch_id' => $batchId,
                'transfers' => $transfers
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Temp to deposit transfer failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Transfer failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transfer all profit balances directly to deposit wallets (bypass temporary)
     */
    public function transferAllProfitToDeposit(Request $request)
    {
        // Clear any output buffer to prevent BOM characters
        if (ob_get_level()) {
            ob_clean();
        }

        $batchId = Str::uuid();
        $transfers = [];

        DB::beginTransaction();
        try {
            // Get users with profit balance > 0
            $usersWithProfit = User::with('wallet')
                ->whereHas('wallet', function ($q) {
                    $q->where('profit', '>', 0);
                })
                ->get();

            if ($usersWithProfit->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users found with profit balance'
                ]);
            }

            foreach ($usersWithProfit as $user) {
                $wallet = $user->wallet;
                $profitAmount = $wallet->profit;

                // Create transfer record
                $transfer = WalletProfitTransfer::create([
                    'user_id' => $user->id,
                    'amount' => $profitAmount,
                    'transfer_type' => 'profit_to_deposit',
                    'status' => 'pending',
                    'batch_id' => $batchId,
                    'processed_by' => auth()->id(),
                    'original_profit_balance' => $profitAmount,
                    'original_deposit_balance' => $wallet->deposit,
                    'original_temp_balance' => $wallet->temporary_profit,
                    'processed_at' => now(),
                    'notes' => 'Direct transfer from profit to deposit (bypass temporary)'
                ]);

                // Update wallet balances: Move all profit directly to deposit
                $wallet->update([
                    'deposit' => $wallet->deposit + $profitAmount,
                    'profit' => 0
                    // temporary_profit remains unchanged
                ]);

                // Update transfer status
                $transfer->update(['status' => 'completed']);

                $transfers[] = [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'amount_transferred' => $profitAmount
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All profit balances transferred to deposit successfully',
                'batch_id' => $batchId,
                'transfers' => $transfers,
                'total_users' => count($transfers),
                'total_amount' => array_sum(array_column($transfers, 'amount_transferred'))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profit to deposit transfer failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Transfer failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return temporary balances back to profit wallets
     */
    public function returnTempToProfit(Request $request)
    {
        // Clear any output buffer to prevent BOM characters
        if (ob_get_level()) {
            ob_clean();
        }

        $batchId = Str::uuid();
        $transfers = [];

        DB::beginTransaction();
        try {
            $usersWithTemp = User::with('wallet')
                ->whereHas('wallet', function ($q) {
                    $q->where('temporary_profit', '>', 0);
                })
                ->get();

            if ($usersWithTemp->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users found with temporary profit balance'
                ]);
            }

            foreach ($usersWithTemp as $user) {
                $wallet = $user->wallet;
                $tempAmount = $wallet->temporary_profit;

                // Create transfer record
                $transfer = WalletProfitTransfer::create([
                    'user_id' => $user->id,
                    'amount' => $tempAmount,
                    'transfer_type' => 'temp_to_profit',
                    'status' => 'pending',
                    'batch_id' => $batchId,
                    'processed_by' => auth()->id(),
                    'original_profit_balance' => $wallet->profit,
                    'original_deposit_balance' => $wallet->deposit,
                    'original_temp_balance' => $tempAmount,
                    'processed_at' => now()
                ]);

                // Update wallet balances
                $wallet->update([
                    'profit' => $wallet->profit + $tempAmount,
                    'temporary_profit' => 0
                ]);

                // Update transfer status
                $transfer->update(['status' => 'completed']);

                $transfers[] = [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'amount_transferred' => $tempAmount
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All temporary balances returned to profit successfully',
                'batch_id' => $batchId,
                'transfers' => $transfers
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Temp to profit return failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Return failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rollback previous transfer batch
     */
    public function rollbackTransfer(Request $request)
    {
        // Clear any output buffer to prevent BOM characters
        if (ob_get_level()) {
            ob_clean();
        }

        $request->validate([
            'batch_id' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            $transfers = WalletProfitTransfer::where('batch_id', $request->batch_id)
                ->where('status', 'completed')
                ->get();

            if ($transfers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No completed transfers found for this batch'
                ]);
            }

            $rollbackBatchId = Str::uuid();
            $rollbacks = [];

            foreach ($transfers as $transfer) {
                $user = $transfer->user;
                if (!$user || !$user->wallet) continue;

                $wallet = $user->wallet;

                // Create rollback record
                $rollback = WalletProfitTransfer::create([
                    'user_id' => $transfer->user_id,
                    'amount' => $transfer->amount,
                    'transfer_type' => 'rollback',
                    'status' => 'pending',
                    'batch_id' => $rollbackBatchId,
                    'processed_by' => auth()->id(),
                    'original_profit_balance' => $wallet->profit,
                    'original_deposit_balance' => $wallet->deposit,
                    'original_temp_balance' => $wallet->temporary_profit,
                    'notes' => "Rollback of batch: {$request->batch_id}",
                    'processed_at' => now()
                ]);

                // Restore original balances
                $wallet->update([
                    'profit' => $transfer->original_profit_balance,
                    'deposit' => $transfer->original_deposit_balance,
                    'temporary_profit' => $transfer->original_temp_balance
                ]);

                // Mark original transfer as rolled back
                $transfer->update(['status' => 'rolled_back']);

                // Mark rollback as completed
                $rollback->update(['status' => 'completed']);

                $rollbacks[] = [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'original_transfer_type' => $transfer->transfer_type
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transfer batch rolled back successfully',
                'rollback_batch_id' => $rollbackBatchId,
                'rollbacks' => $rollbacks
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rollback failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Rollback failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display users over profit threshold page
     */
    public function overThreshold()
    {
        $threshold = WalletProfitSetting::getProfitThreshold();
        return view('admin.wallet-profit-checker.over-threshold', compact('threshold'));
    }

    /**
     * Display users over profit threshold page (alternative method name)
     */
    public function overThresholdPage()
    {
        $threshold = WalletProfitSetting::getProfitThreshold();
        return view('admin.wallet-profit-checker.over-threshold', compact('threshold'));
    }

    /**
     * Adjust individual user profit
     */
    public function adjustUserProfit(Request $request)
    {
        // Clear any output buffer to prevent BOM characters
        if (ob_get_level()) {
            ob_clean();
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_profit_amount' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $user = User::with('wallet')->findOrFail($request->user_id);
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'User wallet not found'
                ]);
            }

            $originalProfit = $wallet->profit;
            $newProfit = $request->new_profit_amount;
            $adjustmentAmount = $newProfit - $originalProfit;

            // Create transfer record for audit
            WalletProfitTransfer::create([
                'user_id' => $user->id,
                'amount' => abs($adjustmentAmount),
                'transfer_type' => $adjustmentAmount >= 0 ? 'profit_adjustment_increase' : 'profit_adjustment_decrease',
                'status' => 'completed',
                'batch_id' => 'manual_adjustment_' . time(),
                'processed_by' => auth()->id(),
                'original_profit_balance' => $originalProfit,
                'original_deposit_balance' => $wallet->deposit,
                'original_temp_balance' => $wallet->temporary_profit,
                'notes' => "Manual profit adjustment from {$originalProfit} to {$newProfit}",
                'processed_at' => now()
            ]);

            // Update wallet
            $wallet->update(['profit' => $newProfit]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User profit adjusted successfully',
                'old_amount' => number_format($originalProfit, 2),
                'new_amount' => number_format($newProfit, 2)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profit adjustment failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Adjustment failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update profit threshold setting
     */
    public function updateThreshold(Request $request)
    {
        // Clear any output buffer to prevent BOM characters
        if (ob_get_level()) {
            ob_clean();
        }

        $request->validate([
            'threshold' => 'required|numeric|min:0|max:1000'
        ]);

        try {
            WalletProfitSetting::setValue(
                'profit_threshold_percentage',
                $request->threshold,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Profit threshold updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Threshold update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transfer history
     */
    public function getTransferHistory(Request $request)
    {
        // Clear any output buffer to prevent BOM characters
        if (ob_get_level()) {
            ob_clean();
        }

        $query = WalletProfitTransfer::with(['user', 'processedBy'])
            ->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addColumn('user_info', function ($transfer) {
                return [
                    'username' => $transfer->user->username ?? 'N/A',
                    'email' => $transfer->user->email ?? 'N/A'
                ];
            })
            ->addColumn('admin_info', function ($transfer) {
                return [
                    'username' => $transfer->processedBy->username ?? 'System'
                ];
            })
            ->addColumn('transfer_details', function ($transfer) {
                return [
                    'type' => $transfer->transfer_type_display,
                    'amount' => number_format($transfer->amount, 2),
                    'status' => $transfer->status,
                    'status_color' => $transfer->status_badge_color
                ];
            })
            ->addColumn('batch_info', function ($transfer) {
                return [
                    'batch_id' => $transfer->batch_id,
                    'short_batch' => substr($transfer->batch_id, 0, 8) . '...'
                ];
            })
            ->addColumn('processed_at_formatted', function ($transfer) {
                return $transfer->processed_at ? $transfer->processed_at->format('M d, Y H:i A') : 'N/A';
            })
            ->rawColumns([])
            ->make(true);
    }

    /**
     * Get transfer history data for DataTables (alias method)
     */
    public function getTransferHistoryData(Request $request)
    {
        return $this->getTransferHistory($request);
    }

    /**
     * Get batch transfer summary for rollback reference
     */
    public function getBatchSummary(Request $request)
    {
        try {
            // Clear any output buffer to prevent BOM characters
            if (ob_get_level()) {
                ob_clean();
            }

            $query = WalletProfitTransfer::select([
                'batch_id',
                'transfer_type',
                'status',
                DB::raw('COUNT(*) as transfer_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('MIN(created_at) as batch_created_at'),
                DB::raw('MAX(processed_at) as last_processed_at')
            ])
            ->groupBy('batch_id', 'transfer_type', 'status')
            ->orderBy('batch_created_at', 'desc');

            return DataTables::of($query)
                ->addColumn('batch_info', function ($batch) {
                    return [
                        'batch_id' => $batch->batch_id,
                        'short_batch' => substr($batch->batch_id, 0, 12) . '...',
                        'full_batch' => $batch->batch_id
                    ];
                })
                ->addColumn('transfer_details', function ($batch) {
                    $typeColors = [
                        'profit_to_temp' => 'warning',
                        'temp_to_deposit' => 'success',
                        'profit_to_deposit' => 'primary',
                        'temp_to_profit' => 'info',
                        'rollback' => 'danger'
                    ];

                    return [
                        'type' => ucwords(str_replace('_', ' ', $batch->transfer_type)),
                        'type_color' => $typeColors[$batch->transfer_type] ?? 'secondary',
                        'count' => number_format($batch->transfer_count),
                        'total_amount' => number_format($batch->total_amount, 2),
                        'status' => ucfirst($batch->status),
                        'status_color' => $batch->status === 'completed' ? 'success' :
                                        ($batch->status === 'pending' ? 'warning' : 'danger')
                    ];
                })
                ->addColumn('timing', function ($batch) {
                    return [
                        'created' => $batch->batch_created_at ?
                                   Carbon::parse($batch->batch_created_at)->format('M d, Y H:i A') : 'N/A',
                        'processed' => $batch->last_processed_at ?
                                     Carbon::parse($batch->last_processed_at)->format('M d, Y H:i A') : 'Pending'
                    ];
                })
                ->addColumn('actions', function ($batch) {
                    return [
                        'batch_id' => $batch->batch_id,
                        'can_rollback' => $batch->status === 'completed' &&
                                        in_array($batch->transfer_type, ['profit_to_temp', 'temp_to_deposit', 'profit_to_deposit'])
                    ];
                })
                ->rawColumns(['actions'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error('getBatchSummary error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load batch data: ' . $e->getMessage(),
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 500);
        }
    }

    /**
     * Get transfer statistics
     */
    private function getTransferStats()
    {
        return [
            'total_transfers' => WalletProfitTransfer::count(),
            'completed_transfers' => WalletProfitTransfer::where('status', 'completed')->count(),
            'pending_transfers' => WalletProfitTransfer::where('status', 'pending')->count(),
            'users_with_temp' => User::whereHas('wallet', function ($q) {
                $q->where('temporary_profit', '>', 0);
            })->count(),
            'total_temp_amount' => Wallet::sum('temporary_profit'),
            'users_over_threshold' => User::whereHas('wallet', function ($q) {
                $threshold = WalletProfitSetting::getProfitThreshold();
                $q->whereColumn('profit', '>', DB::raw("deposit * {$threshold} / 100"));
            })->count(),
            'current_threshold' => WalletProfitSetting::getProfitThreshold()
        ];
    }
}
