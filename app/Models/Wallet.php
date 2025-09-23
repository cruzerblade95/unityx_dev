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
        'floating',
        'crypto_floating',
        'bank_floating',
        'gold_floating',
        'temporary_profit'
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
        'crypto_floating' => 'decimal:2',
        'bank_floating' => 'decimal:2',
        'gold_floating' => 'decimal:2',
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
     * Get the available balance (profit + bonus minus floating).
     *
     * @return float
     */
    public function getAvailableBalanceAttribute()
    {
        return $this->profit + $this->bonus - $this->floating;
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
     * Move amount from profit and bonus to floating (for pending withdrawals).
     * Deducts from profit first, then from bonus if needed.
     *
     * @param float $amount
     * @return bool
     */
    public function holdAmount(float $amount)
    {
        $totalAvailable = $this->profit + $this->bonus;

        if ($totalAvailable < $amount) {
            return false;
        }

        $remainingAmount = $amount;

        // First, deduct from profit
        if ($this->profit > 0) {
            $fromProfit = min($this->profit, $remainingAmount);
            $this->profit -= $fromProfit;
            $remainingAmount -= $fromProfit;
        }

        // Then, deduct from bonus if still needed
        if ($remainingAmount > 0 && $this->bonus >= $remainingAmount) {
            $this->bonus -= $remainingAmount;
        }

        $this->floating += $amount;
        return $this->save();
    }

    /**
     * Move amount from floating back to profit and bonus (for rejected withdrawals).
     * Returns to profit first, then to bonus if needed.
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
        $this->profit += $amount; // For simplicity, return all to profit
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

    /**
     * Hold amount from specific wallet type.
     *
     * @param float $amount
     * @param string $walletType ('profit' or 'bonus')
     * @return bool
     */
    public function holdAmountFromWallet(float $amount, string $walletType)
    {
        switch ($walletType) {
            case 'profit':
                if ($this->profit < $amount) {
                    return false;
                }
                $this->profit -= $amount;
                break;

            case 'bonus':
                if ($this->bonus < $amount) {
                    return false;
                }
                $this->bonus -= $amount;
                break;

            default:
                return false;
        }

        $this->floating += $amount;
        return $this->save();
    }

    /**
     * Get available balance for specific wallet type.
     *
     * @param string $walletType ('profit' or 'bonus')
     * @return float
     */
    public function getAvailableBalance(string $walletType)
    {
        switch ($walletType) {
            case 'profit':
                return max(0, $this->profit);
            case 'bonus':
                return max(0, $this->bonus);
            default:
                return 0;
        }
    }

    /**
     * Get the appropriate floating field name for withdrawal type.
     *
     * @param string $withdrawalType
     * @return string
     */
    public function getFloatingFieldForWithdrawalType(string $withdrawalType)
    {
        switch ($withdrawalType) {
            case 'crypto':
                return 'crypto_floating';
            case 'etc_black':
                return 'bank_floating';
            case 'gold':
                return 'gold_floating';
            default:
                return 'floating'; // marketplace uses default floating
        }
    }

    /**
     * Hold amount from specific wallet type for specific withdrawal type.
     *
     * @param float $amount
     * @param string $walletType ('profit' or 'bonus')
     * @param string $withdrawalType ('crypto', 'etc_black', 'gold')
     * @return bool
     */
    public function holdAmountFromWalletForWithdrawal(float $amount, string $walletType, string $withdrawalType)
    {
        switch ($walletType) {
            case 'profit':
                if ($this->profit < $amount) {
                    return false;
                }
                $this->profit -= $amount;
                break;

            case 'bonus':
                if ($this->bonus < $amount) {
                    return false;
                }
                $this->bonus -= $amount;
                break;

            default:
                return false;
        }

        $floatingField = $this->getFloatingFieldForWithdrawalType($withdrawalType);
        $this->$floatingField += $amount;
        return $this->save();
    }

    /**
     * Release amount from specific withdrawal type floating back to profit (for rejected withdrawals).
     *
     * @param float $amount
     * @param string $withdrawalType ('crypto', 'etc_black', 'gold')
     * @return bool
     */
    public function releaseAmountForWithdrawal(float $amount, string $withdrawalType)
    {
        $floatingField = $this->getFloatingFieldForWithdrawalType($withdrawalType);

        if ($this->$floatingField < $amount) {
            return false;
        }

        $this->$floatingField -= $amount;
        $this->profit += $amount; // For simplicity, return all to profit
        return $this->save();
    }

    /**
     * Remove amount from specific withdrawal type floating (for approved withdrawals).
     *
     * @param float $amount
     * @param string $withdrawalType ('crypto', 'etc_black', 'gold')
     * @return bool
     */
    public function deductFloatingForWithdrawal(float $amount, string $withdrawalType)
    {
        $floatingField = $this->getFloatingFieldForWithdrawalType($withdrawalType);

        if ($this->$floatingField < $amount) {
            return false;
        }

        $this->$floatingField -= $amount;
        return $this->save();
    }

    /**
     * Get total floating amount for specific withdrawal type.
     *
     * @param string $withdrawalType
     * @return float
     */
    public function getFloatingAmountForWithdrawal(string $withdrawalType)
    {
        $floatingField = $this->getFloatingFieldForWithdrawalType($withdrawalType);
        return $this->$floatingField;
    }

    /**
     * Get withdrawable balance considering floating amounts for specific withdrawal type.
     *
     * @param string $withdrawalType
     * @return float
     */
    public function getAvailableBalanceForWithdrawal(string $withdrawalType)
    {
        $floatingField = $this->getFloatingFieldForWithdrawalType($withdrawalType);
        $totalWithdrawable = $this->profit + $this->bonus - $this->$floatingField;
        return max(0, $totalWithdrawable);
    }
}
