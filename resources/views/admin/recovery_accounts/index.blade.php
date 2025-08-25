@extends('admin.layouts.layoutMaster')

@section('title', 'Recovery Accounts Management')

@section('vendor-style')
<!-- Add FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-life-ring me-2"></i>Recovery Accounts
                        </h5>
                        <small class="text-muted">Manage account recovery requests</small>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="input-group" style="width: 200px;">
                            <span class="input-group-text">
                                <i class="fas fa-filter"></i>
                            </span>
                            <select class="form-select" id="statusFilter" onchange="filterByStatus(this.value)">
                                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                    <th><i class="fas fa-envelope me-1"></i>Email</th>
                                    <th><i class="fas fa-dollar-sign me-1"></i>Deposit</th>
                                    <th><i class="fas fa-chart-line me-1"></i>Profit</th>
                                    <th><i class="fas fa-user-friends me-1"></i>Referral</th>
                                    <th><i class="fas fa-flag me-1"></i>Status</th>
                                    <th><i class="fas fa-calendar me-1"></i>Submitted</th>
                                    <th><i class="fas fa-cogs me-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recoveries as $recovery)
                                    <tr>
                                        <td>{{ $recovery->id }}</td>
                                        <td>{{ $recovery->email }}</td>
                                        <td>${{ number_format($recovery->deposit, 2) }}</td>
                                        <td>${{ number_format($recovery->profit, 2) }}</td>
                                        <td>{{ $recovery->referral_username ?: '-' }}</td>
                                        <td>
                                            @if($recovery->status == 'pending')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>Pending
                                                </span>
                                            @elseif($recovery->status == 'approved')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Approved
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>Rejected
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $recovery->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.recovery_accounts.show', $recovery->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">No recovery requests found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $recoveries->appends(['status' => $status])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterByStatus(status) {
    window.location.href = '{{ route("admin.recovery_accounts.index") }}?status=' + status;
}
</script>
@endsection
