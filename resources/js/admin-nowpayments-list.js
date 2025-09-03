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
