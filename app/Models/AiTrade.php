<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiTrade extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'amount_invest', 'pnl', 'trade_status', 'current_amount', 'ending_amount'
    ];

    public function histories()
    {
        return $this->hasMany(AiTradeHistory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
