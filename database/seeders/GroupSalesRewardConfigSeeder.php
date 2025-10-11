<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GroupSalesReward;

class GroupSalesRewardConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rewards = [
            [
                'reward_title' => 'Level 1 Reward',
                'reward_level' => 1,
                'sales_required' => 5000.00,
                'base_percentage' => 15.00,
                'tax_percentage' => 3.00,
                'is_active' => true,
            ],
            [
                'reward_title' => 'Level 2 Reward',
                'reward_level' => 2,
                'sales_required' => 15000.00,
                'base_percentage' => 15.00,
                'tax_percentage' => 4.00,
                'is_active' => true,
            ],
            [
                'reward_title' => 'Level 3 Reward',
                'reward_level' => 3,
                'sales_required' => 50000.00,
                'base_percentage' => 15.00,
                'tax_percentage' => 5.00,
                'is_active' => true,
            ],
            [
                'reward_title' => 'Level 4 Reward',
                'reward_level' => 4,
                'sales_required' => 100000.00,
                'base_percentage' => 15.00,
                'tax_percentage' => 6.00,
                'is_active' => true,
            ],
            [
                'reward_title' => 'Level 5 Reward',
                'reward_level' => 5,
                'sales_required' => 150000.00,
                'base_percentage' => 15.00,
                'tax_percentage' => 7.00,
                'is_active' => true,
            ],
            [
                'reward_title' => 'Level 6 Reward',
                'reward_level' => 6,
                'sales_required' => 300000.00,
                'base_percentage' => 15.00,
                'tax_percentage' => 8.00,
                'is_active' => true,
            ],
            [
                'reward_title' => 'Level 7 Reward',
                'reward_level' => 7,
                'sales_required' => 500000.00,
                'base_percentage' => 15.00,
                'tax_percentage' => 9.00,
                'is_active' => true,
            ],
            [
                'reward_title' => 'Level 8 Reward',
                'reward_level' => 8,
                'sales_required' => 1000000.00,
                'base_percentage' => 15.00,
                'tax_percentage' => 10.00,
                'is_active' => true,
            ],
        ];

        foreach ($rewards as $reward) {
            GroupSalesReward::updateOrCreate(
                ['reward_level' => $reward['reward_level']],
                $reward
            );
        }
    }
}
