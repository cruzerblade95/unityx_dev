@php
    $configData = Helper::appClasses();
@endphp

@extends('admin.layouts.layoutMaster')

@section('title', 'Withdrawal Settings Management')

@section('vendor-style')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
    <script>
        let selectedUsers = [];
        let currentUserSettings = {};
        let usersTable;

        document.addEventListener('DOMContentLoaded', function() {
            initializeDataTable();
        });

        function initializeDataTable() {
            usersTable = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/admin/withdrawal-settings/users-data',
                    type: 'GET'
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        width: '50px'
                    },
                    {
                        data: 'user_info',
                        name: 'user_info',
                        orderable: true
                    },
                    {
                        data: 'crypto',
                        name: 'crypto',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'etc_black',
                        name: 'etc_black',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'gold',
                        name: 'gold',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        width: '150px'
                    }
                ],
                pageLength: 25,
                responsive: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                language: {
                    search: "Search users:",
                    lengthMenu: "Show _MENU_ users per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ users",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                drawCallback: function(settings) {
                    // Restore selections after each draw
                    restoreSelections();
                    // Load settings for visible users
                    loadVisibleUserSettings();
                },
                createdRow: function(row, data, dataIndex) {
                    // Store user ID in row for easy access
                    $(row).attr('data-user-id', data.id);
                }
            });

            // Handle individual checkbox change
            $('#usersTable tbody').on('change', 'input.user-checkbox', function() {
                const userId = $(this).val();

                if ($(this).is(':checked')) {
                    if (!selectedUsers.includes(userId)) {
                        selectedUsers.push(userId);
                    }
                } else {
                    selectedUsers = selectedUsers.filter(id => id !== userId);
                }

                updateSelectedCount();
                updateSelectAllCheckbox();
            });

            // Handle select all checkbox
            $('#select-all-checkbox').on('change', function() {
                const isChecked = $(this).is(':checked');

                if (isChecked) {
                    // Select all visible users
                    $('#usersTable tbody input.user-checkbox').each(function() {
                        const userId = $(this).val();
                        if (!selectedUsers.includes(userId)) {
                            selectedUsers.push(userId);
                        }
                        $(this).prop('checked', true);
                    });
                } else {
                    // Deselect all visible users
                    $('#usersTable tbody input.user-checkbox').each(function() {
                        const userId = $(this).val();
                        selectedUsers = selectedUsers.filter(id => id !== userId);
                        $(this).prop('checked', false);
                    });
                }

                updateSelectedCount();
            });
        }

        function restoreSelections() {
            // Restore checkboxes for selected users
            $('#usersTable tbody input.user-checkbox').each(function() {
                const userId = $(this).val();
                $(this).prop('checked', selectedUsers.includes(userId));
            });

            updateSelectAllCheckbox();
        }

        function updateSelectAllCheckbox() {
            const visibleCheckboxes = $('#usersTable tbody input.user-checkbox');
            const visibleChecked = $('#usersTable tbody input.user-checkbox:checked');

            if (visibleCheckboxes.length === 0) {
                $('#select-all-checkbox').prop('indeterminate', false).prop('checked', false);
            } else if (visibleChecked.length === visibleCheckboxes.length) {
                $('#select-all-checkbox').prop('indeterminate', false).prop('checked', true);
            } else if (visibleChecked.length > 0) {
                $('#select-all-checkbox').prop('indeterminate', true);
            } else {
                $('#select-all-checkbox').prop('indeterminate', false).prop('checked', false);
            }
        }

        function loadVisibleUserSettings() {
            $('#usersTable tbody tr').each(function() {
                const userId = $(this).attr('data-user-id');
                if (userId) {
                    loadUserSettingsPreview(userId);
                }
            });
        }

        function loadUserSettingsPreview(userId) {
            fetch(`/admin/withdrawal-settings/user/${userId}/settings`)
                .then(response => response.json())
                .then(data => {
                    const types = ['crypto', 'etc_black', 'gold'];
                    types.forEach(type => {
                        const element = document.getElementById(`${type}-${userId}`);
                        if (element) {
                            const settings = data.settings[type];
                            if (settings) {
                                element.innerHTML = `
                            <span class="badge ${settings.is_enabled ? 'bg-success' : 'bg-danger'} mb-1">
                                ${settings.is_enabled ? 'Enabled' : 'Disabled'}
                            </span><br>
                            <small>Min: $${settings.min_amount}</small><br>
                            <small>Max: $${settings.max_amount}</small><br>
                            <small>Fee: ${settings.processing_fee_percent}%</small><br>
                            <small>Cooldown: ${settings.cooldown_hours}h</small>
                        `;
                            } else {
                                element.innerHTML = '<small class="text-muted">Using defaults</small>';
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading settings:', error);
                });
        }

        function updateSelectedCount() {
            const selectedInfo = document.getElementById('selected-info');
            const selectedCount = document.getElementById('selected-count');

            selectedCount.textContent = selectedUsers.length;

            if (selectedUsers.length > 0) {
                selectedInfo.classList.remove('d-none');
            } else {
                selectedInfo.classList.add('d-none');
            }
        }

        function showSelectedUsers() {
            if (selectedUsers.length === 0) {
                alert('No users selected');
                return;
            }

            const userIds = selectedUsers.join(', ');
            alert(`Selected User IDs: ${userIds}`);
        }

        function clearAllSelections() {
            selectedUsers = [];
            $('#usersTable tbody input.user-checkbox').prop('checked', false);
            $('#select-all-checkbox').prop('checked', false).prop('indeterminate', false);
            updateSelectedCount();
        }

        function editUserSettings(userId) {
            fetch(`/admin/withdrawal-settings/user/${userId}/settings`)
                .then(response => response.json())
                .then(data => {
                    currentUserSettings = data.settings;

                    document.getElementById('edit-user-id').value = userId;
                    document.getElementById('edit-user-name').textContent = data.user.firstname + ' ' + data.user
                        .lastname;
                    document.getElementById('edit-user-email').textContent = data.user.email;

                    // Load initial settings for crypto
                    document.getElementById('edit-withdrawal-type').value = 'crypto';
                    loadUserSettingsForType();

                    new bootstrap.Modal(document.getElementById('userSettingsModal')).show();
                })
                .catch(error => {
                    console.error('Error loading user settings:', error);
                    alert('Error loading user settings');
                });
        }

        function loadUserSettingsForType() {
            const type = document.getElementById('edit-withdrawal-type').value;
            const settings = currentUserSettings[type];

            if (settings) {
                document.getElementById('edit-min-amount').value = settings.min_amount;
                document.getElementById('edit-max-amount').value = settings.max_amount;
                document.getElementById('edit-fee-percent').value = settings.processing_fee_percent;
                document.getElementById('edit-fee-fixed').value = settings.processing_fee_fixed;
                document.getElementById('edit-cooldown-hours').value = settings.cooldown_hours;
                document.getElementById('edit-is-enabled').checked = settings.is_enabled;
            } else {
                // Clear form for new settings
                document.getElementById('edit-min-amount').value = '';
                document.getElementById('edit-max-amount').value = '';
                document.getElementById('edit-fee-percent').value = '';
                document.getElementById('edit-fee-fixed').value = '';
                document.getElementById('edit-cooldown-hours').value = '';
                document.getElementById('edit-is-enabled').checked = true;
            }
        }

        function saveUserSettings() {
            const userId = document.getElementById('edit-user-id').value;
            const withdrawalType = document.getElementById('edit-withdrawal-type').value;
            const enabledCheckbox = document.getElementById('edit-is-enabled');

            const formData = {
                withdrawal_type: withdrawalType,
                min_amount: parseFloat(document.getElementById('edit-min-amount').value),
                max_amount: parseFloat(document.getElementById('edit-max-amount').value),
                processing_fee_percent: parseFloat(document.getElementById('edit-fee-percent').value),
                processing_fee_fixed: parseFloat(document.getElementById('edit-fee-fixed').value),
                cooldown_hours: parseInt(document.getElementById('edit-cooldown-hours').value),
                is_enabled: enabledCheckbox ? enabledCheckbox.checked : false
            };

            console.log('Saving user settings:', formData);

            fetch(`/admin/withdrawal-settings/user/${userId}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('userSettingsModal')).hide();
                        loadUserSettingsPreview(userId);
                        alert(data.message);
                    } else {
                        alert(`Error updating settings: ${data.message || 'Unknown error'}`);
                    }
                })
                .catch(error => {
                    console.error('Error saving settings:', error);
                    alert(`Error saving settings: ${error.message}`);
                });
        }

        function showBulkUpdateModal() {
            if (selectedUsers.length === 0) {
                alert('Please select users first');
                return;
            }
            new bootstrap.Modal(document.getElementById('bulkUpdateModal')).show();
        }

        function processBulkUpdate() {
            const formData = {
                user_ids: selectedUsers,
                withdrawal_type: document.getElementById('bulk-withdrawal-type').value,
                min_amount: document.getElementById('bulk-min-amount').value || null,
                max_amount: document.getElementById('bulk-max-amount').value || null,
                processing_fee_percent: document.getElementById('bulk-fee-percent').value || null,
                processing_fee_fixed: document.getElementById('bulk-fee-fixed').value || null,
                cooldown_hours: document.getElementById('bulk-cooldown-hours').value || null,
                is_enabled: document.getElementById('bulk-is-enabled').checked
            };

            fetch('/admin/withdrawal-settings/bulk-update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('bulkUpdateModal')).hide();
                        loadVisibleUserSettings();
                        alert(data.message);
                    } else {
                        alert('Error updating settings');
                    }
                })
                .catch(error => {
                    console.error('Error with bulk update:', error);
                    alert('Error with bulk update');
                });
        }

        function showResetModal() {
            if (selectedUsers.length === 0) {
                alert('Please select users first');
                return;
            }
            new bootstrap.Modal(document.getElementById('resetModal')).show();
        }

        function processReset() {
            const formData = {
                user_ids: selectedUsers,
                withdrawal_type: document.getElementById('reset-withdrawal-type').value || null
            };

            fetch('/admin/withdrawal-settings/reset-defaults', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('resetModal')).hide();
                        loadVisibleUserSettings();
                        alert(data.message);
                    } else {
                        alert('Error resetting settings');
                    }
                })
                .catch(error => {
                    console.error('Error resetting settings:', error);
                    alert('Error resetting settings');
                });
        }

        function showDisableModal() {
            if (selectedUsers.length === 0) {
                alert('Please select users first');
                return;
            }
            new bootstrap.Modal(document.getElementById('disableModal')).show();
        }

        function processDisableEnable() {
            const action = document.getElementById('disable-action').value;
            const withdrawalTypes = [];

            if (document.getElementById('disable-crypto').checked) withdrawalTypes.push('crypto');
            if (document.getElementById('disable-etc-black').checked) withdrawalTypes.push('etc_black');
            if (document.getElementById('disable-gold').checked) withdrawalTypes.push('gold');

            if (!action || withdrawalTypes.length === 0) {
                alert('Please select action and withdrawal types');
                return;
            }

            const endpoint = action === 'disable' ? 'disable-withdrawals' : 'enable-withdrawals';

            const formData = {
                user_ids: selectedUsers,
                withdrawal_types: withdrawalTypes
            };

            fetch(`/admin/withdrawal-settings/${endpoint}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('disableModal')).hide();
                        loadVisibleUserSettings();
                        alert(data.message);
                    } else {
                        alert('Error updating withdrawal status');
                    }
                })
                .catch(error => {
                    console.error('Error updating withdrawal status:', error);
                    alert('Error updating withdrawal status');
                });
        }

        // Maintenance Mode Toggle Function
        function toggleMaintenanceMode() {
            const isMaintenanceMode = {{ $isMaintenanceMode ? 'true' : 'false' }};
            const maintenanceMessage = {!! json_encode($maintenanceMessage) !!};

            Swal.fire({
                title: 'Toggle Withdrawal Maintenance Mode',
                html: `
                    <div class="mb-3">
                        <label class="form-label">Current Status: <span class="badge bg-${isMaintenanceMode ? 'danger' : 'success'}">${isMaintenanceMode ? 'Maintenance ON' : 'Maintenance OFF'}</span></label>
                    </div>
                    <div class="mb-3">
                        <label for="maintenance-message" class="form-label">Maintenance Message:</label>
                        <textarea id="maintenance-message" class="form-control" rows="3"
                                  placeholder="Enter maintenance message for users...">${maintenanceMessage}</textarea>
                    </div>
                    <div class="alert alert-warning">
                        <small><i class="fas fa-exclamation-triangle me-1"></i>
                        This will ${isMaintenanceMode ? 'disable' : 'enable'} maintenance mode for ALL withdrawal functionality globally.</small>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: isMaintenanceMode ? '#28a745' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: isMaintenanceMode ? 'Disable Maintenance' : 'Enable Maintenance',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const message = document.getElementById('maintenance-message').value;
                    return { message: message };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = {
                        enabled: !isMaintenanceMode,
                        message: result.value.message
                    };                    fetch('/admin/withdrawal-settings/toggle-maintenance', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success'
                            }).then(() => {
                                location.reload(); // Reload to update button state
                            });
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error toggling maintenance mode:', error);
                        Swal.fire('Error!', 'Failed to toggle maintenance mode', 'error');
                    });
                }
            });
        }
    </script>
@endsection

@section('content')
    <div class="row g-6">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Withdrawal Settings Management</h4>
                    <div class="d-flex gap-2">
                        <!-- Maintenance Mode Toggle -->
                        <button class="btn {{ $isMaintenanceMode ? 'btn-danger' : 'btn-success' }}" onclick="toggleMaintenanceMode()">
                            <i class="fas {{ $isMaintenanceMode ? 'fa-tools' : 'fa-shield-check' }}"></i>
                            {{ $isMaintenanceMode ? 'Maintenance ON' : 'Maintenance OFF' }}
                        </button>
                        <a href="{{ route('admin.withdrawal-settings.global-defaults') }}" class="btn btn-primary">
                            <i class="fas fa-cog"></i> Global Defaults
                        </a>
                        <button class="btn btn-success" onclick="showBulkUpdateModal()">
                            <i class="fas fa-edit"></i> Bulk Update
                        </button>
                        <button class="btn btn-warning" onclick="showResetModal()">
                            <i class="fas fa-undo"></i> Reset to Defaults
                        </button>
                        <button class="btn btn-danger" onclick="showDisableModal()">
                            <i class="fas fa-ban"></i> Disable Withdrawals
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Selected Users Info -->
                    <div id="selected-info" class="alert alert-info d-none">
                        <span id="selected-count">0</span> user(s) selected
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="showSelectedUsers()">
                            <i class="fas fa-eye"></i> View Selected
                        </button>
                        <button class="btn btn-sm btn-outline-secondary ms-1" onclick="clearAllSelections()">
                            <i class="fas fa-times"></i> Clear All
                        </button>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table id="usersTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="select-all-checkbox">
                                    </th>
                                    <th>User Info</th>
                                    <th>Crypto Settings</th>
                                    <th>Crypto Bank Card Settings</th>
                                    <th>Gold Settings</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Settings Modal -->
    <div class="modal fade" id="userSettingsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User Withdrawal Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userSettingsForm">
                        <input type="hidden" id="edit-user-id">

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6 id="edit-user-name"></h6>
                                <small class="text-muted" id="edit-user-email"></small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Withdrawal Type</label>
                                <select class="form-select" id="edit-withdrawal-type" onchange="loadUserSettingsForType()">
                                    <option value="crypto">Cryptocurrency</option>
                                    <option value="etc_black">Crypto Bank Card</option>
                                    <option value="gold">Gold</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="edit-is-enabled">
                                    <label class="form-check-label" for="edit-is-enabled">
                                        Enable Withdrawals
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Minimum Amount</label>
                                <input type="number" class="form-control" id="edit-min-amount" step="0.01"
                                    min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Maximum Amount</label>
                                <input type="number" class="form-control" id="edit-max-amount" step="0.01"
                                    min="0">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Processing Fee (%)</label>
                                <input type="number" class="form-control" id="edit-fee-percent" step="0.01"
                                    min="0" max="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fixed Processing Fee</label>
                                <input type="number" class="form-control" id="edit-fee-fixed" step="0.01"
                                    min="0">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Cooldown Period (Hours)</label>
                                <input type="number" class="form-control" id="edit-cooldown-hours" min="0">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveUserSettings()">Save Settings</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Update Modal -->
    <div class="modal fade" id="bulkUpdateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Update Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="bulkUpdateForm">
                        <div class="mb-3">
                            <label class="form-label">Withdrawal Type</label>
                            <select class="form-select" id="bulk-withdrawal-type" required>
                                <option value="">Select type...</option>
                                <option value="crypto">Cryptocurrency</option>
                                <option value="etc_black">Crypto Bank Card</option>
                                <option value="gold">Gold</option>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Minimum Amount</label>
                                <input type="number" class="form-control" id="bulk-min-amount" step="0.01"
                                    min="0">
                                <small class="text-muted">Leave empty to keep current values</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Maximum Amount</label>
                                <input type="number" class="form-control" id="bulk-max-amount" step="0.01"
                                    min="0">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Processing Fee (%)</label>
                                <input type="number" class="form-control" id="bulk-fee-percent" step="0.01"
                                    min="0" max="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fixed Processing Fee</label>
                                <input type="number" class="form-control" id="bulk-fee-fixed" step="0.01"
                                    min="0">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Cooldown Period (Hours)</label>
                                <input type="number" class="form-control" id="bulk-cooldown-hours" min="0">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="bulk-is-enabled">
                                    <label class="form-check-label" for="bulk-is-enabled">
                                        Enable Withdrawals
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="processBulkUpdate()">Update Selected
                        Users</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Modal -->
    <div class="modal fade" id="resetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset to Default Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="resetForm">
                        <div class="mb-3">
                            <label class="form-label">Reset Type</label>
                            <select class="form-select" id="reset-withdrawal-type">
                                <option value="">All withdrawal types</option>
                                <option value="crypto">Cryptocurrency only</option>
                                <option value="etc_black">Crypto Bank Card only</option>
                                <option value="gold">Gold only</option>
                            </select>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            This will remove all custom settings for selected users and revert them to global defaults.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="processReset()">Reset Selected Users</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Disable Modal -->
    <div class="modal fade" id="disableModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Disable/Enable Withdrawals</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="disableForm">
                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <select class="form-select" id="disable-action" required>
                                <option value="">Select action...</option>
                                <option value="disable">Disable Withdrawals</option>
                                <option value="enable">Enable Withdrawals</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Withdrawal Types</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disable-crypto" value="crypto">
                                <label class="form-check-label" for="disable-crypto">Cryptocurrency</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disable-etc-black"
                                    value="etc_black">
                                <label class="form-check-label" for="disable-etc-black">Crypto Bank Card</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disable-gold" value="gold">
                                <label class="form-check-label" for="disable-gold">Gold</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="processDisableEnable()">Apply
                        Changes</button>
                </div>
            </div>
        </div>
    </div>

@endsection
