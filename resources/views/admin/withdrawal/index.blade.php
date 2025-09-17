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
                dataTable: (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined') ? typeof jQuery.fn
                    .DataTable !== 'undefined' : false
            });

            if (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined' && typeof jQuery.fn.DataTable !==
                'undefined') {
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
                        data: function(d) {
                            d.status = $('#statusFilter').val();
                            d.date_from = $('#dateFromFilter').val();
                            d.date_to = $('#dateToFilter').val();
                        },
                        error: function(xhr, error, thrown) {
                            console.error('DataTables AJAX Error:', xhr, error, thrown);
                            alert('Error loading withdrawal data: ' + xhr.responseText);
                        }
                    },
                    columns: [{
                            data: 'id',
                            name: 'id',
                            title: 'ID'
                        },
                        {
                            data: 'user_info',
                            name: 'user_info',
                            title: 'User Info',
                            render: function(data, type, row) {
                                if (!data) {
                                    return '<div class="user-info"><strong>Unknown User</strong></div>';
                                }
                                return '<div class="user-info">' +
                                    '<strong>' + (data.firstname || 'Unknown') + ' ' + (data
                                        .lastname || 'User') + '</strong><br>' +
                                    '<small class="text-muted">@' + (data.username || 'unknown') +
                                    '</small><br>' +
                                    '<small class="text-muted">' + (data.email ||
                                        'unknown@email.com') + '</small>' +
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
                            title: 'Withdrawal Info',
                            render: function(data, type, row) {
                                if (!data) {
                                    return '<div class="withdrawal-info">N/A</div>';
                                }

                                let html = '<div class="withdrawal-info">';

                                if (data.type) {
                                    html += '<strong class="text-primary">' + data.type +
                                        '</strong><br>';
                                }

                                if (data.currency) {
                                    html += '<small class="text-muted">Currency: ' + data.currency +
                                        '</small><br>';
                                }

                                if (data.wallet_address) {
                                    html += '<small class="text-muted">Address: ' + data
                                        .wallet_address + '</small>';
                                } else if (data.card_number) {
                                    html += '<small class="text-muted">Card: ' + data.card_number +
                                        '</small>';
                                } else if (data.branch && data.manager) {
                                    // For gold withdrawals with branch assignment, show branch info instead of bank info
                                    html +=
                                        '<small class="text-success"><i class="fas fa-map-marker-alt me-1"></i>Branch: ' +
                                        data.branch + '</small>';
                                    html += '<br><small class="text-success">Manager: ' + data
                                        .manager + '</small>';
                                } else if (data.bank_name && data.bank_name !== 'Not provided') {
                                    // Only show bank info if it's not "Not provided"
                                    html += '<small class="text-muted">Bank: ' + data.bank_name +
                                        '</small>';
                                    if (data.account_number && data.account_number !==
                                        'Not provided') {
                                        html += '<br><small class="text-muted">Account: ' + data
                                            .account_number + '</small>';
                                    }
                                } else if (data.type === 'Gold Bank') {
                                    // For gold withdrawals without branch assignment
                                    html +=
                                        '<small class="text-warning">Awaiting branch assignment</small>';
                                }

                                html += '</div>';
                                return html;
                            },
                            orderable: false
                        },
                        {
                            data: 'status',
                            name: 'status',
                            title: 'Status',
                            render: function(data, type, row) {
                                var badgeClass = '';
                                switch (data) {
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
                                return '<span class="badge ' + badgeClass + '">' + data.charAt(0)
                                    .toUpperCase() + data.slice(1) + '</span>';
                            }
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            title: 'Date',
                            render: function(data, type, row) {
                                return new Date(data).toLocaleDateString() + '<br>' +
                                    '<small class="text-muted">' + new Date(data)
                                    .toLocaleTimeString() + '</small>';
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
                    order: [
                        [0, 'desc']
                    ],
                    pageLength: 25,
                    responsive: true,
                    dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    buttons: [{
                        extend: 'collection',
                        className: 'btn btn-label-primary dropdown-toggle me-2',
                        text: '<i class="fas fa-download me-1"></i>Export',
                        buttons: [{
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
                    }],
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
                                        Swal.fire('Approved!', response.message,
                                            'success');
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

                // Gold withdrawal approval with branch assignment
                $(document).on('click', '.gold-approve-btn', function() {
                    var withdrawalId = $(this).data('id');

                    // Load withdrawal details
                    $.ajax({
                        url: "/admin/withdrawals/" + withdrawalId,
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                var data = response.data;

                                // Populate withdrawal summary
                                var summaryHtml = `
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Withdrawal ID:</strong> #${data.id}</p>
                                            <p><strong>User:</strong> ${data.user.firstname} ${data.user.lastname}</p>
                                            <p><strong>Username:</strong> @${data.user.username}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Amount:</strong> $${parseFloat(data.amount).toFixed(2)}</p>
                                            <p><strong>Net Amount:</strong> <strong class="text-success">$${parseFloat(data.net_amount).toFixed(2)}</strong></p>
                                            <p><strong>Wallet:</strong> ${(data.wallet_type || 'profit').charAt(0).toUpperCase() + (data.wallet_type || 'profit').slice(1)}</p>
                                        </div>
                                    </div>
                                `;

                                $('#withdrawalDetails').html(summaryHtml);
                                $('#branchAssignmentForm').data('withdrawal-id', withdrawalId);

                                // Reset form
                                $('#branchSelect').val('');
                                $('#branchManagerInfo').hide();
                                $('#adminNotes').val('');
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'Failed to load withdrawal details', 'error');
                        }
                    });
                });

                // Handle branch selection
                $(document).on('change', '#branchSelect', function() {
                    var selectedOption = $(this).find(':selected');
                    if (selectedOption.val()) {
                        var managerName = selectedOption.data('manager');
                        var managerPhone = selectedOption.data('phone');

                        $('#managerName').text(managerName);
                        $('#managerPhone').text(managerPhone);
                        $('#branchManagerInfo').show();
                    } else {
                        $('#branchManagerInfo').hide();
                    }
                });

                // Handle branch assignment form submission
                $(document).on('submit', '#branchAssignmentForm', function(e) {
                    e.preventDefault();

                    var withdrawalId = $(this).data('withdrawal-id');
                    var selectedBranch = $('#branchSelect').val();
                    var adminNotes = $('#adminNotes').val();

                    if (!selectedBranch) {
                        Swal.fire('Error!', 'Please select a branch', 'error');
                        return;
                    }

                    var selectedOption = $('#branchSelect').find(':selected');
                    var branchData = {
                        branch_key: selectedBranch,
                        branch_name: selectedOption.text(),
                        manager_name: selectedOption.data('manager'),
                        manager_phone: selectedOption.data('phone'),
                        admin_notes: adminNotes,
                        _token: "{{ csrf_token() }}"
                    };

                    $.ajax({
                        url: "/admin/withdrawals/" + withdrawalId + "/approve-gold",
                        type: 'POST',
                        data: branchData,
                        success: function(response) {
                            if (response.success) {
                                $('#branchAssignmentModal').modal('hide');
                                Swal.fire('Success!', response.message, 'success');
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
                });

                // Reject withdrawal
                $(document).on('click', '.reject-btn', function() {
                    var withdrawalId = $(this).data('id');

                    Swal.fire({
                        title: 'Reject Withdrawal',
                        html: `
                <div class="mb-3">
                    <label for="rejection-reason" class="form-label">Rejection Reason *</label>
                    <textarea id="rejection-reason" class="form-control" rows="4"
                              placeholder="Please provide a reason for rejection..." required></textarea>
                </div>
            `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Reject Withdrawal',
                        cancelButtonText: 'Cancel',
                        preConfirm: () => {
                            const reason = document.getElementById('rejection-reason').value;
                            if (!reason.trim()) {
                                Swal.showValidationMessage('Please provide a rejection reason');
                                return false;
                            }
                            return reason;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "/admin/withdrawals/" + withdrawalId + "/reject",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    rejection_reason: result.value
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire('Rejected!', response.message,
                                            'success');
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
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                var data = response.data;
                                var details = data.withdrawal_details || {};

                                var modalContent =
                                    `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Withdrawal Information</h6>
                                <p><strong>Withdrawal ID:</strong> #${data.id}</p>
                                <p><strong>Type:</strong> <span class="badge bg-info">${details.type || 'Unknown'}</span></p>
                                <p><strong>Wallet Used:</strong> <span class="badge bg-secondary">${(data.wallet_type || 'profit').charAt(0).toUpperCase() + (data.wallet_type || 'profit').slice(1)}</span></p>
                                <p><strong>Amount:</strong> $${parseFloat(data.amount).toFixed(2)}</p>
                                <p><strong>Fee Amount:</strong> $${parseFloat(data.tax_amount).toFixed(2)}</p>
                                <p><strong>Net Amount:</strong> <strong class="text-success">$${parseFloat(data.net_amount).toFixed(2)}</strong></p>
                                <p><strong>Status:</strong> <span class="badge bg-${data.status === 'pending' ? 'warning' : data.status === 'approved' ? 'success' : 'danger'}">${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</span></p>
                                <p><strong>Created At:</strong> ${data.created_at}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i>User Information</h6>
                                <p><strong>Name:</strong> ${data.user.firstname} ${data.user.lastname}</p>
                                <p><strong>Username:</strong> @${data.user.username}</p>
                                <p><strong>Email:</strong> ${data.user.email}</p>

                                <h6 class="text-primary mb-3 mt-4"><i class="fas fa-credit-card me-2"></i>Payment Details</h6>`;

                                // Add specific payment details based on withdrawal type
                                if (details.type === 'Crypto Withdrawal') {
                                    modalContent +=
                                        `
                                <p><strong>Currency:</strong> ${details.currency || 'N/A'}</p>
                                <p><strong>Network:</strong> ${details.network || 'N/A'}</p>
                                <p><strong>Wallet Address:</strong><br><code class="small">${details.wallet_address || 'Not provided'}</code></p>`;
                                } else if (details.type === 'Crypto Bank Card Withdrawal') {
                                    modalContent +=
                                        `
                                <p><strong>Card Number:</strong> ${details.card_number || 'Not provided'}</p>
                                <p><strong>Cardholder Name:</strong> ${details.cardholder_name || 'Not provided'}</p>
                                <p><strong>Account Number:</strong> ${details.account_number || 'Not provided'}</p>
                                <p><strong>Card Wallet Address:</strong><br><code class="small">${details.card_wallet_address || 'Not provided'}</code></p>`;
                                } else if (details.type === 'Gold Bank Withdrawal') {
                                    modalContent += `
                                <p><strong>Bank Name:</strong> ${details.bank_name || 'Not provided'}</p>
                                <p><strong>Account Number:</strong> ${details.account_number || 'Not provided'}</p>
                                <p><strong>Account Holder:</strong> ${details.account_holder || 'Not provided'}</p>`;

                                    // Add branch assignment information if available
                                    if (details.branch_assigned) {
                                        modalContent += `
                                <div class="mt-3 p-3 bg-light border-start border-success border-4">
                                    <h6 class="text-success mb-2"><i class="fas fa-map-marker-alt me-2"></i>Branch Assignment</h6>
                                    <p class="mb-1"><strong>Branch:</strong> ${details.branch_name}</p>
                                    <p class="mb-1"><strong>Manager:</strong> ${details.branch_manager_name}</p>
                                    <p class="mb-1"><strong>Contact:</strong> ${details.branch_manager_phone}</p>
                                    ${details.branch_assigned_at ? '<p class="mb-0 small text-muted"><strong>Assigned:</strong> ' + details.branch_assigned_at + '</p>' : ''}
                                </div>`;
                                    } else if (data.status === 'approved') {
                                        modalContent += `
                                <div class="mt-3 p-3 bg-warning bg-opacity-10 border-start border-warning border-4">
                                    <p class="mb-0 text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Branch not yet assigned</p>
                                </div>`;
                                    }
                                }

                                modalContent += `
                            </div>
                        </div>`;

                                if (data.reference || data.note || data.rejection_reason) {
                                    modalContent +=
                                        `
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3"><i class="fas fa-sticky-note me-2"></i>Additional Information</h6>`;

                                    if (data.reference) {
                                        modalContent +=
                                            `<p><strong>Reference:</strong> ${data.reference}</p>`;
                                    }

                                    if (data.note) {
                                        modalContent +=
                                            `<p><strong>Note:</strong> ${data.note}</p>`;
                                    }

                                    if (data.rejection_reason) {
                                        modalContent += `<div class="alert alert-danger mt-3">
                                <strong><i class="fas fa-exclamation-triangle me-2"></i>Rejection Reason:</strong><br>
                                ${data.rejection_reason}
                            </div>`;
                                    }

                                    modalContent += `
                            </div>
                        </div>`;
                                }

                                $('#viewModalBody').html(modalContent);
                                $('#viewModal').modal('show');
                            }
                        },
                        error: function(xhr) {
                            console.log('AJAX Error:', xhr);
                            console.log('Status:', xhr.status);
                            console.log('Response Text:', xhr.responseText);

                            // Try to parse the response even if jQuery failed due to BOM
                            if (xhr.status === 200 && xhr.responseText) {
                                try {
                                    // Remove BOM characters and parse JSON
                                    var cleanText = xhr.responseText.replace(/^\uFEFF/, '')
                                        .replace(/^\ufeff/, '');
                                    var response = JSON.parse(cleanText);

                                    if (response.success) {
                                        var data = response.data;
                                        var details = data.withdrawal_details || {};

                                        var modalContent = `
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Withdrawal Information</h6>
                                                    <p><strong>Withdrawal ID:</strong> #${data.id}</p>
                                                    <p><strong>Type:</strong> <span class="badge bg-info">${details.type || 'Unknown'}</span></p>
                                                    <p><strong>Wallet Used:</strong> <span class="badge bg-secondary">${(data.wallet_type || 'profit').charAt(0).toUpperCase() + (data.wallet_type || 'profit').slice(1)}</span></p>
                                                    <p><strong>Amount:</strong> $${parseFloat(data.amount).toFixed(2)}</p>
                                                    <p><strong>Fee Amount:</strong> $${parseFloat(data.tax_amount).toFixed(2)}</p>
                                                    <p><strong>Net Amount:</strong> <strong class="text-success">$${parseFloat(data.net_amount).toFixed(2)}</strong></p>
                                                    <p><strong>Status:</strong>
                                                        <span class="badge ${data.status === 'approved' ? 'bg-success' : data.status === 'pending' ? 'bg-warning' : 'bg-danger'}">
                                                            ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}
                                                        </span>
                                                    </p>
                                                    <p><strong>Date:</strong> ${data.created_at}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i>User Information</h6>
                                                    <p><strong>Username:</strong> @${data.user.username}</p>
                                                    <p><strong>Name:</strong> ${data.user.firstname} ${data.user.lastname}</p>
                                                    <p><strong>Email:</strong> ${data.user.email}</p>

                                                    ${data.reference ? `<p><strong>Reference:</strong> ${data.reference}</p>` : ''}
                                                    ${data.note ? `<p><strong>Note:</strong> ${data.note}</p>` : ''}
                                                    ${data.rejection_reason ? `<p><strong>Rejection Reason:</strong> <span class="text-danger">${data.rejection_reason}</span></p>` : ''}
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-12">`;

                                        if (details.type === 'Crypto Withdrawal') {
                                            modalContent += `
                                                <h6 class="text-info mb-3"><i class="fas fa-coins me-2"></i>Crypto Details</h6>
                                                <p><strong>Currency:</strong> ${details.currency || 'Unknown'}</p>
                                                <p><strong>Wallet Address:</strong> <code class="small">${details.wallet_address || 'Not provided'}</code></p>
                                                <p><strong>Network:</strong> ${details.network || 'BEP20'}</p>`;
                                        } else if (details.type ===
                                            'Crypto Bank Card Withdrawal') {
                                            modalContent +=
                                                `
                                                <h6 class="text-warning mb-3"><i class="fas fa-credit-card me-2"></i>Bank Card Details</h6>
                                                <p><strong>Card Number:</strong> ${details.card_number || 'Not provided'}</p>
                                                <p><strong>Cardholder:</strong> ${details.cardholder_name || 'Not provided'}</p>
                                                <p><strong>Account Number:</strong> ${details.account_number || 'Not provided'}</p>`;
                                        } else if (details.type === 'Gold Bank Withdrawal') {
                                            modalContent +=
                                                `
                                                <h6 class="text-success mb-3"><i class="fas fa-coins me-2"></i>Gold Withdrawal Details</h6>
                                                <p><strong>Bank Name:</strong> ${details.bank_name || 'Not provided'}</p>
                                                <p><strong>Account Number:</strong> ${details.account_number || 'Not provided'}</p>
                                                <p><strong>Account Holder:</strong> ${details.account_holder || 'Not provided'}</p>`;

                                            if (details.branch_assigned) {
                                                modalContent += `
                                                    <div class="mt-3 p-3 bg-light border-start border-success border-4">
                                                        <h6 class="text-success mb-2"><i class="fas fa-map-marker-alt me-2"></i>Branch Assignment</h6>
                                                        <p class="mb-1"><strong>Branch:</strong> ${details.branch_name}</p>
                                                        <p class="mb-1"><strong>Manager:</strong> ${details.branch_manager_name}</p>
                                                        <p class="mb-1"><strong>Contact:</strong> ${details.branch_manager_phone}</p>
                                                        ${details.branch_assigned_at ? `<p class="mb-0"><strong>Assigned:</strong> ${details.branch_assigned_at}</p>` : ''}
                                                    </div>`;
                                            } else {
                                                modalContent += `
                                                    <div class="mt-3 p-3 bg-warning bg-opacity-10 border-start border-warning border-4">
                                                        <p class="text-warning mb-0"><i class="fas fa-clock me-2"></i>Branch assignment pending</p>
                                                    </div>`;
                                            }
                                        }

                                        modalContent += `
                                                </div>
                                            </div>`;

                                        $('#viewModalBody').html(modalContent);
                                        $('#viewModal').modal('show');
                                        return; // Success, don't show error
                                    }
                                } catch (e) {
                                    console.log('Failed to parse response as JSON:', e);
                                }
                            }

                            Swal.fire('Error!',
                                'Failed to load withdrawal details. Check console for details.',
                                'error');
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
                        <th>Withdrawal Info</th>
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

    <!-- Branch Assignment Modal for Gold Withdrawals -->
    <div class="modal fade" id="branchAssignmentModal" tabindex="-1" aria-labelledby="branchAssignmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="branchAssignmentModalLabel">
                        <i class="fas fa-coins me-2 text-warning"></i>Approve Gold Withdrawal & Assign Branch
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="branchAssignmentForm">
                    <div class="modal-body">
                        <div id="withdrawalSummary" class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>Withdrawal Summary
                                </h6>
                                <div id="withdrawalDetails">
                                    <!-- Withdrawal details will be populated here -->
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="branchSelect" class="form-label">
                                <i class="fas fa-map-marker-alt me-2"></i>Select Branch <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="branchSelect" name="branch" required>
                                <option value="">Choose a branch...</option>
                                @foreach (config('gold_branches.branches') as $key => $branch)
                                    <option value="{{ $key }}" data-manager="{{ $branch['manager_name'] }}"
                                        data-phone="{{ $branch['manager_phone'] }}">
                                        {{ $branch['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="branchManagerInfo" class="card border-success mb-4" style="display: none;">
                            <div class="card-body">
                                <h6 class="card-title text-success">
                                    <i class="fas fa-user-tie me-2"></i>Branch Manager Information
                                </h6>
                                <div class="row">
                                    <div class="col-md-8">
                                        <p class="mb-1"><strong>Manager Name:</strong></p>
                                        <p class="mb-2" id="managerName">-</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Contact:</strong></p>
                                        <p class="mb-2" id="managerPhone">-</p>
                                    </div>
                                </div>
                                <div class="alert alert-info mb-0">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        The branch manager will be contacted automatically after approval.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="adminNotes" class="form-label">
                                <i class="fas fa-sticky-note me-2"></i>Admin Notes (Optional)
                            </label>
                            <textarea class="form-control" id="adminNotes" name="admin_notes" rows="3"
                                placeholder="Any special instructions or notes for this gold withdrawal..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Approve & Assign Branch
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
