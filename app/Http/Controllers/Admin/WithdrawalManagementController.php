<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WithdrawalManagementController extends Controller
{
    public function index()
    {
        // Get statistics for the dashboard cards
        $totalWithdrawals = Withdrawal::count();
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $approvedWithdrawals = Withdrawal::where('status', 'approved')->count();
        $totalAmount = Withdrawal::sum('amount');
        $pendingAmount = Withdrawal::where('status', 'pending')->sum('amount');

        return view('admin.withdrawal.index', compact(
            'totalWithdrawals',
            'pendingWithdrawals', 
            'approvedWithdrawals',
            'totalAmount',
            'pendingAmount'
        ));
    }

    public function list(Request $request)
    {
        Log::info('Withdrawal list method called', ['request_data' => $request->all()]);
        
        $withdrawals = Withdrawal::with(['user'])
            ->select([
                'withdrawals.id',
                'withdrawals.user_id',
                'withdrawals.amount',
                'withdrawals.tax_amount',
                'withdrawals.net_amount',
                'withdrawals.status',
                'withdrawals.bank_name',
                'withdrawals.account_number',
                'withdrawals.reference',
                'withdrawals.note',
                'withdrawals.created_at'
            ]);

        Log::info('Withdrawal query built', ['count' => $withdrawals->count()]);

        // Apply filters
        if ($request->filled('status')) {
            $withdrawals->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $withdrawals->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $withdrawals->whereDate('created_at', '<=', $request->date_to);
        }

        try {
            $result = DataTables::of($withdrawals)
                ->addColumn('user_info', function ($withdrawal) {
                    $user = $withdrawal->user;
                    if (!$user) {
                        return [
                            'id' => null,
                            'username' => 'Unknown',
                            'email' => 'Unknown',
                            'firstname' => 'Unknown',
                            'lastname' => 'User'
                        ];
                    }
                    return [
                        'id' => $user->id,
                        'username' => $user->username ?? 'Unknown',
                        'email' => $user->email ?? 'Unknown',
                        'firstname' => $user->firstname ?? 'Unknown',
                        'lastname' => $user->lastname ?? 'User'
                    ];
                })
                ->addColumn('bank_info', function ($withdrawal) {
                    return [
                        'bank_name' => $withdrawal->bank_name ?? 'N/A',
                        'account_number' => $withdrawal->account_number ? 
                            substr($withdrawal->account_number, 0, 4) . '****' . substr($withdrawal->account_number, -4) : 
                            'N/A'
                    ];
                })
                ->addColumn('action', function ($withdrawal) {
                    $actions = '<div class="btn-group" role="group">';
                    
                    if ($withdrawal->status === 'pending') {
                        $actions .= '<button type="button" class="btn btn-success btn-sm approve-btn" 
                                        data-id="' . $withdrawal->id . '" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#approveModal">
                                        <i class="fas fa-check"></i> Approve
                                    </button>';
                        $actions .= '<button type="button" class="btn btn-danger btn-sm reject-btn" 
                                        data-id="' . $withdrawal->id . '"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#rejectModal">
                                        <i class="fas fa-times"></i> Reject
                                    </button>';
                    }
                    
                    $actions .= '<button type="button" class="btn btn-info btn-sm view-btn" 
                                    data-id="' . $withdrawal->id . '"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#viewModal">
                                    <i class="fas fa-eye"></i> View
                                </button>';
                    
                    $actions .= '</div>';
                    
                    return $actions;
                })
                ->filterColumn('user_info', function ($query, $keyword) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('username', 'like', "%{$keyword}%")
                          ->orWhere('email', 'like', "%{$keyword}%")
                          ->orWhere('firstname', 'like', "%{$keyword}%")
                          ->orWhere('lastname', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['action'])
                ->make(true);
            
            Log::info('DataTables result generated successfully');
            return $result;
            
        } catch (\Exception $e) {
            Log::error('DataTables error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function approve(Request $request, $id)
    {
        try {
            $withdrawal = Withdrawal::with('user.wallet')->findOrFail($id);
            
            if ($withdrawal->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending withdrawals can be approved.'
                ], 400);
            }

            // Check if user has sufficient balance
            $wallet = $withdrawal->user->wallet;
            if (!$wallet || $wallet->total_balance < $withdrawal->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has insufficient balance for this withdrawal.'
                ], 400);
            }

            // Deduct the withdrawal amount from user's wallet
            // You can customize which balance type to deduct from based on your business logic
            if ($wallet->cash >= $withdrawal->amount) {
                $wallet->cash -= $withdrawal->amount;
            } elseif ($wallet->profit >= $withdrawal->amount) {
                $wallet->profit -= $withdrawal->amount;
            } elseif ($wallet->deposit >= $withdrawal->amount) {
                $wallet->deposit -= $withdrawal->amount;
            } else {
                // Deduct from multiple sources
                $remaining = $withdrawal->amount;
                if ($wallet->cash > 0) {
                    $deductFromCash = min($wallet->cash, $remaining);
                    $wallet->cash -= $deductFromCash;
                    $remaining -= $deductFromCash;
                }
                if ($remaining > 0 && $wallet->profit > 0) {
                    $deductFromProfit = min($wallet->profit, $remaining);
                    $wallet->profit -= $deductFromProfit;
                    $remaining -= $deductFromProfit;
                }
                if ($remaining > 0 && $wallet->deposit > 0) {
                    $deductFromDeposit = min($wallet->deposit, $remaining);
                    $wallet->deposit -= $deductFromDeposit;
                    $remaining -= $deductFromDeposit;
                }
            }

            $wallet->save();

            $withdrawal->update([
                'status' => 'approved'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal approved successfully! Amount deducted from user wallet.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving withdrawal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $withdrawal = Withdrawal::findOrFail($id);
            
            if ($withdrawal->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending withdrawals can be rejected.'
                ], 400);
            }

            $withdrawal->update([
                'status' => 'rejected'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal rejected successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting withdrawal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $withdrawal = Withdrawal::with('user')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'tax_amount' => $withdrawal->tax_amount,
                    'net_amount' => $withdrawal->net_amount,
                    'status' => $withdrawal->status,
                    'bank_name' => $withdrawal->bank_name,
                    'account_number' => $withdrawal->account_number,
                    'reference' => $withdrawal->reference,
                    'note' => $withdrawal->note,
                    'created_at' => $withdrawal->created_at->format('M d, Y H:i'),
                    'user' => [
                        'id' => $withdrawal->user->id,
                        'username' => $withdrawal->user->username,
                        'email' => $withdrawal->user->email,
                        'firstname' => $withdrawal->user->firstname,
                        'lastname' => $withdrawal->user->lastname
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching withdrawal details: ' . $e->getMessage()
            ], 500);
        }
    }
}
