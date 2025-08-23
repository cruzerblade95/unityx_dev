<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketplaceController extends Controller
{
    /**
     * Display marketplace transactions overview for admin
     */
    public function index(Request $request)
    {
        $query = MarketplaceListing::with(['seller', 'buyer'])
            ->whereNotNull('buyer_id') // Only transactions that had buyer interaction
            ->orderBy('updated_at', 'desc');

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by username
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('seller', function($sq) use ($search) {
                    $sq->where('username', 'like', "%{$search}%")
                      ->orWhere('firstname', 'like', "%{$search}%")
                      ->orWhere('lastname', 'like', "%{$search}%");
                })->orWhereHas('buyer', function($bq) use ($search) {
                    $bq->where('username', 'like', "%{$search}%")
                      ->orWhere('firstname', 'like', "%{$search}%")
                      ->orWhere('lastname', 'like', "%{$search}%");
                });
            });
        }

        $transactions = $query->paginate(20);

        // Calculate totals for summary
        $totalTransactions = MarketplaceListing::whereNotNull('buyer_id')->count();
        $completedTransactions = MarketplaceListing::whereNotNull('buyer_id')->where('status', 'completed')->count();
        $pendingTransactions = MarketplaceListing::whereNotNull('buyer_id')->where('status', 'pending_verification')->count();
        $cancelledTransactions = MarketplaceListing::whereNotNull('buyer_id')->where('status', 'cancelled')->count();

        $totalVolume = MarketplaceListing::whereNotNull('buyer_id')
            ->where('status', 'completed')
            ->sum('total_price');

        $totalFees = MarketplaceListing::whereNotNull('buyer_id')
            ->where('status', 'completed')
            ->sum(DB::raw('total_price * 0.018')); // 1.8% fee

        return view('admin.content.marketplace', compact(
            'transactions',
            'totalTransactions',
            'completedTransactions',
            'pendingTransactions',
            'cancelledTransactions',
            'totalVolume',
            'totalFees'
        ));
    }

    /**
     * Show detailed view of a specific transaction
     */
    public function show($id)
    {
        $transaction = MarketplaceListing::with(['seller', 'buyer'])
            ->where('unique_id', $id)
            ->firstOrFail();

        return view('admin.content.marketplace-detail', compact('transaction'));
    }
}
