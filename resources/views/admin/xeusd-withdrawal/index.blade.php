@extends('admin/layouts/layoutMaster')

@section('title', 'XEUSD Withdrawal Management')

@section('vendor-style')
    <!-- Add Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Ensure jQuery is available globally
        if (typeof window.$ === 'undefined' && typeof jQuery !== 'undefined') {
            window.$ = jQuery;
        }
    </script>
@endsection

@section('page-script')
    <script>
        // Set the URL for the JavaScript file to use
        window.xeusdWithdrawalsIndexUrl = '{{ route('admin.xeusd_withdrawals.index') }}';
    </script>
    @vite('resources/assets/js/app-xeusd-withdrawal-list.js')
@endsection

@section('content')
    <!-- Statistics Cards -->
    <div class="row g-6 mb-6">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Total XEUSD Withdrawals</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format($totalWithdrawals) }}</h4>
                            </div>
                            <small class="text-muted">All time requests</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="fas fa-coins text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Pending Requests</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format($pendingWithdrawals) }}</h4>
                            </div>
                            <small class="text-muted">
                                Awaiting approval<br>
                                <strong class="text-warning">${{ number_format($pendingAmount, 2) }}</strong> USD
                            </small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="fas fa-clock text-warning"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Processing</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format($processingWithdrawals) }}</h4>
                            </div>
                            <small class="text-muted">Being processed</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="fas fa-cog text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Completed</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format($completedWithdrawals) }}</h4>
                            </div>
                            <small class="text-muted">
                                Successfully completed<br>
                                <strong class="text-success">${{ number_format($completedAmount, 2) }}</strong> USD
                            </small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="fas fa-check-circle text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Owner Wallet Balance Card -->
    <div class="row g-6 mb-6">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Owner Wallet Balance</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format($ownerBalance, 2) }} XEUSD</h4>
                                @if ($ownerBalance > 1000)
                                    <span class="badge bg-success">Sufficient</span>
                                @elseif($ownerBalance > 100)
                                    <span class="badge bg-warning">Low</span>
                                @else
                                    <span class="badge bg-danger">Critical</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                Available for withdrawals • Contract: {{ config('web3.xeusd.contract_address') }}
                                @if ($ownerBalance == 0)
                                    <br><strong class="text-warning">⚠️ Configure wallet credentials in .env to show real
                                        balance</strong>
                                @endif
                            </small>
                        </div>
                        <div class="avatar">
                            <span
                                class="avatar-initial rounded bg-label-{{ $ownerBalance > 1000 ? 'success' : ($ownerBalance > 100 ? 'warning' : 'danger') }}">
                                <i
                                    class="fas fa-wallet text-{{ $ownerBalance > 1000 ? 'success' : ($ownerBalance > 100 ? 'warning' : 'danger') }}"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">
                <i class="fas fa-coins me-2"></i>XEUSD Withdrawal Management
            </h5>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Manage BSC BEP20 XEUSD token withdrawals</small>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table id="xeusdWithdrawalsTable" class="datatables-xeusd-withdrawal table border-top">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Info</th>
                        <th>Amount</th>
                        <th>Wallet Address</th>
                        <th>Status</th>
                        <th>Transaction Hash</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">
                        <i class="fas fa-coins me-2"></i>XEUSD Withdrawal Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
