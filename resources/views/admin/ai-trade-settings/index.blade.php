@extends('admin/layouts/layoutMaster')

@section('title', 'AI Trade Settings')

@section('vendor-style')
<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js'])
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
<script>
// Ensure jQuery is loaded before running the script
document.addEventListener('DOMContentLoaded', function() {
    // Wait for jQuery to be available
    function waitForJQuery() {
        if (window.jQuery) {
            initializeAiTradeSettings();
        } else {
            setTimeout(waitForJQuery, 50);
        }
    }
    waitForJQuery();
});

function initializeAiTradeSettings() {
$(document).ready(function() {
    // Initialize DataTable for users
    var usersTable = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.ai-trade-settings.users.list') }}",
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('DataTables AJAX Error:', xhr, error, thrown);
                Swal.fire('Error', 'Error loading users data: ' + xhr.responseText, 'error');
            }
        },
        columns: [
            { data: 'id', name: 'id', title: 'ID' },
            {
                data: 'user_info',
                name: 'user_info',
                title: 'User Info',
                render: function(data, type, row) {
                    if (!data) {
                        return '<div class="user-info"><strong>Unknown User</strong></div>';
                    }
                    return '<div class="user-info">' +
                           '<strong>' + (data.firstname || 'Unknown') + ' ' + (data.lastname || 'User') + '</strong><br>' +
                           '<small class="text-muted">@' + (data.username || 'unknown') + '</small><br>' +
                           '<small class="text-muted">' + (data.email || 'unknown@email.com') + '</small>' +
                           '</div>';
                },
                orderable: false
            },
            {
                data: 'status_badge',
                name: 'status',
                title: 'Status',
                render: function(data, type, row) {
                    return '<span class="badge bg-' + data.class + '">' + data.label + '</span>';
                }
            },
            {
                data: 'trades_count',
                name: 'trades_count',
                title: 'Total Trades',
                render: function(data, type, row) {
                    return '<span class="badge bg-info">' + data + '</span>';
                }
            },
            {
                data: 'active_trades',
                name: 'active_trades',
                title: 'Active Trades',
                render: function(data, type, row) {
                    return '<span class="badge bg-success">' + data + '</span>';
                }
            },
            {
                data: 'total_investment',
                name: 'total_investment',
                title: 'Total Investment',
                render: function(data, type, row) {
                    return '$' + parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'created_at',
                name: 'created_at',
                title: 'Activated Date',
                render: function(data, type, row) {
                    return new Date(data).toLocaleDateString();
                }
            },
            {
                data: 'action',
                name: 'action',
                title: 'Actions',
                orderable: false,
                searchable: false
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });

    // Settings form submission
    $('#settingsForm').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            min_percent: $('#minPercent').val(),
            max_percent: $('#maxPercent').val(),
            percent_amount_to_deduct: $('#percentAmountToDeduct').val(),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: "{{ route('admin.ai-trade-settings.update') }}",
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Failed to update settings.', 'error');
            }
        });
    });

    // View Details Modal
    $(document).on('click', '.view-details-btn', function() {
        var userId = $(this).data('id');

        $.ajax({
            url: "{{ url('admin/ai-trade-settings/user') }}/" + userId + "/trades",
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    populateDetailsModal(response);
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Failed to load user details.', 'error');
            }
        });
    });

    // Change Status Modal
    $(document).on('click', '.change-status-btn', function() {
        var activateId = $(this).data('id');
        var userId = $(this).data('user-id');
        var currentStatus = $(this).data('current-status');

        $('#changeStatusForm').attr('data-id', activateId);
        $('#statusSelect').val(currentStatus);
        $('#changeStatusModalLabel').text('Change Status for User ID: ' + userId);
    });

    // Change Status Form Submission
    $('#changeStatusForm').on('submit', function(e) {
        e.preventDefault();

        var activateId = $(this).attr('data-id');
        var newStatus = $('#statusSelect').val();

        $.ajax({
            url: "{{ url('admin/ai-trade-settings/update-status') }}/" + activateId,
            method: 'PUT',
            data: {
                status: newStatus,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#changeStatusModal').modal('hide');
                    usersTable.ajax.reload();
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Failed to update status.', 'error');
            }
        });
    });

    // Calculate ending amount automatically (ensure it's always 5x investment)
    $(document).on('input', '#editAmountInvest', function() {
        var amountInvest = parseFloat($(this).val()) || 0;
        var endingAmount = amountInvest * 5;
        $('#editEndingAmount').val(endingAmount.toFixed(8));
    });

    // Validate ending amount to ensure it's always 5x
    $(document).on('input', '#editEndingAmount', function() {
        var amountInvest = parseFloat($('#editAmountInvest').val()) || 0;
        var expectedEnding = amountInvest * 5;
        $(this).val(expectedEnding.toFixed(8));

        if (amountInvest > 0) {
            Swal.fire({
                title: 'Info',
                text: 'Ending amount is automatically calculated as 5x the investment amount',
                icon: 'info',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });

    // Edit Trade Form Submission
    $('#editTradeForm').on('submit', function(e) {
        e.preventDefault();

        var tradeId = $(this).attr('data-trade-id');
        var formData = {
            amount_invest: $('#editAmountInvest').val(),
            current_amount: $('#editCurrentAmount').val(),
            trade_status: $('#editTradeStatus').val(),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: "{{ url('admin/ai-trade-settings/update-trade') }}/" + tradeId,
            method: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#editTradeModal').modal('hide');
                    // Refresh the trades in the details modal
                    $('.view-details-btn[data-id="' + $('#viewDetailsModal').attr('data-user-id') + '"]').click();
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Failed to update trade.', 'error');
            }
        });
    });

    function populateDetailsModal(response) {
        var user = response.user;
        var trades = response.trades;

        $('#viewDetailsModal').attr('data-user-id', user.id);
        $('#userInfoDetails').html(
            '<strong>' + user.firstname + ' ' + user.lastname + '</strong><br>' +
            '<small class="text-muted">@' + user.username + '</small><br>' +
            '<small class="text-muted">' + user.email + '</small>'
        );

        var tradesHtml = '';
        trades.forEach(function(trade) {
            var statusBadge = trade.trade_status == 1 ?
                '<span class="badge bg-success">Running</span>' :
                '<span class="badge bg-warning">Ended</span>';

            tradesHtml += '<tr>';
            tradesHtml += '<td>' + trade.id + '</td>';
            tradesHtml += '<td>$' + parseFloat(trade.amount_invest).toFixed(8) + '</td>';
            tradesHtml += '<td>$' + parseFloat(trade.current_amount).toFixed(8) + '</td>';
            tradesHtml += '<td>$' + parseFloat(trade.ending_amount).toFixed(8) + '</td>';
            tradesHtml += '<td>' + statusBadge + '</td>';
            tradesHtml += '<td>$' + parseFloat(trade.total_pnl).toFixed(8) + '</td>';
            tradesHtml += '<td>' + trade.created_at + '</td>';
            tradesHtml += '<td>';
            tradesHtml += '<button class="btn btn-sm btn-warning edit-trade-btn me-1" data-id="' + trade.id + '" data-trade=\'' + JSON.stringify(trade) + '\'>';
            tradesHtml += '<i class="fas fa-edit"></i> Edit</button>';
            tradesHtml += '<button class="btn btn-sm btn-danger delete-trade-btn" data-id="' + trade.id + '">';
            tradesHtml += '<i class="fas fa-trash"></i> Delete</button>';
            tradesHtml += '</td>';
            tradesHtml += '</tr>';
        });

        $('#tradesTableBody').html(tradesHtml);
    }

    // Edit Trade Button
    $(document).on('click', '.edit-trade-btn', function() {
        var tradeData = $(this).data('trade');

        $('#editTradeForm').attr('data-trade-id', tradeData.id);
        $('#editAmountInvest').val(tradeData.amount_invest);
        $('#editCurrentAmount').val(tradeData.current_amount);
        $('#editEndingAmount').val(tradeData.ending_amount);
        $('#editTradeStatus').val(tradeData.trade_status);

        $('#editTradeModal').modal('show');
    });

    // Delete Trade Button
    $(document).on('click', '.delete-trade-btn', function() {
        var tradeId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: 'This will permanently delete this trade and all its history!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/ai-trade-settings/delete-trade') }}/" + tradeId,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            // Refresh the trades in the details modal
                            $('.view-details-btn[data-id="' + $('#viewDetailsModal').attr('data-user-id') + '"]').click();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Failed to delete trade.', 'error');
                    }
                });
            }
        });
    });
});
}
</script>
@endsection

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">AI Trade Settings</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">AI Trade Settings</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span class="text-heading">Total Users</span>
                                    <div class="d-flex align-items-center my-1">
                                        <h4 class="mb-0 me-2">{{ number_format($stats['total_users']) }}</h4>
                                    </div>
                                    <small class="text-muted">AI Trade participants</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="fas fa-users text-primary"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span class="text-heading">Active Users</span>
                                    <div class="d-flex align-items-center my-1">
                                        <h4 class="mb-0 me-2">{{ number_format($stats['active_users']) }}</h4>
                                    </div>
                                    <small class="text-muted">Currently running</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="fas fa-play text-success"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span class="text-heading">Total Trades</span>
                                    <div class="d-flex align-items-center my-1">
                                        <h4 class="mb-0 me-2">{{ number_format($stats['total_trades']) }}</h4>
                                    </div>
                                    <small class="text-muted">All time trades</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="fas fa-chart-line text-info"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span class="text-heading">Active Trades</span>
                                    <div class="d-flex align-items-center my-1">
                                        <h4 class="mb-0 me-2">{{ number_format($stats['active_trades']) }}</h4>
                                    </div>
                                    <small class="text-muted">Currently running</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="fas fa-robot text-warning"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trading Settings -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-cog me-2"></i>Trading Configuration
                            </h4>
                            <p class="card-title-desc mb-0">Configure AI trading algorithm parameters</p>
                        </div>
                        <div class="card-body">
                            <form id="settingsForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="minPercent" class="form-label">Minimum Percent</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="minPercent"
                                                       value="{{ $settings['min_percent'] }}"
                                                       step="0.0001" min="0" max="1" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <small class="text-muted">Minimum profit percentage (e.g., 0.003 = 0.3%)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="maxPercent" class="form-label">Maximum Percent</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="maxPercent"
                                                       value="{{ $settings['max_percent'] }}"
                                                       step="0.0001" min="0" max="1" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <small class="text-muted">Maximum profit percentage (e.g., 0.004 = 0.4%)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="percentAmountToDeduct" class="form-label">Deduction Percent</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="percentAmountToDeduct"
                                                       value="{{ $settings['percent_amount_to_deduct'] }}"
                                                       step="0.01" min="0" max="1" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <small class="text-muted">Percentage to deduct from investment (e.g., 0.4 = 40%)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Update Settings
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-users me-2"></i>AI Trade Users Management
                            </h4>
                            <p class="card-title-desc mb-0">Manage users' AI trade activation status and view their trades</p>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="usersTable" class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>User Info</th>
                                            <th>Status</th>
                                            <th>Total Trades</th>
                                            <th>Active Trades</th>
                                            <th>Total Investment</th>
                                            <th>Activated Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDetailsModalLabel">
                    <i class="fas fa-eye me-2"></i>User Trade Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <h6>User Information:</h6>
                        <div id="userInfoDetails" class="p-3 bg-light rounded"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6>Trades List:</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Amount Invest</th>
                                        <th>Current Amount</th>
                                        <th>Ending Amount</th>
                                        <th>Status</th>
                                        <th>Total PnL</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tradesTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeStatusModalLabel">Change Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changeStatusForm">
                    <div class="mb-3">
                        <label for="statusSelect" class="form-label">Select Status</label>
                        <select class="form-select" id="statusSelect" required>
                            <option value="0">Cancelled</option>
                            <option value="1">Running</option>
                            <option value="2">Ended</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Trade Modal -->
<div class="modal fade" id="editTradeModal" tabindex="-1" aria-labelledby="editTradeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTradeModalLabel">Edit Trade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTradeForm">
                    <div class="mb-3">
                        <label for="editAmountInvest" class="form-label">Amount Invest</label>
                        <input type="number" class="form-control" id="editAmountInvest" step="0.00000001" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCurrentAmount" class="form-label">Current Amount</label>
                        <input type="number" class="form-control" id="editCurrentAmount" step="0.00000001" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEndingAmount" class="form-label">Ending Amount (Auto-calculated)</label>
                        <input type="number" class="form-control" id="editEndingAmount" step="0.00000001" readonly>
                        <small class="text-muted">Automatically calculated as 5x the investment amount</small>
                    </div>
                    <div class="mb-3">
                        <label for="editTradeStatus" class="form-label">Trade Status</label>
                        <select class="form-select" id="editTradeStatus" required>
                            <option value="1">Running</option>
                            <option value="2">Ended</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Trade</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
