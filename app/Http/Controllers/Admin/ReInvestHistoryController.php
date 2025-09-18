<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReInvestHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReInvestHistoryController extends Controller
{
    /**
     * Display the reinvestment history management page
     */
    public function index()
    {
        return view('admin.reinvest-history.index');
    }

    /**
     * Get data for DataTables
     */
    public function getData(Request $request)
    {
        // Clear any output buffers that might interfere with JSON response
        if (ob_get_level()) {
            ob_clean();
        }

        try {
            $query = ReInvestHistory::with(['user', 'aiTrade', 'processedBy'])
                ->select('reinvest_histories.*');

            return DataTables::of($query)
                ->addColumn('user_info', function ($history) {
                    if (!$history->user) {
                        return [
                            'id' => 'N/A',
                            'username' => 'Deleted User',
                            'email' => 'N/A'
                        ];
                    }
                    return [
                        'id' => $history->user->id,
                        'username' => $history->user->username ?? 'N/A',
                        'email' => $history->user->email ?? 'N/A'
                    ];
                })
                ->addColumn('amounts', function ($history) {
                    return [
                        'original' => number_format($history->original_amount, 2),
                        'tax' => number_format($history->tax_amount, 2),
                        'net' => number_format($history->net_amount, 2),
                        'expected_return' => number_format($history->expected_return ?? 0, 2)
                    ];
                })
                ->addColumn('current_status', function ($history) {
                    $currentValue = $history->aiTrade ?
                        ($history->aiTrade->current_amount ?? $history->net_amount) :
                        $history->net_amount;

                    $profitLoss = $currentValue - $history->net_amount;

                    // Calculate progress percentage manually to avoid accessor issues
                    $progress = 0;
                    if ($history->expected_return && $history->expected_return > $history->net_amount) {
                        $progress = (($currentValue - $history->net_amount) / ($history->expected_return - $history->net_amount)) * 100;
                        $progress = min(100, max(0, $progress));
                    }

                    return [
                        'current_value' => number_format($currentValue, 2),
                        'profit_loss' => number_format($profitLoss, 2),
                        'profit_loss_class' => $profitLoss >= 0 ? 'text-success' : 'text-danger',
                        'progress' => round($progress, 1)
                    ];
                })
                ->addColumn('wallet_badge', function ($history) {
                    $color = $history->wallet_type === 'profit' ? 'warning' : 'info';
                    return [
                        'type' => $history->wallet_type,
                        'color' => $color
                    ];
                })
                ->addColumn('status_badge', function ($history) {
                    switch ($history->status) {
                        case 'completed':
                            $color = 'success';
                            break;
                        case 'pending':
                            $color = 'warning';
                            break;
                        case 'failed':
                            $color = 'danger';
                            break;
                        case 'cancelled':
                            $color = 'secondary';
                            break;
                        default:
                            $color = 'primary';
                    }
                    return [
                        'status' => $history->status,
                        'color' => $color
                    ];
                })
                ->addColumn('ai_trade_info', function ($history) {
                    if ($history->aiTrade) {
                        return [
                            'id' => $history->aiTrade->id,
                            'status' => $history->aiTrade->trade_status ?? 'N/A',
                            'current_amount' => number_format($history->aiTrade->current_amount ?? 0, 2),
                            'ending_amount' => number_format($history->aiTrade->ending_amount ?? 0, 2)
                        ];
                    }
                    return null;
                })
                ->addColumn('processed_info', function ($history) {
                    return [
                        'processed_at' => $history->processed_at ? $history->processed_at->format('M d, Y H:i A') : 'N/A',
                        'processed_by' => $history->processedBy ? $history->processedBy->username : 'System'
                    ];
                })
                ->addColumn('actions', function ($history) {
                    return $history->id;
                })
                ->filterColumn('user_info', function ($query, $keyword) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('username', 'like', "%{$keyword}%")
                          ->orWhere('email', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('wallet_type', function ($query, $keyword) {
                    $query->where('wallet_type', $keyword);
                })
                ->filterColumn('status', function ($query, $keyword) {
                    $query->where('status', $keyword);
                })
                ->orderColumn('user_info', function ($query, $order) {
                    $query->join('users', 'users.id', '=', 'reinvest_histories.user_id')
                          ->orderBy('users.username', $order);
                })
                ->rawColumns(['actions'])
                ->make(true);        } catch (\Exception $e) {
            \Log::error('ReInvestHistory DataTables Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error loading data: ' . $e->getMessage()
            ], 200, [
                'Content-Type' => 'application/json; charset=utf-8'
            ]);
        }
    }    /**
     * Show the form for editing the specified reinvestment history
     */
    public function edit($id)
    {
        // Clear any output buffer to prevent BOM characters
        if (ob_get_level()) {
            ob_clean();
        }

        $history = ReInvestHistory::with(['user', 'aiTrade', 'processedBy'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }

    /**
     * Update the specified reinvestment history
     */
    public function update(Request $request, $id)
    {
        // Clear any output buffer to prevent BOM characters
        if (ob_get_level()) {
            ob_clean();
        }

        $request->validate([
            'status' => 'required|in:pending,completed,failed,cancelled',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $history = ReInvestHistory::findOrFail($id);

            $history->update([
                'status' => $request->status,
                'notes' => $request->notes,
                'processed_by' => auth()->id(),
                'processed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reinvestment history updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reinvestment history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for the dashboard
     */
    public function getStats()
    {
        // Clear any output buffer to prevent BOM characters
        if (ob_get_level()) {
            ob_clean();
        }

        $stats = [
            'total_reinvestments' => ReInvestHistory::count(),
            'total_volume' => ReInvestHistory::sum('original_amount'),
            'total_tax_collected' => ReInvestHistory::sum('tax_amount'),
            'total_net_invested' => ReInvestHistory::sum('net_amount'),
            'by_status' => ReInvestHistory::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'by_wallet_type' => ReInvestHistory::select('wallet_type', DB::raw('count(*) as count'), DB::raw('sum(original_amount) as total_amount'))
                ->groupBy('wallet_type')
                ->get()
                ->keyBy('wallet_type')
                ->toArray(),
            'recent_activity' => ReInvestHistory::with(['user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($history) {
                    return [
                        'id' => $history->id,
                        'user' => $history->user->username,
                        'amount' => $history->original_amount,
                        'wallet_type' => $history->wallet_type,
                        'status' => $history->status,
                        'created_at' => $history->created_at->format('M d, Y H:i A')
                    ];
                })
        ];

        return response()->json($stats);
    }

    /**
     * Export reinvestment history data
     */
    public function export(Request $request)
    {
        $query = ReInvestHistory::with(['user', 'aiTrade'])
            ->orderBy('created_at', 'desc');

        // Apply filters if provided
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('wallet_type') && $request->wallet_type !== '') {
            $query->where('wallet_type', $request->wallet_type);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $histories = $query->get();

        $csvData = [];
        $csvData[] = [
            'ID', 'User', 'Email', 'Original Amount', 'Tax Amount', 'Net Amount',
            'Wallet Type', 'Status', 'Expected Return', 'AI Trade ID',
            'Current Value', 'Profit/Loss', 'Processed At', 'Notes'
        ];

        foreach ($histories as $history) {
            $currentValue = $history->aiTrade ?
                ($history->aiTrade->current_amount ?? $history->net_amount) :
                $history->net_amount;
            $profitLoss = $currentValue - $history->net_amount;

            $csvData[] = [
                $history->id,
                $history->user->username,
                $history->user->email,
                $history->original_amount,
                $history->tax_amount,
                $history->net_amount,
                ucfirst($history->wallet_type),
                ucfirst($history->status),
                $history->expected_return ?? 0,
                $history->ai_trade_id ?? 'N/A',
                $currentValue,
                $profitLoss,
                $history->processed_at->format('Y-m-d H:i:s'),
                $history->notes ?? ''
            ];
        }

        $filename = 'reinvestment_history_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}
