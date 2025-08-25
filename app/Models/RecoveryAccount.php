<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecoveryAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'deposit',
        'profit',
        'referral_username',
        'status',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'deposit' => 'decimal:2',
        'profit' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
