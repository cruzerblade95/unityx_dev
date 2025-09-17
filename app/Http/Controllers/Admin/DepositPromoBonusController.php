<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepositPromoCampaign;
use App\Models\DepositPromoBonusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class DepositPromoBonusController extends Controller
{
    /**
     * Display a listing of deposit promo campaigns
     */
    public function index()
    {
        $activeCampaigns = DepositPromoCampaign::active()->current()->count();
        $totalCampaigns = DepositPromoCampaign::count();
        $totalBonusesGiven = DepositPromoBonusHistory::where('status', 'claimed')->sum('user_bonus_amount');
        $pendingBonuses = DepositPromoBonusHistory::where('status', 'pending')->count();

        return view('admin.deposit-promo.index', compact(
            'activeCampaigns',
            'totalCampaigns',
            'totalBonusesGiven',
            'pendingBonuses'
        ));
    }

    /**
     * Get campaigns data for DataTables
     */
    public function campaignsList(Request $request)
    {
        // Clean any output buffer to prevent BOM characters in JSON response
        if (ob_get_level()) {
            ob_end_clean();
        }

        $campaigns = DepositPromoCampaign::with('creator:id,username')
            ->select('deposit_promo_campaigns.*')
            ->orderBy('created_at', 'desc');

        return DataTables::of($campaigns)
            ->addIndexColumn()
            ->addColumn('creator_name', function ($campaign) {
                return $campaign->creator ? $campaign->creator->username : 'Unknown';
            })
            ->addColumn('status', function ($campaign) {
                if ($campaign->isCurrentlyActive()) {
                    return '<span class="badge bg-success">Active</span>';
                } elseif ($campaign->is_active && Carbon::now()->isBefore($campaign->start_datetime)) {
                    return '<span class="badge bg-warning">Scheduled</span>';
                } elseif ($campaign->is_active && Carbon::now()->isAfter($campaign->end_datetime)) {
                    return '<span class="badge bg-secondary">Expired</span>';
                } else {
                    return '<span class="badge bg-danger">Inactive</span>';
                }
            })
            ->addColumn('duration', function ($campaign) {
                return $campaign->start_datetime->format('M j, Y g:i A') . '<br>' .
                       $campaign->end_datetime->format('M j, Y g:i A');
            })
            ->addColumn('bonuses', function ($campaign) {
                return 'User: ' . $campaign->percentage_bonus . '%<br>' .
                       'Upline: ' . $campaign->percentage_upline_bonus . '%';
            })
            ->addColumn('actions', function ($campaign) {
                $btn = '<div class="btn-group" role="group">';
                $btn .= '<button type="button" class="btn btn-sm btn-info me-1" onclick="viewCampaign(' . $campaign->id . ')">
                            <i class="fas fa-eye"></i>
                        </button>';
                $btn .= '<button type="button" class="btn btn-sm btn-warning me-1" onclick="editCampaign(' . $campaign->id . ')">
                            <i class="fas fa-edit"></i>
                        </button>';
                $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteCampaign(' . $campaign->id . ')">
                            <i class="fas fa-trash"></i>
                        </button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'duration', 'bonuses', 'actions'])
            ->make(true);
    }

    /**
     * Store a new campaign
     */
    public function store(Request $request)
    {
        // Handle checkbox manually
        $data = $request->all();
        $data['is_active'] = $request->input('is_active') === '1' ? true : false;

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date|after_or_equal:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'percentage_bonus' => 'required|numeric|min:0|max:100',
            'percentage_upline_bonus' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $campaign = DepositPromoCampaign::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'percentage_bonus' => $request->percentage_bonus,
            'percentage_upline_bonus' => $request->percentage_upline_bonus,
            'is_active' => $data['is_active'],
            'created_by' => Auth::guard('admin')->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaign created successfully',
            'campaign' => $campaign
        ]);
    }

    /**
     * Display campaign details
     */
    public function show($id)
    {
        $campaign = DepositPromoCampaign::with('creator:id,username')->findOrFail($id);

        return response()->json([
            'success' => true,
            'campaign' => $campaign
        ]);
    }

    /**
     * Update a campaign
     */
    public function update(Request $request, $id)
    {
        $campaign = DepositPromoCampaign::findOrFail($id);

        // Handle checkbox manually
        $data = $request->all();
        $data['is_active'] = $request->input('is_active') === '1' ? true : false;

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'percentage_bonus' => 'required|numeric|min:0|max:100',
            'percentage_upline_bonus' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $campaign->update([
            'name' => $request->name,
            'description' => $request->description,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'percentage_bonus' => $request->percentage_bonus,
            'percentage_upline_bonus' => $request->percentage_upline_bonus,
            'is_active' => $data['is_active']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaign updated successfully',
            'campaign' => $campaign
        ]);
    }

    /**
     * Delete a campaign
     */
    public function destroy($id)
    {
        $campaign = DepositPromoCampaign::findOrFail($id);

        // Check if campaign has associated bonus history
        $hasHistory = $campaign->bonusHistory()->exists();

        if ($hasHistory) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete campaign with existing bonus history'
            ], 422);
        }

        $campaign->delete();

        return response()->json([
            'success' => true,
            'message' => 'Campaign deleted successfully'
        ]);
    }

    /**
     * Get bonus history data for DataTables
     */
    public function bonusHistoryList(Request $request)
    {
        // Clean any output buffer to prevent BOM characters in JSON response
        if (ob_get_level()) {
            ob_end_clean();
        }

        $bonusHistory = DepositPromoBonusHistory::with(['user:id,firstname,lastname,email', 'campaign:id,name'])
            ->select('deposit_promo_bonus_history.*')
            ->orderBy('created_at', 'desc');

        return DataTables::of($bonusHistory)
            ->addIndexColumn()
            ->addColumn('user_info', function ($history) {
                if ($history->user) {
                    return $history->user->firstname . ' ' . $history->user->lastname . '<br>' .
                           '<small class="text-muted">' . $history->user->email . '</small>';
                }
                return 'Unknown User';
            })
            ->addColumn('campaign_name', function ($history) {
                return $history->campaign ? $history->campaign->name : 'Unknown Campaign';
            })
            ->addColumn('amounts', function ($history) {
                return 'Deposit: $' . number_format($history->deposit_amount, 2) . '<br>' .
                       'User Bonus: $' . number_format($history->user_bonus_amount, 2) . '<br>' .
                       'Upline Bonus: $' . number_format($history->upline_bonus_amount, 2);
            })
            ->addColumn('status_badge', function ($history) {
                switch ($history->status) {
                    case 'pending':
                        return '<span class="badge bg-warning">Pending</span>';
                    case 'claimed':
                        return '<span class="badge bg-success">Claimed</span>';
                    case 'expired':
                        return '<span class="badge bg-danger">Expired</span>';
                    default:
                        return '<span class="badge bg-secondary">Unknown</span>';
                }
            })
            ->addColumn('expires_info', function ($history) {
                if ($history->expires_at) {
                    $isExpired = Carbon::now()->isAfter($history->expires_at);
                    $color = $isExpired ? 'text-danger' : 'text-info';
                    return '<span class="' . $color . '">' . $history->expires_at->format('M j, Y g:i A') . '</span>';
                }
                return 'No expiration';
            })
            ->addColumn('claimed_info', function ($history) {
                return $history->claimed_at ? $history->claimed_at->format('M j, Y g:i A') : '-';
            })
            ->rawColumns(['user_info', 'amounts', 'status_badge', 'expires_info'])
            ->make(true);
    }
}
