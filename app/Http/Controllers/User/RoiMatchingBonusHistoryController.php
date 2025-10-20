<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class RoiMatchingBonusHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get paginated data
        $roiMatching = DB::table('referral_bonus_histories')
            ->where('user_id', $user->id)
            ->where('bonus_type', 'roi_matching')
            ->orderByDesc('id')
            ->paginate(10);

        // Calculate summary statistics
        $stats = DB::table('referral_bonus_histories')
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

        // Get level distribution
        $levelStats = DB::table('referral_bonus_histories')
            ->where('user_id', $user->id)
            ->where('bonus_type', 'roi_matching')
            ->selectRaw('level, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        // Get monthly breakdown (last 12 months)
        $monthlyStats = DB::table('referral_bonus_histories')
            ->where('user_id', $user->id)
            ->where('bonus_type', 'roi_matching')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as count,
                SUM(amount) as total_amount
            ')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        return view('user.content.referral_bonus_histories.roi_matching', compact('roiMatching', 'stats', 'levelStats', 'monthlyStats'));
    }
}
