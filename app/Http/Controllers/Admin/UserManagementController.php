<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class UserManagementController extends Controller
{
    public function index()
    {
        // Get statistics for the dashboard cards
        $totalUsers = User::count();
        $lastWeekUsers = User::where('created_at', '<=', Carbon::now()->subWeek())->count();
        $userGrowth = $lastWeekUsers > 0 
            ? round((($totalUsers - $lastWeekUsers) / $lastWeekUsers) * 100, 1)
            : 100;

        $activeUsers = User::where('last_active_at', '>=', Carbon::now()->subDay())->count();
        $lastWeekActiveUsers = User::where('last_active_at', '>=', Carbon::now()->subWeek())
            ->where('last_active_at', '<', Carbon::now()->subDay())
            ->count();
        $activeUsersGrowth = $lastWeekActiveUsers > 0
            ? round((($activeUsers - $lastWeekActiveUsers) / $lastWeekActiveUsers) * 100, 1)
            : 100;

        $onlineUsers = User::where('last_active_at', '>=', Carbon::now()->subMinutes(15))->count();
        
        $newUsers = User::where('created_at', '>=', Carbon::now()->subWeek())->count();

        return view('admin.userList', compact(
            'totalUsers',
            'userGrowth',
            'activeUsers',
            'activeUsersGrowth',
            'onlineUsers',
            'newUsers'
        ));
    }

    public function list(Request $request)
    {
        $users = User::select([
            'id',
            'name',
            'email',
            'role',
            'created_at',
            'last_active_at',
            'profile_photo_path'
        ]);

        return DataTables::of($users)
            ->addColumn('status', function ($user) {
                return $user->last_active_at && $user->last_active_at >= Carbon::now()->subMinutes(15) ? 'online' : 'offline';
            })
            ->addColumn('actions', function ($user) {
                return '';  // Actions will be handled by JavaScript
            })
            ->editColumn('created_at', function ($user) {
                return $user->created_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:user,admin'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully'
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:user,admin'
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}
