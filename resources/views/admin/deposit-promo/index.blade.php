@php
$configData = Helper::appClasses();
$admin = auth()->guard('admin')->user();
@endphp

@extends('admin.layouts.layoutMaster')

@section('title', 'Deposit Promo Bonus Management')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss'
])
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'
])
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Deposit Promo Bonus Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCampaignModal">
            <i class="fas fa-plus"></i> Create Campaign
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Campaigns</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeCampaigns }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Campaigns</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCampaigns }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Bonuses Given</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($totalBonusesGiven, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-gift fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pending Bonuses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingBonuses }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Management Tab -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-tabs card-header-tabs" id="depositPromoTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="campaigns-tab" data-bs-toggle="tab" data-bs-target="#campaigns" type="button" role="tab" aria-controls="campaigns" aria-selected="true">
                        <i class="fas fa-bullhorn"></i> Campaigns
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="bonus-history-tab" data-bs-toggle="tab" data-bs-target="#bonus-history" type="button" role="tab" aria-controls="bonus-history" aria-selected="false">
                        <i class="fas fa-history"></i> Bonus History
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="depositPromoTabContent">
                <!-- Campaigns Tab -->
                <div class="tab-pane fade show active" id="campaigns" role="tabpanel" aria-labelledby="campaigns-tab">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="campaignsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Campaign Name</th>
                                    <th>Creator</th>
                                    <th>Status</th>
                                    <th>Duration</th>
                                    <th>Bonus %</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bonus History Tab -->
                <div class="tab-pane fade" id="bonus-history" role="tabpanel" aria-labelledby="bonus-history-tab">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="bonusHistoryTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Campaign</th>
                                    <th>Amounts</th>
                                    <th>Status</th>
                                    <th>Expires</th>
                                    <th>Claimed</th>
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

<!-- Create Campaign Modal -->
<div class="modal fade" id="createCampaignModal" tabindex="-1" aria-labelledby="createCampaignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCampaignModalLabel">Create New Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createCampaignForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="name" class="form-label">Campaign Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_datetime" class="form-label">Start Date & Time *</label>
                            <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" min="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_datetime" class="form-label">End Date & Time *</label>
                            <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" min="{{ now()->addHour()->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="percentage_bonus" class="form-label">User Bonus Percentage *</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="percentage_bonus" name="percentage_bonus" min="0" max="100" step="0.01" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="percentage_upline_bonus" class="form-label">Upline Bonus Percentage *</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="percentage_upline_bonus" name="percentage_upline_bonus" min="0" max="100" step="0.01" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">
                                    Activate campaign immediately
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Campaign Modal -->
<div class="modal fade" id="editCampaignModal" tabindex="-1" aria-labelledby="editCampaignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCampaignModalLabel">Edit Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCampaignForm">
                <input type="hidden" id="edit_campaign_id" name="campaign_id">
                <div class="modal-body">
                    <!-- Same form fields as create modal -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="edit_name" class="form-label">Campaign Name *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_start_datetime" class="form-label">Start Date & Time *</label>
                            <input type="datetime-local" class="form-control" id="edit_start_datetime" name="start_datetime" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_end_datetime" class="form-label">End Date & Time *</label>
                            <input type="datetime-local" class="form-control" id="edit_end_datetime" name="end_datetime" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_percentage_bonus" class="form-label">User Bonus Percentage *</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="edit_percentage_bonus" name="percentage_bonus" min="0" max="100" step="0.01" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_percentage_upline_bonus" class="form-label">Upline Bonus Percentage *</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="edit_percentage_upline_bonus" name="percentage_upline_bonus" min="0" max="100" step="0.01" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">
                                    Campaign is active
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Campaign Modal -->
<div class="modal fade" id="viewCampaignModal" tabindex="-1" aria-labelledby="viewCampaignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCampaignModalLabel">Campaign Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewCampaignContent">
                <!-- Campaign details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables
    var campaignsTable = $('#campaignsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.deposit-promo.campaigns.list") }}',
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'creator_name', name: 'creator_name' },
            { data: 'status', name: 'status', orderable: false },
            { data: 'duration', name: 'duration', orderable: false },
            { data: 'bonuses', name: 'bonuses', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        responsive: true,
        language: {
            processing: "Loading campaigns..."
        }
    });

    var bonusHistoryTable = $('#bonusHistoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.deposit-promo.bonus-history.list") }}',
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'user_info', name: 'user_info' },
            { data: 'campaign_name', name: 'campaign_name' },
            { data: 'amounts', name: 'amounts', orderable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false },
            { data: 'expires_info', name: 'expires_info' },
            { data: 'claimed_info', name: 'claimed_info' }
        ],
        order: [[0, 'desc']],
        responsive: true,
        language: {
            processing: "Loading bonus history..."
        }
    });

    // Create Campaign Form
    $('#createCampaignForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route("admin.deposit-promo.campaigns.store") }}',
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#createCampaignModal').modal('hide');
                    $('#createCampaignForm')[0].reset();
                    campaignsTable.ajax.reload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 3000
                    });
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var errorMessage = 'Validation failed:\n';

                for (var field in errors) {
                    errorMessage += '• ' + errors[field][0] + '\n';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage
                });
            }
        });
    });

    // Edit Campaign Form
    $('#editCampaignForm').on('submit', function(e) {
        e.preventDefault();

        var campaignId = $('#edit_campaign_id').val();

        $.ajax({
            url: '{{ route("admin.deposit-promo.campaigns.update", ":id") }}'.replace(':id', campaignId),
            type: 'PUT',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#editCampaignModal').modal('hide');
                    campaignsTable.ajax.reload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 3000
                    });
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var errorMessage = 'Validation failed:\n';

                for (var field in errors) {
                    errorMessage += '• ' + errors[field][0] + '\n';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage
                });
            }
        });
    });

    // Tab switching - reload table when switching to bonus history tab
    $('button[data-bs-target="#bonus-history"]').on('shown.bs.tab', function (e) {
        bonusHistoryTable.ajax.reload();
    });

    // Add datetime validation
    $('#start_datetime').on('change', function() {
        var startDate = new Date($(this).val());
        var minEndDate = new Date(startDate.getTime() + 60 * 60 * 1000); // Add 1 hour
        $('#end_datetime').attr('min', minEndDate.toISOString().slice(0, 16));
    });

    $('#edit_start_datetime').on('change', function() {
        var startDate = new Date($(this).val());
        var minEndDate = new Date(startDate.getTime() + 60 * 60 * 1000); // Add 1 hour
        $('#edit_end_datetime').attr('min', minEndDate.toISOString().slice(0, 16));
    });
});

// View Campaign
function viewCampaign(campaignId) {
    $.ajax({
        url: '{{ route("admin.deposit-promo.campaigns.show", ":id") }}'.replace(':id', campaignId),
        type: 'GET',
        success: function(response) {
            if (response.success) {
                var campaign = response.campaign;
                var content = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6><strong>Campaign Name:</strong></h6>
                            <p>${campaign.name}</p>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>Creator:</strong></h6>
                            <p>${campaign.creator ? campaign.creator.name : 'Unknown'}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h6><strong>Description:</strong></h6>
                            <p>${campaign.description || 'No description provided'}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6><strong>Start Date:</strong></h6>
                            <p>${new Date(campaign.start_datetime).toLocaleString()}</p>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>End Date:</strong></h6>
                            <p>${new Date(campaign.end_datetime).toLocaleString()}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6><strong>User Bonus:</strong></h6>
                            <p>${campaign.percentage_bonus}%</p>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>Upline Bonus:</strong></h6>
                            <p>${campaign.percentage_upline_bonus}%</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6><strong>Status:</strong></h6>
                            <p>${campaign.is_active ? 'Active' : 'Inactive'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>Created:</strong></h6>
                            <p>${new Date(campaign.created_at).toLocaleString()}</p>
                        </div>
                    </div>
                `;

                $('#viewCampaignContent').html(content);
                $('#viewCampaignModal').modal('show');
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to load campaign details'
            });
        }
    });
}

// Edit Campaign
function editCampaign(campaignId) {
    $.ajax({
        url: '{{ route("admin.deposit-promo.campaigns.show", ":id") }}'.replace(':id', campaignId),
        type: 'GET',
        success: function(response) {
            if (response.success) {
                var campaign = response.campaign;

                $('#edit_campaign_id').val(campaign.id);
                $('#edit_name').val(campaign.name);
                $('#edit_description').val(campaign.description);
                $('#edit_start_datetime').val(campaign.start_datetime.replace('Z', '').slice(0, 16));
                $('#edit_end_datetime').val(campaign.end_datetime.replace('Z', '').slice(0, 16));
                $('#edit_percentage_bonus').val(campaign.percentage_bonus);
                $('#edit_percentage_upline_bonus').val(campaign.percentage_upline_bonus);
                $('#edit_is_active').prop('checked', campaign.is_active);

                $('#editCampaignModal').modal('show');
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to load campaign details'
            });
        }
    });
}

// Delete Campaign
function deleteCampaign(campaignId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.deposit-promo.campaigns.destroy", ":id") }}'.replace(':id', campaignId),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#campaignsTable').DataTable().ajax.reload();

                        Swal.fire(
                            'Deleted!',
                            response.message,
                            'success'
                        );
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON.message || 'Failed to delete campaign'
                    });
                }
            });
        }
    });
}
</script>
@endsection
