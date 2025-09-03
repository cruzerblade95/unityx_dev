<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Wallet;
use App\Models\KycVerification;
use App\Models\Notification;
use App\Models\AiTradeActivate;
use App\Models\AiTrade;

class User extends Authenticatable
{
    use HasApiTokens, CanResetPassword;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    /**
     * Boot the model and add event listeners.
     */
    protected static function booted()
    {
        static::created(function ($user) {
            // Create a wallet for the user after registration
            $user->wallet()->create([
                'deposit' => 0,
                'profit' => 0,
                'bonus' => 0,
                'cash' => 0,
            ]);
        });
    }
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'email',
        'role',
        'last_active_at',
        'password',
        'referral_code',
        'upline_id',
        'organization',
        'phone',
        'address',
        'state',
        'zipCode',
        'country',
        'language',
        'timezone',
        'currency',
        'telegram_username',
        'profile_photo_path',
        'national_id',
        'crypto_wallet_address',
        'usdt_bep20_address',
        'usdc_bep20_address',
        'fdusd_bep20_address',
        'etc_black_card_number',
        'cardholder_name',
        'account_number',
        'payment_info_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Get the wallet associated with the user.
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get the transaction history for this user.
     */
    public function transactionHistories()
    {
        return $this->hasMany(TransactionHistory::class);
    }

    /**
     * Get the KYC verification record for the user.
     */
    public function kycVerification()
    {
        return $this->hasOne(KycVerification::class);
    }

    /**
     * Get notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get unread notifications for the user.
     */
    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->whereNull('read_at')->orderBy('created_at', 'desc');
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function downlines()
    {
        return $this->hasMany(User::class, 'upline_id');
    }

    /**
     * Get the upline (parent) user
     */
    public function upline()
    {
        return $this->belongsTo(User::class, 'upline_id');
    }

    /**
     * Check if user's KYC is approved.
     *
     * @return bool
     */
    public function isKycApproved()
    {
        return $this->kycVerification && $this->kycVerification->kyc_status === KycVerification::STATUS_APPROVED;
    }

    /**
     * Get the user's withdrawable balance (profit + bonus balance minus floating).
     *
     * @return float
     */
    public function getWithdrawableBalance()
    {
        if (!$this->wallet) {
            return 0;
        }

        // Calculate total withdrawable from profit and bonus minus floating amount
        $totalWithdrawable = $this->wallet->profit + $this->wallet->bonus - $this->wallet->floating;
        return max(0, $totalWithdrawable);
    }

    /**
     * Get withdrawable balance for specific wallet type.
     *
     * @param string $walletType ('profit' or 'bonus')
     * @return float
     */
    public function getWithdrawableBalanceByType(string $walletType)
    {
        if (!$this->wallet) {
            return 0;
        }

        return $this->wallet->getAvailableBalance($walletType);
    }

    /**
     * Get the user's telegram verification record.
     */
    public function telegramVerification()
    {
        return $this->hasOne(\App\Models\TelegramVerification::class);
    }

    public function aiTradeActivate()
    {
        return $this->hasOne(AiTradeActivate::class);
    }

    public function aiTrades()
    {
        return $this->hasMany(AiTrade::class);
    }
}
