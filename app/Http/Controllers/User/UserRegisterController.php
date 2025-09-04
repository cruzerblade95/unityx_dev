<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\DB;

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

        // Add security headers to response
        return response()
            ->view('user.register', compact('pageConfigs'))
            ->header('X-Frame-Options', 'DENY')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-XSS-Protection', '1; mode=block');
    }

    public function register(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users|regex:/^\S*$/u',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'referral_code' => 'required|string|size:6',
            'terms' => 'required|accepted',
            'g-recaptcha-response' => 'required|captcha',
        ], [
            'referral_code.required' => 'Referral code is required to register.',
            'referral_code.size' => 'Referral code must be exactly 6 characters.',
            'username.unique' => 'This username is already taken. Please choose another one.',
            'username.regex' => 'Username must not contain spaces.',
            'email.unique' => 'This email is already registered. Please use a different email or try logging in.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 6 characters long.',
            'terms.required' => 'You must agree to the privacy policy and terms.',
            'terms.accepted' => 'You must agree to the privacy policy and terms.',
            'g-recaptcha-response.required' => 'Please complete the CAPTCHA verification.',
            'g-recaptcha-response.captcha' => 'CAPTCHA verification failed. Please try again.',
        ]);

        // Check if referral code exists and is valid
        if ($request->filled('referral_code')) {
            $referralCode = strtoupper(trim($request->referral_code)); // Normalize the code
            $upline = User::where('referral_code', $referralCode)->first();

            if (!$upline) {
                return redirect()->back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'referral_code' => 'The referral code "' . $referralCode . '" was not found. Please check with your referrer for the correct code.'
                    ])
                    ->header('X-Frame-Options', 'DENY')
                    ->header('X-Content-Type-Options', 'nosniff')
                    ->header('X-XSS-Protection', '1; mode=block');
            }

            // Additional check: Make sure the upline account is active
            if (!$upline || $upline->deleted_at) {
                return redirect()->back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'referral_code' => 'The referral code is no longer valid. Please contact your referrer for assistance.'
                    ])
                    ->header('X-Frame-Options', 'DENY')
                    ->header('X-Content-Type-Options', 'nosniff')
                    ->header('X-XSS-Protection', '1; mode=block');
            }
        } else {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'referral_code' => 'Referral code is required to register.'
                ])
                ->header('X-Frame-Options', 'DENY')
                ->header('X-Content-Type-Options', 'nosniff')
                ->header('X-XSS-Protection', '1; mode=block');
        }

        // Generate unique 6-character referral code
        do {
            $newReferralCode = strtoupper(Str::random(6));
        } while (User::where('referral_code', $newReferralCode)->exists());

        // Get upline user (already verified above)
        $referralCode = strtoupper(trim($request->referral_code));
        $upline = User::where('referral_code', $referralCode)->first();
        $uplineId = $upline->id;

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'referral_code' => $newReferralCode,
            'upline_id' => $uplineId,
        ]);

        // Insert default wallet for new user
        // DB::table('wallets')->insert([
        //     'user_id' => $user->id,
        //     'deposit' => 0,
        //     'profit' => 0,
        //     'bonus' => 0,
        //     'cash' => 0,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        Auth::guard('web')->login($user);
        return redirect()->route('connect-telegram')
            ->with('notyf_success', 'Registration successful!')
            ->header('X-Frame-Options', 'DENY')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-XSS-Protection', '1; mode=block');
        // return redirect()->route('user.dashboard')->with('notyf_success', 'Registration successful!');
    }
}
