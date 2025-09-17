<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WithdrawalSettings;
use App\Models\SystemSettings;
use Illuminate\Support\Facades\DB;

class AdminWithdrawalSettingsController extends Controller
{
    private $withdrawalTypes = ['crypto', 'etc_black', 'gold'];

    public function index(Request $request)
    {
        // Get global default settings
        $globalDefaults = [];
        foreach ($this->withdrawalTypes as $type) {
            $globalDefaults[$type] = WithdrawalSettings::getGlobalDefaults($type);
        }

        // Get maintenance mode status
        $isMaintenanceMode = SystemSettings::isWithdrawalMaintenanceMode();
        $maintenanceMessage = SystemSettings::getWithdrawalMaintenanceMessage();

        return view('admin.withdrawal-settings.index', compact('globalDefaults', 'isMaintenanceMode', 'maintenanceMessage'));
    }

    public function getUsersData(Request $request)
    {
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
            $columns = ['id', 'user_info', 'crypto', 'etc_black', 'gold', 'actions'];
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
                'crypto' => '<div id="crypto-' . $user->id . '" class="settings-preview">
                    <small class="text-muted">Loading...</small>
                </div>',
                'etc_black' => '<div id="etc_black-' . $user->id . '" class="settings-preview">
                    <small class="text-muted">Loading...</small>
                </div>',
                'gold' => '<div id="gold-' . $user->id . '" class="settings-preview">
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
        $user = User::findOrFail($userId);
        $userSettings = [];

        foreach ($this->withdrawalTypes as $type) {
            $userSettings[$type] = WithdrawalSettings::getUserSettings($userId, $type);
        }

        return response()->json([
            'user' => $user,
            'settings' => $userSettings
        ]);
    }

    public function updateUserSettings(Request $request, $userId)
    {
        $request->validate([
            'withdrawal_type' => 'required|in:crypto,etc_black,gold',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'processing_fee_percent' => 'required|numeric|min:0|max:100',
            'processing_fee_fixed' => 'required|numeric|min:0',
            'cooldown_hours' => 'required|integer|min:0',
            'is_enabled' => 'boolean'
        ]);

        $settings = [
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'processing_fee_percent' => $request->processing_fee_percent,
            'processing_fee_fixed' => $request->processing_fee_fixed,
            'cooldown_hours' => $request->cooldown_hours,
            'is_enabled' => $request->has('is_enabled')
        ];

        WithdrawalSettings::setUserSettings($userId, $request->withdrawal_type, $settings);

        return response()->json(['success' => true, 'message' => 'Settings updated successfully']);
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'withdrawal_type' => 'required|in:crypto,etc_black,gold',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric',
            'processing_fee_percent' => 'nullable|numeric|min:0|max:100',
            'processing_fee_fixed' => 'nullable|numeric|min:0',
            'cooldown_hours' => 'nullable|integer|min:0',
            'is_enabled' => 'nullable|boolean'
        ]);

        $settings = array_filter([
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'processing_fee_percent' => $request->processing_fee_percent,
            'processing_fee_fixed' => $request->processing_fee_fixed,
            'cooldown_hours' => $request->cooldown_hours,
            'is_enabled' => $request->is_enabled
        ], function($value) {
            return $value !== null;
        });

        DB::transaction(function() use ($request, $settings) {
            foreach ($request->user_ids as $userId) {
                WithdrawalSettings::setUserSettings($userId, $request->withdrawal_type, $settings);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Settings updated for ' . count($request->user_ids) . ' users'
        ]);
    }

    public function resetToDefaults(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'withdrawal_type' => 'nullable|in:crypto,etc_black,gold'
        ]);

        DB::transaction(function() use ($request) {
            foreach ($request->user_ids as $userId) {
                WithdrawalSettings::resetToDefaults($userId, $request->withdrawal_type);
            }
        });

        $message = 'Users reset to default settings';
        if ($request->withdrawal_type) {
            $message .= ' for ' . $request->withdrawal_type;
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function globalDefaults()
    {
        $globalDefaults = [];
        foreach ($this->withdrawalTypes as $type) {
            $globalDefaults[$type] = WithdrawalSettings::getGlobalDefaults($type);
        }

        return view('admin.withdrawal-settings.global-defaults', compact('globalDefaults'));
    }

    public function updateGlobalDefaults(Request $request)
    {
        $validationRules = [
            'withdrawal_type' => 'required|in:crypto,etc_black,gold',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'processing_fee_percent' => 'required|numeric|min:0|max:100',
            'processing_fee_fixed' => 'required|numeric|min:0',
            'cooldown_hours' => 'required|integer|min:0',
            'is_enabled' => 'boolean'
        ];

        // Add MYR conversion rate validation for gold withdrawal type
        if ($request->withdrawal_type === 'gold') {
            $validationRules['myr_conversion_rate'] = 'required|numeric|min:0.1';
        }

        $request->validate($validationRules);

        $settings = [
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'processing_fee_percent' => $request->processing_fee_percent,
            'processing_fee_fixed' => $request->processing_fee_fixed,
            'cooldown_hours' => $request->cooldown_hours,
            'is_enabled' => $request->has('is_enabled')
        ];

        // Handle additional settings for gold withdrawal type
        if ($request->withdrawal_type === 'gold' && $request->has('myr_conversion_rate')) {
            $settings['additional_settings'] = [
                'myr_conversion_rate' => $request->myr_conversion_rate
            ];
        }

        WithdrawalSettings::setGlobalDefaults($request->withdrawal_type, $settings);

        // Clear MYR conversion rate cache if it was updated
        if ($request->withdrawal_type === 'gold' && $request->has('myr_conversion_rate')) {
            cache()->forget('gold_myr_conversion_rate');
        }

        return response()->json(['success' => true, 'message' => 'Global defaults updated successfully']);
    }

    public function disableWithdrawals(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'withdrawal_types' => 'required|array',
            'withdrawal_types.*' => 'in:crypto,etc_black,gold'
        ]);

        DB::transaction(function() use ($request) {
            foreach ($request->user_ids as $userId) {
                foreach ($request->withdrawal_types as $type) {
                    WithdrawalSettings::setUserSettings($userId, $type, ['is_enabled' => false]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Withdrawals disabled for selected users'
        ]);
    }

    public function enableWithdrawals(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'withdrawal_types' => 'required|array',
            'withdrawal_types.*' => 'in:crypto,etc_black,gold'
        ]);

        DB::transaction(function() use ($request) {
            foreach ($request->user_ids as $userId) {
                foreach ($request->withdrawal_types as $type) {
                    WithdrawalSettings::setUserSettings($userId, $type, ['is_enabled' => true]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Withdrawals enabled for selected users'
        ]);
    }

    /**
     * Toggle withdrawal maintenance mode
     */
    public function toggleMaintenance(Request $request)
    {
        try {
            $enabled = $request->input('enabled', null);
            $message = $request->input('message', null);

            // Toggle maintenance mode
            SystemSettings::toggleWithdrawalMaintenance($enabled);

            // Update message if provided
            if ($message !== null) {
                SystemSettings::set('withdrawal_maintenance_message', $message, 'string', 'Message to display when withdrawal system is in maintenance mode');
            }

            $isEnabled = SystemSettings::isWithdrawalMaintenanceMode();

            return response()->json([
                'success' => true,
                'message' => $isEnabled ? 'Withdrawal maintenance mode enabled' : 'Withdrawal maintenance mode disabled',
                'maintenance_enabled' => $isEnabled
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling maintenance mode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current maintenance status
     */
    public function getMaintenanceStatus()
    {
        return response()->json([
            'maintenance_enabled' => SystemSettings::isWithdrawalMaintenanceMode(),
            'maintenance_message' => SystemSettings::getWithdrawalMaintenanceMessage()
        ]);
    }
}
