<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deposit;
use App\Services\NOWPaymentsService;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    protected $nowPayments;

    public function __construct(NOWPaymentsService $nowPayments)
    {
        $this->nowPayments = $nowPayments;
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
            'amount' => 'required|numeric|min:1',
        ]);
        $systemTaxPercent = 2.5; // Set your system tax percent here
        $tax_amount = $request->amount * ($systemTaxPercent / 100);
        $net_amount = $request->amount - $tax_amount;
        Deposit::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'tax_percent' => $systemTaxPercent,
            'tax_amount' => $tax_amount,
            'net_amount' => $net_amount,
            'status' => 'pending',
            'reference' => $request->reference,
            'note' => $request->note,
            'payment_method' => 'manual',
        ]);
        return redirect()->back()->with('success', 'Deposit request submitted!');
    }

    public function createCryptoPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'crypto_currency' => 'required|string',
        ]);

        try {
            $systemTaxPercent = 2.5;
            $tax_amount = $request->amount * ($systemTaxPercent / 100);
            $net_amount = $request->amount - $tax_amount;

            // Create payment with NOWPayments
            $orderId = 'deposit_' . Auth::id() . '_' . time();
            $payment = $this->nowPayments->createPayment(
                $request->amount,
                'USD',
                $request->crypto_currency,
                $orderId,
                'Unity X Wallet Deposit'
            );

            if (!$payment) {
                return redirect()->back()->withErrors(['error' => 'Failed to create crypto payment. Please try again.']);
            }

            // Store deposit in database
            Deposit::create([
                'user_id' => Auth::id(),
                'amount' => $request->amount,
                'tax_percent' => $systemTaxPercent,
                'tax_amount' => $tax_amount,
                'net_amount' => $net_amount,
                'status' => 'pending',
                'reference' => $orderId,
                'note' => $request->note ?? 'Cryptocurrency deposit',
                'payment_method' => 'crypto',
                'payment_id' => $payment['payment_id'],
                'payment_url' => $payment['pay_url'],
                'crypto_currency' => $request->crypto_currency,
                'crypto_amount' => $payment['pay_amount'] ?? null,
                'payment_address' => $payment['pay_address'] ?? null,
            ]);

            return redirect($payment['pay_url']);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Payment creation failed: ' . $e->getMessage()]);
        }
    }

    public function success()
    {
        return view('user.content.deposit-success');
    }

    public function cancel()
    {
        return redirect()->route('user.deposit.index')->with('warning', 'Payment was cancelled.');
    }
}
