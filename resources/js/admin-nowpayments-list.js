'use strict';

// DataTable initialization
document.addEventListener('DOMContentLoaded', function () {
  // DataTable Initialization
  const depositsTable = $('.datatables-deposits').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: baseUrl + 'admin/nowpayments/list',
      type: 'GET',
      data: function (d) {
        // Only add filter parameters if they have actual values
        const statusFilter = $('#CustomDepositStatus').val();
        const paymentMethodFilter = $('#CustomPaymentMethod').val();

        if (statusFilter && statusFilter !== '') {
          d.status_filter = statusFilter;
        }

        if (paymentMethodFilter && paymentMethodFilter !== '') {
          d.payment_method_filter = paymentMethodFilter;
        }

        // Debug logging
        console.log('DataTable sending filters:', {
          status_filter: d.status_filter || 'not sent',
          payment_method_filter: d.payment_method_filter || 'not sent',
          all_params: d
        });
      }
    },
    columns: [
      // ID column
      {
        data: 'id',
        name: 'id'
      },
      // User column
      {
        data: 'user',
        name: 'user',
        orderable: false,
        searchable: false
      },
      // Amount column
      {
        data: 'amount',
        name: 'amount'
      },
      // Payment ID column
      {
        data: 'payment_id',
        name: 'payment_id'
      },
      // Status column
      {
        data: 'status',
        name: 'status'
      },
      // Created at column
      {
        data: 'created_at',
        name: 'created_at'
      },
      // Actions column
      {
        data: 'actions',
        name: 'actions',
        orderable: false,
        searchable: false
      }
    ],
    columnDefs: [
      {
        // Responsive control
        className: 'control',
        orderable: false,
        searchable: false,
        targets: 0
      }
    ],
    order: [[0, 'desc']], // Sort by ID by default (newest first)
    responsive: {
      details: {
        display: DataTable.Responsive.display.modal({
          header: function (row) {
            const data = row.data();
            return 'Details of Deposit #' + data.id;
          }
        }),
        type: 'column',
        renderer: function (api, rowIdx, columns) {
          const data = columns
            .map(function (col) {
              return col.title !== '' // Do not show row in modal popup if title is blank (for check box)
                ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                    <td>${col.title}:</td>
                    <td>${col.data}</td>
                  </tr>`
                : '';
            })
            .join('');

          if (data) {
            const div = document.createElement('div');
            div.classList.add('table-responsive');
            const table = document.createElement('table');
            div.appendChild(table);
            table.classList.add('table');
            const tbody = document.createElement('tbody');
            tbody.innerHTML = data;
            table.appendChild(tbody);
            return div;
          }
          return false;
        }
      }
    },
    layout: {
      topStart: {
        rowClass: 'row mx-3 my-0 justify-content-between',
        features: [
          {
            pageLength: {
              menu: [10, 25, 50, 100],
              text: 'Show_MENU_entries'
            }
          }
        ]
      },
      topEnd: {
        search: {
          placeholder: 'Search deposits...'
        }
      },
      bottomStart: {
        rowClass: 'row mx-3 justify-content-between',
        features: ['info']
      },
      bottomEnd: 'paging'
    },
    displayLength: 10,
    language: {
      paginate: {
        next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
        previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>',
        first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
        last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>'
      },
      search: '',
      searchPlaceholder: 'Search deposits...',
      info: 'Showing _START_ to _END_ of _TOTAL_ entries',
      infoEmpty: 'Showing 0 to 0 of 0 entries',
      infoFiltered: '(filtered from _MAX_ total entries)',
      lengthMenu: 'Show _MENU_ entries',
      loadingRecords: 'Loading...',
      processing: 'Processing...',
      emptyTable: 'No deposits found',
      zeroRecords: 'No matching deposits found'
    }
  });

  // Add event listeners for filters (with existence check)
  const statusFilter = $('#CustomDepositStatus');
  const paymentMethodFilter = $('#CustomPaymentMethod');

  console.log('Filter elements found:', {
    statusFilter: statusFilter.length,
    paymentMethodFilter: paymentMethodFilter.length
  });

  if (statusFilter.length > 0) {
    statusFilter.on('change', function () {
      console.log('Status filter changed to:', $(this).val());
      depositsTable.ajax.reload();
    });
  } else {
    console.error('CustomDepositStatus filter element not found');
  }

  if (paymentMethodFilter.length > 0) {
    paymentMethodFilter.on('change', function () {
      console.log('Payment method filter changed to:', $(this).val());
      depositsTable.ajax.reload();
    });
  } else {
    console.error('CustomPaymentMethod filter element not found');
  }
});

// Global functions for button onclick events
window.testApiConnection = function () {
  console.log('Testing NOWPayments API connection...');

  fetch('/admin/nowpayments/test-api', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Content-Type': 'application/json'
    }
  })
    .then(response => {
      console.log('API test response status:', response.status);
      return response.json();
    })
    .then(data => {
      console.log('API test response data:', data);

      let message = 'NOWPayments API Test Results:\n\n';

      if (data.success) {
        message += '✅ API Connection: SUCCESS\n';
        message += `📋 Test Result: ${JSON.stringify(data.test_result, null, 2)}\n`;
        message += `⚙️ API Configured: ${data.api_configured ? 'Yes' : 'No'}\n`;
        message += `🧪 Sandbox Mode: ${data.sandbox_mode ? 'Yes' : 'No'}\n`;
      } else {
        message += '❌ API Connection: FAILED\n';
        message += `Error: ${data.message}\n`;
      }
      alert(message);
    })
    .catch(error => {
      console.error('API test error:', error);
      alert(`Network error during API test:
${error.message || error}`);
    });
};

window.debugConnectivity = function () {
  fetch('/admin/nowpayments/debug-connectivity', {
    method: 'GET',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Content-Type': 'application/json'
    }
  })
    .then(response => {
      console.log('Debug response status:', response.status);
      return response.json();
    })
    .then(data => {
      console.log('Debug response data:', data);

      let message = `Environment: ${data.environment}\n\n`;

      if (data.success) {
        message += '✅ Debug Test: SUCCESS\n';
        message += `📋 API Status: ${JSON.stringify(data.api_status, null, 2)}\n`;
        message += `⚙️ Config Check:\n`;
        message += `  - API Key Set: ${data.config.api_key_set ? 'Yes' : 'No'}\n`;
        message += `  - Sandbox Mode: ${data.config.sandbox_mode ? 'Yes' : 'No'}\n`;
        message += `  - Base URL: ${data.config.base_url || 'Not Set'}\n`;
      } else {
        message += '❌ Debug Test: FAILED\n';
        message += `Error: ${data.error}\n`;
      }
      alert(message);
    })
    .catch(error => {
      console.error('Debug error:', error);
      alert(`Debug network error:
${error.message || error}`);
    });
};

window.refreshAllPaymentStatuses = function () {
  if (!confirm('Are you sure you want to refresh all NOWPayments statuses? This may take a moment.')) {
    return;
  }

  // Disable the button and show loading state
  const refreshAllBtn = event.target;
  const originalHtml = refreshAllBtn.innerHTML;
  refreshAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing All...';
  refreshAllBtn.disabled = true;

  fetch('/admin/nowpayments/refresh-all-statuses', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
    .then(response => {
      console.log('Refresh all response status:', response.status);
      console.log('Refresh all response headers:', response.headers);

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      return response.json();
    })
    .then(data => {
      console.log('Refresh all response data:', data);

      if (data.success) {
        let message = `Successfully refreshed ${data.updated_count} out of ${data.total_count} payments.`;

        if (data.updated_count > 0) {
          message += `\n\nUpdated statuses:\n${data.updates
            .map(update => `Payment ${update.payment_id}: ${update.old_status} → ${update.new_status}`)
            .join('\n')}`;
        }

        Swal.fire({
          title: 'Refresh Complete!',
          text: message,
          icon: data.updated_count > 0 ? 'success' : 'info',
          confirmButtonText: 'OK'
        }).then(() => {
          // Reload DataTable to show updated statuses
          $('.datatables-deposits').DataTable().ajax.reload();
        });
      } else {
        Swal.fire({
          title: 'Error!',
          text: data.message || 'Failed to refresh payment statuses',
          icon: 'error',
          confirmButtonText: 'OK'
        });
      }
    })
    .catch(error => {
      console.error('Error refreshing all statuses:', error);

      let errorMessage = 'Network error while refreshing payment statuses';

      if (error.message) {
        errorMessage += `\n\nDetails: ${error.message}`;
        console.error('Detailed error:', error.message);
      }

      // Check if it's a specific type of error
      if (error.message.includes('HTTP 500')) {
        errorMessage += '\n\nThis is a server error. Please check the server logs.';
      } else if (error.message.includes('HTTP 404')) {
        errorMessage += '\n\nThe refresh endpoint was not found. Please check the route configuration.';
      } else if (error.message.includes('HTTP 403')) {
        errorMessage += '\n\nAccess denied. Please check your permissions.';
      } else if (error.message.includes('HTTP 419')) {
        errorMessage += '\n\nCSRF token mismatch. Please refresh the page and try again.';
      }

      Swal.fire({
        title: 'Error!',
        text: errorMessage,
        icon: 'error',
        confirmButtonText: 'OK'
      });
    })
    .finally(() => {
      refreshAllBtn.innerHTML = originalHtml;
      refreshAllBtn.disabled = false;
    });
};
