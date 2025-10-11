<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupSalesReward;
use App\Models\GroupSalesHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class GroupSalesController extends Controller
{
    /**
     * Display group sales rewards management
     */
    public function index()
    {
        $rewards = GroupSalesReward::orderBy('sales_required')->get();
        return view('admin.group-sales.index', compact('rewards'));
    }

    /**
     * Get rewards data for DataTables
     */
    public function getRewardsData()
    {
        $rewards = GroupSalesReward::select([
            'id',
            'reward_title',
            'reward_level',
            'sales_required',
            'base_percentage',
            'tax_percentage',
            'is_active'
        ]);

        return DataTables::of($rewards)
            ->addColumn('reward_amount', function ($reward) {
                return $reward->reward_amount;
            })
            ->addColumn('actions', function ($reward) {
                return '<div class="btn-group" role="group">
                    <button class="btn btn-sm btn-primary edit-reward" data-id="' . $reward->id . '">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger delete-reward" data-id="' . $reward->id . '">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Store a new reward
     */
    public function store(Request $request)
    {
        $request->validate([
            'reward_title' => 'required|string|max:255',
            'reward_level' => 'required|integer|min:1|max:50|unique:group_sales_reward_configs',
            'sales_required' => 'required|numeric|min:0',
            'base_percentage' => 'required|numeric|min:0|max:100',
            'tax_percentage' => 'required|numeric|min:0|max:100',
        ]);

        try {
            GroupSalesReward::create([
                'reward_title' => $request->reward_title,
                'reward_level' => $request->reward_level,
                'sales_required' => $request->sales_required,
                'base_percentage' => $request->base_percentage,
                'tax_percentage' => $request->tax_percentage,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reward created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating reward: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a reward
     */
    public function update(Request $request, $id)
    {
        $reward = GroupSalesReward::findOrFail($id);

        $request->validate([
            'reward_title' => 'required|string|max:255',
            'reward_level' => 'required|integer|min:1|max:50|unique:group_sales_reward_configs,reward_level,' . $id,
            'sales_required' => 'required|numeric|min:0',
            'base_percentage' => 'required|numeric|min:0|max:100',
            'tax_percentage' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $reward->update([
                'reward_title' => $request->reward_title,
                'reward_level' => $request->reward_level,
                'sales_required' => $request->sales_required,
                'base_percentage' => $request->base_percentage,
                'tax_percentage' => $request->tax_percentage,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reward updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating reward: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single reward for editing
     */
    public function show($id)
    {
        try {
            $reward = GroupSalesReward::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $reward
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reward not found'
            ], 404);
        }
    }

    /**
     * Delete a reward
     */
    public function destroy(GroupSalesReward $reward)
    {
        try {
            $reward->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reward deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting reward: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display claims history
     */
    public function history()
    {
        return view('admin.group-sales.history');
    }

    /**
     * Get history data for DataTables
     */
    public function getHistoryData(Request $request)
    {
        $query = DB::table('group_sales_rewards as gsr')
            ->join('users as u', 'gsr.user_id', '=', 'u.id')
            ->leftJoin('group_sales_reward_configs as gsrc', 'gsr.reward_level', '=', 'gsrc.reward_level')
            ->select([
                'gsr.id',
                'u.name as user_name',
                'u.email as user_email',
                'gsrc.reward_title',
                'gsr.reward_level',
                'gsr.sales_threshold as sales_amount',
                'gsr.reward_amount',
                'gsr.tax_percentage',
                'gsr.total_sales_at_claim',
                'gsr.claimed_month',
                'gsr.claimed_at',
                'gsr.created_at'
            ]);

        if ($request->has('get_stats') && $request->get_stats) {
            $stats = [
                'total' => $query->count(),
                'pending' => 0, // No pending status in current structure
                'approved' => $query->count(), // All claims are considered approved
                'rejected' => 0, // No rejected status in current structure
            ];
            return response()->json($stats);
        }

        return DataTables::of($query)
            ->addColumn('actions', function ($claim) {
                return '<button class="btn btn-sm btn-info view-claim" data-id="' . $claim->id . '">
                    <i class="fas fa-eye"></i> View
                </button>';
            })
            ->editColumn('claimed_at', function ($claim) {
                return Carbon::parse($claim->claimed_at)->format('Y-m-d H:i:s');
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Approve a claim
     */
    public function approveClaim(Request $request, GroupSalesHistory $claim)
    {
        try {
            DB::beginTransaction();

            $claim->update(['status' => 'approved']);

            // Add reward amount to user's bonus balance
            $user = $claim->user;
            $user->increment('bonus_balance', $claim->reward_amount);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Claim approved successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error approving claim: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a claim
     */
    public function rejectClaim(Request $request, GroupSalesHistory $claim)
    {
        try {
            $claim->update(['status' => 'rejected']);

            return response()->json([
                'success' => true,
                'message' => 'Claim rejected successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting claim: ' . $e->getMessage()
            ], 500);
        }
    }
}
