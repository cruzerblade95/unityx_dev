<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::where('user_id', Auth::id())->latest()->get();
        return view('user.content.withdrawal', compact('withdrawals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);
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
        ]);
        return redirect()->back()->with('success', 'Withdrawal request submitted!');
    }
}
