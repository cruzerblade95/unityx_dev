@php
    $configData = Helper::appClasses();
@endphp

@extends('admin.layouts.layoutMaster')

@section('title', 'Wallet Profit Checker')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header with Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-wallet text-primary me-2"></i>
                        Wallet Profit Checker
                    </h4>
                    <p class="text-muted mb-0">Manage user profit wallet transfers and threshold monitoring</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-info" onclick="window.location.href='{{ route('admin.wallet-profit-checker.over-threshold') }}'">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Over Threshold Users
                        @if($stats['users_over_threshold'] > 0)
                            <span class="badge bg-danger ms-1">{{ $stats['users_over_threshold'] }}</span>
                        @endif
                    </button>
                    <button class="btn btn-outline-success" onclick="refreshStats()">
                        <i class="fas fa-sync-alt me-1"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Transfers
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalTransfers">
                                {{ number_format($stats['total_transfers']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Completed
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="completedTransfers">
                                {{ number_format($stats['completed_transfers']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Users w/ Temp
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="usersWithTemp">
                                {{ number_format($stats['users_with_temp']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Temp Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalTempAmount">
                                ${{ number_format($stats['total_temp_amount'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-piggy-bank fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Over Threshold
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="overThreshold">
                                {{ number_format($stats['users_over_threshold']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-secondary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Threshold %
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="currentThreshold">
                                {{ $stats['current_threshold'] }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Transfer Operations
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="updateThreshold()">
                            <i class="fas fa-edit me-1"></i>
                            Update Threshold
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <button class="btn btn-warning w-100" onclick="transferSelectedToTemp()" id="transferToTempBtn" disabled>
                                <i class="fas fa-arrow-right me-2"></i>
                                Transfer Selected to Temp
                                <br><small class="text-muted">Move selected profit → temporary</small>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <button class="btn btn-success w-100" onclick="transferAllProfitToDeposit()" id="transferProfitToDepositBtn">
                                <i class="fas fa-coins me-2"></i>
                                Transfer All Profit to Deposit
                                <br><small class="text-muted">All profit → deposit (direct)</small>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <button class="btn btn-primary w-100" onclick="transferAllTempToDeposit()" id="transferTempToDepositBtn">
                                <i class="fas fa-arrow-down me-2"></i>
                                Transfer Temp to Deposit
                                <br><small class="text-muted">All temporary → deposit</small>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <button class="btn btn-info w-100" onclick="returnTempToProfit()" id="returnToProfitBtn">
                                <i class="fas fa-undo me-2"></i>
                                Return Temp to Profit
                                <br><small class="text-muted">Temporary → profit wallet</small>
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <button class="btn btn-danger w-100" onclick="showRollbackModal()">
                                <i class="fas fa-history me-2"></i>
                                Rollback Transfer Batch
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-dark w-100" onclick="rejectAllPendingWithdrawals()" id="rejectAllWithdrawalsBtn">
                                <i class="fas fa-ban me-2"></i>
                                Reject All Pending Withdrawals
                                <br><small class="text-muted">Amount → deposit wallet</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Previous Batch Transfers -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Previous Batch Transfers
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshBatchHistory()">
                            <i class="fas fa-sync me-1"></i>
                            Refresh
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="showDetailedHistory()">
                            <i class="fas fa-history me-1"></i>
                            Detailed History
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="batchHistoryTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch ID</th>
                                    <th>Transfer Type</th>
                                    <th>Transfer Count</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Processed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        User Wallets
                    </h5>
                    <div class="d-flex gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAllUsers">
                            <label class="form-check-label" for="selectAllUsers">
                                Select All
                            </label>
                        </div>
                        <span class="badge bg-primary" id="selectedCount">0 selected</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>User</th>
                                    <th>Deposit Wallet</th>
                                    <th>Profit Wallet</th>
                                    <th>Temporary Wallet</th>
                                    <th>Total Balance</th>
                                    <th>Profit Ratio</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfer History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-history me-2"></i>
                        Transfer History
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="historyTable">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Transfer Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Batch ID</th>
                                    <th>Processed By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rollback Modal -->
    <div class="modal fade" id="rollbackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-history me-2"></i>
                        Rollback Transfer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This will reverse all transfers in the selected batch.
                    </div>
                    <div class="mb-3">
                        <label for="batchIdInput" class="form-label">Batch ID</label>
                        <input type="text" class="form-control" id="batchIdInput" placeholder="Enter batch ID to rollback">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="executeRollback()">
                        <i class="fas fa-history me-1"></i>
                        Execute Rollback
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Threshold Update Modal -->
    <div class="modal fade" id="thresholdModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-percentage me-2"></i>
                        Update Profit Threshold
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="thresholdInput" class="form-label">Threshold Percentage</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="thresholdInput" value="{{ $stats['current_threshold'] }}" min="0" max="1000" step="0.1">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">
                            Users with profit wallet exceeding this percentage of their deposit will be flagged.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="executeThresholdUpdate()">
                        <i class="fas fa-save me-1"></i>
                        Update Threshold
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let usersTable;
let selectedUsers = new Set();

document.addEventListener('DOMContentLoaded', function() {
    initializeUsersTable();
    initializeBatchHistoryTable();
    setupEventListeners();
});

function initializeUsersTable() {
    usersTable = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.wallet-profit-checker.users-data") }}',
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error);
                Swal.fire('Error!', 'Failed to load user data', 'error');
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `<input type="checkbox" class="user-select" value="${row.id}">`;
                }
            },
            {
                data: 'username',
                name: 'username',
                render: function(data, type, row) {
                    return `
                        <div>
                            <strong>${row.username}</strong>
                            <br><small class="text-muted">${row.email}</small>
                        </div>
                    `;
                }
            },
            {
                data: 'wallet_info',
                orderable: false,
                render: function(data) {
                    return `$${data.deposit}`;
                }
            },
            {
                data: 'wallet_info',
                orderable: false,
                render: function(data) {
                    return `$${data.profit}`;
                }
            },
            {
                data: 'temp_status',
                orderable: false,
                render: function(data) {
                    const badgeClass = data.has_temp ? 'bg-warning' : 'bg-secondary';
                    return `<span class="badge ${badgeClass}">$${data.amount}</span>`;
                }
            },
            {
                data: 'wallet_info',
                orderable: false,
                render: function(data) {
                    return `<strong>$${data.total}</strong>`;
                }
            },
            {
                data: 'profit_ratio',
                orderable: false,
                render: function(data) {
                    const badge = data.is_over_threshold ?
                        `<span class="badge bg-danger ms-1">Over Threshold</span>` : '';
                    return `
                        <span class="${data.color_class}">${data.percentage}%</span>
                        ${badge}
                    `;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-outline-info" onclick="viewUserDetails(${row.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[1, 'asc']],
        pageLength: 25,
        responsive: true
    });
}

function initializeBatchHistoryTable() {
    $('#batchHistoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.wallet-profit-checker.batch-summary-data") }}',
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('Batch History DataTables error:', error);
                Swal.fire('Error!', 'Failed to load batch history', 'error');
            }
        },
        columns: [
            {
                data: 'batch_info',
                render: function(data) {
                    return `
                        <span class="font-monospace text-primary"
                              title="${data.full_batch}"
                              onclick="copyBatchId('${data.full_batch}')"
                              style="cursor: pointer;">
                            ${data.short_batch}
                        </span>
                    `;
                }
            },
            {
                data: 'transfer_details',
                render: function(data) {
                    return `<span class="badge bg-${data.type_color}">${data.type}</span>`;
                }
            },
            {
                data: 'transfer_details',
                render: function(data) {
                    return `<strong>${data.count}</strong> transfers`;
                }
            },
            {
                data: 'transfer_details',
                render: function(data) {
                    return `<strong>$${data.total_amount}</strong>`;
                }
            },
            {
                data: 'transfer_details',
                render: function(data) {
                    return `<span class="badge bg-${data.status_color}">${data.status}</span>`;
                }
            },
            {
                data: 'timing',
                render: function(data) {
                    return data.created;
                }
            },
            {
                data: 'timing',
                render: function(data) {
                    return data.processed;
                }
            },
            {
                data: 'actions',
                orderable: false,
                render: function(data, type, row) {
                    let buttons = `
                        <button class="btn btn-sm btn-outline-primary me-1"
                                onclick="copyBatchId('${data.batch_id}')"
                                title="Copy Batch ID">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info me-1"
                                onclick="viewBatchDetails('${data.batch_id}')"
                                title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                    `;

                    if (data.can_rollback) {
                        buttons += `
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="rollbackBatch('${data.batch_id}')"
                                    title="Rollback Batch">
                                <i class="fas fa-undo"></i>
                            </button>
                        `;
                    }

                    return buttons;
                }
            }
        ],
        order: [[5, 'desc']], // Order by created date descending
        pageLength: 10,
        responsive: true,
        dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip'
    });
}

function setupEventListeners() {
    // Select all checkbox
    $('#selectAll').on('change', function() {
        const isChecked = this.checked;
        $('.user-select').prop('checked', isChecked);

        if (isChecked) {
            $('.user-select').each(function() {
                selectedUsers.add($(this).val());
            });
        } else {
            selectedUsers.clear();
        }
        updateSelectedCount();
    });

    // Individual checkboxes
    $(document).on('change', '.user-select', function() {
        const userId = $(this).val();
        if (this.checked) {
            selectedUsers.add(userId);
        } else {
            selectedUsers.delete(userId);
        }
        updateSelectedCount();
    });
}

function updateSelectedCount() {
    const count = selectedUsers.size;
    $('#selectedCount').text(`${count} selected`);
    $('#transferToTempBtn').prop('disabled', count === 0);
}

function transferSelectedToTemp() {
    if (selectedUsers.size === 0) {
        Swal.fire('Warning!', 'Please select users first', 'warning');
        return;
    }

    const userIds = Array.from(selectedUsers);

    Swal.fire({
        title: 'Confirm Transfer',
        text: `Transfer profit to temporary wallet for ${userIds.length} selected users?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        confirmButtonText: 'Yes, Transfer!'
    }).then((result) => {
        if (result.isConfirmed) {
            executeTransfer('profit-to-temp', { user_ids: userIds });
        }
    });
}

function transferAllTempToDeposit() {
    Swal.fire({
        title: 'Confirm Transfer',
        text: 'Transfer all temporary balances to deposit wallets?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, Transfer!'
    }).then((result) => {
        if (result.isConfirmed) {
            executeTransfer('temp-to-deposit');
        }
    });
}

function transferAllProfitToDeposit() {
    Swal.fire({
        title: 'Confirm Direct Transfer',
        html: `
            <div class="text-start">
                <p>This will transfer <strong>ALL profit balances</strong> directly to deposit wallets.</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> This bypasses temporary wallets entirely.
                    <br>Profit → Deposit (Direct)
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, Transfer All Profit!'
    }).then((result) => {
        if (result.isConfirmed) {
            executeTransfer('profit-to-deposit');
        }
    });
}

function returnTempToProfit() {
    Swal.fire({
        title: 'Confirm Return',
        text: 'Return all temporary balances back to profit wallets?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        confirmButtonText: 'Yes, Return!'
    }).then((result) => {
        if (result.isConfirmed) {
            executeTransfer('temp-to-profit');
        }
    });
}

function rejectAllPendingWithdrawals() {
    Swal.fire({
        title: 'Reject All Pending Withdrawals',
        html: `
            <div class="text-start">
                <p>This will reject <strong>ALL pending withdrawals</strong> and move amounts to deposit wallets.</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong>
                    <ul class="mb-0 mt-2">
                        <li>All pending withdrawals will be marked as rejected</li>
                        <li>Rejection reason: "Rescheduling Withdrawal"</li>
                        <li>Amounts will be moved from floating to deposit wallets</li>
                        <li>This action supports rollback if needed</li>
                    </ul>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, Reject All Pending Withdrawals!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            executeRejectAllPendingWithdrawals();
        }
    });
}

function executeRejectAllPendingWithdrawals() {
    const button = document.getElementById('rejectAllWithdrawalsBtn');
    const originalText = button.innerHTML;

    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

    $.ajax({
        url: '{{ route("admin.wallet-profit-checker.reject-all-pending-withdrawals") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                let message = response.message;
                if (response.errors && response.errors.length > 0) {
                    message += '\\n\\nErrors:\\n' + response.errors.join('\\n');
                }

                Swal.fire({
                    title: 'Success!',
                    html: message.replace(/\\n/g, '<br>'),
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Refresh page data
                    refreshStats();
                    if (typeof usersTable !== 'undefined') {
                        usersTable.ajax.reload();
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Error rejecting pending withdrawals';
            Swal.fire({
                title: 'Error!',
                text: message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        },
        complete: function() {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    });
}

function executeTransfer(type, data = {}) {
    const endpoints = {
        'profit-to-temp': '{{ route("admin.wallet-profit-checker.transfer-profit-to-temp") }}',
        'temp-to-deposit': '{{ route("admin.wallet-profit-checker.transfer-temp-to-deposit") }}',
        'profit-to-deposit': '{{ route("admin.wallet-profit-checker.transfer-all-profit-to-deposit") }}',
        'temp-to-profit': '{{ route("admin.wallet-profit-checker.return-temp-to-profit") }}'
    };

    $.ajax({
        url: endpoints[type],
        type: 'POST',
        data: {
            ...data,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                usersTable.ajax.reload();
                selectedUsers.clear();
                updateSelectedCount();
                refreshStats();
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Transfer failed';
            Swal.fire('Error!', message, 'error');
        }
    });
}

function showRollbackModal() {
    $('#rollbackModal').modal('show');
}

function executeRollback() {
    const batchId = $('#batchIdInput').val().trim();
    if (!batchId) {
        Swal.fire('Warning!', 'Please enter a batch ID', 'warning');
        return;
    }

    $.ajax({
        url: '{{ route("admin.wallet-profit-checker.rollback") }}',
        type: 'POST',
        data: {
            batch_id: batchId,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                $('#rollbackModal').modal('hide');
                usersTable.ajax.reload();
                refreshStats();
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Rollback failed';
            Swal.fire('Error!', message, 'error');
        }
    });
}

function updateThreshold() {
    $('#thresholdModal').modal('show');
}

function executeThresholdUpdate() {
    const threshold = $('#thresholdInput').val();
    if (!threshold || threshold < 0) {
        Swal.fire('Warning!', 'Please enter a valid threshold', 'warning');
        return;
    }

    $.ajax({
        url: '{{ route("admin.wallet-profit-checker.update-threshold") }}',
        type: 'POST',
        data: {
            threshold: threshold,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                $('#thresholdModal').modal('hide');
                refreshStats();
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Update failed';
            Swal.fire('Error!', message, 'error');
        }
    });
}

function viewUserDetails(userId) {
    // Implementation for viewing user details
    window.open(`/admin/users/${userId}`, '_blank');
}

// Batch History Functions
function refreshBatchHistory() {
    $('#batchHistoryTable').DataTable().ajax.reload();
    Swal.fire({
        title: 'Refreshed!',
        text: 'Batch history has been updated',
        icon: 'success',
        timer: 1500,
        showConfirmButton: false
    });
}

function showDetailedHistory() {
    $('#historyModal').modal('show');
    if ($.fn.DataTable.isDataTable('#historyTable')) {
        $('#historyTable').DataTable().ajax.reload();
    } else {
        initializeDetailedHistoryTable();
    }
}

function initializeDetailedHistoryTable() {
    $('#historyTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.wallet-profit-checker.transfer-history-data") }}',
            type: 'GET'
        },
        columns: [
            {
                data: 'user_info',
                render: function(data) {
                    return `
                        <div>
                            <strong>${data.username}</strong>
                            <br><small class="text-muted">${data.email}</small>
                        </div>
                    `;
                }
            },
            {
                data: 'transfer_details',
                render: function(data) {
                    return `<span class="badge bg-${data.status_color}">${data.type}</span>`;
                }
            },
            {
                data: 'transfer_details',
                render: function(data) {
                    return `<strong>$${data.amount}</strong>`;
                }
            },
            {
                data: 'transfer_details',
                render: function(data) {
                    return `<span class="badge bg-${data.status_color}">${data.status}</span>`;
                }
            },
            {
                data: 'batch_info',
                render: function(data) {
                    return `<span class="font-monospace text-primary" onclick="copyBatchId('${data.batch_id}')" style="cursor: pointer;" title="Click to copy">${data.short_batch}</span>`;
                }
            },
            {
                data: 'admin_info',
                render: function(data) {
                    return data.username;
                }
            },
            {
                data: 'processed_at_formatted'
            }
        ],
        order: [[6, 'desc']], // Order by date descending
        pageLength: 15,
        responsive: true
    });
}

function copyBatchId(batchId) {
    navigator.clipboard.writeText(batchId).then(function() {
        Swal.fire({
            title: 'Copied!',
            text: 'Batch ID copied to clipboard',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
    }).catch(function() {
        // Fallback for browsers that don't support clipboard API
        const textArea = document.createElement('textarea');
        textArea.value = batchId;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);

        Swal.fire({
            title: 'Copied!',
            text: 'Batch ID copied to clipboard',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
    });
}

function viewBatchDetails(batchId) {
    Swal.fire({
        title: 'Batch Details',
        html: `
            <div class="text-start">
                <p><strong>Batch ID:</strong></p>
                <p class="font-monospace bg-light p-2 border rounded">${batchId}</p>
                <p>You can use this Batch ID for rollback operations.</p>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Copy Batch ID',
        cancelButtonText: 'Close'
    }).then((result) => {
        if (result.isConfirmed) {
            copyBatchId(batchId);
        }
    });
}

function rollbackBatch(batchId) {
    Swal.fire({
        title: 'Confirm Rollback',
        html: `
            <div class="text-start">
                <p>Are you sure you want to rollback batch:</p>
                <p class="font-monospace bg-light p-2 border rounded text-center">${batchId}</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This action will reverse all transfers in this batch and cannot be undone.
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Rollback',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            executeRollback(batchId);
        }
    });
}

function executeRollback(batchId) {
    $.ajax({
        url: '{{ route("admin.wallet-profit-checker.rollback") }}',
        type: 'POST',
        data: {
            batch_id: batchId,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                $('#batchHistoryTable').DataTable().ajax.reload();
                usersTable.ajax.reload();
                refreshStats();
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Rollback failed';
            Swal.fire('Error!', message, 'error');
        }
    });
}

function refreshStats() {
    window.location.reload();
}
</script>
@endsection
