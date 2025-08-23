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
        $deposits = Deposit::where('user_id', Auth::id())->latest()->get();
        $currencies = $this->nowPayments->getAvailableCurrencies();
        $cryptoCurrencies = $currencies ? array_slice($currencies['currencies'], 0, 10) : [];

        return view('user.content.deposit', compact('deposits', 'cryptoCurrencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'payment_id' => 'required|string|min:10',
            'auto_reference' => 'nullable|string',
            'deposit_address' => 'nullable|string',
        ]);

        $systemTaxPercent = 2.5; // Set your system tax percent here
        $tax_amount = $request->amount * ($systemTaxPercent / 100);
        $net_amount = $request->amount - $tax_amount;

        // Generate fallback reference if not provided
        $autoReference = $request->auto_reference ?: 'DEP_' . Auth::id() . '_' . time();
        $depositAddress = $request->deposit_address ?: 'TXFBqBkAjdoBiXRVsRqPW7DGNYqCX6jY8V'; // Default USDT address

        // Create the deposit with auto-generated reference
        $deposit = Deposit::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'tax_percent' => $systemTaxPercent,
            'tax_amount' => $tax_amount,
            'net_amount' => $net_amount,
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

        return redirect()->back()->with('success', 'USDT deposit request submitted! Your deposit will be approved once the transaction is verified. Reference: ' . $autoReference);
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
            $systemTaxPercent = 2.5;
            $tax_amount = $request->amount * ($systemTaxPercent / 100);
            $net_amount = $request->amount - $tax_amount;

            // Create payment with NOWPayments - all payments convert to USDT
            $orderId = 'crypto_deposit_' . Auth::id() . '_' . time();
            $payment = $this->nowPayments->createPayment(
                $request->amount,
                'USD',
                $request->crypto_currency,
                $orderId,
                'Unity X Crypto to USDT Deposit'
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

            // For NOWPayments, we don't get a pay_url, instead we show payment details on success page
            $paymentUrl = route('user.deposit.success') . '?payment_id=' . $payment['payment_id'];

            // Store deposit in database - note that final currency will be USDT
            Deposit::create([
                'user_id' => Auth::id(),
                'amount' => $request->amount,
                'tax_percent' => $systemTaxPercent,
                'tax_amount' => $tax_amount,
                'net_amount' => $net_amount,
                'status' => 'pending',
                'reference' => $orderId,
                'note' => $request->note ?? 'Cryptocurrency deposit (converts to USDT)',
                'payment_method' => 'crypto',
                'payment_id' => $payment['payment_id'],
                'payment_url' => $paymentUrl,
                'crypto_currency' => $request->crypto_currency . '_TO_USDT', // Indicate conversion
                'crypto_amount' => $payment['pay_amount'] ?? null,
                'payment_address' => $payment['pay_address'] ?? null,
                'unique_address' => $payment['pay_address'] ?? null,
                'address_memo' => 'CRYPTO_' . strtoupper($request->crypto_currency) . '_TO_USDT_' . time(),
            ]);

            return redirect($paymentUrl);

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
