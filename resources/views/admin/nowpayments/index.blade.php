@extends('admin.layouts.layoutMaster')

@section('title', 'NOWPayments Payment Details')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleave-zen/cleave-zen.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
    <script>
        // Set base URL for DataTables
        window.baseUrl = '{{ url('/') }}/';
    </script>
    @vite('resources/js/admin-nowpayments-list.js')
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">NOWPayments Payment Details</h4>
            <button class="btn btn-primary" onclick="testApiConnection()">
                <i class="fas fa-plug"></i> Test API Connection
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-6 mb-6">
            <div class="col-sm-6 col-xl-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Total</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $totalDeposits }}</h4>
                                </div>
                                <small class="mb-0">All deposits</small>
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
            <div class="col-sm-6 col-xl-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Pending</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $pendingDeposits }}</h4>
                                </div>
                                <small class="mb-0">Awaiting approval</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="fas fa-clock fa-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Completed</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $completedDeposits }}</h4>
                                </div>
                                <small class="mb-0">Successfully processed</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="fas fa-check fa-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Partial Paid</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $partiallyPaidDeposits }}</h4>
                                </div>
                                <small class="mb-0">Partially paid</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Expired</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ $expiredDeposits }}</h4>
                                </div>
                                <small class="mb-0">Payment expired</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="fas fa-times-circle fa-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span class="text-heading">Total Amount</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">${{ number_format($totalAmount, 2) }}</h4>
                                </div>
                                <small class="mb-0">All deposits combined</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-dark">
                                    <i class="fas fa-dollar-sign fa-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deposits List Table -->
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">All Deposit Records</h5>
                <div class="d-flex justify-content-between align-items-center row pt-4 gap-4 gap-md-0">
                    <div class="col-md-4 custom-deposit-status-filter">
                        <label class="form-label small">Filter by Status:</label>
                        <select class="form-select" id="CustomDepositStatus">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="partially_paid">Partially Paid</option>
                            <option value="expired">Expired</option>
                            <option value="failed">Failed</option>
                            <option value="processing">Processing</option>
                        </select>
                    </div>
                    <div class="col-md-4 custom-payment-method-filter">
                        <label class="form-label small">Filter by Payment Method:</label>
                        <select class="form-select" id="CustomPaymentMethod">
                            <option value="">All Payment Methods</option>
                            <option value="manual">Manual</option>
                            <option value="nowpayments">NOWPayments</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <!-- Additional filters can be added here -->
                    </div>
                </div>
            </div>
            <div class="card-datatable table-responsive">
                <table class="datatables-deposits table border-top">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Deposit Details Modal Overlay -->
    <div id="depositDetailsModal" class="deposit-modal-overlay" style="display: none;">
        <div class="deposit-modal-content">
            <div class="deposit-modal-header">
                <h5 class="deposit-modal-title">
                    <i class="fas fa-info-circle"></i> Deposit Details
                </h5>
                <button type="button" class="deposit-modal-close" onclick="closeDepositModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="deposit-modal-body" id="depositModalBody">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading deposit details...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Deposit Modal -->
    <div id="rejectDepositModal" class="deposit-modal-overlay" style="display: none;">
        <div class="deposit-modal-content">
            <div class="deposit-modal-header">
                <h5 class="deposit-modal-title">
                    <i class="fas fa-times-circle text-danger"></i> Reject Deposit
                </h5>
                <button type="button" class="deposit-modal-close" onclick="closeRejectModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="deposit-modal-body">
                <div class="mb-3">
                    <h6>Deposit Information</h6>
                    <div class="bg-light p-3 rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>User:</strong> <span id="rejectUserName">-</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Amount:</strong> $<span id="rejectAmount">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="rejectReason" class="form-label">
                        <strong>Reason for Rejection <span class="text-danger">*</span></strong>
                    </label>
                    <select class="form-select mb-2" id="rejectReasonSelect" onchange="handleReasonSelect()">
                        <option value="">Select a reason...</option>
                        <option value="invalid_payment_proof">Invalid payment proof/transaction hash</option>
                        <option value="insufficient_payment">Insufficient payment amount</option>
                        <option value="duplicate_transaction">Duplicate transaction</option>
                        <option value="suspicious_activity">Suspicious activity detected</option>
                        <option value="incorrect_wallet_address">Incorrect wallet address used</option>
                        <option value="payment_not_found">Payment transaction not found</option>
                        <option value="compliance_issues">Compliance/KYC issues</option>
                        <option value="custom">Custom reason (specify below)</option>
                    </select>
                    <textarea class="form-control" id="rejectReason" rows="3" placeholder="Enter detailed reason for rejection..."
                        required></textarea>
                    <small class="text-muted">This reason will be sent to the user via notification.</small>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This action cannot be undone. The user will be notified of the rejection with
                    the reason provided.
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary flex-fill" onclick="closeRejectModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger flex-fill" onclick="confirmRejectDeposit()"
                        id="confirmRejectBtn">
                        <i class="fas fa-ban"></i> Reject Deposit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .deposit-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .deposit-modal-content {
            background: var(--bs-body-bg, #ffffff);
            color: var(--bs-body-color, #212529);
            border-radius: 8px;
            border: 1px solid var(--bs-border-color, #dee2e6);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .deposit-modal-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--bs-border-color, #dee2e6);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .deposit-modal-body {
            padding: 1.5rem;
        }

        .deposit-modal-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            color: var(--bs-secondary, #6c757d);
        }

        .deposit-modal-close:hover {
            color: var(--bs-danger, #dc3545);
        }

        .detail-section {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: var(--bs-gray-50, #f8f9fa);
            border-radius: 0.375rem;
            border-left: 4px solid var(--bs-primary, #007bff);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.25rem 0;
        }

        .detail-label {
            font-weight: 600;
            color: var(--bs-gray-700, #495057);
        }

        .detail-value {
            color: var(--bs-gray-900, #212529);
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background-color: #d1edff;
            color: #0c5460;
        }

        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-partially-paid {
            background-color: #e2f3ff;
            color: #0066cc;
        }

        .status-expired {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-processing {
            background-color: #e7f3ff;
            color: #004085;
        }

        .nowpayments-status {
            border-left-color: var(--bs-success, #28a745);
        }

        .nowpayments-error {
            border-left-color: var(--bs-danger, #dc3545);
        }

        /* Hide any automatically generated DataTable filters that conflict with our custom ones */
        .custom-deposit-status-filter select:not(#CustomDepositStatus),
        .custom-payment-method-filter select:not(#CustomPaymentMethod) {
            display: none !important;
        }

        /* Ensure our custom filters are properly styled */
        #CustomDepositStatus,
        #CustomPaymentMethod {
            display: block !important;
        }
    </style>

    <script>
        let currentRejectDepositId = null;

        function testApiConnection() {
            console.log('Testing NOWPayments API connection...');

            fetch('/admin/nowpayments/test-api', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    console.log('API test response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('API test response data:', data);

                    let message = 'NOWPayments API Test Results:\n\n';

                    if (data.success) {
                        message += '✅ API Connection: SUCCESS\n';
                        message += `📋 Test Result: ${JSON.stringify(data.test_result, null, 2)}\n`;
                        message += `⚙️ API Configured: ${data.api_configured ? 'Yes' : 'No'}\n`;
                        message += `🧪 Sandbox Mode: ${data.sandbox_mode ? 'Yes' : 'No'}\n`;
                    } else {
                        message += '❌ API Connection: FAILED\n';
                        message += `Error: ${data.message}\n`;
                    }

                    alert(message);
                })
                .catch(error => {
                    console.error('API test error:', error);
                    alert(`Network error during API test:\n${error.message || error}`);
                });
        }

        function showDepositDetails(depositId, button) {
            console.log('Fetching deposit details for ID:', depositId);

            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            button.disabled = true;

            // Show modal with loading state
            openDepositModal();

            fetch(`/admin/nowpayments/deposit-details/${depositId}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    console.log('Deposit details response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Deposit details response data:', data);

                    if (data.success) {
                        const deposit = data.deposit;

                        // Build HTML content for modal
                        let modalContent = '';

                        // User Information Section
                        modalContent += `
                        <div class="detail-section">
                            <h6><i class="fas fa-user"></i> User Information</h6>
                            <div class="detail-row">
                                <span class="detail-label">User:</span>
                                <span class="detail-value">${deposit.user.name} (${deposit.user.email})</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Username:</span>
                                <span class="detail-value">${deposit.user.username}</span>
                            </div>
                        </div>
                    `;

                        // Payment Information Section
                        const statusClass = deposit.status === 'completed' ? 'status-completed' :
                            (deposit.status === 'pending' ? 'status-pending' : 'status-failed');

                        modalContent += `
                        <div class="detail-section">
                            <h6><i class="fas fa-credit-card"></i> Payment Information</h6>
                            <div class="detail-row">
                                <span class="detail-label">Deposit ID:</span>
                                <span class="detail-value">#${deposit.id}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Amount:</span>
                                <span class="detail-value">$${deposit.amount}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Net Amount:</span>
                                <span class="detail-value">$${deposit.net_amount || 'N/A'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Tax Amount:</span>
                                <span class="detail-value">$${deposit.tax_amount || 'N/A'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status:</span>
                                <span class="detail-value">
                                    <span class="status-badge ${statusClass}">${deposit.status}</span>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment Method:</span>
                                <span class="detail-value">${deposit.payment_method || 'N/A'}</span>
                            </div>
                        </div>
                    `;

                        // Crypto Information Section (if available)
                        if (deposit.crypto_currency) {
                            modalContent += `
                            <div class="detail-section">
                                <h6><i class="fab fa-bitcoin"></i> Crypto Information</h6>
                                <div class="detail-row">
                                    <span class="detail-label">Currency:</span>
                                    <span class="detail-value">${deposit.crypto_currency}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Crypto Amount:</span>
                                    <span class="detail-value">${deposit.crypto_amount || 'N/A'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Payment Address:</span>
                                    <span class="detail-value">${deposit.payment_address || 'N/A'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Unique Address:</span>
                                    <span class="detail-value">${deposit.unique_address || 'N/A'}</span>
                                </div>
                                ${deposit.address_memo ? `
                                                                    <div class="detail-row">
                                                                        <span class="detail-label">Address Memo:</span>
                                                                        <span class="detail-value">${deposit.address_memo}</span>
                                                                    </div>
                                                                    ` : ''}
                            </div>
                        `;
                        }

                        // Additional Information Section
                        modalContent += `
                        <div class="detail-section">
                            <h6><i class="fas fa-info-circle"></i> Additional Information</h6>
                            <div class="detail-row">
                                <span class="detail-label">Reference:</span>
                                <span class="detail-value">${deposit.reference || 'N/A'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Note:</span>
                                <span class="detail-value">${deposit.note || 'N/A'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Created:</span>
                                <span class="detail-value">${new Date(deposit.created_at).toLocaleString()}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Updated:</span>
                                <span class="detail-value">${new Date(deposit.updated_at).toLocaleString()}</span>
                            </div>
                        </div>
                    `;

                        // NOWPayments Status Section
                        const nowpaymentsClass = data.payment_found_in_nowpayments ? 'nowpayments-status' :
                            'nowpayments-error';
                        modalContent += `
                        <div class="detail-section ${nowpaymentsClass}">
                            <h6><i class="fas fa-link"></i> NOWPayments Status</h6>
                            <div class="detail-row">
                                <span class="detail-label">Has Payment ID:</span>
                                <span class="detail-value">${data.has_payment_id ? 'Yes' : 'No'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Found in NOWPayments:</span>
                                <span class="detail-value">${data.payment_found_in_nowpayments ? 'Yes' : 'No'}</span>
                            </div>
                    `;

                        if (data.nowpayments_data) {
                            modalContent += `
                            <div class="detail-row">
                                <span class="detail-label">NOWPayments Status:</span>
                                <span class="detail-value">${data.nowpayments_data.payment_status || 'Unknown'}</span>
                            </div>
                        `;
                            if (data.nowpayments_data.order_id) {
                                modalContent += `
                                <div class="detail-row">
                                    <span class="detail-label">Order ID:</span>
                                    <span class="detail-value">${data.nowpayments_data.order_id}</span>
                                </div>
                            `;
                            }
                        }

                        modalContent += `</div>`;

                        // Update modal content
                        document.getElementById('depositModalBody').innerHTML = modalContent;

                    } else {
                        document.getElementById('depositModalBody').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Error:</strong> ${data.message || 'Failed to load deposit details'}
                        </div>
                    `;
                    }
                })
                .catch(error => {
                    console.error('Error fetching deposit details:', error);
                    document.getElementById('depositModalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Network Error:</strong> Failed to fetch deposit details. Please try again.
                    </div>
                `;
                })
                .finally(() => {
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                });
        }

        function openDepositModal() {
            document.getElementById('depositDetailsModal').style.display = 'flex';
        }

        function closeDepositModal() {
            document.getElementById('depositDetailsModal').style.display = 'none';
        }

        function showRejectModal(depositId, userName, amount) {
            currentRejectDepositId = depositId;
            document.getElementById('rejectUserName').textContent = userName;
            document.getElementById('rejectAmount').textContent = amount;
            document.getElementById('rejectReason').value = '';
            document.getElementById('rejectReasonSelect').value = '';
            document.getElementById('rejectDepositModal').style.display = 'flex';
        }

        function closeRejectModal() {
            document.getElementById('rejectDepositModal').style.display = 'none';
            currentRejectDepositId = null;
        }

        function handleReasonSelect() {
            const select = document.getElementById('rejectReasonSelect');
            const textarea = document.getElementById('rejectReason');

            if (select.value && select.value !== 'custom') {
                const reasonTexts = {
                    'invalid_payment_proof': 'The payment proof or transaction hash provided is invalid or cannot be verified.',
                    'insufficient_payment': 'The payment amount does not match the required deposit amount.',
                    'duplicate_transaction': 'This transaction has already been processed or appears to be a duplicate.',
                    'suspicious_activity': 'Suspicious activity detected. Please contact support for verification.',
                    'incorrect_wallet_address': 'The payment was sent to an incorrect wallet address.',
                    'payment_not_found': 'The payment transaction could not be found or verified on the blockchain.',
                    'compliance_issues': 'There are compliance or KYC verification issues that need to be resolved first.'
                };

                textarea.value = reasonTexts[select.value] || '';
            } else if (select.value === 'custom') {
                textarea.value = '';
                textarea.focus();
            }
        }

        function approvePayment(depositId, button) {
            if (!confirm(
                    'Are you sure you want to approve this payment? This will mark it as completed and update the user\'s wallet balance.'
                )) {
                return;
            }

            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Approving...';
            button.disabled = true;

            fetch(`/admin/deposits/approve/${depositId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Payment approved successfully!',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reload DataTable instead of page
                            $('.datatables-deposits').DataTable().ajax.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Error approving payment',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                })
                .finally(() => {
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                });
        }

        function confirmRejectDeposit() {
            const reason = document.getElementById('rejectReason').value.trim();

            if (!reason) {
                alert('Please provide a reason for rejection.');
                document.getElementById('rejectReason').focus();
                return;
            }

            if (!confirm('Are you sure you want to reject this deposit? This action cannot be undone.')) {
                return;
            }

            const confirmBtn = document.getElementById('confirmRejectBtn');
            const originalHtml = confirmBtn.innerHTML;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rejecting...';
            confirmBtn.disabled = true;

            fetch(`/admin/deposits/reject/${currentRejectDepositId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        reason: reason
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Deposit rejected successfully!',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            closeRejectModal();
                            $('.datatables-deposits').DataTable().ajax.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Network error while rejecting deposit.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                })
                .finally(() => {
                    confirmBtn.innerHTML = originalHtml;
                    confirmBtn.disabled = false;
                });
        }

        // Close modals when clicking outside
        document.getElementById('depositDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDepositModal();
            }
        });

        document.getElementById('rejectDepositModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (document.getElementById('depositDetailsModal').style.display === 'flex') {
                    closeDepositModal();
                }
                if (document.getElementById('rejectDepositModal').style.display === 'flex') {
                    closeRejectModal();
                }
            }
        });
    </script>
@endsection
