<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        // Get total users count
        $totalUsers = User::count();

        // Get active sessions count
        $activeSessions = DB::table('sessions')->count();

        // Get withdrawal statistics
        $totalWithdrawals = Withdrawal::count();
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $approvedWithdrawals = Withdrawal::where('status', 'approved')->count();
        $totalWithdrawalAmount = Withdrawal::sum('amount');
        $todayWithdrawals = Withdrawal::whereDate('created_at', Carbon::today())->count();

        // Create sample recent activity (you can replace this with real activity data)
        $recentActivity = collect([
            (object)[
                'icon' => 'user',
                'title' => 'New User Registration',
                'description' => 'A new user has registered.',
                'time' => Carbon::now()->subHours(2)->format('h:i A')
            ],
            (object)[
                'icon' => 'login',
                'title' => 'Admin Login',
                'description' => 'Admin logged in successfully.',
                'time' => Carbon::now()->subHours(1)->format('h:i A')
            ],
            (object)[
                'icon' => 'settings',
                'title' => 'System Update',
                'description' => 'System settings were updated.',
                'time' => Carbon::now()->subMinutes(30)->format('h:i A')
            ]
        ]);

        return view('admin.dashboard', compact(
            'totalUsers', 
            'activeSessions', 
            'recentActivity',
            'totalWithdrawals',
            'pendingWithdrawals',
            'approvedWithdrawals',
            'totalWithdrawalAmount',
            'todayWithdrawals'
        ));
    }

    public function debug()
    {
        $admin = Auth::guard('admin')->user();
        
        // Add debug logging
        Log::info('Debug page accessed');
        Log::info('Auth check result:', ['is_logged_in' => Auth::guard('admin')->check()]);
        Log::info('Admin user:', ['admin' => $admin]);
        
        $data = [
            'is_logged_in' => Auth::guard('admin')->check(),
            'guard_name' => 'admin',
            'user_data' => $admin ? [
                'id' => $admin->id,
                'username' => $admin->username,
                'email' => $admin->email,
                'created_at' => $admin->created_at ? $admin->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $admin->updated_at ? $admin->updated_at->format('Y-m-d H:i:s') : null,
            ] : null,
            'session_data' => session()->all(),
            'request_info' => [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'method' => request()->method(),
                'url' => request()->url(),
            ]
        ];

        // Debug the data being passed to view
        Log::info('Debug data:', $data);

        return view('admin.debug', compact('data'));
    }
}
