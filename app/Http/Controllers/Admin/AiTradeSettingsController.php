<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AiTrade;
use App\Models\AiTradeActivate;
use App\Models\AiTradeHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Yajra\DataTables\Facades\DataTables;

class AiTradeSettingsController extends Controller
{
    /**
     * Display AI Trade Settings dashboard
     */
    public function index()
    {
        // Get current settings from config or set defaults
        $settings = [
            'min_percent' => config('trading.min_percent', 0.003),
            'max_percent' => config('trading.max_percent', 0.004),
            'percent_amount_to_deduct' => config('trading.percent_amount_to_deduct', 0.4),
        ];

        // Get statistics
        $stats = [
            'total_users' => AiTradeActivate::count(),
            'active_users' => AiTradeActivate::where('status', 1)->count(),
            'cancelled_users' => AiTradeActivate::where('status', 0)->count(),
            'ended_users' => AiTradeActivate::where('status', 2)->count(),
            'total_trades' => AiTrade::count(),
            'active_trades' => AiTrade::where('trade_status', 1)->count(),
        ];

        return view('admin.ai-trade-settings.index', compact('settings', 'stats'));
    }

    /**
     * Update trading settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'min_percent' => 'required|numeric|min:0|max:1',
            'max_percent' => 'required|numeric|min:0|max:1',
            'percent_amount_to_deduct' => 'required|numeric|min:0|max:1',
        ]);

        try {
            // Update config file or database settings
            // For now, we'll store in a settings table or config
            $settingsPath = config_path('trading.php');

            $configContent = "<?php\n\nreturn [\n";
            $configContent .= "    'min_percent' => {$request->min_percent},\n";
            $configContent .= "    'max_percent' => {$request->max_percent},\n";
            $configContent .= "    'percent_amount_to_deduct' => {$request->percent_amount_to_deduct},\n";
            $configContent .= "];\n";

            file_put_contents($settingsPath, $configContent);

            return response()->json([
                'success' => true,
                'message' => 'Trading settings updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating trading settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users list for DataTables
     */
    public function getUsersList(Request $request)
    {
        try {
            $query = AiTradeActivate::with(['user'])
                ->select([
                    'ai_trade_activates.id',
                    'ai_trade_activates.user_id',
                    'ai_trade_activates.status',
                    'ai_trade_activates.created_at',
                    'ai_trade_activates.updated_at'
                ]);

            return DataTables::of($query)
                ->addColumn('user_info', function ($activate) {
                    $user = $activate->user;
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
                ->addColumn('trades_count', function ($activate) {
                    return AiTrade::where('user_id', $activate->user_id)->count();
                })
                ->addColumn('active_trades', function ($activate) {
                    return AiTrade::where('user_id', $activate->user_id)
                        ->where('trade_status', 1)->count();
                })
                ->addColumn('total_investment', function ($activate) {
                    return AiTrade::where('user_id', $activate->user_id)->sum('amount_invest');
                })
                ->addColumn('status_badge', function ($activate) {
                    $statusLabels = [
                        0 => ['label' => 'Cancelled', 'class' => 'danger'],
                        1 => ['label' => 'Running', 'class' => 'success'],
                        2 => ['label' => 'Ended', 'class' => 'warning']
                    ];

                    $status = $statusLabels[$activate->status] ?? ['label' => 'Unknown', 'class' => 'secondary'];

                    return [
                        'status' => $activate->status,
                        'label' => $status['label'],
                        'class' => $status['class']
                    ];
                })
                ->addColumn('action', function ($activate) {
                    $actions = '<div class="btn-group" role="group">';

                    $actions .= '<button type="button" class="btn btn-info btn-sm view-details-btn"
                                    data-id="' . $activate->user_id . '"
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewDetailsModal">
                                    <i class="fas fa-eye"></i> View Details
                                </button>';

                    $actions .= '<button type="button" class="btn btn-warning btn-sm change-status-btn"
                                    data-id="' . $activate->id . '"
                                    data-user-id="' . $activate->user_id . '"
                                    data-current-status="' . $activate->status . '"
                                    data-bs-toggle="modal"
                                    data-bs-target="#changeStatusModal">
                                    <i class="fas fa-edit"></i> Change Status
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

        } catch (\Exception $e) {
            Log::error('DataTables error in getUsersList', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get user trades for details modal
     */
    public function getUserTrades($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $trades = AiTrade::where('user_id', $userId)
                ->with(['histories'])
                ->orderBy('created_at', 'desc')
                ->get();

            $tradesData = $trades->map(function ($trade) {
                return [
                    'id' => $trade->id,
                    'amount_invest' => $trade->amount_invest,
                    'current_amount' => $trade->current_amount,
                    'ending_amount' => $trade->ending_amount,
                    'trade_status' => $trade->trade_status,
                    'trade_status_label' => $trade->trade_status == 1 ? 'Running' : 'Ended',
                    'histories_count' => $trade->histories->count(),
                    'total_pnl' => $trade->histories->sum('pnl'),
                    'created_at' => $trade->created_at->format('M d, Y H:i'),
                    'updated_at' => $trade->updated_at->format('M d, Y H:i'),
                ];
            });

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                ],
                'trades' => $tradesData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching user trades: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update AI Trade Activate status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:0,1,2'
        ]);

        try {
            $activate = AiTradeActivate::findOrFail($id);
            $activate->update(['status' => $request->status]);

            $statusLabels = [
                0 => 'Cancelled',
                1 => 'Running',
                2 => 'Ended'
            ];

            return response()->json([
                'success' => true,
                'message' => "Status updated to {$statusLabels[$request->status]} successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update AI Trade data
     */
    public function updateTrade(Request $request, $id)
    {
        $request->validate([
            'amount_invest' => 'required|numeric|min:0',
            'current_amount' => 'required|numeric|min:0',
            'trade_status' => 'required|integer|in:1,2'
        ]);

        try {
            $trade = AiTrade::findOrFail($id);

            // Calculate ending_amount as 5 times the amount_invest
            $ending_amount = $request->amount_invest * 5;

            $trade->update([
                'amount_invest' => $request->amount_invest,
                'current_amount' => $request->current_amount,
                'ending_amount' => $ending_amount,
                'trade_status' => $request->trade_status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Trade updated successfully!',
                'trade' => [
                    'id' => $trade->id,
                    'amount_invest' => $trade->amount_invest,
                    'current_amount' => $trade->current_amount,
                    'ending_amount' => $trade->ending_amount,
                    'trade_status' => $trade->trade_status,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating trade: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete AI Trade
     */
    public function deleteTrade($id)
    {
        try {
            $trade = AiTrade::findOrFail($id);

            // Delete related histories first
            $trade->histories()->delete();

            // Delete the trade
            $trade->delete();

            return response()->json([
                'success' => true,
                'message' => 'Trade deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting trade: ' . $e->getMessage()
            ], 500);
        }
    }
}
