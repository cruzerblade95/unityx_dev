@php
    $configData = Helper::appClasses();
@endphp

@extends('admin.layouts.layoutMaster')

@section('title', 'Over Threshold Users - Wallet Profit Checker')

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
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.wallet-profit-checker.index') }}">Wallet Profit Checker</a>
                            </li>
                            <li class="breadcrumb-item active">Over Threshold Users</li>
                        </ol>
                    </nav>
                    <h4 class="mb-1">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Over Threshold Users
                    </h4>
                    <p class="text-muted mb-0">
                        Users with profit wallet exceeding {{ $threshold }}% of their deposit balance
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="updateThresholdFromPage()">
                        <i class="fas fa-edit me-1"></i>
                        Update Threshold ({{ $threshold }}%)
                    </button>
                    <button class="btn btn-success" onclick="window.location.href='{{ route('admin.wallet-profit-checker.index') }}'">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Main
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Information:</strong>
                This page shows users whose profit wallet balance exceeds {{ $threshold }}% of their deposit balance.
                You can adjust individual user profit amounts or update the global threshold percentage.
            </div>
        </div>
    </div>

    <!-- Over Threshold Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Users Over Threshold
                    </h5>
                    <div>
                        <span class="badge bg-danger fs-6" id="userCount">Loading...</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="overThresholdTable">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Deposit Wallet</th>
                                    <th>Profit Wallet</th>
                                    <th>Temporary Wallet</th>
                                    <th>Profit Ratio</th>
                                    <th>Excess Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Adjust Profit Modal -->
    <div class="modal fade" id="adjustProfitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Adjust User Profit
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>User:</strong></label>
                        <p id="adjustUserInfo" class="text-muted"></p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Deposit Balance</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control" id="userDepositBalance" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Profit</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control" id="currentProfitBalance" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="newProfitAmount" class="form-label">New Profit Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="newProfitAmount" min="0" step="0.01">
                        </div>
                        <div class="form-text">
                            Recommended maximum: $<span id="recommendedMax">0.00</span> ({{ $threshold }}% of deposit)
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="setRecommendedAmount()">
                                Set to Recommended
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="setZeroAmount()">
                                Set to Zero
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action will directly modify the user's profit wallet balance.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="executeAdjustment()">
                        <i class="fas fa-save me-1"></i>
                        Adjust Profit
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
                            <input type="number" class="form-control" id="thresholdInput" value="{{ $threshold }}" min="0" max="1000" step="0.1">
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
let overThresholdTable;
let currentUserId = null;
let currentUserData = null;
let currentThreshold = {{ $threshold }};

document.addEventListener('DOMContentLoaded', function() {
    initializeOverThresholdTable();
});

function initializeOverThresholdTable() {
    overThresholdTable = $('#overThresholdTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.wallet-profit-checker.over-threshold-data") }}',
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error);
                Swal.fire('Error!', 'Failed to load over threshold users data', 'error');
            },
            dataSrc: function(json) {
                // Update user count badge
                $('#userCount').text(`${json.recordsTotal} users over threshold`);
                return json.data;
            }
        },
        columns: [
            {
                data: null,
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
                render: function(data) {
                    return `<span class="badge bg-info fs-6">$${data.deposit}</span>`;
                }
            },
            {
                data: 'wallet_info',
                render: function(data) {
                    return `<span class="badge bg-warning fs-6">$${data.profit}</span>`;
                }
            },
            {
                data: 'wallet_info',
                render: function(data) {
                    const hasTemp = parseFloat(data.temporary_profit) > 0;
                    const badgeClass = hasTemp ? 'bg-secondary' : 'bg-light text-dark';
                    return `<span class="badge ${badgeClass} fs-6">$${data.temporary_profit}</span>`;
                }
            },
            {
                data: 'profit_ratio',
                render: function(data) {
                    return `<span class="badge bg-danger fs-6">${data}%</span>`;
                }
            },
            {
                data: 'excess_amount',
                render: function(data) {
                    return `<span class="badge bg-danger fs-6">$${data}</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary" onclick="showAdjustModal(${row.id}, '${row.username}', '${row.email}')">
                            <i class="fas fa-edit"></i> Adjust
                        </button>
                    `;
                }
            }
        ],
        order: [[4, 'desc']], // Order by profit ratio descending
        pageLength: 25,
        responsive: true
    });
}

function showAdjustModal(userId, username, email) {
    currentUserId = userId;

    // Get current user data from the table
    const rowData = overThresholdTable.rows().data().toArray().find(row => row.id == userId);
    if (!rowData) {
        Swal.fire('Error!', 'User data not found', 'error');
        return;
    }

    currentUserData = rowData;

    // Populate modal
    $('#adjustUserInfo').html(`<strong>${username}</strong><br><small class="text-muted">${email}</small>`);
    $('#userDepositBalance').val(parseFloat(rowData.wallet_info.deposit.replace(/,/g, '')));
    $('#currentProfitBalance').val(parseFloat(rowData.wallet_info.profit.replace(/,/g, '')));
    $('#newProfitAmount').val(parseFloat(rowData.wallet_info.profit.replace(/,/g, '')));

    const depositAmount = parseFloat(rowData.wallet_info.deposit.replace(/,/g, ''));
    const recommendedMax = (depositAmount * currentThreshold / 100).toFixed(2);
    $('#recommendedMax').text(recommendedMax);

    $('#adjustProfitModal').modal('show');
}

function setRecommendedAmount() {
    const recommendedMax = $('#recommendedMax').text();
    $('#newProfitAmount').val(recommendedMax);
}

function setZeroAmount() {
    $('#newProfitAmount').val('0.00');
}

function executeAdjustment() {
    const newAmount = $('#newProfitAmount').val();

    if (!newAmount || newAmount < 0) {
        Swal.fire('Warning!', 'Please enter a valid amount', 'warning');
        return;
    }

    const currentAmount = $('#currentProfitBalance').val();

    Swal.fire({
        title: 'Confirm Adjustment',
        html: `
            <p>Adjust profit for <strong>${currentUserData.username}</strong>?</p>
            <p>From: $${parseFloat(currentAmount).toFixed(2)} → To: $${parseFloat(newAmount).toFixed(2)}</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#007bff',
        confirmButtonText: 'Yes, Adjust!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.wallet-profit-checker.adjust-user-profit") }}',
                type: 'POST',
                data: {
                    user_id: currentUserId,
                    new_profit_amount: newAmount,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success');
                        $('#adjustProfitModal').modal('hide');
                        overThresholdTable.ajax.reload();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Adjustment failed';
                    Swal.fire('Error!', message, 'error');
                }
            });
        }
    });
}

function updateThresholdFromPage() {
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
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success'
                }).then(() => {
                    // Reload page to reflect new threshold
                    window.location.reload();
                });
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
</script>
@endsection
