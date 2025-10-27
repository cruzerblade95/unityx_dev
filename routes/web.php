<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\laravel_example\UserManagement;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\dashboard\Crm;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\layouts\CollapsedMenu;
use App\Http\Controllers\layouts\ContentNavbar;
use App\Http\Controllers\layouts\ContentNavSidebar;
use App\Http\Controllers\layouts\NavbarFull;
use App\Http\Controllers\layouts\Navbar as LayoutNavbar;
use App\Http\Controllers\layouts\Horizontal;
use App\Http\Controllers\layouts\Vertical;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\front_pages\Landing;
use App\Http\Controllers\front_pages\Pricing;
use App\Http\Controllers\front_pages\Payment;
use App\Http\Controllers\front_pages\Checkout;
use App\Http\Controllers\front_pages\HelpCenter;
use App\Http\Controllers\front_pages\HelpCenterArticle;
use App\Http\Controllers\apps\Email;
use App\Http\Controllers\apps\Chat;
use App\Http\Controllers\apps\Calendar;
use App\Http\Controllers\apps\Kanban;
use App\Http\Controllers\NOWPaymentsWebhookController;
use App\Http\Controllers\apps\EcommerceDashboard;
use App\Http\Controllers\apps\EcommerceProductList;
use App\Http\Controllers\apps\EcommerceProductAdd;
use App\Http\Controllers\apps\EcommerceProductCategory;
use App\Http\Controllers\apps\EcommerceOrderList;
use App\Http\Controllers\apps\EcommerceOrderDetails;
use App\Http\Controllers\apps\EcommerceCustomerAll;
use App\Http\Controllers\apps\EcommerceCustomerDetailsOverview;
use App\Http\Controllers\apps\EcommerceCustomerDetailsSecurity;
use App\Http\Controllers\apps\EcommerceCustomerDetailsBilling;
use App\Http\Controllers\apps\EcommerceCustomerDetailsNotifications;
use App\Http\Controllers\apps\EcommerceManageReviews;
use App\Http\Controllers\apps\EcommerceReferrals;
use App\Http\Controllers\apps\EcommerceSettingsDetails;
use App\Http\Controllers\apps\EcommerceSettingsPayments;
use App\Http\Controllers\apps\EcommerceSettingsCheckout;
use App\Http\Controllers\apps\EcommerceSettingsShipping;
use App\Http\Controllers\apps\EcommerceSettingsLocations;
use App\Http\Controllers\apps\EcommerceSettingsNotifications;
use App\Http\Controllers\apps\AcademyDashboard;
use App\Http\Controllers\apps\AcademyCourse;
use App\Http\Controllers\apps\AcademyCourseDetails;
use App\Http\Controllers\apps\LogisticsDashboard;
use App\Http\Controllers\apps\LogisticsFleet;
use App\Http\Controllers\apps\InvoiceList;
use App\Http\Controllers\apps\InvoicePreview;
use App\Http\Controllers\apps\InvoicePrint;
use App\Http\Controllers\apps\InvoiceEdit;
use App\Http\Controllers\apps\InvoiceAdd;
use App\Http\Controllers\apps\UserList;
use App\Http\Controllers\apps\UserViewAccount;
use App\Http\Controllers\apps\UserViewSecurity;
use App\Http\Controllers\apps\UserViewBilling;
use App\Http\Controllers\apps\UserViewNotifications;
use App\Http\Controllers\apps\UserViewConnections;
use App\Http\Controllers\apps\AccessRoles;
use App\Http\Controllers\apps\AccessPermission;
use App\Http\Controllers\pages\UserProfile;
use App\Http\Controllers\pages\UserTeams;
use App\Http\Controllers\pages\UserProjects;
use App\Http\Controllers\pages\UserConnections;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsSecurity;
use App\Http\Controllers\pages\AccountSettingsBilling;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\Faq;
use App\Http\Controllers\pages\Pricing as PagesPricing;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\pages\MiscComingSoon;
use App\Http\Controllers\pages\MiscNotAuthorized;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\LoginCover;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\RegisterCover;
use App\Http\Controllers\authentications\RegisterMultiSteps;
use App\Http\Controllers\authentications\VerifyEmailBasic;
use App\Http\Controllers\authentications\VerifyEmailCover;
use App\Http\Controllers\authentications\ResetPasswordBasic;
use App\Http\Controllers\authentications\ResetPasswordCover;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\authentications\ForgotPasswordCover;
use App\Http\Controllers\authentications\TwoStepsBasic;
use App\Http\Controllers\authentications\TwoStepsCover;
use App\Http\Controllers\wizard_example\Checkout as WizardCheckout;
use App\Http\Controllers\wizard_example\PropertyListing;
use App\Http\Controllers\wizard_example\CreateDeal;
use App\Http\Controllers\modal\ModalExample;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\cards\CardAdvance;
use App\Http\Controllers\cards\CardStatistics;
use App\Http\Controllers\cards\CardAnalytics;
use App\Http\Controllers\cards\CardGamifications;
use App\Http\Controllers\cards\CardActions;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar as UINavbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\Avatar;
use App\Http\Controllers\extended_ui\BlockUI;
use App\Http\Controllers\extended_ui\DragAndDrop;
use App\Http\Controllers\extended_ui\MediaPlayer;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\StarRatings;
use App\Http\Controllers\extended_ui\SweetAlert;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\extended_ui\TimelineBasic;
use App\Http\Controllers\extended_ui\TimelineFullscreen;
use App\Http\Controllers\extended_ui\Tour;
use App\Http\Controllers\extended_ui\Treeview;
use App\Http\Controllers\extended_ui\Misc;
use App\Http\Controllers\icons\Tabler;
use App\Http\Controllers\icons\FontAwesome;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_elements\CustomOptions;
use App\Http\Controllers\form_elements\Editors;
use App\Http\Controllers\form_elements\FileUpload;
use App\Http\Controllers\form_elements\Picker;
use App\Http\Controllers\form_elements\Selects;
use App\Http\Controllers\form_elements\Sliders;
use App\Http\Controllers\form_elements\Switches;
use App\Http\Controllers\form_elements\Extras;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\form_layouts\StickyActions;
use App\Http\Controllers\form_wizard\Numbered as FormWizardNumbered;
use App\Http\Controllers\form_wizard\Icons as FormWizardIcons;
use App\Http\Controllers\form_validation\Validation;
use App\Http\Controllers\tables\Basic as TablesBasic;
use App\Http\Controllers\tables\DatatableBasic;
use App\Http\Controllers\tables\DatatableAdvanced;
use App\Http\Controllers\tables\DatatableExtensions;
use App\Http\Controllers\charts\ApexCharts;
use App\Http\Controllers\charts\ChartJs;
use App\Http\Controllers\maps\Leaflet;
use App\Http\Controllers\authentications\ConnectTelegramController;
use App\Http\Controllers\TelegramController;

// Migration Notice Route (High Priority)
Route::get('/migration-notice', [App\Http\Controllers\MigrationNoticeController::class, 'index'])
    ->name('migration-notice');

// Test route to check domain detection
Route::get('/test-domain', function () {
    $host = request()->getHost();
    $isUnityx = str_contains(strtolower($host), 'unityx.dev');

    return response()->json([
        'host' => $host,
        'is_unityx_dev' => $isUnityx,
        'should_show_migration' => $isUnityx
    ]);
});

// Test route to show migration notice directly
Route::get('/test-migration', function () {
    return view('migration_notice');
});

// Account Recovery Routes (High Priority - Must be at top)
Route::get('/account-recovery', [App\Http\Controllers\AccountRecoveryController::class, 'showEmailForm'])
    ->name('account-recovery.email');
Route::post('/account-recovery/validate-email', [App\Http\Controllers\AccountRecoveryController::class, 'validateEmail'])->name('account-recovery.validate-email');
Route::get('/account-recovery/details', [App\Http\Controllers\AccountRecoveryController::class, 'showDetailsForm'])->name('account-recovery.details');
Route::post('/account-recovery/submit', [App\Http\Controllers\AccountRecoveryController::class, 'submitRecovery'])->name('account-recovery.submit');
Route::get('/account-recovery/success', [App\Http\Controllers\AccountRecoveryController::class, 'showSuccess'])->name('account-recovery.success');
Route::get('/account-recovery/search-referral-users', [App\Http\Controllers\AccountRecoveryController::class, 'searchReferralUsers'])->name('account-recovery.search-referral-users');
Route::post('/account-recovery/reset', [App\Http\Controllers\AccountRecoveryController::class, 'resetRecovery'])->name('account-recovery.reset');

// NOWPayments webhook route (public access) - DISABLED (See NOWPAYMENTS_DISABLED.md to re-enable)
// Route::post('/api/nowpayments/webhook', [NOWPaymentsWebhookController::class, 'handle'])->name('nowpayments.webhook');

// Route to process wallets and insert into ai_trades if not already present
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\CalculationDepositProfitController;
use App\Models\User;
use Illuminate\Support\Facades\Log;

// Manual trigger for calculation of deposit profit
Route::get('/calculate-deposit-profit', [CalculationDepositProfitController::class, 'calculate']);

Route::get('/process-wallets-to-ai-trades', function () {
    $users = DB::table('users')->get();
    $upserts = [];
    foreach ($users as $user) {
        $wallet = DB::table('wallets')->where('user_id', $user->id)->first();
        if (!$wallet) continue;
        $amount_invest = $wallet->deposit;
        $ending_amount = $amount_invest * 5;

        // Get all AI trades for this user, ordered by creation date
        $aiTrades = DB::table('ai_trades')->where('user_id', $user->id)->orderBy('created_at', 'asc')->get();

        if ($aiTrades->count() >= 2) {
            // If there are 2 or more rows, only update the earliest (first) row
            $earliestTrade = $aiTrades->first();

            // Calculate sum of amount_invest from row 2 onwards (skip first row)
            $sumFromRow2Onwards = $aiTrades->skip(1)->sum('amount_invest');

            // Calculate the difference: sum from row 2+ minus first row's current amount_invest
            // $difference = $sumFromRow2Onwards - $earliestTrade->amount_invest;
            $difference = $amount_invest - $sumFromRow2Onwards;

            // Update the earliest trade with wallet deposit amount plus the calculated difference
            // $newAmountInvest = $amount_invest + $difference;
            $newAmountInvest = $difference;
            $newEndingAmount = $newAmountInvest * 5;

            DB::table('ai_trades')->where('id', $earliestTrade->id)->update([
                'amount_invest' => $newAmountInvest,
                'ending_amount' => $newEndingAmount,
                'trade_status' => 1,
                'updated_at' => now(),
            ]);

            $upserts[] = [
                'user_id' => $user->id,
                'action' => 'updated_earliest_only',
                'trade_id' => $earliestTrade->id,
                'total_trades' => $aiTrades->count(),
                'sum_from_row2_onwards' => $sumFromRow2Onwards,
                'first_row_original_amount' => $earliestTrade->amount_invest,
                'difference_calculated' => $difference,
                'new_amount_invest' => $newAmountInvest
            ];
        } else if ($aiTrades->count() == 1) {
            // If there's exactly 1 row, update it
            $aiTrade = $aiTrades->first();
            DB::table('ai_trades')->where('id', $aiTrade->id)->update([
                'amount_invest' => $amount_invest,
                'ending_amount' => $ending_amount,
                'trade_status' => 1,
                'updated_at' => now(),
            ]);
            $upserts[] = ['user_id' => $user->id, 'action' => 'updated', 'trade_id' => $aiTrade->id];
        } else {
            // If there are no rows, insert new
            $tradeId = DB::table('ai_trades')->insertGetId([
                'user_id' => $user->id,
                'amount_invest' => $amount_invest,
                'trade_status' => 1,
                'ending_amount' => $ending_amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $upserts[] = ['user_id' => $user->id, 'action' => 'inserted', 'trade_id' => $tradeId];
        }
    }
    return response()->json([
        'upserts' => $upserts,
        'message' => 'Processed wallets. Updated or inserted ai_trades as needed. For users with 2+ trades, only earliest trade was updated.'
    ]);
});

//telegram bot hehe
Route::get('/connect-telegram', [ConnectTelegramController::class, 'index'])->name('connect-telegram');
Route::get('/telegram/verify', [TelegramController::class, 'verify'])->name('telegram.verify');

// Main Page Route
Route::get('/', function () {
    // Show landing page (middleware will handle unityx.dev domain check)
    return app(Landing::class)->index();
})->name('front-pages-landing');

// Jetstream Routes
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);

// modal
Route::get('/modal-examples', [ModalExample::class, 'index'])->name('modal-examples');

// maps
Route::get('/maps/leaflet', [Leaflet::class, 'index'])->name('maps-leaflet');

// laravel example
Route::get('/laravel/user-management', [UserManagement::class, 'UserManagement'])->name('laravel-example-user-management');
Route::resource('/user-list', UserManagement::class);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        // return view('dashboard');
        return redirect('/user/dashboard');
    })->name('dashboard');
});

// Test route for MYR conversion rate
Route::get('/test-myr', function() {
    return response()->json(['myr_rate' => App\Models\WithdrawalSettings::getMyrConversionRate()]);
});

// Test route for MYR conversion functionality
Route::get('/test-myr-functionality', function() {
    try {
        // Test the MYR conversion rate method
        $myrRate = App\Models\WithdrawalSettings::getMyrConversionRate();

        // Test with a sample amount
        $sampleAmount = 100.00;
        $convertedAmount = $sampleAmount * $myrRate;

        return response()->json([
            'success' => true,
            'myr_rate' => $myrRate,
            'sample_usd' => $sampleAmount,
            'converted_myr' => $convertedAmount,
            'test_passed' => true,
            'message' => 'MYR conversion functionality is working'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'test_passed' => false
        ]);
    }
});



// Catch-all route (must be last)
Route::fallback(function () {
    // Show 404 (middleware will handle unityx.dev domain check)
    abort(404);
});
