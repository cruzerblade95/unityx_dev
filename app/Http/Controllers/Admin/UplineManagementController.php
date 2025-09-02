<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class UplineManagementController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display the upline management interface
     */
    public function index()
    {
        return view('admin.upline-management.index');
    }

    /**
     * Get users data for DataTables
     */
    public function getUsersData(Request $request)
    {
        try {
            Log::info('UplineManagement getUsersData called', [
                'user_authenticated' => auth()->check(),
                'admin_authenticated' => auth('admin')->check(),
                'request_data' => $request->all()
            ]);

            // Start query with optimized eager loading and select only needed fields
            $query = User::select([
                'id',
                'username',
                'firstname',
                'lastname',
                'email',
                'upline_id',
                'created_at'
            ])->with([
                'wallet:user_id,deposit,profit,bonus', // Only select needed wallet fields
                'upline:id,username', // Only select id and username for upline
                'downlines:id,upline_id' // Only select needed fields for counting
            ]);

            $result = DataTables::of($query)
                ->addColumn('full_name', function ($user) {
                    return trim($user->firstname . ' ' . $user->lastname);
                })
                ->addColumn('upline_username', function ($user) {
                    return ($user->upline_id && $user->upline) ? $user->upline->username : 'No Upline';
                })
                ->addColumn('downlines_count', function ($user) {
                    return $user->downlines ? $user->downlines->count() : 0;
                })
                ->addColumn('wallet_info', function ($user) {
                    if (!$user->wallet) {
                        return null;
                    }

                    $deposit = (float) ($user->wallet->deposit ?? 0);
                    $profit = (float) ($user->wallet->profit ?? 0);
                    $bonus = (float) ($user->wallet->bonus ?? 0);

                    return [
                        'deposit' => number_format($deposit, 2),
                        'profit' => number_format($profit, 2),
                        'total' => number_format($deposit + $profit + $bonus, 2)
                    ];
                })
                ->addColumn('actions', function ($user) {
                    return '<div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-sm btn-outline-primary edit-upline-btn"
                                        data-user-id="' . $user->id . '"
                                        data-username="' . $user->username . '"
                                        data-current-upline="' . ($user->upline ? $user->upline->username : '') . '"
                                        title="Edit Upline">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info view-downlines-btn"
                                        data-user-id="' . $user->id . '"
                                        data-username="' . $user->username . '"
                                        title="View Downlines">
                                    <i class="fas fa-sitemap"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['actions'])
                ->filter(function ($query) use ($request) {
                    // Optimize search functionality
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $searchValue = $request->search['value'];
                        $query->where(function ($q) use ($searchValue) {
                            $q->where('username', 'LIKE', "%{$searchValue}%")
                              ->orWhere('email', 'LIKE', "%{$searchValue}%")
                              ->orWhere('firstname', 'LIKE', "%{$searchValue}%")
                              ->orWhere('lastname', 'LIKE', "%{$searchValue}%")
                              ->orWhereHas('upline', function ($uplineQuery) use ($searchValue) {
                                  $uplineQuery->where('username', 'LIKE', "%{$searchValue}%");
                              });
                        });
                    }
                })
                ->order(function ($query) use ($request) {
                    // Optimize ordering - only allow ordering on database columns
                    if ($request->has('order')) {
                        $columns = ['id', 'username', 'firstname', 'email', 'upline_id', 'created_at'];
                        $orderColumnIndex = $request->order[0]['column'] ?? 0;
                        $orderDirection = $request->order[0]['dir'] ?? 'desc';

                        // Map DataTable column index to actual database column
                        $columnMap = [
                            0 => 'id',
                            1 => 'username',
                            2 => 'firstname', // full_name maps to firstname
                            3 => 'email',
                            4 => 'id', // upline_username - fallback to id
                            5 => 'id', // downlines_count - fallback to id
                            6 => 'id', // wallet_info - fallback to id
                            7 => 'created_at'
                        ];

                        $orderColumn = $columnMap[$orderColumnIndex] ?? 'id';
                        $query->orderBy($orderColumn, $orderDirection);
                    }
                })
                ->make(true);

            // Debug: Log the result to see what's being returned
            $responseData = $result->getData(true);
            Log::info('DataTables response data', [
                'draw' => $responseData['draw'] ?? 'N/A',
                'recordsTotal' => $responseData['recordsTotal'] ?? 'N/A',
                'recordsFiltered' => $responseData['recordsFiltered'] ?? 'N/A',
                'data_count' => count($responseData['data'] ?? []),
                'first_row' => isset($responseData['data'][0]) ? $responseData['data'][0] : 'No data',
                'sample_data' => array_slice($responseData['data'] ?? [], 0, 2)
            ]);

            return $result;

        } catch (\Exception $e) {
            \Log::error('UplineManagement getUsersData error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load users data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users for autocomplete
     */
    public function searchUsers(Request $request)
    {
        $term = $request->get('term', '');

        $users = User::where('username', 'LIKE', "%{$term}%")
            ->orWhere('firstname', 'LIKE', "%{$term}%")
            ->orWhere('lastname', 'LIKE', "%{$term}%")
            ->orWhere('email', 'LIKE', "%{$term}%")
            ->select('id', 'username', 'firstname', 'lastname', 'email', 'upline_id')
            ->limit(20)
            ->get();

        return response()->json($users->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->firstname . ' ' . $user->lastname,
                'email' => $user->email,
                'label' => $user->username . ' (' . $user->firstname . ' ' . $user->lastname . ')',
                'upline_id' => $user->upline_id
            ];
        }));
    }

    /**
     * Get user tree structure
     */
    public function getUserTree(Request $request, $userId)
    {
        $user = User::with(['downlines' => function ($query) {
            $query->with('downlines');
        }])->findOrFail($userId);

        $tree = $this->buildUserTree($user);

        return response()->json($tree);
    }

    /**
     * Get all downlines of a user (recursive)
     */
    public function getAllDownlines(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $downlines = $this->getDownlinesRecursive($user);

        return response()->json($downlines);
    }

    /**
     * Update user's upline
     */
    public function updateUpline(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_upline_id' => 'nullable|exists:users,id'
        ]);

        $userId = $request->user_id;
        $newUplineId = $request->new_upline_id;

        // Prevent circular reference
        if ($newUplineId && $this->wouldCreateCircularReference($userId, $newUplineId)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot set upline: This would create a circular reference in the tree structure.'
            ], 400);
        }

        // Prevent user from being their own upline
        if ($userId == $newUplineId) {
            return response()->json([
                'success' => false,
                'message' => 'User cannot be their own upline.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $user = User::findOrFail($userId);
            $oldUplineId = $user->upline_id;

            $user->upline_id = $newUplineId;
            $user->save();

            // Log the change
            Log::info('Upline changed', [
                'user_id' => $userId,
                'username' => $user->username,
                'old_upline_id' => $oldUplineId,
                'new_upline_id' => $newUplineId,
                'admin_action' => true
            ]);

            DB::commit();

            $uplineUsername = null;
            if ($newUplineId) {
                $upline = User::find($newUplineId);
                $uplineUsername = $upline ? $upline->username : null;
            }

            return response()->json([
                'success' => true,
                'message' => 'Upline updated successfully.',
                'new_upline' => [
                    'id' => $newUplineId,
                    'username' => $uplineUsername
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update upline', [
                'user_id' => $userId,
                'new_upline_id' => $newUplineId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update upline: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update uplines
     */
    public function bulkUpdateUpline(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.user_id' => 'required|exists:users,id',
            'updates.*.new_upline_id' => 'nullable|exists:users,id'
        ]);

        $updates = $request->updates;
        $errors = [];
        $successful = 0;

        DB::beginTransaction();
        try {
            foreach ($updates as $update) {
                $userId = $update['user_id'];
                $newUplineId = $update['new_upline_id'] ?? null;

                // Validation checks
                if ($userId == $newUplineId) {
                    $errors[] = "User ID {$userId}: Cannot be their own upline";
                    continue;
                }

                if ($newUplineId && $this->wouldCreateCircularReference($userId, $newUplineId)) {
                    $errors[] = "User ID {$userId}: Would create circular reference";
                    continue;
                }

                $user = User::findOrFail($userId);
                $user->upline_id = $newUplineId;
                $user->save();
                $successful++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$successful} users." . (count($errors) > 0 ? " Errors: " . implode(', ', $errors) : ''),
                'successful' => $successful,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Bulk update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if updating upline would create circular reference
     */
    private function wouldCreateCircularReference($userId, $newUplineId)
    {
        // Get all uplines of the new upline
        $current = $newUplineId;
        $visited = [];

        while ($current && !in_array($current, $visited)) {
            if ($current == $userId) {
                return true; // Found circular reference
            }

            $visited[] = $current;
            $user = User::find($current);
            $current = $user ? $user->upline_id : null;
        }

        return false;
    }

    /**
     * Build tree structure for a user
     */
    private function buildUserTree($user, $depth = 0, $maxDepth = 5)
    {
        if ($depth > $maxDepth) {
            return null;
        }

        $tree = [
            'id' => $user->id,
            'username' => $user->username,
            'full_name' => $user->firstname . ' ' . $user->lastname,
            'email' => $user->email,
            'upline_id' => $user->upline_id,
            'depth' => $depth,
            'children' => []
        ];

        if ($user->downlines && $user->downlines->count() > 0) {
            foreach ($user->downlines as $downline) {
                $childTree = $this->buildUserTree($downline, $depth + 1, $maxDepth);
                if ($childTree) {
                    $tree['children'][] = $childTree;
                }
            }
        }

        return $tree;
    }

    /**
     * Get all downlines recursively
     */
    private function getDownlinesRecursive($user, $depth = 0, $maxDepth = 10)
    {
        if ($depth > $maxDepth) {
            return [];
        }

        $downlines = [];

        foreach ($user->downlines as $downline) {
            $downlines[] = [
                'id' => $downline->id,
                'username' => $downline->username,
                'full_name' => $downline->firstname . ' ' . $downline->lastname,
                'email' => $downline->email,
                'depth' => $depth + 1,
                'upline_id' => $downline->upline_id
            ];

            // Get recursive downlines
            $subDownlines = $this->getDownlinesRecursive($downline, $depth + 1, $maxDepth);
            $downlines = array_merge($downlines, $subDownlines);
        }

        return $downlines;
    }
}
