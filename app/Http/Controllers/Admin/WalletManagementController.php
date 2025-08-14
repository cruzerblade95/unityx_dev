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
     * Display the wallet management page
     */
    public function index()
    {
        return view('admin.wallet.index');
    }

    /**
     * Get the list of wallets for DataTables
     */
    public function list(Request $request)
    {
        try {
            $users = User::with('wallet')->select('users.*');

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('user', function ($user) {
                    return $user->name ?: $user->username;
                })
                ->addColumn('deposit', function ($user) {
                    return $user->wallet ? $user->wallet->deposit : 0;
                })
                ->addColumn('profit', function ($user) {
                    return $user->wallet ? $user->wallet->profit : 0;
                })
                ->addColumn('bonus', function ($user) {
                    return $user->wallet ? $user->wallet->bonus : 0;
                })
                ->addColumn('cash', function ($user) {
                    return $user->wallet ? $user->wallet->cash : 0;
                })
                ->addColumn('total_balance', function ($user) {
                    if (!$user->wallet) return 0;
                    return $user->wallet->deposit + $user->wallet->profit + $user->wallet->bonus - $user->wallet->cash;
                })
                ->addColumn('actions', function ($user) {
                    return view('admin.wallet.actions', compact('user'))->render();
                })
                ->editColumn('updated_at', function ($user) {
                    return $user->wallet ? $user->wallet->updated_at->format('Y-m-d H:i:s') : '-';
                })
                ->rawColumns(['actions'])
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

    /**
     * Update wallet balance
     */
    public function updateBalance(Request $request, User $user)
    {
        $request->validate([
            'type' => 'required|in:deposit,profit,bonus,cash',
            'amount' => 'required|numeric|min:0',
            'operation' => 'required|in:add,subtract,modify'
        ]);

        try {
            DB::beginTransaction();

            // Create wallet if it doesn't exist
            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = new Wallet([
                    'user_id' => $user->id,
                    'deposit' => 0,
                    'profit' => 0,
                    'bonus' => 0,
                    'cash' => 0
                ]);
                $wallet->save();
            }

            $type = $request->type;
            $amount = $request->amount;

            if ($request->operation === 'add') {
                $wallet->$type += $amount;
            } elseif ($request->operation === 'subtract') {
                // Check if there's enough balance for subtraction
                if ($wallet->$type < $amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient balance for this operation.'
                    ], 400);
                }
                $wallet->$type -= $amount;
            } elseif ($request->operation === 'modify') {
                $wallet->$type = $amount;
            }

            $wallet->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Balance updated successfully',
                'new_balance' => $wallet->$type,
                'total_balance' => $wallet->deposit + $wallet->profit + $wallet->bonus - $wallet->cash
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Wallet update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the balance.'
            ], 500);
        }
    }
}
