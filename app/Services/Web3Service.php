<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class Web3Service
{
    protected $rpcUrl;
    protected $chainId;
    protected $contractAddress;
    protected $ownerAddress;
    protected $ownerPrivateKey;

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
        $this->ownerPrivateKey = config('web3.xeusd.owner_wallet.private_key');

        // Address can be provided OR derived from private key
        $this->ownerAddress = config('web3.xeusd.owner_wallet.address');

        // If no address provided, we can derive it from private key (in production)
        if (!$this->ownerAddress && $this->ownerPrivateKey) {
            $this->ownerAddress = $this->deriveAddressFromPrivateKey($this->ownerPrivateKey);
        }

        // Validate configuration - only private key is absolutely required
        if (!$this->ownerPrivateKey || !$this->contractAddress) {
            throw new Exception('Web3 configuration incomplete. Please set XEUSD_OWNER_WALLET_PRIVATE_KEY and XEUSD_CONTRACT_ADDRESS in .env file. Owner address is optional (can be derived from private key).');
        }
    }

    /**
     * Derive wallet address from private key
     * NOTE: This is a placeholder - implement with actual cryptographic library
     */
    protected function deriveAddressFromPrivateKey($privateKey)
    {
        // In production, you would use a proper library like:
        // - kornrunner/ethereum-offline-signer
        // - web3p/ethereum-tx
        //
        // Example implementation would be:
        // $publicKey = secp256k1_pubkey_create($privateKey);
        // $address = keccak256(substr($publicKey, 1));
        // return '0x' . substr($address, -40);

        // For now, return null to indicate address should be provided
        return null;
    }

    /**
     * Send XEUSD tokens to a recipient address using Node.js + Web3.js
     *
     * @param string $recipientAddress
     * @param float $amountUSD
     * @return array
     */
    public function sendXEUSD($recipientAddress, $amountUSD)
    {
        try {
            Log::info("Initiating XEUSD transfer", [
                'recipient' => $recipientAddress,
                'amount_usd' => $amountUSD,
                'owner_wallet' => $this->ownerAddress
            ]);

            // Validate recipient address
            if (!$this->isValidAddress($recipientAddress)) {
                throw new Exception('Invalid recipient address format');
            }

            // Validate amount
            if ($amountUSD < 1 || $amountUSD > 500) {
                throw new Exception('Amount must be between $1 and $500');
            }

            // Check owner wallet balance
            $ownerBalance = $this->getOwnerWalletBalance();
            if ($ownerBalance < $amountUSD) {
                throw new Exception("Insufficient XEUSD balance. Available: {$ownerBalance}, Required: {$amountUSD}");
            }

            // Convert amount to Wei (18 decimals for BEP20)
            $amountWei = $this->toWei($amountUSD);

            // Execute Node.js script to send tokens
            $result = $this->executeNodeScript('send-token', [
                'contractAddress' => $this->contractAddress,
                'recipientAddress' => $recipientAddress,
                'amount' => $amountWei,
                'privateKey' => $this->ownerPrivateKey,
                'rpcUrl' => $this->rpcUrl,
                'chainId' => $this->chainId
            ]);

            if (!$result['success']) {
                throw new Exception($result['error'] ?? 'Transaction failed');
            }

            Log::info("XEUSD transfer completed", [
                'tx_hash' => $result['transactionHash'],
                'recipient' => $recipientAddress,
                'amount' => $amountUSD,
                'gas_used' => $result['gasUsed'] ?? 'N/A'
            ]);

            return [
                'success' => true,
                'transaction_hash' => $result['transactionHash'],
                'amount_sent' => $amountUSD,
                'recipient' => $recipientAddress,
                'gas_used' => $result['gasUsed'] ?? null,
                'block_number' => $result['blockNumber'] ?? null
            ];

        } catch (Exception $e) {
            Log::error("XEUSD transfer failed", [
                'error' => $e->getMessage(),
                'recipient' => $recipientAddress ?? null,
                'amount' => $amountUSD ?? null
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'transaction_hash' => null,
            ];
        }
    }

    /**
     * Check transaction status and confirmation
     *
     * @param string $txHash
     * @return array
     */
    public function checkTransaction($txHash)
    {
        try {
            Log::info("Checking transaction status", ['tx_hash' => $txHash]);

            $response = Http::withOptions(['verify' => false])
                ->timeout(30)
                ->post($this->rpcUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'eth_getTransactionReceipt',
                    'params' => [$txHash],
                    'id' => 1
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to query blockchain');
            }

            $data = $response->json();
            $receipt = $data['result'] ?? null;

            if (!$receipt) {
                return [
                    'confirmed' => false,
                    'status' => 'pending',
                    'confirmations' => 0
                ];
            }

            // Get current block number
            $currentBlockResponse = Http::withOptions(['verify' => false])
                ->timeout(30)
                ->post($this->rpcUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'eth_blockNumber',
                    'params' => [],
                    'id' => 1
                ]);

            $currentBlock = hexdec($currentBlockResponse->json()['result'] ?? '0');
            $txBlock = hexdec($receipt['blockNumber']);
            $confirmations = $currentBlock - $txBlock;

            // Status: 0x1 = success, 0x0 = failed
            $status = ($receipt['status'] === '0x1') ? 'success' : 'failed';

            return [
                'confirmed' => $confirmations >= 15,
                'confirmations' => $confirmations,
                'status' => $status,
                'block_number' => $txBlock,
                'gas_used' => hexdec($receipt['gasUsed'] ?? '0')
            ];

        } catch (Exception $e) {
            Log::error("Failed to check transaction status", [
                'tx_hash' => $txHash,
                'error' => $e->getMessage()
            ]);

            return [
                'confirmed' => false,
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get owner wallet XEUSD balance from blockchain
     *
     * @return float
     */
    public function getOwnerWalletBalance()
    {
        try {
            // Call balanceOf(address) on the XEUSD contract
            $balanceOfABI = '0x70a08231'; // balanceOf(address) function selector
            $data = $balanceOfABI . str_pad(substr($this->ownerAddress, 2), 64, '0', STR_PAD_LEFT);

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
            $balance = $this->fromWei($result); // Pass hex string directly

            Log::info("Owner wallet balance checked", [
                'balance' => $balance,
                'wallet' => $this->ownerAddress
            ]);

            return $balance;

        } catch (Exception $e) {
            Log::error("Failed to get owner wallet balance", [
                'error' => $e->getMessage(),
                'wallet' => $this->ownerAddress
            ]);
            return 0;
        }
    }

    /**
     * Validate Ethereum/BSC address format
     *
     * @param string $address
     * @return bool
     */
    protected function isValidAddress($address)
    {
        return preg_match('/^0x[a-fA-F0-9]{40}$/', $address);
    }

    /**
     * Generate a mock transaction hash for simulation
     *
     * @return string
     */
    protected function generateMockTransactionHash()
    {
        return '0x' . bin2hex(random_bytes(32));
    }

    /**
     * Execute Node.js script for blockchain operations
     *
     * @param string $scriptName
     * @param array $params
     * @return array
     */
    protected function executeNodeScript($scriptName, $params)
    {
        $scriptPath = base_path("scripts/web3/{$scriptName}.js");

        if (!file_exists($scriptPath)) {
            throw new Exception("Node.js script not found: {$scriptPath}");
        }

        // Escape params for Windows command line
        $paramsJson = json_encode($params);
        $paramsJson = str_replace('"', '""', $paramsJson);

        $command = "node \"{$scriptPath}\" \"{$paramsJson}\"";

        $output = [];
        $returnCode = 0;

        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("Node.js script failed: " . implode("\n", $output));
        }

        $result = json_decode(implode("\n", $output), true);

        if (!$result) {
            throw new Exception("Failed to parse Node.js script output: " . implode("\n", $output));
        }

        return $result;
    }

    /**
     * Convert USD amount to Wei (18 decimals)
     *
     * @param float $amount
     * @return string
     */
    protected function toWei($amount)
    {
        return bcmul(strval($amount), '1000000000000000000', 0);
    }

    /**
     * Convert Wei to USD amount
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
        // Remove 0x prefix
        $hex = ltrim($hex, '0x');

        // Convert hex to decimal using bcmath for large numbers
        $dec = '0';
        $len = strlen($hex);

        for ($i = 0; $i < $len; $i++) {
            $dec = bcadd(bcmul($dec, '16'), strval(hexdec($hex[$i])));
        }

        return $dec;
    }

    /**
     * Get blockchain network info
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
            'owner_wallet' => $this->ownerAddress,
            'status' => 'live' // Real blockchain integration active
        ];
    }

    /**
     * Get BNB balance (for gas fees monitoring)
     *
     * @return float
     */
    public function getOwnerBNBBalance()
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->timeout(30)
                ->post($this->rpcUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'eth_getBalance',
                    'params' => [$this->ownerAddress, 'latest'],
                    'id' => 1
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to query BNB balance: ' . $response->body());
            }

            $result = $response->json()['result'] ?? '0x0';
            $balance = $this->fromWei($result); // Pass hex string directly

            return $balance;

        } catch (Exception $e) {
            Log::error("Failed to get BNB balance", [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}
