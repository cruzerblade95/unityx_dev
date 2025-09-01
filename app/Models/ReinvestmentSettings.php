<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReinvestmentSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_type',
        'min_amount',
        'max_amount',
        'tax_percent',
        'is_enabled',
        'is_global_default',
        'additional_settings'
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'is_enabled' => 'boolean',
        'is_global_default' => 'boolean',
        'additional_settings' => 'array'
    ];

    /**
     * Get the user that owns the reinvestment settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get reinvestment settings for a specific user and wallet type
     */
    public static function getUserSettings($userId, $walletType)
    {
        // First try to get user-specific settings
        $userSettings = self::where('user_id', $userId)
                           ->where('wallet_type', $walletType)
                           ->first();

        if ($userSettings) {
            return $userSettings;
        }

        // Fallback to global default settings
        return self::getGlobalDefaults($walletType);
    }

    /**
     * Get global default settings for a wallet type
     */
    public static function getGlobalDefaults($walletType)
    {
        return self::where('user_id', null)
                   ->where('wallet_type', $walletType)
                   ->where('is_global_default', true)
                   ->first();
    }

    /**
     * Create or update user-specific settings
     */
    public static function setUserSettings($userId, $walletType, $settings)
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'wallet_type' => $walletType
            ],
            array_merge($settings, ['is_global_default' => false])
        );
    }

    /**
     * Create or update global default settings
     */
    public static function setGlobalDefaults($walletType, $settings)
    {
        return self::updateOrCreate(
            [
                'user_id' => null,
                'wallet_type' => $walletType,
                'is_global_default' => true
            ],
            $settings
        );
    }

    /**
     * Check if reinvestment is enabled for user and wallet type
     */
    public static function isReinvestmentEnabled($userId, $walletType)
    {
        $settings = self::getUserSettings($userId, $walletType);
        return $settings ? $settings->is_enabled : true; // Default enabled if no settings
    }

    /**
     * Get tax percentage for user and wallet type
     */
    public static function getTaxPercent($userId, $walletType)
    {
        $settings = self::getUserSettings($userId, $walletType);
        return $settings ? $settings->tax_percent : 0.00; // Default 0% tax
    }

    /**
     * Validate reinvestment amount against settings
     */
    public static function validateReinvestmentAmount($userId, $walletType, $amount)
    {
        $settings = self::getUserSettings($userId, $walletType);

        if (!$settings) {
            return true; // No restrictions if no settings
        }

        return $amount >= $settings->min_amount && $amount <= $settings->max_amount;
    }

    /**
     * Calculate tax amount for reinvestment
     */
    public static function calculateTax($userId, $walletType, $amount)
    {
        $taxPercent = self::getTaxPercent($userId, $walletType);
        return ($amount * $taxPercent) / 100;
    }

    /**
     * Get net amount after tax
     */
    public static function getNetAmount($userId, $walletType, $amount)
    {
        $tax = self::calculateTax($userId, $walletType, $amount);
        return $amount - $tax;
    }
}
