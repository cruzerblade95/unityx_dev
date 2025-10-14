@extends('user.layouts.layoutMaster')

@section('title', 'XEUSD Withdrawal')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/notyf/notyf.scss'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js"></script>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/notyf/notyf.js'])
@endsection

@section('content')
    @include('user.content.withdrawal.partials.styles')

    <style>
        .metamask-connect-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 15px;
            padding: 25px;
            color: white;
            margin-bottom: 25px;
        }

        .wallet-connected {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .xeusd-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 25px;
            color: white;
            margin-bottom: 25px;
        }

        .balance-card {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .balance-card:hover {
            border-color: #6f42c1;
            transform: translateY(-2px);
        }

        .token-info {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .withdrawal-form {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
    </style>

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="withdrawal-page-header">
            <a href="{{ route('user.withdrawal.index') }}" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h4 class="py-3 mb-0">XEUSD Token Withdrawal</h4>
                <p class="text-muted mb-0">Withdraw your balance as XEUSD tokens to BSC network</p>
            </div>
        </div>

        <!-- XEUSD Banner -->
        <div class="xeusd-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-2">
                        <i class="fas fa-coins me-2"></i>
                        XEUSD Token Withdrawal
                    </h3>
                    <p class="mb-2">Withdraw your USD balance as XEUSD tokens on BSC BEP20 network</p>
                    <p class="mb-0"><strong>Exchange Rate:</strong> 1 USD = 1 XEUSD | <strong>Network:</strong> BSC
                        (BEP20) | <strong>Gas Fees:</strong> Covered by platform</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="token-info">
                        <strong>Contract Address:</strong><br>
                        <small class="text-dark">0x046b0712F251f56ca1F0EecD4e57AB592FDA71be</small>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- MetaMask Connection Section -->
        <div class="metamask-connect-section @if (auth()->user()->hasConnectedWallet()) wallet-connected @endif">
            <div class="row align-items-center">
                <div class="col-md-8">
                    @if (auth()->user()->hasConnectedWallet())
                        <h5 class="mb-2">
                            <i class="fab fa-ethereum me-2"></i>
                            Wallet Connected Successfully
                        </h5>
                        <p class="mb-0">Connected Address:
                            <strong>{{ auth()->user()->getFormattedWalletAddressAttribute() }}</strong>
                        </p>
                        <small class="opacity-75">Connected on
                            {{ auth()->user()->wallet_connected_at ? \Carbon\Carbon::parse(auth()->user()->wallet_connected_at)->format('M d, Y H:i') : 'Unknown' }}</small>
                    @else
                        <h5 class="mb-2">
                            <i class="fab fa-ethereum me-2"></i>
                            Connect Your MetaMask Wallet
                        </h5>
                        <p class="mb-0">Connect your MetaMask wallet to receive XEUSD tokens on BSC network</p>
                    @endif
                </div>
                <div class="col-md-4 text-end">
                    @if (auth()->user()->hasConnectedWallet())
                        <button class="btn btn-light" onclick="connectWallet()">
                            <i class="fas fa-sync-alt me-2"></i>
                            Reconnect Wallet
                        </button>
                    @else
                        <button class="btn btn-warning btn-lg" onclick="connectWallet()">
                            <i class="fab fa-ethereum me-2"></i>
                            Connect MetaMask
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Wallet Balances -->
        <div class="row">
            <div class="col-md-4">
                <div class="balance-card">
                    <h6 class="text-muted mb-1">Cash Balance</h6>
                    <h3 class="mb-0 text-primary">${{ number_format($cashBalance, 2) }}</h3>
                    <small class="text-muted">Available for withdrawal</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="balance-card">
                    <h6 class="text-muted mb-1">Profit Balance</h6>
                    <h3 class="mb-0 text-success">${{ number_format($profitBalance, 2) }}</h3>
                    <small class="text-muted">Available for withdrawal</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="balance-card">
                    <h6 class="text-muted mb-1">Bonus Balance</h6>
                    <h3 class="mb-0 text-warning">${{ number_format($bonusBalance, 2) }}</h3>
                    <small class="text-muted">Available for withdrawal</small>
                </div>
            </div>
        </div>

        <!-- Withdrawal Form -->
        <div class="withdrawal-form">
            <h5 class="mb-4">
                <i class="fas fa-paper-plane me-2"></i>
                Create XEUSD Withdrawal Request
            </h5>

            <form method="POST" action="{{ route('user.withdrawal.xeusd.create') }}" id="xeusdWithdrawalForm">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Source Wallet</label>
                            <select class="form-select" name="source_wallet" id="source_wallet" required
                                onchange="updateMaxAmount()">
                                <option value="">Select wallet to withdraw from</option>
                                @if ($cashBalance > 0)
                                    <option value="cash">Cash Wallet (${{ number_format($cashBalance, 2) }} available)
                                    </option>
                                @endif
                                @if ($profitBalance > 0)
                                    <option value="profit">Profit Wallet (${{ number_format($profitBalance, 2) }}
                                        available)</option>
                                @endif
                                @if ($bonusBalance > 0)
                                    <option value="bonus">Bonus Wallet (${{ number_format($bonusBalance, 2) }} available)
                                    </option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Withdrawal Amount (USD)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="amount" id="amount" required
                                    min="1" max="500" step="0.01" placeholder="Enter amount">
                            </div>
                            <div class="form-text">Minimum: $1 | Maximum: $500</div>
                        </div>
                    </div>
                </div>

                <div class="token-info">
                    <h6 class="mb-2">
                        <i class="fas fa-info-circle me-2"></i>
                        Withdrawal Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>You will receive:</strong> <span id="xeusd-amount">0</span> XEUSD
                                tokens</p>
                            <p class="mb-1"><strong>Exchange Rate:</strong> 1 USD = 1 XEUSD</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Network:</strong> BSC (BEP20)</p>
                            <p class="mb-1"><strong>Gas Fees:</strong> Covered by platform</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Withdrawals are processed manually by our admin team
                        </small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg"
                        @if (!auth()->user()->hasConnectedWallet() || $cashBalance + $profitBalance + $bonusBalance == 0) disabled @endif>
                        <i class="fas fa-paper-plane me-2"></i>
                        Submit Withdrawal Request
                    </button>
                </div>
            </form>
        </div>

        <!-- Withdrawal History -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>
                    Your XEUSD Withdrawal History
                </h5>
            </div>
            <div class="card-body">
                @if ($withdrawals->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Reference ID</th>
                                    <th>Amount</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($withdrawals as $withdrawal)
                                    <tr>
                                        <td>
                                            <strong>{{ $withdrawal->unique_id }}</strong>
                                        </td>
                                        <td>
                                            ${{ number_format($withdrawal->amount_usd, 2) }}<br>
                                            <small class="text-muted">{{ number_format($withdrawal->amount_xeusd, 8) }}
                                                XEUSD</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ ucfirst($withdrawal->source_wallet) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $withdrawal->getStatusBadgeClass() }}">
                                                {{ ucfirst($withdrawal->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $withdrawal->created_at ? \Carbon\Carbon::parse($withdrawal->created_at)->format('M d, Y') : 'N/A' }}<br>
                                            <small
                                                class="text-muted">{{ $withdrawal->created_at ? \Carbon\Carbon::parse($withdrawal->created_at)->format('H:i') : '' }}</small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="viewWithdrawalDetails('{{ $withdrawal->unique_id }}')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if ($withdrawal->transaction_hash)
                                                <a href="https://bscscan.com/tx/{{ $withdrawal->transaction_hash }}"
                                                    target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $withdrawals->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No withdrawal history</h5>
                        <p class="text-muted">Your XEUSD withdrawal requests will appear here</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        let web3;

        // Initialize Web3 and check MetaMask
        async function initWeb3() {
            if (typeof window.ethereum !== 'undefined') {
                web3 = new Web3(window.ethereum);
                return true;
            } else {
                Swal.fire({
                    title: 'MetaMask Required',
                    text: 'Please install MetaMask to use this feature',
                    icon: 'warning',
                    confirmButtonText: 'Install MetaMask',
                    showCancelButton: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open('https://metamask.io/', '_blank');
                    }
                });
                return false;
            }
        }

        // Connect wallet function
        async function connectWallet() {
            if (!(await initWeb3())) return;

            try {
                // Request account access
                const accounts = await window.ethereum.request({
                    method: 'eth_requestAccounts'
                });

                if (accounts.length === 0) {
                    throw new Error('No accounts found');
                }

                const walletAddress = accounts[0];

                // Switch to BSC network if not already on it
                await switchToBSC();

                // Send wallet address to backend
                const response = await fetch('{{ route('user.withdrawal.xeusd.connect_wallet') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        wallet_address: walletAddress
                    })
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: result.message,
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(result.message);
                }

            } catch (error) {
                console.error('Error connecting wallet:', error);
                Swal.fire({
                    title: 'Connection Failed',
                    text: error.message || 'Failed to connect wallet',
                    icon: 'error'
                });
            }
        }

        // Switch to BSC network
        async function switchToBSC() {
            const bscChainId = '0x38'; // BSC Mainnet

            try {
                await window.ethereum.request({
                    method: 'wallet_switchEthereumChain',
                    params: [{
                        chainId: bscChainId
                    }]
                });
            } catch (switchError) {
                // This error code indicates that the chain has not been added to MetaMask
                if (switchError.code === 4902) {
                    try {
                        await window.ethereum.request({
                            method: 'wallet_addEthereumChain',
                            params: [{
                                chainId: bscChainId,
                                chainName: 'BSC Mainnet',
                                nativeCurrency: {
                                    name: 'BNB',
                                    symbol: 'BNB',
                                    decimals: 18
                                },
                                rpcUrls: ['https://bsc-dataseed1.binance.org/'],
                                blockExplorerUrls: ['https://bscscan.com/']
                            }]
                        });
                    } catch (addError) {
                        throw new Error('Failed to add BSC network');
                    }
                } else {
                    throw new Error('Failed to switch to BSC network');
                }
            }
        }

        // Update max amount based on selected wallet
        function updateMaxAmount() {
            const sourceWallet = document.getElementById('source_wallet').value;
            const amountInput = document.getElementById('amount');
            const xeusdAmount = document.getElementById('xeusd-amount');

            const cashBalance = {{ $cashBalance }};
            const profitBalance = {{ $profitBalance }};
            const bonusBalance = {{ $bonusBalance }};

            let maxAmount = 0;

            if (sourceWallet === 'cash') {
                maxAmount = Math.min(cashBalance, 500);
            } else if (sourceWallet === 'profit') {
                maxAmount = Math.min(profitBalance, 500);
            } else if (sourceWallet === 'bonus') {
                maxAmount = Math.min(bonusBalance, 500);
            }

            amountInput.max = maxAmount.toFixed(2);

            // Reset amount if it exceeds new max
            if (parseFloat(amountInput.value) > maxAmount) {
                amountInput.value = '';
            }

            updateXeusdAmount();
        }

        // Update XEUSD amount display
        function updateXeusdAmount() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            document.getElementById('xeusd-amount').textContent = amount.toFixed(8);
        }

        // View withdrawal details
        async function viewWithdrawalDetails(uniqueId) {
            try {
                const response = await fetch(`{{ route('user.withdrawal.xeusd.status', ':id') }}`.replace(':id',
                    uniqueId));
                const result = await response.json();

                if (result.success) {
                    const withdrawal = result.withdrawal;

                    let html = `
                        <div class="text-start">
                            <p><strong>Reference ID:</strong> ${withdrawal.unique_id}</p>
                            <p><strong>Amount:</strong> $${withdrawal.amount_usd} (${withdrawal.amount_xeusd} XEUSD)</p>
                            <p><strong>Status:</strong> <span class="badge bg-primary">${withdrawal.status}</span></p>
                            <p><strong>Created:</strong> ${withdrawal.created_at}</p>
                    `;

                    if (withdrawal.processed_at) {
                        html += `<p><strong>Processed:</strong> ${withdrawal.processed_at}</p>`;
                    }

                    if (withdrawal.transaction_hash) {
                        html +=
                            `<p><strong>Transaction:</strong> <a href="https://bscscan.com/tx/${withdrawal.transaction_hash}" target="_blank">${withdrawal.transaction_hash.substring(0, 20)}...</a></p>`;
                    }

                    if (withdrawal.admin_notes) {
                        html += `<p><strong>Notes:</strong> ${withdrawal.admin_notes}</p>`;
                    }

                    html += `</div>`;

                    Swal.fire({
                        title: 'Withdrawal Details',
                        html: html,
                        width: 600,
                        confirmButtonText: 'Close'
                    });
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to load withdrawal details',
                    icon: 'error'
                });
            }
        }

        // Add event listeners
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('amount').addEventListener('input', updateXeusdAmount);

            // Form validation
            document.getElementById('xeusdWithdrawalForm').addEventListener('submit', function(e) {
                @if (!auth()->user()->hasConnectedWallet())
                    e.preventDefault();
                    Swal.fire({
                        title: 'Wallet Required',
                        text: 'Please connect your MetaMask wallet first',
                        icon: 'warning'
                    });
                    return;
                @endif

                const sourceWallet = document.getElementById('source_wallet').value;
                const amount = parseFloat(document.getElementById('amount').value);

                if (!sourceWallet) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Source Wallet Required',
                        text: 'Please select a wallet to withdraw from',
                        icon: 'warning'
                    });
                    return;
                }

                if (!amount || amount < 1 || amount > 500) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Invalid Amount',
                        text: 'Please enter an amount between $1 and $500',
                        icon: 'warning'
                    });
                    return;
                }
            });
        });
    </script>
@endsection
