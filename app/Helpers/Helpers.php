<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\TelegramNotificationService;
use App\Models\User;

class Helpers
{
  /**
   * Insert a new AI trade for a user with given amount.
   *
   * @param int $user_id
   * @param float $amount_invest
   * @return int|bool Inserted trade ID or false on failure
   */
  public static function insertAiTradeV2($user_id, $amount_invest)
  {
    $ending_amount = $amount_invest * 5;
    $tradeId = DB::table('ai_trades')->insertGetId([
      'user_id' => $user_id,
      'amount_invest' => $amount_invest,
      'ending_amount' => $ending_amount,
      'trade_status' => 1,
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    // Send Telegram notification to user about new AI Trade
    if ($tradeId) {
      try {
        $user = User::find($user_id);
        if ($user) {
          $title = '🤖 New AI Trade Started';
          $message = "Your AI Trade has been successfully created!\n\n";
          $message .= "💰 Investment Amount: $" . number_format($amount_invest, 2) . "\n";
          $message .= "🎯 Target Amount: $" . number_format($ending_amount, 2) . " (5x potential return)\n";
          $message .= "📊 Trade ID: #{$tradeId}\n";
          $message .= "⏰ Status: Running\n\n";
          $message .= "Your AI Trade is now active and working to generate profits. You can monitor its progress in your dashboard.";

          TelegramNotificationService::sendNotificationToTelegram(
            $user_id,
            $title,
            $message
          );
        }
      } catch (\Exception $e) {
        // Log error but don't fail the trade creation
        Log::error('Failed to send Telegram notification for new AI Trade', [
          'user_id' => $user_id,
          'trade_id' => $tradeId,
          'error' => $e->getMessage()
        ]);
      }
    }

    return $tradeId;
  }
  /**
   * Distribute ROI Matching Bonus to 10-level referral downline
   * @param int $userId The user who generated the ROI
   * @param float $amount The base amount for bonus calculation
   */
  public static function roiMatchingBonus($userId, $amount)
  {
    $percentages = [6, 3, 2, 1, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5];
    $totalPercent = 15;
    $bonusBase = $amount * ($totalPercent / 100);
    $currentUserId = $userId;

    // Get the username of the user who generated the ROI
    $fromUser = DB::table('users')->where('id', $userId)->first();
    $fromUsername = $fromUser ? $fromUser->username : 'Unknown User';

    for ($level = 1; $level <= 10; $level++) {
      $referrer = DB::table('users')->where('id', $currentUserId)->value('upline_id');
      if (!$referrer) break;
      $percent = $percentages[$level-1];
      $bonus = $amount * ($percent / 100);
      // Add bonus to wallet
      DB::table('wallets')->where('user_id', $referrer)->increment('bonus', $bonus);
      // Log to referral_bonus_histories
      DB::table('referral_bonus_histories')->insert([
        'user_id' => $referrer,
        'from_user_id' => $userId,
        'amount' => $bonus,
        'percent' => $percent,
        'bonus_type' => 'roi_matching',
        'level' => $level,
        'note' => 'ROI Matching Bonus from ' . $fromUsername,
        'created_at' => now(),
        'updated_at' => now(),
      ]);
      $currentUserId = $referrer;
    }
  }

  /**
   * Distribute Deposit Bonus to referral downline based on level 1 referrer's direct downline count
   * @param int $userId The user who made the deposit
   * @param float $amount The base amount for bonus calculation
   */
  public static function depositBonus($userId, $amount)
  {
    // $percentages = [10, 5, 3, 2, 1, 0.5, 0.5, 0.5, 0.5, 0.5];
    $percentages = [6, 2, 1, 0.3, 0.2, 0.1, 0.1, 0.1, 0.1, 0.1];
    // $totalPercent = 22.5;
    $totalPercent = 10;
    $bonusBase = $amount * ($totalPercent / 100);
    $currentUserId = $userId;

    // Get the username of the user who made the deposit
    $fromUser = DB::table('users')->where('id', $userId)->first();
    $fromUsername = $fromUser ? $fromUser->username : 'Unknown User';

    // Get level 1 referrer (direct upline)
    $level1Referrer = DB::table('users')->where('id', $currentUserId)->value('upline_id');

    // Determine maximum levels based on level 1 referrer's direct downline count
    $maxLevels = 3; // Default to 3 levels
    if ($level1Referrer) {
      // Count direct downlines of level 1 referrer (only level 1 downlines)
      $directDownlineCount = DB::table('users')->where('upline_id', $level1Referrer)->count();

      // If level 1 referrer has 6 or more direct downlines, distribute to 10 levels
      if ($directDownlineCount >= 6) {
        $maxLevels = 10;
      }
    }

    for ($level = 1; $level <= $maxLevels; $level++) {
      $referrer = DB::table('users')->where('id', $currentUserId)->value('upline_id');
      if (!$referrer) break;
      $percent = $percentages[$level-1];
      $bonus = $amount * ($percent / 100);
      // Add bonus to wallet
      DB::table('wallets')->where('user_id', $referrer)->increment('bonus', $bonus);
      // Log to referral_bonus_histories
      DB::table('referral_bonus_histories')->insert([
        'user_id' => $referrer,
        'from_user_id' => $userId,
        'amount' => $bonus,
        'percent' => $percent,
        'bonus_type' => 'deposit',
        'level' => $level,
        'note' => 'Deposit Bonus from ' . $fromUsername . ' (Max levels: ' . $maxLevels . ')',
        'created_at' => now(),
        'updated_at' => now(),
      ]);
      $currentUserId = $referrer;
    }
  }
  /**
   * Generate menu attributes for semi-dark mode
   *
   * @param bool $semiDarkEnabled Whether semi-dark mode is enabled
   * @return array HTML attributes for the menu element
   */
  public static function getMenuAttributes($semiDarkEnabled)
  {
    $attributes = [];

    if ($semiDarkEnabled) {
      $attributes['data-bs-theme'] = 'dark';
    }

    return $attributes;
  }

  public static function appClasses()
  {

    $data = config('custom.custom');


    // default data array
    $DefaultData = [
      'myLayout' => 'vertical',
      'myTheme' => 'light',
      'mySkins' => 'default',
      'hasSemiDark' => false,
      'myRTLMode' => true,
      'hasCustomizer' => true,
      'showDropdownOnHover' => true,
      'displayCustomizer' => true,
      'contentLayout' => 'compact',
      'headerType' => 'fixed',
      'navbarType' => 'sticky',
      'menuFixed' => true,
      'menuCollapsed' => false,
      'footerFixed' => false,
      'customizerControls' => [
        'color',
        'theme',
        'skins',
        'semiDark',
        'layoutCollapsed',
        'layoutNavbarOptions',
        'headerType',
        'contentLayout',
        'rtl'
      ],
      //   'defaultLanguage'=>'en',
    ];

    // if any key missing of array from custom.php file it will be merge and set a default value from dataDefault array and store in data variable
    $data = array_merge($DefaultData, $data);

    // All options available in the template
    $allOptions = [
      'myLayout' => ['vertical', 'horizontal', 'blank', 'front'],
      'menuCollapsed' => [true, false],
      'hasCustomizer' => [true, false],
      'showDropdownOnHover' => [true, false],
      'displayCustomizer' => [true, false],
      'contentLayout' => ['compact', 'wide'],
      'headerType' => ['fixed', 'static'],
      'navbarType' => ['sticky', 'static', 'hidden'],
      'myTheme' => ['light', 'dark', 'system'],
      'mySkins' => ['default', 'bordered', 'raspberry'],
      'hasSemiDark' => [true, false],
      'myRTLMode' => [true, false],
      'menuFixed' => [true, false],
      'footerFixed' => [true, false],
      'customizerControls' => [],
      // 'defaultLanguage'=>array('en'=>'en','fr'=>'fr','de'=>'de','ar'=>'ar'),
    ];

    //if myLayout value empty or not match with default options in custom.php config file then set a default value
    foreach ($allOptions as $key => $value) {
      if (array_key_exists($key, $DefaultData)) {
        if (gettype($DefaultData[$key]) === gettype($data[$key])) {
          // data key should be string
          if (is_string($data[$key])) {
            // data key should not be empty
            if (isset($data[$key]) && $data[$key] !== null) {
              // data key should not be exist inside allOptions array's sub array
              if (!array_key_exists($data[$key], $value)) {
                // ensure that passed value should be match with any of allOptions array value
                $result = array_search($data[$key], $value, 'strict');
                if (empty($result) && $result !== 0) {
                  $data[$key] = $DefaultData[$key];
                }
              }
            } else {
              // if data key not set or
              $data[$key] = $DefaultData[$key];
            }
          }
        } else {
          $data[$key] = $DefaultData[$key];
        }
      }
    }
    $themeVal = $data['myTheme'] == "dark" ? "dark" : "light";
    $themeUpdatedVal = $data['myTheme'] == "dark" ? "dark" : $data['myTheme'];

    // Determine if the layout is admin or front based on template name
    $layoutName = $data['myLayout'];
    $isAdmin = !Str::contains($layoutName, 'front');

    $modeCookieName = $isAdmin ? 'admin-mode' : 'front-mode';
    $colorPrefCookieName = $isAdmin ? 'admin-colorPref' : 'front-colorPref';
    $primaryColorCookieName = $isAdmin ? 'admin-primaryColor' : 'front-primaryColor';

    // Get primary color from custom.php if explicitly set
    $primaryColor = null;
    if (array_key_exists('primaryColor', $data)) {
      $primaryColor = $data['primaryColor'];
    }

    // Check for primary color in cookie
    if (isset($_COOKIE[$primaryColorCookieName])) {
      $primaryColor = $_COOKIE[$primaryColorCookieName];
    }

    // Determine style based on cookies, only if not 'blank-layout'
    if ($layoutName !== 'blank') {
      if (isset($_COOKIE[$modeCookieName])) {
        $themeVal = $_COOKIE[$modeCookieName];
        if ($themeVal === 'system') {
          $themeVal = isset($_COOKIE[$colorPrefCookieName]) ? $_COOKIE[$colorPrefCookieName] : 'light';
        }
        $themeUpdatedVal = $_COOKIE[$modeCookieName];
      }
    }

    // Define standardized cookie names
    $skinCookieName = 'customize_skin';
    $semiDarkCookieName = 'customize_semi_dark';

    // Process skin and semi-dark settings only for admin layouts
    if ($isAdmin) {
      // Get skin from cookie or fall back to config
      $skinFromCookie = isset($_COOKIE[$skinCookieName]) ? $_COOKIE[$skinCookieName] : null;
      $configSkin = isset($data['mySkins']) ? $data['mySkins'] : 'default';
      $skinName = $skinFromCookie ?: $configSkin;

      // Get semi-dark setting from cookie or fall back to config
      $semiDarkFromCookie = isset($_COOKIE[$semiDarkCookieName]) ? $_COOKIE[$semiDarkCookieName] : null;
      // Ensure we have a proper boolean conversion
      $semiDarkEnabled = $semiDarkFromCookie !== null ?
        filter_var($semiDarkFromCookie, FILTER_VALIDATE_BOOLEAN) :
        (bool)$data['hasSemiDark'];
    } else {
      // For front-end layouts, use defaults
      $skinName = 'default';
      $semiDarkEnabled = false;
    }

    // Get menu Collapsed state from cookie or fall back to config
    $menuCollapsedFromCookie = isset($_COOKIE['LayoutCollapsed']) ? $_COOKIE['LayoutCollapsed'] : $data['menuCollapsed'];

    // Get content layout from cookie or fall back to config
    $contentLayoutFromCookie = isset($_COOKIE['contentLayout']) ? $_COOKIE['contentLayout'] : $data['contentLayout'];

    // Get header type from cookie or fall back to config
    $navbarTypeFromCookie = isset($_COOKIE['navbarType']) ? $_COOKIE['navbarType'] : $data['navbarType'];

    // Get Header type from cookie or fall back to config
    $headerTypeFromCookie = isset($_COOKIE['headerType']) ? $_COOKIE['headerType'] : $data['headerType'];

    $directionVal = isset($_COOKIE['direction']) ? ($_COOKIE['direction'] === 'true' ? 'rtl' : 'ltr') : $data['myRTLMode'];

    //layout classes
    $layoutClasses = [
      'layout' => $data['myLayout'],
      'skins' => $data['mySkins'],
      'skinName' => $skinName,
      'semiDark' => $semiDarkEnabled,
      'color' => $primaryColor,
      'theme' => $themeVal,
      'themeOpt' => $data['myTheme'],
      'themeOptVal' => $themeUpdatedVal,
      'rtlMode' => $data['myRTLMode'],
      'textDirection' => $directionVal,
      'menuCollapsed' => $menuCollapsedFromCookie,
      'hasCustomizer' => $data['hasCustomizer'],
      'showDropdownOnHover' => $data['showDropdownOnHover'],
      'displayCustomizer' => $data['displayCustomizer'],
      'contentLayout' => $contentLayoutFromCookie,
      'headerType' => $headerTypeFromCookie,
      'navbarType' => $navbarTypeFromCookie,
      'menuFixed' => $data['menuFixed'],
      'footerFixed' => $data['footerFixed'],
      'customizerControls' => $data['customizerControls'],
      'menuAttributes' => self::getMenuAttributes($semiDarkEnabled),
    ];

    // sidebar Collapsed
    if ($layoutClasses['menuCollapsed'] === 'true' || $layoutClasses['menuCollapsed'] === true) {
      $layoutClasses['menuCollapsed'] = 'layout-menu-collapsed';
    } else {
      $layoutClasses['menuCollapsed'] = '';
    }

    // Header Type
    if ($layoutClasses['headerType'] == 'fixed') {
      $layoutClasses['headerType'] = 'layout-menu-fixed';
    }
    // Navbar Type
    if ($layoutClasses['navbarType'] == 'sticky') {
      $layoutClasses['navbarType'] = 'layout-navbar-fixed';
    } elseif ($layoutClasses['navbarType'] == 'static') {
      $layoutClasses['navbarType'] = '';
    } else {
      $layoutClasses['navbarType'] = 'layout-navbar-hidden';
    }

    // Menu Fixed
    if ($layoutClasses['menuFixed'] == true) {
      $layoutClasses['menuFixed'] = 'layout-menu-fixed';
    }


    // Footer Fixed
    if ($layoutClasses['footerFixed'] == true) {
      $layoutClasses['footerFixed'] = 'layout-footer-fixed';
    }

    // RTL Layout/Mode
    if ($layoutClasses['rtlMode'] == true) {
      $layoutClasses['rtlMode'] = 'rtl';
      $layoutClasses['textDirection'] = isset($_COOKIE['direction']) ? ($_COOKIE['direction'] === 'true' ? 'rtl' : 'ltr') : 'rtl';
    } else {
      $layoutClasses['rtlMode'] = 'ltr';
      $layoutClasses['textDirection'] = isset($_COOKIE['direction']) && $_COOKIE['direction'] === 'true' ? 'rtl' : 'ltr';
    }

    // Show DropdownOnHover for Horizontal Menu
    if ($layoutClasses['showDropdownOnHover'] == true) {
      $layoutClasses['showDropdownOnHover'] = true;
    } else {
      $layoutClasses['showDropdownOnHover'] = false;
    }

    // To hide/show display customizer UI, not js
    if ($layoutClasses['displayCustomizer'] == true) {
      $layoutClasses['displayCustomizer'] = true;
    } else {
      $layoutClasses['displayCustomizer'] = false;
    }

    return $layoutClasses;
  }

  public static function updatePageConfig($pageConfigs)
  {
    $demo = 'custom';
    if (isset($pageConfigs)) {
      if (count($pageConfigs) > 0) {
        foreach ($pageConfigs as $config => $val) {
          Config::set('custom.' . $demo . '.' . $config, $val);
        }
      }
    }
  }

    /**
   * Insert a new AI trade for a user with given amount.
   *
   * @param int $user_id
   * @param float $amount_invest
   * @return int|bool Inserted trade ID or false on failure
   */
  public static function insertAiTrade($user_id, $amount_invest)
  {
    $ending_amount = $amount_invest * 5;
    return DB::table('ai_trades')->insertGetId([
      'user_id' => $user_id,
      'amount_invest' => $amount_invest,
      'ending_amount' => $ending_amount,
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }

  /**
   * Generate CSS for primary color
   *
   * @param string $color Hex color code for primary color
   * @return string CSS for primary color
   */
  public static function generatePrimaryColorCSS($color)
  {
    if (!$color) return '';

    // Check if the color actually came from a cookie or explicit configuration
    // Don't generate CSS if there's no specific need for a custom color
    $configColor = config('custom.custom.primaryColor', null);
    $isFromCookie = isset($_COOKIE['admin-primaryColor']) || isset($_COOKIE['front-primaryColor']);

    if (!$configColor && !$isFromCookie) return '';

    $r = hexdec(substr($color, 1, 2));
    $g = hexdec(substr($color, 3, 2));
    $b = hexdec(substr($color, 5, 2));

    // Calculate contrast color based on YIQ formula
    $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    $contrastColor = ($yiq >= 150) ? '#000' : '#fff';

    return <<<CSS
:root, [data-bs-theme=light], [data-bs-theme=dark] {
  --bs-primary: {$color};
  --bs-primary-rgb: {$r}, {$g}, {$b};
  --bs-primary-bg-subtle: rgba({$r}, {$g}, {$b}, 0.1);
  --bs-primary-border-subtle: rgba({$r}, {$g}, {$b}, 0.3);
  --bs-primary-contrast: {$contrastColor};
}
CSS;
  }

  /**
   * Process deposit promo bonus for a user deposit
   *
   * @param int $user_id
   * @param float $deposit_amount
   * @param string|null $deposit_id Optional deposit ID to prevent duplicate processing
   * @return array Result with bonus information or null if no active campaign or minimum deposit not met
   */
  public static function processDepositPromoBonus($user_id, $deposit_amount, $deposit_id = null)
  {
    // Check minimum deposit amount requirement
    if ($deposit_amount < 1000) {
      return null;
    }

    // Get active campaign
    $campaign = \App\Models\DepositPromoCampaign::getActiveCampaign();

    if (!$campaign) {
      return null;
    }

    // Check if bonus has already been processed for this user and deposit amount
    // to prevent duplicate processing during status refreshes
    if ($deposit_id) {
      // For manual processing (starts with 'manual_'), allow processing but log it
      if (strpos($deposit_id, 'manual_') === 0) {
        Log::info('Manual deposit promo bonus processing requested', [
          'user_id' => $user_id,
          'deposit_amount' => $deposit_amount,
          'manual_id' => $deposit_id
        ]);
      } else {
        // For regular deposits, check for duplicates
        $existingBonus = \App\Models\DepositPromoBonusHistory::where('user_id', $user_id)
          ->where('deposit_amount', $deposit_amount)
          ->where('campaign_id', $campaign->id)
          ->where('created_at', '>=', now()->subHours(24)) // Within last 24 hours
          ->first();

        if ($existingBonus) {
          Log::info('Deposit promo bonus already processed, skipping duplicate', [
            'user_id' => $user_id,
            'deposit_amount' => $deposit_amount,
            'deposit_id' => $deposit_id,
            'existing_bonus_id' => $existingBonus->id
          ]);
          return null;
        }
      }
    }

    // Calculate bonus amounts
    $userBonusAmount = ($deposit_amount * $campaign->percentage_bonus) / 100;
    $uplineBonusAmount = ($userBonusAmount * $campaign->percentage_upline_bonus) / 100;

    // Set expiration to campaign's end_datetime
    $expiresAt = $campaign->end_datetime;

    // Create bonus history record with deposit reference
    $historyData = [
      'user_id' => $user_id,
      'campaign_id' => $campaign->id,
      'deposit_amount' => $deposit_amount,
      'user_bonus_amount' => $userBonusAmount,
      'upline_bonus_amount' => $uplineBonusAmount,
      'status' => \App\Models\DepositPromoBonusHistory::STATUS_PENDING,
      'expires_at' => $expiresAt
    ];

    // Add deposit reference if provided
    if ($deposit_id) {
      $historyData['deposit_reference'] = (string)$deposit_id;
    }

    $bonusHistory = \App\Models\DepositPromoBonusHistory::create($historyData);

    // Process upline bonus if applicable
    if ($uplineBonusAmount > 0) {
        $user = User::find($user_id);
        if ($user && $user->upline_id) {
            $uplineUser = User::find($user->upline_id);
            if ($uplineUser) {

            // Create upline bonus history record with deposit reference
            $uplineHistoryData = [
              'user_id' => $uplineUser->id,
              'campaign_id' => $campaign->id,
              'deposit_amount' => $deposit_amount,
              'user_bonus_amount' => $uplineBonusAmount,
              'upline_bonus_amount' => $uplineBonusAmount,
              'status' => \App\Models\DepositPromoBonusHistory::STATUS_PENDING,
              'expires_at' => $expiresAt
            ];

            // Add deposit reference if provided
            if ($deposit_id) {
              $uplineHistoryData['deposit_reference'] = (string)$deposit_id . '_upline';
            }

            $bonusHistory2 = \App\Models\DepositPromoBonusHistory::create($uplineHistoryData);
            }
        }
    }

    return [
      'campaign' => $campaign,
      'bonus_history' => $bonusHistory,
      'user_bonus_amount' => $userBonusAmount,
      'upline_bonus_amount' => $uplineBonusAmount,
      'expires_at' => $expiresAt
    ];
  }
}
