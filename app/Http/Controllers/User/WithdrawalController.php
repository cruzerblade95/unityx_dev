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
        
        // Get wallet balance
        $walletBalance = $user->wallet ? $user->wallet->total_balance : 0;
        
        return view('user.content.withdrawal', compact('withdrawals', 'isKycApproved', 'walletBalance'));
    }

    public function store(WithdrawalRequest $request)
    {
        $user = Auth::user();
        
        $systemTaxPercent = 2.5; // Set your system tax percent here
        $tax_amount = $request->amount * ($systemTaxPercent / 100);
        $net_amount = $request->amount - $tax_amount;
        
        Withdrawal::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'tax_percent' => $systemTaxPercent,
            'tax_amount' => $tax_amount,
            'net_amount' => $net_amount,
            'status' => 'pending',
            'reference' => $request->reference,
            'note' => $request->note,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
        ]);
        
        return redirect()->back()->with('success', 'Withdrawal request submitted successfully!');
    }
}
