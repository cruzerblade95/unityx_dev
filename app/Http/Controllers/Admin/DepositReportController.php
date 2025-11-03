<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Deposit;
use App\Models\TransactionHistory;
use App\Models\Wallet;
use App\Models\MarketplaceListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class DepositReportController extends Controller
{
    /**
     * Display the deposit report page
     */
    public function index()
    {
        // Get statistics for the header cards
        $stats = $this->getDepositStats();

        return view('admin.deposit-report.index', $stats);
    }

    /**
     * Return DataTables data for AJAX requests
     */
    public function list(Request $request)
    {
        try {
            Log::info('=== DEPOSIT REPORT LIST START ===');
            Log::info('DataTables request parameters:', $request->all());

            // Check if DataTables is requesting ordering
            $orderColumn = $request->input('order.0.column', 5);
            $orderDir = $request->input('order.0.dir', 'desc');
            Log::info('DataTables ordering request:', [
                'column' => $orderColumn,
                'direction' => $orderDir
            ]);

            // Get all deposit sources
            $deposits = collect();

            // 1. Crypto Deposits (from /user/deposit - NOWPayments)
            $cryptoDeposits = Deposit::with('user')
                ->where('status', 'completed')
                ->where('payment_method', 'crypto')
                ->get()
                ->map(function ($deposit) {
                    return [
                        'id' => 'crypto_' . $deposit->id,
                        'user_id' => $deposit->user_id,
                        'user' => $deposit->user,
                        'amount' => $deposit->amount,
                        'status' => 'completed',
                        'created_at' => $deposit->created_at,
                        'source_type' => 'crypto_deposit',
                        'reference' => $deposit->reference ?? 'N/A',
                        'note' => 'Crypto Deposit via NOWPayments'
                    ];
                });
            $deposits = $deposits->merge($cryptoDeposits);

            // 1b. Direct/Manual Deposits (from /user/deposit/direct - Manual)
            $directDeposits = Deposit::with('user')
                ->where('status', 'completed')
                ->where('payment_method', 'manual')
                ->get()
                ->map(function ($deposit) {
                    return [
                        'id' => 'direct_' . $deposit->id,
                        'user_id' => $deposit->user_id,
                        'user' => $deposit->user,
                        'amount' => $deposit->amount,
                        'status' => 'completed',
                        'created_at' => $deposit->created_at,
                        'source_type' => 'direct_deposit',
                        'reference' => $deposit->reference ?? 'N/A',
                        'note' => 'Direct Manual Deposit'
                    ];
                });
            $deposits = $deposits->merge($directDeposits);

            // 1c. XEUSD Deposits (from /user/deposit/direct - XEUSD blockchain deposits)
            $xeusdDeposits = Deposit::with('user')
                ->where('status', 'completed')
                ->where('payment_method', 'xeusd')
                ->get()
                ->map(function ($deposit) {
                    return [
                        'id' => 'xeusd_' . $deposit->id,
                        'user_id' => $deposit->user_id,
                        'user' => $deposit->user,
                        'amount' => $deposit->amount,
                        'status' => 'completed',
                        'created_at' => $deposit->created_at,
                        'source_type' => 'xeusd_deposit',
                        'reference' => $deposit->reference ?? 'N/A',
                        'note' => 'XEUSD Blockchain Deposit'
                    ];
                });
            $deposits = $deposits->merge($xeusdDeposits);

            // 2. Marketplace deposits (when user buys from marketplace)
            $marketplaceDeposits = DB::table('marketplace_listings')
                ->join('users', 'marketplace_listings.buyer_id', '=', 'users.id')
                ->where('marketplace_listings.status', 'sold')
                ->whereNotNull('marketplace_listings.buyer_id')
                ->select(
                    'marketplace_listings.id',
                    'marketplace_listings.buyer_id as user_id',
                    'marketplace_listings.amount',
                    'marketplace_listings.sold_at as created_at',
                    'marketplace_listings.unique_id',
                    'users.firstname',
                    'users.lastname',
                    'users.email',
                    'users.username'
                )
                ->get()
                ->map(function ($listing) {
                    return [
                        'id' => 'marketplace_' . $listing->id,
                        'user_id' => $listing->user_id,
                        'user' => [
                            'firstname' => $listing->firstname,
                            'lastname' => $listing->lastname,
                            'email' => $listing->email,
                            'username' => $listing->username
                        ],
                        'amount' => $listing->amount,
                        'status' => 'completed',
                        'created_at' => Carbon::parse($listing->created_at),
                        'source_type' => 'marketplace',
                        'reference' => $listing->unique_id,
                        'note' => 'Marketplace Purchase'
                    ];
                });
            $deposits = $deposits->merge($marketplaceDeposits);

            // 3. Manual Admin deposits from TransactionHistory
            $manualDeposits = TransactionHistory::with(['user', 'admin'])
                ->where('transaction_type', 'add')
                ->where('balance_type', 'deposit')
                ->whereNotNull('admin_id')
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => 'manual_' . $transaction->id,
                        'user_id' => $transaction->user_id,
                        'user' => $transaction->user,
                        'amount' => $transaction->amount,
                        'status' => 'completed',
                        'created_at' => $transaction->created_at,
                        'source_type' => 'admin_manual',
                        'reference' => 'ADMIN-' . $transaction->id,
                        'note' => $transaction->reason ?? 'Admin Manual Deposit'
                    ];
                });
            $deposits = $deposits->merge($manualDeposits);

            // FORCE sorting by created_at descending (latest first)
            // Add a sort timestamp to ensure proper ordering
            $depositsWithSortKey = $deposits->map(function($item) {
                $date = $item['created_at'];
                $timestamp = $date instanceof Carbon ? $date->timestamp : Carbon::parse($date)->timestamp;
                $item['sort_timestamp'] = $timestamp;
                return $item;
            });

            // Sort by timestamp descending (latest first)
            $testData = $depositsWithSortKey->sortByDesc('sort_timestamp')->values();

            // Log first few items to debug sorting
            Log::info('First 5 deposits after sorting:', [
                'items' => $testData->take(5)->map(function($item) {
                    $date = $item['created_at'];
                    $timestamp = $date instanceof Carbon ? $date->timestamp : Carbon::parse($date)->timestamp;
                    return [
                        'id' => $item['id'],
                        'created_at' => $date,
                        'timestamp' => $timestamp,
                        'formatted_date' => Carbon::parse($date)->format('Y-m-d H:i:s'),
                        'source_type' => $item['source_type']
                    ];
                })->toArray()
            ]);

            Log::info('Test data created, count: ' . $testData->count());
            Log::info('All deposits in order:', [
                'deposits' => $testData->map(function($item, $index) {
                    $date = $item['created_at'];
                    $timestamp = $date instanceof Carbon ? $date->timestamp : Carbon::parse($date)->timestamp;
                    return [
                        'index' => $index,
                        'id' => $item['id'],
                        'timestamp' => $timestamp,
                        'formatted_date' => Carbon::parse($date)->format('Y-m-d H:i:s'),
                        'source' => $item['source_type']
                    ];
                })->toArray()
            ]);

            return DataTables::of($testData)
                ->addIndexColumn()
                ->order(function ($query) {
                    // Don't apply any ordering - we've already sorted the collection
                })
                ->addColumn('user_info', function ($deposit) {
                    $user = $deposit['user'] ?? [];
                    $displayName = ($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '');
                    return '<div>
                                <h6 class="mb-0">' . htmlspecialchars($displayName) . '</h6>
                                <small class="text-muted">' . htmlspecialchars($user['email'] ?? '') . '</small>
                            </div>';
                })
                ->addColumn('amount', function ($deposit) {
                    return '<strong class="text-success">$' . number_format($deposit['amount'], 2) . '</strong>';
                })
                ->addColumn('source', function ($deposit) {
                    return $this->getDepositSourceBadge($deposit['source_type']);
                })
                ->addColumn('status', function ($deposit) {
                    return '<span class="badge bg-success">Completed</span>';
                })
                ->editColumn('created_at', function ($deposit) {
                    $date = Carbon::parse($deposit['created_at']);
                    // Use the sort_timestamp we added for proper ordering
                    return '<small data-order="' . $deposit['sort_timestamp'] . '">' . $date->format('M d, Y H:i') . '</small>';
                })
                ->addColumn('actions', function ($deposit) {
                    $user = $deposit['user'];
                    $userName = ($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '');
                    $userName = trim($userName) ?: ($user['username'] ?? 'N/A');

                    // Get user ID - for marketplace deposits it's in $deposit['user_id'], for others it's in $user['id']
                    $userId = $deposit['user_id'] ?? ($user['id'] ?? null);

                    $actions = '<div class="btn-group">';

                    // View Details/Transaction button and Adjust Wallet
                    if ($deposit['source_type'] === 'marketplace') {
                        $actions .= '<a href="' . route('admin.marketplace.show', $deposit['reference']) . '" class="btn btn-sm btn-outline-primary">View Transaction</a>';

                        // Add Adjust Wallet button for marketplace transactions
                        if ($userId) {
                            $wallet = \App\Models\Wallet::where('user_id', $userId)->first();
                            if ($wallet) {
                                $actions .= '<button class="btn btn-sm btn-outline-success ms-1 btn-adjust-wallet"
                                                data-wallet-id="' . $wallet->id . '"
                                                data-user-name="' . htmlspecialchars($userName) . '"
                                                title="Adjust User Wallet">
                                                <i class="fas fa-wallet"></i> Adjust Wallet
                                            </button>';
                            }
                        }
                    } else {
                        $actions .= '<button class="btn btn-sm btn-outline-primary" onclick="viewDepositDetails(\'' . $deposit['id'] . '\')">View Details</button>';

                        // Add Adjust Wallet button for other deposit types
                        if ($userId) {
                            $wallet = \App\Models\Wallet::where('user_id', $userId)->first();
                            if ($wallet) {
                                $actions .= '<button class="btn btn-sm btn-outline-success ms-1 btn-adjust-wallet"
                                                data-wallet-id="' . $wallet->id . '"
                                                data-user-name="' . htmlspecialchars($userName) . '"
                                                title="Adjust User Wallet">
                                                <i class="fas fa-wallet"></i> Adjust Wallet
                                            </button>';
                            }
                        }
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['user_info', 'amount', 'source', 'status', 'created_at', 'actions'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error('Deposit Report List Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'draw' => (int) $request->get('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Server error occurred'
            ], 500);
        }
    }

    /**
     * Get deposit statistics for dashboard cards
     */
    private function getDepositStats()
    {
        try {
            // Crypto deposits (NOWPayments)
            $cryptoDeposits = Deposit::where('status', 'completed')->where('payment_method', 'crypto')->count();
            $cryptoAmount = Deposit::where('status', 'completed')->where('payment_method', 'crypto')->sum('amount');

            // Direct deposits (Manual)
            $directDeposits = Deposit::where('status', 'completed')->where('payment_method', 'manual')->count();
            $directAmount = Deposit::where('status', 'completed')->where('payment_method', 'manual')->sum('amount');

            // XEUSD deposits
            $xeusdDeposits = Deposit::where('status', 'completed')->where('payment_method', 'xeusd')->count();
            $xeusdAmount = Deposit::where('status', 'completed')->where('payment_method', 'xeusd')->sum('amount');

            // Marketplace deposits
            $marketplaceDeposits = DB::table('marketplace_listings')
                ->where('status', 'sold')
                ->whereNotNull('buyer_id')
                ->count();
            $marketplaceAmount = DB::table('marketplace_listings')
                ->where('status', 'sold')
                ->whereNotNull('buyer_id')
                ->sum('amount');

            // Manual admin deposits
            $manualDeposits = TransactionHistory::where('transaction_type', 'add')
                ->where('balance_type', 'deposit')
                ->whereNotNull('admin_id')
                ->count();
            $manualAmount = TransactionHistory::where('transaction_type', 'add')
                ->where('balance_type', 'deposit')
                ->whereNotNull('admin_id')
                ->sum('amount');

            $totalDeposits = $cryptoDeposits + $directDeposits + $xeusdDeposits + $marketplaceDeposits + $manualDeposits;
            $totalAmount = $cryptoAmount + $directAmount + $xeusdAmount + $marketplaceAmount + $manualAmount;

            return [
                'totalDeposits' => $totalDeposits,
                'totalAmount' => $totalAmount,
                'cryptoDeposits' => $cryptoDeposits,
                'xeusdDeposits' => $xeusdDeposits,
                'marketplaceDeposits' => $marketplaceDeposits,
                'directDeposits' => $directDeposits,
                'manualDeposits' => $manualDeposits,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting deposit stats: ' . $e->getMessage());

            return [
                'totalDeposits' => 0,
                'totalAmount' => 0,
                'cryptoDeposits' => 0,
                'xeusdDeposits' => 0,
                'marketplaceDeposits' => 0,
                'directDeposits' => 0,
                'manualDeposits' => 0,
            ];
        }
    }

    /**
     * Get deposit source badge HTML
     */
    private function getDepositSourceBadge($sourceType)
    {
        switch ($sourceType) {
            case 'crypto_deposit':
                return '<span class="badge bg-label-success">Crypto Deposit</span>';
            case 'xeusd_deposit':
                return '<span class="badge bg-label-success"><i class="fas fa-coins me-1"></i>XEUSD</span>';
            case 'marketplace':
                return '<span class="badge bg-label-primary">Marketplace</span>';
            case 'direct_deposit':
                return '<span class="badge bg-label-warning">Direct Deposit</span>';
            case 'admin_manual':
                return '<span class="badge bg-label-info">Admin Manual</span>';
            default:
                return '<span class="badge bg-label-secondary">Unknown</span>';
        }
    }
}
