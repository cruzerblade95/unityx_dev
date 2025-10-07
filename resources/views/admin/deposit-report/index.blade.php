@extends('admin/layouts/layoutMaster')

@section('title', 'Deposit Report')

@section('vendor-style')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
    <!-- Ensure jQuery is loaded first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
@endsection

@section('page-style')
    <style>
        /* Custom styles for deposit report */
        .card-datatable .dt-buttons {
            margin-bottom: 0;
        }

        .dt-buttons .btn {
            margin: 0 0.25rem 0 0;
            border-radius: 0.375rem;
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0;
        }

        .dataTables_wrapper .dataTables_length select {
            min-width: 70px;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #566a7f;
            background-color: #f8f9fa;
        }

        .stats-card {
            transition: transform 0.2s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }



        .deposit-modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .deposit-modal-content {
            background-color: var(--bs-body-bg);
            margin: 5% auto;
            padding: 0;
            border: none;
            border-radius: 0.5rem;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .deposit-modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--bs-border-color);
            background: linear-gradient(135deg, var(--bs-primary) 0%, rgba(105, 108, 255, 0.8) 100%);
            color: white;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .deposit-modal-body {
            padding: 1.5rem;
        }

        .deposit-modal-close {
            color: white;
            float: right;
            font-size: 1.5rem;
            font-weight: bold;
            background: none;
            border: none;
            cursor: pointer;
        }

        .deposit-modal-close:hover,
        .deposit-modal-close:focus {
            color: rgba(255, 255, 255, 0.8);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--bs-border-color-translucent);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: var(--bs-secondary);
        }

        .detail-value {
            font-weight: 500;
        }

        /* Badge customization */
        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }

        /* DataTable custom styling */
        .dataTables_wrapper .dataTables_info {
            color: #566a7f;
            font-size: 0.875rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin: 0 0.125rem;
            border-radius: 0.375rem;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="fas fa-chart-bar me-2 text-primary"></i>
                    Deposit Report
                </h4>
                <p class="mb-0 text-muted">Track recent deposit activities from all sources</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Total Deposits</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $totalDeposits }}</h4>
                                </div>
                                <small class="mb-0 text-success">Last 30 days</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="fas fa-list fa-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Total Amount</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">${{ number_format($totalAmount, 2) }}</h4>
                                </div>
                                <small class="mb-0 text-success">USD value</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="fas fa-dollar-sign fa-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Crypto Deposits</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $cryptoDeposits }}</h4>
                                </div>
                                <small class="mb-0 text-info">Cryptocurrency</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="fas fa-coins fa-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Other Sources</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $marketplaceDeposits + $manualDeposits }}</h4>
                                </div>
                                <small class="mb-0 text-warning">Marketplace & Manual</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="fas fa-plus-circle fa-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Source Filter</label>
                        <select class="form-select" id="sourceFilter">
                            <option value="all">All Sources</option>
                            <option value="crypto_deposit">Crypto Deposit</option>
                            <option value="direct_deposit">Direct Deposit</option>
                            <option value="marketplace">Marketplace</option>
                            <option value="admin_manual">Admin Manual</option>
                            <option value="other_deposit">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" id="dateFrom" max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" id="dateTo" max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                <i class="fas fa-filter me-1"></i>Apply
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="fas fa-times me-1"></i>Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deposit Report Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-table me-2"></i>Recent Deposit Activities
                </h5>
                <div class="card-tools">
                    <button class="btn btn-outline-primary btn-sm" onclick="refreshTable()">
                        <i class="fas fa-refresh me-1"></i>Refresh
                    </button>
                </div>
            </div>
            <div class="card-datatable table-responsive">
                <table id="depositReportTable" class="datatables-basic table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Deposit Details Modal -->
    <div id="depositDetailsModal" class="deposit-modal">
        <div class="deposit-modal-content">
            <div class="deposit-modal-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Deposit Details
                </h5>
                <span class="deposit-modal-close" onclick="closeDepositModal()">&times;</span>
            </div>
            <div class="deposit-modal-body" id="depositModalBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading deposit details...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
    <script>
        // Initialize when everything is ready
        $(document).ready(function() {
            // Double check jQuery and DataTables are available
            if (typeof $ === 'undefined') {
                console.error('jQuery is not loaded');
                return;
            }

            if (typeof $.fn.DataTable === 'undefined') {
                console.error('DataTables is not loaded');
                setTimeout(function() {
                    location.reload();
                }, 2000); // Retry after 2 seconds
                return;
            }

            try {
                // Initialize DataTable
                var table = $('#depositReportTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('admin.deposit-report.list') }}',
                        data: function(d) {
                            d.source_filter = $('#sourceFilter').val();
                            d.date_from = $('#dateFrom').val();
                            d.date_to = $('#dateTo').val();
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'user_info',
                            name: 'user_info',
                            orderable: false
                        },
                        {
                            data: 'amount',
                            name: 'amount'
                        },
                        {
                            data: 'source',
                            name: 'source',
                            orderable: false
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [
                        [5, 'desc']
                    ], // Order by date descending
                    pageLength: 10,
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-end"f>>' +
                        '<"table-responsive"t>' +
                        '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    language: {
                        search: '',
                        searchPlaceholder: 'Search deposits...',
                        lengthMenu: '_MENU_ per page',
                        info: 'Showing _START_ to _END_ of _TOTAL_ deposits',
                        infoEmpty: 'No deposits found',
                        infoFiltered: '(filtered from _MAX_ total deposits)',
                        processing: '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><br>Loading deposit data...</div>'
                    },
                    responsive: true,
                    drawCallback: function(settings) {
                        // Initialize tooltips
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }
                });

                // Set default date range (last 30 days)
                var today = new Date();
                var thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
                $('#dateTo').val(today.toISOString().split('T')[0]);
                $('#dateFrom').val(thirtyDaysAgo.toISOString().split('T')[0]);

                // Make table variable global for other functions
                window.depositReportTable = table;

            } catch (error) {
                console.error('Error initializing DataTable:', error);
                // Fallback: show error message to user
                $('#depositReportTable').html(
                    '<tr><td colspan="7" class="text-center text-danger">Error loading table. Please refresh the page.</td></tr>'
                );
            }
        });

        function applyFilters() {
            if (window.depositReportTable && typeof window.depositReportTable.ajax !== 'undefined') {
                window.depositReportTable.ajax.reload();
            } else {
                console.error('DataTable not initialized');
                location.reload(); // Fallback to page reload
            }
        }

        function clearFilters() {
            if (typeof $ !== 'undefined') {
                $('#sourceFilter').val('all');
                $('#dateFrom').val('');
                $('#dateTo').val('');

                // Set default date range again
                var today = new Date();
                var thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
                $('#dateTo').val(today.toISOString().split('T')[0]);
                $('#dateFrom').val(thirtyDaysAgo.toISOString().split('T')[0]);
            }

            if (window.depositReportTable && typeof window.depositReportTable.ajax !== 'undefined') {
                window.depositReportTable.ajax.reload();
            } else {
                console.error('DataTable not initialized');
                location.reload(); // Fallback to page reload
            }
        }

        function refreshTable() {
            if (window.depositReportTable && typeof window.depositReportTable.ajax !== 'undefined') {
                window.depositReportTable.ajax.reload(null, false);
            } else {
                console.error('DataTable not initialized');
                location.reload(); // Fallback to page reload
            }
        }

        function viewDepositDetails(depositId) {
            // Open modal
            document.getElementById('depositDetailsModal').style.display = 'block';

            // Show loading state
            document.getElementById('depositModalBody').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading deposit details...</p>
        </div>
    `;

            // For now, show a simple details view - can be enhanced later
            setTimeout(function() {
                document.getElementById('depositModalBody').innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Deposit ID:</strong> ${depositId}
                <br><br>
                <small class="text-muted">
                    Detailed deposit information can be accessed through the respective management pages:
                    <ul class="mt-2 mb-0">
                        <li>Crypto deposits: <a href="{{ route('admin.nowpayments.index') }}">NOWPayments Management</a></li>
                        <li>User wallets: <a href="{{ route('admin.wallets.index') }}">Wallet Management</a></li>
                        <li>Transaction history: Available in user profiles</li>
                    </ul>
                </small>
            </div>
        `;
            }, 500);
        }

        function closeDepositModal() {
            document.getElementById('depositDetailsModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('depositDetailsModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
@endsection
