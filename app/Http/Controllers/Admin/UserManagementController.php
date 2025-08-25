<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'firstname',
            'lastname',
            'username',
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
            ->filterColumn('role', function($query, $keyword) {
                if (!empty($keyword)) {
                    $query->where('role', $keyword);
                }
            })
            ->filterColumn('status', function($query, $keyword) {
                if (!empty($keyword)) {
                    if ($keyword === 'online') {
                        $query->where('last_active_at', '>=', Carbon::now()->subMinutes(15));
                    } else if ($keyword === 'offline') {
                        $query->where(function($q) {
                            $q->whereNull('last_active_at')
                              ->orWhere('last_active_at', '<', Carbon::now()->subMinutes(15));
                        });
                    }
                }
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'role' => 'required|string|in:user,admin'
            ]);

            // Create base username
            $baseUsername = strtolower($validatedData['firstname'] . '.' . $validatedData['lastname']);

            // Check if username exists and append number if needed
            $username = $baseUsername;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            // Generate unique 6-character referral code
            do {
                $newReferralCode = strtoupper(Str::random(6));
            } while (User::where('referral_code', $newReferralCode)->exists());

            // Log the data before creating user
            Log::info('Creating user with data:', [
                'firstname' => $validatedData['firstname'],
                'lastname' => $validatedData['lastname'],
                'username' => $username,
                'email' => $validatedData['email'],
                'role' => $validatedData['role'],
                'referral_code' => $newReferralCode
            ]);

            $user = User::create([
                'firstname' => (string) $validatedData['firstname'],
                'lastname' => (string) $validatedData['lastname'],
                'email' => (string) $validatedData['email'],
                'password' => Hash::make((string) $validatedData['password']),
                'role' => (string) $validatedData['role'],
                'username' => $username,
                'referral_code' => $newReferralCode
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('User creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function profile($id)
    {
        $user = User::with('wallet')->findOrFail($id);
        return view('admin.userProfile', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:user,admin'
        ]);

        $userData = [
            'username' => $request->username,
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
