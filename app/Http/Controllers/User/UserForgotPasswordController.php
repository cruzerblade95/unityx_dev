<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CustomPasswordResetService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserForgotPasswordController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'blank'];
        return view('user.content.forgot-password', ['pageConfigs' => $pageConfigs]);
    }

    /**
     * Handle password reset link request
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $result = CustomPasswordResetService::sendResetLinkNotification($request->email);

        if ($result['success']) {
            return back()->with('status', $result['message']);
        } else {
            return back()->withErrors(['email' => $result['message']])->withInput();
        }
    }

    /**
     * Show password reset form from telegram link
     */
    public function showTelegramResetForm(Request $request)
    {
        $token = $request->route('token');
        $email = $request->get('email');

        $user = CustomPasswordResetService::verifyTelegramToken($token, $email);

        if (!$user) {
            return redirect()->route('user.forgot-password')
                ->withErrors(['email' => 'Invalid or expired reset link.']);
        }

        $pageConfigs = ['myLayout' => 'blank'];
        return view('user.content.reset-password', [
            'pageConfigs' => $pageConfigs,
            'token' => $token,
            'email' => $email,
            'from_telegram' => true
        ]);
    }

    /**
     * Show password reset form from email link
     */
    public function showEmailResetForm(Request $request)
    {
        $token = $request->route('token');
        $email = $request->get('email');

        if (!$token || !$email) {
            return redirect()->route('user.forgot-password')
                ->withErrors(['email' => 'Invalid reset link.']);
        }

        $pageConfigs = ['myLayout' => 'blank'];
        return view('user.content.reset-password', [
            'pageConfigs' => $pageConfigs,
            'token' => $token,
            'email' => $email,
            'from_telegram' => false
        ]);
    }

    /**
     * Handle password reset form submission
     */
    public function resetPassword(Request $request)
    {
        Log::info('Password reset attempt', [
            'request_data' => $request->all(),
            'from_telegram' => $request->boolean('from_telegram', false)
        ]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            Log::error('Password reset validation failed', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        $isFromTelegram = $request->boolean('from_telegram', false);

        $result = CustomPasswordResetService::resetPassword(
            $request->token,
            $request->email,
            $request->password,
            $isFromTelegram,
            $request->ip(),
            $request->userAgent()
        );

        Log::info('Password reset result', $result);

        if ($result['success']) {
            return redirect()->route('user.login')->with('status', $result['message']);
        } else {
            return back()->withErrors(['token' => $result['message']])->withInput();
        }
    }
}
