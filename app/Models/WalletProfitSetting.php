<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletProfitSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'description',
        'updated_by'
    ];

    /**
     * Get the admin who updated this setting
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    /**
     * Get setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function setValue($key, $value, $adminId = null)
    {
        return self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'updated_by' => $adminId,
                'updated_at' => now()
            ]
        );
    }

    /**
     * Get profit threshold percentage
     */
    public static function getProfitThreshold()
    {
        return (float) self::getValue('profit_threshold_percentage', 85);
    }

    /**
     * Check if auto check is enabled
     */
    public static function isAutoCheckEnabled()
    {
        return self::getValue('auto_check_enabled', 'false') === 'true';
    }
}
