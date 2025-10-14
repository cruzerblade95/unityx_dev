const { Web3 } = require('web3');

/**
 * Send BEP20 tokens (XEUSD) on BSC network
 *
 * This script is called from PHP to perform actual blockchain transactions
 */

async function sendToken() {
  try {
    // Parse parameters from PHP (handle Windows escaped quotes)
    let paramString = process.argv[2];

    // Remove double quotes escaping for Windows
    paramString = paramString.replace(/""/g, '"');

    const params = JSON.parse(paramString);

    const { contractAddress, recipientAddress, amount, privateKey, rpcUrl, chainId } = params;

    // Initialize Web3
    const web3 = new Web3(rpcUrl);

    // Get account from private key
    const account = web3.eth.accounts.privateKeyToAccount(privateKey);
    web3.eth.accounts.wallet.add(account);

    // BEP20 Token ABI (minimal - only transfer function)
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

    // Send transaction
    const tx = await contract.methods.transfer(recipientAddress, amount).send({
      from: account.address,
      gas: Math.floor(gasEstimate * 1.2), // Add 20% buffer
      gasPrice: gasPrice,
      chainId: chainId
    });

    // Return success response
    console.log(
      JSON.stringify({
        success: true,
        transactionHash: tx.transactionHash,
        blockNumber: tx.blockNumber,
        gasUsed: tx.gasUsed
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
