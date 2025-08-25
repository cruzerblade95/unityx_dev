<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class WalletManagementController extends Controller
{
    /**
     * Display the wallet management page or return AJAX data
     */
    public function index(Request $request)
    {
        // If this is an AJAX request, return DataTables data
        if ($request->ajax()) {
            try {
                $users = User::with('wallet')->select('users.*');

                // Apply search filter
                if ($request->has('search_input') && !empty($request->search_input)) {
                    $searchTerm = $request->search_input;
                    $users->where(function($query) use ($searchTerm) {
                        $query->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('username', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%");
                    });
                }

                // Apply role filter
                if ($request->has('role') && !empty($request->role)) {
                    $users->where('role', $request->role);
                }

                return DataTables::of($users)
                    ->addIndexColumn()
                    ->addColumn('checkbox', function ($user) {
                        return '<input type="checkbox" class="form-check-input">';
                    })
                    ->addColumn('user', function ($user) {
                        return $user->name ?: $user->username;
                    })
                    ->addColumn('email', function ($user) {
                        return $user->email;
                    })
                    ->addColumn('role', function ($user) {
                        return ucfirst($user->role);
                    })
                    ->addColumn('wallet_status', function ($user) {
                        return $user->wallet ? 'Active' : 'Not Created';
                    })
                    ->addColumn('actions', function ($user) {
                        return view('admin.wallet.actions', compact('user'))->render();
                    })
                    ->editColumn('created_at', function ($user) {
                        return $user->created_at->format('Y-m-d H:i:s');
                    })
                    ->rawColumns(['checkbox', 'actions'])
                    ->make(true);
            } catch (Exception $e) {
                Log::error('Wallet list error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error loading wallet data',
                    'data' => []
                ], 500);
            }
        }

        // Return the view for non-AJAX requests
        return view('admin.wallet.index');
    }

    /**
     * Create wallet for a user
     */
    public function createWallet(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $user = User::findOrFail($request->user_id);

            DB::beginTransaction();

            // Check if wallet already exists
            if ($user->wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet already exists for this user.'
                ], 400);
            }

            // Create new wallet with default values
            $wallet = new Wallet([
                'user_id' => $user->id,
                'deposit' => 0,
                'profit' => 0,
                'bonus' => 0,
                'cash' => 0
            ]);
            $wallet->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Wallet created successfully for ' . ($user->name ?: $user->username),
                'wallet' => $wallet
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Wallet creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the wallet.'
            ], 500);
        }
    }
}
