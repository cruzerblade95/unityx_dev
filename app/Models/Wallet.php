<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'deposit',
        'profit',
        'bonus',
        'cash',
        'floating'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deposit' => 'decimal:2',
        'profit' => 'decimal:2',
        'bonus' => 'decimal:2',
        'cash' => 'decimal:2',
        'floating' => 'decimal:2',
    ];

    /**
     * Get the user that owns the wallet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transaction history for this wallet.
     */
    public function transactionHistories()
    {
        return $this->hasMany(TransactionHistory::class);
    }

    /**
     * Get the total balance of the wallet.
     *
     * @return float
     */
    public function getTotalBalanceAttribute()
    {
        return $this->deposit + $this->profit + $this->bonus + $this->cash;
    }

    /**
     * Get the available balance (profit minus floating).
     *
     * @return float
     */
    public function getAvailableBalanceAttribute()
    {
        return $this->profit - $this->floating;
    }

    /**
     * Add amount to a specific type of balance.
     *
     * @param string $type
     * @param float $amount
     * @return bool
     */
    public function addBalance(string $type, float $amount)
    {
        if (!in_array($type, ['deposit', 'profit', 'bonus', 'cash', 'floating'])) {
            return false;
        }

        $this->$type += $amount;
        return $this->save();
    }

    /**
     * Subtract amount from a specific type of balance.
     *
     * @param string $type
     * @param float $amount
     * @return bool
     */
    public function subtractBalance(string $type, float $amount)
    {
        if (!in_array($type, ['deposit', 'profit', 'bonus', 'cash', 'floating'])) {
            return false;
        }

        if ($this->$type < $amount) {
            return false;
        }

        $this->$type -= $amount;
        return $this->save();
    }

    /**
     * Move amount from profit to floating (for pending withdrawals).
     *
     * @param float $amount
     * @return bool
     */
    public function holdAmount(float $amount)
    {
        if ($this->profit < $amount) {
            return false;
        }

        $this->profit -= $amount;
        $this->floating += $amount;
        return $this->save();
    }

    /**
     * Move amount from floating back to profit (for rejected withdrawals).
     *
     * @param float $amount
     * @return bool
     */
    public function releaseAmount(float $amount)
    {
        if ($this->floating < $amount) {
            return false;
        }

        $this->floating -= $amount;
        $this->profit += $amount;
        return $this->save();
    }

    /**
     * Remove amount from floating (for approved withdrawals).
     *
     * @param float $amount
     * @return bool
     */
    public function deductFloating(float $amount)
    {
        if ($this->floating < $amount) {
            return false;
        }

        $this->floating -= $amount;
        return $this->save();
    }
}
