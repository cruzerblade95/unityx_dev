<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReinvestmentSettings;

class ReinvestmentSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create global default settings for profit wallet
        ReinvestmentSettings::updateOrCreate(
            [
                'user_id' => null,
                'wallet_type' => 'profit',
                'is_global_default' => true
            ],
            [
                'min_amount' => 10.00,
                'max_amount' => 10000.00,
                'tax_percent' => 0.00, // Default 0% tax as requested
                'is_enabled' => true,
                'additional_settings' => null
            ]
        );

        // Create global default settings for bonus wallet
        ReinvestmentSettings::updateOrCreate(
            [
                'user_id' => null,
                'wallet_type' => 'bonus',
                'is_global_default' => true
            ],
            [
                'min_amount' => 5.00,
                'max_amount' => 5000.00,
                'tax_percent' => 0.00, // Default 0% tax as requested
                'is_enabled' => true,
                'additional_settings' => null
            ]
        );
    }
}
