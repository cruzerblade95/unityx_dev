@extends('admin/layouts/layoutMaster')

@section('title', 'User Wallet Management')

@section('vendor-style')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('page-style')
<style>
/* Custom styles for wallet management */
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

.modal-xl {
    max-width: 90%;
}

/* Enhanced export buttons */
.dt-button-collection .dropdown-menu {
    min-width: 180px;
}

.dt-button-collection .dropdown-item {
    padding: 0.5rem 1rem;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
}

.dt-button-collection .dropdown-item:hover {
    background-color: #f8f9fa;
}

/* Responsive table styling */
@media (max-width: 768px) {
    .card-datatable {
        padding: 0;
    }

    .dt-action-buttons {
        justify-content: center !important;
        margin-bottom: 1rem;
    }
}

/* Balance cards in modal */
.modal .card.bg-light {
    border: 1px solid #e7eaf3;
    background-color: #f8f9fa !important;
}

.modal .card.bg-light .card-body {
    padding: 1rem;
}

/* Action buttons spacing */
.dropdown-menu .dropdown-item {
    padding: 0.5rem 1rem;
}

.dropdown-menu .dropdown-item i {
    width: 1.2rem;
}
</style>
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js'])
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-2">
                    <i class="tabler-wallet me-2"></i>User Wallet Management
                </h5>
                <p class="text-muted mb-0">Manage user wallet balances, add or deduct amounts from deposit, profit, bonus, and cash balances.</p>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="searchInput" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="tabler-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by username, name, or email...">
                </div>
            </div>
            <div class="col-md-3">
                <label for="roleFilter" class="form-label">Role Filter</label>
                <select class="form-select" id="roleFilter">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" id="applyFilters">
                        <i class="tabler-filter me-1"></i> Apply Filters
                    </button>
                    <button class="btn btn-label-secondary" id="clearFilters">
                        <i class="tabler-x me-1"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card-datatable table-responsive">
        <table class="datatables-wallets table">
            <thead>
                <tr>
                    <th></th>
                    <th>User</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Deposit</th>
                    <th>Profit</th>
                    <th>Bonus</th>
                    <th>Cash</th>
                    <th>Total Balance</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Adjust Wallet Modal -->
<div class="modal fade" id="adjustWalletModal" tabindex="-1" aria-labelledby="adjustWalletModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adjustWalletModalLabel">
                    <i class="tabler-wallet me-2"></i>Adjust User Wallet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="adjustWalletForm">
                    <input type="hidden" id="wallet_id" name="wallet_id">

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="tabler-info-circle me-2"></i>
                                <strong>User:</strong> <span id="modal_user_name">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Current Balances Display -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="mb-3">Current Balances</h6>
                            <div class="row">
                                <div class="col-md-3 col-6">
                                    <div class="card bg-light">
                                        <div class="card-body p-3 text-center">
                                            <h6 class="card-title mb-1">Deposit</h6>
                                            <h5 class="text-primary mb-0">$<span id="current_deposit">0.00</span></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="card bg-light">
                                        <div class="card-body p-3 text-center">
                                            <h6 class="card-title mb-1">Profit</h6>
                                            <h5 class="text-success mb-0">$<span id="current_profit">0.00</span></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="card bg-light">
                                        <div class="card-body p-3 text-center">
                                            <h6 class="card-title mb-1">Bonus</h6>
                                            <h5 class="text-warning mb-0">$<span id="current_bonus">0.00</span></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="card bg-light">
                                        <div class="card-body p-3 text-center">
                                            <h6 class="card-title mb-1">Cash</h6>
                                            <h5 class="text-info mb-0">$<span id="current_cash">0.00</span></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Adjustment Form -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="balance_type" class="form-label">Balance Type</label>
                            <select class="form-select" id="balance_type" name="type" required>
                                <option value="">Select Balance Type</option>
                                <option value="deposit">Deposit</option>
                                <option value="profit">Profit</option>
                                <option value="bonus">Bonus</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="adjustment_action" class="form-label">Action</label>
                            <select class="form-select" id="adjustment_action" name="action" required>
                                <option value="">Select Action</option>
                                <option value="add">Add Amount</option>
                                <option value="deduct">Deduct Amount</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="adjustment_amount" class="form-label">Amount ($)</label>
                            <input type="number" class="form-control" id="adjustment_amount" name="amount"
                                   step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="adjustment_reason" class="form-label">Reason</label>
                            <input type="text" class="form-control" id="adjustment_reason" name="reason"
                                   placeholder="Enter reason for adjustment" required>
                        </div>
                    </div>

                    <!-- Toggle Deposit Bonus Script -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="trigger_deposit_bonus" name="trigger_deposit_bonus" value="1">
                                <label class="form-check-label" for="trigger_deposit_bonus">
                                    <strong>Trigger Deposit Bonus</strong>
                                    <small class="text-muted d-block">Check this to run the deposit bonus script when adding deposit balance</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="ti ti-alert-triangle me-2"></i>
                                <strong>Warning:</strong> This action will immediately modify the user's wallet balance. Please ensure the amount and reason are correct before proceeding.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>Cancel
                </button>
                <button type="submit" form="adjustWalletForm" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i>Adjust Balance
                </button>
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDetailsModalLabel">
                    <i class="tabler-user me-2"></i>User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="ti ti-user-circle me-2"></i>Personal Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Full Name:</label>
                                    <p class="mb-2" id="user_full_name">Loading...</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email:</label>
                                    <p class="mb-2" id="user_email">Loading...</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Username:</label>
                                    <p class="mb-2" id="user_username">Loading...</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Registration Date:</label>
                                    <p class="mb-2" id="user_created_at">Loading...</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Account Status:</label>
                                    <p class="mb-0" id="user_status">Loading...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="ti ti-wallet me-2"></i>Wallet Summary
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h6 class="text-primary mb-1">Deposit</h6>
                                            <h5 class="mb-0">$<span id="user_deposit">0.00</span></h5>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h6 class="text-success mb-1">Profit</h6>
                                            <h5 class="mb-0">$<span id="user_profit">0.00</span></h5>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h6 class="text-warning mb-1">Bonus</h6>
                                            <h5 class="mb-0">$<span id="user_bonus">0.00</span></h5>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h6 class="text-info mb-1">Cash</h6>
                                            <h5 class="mb-0">$<span id="user_cash">0.00</span></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <div class="border rounded p-3 bg-light">
                                        <h6 class="text-dark mb-1">Total Balance</h6>
                                        <h4 class="text-dark mb-0">$<span id="user_total_balance">0.00</span></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Transaction History Modal -->
<div class="modal fade" id="transactionHistoryModal" tabindex="-1" aria-labelledby="transactionHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionHistoryModalLabel">
                    <i class="tabler-history me-2"></i>Transaction History - <span id="history_user_name">User</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="transactionHistoryTable">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance Type</th>
                                <th>Before</th>
                                <th>After</th>
                                <th>Reason</th>
                                <th>Admin</th>
                            </tr>
                        </thead>
                        <tbody id="transaction_history_body">
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Loading transaction history...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
    // Set the base URL for AJAX calls
    window.baseUrl = '{{ url('/admin') }}/';
    console.log('Base URL set to:', window.baseUrl);
</script>
@vite(['resources/assets/js/app-wallet-list.js'])
@endsection
