<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupSalesRewardClaim extends Model
{
    protected $table = 'group_sales_rewards';

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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted reward amount
     */
    public function getFormattedRewardAmountAttribute(): string
    {
        return '$' . number_format($this->reward_amount, 2);
    }

    /**
     * Get the formatted sales threshold
     */
    public function getFormattedSalesThresholdAttribute(): string
    {
        return '$' . number_format($this->sales_threshold, 2);
    }

    /**
     * Get the formatted total sales at claim
     */
    public function getFormattedTotalSalesAtClaimAttribute(): string
    {
        return '$' . number_format($this->total_sales_at_claim, 2);
    }
}
