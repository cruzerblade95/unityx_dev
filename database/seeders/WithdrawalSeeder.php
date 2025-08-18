<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Withdrawal;
use App\Models\User;

class WithdrawalSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if ($user) {
            for ($i = 1; $i <= 5; $i++) {
                $amount = rand(100, 1000);
                $tax_percent = 2.5;
                $tax_amount = $amount * ($tax_percent / 100);
                $net_amount = $amount - $tax_amount;
                Withdrawal::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'tax_percent' => $tax_percent,
                    'tax_amount' => $tax_amount,
                    'net_amount' => $net_amount,
                    'status' => 'pending',
                    'reference' => 'WDR' . rand(10000, 99999),
                    'note' => 'Seeded withdrawal',
                ]);
            }
        }
    }
}
