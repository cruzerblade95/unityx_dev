<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RecoveryAccount;

class AccountRecoveryController extends Controller
{
    public function showEmailForm()
    {
        return view('account_recovery.email_form');
    }    public function validateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->withErrors([
                'email' => 'Email not found in our database. Please register first.'
            ])->withInput();
        }

        // Store email in session for the next step
        session(['recovery_email' => $email]);

        return redirect()->route('account-recovery.details');
    }

    public function showDetailsForm()
    {
        if (!session('recovery_email')) {
            return redirect()->route('account-recovery.email');
        }

        return view('account_recovery.details_form');
    }

    public function submitRecovery(Request $request)
    {
        if (!session('recovery_email')) {
            return redirect()->route('account-recovery.email');
        }

        $request->validate([
            'email' => 'required|email|same:recovery_email',
            'deposit' => 'required|numeric|min:0',
            'profit' => 'required|numeric|min:0',
            'referral_username' => 'nullable|string|max:255',
        ]);

        // Double check email matches session
        if ($request->email !== session('recovery_email')) {
            return redirect()->back()->withErrors([
                'email' => 'Email does not match the one you entered previously.'
            ])->withInput();
        }

        RecoveryAccount::create([
            'email' => $request->email,
            'deposit' => $request->deposit,
            'profit' => $request->profit,
            'referral_username' => $request->referral_username,
            'status' => 'pending',
        ]);

        // Clear session
        session()->forget('recovery_email');

        return redirect()->route('account-recovery.success');
    }

    public function showSuccess()
    {
        return view('account_recovery.success');
    }
}
