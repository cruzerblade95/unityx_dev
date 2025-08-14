<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserLoginController extends Controller
{
    public function showLoginForm()
    {
        $pageConfigs = ['myLayout' => 'blank'];
        return view('user.login', compact('pageConfigs'));
    }

    public function login(Request $request)
    {
        // dd($request->all());
        // Log the incoming request data
        Log::info('Login attempt with data:', $request->all());
        
        $login = $request->input('email-username');
        $password = $request->input('password');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$fieldType => $login, 'password' => $password];
        
        // Log the credentials being used
        Log::info('Attempting login with credentials:', ['fieldType' => $fieldType]);

        if (Auth::guard('web')->attempt($credentials)) {
            Log::info('Login successful');
            
            // Update last login timestamp using DB facade
            $userId = Auth::guard('web')->id();
            DB::table('users')
                ->where('id', $userId)
                ->update(['last_active_at' => now()]);

            return redirect()->route('user.dashboard');
        }
        
        Log::info('Login failed');
        $errorMessage = 'Invalid credentials. Please check your ' . ($fieldType === 'email' ? 'email' : 'username') . ' and password.';
        return back()->withErrors(['email' => $errorMessage])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('user.login');
    }
}
