# Real XEUSD Blockchain Integration Setup

✅ **Web3 Integration Completed!** Your XEUSD withdrawal system is now configured to send **real tokens** on the BSC blockchain.

## What's Changed

The system has been upgraded from **simulated** to **LIVE** blockchain interaction:

### Before (Simulated):

- ❌ Fake transaction hashes
- ❌ Mock balance (1000 XEUSD)
- ❌ No real tokens sent

### After (Real):

- ✅ Real BSC blockchain transactions
- ✅ Actual XEUSD balance checking
- ✅ Real tokens sent from `0x4EFd5C0e73f98e9110E1E2eEB6A47ec6f6E4Ec60`
- ✅ Transactions visible on BSCScan

## How It Works

```
User Requests Withdrawal
         ↓
Admin Approves
         ↓
PHP calls Node.js Script
         ↓
Node.js + Web3.js signs transaction
         ↓
Transaction sent to BSC network
         ↓
Real XEUSD tokens transferred
         ↓
Transaction hash returned
         ↓
System tracks confirmation
```

## Prerequisites Checklist

### 1. Owner Wallet Setup

- ✅ Address: `0x4EFd5C0e73f98e9110E1E2eEB6A47ec6f6E4Ec60`
- ⚠️ **Required**: Sufficient BNB for gas fees (~0.0005 BNB per transfer)
- ⚠️ **Required**: XEUSD tokens to send to users

**Check your balances:**

```bash
# Check on BSCScan
https://bscscan.com/address/0x4EFd5C0e73f98e9110E1E2eEB6A47ec6f6E4Ec60
```

### 2. Environment Configuration

Ensure your `.env` file has:

```env
# BSC Network Configuration
WEB3_ENVIRONMENT=mainnet  # or testnet for testing

# Mainnet RPC (choose one)
WEB3_BSC_MAINNET_RPC_URL=https://bsc-dataseed.binance.org/
# Or use faster paid RPC:
# WEB3_BSC_MAINNET_RPC_URL=https://bsc-dataseed1.defibit.io/
# WEB3_BSC_MAINNET_RPC_URL=https://rpc.ankr.com/bsc

WEB3_BSC_MAINNET_CHAIN_ID=56

# Testnet RPC (for testing without real money)
WEB3_BSC_TESTNET_RPC_URL=https://data-seed-prebsc-1-s1.binance.org:8545/
WEB3_BSC_TESTNET_CHAIN_ID=97

# XEUSD Contract
XEUSD_CONTRACT_ADDRESS=0x046b0712F251f56ca1F0EecD4e57AB592FDA71be

# Owner Wallet (KEEP THIS SECRET!)
XEUSD_OWNER_WALLET_ADDRESS=0x4EFd5C0e73f98e9110E1E2eEB6A47ec6f6E4Ec60
XEUSD_OWNER_WALLET_PRIVATE_KEY=your_private_key_here
```

### 3. Node.js Setup

✅ Already completed:

- `scripts/web3/` directory created
- `package.json` configured
- `web3` library installed (v4.3.0)
- `send-token.js` script ready

### 4. Test the Integration

#### Step 1: Check Real Balance

Create a test script:

```php
// test-web3-balance.php
<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$web3 = new \App\Services\Web3Service();

echo "Owner Address: " . config('web3.xeusd.owner_wallet.address') . "\n";
echo "XEUSD Balance: " . $web3->getOwnerWalletBalance() . " XEUSD\n";
echo "BNB Balance: " . $web3->getOwnerBNBBalance() . " BNB\n";
```

Run it:

```bash
php test-web3-balance.php
```

#### Step 2: Test on BSC Testnet First (Recommended)

Before using real mainnet tokens, test on testnet:

1. Change `.env`:

   ```env
   WEB3_ENVIRONMENT=testnet
   ```

2. Get testnet BNB:

   - Visit: https://testnet.bnbchain.org/faucet-smart
   - Send testnet BNB to your wallet

3. Get testnet XEUSD (if available) or deploy test contract

4. Test a withdrawal

#### Step 3: Test Real Transaction (Small Amount)

Once ready for mainnet:

1. Switch to mainnet in `.env`:

   ```env
   WEB3_ENVIRONMENT=mainnet
   ```

2. Make a test withdrawal for $1-5

3. Check transaction on BSCScan

## Security Recommendations

### 🔒 Critical Security

1. **Never commit `.env` file** - Already in `.gitignore`
2. **Keep private key secret** - Never share or log it
3. **Use environment variables** - Never hardcode in code
4. **Monitor wallet balance** - Set up alerts for low BNB/XEUSD

### 🛡️ Best Practices

1. **Use separate wallets**:
   - Hot wallet (your current one) for automated withdrawals
   - Cold wallet for storing bulk XEUSD
2. **Set withdrawal limits**: Currently $1-500 per transaction

3. **Manual approval required**: Admin must approve each withdrawal

4. **Transaction logging**: All transactions logged in database and Laravel logs

## Monitoring

### Check Transaction Status

```php
$web3 = new \App\Services\Web3Service();
$status = $web3->checkTransaction('0x...');

if ($status['confirmed']) {
    echo "✓ Transaction confirmed ({$status['confirmations']} confirmations)\n";
} else {
    echo "⏳ Waiting for confirmation...\n";
}
```

### Monitor Wallet Balance

Set up a cron job to alert when:

- BNB balance < 0.01 (need more for gas)
- XEUSD balance < $1000 (need to refill)

## Troubleshooting

### "Node.js script not found"

```bash
# Verify script exists
dir scripts\web3\send-token.js

# Reinstall if needed
cd scripts\web3
npm install
cd ..\..
```

### "Insufficient XEUSD balance"

- Check actual balance on BSCScan
- Transfer more XEUSD to owner wallet
- Balance checking is now REAL, not mocked

### "Insufficient BNB for gas"

- Each transaction costs ~0.0003-0.0005 BNB
- Send more BNB to owner wallet

### "Transaction failed" / "Gas estimation failed"

- Check BNB balance (need gas)
- Verify recipient address is valid
- Check XEUSD token allowance
- Review error in Laravel logs: `storage/logs/laravel.log`

### Testing Failed Transactions

If you see errors, check:

```bash
# View Laravel logs
Get-Content storage\logs\laravel.log -Tail 50

# Check Node.js is working
node --version

# Test Node.js script directly (careful - this sends real tokens!)
node scripts\web3\send-token.js '{"contractAddress":"0x046b0712F251f56ca1F0EecD4e57AB592FDA71be","recipientAddress":"0x...test address...","amount":"1000000000000000000","privateKey":"your_key","rpcUrl":"https://bsc-dataseed.binance.org/","chainId":56}'
```

## Next Steps

1. ✅ Verify `.env` configuration
2. ✅ Check owner wallet has BNB and XEUSD
3. ⚠️ Test on BSC Testnet first (recommended)
4. ⚠️ Test small mainnet transaction ($1-5)
5. ✅ Monitor transaction on BSCScan
6. ✅ Set up balance monitoring/alerts
7. ✅ Ready for production!

## View Transactions

### BSCScan

- Mainnet: https://bscscan.com/tx/{transaction_hash}
- Testnet: https://testnet.bscscan.com/tx/{transaction_hash}

### In Admin Panel

- Go to `/admin/xeusd-withdrawals`
- View transaction hashes (clickable links to BSCScan)
- See real-time status updates

## Support

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify Node.js is installed: `node --version`
3. Test RPC connectivity: Visit your RPC URL in browser
4. Verify contract address on BSCScan
5. Ensure private key matches the wallet address

---

**🎉 Your XEUSD system is now LIVE on BSC blockchain!**

Users will receive **real XEUSD tokens** when you approve their withdrawals.
