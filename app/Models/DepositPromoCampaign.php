<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DepositPromoCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_datetime',
        'end_datetime',
        'percentage_bonus',
        'percentage_upline_bonus',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'percentage_bonus' => 'decimal:2',
        'percentage_upline_bonus' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Get the admin who created this campaign
     */
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get the bonus history for this campaign
     */
    public function bonusHistory()
    {
        return $this->hasMany(DepositPromoBonusHistory::class, 'campaign_id');
    }

    /**
     * Check if campaign is currently active
     */
    public function isCurrentlyActive()
    {
        return $this->is_active &&
               Carbon::now()->between($this->start_datetime, $this->end_datetime);
    }

    /**
     * Get active campaign for current time
     */
    public static function getActiveCampaign()
    {
        return static::where('is_active', true)
            ->where('start_datetime', '<=', Carbon::now())
            ->where('end_datetime', '>=', Carbon::now())
            ->first();
    }

    /**
     * Scope for active campaigns
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for current campaigns
     */
    public function scopeCurrent($query)
    {
        return $query->where('start_datetime', '<=', Carbon::now())
            ->where('end_datetime', '>=', Carbon::now());
    }
}
