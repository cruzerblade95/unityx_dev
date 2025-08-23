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
        return view('user.content.referral_bonus_histories.index', compact('roiMatching', 'deposit'));
    }
}
