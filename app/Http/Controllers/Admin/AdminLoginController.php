<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Providers\RouteServiceProvider;

class AdminLoginController extends Controller
{
    // Constructor removed - middleware handled in routes
    public function showLoginForm()
    {
        $pageConfigs = ['myLayout' => 'blank'];
        return view('admin.login', compact('pageConfigs'));
    }

    public function login(Request $request)
    {
        // Log the incoming request data
        Log::info('Login attempt with data:', $request->all());
        
        $login = $request->input('email');
        $password = $request->input('password');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$fieldType => $login, 'password' => $password];
        
        // Log the credentials being used
        Log::info('Attempting login with credentials:', ['fieldType' => $fieldType]);
        
        if (Auth::guard('admin')->attempt($credentials)) {
            Log::info('Login successful');
            return redirect()->intended('/admin/dashboard');
        }
        
        Log::info('Login failed');
        $errorMessage = 'Invalid credentials. Please check your ' . ($fieldType === 'email' ? 'email' : 'username') . ' and password.';
        return back()->withErrors(['email' => $errorMessage])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
