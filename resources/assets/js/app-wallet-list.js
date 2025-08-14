'use strict';

$(function () {
    // Set CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let walletTable = $('.datatables-wallets').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/admin/wallets/list',
            method: 'GET',
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error, xhr.responseText);
                if (xhr.status === 401) {
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while loading the data. Please try refreshing the page.'
                    });
                }
            }
        },
        columns: [
            // Index column for Yajra
            {
                data: 'DT_RowIndex',
                defaultContent: '',
                className: 'control',
                orderable: false,
                searchable: false
            },
            // User column
            {
                data: 'user',
                render: function (data, type, row) {
                    return `
                        <div class="d-flex justify-content-start align-items-center user-name">
                            <div class="avatar-wrapper">
                                <div class="avatar me-2">
                                    <img src="${row.profile_photo_url || '/assets/img/avatars/1.png'}" alt="Avatar" class="rounded-circle">
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-medium">${row.name || row.username}</span>
                                <small class="text-muted">@${row.username}</small>
                            </div>
                        </div>
                    `;
                }
            },
            // Deposit column
            {
                data: 'deposit',
                render: function (data) {
                    return `$${parseFloat(data || 0).toFixed(2)}`;
                }
            },
            // Profit column
            {
                data: 'profit',
                render: function (data) {
                    return `$${parseFloat(data || 0).toFixed(2)}`;
                }
            },
            // Bonus column
            {
                data: 'bonus',
                render: function (data) {
                    return `$${parseFloat(data || 0).toFixed(2)}`;
                }
            },
            // Cash column
            {
                data: 'cash',
                render: function (data) {
                    return `$${parseFloat(data || 0).toFixed(2)}`;
                }
            },
            // Total Balance column
            {
                data: 'total_balance',
                render: function (data) {
                    return `$${parseFloat(data || 0).toFixed(2)}`;
                }
            },
            // Last Updated column
            { data: 'updated_at' },
            // Actions column
            {
                data: 'actions',
                orderable: false,
                searchable: false
            }
        ],
        order: [[1, 'asc']], // Sort by user name (now at index 1)
        dom: '<"row mx-2"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"f>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: {
            search: '',
            searchPlaceholder: 'Search Users...',
            paginate: {
                previous: '<i class="fas fa-chevron-left"></i>',
                next: '<i class="fas fa-chevron-right"></i>'
            }
        },
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({
                    header: function (row) {
                        const data = row.data();
                        return 'Wallet Details of ' + (data.name || data.username);
                    }
                }),
                type: 'column',
                renderer: function (api, rowIdx, columns) {
                    const data = $.map(columns, function (col, i) {
                        return col.title !== '' && col.title !== 'Actions'
                            ? '<tr data-dt-row="' +
                                col.rowIndex +
                                '" data-dt-column="' +
                                col.columnIndex +
                                '">' +
                                '<td>' +
                                col.title +
                                ':' +
                                '</td> ' +
                                '<td>' +
                                col.data +
                                '</td>' +
                                '</tr>'
                            : '';
                    }).join('');

                    return data ? $('<table class="table"/><tbody />').append(data) : false;
                }
            }
        }
    });

    // Search input event
    $('#searchInput').on('keyup', function() {
        walletTable.search(this.value).draw();
    });

    // Update wallet event
    $(document).on('click', '.update-wallet', function() {
        const button = $(this);
        const userId = button.data('id');
        const name = button.data('name');
        const username = button.data('username');
        const photo = button.data('photo');

        $('#userId').val(userId);
        $('#userName').text(name);
        $('#userUsername').text('@' + username);
        $('#userAvatar').attr('src', photo);

        // Reset form and error states
        $('#updateWalletForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
    });

    // Handle form submission
    $('#updateWalletForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const userId = form.find('#userId').val();
        
        // Disable submit button
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: `/admin/wallets/${userId}`,
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    
                    // Close the offcanvas
                    $('#offcanvasUpdateWallet').offcanvas('hide');
                    
                    // Reset form
                    form[0].reset();
                    
                    // Reload table
                    walletTable.ajax.reload(null, false);
                } else {
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                // Handle validation errors
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(field => {
                        const input = form.find(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.next('.invalid-feedback').text(errors[field][0]);
                    });
                } else {
                    // Show general error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'An error occurred while updating the wallet.'
                    });
                }
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    // Manage wallet event
    $(document).on('click', '.manage-wallet', function() {
        const button = $(this);
        const userId = button.data('id');
        const name = button.data('name');
        const username = button.data('username');
        const photo = button.data('photo');

        $('#manageUserId').val(userId);
        $('#manageUserName').text(name);
        $('#manageUserUsername').text('@' + username);
        $('#manageUserAvatar').attr('src', photo);

        // Reset form and error states
        $('#manageWalletForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
    });

    // Handle manage wallet form submission
    $('#manageWalletForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const userId = form.find('#manageUserId').val();
        const type = form.find('[name="type"]').val();
        const operation = form.find('[name="operation"]').val();
        const amount = form.find('[name="amount"]').val();
        const csrf = $('meta[name="csrf-token"]').attr('content');

        // Disable submit button
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
        submitBtn.prop('disabled', true);

        let data = form.serializeArray();
        // If modify, send a special flag
        if (operation === 'modify') {
            data.push({ name: 'modify', value: true });
        }

        $.ajax({
            url: `/admin/wallets/${userId}`,
            type: 'POST',
            data: $.param(data),
            headers: { 'X-CSRF-TOKEN': csrf },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#offcanvasManageWallet').offcanvas('hide');
                    form[0].reset();
                    walletTable.ajax.reload(null, false);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(field => {
                        const input = form.find(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.next('.invalid-feedback').text(errors[field][0]);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'An error occurred while updating the wallet.'
                    });
                }
            },
            complete: function() {
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
            }
        });
    });
});
