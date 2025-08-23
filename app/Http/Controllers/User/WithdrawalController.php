<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\WithdrawalRequest;
use App\Models\Withdrawal;
use App\Models\KycVerification;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $withdrawals = Withdrawal::where('user_id', Auth::id())->latest()->get();

        // Check KYC status
        $kycStatus = $user->kycVerification ? $user->kycVerification->kyc_status : KycVerification::STATUS_NOT_SUBMITTED;
        $isKycApproved = $kycStatus === KycVerification::STATUS_APPROVED;

        // Get wallet profit balance (only profit balance is available for withdrawal)
        $walletBalance = $user->getWithdrawableBalance();

        return view('user.content.withdrawal', compact('withdrawals', 'isKycApproved', 'walletBalance'));
    }

    public function store(WithdrawalRequest $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        // Check if user has sufficient withdrawable balance (profit - floating)
        $withdrawableBalance = $user->getWithdrawableBalance();
        if ($withdrawableBalance < $request->amount) {
            return redirect()->back()->withErrors(['amount' => 'Insufficient withdrawable balance. Available: $' . number_format($withdrawableBalance, 2)]);
        }

        $systemTaxPercent = 4.75; // Set your system tax percent here
        $tax_amount = $request->amount * ($systemTaxPercent / 100);
        $net_amount = $request->amount - $tax_amount;

        // Move the requested amount to floating (hold it)
        if ($wallet && !$wallet->holdAmount($request->amount)) {
            return redirect()->back()->withErrors(['amount' => 'Failed to hold withdrawal amount. Please try again.']);
        }

        Withdrawal::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'tax_percent' => $systemTaxPercent,
            'tax_amount' => $tax_amount,
            'net_amount' => $net_amount,
            'status' => 'pending',
            'reference' => $request->reference,
            'note' => $request->note,
            'bank_name' => $request->currency, // Store currency in bank_name field
            'account_number' => $request->wallet_address, // Store wallet address in account_number field
        ]);

        return redirect()->back()->with('success', 'Withdrawal request submitted successfully! Net amount after 4.75% fee: $' . number_format($net_amount, 2));
    }
}
