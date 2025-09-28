<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\WithdrawalRequest;
use App\Models\Withdrawal;
use App\Models\WithdrawalSettings;
use App\Models\SystemSettings;
use App\Models\KycVerification;
use App\Models\TelegramVerification;
use App\Models\WithdrawalOtp;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class WithdrawalController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Check if withdrawal system is in maintenance mode
        if (SystemSettings::isWithdrawalMaintenanceMode()) {
            $maintenanceMessage = SystemSettings::getWithdrawalMaintenanceMessage();
            return view('user.content.withdrawal.maintenance', compact('maintenanceMessage'));
        }

        // Check if payment information is completed
        if (!$user->payment_info_completed) {
            return redirect()->route('user.payment-info.index')
                ->with('warning', 'Please complete your payment information to access withdrawal features.');
        }

        $withdrawals = Withdrawal::where('user_id', Auth::id())->latest()->get();

        // Check for pending withdrawal
        $pendingWithdrawal = Withdrawal::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        // Check KYC status
        $kycStatus = $user->kycVerification ? $user->kycVerification->kyc_status : KycVerification::STATUS_NOT_SUBMITTED;
        $isKycApproved = $kycStatus === KycVerification::STATUS_APPROVED;

        // Get wallet withdrawable balance (profit + bonus balance is available for withdrawal)
        $walletBalance = $user->getWithdrawableBalance();

        return view('user.content.withdrawal.index', compact('withdrawals', 'isKycApproved', 'walletBalance', 'user', 'pendingWithdrawal'));
    }

    public function crypto()
    {
        $user = Auth::user();

        // Check if withdrawal system is in maintenance mode
        if (SystemSettings::isWithdrawalMaintenanceMode()) {
            $maintenanceMessage = SystemSettings::getWithdrawalMaintenanceMessage();
            return view('user.content.withdrawal.maintenance', compact('maintenanceMessage'));
        }

        // Check if payment information is completed
        if (!$user->payment_info_completed) {
            return redirect()->route('user.payment-info.index')
                ->with('warning', 'Please complete your payment information to access withdrawal features.');
        }

        // Check if withdrawal is enabled for this user
        if (!WithdrawalSettings::isWithdrawalEnabled(Auth::id(), 'crypto')) {
            return redirect()->route('user.withdrawal.index')
                ->with('error', 'Cryptocurrency withdrawals are currently disabled for your account.');
        }

        $withdrawals = Withdrawal::where('user_id', Auth::id())->latest()->get();

        // Check for pending withdrawal
        $pendingWithdrawal = Withdrawal::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        // Check for last withdrawal and calculate countdown using settings
        $lastWithdrawal = Withdrawal::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->latest('created_at')
            ->first();

        $canWithdraw = true;
        $nextWithdrawalTime = null;
        $hoursRemaining = 0;
        $cooldownHours = WithdrawalSettings::getCooldownHours(Auth::id(), 'crypto');

        if ($lastWithdrawal) {
            $lastWithdrawalTime = $lastWithdrawal->created_at;
            $nextWithdrawalTime = $lastWithdrawalTime->addHours($cooldownHours);
            $hoursRemaining = max(0, now()->diffInHours($nextWithdrawalTime, false));

            if ($hoursRemaining > 0) {
                $canWithdraw = false;
            }
        }

        // Check KYC status
        $kycStatus = $user->kycVerification ? $user->kycVerification->kyc_status : KycVerification::STATUS_NOT_SUBMITTED;
        $isKycApproved = $kycStatus === KycVerification::STATUS_APPROVED;

        // Get wallet withdrawable balance for crypto withdrawals
        $walletBalance = $user->getWithdrawableBalanceForWithdrawal('crypto');

        // Get withdrawal settings for display
        $withdrawalSettings = WithdrawalSettings::getUserSettings(Auth::id(), 'crypto');

        return view('user.content.withdrawal.crypto', compact(
            'withdrawals',
            'isKycApproved',
            'walletBalance',
            'user',
            'pendingWithdrawal',
            'canWithdraw',
            'nextWithdrawalTime',
            'hoursRemaining',
            'lastWithdrawal',
            'withdrawalSettings',
            'cooldownHours'
        ));
    }

    public function cryptoBankCard()
    {
        $user = Auth::user();

        // Check if withdrawal system is in maintenance mode
        if (SystemSettings::isWithdrawalMaintenanceMode()) {
            $maintenanceMessage = SystemSettings::getWithdrawalMaintenanceMessage();
            return view('user.content.withdrawal.maintenance', compact('maintenanceMessage'));
        }

        // Check if payment information is completed
        if (!$user->payment_info_completed) {
            return redirect()->route('user.payment-info.index')
                ->with('warning', 'Please complete your payment information to access withdrawal features.');
        }

        // Check if withdrawal is enabled for this user
        if (!WithdrawalSettings::isWithdrawalEnabled(Auth::id(), 'etc_black')) {
            return redirect()->route('user.withdrawal.index')
                ->with('error', 'Crypto Bank Card withdrawals are currently disabled for your account.');
        }

        $withdrawals = Withdrawal::where('user_id', Auth::id())->latest()->get();

        // Check for pending withdrawal
        $pendingWithdrawal = Withdrawal::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        // Check for last withdrawal and calculate countdown using settings
        $lastWithdrawal = Withdrawal::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->latest('created_at')
            ->first();

        $canWithdraw = true;
        $nextWithdrawalTime = null;
        $hoursRemaining = 0;
        $cooldownHours = WithdrawalSettings::getCooldownHours(Auth::id(), 'etc_black');

        if ($lastWithdrawal) {
            $lastWithdrawalTime = $lastWithdrawal->created_at;
            $nextWithdrawalTime = $lastWithdrawalTime->addHours($cooldownHours);
            $hoursRemaining = max(0, now()->diffInHours($nextWithdrawalTime, false));

            if ($hoursRemaining > 0) {
                $canWithdraw = false;
            }
        }

        // Check KYC status
        $kycStatus = $user->kycVerification ? $user->kycVerification->kyc_status : KycVerification::STATUS_NOT_SUBMITTED;
        $isKycApproved = $kycStatus === KycVerification::STATUS_APPROVED;

        // Get wallet withdrawable balance for bank card withdrawals
        $walletBalance = $user->getWithdrawableBalanceForWithdrawal('etc_black');

        // Get withdrawal settings for display
        $withdrawalSettings = WithdrawalSettings::getUserSettings(Auth::id(), 'etc_black');

        return view('user.content.withdrawal.crypto-bank-card', compact(
            'withdrawals',
            'isKycApproved',
            'walletBalance',
            'user',
            'pendingWithdrawal',
            'canWithdraw',
            'nextWithdrawalTime',
            'hoursRemaining',
            'lastWithdrawal',
            'withdrawalSettings',
            'cooldownHours'
        ));
    }

    public function gold()
    {
        $user = Auth::user();

        // Check if withdrawal system is in maintenance mode
        if (SystemSettings::isWithdrawalMaintenanceMode()) {
            $maintenanceMessage = SystemSettings::getWithdrawalMaintenanceMessage();
            return view('user.content.withdrawal.maintenance', compact('maintenanceMessage'));
        }

        // Check if payment information is completed
        if (!$user->payment_info_completed) {
            return redirect()->route('user.payment-info.index')
                ->with('warning', 'Please complete your payment information to access withdrawal features.');
        }

        // Check if withdrawal is enabled for this user
        if (!WithdrawalSettings::isWithdrawalEnabled(Auth::id(), 'gold')) {
            return redirect()->route('user.withdrawal.index')
                ->with('error', 'Gold withdrawals are currently disabled for your account.');
        }

        $withdrawals = Withdrawal::where('user_id', Auth::id())->latest()->get();

        // Check for pending withdrawal
        $pendingWithdrawal = Withdrawal::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        // Check for last withdrawal and calculate countdown using settings
        $lastWithdrawal = Withdrawal::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->latest('created_at')
            ->first();

        $canWithdraw = true;
        $nextWithdrawalTime = null;
        $hoursRemaining = 0;
        $cooldownHours = WithdrawalSettings::getCooldownHours(Auth::id(), 'gold');

        if ($lastWithdrawal) {
            $lastWithdrawalTime = $lastWithdrawal->created_at;
            $nextWithdrawalTime = $lastWithdrawalTime->addHours($cooldownHours);
            $hoursRemaining = max(0, now()->diffInHours($nextWithdrawalTime, false));

            if ($hoursRemaining > 0) {
                $canWithdraw = false;
            }
        }

        // Check KYC status
        $kycStatus = $user->kycVerification ? $user->kycVerification->kyc_status : KycVerification::STATUS_NOT_SUBMITTED;
        $isKycApproved = $kycStatus === KycVerification::STATUS_APPROVED;

        // Get wallet withdrawable balance for gold withdrawals
        $walletBalance = $user->getWithdrawableBalanceForWithdrawal('gold');

        // Get withdrawal settings for display
        $withdrawalSettings = WithdrawalSettings::getUserSettings(Auth::id(), 'gold');

        return view('user.content.withdrawal.gold', compact(
            'withdrawals',
            'isKycApproved',
            'walletBalance',
            'user',
            'pendingWithdrawal',
            'canWithdraw',
            'nextWithdrawalTime',
            'hoursRemaining',
            'lastWithdrawal',
            'withdrawalSettings',
            'cooldownHours'
        ));
    }

    public function store(WithdrawalRequest $request)
    {
        $user = Auth::user();

        // Check if withdrawal system is in maintenance mode
        if (SystemSettings::isWithdrawalMaintenanceMode()) {
            return redirect()->route('user.withdrawal.index')
                ->with('error', SystemSettings::getWithdrawalMaintenanceMessage());
        }

        // Check if payment information is completed
        if (!$user->payment_info_completed) {
            return redirect()->route('user.payment-info.index')
                ->with('warning', 'Please complete your payment information to access withdrawal features.');
        }

        // For crypto and crypto bank card withdrawals, check Telegram verification first
        if (in_array($request->withdrawal_type, ['crypto', 'etc_black'])) {
            // Check if user has verified Telegram
            $telegramVerification = \App\Models\TelegramVerification::where('user_id', $user->id)->first();

            if (!$telegramVerification || !$telegramVerification->telegram_username) {
                return response()->json([
                    'success' => false,
                    'error' => 'telegram_not_verified',
                    'message' => 'Please verify your Telegram account in your profile first.'
                ], 422);
            }
        }

        // Validate withdrawal is enabled for this type
        if (!WithdrawalSettings::isWithdrawalEnabled($user->id, $request->withdrawal_type)) {
            return redirect()->back()->withErrors([
                'withdrawal_disabled' => ucfirst(str_replace('_', ' ', $request->withdrawal_type)) . ' withdrawals are currently disabled for your account.'
            ]);
        }

        // Validate withdrawal amount limits
        $amountValidation = WithdrawalSettings::validateWithdrawalAmount($user->id, $request->withdrawal_type, $request->amount);
        if (!$amountValidation['valid']) {
            return redirect()->back()->withErrors(['amount' => $amountValidation['message']]);
        }

        // Check cooldown period
        $cooldownHours = WithdrawalSettings::getCooldownHours($user->id, $request->withdrawal_type);
        $lastWithdrawal = Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->latest('created_at')
            ->first();

        if ($lastWithdrawal) {
            $nextWithdrawalTime = $lastWithdrawal->created_at->addHours($cooldownHours);
            $hoursRemaining = max(0, now()->diffInHours($nextWithdrawalTime, false));

            if ($hoursRemaining > 0) {
                return redirect()->back()->withErrors([
                    'cooldown' => "You must wait {$hoursRemaining} more hours before making another withdrawal."
                ]);
            }
        }

        // Check if user has any pending withdrawal requests
        $pendingWithdrawal = Withdrawal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingWithdrawal) {
            return redirect()->back()->withErrors([
                'withdrawal_limit' => 'You already have a pending withdrawal request (#' . $pendingWithdrawal->id . '). Please wait for it to be processed before submitting a new request.'
            ]);
        }

        $wallet = $user->wallet;

        // Validate wallet type
        $walletType = $request->wallet_type;
        if (!in_array($walletType, ['profit', 'bonus'])) {
            return redirect()->back()->withErrors(['wallet_type' => 'Please select a valid wallet type.']);
        }

        // Check if user has sufficient withdrawable balance for the selected wallet type
        $withdrawableBalance = $wallet->getAvailableBalance($walletType);
        if ($withdrawableBalance < $request->amount) {
            $walletName = ucfirst($walletType . ' wallet');
            return redirect()->back()->withErrors(['amount' => "Insufficient balance in {$walletName}. Available: $" . number_format($withdrawableBalance, 2)]);
        }

        // Calculate processing fee using settings
        $processingFee = WithdrawalSettings::getProcessingFee($user->id, $request->withdrawal_type, $request->amount);
        $net_amount = $request->amount - $processingFee;

        // Move the requested amount to floating (hold it) from the selected wallet type
        if ($wallet && !$wallet->holdAmountFromWalletForWithdrawal($request->amount, $walletType, $request->withdrawal_type)) {
            return redirect()->back()->withErrors(['amount' => 'Failed to hold withdrawal amount. Please try again.']);
        }

        // Prepare data for withdrawal creation
        $withdrawalData = [
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'tax_percent' => 0, // Will be calculated from settings
            'tax_amount' => $processingFee,
            'net_amount' => $net_amount,
            'status' => 'pending',
            'reference' => $request->reference,
            'note' => $request->note,
            'withdrawal_type' => $request->withdrawal_type,
            'wallet_type' => $walletType,
            'payment_details' => $this->getPaymentDetails($request, $user),
        ];

        // Get withdrawal settings to calculate proper tax percent for display
        $settings = WithdrawalSettings::getUserSettings($user->id, $request->withdrawal_type);
        if ($settings) {
            $withdrawalData['tax_percent'] = $settings->processing_fee_percent;
        }

        // For crypto withdrawals, store currency and wallet address
        if ($request->withdrawal_type === 'crypto' && $request->currency && $request->wallet_address) {
            $withdrawalData['bank_name'] = $request->currency; // Store currency in bank_name
            $withdrawalData['account_number'] = $request->wallet_address; // Store wallet address
        }

        // For gold withdrawals, store selected branch
        if ($request->withdrawal_type === 'gold' && $request->branch_name) {
            $withdrawalData['branch_name'] = $request->branch_name;
        }

        Withdrawal::create($withdrawalData);

        $taxPercent = $settings ? $settings->processing_fee_percent : 4.75;
        return redirect()->back()->with('success', 'Withdrawal request submitted successfully! Net amount after ' . $taxPercent . '% fee: $' . number_format($net_amount, 2));
    }

    private function getPaymentDetails($request, $user)
    {
        switch ($request->withdrawal_type) {
            case 'crypto':
                $paymentDetails = [
                    'type' => 'crypto',
                    'currency' => $request->currency ?? '',
                    'wallet_address' => $request->wallet_address ?? '',
                ];

                // Add available wallet addresses for reference
                if ($user->usdt_bep20_address) {
                    $paymentDetails['usdt_bep20_address'] = $user->usdt_bep20_address;
                }
                if ($user->usdc_bep20_address) {
                    $paymentDetails['usdc_bep20_address'] = $user->usdc_bep20_address;
                }
                if ($user->fdusd_bep20_address) {
                    $paymentDetails['fdusd_bep20_address'] = $user->fdusd_bep20_address;
                }

                return $paymentDetails;

            case 'etc_black':
                return [
                    'type' => 'etc_black',
                    'etc_black_card_number' => $user->etc_black_card_number,
                    'account_number' => $user->account_number,
                    'cardholder_name' => $user->cardholder_name,
                ];

            case 'gold':
                return [
                    'type' => 'gold',
                    'national_id' => $user->national_id,
                    'branch_name' => $request->branch_name ?? '',
                ];

            default:
                return [];
        }
    }

    public function sendOtp(Request $request)
    {
        $user = Auth::user();

        // Check if withdrawal type requires OTP (crypto or crypto bank card)
        if (!in_array($request->withdrawal_type, ['crypto', 'etc_black'])) {
            return response()->json([
                'success' => false,
                'message' => 'OTP is only required for crypto and crypto bank card withdrawals.'
            ], 422, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        // Check if user has verified Telegram
        $telegramVerification = \App\Models\TelegramVerification::where('user_id', $user->id)->first();

        if (!$telegramVerification || !$telegramVerification->telegram_username) {
            return response()->json([
                'success' => false,
                'error' => 'telegram_not_verified',
                'message' => 'Please verify your Telegram account in your profile first.'
            ], 422, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        // Check for existing pending OTP
        $existingOtp = \App\Models\WithdrawalOtp::where('user_id', $user->id)
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();

        if ($existingOtp) {
            return response()->json([
                'success' => false,
                'message' => 'An OTP has already been sent. Please check your Telegram or wait for it to expire.',
                'expires_at' => $existingOtp->expires_at->toISOString()
            ], 422, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        // Generate and send OTP
        $otpModel = new \App\Models\WithdrawalOtp();
        $otp = $otpModel->generateOtp($user->id, $request->all());

        try {
            $result = \App\Services\TelegramNotificationService::sendOtpToTelegram($user, $otp);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send OTP. Please check your Telegram verification status.'
                ], 500, ['Content-Type' => 'application/json; charset=utf-8']);
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your Telegram. Please enter the 6-digit code to complete withdrawal.',
                'expires_at' => $otp->expires_at->toISOString()
            ], 200, ['Content-Type' => 'application/json; charset=utf-8']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ], 500, ['Content-Type' => 'application/json; charset=utf-8']);
        }
    }

    public function verifyOtp(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        // Verify OTP
        $otpModel = new \App\Models\WithdrawalOtp();
        $verification = $otpModel->verifyOtp($user->id, $request->otp);

        if (!$verification['valid']) {
            return response()->json([
                'success' => false,
                'message' => $verification['message']
            ], 422);
        }

        // Get the withdrawal data from OTP record
        $withdrawalData = $verification['data'];

        // Recreate the request object with stored data
        $withdrawalRequest = new WithdrawalRequest();
        $withdrawalRequest->merge($withdrawalData);

        // Validate withdrawal is enabled for this type
        if (!WithdrawalSettings::isWithdrawalEnabled($user->id, $withdrawalRequest->withdrawal_type)) {
            return response()->json([
                'success' => false,
                'message' => ucfirst(str_replace('_', ' ', $withdrawalRequest->withdrawal_type)) . ' withdrawals are currently disabled for your account.'
            ], 422);
        }

        // Check if user has any pending withdrawal requests
        $pendingWithdrawal = Withdrawal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingWithdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending withdrawal request (#' . $pendingWithdrawal->id . '). Please wait for it to be processed before submitting a new request.'
            ], 422);
        }

        $wallet = $user->wallet;

        // Validate wallet type
        $walletType = $withdrawalRequest->wallet_type;
        if (!in_array($walletType, ['profit', 'bonus'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a valid wallet type.'
            ], 422);
        }

        // Check if user has sufficient withdrawable balance for the selected wallet type
        $withdrawableBalance = $wallet->getAvailableBalance($walletType);
        if ($withdrawableBalance < $withdrawalRequest->amount) {
            $walletName = ucfirst($walletType . ' wallet');
            return response()->json([
                'success' => false,
                'message' => "Insufficient balance in {$walletName}. Available: $" . number_format($withdrawableBalance, 2)
            ], 422);
        }

        // Calculate processing fee using settings
        $processingFee = WithdrawalSettings::getProcessingFee($user->id, $withdrawalRequest->withdrawal_type, $withdrawalRequest->amount);
        $net_amount = $withdrawalRequest->amount - $processingFee;

        // Move the requested amount to floating (hold it) from the selected wallet type
        if ($wallet && !$wallet->holdAmountFromWalletForWithdrawal($withdrawalRequest->amount, $walletType, $withdrawalRequest->withdrawal_type)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to hold withdrawal amount. Please try again.'
            ], 422);
        }

        // Prepare data for withdrawal creation
        $withdrawalCreateData = [
            'user_id' => Auth::id(),
            'amount' => $withdrawalRequest->amount,
            'tax_percent' => 0, // Will be calculated from settings
            'tax_amount' => $processingFee,
            'net_amount' => $net_amount,
            'status' => 'pending',
            'reference' => $withdrawalRequest->reference,
            'note' => $withdrawalRequest->note,
            'withdrawal_type' => $withdrawalRequest->withdrawal_type,
            'wallet_type' => $walletType,
            'payment_details' => $this->getPaymentDetails($withdrawalRequest, $user),
        ];

        // Get withdrawal settings to calculate proper tax percent for display
        $settings = WithdrawalSettings::getUserSettings($user->id, $withdrawalRequest->withdrawal_type);
        if ($settings) {
            $withdrawalCreateData['tax_percent'] = $settings->processing_fee_percent;
        }

        // For crypto withdrawals, store currency and wallet address
        if ($withdrawalRequest->withdrawal_type === 'crypto' && $withdrawalRequest->currency && $withdrawalRequest->wallet_address) {
            $withdrawalCreateData['bank_name'] = $withdrawalRequest->currency; // Store currency in bank_name
            $withdrawalCreateData['account_number'] = $withdrawalRequest->wallet_address; // Store wallet address
        }

        Withdrawal::create($withdrawalCreateData);

        // Delete the used OTP
        \App\Models\WithdrawalOtp::where('user_id', $user->id)->delete();

        $taxPercent = $settings ? $settings->processing_fee_percent : 4.75;

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully! Net amount after ' . $taxPercent . '% fee: $' . number_format($net_amount, 2)
        ]);
    }

    public function receipt($id)
    {
        try {
            $withdrawal = Withdrawal::where('user_id', Auth::id())->findOrFail($id);

            // Validate that this is an approved gold withdrawal with branch assigned
            if ($withdrawal->withdrawal_type !== 'gold') {
                abort(404, 'Receipt not available for this withdrawal type.');
            }

            if ($withdrawal->status !== 'approved' || !$withdrawal->branch_selected) {
                abort(404, 'Receipt only available for approved gold withdrawals with assigned branch.');
            }

            // Get MYR conversion rate once in the controller to avoid database calls in the view
            try {
                $myrRate = WithdrawalSettings::getMyrConversionRate();
            } catch (\Exception $e) {
                Log::error('Error getting MYR rate: ' . $e->getMessage());
                $myrRate = 4.7; // Default fallback
            }

            // Use the admin receipt template but pass user context
            return view('admin.withdrawal.receipt', compact('withdrawal', 'myrRate'));

        } catch (\Exception $e) {
            abort(500, 'Error generating receipt: ' . $e->getMessage());
        }
    }

    /**
     * Get withdrawal history data for DataTables AJAX
     */
    public function historyList(Request $request)
    {
        try {
            // Set proper JSON response headers
            header('Content-Type: application/json');

            $withdrawals = Withdrawal::where('user_id', Auth::id())
                ->select([
                    'id',
                    'amount',
                    'tax_percent',
                    'tax_amount',
                    'net_amount',
                    'status',
                    'withdrawal_type',
                    'wallet_type',
                    'bank_name',
                    'account_number',
                    'reference',
                    'note',
                    'rejection_reason',
                    'branch_name',
                    'branch_selected',
                    'branch_manager_name',
                    'branch_manager_phone',
                    'created_at'
                ]);

            return DataTables::of($withdrawals)
                ->addColumn('type_badge', function ($withdrawal) {
                    if ($withdrawal->withdrawal_type === 'crypto') {
                        return '<span class="badge bg-success type-badge"><i class="fab fa-bitcoin me-1"></i>Crypto</span>';
                    } elseif ($withdrawal->withdrawal_type === 'etc_black') {
                        return '<span class="badge type-badge" style="background-color: rgba(111, 66, 193, 1);"><i class="fas fa-credit-card me-1"></i>Crypto Bank Card</span>';
                    } elseif ($withdrawal->withdrawal_type === 'gold') {
                        return '<span class="badge bg-warning type-badge"><i class="fas fa-coins me-1"></i>Gold</span>';
                    }
                    return '<span class="badge bg-light text-dark"><i class="fas fa-question me-1"></i>Legacy</span>';
                })
                ->addColumn('wallet_badge', function ($withdrawal) {
                    if ($withdrawal->wallet_type === 'profit') {
                        return '<span class="badge bg-primary"><i class="fas fa-chart-line me-1"></i>Profit</span>';
                    } elseif ($withdrawal->wallet_type === 'bonus') {
                        return '<span class="badge bg-info"><i class="fas fa-gift me-1"></i>Bonus</span>';
                    }
                    return '<span class="badge bg-secondary"><i class="fas fa-wallet me-1"></i>General</span>';
                })
                ->addColumn('formatted_amount', function ($withdrawal) {
                    return '$' . number_format($withdrawal->amount, 2);
                })
                ->addColumn('formatted_net_amount', function ($withdrawal) {
                    return '$' . number_format($withdrawal->net_amount, 2);
                })
                ->addColumn('fee_info', function ($withdrawal) {
                    return $withdrawal->tax_percent . '%<br><small>$' . number_format($withdrawal->tax_amount, 2) . '</small>';
                })
                ->addColumn('payment_details', function ($withdrawal) {
                    if ($withdrawal->withdrawal_type === 'crypto') {
                        $bankName = $withdrawal->bank_name ?? 'N/A';
                        $accountNumber = $withdrawal->account_number ? \Illuminate\Support\Str::limit($withdrawal->account_number, 20) : 'N/A';
                        return '<div class="small"><strong>' . $bankName . '</strong><br><span class="font-monospace">' . $accountNumber . '</span></div>';
                    } elseif ($withdrawal->withdrawal_type === 'gold') {
                        if ($withdrawal->branch_name && $withdrawal->branch_selected) {
                            $details = '<strong class="text-success">Branch: ' . $withdrawal->branch_name . '</strong><br>';
                            if ($withdrawal->branch_manager_name) {
                                $details .= 'Manager: ' . $withdrawal->branch_manager_name . '<br>Phone: ' . $withdrawal->branch_manager_phone;
                            }
                            return '<div class="small">' . $details . '</div>';
                        } elseif ($withdrawal->status === 'approved') {
                            return '<em class="text-info">Branch being assigned...</em>';
                        } else {
                            return '<em class="text-muted">Awaiting approval</em>';
                        }
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('status_badge', function ($withdrawal) {
                    if ($withdrawal->status === 'pending') {
                        return '<span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Pending</span>';
                    } elseif ($withdrawal->status === 'approved') {
                        if ($withdrawal->withdrawal_type === 'gold' && !$withdrawal->branch_selected) {
                            return '<span class="badge bg-info"><i class="fas fa-cog me-1"></i>Processing</span>';
                        } else {
                            return '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Approved</span>';
                        }
                    } elseif ($withdrawal->status === 'rejected') {
                        $badge = '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Rejected</span>';
                        if ($withdrawal->rejection_reason) {
                            $badge .= '<br><small class="text-muted mt-1" style="display: block;"><i class="fas fa-info-circle me-1"></i><button type="button" class="btn btn-sm btn-outline-danger mt-1 rejection-btn" data-bs-toggle="modal" data-bs-target="#rejectionModal' . $withdrawal->id . '">View Reason</button></small>';
                        }
                        return $badge;
                    } elseif ($withdrawal->status === 'completed') {
                        return '<span class="badge bg-success"><i class="fas fa-check-double me-1"></i>Completed</span>';
                    }
                    return '<span class="badge bg-secondary">' . ucfirst($withdrawal->status) . '</span>';
                })
                ->addColumn('formatted_date', function ($withdrawal) {
                    return $withdrawal->created_at->format('M j, Y') . '<br>' . $withdrawal->created_at->format('g:i A');
                })
                ->addColumn('actions', function ($withdrawal) {
                    $actions = '';

                    if ($withdrawal->withdrawal_type === 'gold' && $withdrawal->status === 'approved' && $withdrawal->branch_selected) {
                        $actions .= '<a href="' . route('user.withdrawal.receipt', $withdrawal->id) . '" class="btn btn-sm btn-success" target="_blank"><i class="fas fa-receipt me-1"></i>Download Receipt</a>';
                    } elseif ($withdrawal->withdrawal_type === 'gold' && $withdrawal->status === 'approved' && !$withdrawal->branch_selected) {
                        $actions .= '<span class="badge bg-info"><i class="fas fa-clock me-1"></i>Awaiting Branch Assignment</span>';
                    } elseif ($withdrawal->status === 'rejected' && $withdrawal->rejection_reason) {
                        $actions .= '<button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectionModal' . $withdrawal->id . '"><i class="fas fa-exclamation-circle me-1"></i>View Reason</button>';
                    } else {
                        $actions .= '<span class="text-muted small">No actions</span>';
                    }

                    return $actions;
                })
                ->filter(function ($query) use ($request) {
                    // Filter by withdrawal type
                    if ($request->filled('withdrawal_type')) {
                        $query->where('withdrawal_type', $request->withdrawal_type);
                    }

                    // Filter by status
                    if ($request->filled('status')) {
                        $query->where('status', $request->status);
                    }

                    // Filter by wallet type
                    if ($request->filled('wallet_type')) {
                        $query->where('wallet_type', $request->wallet_type);
                    }

                    // Date range filter
                    if ($request->filled('date_from')) {
                        $query->whereDate('created_at', '>=', $request->date_from);
                    }

                    if ($request->filled('date_to')) {
                        $query->whereDate('created_at', '<=', $request->date_to);
                    }
                })
                ->rawColumns(['type_badge', 'wallet_badge', 'fee_info', 'payment_details', 'status_badge', 'formatted_date', 'actions'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error('Error in withdrawal history list: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => intval($request->get('draw', 0)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load withdrawal history: ' . $e->getMessage()
            ], 500);
        }
    }
}
