<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'amount', 'tax_percent', 'tax_amount', 'net_amount', 'status', 'reference', 'note'
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }
}
