<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserRegisterController extends Controller
{
    public function showRegisterForm()
    {
        $pageConfigs = ['myLayout' => 'blank'];
        return view('user.register', compact('pageConfigs'));
    }

    public function register(Request $request)
    {

        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'referral_code' => 'nullable|string|size:6|exists:users,referral_code',
        ]);

        // Generate unique 6-character referral code
        do {
            $newReferralCode = strtoupper(Str::random(6));
        } while (User::where('referral_code', $newReferralCode)->exists());

        $uplineId = null;
        if ($request->filled('referral_code')) {
            $upline = User::where('referral_code', $request->referral_code)->first();
            if ($upline) {
                $uplineId = $upline->id;
            }
        }

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'referral_code' => $newReferralCode,
            'upline_id' => $uplineId,
        ]);

        Auth::guard('web')->login($user);
        return redirect()->route('connect-telegram')->with('notyf_success', 'Registration successful!');
    }
}
