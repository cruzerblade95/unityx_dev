# NOWPayments Integration - Currently Disabled

This document tracks all NOWPayments functionality that has been temporarily disabled in the system. Use this guide to re-enable NOWPayments when needed.

## Overview

NOWPayments is a cryptocurrency payment gateway that allows users to deposit funds using various cryptocurrencies, which are then converted to USDT. This functionality has been temporarily disabled across both admin and user interfaces.

---

## Files Modified

### 1. **Admin Routes** (`routes/admin.php`)

**Lines: 66-75**

**Status:** Commented out

**What was disabled:**

- Admin NOWPayments dashboard (`/admin/nowpayments`)
- Payment list endpoint
- Refresh payment status endpoints
- API testing endpoints
- Deposit approval/rejection endpoints

**To re-enable:**

```php
// Remove comment markers from lines 66-75
// Change FROM:
        // NOWPayments Payment Details - DISABLED (See NOWPAYMENTS_DISABLED.md to re-enable)
        // Route::get('/nowpayments', [NOWPaymentsController::class, 'index'])->name('admin.nowpayments.index');
        // Route::get('/nowpayments/list', [NOWPaymentsController::class, 'list'])->name('admin.nowpayments.list');
        // Route::post('/nowpayments/refresh-status/{paymentId}', [NOWPaymentsController::class, 'refreshStatus'])->name('admin.nowpayments.refresh-status');
        // Route::post('/nowpayments/refresh-all-statuses', [NOWPaymentsController::class, 'refreshAllStatuses'])->name('admin.nowpayments.refresh-all-statuses');
        // Route::post('/nowpayments/test-api', [NOWPaymentsController::class, 'testApiConnection'])->name('admin.nowpayments.test-api');
        // Route::get('/nowpayments/debug-connectivity', [NOWPaymentsController::class, 'debugConnectivity'])->name('admin.nowpayments.debug-connectivity');
        // Route::get('/nowpayments/deposit-details/{depositId}', [NOWPaymentsController::class, 'showDepositDetails'])->name('admin.nowpayments.deposit-details');
        // Route::post('/deposits/approve/{depositId}', [NOWPaymentsController::class, 'approveDeposit'])->name('admin.deposits.approve');
        // Route::post('/deposits/reject/{depositId}', [NOWPaymentsController::class, 'rejectDeposit'])->name('admin.deposits.reject');

// TO:
        // NOWPayments Payment Details
        Route::get('/nowpayments', [NOWPaymentsController::class, 'index'])->name('admin.nowpayments.index');
        Route::get('/nowpayments/list', [NOWPaymentsController::class, 'list'])->name('admin.nowpayments.list');
        Route::post('/nowpayments/refresh-status/{paymentId}', [NOWPaymentsController::class, 'refreshStatus'])->name('admin.nowpayments.refresh-status');
        Route::post('/nowpayments/refresh-all-statuses', [NOWPaymentsController::class, 'refreshAllStatuses'])->name('admin.nowpayments.refresh-all-statuses');
        Route::post('/nowpayments/test-api', [NOWPaymentsController::class, 'testApiConnection'])->name('admin.nowpayments.test-api');
        Route::get('/nowpayments/debug-connectivity', [NOWPaymentsController::class, 'debugConnectivity'])->name('admin.nowpayments.debug-connectivity');
        Route::get('/nowpayments/deposit-details/{depositId}', [NOWPaymentsController::class, 'showDepositDetails'])->name('admin.nowpayments.deposit-details');
        Route::post('/deposits/approve/{depositId}', [NOWPaymentsController::class, 'approveDeposit'])->name('admin.deposits.approve');
        Route::post('/deposits/reject/{depositId}', [NOWPaymentsController::class, 'rejectDeposit'])->name('admin.deposits.reject');
```

---

### 2. **Webhook Routes** (`routes/web.php`)

**Lines: 199-200**

**Status:** Commented out

**What was disabled:**

- NOWPayments webhook endpoint for payment notifications

**To re-enable:**

```php
// Remove comment markers from lines 199-200
// Change FROM:
// NOWPayments webhook route (public access) - DISABLED (See NOWPAYMENTS_DISABLED.md to re-enable)
// Route::post('/api/nowpayments/webhook', [NOWPaymentsWebhookController::class, 'handle'])->name('nowpayments.webhook');

// TO:
// NOWPayments webhook route (public access)
Route::post('/api/nowpayments/webhook', [NOWPaymentsWebhookController::class, 'handle'])->name('nowpayments.webhook');
```

---

### 3. **User Routes** (`routes/user.php`)

**Lines: 103-104**

**Status:** Commented out

**What was disabled:**

- User crypto deposit creation endpoint
- Crypto deposit details page

**To re-enable:**

```php
// Remove comment markers from lines 103-104
// Change FROM:
    // Crypto deposit (NOWPayments) - DISABLED (See NOWPAYMENTS_DISABLED.md to re-enable)
    // Route::post('deposit/crypto', [DepositController::class, 'createCryptoPayment'])->name('user.deposit.crypto');
    // Route::get('deposit/crypto/{deposit}/details', [DepositController::class, 'cryptoDetails'])->name('user.deposit.crypto.details');

// TO:
    Route::post('deposit/crypto', [DepositController::class, 'createCryptoPayment'])->name('user.deposit.crypto');
    Route::get('deposit/crypto/{deposit}/details', [DepositController::class, 'cryptoDetails'])->name('user.deposit.crypto.details');
```

---

### 4. **Admin Navbar Menu** (`resources/views/admin/layouts/navbar-partial.blade.php`)

**Lines: 163-172**

**Status:** Commented out with Blade syntax

**What was disabled:**

- Admin quick access menu item for NOWPayments deposits

**To re-enable:**

```blade
// Remove {{-- and --}} comment markers from lines 163-172
// Change FROM:
                        {{-- NOWPayments Menu - DISABLED (See NOWPAYMENTS_DISABLED.md to re-enable)
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ti tabler-cash icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('admin.nowpayments.index') }}" class="stretched-link">Deposits</a>
                            <small>NOWPayments</small>
                        </div>
                        --}}

// TO:
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ti tabler-cash icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('admin.nowpayments.index') }}" class="stretched-link">Deposits</a>
                            <small>NOWPayments</small>
                        </div>
```

---

### 5. **User Deposit Page** (`resources/views/user/content/deposit.blade.php`)

**Lines: 319-693 (Entire crypto deposit section)**

**Status:** Commented out with Blade syntax

**What was disabled:**

- Complete crypto deposit form
- Currency selection dropdown
- Fee calculation display
- Crypto deposit history table
- All related JavaScript functionality

**To re-enable:**

```blade
// Remove {{-- and --}} comment markers
// Change FROM (Line ~319):
    {{-- NOWPayments Crypto Deposit - DISABLED (See NOWPAYMENTS_DISABLED.md to re-enable) --}}
    {{--
    <div class="container py-4 position-relative" style="z-index: 1;">
        ...entire section...
    </div>
    --}}
    {{-- End NOWPayments Crypto Deposit Section --}}

    <script>
        {{-- Crypto form validation scripts - DISABLED
        document.addEventListener('DOMContentLoaded', function() {
            ...entire script...
        });
        --}}
    </script>

// TO:
    <div class="container py-4 position-relative" style="z-index: 1;">
        ...entire section...
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ...entire script...
        });
    </script>
```

---

### 6. **User Navbar Menu** (`resources/views/user/layouts/sections/navbar/navbar-partial.blade.php`)

**Lines: 128**

**Status:** Modified (not disabled, redirected)

**What was changed:**

- Deposit menu now points to direct XEUSD deposit instead of crypto deposit

**Current state:**

```blade
{{-- Changed from route('user.deposit.index') to direct deposit (NOWPayments disabled) --}}
<a href="{{ route('user.deposit.direct') }}" class="stretched-link">Deposit</a>
```

**To re-enable NOWPayments as primary:**

```blade
// Change FROM:
            {{-- Changed from route('user.deposit.index') to direct deposit (NOWPayments disabled) --}}
            <a href="{{ route('user.deposit.direct') }}" class="stretched-link">Deposit</a>

// TO:
            <a href="{{ route('user.deposit.index') }}" class="stretched-link">Deposit</a>
```

---

## Backend Files (Not Modified - Still Functional)

These files remain unchanged and will work immediately when routes are re-enabled:

### Controllers:

- `app/Http/Controllers/Admin/NOWPaymentsController.php` - Admin deposit management
- `app/Http/Controllers/NOWPaymentsWebhookController.php` - Webhook handler
- `app/Http/Controllers/User/DepositController.php` - User deposit creation

### Services:

- `app/Services/NOWPaymentsService.php` - API integration service

### Views:

- `resources/views/admin/nowpayments/index.blade.php` - Admin dashboard
- `resources/views/admin/nowpayments/partials/payment-list.blade.php` - Payment list partial
- `resources/views/admin/nowpayments/partials/custom-pagination.blade.php` - Pagination partial
- `resources/views/user/content/deposit-success.blade.php` - Success page (handles both crypto & XEUSD)

### JavaScript:

- `resources/js/admin-nowpayments-list.js` - Admin list functionality
- `resources/assets/js/admin-nowpayments-list.js` - Admin list assets

### Database:

- `database/migrations/2025_08_21_032255_add_nowpayments_fields_to_deposits_table.php` - Database schema

---

## Re-enabling Checklist

To fully re-enable NOWPayments functionality:

1. ✅ Uncomment routes in `routes/admin.php` (lines 66-75)
2. ✅ Uncomment webhook route in `routes/web.php` (lines 199-200)
3. ✅ Uncomment user routes in `routes/user.php` (lines 103-104)
4. ✅ Uncomment admin menu in `resources/views/admin/layouts/navbar-partial.blade.php` (lines 163-172)
5. ✅ Uncomment crypto deposit section in `resources/views/user/content/deposit.blade.php` (lines 319-693)
6. ✅ Update deposit link in `resources/views/user/layouts/sections/navbar/navbar-partial.blade.php` (line 128)
7. ✅ Verify NOWPayments API credentials in `.env`:
   ```
   NOWPAYMENTS_API_KEY=your_api_key_here
   NOWPAYMENTS_IPN_SECRET=your_ipn_secret_here
   NOWPAYMENTS_SANDBOX=false
   ```
8. ✅ Test webhook endpoint: `POST /api/nowpayments/webhook`
9. ✅ Test admin deposit management at `/admin/nowpayments`
10. ✅ Test user deposit flow at `/deposit`

---

## Configuration

NOWPayments requires the following environment variables (in `.env`):

```env
NOWPAYMENTS_API_KEY=your_nowpayments_api_key
NOWPAYMENTS_IPN_SECRET=your_ipn_secret_for_webhooks
NOWPAYMENTS_SANDBOX=false  # Set to true for testing
```

---

## Features Disabled

### User Features:

- ❌ Cryptocurrency deposit with automatic USDT conversion
- ❌ Support for 10+ cryptocurrencies (BTC, ETH, LTC, etc.)
- ❌ Real-time crypto price conversion
- ❌ Crypto deposit history viewing
- ❌ Payment address generation
- ❌ Webhook-based automatic confirmation

### Admin Features:

- ❌ NOWPayments deposit dashboard
- ❌ Payment status monitoring
- ❌ Manual payment refresh
- ❌ Bulk status refresh
- ❌ Payment details viewing
- ❌ Deposit approval/rejection
- ❌ API connectivity testing
- ❌ Debug tools

---

## Alternative Active Payment Methods

While NOWPayments is disabled, users can still deposit using:

✅ **XEUSD Direct Deposit** (1% fee)

- Route: `/deposit/direct`
- Direct blockchain transfer
- Automatic verification
- Lower fees

---

## Notes

- All NOWPayments code remains in the codebase and is functional
- Only UI access and routes are disabled
- Database tables and migrations are unchanged
- Re-enabling requires only uncommenting code (no code changes needed)
- Consider testing in sandbox mode before enabling in production

---

## Support

For issues or questions about re-enabling NOWPayments:

1. Verify all environment variables are set correctly
2. Check NOWPayments API status: https://nowpayments.io/status
3. Review webhook logs in `storage/logs/laravel.log`
4. Test API connection using admin debug tools after re-enabling

---

**Last Updated:** October 27, 2025
**Status:** Temporarily Disabled
**Reason:** System configuration/preference change
