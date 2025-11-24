<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\AiTrade;
use App\Models\AiTradeHistory;
use App\Models\Wallet;
use App\Models\TradingCronTestLog;
use App\Helpers\Helpers;
use App\Services\TelegramNotificationService;
use Carbon\Carbon;

class TradingCronTestController extends Controller
{
    /**
     * Display the trading cron test page
     */
    public function index()
    {
        // Get all users with ai_trade_activates.status = 1
        $activeUsers = DB::table('ai_trade_activates')
            ->where('status', 1)
            ->join('users', 'ai_trade_activates.user_id', '=', 'users.id')
            ->select('users.*', 'ai_trade_activates.status as activate_status')
            ->get();

        // Get recent test logs
        $recentLogs = TradingCronTestLog::with(['user', 'aiTrade'])
            ->orderBy('test_executed_at', 'desc')
            ->limit(50)
            ->get();

        // Get error summary
        $errorSummary = TradingCronTestLog::where('status', 'failed')
            ->select('failure_reason', DB::raw('COUNT(*) as count'))
            ->groupBy('failure_reason')
            ->orderBy('count', 'desc')
            ->get();

        // Get statistics
        $stats = [
            'total_active_users' => $activeUsers->count(),
            'total_tests_run' => TradingCronTestLog::count(),
            'successful_tests' => TradingCronTestLog::where('status', 'success')->count(),
            'failed_tests' => TradingCronTestLog::where('status', 'failed')->count(),
            'skipped_tests' => TradingCronTestLog::where('status', 'skipped')->count(),
        ];

        return view('admin.trading-cron-test.index', compact('activeUsers', 'recentLogs', 'errorSummary', 'stats'));
    }

    /**
     * Get data for DataTables
     */
    public function getData(Request $request)
    {
        $query = DB::table('ai_trade_activates')
            ->where('ai_trade_activates.status', 1)
            ->join('users', 'ai_trade_activates.user_id', '=', 'users.id')
            ->leftJoin('ai_trades', function($join) {
                $join->on('users.id', '=', 'ai_trades.user_id')
                     ->where('ai_trades.trade_status', 1);
            })
            ->leftJoin('wallets', 'users.id', '=', 'wallets.user_id')
            ->select(
                'users.id as user_id',
                'users.username',
                'users.email',
                'ai_trade_activates.status as activate_status',
                DB::raw('COUNT(DISTINCT ai_trades.id) as active_trades_count'),
                DB::raw('SUM(ai_trades.amount_invest) as total_investment'),
                DB::raw('MAX(wallets.profit) as wallet_profit')
            )
            ->groupBy('users.id', 'users.username', 'users.email', 'ai_trade_activates.status');

        return datatables()->of($query)
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-sm btn-primary test-user-btn" data-user-id="' . $row->user_id . '">
                            <i class="fas fa-play"></i> Test
                        </button>
                        <button class="btn btn-sm btn-info view-details-btn" data-user-id="' . $row->user_id . '">
                            <i class="fas fa-eye"></i> Details
                        </button>';
            })
            ->addColumn('status', function ($row) {
                if ($row->active_trades_count > 0) {
                    return '<span class="badge bg-success">Active</span>';
                } else {
                    return '<span class="badge bg-warning">No Active Trades</span>';
                }
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    /**
     * Test trading cron for a specific user - Part 1
     */
    public function testUser(Request $request, $userId)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($userId);
            $results = [];

            // Check if user has AI trade activated
            $aiTradeActivate = DB::table('ai_trade_activates')
                ->where('user_id', $userId)
                ->where('status', 1)
                ->first();

            if (!$aiTradeActivate) {
                TradingCronTestLog::create([
                    'user_id' => $userId,
                    'test_type' => 'manual_test',
                    'status' => 'skipped',
                    'ai_trade_active' => false,
                    'trade_status_valid' => false,
                    'wallet_exists' => false,
                    'failure_reason' => 'AI Trade not activated for this user',
                    'error_message' => 'User does not have ai_trade_activates.status = 1',
                    'test_executed_at' => now(),
                ]);

                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => 'AI Trade not activated for this user',
                    'results' => []
                ]);
            }

            // Get all active trades for this user
            $trades = AiTrade::where('trade_status', 1)
                ->where('user_id', $userId)
                ->get();

            if ($trades->isEmpty()) {
                TradingCronTestLog::create([
                    'user_id' => $userId,
                    'test_type' => 'manual_test',
                    'status' => 'skipped',
                    'ai_trade_active' => true,
                    'trade_status_valid' => false,
                    'wallet_exists' => Wallet::where('user_id', $userId)->exists(),
                    'failure_reason' => 'No active trades found (trade_status != 1)',
                    'error_message' => 'User has AI trade activated but no trades with trade_status = 1',
                    'test_executed_at' => now(),
                ]);

                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => 'No active trades found for this user',
                    'results' => []
                ]);
            }

            // Continue with processing trades...
            $results = $this->processUserTrades($userId, $trades);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Test completed for user ' . $user->username,
                'results' => $results,
                'total_trades_tested' => count($results)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log general failure
            TradingCronTestLog::create([
                'user_id' => $userId,
                'test_type' => 'manual_test',
                'status' => 'failed',
                'ai_trade_active' => false,
                'trade_status_valid' => false,
                'wallet_exists' => false,
                'failure_reason' => 'System error: ' . substr($e->getMessage(), 0, 200),
                'error_message' => $e->getMessage(),
                'error_details' => $e->getTraceAsString(),
                'test_executed_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test failed with error: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process user trades - Part 2
     */
    private function processUserTrades($userId, $trades)
    {
        $results = [];

        foreach ($trades as $trade) {
            try {
                // Get configurable settings
                $minPercent = config('trading.min_percent', 0.003);
                $maxPercent = config('trading.max_percent', 0.004);
                $percentAmountToDeduct = config('trading.percent_amount_to_deduct', 0.4);

                // Calculate profit
                $percent = mt_rand($minPercent * 1000000, $maxPercent * 1000000) / 1000000;
                $amount_to_deduct = $trade->amount_invest * $percentAmountToDeduct;
                $profit = ($trade->amount_invest - $amount_to_deduct) * $percent;

                // Check wallet exists
                $wallet = Wallet::where('user_id', $userId)->first();
                if (!$wallet) {
                    throw new \Exception('Wallet not found for user');
                }

                // Insert into ai_trade_histories (TEST MODE)
                $history = new AiTradeHistory([
                    'amount' => $profit,
                    'pnl' => $profit,
                    'description' => '[TEST] Ai Trade test - profit calculated',
                ]);
                $trade->histories()->save($history);

                // Calculate new amounts
                $totalPnl = $trade->histories()->sum('pnl');
                $newCurrentAmount = $totalPnl;
                $status = 1;
                if ($newCurrentAmount >= $trade->ending_amount) {
                    $newCurrentAmount = $trade->ending_amount;
                    $status = 2;
                }

                // Update trade
                $oldCurrentAmount = $trade->current_amount;
                $oldTradeStatus = $trade->trade_status;

                $trade->current_amount = $newCurrentAmount;
                $trade->trade_status = $status;
                $trade->updated_at = now();
                $trade->save();

                // Update wallet profit
                $oldProfit = $wallet->profit;
                $wallet->profit += $profit;
                $wallet->save();

                // If trade ended, deduct from deposit
                $depositDeduction = null;
                if ($status === 2 && $wallet->deposit >= $trade->amount_invest) {
                    $oldDeposit = $wallet->deposit;
                    $wallet->deposit -= $trade->amount_invest;
                    $wallet->save();
                    $depositDeduction = [
                        'old_deposit' => $oldDeposit,
                        'new_deposit' => $wallet->deposit,
                        'deducted' => $trade->amount_invest
                    ];
                }

                // Log successful test
                TradingCronTestLog::create([
                    'user_id' => $userId,
                    'ai_trade_id' => $trade->id,
                    'test_type' => 'manual_test',
                    'status' => 'success',
                    'profit_amount' => $profit,
                    'trade_amount' => $trade->amount_invest,
                    'current_amount' => $newCurrentAmount,
                    'ending_amount' => $trade->ending_amount,
                    'ai_trade_active' => true,
                    'trade_status_valid' => true,
                    'wallet_exists' => true,
                    'data_snapshot' => [
                        'percent' => $percent,
                        'amount_to_deduct' => $amount_to_deduct,
                        'total_pnl' => $totalPnl,
                        'old_current_amount' => $oldCurrentAmount,
                        'new_current_amount' => $newCurrentAmount,
                        'old_trade_status' => $oldTradeStatus,
                        'new_trade_status' => $status,
                        'old_wallet_profit' => $oldProfit,
                        'new_wallet_profit' => $wallet->profit,
                        'deposit_deduction' => $depositDeduction,
                    ],
                    'test_executed_at' => now(),
                ]);

                $results[] = [
                    'trade_id' => $trade->id,
                    'success' => true,
                    'profit' => $profit,
                    'message' => 'Test successful - profit calculated and logged'
                ];

            } catch (\Exception $e) {
                // Log failed test
                TradingCronTestLog::create([
                    'user_id' => $userId,
                    'ai_trade_id' => $trade->id ?? null,
                    'test_type' => 'manual_test',
                    'status' => 'failed',
                    'trade_amount' => $trade->amount_invest ?? null,
                    'current_amount' => $trade->current_amount ?? null,
                    'ending_amount' => $trade->ending_amount ?? null,
                    'ai_trade_active' => true,
                    'trade_status_valid' => true,
                    'wallet_exists' => Wallet::where('user_id', $userId)->exists(),
                    'failure_reason' => substr($e->getMessage(), 0, 255),
                    'error_message' => $e->getMessage(),
                    'error_details' => $e->getTraceAsString(),
                    'data_snapshot' => [
                        'trade_data' => $trade->toArray() ?? null,
                        'error_line' => $e->getLine(),
                        'error_file' => $e->getFile(),
                    ],
                    'test_executed_at' => now(),
                ]);

                $results[] = [
                    'trade_id' => $trade->id ?? null,
                    'success' => false,
                    'error' => $e->getMessage(),
                    'message' => 'Test failed - error logged'
                ];
            }
        }

        return $results;
    }

    /**
     * Get user details and their trades
     */
    public function getUserDetails($userId)
    {
        $user = User::findOrFail($userId);

        $aiTradeActivate = DB::table('ai_trade_activates')
            ->where('user_id', $userId)
            ->first();

        $activeTrades = AiTrade::where('user_id', $userId)
            ->where('trade_status', 1)
            ->with('histories')
            ->get();

        $wallet = Wallet::where('user_id', $userId)->first();

        $recentLogs = TradingCronTestLog::where('user_id', $userId)
            ->orderBy('test_executed_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'user' => $user,
            'ai_trade_activate' => $aiTradeActivate,
            'active_trades' => $activeTrades,
            'wallet' => $wallet,
            'recent_test_logs' => $recentLogs,
        ]);
    }

    /**
     * Get test logs data for DataTables
     */
    public function getLogsData(Request $request)
    {
        $query = TradingCronTestLog::with(['user', 'aiTrade'])
            ->select('trading_cron_test_logs.*');

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }

        return datatables()->of($query)
            ->addColumn('user_info', function ($row) {
                return $row->user ? $row->user->username . '<br><small>' . $row->user->email . '</small>' : 'N/A';
            })
            ->addColumn('trade_info', function ($row) {
                if ($row->aiTrade) {
                    return 'ID: ' . $row->ai_trade_id . '<br>Amount: $' . number_format($row->trade_amount, 2);
                }
                return 'N/A';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'success' => 'bg-success',
                    'failed' => 'bg-danger',
                    'skipped' => 'bg-warning'
                ];
                $class = $badges[$row->status] ?? 'bg-secondary';
                return '<span class="badge ' . $class . '">' . strtoupper($row->status) . '</span>';
            })
            ->addColumn('profit_display', function ($row) {
                if ($row->profit_amount) {
                    return '$' . number_format($row->profit_amount, 8);
                }
                return '-';
            })
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-sm btn-info view-log-details" data-log-id="' . $row->id . '">
                            <i class="fas fa-info-circle"></i> Details
                        </button>';
            })
            ->rawColumns(['user_info', 'trade_info', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Get specific log details
     */
    public function getLogDetails($logId)
    {
        $log = TradingCronTestLog::with(['user', 'aiTrade'])
            ->findOrFail($logId);

        return response()->json($log);
    }

    /**
     * Test all active users
     */
    public function testAllUsers(Request $request)
    {
        $activeUserIds = DB::table('ai_trade_activates')
            ->where('status', 1)
            ->pluck('user_id');

        $results = [
            'total_users' => $activeUserIds->count(),
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        foreach ($activeUserIds as $userId) {
            try {
                $response = $this->testUser($request, $userId);
                $data = json_decode($response->getContent(), true);

                if ($data['success']) {
                    $results['successful']++;
                } else {
                    $results['skipped']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Batch test completed',
            'results' => $results
        ]);
    }

    /**
     * Clear old test logs
     */
    public function clearOldLogs(Request $request)
    {
        $days = $request->input('days', 30);

        $deleted = TradingCronTestLog::where('test_executed_at', '<', now()->subDays($days))
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted $deleted old test logs (older than $days days)"
        ]);
    }
}
