<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'telegram_username',
        'chat_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the telegram verification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the token has expired.
     */
    public function isExpired()
    {
        return $this->expires_at && now()->isAfter($this->expires_at);
    }
}
