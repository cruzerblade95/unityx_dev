/**
 * DataTables Wallet Management (jquery)
 */

'use strict';

$(function () {
  // Variable declaration for table
  var dt_wallets_table = $('.datatables-wallets');

  // Wallets datatable
  if (dt_wallets_table.length) {
    // Construct the correct AJAX URL explicitly
    var ajaxUrl = window.location.origin + '/admin/wallets/list';
    console.log('DataTable AJAX URL:', ajaxUrl);
    console.log('Window baseUrl:', window.baseUrl);
    console.log('Constructed URL from origin:', ajaxUrl);

    var dt_wallets = dt_wallets_table.DataTable({
      ajax: {
        url: ajaxUrl,
        type: 'GET',
        error: function(xhr, error, thrown) {
          console.log('AJAX Error:', error);
          console.log('Response:', xhr.responseText);
          console.log('Attempted URL:', ajaxUrl);
        }
      },
      columns: [
        // columns according to JSON
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'user', name: 'user' },
        { data: 'deposit', name: 'deposit' },
        { data: 'profit', name: 'profit' },
        { data: 'bonus', name: 'bonus' },
        { data: 'cash', name: 'cash' },
        { data: 'total_balance', name: 'total_balance' },
        { data: 'updated_at', name: 'updated_at' },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          targets: 1,
          responsivePriority: 4,
          render: function (data, type, full, meta) {
            var $user = full['user'];
            return '<span class="fw-medium">' + $user + '</span>';
          }
        }
      ],
      order: [[1, 'desc']],
      dom: '<"row"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-label-secondary dropdown-toggle me-2',
          text: '<i class="tabler-download me-1"></i> <span class="d-none d-sm-inline-block">Export</span>',
          buttons: [
            {
              extend: 'print',
              text: '<i class="tabler-printer me-2"></i>Print',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7],
                format: {
                  body: function ( data, row, column, node ) {
                    return data;
                  }
                }
              },
              customize: function (win) {
                $(win.document.body)
                  .css('font-size', '10pt')
                  .prepend('<h3>User Wallet Management Report</h3>');
                $(win.document.body).find('table')
                  .addClass('compact')
                  .css('font-size', 'inherit');
              }
            },
            {
              extend: 'csv',
              text: '<i class="tabler-file-text me-2"></i>CSV',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7]
              }
            },
            {
              extend: 'excel',
              text: '<i class="tabler-file-spreadsheet me-2"></i>Excel',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7]
              }
            },
            {
              extend: 'pdf',
              text: '<i class="tabler-file-description me-2"></i>PDF',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7]
              },
              customize: function (doc) {
                doc.content[1].table.widths = ['20%', '12%', '12%', '12%', '12%', '15%', '17%'];
                doc.content.splice(0, 1, {
                  text: 'User Wallet Management Report',
                  style: 'header',
                  alignment: 'center',
                  margin: [0, 0, 0, 12]
                });
              }
            }
          ]
        }
      ],
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search..'
      },
      // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['user'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
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
  }

  // Handle adjust wallet button click
  $(document).on('click', '.btn-adjust-wallet', function() {
    var walletId = $(this).data('wallet-id');
    var userName = $(this).data('user-name');

    // Set wallet ID and user name
    $('#wallet_id').val(walletId);
    $('#modal_user_name').text(userName);

    // Reset form
    $('#adjustWalletForm')[0].reset();
    $('#wallet_id').val(walletId); // Keep wallet ID after reset

    // Hide deposit bonus checkbox initially
    $('#trigger_deposit_bonus').closest('.row').hide();

    // Load current wallet data
    loadWalletData(walletId);

    // Show modal
    $('#adjustWalletModal').modal('show');
  });

  // Handle balance type and action change to show/hide deposit bonus checkbox
  $('#balance_type, #adjustment_action').on('change', function() {
    var balanceType = $('#balance_type').val();
    var action = $('#adjustment_action').val();

    // Show checkbox only when adding to deposit balance
    if (balanceType === 'deposit' && action === 'add') {
      $('#trigger_deposit_bonus').closest('.row').show();
    } else {
      $('#trigger_deposit_bonus').closest('.row').hide();
      $('#trigger_deposit_bonus').prop('checked', false);
    }
  });

  // Function to load wallet data
  function loadWalletData(walletId) {
    $.ajax({
      url: window.location.origin + '/admin/wallets/' + walletId,
      type: 'GET',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        if (response.success) {
          var wallet = response.wallet;
          $('#current_deposit').text(wallet.deposit);
          $('#current_profit').text(wallet.profit);
          $('#current_bonus').text(wallet.bonus);
          $('#current_cash').text(wallet.cash);
          $('#modal_user_name').text(wallet.user_name);
        }
      },
      error: function(xhr) {
        console.error('Error loading wallet data:', xhr);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to load wallet data'
        });
      }
    });
  }

  // Handle form submission
  $('#adjustWalletForm').on('submit', function(e) {
    e.preventDefault();

    var form = $(this);
    var formData = {
      wallet_id: $('#wallet_id').val(),
      type: $('#balance_type').val(),
      action: $('#adjustment_action').val(),
      amount: $('#adjustment_amount').val(),
      reason: $('#adjustment_reason').val(),
      trigger_deposit_bonus: $('#trigger_deposit_bonus').is(':checked') ? 1 : 0
    };

    // Validate form
    if (!formData.type || !formData.action || !formData.amount || !formData.reason) {
      Swal.fire({
        icon: 'warning',
        title: 'Validation Error',
        text: 'Please fill in all required fields'
      });
      return;
    }

    if (parseFloat(formData.amount) <= 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Amount',
        text: 'Amount must be greater than 0'
      });
      return;
    }

    // Show confirmation
    var actionText = formData.action === 'add' ? 'add $' + formData.amount + ' to' : 'deduct $' + formData.amount + ' from';
    var bonusText = '';

    if (formData.type === 'deposit' && formData.action === 'add' && formData.trigger_deposit_bonus) {
      bonusText = '<br><br><span class="text-info"><i class="ti ti-info-circle me-1"></i><strong>Note:</strong> Deposit bonus script will be triggered for referral downline.</span>';
    }

    // Close modal first to ensure SweetAlert appears in front
    $('#adjustWalletModal').modal('hide');

    // Show confirmation after modal is hidden
    setTimeout(function() {
      Swal.fire({
        title: 'Confirm Wallet Adjustment',
        html: 'Are you sure you want to <strong>' + actionText + '</strong> the <strong>' + formData.type + '</strong> balance?<br><br><strong>Reason:</strong> ' + formData.reason + bonusText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#696cff',
        cancelButtonColor: '#8592a3',
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          submitWalletAdjustment(formData);
        } else {
          // If user cancels, show the modal again
          $('#adjustWalletModal').modal('show');
        }
      });
    }, 300); // Small delay to ensure modal is fully hidden
  });

  // Function to submit wallet adjustment
  function submitWalletAdjustment(formData) {
    $.ajax({
      url: window.location.origin + '/admin/wallets/adjust',
      type: 'POST',
      data: formData,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend: function() {
        $('button[type="submit"]', '#adjustWalletForm').prop('disabled', true).html('<i class="ti ti-loader me-1"></i>Processing...');
      },
      success: function(response) {
        if (response.success) {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: response.message,
            timer: 2000,
            showConfirmButton: false
          });

          // Refresh table
          dt_wallets.ajax.reload(null, false);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: response.message || 'Failed to adjust wallet balance'
          });
        }
      },
      error: function(xhr) {
        var errorMessage = 'An error occurred while adjusting wallet balance';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }

        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: errorMessage
        });
      },
      complete: function() {
        $('button[type="submit"]', '#adjustWalletForm').prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>Adjust Balance');
      }
    });
  }

  // Handle wallet history button click
  $(document).on('click', '.btn-wallet-history', function() {
    var walletId = $(this).data('wallet-id');
    loadTransactionHistory(walletId);
  });

  // Handle view user button click
  $(document).on('click', '.btn-view-user', function() {
    var walletId = $(this).data('wallet-id');
    loadUserDetails(walletId);
  });

  // Function to load user details
  function loadUserDetails(walletId) {
    // First get the wallet to find the user_id
    $.ajax({
      url: window.location.origin + '/admin/wallets/' + walletId,
      type: 'GET',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(walletResponse) {
        if (walletResponse.success) {
          var userId = walletResponse.user.id;

          // Now get the full user details
          $.ajax({
            url: window.location.origin + '/admin/users/' + userId,
            type: 'GET',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
              $('#userDetailsModal').modal('show');
              $('#userDetailsModal .modal-body').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading user details...</p></div>');
            },
            success: function(user) {
              // Format the full name
              const fullName = `${user.firstname || ''} ${user.lastname || ''}`.trim() || 'N/A';

              // Format email verification status
              const emailVerified = user.email_verified_at ? 'Verified' : 'Not Verified';

              // Format profile completion
              const profileComplete = user.profile_complete ? 'Complete' : 'Incomplete';

              // Format payment info status
              const paymentInfoComplete = user.payment_info_completed ? 'Complete' : 'Incomplete';

              // Format wallet data
              const wallet = walletResponse.wallet;
              const totalBalance = (parseFloat(wallet.deposit) + parseFloat(wallet.profit) + parseFloat(wallet.bonus) + parseFloat(wallet.cash)).toFixed(2);

              // Populate user details modal with real data
              $('#userDetailsModal .modal-body').html(`
                <div class="row">
                  <div class="col-md-6">
                    <div class="card">
                      <div class="card-body">
                        <h6 class="card-title">Personal Information</h6>
                        <p><strong>Full Name:</strong> ${fullName}</p>
                        <p><strong>Username:</strong> ${user.username || 'N/A'}</p>
                        <p><strong>Email:</strong> ${user.email || 'N/A'}</p>
                        <p><strong>Phone:</strong> ${user.phone || 'N/A'}</p>
                        <p><strong>Country:</strong> ${user.country || 'N/A'}</p>
                        <p><strong>State:</strong> ${user.state || 'N/A'}</p>
                        <p><strong>Address:</strong> ${user.address || 'N/A'}</p>
                        <p><strong>Organization:</strong> ${user.organization || 'N/A'}</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="card">
                      <div class="card-body">
                        <h6 class="card-title">Account Details</h6>
                        <p><strong>Role:</strong> <span class="badge bg-primary">${user.role || 'user'}</span></p>
                        <p><strong>Email Status:</strong> <span class="badge ${user.email_verified_at ? 'bg-success' : 'bg-warning'}">${emailVerified}</span></p>
                        <p><strong>Profile:</strong> <span class="badge ${user.profile_complete ? 'bg-success' : 'bg-warning'}">${profileComplete}</span></p>
                        <p><strong>Payment Info:</strong> <span class="badge ${user.payment_info_completed ? 'bg-success' : 'bg-warning'}">${paymentInfoComplete}</span></p>
                        <p><strong>Referral Code:</strong> ${user.referral_code || 'N/A'}</p>
                        <p><strong>Created:</strong> ${new Date(user.created_at).toLocaleDateString()}</p>
                        <p><strong>Last Updated:</strong> ${new Date(user.updated_at).toLocaleDateString()}</p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-12">
                    <div class="card">
                      <div class="card-body">
                        <h6 class="card-title">Wallet Summary</h6>
                        <div class="row">
                          <div class="col-3">
                            <p><strong>Deposit:</strong> <span class="text-success">$${parseFloat(wallet.deposit).toFixed(2)}</span></p>
                          </div>
                          <div class="col-3">
                            <p><strong>Profit:</strong> <span class="text-info">$${parseFloat(wallet.profit).toFixed(2)}</span></p>
                          </div>
                          <div class="col-3">
                            <p><strong>Bonus:</strong> <span class="text-warning">$${parseFloat(wallet.bonus).toFixed(2)}</span></p>
                          </div>
                          <div class="col-3">
                            <p><strong>Cash:</strong> <span class="text-primary">$${parseFloat(wallet.cash).toFixed(2)}</span></p>
                          </div>
                        </div>
                        <hr>
                        <p><strong>Total Balance:</strong> <span class="h5 text-success">$${totalBalance}</span></p>
                      </div>
                    </div>
                  </div>
                </div>
                ${user.telegram_id ? `
                <div class="row mt-3">
                  <div class="col-12">
                    <div class="card">
                      <div class="card-body">
                        <h6 class="card-title">Additional Information</h6>
                        <p><strong>Telegram ID:</strong> ${user.telegram_id}</p>
                        <p><strong>Language:</strong> ${user.language || 'N/A'}</p>
                        <p><strong>Timezone:</strong> ${user.timezone || 'N/A'}</p>
                        <p><strong>Currency:</strong> ${user.currency || 'N/A'}</p>
                      </div>
                    </div>
                  </div>
                </div>
                ` : ''}
              `);
            },
            error: function(xhr, status, error) {
              console.error('Error loading user details:', error);
              $('#userDetailsModal .modal-body').html('<div class="alert alert-danger">Error loading user details: ' + error + '</div>');
            }
          });
        }
      },
      error: function(xhr, status, error) {
        console.error('Error loading wallet data:', error);
        $('#userDetailsModal').modal('show');
        $('#userDetailsModal .modal-body').html('<div class="alert alert-danger">Error loading wallet data: ' + error + '</div>');
      }
    });
  }

  // Function to load transaction history
  function loadTransactionHistory(walletId) {
    $.ajax({
      url: window.location.origin + '/admin/wallets/' + walletId + '/history',
      type: 'GET',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend: function() {
        $('#transactionHistoryModal').modal('show');
        $('#history_user_name').text('Loading...');
        // Show loading state
        $('#transaction_history_body').html(`
          <tr>
            <td colspan="8" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2 mb-0">Loading transaction history...</p>
            </td>
          </tr>
        `);
      },
      success: function(response) {
        if (response.success) {
          $('#history_user_name').text(response.user_name);

          if (response.transactions && response.transactions.length > 0) {
            var tbody = '';
            response.transactions.forEach(function(transaction) {
              var typeClass = transaction.type === 'add' ? 'text-success' : 'text-danger';
              var typeIcon = transaction.type === 'add' ? 'ti-plus' : 'ti-minus';
              var amountPrefix = transaction.type === 'add' ? '+' : '-';

              tbody += `
                <tr>
                  <td>${new Date(transaction.created_at).toLocaleString()}</td>
                  <td>
                    <span class="${typeClass}">
                      <i class="ti ${typeIcon} me-1"></i>
                      ${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}
                    </span>
                  </td>
                  <td class="${typeClass}">
                    <strong>${amountPrefix}$${parseFloat(transaction.amount).toFixed(2)}</strong>
                  </td>
                  <td>
                    <span class="badge bg-primary">${transaction.balance_type.charAt(0).toUpperCase() + transaction.balance_type.slice(1)}</span>
                  </td>
                  <td>$${parseFloat(transaction.balance_before).toFixed(2)}</td>
                  <td>$${parseFloat(transaction.balance_after).toFixed(2)}</td>
                  <td>${transaction.reason || 'N/A'}</td>
                  <td>${transaction.admin_name || 'System'}</td>
                </tr>
              `;
            });
            $('#transaction_history_body').html(tbody);
          } else {
            $('#transaction_history_body').html(`
              <tr>
                <td colspan="8" class="text-center py-4">
                  <i class="ti ti-history fs-1 text-muted"></i>
                  <p class="mt-2 mb-0 text-muted">No transaction history found</p>
                </td>
              </tr>
            `);
          }
        }
      },
      error: function(xhr) {
        console.error('Error loading transaction history:', xhr);
        $('#transactionHistoryModal').modal('hide');
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to load transaction history'
        });
      }
    });
  }

  // Clear form when modal is hidden
  $('#adjustWalletModal').on('hidden.bs.modal', function() {
    $('#adjustWalletForm')[0].reset();
    $('#current_deposit, #current_profit, #current_bonus, #current_cash').text('0.00');
    $('#modal_user_name').text('Loading...');
  });

  // Clear user details modal when hidden
  $('#userDetailsModal').on('hidden.bs.modal', function() {
    $('#user_full_name, #user_email, #user_username, #user_created_at, #user_status').text('Loading...');
    $('#user_deposit, #user_profit, #user_bonus, #user_cash, #user_total_balance').text('0.00');
  });

  // Clear transaction history modal when hidden
  $('#transactionHistoryModal').on('hidden.bs.modal', function() {
    $('#history_user_name').text('User');
    $('#transaction_history_body').html(`
      <tr>
        <td colspan="8" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2 mb-0">Loading transaction history...</p>
        </td>
      </tr>
    `);
  });
});
