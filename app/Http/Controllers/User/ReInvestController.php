<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReInvestController extends Controller
{
    /**
     * Display the re-invest page
     */
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        
        // Get profit balance
        $profitBalance = $wallet ? $wallet->profit : 0;
        
        // Get user's recent AI trades for display
        $recentAiTrades = DB::table('ai_trades')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('user.content.re-invest', compact('user', 'profitBalance', 'recentAiTrades'));
    }

    /**
     * Process the re-investment
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'confirm_transfer' => 'required|accepted',
        ], [
            'amount.required' => 'Please enter an amount to re-invest.',
            'amount.min' => 'Minimum re-investment amount is $1.00.',
            'confirm_transfer.accepted' => 'Please confirm that you want to proceed with the transfer.',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        // Check if user has sufficient profit balance
        if (!$wallet || $wallet->profit < $request->amount) {
            return redirect()->back()->withErrors([
                'amount' => 'Insufficient profit balance. Available: $' . number_format($wallet ? $wallet->profit : 0, 2)
            ]);
        }

        try {
            DB::beginTransaction();

            // Transfer from profit to deposit
            $wallet->profit -= $request->amount;
            $wallet->deposit += $request->amount;
            $wallet->save();

            // Create AI Trade with the transferred amount
            $aiTradeId = Helpers::insertAiTradeV2($user->id, $request->amount);

            // Log the transaction
            Log::info("Re-Investment: User {$user->id} transferred {$request->amount} from profit to deposit and created AI Trade {$aiTradeId}");

            DB::commit();

            return redirect()->back()->with('success', 
                'Re-investment successful! $' . number_format($request->amount, 2) . 
                ' transferred from profit to deposit and automatically invested in AI Trade (5x potential return). Trade ID: ' . $aiTradeId
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing re-investment: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to process re-investment. Please try again.']);
        }
    }
}
