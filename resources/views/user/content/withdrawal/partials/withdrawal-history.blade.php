<!-- Withdrawal History -->
<div class="card">
    <div class="card-header">
        <h4>Withdrawal History</h4>
    </div>
    <div class="card-body">
        <!-- Filters Section -->
        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <label for="typeFilter" class="form-label">Withdrawal Type</label>
                <select class="form-select" id="typeFilter">
                    <option value="">All Types</option>
                    <option value="crypto">Crypto Wallet</option>
                    <option value="etc_black">Crypto Bank Card</option>
                    <option value="gold">Gold Redemption</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label for="statusFilter" class="form-label">Status</label>
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="completed">Completed</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label for="walletFilter" class="form-label">Wallet Type</label>
                <select class="form-select" id="walletFilter">
                    <option value="">All Wallets</option>
                    <option value="profit">Profit</option>
                    <option value="bonus">Bonus</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label for="dateFromFilter" class="form-label">From Date</label>
                <input type="date" class="form-control" id="dateFromFilter">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <label for="dateToFilter" class="form-label">To Date</label>
                <input type="date" class="form-control" id="dateToFilter">
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button id="clearFilters" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Withdrawal History Table -->
        <div class="table-responsive">
            <table id="withdrawalhistorytable" class="table table-striped withdrawal-history-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Wallet</th>
                        <th>Amount</th>
                        <th>Fee</th>
                        <th>Net Amount</th>
                        <th>Payment Details</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables will populate this -->
                </tbody>
            </table>
        </div>

        <!-- Rejection Reason Modals (will be dynamically created by AJAX) -->
        <div id="rejectionModalsContainer"></div>
    </div>
</div>

<!-- DataTables JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Notyf for notifications
        if (typeof window.notyf === 'undefined') {
            window.notyf = new Notyf({
                duration: 4000,
                position: {
                    x: 'right',
                    y: 'top'
                }
            });
        }

        // Initialize DataTable
        let table = $('#withdrawalhistorytable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            pageLength: 10,
            lengthMenu: [
                [5, 10, 25, 50, 100],
                [5, 10, 25, 50, 100]
            ],
            order: [
                [0, 'desc']
            ], // Order by ID descending (newest first)
            ajax: {
                url: "{{ route('user.withdrawal.history.list') }}",
                type: 'GET',
                data: function(d) {
                    // Add custom filters to the DataTables request
                    d.withdrawal_type = $('#typeFilter').val() || '';
                    d.status = $('#statusFilter').val() || '';
                    d.wallet_type = $('#walletFilter').val() || '';
                    d.date_from = $('#dateFromFilter').val() || '';
                    d.date_to = $('#dateToFilter').val() || '';

                    console.log('DataTables request data:', d);
                    return d;
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables AJAX Error:', {
                        xhr: xhr,
                        error: error,
                        thrown: thrown,
                        responseText: xhr.responseText,
                        status: xhr.status
                    });

                    // Initialize notyf if not already done
                    if (typeof notyf === 'undefined') {
                        window.notyf = new Notyf();
                    }

                    let errorMessage = 'Error loading withdrawal history';
                    if (xhr.status === 500) {
                        errorMessage += ': Server error occurred';
                    } else if (xhr.status === 404) {
                        errorMessage += ': API endpoint not found';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += ': ' + xhr.responseJSON.message;
                    }

                    notyf.error(errorMessage);
                },
                dataSrc: function(json) {
                    console.log('DataTables response:', json);
                    return json.data;
                }
            },
            columns: [{
                    data: 'id',
                    name: 'id',
                    title: 'ID',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return '<span class="font-monospace">#' + data + '</span>';
                        }
                        return data;
                    }
                },
                {
                    data: 'type_badge',
                    name: 'withdrawal_type',
                    title: 'Type',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'wallet_badge',
                    name: 'wallet_type',
                    title: 'Wallet',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'formatted_amount',
                    name: 'amount',
                    title: 'Amount',
                    className: 'amount-cell',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'fee_info',
                    name: 'tax_percent',
                    title: 'Fee',
                    className: 'text-muted',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'formatted_net_amount',
                    name: 'net_amount',
                    title: 'Net Amount',
                    className: 'amount-cell text-success',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'payment_details',
                    name: 'payment_details',
                    title: 'Payment Details',
                    className: 'payment-details',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    title: 'Status',
                    className: 'status-cell',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'formatted_date',
                    name: 'created_at',
                    title: 'Date',
                    className: 'small text-muted',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'actions',
                    name: 'actions',
                    title: 'Actions',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> Loading withdrawal history...',
                search: '_INPUT_',
                searchPlaceholder: 'Search withdrawals...',
                lengthMenu: 'Show _MENU_ withdrawals per page',
                info: 'Showing _START_ to _END_ of _TOTAL_ withdrawals',
                infoEmpty: 'No withdrawal records found',
                infoFiltered: '(filtered from _MAX_ total withdrawals)',
                zeroRecords: '<div class="text-center py-4"><i class="fas fa-history text-muted fa-3x mb-3"></i><p class="text-muted">No withdrawal requests found.</p></div>',
                emptyTable: '<div class="text-center py-4"><i class="fas fa-history text-muted fa-3x mb-3"></i><p class="text-muted">No withdrawal requests found.</p></div>',
                paginate: {
                    first: '<i class="fas fa-angle-double-left"></i>',
                    previous: '<i class="fas fa-angle-left"></i>',
                    next: '<i class="fas fa-angle-right"></i>',
                    last: '<i class="fas fa-angle-double-right"></i>'
                }
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            drawCallback: function(settings) {
                // Re-initialize Bootstrap tooltips if any
                $('[data-bs-toggle="tooltip"]').tooltip();

                // Handle rejection modal buttons
                $('.rejection-btn').off('click').on('click', function(e) {
                    e.preventDefault();
                    let modalTarget = $(this).data('bs-target');
                    $(modalTarget).modal('show');
                });
            },
            initComplete: function() {
                // Custom styling for search input
                $('.dataTables_filter input').addClass('form-control').attr('placeholder',
                    'Search withdrawals...');
                $('.dataTables_length select').addClass('form-select');
            }
        });

        // Filter event handlers
        $('#typeFilter, #statusFilter, #walletFilter, #dateFromFilter, #dateToFilter').on('change', function() {
            table.draw();
        });

        // Clear filters button
        $('#clearFilters').on('click', function() {
            $('#typeFilter, #statusFilter, #walletFilter').val('');
            $('#dateFromFilter, #dateToFilter').val('');
            table.draw();

            // Show success message
            if (typeof notyf === 'undefined') {
                window.notyf = new Notyf();
            }
            notyf.success('Filters cleared successfully');
        });

        // Auto-reload table every 30 seconds to check for status updates
        setInterval(function() {
            if (table && !table.processing()) {
                table.ajax.reload(null, false); // false = don't reset pagination
            }
        }, 30000);
    });
</script>

<style>
    /* Enhanced DataTables styling for withdrawal history */
    #withdrawalhistorytable {
        font-size: 0.9rem;
    }

    #withdrawalhistorytable_wrapper .dataTables_length,
    #withdrawalhistorytable_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }

    #withdrawalhistorytable_wrapper .dataTables_paginate {
        margin-top: 1rem;
    }

    #withdrawalhistorytable_wrapper .dataTables_info {
        margin-top: 1rem;
        color: var(--bs-secondary-color);
    }

    /* Filter section styling */
    .card-body .row.mb-3 {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem !important;
    }

    .card-body .row.mb-3:first-child {
        border-bottom: none;
        margin-bottom: 0.5rem !important;
    }

    /* Table responsive improvements */
    @media (max-width: 992px) {
        #withdrawalhistorytable {
            font-size: 0.8rem;
        }

        #withdrawalhistorytable .type-badge,
        #withdrawalhistorytable .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }
    }

    /* Loading spinner styling */
    .dataTables_processing {
        background: var(--bs-body-bg) !important;
        border: 1px solid var(--bs-border-color) !important;
        color: var(--bs-body-color) !important;
        border-radius: 8px !important;
        font-size: 1rem !important;
    }

    /* Pagination styling */
    .dataTables_paginate .paginate_button {
        border: 1px solid var(--bs-border-color) !important;
        color: var(--bs-body-color) !important;
    }

    .dataTables_paginate .paginate_button:hover {
        background: var(--bs-primary) !important;
        border-color: var(--bs-primary) !important;
        color: white !important;
    }

    .dataTables_paginate .paginate_button.current {
        background: var(--bs-primary) !important;
        border-color: var(--bs-primary) !important;
        color: white !important;
    }
</style>
