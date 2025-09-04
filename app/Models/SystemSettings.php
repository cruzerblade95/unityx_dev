<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description'
    ];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = Cache::remember("system_setting_{$key}", 3600, function () use ($key) {
            return self::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        // Cast value based on type
        switch ($setting->type) {
            case 'boolean':
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $setting->value;
            case 'float':
                return (float) $setting->value;
            case 'json':
                return json_decode($setting->value, true);
            default:
                return $setting->value;
        }
    }

    /**
     * Set a setting value by key
     */
    public static function set($key, $value, $type = 'string', $description = null)
    {
        // Convert value to string for storage
        if ($type === 'boolean') {
            $value = $value ? 'true' : 'false';
        } elseif ($type === 'json') {
            $value = json_encode($value);
        } else {
            $value = (string) $value;
        }

        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description
            ]
        );

        // Clear cache
        Cache::forget("system_setting_{$key}");

        return $setting;
    }

    /**
     * Check if withdrawals are in maintenance mode
     */
    public static function isWithdrawalMaintenanceMode()
    {
        return self::get('withdrawal_maintenance_mode', false);
    }

    /**
     * Get withdrawal maintenance message
     */
    public static function getWithdrawalMaintenanceMessage()
    {
        return self::get('withdrawal_maintenance_message', 'Withdrawal system is currently under maintenance. Please try again later.');
    }

    /**
     * Toggle withdrawal maintenance mode
     */
    public static function toggleWithdrawalMaintenance($enabled = null)
    {
        if ($enabled === null) {
            $current = self::get('withdrawal_maintenance_mode', false);
            $enabled = !$current;
        }

        return self::set('withdrawal_maintenance_mode', $enabled, 'boolean', 'Global maintenance mode for all withdrawal functionality');
    }
}
