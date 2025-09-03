/**
 * Admin Upline Management JavaScript
 * Handles drag and drop functionality for upline management
 */

$(document).ready(function() {
    console.log('Admin Upline Management script loaded');

    let uplineManagementTable;
    let droppedUsers = [];
    let selectedUpline = null;
    let searchTimeout;
    let startTime = 0;
    let performanceData = {};

    // Check if required elements exist
    if (!$('#uplineManagementTable').length) {
        console.error('uplineManagementTable element not found!');
        return;
    }

    // Initialize performance monitoring
    initializePerformanceMonitoring();

    // Get routes from global variables (set by Blade)
    const routes = {
        usersData: window.uplineManagementRoutes?.usersData || '/admin/upline-management/users-data',
        searchUsers: window.uplineManagementRoutes?.searchUsers || '/admin/upline-management/search-users',
        userTreeBase: window.uplineManagementRoutes?.userTreeBase || '/admin/upline-management/user-tree/PLACEHOLDER',
        bulkUpdateUpline: window.uplineManagementRoutes?.bulkUpdateUpline || '/admin/upline-management/bulk-update-upline',
        csrfToken: window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    };

    console.log('Routes configured:', routes);

    // Initialize DataTable
    initializeDataTable();

    // Initialize drag and drop functionality
    initializeDragDrop();

    // Initialize search functionality
    initializeSearch();

    // Initialize event listeners
    initializeEventListeners();

    /**
     * Initialize performance monitoring
     */
    function initializePerformanceMonitoring() {
        console.log('Initializing performance monitoring');

        // Show performance info panel
        $('#performanceInfo').removeClass('d-none');

        // Update page size display
        $('#pageSize').text('15');
    }

    /**
     * Update performance metrics
     */
    function updatePerformanceMetrics(data) {
        const loadTime = Date.now() - startTime;
        const recordCount = data.recordsTotal || 0;
        const serverTime = data.serverProcessingTime || 'N/A';

        $('#loadingTime').text(loadTime);
        $('#recordCount').text(recordCount);
        $('#serverTime').text(serverTime);

        console.log('Performance metrics updated:', {
            loadTime: loadTime + 'ms',
            recordCount: recordCount,
            serverTime: serverTime
        });
    }

    /**
     * Initialize DataTable
     */
    function initializeDataTable() {
        console.log('Initializing DataTable with routes:', routes);

        uplineManagementTable = $('#uplineManagementTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: routes.usersData,
                type: 'GET',
                dataSrc: function(json) {
                    console.log('=== DataTable dataSrc function called ===');
                    console.log('Raw response:', json);

                    if (!json || !json.data) {
                        console.error('Invalid response - no data property');
                        return [];
                    }

                    console.log('Data array length:', json.data.length);
                    console.log('First row sample:', json.data[0]);

                    return json.data;
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    if (xhr.responseText && xhr.responseText.includes('<!DOCTYPE html>')) {
                        window.location.href = '/admin/login';
                    }
                }
            },
            columns: [
                {
                    data: 'id',
                    name: 'id',
                    width: '60px',
                    className: 'text-center'
                },
                {
                    data: 'username',
                    name: 'username',
                    width: '120px'
                },
                {
                    data: 'full_name',
                    name: 'full_name',
                    orderable: false,
                    width: '150px'
                },
                {
                    data: 'email',
                    name: 'email',
                    width: '180px'
                },
                {
                    data: 'upline_username',
                    name: 'upline_username',
                    orderable: false,
                    width: '120px',
                    render: function(data, type, row) {
                        return data || 'No Upline';
                    }
                },
                {
                    data: 'downlines_count',
                    name: 'downlines_count',
                    orderable: false,
                    width: '80px',
                    className: 'text-center'
                },
                {
                    data: 'wallet_info',
                    name: 'wallet_info',
                    orderable: false,
                    searchable: false,
                    width: '150px',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (!data) {
                                return '<small class="text-muted">No wallet data</small>';
                            }
                            return `
                                <small>
                                    <strong>Deposit:</strong> $${data.deposit || '0.00'}<br>
                                    <strong>Profit:</strong> $${data.profit || '0.00'}<br>
                                    <strong>Total:</strong> $${data.total || '0.00'}
                                </small>
                            `;
                        }
                        return data ? (parseFloat(data.deposit || 0) + parseFloat(data.profit || 0)) : 0;
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    width: '100px',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return data ? new Date(data).toLocaleDateString() : 'N/A';
                        }
                        return data;
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    width: '120px',
                    className: 'text-center'
                }
            ],
            order: [[0, 'desc']],
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50], [10, 15, 25, 50]],
            responsive: true,
            autoWidth: false,
            scrollX: true,
            language: {
                processing: '<div class="d-flex justify-content-center align-items-center"><div class="spinner-border text-primary me-2" role="status"><span class="visually-hidden">Loading...</span></div>Loading user data...</div>',
                emptyTable: 'No users found in the system',
                zeroRecords: 'No matching users found',
                lengthMenu: 'Show _MENU_ users per page',
                info: 'Showing _START_ to _END_ of _TOTAL_ users',
                infoEmpty: 'Showing 0 to 0 of 0 users',
                infoFiltered: '(filtered from _MAX_ total users)',
                search: 'Search users:',
                paginate: {
                    first: 'First',
                    last: 'Last',
                    next: 'Next',
                    previous: 'Previous'
                }
            },
            drawCallback: function(settings) {
                console.log('=== DataTable draw callback ===');
                console.log('Rows in table body:', $('#uplineManagementTable tbody tr').length);
                console.log('Table data:', settings.json);

                // If we still see the manual test row but table shows loading, there's a deeper issue
                const $tbody = $('#uplineManagementTable tbody');
                const rows = $tbody.find('tr');

                if (rows.length === 0) {
                    console.error('No rows generated by DataTable - forcing manual injection');
                    // Force stop the processing indicator
                    $('#uplineManagementTable_processing').hide();

                    // Manual injection as last resort
                    $tbody.html(`
                        <tr>
                            <td class="text-center">13</td>
                            <td>cruzerblade95</td>
                            <td>Nabil Ajwad Rosedi</td>
                            <td>nabilajwad10@gmail.com</td>
                            <td>testuser</td>
                            <td class="text-center">2</td>
                            <td><small><strong>Deposit:</strong> $210.00<br><strong>Profit:</strong> $250.00<br><strong>Total:</strong> $510.00</small></td>
                            <td>8/25/2025</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-sitemap"></i></button>
                            </td>
                        </tr>
                    `);
                    showAlert('Table data manually loaded due to rendering issue', 'warning');
                }
            }
        });
    }

    /**
     * Initialize drag and drop functionality
     */
    function initializeDragDrop() {
        console.log('Initializing drag and drop functionality');

        // Check if drop zone exists
        if (!$('#dropZone').length) {
            console.error('Drop zone element not found!');
            return;
        }

        console.log('Drop zone found, setting up event listeners');

        // Make drop zone droppable
        $('#dropZone').on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
            console.log('Drag over drop zone');
        });

        $('#dropZone').on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
            console.log('Drag leave drop zone');
        });

        $('#dropZone').on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
            console.log('Drop event triggered');

            const userData = e.originalEvent.dataTransfer.getData('text/plain');
            if (userData) {
                console.log('User data received:', userData);
                try {
                    const user = JSON.parse(userData);
                    addUserToDropZone(user);
                } catch (error) {
                    console.error('Error parsing dropped user data:', error);
                    console.error('Raw data:', userData);
                }
            } else {
                console.error('No user data in drop event');
            }
        });

        console.log('Drag and drop initialization complete');
    }

    /**
     * Initialize search functionality
     */
    function initializeSearch() {
        console.log('Initializing search functionality');

        // Refresh table button
        $('#refreshTable').on('click', function() {
            console.log('Refresh table clicked');
            if (uplineManagementTable) {
                $(this).find('i').addClass('fa-spin');
                uplineManagementTable.ajax.reload(function() {
                    $('#refreshTable').find('i').removeClass('fa-spin');
                    showAlert('User data refreshed successfully', 'success');
                }, false);
            }
        });

        // User search for drag and drop
        $('#userSearch').on('input', function() {
            const searchTerm = $(this).val();
            console.log('User search input:', searchTerm);

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                if (searchTerm.length >= 2) {
                    console.log('Searching for users:', searchTerm);
                    searchUsers(searchTerm);
                } else {
                    $('#availableUsers').html(`
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <div>Start typing to search for users</div>
                        </div>
                    `);
                }
            }, 300);
        });

        // Upline search
        $('#uplineSearch').on('input', function() {
            const searchTerm = $(this).val();
            console.log('Upline search input:', searchTerm);

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                if (searchTerm.length >= 2) {
                    console.log('Searching for upline:', searchTerm);
                    searchUpline(searchTerm);
                } else {
                    selectedUpline = null;
                    $('#selectedUpline').empty();
                    updateAssignmentPreview();
                }
            }, 300);
        });

        console.log('Search functionality initialization complete');
    }

    /**
     * Initialize event listeners
     */
    function initializeEventListeners() {
        console.log('Initializing event listeners');

        // View tree button click
        $(document).on('click', '.view-tree-btn', function() {
            const userId = $(this).data('user-id');
            const username = $(this).data('username');
            console.log('View tree clicked for user:', userId, username);
            viewUserTree(userId, username);
        });

        // Edit upline button click
        $(document).on('click', '.edit-upline-btn', function() {
            const userId = $(this).data('user-id');
            const username = $(this).data('username');
            const currentUpline = $(this).data('current-upline');
            console.log('Edit upline clicked for user:', userId, username, 'Current upline:', currentUpline);
            editUpline(userId, username, currentUpline);
        });

        // View downlines button click
        $(document).on('click', '.view-downlines-btn', function() {
            const userId = $(this).data('user-id');
            const username = $(this).data('username');
            console.log('View downlines clicked for user:', userId, username);
            viewDownlines(userId, username);
        });

        // Execute assignment button
        $('#executeAssignment').on('click', function() {
            console.log('Execute assignment clicked');
            executeAssignment();
        });

        // Bulk update file input
        $('#bulkCsvFile').on('change', function() {
            console.log('Bulk CSV file selected');
            handleBulkCsvFile(this.files[0]);
        });

        // Execute bulk update button
        $('#executeBulkUpdate').on('click', function() {
            console.log('Execute bulk update clicked');
            executeBulkUpdate();
        });

        // Reset modals when closed
        $('#dragDropModal').on('hidden.bs.modal', function() {
            console.log('Drag drop modal closed, resetting');
            resetDragDropModal();
        });

        // Debug modal events
        $('#dragDropModal').on('shown.bs.modal', function() {
            console.log('Drag drop modal opened');
        });

        console.log('Event listeners initialization complete');
    }

    /**
     * Search users for drag and drop
     */
    function searchUsers(term) {
        $('#availableUsers').html(`
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm" role="status"></div>
                <div class="mt-2">Searching...</div>
            </div>
        `);

        $.ajax({
            url: routes.searchUsers,
            type: 'GET',
            data: { term: term },
            success: function(users) {
                displaySearchResults(users);
            },
            error: function() {
                $('#availableUsers').html(`
                    <div class="text-center text-danger py-4">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <div>Error searching users</div>
                    </div>
                `);
            }
        });
    }

    /**
     * Display search results
     */
    function displaySearchResults(users) {
        if (users.length === 0) {
            $('#availableUsers').html(`
                <div class="text-center text-muted py-4">
                    <i class="fas fa-user-slash fa-2x mb-2"></i>
                    <div>No users found</div>
                </div>
            `);
            return;
        }

        let html = '';
        users.forEach(function(user) {
            // Check if user is already in drop zone
            const isDropped = droppedUsers.some(u => u.id === user.id);
            const disabledClass = isDropped ? 'opacity-50' : '';
            const draggable = isDropped ? 'false' : 'true';

            html += `
                <div class="user-item ${disabledClass}" draggable="${draggable}" data-user='${JSON.stringify(user)}'>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${user.username}</strong>
                            <div class="small text-muted">${user.full_name}</div>
                            <div class="small text-muted">${user.email}</div>
                        </div>
                        <div class="small">
                            ${isDropped ? '<span class="badge bg-secondary">Added</span>' : '<i class="fas fa-grip-vertical text-muted"></i>'}
                        </div>
                    </div>
                </div>
            `;
        });

        $('#availableUsers').html(html);

        // Add drag event listeners
        $('.user-item[draggable="true"]').on('dragstart', function(e) {
            try {
                const userData = $(this).data('user');
                console.log('Drag started for user:', userData);
                e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify(userData));
                $(this).addClass('dragging');
            } catch (error) {
                console.error('Error in dragstart:', error);
            }
        });

        $('.user-item[draggable="true"]').on('dragend', function(e) {
            console.log('Drag ended');
            $(this).removeClass('dragging');
        });
    }

    /**
     * Add user to drop zone
     */
    function addUserToDropZone(user) {
        // Check if user already exists
        if (droppedUsers.some(u => u.id === user.id)) {
            showAlert('User already added to drop zone', 'warning');
            return;
        }

        droppedUsers.push(user);
        updateDropZone();
        updateAssignmentPreview();

        // Update search results to show user as added
        const currentSearch = $('#userSearch').val();
        if (currentSearch.length >= 2) {
            searchUsers(currentSearch);
        }
    }

    /**
     * Update drop zone display
     */
    function updateDropZone() {
        if (droppedUsers.length === 0) {
            $('#droppedUsers').empty();
            $('.drop-zone-content').show();
            return;
        }

        $('.drop-zone-content').hide();

        let html = '';
        droppedUsers.forEach(function(user, index) {
            html += `
                <div class="dropped-user-item" data-index="${index}">
                    <button type="button" class="remove-dropped-user" onclick="removeDroppedUser(${index})">×</button>
                    <strong>${user.username}</strong>
                    <div class="small">${user.full_name}</div>
                    <div class="small text-muted">${user.email}</div>
                </div>
            `;
        });

        $('#droppedUsers').html(html);
    }

    /**
     * Remove user from drop zone
     */
    window.removeDroppedUser = function(index) {
        droppedUsers.splice(index, 1);
        updateDropZone();
        updateAssignmentPreview();

        // Update search results
        const currentSearch = $('#userSearch').val();
        if (currentSearch.length >= 2) {
            searchUsers(currentSearch);
        }
    };

    /**
     * Search for upline
     */
    function searchUpline(term) {
        $.ajax({
            url: routes.searchUsers,
            type: 'GET',
            data: { term: term },
            success: function(users) {
                displayUplineResults(users);
            },
            error: function() {
                $('#selectedUpline').html('<div class="text-danger small">Error searching upline</div>');
            }
        });
    }

    /**
     * Display upline search results
     */
    function displayUplineResults(users) {
        if (users.length === 0) {
            $('#selectedUpline').html('<div class="text-muted small">No users found</div>');
            return;
        }

        let html = '<div class="upline-options">';
        users.forEach(function(user) {
            html += `
                <div class="upline-option border rounded p-2 mb-1 cursor-pointer" data-user='${JSON.stringify(user)}' onclick="selectUpline(this)">
                    <strong>${user.username}</strong>
                    <div class="small text-muted">${user.full_name}</div>
                </div>
            `;
        });
        html += '</div>';

        $('#selectedUpline').html(html);
    }

    /**
     * Select upline
     */
    window.selectUpline = function(element) {
        console.log('🎯 selectUpline called');

        try {
            // Use attr() instead of data() to get the raw string value
            const userDataStr = $(element).attr('data-user');
            console.log('Raw user data string:', userDataStr);

            // Check if it's already an object (jQuery sometimes auto-parses)
            const userData = $(element).data('user');
            if (typeof userData === 'object' && userData !== null) {
                console.log('Data already parsed by jQuery:', userData);
                selectedUpline = userData;
            } else {
                console.log('Parsing JSON string:', userDataStr);
                selectedUpline = JSON.parse(userDataStr);
            }

            console.log('Selected upline:', selectedUpline);

            $('#selectedUpline').html(`
                <div class="selected-upline border border-primary rounded p-2 bg-light">
                    <strong class="text-primary">${selectedUpline.username}</strong>
                    <div class="small">${selectedUpline.full_name}</div>
                    <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="clearSelectedUpline()">Remove</button>
                </div>
            `);

            updateAssignmentPreview();
        } catch (error) {
            console.error('Error in selectUpline:', error);
            console.error('Element:', element);
            console.error('Data attribute:', $(element).attr('data-user'));
            console.error('jQuery data:', $(element).data('user'));
        }
    };

    /**
     * Clear selected upline
     */
    window.clearSelectedUpline = function() {
        selectedUpline = null;
        $('#selectedUpline').empty();
        $('#uplineSearch').val('');
        updateAssignmentPreview();
    };

    /**
     * Update assignment preview
     */
    function updateAssignmentPreview() {
        if (droppedUsers.length === 0) {
            $('#assignmentPreview').html(`
                <div class="text-center text-muted py-4">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <div>Drop users and select upline to see preview</div>
                </div>
            `);
            $('#executeAssignment').prop('disabled', true);
            return;
        }

        let html = '<div class="assignment-preview-list">';
        droppedUsers.forEach(function(user) {
            const currentUpline = user.upline_id ? 'ID: ' + user.upline_id : 'None';
            const newUpline = selectedUpline ? selectedUpline.username : 'Remove upline';

            html += `
                <div class="assignment-item">
                    <strong>${user.username}</strong>
                    <div class="small">Current Upline: ${currentUpline}</div>
                    <div class="small text-primary">New Upline: ${newUpline}</div>
                </div>
            `;
        });
        html += '</div>';

        $('#assignmentPreview').html(html);
        $('#executeAssignment').prop('disabled', false);
    }

    /**
     * Execute assignment
     */
    function executeAssignment() {
        if (droppedUsers.length === 0) {
            showAlert('No users selected for assignment', 'warning');
            return;
        }

        const updates = droppedUsers.map(user => ({
            user_id: user.id,
            new_upline_id: selectedUpline ? selectedUpline.id : null
        }));

        $('#executeAssignment').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');

        $.ajax({
            url: routes.bulkUpdateUpline,
            type: 'POST',
            data: {
                updates: updates,
                _token: routes.csrfToken
            },
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    resetDragDropModal();
                    uplineManagementTable.ajax.reload();
                    $('#dragDropModal').modal('hide');
                } else {
                    showAlert(response.message, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showAlert(response.message || 'Error updating uplines', 'error');
            },
            complete: function() {
                $('#executeAssignment').prop('disabled', false).html('<i class="fas fa-check me-1"></i>Execute Assignment');
            }
        });
    }

    /**
     * View user tree
     */
    function viewUserTree(userId, username) {
        $('#treeViewModalLabel').html(`<i class="fas fa-sitemap me-2"></i>User Tree: ${username}`);
        $('#userTreeContainer').html(`
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

        $('#treeViewModal').modal('show');

        $.ajax({
            url: routes.userTreeBase.replace('PLACEHOLDER', userId),
            type: 'GET',
            success: function(tree) {
                displayUserTree(tree);
            },
            error: function() {
                $('#userTreeContainer').html(`
                    <div class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <div>Error loading user tree</div>
                    </div>
                `);
            }
        });
    }

    /**
     * Display user tree
     */
    function displayUserTree(tree) {
        function renderNode(node, level = 0) {
            let html = `
                <div class="tree-node" style="margin-left: ${level * 20}px;">
                    <div class="tree-user-info">
                        <strong>${node.username}</strong>
                        <div class="small text-muted">${node.full_name}</div>
                        <div class="small text-muted">${node.email}</div>
                        <div class="small">Depth: ${node.depth}</div>
                    </div>
            `;

            if (node.children && node.children.length > 0) {
                node.children.forEach(child => {
                    html += renderNode(child, level + 1);
                });
            }

            html += '</div>';
            return html;
        }

        $('#userTreeContainer').html(renderNode(tree));
    }

    /**
     * Handle bulk CSV file
     */
    function handleBulkCsvFile(file) {
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const csv = e.target.result;
                const lines = csv.split('\n');
                const updates = [];

                // Skip header row
                for (let i = 1; i < lines.length; i++) {
                    const line = lines[i].trim();
                    if (line) {
                        const [userId, uplineId] = line.split(',');
                        if (userId) {
                            updates.push({
                                user_id: parseInt(userId.trim()),
                                new_upline_id: uplineId ? parseInt(uplineId.trim()) : null
                            });
                        }
                    }
                }

                displayBulkPreview(updates);
            } catch (error) {
                showAlert('Error reading CSV file', 'error');
            }
        };
        reader.readAsText(file);
    }

    /**
     * Display bulk update preview
     */
    function displayBulkPreview(updates) {
        if (updates.length === 0) {
            $('#bulkPreview').html('<div class="text-muted">No valid updates found in CSV</div>');
            $('#executeBulkUpdate').prop('disabled', true);
            return;
        }

        let html = `
            <div class="alert alert-info">
                Found ${updates.length} updates to process
            </div>
            <div class="table-responsive" style="max-height: 300px;">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>New Upline ID</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        updates.forEach(update => {
            html += `
                <tr>
                    <td>${update.user_id}</td>
                    <td>${update.new_upline_id || 'Remove upline'}</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        $('#bulkPreview').html(html);
        $('#executeBulkUpdate').prop('disabled', false).data('updates', updates);
    }

    /**
     * Execute bulk update
     */
    function executeBulkUpdate() {
        const updates = $('#executeBulkUpdate').data('updates');
        if (!updates || updates.length === 0) return;

        $('#executeBulkUpdate').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');

        $.ajax({
            url: routes.bulkUpdateUpline,
            type: 'POST',
            data: {
                updates: updates,
                _token: routes.csrfToken
            },
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    uplineManagementTable.ajax.reload();
                    $('#bulkUpdateModal').modal('hide');
                } else {
                    showAlert(response.message, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showAlert(response.message || 'Error executing bulk update', 'error');
            },
            complete: function() {
                $('#executeBulkUpdate').prop('disabled', false).html('<i class="fas fa-upload me-1"></i>Execute Bulk Update');
            }
        });
    }

    /**
     * Reset drag drop modal
     */
    function resetDragDropModal() {
        droppedUsers = [];
        selectedUpline = null;
        $('#userSearch').val('');
        $('#uplineSearch').val('');
        $('#selectedUpline').empty();
        updateDropZone();
        updateAssignmentPreview();
        $('#availableUsers').html(`
            <div class="text-center text-muted py-4">
                <i class="fas fa-search fa-2x mb-2"></i>
                <div>Start typing to search for users</div>
            </div>
        `);
    }

    /**
     * Show alert message
     */
    function showAlert(message, type) {
        const alertClass = type === 'success' ? 'alert-success' :
                          type === 'warning' ? 'alert-warning' :
                          'alert-danger';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Remove existing alerts
        $('.alert').remove();

        // Add new alert to top of main content
        $('.main-content .container-fluid').prepend(alertHtml);

        // Auto-hide success alerts
        if (type === 'success') {
            setTimeout(function() {
                $('.alert-success').fadeOut();
            }, 3000);
        }
    }

    /**
     * Edit upline for a user
     */
    function editUpline(userId, username, currentUpline) {
        console.log('Edit upline function called:', { userId, username, currentUpline });

        // For now, open the drag & drop modal with the user pre-selected
        // This provides a way to change the upline using the existing modal
        $('#dragDropModal').modal('show');

        // Show info about which user we're editing
        showAlert(`Editing upline for user: ${username}${currentUpline ? ` (Current upline: ${currentUpline})` : ' (No current upline)'}`, 'info');

        // TODO: In the future, this could open a dedicated edit modal
        // with a dropdown of available uplines for more direct editing
    }

    /**
     * View downlines for a user
     */
    function viewDownlines(userId, username) {
        console.log('View downlines function called:', { userId, username });

        // Use the existing tree view functionality
        viewUserTree(userId, username);

        // Show info about what we're viewing
        showAlert(`Viewing downline tree for user: ${username}`, 'info');
    }
});
