<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PaymentInfoController extends Controller
{
    public function index()
    {
        return view('user.content.payment-info');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'national_id' => 'required|string|max:50|regex:/^[A-Za-z0-9\-]+$/',
            'usdt_bep20_address' => 'nullable|string|max:255|regex:/^[A-Za-z0-9]+$/',
            'usdc_bep20_address' => 'nullable|string|max:255|regex:/^[A-Za-z0-9]+$/',
            'fdusd_bep20_address' => 'nullable|string|max:255|regex:/^[A-Za-z0-9]+$/',
            'etc_black_card_number' => 'nullable|string|max:19|regex:/^[0-9\s]+$/',
            'account_number' => 'nullable|string|max:20|regex:/^[0-9]+$/',
            'cardholder_name' => 'required|string|max:100|regex:/^[A-Za-z\s\.]+$/',
        ], [
            'national_id.regex' => 'National ID must contain only letters, numbers, and hyphens.',
            'usdt_bep20_address.regex' => 'USDT wallet address must contain only letters and numbers.',
            'usdc_bep20_address.regex' => 'USDC wallet address must contain only letters and numbers.',
            'fdusd_bep20_address.regex' => 'FDUSD wallet address must contain only letters and numbers.',
            'etc_black_card_number.regex' => 'Card number must contain only numbers and spaces.',
            'account_number.regex' => 'Account number must contain only numbers.',
            'cardholder_name.regex' => 'Cardholder name must contain only letters, spaces, and periods.',
        ]);

        // Check that at least one crypto wallet address is provided
        if (empty($request->usdt_bep20_address) &&
            empty($request->usdc_bep20_address) &&
            empty($request->fdusd_bep20_address)) {
            return back()
                ->withErrors(['crypto_addresses' => 'At least one crypto wallet address (USDT, USDC, or FDUSD) is required.'])
                ->withInput();
        }

        // Update user using the User model
        User::where('id', $user->id)->update([
            'national_id' => strtoupper($request->national_id),
            'usdt_bep20_address' => $request->usdt_bep20_address,
            'usdc_bep20_address' => $request->usdc_bep20_address,
            'fdusd_bep20_address' => $request->fdusd_bep20_address,
            'etc_black_card_number' => $request->etc_black_card_number ? preg_replace('/\s+/', ' ', $request->etc_black_card_number) : null,
            'account_number' => $request->account_number,
            'cardholder_name' => ucwords(strtolower($request->cardholder_name)),
            'payment_info_completed' => true,
        ]);

        return redirect()->route('user.profile')
            ->with('success', 'Payment information has been saved successfully! You can now access withdrawal features.');
    }
}
