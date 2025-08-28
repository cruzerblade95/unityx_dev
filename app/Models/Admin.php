<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The guard that should be used for authentication.
     *
     * @var string
     */
    protected $guard = 'admin';

    protected $dates = [
        'last_login_at',
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'username',
        'email',
        'password',
        'last_login_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the transaction histories performed by this admin.
     */
    public function transactionHistories()
    {
        return $this->hasMany(TransactionHistory::class, 'admin_id');
    }
}
