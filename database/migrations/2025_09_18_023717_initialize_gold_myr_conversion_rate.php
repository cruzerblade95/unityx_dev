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
        // Initialize MYR conversion rate setting for gold withdrawals
        $existingSettings = WithdrawalSettings::where('user_id', null)
                                            ->where('withdrawal_type', 'gold')
                                            ->where('is_global_default', true)
                                            ->first();

        if ($existingSettings) {
            // Update existing settings to include MYR conversion rate if not present
            $additionalSettings = $existingSettings->additional_settings ?? [];
            if (!isset($additionalSettings['myr_conversion_rate'])) {
                $additionalSettings['myr_conversion_rate'] = 4.7;
                $existingSettings->update(['additional_settings' => $additionalSettings]);
            }
        } else {
            // Create new global default settings for gold withdrawals
            WithdrawalSettings::create([
                'user_id' => null,
                'withdrawal_type' => 'gold',
                'min_amount' => 10.00,
                'max_amount' => 10000.00,
                'processing_fee_percent' => 7.00,
                'processing_fee_fixed' => 0.00,
                'cooldown_hours' => 30,
                'is_enabled' => true,
                'is_global_default' => true,
                'additional_settings' => [
                    'myr_conversion_rate' => 4.7
                ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove MYR conversion rate from gold withdrawal settings
        $settings = WithdrawalSettings::where('user_id', null)
                                    ->where('withdrawal_type', 'gold')
                                    ->where('is_global_default', true)
                                    ->first();

        if ($settings && $settings->additional_settings) {
            $additionalSettings = $settings->additional_settings;
            unset($additionalSettings['myr_conversion_rate']);
            $settings->update(['additional_settings' => $additionalSettings]);
        }
    }
};
