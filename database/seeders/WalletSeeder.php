<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Wallet;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Create wallet for each user if they don't have one
            Wallet::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'deposit' => fake()->randomFloat(2, 100, 10000),
                    'profit' => fake()->randomFloat(2, 0, 5000),
                    'bonus' => fake()->randomFloat(2, 0, 1000),
                    'cash' => fake()->randomFloat(2, 0, 3000)
                ]
            );
        }
    }
}
