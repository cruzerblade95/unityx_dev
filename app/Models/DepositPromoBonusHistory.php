<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DepositPromoBonusHistory extends Model
{
    use HasFactory;

    protected $table = 'deposit_promo_bonus_history';

    protected $fillable = [
        'user_id',
        'campaign_id',
        'deposit_amount',
        'user_bonus_amount',
        'upline_bonus_amount',
        'deposit_reference',
        'status',
        'expires_at',
        'claimed_at'
    ];

    protected $casts = [
        'deposit_amount' => 'decimal:8',
        'user_bonus_amount' => 'decimal:8',
        'upline_bonus_amount' => 'decimal:8',
        'expires_at' => 'datetime',
        'claimed_at' => 'datetime'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_CLAIMED = 'claimed';
    const STATUS_EXPIRED = 'expired';

    /**
     * Get the user who received this bonus
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the campaign this bonus is from
     */
    public function campaign()
    {
        return $this->belongsTo(DepositPromoCampaign::class, 'campaign_id');
    }

    /**
     * Check if bonus is expired
     */
    public function isExpired()
    {
        return $this->expires_at && Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Check if bonus is claimable
     */
    public function isClaimable()
    {
        return $this->status === self::STATUS_PENDING && !$this->isExpired();
    }

    /**
     * Mark bonus as claimed
     */
    public function markAsClaimed()
    {
        $this->update([
            'status' => self::STATUS_CLAIMED,
            'claimed_at' => Carbon::now()
        ]);
    }

    /**
     * Mark bonus as expired
     */
    public function markAsExpired()
    {
        $this->update([
            'status' => self::STATUS_EXPIRED
        ]);
    }

    /**
     * Scope for claimable bonuses
     */
    public function scopeClaimable($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', Carbon::now());
            });
    }

    /**
     * Scope for expired bonuses
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('expires_at', '<=', Carbon::now());
    }

    /**
     * Scope for user bonuses
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
