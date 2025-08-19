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
            DB::table('ai_trades')->insert([
                'user_id' => 1,
                'amount_invest' => 100.00000000 + $i,
                'pnl' => rand(-10, 20) + (rand(0, 99) / 100),
                'trade_status' => rand(0, 2),
                'current_amount' => 100.00000000 + $i + rand(0, 10),
                'ending_amount' => 100.00000000 + $i + rand(0, 20),
                'created_at' => Carbon::now()->subDays(5 - $i),
                'updated_at' => Carbon::now()->subDays(5 - $i),
            ]);
        }
    }
}
