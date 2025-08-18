<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycVerification extends Model
{
    protected $table = 'kyc_verification';
    
    protected $fillable = [
        'user_id',
        'kyc_doc1',
        'kyc_doc2', 
        'kyc_doc3',
        'kyc_status',
        'kyc_reason'
    ];

    protected $casts = [
        'kyc_status' => 'integer',
    ];

    // Status constants
    const STATUS_NOT_SUBMITTED = 0;
    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->kyc_status) {
            self::STATUS_NOT_SUBMITTED => 'Not Submitted',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            default => 'Unknown'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->kyc_status) {
            self::STATUS_NOT_SUBMITTED => 'danger',
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            default => 'secondary'
        };
    }
}
