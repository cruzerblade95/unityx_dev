<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceListing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarketplaceController extends Controller
{
    /**
     * Display the marketplace
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get user wallet balances
        $wallet = $user->wallet;
        $cashBalance = $wallet ? $wallet->cash : 0;
        $profitBalance = $wallet ? $wallet->profit : 0;
        
        // Get active listings with pagination (16 per page for 4x4 grid)
        $listings = MarketplaceListing::with('seller')
            ->active()
            ->where('seller_id', '!=', Auth::id()) // Don't show user's own listings
            ->orderBy('created_at', 'desc')
            ->paginate(16);
        
        // Get user's own listings
        $myListings = MarketplaceListing::bySeller(Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get user's pending verifications (as buyer)
        $pendingVerifications = MarketplaceListing::byBuyer(Auth::id())
            ->pendingVerification()
            ->with('seller')
            ->orderBy('verification_started_at', 'desc')
            ->get();

        // Get user's verification requests (as seller)
        $verificationRequests = MarketplaceListing::bySeller(Auth::id())
            ->pendingVerification()
            ->with('buyer')
            ->orderBy('verification_started_at', 'desc')
            ->get();
        
        return view('user.content.marketplace', compact('listings', 'myListings', 'pendingVerifications', 'verificationRequests', 'cashBalance', 'profitBalance'));
    }

    /**
     * Create a new listing (sell USDT)
     */
    public function createListing(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'price_per_usdt' => 'required|numeric|min:0.95|max:1.5',
            'description' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet || $wallet->cash < $request->amount) {
            return redirect()->back()->withErrors(['error' => 'Insufficient cash balance to create this listing.']);
        }

        try {
            DB::beginTransaction();

            // Deduct USDT from cash wallet
            $wallet->cash -= $request->amount;
            $wallet->save();

            // Create listing
            $listing = MarketplaceListing::create([
                'unique_id' => MarketplaceListing::generateUniqueId(),
                'seller_id' => Auth::id(),
                'amount' => $request->amount,
                'price_per_usdt' => $request->price_per_usdt,
                'total_price' => $request->amount * $request->price_per_usdt,
                'description' => $request->description,
                'status' => 'active',
            ]);

            DB::commit();
            
            return redirect()->back()->with('success', 'USDT listing created successfully! Listing ID: ' . $listing->unique_id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating marketplace listing: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to create listing. Please try again.']);
        }
    }

    /**
     * Start verification process for a listing (buyer initiates contact)
     */
    public function purchaseListing(Request $request, $listingId)
    {
        $listing = MarketplaceListing::where('unique_id', $listingId)->firstOrFail();
        
        if (!$listing->isPurchasable()) {
            return redirect()->back()->withErrors(['error' => 'This listing is no longer available.']);
        }

        if ($listing->seller_id === Auth::id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot purchase your own listing.']);
        }

        $user = Auth::user();
        $wallet = $user->wallet;
        
        $feePercent = 1.8;
        $feeAmount = $listing->total_price * ($feePercent / 100);
        $totalCost = $listing->total_price + $feeAmount;

        if (!$wallet || $wallet->cash < $totalCost) {
            return redirect()->back()->withErrors(['error' => 'Insufficient cash balance. You need $' . number_format($totalCost, 2) . ' (including 1.8% fee).']);
        }

        try {
            DB::beginTransaction();

            // Start verification process
            $listing->startVerification(Auth::id());

            DB::commit();
            
            return redirect()->back()->with('success', 'Verification started! Please contact the seller via Telegram within 45 minutes. Check your "Pending Verifications" section.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting marketplace verification: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to start verification. Please try again.']);
        }
    }

    /**
     * Seller approves a verification (completes the transaction)
     */
    public function approveVerification(Request $request, $listingId)
    {
        $listing = MarketplaceListing::where('unique_id', $listingId)
            ->where('seller_id', Auth::id())
            ->where('status', 'pending_verification')
            ->firstOrFail();

        if ($listing->isVerificationExpired()) {
            $listing->cancelVerification('Verification expired');
            return redirect()->back()->withErrors(['error' => 'Verification has expired.']);
        }

        try {
            DB::beginTransaction();

            $buyer = $listing->buyer;
            $buyerWallet = $buyer->wallet;
            $sellerWallet = Auth::user()->wallet;
            
            $feePercent = 1.8;
            $feeAmount = $listing->total_price * ($feePercent / 100);
            $totalCost = $listing->total_price + $feeAmount;

            // Verify buyer still has sufficient funds
            if (!$buyerWallet || $buyerWallet->cash < $totalCost) {
                DB::rollBack();
                return redirect()->back()->withErrors(['error' => 'Buyer no longer has sufficient cash balance.']);
            }

            // Process the transaction
            $buyerWallet->cash -= $totalCost;
            $buyerWallet->deposit += $listing->amount;
            $buyerWallet->save();

            $sellerWallet->cash += $listing->total_price;
            $sellerWallet->save();

            // Complete the listing
            $listing->completeTransaction(true, 'Transaction approved by seller');

            DB::commit();
            
            return redirect()->back()->with('success', 'Transaction approved! USDT has been transferred to the buyer.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving marketplace verification: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to approve transaction. Please try again.']);
        }
    }

    /**
     * Seller rejects a verification
     */
    public function rejectVerification(Request $request, $listingId)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $listing = MarketplaceListing::where('unique_id', $listingId)
            ->where('seller_id', Auth::id())
            ->where('status', 'pending_verification')
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $listing->completeTransaction(false, $request->reason);

            DB::commit();
            
            return redirect()->back()->with('success', 'Transaction rejected. Listing is now available again.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting marketplace verification: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to reject transaction. Please try again.']);
        }
    }

    /**
     * Cancel verification (buyer cancels)
     */
    public function cancelVerification(Request $request, $listingId)
    {
        $listing = MarketplaceListing::where('unique_id', $listingId)
            ->where('buyer_id', Auth::id())
            ->where('status', 'pending_verification')
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $listing->cancelVerification('Cancelled by buyer');

            DB::commit();
            
            return redirect()->back()->with('success', 'Verification cancelled. Listing is now available again.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling marketplace verification: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to cancel verification. Please try again.']);
        }
    }
    public function cancelListing(Request $request, $listingId)
    {
        $listing = MarketplaceListing::where('unique_id', $listingId)
            ->where('seller_id', Auth::id())
            ->where('status', 'active')
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // Return USDT to seller's cash wallet
            $user = Auth::user();
            $wallet = $user->wallet;
            if ($wallet) {
                $wallet->cash += $listing->amount;
                $wallet->save();
            }

            // Update listing status
            $listing->update(['status' => 'cancelled']);

            DB::commit();
            
            return redirect()->back()->with('success', 'Listing cancelled successfully. USDT returned to your cash wallet.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling marketplace listing: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to cancel listing. Please try again.']);
        }
    }

    /**
     * Top up cash wallet from profit wallet
     */
    public function topUpCash(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;
        
        $feePercent = 1.5;
        $feeAmount = $request->amount * ($feePercent / 100);
        $totalDeduction = $request->amount + $feeAmount;

        if (!$wallet || $wallet->profit < $totalDeduction) {
            return redirect()->back()->withErrors(['error' => 'Insufficient profit balance. You need $' . number_format($totalDeduction, 2) . ' (including 1.5% fee).']);
        }

        try {
            DB::beginTransaction();

            // Deduct from profit wallet (including fee)
            $wallet->profit -= $totalDeduction;
            
            // Add to cash wallet (without fee)
            $wallet->cash += $request->amount;
            
            $wallet->save();

            DB::commit();
            
            return redirect()->back()->with('success', 'Cash wallet topped up successfully! $' . number_format($request->amount, 2) . ' added to your cash wallet.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error topping up cash wallet: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to top up cash wallet. Please try again.']);
        }
    }
}
