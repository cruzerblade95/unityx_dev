@extends('admin/layouts/layoutMaster')

@section('title', 'Withdrawal Management')

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
// Wait for both jQuery and DataTables to be available
function waitForLibraries() {
    console.log('Checking for libraries...', {
        jquery: typeof jQuery !== 'undefined',
        dollar: typeof $ !== 'undefined',
        dataTable: (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined') ? typeof jQuery.fn.DataTable !== 'undefined' : false
    });

    if (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined' && typeof jQuery.fn.DataTable !== 'undefined') {
        console.log('Both jQuery and DataTables are now available');
        // Set $ as alias for jQuery if not already set
        if (typeof $ === 'undefined') {
            window.$ = jQuery;
        }
        initializeWithdrawalPage();
    } else {
        setTimeout(waitForLibraries, 100);
    }
}

function initializeWithdrawalPage() {
    $(document).ready(function() {
    console.log('Initializing withdrawal page...');

    // Initialize DataTable
    var table = $('#withdrawalsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.withdrawals.list') }}",
            type: 'GET',
            data: function (d) {
                d.status = $('#statusFilter').val();
                d.date_from = $('#dateFromFilter').val();
                d.date_to = $('#dateToFilter').val();
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables AJAX Error:', xhr, error, thrown);
                alert('Error loading withdrawal data: ' + xhr.responseText);
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
                data: 'amount',
                name: 'amount',
                title: 'Amount',
                render: function(data, type, row) {
                    return '$' + parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'net_amount',
                name: 'net_amount',
                title: 'Net Amount',
                render: function(data, type, row) {
                    return '$' + parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'bank_info',
                name: 'bank_info',
                title: 'Crypto Info',
                render: function(data, type, row) {
                    if (!data) {
                        return '<div class="crypto-info">N/A</div>';
                    }
                    return '<div class="crypto-info">' +
                           '<strong>' + (data.bank_name || 'N/A') + '</strong><br>' +
                           '<small class="text-muted">' + (data.account_number ? (data.account_number.substr(0, 6) + '...' + data.account_number.substr(-6)) : 'N/A') + '</small>' +
                           '</div>';
                },
                orderable: false
            },
            {
                data: 'status',
                name: 'status',
                title: 'Status',
                render: function(data, type, row) {
                    var badgeClass = '';
                    switch(data) {
                        case 'pending':
                            badgeClass = 'bg-warning';
                            break;
                        case 'approved':
                            badgeClass = 'bg-success';
                            break;
                        case 'rejected':
                            badgeClass = 'bg-danger';
                            break;
                        default:
                            badgeClass = 'bg-secondary';
                    }
                    return '<span class="badge ' + badgeClass + '">' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                }
            },
            {
                data: 'created_at',
                name: 'created_at',
                title: 'Date',
                render: function(data, type, row) {
                    return new Date(data).toLocaleDateString() + '<br>' +
                           '<small class="text-muted">' + new Date(data).toLocaleTimeString() + '</small>';
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
        responsive: true,
        dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
            {
                extend: 'collection',
                className: 'btn btn-label-primary dropdown-toggle me-2',
                text: '<i class="fas fa-download me-1"></i>Export',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel me-2"></i>Excel',
                        className: 'dropdown-item',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf me-2"></i>PDF',
                        className: 'dropdown-item',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    }
                ]
            }
        ],
        language: {
            sLengthMenu: '_MENU_',
            search: '',
            searchPlaceholder: 'Search withdrawals...'
        }
    });

    // Add title to DataTable
    $('div.head-label').html('<h5 class="card-title mb-0">Withdrawal Requests</h5>');

    // Approve withdrawal
    $(document).on('click', '.approve-btn', function() {
        var withdrawalId = $(this).data('id');

        Swal.fire({
            title: 'Approve Withdrawal?',
            text: "Are you sure you want to approve this withdrawal request?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/admin/withdrawals/" + withdrawalId + "/approve",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Approved!', response.message, 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        var response = JSON.parse(xhr.responseText);
                        Swal.fire('Error!', response.message, 'error');
                    }
                });
            }
        });
    });

    // Reject withdrawal
    $(document).on('click', '.reject-btn', function() {
        var withdrawalId = $(this).data('id');

        Swal.fire({
            title: 'Reject Withdrawal?',
            text: "Are you sure you want to reject this withdrawal request?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, reject it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/admin/withdrawals/" + withdrawalId + "/reject",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Rejected!', response.message, 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        var response = JSON.parse(xhr.responseText);
                        Swal.fire('Error!', response.message, 'error');
                    }
                });
            }
        });
    });

    // View withdrawal details
    $(document).on('click', '.view-btn', function() {
        var withdrawalId = $(this).data('id');

        $.ajax({
            url: "/admin/withdrawals/" + withdrawalId,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var modalContent = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Withdrawal ID:</strong> #${data.id}</p>
                                <p><strong>Amount:</strong> $${parseFloat(data.amount).toFixed(2)}</p>
                                <p><strong>Fee Amount (4.75%):</strong> $${parseFloat(data.tax_amount).toFixed(2)}</p>
                                <p><strong>Net Amount:</strong> $${parseFloat(data.net_amount).toFixed(2)}</p>
                                <p><strong>Status:</strong> <span class="badge bg-${data.status === 'pending' ? 'warning' : data.status === 'approved' ? 'success' : 'danger'}">${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>User:</strong> ${data.user.firstname} ${data.user.lastname}</p>
                                <p><strong>Username:</strong> @${data.user.username}</p>
                                <p><strong>Email:</strong> ${data.user.email}</p>
                                <p><strong>Currency:</strong> ${data.bank_name}</p>
                                <p><strong>Wallet Address:</strong> ${data.account_number}</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Reference:</strong> ${data.reference || 'N/A'}</p>
                                <p><strong>Note:</strong> ${data.note || 'N/A'}</p>
                                <p><strong>Created At:</strong> ${data.created_at}</p>
                            </div>
                        </div>
                    `;

                    $('#viewModalBody').html(modalContent);
                    $('#viewModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Failed to load withdrawal details', 'error');
            }
        });
    });

    // Filter handlers
    $('#statusFilter, #dateFromFilter, #dateToFilter').on('change', function() {
        table.ajax.reload();
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#statusFilter').val('');
        $('#dateFromFilter').val('');
        $('#dateToFilter').val('');
        table.ajax.reload();
    });
    });
}

// Start the initialization
waitForLibraries();
</script>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Total Withdrawals</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ number_format($totalWithdrawals) }}</h4>
                        </div>
                        <small class="text-muted">All time requests</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="fas fa-money-bill-wave text-primary"></i>
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
                        <small class="text-muted">Awaiting approval</small>
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
                        <span class="text-heading">Approved</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ number_format($approvedWithdrawals) }}</h4>
                        </div>
                        <small class="text-muted">Successfully approved</small>
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

    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Total Net Amount</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">${{ number_format($totalNetAmount, 2) }}</h4>
                        </div>
                        <small class="text-muted">All net withdrawals</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="fas fa-dollar-sign text-info"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Row - Net Amount by Status -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Total Pending Net</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">${{ number_format($pendingNetAmount, 2) }}</h4>
                        </div>
                        <small class="text-muted">Pending net amount</small>
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

    <div class="col-sm-6 col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Total Approved Net</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">${{ number_format($approvedNetAmount, 2) }}</h4>
                        </div>
                        <small class="text-muted">Approved net amount</small>
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

    <div class="col-sm-6 col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Total Rejected Net</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">${{ number_format($rejectedNetAmount, 2) }}</h4>
                        </div>
                        <small class="text-muted">Rejected net amount</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-danger">
                            <i class="fas fa-times-circle text-danger"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Withdrawals List -->
<div class="card">
    <!-- Filter Section -->
    <div class="card-header">
        <div class="row">
            <div class="col-md-3">
                <label for="statusFilter" class="form-label">Filter by Status:</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="dateFromFilter" class="form-label">From Date:</label>
                <input type="date" id="dateFromFilter" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="dateToFilter" class="form-label">To Date:</label>
                <input type="date" id="dateToFilter" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button id="clearFilters" class="btn btn-secondary">Clear Filters</button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-datatable table-responsive">
        <table id="withdrawalsTable" class="datatables-withdrawal table border-top">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Info</th>
                    <th>Amount</th>
                    <th>Net Amount</th>
                    <th>Bank Info</th>
                    <th>Status</th>
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
                <h5 class="modal-title" id="viewModalLabel">Withdrawal Details</h5>
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
