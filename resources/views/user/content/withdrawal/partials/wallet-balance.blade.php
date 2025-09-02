<!-- Wallet Balance Display -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5 class="card-title">Profit Balance Overview</h5>
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="text-primary">${{ number_format($user->wallet ? $user->wallet->profit : 0, 2) }}</h3>
                        <small class="text-muted">Available for Withdrawal</small>
                    </div>
                    @if ($user->wallet && $user->wallet->floating > 0)
                        <div class="col-sm-6">
                            <h5 class="text-warning">${{ number_format($user->wallet->floating, 2) }}</h5>
                            <small class="text-muted">Pending Withdrawals</small>
                        </div>
                    @endif
                </div>
                @if ($user->wallet && $user->wallet->floating > 0)
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
                    <div>Capital: ${{ number_format($user->wallet ? $user->wallet->capital : 0, 2) }}</div>
                    @if ($user->wallet && $user->wallet->floating > 0)
                        <div>Floating: ${{ number_format($user->wallet->floating, 2) }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
