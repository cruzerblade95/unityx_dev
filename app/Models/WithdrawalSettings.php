<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'withdrawal_type',
        'min_amount',
        'max_amount',
        'processing_fee_percent',
        'processing_fee_fixed',
        'cooldown_hours',
        'is_enabled',
        'is_global_default',
        'additional_settings'
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'processing_fee_percent' => 'decimal:2',
        'processing_fee_fixed' => 'decimal:2',
        'is_enabled' => 'boolean',
        'is_global_default' => 'boolean',
        'additional_settings' => 'array'
    ];

    /**
     * Get the user that owns the withdrawal settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get withdrawal settings for a specific user and type
     */
    public static function getUserSettings($userId, $withdrawalType)
    {
        // First try to get user-specific settings
        $userSettings = self::where('user_id', $userId)
                           ->where('withdrawal_type', $withdrawalType)
                           ->first();

        if ($userSettings) {
            return $userSettings;
        }

        // Fallback to global default settings
        return self::getGlobalDefaults($withdrawalType);
    }

    /**
     * Get global default settings for a withdrawal type
     */
    public static function getGlobalDefaults($withdrawalType)
    {
        return self::where('user_id', null)
                   ->where('withdrawal_type', $withdrawalType)
                   ->where('is_global_default', true)
                   ->first();
    }

    /**
     * Create or update user-specific settings
     */
    public static function setUserSettings($userId, $withdrawalType, $settings)
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'withdrawal_type' => $withdrawalType
            ],
            array_merge($settings, ['is_global_default' => false])
        );
    }

    /**
     * Create or update global default settings
     */
    public static function setGlobalDefaults($withdrawalType, $settings)
    {
        return self::updateOrCreate(
            [
                'user_id' => null,
                'withdrawal_type' => $withdrawalType,
                'is_global_default' => true
            ],
            $settings
        );
    }

    /**
     * Reset user to global defaults
     */
    public static function resetToDefaults($userId, $withdrawalType = null)
    {
        $query = self::where('user_id', $userId);

        if ($withdrawalType) {
            $query->where('withdrawal_type', $withdrawalType);
        }

        return $query->delete();
    }

    /**
     * Check if withdrawal is enabled for user
     */
    public static function isWithdrawalEnabled($userId, $withdrawalType)
    {
        $settings = self::getUserSettings($userId, $withdrawalType);
        return $settings ? $settings->is_enabled : true;
    }

    /**
     * Get processing fee for user
     */
    public static function getProcessingFee($userId, $withdrawalType, $amount)
    {
        $settings = self::getUserSettings($userId, $withdrawalType);

        if (!$settings) {
            return 0;
        }

        $percentageFee = ($amount * $settings->processing_fee_percent) / 100;
        return $percentageFee + $settings->processing_fee_fixed;
    }

    /**
     * Validate withdrawal amount against limits
     */
    public static function validateWithdrawalAmount($userId, $withdrawalType, $amount)
    {
        $settings = self::getUserSettings($userId, $withdrawalType);

        if (!$settings) {
            return ['valid' => true];
        }

        if ($amount < $settings->min_amount) {
            return [
                'valid' => false,
                'message' => "Minimum withdrawal amount is {$settings->min_amount}"
            ];
        }

        if ($amount > $settings->max_amount) {
            return [
                'valid' => false,
                'message' => "Maximum withdrawal amount is {$settings->max_amount}"
            ];
        }

        return ['valid' => true];
    }

    /**
     * Get withdrawal cooldown hours for user
     */
    public static function getCooldownHours($userId, $withdrawalType)
    {
        $settings = self::getUserSettings($userId, $withdrawalType);
        return $settings ? $settings->cooldown_hours : 30; // default 30 hours
    }
}
