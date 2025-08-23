<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'amount',
        'tax_percent',
        'tax_amount',
        'net_amount',
        'status',
        'reference',
        'note',
        'payment_method',
        'payment_id',
        'payment_url',
        'crypto_currency',
        'crypto_amount',
        'payment_address',
        'unique_address',
        'address_memo',
        'address_expires_at',
        'rejection_reason',
        'rejected_at',
        'rejected_by'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
