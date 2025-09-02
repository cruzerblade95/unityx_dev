@php
    $configData = Helper::appClasses();
@endphp

@extends('admin.layouts.layoutMaster')

@section('title', 'Reinvestment Settings Management')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
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
                    url: '/admin/reinvestment-settings/users-data',
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
                        data: 'profit',
                        name: 'profit',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'bonus',
                        name: 'bonus',
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
            fetch(`/admin/reinvestment-settings/user/${userId}/settings`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const types = ['profit', 'bonus'];
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
                                <small>Tax: ${settings.tax_percent}%</small>
                            `;
                                } else {
                                    element.innerHTML = '<small class="text-muted">Using defaults</small>';
                                }
                            }
                        });
                    } else {
                        console.error('Error loading user settings:', data.message);
                        // Show fallback content for this user
                        const types = ['profit', 'bonus'];
                        types.forEach(type => {
                            const element = document.getElementById(`${type}-${userId}`);
                            if (element) {
                                element.innerHTML = '<small class="text-danger">Error loading settings</small>';
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading user settings:', error);
                });
        }

        function updateSelectedCount() {
            const count = selectedUsers.length;
            document.getElementById('selected-count').textContent = count;
            document.getElementById('bulk-actions').style.display = count > 0 ? 'block' : 'none';
        }

        function editUserSettings(userId) {
            console.log('Editing user settings for user ID:', userId);

            const url = `/admin/reinvestment-settings/user/${userId}/settings`;
            console.log('Fetching URL:', url);

            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response ok:', response.ok);
                    console.log('Response headers:', response.headers);

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        currentUserSettings = data;
                        showUserSettingsModal(data);
                    } else {
                        console.error('Error in response:', data.message);
                        alert('Error loading user settings: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Fetch error details:', error);
                    console.error('Error message:', error.message);
                    console.error('Error stack:', error.stack);
                    alert('Error loading user settings: ' + error.message);
                });
        }

        function showUserSettingsModal(data) {
            document.getElementById('modal-user-name').textContent =
                `${data.user.firstname || ''} ${data.user.lastname || ''} (${data.user.username || ''})`;
            document.getElementById('modal-user-email').textContent = data.user.email || '';

            // Check if user has custom settings or is following defaults
            const hasCustomSettings = (data.settings.profit && data.settings.profit.user_id) ||
                (data.settings.bonus && data.settings.bonus.user_id);

            const statusElement = document.getElementById('modal-settings-status');
            if (hasCustomSettings) {
                statusElement.textContent = 'Custom Settings';
                statusElement.className = 'badge bg-primary ms-1';
            } else {
                statusElement.textContent = 'Following Global Defaults';
                statusElement.className = 'badge bg-secondary ms-1';
            }

            // Set current user ID for form submission
            document.getElementById('userSettingsModal').setAttribute('data-user-id', data.user.id);

            // Show modal first
            const modal = new bootstrap.Modal(document.getElementById('userSettingsModal'));
            modal.show();

            // Wait for modal to be shown, then populate forms
            document.getElementById('userSettingsModal').addEventListener('shown.bs.modal', function() {
                populateUserSettingsForms(data);
            }, {
                once: true
            });
        }

        function populateUserSettingsForms(data) {
            // Populate forms for each wallet type
            const types = ['profit', 'bonus'];
            types.forEach(type => {
                const settings = data.settings[type];
                const form = document.getElementById(`${type}-settings-form`);

                console.log(`Looking for form: ${type}-settings-form`, form);

                if (!form) {
                    console.error(`Form not found: ${type}-settings-form`);
                    return;
                }

                if (settings && settings !== null) {
                    if (form.querySelector('[name="min_amount"]')) form.querySelector('[name="min_amount"]').value =
                        settings.min_amount || '';
                    if (form.querySelector('[name="max_amount"]')) form.querySelector('[name="max_amount"]').value =
                        settings.max_amount || '';
                    if (form.querySelector('[name="tax_percent"]')) form.querySelector('[name="tax_percent"]')
                        .value = settings.tax_percent || 0;
                    if (form.querySelector('[name="is_enabled"]')) form.querySelector('[name="is_enabled"]')
                        .checked = settings.is_enabled || false;
                } else {
                    // Use global defaults if available
                    const globalDefaults = @json($globalDefaults ?? []);
                    if (globalDefaults[type]) {
                        if (form.querySelector('[name="min_amount"]')) form.querySelector('[name="min_amount"]')
                            .value = globalDefaults[type].min_amount || '';
                        if (form.querySelector('[name="max_amount"]')) form.querySelector('[name="max_amount"]')
                            .value = globalDefaults[type].max_amount || '';
                        if (form.querySelector('[name="tax_percent"]')) form.querySelector('[name="tax_percent"]')
                            .value = globalDefaults[type].tax_percent || 0;
                        if (form.querySelector('[name="is_enabled"]')) form.querySelector('[name="is_enabled"]')
                            .checked = globalDefaults[type].is_enabled || true;
                    } else {
                        // Default values
                        if (form.querySelector('[name="min_amount"]')) form.querySelector('[name="min_amount"]')
                            .value = '';
                        if (form.querySelector('[name="max_amount"]')) form.querySelector('[name="max_amount"]')
                            .value = '';
                        if (form.querySelector('[name="tax_percent"]')) form.querySelector('[name="tax_percent"]')
                            .value = 0;
                        if (form.querySelector('[name="is_enabled"]')) form.querySelector('[name="is_enabled"]')
                            .checked = true;
                    }
                }
            });
        }

        function saveUserSettings(walletType) {
            const form = document.getElementById(`${walletType}-settings-form`);

            if (!form) {
                console.error(`Form not found: ${walletType}-settings-form`);
                alert(`Error: Form not found for ${walletType} settings`);
                return;
            }

            const formData = new FormData(form);
            formData.append('wallet_type', walletType);

            const userId = document.getElementById('userSettingsModal').getAttribute('data-user-id');

            fetch(`/admin/reinvestment-settings/user/${userId}/update`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Settings updated successfully!');
                        usersTable.ajax.reload(null, false); // Reload without resetting pagination
                    } else {
                        alert('Error updating settings: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating settings');
                });
        }

        function showBulkModal() {
            if (selectedUsers.length === 0) {
                alert('Please select users first');
                return;
            }

            document.getElementById('bulk-selected-count').textContent = selectedUsers.length;
            new bootstrap.Modal(document.getElementById('bulkUpdateModal')).show();
        }

        function applyBulkUpdate() {
            const form = document.getElementById('bulk-update-form');
            const formData = new FormData(form);
            formData.append('user_ids', JSON.stringify(selectedUsers));

            fetch('/admin/reinvestment-settings/bulk-update', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        selectedUsers = [];
                        updateSelectedCount();
                        usersTable.ajax.reload(null, false);
                        bootstrap.Modal.getInstance(document.getElementById('bulkUpdateModal')).hide();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error applying bulk update');
                });
        }

        function setUserToFollowDefaults() {
            if (!confirm(
                    'Are you sure you want this user to follow global defaults?\n\n' +
                    'This will:\n' +
                    '• Remove all custom settings for this user\n' +
                    '• User will automatically use current and future global defaults\n' +
                    '• When global defaults change, this user\'s settings will change too'
                )) {
                return;
            }

            const userId = document.getElementById('userSettingsModal').getAttribute('data-user-id');

            fetch(`/admin/reinvestment-settings/user/${userId}/follow-defaults`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        usersTable.ajax.reload(null, false); // Reload table to show updated settings

                        // Close modal
                        bootstrap.Modal.getInstance(document.getElementById('userSettingsModal')).hide();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error setting user to follow defaults');
                });
        }

        function showGlobalDefaultsModal() {
            new bootstrap.Modal(document.getElementById('globalDefaultsModal')).show();
        }

        function saveGlobalDefaults(walletType) {
            const form = document.getElementById(`global-${walletType}-form`);
            const formData = new FormData(form);
            formData.append('wallet_type', walletType);

            fetch('/admin/reinvestment-settings/global-defaults/update', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Global defaults updated successfully!');
                        location.reload(); // Reload to show updated defaults
                    } else {
                        alert('Error updating global defaults: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating global defaults');
                });
        }
    </script>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>
                            Reinvestment Settings Management
                        </h5>
                        <div>
                            <button type="button" class="btn btn-secondary me-2" onclick="showGlobalDefaultsModal()">
                                <i class="fas fa-globe me-1"></i>
                                Global Defaults
                            </button>
                            <button type="button" class="btn btn-info" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-1"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Reinvestment Settings Management:</strong> Configure minimum and maximum amounts for
                            user reinvestments, set tax percentages, and enable/disable reinvestment functionality for
                            different wallet types (profit/bonus).
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Panel -->
        <div id="bulk-actions" class="row mb-3" style="display: none;">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <span>
                                <i class="fas fa-check-circle text-primary me-2"></i>
                                <span id="selected-count">0</span> users selected
                            </span>
                            <div>
                                <button type="button" class="btn btn-sm btn-primary" onclick="showBulkModal()">
                                    <i class="fas fa-edit me-1"></i>
                                    Bulk Update
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>
                            Users Reinvestment Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="usersTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                                        </th>
                                        <th>User Information</th>
                                        <th class="text-center">
                                            <i class="fas fa-coins text-warning me-1"></i>
                                            Profit Wallet
                                        </th>
                                        <th class="text-center">
                                            <i class="fas fa-gift text-info me-1"></i>
                                            Bonus Wallet
                                        </th>
                                        <th class="text-center">Actions</th>
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

    <!-- User Settings Modal -->
    <div class="modal fade" id="userSettingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-cog me-2"></i>
                        Edit Reinvestment Settings
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-light border">
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="mb-2">
                                            <strong class="text-dark">User:</strong>
                                            <span id="modal-user-name" class="text-primary fw-medium"></span>
                                        </div>
                                        <div class="mb-2">
                                            <strong class="text-dark">Email:</strong>
                                            <span id="modal-user-email" class="text-primary fw-medium"></span>
                                        </div>
                                        <div>
                                            <strong class="text-dark">Settings Status:</strong>
                                            <span id="modal-settings-status" class="badge ms-1"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="user-settings-container">
                        <!-- Profit Wallet Settings -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-coins text-warning me-1"></i>
                                        Profit Wallet Settings
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="profit-settings-form">
                                        <div class="mb-3">
                                            <label class="form-label">Minimum Amount ($)</label>
                                            <input type="number" name="min_amount" class="form-control" step="0.01"
                                                min="0" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Maximum Amount ($)</label>
                                            <input type="number" name="max_amount" class="form-control" step="0.01"
                                                min="0" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tax Percentage (%)</label>
                                            <input type="number" name="tax_percent" class="form-control" step="0.01"
                                                min="0" max="100" required>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" name="is_enabled" class="form-check-input"
                                                    id="profit-enabled">
                                                <label class="form-check-label" for="profit-enabled">
                                                    Enable Profit Wallet Reinvestment
                                                </label>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-warning"
                                            onclick="saveUserSettings('profit')">
                                            <i class="fas fa-save me-1"></i>
                                            Save Profit Settings
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Bonus Wallet Settings -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-gift text-info me-1"></i>
                                        Bonus Wallet Settings
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="bonus-settings-form">
                                        <div class="mb-3">
                                            <label class="form-label">Minimum Amount ($)</label>
                                            <input type="number" name="min_amount" class="form-control" step="0.01"
                                                min="0" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Maximum Amount ($)</label>
                                            <input type="number" name="max_amount" class="form-control" step="0.01"
                                                min="0" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tax Percentage (%)</label>
                                            <input type="number" name="tax_percent" class="form-control" step="0.01"
                                                min="0" max="100" required>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" name="is_enabled" class="form-check-input"
                                                    id="bonus-enabled">
                                                <label class="form-check-label" for="bonus-enabled">
                                                    Enable Bonus Wallet Reinvestment
                                                </label>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-info" onclick="saveUserSettings('bonus')">
                                            <i class="fas fa-save me-1"></i>
                                            Save Bonus Settings
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Close
                    </button>
                    <button type="button" class="btn btn-success" onclick="setUserToFollowDefaults()">
                        <i class="fas fa-link me-1"></i>
                        Follow Global Defaults
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Bulk Update Modal -->
    <div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-users-cog me-2"></i>
                        Bulk Update Settings
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        You are about to update settings for <strong><span id="bulk-selected-count"></span> users</strong>.
                        Only filled fields will be updated.
                    </div>

                    <form id="bulk-update-form">
                        <div class="mb-3">
                            <label class="form-label">Wallet Type</label>
                            <select name="wallet_type" class="form-select" required>
                                <option value="">Select wallet type</option>
                                <option value="profit">Profit Wallet</option>
                                <option value="bonus">Bonus Wallet</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Minimum Amount ($)</label>
                            <input type="number" name="min_amount" class="form-control" step="0.01" min="0"
                                placeholder="Leave empty to skip">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Maximum Amount ($)</label>
                            <input type="number" name="max_amount" class="form-control" step="0.01" min="0"
                                placeholder="Leave empty to skip">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tax Percentage (%)</label>
                            <input type="number" name="tax_percent" class="form-control" step="0.01" min="0"
                                max="100" placeholder="Leave empty to skip">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Enable/Disable</label>
                            <select name="is_enabled" class="form-select">
                                <option value="">Don't change</option>
                                <option value="1">Enable</option>
                                <option value="0">Disable</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="applyBulkUpdate()">
                        <i class="fas fa-save me-1"></i>
                        Apply Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Defaults Modal -->
    <div class="modal fade" id="globalDefaultsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-globe me-2"></i>
                        Global Default Settings
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> These settings will be used as defaults for users who don't have specific
                        settings configured.
                    </div>

                    <div class="row">
                        <!-- Profit Wallet Global Defaults -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-coins text-warning me-1"></i>
                                        Profit Wallet Defaults
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="global-profit-form">
                                        <div class="mb-3">
                                            <label class="form-label">Minimum Amount ($)</label>
                                            <input type="number" name="min_amount" class="form-control" step="0.01"
                                                min="0" value="{{ $globalDefaults['profit']->min_amount ?? '' }}"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Maximum Amount ($)</label>
                                            <input type="number" name="max_amount" class="form-control" step="0.01"
                                                min="0" value="{{ $globalDefaults['profit']->max_amount ?? '' }}"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tax Percentage (%)</label>
                                            <input type="number" name="tax_percent" class="form-control" step="0.01"
                                                min="0" max="100"
                                                value="{{ $globalDefaults['profit']->tax_percent ?? 0 }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" name="is_enabled" class="form-check-input"
                                                    id="global-profit-enabled"
                                                    {{ $globalDefaults['profit']->is_enabled ?? true ? 'checked' : '' }}>
                                                <label class="form-check-label" for="global-profit-enabled">
                                                    Enable by Default
                                                </label>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-warning"
                                            onclick="saveGlobalDefaults('profit')">
                                            <i class="fas fa-save me-1"></i>
                                            Save Profit Defaults
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Bonus Wallet Global Defaults -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-gift text-info me-1"></i>
                                        Bonus Wallet Defaults
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="global-bonus-form">
                                        <div class="mb-3">
                                            <label class="form-label">Minimum Amount ($)</label>
                                            <input type="number" name="min_amount" class="form-control" step="0.01"
                                                min="0" value="{{ $globalDefaults['bonus']->min_amount ?? '' }}"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Maximum Amount ($)</label>
                                            <input type="number" name="max_amount" class="form-control" step="0.01"
                                                min="0" value="{{ $globalDefaults['bonus']->max_amount ?? '' }}"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tax Percentage (%)</label>
                                            <input type="number" name="tax_percent" class="form-control" step="0.01"
                                                min="0" max="100"
                                                value="{{ $globalDefaults['bonus']->tax_percent ?? 0 }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" name="is_enabled" class="form-check-input"
                                                    id="global-bonus-enabled"
                                                    {{ $globalDefaults['bonus']->is_enabled ?? true ? 'checked' : '' }}>
                                                <label class="form-check-label" for="global-bonus-enabled">
                                                    Enable by Default
                                                </label>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-info"
                                            onclick="saveGlobalDefaults('bonus')">
                                            <i class="fas fa-save me-1"></i>
                                            Save Bonus Defaults
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
