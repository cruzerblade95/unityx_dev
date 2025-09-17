<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ReinvestmentSettings;
use Illuminate\Support\Facades\DB;

class AdminReinvestmentSettingsController extends Controller
{
    private $walletTypes = ['profit', 'bonus'];

    public function index(Request $request)
    {
        // Get global default settings
        $globalDefaults = [];
        foreach ($this->walletTypes as $type) {
            $globalDefaults[$type] = ReinvestmentSettings::getGlobalDefaults($type);
        }

        return view('admin.reinvestment-settings.index', compact('globalDefaults'));
    }

    public function getUsersData(Request $request)
    {
        // Clean any output buffer to prevent BOM characters in JSON response
        if (ob_get_level()) {
            ob_end_clean();
        }

        $query = User::query()->select('id', 'firstname', 'lastname', 'email', 'username');

        // Apply search filter if provided
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%");
            });
        }

        // Get total count before pagination
        $totalRecords = User::count();
        $filteredRecords = $query->count();

        // Apply pagination
        if ($request->has('start') && $request->has('length')) {
            $query->skip($request->start)->take($request->length);
        }

        // Apply ordering
        if ($request->has('order')) {
            $columns = ['id', 'user_info', 'profit', 'bonus', 'actions'];
            $orderColumn = $columns[$request->order[0]['column']] ?? 'id';
            $orderDir = $request->order[0]['dir'] ?? 'asc';

            if ($orderColumn === 'user_info') {
                $query->orderBy('firstname', $orderDir);
            } else {
                $query->orderBy('id', $orderDir);
            }
        }

        $users = $query->get();

        // Format data for DataTables
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->id,
                'checkbox' => '<input type="checkbox" class="user-checkbox" value="' . $user->id . '">',
                'user_info' => '<div>
                    <strong>' . $user->firstname . ' ' . $user->lastname . '</strong><br>
                    <small class="text-muted">' . $user->email . '</small><br>
                    <small class="text-muted">' . $user->username . '</small>
                </div>',
                'profit' => '<div id="profit-' . $user->id . '" class="settings-preview">
                    <small class="text-muted">Loading...</small>
                </div>',
                'bonus' => '<div id="bonus-' . $user->id . '" class="settings-preview">
                    <small class="text-muted">Loading...</small>
                </div>',
                'actions' => '<button class="btn btn-sm btn-primary" onclick="editUserSettings(' . $user->id . ')">
                    <i class="fas fa-edit"></i> Edit
                </button>'
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function getUserSettings(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            $userSettings = [];

            foreach ($this->walletTypes as $type) {
                $userSettings[$type] = ReinvestmentSettings::getUserSettings($userId, $type);
            }

            return response()->json([
                'success' => true,
                'user' => $user,
                'settings' => $userSettings
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error("User not found: {$userId}");
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error loading user settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading user settings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateUserSettings(Request $request, $userId)
    {
        try {
            $request->validate([
                'wallet_type' => 'required|in:profit,bonus',
                'min_amount' => 'required|numeric|min:0',
                'max_amount' => 'required|numeric|gt:min_amount',
                'tax_percent' => 'required|numeric|min:0|max:100',
                'is_enabled' => 'nullable'
            ]);

            $settings = [
                'min_amount' => $request->min_amount,
                'max_amount' => $request->max_amount,
                'tax_percent' => $request->tax_percent,
                'is_enabled' => $request->has('is_enabled') ? (bool)$request->is_enabled : false
            ];

            ReinvestmentSettings::setUserSettings($userId, $request->wallet_type, $settings);

            return response()->json(['success' => true, 'message' => 'Settings updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Error updating user settings: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error updating settings: ' . $e->getMessage()], 500);
        }
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'wallet_type' => 'required|in:profit,bonus',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'is_enabled' => 'nullable|boolean'
        ]);

        // Validate max_amount is greater than min_amount if both provided
        if ($request->filled(['min_amount', 'max_amount']) && $request->max_amount <= $request->min_amount) {
            return response()->json(['error' => 'Maximum amount must be greater than minimum amount'], 422);
        }

        $settings = [];
        if ($request->filled('min_amount')) $settings['min_amount'] = $request->min_amount;
        if ($request->filled('max_amount')) $settings['max_amount'] = $request->max_amount;
        if ($request->filled('tax_percent')) $settings['tax_percent'] = $request->tax_percent;
        if ($request->has('is_enabled')) $settings['is_enabled'] = $request->is_enabled;

        $updatedCount = 0;
        foreach ($request->user_ids as $userId) {
            ReinvestmentSettings::setUserSettings($userId, $request->wallet_type, $settings);
            $updatedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Settings updated for {$updatedCount} users"
        ]);
    }

    public function updateGlobalDefaults(Request $request)
    {
        try {
            $request->validate([
                'wallet_type' => 'required|in:profit,bonus',
                'min_amount' => 'required|numeric|min:0',
                'max_amount' => 'required|numeric|gt:min_amount',
                'tax_percent' => 'required|numeric|min:0|max:100',
                'is_enabled' => 'nullable'
            ]);

            $settings = [
                'min_amount' => $request->min_amount,
                'max_amount' => $request->max_amount,
                'tax_percent' => $request->tax_percent,
                'is_enabled' => $request->has('is_enabled') ? (bool)$request->is_enabled : false
            ];

            ReinvestmentSettings::setGlobalDefaults($request->wallet_type, $settings);

            return response()->json(['success' => true, 'message' => 'Global defaults updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Error updating global defaults: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error updating global defaults: ' . $e->getMessage()], 500);
        }
    }

    public function getSettingsPreview(Request $request)
    {
        $userIds = explode(',', $request->user_ids);
        $walletType = $request->wallet_type;

        $previews = [];
        foreach ($userIds as $userId) {
            $settings = ReinvestmentSettings::getUserSettings($userId, $walletType);

            if ($settings) {
                $previews[$userId] = [
                    'min_amount' => '$' . number_format($settings->min_amount, 2),
                    'max_amount' => '$' . number_format($settings->max_amount, 2),
                    'tax_percent' => $settings->tax_percent . '%',
                    'status' => $settings->is_enabled ? 'Enabled' : 'Disabled',
                    'status_class' => $settings->is_enabled ? 'text-success' : 'text-danger'
                ];
            } else {
                $previews[$userId] = [
                    'min_amount' => 'Not set',
                    'max_amount' => 'Not set',
                    'tax_percent' => 'Not set',
                    'status' => 'Default',
                    'status_class' => 'text-muted'
                ];
            }
        }

        return response()->json($previews);
    }

    public function deleteUserSettings(Request $request, $userId)
    {
        $request->validate([
            'wallet_type' => 'required|in:profit,bonus'
        ]);

        ReinvestmentSettings::where('user_id', $userId)
                           ->where('wallet_type', $request->wallet_type)
                           ->delete();

        return response()->json(['success' => true, 'message' => 'User settings deleted, will use global defaults']);
    }

    public function setUserToFollowDefaults(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);

            // Delete all user-specific settings so they will fall back to global defaults
            $deletedCount = ReinvestmentSettings::where('user_id', $userId)->delete();

            return response()->json([
                'success' => true,
                'message' => "User will now follow global default settings for all wallet types. Removed {$deletedCount} custom settings."
            ]);

        } catch (\Exception $e) {
            \Log::error('Error setting user to follow defaults: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error setting user to follow defaults: ' . $e->getMessage()
            ], 500);
        }
    }
}
