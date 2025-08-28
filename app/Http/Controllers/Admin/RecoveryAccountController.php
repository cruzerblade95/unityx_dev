<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RecoveryAccount;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

class RecoveryAccountController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = RecoveryAccount::with('processedBy')->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $recoveries = $query->paginate(15);

        return view('admin.recovery_accounts.index', compact('recoveries', 'status'));
    }

    public function show($id)
    {
        $recovery = RecoveryAccount::with('processedBy')->findOrFail($id);
        return view('admin.recovery_accounts.show', compact('recovery'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            'deposit' => 'required|numeric|min:0',
            'profit' => 'required|numeric|min:0',
            'replace_upline' => 'nullable|boolean',
        ]);

        $recovery = RecoveryAccount::findOrFail($id);

        if ($recovery->status !== 'pending') {
            return redirect()->back()->with('error', 'This recovery request has already been processed.');
        }

        // Find the user by email from the recovery request
        $user = \App\Models\User::where('email', $recovery->email)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'User not found for the provided email address.');
        }

        // Get the adjusted amounts from the form
        $adjustedDeposit = $request->deposit;
        $adjustedProfit = $request->profit;

        // Handle upline_id replacement if checkbox is checked and referral_username exists
        $uplineReplaced = false;
        if ($request->replace_upline && $recovery->referral_username) {
            $referralUser = \App\Models\User::where('username', $recovery->referral_username)->first();
            if ($referralUser) {
                $user->update(['upline_id' => $referralUser->id]);
                $uplineReplaced = true;
            }
        }

        // Get or create user's wallet
        $wallet = \App\Models\Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'deposit' => 0,
                'profit' => 0,
                'bonus' => 0,
                'cash' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Update wallet with adjusted recovery values
        $wallet->update([
            'deposit' => $adjustedDeposit,
            'profit' => $adjustedProfit,
            'updated_at' => now(),
        ]);

        // Update recovery request status with adjusted amounts note
        $originalNote = $request->admin_notes ? $request->admin_notes . "\n\n" : '';
        $adjustmentNote = "Original amounts - Deposit: $" . number_format($recovery->deposit, 2) . ", Profit: $" . number_format($recovery->profit, 2);
        $adjustmentNote .= "\nAdjusted amounts - Deposit: $" . number_format($adjustedDeposit, 2) . ", Profit: $" . number_format($adjustedProfit, 2);

        if ($uplineReplaced) {
            $adjustmentNote .= "\nUpline replaced with referral user: " . $recovery->referral_username;
        }

        $recovery->update([
            'status' => 'approved',
            'admin_notes' => $originalNote . $adjustmentNote,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        $successMessage = "Recovery request approved successfully. User's wallet has been updated with deposit: $" . number_format($adjustedDeposit, 2) . " and profit: $" . number_format($adjustedProfit, 2) . ".";
        if ($uplineReplaced) {
            $successMessage .= " User's upline has been updated to: " . $recovery->referral_username . ".";
        }

        return redirect()->route('admin.recovery_accounts.index')
            ->with('success', $successMessage);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $recovery = RecoveryAccount::findOrFail($id);

        if ($recovery->status !== 'pending') {
            return redirect()->back()->with('error', 'This recovery request has already been processed.');
        }

        $recovery->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        return redirect()->route('admin.recovery_accounts.index')
            ->with('success', 'Recovery request rejected successfully.');
    }
}
