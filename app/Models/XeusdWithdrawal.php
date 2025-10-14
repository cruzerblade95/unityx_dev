<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XeusdWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'unique_id',
        'amount_usd',
        'amount_xeusd',
        'source_wallet',
        'recipient_address',
        'status',
        'transaction_hash',
        'admin_notes',
        'processed_by',
        'processed_at',
        'blockchain_sent_at',
        'blockchain_confirmed_at',
        'blockchain_data'
    ];

    protected $casts = [
        'amount_usd' => 'decimal:2',
        'amount_xeusd' => 'decimal:8',
        'processed_at' => 'datetime',
        'blockchain_sent_at' => 'datetime',
        'blockchain_confirmed_at' => 'datetime',
        'blockchain_data' => 'array'
    ];

    /**
     * Generate a unique withdrawal ID
     */
    public static function generateUniqueId()
    {
        do {
            $uniqueId = 'XEUSD' . strtoupper(substr(uniqid(), -10));
        } while (self::where('unique_id', $uniqueId)->exists());

        return $uniqueId;
    }

    /**
     * Get the user that owns the withdrawal
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who processed the withdrawal
     */
    public function processedBy()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'processed_by');
    }

    /**
     * Scope for pending withdrawals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed withdrawals
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed withdrawals
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check if withdrawal can be processed
     */
    public function canBeProcessed()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if withdrawal is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'pending' => 'bg-warning',
            'processing' => 'bg-info',
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
            'rejected' => 'bg-secondary',
            default => 'bg-secondary'
        };
    }
}
