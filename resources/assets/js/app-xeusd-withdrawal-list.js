'use strict';

// XEUSD Withdrawal List initialization
document.addEventListener('DOMContentLoaded', function () {
  console.log('XEUSD withdrawal list initializing...');

  // Initialize DataTable for XEUSD withdrawals
  const xeusdWithdrawalsTable = $('#xeusdWithdrawalsTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '/admin/xeusd-withdrawals/list',
      type: 'GET'
    },
    columns: [
      {
        data: 'id',
        name: 'id',
        title: 'ID'
      },
      {
        data: 'user_info',
        name: 'user_info',
        title: 'User',
        render: function (data, type, row) {
          if (type === 'display' && data) {
            const name = data.firstname + ' ' + data.lastname;
            const username = data.username || data.email;
            return `<div><strong>${name}</strong><br><small class="text-muted">@${username}</small></div>`;
          }
          return data ? data.firstname + ' ' + data.lastname : 'N/A';
        }
      },
      {
        data: 'amount_usd',
        name: 'amount_usd',
        title: 'USD Amount',
        render: function (data, type, row) {
          return '$' + parseFloat(data).toFixed(2);
        }
      },
      {
        data: 'amount_xeusd',
        name: 'amount_xeusd',
        title: 'XEUSD Amount',
        render: function (data, type, row) {
          return parseFloat(data).toFixed(2) + ' XEUSD';
        }
      },
      {
        data: 'recipient_address',
        name: 'recipient_address',
        title: 'Wallet Address',
        render: function (data, type, row) {
          if (data && data.length > 10) {
            return data.substring(0, 6) + '...' + data.substring(data.length - 4);
          }
          return data;
        }
      },
      {
        data: 'status',
        name: 'status',
        title: 'Status',
        render: function (data, type, row) {
          let badgeClass = '';
          switch (data) {
            case 'pending':
              badgeClass = 'bg-warning';
              break;
            case 'processing':
              badgeClass = 'bg-info';
              break;
            case 'approved':
            case 'completed':
              badgeClass = 'bg-success';
              break;
            case 'rejected':
              badgeClass = 'bg-danger';
              break;
            default:
              badgeClass = 'bg-secondary';
          }
          return `<span class="badge ${badgeClass}">${data}</span>`;
        }
      },
      {
        data: 'created_at',
        name: 'created_at',
        title: 'Created At',
        render: function (data, type, row) {
          if (type === 'display' && data) {
            const date = new Date(data);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
          }
          return data;
        }
      },
      {
        data: 'actions',
        name: 'actions',
        title: 'Actions',
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          let actions = '';

          // View button - use numeric id for view route
          actions += `<a href="/admin/xeusd-withdrawals/${row.id}" class="btn btn-sm btn-info me-1" title="View Details">
                        <i class="fas fa-eye"></i>
                    </a>`;

          // Action buttons based on status - use unique_id for approve/reject
          if (row.status === 'pending') {
            actions += `<button type="button" class="btn btn-sm btn-success me-1" onclick="approveWithdrawal('${row.unique_id}')" title="Approve">
                            <i class="fas fa-check"></i>
                        </button>`;
            actions += `<button type="button" class="btn btn-sm btn-danger" onclick="rejectWithdrawal('${row.unique_id}')" title="Reject">
                            <i class="fas fa-times"></i>
                        </button>`;
          }

          return actions;
        }
      }
    ],
    order: [[0, 'desc']], // Sort by ID descending (newest first)
    pageLength: 25,
    responsive: true,
    language: {
      processing: 'Processing XEUSD withdrawals...',
      emptyTable: 'No XEUSD withdrawals found',
      zeroRecords: 'No matching XEUSD withdrawals found'
    }
  });

  console.log('XEUSD withdrawals DataTable initialized:', xeusdWithdrawalsTable);
});

// Global functions for withdrawal actions
window.approveWithdrawal = function (id) {
  Swal.fire({
    title: 'Approve Withdrawal?',
    text: "This will process the XEUSD withdrawal to the user's wallet.",
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, approve it!'
  }).then(result => {
    if (result.isConfirmed) {
      // Send AJAX request to approve
      $.ajax({
        url: `/admin/xeusd-withdrawals/${id}/approve`,
        type: 'POST',
        data: {
          _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          if (response.success) {
            Swal.fire('Approved!', response.message, 'success');
            $('#xeusdWithdrawalsTable').DataTable().ajax.reload();
          } else {
            Swal.fire('Error!', response.message || 'Failed to approve withdrawal', 'error');
          }
        },
        error: function (xhr) {
          const errorMsg = xhr.responseJSON?.message || 'Failed to approve withdrawal';
          Swal.fire('Error!', errorMsg, 'error');
        }
      });
    }
  });
};

window.rejectWithdrawal = function (id) {
  Swal.fire({
    title: 'Reject Withdrawal?',
    html: `
      <div class="mb-3">
        <label for="rejection-reason" class="form-label">Rejection Reason *</label>
        <textarea
          id="rejection-reason"
          class="form-control"
          rows="4"
          placeholder="Please provide a reason for rejection (required)..."
          maxlength="500"
        ></textarea>
        <small class="text-muted">Maximum 500 characters</small>
      </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, reject it!',
    cancelButtonText: 'Cancel',
    preConfirm: () => {
      const reason = document.getElementById('rejection-reason').value.trim();
      if (!reason) {
        Swal.showValidationMessage('Please provide a rejection reason');
        return false;
      }
      if (reason.length < 10) {
        Swal.showValidationMessage('Reason must be at least 10 characters');
        return false;
      }
      return reason;
    }
  }).then(result => {
    if (result.isConfirmed && result.value) {
      // Send AJAX request to reject with reason
      $.ajax({
        url: `/admin/xeusd-withdrawals/${id}/reject`,
        type: 'POST',
        data: {
          _token: $('meta[name="csrf-token"]').attr('content'),
          reason: result.value
        },
        success: function (response) {
          if (response.success) {
            Swal.fire('Rejected!', response.message, 'success');
            $('#xeusdWithdrawalsTable').DataTable().ajax.reload();
          } else {
            Swal.fire('Error!', response.message || 'Failed to reject withdrawal', 'error');
          }
        },
        error: function (xhr) {
          const errorMsg = xhr.responseJSON?.message || 'Failed to reject withdrawal';
          Swal.fire('Error!', errorMsg, 'error');
        }
      });
    }
  });
};
