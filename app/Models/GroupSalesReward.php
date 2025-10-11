<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupSalesReward extends Model
{
    use HasFactory;

    protected $table = 'group_sales_reward_configs';

    protected $fillable = [
        'reward_title',
        'reward_level',
        'sales_required',
        'base_percentage',
        'tax_percentage',
        'is_active',
    ];

    protected $casts = [
        'sales_required' => 'decimal:2',
        'base_percentage' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the reward amount calculated
     */
    public function getRewardAmountAttribute()
    {
        return $this->sales_required * ($this->base_percentage / 100) * ($this->tax_percentage / 100);
    }

    /**
     * Get claims for this reward configuration
     */
    public function claims()
    {
        return $this->hasMany(GroupSalesRewardClaim::class, 'reward_level', 'reward_level');
    }

    /**
     * Scope for active rewards
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted sales required
     */
    public function getFormattedSalesRequiredAttribute()
    {
        return '$' . number_format($this->sales_required, 2);
    }

    /**
     * Get formatted reward amount
     */
    public function getFormattedRewardAmountAttribute()
    {
        return '$' . number_format($this->reward_amount, 2);
    }
}
