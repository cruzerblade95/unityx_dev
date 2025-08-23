<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Wallet;
use App\Models\KycVerification;
use App\Models\Notification;

class User extends Authenticatable
{
    use HasApiTokens;

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
        'telegram_id',
        'profile_photo_path',
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
     * Check if user's KYC is approved.
     *
     * @return bool
     */
    public function isKycApproved()
    {
        return $this->kycVerification && $this->kycVerification->kyc_status === KycVerification::STATUS_APPROVED;
    }

    /**
     * Get the user's withdrawable balance (profit balance).
     *
     * @return float
     */
    public function getWithdrawableBalance()
    {
        return $this->wallet ? $this->wallet->profit : 0;
    }
}
