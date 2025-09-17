<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
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
        'rejection_reason',
        'bank_name',
        'account_number',
        'withdrawal_type',
        'wallet_type',
        'branch_name',
        'branch_selected',
        'branch_manager_name',
        'branch_manager_phone',
        'branch_assigned_at',
        'assigned_by_admin_id',
        'payment_details'
    ];

    protected $casts = [
        'payment_details' => 'array',
        'branch_selected' => 'boolean',
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }
}
