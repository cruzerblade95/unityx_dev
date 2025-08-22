<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Notification types
    const TYPE_KYC_APPROVED = 'kyc_approved';
    const TYPE_KYC_REJECTED = 'kyc_rejected';
    const TYPE_WITHDRAWAL_APPROVED = 'withdrawal_approved';
    const TYPE_WITHDRAWAL_REJECTED = 'withdrawal_rejected';
    const TYPE_DEPOSIT_SUCCESS = 'deposit_success';
    const TYPE_DEPOSIT_REJECTED = 'deposit_rejected';
    const TYPE_MARKETPLACE_SELLER_PENDING = 'marketplace_seller_pending';
    const TYPE_MARKETPLACE_SELLER_APPROVED = 'marketplace_seller_approved';
    const TYPE_MARKETPLACE_SELLER_REJECTED = 'marketplace_seller_rejected';
    const TYPE_CASH_TOPUP = 'cash_topup';

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if notification is read
     */
    public function isRead()
    {
        return !is_null($this->read_at);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Get time ago string
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get notification icon based on type
     */
    public function getIconAttribute()
    {
        return match($this->type) {
            self::TYPE_KYC_APPROVED => 'tabler-check-circle',
            self::TYPE_KYC_REJECTED => 'tabler-x-circle',
            self::TYPE_WITHDRAWAL_APPROVED => 'tabler-check-circle',
            self::TYPE_WITHDRAWAL_REJECTED => 'tabler-x-circle',
            self::TYPE_DEPOSIT_SUCCESS => 'tabler-coin',
            self::TYPE_MARKETPLACE_SELLER_PENDING => 'tabler-clock',
            self::TYPE_MARKETPLACE_SELLER_APPROVED => 'tabler-check-circle',
            self::TYPE_MARKETPLACE_SELLER_REJECTED => 'tabler-x-circle',
            self::TYPE_CASH_TOPUP => 'tabler-wallet',
            default => 'tabler-bell'
        };
    }

    /**
     * Get notification color based on type
     */
    public function getColorAttribute()
    {
        return match($this->type) {
            self::TYPE_KYC_APPROVED,
            self::TYPE_WITHDRAWAL_APPROVED,
            self::TYPE_DEPOSIT_SUCCESS,
            self::TYPE_MARKETPLACE_SELLER_APPROVED,
            self::TYPE_CASH_TOPUP => 'success',

            self::TYPE_KYC_REJECTED,
            self::TYPE_WITHDRAWAL_REJECTED,
            self::TYPE_MARKETPLACE_SELLER_REJECTED => 'danger',

            self::TYPE_MARKETPLACE_SELLER_PENDING => 'warning',

            default => 'info'
        };
    }

    /**
     * Create a notification
     */
    public static function createNotification($userId, $type, $title, $message, $data = null)
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data
        ]);
    }
}
