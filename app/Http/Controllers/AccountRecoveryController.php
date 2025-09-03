<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RecoveryAccount;

class AccountRecoveryController extends Controller
{
    public function showEmailForm()
    {
        return view('account_recovery.email_form');
    }

    public function validateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        $pageConfigs = ['myLayout' => 'blank'];

        // Check if user exists
        $user = User::where('email', $email)->first();

        if ($user) {
            // Check if user has already submitted a recovery request
            $existingRecovery = RecoveryAccount::where('email', $email)->first();

            if ($existingRecovery) {
                // User has already submitted a recovery request, show status instead
                return view('account_recovery.status', compact('existingRecovery', 'pageConfigs'));
            }

            // Store email in session and redirect to details page
            session(['recovery_email' => $email]);
            return redirect()->route('account-recovery.details');
        } else {
            // Email not found, redirect to registration with alert
            return redirect()->route('user.register')->with('email_not_found', true);
        }
    }

    public function showDetailsForm()
    {
        if (!session('recovery_email')) {
            return redirect()->route('account-recovery.email');
        }

        $email = session('recovery_email');

        // Double check if user has already submitted a recovery request
        $existingRecovery = RecoveryAccount::where('email', $email)->first();

        if ($existingRecovery) {
            // Clear session and show status instead
            session()->forget('recovery_email');
            $pageConfigs = ['myLayout' => 'blank'];
            return view('account_recovery.status', compact('existingRecovery', 'pageConfigs'));
        }

        return view('account_recovery.details_form');
    }

    public function submitRecovery(Request $request)
    {
        if (!session('recovery_email')) {
            return redirect()->route('account-recovery.email');
        }

        $request->validate([
            'email' => 'required|email|same:recovery_email',
            'deposit' => 'required|numeric|min:0',
            'profit' => 'required|numeric|min:0',
            'referral_username' => 'nullable|string|max:255',
        ]);

        // Double check email matches session
        if ($request->email !== session('recovery_email')) {
            return redirect()->back()->withErrors([
                'email' => 'Email does not match the one you entered previously.'
            ])->withInput();
        }

        // Final check for duplicate submission
        $existingRecovery = RecoveryAccount::where('email', $request->email)->first();
        if ($existingRecovery) {
            session()->forget('recovery_email');
            $pageConfigs = ['myLayout' => 'blank'];
            return view('account_recovery.status', compact('existingRecovery', 'pageConfigs'))
                ->with('warning', 'You have already submitted a recovery request for this email.');
        }

        RecoveryAccount::create([
            'email' => $request->email,
            'deposit' => $request->deposit,
            'profit' => $request->profit,
            'referral_username' => $request->referral_username,
            'status' => 'pending',
        ]);

        // Clear session
        session()->forget('recovery_email');

        return redirect()->route('account-recovery.success');
    }

    public function showSuccess()
    {
        return view('account_recovery.success');
    }

    /**
     * Search for referral users
     */
    public function searchReferralUsers(Request $request)
    {
        $query = $request->get('query');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where(function($q) use ($query) {
            $q->where('firstname', 'LIKE', "%{$query}%")
              ->orWhere('lastname', 'LIKE', "%{$query}%")
              ->orWhere('username', 'LIKE', "%{$query}%")
              ->orWhere('referral_code', 'LIKE', "%{$query}%");
        })
        ->select('id', 'firstname', 'lastname', 'username', 'referral_code')
        ->limit(10)
        ->get()
        ->map(function($user) {
            return [
                'id' => $user->id,
                'name' => trim($user->firstname . ' ' . $user->lastname),
                'username' => $user->username,
                'referral_code' => $user->referral_code,
                'display' => trim($user->firstname . ' ' . $user->lastname) . " (@{$user->username})"
            ];
        });

        return response()->json($users);
    }

    public function resetRecovery(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // Find the existing recovery request
        $existingRecovery = RecoveryAccount::where('email', $email)->first();

        if ($existingRecovery && $existingRecovery->status === 'rejected') {
            // Delete the rejected recovery request to allow a new one
            $existingRecovery->delete();

            // Store email in session and redirect to details page
            session(['recovery_email' => $email]);
            return redirect()->route('account-recovery.details')
                ->with('success', 'Previous request cleared. You can now submit a new recovery request.');
        }

        return redirect()->route('account-recovery.email')
            ->with('error', 'Cannot reset recovery request.');
    }
}
