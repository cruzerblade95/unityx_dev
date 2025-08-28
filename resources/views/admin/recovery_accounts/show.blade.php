@extends('admin.layouts.layoutMaster')

@section('title', 'Recovery Account Details')

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
                            <i class="fas fa-life-ring me-2"></i>Recovery Account #{{ $recovery->id }}
                        </h5>
                        <small class="text-muted">Account recovery request details</small>
                    </div>
                    <a href="{{ route('admin.recovery_accounts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="mb-3">
                                <i class="fas fa-info-circle me-2"></i>Account Information
                            </h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150">
                                        <strong><i class="fas fa-envelope me-1"></i>Email:</strong>
                                    </td>
                                    <td>{{ $recovery->email }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><i class="fas fa-dollar-sign me-1"></i>Deposit:</strong>
                                    </td>
                                    <td>${{ number_format($recovery->deposit, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><i class="fas fa-chart-line me-1"></i>Profit:</strong>
                                    </td>
                                    <td>${{ number_format($recovery->profit, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><i class="fas fa-user-friends me-1"></i>Referral:</strong>
                                    </td>
                                    <td>{{ $recovery->referral_username ?: 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><i class="fas fa-flag me-1"></i>Status:</strong>
                                    </td>
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
                                </tr>
                                <tr>
                                    <td>
                                        <strong><i class="fas fa-calendar me-1"></i>Submitted:</strong>
                                    </td>
                                    <td>{{ $recovery->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                @if($recovery->processed_at)
                                <tr>
                                    <td>
                                        <strong><i class="fas fa-clock me-1"></i>Processed:</strong>
                                    </td>
                                    <td>{{ $recovery->processed_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong><i class="fas fa-user-shield me-1"></i>Processed by:</strong>
                                    </td>
                                    <td>{{ $recovery->processedBy ? $recovery->processedBy->username : 'Unknown' }}</td>
                                </tr>
                                @endif
                                @if($recovery->admin_notes)
                                <tr>
                                    <td>
                                        <strong><i class="fas fa-sticky-note me-1"></i>Admin Notes:</strong>
                                    </td>
                                    <td>{{ $recovery->admin_notes }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>

                        @if($recovery->status == 'pending')
                        <div class="col-md-4">
                            <h6 class="mb-3">
                                <i class="fas fa-cogs me-2"></i>Actions
                            </h6>

                            <!-- Approve Form -->
                            <form action="{{ route('admin.recovery_accounts.approve', $recovery->id) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-dollar-sign me-1"></i>Adjust Deposit Amount
                                    </label>
                                    <input type="number" name="deposit" class="form-control" step="0.01" min="0" value="{{ $recovery->deposit }}" placeholder="Enter deposit amount">
                                    <small class="text-muted">Original: ${{ number_format($recovery->deposit, 2) }}</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-chart-line me-1"></i>Adjust Profit Amount
                                    </label>
                                    <input type="number" name="profit" class="form-control" step="0.01" min="0" value="{{ $recovery->profit }}" placeholder="Enter profit amount">
                                    <small class="text-muted">Original: ${{ number_format($recovery->profit, 2) }}</small>
                                </div>
                                @if($recovery->referral_username)
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="replace_upline" id="replace_upline" value="1">
                                        <label class="form-check-label" for="replace_upline">
                                            <i class="fas fa-exchange-alt me-1"></i>Replace upline with referral user
                                        </label>
                                    </div>
                                    <small class="text-muted">This will update the user's upline_id to match the referral username: <strong>{{ $recovery->referral_username }}</strong></small>
                                </div>
                                @endif
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-comment me-1"></i>Admin Notes (Optional)
                                    </label>
                                    <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add notes for approval..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to approve this recovery request with the specified amounts?')">
                                    <i class="fas fa-check me-1"></i> Approve
                                </button>
                            </form>

                            <!-- Reject Form -->
                            <form action="{{ route('admin.recovery_accounts.reject', $recovery->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Rejection Reason (Required)
                                    </label>
                                    <textarea name="admin_notes" class="form-control" rows="3" placeholder="Provide reason for rejection..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to reject this recovery request?')">
                                    <i class="fas fa-times me-1"></i> Reject
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
