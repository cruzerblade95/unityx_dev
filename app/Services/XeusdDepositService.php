<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * XEUSD Deposit Monitor Service
 *
 * Monitors incoming XEUSD token transfers to the deposit wallet
 * and automatically credits user accounts
 */
class XeusdDepositService
{
    protected $rpcUrl;
    protected $chainId;
    protected $contractAddress;
    protected $depositWalletAddress;
    protected $depositWalletPrivateKey;

    public function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Load configuration settings
     */
    protected function loadConfiguration()
    {
        $environment = config('web3.environment', 'mainnet');
        $this->rpcUrl = config("web3.bsc.{$environment}.rpc_url");
        $this->chainId = config("web3.bsc.{$environment}.chain_id");
        $this->contractAddress = config('web3.xeusd.contract_address');

        // Deposit wallet configuration
        $this->depositWalletAddress = config('web3.xeusd.deposit_wallet.address');
        $this->depositWalletPrivateKey = config('web3.xeusd.deposit_wallet.private_key');

        if (!$this->depositWalletAddress || !$this->contractAddress) {
            throw new Exception('XEUSD deposit wallet configuration incomplete. Please check .env file.');
        }
    }

    /**
     * Get deposit wallet XEUSD balance
     *
     * @return float
     */
    public function getDepositWalletBalance()
    {
        try {
            $balanceOfABI = '0x70a08231'; // balanceOf(address)
            $data = $balanceOfABI . str_pad(substr($this->depositWalletAddress, 2), 64, '0', STR_PAD_LEFT);

            $response = Http::withOptions(['verify' => false])
                ->timeout(30)
                ->post($this->rpcUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'eth_call',
                    'params' => [
                        [
                            'to' => $this->contractAddress,
                            'data' => $data
                        ],
                        'latest'
                    ],
                    'id' => 1
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to query balance: ' . $response->body());
            }

            $result = $response->json()['result'] ?? '0x0';
            $balance = $this->fromWei($result);

            Log::info("Deposit wallet balance checked", [
                'balance' => $balance,
                'wallet' => $this->depositWalletAddress
            ]);

            return $balance;

        } catch (Exception $e) {
            Log::error("Failed to get deposit wallet balance", [
                'error' => $e->getMessage(),
                'wallet' => $this->depositWalletAddress
            ]);
            return 0;
        }
    }

    /**
     * Get BNB balance for deposit wallet
     *
     * @return float
     */
    public function getDepositWalletBNBBalance()
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->timeout(30)
                ->post($this->rpcUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'eth_getBalance',
                    'params' => [$this->depositWalletAddress, 'latest'],
                    'id' => 1
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to query BNB balance');
            }

            $result = $response->json()['result'] ?? '0x0';
            $balance = $this->fromWei($result);

            return $balance;

        } catch (Exception $e) {
            Log::error("Failed to get deposit wallet BNB balance", [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get recent incoming transactions to deposit wallet
     *
     * Note: For production, use BSCScan API or similar indexer
     * This is a basic implementation
     *
     * @param int $fromBlock
     * @return array
     */
    public function getIncomingTransactions($fromBlock = null)
    {
        try {
            // If no fromBlock specified, check last 1000 blocks (~50 minutes on BSC)
            if (!$fromBlock) {
                $currentBlock = $this->getCurrentBlockNumber();
                $fromBlock = max(0, $currentBlock - 1000);
            }

            // For better implementation, use BSCScan API:
            // https://api.bscscan.com/api?module=account&action=tokentx&contractaddress={contract}&address={wallet}&startblock={fromBlock}

            Log::info("Checking for incoming XEUSD transactions", [
                'wallet' => $this->depositWalletAddress,
                'from_block' => $fromBlock
            ]);

            // This is a placeholder - implement with BSCScan API or event logs
            return [];

        } catch (Exception $e) {
            Log::error("Failed to get incoming transactions", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get current block number
     *
     * @return int
     */
    protected function getCurrentBlockNumber()
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->timeout(30)
                ->post($this->rpcUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'eth_blockNumber',
                    'params' => [],
                    'id' => 1
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to get block number');
            }

            $result = $response->json()['result'] ?? '0x0';
            return hexdec($result);

        } catch (Exception $e) {
            Log::error("Failed to get current block number", [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Verify a transaction and check if it's a valid deposit
     *
     * @param string $txHash
     * @return array|null
     */
    public function verifyTransaction($txHash)
    {
        try {
            Log::info("Verifying XEUSD deposit transaction", ['tx_hash' => $txHash]);

            $response = Http::withOptions(['verify' => false])
                ->timeout(30)
                ->post($this->rpcUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'eth_getTransactionReceipt',
                    'params' => [$txHash],
                    'id' => 1
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to query transaction');
            }

            $receipt = $response->json()['result'] ?? null;

            if (!$receipt) {
                return null; // Transaction not found or pending
            }

            // Check if transaction was successful
            if ($receipt['status'] !== '0x1') {
                throw new Exception('Transaction failed');
            }

            // Parse transfer event logs to find amount and sender
            $logs = $receipt['logs'] ?? [];

            foreach ($logs as $log) {
                // Check if this is a Transfer event to our deposit wallet
                if (strtolower($log['address']) === strtolower($this->contractAddress)) {
                    $topics = $log['topics'] ?? [];

                    // Transfer event signature: Transfer(address indexed from, address indexed to, uint256 value)
                    if (count($topics) >= 3) {
                        $from = '0x' . substr($topics[1], 26); // Remove padding
                        $to = '0x' . substr($topics[2], 26);   // Remove padding

                        // Check if transfer is TO our deposit wallet
                        if (strtolower($to) === strtolower($this->depositWalletAddress)) {
                            $amountHex = $log['data'];
                            $amount = $this->fromWei($amountHex);

                            return [
                                'from' => $from,
                                'to' => $to,
                                'amount' => $amount,
                                'tx_hash' => $txHash,
                                'block_number' => hexdec($receipt['blockNumber']),
                                'confirmed' => true
                            ];
                        }
                    }
                }
            }

            throw new Exception('No valid XEUSD transfer found to deposit wallet');

        } catch (Exception $e) {
            Log::error("Failed to verify transaction", [
                'tx_hash' => $txHash,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Convert Wei to XEUSD amount
     *
     * @param string|int $wei
     * @return float
     */
    protected function fromWei($wei)
    {
        // Handle hex strings
        if (is_string($wei) && str_starts_with($wei, '0x')) {
            $wei = $this->hexToDec($wei);
        }

        return floatval(bcdiv(strval($wei), '1000000000000000000', 18));
    }

    /**
     * Convert hex to decimal for large numbers
     *
     * @param string $hex
     * @return string
     */
    protected function hexToDec($hex)
    {
        $hex = ltrim($hex, '0x');
        $dec = '0';
        $len = strlen($hex);

        for ($i = 0; $i < $len; $i++) {
            $dec = bcadd(bcmul($dec, '16'), strval(hexdec($hex[$i])));
        }

        return $dec;
    }

    /**
     * Get deposit wallet address
     *
     * @return string
     */
    public function getDepositWalletAddress()
    {
        return $this->depositWalletAddress;
    }

    /**
     * Get network info
     *
     * @return array
     */
    public function getNetworkInfo()
    {
        return [
            'network' => config('web3.environment'),
            'chain_id' => $this->chainId,
            'rpc_url' => $this->rpcUrl,
            'contract_address' => $this->contractAddress,
            'deposit_wallet' => $this->depositWalletAddress,
            'status' => 'live'
        ];
    }
}
