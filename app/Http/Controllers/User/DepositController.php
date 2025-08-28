<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deposit;
use App\Services\NOWPaymentsService;
use App\Services\WalletAddressService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DepositController extends Controller
{
    protected $nowPayments;
    protected $walletService;

    public function __construct(NOWPaymentsService $nowPayments, WalletAddressService $walletService)
    {
        $this->nowPayments = $nowPayments;
        $this->walletService = $walletService;
    }

    public function index()
    {
        // NOWPayments ONLY page - for crypto conversion deposits
        $deposits = Deposit::where('user_id', Auth::id())
                          ->where('payment_method', 'crypto')
                          ->whereNotIn('crypto_currency', ['usdt_bsc_TO_USDT', 'usdt_trc_TO_USDT', 'usdt_erc_TO_USDT'])
                          ->latest()
                          ->get();

        $currencies = $this->nowPayments->getAvailableCurrencies();
        $cryptoCurrencies = $currencies ? array_slice($currencies['currencies'], 0, 10) : [];

        return view('user.content.deposit', compact('deposits', 'cryptoCurrencies'));
    }

    public function direct()
    {
        // Manual deposit ONLY page - 1.8% fee added to amount
        $deposits = Deposit::where('user_id', Auth::id())
                          ->where('payment_method', 'manual')
                          ->latest()
                          ->get();

        return view('user.content.deposit-direct', compact('deposits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'payment_id' => 'required|string|min:10',
            'auto_reference' => 'nullable|string',
            'deposit_address' => 'nullable|string',
        ]);

        // Manual deposit fee is 1.8% ADDED to the amount (not deducted)
        $manualFeePercent = 1.8;
        $fee_amount = $request->amount * ($manualFeePercent / 100);
        $total_amount = $request->amount + $fee_amount; // User pays MORE than requested

        // Generate fallback reference if not provided
        $autoReference = $request->auto_reference ?: 'DEP_' . Auth::id() . '_' . time();
        $depositAddress = $request->deposit_address ?: 'TXFBqBkAjdoBiXRVsRqPW7DGNYqCX6jY8V'; // Default USDT address

        // Create the deposit with manual fee structure
        $deposit = Deposit::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount, // Base amount user requested
            'tax_percent' => $manualFeePercent,
            'tax_amount' => $fee_amount, // Fee amount ADDED
            'net_amount' => $request->amount, // User gets the full amount they requested
            'status' => 'pending',
            'reference' => $autoReference,
            'note' => $request->note,
            'payment_method' => 'manual',
            'payment_id' => $request->payment_id,
            'crypto_currency' => 'USDT',
            'payment_address' => $depositAddress,
            'unique_address' => $depositAddress,
            'address_memo' => $autoReference,
        ]);

        return redirect()->back()->with('success',
            'USDT deposit request submitted! Please send $' . number_format($total_amount, 2) .
            ' USD (includes 1.8% fee) to receive $' . number_format($request->amount, 2) .
            ' USDT. Reference: ' . $autoReference);
    }

    /**
     * Get unique wallet address for a new deposit
     */
    public function getUniqueAddress(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
        ]);

        try {
            // Create a temporary deposit to get unique address
            $tempDeposit = new Deposit([
                'user_id' => Auth::id(),
                'amount' => $request->amount,
            ]);

            // Generate a temporary ID for address generation
            $tempId = 'temp_' . Auth::id() . '_' . time();

            $addressData = $this->walletService->generateUniqueAddress(Auth::id(), $tempId);

            return response()->json([
                'success' => true,
                'address' => $addressData['address'],
                'memo' => $addressData['memo'],
                'expires_at' => $addressData['expires_at'],
                'instructions' => $addressData['instructions']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function createCryptoPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'crypto_currency' => 'required|string',
        ]);

        try {
            // NOWPayments fee is 1.8% ADDED to the requested amount (like manual deposits)
            $nowPaymentsFeePercent = 1.8;
            $fee_amount = $request->amount * ($nowPaymentsFeePercent / 100);
            $total_amount_to_pay = $request->amount + $fee_amount; // User pays MORE

            // All crypto currencies go through NOWPayments - NO USDT variants here
            // USDT variants should be handled in manual deposit (/user/deposit/direct)
            $orderId = 'crypto_deposit_' . Auth::id() . '_' . time();

            // Create payment with NOWPayments for the TOTAL amount (including our fee)
            $payment = $this->nowPayments->createPayment(
                $total_amount_to_pay, // Send total amount to NOWPayments
                'USD',
                $request->crypto_currency,
                $orderId,
                'Unity X Crypto to USDT Deposit (includes 1.8% fee)'
            );

            if (!$payment) {
                Log::error('NOWPayments createPayment returned false for user ' . Auth::id());
                return redirect()->back()->withErrors(['error' => 'Failed to create crypto payment. Please check the logs and try again.']);
            }

            // Check if the payment response has the required fields
            if (!isset($payment['payment_id'])) {
                Log::error('NOWPayments response missing payment_id', ['response' => $payment]);
                return redirect()->back()->withErrors(['error' => 'Invalid payment response from NOWPayments.']);
            }

            // Check if NOWPayments returned a payment URL for redirect
            if (!isset($payment['payment_url'])) {
                Log::error('NOWPayments response missing payment_url', ['response' => $payment]);
                return redirect()->back()->withErrors(['error' => 'Invalid payment response from NOWPayments - no payment URL provided.']);
            }

            $paymentUrl = $payment['payment_url']; // Use the actual NOWPayments payment URL

            // Store deposit in database - user gets the full requested amount
            Deposit::create([
                'user_id' => Auth::id(),
                'amount' => $request->amount, // Base amount user requested
                'tax_percent' => $nowPaymentsFeePercent,
                'tax_amount' => $fee_amount, // Fee amount ADDED
                'net_amount' => $request->amount, // User gets the full amount they requested
                'status' => 'pending',
                'reference' => $orderId,
                'note' => $request->note ?? 'NOWPayments cryptocurrency deposit (1.8% fee applied)',
                'payment_method' => 'crypto',
                'payment_id' => $payment['payment_id'],
                'payment_url' => $payment['payment_url'], // Store the actual NOWPayments URL
                'crypto_currency' => $request->crypto_currency . '_TO_USDT', // Indicate conversion
                'crypto_amount' => $payment['pay_amount'] ?? null,
                'payment_address' => $payment['pay_address'] ?? null,
                'unique_address' => $payment['pay_address'] ?? null,
                'address_memo' => 'CRYPTO_' . strtoupper($request->crypto_currency) . '_TO_USDT_' . time(),
            ]);

            return redirect($paymentUrl); // Redirect to actual NOWPayments payment page

        } catch (\Exception $e) {
            Log::error('Crypto payment creation exception: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'amount' => $request->amount,
                'currency' => $request->crypto_currency,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Payment creation failed: ' . $e->getMessage()]);
        }
    }

    public function success(Request $request)
    {
        $paymentId = $request->get('payment_id');
        $deposit = null;

        if ($paymentId) {
            $deposit = Deposit::where('payment_id', $paymentId)
                             ->where('user_id', Auth::id())
                             ->first();
        }

        return view('user.content.deposit-success', compact('deposit'));
    }

    public function cancel()
    {
        return redirect()->route('user.deposit.index')->with('warning', 'Payment was cancelled.');
    }

}
