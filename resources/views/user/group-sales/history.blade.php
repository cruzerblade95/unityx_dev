@extends('user.layouts.layoutMaster')

@section('title', 'Group Sales - Reward History')

@section('vendor-style')
<meta name="csrf-token" content="{{ csrf_token() }}">
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
.history-card {
    transition: all 0.3s ease;
}

.history-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.reward-badge {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}

.amount-display {
    font-size: 1.25rem;
    font-weight: 600;
    color: #28a745;
}

.stats-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
</style>
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">User / Group Sales / </span>Reward History
            </h4>
            <p class="text-muted mb-0">View your claimed group sales bonus rewards history</p>
        </div>
        <a href="{{ route('user.group-sales.index') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left me-1"></i>Back to Group Sales
        </a>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-2">
                        <span class="avatar-initial rounded-circle bg-primary">
                            <i class="fas fa-trophy"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">{{ $history->total() }}</h5>
                    <small class="text-muted">Total Claims</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-2">
                        <span class="avatar-initial rounded-circle bg-success">
                            <i class="fas fa-dollar-sign"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">${{ number_format($history->sum('reward_amount'), 2) }}</h5>
                    <small class="text-muted">Total Earned</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-2">
                        <span class="avatar-initial rounded-circle bg-info">
                            <i class="fas fa-calendar"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">{{ \Carbon\Carbon::now()->format('M Y') }}</h5>
                    <small class="text-muted">Current Month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-2">
                        <span class="avatar-initial rounded-circle bg-warning">
                            <i class="fas fa-star"></i>
                        </span>
                    </div>
                    @php
                        $currentMonth = \Carbon\Carbon::now()->format('Y-m');
                        $currentMonthClaims = $history->where('claimed_month', $currentMonth)->count();
                    @endphp
                    <h5 class="mb-1">{{ $currentMonthClaims }}</h5>
                    <small class="text-muted">This Month</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Reward History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Reward Claim History
                    </h5>
                    <small class="text-muted">Complete history of your group sales bonus reward claims</small>
                </div>
                <div class="card-body">
                    @if($history->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" id="historyTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Reward</th>
                                        <th>Sales Threshold</th>
                                        <th>Reward Amount</th>
                                        <th>Tax Applied</th>
                                        <th>Total Sales</th>
                                        <th>Month</th>
                                        <th>Claimed Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history as $claim)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary reward-badge">
                                                    <i class="fas fa-trophy me-1"></i>Reward {{ $claim->reward_level }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-medium">${{ number_format($claim->sales_threshold) }}</span>
                                            </td>
                                            <td>
                                                <span class="amount-display">${{ number_format($claim->reward_amount, 2) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">{{ $claim->tax_percentage }}%</span>
                                            </td>
                                            <td>
                                                <span class="text-success fw-medium">${{ number_format($claim->total_sales_at_claim, 2) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ \Carbon\Carbon::createFromFormat('Y-m', $claim->claimed_month)->format('M Y') }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ \Carbon\Carbon::parse($claim->claimed_at)->format('M d, Y H:i') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $history->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded-circle bg-label-secondary">
                                    <i class="fas fa-history fa-2x"></i>
                                </span>
                            </div>
                            <h5 class="text-muted">No Reward Claims Yet</h5>
                            <p class="text-muted mb-3">You haven't claimed any group sales rewards yet. Start building your downline sales to unlock rewards!</p>
                            <a href="{{ route('user.group-sales.index') }}" class="btn btn-primary">
                                <i class="fas fa-trophy me-1"></i>View Available Rewards
                            </a>
                        </div>
                    @endif
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
    if ($.fn.DataTable && $('#historyTable').length) {
        $('#historyTable').DataTable({
            responsive: true,
            order: [[6, 'desc']], // Sort by claimed date, newest first
            columnDefs: [
                { targets: [2, 4], type: 'num-fmt' }, // Amount columns
                { targets: [6], type: 'date' } // Date column
            ],
            language: {
                search: "Search history:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ claims",
                infoEmpty: "No claims found",
                emptyTable: "No reward claims available"
            },
            pageLength: 25,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
        });
    }
});
</script>
@endsection
