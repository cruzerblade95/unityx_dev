<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AiTradeSeeder extends Seeder
{
    public function run(): void
    {
        // Example: Seed 5 AI trades for user_id 1
        for ($i = 1; $i <= 5; $i++) {
            $amount_invest = 100.00000000 + $i;
            $ending_amount = $amount_invest * 3;
            // Insert trade first to get its ID
            $tradeId = DB::table('ai_trades')->insertGetId([
                'user_id' => 1,
                'amount_invest' => $amount_invest,
                'trade_status' => rand(0, 2),
                'current_amount' => 0, // will update after histories
                'ending_amount' => $ending_amount,
                'created_at' => Carbon::now()->subDays(5 - $i),
                'updated_at' => Carbon::now()->subDays(5 - $i),
            ]);

            // Seed 2 histories for this trade
            $totalPnl = 0;
            for ($j = 1; $j <= 2; $j++) {
                $pnl = rand(-5, 10) + (rand(0, 99) / 100);
                $totalPnl += $pnl;
                DB::table('ai_trade_histories')->insert([
                    'ai_trade_id' => $tradeId,
                    'amount' => 10.00000000 + $j,
                    'pnl' => $pnl,
                    'description' => 'Trade history ' . $j . ' for trade ' . $tradeId,
                    'created_at' => Carbon::now()->subDays($j),
                    'updated_at' => Carbon::now()->subDays($j),
                ]);
            }
            // current_amount is sum of pnl, but not over ending_amount
            $current_amount = $totalPnl;
            if ($current_amount > $ending_amount) {
                $current_amount = $ending_amount;
            }
            // Update ai_trades with correct current_amount
            DB::table('ai_trades')->where('id', $tradeId)->update([
                'current_amount' => $current_amount
            ]);
        }
    }
}
