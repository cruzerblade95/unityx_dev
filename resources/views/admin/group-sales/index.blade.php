@extends('admin/layouts/layoutMaster')

@section('title', 'Group Sales Management')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
    <style>
        .form-control.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        .form-text {
            font-size: 0.875em;
            color: #6c757d;
        }
        .reward-level-info {
            background-color: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 4px;
            padding: 8px 12px;
            margin-top: 5px;
            font-size: 0.875em;
        }
        .modal-body .mb-3:last-child {
            margin-bottom: 0.5rem !important;
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/jquery/jquery.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Fallback jQuery if Vite fails -->
    <script>
        if (typeof jQuery === 'undefined') {
            document.write('<script src="https://code.jquery.com/jquery-3.7.1.min.js"><\/script>');
        }
    </script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Summary Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="avatar mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="fas fa-trophy fa-lg"></i>
                        </span>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Rewards</span>
                    <h3 class="card-title mb-2" id="total-rewards">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="avatar mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="fas fa-layer-group fa-lg"></i>
                        </span>
                    </div>
                    <span class="fw-semibold d-block mb-1">Active Levels</span>
                    <h3 class="card-title mb-2" id="active-levels">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="avatar mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="fas fa-chart-line fa-lg"></i>
                        </span>
                    </div>
                    <span class="fw-semibold d-block mb-1">Highest Level</span>
                    <h3 class="card-title mb-2" id="highest-level">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="avatar mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="fas fa-dollar-sign fa-lg"></i>
                        </span>
                    </div>
                    <span class="fw-semibold d-block mb-1">Max Reward</span>
                    <h3 class="card-title mb-2" id="max-reward">-</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Group Sales Rewards Management</h5>
                    <div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRewardModal">
                            <i class="fas fa-plus me-1"></i> Add Reward
                        </button>
                        <a href="{{ route('admin.group-sales.history') }}" class="btn btn-info">
                            <i class="fas fa-history me-1"></i> View History
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="rewardsTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Reward Level</th>
                                    <th>Reward Title</th>
                                    <th>Sales Required</th>
                                    <th>Base Percentage</th>
                                    <th>Tax Percentage</th>
                                    <th>Reward Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Reward Modal -->
<div class="modal fade" id="addRewardModal" tabindex="-1" aria-labelledby="addRewardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRewardModalLabel">Add New Reward</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addRewardForm">
                @csrf
                <div class="modal-body">
                    <div class="reward-level-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Reward Level Guide:</strong> You can create up to 50 different reward levels. Each level should have unique requirements and progressively higher rewards to motivate group sales performance.
                    </div>
                    <div class="mb-3">
                        <label for="reward_level" class="form-label">Reward Level</label>
                        <input type="number" class="form-control" id="reward_level" name="reward_level" min="1" max="50" required>
                        <div class="form-text">Enter a level between 1 and 50</div>
                    </div>
                    <div class="mb-3">
                        <label for="reward_title" class="form-label">Reward Title</label>
                        <input type="text" class="form-control" id="reward_title" name="reward_title" required placeholder="e.g., Silver Achiever, Gold Master, Diamond Elite">
                        <div class="form-text">Enter a descriptive title for this reward level</div>
                    </div>
                    <div class="mb-3">
                        <label for="sales_required" class="form-label">Sales Required</label>
                        <input type="number" class="form-control" id="sales_required" name="sales_required" step="0.01" required placeholder="0.00">
                        <div class="form-text">Minimum sales amount required to qualify for this reward</div>
                    </div>
                    <div class="mb-3">
                        <label for="base_percentage" class="form-label">Base Percentage (%)</label>
                        <input type="number" class="form-control" id="base_percentage" name="base_percentage" step="0.01" value="15.00" min="0" max="100" required>
                        <div class="form-text">Base percentage for reward calculation (0-100%)</div>
                    </div>
                    <div class="mb-3">
                        <label for="tax_percentage" class="form-label">Tax Percentage (%)</label>
                        <input type="number" class="form-control" id="tax_percentage" name="tax_percentage" step="0.01" min="0" max="100" required placeholder="0.00">
                        <div class="form-text">Tax percentage applied to the reward (0-100%)</div>
                    </div>
                    <div class="alert alert-info" id="reward_preview" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Reward</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Reward Modal -->
<div class="modal fade" id="editRewardModal" tabindex="-1" aria-labelledby="editRewardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRewardModalLabel">Edit Reward</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRewardForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_reward_id" name="reward_id">
                <div class="modal-body">
                    <div class="reward-level-info mb-3">
                        <i class="fas fa-edit me-2"></i>
                        <strong>Editing Reward Level:</strong> You can modify the reward level (1-50) and all associated parameters. Ensure the new level doesn't conflict with existing rewards.
                    </div>
                    <div class="mb-3">
                        <label for="edit_reward_level" class="form-label">Reward Level</label>
                        <input type="number" class="form-control" id="edit_reward_level" name="reward_level" min="1" max="50" required>
                        <div class="form-text">Enter a level between 1 and 50</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_reward_title" class="form-label">Reward Title</label>
                        <input type="text" class="form-control" id="edit_reward_title" name="reward_title" required placeholder="e.g., Silver Achiever, Gold Master, Diamond Elite">
                        <div class="form-text">Enter a descriptive title for this reward level</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_sales_required" class="form-label">Sales Required</label>
                        <input type="number" class="form-control" id="edit_sales_required" name="sales_required" step="0.01" required placeholder="0.00">
                        <div class="form-text">Minimum sales amount required to qualify for this reward</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_base_percentage" class="form-label">Base Percentage (%)</label>
                        <input type="number" class="form-control" id="edit_base_percentage" name="base_percentage" step="0.01" min="0" max="100" required>
                        <div class="form-text">Base percentage for reward calculation (0-100%)</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tax_percentage" class="form-label">Tax Percentage (%)</label>
                        <input type="number" class="form-control" id="edit_tax_percentage" name="tax_percentage" step="0.01" min="0" max="100" required placeholder="0.00">
                        <div class="form-text">Tax percentage applied to the reward (0-100%)</div>
                    </div>
                    <div class="alert alert-info" id="edit_reward_preview" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Reward</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Check if jQuery and DataTables are available
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }

    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables is not loaded');
        return;
    }

    // Initialize DataTable
    const table = $('#rewardsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.group-sales.data") }}',
            type: 'GET',
            dataSrc: function(response) {
                // Update stats when data loads
                updateStats(response.data);
                return response.data;
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'reward_level', name: 'reward_level' },
            { data: 'reward_title', name: 'reward_title' },
            { data: 'sales_required', name: 'sales_required', render: function(data) {
                return '$' + parseFloat(data).toFixed(2);
            }},
            { data: 'base_percentage', name: 'base_percentage', render: function(data) {
                return parseFloat(data).toFixed(2) + '%';
            }},
            { data: 'tax_percentage', name: 'tax_percentage', render: function(data) {
                return parseFloat(data).toFixed(2) + '%';
            }},
            { data: 'reward_amount', name: 'reward_amount', render: function(data) {
                return '$' + parseFloat(data).toFixed(2);
            }},
            { data: 'is_active', name: 'is_active', render: function(data) {
                return data ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
            }},
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']], // Order by reward_level
        responsive: true
    });

    // Update stats function
    function updateStats(data) {
        const totalRewards = data.length;
        const activeRewards = data.filter(item => item.is_active).length;
        const highestLevel = data.length > 0 ? Math.max(...data.map(item => parseInt(item.reward_level))) : 0;
        const maxReward = data.length > 0 ? Math.max(...data.map(item => parseFloat(item.reward_amount))) : 0;

        $('#total-rewards').text(totalRewards);
        $('#active-levels').text(activeRewards);
        $('#highest-level').text(highestLevel);
        $('#max-reward').text('$' + maxReward.toFixed(2));
    }

    // Reward Level validation
    function validateRewardLevel(input) {
        const value = parseInt(input.val());
        const min = parseInt(input.attr('min'));
        const max = parseInt(input.attr('max'));

        if (value < min || value > max) {
            input.addClass('is-invalid');
            return false;
        } else {
            input.removeClass('is-invalid');
            return true;
        }
    }

    // Real-time validation for reward level inputs
    $('#reward_level, #edit_reward_level').on('input', function() {
        validateRewardLevel($(this));
    });

    // Percentage validation
    function validatePercentage(input) {
        const value = parseFloat(input.val());

        if (value < 0 || value > 100) {
            input.addClass('is-invalid');
            return false;
        } else {
            input.removeClass('is-invalid');
            return true;
        }
    }

    // Real-time validation for percentage inputs
    $('#base_percentage, #tax_percentage, #edit_base_percentage, #edit_tax_percentage').on('input', function() {
        validatePercentage($(this));
    });

    // Real-time reward calculation preview
    function calculateRewardPreview(isEdit = false) {
        const prefix = isEdit ? 'edit_' : '';
        const salesRequired = parseFloat($('#' + prefix + 'sales_required').val()) || 0;
        const basePercentage = parseFloat($('#' + prefix + 'base_percentage').val()) || 0;
        const taxPercentage = parseFloat($('#' + prefix + 'tax_percentage').val()) || 0;

        const rewardAmount = salesRequired * (basePercentage / 100) * (taxPercentage / 100);
        const previewId = isEdit ? '#edit_reward_preview' : '#reward_preview';

        if (salesRequired > 0 && basePercentage > 0 && taxPercentage > 0) {
            $(previewId).html('<i class="fas fa-calculator me-2"></i>Calculated Reward: <strong>$' + rewardAmount.toFixed(2) + '</strong>').show();
        } else {
            $(previewId).hide();
        }
    }

    // Add reward calculation listeners
    $('#sales_required, #base_percentage, #tax_percentage').on('input', function() {
        calculateRewardPreview(false);
    });

    $('#edit_sales_required, #edit_base_percentage, #edit_tax_percentage').on('input', function() {
        calculateRewardPreview(true);
    });

    // Reset form validation when modals are closed
    $('#addRewardModal').on('hidden.bs.modal', function() {
        $('#addRewardForm')[0].reset();
        $('#addRewardForm').find('.is-invalid').removeClass('is-invalid');
        $('#reward_preview').hide();
    });

    $('#editRewardModal').on('hidden.bs.modal', function() {
        $('#editRewardForm').find('.is-invalid').removeClass('is-invalid');
        $('#edit_reward_preview').hide();
    });

    // Show reward calculation when edit modal opens
    $('#editRewardModal').on('shown.bs.modal', function() {
        calculateRewardPreview(true);
    });

    // Add Reward Form
    $('#addRewardForm').on('submit', function(e) {
        e.preventDefault();

        // Client-side validation
        let isValid = true;
        isValid = validateRewardLevel($('#reward_level')) && isValid;
        isValid = validatePercentage($('#base_percentage')) && isValid;
        isValid = validatePercentage($('#tax_percentage')) && isValid;

        if (!isValid) {
            Swal.fire('Validation Error!', 'Please fix the validation errors before submitting.', 'error');
            return;
        }

        $.ajax({
            url: '{{ route("admin.group-sales.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#addRewardModal').modal('hide');
                table.ajax.reload();
                Swal.fire('Success!', 'Reward added successfully.', 'success');
                $('#addRewardForm')[0].reset();
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = 'Please fix the following errors:\n';
                for (let field in errors) {
                    errorMessage += '- ' + errors[field][0] + '\n';
                }
                Swal.fire('Error!', errorMessage, 'error');
            }
        });
    });

    // Edit Reward
    $(document).on('click', '.edit-reward', function() {
        const id = $(this).data('id');

        // Load reward data
        $.get('{{ route("admin.group-sales.show", ":id") }}'.replace(':id', id), function(response) {
            if (response.success) {
                const reward = response.data;
                $('#edit_reward_id').val(reward.id);
                $('#edit_reward_level').val(reward.reward_level);
                $('#edit_reward_title').val(reward.reward_title);
                $('#edit_sales_required').val(reward.sales_required);
                $('#edit_base_percentage').val(reward.base_percentage);
                $('#edit_tax_percentage').val(reward.tax_percentage);
                $('#editRewardModal').modal('show');
            } else {
                Swal.fire('Error!', 'Failed to load reward data.', 'error');
            }
        }).fail(function() {
            Swal.fire('Error!', 'Failed to load reward data.', 'error');
        });
    });

    // Edit Reward Form
    $('#editRewardForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit_reward_id').val();

        // Client-side validation
        let isValid = true;
        isValid = validateRewardLevel($('#edit_reward_level')) && isValid;
        isValid = validatePercentage($('#edit_base_percentage')) && isValid;
        isValid = validatePercentage($('#edit_tax_percentage')) && isValid;

        if (!isValid) {
            Swal.fire('Validation Error!', 'Please fix the validation errors before submitting.', 'error');
            return;
        }

        $.ajax({
            url: '{{ route("admin.group-sales.update", ":id") }}'.replace(':id', id),
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                $('#editRewardModal').modal('hide');
                table.ajax.reload();
                Swal.fire('Success!', 'Reward updated successfully.', 'success');
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = 'Please fix the following errors:\n';
                for (let field in errors) {
                    errorMessage += '- ' + errors[field][0] + '\n';
                }
                Swal.fire('Error!', errorMessage, 'error');
            }
        });
    });

    // Delete Reward
    $(document).on('click', '.delete-reward', function() {
        const id = $(this).data('id');

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
                    url: '{{ route("admin.group-sales.destroy", ":id") }}'.replace(':id', id),
                    method: 'DELETE',
                    data: {
                        '_token': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        table.ajax.reload();
                        Swal.fire('Deleted!', 'Reward has been deleted.', 'success');
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
