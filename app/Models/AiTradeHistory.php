<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiTradeHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'ai_trade_id', 'amount', 'pnl', 'description'
    ];

    public function aiTrade()
    {
        return $this->belongsTo(AiTrade::class);
    }
}
