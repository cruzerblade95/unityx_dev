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
            'profile_photo_path',
            'is_banned',
            'ban_reason',
            'banned_at'
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
            ->filterColumn('firstname', function($query, $keyword) {
                if (!empty($keyword)) {
                    $query->where(function($q) use ($keyword) {
                        $q->where('firstname', 'like', "%{$keyword}%")
                          ->orWhere('lastname', 'like', "%{$keyword}%")
                          ->orWhere('username', 'like', "%{$keyword}%")
                          ->orWhere('email', 'like', "%{$keyword}%")
                          ->orWhereRaw("CONCAT(firstname, ' ', lastname) like ?", ["%{$keyword}%"]);
                    });
                }
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
                'username' => 'required|string|max:255|unique:users,username|regex:/^\S*$/u',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'password_confirmation' => 'required|string|same:password',
                'upline_id' => 'nullable|exists:users,id'
            ], [
                'username.regex' => 'Username must not contain spaces.',
            ]);

            // Generate unique 6-character referral code
            do {
                $newReferralCode = strtoupper(Str::random(6));
            } while (User::where('referral_code', $newReferralCode)->exists());

            // Log the data before creating user
            Log::info('Creating user with data:', [
                'firstname' => $validatedData['firstname'],
                'lastname' => $validatedData['lastname'],
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
                'upline_id' => $validatedData['upline_id'] ?? null,
                'referral_code' => $newReferralCode
            ]);

            $user = User::create([
                'firstname' => (string) $validatedData['firstname'],
                'lastname' => (string) $validatedData['lastname'],
                'username' => (string) $validatedData['username'],
                'email' => (string) $validatedData['email'],
                'password' => Hash::make((string) $validatedData['password']),
                'role' => 'user', // Default role for new users
                'upline_id' => $validatedData['upline_id'] ?? null,
                'referral_code' => $newReferralCode
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully and added to downline'
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
        $user = User::with(['wallet', 'kycVerification'])->findOrFail($id);

        // Get direct downlines
        $downlines = User::with('wallet')
            ->where('upline_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate downline statistics
        $downlineCount = $downlines->count();
        $activeDownlines = $downlines->filter(function($downline) {
            return $downline->last_active_at && $downline->last_active_at >= now()->subMinutes(15);
        })->count();

        // Calculate total network count (recursive)
        $totalNetworkCount = $this->getTotalNetworkCount($user->id);

        // Calculate total downline earnings
        $totalDownlineEarnings = $downlines->sum(function($downline) {
            return $downline->wallet ? $downline->wallet->total_balance : 0;
        });

        return view('admin.userProfile', compact(
            'user',
            'downlines',
            'downlineCount',
            'activeDownlines',
            'totalNetworkCount',
            'totalDownlineEarnings'
        ));
    }

    /**
     * Get total network count recursively
     */
    private function getTotalNetworkCount($userId)
    {
        $directDownlines = User::where('upline_id', $userId)->pluck('id');
        $count = $directDownlines->count();

        foreach ($directDownlines as $downlineId) {
            $count += $this->getTotalNetworkCount($downlineId);
        }

        return $count;
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $id . '|regex:/^\S*$/u',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:user,admin'
        ], [
            'username.regex' => 'Username must not contain spaces.',
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

    public function ban(Request $request, User $user)
    {
        $request->validate([
            'ban_reason' => 'required|string|max:1000',
        ], [
            'ban_reason.required' => 'Please provide a reason for banning this user.',
            'ban_reason.max' => 'Ban reason cannot exceed 1000 characters.',
        ]);

        $user->update([
            'is_banned' => true,
            'ban_reason' => $request->ban_reason,
            'banned_at' => now(),
            'banned_by' => auth('admin')->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User has been banned successfully'
        ]);
    }

    public function unban(User $user)
    {
        $user->update([
            'is_banned' => false,
            'ban_reason' => null,
            'banned_at' => null,
            'banned_by' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User has been unbanned successfully'
        ]);
    }

    public function testTelegramConnection(User $user)
    {
        try {
            $result = \App\Services\TelegramNotificationService::testTelegramConnection($user->id);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test notification sent successfully to user\'s Telegram account!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send test notification. User may not have verified their Telegram account or there may be a configuration issue.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram test failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while testing Telegram connection: ' . $e->getMessage()
            ]);
        }
    }

    public function processDepositPromoBonus(Request $request, User $user)
    {
        try {
            // Validate request
            $request->validate([
                'deposit_amount' => 'required|numeric|min:1000'
            ], [
                'deposit_amount.required' => 'Deposit amount is required.',
                'deposit_amount.numeric' => 'Deposit amount must be a valid number.',
                'deposit_amount.min' => 'Minimum deposit amount is $1,000.00 to qualify for promo bonus.'
            ]);

            $depositAmount = $request->deposit_amount;

            // Process the deposit promo bonus using the helper
            $result = \App\Helpers\Helpers::processDepositPromoBonus($user->id, $depositAmount);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Deposit promo bonus processed successfully!',
                    'data' => [
                        'deposit_amount' => $depositAmount,
                        'user_bonus_amount' => $result['user_bonus_amount'],
                        'upline_bonus_amount' => $result['upline_bonus_amount'] ?? 0,
                        'campaign_name' => $result['campaign']->name ?? 'N/A',
                        'expires_at' => $result['expires_at'] ?? 'N/A'
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process deposit promo bonus. Either the deposit amount is below the minimum threshold ($1,000) or there is no active campaign available.'
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Deposit promo bonus processing failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the deposit promo bonus: ' . $e->getMessage()
            ], 500);
        }
    }
}
