<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Blockchain Environment
    |--------------------------------------------------------------------------
    |
    | This value determines which blockchain network to use. Can be either
    | 'BSC BEP20' or 'testnet'. In production, this should be 'BSC BEP20'.
    |
    */

    'environment' => env('BLOCKCHAIN_ENVIRONMENT', 'BSC BEP20'),

    /*
    |--------------------------------------------------------------------------
    | BSC Network Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Binance Smart Chain connections including RPC URLs
    | and chain IDs for both mainnet and testnet networks.
    |
    */

    'bsc' => [
        'mainnet' => [
            'rpc_url' => env('BSC_RPC_URL', 'https://bsc-dataseed1.binance.org/'),
            'chain_id' => env('BSC_CHAIN_ID', 56),
        ],
        'testnet' => [
            'rpc_url' => env('BSC_TESTNET_RPC_URL', 'https://data-seed-prebsc-1-s1.binance.org:8545/'),
            'chain_id' => env('BSC_TESTNET_CHAIN_ID', 97),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | XEUSD Token Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the XEUSD BEP20 token including contract address
    | and owner wallet credentials for sending tokens.
    |
    */

    'xeusd' => [
        'contract_address' => env('XEUSD_CONTRACT_ADDRESS', '0x046b0712F251f56ca1F0EecD4e57AB592FDA71be'),
        'owner_wallet' => [
            'address' => env('XEUSD_OWNER_WALLET_ADDRESS'),
            'private_key' => env('XEUSD_OWNER_WALLET_PRIVATE_KEY'),
        ],
        'deposit_wallet' => [
            'address' => env('XEUSD_DEPOSIT_WALLET_ADDRESS', '0xE54189A5b3E4d4af82dEf1a58f600d40f9ec6256'),
            'private_key' => env('XEUSD_DEPOSIT_WALLET_PRIVATE_KEY'),
        ],
        'decimals' => 18, // Standard BEP20 token decimals
    ],

    /*
    |--------------------------------------------------------------------------
    | Gas Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for blockchain transaction gas settings. These values
    | should be adjusted based on network congestion and urgency requirements.
    |
    */

    'gas' => [
        'price_gwei' => env('GAS_PRICE_GWEI', 5),
        'limit' => env('GAS_LIMIT', 100000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Confirmation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for monitoring and confirming blockchain transactions.
    |
    */

    'confirmations' => [
        'required' => 12, // Number of block confirmations to wait for
        'timeout' => 600, // Maximum seconds to wait for confirmation
        'check_interval' => 30, // Seconds between confirmation checks
    ],

];
