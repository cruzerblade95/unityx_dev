<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AiTradeHistorySeeder extends Seeder
{
    public function run(): void
    {
        // Example: Seed 2 histories for each of the first 5 ai_trades
        $aiTradeIds = DB::table('ai_trades')->pluck('id')->take(5);
        foreach ($aiTradeIds as $tradeId) {
            for ($j = 1; $j <= 2; $j++) {
                DB::table('ai_trade_histories')->insert([
                    'ai_trade_id' => $tradeId,
                    'amount' => 10.00000000 + $j,
                    'pnl' => rand(-5, 10) + (rand(0, 99) / 100),
                    'description' => 'Trade history ' . $j . ' for trade ' . $tradeId,
                    'created_at' => Carbon::now()->subDays($j),
                    'updated_at' => Carbon::now()->subDays($j),
                ]);
            }
        }
    }
}
