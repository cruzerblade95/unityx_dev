# NOWPayments Integration Documentation

## Overview
This Unity X Laravel application now includes NOWPayments integration for cryptocurrency payments. Users can make deposits using Bitcoin, Ethereum, and other cryptocurrencies.

## Features Added

### 1. NOWPayments Service
- **File**: `app/Services/NOWPaymentsService.php`
- **Features**:
  - Get available cryptocurrencies
  - Create cryptocurrency payments
  - Check payment status
  - Get exchange rates
  - Webhook signature verification

### 2. Enhanced Deposit Controller
- **File**: `app/Http/Controllers/User/DepositController.php`
- **New Methods**:
  - `createCryptoPayment()` - Creates NOWPayments crypto payment
  - `success()` - Payment success page
  - `cancel()` - Payment cancellation handling

### 3. Webhook Handler
- **File**: `app/Http/Controllers/NOWPaymentsWebhookController.php`
- **Features**:
  - Receives NOWPayments webhooks
  - Verifies webhook signatures
  - Updates deposit status
  - Automatically credits user wallets

### 4. Database Changes
- **Migration**: `database/migrations/2025_08_21_032255_add_nowpayments_fields_to_deposits_table.php`
- **New Fields**:
  - `payment_method` - 'manual' or 'crypto'
  - `payment_id` - NOWPayments payment ID
  - `payment_url` - Payment URL for redirect
  - `crypto_currency` - Selected cryptocurrency
  - `crypto_amount` - Amount in cryptocurrency
  - `payment_address` - Cryptocurrency payment address

### 5. Updated Views
- **File**: `resources/views/user/content/deposit.blade.php`
- **Features**:
  - Dual forms (Manual + Crypto)
  - Cryptocurrency selection dropdown
  - Enhanced deposit history with payment methods
  - Status badges for different payment states

- **File**: `resources/views/user/content/deposit-success.blade.php`
- **Features**:
  - Success page after crypto payment
  - User-friendly confirmation message

### 6. New Routes
```php
// User routes
Route::post('deposit/crypto', [DepositController::class, 'createCryptoPayment'])->name('user.deposit.crypto');
Route::get('deposit/success', [DepositController::class, 'success'])->name('user.deposit.success');
Route::get('deposit/cancel', [DepositController::class, 'cancel'])->name('user.deposit.cancel');

// Public webhook route
Route::post('/api/nowpayments/webhook', [NOWPaymentsWebhookController::class, 'handle'])->name('nowpayments.webhook');
```

## Configuration

### Environment Variables
Add these to your `.env` file:

```env
# NOWPayments Configuration
NOWPAYMENTS_API_KEY=your_nowpayments_api_key_here
NOWPAYMENTS_IPN_SECRET=your_nowpayments_ipn_secret_here
NOWPAYMENTS_SANDBOX=true
```

### Getting NOWPayments Credentials
1. Visit [NOWPayments](https://nowpayments.io/)
2. Create an account
3. Get your API key from the dashboard
4. Set up IPN (webhook) URL: `https://yourdomain.com/api/nowpayments/webhook`
5. Get your IPN secret key

## How It Works

### Cryptocurrency Deposit Flow
1. User selects cryptocurrency and amount
2. System creates payment via NOWPayments API
3. User is redirected to NOWPayments payment page
4. User sends cryptocurrency to provided address
5. NOWPayments confirms payment and sends webhook
6. System updates deposit status and credits user wallet

### Payment States
- **pending** - Initial state
- **processing** - Payment being confirmed
- **completed** - Payment confirmed, wallet credited
- **failed** - Payment failed or expired
- **partially_paid** - Partial payment received

## Security Features
- Webhook signature verification
- CSRF protection on forms
- Input validation
- Secure API communication

## Testing
1. Set `NOWPAYMENTS_SANDBOX=true` for testing
2. Use NOWPayments sandbox environment
3. Test with small amounts first
4. Verify webhook URL is accessible

## Support
- NOWPayments supports 150+ cryptocurrencies
- Automatic wallet crediting on successful payments
- Real-time status updates via webhooks
- Comprehensive error handling and logging

## Future Enhancements
- Email notifications for successful payments
- Cryptocurrency exchange rate display
- Payment timeout handling
- Partial payment recovery options
