@extends('admin.layouts.layoutMaster')

@section('title', 'Upline Management')

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
    // Set up routes for JavaScript
    window.uplineManagementRoutes = {
        usersData: '{{ route("admin.upline-management.users-data") }}',
        searchUsers: '{{ route("admin.upline-management.search-users") }}',
        userTreeBase: '{{ route("admin.upline-management.user-tree", "PLACEHOLDER") }}',
        bulkUpdateUpline: '{{ route("admin.upline-management.bulk-update-upline") }}',
        refreshReferralCodes: '{{ route("admin.upline-management.refresh-referral-codes") }}'
    };
    window.csrfToken = '{{ csrf_token() }}';

    // Add referral code refresh functionality
    document.addEventListener('DOMContentLoaded', function() {
        const refreshReferralBtn = document.getElementById('refreshReferralCodes');

        if (refreshReferralBtn) {
            refreshReferralBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Refresh Referral Codes?',
                    text: 'This will generate referral codes for users who don\'t have one. Existing codes will remain unchanged.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, refresh codes!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        refreshReferralBtn.disabled = true;
                        refreshReferralBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';

                        // Make AJAX request
                        fetch(window.uplineManagementRoutes.refreshReferralCodes, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: `Referral codes refreshed successfully! ${data.updated_count} users got new referral codes.`,
                                    icon: 'success'
                                });

                                // Refresh the table if it exists
                                if (typeof refreshTable === 'function') {
                                    refreshTable();
                                }
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message || 'Failed to refresh referral codes.',
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while refreshing referral codes.',
                                icon: 'error'
                            });
                        })
                        .finally(() => {
                            // Reset button
                            refreshReferralBtn.disabled = false;
                            refreshReferralBtn.innerHTML = '<i class="fas fa-key me-1"></i>Refresh Referral Codes';
                        });
                    }
                });
            });
        }
    });
</script>
@vite(['resources/js/admin-upline-management.js'])
@endsection

@section('page-style')
<style>
.user-list-container {
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 0.5rem;
}

.user-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    cursor: move;
    transition: all 0.2s ease;
}

.user-item:hover {
    background: #e9ecef;
    border-color: #6c757d;
}

.user-item.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

.drop-zone {
    transition: all 0.2s ease;
}

.drop-zone.drag-over {
    background-color: #e7f3ff;
    border-color: #0d6efd !important;
    border-width: 2px !important;
}

.dropped-user-item {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    border-radius: 0.375rem;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    position: relative;
}

.remove-dropped-user {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    cursor: pointer;
}

.tree-node {
    position: relative;
    margin-left: 20px;
    padding: 10px;
    border-left: 2px solid #e9ecef;
}

.tree-node::before {
    content: '';
    position: absolute;
    left: -2px;
    top: 50%;
    width: 15px;
    height: 2px;
    background: #e9ecef;
}

.tree-user-info {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
}

.assignment-item {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 0.375rem;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
}
</style>
@endsection

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Upline Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Upline Management</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="card-title mb-0">User Hierarchy Management</h4>
                                    <p class="card-title-desc mb-0">Manage upline and downline relationships with drag and drop functionality</p>
                                </div>
                                <div class="col-auto">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="refreshTable">
                                            <i class="fas fa-sync-alt me-1"></i>Refresh
                                        </button>
                                        <button type="button" class="btn btn-warning" id="refreshReferralCodes">
                                            <i class="fas fa-key me-1"></i>Refresh Referral Codes
                                        </button>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#dragDropModal">
                                            <i class="fas fa-exchange-alt me-1"></i>Drag & Drop Manager
                                        </button>
                                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
                                            <i class="fas fa-users me-1"></i>Bulk Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Performance indicator -->
                            <div id="performanceInfo" class="alert alert-info d-none mb-3">
                                <div class="row">
                                    <div class="col-md-3">
                                        <small><strong>Loading Time:</strong> <span id="loadingTime">-</span>ms</small>
                                    </div>
                                    <div class="col-md-3">
                                        <small><strong>Records:</strong> <span id="recordCount">-</span></small>
                                    </div>
                                    <div class="col-md-3">
                                        <small><strong>Page Size:</strong> <span id="pageSize">15</span></small>
                                    </div>
                                    <div class="col-md-3">
                                        <small><strong>Server Response:</strong> <span id="serverTime">-</span>ms</small>
                                    </div>
                                </div>
                            </div>

                            <!-- DataTable -->
                            <div class="table-responsive">
                                <table id="uplineManagementTable" class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Current Upline</th>
                                            <th>Downlines Count</th>
                                            <th>Wallet Info</th>
                                            <th>Join Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Drag & Drop Management Modal -->
<div class="modal fade" id="dragDropModal" tabindex="-1" aria-labelledby="dragDropModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dragDropModalLabel">
                    <i class="fas fa-exchange-alt me-2"></i>Drag & Drop Upline Manager
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Search and Available Users -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Available Users</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Search Users</label>
                                    <input type="text" id="userSearch" class="form-control" placeholder="Search by username, name, or email...">
                                </div>
                                <div id="availableUsers" class="user-list-container" style="min-height: 400px; max-height: 400px; overflow-y: auto;">
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-search fa-2x mb-2"></i>
                                        <div>Start typing to search for users</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Drag Drop Area -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Drop Zone</h6>
                            </div>
                            <div class="card-body">
                                <div id="dropZone" class="drop-zone text-center p-4 border border-dashed border-primary rounded" style="min-height: 400px;">
                                    <div class="drop-zone-content">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                        <h5>Drop Users Here</h5>
                                        <p class="text-muted">Drag users from the search results and drop them here to prepare for upline assignment</p>
                                    </div>
                                    <div id="droppedUsers" class="dropped-users-list mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upline Assignment -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Assign Upline</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Select New Upline</label>
                                    <input type="text" id="uplineSearch" class="form-control" placeholder="Search for upline user...">
                                    <div id="selectedUpline" class="mt-2"></div>
                                </div>
                                <div class="assignment-preview" id="assignmentPreview" style="min-height: 300px; max-height: 300px; overflow-y: auto;">
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <div>Drop users and select upline to see preview</div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" id="executeAssignment" class="btn btn-success w-100" disabled>
                                        <i class="fas fa-check me-1"></i>Execute Assignment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tree View Modal -->
<div class="modal fade" id="treeViewModal" tabindex="-1" aria-labelledby="treeViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="treeViewModalLabel">
                    <i class="fas fa-sitemap me-2"></i>User Tree Structure
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="userTreeContainer" style="min-height: 400px;">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-labelledby="bulkUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkUpdateModalLabel">
                    <i class="fas fa-users me-2"></i>Bulk Update Uplines
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Upload a CSV file with columns: user_id, new_upline_id (optional - leave empty to remove upline)
                </div>
                <div class="mb-3">
                    <label class="form-label">CSV File</label>
                    <input type="file" id="bulkCsvFile" class="form-control" accept=".csv">
                </div>
                <div id="bulkPreview" class="mt-3"></div>
                <div class="mt-3">
                    <button type="button" id="executeBulkUpdate" class="btn btn-primary" disabled>
                        <i class="fas fa-upload me-1"></i>Execute Bulk Update
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection




