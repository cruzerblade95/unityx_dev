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
        /* Dark mode theme for XEUSD withdrawal */
        .metamask-connect-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 15px;
            padding: 25px;
            color: white;
            margin-bottom: 25px;
        }

        .wallet-connected {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a7b 100%);
            border: 2px solid #4a90e2;
        }

        .wallet-connected h5,
        .wallet-connected p,
        .wallet-connected small {
            color: #ffffff !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .wallet-connected strong {
            color: #b8e6ff;
        }

        .xeusd-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 25px;
            color: white;
            margin-bottom: 25px;
        }

        .balance-card {
            background: #2b2c40;
            border: 2px solid #4a4b68;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            color: #ffffff;
        }

        .balance-card:hover {
            border-color: #6f42c1;
            transform: translateY(-2px);
            background: #34354a;
        }

        .balance-card h6 {
            color: #a5a5ba;
        }

        .balance-card small {
            color: #a5a5ba;
        }

        .token-info {
            background: #2b2c40;
            border: 1px solid #4a4b68;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            color: #ffffff;
            overflow-wrap: break-word;
            word-wrap: break-word;
            word-break: break-all;
        }

        .token-info h6 {
            color: #ffffff;
        }

        .token-info p {
            color: #d2d2dc;
        }

        .token-info small {
            font-size: 0.75rem;
            word-break: break-all;
            overflow-wrap: break-word;
            display: block;
            line-height: 1.4;
        }

        /* Responsive adjustments for mobile */
        @media (max-width: 768px) {
            .xeusd-banner .col-md-4 {
                margin-top: 15px;
                text-align: left !important;
            }

            .token-info {
                padding: 10px;
                margin: 10px 0;
            }

            .token-info small {
                font-size: 0.7rem;
            }

            /* Withdrawal Form Mobile Responsive */
            .withdrawal-form {
                padding: 15px;
                border-radius: 10px;
            }

            .withdrawal-form h5 {
                font-size: 1.1rem;
                margin-bottom: 1rem !important;
            }

            .withdrawal-form .form-label {
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
            }

            .withdrawal-form .form-text {
                font-size: 0.75rem;
            }

            .withdrawal-form .btn-lg {
                font-size: 0.9rem;
                padding: 0.6rem 1rem;
            }

            /* Make button and text stack on mobile */
            .withdrawal-form .d-flex.justify-content-between {
                flex-direction: column;
                gap: 15px;
            }

            .withdrawal-form .d-flex.justify-content-between>div {
                width: 100%;
            }

            .withdrawal-form .d-flex.justify-content-between button {
                width: 100%;
            }

            /* Token info mobile adjustments */
            .withdrawal-form .token-info {
                margin: 15px 0;
            }

            .withdrawal-form .token-info h6 {
                font-size: 0.95rem;
            }

            .withdrawal-form .token-info p {
                font-size: 0.85rem;
                margin-bottom: 0.5rem !important;
            }

            /* Balance cards mobile spacing */
            .balance-card {
                margin-bottom: 15px;
            }

            .balance-card h3 {
                font-size: 1.5rem;
            }

            /* MetaMask section mobile */
            .metamask-connect-section {
                padding: 15px;
            }

            .metamask-connect-section h5 {
                font-size: 1rem;
            }

            .metamask-connect-section .col-md-4 {
                margin-top: 15px;
                text-align: left !important;
            }

            .metamask-connect-section .btn {
                width: 100%;
            }

            /* Alert mobile adjustments */
            .alert h5,
            .alert h6 {
                font-size: 1rem;
            }

            .alert ol,
            .alert ul {
                font-size: 0.85rem;
                padding-left: 20px;
            }

            .alert .btn-sm {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
        }

        /* Extra small devices (phones in portrait mode) */
        @media (max-width: 576px) {
            .withdrawal-form {
                padding: 12px;
            }

            .withdrawal-form h5 {
                font-size: 1rem;
            }

            .withdrawal-form .form-control,
            .withdrawal-form .form-select {
                font-size: 0.9rem;
            }

            .withdrawal-form .input-group-text {
                font-size: 0.9rem;
                padding: 0.5rem 0.75rem;
            }

            .token-info h6 {
                font-size: 0.9rem;
            }

            .token-info p {
                font-size: 0.8rem;
            }

            .balance-card h6 {
                font-size: 0.85rem;
            }

            .balance-card h3 {
                font-size: 1.3rem;
            }

            .balance-card small {
                font-size: 0.75rem;
            }
        }


        .withdrawal-form {
            background: #2b2c40;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.3);
            padding: 30px;
            color: #ffffff;
        }

        .withdrawal-form h5 {
            color: #ffffff;
        }

        .withdrawal-form .form-label {
            color: #d2d2dc;
        }

        .withdrawal-form .form-text {
            color: #a5a5ba;
        }

        .withdrawal-form small {
            color: #a5a5ba;
        }

        /* Dark mode form controls */
        .withdrawal-form .form-select,
        .withdrawal-form .form-control {
            background-color: #34354a;
            border-color: #4a4b68;
            color: #ffffff;
        }

        .withdrawal-form .form-select:focus,
        .withdrawal-form .form-control:focus {
            background-color: #34354a;
            border-color: #6f42c1;
            color: #ffffff;
        }

        .withdrawal-form .form-select option {
            background-color: #34354a;
            color: #ffffff;
        }

        .withdrawal-form .input-group-text {
            background-color: #34354a;
            border-color: #4a4b68;
            color: #a5a5ba;
        }

        /* Dark mode table */
        .card {
            background-color: #2b2c40;
            border-color: #4a4b68;
        }

        .card-header {
            background-color: #34354a;
            border-bottom-color: #4a4b68;
        }

        .card-title {
            color: #ffffff;
        }

        .table {
            color: #d2d2dc;
        }

        .table thead th {
            background-color: #34354a;
            color: #ffffff;
            border-color: #4a4b68;
        }

        .table tbody td {
            border-color: #4a4b68;
        }

        .table-hover tbody tr:hover {
            background-color: #34354a;
        }

        /* Dark mode text colors */
        .withdrawal-page-header h4 {
            color: #ffffff;
        }

        .withdrawal-page-header .text-muted {
            color: #a5a5ba !important;
        }
    </style>

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="withdrawal-page-header">
            <a href="{{ route('user.withdrawal.index') }}" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h4 class="py-3 mb-0"><i class="fas fa-coins me-2"></i>XEVIEX USD Withdrawal</h4>
                <p class="text-muted mb-0">Withdraw your balance as XEVIEX USD to BSC network</p>
            </div>
        </div>

        <!-- XEUSD Banner -->
        <div class="xeusd-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-2">
                        <i class="fas fa-coins me-2"></i>
                        XEVIEX USD Withdrawal
                    </h3>
                    <p class="mb-2">Withdraw your USD balance as XEVIEX USD on BSC BEP20 network</p>
                    <p class="mb-0"><strong>Network:</strong> BSC
                        (BEP20) | <strong>Withdrawal Fee:</strong> 1.8% | <strong>Gas Fees:</strong> Covered by platform</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="token-info">
                        <strong>Contract Address:</strong><br>
                        <small class="text-light">0x046b0712F251f56ca1F0EecD4e57AB592FDA71be</small>
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

        <!-- Wallet Connection Guide -->
        @if (!auth()->user()->hasConnectedWallet())
            <div class="alert alert-info" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>
                    How to Connect Your Wallet
                </h5>
                <ol class="mb-2">
                    <li><strong>Install MetaMask:</strong> Make sure you have MetaMask browser extension installed</li>
                    <li><strong>Click "Connect MetaMask":</strong> Click the button below to connect your wallet</li>
                    <li><strong>Select Account:</strong> Choose the wallet address where you want to receive XEUSD tokens
                    </li>
                    <li><strong>Approve Connection:</strong> Confirm the connection in MetaMask popup</li>
                    <li><strong>Network Switch:</strong> You'll be prompted to switch to BSC network (if not already)</li>
                </ol>
                <p class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> The connected wallet address will be used to receive all your XEUSD
                    withdrawals.
                    Make sure you have access to this wallet.
                </p>
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
                        <div class="mt-2">
                            <small class="text-light">
                                <i class="fas fa-lightbulb me-1"></i>
                                <strong>Tip:</strong> To use a different wallet, first switch accounts in MetaMask
                                extension,
                                make sure it's connected to this website, then click "Reconnect Wallet" below.
                            </small>
                        </div>
                    @else
                        <h5 class="mb-2">
                            <i class="fab fa-ethereum me-2"></i>
                            Connect Your MetaMask Wallet
                        </h5>
                        <p class="mb-0">Connect your MetaMask wallet to receive XEVIEX USD on BSC network</p>
                        <small class="opacity-75 mt-2 d-block">
                            <i class="fas fa-shield-alt me-1"></i>
                            Your wallet address will be securely stored for future withdrawals
                        </small>
                    @endif
                </div>
                <div class="col-md-4 text-end">
                    @if (auth()->user()->hasConnectedWallet())
                        <button class="btn btn-light" onclick="connectWallet()" title="Connect a different wallet address">
                            <i class="fas fa-sync-alt me-2"></i>
                            Reconnect Wallet
                        </button>
                        <div class="mt-2">
                            <small class="text-light">Click to connect a different wallet</small>
                        </div>
                    @else
                        <button class="btn btn-warning btn-lg" onclick="connectWallet()">
                            <i class="fab fa-ethereum me-2"></i>
                            Connect MetaMask
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Additional Wallet Information -->
        @if (auth()->user()->hasConnectedWallet())
            <div class="alert alert-success" role="alert">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="alert-heading mb-2">
                            <i class="fas fa-check-circle me-2"></i>
                            Your Wallet is Ready
                        </h6>
                        <p class="mb-0">
                            All XEUSD withdrawals will be sent to:
                            <strong>{{ auth()->user()->getFormattedWalletAddressAttribute() }}</strong>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="showWalletGuide()">
                            <i class="fas fa-question-circle me-1"></i>
                            Need Help?
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Wallet Balances -->
        <div class="row">
            {{-- <div class="col-md-4"> #unhide this if cash wallet is to be used. look at source wallet under
                <div class="balance-card">
                    <h6 class="text-muted mb-1">Cash Balance</h6>
                    <h3 class="mb-0 text-primary">${{ number_format($cashBalance, 2) }}</h3>
                    <small class="text-muted">Available for withdrawal</small>
                </div>
            </div> --}}
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
                                {{-- @if ($cashBalance > 0)
                                    <option value="cash">Cash Wallet (${{ number_format($cashBalance, 2) }} available)
                                    </option>
                                @endif --}}
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
                            <p class="mb-1"><strong>Withdrawal Amount:</strong> $<span
                                    id="withdrawal-amount">0.00</span>
                            </p>
                            <p class="mb-1"><strong>Withdrawal Fee (1.8%):</strong> $<span id="fee-amount">0.00</span>
                            </p>
                            <p class="mb-1"><strong>You will receive:</strong> <span id="xeusd-amount">0</span> XEUSD
                                tokens</p>
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
                                                <i class="ti tabler-eye"></i>
                                            </button>
                                            @if ($withdrawal->transaction_hash)
                                                <a href="https://bscscan.com/tx/{{ $withdrawal->transaction_hash }}"
                                                    target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="ti tabler-external-link"></i>
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
                        <i class="ti tabler-clock-history" style="font-size: 3rem;"></i>
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

            // Show connecting message
            Swal.fire({
                title: 'Connecting...',
                html: `
                    <p>Please complete these steps:</p>
                    <ol class="text-start">
                        <li>Check your MetaMask extension for a popup</li>
                        <li>Make sure the correct account is selected in MetaMask</li>
                        <li>Click "Next" then "Connect" to approve</li>
                        <li>Approve the network switch to BSC if prompted</li>
                    </ol>
                    <div class="alert alert-info mt-3 text-start">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Switching wallets?</strong> Make sure you've already switched to your desired account in MetaMask before clicking Reconnect.
                        </small>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

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
                        html: `
                            <p>${result.message}</p>
                            <p class="mt-3">
                                <strong>Connected Wallet:</strong><br>
                                <small>${walletAddress}</small>
                            </p>
                            <div class="alert alert-info mt-3">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    All your XEUSD withdrawals will be sent to this address
                                </small>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: 'Continue'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(result.message);
                }

            } catch (error) {
                console.error('Error connecting wallet:', error);

                let errorMessage = error.message || 'Failed to connect wallet';
                let helpText = '';

                if (error.code === 4001) {
                    errorMessage = 'Connection request was rejected';
                    helpText =
                        'You cancelled the connection request. Please try again and approve the connection in MetaMask.';
                } else if (error.code === -32002) {
                    errorMessage = 'Connection request pending';
                    helpText =
                        'Please check your MetaMask extension. There might be a pending request waiting for your approval.';
                }

                Swal.fire({
                    title: 'Connection Failed',
                    html: `
                        <p>${errorMessage}</p>
                        ${helpText ? `<p class="text-muted mt-2"><small>${helpText}</small></p>` : ''}
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm" onclick="showWalletGuide()">
                                <i class="fas fa-question-circle me-1"></i>
                                View Connection Guide
                            </button>
                        </div>
                    `,
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
            const feePercentage = 0.018; // 1.8%
            const feeAmount = amount * feePercentage;
            const netAmount = amount - feeAmount;

            document.getElementById('withdrawal-amount').textContent = amount.toFixed(2);
            document.getElementById('fee-amount').textContent = feeAmount.toFixed(2);
            document.getElementById('xeusd-amount').textContent = netAmount.toFixed(8);
        }

        // Show wallet connection guide
        function showWalletGuide() {
            Swal.fire({
                title: 'Wallet Connection Guide',
                html: `
                    <div class="text-start">
                        <h6><i class="fas fa-question-circle me-2"></i>How to Change Wallet Address?</h6>
                        <ol>
                            <li><strong>Open MetaMask Extension:</strong> Click the MetaMask icon in your browser</li>
                            <li><strong>Switch Account:</strong> Click on your current account at the top of MetaMask</li>
                            <li><strong>Select Different Account:</strong> Choose the wallet address you want to use</li>
                            <li><strong>Connect to Website (if needed):</strong> If the new account isn't connected to this site yet, you'll see a "Not connected" status. Click "Connect" in MetaMask</li>
                            <li><strong>Click "Reconnect Wallet":</strong> Come back to this page and click the "Reconnect Wallet" button</li>
                            <li><strong>Confirm in MetaMask:</strong> Approve the connection request in the MetaMask popup</li>
                            <li><strong>Done:</strong> Your new wallet address will be saved and displayed</li>
                        </ol>

                        <hr class="my-3">

                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Important Notes</h6>
                        <ul>
                            <li>Make sure you own and have access to the wallet address</li>
                            <li>Double-check the address before submitting withdrawals</li>
                            <li>XEUSD tokens will be sent on BSC (BEP20) network only</li>
                            <li>You can reconnect anytime to change your receiving address</li>
                            <li>Gas fees for receiving tokens are covered by the platform</li>
                        </ul>

                        <hr class="my-3">

                        <h6><i class="fas fa-wallet me-2"></i>Don't Have MetaMask?</h6>
                        <p class="mb-0">
                            <a href="https://metamask.io/download/" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-download me-1"></i>
                                Download MetaMask
                            </a>
                        </p>
                    </div>
                `,
                width: 600,
                confirmButtonText: 'Got it!',
                customClass: {
                    popup: 'text-dark'
                }
            });
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
                        html: `
                            <p>Please connect your MetaMask wallet first to receive XEUSD tokens.</p>
                            <div class="mt-3">
                                <button class="btn btn-primary" onclick="connectWallet(); Swal.close();">
                                    <i class="fab fa-ethereum me-2"></i>
                                    Connect Wallet Now
                                </button>
                            </div>
                        `,
                        icon: 'warning',
                        showConfirmButton: false,
                        showCloseButton: true
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
