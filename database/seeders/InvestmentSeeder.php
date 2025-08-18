<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Investment;
use App\Models\User;

class InvestmentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if ($user) {
            for ($i = 1; $i <= 5; $i++) {
                $transaction_id = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
                $status = ['valid', 'error'][array_rand(['valid', 'error'])];
                $pnl = rand(-1000, 1000) / 10;
                Investment::create([
                    'user_id' => $user->id,
                    'transaction_id' => $transaction_id,
                    'status' => $status,
                    'pnl' => $pnl,
                ]);
            }
        }
    }
}
