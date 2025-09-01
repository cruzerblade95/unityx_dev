<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WithdrawalSettings;

class WithdrawalSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $withdrawalTypes = ['crypto', 'etc_black', 'gold'];

        foreach ($withdrawalTypes as $type) {
            WithdrawalSettings::updateOrCreate(
                [
                    'user_id' => null,
                    'withdrawal_type' => $type,
                    'is_global_default' => true
                ],
                [
                    'min_amount' => 10.00,
                    'max_amount' => 10000.00,
                    'processing_fee_percent' => 4.75,
                    'processing_fee_fixed' => 0.00,
                    'cooldown_hours' => 30,
                    'is_enabled' => true,
                    'additional_settings' => []
                ]
            );
        }

        $this->command->info('Global withdrawal default settings created successfully!');
    }
}
