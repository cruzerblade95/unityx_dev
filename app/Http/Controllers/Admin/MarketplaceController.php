<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $completedTransactions = MarketplaceListing::whereNotNull('buyer_id')->where('status', 'sold')->count();
        $pendingTransactions = MarketplaceListing::whereNotNull('buyer_id')->where('status', 'pending_verification')->count();
        $cancelledTransactions = MarketplaceListing::whereNotNull('buyer_id')->where('status', 'cancelled')->count();
        $rejectedTransactions = MarketplaceListing::whereNotNull('buyer_id')->where('status', 'rejected')->count();

        $totalVolume = MarketplaceListing::whereNotNull('buyer_id')
            ->where('status', 'sold')
            ->sum('total_price');

        $totalFees = MarketplaceListing::whereNotNull('buyer_id')
            ->where('status', 'sold')
            ->sum(DB::raw('total_price * 0.018')); // 1.8% fee

        return view('admin.content.marketplace', compact(
            'transactions',
            'totalTransactions',
            'completedTransactions',
            'pendingTransactions',
            'cancelledTransactions',
            'rejectedTransactions',
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

    /**
     * Reject a marketplace transaction with reason
     */
    public function reject(Request $request, $id)
    {
        try {
            // Debug: Log the incoming request
            Log::info('Reject request received', [
                'id' => $id,
                'request_data' => $request->all(),
                'admin_id' => auth('admin')->id(),
                'admin_user' => auth('admin')->user()
            ]);

            $request->validate([
                'reason' => 'required|string|max:500'
            ]);

            $transaction = MarketplaceListing::where('unique_id', $id)->firstOrFail();

            Log::info('Transaction found', [
                'transaction_id' => $transaction->id,
                'current_status' => $transaction->status
            ]);

            // Update transaction status to rejected
            $transaction->update([
                'status' => 'rejected',
                'admin_rejection_reason' => $request->reason,
                'admin_rejected_at' => now(),
                'admin_rejected_by' => auth('admin')->id()
            ]);

            Log::info('Transaction updated successfully');

            // Optional: Send notification to buyer and seller
            // You can implement notification logic here if needed

            return response()->json([
                'success' => true,
                'message' => 'Transaction rejected successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in reject', [
                'errors' => $e->validator->errors()->all(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Transaction not found in reject', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error rejecting marketplace transaction: ' . $e->getMessage(), [
                'transaction_id' => $id,
                'admin_id' => auth('admin')->id(),
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
