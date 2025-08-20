<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class CalculationDepositProfitController extends Controller
{
    public function calculate()
    {
        $rows = DB::table('combined_ai_trade_histories')->get();
        $results = [];
        foreach ($rows as $row) {
            $final_deposit = $row->deposit - $row->reinvest;
            $final_profit = ($row->profit + $row->reinvest) - $row->amount_invest - $row->bonus;
            // Update wallets table for this user
            DB::table('wallets')->where('user_id', $row->user_id)
                ->update([
                    'deposit' => $final_deposit,
                    'profit' => $final_profit
                ]);
            $results[] = [
                'user_id' => $row->user_id,
                'final_deposit' => $final_deposit,
                'final_profit' => $final_profit
            ];
        }
        return response()->json([
            'results' => $results
        ]);
    }
}
