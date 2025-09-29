<!-- Withdrawal History -->
<div class="card">
    <div class="card-header">
        <h4>Withdrawal History</h4>
    </div>
    <div class="card-body">
        <!-- Filters Section -->
        <div class="filters-container">
            <div class="row g-2">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <label for="typeFilter" class="form-label">Withdrawal Type</label>
                    <select class="form-select form-select-sm" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="crypto">Crypto Wallet</option>
                        <option value="etc_black">Crypto Bank Card</option>
                        <option value="gold">Gold Redemption</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select class="form-select form-select-sm" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="completed">Completed</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <label for="walletFilter" class="form-label">Wallet Type</label>
                    <select class="form-select form-select-sm" id="walletFilter">
                        <option value="">All Wallets</option>
                        <option value="profit">Profit</option>
                        <option value="bonus">Bonus</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <label for="dateFromFilter" class="form-label">From Date</label>
                    <input type="date" class="form-control form-control-sm" id="dateFromFilter">
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <label for="dateToFilter" class="form-label">To Date</label>
                    <input type="date" class="form-control form-control-sm" id="dateToFilter">
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <label class="form-label d-none d-sm-block">&nbsp;</label>
                    <button id="clearFilters" class="btn btn-secondary btn-sm w-100">
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

        <!-- Withdrawal Details Modals (will be dynamically created by AJAX) -->
        <div id="rejectionModalsContainer"></div>
    </div>
</div>

<!-- DataTables JavaScript -->
<script>
    // Ensure Bootstrap and other dependencies are loaded before initializing
    function initializeWithdrawalHistory() {
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

        // Check if required dependencies are loaded
        if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
            console.error('jQuery or DataTables not loaded');
            return;
        }

        // Initialize DataTable
        let table = $('#withdrawalhistorytable').DataTable({
            processing: true,
            serverSide: true,
            responsive: false, // Disable DataTables responsive as we use custom CSS
            pageLength: window.innerWidth < 768 ? 5 : 10,
            lengthMenu: window.innerWidth < 768 ? [
                [5, 10, 25],
                [5, 10, 25]
            ] : [
                [5, 10, 25, 50, 100],
                [5, 10, 25, 50, 100]
            ],
            order: [
                [0, 'desc']
            ], // Order by ID descending (newest first)
            scrollX: false,
            autoWidth: false,
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
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return '<div class="mobile-content">' + data + '</div>';
                        }
                        return data;
                    },
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'wallet_badge',
                    name: 'wallet_type',
                    title: 'Wallet',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return '<div class="mobile-content">' + data + '</div>';
                        }
                        return data;
                    },
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'formatted_amount',
                    name: 'amount',
                    title: 'Amount',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return '<div class="mobile-content"><span class="text-info fw-semibold">' +
                                data + '</span></div>';
                        }
                        return data;
                    },
                    className: 'amount-cell',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'fee_info',
                    name: 'tax_percent',
                    title: 'Fee',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return '<div class="mobile-content"><span class="text-muted">' +
                                data + '</span></div>';
                        }
                        return data;
                    },
                    className: 'text-muted',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'formatted_net_amount',
                    name: 'net_amount',
                    title: 'Net Amount',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return '<div class="mobile-content"><span class="text-success fw-bold">' +
                                data + '</span></div>';
                        }
                        return data;
                    },
                    className: 'amount-cell text-success',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'payment_details',
                    name: 'payment_details',
                    title: 'Payment Details',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return '<div class="mobile-content"><small class="text-muted">' +
                                data + '</small></div>';
                        }
                        return data;
                    },
                    className: 'payment-details',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    title: 'Status',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return '<div class="mobile-content">' + data + '</div>';
                        }
                        return data;
                    },
                    className: 'status-cell',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'formatted_date',
                    name: 'created_at',
                    title: 'Date',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return '<div class="mobile-content"><small class="text-muted">' +
                                data + '</small></div>';
                        }
                        return data;
                    },
                    className: 'small text-muted',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'actions',
                    name: 'actions',
                    title: 'Actions',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return '<div class="mobile-content">' + data + '</div>';
                        }
                        return data;
                    },
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
                // Clear existing modals to prevent duplicates
                $('#rejectionModalsContainer').empty();

                // Re-initialize Bootstrap tooltips if any
                $('[data-bs-toggle="tooltip"]').tooltip();

                // Handle details modal buttons with improved error handling
                $(document).off('click', '.details-btn').on('click', '.details-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    let $btn = $(this);
                    let modalTarget = $btn.data('bs-target') || $btn.attr('data-bs-target');

                    console.log('Modal button clicked:', {
                        target: modalTarget,
                        button: $btn[0],
                        hasDetails: !!$btn.data('details')
                    });

                    // Validate modal target
                    if (!modalTarget) {
                        console.error('No modal target found');
                        return;
                    }

                    // Check if modal exists, if not create it
                    if ($(modalTarget).length === 0) {
                        let withdrawalId = modalTarget.replace('#detailsModal', '');
                        let withdrawalDetails = $btn.data('details') || $btn.attr('data-details');

                        if (withdrawalDetails) {
                            try {
                                // Parse the details data with error handling
                                let details;
                                if (typeof withdrawalDetails === 'string') {
                                    details = JSON.parse(withdrawalDetails);
                                } else if (typeof withdrawalDetails === 'object') {
                                    details = withdrawalDetails;
                                } else {
                                    throw new Error('Invalid withdrawal details format');
                                }

                                console.log('Parsed withdrawal details:', details);

                                // Determine status color class
                                let statusClass = '';
                                switch (details.status.toLowerCase()) {
                                    case 'pending':
                                        statusClass = 'bg-warning';
                                        break;
                                    case 'approved':
                                        statusClass = 'bg-success';
                                        break;
                                    case 'completed':
                                        statusClass = 'bg-success';
                                        break;
                                    case 'rejected':
                                        statusClass = 'bg-danger';
                                        break;
                                    default:
                                        statusClass = 'bg-secondary';
                                }

                                // Create the modal HTML
                                let modalHtml = `
                                <div class="modal fade" id="detailsModal${withdrawalId}" tabindex="-1"
                                     aria-labelledby="detailsModalLabel${withdrawalId}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="detailsModalLabel${withdrawalId}">
                                                    <i class="fas fa-info-circle text-primary me-2"></i>
                                                    Withdrawal Details - #${details.id}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <div class="card border-0" style="background-color: var(--bs-tertiary-bg);">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-muted mb-3">
                                                                    <i class="fas fa-exchange-alt me-2"></i>Transaction Info
                                                                </h6>
                                                                <p class="mb-2"><strong>ID:</strong> #${details.id}</p>
                                                                <p class="mb-2"><strong>Type:</strong> ${details.type}</p>
                                                                <p class="mb-2"><strong>Wallet:</strong> ${details.wallet}</p>
                                                                <p class="mb-2"><strong>Status:</strong> <span class="badge ${statusClass}">${details.status}</span></p>
                                                                <p class="mb-0"><strong>Date:</strong> ${details.date}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="card border-0" style="background-color: var(--bs-tertiary-bg);">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-muted mb-3">
                                                                    <i class="fas fa-dollar-sign me-2"></i>Amount Details
                                                                </h6>
                                                                <p class="mb-2"><strong>Requested Amount:</strong> $${details.amount}</p>
                                                                <p class="mb-2"><strong>Fee:</strong> ${details.fee}</p>
                                                                <p class="mb-0"><strong>Net Amount:</strong> <span class="text-success fw-bold">$${details.net_amount}</span></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="card border-0" style="background-color: var(--bs-tertiary-bg);">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-muted mb-3">
                                                                    <i class="fas fa-credit-card me-2"></i>Payment Details
                                                                </h6>
                                                                <p class="mb-0">${details.payment_details}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    ${details.branch_info ? `
                                                        <div class="col-12">
                                                            <div class="card border-0" style="background-color: var(--bs-tertiary-bg);">
                                                                <div class="card-body">
                                                                    <h6 class="card-title text-muted mb-3">
                                                                        <i class="fas fa-map-marker-alt me-2"></i>Branch Information
                                                                    </h6>
                                                                    <p class="mb-0">${details.branch_info}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    ` : ''}
                                                    ${details.rejection_reason ? `
                                                        <div class="col-12">
                                                            <div class="alert alert-danger">
                                                                <h6 class="alert-heading mb-2">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i>Rejection Reason
                                                                </h6>
                                                                <p class="mb-0">${details.rejection_reason}</p>
                                                            </div>
                                                        </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="fas fa-times me-1"></i>Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                                // Append modal to container
                                $('#rejectionModalsContainer').append(modalHtml);

                            } catch (parseError) {
                                console.error('Error parsing withdrawal details:', parseError);
                                alert(
                                    'Error displaying withdrawal details. Please refresh and try again.');
                                return;
                            }
                        } else {
                            console.error('No withdrawal details found');
                            alert('Withdrawal details not available.');
                            return;
                        }
                    }

                    // Show the modal using Bootstrap 5 syntax with proper delay and initialization
                    setTimeout(function() {
                        if ($(modalTarget).length > 0) {
                            try {
                                let modalElement = document.querySelector(modalTarget);
                                if (modalElement) {
                                    // Check if Bootstrap is available and use proper initialization
                                    if (typeof bootstrap !== 'undefined' && bootstrap
                                        .Modal) {
                                        // Dispose existing modal instance if any
                                        let existingModal = bootstrap.Modal.getInstance(
                                            modalElement);
                                        if (existingModal) {
                                            existingModal.dispose();
                                        }

                                        // Create new modal with correct Bootstrap 5 options
                                        let modal = new bootstrap.Modal(modalElement, {
                                            backdrop: 'static',
                                            keyboard: true,
                                            focus: true
                                        });
                                        modal.show();
                                    } else {
                                        // Fallback to jQuery/Bootstrap 4 method if Bootstrap 5 not available
                                        console.warn(
                                            'Bootstrap 5 not available, trying jQuery fallback'
                                            );
                                        if (typeof $.fn.modal !== 'undefined') {
                                            $(modalTarget).modal({
                                                backdrop: 'static',
                                                keyboard: true,
                                                show: true
                                            });
                                        } else {
                                            console.error('No modal library available');
                                        }
                                    }
                                } else {
                                    console.error('Modal element not found:', modalTarget);
                                }
                            } catch (error) {
                                console.error('Modal initialization error:', error);
                                // Last resort fallback
                                try {
                                    if (typeof $.fn.modal !== 'undefined') {
                                        $(modalTarget).modal('show');
                                    }
                                } catch (fallbackError) {
                                    console.error('Fallback modal error:', fallbackError);
                                    alert(
                                        'Unable to show modal details. Please refresh the page and try again.');
                                }
                            }
                        } else {
                            console.error('Modal target not found in DOM:', modalTarget);
                        }
                    }, 100);
                });

                // Set mobile data labels for better mobile display
                setMobileDataLabels();
            },
            initComplete: function() {
                // Custom styling for search input
                $('.dataTables_filter input').addClass('form-control').attr('placeholder',
                    'Search withdrawals...');
                $('.dataTables_length select').addClass('form-select');

                // Force mobile layout on initial load if mobile
                if (window.innerWidth <= 768) {
                    setTimeout(function() {
                        table.columns.adjust();
                        setMobileDataLabels();
                    }, 100);
                }
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

        // Function to set mobile data labels
        function setMobileDataLabels() {
            if (window.innerWidth <= 768) {
                $('#withdrawalhistorytable tbody tr').each(function() {
                    $(this).find('td').each(function(index) {
                        const columnHeaders = ['ID', 'Type', 'Wallet', 'Amount',
                            'Fee', 'Net Amount', 'Payment Details',
                            'Status', 'Date', 'Actions'
                        ];
                        $(this).attr('data-label', columnHeaders[index]);
                    });
                });
            }
        }

        // Handle window resize for mobile layout
        $(window).on('resize', function() {
            clearTimeout(window.resizeTimeout);
            window.resizeTimeout = setTimeout(function() {
                table.columns.adjust();
                setMobileDataLabels();
            }, 250);
        });

        // Auto-reload table every 30 seconds to check for status updates
        setInterval(function() {
            if (table && !table.processing()) {
                table.ajax.reload(null, false); // false = don't reset pagination
            }
        }, 30000);
    }

    // Initialize when DOM is ready and dependencies are loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Check if dependencies are loaded, if not wait a bit more
        if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
            initializeWithdrawalHistory();
        } else {
            // Wait for dependencies to load
            let attempts = 0;
            const maxAttempts = 50; // 5 seconds max wait
            const checkInterval = setInterval(function() {
                attempts++;
                if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
                    clearInterval(checkInterval);
                    initializeWithdrawalHistory();
                } else if (attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    console.error(
                    'Required dependencies (jQuery/DataTables) not loaded within timeout');
                }
            }, 100);
        }
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

    /* Mobile responsive filters */
    .filters-container {
        background: var(--bs-tertiary-bg);
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        .filters-container {
            padding: 0.75rem;
            margin-bottom: 1rem;
        }

        .filters-container .form-label {
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .filters-container .form-select-sm,
        .filters-container .form-control-sm {
            font-size: 0.8rem;
            padding: 0.375rem 0.5rem;
        }

        .card-header h4 {
            font-size: 1.2rem;
        }
    }

    /* Table responsive improvements */
    @media (max-width: 992px) {
        #withdrawalhistorytable {
            font-size: 0.75rem;
        }

        #withdrawalhistorytable .type-badge,
        #withdrawalhistorytable .badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.35rem;
        }

        #withdrawalhistorytable th,
        #withdrawalhistorytable td {
            padding: 0.5rem 0.25rem;
            vertical-align: middle;
        }

        /* Hide less important columns on mobile */
        #withdrawalhistorytable th:nth-child(5),
        /* Fee */
        #withdrawalhistorytable td:nth-child(5),
        #withdrawalhistorytable th:nth-child(7),
        /* Payment Details */
        #withdrawalhistorytable td:nth-child(7) {
            display: none;
        }
    }

    /* Mobile Card Style Layout */
    @media (max-width: 768px) {
        .table-responsive {
            border: none;
            overflow: visible;
        }

        #withdrawalhistorytable {
            border-collapse: separate;
            border-spacing: 0;
        }

        #withdrawalhistorytable thead {
            display: none;
        }

        #withdrawalhistorytable tbody tr {
            display: block;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
            margin-bottom: 1rem;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #withdrawalhistorytable tbody tr td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border: none;
            border-bottom: 1px solid var(--bs-border-color-translucent);
        }

        #withdrawalhistorytable tbody tr td:last-child {
            border-bottom: none;
        }

        #withdrawalhistorytable tbody tr td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--bs-secondary-color);
            font-size: 0.85rem;
            flex: 0 0 40%;
            text-align: left;
        }

        #withdrawalhistorytable tbody tr td .mobile-value {
            flex: 1;
            text-align: right;
            font-size: 0.9rem;
        }

        .dataTables_length,
        .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_length select,
        .dataTables_filter input {
            font-size: 0.9rem;
        }

        .dataTables_info,
        .dataTables_paginate {
            text-align: center;
            margin-top: 1rem;
        }
    }

    @media (max-width: 576px) {
        #withdrawalhistorytable tbody tr {
            padding: 0.75rem;
        }

        #withdrawalhistorytable tbody tr td::before {
            flex: 0 0 45%;
            font-size: 0.8rem;
        }

        #withdrawalhistorytable tbody tr td .mobile-value {
            font-size: 0.85rem;
        }

        .dataTables_paginate .paginate_button {
            padding: 0.4rem 0.6rem;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {

        #withdrawalhistorytable th,
        #withdrawalhistorytable td {
            padding: 0.3rem 0.15rem;
        }

        /* Show only essential columns on very small screens */
        #withdrawalhistorytable th:nth-child(6),
        /* Net Amount */
        #withdrawalhistorytable td:nth-child(6) {
            display: none;
        }

        .dataTables_info,
        .dataTables_paginate {
            font-size: 0.75rem;
            text-align: center;
            margin-top: 0.5rem;
        }

        .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.5rem;
            font-size: 0.7rem;
        }
    }

    /* Mobile Card Style Layout */
    @media (max-width: 768px) {
        .table-responsive {
            border: none;
            overflow: visible;
        }

        #withdrawalhistorytable {
            border-collapse: separate;
            border-spacing: 0;
            border: none;
        }

        #withdrawalhistorytable thead {
            display: none;
        }

        #withdrawalhistorytable tbody tr {
            display: block;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 12px;
            margin-bottom: 1rem;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        #withdrawalhistorytable tbody tr:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }

        #withdrawalhistorytable tbody tr td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 0;
            border: none;
            border-bottom: 1px solid var(--bs-border-color-translucent);
            min-height: 40px;
        }

        #withdrawalhistorytable tbody tr td:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        #withdrawalhistorytable tbody tr td:first-child {
            padding-top: 0;
        }

        #withdrawalhistorytable tbody tr td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--bs-secondary-color);
            font-size: 0.875rem;
            flex: 0 0 35%;
            text-align: left;
        }

        #withdrawalhistorytable tbody tr td .mobile-content {
            flex: 1;
            text-align: right;
            font-size: 0.9rem;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        /* Special styling for status badges */
        .status-cell .badge {
            font-size: 0.75rem;
        }

        /* Special styling for amounts */
        .amount-cell {
            font-weight: 600;
        }

        .dataTables_length,
        .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_info,
        .dataTables_paginate {
            text-align: center;
            margin-top: 1rem;
        }
    }

    @media (max-width: 576px) {
        #withdrawalhistorytable tbody tr {
            padding: 0.8rem;
        }

        #withdrawalhistorytable tbody tr td::before {
            flex: 0 0 40%;
            font-size: 0.8rem;
        }

        #withdrawalhistorytable tbody tr td .mobile-content {
            font-size: 0.85rem;
        }

        .dataTables_paginate .paginate_button {
            padding: 0.4rem 0.6rem;
            font-size: 0.8rem;
        }
    }

    /* Mobile responsive button styling */
    @media (max-width: 992px) {
        .rejection-btn {
            font-size: 0.7rem !important;
            padding: 0.2rem 0.4rem !important;
        }

        .btn-sm {
            font-size: 0.7rem !important;
            padding: 0.25rem 0.5rem !important;
        }
    }

    @media (max-width: 768px) {
        .rejection-btn {
            font-size: 0.65rem !important;
            padding: 0.15rem 0.3rem !important;
        }

        .btn-sm {
            font-size: 0.65rem !important;
            padding: 0.2rem 0.4rem !important;
        }
    }

    @media (max-width: 576px) {
        .rejection-btn {
            font-size: 0.6rem !important;
            padding: 0.1rem 0.25rem !important;
            line-height: 1.2 !important;
        }

        .btn-sm {
            font-size: 0.6rem !important;
            padding: 0.15rem 0.3rem !important;
            line-height: 1.2 !important;
        }

        /* Make button text shorter on very small screens */
        .btn-sm .fas {
            display: none !important;
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
