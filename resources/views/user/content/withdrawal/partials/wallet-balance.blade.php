<!-- Wallet Balance Display -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5 class="card-title">Withdrawable Balance Overview</h5>
                <div class="row">
                    <div class="col-sm-4">
                        <h5 class="text-success">${{ number_format($user->wallet ? $user->wallet->profit : 0, 2) }}</h5>
                        <small class="text-muted">Profit Balance</small>
                    </div>
                    <div class="col-sm-4">
                        <h5 class="text-warning">${{ number_format($user->wallet ? $user->wallet->bonus : 0, 2) }}</h5>
                        <small class="text-muted">Bonus Balance</small>
                    </div>
                    <div class="col-sm-4">
                        <h3 class="text-primary">${{ number_format($walletBalance, 2) }}</h3>
                        <small class="text-muted">Total Available</small>
                    </div>
                </div>
                @if ($user->wallet && $user->wallet->floating > 0)
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-sm-6">
                                <h5 class="text-info">${{ number_format($user->wallet->floating, 2) }}</h5>
                                <small class="text-muted">Pending Withdrawals</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Floating balance represents amounts held for pending withdrawal requests
                        </small>
                    </div>
                @endif
            </div>
            <div class="col-md-4 text-end">
                <div class="small text-muted">
                    <div><strong>Balance System:</strong></div>
                    <div>Profit: ${{ number_format($user->wallet ? $user->wallet->profit : 0, 2) }}</div>
                    <div>Bonus: ${{ number_format($user->wallet ? $user->wallet->bonus : 0, 2) }}</div>
                    <div>Deposit: ${{ number_format($user->wallet ? $user->wallet->deposit : 0, 2) }}</div>
                    @if ($user->wallet && $user->wallet->floating > 0)
                        <div>Floating: ${{ number_format($user->wallet->floating, 2) }}</div>
                    @endif
                    <hr class="my-1">
                    <div><strong>Withdrawable: ${{ number_format($walletBalance, 2) }}</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
