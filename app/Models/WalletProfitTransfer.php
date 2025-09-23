<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletProfitTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'transfer_type',
        'status',
        'batch_id',
        'processed_by',
        'original_profit_balance',
        'original_deposit_balance',
        'original_temp_balance',
        'notes',
        'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_profit_balance' => 'decimal:2',
        'original_deposit_balance' => 'decimal:2',
        'original_temp_balance' => 'decimal:2',
        'processed_at' => 'datetime'
    ];

    /**
     * Get the user associated with this transfer
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who processed this transfer
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    /**
     * Scope for specific batch
     */
    public function scopeForBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Scope for specific transfer type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('transfer_type', $type);
    }

    /**
     * Scope for completed transfers
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
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
            case 'rolled_back':
                return 'secondary';
            default:
                return 'primary';
        }
    }

    /**
     * Get the transfer type display name
     */
    public function getTransferTypeDisplayAttribute()
    {
        switch ($this->transfer_type) {
            case 'profit_to_temp':
                return 'Profit → Temporary';
            case 'temp_to_deposit':
                return 'Temporary → Deposit';
            case 'profit_to_deposit':
                return 'Profit → Deposit (Direct)';
            case 'temp_to_profit':
                return 'Temporary → Profit';
            case 'rollback':
                return 'Rollback';
            default:
                return ucfirst(str_replace('_', ' ', $this->transfer_type));
        }
    }
}
