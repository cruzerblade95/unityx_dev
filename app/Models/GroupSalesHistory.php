<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupSalesHistory extends Model
{
    use HasFactory;

    protected $table = 'group_sales_rewards'; // Using existing table for claims

    protected $fillable = [
        'user_id',
        'reward_level',
        'sales_threshold',
        'reward_amount',
        'tax_percentage',
        'total_sales_at_claim',
        'claimed_month',
        'claimed_at',
    ];

    protected $casts = [
        'sales_threshold' => 'decimal:2',
        'reward_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'total_sales_at_claim' => 'decimal:2',
        'claimed_at' => 'datetime',
    ];

    /**
     * Get the user who claimed this reward
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reward configuration
     */
    public function groupSalesReward()
    {
        return $this->belongsTo(GroupSalesReward::class, 'reward_level', 'reward_level');
    }

    /**
     * Get formatted reward amount
     */
    public function getFormattedRewardAmountAttribute()
    {
        return '$' . number_format($this->reward_amount, 2);
    }

    /**
     * Get formatted sales threshold
     */
    public function getFormattedSalesThresholdAttribute()
    {
        return '$' . number_format($this->sales_threshold, 2);
    }
}
