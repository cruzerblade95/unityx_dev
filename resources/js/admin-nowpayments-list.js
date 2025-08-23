'use strict';

// DataTable initialization
document.addEventListener('DOMContentLoaded', function () {
  // DataTable Initialization
  const depositsTable = $('.datatables-deposits').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: baseUrl + 'admin/nowpayments/list',
      type: 'GET'
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
    },
    initComplete: function () {
      // Adding status filter once table initialized
      this.api()
        .columns(4) // Status column
        .every(function () {
          const column = this;
          const select = $(
            '<select id="DepositStatus" class="form-select text-capitalize"><option value="">All Statuses</option></select>'
          )
            .appendTo('.deposit_status')
            .on('change', function () {
              const val = $.fn.dataTable.util.escapeRegex($(this).val());
              column.search(val ? '^' + val + '$' : '', true, false).draw();
            });

          // Add status options
          select.append('<option value="pending">Pending</option>');
          select.append('<option value="completed">Completed</option>');
          select.append('<option value="failed">Failed</option>');
          select.append('<option value="rejected">Rejected</option>');
        });

      // Adding payment method filter
      this.api()
        .columns(3) // Payment ID column (we'll use this to filter by payment method)
        .every(function () {
          const column = this;
          const select = $(
            '<select id="PaymentMethod" class="form-select text-capitalize"><option value="">All Payment Methods</option></select>'
          )
            .appendTo('.payment_method')
            .on('change', function () {
              const val = $(this).val();
              if (val === 'nowpayments') {
                // Show only records with payment_id (NOWPayments)
                column.search('.+', true, false).draw();
              } else if (val === 'manual') {
                // Show only records without payment_id (Manual)
                column.search('^$', true, false).draw();
              } else {
                // Show all
                column.search('', true, false).draw();
              }
            });

          // Add payment method options
          select.append('<option value="nowpayments">NOWPayments</option>');
          select.append('<option value="manual">Manual</option>');
        });
    }
  });
});
