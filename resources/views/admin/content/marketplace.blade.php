@extends('admin/layouts/layoutMaster')

@section('title', 'Marketplace Transactions')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endsection

@section('content')
    <!-- Add CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-6">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ti tabler-shopping-cart me-2"></i>
                            Marketplace Transactions Overview
                        </h5>
                        <div class="d-flex gap-2">
                            <span class="badge bg-info">{{ $totalTransactions }} Total</span>
                            <span class="badge bg-success">{{ $completedTransactions }} Completed</span>
                            <span class="badge bg-warning">{{ $pendingTransactions }} Pending</span>
                            <span class="badge bg-danger">{{ $cancelledTransactions }} Cancelled</span>
                            <span class="badge bg-dark">{{ $rejectedTransactions }} Rejected</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-6">
            <div class="col-lg-3 col-md-6 col-12 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="content-left">
                                <span class="text-heading">Total Volume</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">${{ number_format($totalVolume, 2) }}</h4>
                                </div>
                                <small class="mb-0">Completed transactions</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ti tabler-currency-dollar ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="content-left">
                                <span class="text-heading">Total Fees</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">${{ number_format($totalFees, 2) }}</h4>
                                </div>
                                <small class="mb-0">1.8% transaction fee</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ti tabler-percentage ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="content-left">
                                <span class="text-heading">Success Rate</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">
                                        {{ $totalTransactions > 0 ? number_format(($completedTransactions / $totalTransactions) * 100, 1) : 0 }}%
                                    </h4>
                                </div>
                                <small class="mb-0">Completion rate</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="ti tabler-trending-up ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="content-left">
                                <span class="text-heading">Avg Transaction</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">
                                        ${{ $completedTransactions > 0 ? number_format($totalVolume / $completedTransactions, 2) : '0.00' }}
                                    </h4>
                                </div>
                                <small class="mb-0">Average amount</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ti tabler-chart-line ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.marketplace.index') }}">
                            <div class="row g-4">
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" name="status" id="status">
                                        <option value="">All Statuses</option>
                                        <option value="pending_verification"
                                            {{ request('status') == 'pending_verification' ? 'selected' : '' }}>Pending
                                            Verification</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                            Completed</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                            Cancelled</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                            Rejected</option>
                                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>
                                            Expired</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="date_from" class="form-label">Date From</label>
                                    <input type="date" class="form-control" name="date_from" id="date_from"
                                        value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_to" class="form-label">Date To</label>
                                    <input type="date" class="form-control" name="date_to" id="date_to"
                                        value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="search" class="form-label">Search User</label>
                                    <input type="text" class="form-control" name="search" id="search"
                                        placeholder="Username or name" value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="ti tabler-search me-1"></i>Filter
                                    </button>
                                    <a href="{{ route('admin.marketplace.index') }}" class="btn btn-outline-secondary">
                                        <i class="ti tabler-refresh me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Marketplace Transactions</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                <i class="ti tabler-printer me-1"></i>Print
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="exportToCSV()">
                                <i class="ti tabler-download me-1"></i>Export CSV
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Buyer</th>
                                        <th>Seller</th>
                                        <th>Status</th>
                                        <th>USDT Amount</th>
                                        <th>Total Price</th>
                                        <th>Tax Amount</th>
                                        <th>Net Amount</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $transaction)
                                        <tr>
                                            <td>
                                                <span class="text-primary fw-medium">{{ $transaction->unique_id }}</span>
                                            </td>
                                            <td>
                                                @if ($transaction->buyer)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-xs me-2">
                                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                                {{ strtoupper(substr($transaction->buyer->username, 0, 2)) }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <span
                                                                class="fw-medium">{{ $transaction->buyer->username }}</span>
                                                            <br><small
                                                                class="text-muted">{{ $transaction->buyer->firstname }}
                                                                {{ $transaction->buyer->lastname }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">No buyer</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-xs me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-info">
                                                            {{ strtoupper(substr($transaction->seller->username, 0, 2)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="fw-medium">{{ $transaction->seller->username }}</span>
                                                        <br><small
                                                            class="text-muted">{{ $transaction->seller->firstname }}
                                                            {{ $transaction->seller->lastname }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @switch($transaction->status)
                                                    @case('completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @break

                                                    @case('pending_verification')
                                                        <span class="badge bg-warning">Pending</span>
                                                    @break

                                                    @case('cancelled')
                                                        <span class="badge bg-danger">Cancelled</span>
                                                    @break

                                                    @case('rejected')
                                                        <span class="badge bg-dark">Rejected</span>
                                                    @break

                                                    @case('expired')
                                                        <span class="badge bg-secondary">Expired</span>
                                                    @break

                                                    @default
                                                        <span
                                                            class="badge bg-light text-dark">{{ ucfirst($transaction->status) }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <span class="fw-medium">{{ number_format($transaction->amount, 2) }}
                                                    USDT</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="fw-medium">${{ number_format($transaction->total_price, 2) }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $taxAmount = $transaction->total_price * 0.018; // 1.8% tax
                                                @endphp
                                                <span
                                                    class="text-warning fw-medium">${{ number_format($taxAmount, 2) }}</span>
                                                <br><small class="text-muted">1.8%</small>
                                            </td>
                                            <td>
                                                @php
                                                    $netAmount = $transaction->total_price - $taxAmount;
                                                @endphp
                                                <span
                                                    class="text-success fw-medium">${{ number_format($netAmount, 2) }}</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="text-muted">{{ $transaction->created_at->format('M d, Y') }}</span>
                                                <br><small>{{ $transaction->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <span
                                                    class="text-muted">{{ $transaction->updated_at->format('M d, Y') }}</span>
                                                <br><small>{{ $transaction->updated_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.marketplace.show', $transaction->unique_id) }}">
                                                                <i class="ti tabler-eye me-1"></i>View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#"
                                                                onclick="copyTransactionId('{{ $transaction->unique_id }}')">
                                                                <i class="ti tabler-copy me-1"></i>Copy ID
                                                            </a>
                                                        </li>
                                                        @if ($transaction->status === 'pending_verification')
                                                            <li>
                                                                <hr class="dropdown-divider">
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item text-danger" href="#"
                                                                    onclick="rejectTransaction('{{ $transaction->unique_id }}')">
                                                                    <i class="ti tabler-x me-1"></i>Reject with Reason
                                                                </a>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center py-4">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <i class="ti tabler-shopping-cart-off ti-48px text-muted mb-2"></i>
                                                        <span class="text-muted">No transactions found</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($transactions->hasPages())
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div>
                                        <span class="text-muted">
                                            Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of
                                            {{ $transactions->total() }} results
                                        </span>
                                    </div>
                                    <div>
                                        {{ $transactions->links() }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Transaction Modal -->
        <div class="modal fade" id="rejectTransactionModal" tabindex="-1" aria-labelledby="rejectTransactionModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectTransactionModalLabel">Reject Transaction</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="rejectTransactionForm">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="ti tabler-alert-triangle me-2"></i>
                                <strong>Warning:</strong> This action will permanently reject the transaction and cannot be
                                undone.
                            </div>
                            <div class="mb-3">
                                <label for="rejectReason" class="form-label">Rejection Reason <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="rejectReason" name="reason" rows="4"
                                    placeholder="Please provide a detailed reason for rejecting this transaction..." required></textarea>
                                <div class="form-text">This reason will be visible to both the buyer and seller.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="ti tabler-x me-1"></i>Reject Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            let currentTransactionId = null;

            function rejectTransaction(transactionId) {
                currentTransactionId = transactionId;
                const modal = new bootstrap.Modal(document.getElementById('rejectTransactionModal'));
                modal.show();
            }

            document.getElementById('rejectTransactionForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const reason = document.getElementById('rejectReason').value;
                const submitBtn = this.querySelector('button[type="submit"]');

                if (!reason.trim()) {
                    alert('Please provide a rejection reason.');
                    return;
                }

                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Rejecting...';

                fetch(`/admin/marketplace/${currentTransactionId}/reject`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            reason: reason
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Close modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'rejectTransactionModal'));
                            modal.hide();

                            // Show success message
                            const toast = document.createElement('div');
                            toast.className = 'toast position-fixed top-0 end-0 m-3';
                            toast.innerHTML = `
                            <div class="toast-header bg-success text-white">
                                <strong class="me-auto">Success</strong>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                            </div>
                            <div class="toast-body">Transaction rejected successfully</div>
                        `;
                            document.body.appendChild(toast);
                            const bsToast = new bootstrap.Toast(toast);
                            bsToast.show();

                            // Reload page after 2 seconds
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            alert('Error: ' + (data.message || 'Failed to reject transaction'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while rejecting the transaction: ' + error.message);
                    })
                    .finally(() => {
                        // Re-enable submit button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="ti tabler-x me-1"></i>Reject Transaction';
                    });
            });

            // Reset form when modal is hidden
            document.getElementById('rejectTransactionModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('rejectTransactionForm').reset();
                currentTransactionId = null;
            });

            function copyTransactionId(id) {
                navigator.clipboard.writeText(id).then(function() {
                    // Show success toast
                    const toast = document.createElement('div');
                    toast.className = 'toast position-fixed top-0 end-0 m-3';
                    toast.innerHTML = `
      <div class="toast-header">
        <strong class="me-auto">Success</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
      </div>
      <div class="toast-body">Transaction ID copied to clipboard</div>
    `;
                    document.body.appendChild(toast);
                    const bsToast = new bootstrap.Toast(toast);
                    bsToast.show();

                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 3000);
                });
            }

            function exportToCSV() {
                const table = document.querySelector('table');
                const rows = Array.from(table.querySelectorAll('tr'));

                const csvContent = rows.map(row => {
                    const cells = Array.from(row.querySelectorAll('td, th'));
                    return cells.map(cell => {
                        const text = cell.textContent.trim().replace(/\s+/g, ' ');
                        return `"${text}"`;
                    }).join(',');
                }).join('\n');

                const blob = new Blob([csvContent], {
                    type: 'text/csv'
                });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `marketplace-transactions-${new Date().toISOString().split('T')[0]}.csv`;
                a.click();
                window.URL.revokeObjectURL(url);
            }
        </script>
    @endsection
