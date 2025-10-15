@extends('user.layouts.layoutMaster')

@section('title', 'Group Sales')

@section('vendor-style')
<meta name="csrf-token" content="{{ csrf_token() }}">
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
.reward-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.reward-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.reward-card.claimed {
    border-color: #28a745;
    background-color: #f8fff9;
}

.reward-card.available {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.reward-card.locked {
    opacity: 0.6;
    background-color: #f8f9fa;
}

.progress-custom {
    height: 8px;
    border-radius: 10px;
}

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.level-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.sales-amount {
    font-weight: 600;
    color: #28a745;
}
</style>
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">User / </span>Performance Bonus
    </h4>

    <!-- Sales Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title text-white mb-2">
                                <i class="fas fa-chart-line me-2"></i>Total Downline Sales (10 Levels)
                            </h5>
                            <h2 class="text-white mb-0">${{ number_format($totalSales, 2) }}</h2>
                            <small class="text-white-50">Total sales from all your downlines up to 10 levels</small>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="avatar avatar-xl">
                                <span class="avatar-initial rounded-circle bg-opacity-20">
                                    <i class="fas fa-chart-pie fa-2x text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reward System -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-trophy me-2 text-warning"></i>Monthly Bonus Rewards
                        </h5>
                        <a href="{{ route('user.group-sales.history') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-history me-1"></i>View History
                        </a>
                    </div>
                    <small class="text-muted">Claim your monthly bonus rewards based on your total downline sales. Each reward can only be claimed once per month.</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($rewards as $level => $reward)
                            @php
                                $isClaimed = in_array($level, $claimedRewards);
                                $isAvailable = $totalSales >= $reward['threshold'] && !$isClaimed;
                                $progressPercentage = min(($totalSales / $reward['threshold']) * 100, 100);

                                // Use the pre-calculated reward amount from the configuration
                                $rewardAmount = $reward['reward_amount'];
                            @endphp

                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card reward-card {{ $isClaimed ? 'claimed' : ($isAvailable ? 'available' : 'locked') }} h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="card-title mb-1">{{ $reward['title'] ?? 'Reward ' . $level }}</h6>
                                                <span class="badge bg-{{ $isClaimed ? 'success' : ($isAvailable ? 'primary' : 'secondary') }}">
                                                    @if($isClaimed)
                                                        <i class="fas fa-check me-1"></i>Claimed
                                                    @elseif($isAvailable)
                                                        <i class="fas fa-gift me-1"></i>Available
                                                    @else
                                                        <i class="fas fa-lock me-1"></i>Locked
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="text-end">
                                                <i class="fas fa-trophy text-warning"></i>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">Sales Required:</small>
                                            <div class="fw-bold">${{ number_format($reward['threshold']) }}</div>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">Reward Amount:</small>
                                            <div class="fw-bold text-success">${{ number_format($rewardAmount, 2) }}</div>
                                            <small class="text-muted">${{ number_format($reward['threshold']) }} × {{ $reward['base_percentage'] }}% × {{ $reward['level_percentage'] }}% × {{ $reward['tax'] }}% tax</small>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted">Progress</small>
                                                <small class="text-muted">{{ number_format($progressPercentage, 1) }}%</small>
                                            </div>
                                            <div class="progress progress-custom">
                                                <div class="progress-bar bg-{{ $isClaimed ? 'success' : ($isAvailable ? 'primary' : 'secondary') }}"
                                                     style="width: {{ $progressPercentage }}%"></div>
                                            </div>
                                        </div>

                                        @if($isAvailable)
                                            <button class="btn btn-primary btn-sm w-100 claim-reward-btn"
                                                    data-reward-level="{{ $level }}"
                                                    data-reward-amount="{{ $rewardAmount }}"
                                                    data-threshold="{{ $reward['threshold'] }}">
                                                <i class="fas fa-gift me-1"></i>Claim Reward
                                            </button>
                                        @elseif($isClaimed)
                                            <button class="btn btn-success btn-sm w-100" disabled>
                                                <i class="fas fa-check me-1"></i>Already Claimed
                                            </button>
                                        @else
                                            <button class="btn btn-secondary btn-sm w-100" disabled>
                                                <i class="fas fa-lock me-1"></i>Not Available
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Downline Sales Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>Downline Sales (10 Levels)
                    </h5>
                    <small class="text-muted">View sales data from all your downlines up to 10 levels deep</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="downlineSalesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Level</th>
                                    <th>Sales Amount</th>
                                    <th>Join Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($downlineSales as $index => $sale)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium">{{ $sale['firstname'] }} {{ $sale['lastname'] }}</span>
                                                <small class="text-muted">{{ $sale['username'] }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary level-badge">Level {{ $sale['level'] }}</span>
                                        </td>
                                        <td>
                                            <span class="sales-amount">${{ number_format($sale['sales_amount'], 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $sale['join_date']->format('M d, Y') }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-2x mb-2"></i>
                                                <p>No downline sales found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Initialize DataTable
    if ($.fn.DataTable) {
        $('#downlineSalesTable').DataTable({
            responsive: true,
            order: [[2, 'asc'], [3, 'desc']], // Sort by level first, then by sales amount
            columnDefs: [
                { targets: [0], orderable: false },
                { targets: [3], type: 'num-fmt' }
            ],
            language: {
                search: "Search downlines:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ downlines",
                infoEmpty: "No downlines found",
                emptyTable: "No downline sales data available"
            }
        });
    }

    // Handle reward claim
    $('.claim-reward-btn').on('click', function() {
        const rewardLevel = $(this).data('reward-level');
        const rewardAmount = $(this).data('reward-amount');
        const threshold = $(this).data('threshold');
        const button = $(this);

        Swal.fire({
            title: 'Claim Reward ' + rewardLevel,
            html: `
                <div class="text-start">
                    <p><strong>Sales Threshold:</strong> $${threshold.toLocaleString()}</p>
                    <p><strong>Reward Amount:</strong> $${rewardAmount.toLocaleString()}</p>
                    <p class="text-muted">This reward can only be claimed once per month and will be added to your bonus balance.</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Claim Reward!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                claimReward(rewardLevel, button);
            }
        });
    });

    function claimReward(rewardLevel, button) {
        const originalText = button.html();
        button.html('<i class="fas fa-spinner fa-spin me-1"></i>Claiming...');
        button.prop('disabled', true);

        $.ajax({
            url: '{{ route("user.group-sales.claim-reward") }}',
            type: 'POST',
            data: {
                reward_level: rewardLevel,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reward Claimed!',
                        text: response.message,
                        timer: 3000,
                        showConfirmButton: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Claim Failed',
                        text: response.message
                    });
                    button.html(originalText);
                    button.prop('disabled', false);
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while claiming the reward.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });

                button.html(originalText);
                button.prop('disabled', false);
            }
        });
    }
});
</script>
@endsection
