@extends('user.layouts.layoutMaster')

@section('title', 'Deposit Promo Bonuses')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
<script>
    // Make route available to JavaScript
    window.userBonusClaimRoute = '{{ route("user.bonuses.claim", ":id") }}';
</script>
@vite(['resources/assets/js/user-deposit-promo-bonuses.js'])
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Deposit Promo Bonuses</h1>
        <div class="text-muted">
            <i class="fas fa-gift"></i> Total Claimed: <strong>${{ number_format($totalClaimedAmount, 2) }}</strong>
        </div>
    </div>

    <!-- Available Bonuses -->
    @if($availableBonuses->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-gradient-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-gift"></i> Available Bonuses ({{ $availableBonuses->count() }})
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($availableBonuses as $bonus)
                <div class="col-md-6 col-lg-4 mb-3 mt-2">
                    <div class="card border-primary h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">{{ $bonus->campaign->name }}</h6>
                        </div>
                        <div class="card-body">
                            <p class="card-text">{{ $bonus->campaign->description ?: 'No description available' }}</p>

                            <div class="bonus-details mb-3">
                                <div class="row">
                                    {{-- <div class="col-6">
                                        <small class="text-muted">Deposit Amount</small>
                                        <div class="fw-bold">${{ number_format($bonus->deposit_amount, 2) }}</div>
                                    </div> --}}
                                    <div class="col-6">
                                        <small class="text-muted">Bonus Amount you can claim</small>
                                        <div class="fw-bold text-success">${{ number_format($bonus->user_bonus_amount, 2) }}</div>
                                    </div>
                                </div>

                                @if($bonus->upline_bonus_amount > 0)
                                {{-- <div class="row mt-2">
                                    <div class="col-12">
                                        <small class="text-muted">Upline Bonus</small>
                                        <div class="fw-bold text-info">${{ number_format($bonus->upline_bonus_amount, 2) }}</div>
                                    </div>
                                </div> --}}
                                @endif
                            </div>

                            @if($bonus->expires_at)
                            <div class="alert alert-warning p-2 mb-3">
                                <small>
                                    <i class="fas fa-clock"></i>
                                    Expires: {{ $bonus->expires_at->format('M j, Y g:i A') }}
                                    <br>
                                    <span class="countdown" data-expires="{{ $bonus->expires_at->toISOString() }}">
                                        Calculating...
                                    </span>
                                </small>
                            </div>
                            @endif

                            <button class="btn btn-success btn-sm w-100 claim-bonus-btn"
                                    data-bonus-id="{{ $bonus->id }}"
                                    data-bonus-amount="{{ $bonus->user_bonus_amount }}">
                                <i class="fas fa-hand-holding-usd"></i> Claim Bonus
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @else
    <div class="card shadow mb-4">
        <div class="card-body text-center py-5">
            <i class="fas fa-gift fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Available Bonuses</h5>
            <p class="text-muted">You don't have any available deposit promo bonuses to claim at the moment.</p>
        </div>
    </div>
    @endif

    <!-- Claimed Bonuses -->
    @if($claimedBonuses->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-check-circle"></i> Recently Claimed Bonuses
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Deposit</th>
                            <th>Bonus</th>
                            <th>Claimed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($claimedBonuses as $bonus)
                        <tr>
                            <td>{{ $bonus->campaign->name }}</td>
                            <td>${{ number_format($bonus->deposit_amount, 2) }}</td>
                            <td class="text-success">${{ number_format($bonus->user_bonus_amount, 2) }}</td>
                            <td>{{ $bonus->claimed_at->format('M j, Y g:i A') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Expired Bonuses -->
    @if($expiredBonuses->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">
                <i class="fas fa-exclamation-triangle"></i> Recently Expired Bonuses
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Deposit</th>
                            <th>Missed Bonus</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiredBonuses as $bonus)
                        <tr>
                            <td>{{ $bonus->campaign->name }}</td>
                            <td>${{ number_format($bonus->deposit_amount, 2) }}</td>
                            <td class="text-danger">${{ number_format($bonus->user_bonus_amount, 2) }}</td>
                            <td>{{ $bonus->created_at->format('M j, Y g:i A') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Claim Confirmation Modal -->
<div class="modal fade" id="claimBonusModal" tabindex="-1" aria-labelledby="claimBonusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="claimBonusModalLabel">Confirm Bonus Claim</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-gift fa-3x text-success mb-3"></i>
                    <h5>Claim Bonus</h5>
                    <p class="mb-3">Are you sure you want to claim this bonus?</p>
                    <div class="bonus-amount-display">
                        <strong class="text-success fs-4" id="claimAmountDisplay">$0.00</strong>
                    </div>
                    <p class="text-muted mt-2">This bonus will be added to your deposit wallet immediately.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmClaimBtn">
                    <i class="fas fa-hand-holding-usd"></i> Claim Now
                </button>
            </div>
        </div>
    </div>
</div>
@endsection


