const { Web3 } = require('web3');
const fs = require('fs');

/**
 * Send BEP20 tokens (XEUSD) on BSC network
 *
 * This script is called from PHP to perform actual blockchain transactions
 */

async function sendToken() {
  try {
    // Read parameters from temp file (avoid Windows command line escaping issues)
    const tempFilePath = process.argv[2];

    if (!tempFilePath || !fs.existsSync(tempFilePath)) {
      throw new Error('Parameter file not found');
    }

    const fileContent = fs.readFileSync(tempFilePath, 'utf8');
    const params = JSON.parse(fileContent);

    const { contractAddress, recipientAddress, amount, privateKey, rpcUrl, chainId } = params;

    // Validate required parameters
    if (!privateKey) {
      throw new Error('Private key is missing');
    }

    if (!contractAddress) {
      throw new Error('Contract address is missing');
    }

    if (!recipientAddress) {
      throw new Error('Recipient address is missing');
    }

    if (!amount) {
      throw new Error('Amount is missing');
    }

    if (!rpcUrl) {
      throw new Error('RPC URL is missing');
    }

    // Ensure private key has 0x prefix
    let formattedPrivateKey = privateKey;
    if (!formattedPrivateKey.startsWith('0x')) {
      formattedPrivateKey = '0x' + formattedPrivateKey;
    }

    // Initialize Web3
    const web3 = new Web3(rpcUrl);

    // Get account from private key
    const account = web3.eth.accounts.privateKeyToAccount(formattedPrivateKey);
    web3.eth.accounts.wallet.add(account); // BEP20 Token ABI (minimal - only transfer function)
    const tokenABI = [
      {
        constant: false,
        inputs: [
          {
            name: '_to',
            type: 'address'
          },
          {
            name: '_value',
            type: 'uint256'
          }
        ],
        name: 'transfer',
        outputs: [
          {
            name: '',
            type: 'bool'
          }
        ],
        type: 'function'
      }
    ];

    // Create contract instance
    const contract = new web3.eth.Contract(tokenABI, contractAddress);

    // Get current gas price
    const gasPrice = await web3.eth.getGasPrice();

    // Estimate gas
    const gasEstimate = await contract.methods
      .transfer(recipientAddress, amount)
      .estimateGas({ from: account.address });

    // Convert gas to Number to avoid BigInt mixing issues
    const gasLimit = Math.floor(Number(gasEstimate) * 1.2); // Add 20% buffer

    // Send transaction
    const tx = await contract.methods.transfer(recipientAddress, amount).send({
      from: account.address,
      gas: gasLimit,
      gasPrice: gasPrice,
      chainId: chainId
    }); // Return success response
    console.log(
      JSON.stringify({
        success: true,
        transactionHash: tx.transactionHash,
        blockNumber: Number(tx.blockNumber),
        gasUsed: Number(tx.gasUsed)
      })
    );
  } catch (error) {
    // Return error response
    console.log(
      JSON.stringify({
        success: false,
        error: error.message || 'Unknown error occurred'
      })
    );
    process.exit(1);
  }
}

// Run the function
sendToken();
