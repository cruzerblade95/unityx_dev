@extends('user.layouts.layoutMaster')

@section('title', 'Marketplace')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .listing-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            height: 100%;
        }

        .listing-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .wallet-balance-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .balance-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            padding: 1rem;
            backdrop-filter: blur(10px);
        }

        .listing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .listing-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .listing-card .card-body {
            position: relative;
        }

        /* Custom pagination styling */
        .pagination {
            margin-bottom: 0;
        }

        .pagination .page-link {
            color: #696cff;
            border-color: #d9dee3;
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }

        .pagination .page-link:hover {
            color: #5f63f2;
            background-color: #f8f9fa;
            border-color: #d9dee3;
        }

        .pagination .page-item.active .page-link {
            background-color: #696cff;
            border-color: #696cff;
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            color: #a7a9b7;
            background-color: white;
            border-color: #d9dee3;
        }
    </style>
@endsection

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-store me-2"></i>
                USDT Marketplace
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createListingModal">
                <i class="fas fa-plus me-2"></i>
                Sell USDT
            </button>
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

        @if (empty(auth()->user()->telegram_id))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fab fa-telegram-plane me-2"></i>
                <strong>Telegram Connection Required!</strong>
                You need to connect your Telegram account to use marketplace features.
                <a href="{{ route('user.profile') }}" class="alert-link">Connect now in your profile settings</a>.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Wallet Balances -->
        <div class="card wallet-balance-card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="fas fa-wallet me-2"></i>
                    Your Wallet Balances
                </h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="balance-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Cash Balance</h6>
                                    <small class="opacity-75">Available for marketplace</small>
                                </div>
                                <h4 class="mb-0">${{ number_format($cashBalance, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="balance-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Profit Balance</h6>
                                    <small class="opacity-75">For cash top-up</small>
                                </div>
                                <h4 class="mb-0">${{ number_format($profitBalance, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="balance-item">
                            <button class="btn btn-light btn-sm w-100" data-bs-toggle="modal" data-bs-target="#topUpModal">
                                <i class="fas fa-arrow-up me-2"></i>
                                Top Up Cash (1.5% fee)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Verifications (for buyers) -->
        @if (isset($pendingVerifications) && $pendingVerifications->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Your Pending Verifications
                        <span class="badge bg-dark ms-2">{{ $pendingVerifications->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($pendingVerifications as $verification)
                            <div class="col-md-6 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">{{ number_format($verification->amount, 2) }} USDT
                                            </h6>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-tag me-1"></i>
                                                <strong>Listing ID:</strong> <code>{{ $verification->unique_id }}</code>
                                            </small>
                                        </div>

                                        <div class="alert alert-info mb-3">
                                            <h6 class="alert-heading mb-2">
                                                <i class="fab fa-telegram me-2"></i>
                                                Contact Seller via Telegram
                                            </h6>
                                            <p class="mb-2">Contact the seller using their Telegram username below to
                                                verify the transaction. Please mention listing ID
                                                <strong>{{ $verification->unique_id }}</strong> when contacting.
                                            </p>
                                            <div class="row">
                                                <div class="col-12">
                                                    <strong>Telegram Contact:</strong><br>
                                                    @if ($verification->seller && $verification->seller->telegram_id)
                                                        @if (is_numeric($verification->seller->telegram_id))
                                                            <code>{{ $verification->seller->telegram_id }}</code>
                                                            <small class="text-muted d-block">(Telegram ID - search in
                                                                Telegram)</small>
                                                        @else
                                                            <a href="https://t.me/{{ $verification->seller->telegram_id }}"
                                                                target="_blank" class="btn btn-outline-primary btn-sm">
                                                                <i class="fab fa-telegram me-2"></i>
                                                                {{ '@' . $verification->seller->telegram_id }}
                                                            </a>
                                                            <small class="text-muted d-block mt-1">(Click to open in
                                                                Telegram)</small>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">Not provided</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <small class="text-muted mt-2 d-block">
                                                <i class="fas fa-info-circle me-1"></i>
                                                The seller will approve/reject the transaction here on the marketplace after
                                                verification.
                                            </small>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted">You'll receive USDT:
                                                {{ number_format($verification->amount * 0.982, 2) }} USDT</small>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-hourglass-half me-1"></i>
                                                Time Remaining: <span class="countdown text-danger"
                                                    data-expires="{{ $verification->verification_expires_at }}"></span>
                                            </small>
                                        </div>

                                        <button class="btn btn-outline-secondary btn-sm w-100"
                                            onclick="cancelVerification('{{ $verification->unique_id }}')">
                                            <i class="fas fa-times me-2"></i>
                                            Cancel Verification
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Verification Requests (for sellers) -->
        @if (isset($verificationRequests) && $verificationRequests->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bell me-2"></i>
                        Verification Requests for Your Listings
                        <span class="badge bg-light text-dark ms-2">{{ $verificationRequests->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($verificationRequests as $request)
                            <div class="col-md-6 mb-3">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">{{ number_format($request->amount, 2) }} USDT</h6>
                                            <span class="badge bg-info">Verification Request</span>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-tag me-1"></i>
                                                <strong>Listing ID:</strong> <code>{{ $request->unique_id }}</code>
                                            </small>
                                        </div>

                                        <div class="alert alert-light mb-3">
                                            <h6 class="alert-heading mb-2">
                                                <i class="fas fa-user me-2"></i>
                                                Buyer Information
                                            </h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>Buyer:</strong><br>
                                                    {{ $request->buyer->username ?? 'Unknown' }}
                                                </div>
                                                <div class="col-6">
                                                    <strong>Contact:</strong><br>
                                                    <small>{{ $request->buyer->email ?? 'No email' }}</small>
                                                </div>
                                            </div>
                                            <small class="text-muted mt-2 d-block">
                                                <i class="fas fa-info-circle me-1"></i>
                                                The buyer should contact you via your Telegram. After verification,
                                                approve/reject below.
                                            </small>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted">USDT Amount to give:
                                                {{ number_format($request->amount, 2) }} USDT</small>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-hourglass-half me-1"></i>
                                                Time Remaining: <span class="countdown text-danger"
                                                    data-expires="{{ $request->verification_expires_at }}"></span>
                                            </small>
                                        </div>

                                        <div class="row">
                                            <div class="col-6">
                                                <button class="btn btn-success btn-sm w-100"
                                                    onclick="approveVerification('{{ $request->unique_id }}')">
                                                    <i class="fas fa-check me-2"></i>
                                                    Approve
                                                </button>
                                            </div>
                                            <div class="col-6">
                                                <button class="btn btn-danger btn-sm w-100"
                                                    onclick="showRejectModal('{{ $request->unique_id }}')">
                                                    <i class="fas fa-times me-2"></i>
                                                    Reject
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Active Listings -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Available USDT Listings
                </h5>
            </div>
            <div class="card-body">
                @if ($listings->count() > 0)
                    <div class="listing-grid">
                        @foreach ($listings as $listing)
                            <div class="card listing-card">
                                <div class="card-body">
                                    <span class="badge bg-success status-badge">Available</span>

                                    <div class="text-center mb-3">
                                        <i class="fas fa-coins text-warning" style="font-size: 2rem;"></i>
                                    </div>

                                    <h6 class="card-title text-center">{{ number_format($listing->amount, 2) }} USDT</h6>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">Multiplier:</small>
                                            <strong>x{{ number_format($listing->price_per_usdt, 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">Total Price:</small>
                                            <strong>${{ number_format($listing->total_price, 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">USDT After Fee (1.8%):</small>
                                            <small class="text-success">{{ number_format($listing->amount * 0.982, 2) }}
                                                USDT</small>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>You Receive:</strong>
                                            <strong class="text-success">{{ number_format($listing->amount * 0.982, 2) }}
                                                USDT</strong>
                                        </div>
                                    </div>

                                    @if ($listing->description)
                                        <p class="card-text small text-muted">
                                            {{ strlen($listing->description) > 80 ? substr($listing->description, 0, 80) . '...' : $listing->description }}
                                        </p>
                                    @endif

                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $listing->seller->username ?? 'Seller' }}
                                        </small>
                                        <small class="text-muted">ID: {{ $listing->unique_id }}</small>
                                    </div>

                                    <button class="btn btn-primary btn-sm w-100 mt-3"
                                        onclick="purchaseListing('{{ $listing->unique_id }}', {{ $listing->amount }})">
                                        <i class="fas fa-gift me-2"></i>
                                        Start Verification
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if ($listings->hasPages())
                        <div class="mt-4">
                            <!-- Results Info -->
                            <div class="text-center mb-3">
                                <small class="text-muted">
                                    Showing {{ $listings->firstItem() }} to {{ $listings->lastItem() }} of
                                    {{ $listings->total() }} listings
                                </small>
                            </div>
                            <!-- Pagination Links -->
                            <div class="d-flex justify-content-center">
                                {{ $listings->links('pagination::custom-bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-store-slash text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">No USDT listings available</h5>
                        <p class="text-muted">Be the first to create a listing!</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- My Listings -->
        @if ($myListings->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        My Listings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Amount</th>
                                    <th>Multiplier</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($myListings as $listing)
                                    <tr>
                                        <td><code>{{ $listing->unique_id }}</code></td>
                                        <td>{{ number_format($listing->amount, 2) }} USDT</td>
                                        <td>x{{ number_format($listing->price_per_usdt, 2) }}</td>
                                        <td>${{ number_format($listing->total_price, 2) }}</td>
                                        <td>
                                            @if ($listing->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @elseif($listing->status === 'sold')
                                                <span class="badge bg-primary">Sold</span>
                                            @else
                                                <span class="badge bg-secondary">Cancelled</span>
                                            @endif
                                        </td>
                                        <td>{{ $listing->created_at->diffForHumans() }}</td>
                                        <td>
                                            @if ($listing->status === 'active')
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="cancelListing('{{ $listing->unique_id }}')">
                                                    <i class="fas fa-times me-1"></i>
                                                    Cancel
                                                </button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Create Listing Modal -->
    <div class="modal fade" id="createListingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>
                        Create USDT Listing
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('user.marketplace.create') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Minimum:</strong> 10 USDT | <strong>Available Cash:</strong>
                            ${{ number_format($cashBalance, 2) }}
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">USDT Amount</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                                    required min="10" max="{{ $cashBalance }}">
                                <span class="input-group-text">USDT</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="price_per_usdt" class="form-label">Price Multiplier</label>
                            <div class="input-group">
                                <span class="input-group-text">x</span>
                                <input type="number" step="0.01" class="form-control" id="price_per_usdt"
                                    name="price_per_usdt" required min="0.95" max="1.5" value="1.00"
                                    onchange="calculateFinalPrice()">
                            </div>
                            <div class="form-text">Multiplier range: x0.95 - x1.50</div>
                        </div>

                        <div class="mb-3">
                            <div class="card border-info">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-2">
                                        <i class="fas fa-calculator me-2"></i>
                                        Price Calculation
                                    </h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">USDT Amount:</small>
                                            <div id="calc-amount">0 USDT</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Multiplier:</small>
                                            <div id="calc-multiplier">x1.00</div>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <hr class="my-2">
                                            <strong class="text-primary">Final Price: <span
                                                    id="calc-final">$0.00</span></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3" maxlength="500"
                                placeholder="Add a description for your listing..."></textarea>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            The USDT amount will be deducted from your cash wallet immediately. Final price will be
                            calculated as: <strong>Amount × Multiplier</strong>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Create Listing
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Top Up Cash Modal -->
    <div class="modal fade" id="topUpModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-arrow-up me-2"></i>
                        Top Up Cash Wallet
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('user.marketplace.topup') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Available Profit:</strong> ${{ number_format($profitBalance, 2) }} |
                            <strong>Fee:</strong> 1.5%
                        </div>

                        <div class="mb-3">
                            <label for="topup_amount" class="form-label">Amount to Transfer</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" id="topup_amount"
                                    name="amount" required min="1" max="{{ $profitBalance * 0.985 }}">
                            </div>
                            <div class="form-text">Fee (1.5%) will be deducted from your profit wallet</div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-calculator me-2"></i>
                            <strong>Example:</strong> Transfer $100 → You pay $101.50 (including 1.5% fee)
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-arrow-up me-2"></i>
                            Top Up Cash
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function calculateFinalPrice() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const multiplier = parseFloat(document.getElementById('price_per_usdt').value) || 1.00;
            const finalPrice = amount * multiplier;

            document.getElementById('calc-amount').textContent = amount.toFixed(2) + ' USDT';
            document.getElementById('calc-multiplier').textContent = 'x' + multiplier.toFixed(2);
            document.getElementById('calc-final').textContent = '$' + finalPrice.toFixed(2);
        }

        // Update calculation when amount changes
        document.addEventListener('DOMContentLoaded', function() {
            const amountInput = document.getElementById('amount');
            const multiplierInput = document.getElementById('price_per_usdt');

            if (amountInput) {
                amountInput.addEventListener('input', calculateFinalPrice);
            }
            if (multiplierInput) {
                multiplierInput.addEventListener('input', calculateFinalPrice);
            }

            // Initial calculation
            calculateFinalPrice();
        });

        function purchaseListing(listingId, usdtAmount) {
            const feeDeduction = usdtAmount * 0.018;
            const finalAmount = usdtAmount * 0.982;

            Swal.fire({
                title: 'Start Verification Process?',
                html: `
            <div class="text-start">
                <p><strong>USDT Amount:</strong> ${usdtAmount.toFixed(2)} USDT</p>
                <p><strong>Fee (1.8%):</strong> -${feeDeduction.toFixed(2)} USDT</p>
                <p><strong>You'll Receive:</strong> ${finalAmount.toFixed(2)} USDT</p>
                <div class="alert alert-success">
                    <i class="fas fa-gift me-2"></i>
                    <strong>BUYING USDT!</strong> Payment on Telegram.
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Process:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Contact seller via Telegram for verification</li>
                        <li>Seller will approve/reject on this marketplace</li>
                        <li>You have <strong>45 minutes</strong> to complete</li>
                        <li>USDT will be added to your deposit wallet when approved</li>
                    </ol>
                </div>
            </div>
        `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Start Verification'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/user/marketplace/purchase/${listingId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    form.appendChild(csrfToken);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function approveVerification(listingId) {
            Swal.fire({
                title: 'Approve Transaction?',
                text: 'This will transfer USDT from your cash wallet to the buyer\'s deposit wallet.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Approve Transaction'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/user/marketplace/approve/${listingId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    form.appendChild(csrfToken);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function showRejectModal(listingId) {
            Swal.fire({
                title: 'Reject Transaction',
                input: 'textarea',
                inputLabel: 'Reason for rejection',
                inputPlaceholder: 'Please provide a reason for rejecting this transaction...',
                inputAttributes: {
                    'aria-label': 'Reason for rejection',
                    'maxlength': '255'
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Reject Transaction',
                inputValidator: (value) => {
                    if (!value || value.trim().length < 5) {
                        return 'Please provide a reason (at least 5 characters)';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/user/marketplace/reject/${listingId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    const reasonInput = document.createElement('input');
                    reasonInput.type = 'hidden';
                    reasonInput.name = 'reason';
                    reasonInput.value = result.value;

                    form.appendChild(csrfToken);
                    form.appendChild(reasonInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function cancelVerification(listingId) {
            Swal.fire({
                title: 'Cancel Verification?',
                text: 'This will cancel the verification process and make the listing available again.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Cancel Verification'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/user/marketplace/cancel-verification/${listingId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    form.appendChild(csrfToken);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Countdown timer for verification expiration
        function updateCountdowns() {
            const countdowns = document.querySelectorAll('.countdown');

            countdowns.forEach(countdown => {
                const expires = new Date(countdown.dataset.expires).getTime();
                const now = new Date().getTime();
                const distance = expires - now;

                if (distance > 0) {
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    countdown.innerHTML = `${minutes}m ${seconds}s`;
                } else {
                    countdown.innerHTML = 'EXPIRED';
                    countdown.classList.add('text-danger');
                }
            });
        }

        // Update countdowns every second
        document.addEventListener('DOMContentLoaded', function() {
            updateCountdowns();
            setInterval(updateCountdowns, 1000);
        });

        function cancelListing(listingId) {
            Swal.fire({
                title: 'Cancel Listing?',
                text: 'The USDT will be returned to your cash wallet',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/user/marketplace/cancel/${listingId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    form.appendChild(csrfToken);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
@endsection
