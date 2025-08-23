<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketplaceListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_id',
        'seller_id',
        'amount',
        'price_per_usdt',
        'total_price',
        'status',
        'description',
        'expires_at',
        'buyer_id',
        'sold_at',
        'verification_started_at',
        'verification_expires_at',
        'seller_approval_reason',
        'seller_approved',
        'seller_approved_at',
        'admin_rejection_reason',
        'admin_rejected_at',
        'admin_rejected_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'price_per_usdt' => 'decimal:4',
        'total_price' => 'decimal:2',
        'expires_at' => 'datetime',
        'sold_at' => 'datetime',
        'verification_started_at' => 'datetime',
        'verification_expires_at' => 'datetime',
        'seller_approved_at' => 'datetime',
        'seller_approved' => 'boolean',
        'admin_rejected_at' => 'datetime',
    ];

    /**
     * Generate a unique listing ID
     */
    public static function generateUniqueId()
    {
        do {
            $uniqueId = 'MP' . strtoupper(substr(uniqid(), -8));
        } while (self::where('unique_id', $uniqueId)->exists());

        return $uniqueId;
    }

    /**
     * Get the seller user
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the buyer user
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the admin who rejected the transaction
     */
    public function rejectedBy()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'admin_rejected_by');
    }

    /**
     * Scope for active listings
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for listings by seller
     */
    public function scopeBySeller($query, $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    /**
     * Check if listing is purchasable
     */
    public function isPurchasable()
    {
        return $this->status === 'active' &&
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Scope for pending verification listings
     */
    public function scopePendingVerification($query)
    {
        return $query->where('status', 'pending_verification');
    }

    /**
     * Scope for buyer's pending transactions
     */
    public function scopeByBuyer($query, $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    /**
     * Scope for sold listings
     */
    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    /**
     * Scope for cancelled listings
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for rejected listings
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for expired listings
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Check if verification has expired
     */
    public function isVerificationExpired()
    {
        return $this->verification_expires_at &&
               $this->verification_expires_at->isPast() &&
               $this->status === 'pending_verification';
    }

    /**
     * Start verification process
     */
    public function startVerification($buyerId)
    {
        $this->update([
            'status' => 'pending_verification',
            'buyer_id' => $buyerId,
            'verification_started_at' => now(),
            'verification_expires_at' => now()->addMinutes(45),
        ]);
    }

    /**
     * Cancel verification and return to marketplace
     */
    public function cancelVerification()
    {
        $this->update([
            'status' => 'active',
            'buyer_id' => null,
            'verification_started_at' => null,
            'verification_expires_at' => null,
            'seller_approved' => false,
            'seller_approval_reason' => null,
        ]);
    }

    /**
     * Complete the transaction
     */
    public function completeTransaction()
    {
        $this->update([
            'status' => 'sold',
            'sold_at' => now(),
        ]);
    }

    /**
     * Get remaining verification time in minutes
     */
    public function getRemainingVerificationTimeAttribute()
    {
        if (!$this->verification_expires_at) {
            return 0;
        }

        return max(0, now()->diffInMinutes($this->verification_expires_at, false));
    }
}
