<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deposit;
use App\Services\NOWPaymentsService;
use App\Services\WalletAddressService;
use App\Services\XeusdDepositService;
use App\Services\NotificationService;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    protected $nowPayments;
    protected $walletService;
    protected $xeusdDepositService;

    public function __construct(
        NOWPaymentsService $nowPayments,
        WalletAddressService $walletService,
        XeusdDepositService $xeusdDepositService
    ) {
        $this->nowPayments = $nowPayments;
        $this->walletService = $walletService;
        $this->xeusdDepositService = $xeusdDepositService;
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
        // XEUSD deposit ONLY page - 1% fee, blockchain verified
        $deposits = Deposit::where('user_id', Auth::id())
                          ->where('payment_method', 'xeusd')
                          ->latest()
                          ->get();

        // Get XEUSD deposit wallet info
        $xeusdWalletAddress = $this->xeusdDepositService->getDepositWalletAddress();
        $xeusdNetworkInfo = $this->xeusdDepositService->getNetworkInfo();

        return view('user.content.deposit-direct', compact('deposits', 'xeusdWalletAddress', 'xeusdNetworkInfo'));
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

            // All crypto currencies go through NOWPayments - INCLUDING USDT variants
            // USDT variants can be processed through NOWPayments for consistency
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

            // Log the full payment response for debugging
            Log::info('NOWPayments response received', [
                'user_id' => Auth::id(),
                'response' => $payment,
                'response_keys' => array_keys($payment ?? [])
            ]);

            // Check if the payment response has the required fields
            if (!isset($payment['payment_id'])) {
                Log::error('NOWPayments response missing payment_id', ['response' => $payment]);
                return redirect()->back()->withErrors(['error' => 'Invalid payment response from NOWPayments.']);
            }

            // Check if NOWPayments returned payment details (NOWPayments doesn't provide hosted URLs)
            if (!isset($payment['pay_address']) || !isset($payment['pay_amount']) || !isset($payment['pay_currency'])) {
                Log::error('NOWPayments response missing required payment details', [
                    'response' => $payment,
                    'available_keys' => array_keys($payment ?? [])
                ]);

                return redirect()->back()->withErrors(['error' => 'Invalid payment response from NOWPayments - missing payment details. Available fields: ' . implode(', ', array_keys($payment ?? []))]);
            }

            $paymentAddress = $payment['pay_address'];
            $payAmount = $payment['pay_amount'];
            $payCurrency = strtoupper($payment['pay_currency']);
            $expirationDate = $payment['expiration_estimate_date'] ?? null;

            // Store deposit in database - user gets the full requested amount
            $deposit = Deposit::create([
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
                'payment_url' => null, // NOWPayments doesn't provide hosted URLs
                'crypto_currency' => $payCurrency . '_TO_USDT', // Indicate conversion
                'crypto_amount' => $payAmount,
                'payment_address' => $paymentAddress,
                'unique_address' => $paymentAddress,
                'address_memo' => 'CRYPTO_' . $payCurrency . '_TO_USDT_' . time(),
            ]);

            // Redirect to payment details page instead of external URL
            return redirect()->route('user.deposit.crypto.details', ['deposit' => $deposit->id])
                           ->with('success', 'Crypto payment created! Please send the exact amount to complete your deposit.');

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

    public function cryptoDetails(Deposit $deposit)
    {
        // Ensure user can only view their own deposits
        if ($deposit->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to deposit details.');
        }

        // Ensure this is a crypto deposit
        if ($deposit->payment_method !== 'crypto') {
            return redirect()->route('user.deposit.index')->with('error', 'Invalid deposit type.');
        }

        return view('user.content.crypto-payment-details', compact('deposit'));
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

    /**
     * Submit XEUSD deposit request
     */
    public function xeusdDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:10000',
            'transaction_hash' => 'required|string|min:66|max:66',
        ], [
            'transaction_hash.min' => 'Invalid transaction hash format. Must be 66 characters (0x + 64 hex)',
            'transaction_hash.max' => 'Invalid transaction hash format. Must be 66 characters (0x + 64 hex)',
        ]);

        try {
            $txHash = $request->transaction_hash;

            // Check if transaction already processed
            $existing = Deposit::where('payment_id', $txHash)
                              ->where('payment_method', 'xeusd')
                              ->first();

            if ($existing) {
                return redirect()->back()->with('error', 'This transaction has already been processed!');
            }

            // Verify the transaction on blockchain
            $txData = $this->xeusdDepositService->verifyTransaction($txHash);

            if (!$txData) {
                return redirect()->back()->with('error', 'Transaction not found or still pending. Please wait for blockchain confirmation and try again.');
            }

            // Validate amount matches
            if ($txData['amount'] < $request->amount - 0.01) { // Allow small rounding difference
                return redirect()->back()->with('error',
                    'Transaction amount mismatch! You sent ' . $txData['amount'] . ' XEUSD but claimed ' . $request->amount . ' XEUSD.');
            }

            // Calculate 1% fee for XEUSD deposits
            $feePercent = 1;
            $feeAmount = $txData['amount'] * ($feePercent / 100);
            $netAmount = $txData['amount'] - $feeAmount;

            // Start database transaction for atomic operation
            DB::beginTransaction();

            try {
                // Create deposit record with completed status (auto-approve)
                $deposit = Deposit::create([
                    'user_id' => Auth::id(),
                    'amount' => $txData['amount'],
                    'tax_percent' => $feePercent,
                    'tax_amount' => $feeAmount,
                    'net_amount' => $netAmount,
                    'status' => 'completed', // Auto-approved since transaction hash is verified
                    'reference' => 'XEUSD_' . substr($txHash, 2, 10),
                    'note' => 'XEUSD deposit from ' . $txData['from'] . ' (Auto-approved)',
                    'payment_method' => 'xeusd',
                    'payment_id' => $txHash,
                    'crypto_currency' => 'XEUSD',
                    'payment_address' => $txData['from'],
                    'unique_address' => $this->xeusdDepositService->getDepositWalletAddress(),
                    'address_memo' => 'Block: ' . $txData['block_number'],
                ]);

                // Update user wallet balance (add to deposit balance)
                $user = Auth::user();
                if ($user && $user->wallet) {
                    $originalBalance = $user->wallet->deposit;
                    $user->wallet->deposit += $netAmount;
                    $user->wallet->save();

                    // Verify the credit was applied
                    $wallet = $user->wallet->fresh();
                    $actualCreditedAmount = $wallet->deposit - $originalBalance;

                    if (abs($actualCreditedAmount - $netAmount) > 0.01) {
                        throw new \Exception("Wallet credit verification failed - expected: {$netAmount}, actual: {$actualCreditedAmount}");
                    }
                } else {
                    throw new \Exception('User or wallet not found');
                }

                // Add bonus to upline
                Helpers::insertAiTradeV2($user->id, $netAmount);
                Helpers::depositBonus($user->id, $netAmount);

                // Process deposit promo bonus if campaign is active
                $promoBonusResult = Helpers::processDepositPromoBonus($user->id, $netAmount, $deposit->id);

                // Create success notification for the user
                NotificationService::createDepositSuccessNotification(
                    $user->id,
                    $netAmount,
                    $deposit->id
                );

                // Commit transaction if all operations succeeded
                DB::commit();

                Log::info("XEUSD deposit auto-approved successfully", [
                    'user_id' => $user->id,
                    'amount' => $txData['amount'],
                    'net_amount' => $netAmount,
                    'tx_hash' => $txHash,
                    'deposit_id' => $deposit->id,
                    'wallet_credited' => $netAmount
                ]);

                return redirect()->back()->with('success',
                    'XEUSD deposit verified and approved! Amount: $' . number_format($txData['amount'], 2) .
                    ' XEUSD. Fee (1%): $' . number_format($feeAmount, 2) .
                    '. Net amount credited: $' . number_format($netAmount, 2) .
                    '. Your wallet has been updated automatically.');

            } catch (\Throwable $e) {
                DB::rollBack();

                Log::error('Error in XEUSD deposit auto-approval: ' . $e->getMessage(), [
                    'user_id' => Auth::id(),
                    'tx_hash' => $txHash,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);

                return redirect()->back()->with('error',
                    'Transaction verified but failed to credit wallet: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            Log::error("XEUSD deposit verification failed", [
                'user_id' => Auth::id(),
                'tx_hash' => $request->transaction_hash ?? null,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error',
                'Failed to verify XEUSD transaction: ' . $e->getMessage());
        }
    }

    /**
     * Get XEUSD deposit wallet info
     */
    public function getXeusdWalletInfo()
    {
        try {
            $networkInfo = $this->xeusdDepositService->getNetworkInfo();
            $balance = $this->xeusdDepositService->getDepositWalletBalance();

            return response()->json([
                'success' => true,
                'wallet_address' => $networkInfo['deposit_wallet'],
                'contract_address' => $networkInfo['contract_address'],
                'network' => $networkInfo['network'],
                'chain_id' => $networkInfo['chain_id'],
                'balance' => $balance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
