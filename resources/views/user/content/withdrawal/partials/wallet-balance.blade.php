<!-- Wallet Balance Display -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <h5 class="card-title mb-3">Withdrawable Balance Overview</h5>
                <div class="row g-3">
                    <div class="col-lg-4 col-md-4 col-sm-6 col-6">
                        <div class="balance-item">
                            <h5 class="text-success mb-1">
                                ${{ number_format($user->wallet ? $user->wallet->profit : 0, 2) }}</h5>
                            <small class="text-muted">Profit Balance</small>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-6">
                        <div class="balance-item">
                            <h5 class="text-warning mb-1">
                                ${{ number_format($user->wallet ? $user->wallet->bonus : 0, 2) }}</h5>
                            <small class="text-muted">Bonus Balance</small>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                        <div class="balance-item">
                            <h3 class="text-primary mb-1">${{ number_format($walletBalance, 2) }}</h3>
                            <small class="text-muted">Total Available</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 mt-lg-0 mt-3">
                <div class="balance-details text-lg-end text-start">
                    <div class="small text-muted">
                        <div><strong>Balance System:</strong></div>
                        <div>Profit: ${{ number_format($user->wallet ? $user->wallet->profit : 0, 2) }}</div>
                        <div>Bonus: ${{ number_format($user->wallet ? $user->wallet->bonus : 0, 2) }}</div>
                        <div>Deposit: ${{ number_format($user->wallet ? $user->wallet->deposit : 0, 2) }}</div>
                        <hr class="my-1">
                        <div><strong>Withdrawable: ${{ number_format($walletBalance, 2) }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .balance-item {
            text-align: center;
            padding: 0.75rem;
            background: var(--bs-tertiary-bg);
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .balance-item h5,
        .balance-item h3 {
            font-size: 1.1rem;
        }

        .balance-details {
            background: var(--bs-secondary-bg);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }

        .balance-details .small {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        .balance-item {
            padding: 0.5rem;
        }

        .balance-item h5,
        .balance-item h3 {
            font-size: 1rem;
        }

        .balance-item small {
            font-size: 0.75rem;
        }

        .balance-details {
            padding: 0.75rem;
        }

        .card-title {
            font-size: 1.1rem;
        }
    }
</style>
