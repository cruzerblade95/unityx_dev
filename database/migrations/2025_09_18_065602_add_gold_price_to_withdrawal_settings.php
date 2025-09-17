<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\WithdrawalSettings;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add Malaysian gold price to the global gold withdrawal settings
        $settings = WithdrawalSettings::where('user_id', null)
                                    ->where('withdrawal_type', 'gold')
                                    ->where('is_global_default', true)
                                    ->first();

        if ($settings) {
            // Update existing settings to include Malaysian gold price
            $additionalSettings = $settings->additional_settings ?? [];

            // Set current Malaysian gold market price (approximate)
            // This should be updated regularly via admin panel or API
            if (!isset($additionalSettings['gold_price_per_oz'])) {
                $additionalSettings['gold_price_per_oz'] = 2650.00; // Current Malaysian market rate
            }

            $settings->update(['additional_settings' => $additionalSettings]);
        } else {
            // Create new global default settings for gold withdrawals with Malaysian gold price
            WithdrawalSettings::create([
                'user_id' => null,
                'withdrawal_type' => 'gold',
                'min_amount' => 300.00,
                'max_amount' => 10000.00,
                'processing_fee_percent' => 7.00,
                'processing_fee_fixed' => 0.00,
                'cooldown_hours' => 30,
                'is_enabled' => true,
                'is_global_default' => true,
                'additional_settings' => [
                    'myr_conversion_rate' => 4.7,
                    'gold_price_per_oz' => 2650.00 // Malaysian market gold price per ounce
                ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove gold price from additional settings
        $settings = WithdrawalSettings::where('user_id', null)
                                    ->where('withdrawal_type', 'gold')
                                    ->where('is_global_default', true)
                                    ->first();

        if ($settings && $settings->additional_settings) {
            $additionalSettings = $settings->additional_settings;
            unset($additionalSettings['gold_price_per_oz']);
            $settings->update(['additional_settings' => $additionalSettings]);
        }
    }
};
