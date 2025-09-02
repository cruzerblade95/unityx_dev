<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\WithdrawalRequest;
use App\Models\Withdrawal;
use App\Models\WithdrawalSettings;
use App\Models\KycVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    public function index()
    {
        $user = Auth::user();

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

        // Get wallet profit balance (only profit balance is available for withdrawal)
        $walletBalance = $user->getWithdrawableBalance();

        return view('user.content.withdrawal.index', compact('withdrawals', 'isKycApproved', 'walletBalance', 'user', 'pendingWithdrawal'));
    }

    public function crypto()
    {
        $user = Auth::user();

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

        // Get wallet profit balance (only profit balance is available for withdrawal)
        $walletBalance = $user->getWithdrawableBalance();

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

    public function etcBlack()
    {
        $user = Auth::user();

        // Check if payment information is completed
        if (!$user->payment_info_completed) {
            return redirect()->route('user.payment-info.index')
                ->with('warning', 'Please complete your payment information to access withdrawal features.');
        }

        // Check if withdrawal is enabled for this user
        if (!WithdrawalSettings::isWithdrawalEnabled(Auth::id(), 'etc_black')) {
            return redirect()->route('user.withdrawal.index')
                ->with('error', 'ETC Black withdrawals are currently disabled for your account.');
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

        // Get wallet profit balance (only profit balance is available for withdrawal)
        $walletBalance = $user->getWithdrawableBalance();

        // Get withdrawal settings for display
        $withdrawalSettings = WithdrawalSettings::getUserSettings(Auth::id(), 'etc_black');

        return view('user.content.withdrawal.etc-black', compact(
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

        // Get wallet profit balance (only profit balance is available for withdrawal)
        $walletBalance = $user->getWithdrawableBalance();

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

        // Check if payment information is completed
        if (!$user->payment_info_completed) {
            return redirect()->route('user.payment-info.index')
                ->with('warning', 'Please complete your payment information to access withdrawal features.');
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

        // Check if user has sufficient withdrawable balance (profit - floating)
        $withdrawableBalance = $user->getWithdrawableBalance();
        if ($withdrawableBalance < $request->amount) {
            return redirect()->back()->withErrors(['amount' => 'Insufficient withdrawable balance. Available: $' . number_format($withdrawableBalance, 2)]);
        }

        // Calculate processing fee using settings
        $processingFee = WithdrawalSettings::getProcessingFee($user->id, $request->withdrawal_type, $request->amount);
        $net_amount = $request->amount - $processingFee;

        // Move the requested amount to floating (hold it)
        if ($wallet && !$wallet->holdAmount($request->amount)) {
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

    public function selectBranch(Request $request, $withdrawalId)
    {
        $user = Auth::user();
        $withdrawal = Withdrawal::where('id', $withdrawalId)
            ->where('user_id', $user->id)
            ->where('withdrawal_type', 'gold')
            ->where('status', 'approved')
            ->where('branch_selected', false)
            ->firstOrFail();

        $validBranches = [
            'SHAH ALAM',
            'BANGI',
            'BANTING',
            'ALOR SETAR',
            'JELAWAT',
            'KOTA TINGGI',
            'BANDAR BARU UDA'
        ];

        $request->validate([
            'branch_name' => 'required|in:' . implode(',', $validBranches)
        ]);

        $withdrawal->update([
            'branch_name' => $request->branch_name,
            'branch_selected' => true,
        ]);

        return redirect()->back()->with('success', 'Branch selected successfully! Your gold withdrawal is ready for pickup at Musleem Gold - ' . $request->branch_name);
    }
}
