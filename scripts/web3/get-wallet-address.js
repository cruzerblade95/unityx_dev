const { Web3 } = require('web3');

// Read private key from command line argument
const privateKey = process.argv[2];

if (!privateKey) {
  console.error('Usage: node get-wallet-address.js <private_key>');
  process.exit(1);
}

// Ensure private key has 0x prefix
let formattedPrivateKey = privateKey;
if (!formattedPrivateKey.startsWith('0x')) {
  formattedPrivateKey = '0x' + formattedPrivateKey;
}

try {
  const web3 = new Web3();
  const account = web3.eth.accounts.privateKeyToAccount(formattedPrivateKey);

  console.log(
    JSON.stringify({
      success: true,
      address: account.address
    })
  );
} catch (error) {
  console.log(
    JSON.stringify({
      success: false,
      error: error.message
    })
  );
  process.exit(1);
}
