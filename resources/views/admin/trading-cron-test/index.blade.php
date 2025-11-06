@extends('admin.layouts.layoutMaster')

@section('title', 'Trading Cron Test & Monitoring')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Active Users</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2">{{ $stats['total_active_users'] }}</h4>
                            </div>
                            <small class="text-muted">AI Trade Enabled</small>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-primary rounded p-2">
                                <i class="fas fa-users fa-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Total Tests</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2">{{ $stats['total_tests_run'] }}</h4>
                            </div>
                            <small class="text-muted">All Time</small>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-info rounded p-2">
                                <i class="fas fa-vial fa-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Successful</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2 text-success">{{ $stats['successful_tests'] }}</h4>
                            </div>
                            <small class="text-muted">Success Rate: {{ $stats['total_tests_run'] > 0 ? number_format(($stats['successful_tests'] / $stats['total_tests_run']) * 100, 1) : 0 }}%</small>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-success rounded p-2">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Failed</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2 text-danger">{{ $stats['failed_tests'] }}</h4>
                            </div>
                            <small class="text-muted">Skipped: {{ $stats['skipped_tests'] }}</small>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-danger rounded p-2">
                                <i class="fas fa-exclamation-triangle fa-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Summary -->
    @if($errorSummary->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Common Error Reasons</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Error Reason</th>
                                    <th>Occurrences</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($errorSummary as $error)
                                <tr>
                                    <td>{{ $error->failure_reason ?? 'Unknown' }}</td>
                                    <td><span class="badge bg-danger">{{ $error->count }}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary filter-by-error" data-reason="{{ $error->failure_reason }}">
                                            <i class="fas fa-filter"></i> View Details
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="row">
        <!-- Active Users Table -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Active Users (AI Trade Enabled)</h5>
                    <div>
                        <button class="btn btn-primary btn-sm" id="testAllUsersBtn">
                            <i class="fas fa-play-circle"></i> Test All Users
                        </button>
                        <button class="btn btn-secondary btn-sm" id="refreshUsersBtn">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="activeUsersTable">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Active Trades</th>
                                    <th>Total Investment</th>
                                    <th>Wallet Profit</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Logs Table -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Test Execution Logs</h5>
                    <div>
                        <select class="form-select form-select-sm d-inline-block w-auto me-2" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                            <option value="skipped">Skipped</option>
                        </select>
                        <button class="btn btn-warning btn-sm" id="clearOldLogsBtn">
                            <i class="fas fa-trash"></i> Clear Old Logs
                        </button>
                        <button class="btn btn-secondary btn-sm" id="refreshLogsBtn">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="testLogsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Trade Info</th>
                                    <th>Status</th>
                                    <th>Profit</th>
                                    <th>Test Type</th>
                                    <th>Executed At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details & Active Trades</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let activeUsersTable;
    let testLogsTable;

    // Initialize Active Users DataTable
    activeUsersTable = $('#activeUsersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("admin.trading-cron-test.data") }}',
        columns: [
            { data: 'user_id', name: 'user_id' },
            { data: 'username', name: 'username' },
            { data: 'email', name: 'email' },
            { data: 'active_trades_count', name: 'active_trades_count' },
            {
                data: 'total_investment',
                name: 'total_investment',
                render: function(data) {
                    return data ? '$' + parseFloat(data).toFixed(2) : '$0.00';
                }
            },
            {
                data: 'wallet_profit',
                name: 'wallet_profit',
                render: function(data) {
                    return data ? '$' + parseFloat(data).toFixed(8) : '$0.00';
                }
            },
            { data: 'status', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']]
    });

    // Initialize Test Logs DataTable
    testLogsTable = $('#testLogsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.trading-cron-test.logs-data") }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'user_info', name: 'user_info' },
            { data: 'trade_info', name: 'trade_info' },
            { data: 'status_badge', name: 'status_badge', orderable: false },
            { data: 'profit_display', name: 'profit_display' },
            { data: 'test_type', name: 'test_type' },
            { data: 'test_executed_at', name: 'test_executed_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']]
    });

    // Test User Button
    $(document).on('click', '.test-user-btn', function() {
        const userId = $(this).data('user-id');

        Swal.fire({
            title: 'Running Test...',
            text: 'Testing trading cron for this user',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("admin.trading-cron-test.test-user", ":id") }}'.replace(':id', userId),
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.close();

                let html = '<div class="alert alert-' + (response.success ? 'success' : 'warning') + '">';
                html += '<strong>' + response.message + '</strong></div>';

                if (response.results && response.results.length > 0) {
                    html += '<table class="table table-sm mt-3"><thead><tr><th>Trade ID</th><th>Status</th><th>Message</th></tr></thead><tbody>';
                    response.results.forEach(function(result) {
                        html += '<tr>';
                        html += '<td>' + result.trade_id + '</td>';
                        html += '<td><span class="badge bg-' + (result.success ? 'success' : 'danger') + '">' + (result.success ? 'Success' : 'Failed') + '</span></td>';
                        html += '<td>' + result.message + (result.error ? '<br><small class="text-danger">' + result.error + '</small>' : '') + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                }

                Swal.fire({
                    title: response.success ? 'Test Completed!' : 'Test Result',
                    html: html,
                    icon: response.success ? 'success' : 'info',
                    width: '600px'
                });

                testLogsTable.ajax.reload();
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to run test',
                    icon: 'error'
                });
            }
        });
    });

    // View Details Button
    $(document).on('click', '.view-details-btn', function() {
        const userId = $(this).data('user-id');

        $('#userDetailsModal').modal('show');

        $.ajax({
            url: '{{ route("admin.trading-cron-test.user-details", ":id") }}'.replace(':id', userId),
            type: 'GET',
            success: function(response) {
                let html = '<div class="row">';

                // User Info
                html += '<div class="col-md-6 mb-3">';
                html += '<h6>User Information</h6>';
                html += '<p><strong>Username:</strong> ' + response.user.username + '</p>';
                html += '<p><strong>Email:</strong> ' + response.user.email + '</p>';
                html += '<p><strong>AI Trade Status:</strong> <span class="badge bg-' + (response.ai_trade_activate?.status == 1 ? 'success' : 'danger') + '">' + (response.ai_trade_activate?.status == 1 ? 'Active' : 'Inactive') + '</span></p>';
                html += '</div>';

                // Wallet Info
                html += '<div class="col-md-6 mb-3">';
                html += '<h6>Wallet Information</h6>';
                if (response.wallet) {
                    html += '<p><strong>Deposit:</strong> $' + parseFloat(response.wallet.deposit).toFixed(8) + '</p>';
                    html += '<p><strong>Profit:</strong> $' + parseFloat(response.wallet.profit).toFixed(8) + '</p>';
                } else {
                    html += '<p class="text-danger">No wallet found!</p>';
                }
                html += '</div>';

                // Active Trades
                html += '<div class="col-12 mb-3">';
                html += '<h6>Active Trades (' + response.active_trades.length + ')</h6>';
                if (response.active_trades.length > 0) {
                    html += '<table class="table table-sm"><thead><tr><th>ID</th><th>Investment</th><th>Current</th><th>Ending</th><th>Status</th></tr></thead><tbody>';
                    response.active_trades.forEach(function(trade) {
                        html += '<tr>';
                        html += '<td>' + trade.id + '</td>';
                        html += '<td>$' + parseFloat(trade.amount_invest).toFixed(2) + '</td>';
                        html += '<td>$' + parseFloat(trade.current_amount).toFixed(2) + '</td>';
                        html += '<td>$' + parseFloat(trade.ending_amount).toFixed(2) + '</td>';
                        html += '<td><span class="badge bg-' + (trade.trade_status == 1 ? 'success' : 'secondary') + '">' + (trade.trade_status == 1 ? 'Active' : 'Ended') + '</span></td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                } else {
                    html += '<p class="text-muted">No active trades found</p>';
                }
                html += '</div>';

                html += '</div>';

                $('#userDetailsContent').html(html);
            },
            error: function() {
                $('#userDetailsContent').html('<div class="alert alert-danger">Failed to load user details</div>');
            }
        });
    });

    // View Log Details
    $(document).on('click', '.view-log-details', function() {
        const logId = $(this).data('log-id');

        $('#logDetailsModal').modal('show');

        $.ajax({
            url: '{{ route("admin.trading-cron-test.log-details", ":id") }}'.replace(':id', logId),
            type: 'GET',
            success: function(response) {
                let html = '<div class="row">';

                html += '<div class="col-md-6"><p><strong>User:</strong> ' + (response.user ? response.user.username : 'N/A') + '</p></div>';
                html += '<div class="col-md-6"><p><strong>Status:</strong> <span class="badge bg-' + (response.status == 'success' ? 'success' : (response.status == 'failed' ? 'danger' : 'warning')) + '">' + response.status.toUpperCase() + '</span></p></div>';

                if (response.ai_trade_id) {
                    html += '<div class="col-md-6"><p><strong>Trade ID:</strong> ' + response.ai_trade_id + '</p></div>';
                }

                if (response.profit_amount) {
                    html += '<div class="col-md-6"><p><strong>Profit:</strong> $' + parseFloat(response.profit_amount).toFixed(8) + '</p></div>';
                }

                if (response.failure_reason) {
                    html += '<div class="col-12"><div class="alert alert-danger"><strong>Error:</strong> ' + response.failure_reason + '</div></div>';
                }

                if (response.error_message) {
                    html += '<div class="col-12"><div class="alert alert-warning"><strong>Details:</strong><br>' + response.error_message + '</div></div>';
                }

                if (response.data_snapshot) {
                    html += '<div class="col-12"><h6>Data Snapshot:</h6><pre class="bg-light p-3">' + JSON.stringify(response.data_snapshot, null, 2) + '</pre></div>';
                }

                html += '</div>';

                $('#logDetailsContent').html(html);
            },
            error: function() {
                $('#logDetailsContent').html('<div class="alert alert-danger">Failed to load log details</div>');
            }
        });
    });

    // Test All Users
    $('#testAllUsersBtn').click(function() {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will test trading cron for all active users. This may take a while.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, test all!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Running Tests...',
                    text: 'Testing all active users',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route("admin.trading-cron-test.test-all-users") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Batch Test Completed!',
                            html: '<p>Total Users: ' + response.results.total_users + '</p>' +
                                  '<p>Successful: <span class="text-success">' + response.results.successful + '</span></p>' +
                                  '<p>Failed: <span class="text-danger">' + response.results.failed + '</span></p>' +
                                  '<p>Skipped: <span class="text-warning">' + response.results.skipped + '</span></p>',
                            icon: 'success'
                        });

                        testLogsTable.ajax.reload();
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to run batch test', 'error');
                    }
                });
            }
        });
    });

    // Clear Old Logs
    $('#clearOldLogsBtn').click(function() {
        Swal.fire({
            title: 'Clear Old Logs',
            input: 'number',
            inputLabel: 'Delete logs older than (days):',
            inputValue: 30,
            showCancelButton: true,
            confirmButtonText: 'Delete',
            inputValidator: (value) => {
                if (!value || value < 1) {
                    return 'Please enter a valid number of days';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.trading-cron-test.clear-old-logs") }}',
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: { days: result.value },
                    success: function(response) {
                        Swal.fire('Success!', response.message, 'success');
                        testLogsTable.ajax.reload();
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to clear logs', 'error');
                    }
                });
            }
        });
    });

    // Status Filter
    $('#statusFilter').change(function() {
        testLogsTable.ajax.reload();
    });

    // Refresh Buttons
    $('#refreshUsersBtn').click(function() {
        activeUsersTable.ajax.reload();
    });

    $('#refreshLogsBtn').click(function() {
        testLogsTable.ajax.reload();
    });

    // Filter by Error
    $('.filter-by-error').click(function() {
        const reason = $(this).data('reason');
        // You can implement filtering logic here
        Swal.fire('Info', 'Filtering by error: ' + reason, 'info');
    });
});
</script>
@endsection
