# Withdrawal System with Floating Amount Management

## Overview

This system implements a secure withdrawal request process with proper fund management using floating amounts to prevent double spending and ensure accurate balance tracking. Users can withdraw from both their profit and bonus wallet balances.

## Key Features

### 1. Fee Calculation

- **Fixed fee**: 4.75% deducted from withdrawal amount
- **Real-time calculation**: Shows net amount as user types
- **Transparent display**: Fee information clearly shown to users

### 2. Multi-Wallet Withdrawal System

The system allows withdrawals from both profit and bonus balances:
- **Profit Balance**: Primary earnings from trading activities
- **Bonus Balance**: Rewards and bonuses earned
- **Deduction Order**: Profit first, then bonus if needed

### 3. Floating Amount System

The system uses a "floating" balance to hold requested withdrawal amounts:

#### Initial State

```
User Wallet:
- Profit: $800
- Bonus: $200
- Floating: $0
- Total Withdrawable: $1000
```

#### After Withdrawal Request ($500)

```
User Wallet:
- Profit: $300 (reduced by $500, taking all $800 profit and $200 from bonus)
- Bonus: $0 (reduced by $200 to complete the $500 withdrawal)
- Floating: $500 (held)
- Total Withdrawable: $0 (profit + bonus - floating)

Withdrawal Record:
- Amount: $500
- Fee: $23.75 (4.75%)
- Net Amount: $476.25
- Status: pending
```

#### After Admin Approval

```
User Wallet:
- Profit: $500 (unchanged)
- Floating: $0 (deducted)
- Withdrawable: $500

Withdrawal Record:
- Status: approved
```

#### After Admin Rejection

```
User Wallet:
- Profit: $1000 (restored)
- Floating: $0 (released)
- Withdrawable: $1000

Withdrawal Record:
- Status: rejected
```

## Database Changes

### New Migration

- `2025_08_23_000001_add_floating_to_wallets_table.php`
- Adds `floating` column to `wallets` table

### Wallet Model Updates

- Added `floating` to fillable and casts
- New methods: `holdAmount()`, `releaseAmount()`, `deductFloating()`
- New attribute: `getAvailableBalanceAttribute()`

## Controller Changes

### User Withdrawal Controller

```php
// Before submission - Check withdrawable balance
$withdrawableBalance = $user->getWithdrawableBalance(); // profit - floating

// After validation - Hold amount as floating
$wallet->holdAmount($request->amount); // profit -= amount, floating += amount
```

### Admin Withdrawal Management

```php
// Approval - Remove from floating
$wallet->deductFloating($withdrawal->amount); // floating -= amount

// Rejection - Return to profit
$wallet->releaseAmount($withdrawal->amount); // floating -= amount, profit += amount
```

## User Experience

### Withdrawal Form

1. User enters withdrawal amount
2. Real-time calculator shows net amount after 4.75% fee
3. System validates against available balance (profit - floating)
4. On submission, amount is immediately held as floating

### Admin Panel

1. Admin sees pending withdrawal requests
2. Approval processes the floating amount (removes from floating)
3. Rejection returns floating amount to profit balance

## Security Features

### Double Spending Prevention

- Withdrawable balance considers floating amounts
- Users cannot request more than available balance
- Floating amounts are locked until admin decision

### Balance Integrity

- All balance changes are atomic
- Proper error handling for failed operations
- Validation at multiple levels

## Error Handling

### User Side

- Insufficient balance validation
- Minimum/maximum amount checks
- KYC verification requirements

### Admin Side

- Floating balance mismatch detection
- Failed operation rollback
- Clear error messages

## Testing

Use the test script `test_withdrawal_floating.php` to verify:

- Fee calculations
- Balance transfers
- Floating amount management
- Admin approval/rejection flows

## Files Modified

1. **Database**

   - `database/migrations/2025_08_23_000001_add_floating_to_wallets_table.php`

2. **Models**

   - `app/Models/Wallet.php` - Added floating field and methods
   - `app/Models/User.php` - Updated getWithdrawableBalance()

3. **Controllers**

   - `app/Http/Controllers/User/WithdrawalController.php` - Updated store method
   - `app/Http/Controllers/Admin/WithdrawalManagementController.php` - Updated approve/reject methods

4. **Views**

   - `resources/views/user/content/withdrawal.blade.php` - Added real-time calculator

5. **Validation**
   - `app/Http/Requests/WithdrawalRequest.php` - Already using getWithdrawableBalance()

## Configuration

The withdrawal fee is configured in the controller:

```php
$systemTaxPercent = 4.75; // 4.75% fee
```

To change the fee, update this value in `WithdrawalController.php` and update the corresponding display text in the view file.
