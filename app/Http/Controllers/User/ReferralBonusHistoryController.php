<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ReferralBonusHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get paginated data
        $roiMatching = DB::table('referral_bonus_histories')
            ->where('user_id', $user->id)
            ->where('bonus_type', 'roi_matching')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'roi_page');
        $deposit = DB::table('referral_bonus_histories')
            ->where('user_id', $user->id)
            ->where('bonus_type', 'deposit')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'deposit_page');

        // Calculate summary statistics for deposit bonuses
        $depositStats = DB::table('referral_bonus_histories')
            ->where('user_id', $user->id)
            ->where('bonus_type', 'deposit')
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(amount) as total_amount,
                AVG(amount) as average_amount,
                MAX(amount) as highest_amount,
                MIN(amount) as lowest_amount
            ')
            ->first();

        // Calculate summary statistics for ROI matching bonuses
        $roiStats = DB::table('referral_bonus_histories')
            ->where('user_id', $user->id)
            ->where('bonus_type', 'roi_matching')
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(amount) as total_amount,
                AVG(amount) as average_amount,
                MAX(amount) as highest_amount,
                MIN(amount) as lowest_amount
            ')
            ->first();

        // Get level distribution for deposit bonuses
        $depositLevelStats = DB::table('referral_bonus_histories')
            ->where('user_id', $user->id)
            ->where('bonus_type', 'deposit')
            ->selectRaw('level, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        // Get monthly breakdown for deposit bonuses (last 6 months)
        $depositMonthlyStats = DB::table('referral_bonus_histories')
            ->where('user_id', $user->id)
            ->where('bonus_type', 'deposit')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as count,
                SUM(amount) as total_amount
            ')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        // Combined statistics
        $totalStats = DB::table('referral_bonus_histories')
            ->where('user_id', $user->id)
            ->whereIn('bonus_type', ['deposit', 'roi_matching'])
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(amount) as total_amount,
                AVG(amount) as average_amount
            ')
            ->first();

        return view('user.content.referral_bonus_histories.index', compact(
            'roiMatching', 'deposit', 'depositStats', 'roiStats',
            'depositLevelStats', 'depositMonthlyStats', 'totalStats'
        ));
    }
}
