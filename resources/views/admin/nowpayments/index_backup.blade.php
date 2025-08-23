@extends('admin.layouts.layoutMaster')

@section('title', 'NOWPayments Payment Details')

@section('content')
    <!-- CSRF Token Meta Tag for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">NOWPayments Payment Details</h4>
            <button class="btn btn-primary" onclick="testApiConnection()">
                <i class="fas fa-plug"></i> Test API Connection
            </button>
        </div>

        @if (isset($apiError) && $apiError)
            <div class="alert alert-info">
                <strong>API Info:</strong> {{ $apiError }}
            </div>
        @endif

        <!-- Payment Lists Container - Will be updated via AJAX -->
        <div id="payment-lists-container">
            @include('admin.nowpayments.partials.payment-list')
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

        /* Dark theme overlay adjustment */
        [data-bs-theme="dark"] .deposit-modal-overlay {
            background-color: rgba(0, 0, 0, 0.7);
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

        /* Dark theme modal content */
        [data-bs-theme="dark"] .deposit-modal-content {
            background: var(--bs-dark, #212529);
            color: var(--bs-light, #f8f9fa);
            border-color: var(--bs-gray-600, #6c757d);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
        }

        /* Loading Animation Styles */
        .ajax-loading {
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .loading-spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* AJAX Loading Spinner */
        .ajax-loading {
            background: rgba(255, 255, 255, 0.95);
            position: relative;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        [data-bs-theme="dark"] .ajax-loading {
            background: rgba(33, 37, 41, 0.95);
            border-color: #495057;
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
            padding: 20px 20px 0;
            border-bottom: 1px solid var(--bs-border-color, #dee2e6);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        [data-bs-theme="dark"] .deposit-modal-header {
            border-bottom-color: var(--bs-gray-600, #6c757d);
        }

        .deposit-modal-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--bs-body-color, #495057);
        }

        .deposit-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--bs-secondary, #6c757d);
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .deposit-modal-close:hover {
            background-color: var(--bs-gray-200, #f8f9fa);
            color: var(--bs-body-color, #495057);
        }

        [data-bs-theme="dark"] .deposit-modal-close:hover {
            background-color: var(--bs-gray-700, #495057);
            color: var(--bs-light, #f8f9fa);
        }

        .deposit-modal-body {
            padding: 0 20px 20px;
        }

        /* Form controls theme adjustments */
        .deposit-modal-content .form-control,
        .deposit-modal-content .form-select {
            background-color: var(--bs-body-bg, #ffffff);
            border-color: var(--bs-border-color, #ced4da);
            color: var(--bs-body-color, #212529);
        }

        [data-bs-theme="dark"] .deposit-modal-content .form-control,
        [data-bs-theme="dark"] .deposit-modal-content .form-select {
            background-color: var(--bs-gray-800, #343a40);
            border-color: var(--bs-gray-600, #6c757d);
            color: var(--bs-light, #f8f9fa);
        }

        .deposit-modal-content .form-control:focus,
        .deposit-modal-content .form-select:focus {
            border-color: var(--bs-primary, #0d6efd);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        /* Text muted adjustments */
        .deposit-modal-content .text-muted {
            color: var(--bs-secondary, #6c757d) !important;
        }

        [data-bs-theme="dark"] .deposit-modal-content .text-muted {
            color: var(--bs-gray-400, #adb5bd) !important;
        }

        /* Alert theme adjustments */
        .deposit-modal-content .alert-warning {
            background-color: var(--bs-warning-bg-subtle, #fff3cd);
            border-color: var(--bs-warning-border-subtle, #ffecb5);
            color: var(--bs-warning-text-emphasis, #664d03);
        }

        [data-bs-theme="dark"] .deposit-modal-content .alert-warning {
            background-color: rgba(255, 193, 7, 0.1);
            border-color: rgba(255, 193, 7, 0.2);
            color: #ffda6a;
        }

        .deposit-modal-content .bg-light {
            background-color: var(--bs-light, #f8f9fa) !important;
            color: var(--bs-body-color, #212529) !important;
        }

        [data-bs-theme="dark"] .deposit-modal-content .bg-light {
            background-color: var(--bs-gray-800, #343a40) !important;
            color: var(--bs-light, #f8f9fa) !important;
        }

        /* Button theme consistency */
        .deposit-modal-content .btn {
            transition: all 0.15s ease-in-out;
        }

        [data-bs-theme="dark"] .deposit-modal-content .btn-secondary {
            --bs-btn-bg: #6c757d;
            --bs-btn-border-color: #6c757d;
            --bs-btn-hover-bg: #5c636a;
            --bs-btn-hover-border-color: #565e64;
            --bs-btn-color: #fff;
        }

        [data-bs-theme="dark"] .deposit-modal-content .btn-danger {
            --bs-btn-bg: #dc3545;
            --bs-btn-border-color: #dc3545;
            --bs-btn-hover-bg: #bb2d3b;
            --bs-btn-hover-border-color: #b02a37;
            --bs-btn-color: #fff;
        }

        .detail-section {
            margin-bottom: 25px;
            padding: 15px;
            background-color: var(--bs-light, #f8f9fa);
            border-radius: 6px;
            border-left: 4px solid var(--bs-primary, #007bff);
        }

        [data-bs-theme="dark"] .detail-section {
            background-color: var(--bs-gray-800, #343a40);
            color: var(--bs-light, #f8f9fa);
        }

        .detail-section h6 {
            margin-bottom: 15px;
            font-weight: 600;
            color: var(--bs-body-color, #495057);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid var(--bs-border-color, #e9ecef);
        }

        [data-bs-theme="dark"] .detail-row {
            border-bottom-color: var(--bs-gray-600, #6c757d);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: var(--bs-secondary, #6c757d);
            min-width: 140px;
        }

        [data-bs-theme="dark"] .detail-label {
            color: var(--bs-gray-400, #adb5bd);
        }

        .detail-value {
            color: var(--bs-body-color, #495057);
            font-weight: 600;
            word-break: break-all;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        [data-bs-theme="dark"] .status-pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ffda6a;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        [data-bs-theme="dark"] .status-completed {
            background-color: rgba(25, 135, 84, 0.2);
            color: #75b798;
        }

        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        [data-bs-theme="dark"] .status-failed {
            background-color: rgba(220, 53, 69, 0.2);
            color: #ea868f;
        }

        .nowpayments-status {
            border-left-color: var(--bs-success, #28a745);
        }

        .nowpayments-error {
            border-left-color: var(--bs-danger, #dc3545);
        }
    </style>

    <script>
        function refreshPaymentStatus(paymentId, button) {
            // Add loading state
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            button.disabled = true;

            console.log('Refreshing payment status for ID:', paymentId);

            // Make AJAX request to refresh payment status
            fetch(`/admin/nowpayments/refresh-status/${paymentId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);

                    if (data.success) {
                        // Show success message with more details
                        let message = `Payment status refreshed successfully!`;
                        if (data.payment && data.payment.payment_status) {
                            message += `\nStatus: ${data.payment.payment_status}`;
                        }
                        if (data.local_deposit_updated) {
                            message += `\nLocal database updated.`;
                        }

                        alert(message);

                        // Reload the page to show updated status
                        window.location.reload();
                    } else {
                        // Show detailed error message
                        let errorMessage = 'Failed to refresh payment status:\n';
                        errorMessage += data.message || 'Unknown error';

                        if (data.payment_id) {
                            errorMessage += `\nPayment ID: ${data.payment_id}`;
                        }

                        if (data.local_deposit_exists !== undefined) {
                            errorMessage += `\nLocal deposit exists: ${data.local_deposit_exists ? 'Yes' : 'No'}`;
                        }

                        alert(errorMessage);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(`Network error while refreshing payment status:\n${error.message || error}`);
                })
                .finally(() => {
                    // Restore button state
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                });
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
                        alert('Payment approved successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error approving payment');
                })
                .finally(() => {
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                });
        }

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
                                <span class="detail-label">Name:</span>
                                <span class="detail-value">${deposit.user.name}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value">${deposit.user.email}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Username:</span>
                                <span class="detail-value">${deposit.user.username}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">User ID:</span>
                                <span class="detail-value">${deposit.user.id}</span>
                            </div>
                        </div>
                    `;

                        // Payment Information Section
                        const statusClass = deposit.status === 'completed' ? 'status-completed' :
                            deposit.status === 'pending' ? 'status-pending' :
                            'status-failed';

                        modalContent += `
                        <div class="detail-section">
                            <h6><i class="fas fa-credit-card"></i> Payment Information</h6>
                            <div class="detail-row">
                                <span class="detail-label">Deposit ID:</span>
                                <span class="detail-value">${deposit.id}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment ID:</span>
                                <span class="detail-value">${deposit.payment_id || 'N/A'}</span>
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
                        } else if (data.has_payment_id) {
                            modalContent += `
                            <div class="detail-row">
                                <span class="detail-label">Status:</span>
                                <span class="detail-value" style="color: #dc3545;">⚠️ Payment ID exists but not found in NOWPayments system</span>
                            </div>
                        `;
                        }

                        modalContent += '</div>';

                        // Update modal content
                        document.getElementById('depositModalBody').innerHTML = modalContent;

                    } else {
                        // Show error in modal
                        document.getElementById('depositModalBody').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Error:</strong> ${data.message}
                        </div>
                    `;
                    }
                })
                .catch(error => {
                    console.error('Error fetching deposit details:', error);

                    // Show network error in modal
                    document.getElementById('depositModalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Network Error:</strong> ${error.message || error}
                    </div>
                `;
                })
                .finally(() => {
                    // Restore button state
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                });
        }

        function openDepositModal() {
            document.getElementById('depositDetailsModal').style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closeDepositModal() {
            document.getElementById('depositDetailsModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore background scrolling
        }

        // Close modal when clicking outside
        document.getElementById('depositDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDepositModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('depositDetailsModal').style.display === 'flex') {
                closeDepositModal();
            }
        });

        // Reject Modal Functions
        let currentRejectDepositId = null;

        function showRejectModal(depositId, userName, amount) {
            currentRejectDepositId = depositId;

            // Update modal content
            document.getElementById('rejectUserName').textContent = userName;
            document.getElementById('rejectAmount').textContent = amount;

            // Clear form
            document.getElementById('rejectReasonSelect').value = '';
            document.getElementById('rejectReason').value = '';

            // Show modal
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
                // Pre-fill textarea with selected reason
                const reasonTexts = {
                    'invalid_payment_proof': 'The payment proof or transaction hash provided is invalid or cannot be verified.',
                    'insufficient_payment': 'The payment amount is insufficient or does not match the deposit request.',
                    'duplicate_transaction': 'This transaction appears to be a duplicate of an existing deposit.',
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
                        alert('Deposit rejected successfully!');
                        closeRejectModal();
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error while rejecting deposit.');
                })
                .finally(() => {
                    confirmBtn.innerHTML = originalHtml;
                    confirmBtn.disabled = false;
                });
        }

        // Close reject modal when clicking outside
        document.getElementById('rejectDepositModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });

        // Close reject modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('rejectDepositModal').style.display === 'flex') {
                closeRejectModal();
            }
        });

        // AJAX Pagination functionality
        function initializePagination() {
            $(document).on('click', '.ajax-pagination', function(e) {
                e.preventDefault();
                
                const url = $(this).attr('href');
                
                // Show loading state with better styling
                $('#payment-lists-container').html(`
                    <div class="ajax-loading">
                        <i class="fas fa-spinner loading-spinner fa-3x text-primary mb-3"></i>
                        <h5>Loading payments...</h5>
                        <p class="text-muted">Please wait while we fetch the data</p>
                    </div>
                `);
                
                // Make AJAX request
                $.ajax({
                    url: url.replace('{{ route("admin.nowpayments.index") }}', '{{ route("admin.nowpayments.ajax") }}'),
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#payment-lists-container').html(response).addClass('fade-in');
                        
                        // Scroll to top of the container smoothly
                        $('html, body').animate({
                            scrollTop: $('#payment-lists-container').offset().top - 100
                        }, 500);
                        
                        // Show success notification
                        console.log('✅ Pagination loaded successfully');
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        $('#payment-lists-container').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error:</strong> Failed to load payment data. Please refresh the page.
                                <br><small>Error details: ${error}</small>
                            </div>
                        `);
                    }
                });
            });
        }        // Initialize pagination when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializePagination();
        });
    </script>
@endsection
