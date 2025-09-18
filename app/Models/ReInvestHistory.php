<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReInvestHistory extends Model
{
    use HasFactory;

    protected $table = 'reinvest_histories';

    protected $fillable = [
        'user_id',
        'ai_trade_id',
        'original_amount',
        'tax_amount',
        'net_amount',
        'wallet_type',
        'status',
        'expected_return',
        'notes',
        'processed_by',
        'processed_at'
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'expected_return' => 'decimal:2',
        'processed_at' => 'datetime'
    ];

    /**
     * Get the user that owns the reinvestment history.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the AI trade associated with this reinvestment.
     */
    public function aiTrade(): BeLongsTo
    {
        return $this->belongsTo(AiTrade::class);
    }

    /**
     * Get the admin who processed this reinvestment (if manually handled).
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope to get reinvestments by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get reinvestments by wallet type
     */
    public function scopeByWalletType($query, $walletType)
    {
        return $query->where('wallet_type', $walletType);
    }

    /**
     * Scope to get reinvestments by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get the profit/loss for this reinvestment
     */
    public function getProfitLossAttribute()
    {
        if (!$this->aiTrade) {
            return 0;
        }

        $currentValue = $this->aiTrade->current_amount ?? $this->net_amount;
        return $currentValue - $this->net_amount;
    }

    /**
     * Get the current progress percentage
     */
    public function getProgressPercentageAttribute()
    {
        if (!$this->aiTrade || !$this->expected_return) {
            return 0;
        }

        $currentValue = $this->aiTrade->current_amount ?? $this->net_amount;
        $progress = (($currentValue - $this->net_amount) / ($this->expected_return - $this->net_amount)) * 100;

        return min(100, max(0, $progress));
    }

    /**
     * Get the status badge color
     */
    public function getStatusBadgeColorAttribute()
    {
        switch ($this->status) {
            case 'completed':
                return 'success';
            case 'pending':
                return 'warning';
            case 'failed':
                return 'danger';
            case 'cancelled':
                return 'secondary';
            default:
                return 'primary';
        }
    }

    /**
     * Get the wallet type badge color
     */
    public function getWalletTypeBadgeColorAttribute()
    {
        switch ($this->wallet_type) {
            case 'profit':
                return 'warning';
            case 'bonus':
                return 'info';
            default:
                return 'primary';
        }
    }
}
