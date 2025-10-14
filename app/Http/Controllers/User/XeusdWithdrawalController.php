<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\XeusdWithdrawal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class XeusdWithdrawalController extends Controller
{
    /**
     * Display the XEUSD withdrawal page
     */
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        // Get wallet balances
        $cashBalance = $wallet ? $wallet->cash : 0;
        $profitBalance = $wallet ? $wallet->profit : 0;
        $bonusBalance = $wallet ? $wallet->bonus : 0;

        // Get user's withdrawal history
        $withdrawals = XeusdWithdrawal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.content.xeusd-withdrawal', compact(
            'cashBalance',
            'profitBalance',
            'bonusBalance',
            'withdrawals'
        ));
    }

    /**
     * Connect MetaMask wallet address
     */
    public function connectWallet(Request $request)
    {
        $request->validate([
            'wallet_address' => 'required|string|size:42|starts_with:0x',
        ]);

        $user = Auth::user();

        // Check if wallet address is already used by another user
        $existingUser = User::where('wallet_address', $request->wallet_address)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'This wallet address is already connected to another account.'
            ], 400);
        }

        try {
            $user->update([
                'wallet_address' => strtolower($request->wallet_address),
                'wallet_connected_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wallet connected successfully!',
                'wallet_address' => $user->getFormattedWalletAddressAttribute()
            ]);
        } catch (\Exception $e) {
            Log::error('Error connecting wallet: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect wallet. Please try again.'
            ], 500);
        }
    }

    /**
     * Create XEUSD withdrawal request
     */
    public function create(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:500',
            'source_wallet' => 'required|in:profit,bonus,cash',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        // Check if user has connected wallet
        if (!$user->hasConnectedWallet()) {
            return redirect()->back()->withErrors(['error' => 'Please connect your MetaMask wallet first.']);
        }

        if (!$wallet) {
            return redirect()->back()->withErrors(['error' => 'Wallet not found.']);
        }

        $sourceWallet = $request->source_wallet;
        $amount = $request->amount;

        // Check if sufficient balance exists in selected wallet
        $availableBalance = match($sourceWallet) {
            'profit' => $wallet->profit,
            'bonus' => $wallet->bonus,
            'cash' => $wallet->cash,
            default => 0
        };

        if ($availableBalance < $amount) {
            $walletName = ucfirst($sourceWallet);
            return redirect()->back()->withErrors(['error' => "Insufficient {$walletName} balance. You have $" . number_format($availableBalance, 2) . ' available.']);
        }

        try {
            DB::beginTransaction();

            // Deduct from selected wallet
            match($sourceWallet) {
                'profit' => $wallet->profit -= $amount,
                'bonus' => $wallet->bonus -= $amount,
                'cash' => $wallet->cash -= $amount
            };
            $wallet->save();

            // Create withdrawal request
            $withdrawal = XeusdWithdrawal::create([
                'user_id' => $user->id,
                'unique_id' => XeusdWithdrawal::generateUniqueId(),
                'amount_usd' => $amount,
                'amount_xeusd' => $amount, // 1:1 conversion rate
                'source_wallet' => $sourceWallet,
                'recipient_address' => $user->wallet_address,
                'status' => 'pending'
            ]);

            DB::commit();

            return redirect()->back()->with('success',
                'XEUSD withdrawal request created successfully! Reference: ' . $withdrawal->unique_id .
                '. Your request will be processed by our admin team.'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating XEUSD withdrawal: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to create withdrawal request. Please try again.']);
        }
    }

    /**
     * Get withdrawal status via API
     */
    public function getWithdrawalStatus($uniqueId)
    {
        $withdrawal = XeusdWithdrawal::where('unique_id', $uniqueId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$withdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'withdrawal' => [
                'unique_id' => $withdrawal->unique_id,
                'amount_usd' => $withdrawal->amount_usd,
                'amount_xeusd' => $withdrawal->amount_xeusd,
                'status' => $withdrawal->status,
                'transaction_hash' => $withdrawal->transaction_hash,
                'created_at' => $withdrawal->created_at->format('M d, Y H:i'),
                'processed_at' => $withdrawal->processed_at?->format('M d, Y H:i'),
                'admin_notes' => $withdrawal->admin_notes
            ]
        ]);
    }
}
