@php
    $configData = Helper::appClasses();
@endphp

@extends('admin.layouts.layoutMaster')

@section('title', 'Reinvestment History Management')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header with Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-recycle text-primary me-2"></i>
                        Reinvestment History Management
                    </h4>
                    <p class="text-muted mb-0">Monitor and manage all user reinvestment activities</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success" onclick="refreshStats()">
                        <i class="fas fa-sync-alt me-1"></i>
                        Refresh Stats
                    </button>
                    <button class="btn btn-success" onclick="exportData()">
                        <i class="fas fa-download me-1"></i>
                        Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4" id="statsCards">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Reinvestments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalReinvestments">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-recycle fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Volume
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalVolume">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Tax Collected
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="taxCollected">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Net Invested
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="netInvested">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filters
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">All Statuses</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Wallet Type</label>
                            <select class="form-select" id="filterWalletType">
                                <option value="">All Wallet Types</option>
                                <option value="profit">Profit</option>
                                <option value="bonus">Bonus</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" id="filterDateFrom">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" id="filterDateTo">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button class="btn btn-primary" onclick="applyFilters()">
                                <i class="fas fa-search me-1"></i>
                                Apply Filters
                            </button>
                            <button class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="fas fa-times me-1"></i>
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reinvestment History Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Reinvestment History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="reinvestHistoryTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User Information</th>
                                    <th>Amounts</th>
                                    <th>Wallet Type</th>
                                    <th>Status</th>
                                    <th>Current Performance</th>
                                    <th>AI Trade</th>
                                    <th>Processed</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="fas fa-edit me-2"></i>
                    Edit Reinvestment History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">User</label>
                                <input type="text" class="form-control" id="editUser" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">AI Trade ID</label>
                                <input type="text" class="form-control" id="editAiTradeId" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Original Amount</label>
                                <input type="text" class="form-control" id="editOriginalAmount" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tax Amount</label>
                                <input type="text" class="form-control" id="editTaxAmount" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Net Amount</label>
                                <input type="text" class="form-control" id="editNetAmount" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Wallet Type</label>
                                <input type="text" class="form-control" id="editWalletType" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="editStatus" required>
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                    <option value="failed">Failed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea class="form-control" id="editNotes" rows="3" placeholder="Add notes about this reinvestment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
    let reinvestHistoryTable;
    let currentEditId = null;

    document.addEventListener('DOMContentLoaded', function() {
        initializeDataTable();
        loadStats();
        setupFormHandlers();
    });

    function initializeDataTable() {
        reinvestHistoryTable = $('#reinvestHistoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/admin/reinvest-history/data',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                error: function(xhr, error, code) {
                    console.error('DataTables AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error,
                        code: code
                    });

                    // Try to parse JSON to see if it's a JSON parsing issue
                    let errorMessage = 'Failed to load reinvestment history data.';
                    if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                errorMessage = response.error;
                            }
                        } catch (parseError) {
                            console.error('JSON Parse Error:', parseError);
                            console.log('Raw Response (first 500 chars):', xhr.responseText.substring(0, 500));
                            errorMessage = 'Invalid JSON response from server. Check console for details.';
                        }
                    }

                    // Show user-friendly error
                    Swal.fire({
                        title: 'Error Loading Data',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            columns: [
                { data: 'id', name: 'id', width: '60px' },
                {
                    data: 'user_info',
                    name: 'user_info',
                    render: function(data) {
                        if (!data) return '<span class="text-muted">N/A</span>';
                        return `
                            <div>
                                <strong>${data.username || 'N/A'}</strong> (#${data.id || 'N/A'})
                                <br><small class="text-muted">${data.email || 'N/A'}</small>
                            </div>
                        `;
                    }
                },
                {
                    data: 'amounts',
                    name: 'amounts',
                    render: function(data) {
                        if (!data) return '<span class="text-muted">N/A</span>';
                        return `
                            <div>
                                <strong>Original:</strong> $${data.original || '0.00'}
                                <br><strong>Tax:</strong> $${data.tax || '0.00'}
                                <br><strong>Net:</strong> $${data.net || '0.00'}
                                <br><small class="text-muted">Expected: $${data.expected_return || '0.00'}</small>
                            </div>
                        `;
                    }
                },
                {
                    data: 'wallet_badge',
                    name: 'wallet_type',
                    render: function(data) {
                        if (!data || !data.type) return '<span class="badge bg-secondary">N/A</span>';
                        return `<span class="badge bg-${data.color || 'secondary'}">${data.type.charAt(0).toUpperCase() + data.type.slice(1)}</span>`;
                    }
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    render: function(data) {
                        if (!data || !data.status) return '<span class="badge bg-secondary">N/A</span>';
                        return `<span class="badge bg-${data.color || 'secondary'}">${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</span>`;
                    }
                },
                {
                    data: 'current_status',
                    name: 'current_status',
                    render: function(data) {
                        if (!data) return '<span class="text-muted">N/A</span>';
                        return `
                            <div>
                                <strong>Current:</strong> $${data.current_value || '0.00'}
                                <br><span class="${data.profit_loss_class || 'text-muted'}">P&L: $${data.profit_loss || '0.00'}</span>
                                <br><small class="text-muted">Progress: ${data.progress || '0'}%</small>
                            </div>
                        `;
                    }
                },
                {
                    data: 'ai_trade_info',
                    name: 'ai_trade_info',
                    render: function(data) {
                        if (data) {
                            return `
                                <div>
                                    <strong>Trade #${data.id}</strong>
                                    <br><small class="text-muted">${data.status}</small>
                                    <br><small>Current: $${data.current_amount}</small>
                                </div>
                            `;
                        }
                        return '<span class="text-muted">No AI Trade</span>';
                    }
                },
                {
                    data: 'processed_info',
                    name: 'processed_info',
                    render: function(data) {
                        if (!data) return '<span class="text-muted">N/A</span>';
                        return `
                            <div>
                                <small>${data.processed_at || 'N/A'}</small>
                                <br><small class="text-muted">by ${data.processed_by || 'N/A'}</small>
                            </div>
                        `;
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return `
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-primary" onclick="editHistory(${data})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="viewDetails(${data})" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"ms-auto"f>>rtip',
            language: {
                search: '',
                searchPlaceholder: 'Search reinvestments...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ reinvestments',
                infoEmpty: 'No reinvestments found',
                infoFiltered: '(filtered from _MAX_ total reinvestments)',
                zeroRecords: 'No matching reinvestments found'
            }
        });
    }

    function loadStats() {
        fetch('/admin/reinvest-history/stats', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalReinvestments').textContent = data.total_reinvestments.toLocaleString();
            document.getElementById('totalVolume').textContent = '$' + parseFloat(data.total_volume).toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('taxCollected').textContent = '$' + parseFloat(data.total_tax_collected).toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('netInvested').textContent = '$' + parseFloat(data.total_net_invested).toLocaleString(undefined, {minimumFractionDigits: 2});
        })
        .catch(error => {
            console.error('Error loading stats:', error);
            ['totalReinvestments', 'totalVolume', 'taxCollected', 'netInvested'].forEach(id => {
                document.getElementById(id).innerHTML = '<i class="fas fa-exclamation-triangle text-danger"></i>';
            });
        });
    }

    function setupFormHandlers() {
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            updateHistory();
        });
    }

    function editHistory(id) {
        currentEditId = id;

        fetch(`/admin/reinvest-history/${id}/edit`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const history = data.history;

                document.getElementById('editUser').value = `${history.user.username} (${history.user.email})`;
                document.getElementById('editAiTradeId').value = history.ai_trade_id || 'N/A';
                document.getElementById('editOriginalAmount').value = '$' + parseFloat(history.original_amount).toFixed(2);
                document.getElementById('editTaxAmount').value = '$' + parseFloat(history.tax_amount).toFixed(2);
                document.getElementById('editNetAmount').value = '$' + parseFloat(history.net_amount).toFixed(2);
                document.getElementById('editWalletType').value = history.wallet_type.charAt(0).toUpperCase() + history.wallet_type.slice(1);
                document.getElementById('editStatus').value = history.status;
                document.getElementById('editNotes').value = history.notes || '';

                new bootstrap.Modal(document.getElementById('editModal')).show();
            } else {
                Swal.fire('Error!', data.message || 'Failed to load reinvestment history', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error!', 'Failed to load reinvestment history', 'error');
        });
    }

    function updateHistory() {
        const formData = new FormData();
        formData.append('status', document.getElementById('editStatus').value);
        formData.append('notes', document.getElementById('editNotes').value);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('_method', 'PUT');

        fetch(`/admin/reinvest-history/${currentEditId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success!', data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                reinvestHistoryTable.ajax.reload();
                loadStats();
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error!', 'Failed to update reinvestment history', 'error');
        });
    }

    function viewDetails(id) {
        // This could open a detailed view modal - implement as needed
        console.log('View details for reinvestment:', id);
    }

    function applyFilters() {
        const status = document.getElementById('filterStatus').value;
        const walletType = document.getElementById('filterWalletType').value;
        const dateFrom = document.getElementById('filterDateFrom').value;
        const dateTo = document.getElementById('filterDateTo').value;

        reinvestHistoryTable.columns(4).search(status);
        reinvestHistoryTable.columns(3).search(walletType);

        // Note: Date filtering would need to be implemented server-side
        reinvestHistoryTable.ajax.reload();
    }

    function clearFilters() {
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterWalletType').value = '';
        document.getElementById('filterDateFrom').value = '';
        document.getElementById('filterDateTo').value = '';

        reinvestHistoryTable.search('').columns().search('').draw();
    }

    function refreshStats() {
        loadStats();
        reinvestHistoryTable.ajax.reload();
    }

    function exportData() {
        const status = document.getElementById('filterStatus').value;
        const walletType = document.getElementById('filterWalletType').value;
        const dateFrom = document.getElementById('filterDateFrom').value;
        const dateTo = document.getElementById('filterDateTo').value;

        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (walletType) params.append('wallet_type', walletType);
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);

        window.location.href = `/admin/reinvest-history/export?${params.toString()}`;
    }
</script>

<style>
.border-left-primary { border-left: 4px solid #007bff !important; }
.border-left-success { border-left: 4px solid #28a745 !important; }
.border-left-info { border-left: 4px solid #17a2b8 !important; }
.border-left-warning { border-left: 4px solid #ffc107 !important; }

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
    border: 1px solid #e3e6f0 !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.85rem;
    color: #5a5c69;
    background-color: #f8f9fc;
}

.badge {
    font-size: 0.75rem;
}

#reinvestHistoryTable_wrapper .dataTables_filter input {
    border-radius: 0.25rem;
    border: 1px solid #d1d3e2;
    padding: 0.375rem 0.75rem;
}

#reinvestHistoryTable_wrapper .dataTables_length select {
    border-radius: 0.25rem;
    border: 1px solid #d1d3e2;
    padding: 0.375rem 0.75rem;
}
</style>
@endsection
