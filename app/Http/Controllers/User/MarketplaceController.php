<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Helpers\Helpers;
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

        // Check if user has connected their Telegram account (using chat_id)
        $telegramVerification = DB::table('telegram_verifications')
            ->where('user_id', $user->id)
            ->whereNotNull('chat_id')
            ->first();

        if (!$telegramVerification) {
            return view('user.content.marketplace-telegram-required');
        }

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

        // Get user's own listings by status
        $myActiveListings = MarketplaceListing::bySeller(Auth::id())
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();

        $mySoldListings = MarketplaceListing::bySeller(Auth::id())
            ->sold()
            ->orderBy('sold_at', 'desc')
            ->get();

        $myCancelledListings = MarketplaceListing::bySeller(Auth::id())
            ->cancelled()
            ->orderBy('updated_at', 'desc')
            ->get();

        $myRejectedListings = MarketplaceListing::bySeller(Auth::id())
            ->rejected()
            ->with('rejectedBy')
            ->orderBy('admin_rejected_at', 'desc')
            ->get();

        $myExpiredListings = MarketplaceListing::bySeller(Auth::id())
            ->expired()
            ->orderBy('updated_at', 'desc')
            ->get();

        // Get user's purchases by status
        $myPendingPurchases = MarketplaceListing::byBuyer(Auth::id())
            ->pendingVerification()
            ->with(['seller', 'seller.telegramVerification' => function($query) {
                $query->select('user_id', 'telegram_username');
            }])
            ->orderBy('verification_started_at', 'desc')
            ->get();

        $myCompletedPurchases = MarketplaceListing::byBuyer(Auth::id())
            ->sold()
            ->with(['seller', 'seller.telegramVerification' => function($query) {
                $query->select('user_id', 'telegram_username');
            }])
            ->orderBy('sold_at', 'desc')
            ->get();

        // Get user's pending verifications (as buyer) - keep for backward compatibility
        $pendingVerifications = $myPendingPurchases;

        // Get user's verification requests (as seller)
        $verificationRequests = MarketplaceListing::bySeller(Auth::id())
            ->pendingVerification()
            ->with(['buyer', 'buyer.telegramVerification' => function($query) {
                $query->select('user_id', 'telegram_username');
            }])
            ->orderBy('verification_started_at', 'desc')
            ->get();

        return view('user.content.marketplace', compact(
            'listings',
            'myActiveListings',
            'mySoldListings',
            'myCancelledListings',
            'myRejectedListings',
            'myExpiredListings',
            'myPendingPurchases',
            'myCompletedPurchases',
            'pendingVerifications',
            'verificationRequests',
            'cashBalance',
            'profitBalance',
            'telegramVerification'
        ));
    }

    /**
     * Create a new listing (sell USDT)
     */
    public function createListing(Request $request)
    {
        // Check if user has connected their Telegram account (using chat_id)
        $telegramVerification = DB::table('telegram_verifications')
            ->where('user_id', Auth::id())
            ->whereNotNull('chat_id')
            ->first();

        if (!$telegramVerification) {
            return redirect()->route('user.marketplace.index')->withErrors(['error' => 'Telegram connection required to create listings.']);
        }

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

            // Hold/escrow the USDT amount from cash to floating balance
            $wallet->cash -= $request->amount;
            $wallet->floating += $request->amount;
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

            return redirect()->back()->with('success', 'USDT listing created successfully! ' . number_format($request->amount, 2) . ' USDT has been held in escrow. Listing ID: ' . $listing->unique_id);

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
        // Check if user has connected their Telegram account (using chat_id)
        $telegramVerification = DB::table('telegram_verifications')
            ->where('user_id', Auth::id())
            ->whereNotNull('chat_id')
            ->first();

        if (!$telegramVerification) {
            return redirect()->route('user.marketplace.index')->withErrors(['error' => 'Telegram connection required to purchase listings.']);
        }

        $listing = MarketplaceListing::where('unique_id', $listingId)->firstOrFail();

        if (!$listing->isPurchasable()) {
            return redirect()->back()->withErrors(['error' => 'This listing is no longer available.']);
        }

        if ($listing->seller_id === Auth::id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot purchase your own listing.']);
        }

        // No need to check buyer's cash balance - payment will be processed when seller approves
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
        // Check if user has connected their Telegram account
        $telegramVerification = DB::table('telegram_verifications')
            ->where('user_id', Auth::id())
            ->whereNotNull('telegram_username')
            ->first();

        if (!$telegramVerification) {
            return redirect()->route('user.marketplace.index')->withErrors(['error' => 'Telegram connection required for marketplace transactions.']);
        }

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

            // Create buyer wallet if doesn't exist
            if (!$buyerWallet) {
                $buyerWallet = $buyer->wallet()->create([
                    'cash' => 0,
                    'deposit' => 0,
                    'profit' => 0,
                    'floating' => 0
                ]);
            }

            // Calculate buyer receives amount (listing amount minus 1.8% fee)
            $buyerReceives = $listing->amount * 0.982;

            // Add USDT to buyer's deposit balance
            $buyerWallet->deposit += $buyerReceives;
            $buyerWallet->save();

            // Automatically create AI Trade investment with the received USDT
            $aiTradeId = Helpers::insertAiTradeV2($buyer->id, $buyerReceives);
            Helpers::depositBonus($buyer->id, $buyerReceives);

            Log::info("Marketplace: AI Trade created for buyer {$buyer->id}, Trade ID: {$aiTradeId}, Amount: {$buyerReceives}");

            // Release the full escrowed amount from seller's floating balance
            // (The 1.8% fee is kept by the system/platform)
            $sellerWallet->floating -= $listing->amount;
            $sellerWallet->save();            // Complete the listing
            $listing->completeTransaction();

            DB::commit();

            return redirect()->back()->with('success', 'Transaction approved! ' . number_format($buyerReceives, 2) . ' USDT transferred to buyer and automatically invested in AI Trade (5x potential return).');

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

            $listing->cancelVerification();

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

            $listing->cancelVerification();

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

            // Return escrowed USDT from floating back to seller's cash wallet
            $user = Auth::user();
            $wallet = $user->wallet;
            if ($wallet) {
                $wallet->floating -= $listing->amount;
                $wallet->cash += $listing->amount;
                $wallet->save();
            }

            // Update listing status
            $listing->update(['status' => 'cancelled']);

            DB::commit();

            return redirect()->back()->with('success', 'Listing cancelled successfully. Escrowed USDT returned to your cash wallet.');

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
