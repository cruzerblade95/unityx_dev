# NOWPayments Integration - Partially Disabled (User Side Only)# NOWPayments Integration - Currently Disabled

This document tracks NOWPayments functionality status. This document tracks all NOWPayments functionality that has been temporarily disabled in the system. Use this guide to re-enable NOWPayments when needed.

## Current Status## Overview

- ✅ **ADMIN SIDE: ENABLED** - Admins can manage deposits, view payment details, and approve/rejectNOWPayments is a cryptocurrency payment gateway that allows users to deposit funds using various cryptocurrencies, which are then converted to USDT. This functionality has been temporarily disabled across both admin and user interfaces.

- ❌ **USER SIDE: DISABLED** - Users cannot create new crypto deposits via NOWPayments

---

## Overview

## Files Modified

NOWPayments is a cryptocurrency payment gateway that allows users to deposit funds using various cryptocurrencies, which are then converted to USDT. The user-facing deposit functionality has been temporarily disabled, but admin management tools remain fully active.

### 1. **Admin Routes** (`routes/admin.php`)

---

**Lines: 66-75**

## What's Currently Active (Admin)

**Status:** Commented out

### ✅ Enabled Features:

**What was disabled:**

**Admin Routes** (`routes/admin.php` - Lines 66-75):

- `/admin/nowpayments` - Dashboard- Admin NOWPayments dashboard (`/admin/nowpayments`)

- `/admin/nowpayments/list` - Payment list- Payment list endpoint

- `/admin/nowpayments/refresh-status/{paymentId}` - Refresh single payment- Refresh payment status endpoints

- `/admin/nowpayments/refresh-all-statuses` - Bulk refresh- API testing endpoints

- `/admin/nowpayments/test-api` - API testing- Deposit approval/rejection endpoints

- `/admin/nowpayments/debug-connectivity` - Debug tools

- `/admin/nowpayments/deposit-details/{depositId}` - View details**To re-enable:**

- `/admin/deposits/approve/{depositId}` - Approve deposits

- `/admin/deposits/reject/{depositId}` - Reject deposits```php

// Remove comment markers from lines 66-75

**Admin UI**:// Change FROM:

- Admin navbar menu item (visible) // NOWPayments Payment Details - DISABLED (See NOWPAYMENTS_DISABLED.md to re-enable)

- Admin dashboard at `/admin/nowpayments` // Route::get('/nowpayments', [NOWPaymentsController::class, 'index'])->name('admin.nowpayments.index');

- All admin views and partials // Route::get('/nowpayments/list', [NOWPaymentsController::class, 'list'])->name('admin.nowpayments.list');

        // Route::post('/nowpayments/refresh-status/{paymentId}', [NOWPaymentsController::class, 'refreshStatus'])->name('admin.nowpayments.refresh-status');

--- // Route::post('/nowpayments/refresh-all-statuses', [NOWPaymentsController::class, 'refreshAllStatuses'])->name('admin.nowpayments.refresh-all-statuses');

        // Route::post('/nowpayments/test-api', [NOWPaymentsController::class, 'testApiConnection'])->name('admin.nowpayments.test-api');

## What's Currently Disabled (User Side) // Route::get('/nowpayments/debug-connectivity', [NOWPaymentsController::class, 'debugConnectivity'])->name('admin.nowpayments.debug-connectivity');

        // Route::get('/nowpayments/deposit-details/{depositId}', [NOWPaymentsController::class, 'showDepositDetails'])->name('admin.nowpayments.deposit-details');

### 1. **Webhook Routes** (`routes/web.php`) // Route::post('/deposits/approve/{depositId}', [NOWPaymentsController::class, 'approveDeposit'])->name('admin.deposits.approve');

        // Route::post('/deposits/reject/{depositId}', [NOWPaymentsController::class, 'rejectDeposit'])->name('admin.deposits.reject');

**Lines: 199-200**

// TO:

**Status:** ❌ Commented out // NOWPayments Payment Details

        Route::get('/nowpayments', [NOWPaymentsController::class, 'index'])->name('admin.nowpayments.index');

**What's disabled:** Route::get('/nowpayments/list', [NOWPaymentsController::class, 'list'])->name('admin.nowpayments.list');

- NOWPayments webhook endpoint for payment notifications Route::post('/nowpayments/refresh-status/{paymentId}', [NOWPaymentsController::class, 'refreshStatus'])->name('admin.nowpayments.refresh-status');

        Route::post('/nowpayments/refresh-all-statuses', [NOWPaymentsController::class, 'refreshAllStatuses'])->name('admin.nowpayments.refresh-all-statuses');

**Current code:** Route::post('/nowpayments/test-api', [NOWPaymentsController::class, 'testApiConnection'])->name('admin.nowpayments.test-api');

````php Route::get('/nowpayments/debug-connectivity', [NOWPaymentsController::class, 'debugConnectivity'])->name('admin.nowpayments.debug-connectivity');

// NOWPayments webhook route (public access) - DISABLED (See NOWPAYMENTS_DISABLED.md to re-enable)        Route::get('/nowpayments/deposit-details/{depositId}', [NOWPaymentsController::class, 'showDepositDetails'])->name('admin.nowpayments.deposit-details');

// Route::post('/api/nowpayments/webhook', [NOWPaymentsWebhookController::class, 'handle'])->name('nowpayments.webhook');        Route::post('/deposits/approve/{depositId}', [NOWPaymentsController::class, 'approveDeposit'])->name('admin.deposits.approve');

```        Route::post('/deposits/reject/{depositId}', [NOWPaymentsController::class, 'rejectDeposit'])->name('admin.deposits.reject');

````

**To re-enable:**

```php---

// NOWPayments webhook route (public access)

Route::post('/api/nowpayments/webhook', [NOWPaymentsWebhookController::class, 'handle'])->name('nowpayments.webhook');### 2. **Webhook Routes** (`routes/web.php`)

```

**Lines: 199-200**

---

**Status:** Commented out

### 2. **User Routes** (`routes/user.php`)

**What was disabled:**

**Lines: 103-104**

- NOWPayments webhook endpoint for payment notifications

**Status:** ❌ Commented out

**To re-enable:**

**What's disabled:**

- User crypto deposit creation endpoint```php

- Crypto deposit details page// Remove comment markers from lines 199-200

// Change FROM:

**Current code:**// NOWPayments webhook route (public access) - DISABLED (See NOWPAYMENTS_DISABLED.md to re-enable)

````php// Route::post('/api/nowpayments/webhook', [NOWPaymentsWebhookController::class, 'handle'])->name('nowpayments.webhook');

// Crypto deposit (NOWPayments) - DISABLED (See NOWPAYMENTS_DISABLED.md to re-enable)

// Route::post('deposit/crypto', [DepositController::class, 'createCryptoPayment'])->name('user.deposit.crypto');// TO:

// Route::get('deposit/crypto/{deposit}/details', [DepositController::class, 'cryptoDetails'])->name('user.deposit.crypto.details');// NOWPayments webhook route (public access)

```Route::post('/api/nowpayments/webhook', [NOWPaymentsWebhookController::class, 'handle'])->name('nowpayments.webhook');

````

**To re-enable:**

```php---

Route::post('deposit/crypto', [DepositController::class, 'createCryptoPayment'])->name('user.deposit.crypto');

Route::get('deposit/crypto/{deposit}/details', [DepositController::class, 'cryptoDetails'])->name('user.deposit.crypto.details');### 3. **User Routes** (`routes/user.php`)

```

**Lines: 103-104**

---

**Status:** Commented out

### 3. **User Deposit Page** (`resources/views/user/content/deposit.blade.php`)

**What was disabled:**

**Lines: 319-693 (Entire crypto deposit section)**

- User crypto deposit creation endpoint

**Status:** ❌ Commented out with Blade syntax- Crypto deposit details page

**What's disabled:\*\***To re-enable:\*\*

- Complete crypto deposit form

- Currency selection dropdown```php

- Fee calculation display// Remove comment markers from lines 103-104

- Crypto deposit history table// Change FROM:

- All related JavaScript functionality // Crypto deposit (NOWPayments) - DISABLED (See NOWPAYMENTS_DISABLED.md to re-enable)

  // Route::post('deposit/crypto', [DepositController::class, 'createCryptoPayment'])->name('user.deposit.crypto');

**To re-enable:** // Route::get('deposit/crypto/{deposit}/details', [DepositController::class, 'cryptoDetails'])->name('user.deposit.crypto.details');

Remove the `{{--` and `--}}` comment markers around:

- The main content section (starts around line 319)// TO:

- The script section (starts around line 590) Route::post('deposit/crypto', [DepositController::class, 'createCryptoPayment'])->name('user.deposit.crypto');

  Route::get('deposit/crypto/{deposit}/details', [DepositController::class, 'cryptoDetails'])->name('user.deposit.crypto.details');

---```

### 4. **User Navbar Menu** (`resources/views/user/layouts/sections/navbar/navbar-partial.blade.php`)---

**Line: 128**### 4. **Admin Navbar Menu** (`resources/views/admin/layouts/navbar-partial.blade.php`)

**Status:** ⚠️ Modified (redirected to direct deposit)**Lines: 163-172**

**What was changed:\*\***Status:\*\* Commented out with Blade syntax

- Deposit menu now points to direct XEUSD deposit instead of crypto deposit

**What was disabled:**

**Current code:**

```blade- Admin quick access menu item for NOWPayments deposits

{{-- Changed from route('user.deposit.index') to direct deposit (NOWPayments disabled) --}}

<a href="{{ route('user.deposit.direct') }}" class="stretched-link">Deposit</a>**To re-enable:**

```

`````blade

**To re-enable NOWPayments as primary:**// Remove {{-- and --}} comment markers from lines 163-172

```blade// Change FROM:

<a href="{{ route('user.deposit.index') }}" class="stretched-link">Deposit</a>                        {{-- NOWPayments Menu - DISABLED (See NOWPAYMENTS_DISABLED.md to re-enable)

```                        <div class="dropdown-shortcuts-item col">

                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">

---                                <i class="icon-base ti tabler-cash icon-26px text-heading"></i>

                            </span>

## Backend Files (Fully Functional)                            <a href="{{ route('admin.nowpayments.index') }}" class="stretched-link">Deposits</a>

                            <small>NOWPayments</small>

These files remain unchanged and work perfectly for admin functionality:                        </div>

                        --}}

### Controllers:

- ✅ `app/Http/Controllers/Admin/NOWPaymentsController.php` - Admin deposit management// TO:

- ⚠️ `app/Http/Controllers/NOWPaymentsWebhookController.php` - Webhook handler (route disabled)                        <div class="dropdown-shortcuts-item col">

- ⚠️ `app/Http/Controllers/User/DepositController.php` - User deposit creation (routes disabled)                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">

                                <i class="icon-base ti tabler-cash icon-26px text-heading"></i>

### Services:                            </span>

- ✅ `app/Services/NOWPaymentsService.php` - API integration service                            <a href="{{ route('admin.nowpayments.index') }}" class="stretched-link">Deposits</a>

                            <small>NOWPayments</small>

### Views:                        </div>

- ✅ `resources/views/admin/nowpayments/index.blade.php` - Admin dashboard```

- ✅ `resources/views/admin/nowpayments/partials/payment-list.blade.php` - Payment list partial

- ✅ `resources/views/admin/nowpayments/partials/custom-pagination.blade.php` - Pagination partial---

- ⚠️ `resources/views/user/content/deposit.blade.php` - User deposit page (commented out)

- ✅ `resources/views/user/content/deposit-success.blade.php` - Success page (handles both crypto & XEUSD)### 5. **User Deposit Page** (`resources/views/user/content/deposit.blade.php`)



### JavaScript:**Lines: 319-693 (Entire crypto deposit section)**

- ✅ `resources/js/admin-nowpayments-list.js` - Admin list functionality

- ✅ `resources/assets/js/admin-nowpayments-list.js` - Admin list assets**Status:** Commented out with Blade syntax



### Database:**What was disabled:**

- ✅ `database/migrations/2025_08_21_032255_add_nowpayments_fields_to_deposits_table.php` - Database schema

- Complete crypto deposit form

---- Currency selection dropdown

- Fee calculation display

## Re-enabling User Side Checklist- Crypto deposit history table

- All related JavaScript functionality

To fully re-enable NOWPayments for users:

**To re-enable:**

1. ⬜ Uncomment webhook route in `routes/web.php` (line 200)

2. ⬜ Uncomment user routes in `routes/user.php` (lines 103-104)```blade

3. ⬜ Uncomment crypto deposit section in `resources/views/user/content/deposit.blade.php` (lines 319-693)// Remove {{-- and --}} comment markers

4. ⬜ Update deposit link in `resources/views/user/layouts/sections/navbar/navbar-partial.blade.php` (line 128)// Change FROM (Line ~319):

5. ⬜ Verify NOWPayments API credentials in `.env`    {{-- NOWPayments Crypto Deposit - DISABLED (See NOWPAYMENTS_DISABLED.md to re-enable) --}}

6. ⬜ Test webhook endpoint: `POST /api/nowpayments/webhook`    {{--

7. ⬜ Test user deposit flow at `/deposit`    <div class="container py-4 position-relative" style="z-index: 1;">

        ...entire section...

---    </div>

    --}}

## Configuration    {{-- End NOWPayments Crypto Deposit Section --}}



NOWPayments requires the following environment variables (in `.env`):    <script>

        {{-- Crypto form validation scripts - DISABLED

```env        document.addEventListener('DOMContentLoaded', function() {

NOWPAYMENTS_API_KEY=your_nowpayments_api_key            ...entire script...

NOWPAYMENTS_IPN_SECRET=your_ipn_secret_for_webhooks        });

NOWPAYMENTS_SANDBOX=false  # Set to true for testing        --}}

```    </script>



---// TO:

    <div class="container py-4 position-relative" style="z-index: 1;">

## Features Status        ...entire section...

    </div>

### ✅ Admin Features (ENABLED):

- ✅ NOWPayments deposit dashboard    <script>

- ✅ Payment status monitoring        document.addEventListener('DOMContentLoaded', function() {

- ✅ Manual payment refresh            ...entire script...

- ✅ Bulk status refresh        });

- ✅ Payment details viewing    </script>

- ✅ Deposit approval/rejection```

- ✅ API connectivity testing

- ✅ Debug tools---



### ❌ User Features (DISABLED):### 6. **User Navbar Menu** (`resources/views/user/layouts/sections/navbar/navbar-partial.blade.php`)

- ❌ Cryptocurrency deposit with automatic USDT conversion

- ❌ Support for 10+ cryptocurrencies (BTC, ETH, LTC, etc.)**Lines: 128**

- ❌ Real-time crypto price conversion

- ❌ Crypto deposit history viewing on deposit page**Status:** Modified (not disabled, redirected)

- ❌ Payment address generation for users

- ❌ Webhook-based automatic confirmation**What was changed:**



---- Deposit menu now points to direct XEUSD deposit instead of crypto deposit



## Alternative Active Payment Methods**Current state:**



While NOWPayments user side is disabled, users can still deposit using:```blade

{{-- Changed from route('user.deposit.index') to direct deposit (NOWPayments disabled) --}}

✅ **XEUSD Direct Deposit** (1% fee)<a href="{{ route('user.deposit.direct') }}" class="stretched-link">Deposit</a>

- Route: `/deposit/direct````

- Direct blockchain transfer

- Automatic verification**To re-enable NOWPayments as primary:**

- Lower fees

```blade

---// Change FROM:

            {{-- Changed from route('user.deposit.index') to direct deposit (NOWPayments disabled) --}}

## Admin Access            <a href="{{ route('user.deposit.direct') }}" class="stretched-link">Deposit</a>



Admins can still:// TO:

- View all existing NOWPayments deposits (including old ones)            <a href="{{ route('user.deposit.index') }}" class="stretched-link">Deposit</a>

- Refresh payment statuses from NOWPayments API```

- Approve/reject deposits manually

- View detailed payment information---

- Test API connectivity

- Debug payment issues## Backend Files (Not Modified - Still Functional)



This allows admins to manage existing deposits and resolve any pending issues even while new user deposits are disabled.These files remain unchanged and will work immediately when routes are re-enabled:



---### Controllers:



## Notes- `app/Http/Controllers/Admin/NOWPaymentsController.php` - Admin deposit management

- `app/Http/Controllers/NOWPaymentsWebhookController.php` - Webhook handler

- Admin tools are fully functional and can manage all deposits- `app/Http/Controllers/User/DepositController.php` - User deposit creation

- Users cannot create NEW crypto deposits via NOWPayments

- Existing crypto deposits in the system can still be managed by admins### Services:

- All NOWPayments code remains in the codebase

- Database tables and migrations are unchanged- `app/Services/NOWPaymentsService.php` - API integration service

- Re-enabling user side requires only uncommenting code (no code changes needed)

### Views:

---

- `resources/views/admin/nowpayments/index.blade.php` - Admin dashboard

## Support- `resources/views/admin/nowpayments/partials/payment-list.blade.php` - Payment list partial

- `resources/views/admin/nowpayments/partials/custom-pagination.blade.php` - Pagination partial

For issues or questions:- `resources/views/user/content/deposit-success.blade.php` - Success page (handles both crypto & XEUSD)

1. Admin side issues: Check `/admin/nowpayments` dashboard and debug tools

2. API issues: Use "Test API Connection" button in admin panel### JavaScript:

3. Webhook logs: Review `storage/logs/laravel.log`

4. NOWPayments API status: https://nowpayments.io/status- `resources/js/admin-nowpayments-list.js` - Admin list functionality

- `resources/assets/js/admin-nowpayments-list.js` - Admin list assets

---

### Database:

**Last Updated:** October 31, 2025

**Status:** Partially Enabled (Admin Only)  - `database/migrations/2025_08_21_032255_add_nowpayments_fields_to_deposits_table.php` - Database schema

**Reason:** User side temporarily disabled, admin management kept active

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
`````

NOWPAYMENTS_API_KEY=your_api_key_here
NOWPAYMENTS_IPN_SECRET=your_ipn_secret_here
NOWPAYMENTS_SANDBOX=false

````
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
````

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
