@extends('admin.layouts.layoutMaster')

@section('title', 'NOWPayments Payment Details')

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-4">NOWPayments Payment Details</h4>
    
    @if(isset($apiError) && $apiError)
        <div class="alert alert-info">
            <strong>API Info:</strong> {{ $apiError }}
        </div>
    @endif
    
    <!-- All Local Deposits Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list"></i> All Deposit Records
                @if(isset($localPayments))
                    <span class="badge badge-info ml-2">{{ $localPayments->count() }} total</span>
                @endif
            </h5>
            <small class="text-muted">Complete list of all deposit requests (including pending ones)</small>
        </div>
        <div class="card-body">
            @if(isset($localPayments) && $localPayments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Payment ID</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($localPayments as $deposit)
                                <tr class="{{ $deposit->payment_id ? '' : 'table-warning' }}">
                                    <td><strong>{{ $deposit->id }}</strong></td>
                                    <td>
                                        @if($deposit->user)
                                            <strong>{{ $deposit->user->firstname }} {{ $deposit->user->lastname }}</strong>
                                            <br><small class="text-muted">{{ $deposit->user->email }}</small>
                                        @else
                                            <span class="text-muted">Unknown User</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-success">${{ number_format($deposit->amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($deposit->payment_id)
                                            <code class="bg-light p-1">{{ $deposit->payment_id }}</code>
                                        @else
                                            <span class="badge badge-warning">Pending NOWPayments</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $deposit->status === 'completed' ? 'success' : 
                                            ($deposit->status === 'pending' ? 'warning' : 'secondary') 
                                        }}">
                                            {{ ucfirst($deposit->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $deposit->created_at->format('M d, Y H:i') }}</small>
                                        <br><small class="text-muted">{{ $deposit->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        @if($deposit->payment_id)
                                            <button class="btn btn-sm btn-outline-primary" onclick="refreshPaymentStatus('{{ $deposit->payment_id }}', this)">
                                                <i class="fas fa-sync-alt"></i> Check
                                            </button>
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-clock"></i> Awaiting payment
                                            </span>
                                        @endif
                                        
                                        @if($deposit->status === 'pending')
                                            <button class="btn btn-sm btn-success ml-2" onclick="approvePayment({{ $deposit->id }}, this)">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>No deposits found!</strong> This could mean:
                    <ul class="mb-0 mt-2">
                        <li>No deposit requests have been made yet</li>
                        <li>There might be an issue with the database query</li>
                        <li>Check if deposits are being saved to the correct table</li>
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <!-- Enriched Local Payments Section -->
    @if(isset($enrichedPayments) && count($enrichedPayments) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">NOWPayments Transaction History</h5>
                <small class="text-muted">Payments processed through NOWPayments with live status updates</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Local ID</th>
                                <th>User</th>
                                <th>Payment ID</th>
                                <th>Order ID</th>
                                <th>Amount (USD)</th>
                                <th>Crypto Amount</th>
                                <th>Local Status</th>
                                <th>NOWPayments Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enrichedPayments as $payment)
                                <tr>
                                    <td>{{ $payment['local_deposit']->id }}</td>
                                    <td>
                                        @if($payment['user'])
                                            {{ $payment['user']->firstname }} {{ $payment['user']->lastname }}
                                            <br><small class="text-muted">{{ $payment['user']->email }}</small>
                                        @else
                                            <span class="text-muted">Unknown User</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code>{{ $payment['payment_id'] }}</code>
                                    </td>
                                    <td>
                                        @if($payment['order_id'])
                                            <code>{{ $payment['order_id'] }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>${{ number_format($payment['amount'], 2) }}</td>
                                    <td>
                                        @if($payment['nowpayments_data'] && isset($payment['nowpayments_data']['pay_amount']))
                                            {{ $payment['nowpayments_data']['pay_amount'] }} 
                                            {{ strtoupper($payment['nowpayments_data']['pay_currency'] ?? '') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $payment['local_deposit']->status == 'completed' ? 'success' : ($payment['local_deposit']->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($payment['local_deposit']->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($payment['payment_status'] == 'api_error')
                                            <span class="badge bg-secondary">API Error</span>
                                        @else
                                            <span class="badge bg-{{ $payment['payment_status'] == 'finished' || $payment['payment_status'] == 'confirmed' ? 'success' : ($payment['payment_status'] == 'waiting' || $payment['payment_status'] == 'confirming' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($payment['payment_status']) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $payment['created_at']->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="refreshPaymentStatus('{{ $payment['payment_id'] }}', this)">
                                            <i class="fas fa-sync-alt"></i> Refresh
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Raw Local Payments Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Local Deposits with NOWPayments Integration</h5>
        </div>
        <div class="card-body">
            @if(isset($localPayments) && $localPayments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Deposit ID</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment ID</th>
                                <th>Payment Method</th>
                                <th>Reference</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($localPayments as $deposit)
                                <tr>
                                    <td>{{ $deposit->id }}</td>
                                    <td>{{ $deposit->user ? $deposit->user->firstname . ' ' . $deposit->user->lastname . ' (' . $deposit->user->email . ')' : 'N/A' }}</td>
                                    <td>${{ number_format($deposit->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $deposit->status == 'completed' ? 'success' : ($deposit->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($deposit->status) }}
                                        </span>
                                    </td>
                                    <td><code>{{ $deposit->payment_id }}</code></td>
                                    <td>{{ ucfirst($deposit->payment_method) }}</td>
                                    <td>{{ $deposit->reference }}</td>
                                    <td>{{ $deposit->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <h6>No NOWPayments transactions found</h6>
                    <p>This could mean:</p>
                    <ul>
                        <li>No payments have been processed through NOWPayments yet</li>
                        <li>Payments were processed but not stored with payment_id</li>
                        <li>You're viewing test/sandbox data</li>
                    </ul>
                    <p><strong>To create test payments:</strong> Use the user deposit page to initiate a NOWPayments transaction.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function refreshPaymentStatus(paymentId, button) {
    // Add loading state
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    button.disabled = true;
    
    // Make AJAX request to refresh payment status
    fetch(`/admin/nowpayments/refresh-status/${paymentId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to show updated status
            window.location.reload();
        } else {
            alert('Failed to refresh payment status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error refreshing payment status');
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalHtml;
        button.disabled = false;
    });
}

function approvePayment(depositId, button) {
    if (!confirm('Are you sure you want to approve this payment? This will mark it as completed and update the user\'s wallet balance.')) {
        return;
    }
    
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Approving...';
    button.disabled = true;

    fetch(`/admin/deposits/approve/${depositId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Payment approved successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error approving payment');
    })
    .finally(() => {
        button.innerHTML = originalHtml;
        button.disabled = false;
    });
}
</script>
@endsection
