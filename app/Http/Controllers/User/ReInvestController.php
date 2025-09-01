<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ReinvestmentSettings;
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

        // Get profit and bonus balances
        $profitBalance = $wallet ? $wallet->profit : 0;
        $bonusBalance = $wallet ? $wallet->bonus : 0;

        // Get reinvestment settings for both wallet types
        $profitSettings = ReinvestmentSettings::getUserSettings($user->id, 'profit');
        $bonusSettings = ReinvestmentSettings::getUserSettings($user->id, 'bonus');

        // Get user's recent AI trades for display
        $recentAiTrades = DB::table('ai_trades')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get re-investment history (AI trades created through re-investment)
        $reInvestHistory = DB::table('ai_trades')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('user.content.re-invest', compact(
            'user',
            'profitBalance',
            'bonusBalance',
            'profitSettings',
            'bonusSettings',
            'recentAiTrades',
            'reInvestHistory'
        ));
    }

    /**
     * Process the re-investment
     */
    public function store(Request $request)
    {
        // Get reinvestment settings first to validate against them
        $user = Auth::user();
        $settings = ReinvestmentSettings::getUserSettings($user->id, $request->wallet_type);

        // Dynamic validation rules based on settings
        $rules = [
            'amount' => 'required|numeric',
            'wallet_type' => 'required|in:profit,bonus',
            'confirm_transfer' => 'required|accepted',
        ];

        if ($settings) {
            $rules['amount'] .= '|min:' . $settings->min_amount . '|max:' . $settings->max_amount;
        } else {
            $rules['amount'] .= '|min:1'; // Fallback minimum
        }

        $messages = [
            'amount.required' => 'Please enter an amount to re-invest.',
            'amount.min' => $settings ? "Minimum re-investment amount is $" . number_format($settings->min_amount, 2) . "." : 'Minimum re-investment amount is $1.00.',
            'amount.max' => $settings ? "Maximum re-investment amount is $" . number_format($settings->max_amount, 2) . "." : '',
            'wallet_type.required' => 'Please select a wallet type.',
            'wallet_type.in' => 'Invalid wallet type selected.',
            'confirm_transfer.accepted' => 'Please confirm that you want to proceed with the transfer.',
        ];

        $request->validate($rules, $messages);

        // Check if reinvestment is enabled for this wallet type
        if (!ReinvestmentSettings::isReinvestmentEnabled($user->id, $request->wallet_type)) {
            return redirect()->back()->withErrors([
                'wallet_type' => "Reinvestment is currently disabled for {$request->wallet_type} wallet."
            ]);
        }

        // Validate amount against settings
        if (!ReinvestmentSettings::validateReinvestmentAmount($user->id, $request->wallet_type, $request->amount)) {
            return redirect()->back()->withErrors([
                'amount' => "Amount not within allowed limits for {$request->wallet_type} wallet."
            ]);
        }

        $wallet = $user->wallet;

        // Check wallet exists
        if (!$wallet) {
            return redirect()->back()->withErrors([
                'error' => 'Wallet not found. Please contact support.'
            ]);
        }

        // Get the selected wallet balance
        $selectedBalance = $request->wallet_type === 'profit' ? $wallet->profit : $wallet->bonus;

        // Prevent division by zero and negative balance issues
        if ($selectedBalance <= 0) {
            return redirect()->back()->withErrors([
                'amount' => "No balance available in {$request->wallet_type} wallet."
            ]);
        }

        // Check if user has sufficient balance in selected wallet
        if ($selectedBalance < $request->amount) {
            return redirect()->back()->withErrors([
                'amount' => "Insufficient {$request->wallet_type} balance. Available: $" . number_format($selectedBalance, 2)
            ]);
        }

        // Calculate tax
        $taxAmount = ReinvestmentSettings::calculateTax($user->id, $request->wallet_type, $request->amount);
        $netAmount = $request->amount - $taxAmount;

        // Ensure net amount is positive
        if ($netAmount <= 0) {
            return redirect()->back()->withErrors([
                'amount' => 'Net amount after tax would be zero or negative. Please enter a higher amount.'
            ]);
        }

        try {
            DB::beginTransaction();

            // Transfer from selected wallet to deposit (full amount including tax)
            if ($request->wallet_type === 'profit') {
                $wallet->profit -= $request->amount;
            } else {
                $wallet->bonus -= $request->amount;
            }

            // Add net amount to deposit (after tax deduction)
            $wallet->deposit += $netAmount;
            $wallet->save();

            // Create AI Trade with the net amount
            $aiTradeId = Helpers::insertAiTradeV2($user->id, $netAmount);
            Helpers::depositBonus($user->id, $netAmount);

            // Log the transaction
            Log::info("Re-Investment: User {$user->id} transferred {$request->amount} from {$request->wallet_type} to deposit (net: {$netAmount}, tax: {$taxAmount}) and created AI Trade {$aiTradeId}");

            DB::commit();

            $message = 'Re-investment successful! $' . number_format($request->amount, 2) .
                " transferred from {$request->wallet_type} to deposit";

            if ($taxAmount > 0) {
                $message .= " (Tax: $" . number_format($taxAmount, 2) . ", Net: $" . number_format($netAmount, 2) . ")";
            }

            $message .= " and automatically invested in AI Trade (5x potential return). Trade ID: " . $aiTradeId;

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing re-investment: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to process re-investment. Please try again.']);
        }
    }
}
