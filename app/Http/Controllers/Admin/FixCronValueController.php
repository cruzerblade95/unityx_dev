<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Wallet;

class FixCronValueController extends Controller
{
    public function index()
    {
        return view('admin.fix_cron_value.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'timestamp' => 'required|date',
        ]);
        $timestamp = $request->input('timestamp');
        $results = [];

        // Process AI Trade Histories (join with ai_trades to get user_id)
        $aiTradeSums = DB::table('ai_trade_histories')
            ->join('ai_trades', 'ai_trade_histories.ai_trade_id', '=', 'ai_trades.id')
            ->where('ai_trade_histories.created_at', '>=', $timestamp)
            ->select('ai_trades.user_id', DB::raw('SUM(ai_trade_histories.pnl) as total_pnl'))
            ->groupBy('ai_trades.user_id')
            ->get();
        foreach ($aiTradeSums as $row) {
            $wallet = Wallet::where('user_id', $row->user_id)->first();
            if ($wallet) {
                $wallet->profit -= $row->total_pnl;
                $wallet->save();
                $results[$row->user_id]['profit'] = $row->total_pnl;
            }
        }
        // Delete summed ai_trade_histories
        DB::table('ai_trade_histories')->where('created_at', '>=', $timestamp)->delete();

        // Process Referral Bonus Histories
        $bonusSums = DB::table('referral_bonus_histories')
            ->select('user_id', DB::raw('SUM(amount) as total_bonus'))
            ->where('created_at', '>=', $timestamp)
            ->groupBy('user_id')
            ->get();
        foreach ($bonusSums as $row) {
            $wallet = Wallet::where('user_id', $row->user_id)->first();
            if ($wallet) {
                $wallet->bonus -= $row->total_bonus;
                $wallet->save();
                $results[$row->user_id]['bonus'] = $row->total_bonus;
            }
        }
        // Delete summed referral_bonus_histories
        DB::table('referral_bonus_histories')->where('created_at', '>=', $timestamp)->delete();

        // Prepare result for view
        $users = User::whereIn('id', array_keys($results))->get()->keyBy('id');
        return view('admin.fix_cron_value.index', compact('results', 'users', 'timestamp'));
    }
}
