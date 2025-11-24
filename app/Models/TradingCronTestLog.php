<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradingCronTestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ai_trade_id',
        'test_type',
        'status',
        'profit_amount',
        'trade_amount',
        'current_amount',
        'ending_amount',
        'error_message',
        'error_details',
        'data_snapshot',
        'ai_trade_active',
        'trade_status_valid',
        'wallet_exists',
        'failure_reason',
        'test_executed_at',
    ];

    protected $casts = [
        'profit_amount' => 'decimal:8',
        'trade_amount' => 'decimal:8',
        'current_amount' => 'decimal:8',
        'ending_amount' => 'decimal:8',
        'data_snapshot' => 'array',
        'ai_trade_active' => 'boolean',
        'trade_status_valid' => 'boolean',
        'wallet_exists' => 'boolean',
        'test_executed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aiTrade()
    {
        return $this->belongsTo(AiTrade::class);
    }
}
