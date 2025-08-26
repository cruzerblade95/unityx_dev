<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Deposit;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GroupSalesController extends Controller
{
    /**
     * Display the group sales dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Get downline sales data (up to 10 levels)
        $downlineSales = $this->getDownlineSales($user->id, 10);

        // Get total sales amount
        $totalSales = $this->getTotalDownlineSales($user->id, 10);

        // Get reward claims for current month
        $currentMonth = Carbon::now()->format('Y-m');
        $claimedRewards = DB::table('group_sales_rewards')
            ->where('user_id', $user->id)
            ->where('claimed_month', $currentMonth)
            ->pluck('reward_level')
            ->toArray();

        // Define reward thresholds and tax percentages
        $rewards = [
            1 => ['threshold' => 5000, 'tax' => 3],
            2 => ['threshold' => 15000, 'tax' => 4],
            3 => ['threshold' => 50000, 'tax' => 5],
            4 => ['threshold' => 100000, 'tax' => 6],
            5 => ['threshold' => 150000, 'tax' => 7],
            6 => ['threshold' => 300000, 'tax' => 8],
            7 => ['threshold' => 500000, 'tax' => 9],
            8 => ['threshold' => 1000000, 'tax' => 10],
        ];

        return view('user.group-sales.index', compact('downlineSales', 'totalSales', 'rewards', 'claimedRewards'));
    }

    /**
     * Claim a reward
     */
    public function claimReward(Request $request)
    {
        $request->validate([
            'reward_level' => 'required|integer|min:1|max:8'
        ]);

        $user = Auth::user();
        $rewardLevel = $request->reward_level;
        $currentMonth = Carbon::now()->format('Y-m');

        // Check if already claimed this month
        $alreadyClaimed = DB::table('group_sales_rewards')
            ->where('user_id', $user->id)
            ->where('reward_level', $rewardLevel)
            ->where('claimed_month', $currentMonth)
            ->exists();

        if ($alreadyClaimed) {
            return response()->json([
                'success' => false,
                'message' => 'You have already claimed this reward this month.'
            ]);
        }

        // Define reward thresholds and tax percentages
        $rewards = [
            1 => ['threshold' => 5000, 'tax' => 3],
            2 => ['threshold' => 15000, 'tax' => 4],
            3 => ['threshold' => 50000, 'tax' => 5],
            4 => ['threshold' => 100000, 'tax' => 6],
            5 => ['threshold' => 150000, 'tax' => 7],
            6 => ['threshold' => 300000, 'tax' => 8],
            7 => ['threshold' => 500000, 'tax' => 9],
            8 => ['threshold' => 1000000, 'tax' => 10],
        ];

        if (!isset($rewards[$rewardLevel])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reward level.'
            ]);
        }

        $reward = $rewards[$rewardLevel];
        $totalSales = $this->getTotalDownlineSales($user->id, 10);

        // Check if user meets the sales threshold
        if ($totalSales < $reward['threshold']) {
            return response()->json([
                'success' => false,
                'message' => "You need $" . number_format($reward['threshold']) . " total sales to claim this reward. Current sales: $" . number_format($totalSales)
            ]);
        }

        try {
            DB::beginTransaction();

            // Calculate reward amount: threshold * 15% * tax%
            // Example: Reward 1: $5,000 sales x 15% x 3% tax = $5,000 * 0.15 * 0.03 = $22.50
            $basePercentage = 15; // 15% of sales threshold
            $taxPercentage = $reward['tax']; // Tax percentage for this reward level
            $rewardAmount = $reward['threshold'] * ($basePercentage / 100) * ($taxPercentage / 100);

            // Add to user's wallet bonus
            $wallet = Wallet::where('user_id', $user->id)->first();
            if (!$wallet) {
                // Create wallet if doesn't exist
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'deposit' => 0,
                    'profit' => 0,
                    'bonus' => 0,
                    'cash' => 0
                ]);
            }

            $wallet->increment('bonus', $rewardAmount);

            // Record the claim in history
            DB::table('group_sales_rewards')->insert([
                'user_id' => $user->id,
                'reward_level' => $rewardLevel,
                'sales_threshold' => $reward['threshold'],
                'reward_amount' => $rewardAmount,
                'tax_percentage' => $taxPercentage,
                'total_sales_at_claim' => $totalSales,
                'claimed_month' => $currentMonth,
                'claimed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Reward claimed successfully! $" . number_format($rewardAmount, 2) . " has been added to your bonus balance.",
                'reward_amount' => $rewardAmount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Group sales reward claim failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to claim reward. Please try again.'
            ]);
        }
    }

    /**
     * Get reward claim history
     */
    public function history()
    {
        $user = Auth::user();

        $history = DB::table('group_sales_rewards')
            ->where('user_id', $user->id)
            ->orderBy('claimed_at', 'desc')
            ->paginate(20);

        return view('user.group-sales.history', compact('history'));
    }

    /**
     * Get downline sales data recursively up to specified level
     */
    private function getDownlineSales($userId, $maxLevel, $currentLevel = 1)
    {
        if ($currentLevel > $maxLevel) {
            return collect();
        }

        // Get direct downlines
        $directDownlines = User::where('upline_id', $userId)->get();
        $salesData = collect();

        foreach ($directDownlines as $downline) {
            // Get sales amount for this downline (current month only)
            $salesAmount = Deposit::where('user_id', $downline->id)
                ->where('status', 'completed')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('net_amount');

            $salesData->push([
                'user_id' => $downline->id,
                'username' => $downline->username,
                'firstname' => $downline->firstname,
                'lastname' => $downline->lastname,
                'level' => $currentLevel,
                'sales_amount' => $salesAmount,
                'join_date' => $downline->created_at
            ]);

            // Recursively get sales from this downline's downlines
            if ($currentLevel < $maxLevel) {
                $subDownlineSales = $this->getDownlineSales($downline->id, $maxLevel, $currentLevel + 1);
                $salesData = $salesData->merge($subDownlineSales);
            }
        }

        return $salesData;
    }

    /**
     * Get total sales amount from all downlines up to specified level
     */
    private function getTotalDownlineSales($userId, $maxLevel)
    {
        $allDownlineIds = $this->getAllDownlineIds($userId, $maxLevel);

        return Deposit::whereIn('user_id', $allDownlineIds)
            ->where('status', 'completed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('net_amount');
    }

    /**
     * Get all downline user IDs recursively up to specified level
     */
    private function getAllDownlineIds($userId, $maxLevel, $currentLevel = 1)
    {
        if ($currentLevel > $maxLevel) {
            return collect();
        }

        $directDownlines = User::where('upline_id', $userId)->pluck('id');
        $allDownlines = $directDownlines;

        foreach ($directDownlines as $downlineId) {
            if ($currentLevel < $maxLevel) {
                $subDownlines = $this->getAllDownlineIds($downlineId, $maxLevel, $currentLevel + 1);
                $allDownlines = $allDownlines->merge($subDownlines);
            }
        }

        return $allDownlines->unique();
    }
}
