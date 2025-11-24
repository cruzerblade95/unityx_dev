<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\XeusdWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Web3Service;

class XeusdWithdrawalController extends Controller
{
    /**
     * Display XEUSD withdrawal management page
     */
    public function index()
    {
        // Statistics
        $totalWithdrawals = XeusdWithdrawal::count();
        $pendingWithdrawals = XeusdWithdrawal::where('status', 'pending')->count();
        $processingWithdrawals = XeusdWithdrawal::where('status', 'processing')->count();
        $completedWithdrawals = XeusdWithdrawal::where('status', 'completed')->count();

        // Amount statistics
        $pendingAmount = XeusdWithdrawal::where('status', 'pending')->sum('amount_usd');
        $completedAmount = XeusdWithdrawal::where('status', 'completed')->sum('amount_usd');

        // Get owner wallet balance for display
        try {
            $web3Service = new Web3Service();
            $ownerBalance = $web3Service->getOwnerWalletBalance();
        } catch (\Exception $e) {
            Log::error('Failed to get owner wallet balance: ' . $e->getMessage());
            $ownerBalance = 0;
        }

        return view('admin.xeusd-withdrawal.index', compact(
            'totalWithdrawals',
            'pendingWithdrawals',
            'processingWithdrawals',
            'completedWithdrawals',
            'pendingAmount',
            'completedAmount',
            'ownerBalance'
        ));
    }

    /**
     * Get XEUSD withdrawals list for DataTables
     */
    public function list(Request $request)
    {
        try {
            $query = XeusdWithdrawal::with(['user', 'processedBy'])
                ->select('xeusd_withdrawals.*');

            // Handle search
            if ($request->has('search') && $request->search['value']) {
                $search = $request->search['value'];
                $query->where(function ($q) use ($search) {
                    $q->where('unique_id', 'LIKE', "%{$search}%")
                      ->orWhere('recipient_address', 'LIKE', "%{$search}%")
                      ->orWhere('transaction_hash', 'LIKE', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('username', 'LIKE', "%{$search}%")
                                   ->orWhere('firstname', 'LIKE', "%{$search}%")
                                   ->orWhere('lastname', 'LIKE', "%{$search}%")
                                   ->orWhere('email', 'LIKE', "%{$search}%");
                      });
                });
            }

            $totalRecords = XeusdWithdrawal::count();

            // Get filtered count before any joins
            $filteredRecords = (clone $query)->count();

            // Handle ordering
            if ($request->has('order')) {
                $columns = ['id', 'user_info', 'amount_usd', 'amount_xeusd', 'recipient_address', 'status', 'created_at', 'actions'];
                $orderColumnIndex = $request->order[0]['column'];
                $orderDirection = $request->order[0]['dir'];

                // Validate column index
                if (isset($columns[$orderColumnIndex])) {
                    $orderColumn = $columns[$orderColumnIndex];

                    if ($orderColumn === 'user_info') {
                        $query->join('users', 'xeusd_withdrawals.user_id', '=', 'users.id')
                              ->orderBy('users.username', $orderDirection)
                              ->select('xeusd_withdrawals.*');
                    } elseif ($orderColumn !== 'actions') {
                        $query->orderBy($orderColumn, $orderDirection);
                    }
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Apply pagination
            $withdrawals = $query->offset($request->start ?? 0)
                                ->limit($request->length ?? 25)
                                ->get();

            $data = $withdrawals->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'unique_id' => $withdrawal->unique_id,
                    'user_info' => $withdrawal->user ? [
                        'username' => $withdrawal->user->username,
                        'firstname' => $withdrawal->user->firstname,
                        'lastname' => $withdrawal->user->lastname,
                        'email' => $withdrawal->user->email,
                    ] : null,
                    'amount_usd' => $withdrawal->amount_usd,
                    'amount_xeusd' => $withdrawal->amount_xeusd,
                    'source_wallet' => $withdrawal->source_wallet,
                    'recipient_address' => $withdrawal->recipient_address,
                    'status' => $withdrawal->status,
                    'transaction_hash' => $withdrawal->transaction_hash,
                    'created_at' => $withdrawal->created_at->format('Y-m-d H:i:s'),
                    'notes' => $withdrawal->notes,
                    'rejection_reason' => $withdrawal->rejection_reason,
                    'actions' => null, // Will be handled by frontend
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching XEUSD withdrawals list: ' . $e->getMessage());
            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load data'
            ]);
        }
    }

    /**
     * Show specific withdrawal details
     */
    public function show($id)
    {
        try {
            $withdrawal = XeusdWithdrawal::with(['user', 'processedBy'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $withdrawal->id,
                    'unique_id' => $withdrawal->unique_id,
                    'user' => [
                        'username' => $withdrawal->user->username,
                        'firstname' => $withdrawal->user->firstname,
                        'lastname' => $withdrawal->user->lastname,
                        'email' => $withdrawal->user->email,
                    ],
                    'amount_usd' => $withdrawal->amount_usd,
                    'amount_xeusd' => $withdrawal->amount_xeusd,
                    'source_wallet' => $withdrawal->source_wallet,
                    'recipient_address' => $withdrawal->recipient_address,
                    'status' => $withdrawal->status,
                    'transaction_hash' => $withdrawal->transaction_hash,
                    'created_at' => $withdrawal->created_at,
                    'notes' => $withdrawal->notes,
                    'rejection_reason' => $withdrawal->rejection_reason,
                    'processed_by' => $withdrawal->processedBy ? [
                        'username' => $withdrawal->processedBy->username,
                        'processed_at' => $withdrawal->processed_at,
                    ] : null,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching XEUSD withdrawal details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal not found'
            ], 404);
        }
    }

    /**
     * Approve and process withdrawal automatically via blockchain
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            'auto_process' => 'boolean'
        ]);

        $withdrawal = XeusdWithdrawal::where('unique_id', $id)->firstOrFail();

        if (!$withdrawal->canBeProcessed()) {
            return response()->json([
                'success' => false,
                'message' => 'This withdrawal cannot be processed.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Check if we should auto-process or manual approve
            if ($request->get('auto_process', true)) {
                return $this->processBlockchainWithdrawal($withdrawal, $request->admin_notes);
            } else {
                return $this->manualApproveWithdrawal($withdrawal, $request);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving XEUSD withdrawal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process withdrawal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process withdrawal automatically via blockchain
     */
    protected function processBlockchainWithdrawal($withdrawal, $adminNotes = null)
    {
        // Update status to processing first
        $withdrawal->update([
            'status' => 'processing',
            'processed_by' => Auth::guard('admin')->id(),
            'processed_at' => now(),
            'admin_notes' => $adminNotes,
        ]);

        // Initialize Web3 service
        $web3Service = new Web3Service();

        // Send blockchain transaction (use amount_xeusd which has fee already deducted)
        $result = $web3Service->sendXEUSD(
            $withdrawal->recipient_address,
            $withdrawal->amount_xeusd
        );

        if ($result['success']) {
            // Update withdrawal with transaction details
            $withdrawal->update([
                'status' => 'completed',
                'transaction_hash' => $result['transaction_hash'],
                'blockchain_sent_at' => now(),
                'blockchain_data' => [
                    'network' => 'BSC',
                    'token_contract' => config('web3.xeusd.contract_address'),
                    'processed_by_admin' => Auth::guard('admin')->user()->name ?? 'Admin',
                    'auto_processed' => true,
                    'gas_used' => $result['gas_used'] ?? null,
                ]
            ]);

            DB::commit();

            Log::info("XEUSD Withdrawal processed via blockchain", [
                'withdrawal_id' => $withdrawal->unique_id,
                'user_id' => $withdrawal->user_id,
                'amount_usd' => $withdrawal->amount_usd,
                'amount_xeusd' => $withdrawal->amount_xeusd,
                'transaction_hash' => $result['transaction_hash'],
                'admin_id' => Auth::guard('admin')->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal processed successfully via blockchain!',
                'transaction_hash' => $result['transaction_hash']
            ]);
        } else {
            // Blockchain transaction failed, revert to pending
            $withdrawal->update([
                'status' => 'pending',
                'admin_notes' => ($adminNotes ? $adminNotes . "\n\n" : '') .
                                "Blockchain processing failed: " . $result['error'],
                'processed_by' => null,
                'processed_at' => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => false,
                'message' => 'Blockchain processing failed: ' . $result['error']
            ], 500);
        }
    }

    /**
     * Manual approval with existing transaction hash
     */
    protected function manualApproveWithdrawal($withdrawal, $request)
    {
        $request->validate([
            'transaction_hash' => 'required|string|size:66|starts_with:0x',
        ]);

        $withdrawal->update([
            'status' => 'completed',
            'transaction_hash' => $request->transaction_hash,
            'processed_by' => Auth::guard('admin')->id(),
            'processed_at' => now(),
            'blockchain_sent_at' => now(),
            'blockchain_confirmed_at' => now(),
            'admin_notes' => $request->admin_notes,
            'blockchain_data' => [
                'network' => 'BSC',
                'token_contract' => config('web3.xeusd.contract_address'),
                'processed_by_admin' => Auth::guard('admin')->user()->name ?? 'Admin',
                'manual_approval' => true,
            ]
        ]);

        DB::commit();

        Log::info("XEUSD Withdrawal manually approved", [
            'withdrawal_id' => $withdrawal->unique_id,
            'user_id' => $withdrawal->user_id,
            'amount_usd' => $withdrawal->amount_usd,
            'amount_xeusd' => $withdrawal->amount_xeusd,
            'transaction_hash' => $request->transaction_hash,
            'admin_id' => Auth::guard('admin')->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal approved and marked as completed successfully!',
            'transaction_hash' => $request->transaction_hash
        ]);
    }

    /**
     * Reject withdrawal
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $withdrawal = XeusdWithdrawal::where('unique_id', $id)->firstOrFail();

        if (!$withdrawal->canBeProcessed()) {
            return response()->json([
                'success' => false,
                'message' => 'This withdrawal cannot be processed.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Return funds to user's wallet
            $user = $withdrawal->user;
            $wallet = $user->wallet;

            if ($wallet) {
                match($withdrawal->source_wallet) {
                    'profit' => $wallet->profit += $withdrawal->amount_usd,
                    'bonus' => $wallet->bonus += $withdrawal->amount_usd,
                    'cash' => $wallet->cash += $withdrawal->amount_usd
                };
                $wallet->save();
            }

            $withdrawal->update([
                'status' => 'rejected',
                'admin_notes' => $request->reason,
                'processed_by' => Auth::guard('admin')->id(),
                'processed_at' => now()
            ]);

            DB::commit();

            Log::info("XEUSD Withdrawal rejected", [
                'withdrawal_id' => $withdrawal->unique_id,
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount_usd,
                'reason' => $request->reason,
                'admin_id' => Auth::guard('admin')->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal rejected and funds returned to user wallet.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting XEUSD withdrawal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject withdrawal. Please try again.'
            ], 500);
        }
    }

    /**
     * Update withdrawal to processing status
     */
    public function markProcessing($id)
    {
        $withdrawal = XeusdWithdrawal::where('unique_id', $id)->firstOrFail();

        if (!$withdrawal->canBeProcessed()) {
            return response()->json([
                'success' => false,
                'message' => 'This withdrawal cannot be processed.'
            ], 400);
        }

        try {
            $withdrawal->update([
                'status' => 'processing',
                'blockchain_sent_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal marked as processing.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking withdrawal as processing: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update withdrawal status.'
            ], 500);
        }
    }
}
