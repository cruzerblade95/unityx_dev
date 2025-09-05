<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WithdrawalOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'otp_code',
        'withdrawal_data',
        'is_verified',
        'expires_at'
    ];

    protected $casts = [
        'withdrawal_data' => 'array',
        'is_verified' => 'boolean',
        'expires_at' => 'datetime'
    ];

    /**
     * Generate a new OTP for withdrawal
     */
    public static function generateOtp($userId, $withdrawalData)
    {
        // Delete any existing unverified OTPs for this user
        static::where('user_id', $userId)
            ->where('is_verified', false)
            ->delete();

        $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        return static::create([
            'user_id' => $userId,
            'otp_code' => $otpCode,
            'withdrawal_data' => $withdrawalData,
            'expires_at' => now()->addMinutes(5) // OTP expires in 5 minutes
        ]);
    }

    /**
     * Verify OTP and return withdrawal data if valid
     */
    public static function verifyOtp($userId, $otpCode)
    {
        $otp = static::where('user_id', $userId)
            ->where('otp_code', $otpCode)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($otp) {
            $otp->update(['is_verified' => true]);
            return [
                'valid' => true,
                'data' => $otp->withdrawal_data,
                'message' => 'OTP verified successfully'
            ];
        }

        // Check if OTP exists but is expired or already used
        $existingOtp = static::where('user_id', $userId)
            ->where('otp_code', $otpCode)
            ->first();

        if ($existingOtp) {
            if ($existingOtp->is_verified) {
                return [
                    'valid' => false,
                    'message' => 'OTP has already been used'
                ];
            } elseif ($existingOtp->expires_at <= now()) {
                return [
                    'valid' => false,
                    'message' => 'OTP has expired'
                ];
            }
        }

        return [
            'valid' => false,
            'message' => 'Invalid OTP code'
        ];
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired()
    {
        return $this->expires_at < now();
    }

    /**
     * Get the user that owns the OTP
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
