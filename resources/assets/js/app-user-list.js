'use strict';

// User List initialization
document.addEventListener('DOMContentLoaded', function () {
  // DataTable Initialization
  const userTable = $('.datatables-users').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '/admin/users/list',
      type: 'GET'
    },
    columns: [
      // Responsive control column
      {
        data: null,
        defaultContent: '',
        className: 'control',
        orderable: false,
        searchable: false
      },
      // User column with avatar
      {
        data: 'firstname',
        render: function (data, type, row) {
          // For search and export, return plain text
          if (type === 'search' || type === 'export') {
            const fullName = row.firstname + ' ' + row.lastname;
            const username = row.username || '';
            const email = row.email || '';
            return fullName + ' ' + username + ' @' + username + ' ' + email;
          }

          // Get the profile photo URL or use default avatar
          const photoUrl = row.profile_photo_path
            ? `/storage/${row.profile_photo_path}`
            : '/assets/img/avatars/1.png';

          const fullName = row.firstname + ' ' + row.lastname;

          // Debug logging
          console.log('Row data:', {
            firstname: row.firstname,
            lastname: row.lastname,
            username: row.username,
            email: row.email,
            fullRow: row
          });

          const username = row.username ? `@${row.username}` : row.email;
          const bannedBadge = row.is_banned ? '<span class="badge bg-danger ms-2">BANNED</span>' : '';

          return `
            <div class="d-flex justify-content-start align-items-center user-name">
              <div class="avatar-wrapper">
                <div class="avatar me-2">
                  <img src="${photoUrl}" alt="Avatar" class="rounded-circle">
                </div>
              </div>
              <div class="d-flex flex-column">
                <span class="fw-medium">${fullName} ${bannedBadge}</span>
                <small class="text-muted">${username}</small>
              </div>
            </div>
          `;
        }
      },
      // Email column (hidden as it's shown in name column)
      {
        data: 'email',
        visible: false
      },
      // Role column
      {
        data: 'role',
        render: function (data) {
          const roleClasses = {
            'admin': 'bg-label-primary',
            'user': 'bg-label-success'
          };
          const roleIcons = {
            'admin': 'fa-shield-alt',
            'user': 'fa-user'
          };
          return `
            <span class="badge ${roleClasses[data] || 'bg-label-primary'}">
              <i class="fas ${roleIcons[data]} me-1"></i>
              ${data}
            </span>
          `;
        }
      },
      // Status column
      {
        data: 'status',
        render: function (data, type, row) {
          // Check if user is banned first
          if (row.is_banned) {
            return `
              <span class="badge bg-label-danger">
                <i class="fas fa-ban me-1"></i>
                Banned
              </span>
            `;
          }

          const statusClasses = {
            'online': 'bg-label-success',
            'offline': 'bg-label-secondary'
          };
          const statusIcons = {
            'online': 'fa-check-circle',
            'offline': 'fa-times-circle'
          };
          return `
            <span class="badge ${statusClasses[data]}">
              <i class="fas ${statusIcons[data]} me-1"></i>
              ${data}
            </span>
          `;
        }
      },
      // Created at column
      {
        data: 'created_at',
        render: function (data) {
          return moment(data).format('MMM DD, YYYY');
        }
      },
      // Actions column
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          const banButton = row.is_banned
            ? `<button class="btn btn-sm btn-icon btn-success unban-record" data-id="${row.id}" title="Unban User">
                 <i class="fas fa-user-check"></i>
               </button>`
            : `<button class="btn btn-sm btn-icon btn-warning ban-record" data-id="${row.id}" title="Ban User">
                 <i class="fas fa-ban"></i>
               </button>`;

          return `
            <div class="d-inline-flex gap-2">
              <a href="/admin/users/${row.id}/profile" class="btn btn-sm btn-icon btn-info" title="View Profile">
                <i class="fas fa-user"></i>
              </a>
              <button class="btn btn-sm btn-icon btn-primary edit-record" data-id="${row.id}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditUser" title="Edit User">
                <i class="fas fa-edit"></i>
              </button>
              ${banButton}
              <button class="btn btn-sm btn-icon btn-danger delete-record" data-id="${row.id}" title="Delete User">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          `;
        }
      }
    ],
    order: [[5, 'desc']], // Sort by created_at by default
    dom: '<"row mx-2"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    language: {
      sLengthMenu: '_MENU_',
      search: '',
      searchPlaceholder: 'Search Users...'
    },
    // Buttons with Dropdown
    buttons: [],
    // For responsive features
    responsive: {
      details: {
        display: $.fn.dataTable.Responsive.display.modal({
          header: function (row) {
            return 'Details of ' + row.data().firstname + ' ' + row.data().lastname;
          }
        }),
        type: 'column',
        renderer: function (api, rowIdx, columns) {
          const data = $.map(columns, function (col, i) {
            return col.title !== '' // ? Do not show actions in details
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

  // Initialize Filters
  initializeFilters();

  function initializeFilters() {
    // Role filter
    const roleFilter = `
      <select class="form-select text-capitalize" id="UserRole">
        <option value="">Select Role</option>
        <option value="admin">Admin</option>
        <option value="user">User</option>
      </select>
    `;
    $('.user_role').html(roleFilter);

    // Status filter
    const statusFilter = `
      <select class="form-select text-capitalize" id="UserStatus">
        <option value="">Select Status</option>
        <option value="online">Online</option>
        <option value="offline">Offline</option>
      </select>
    `;
    $('.user_status').html(statusFilter);

    // Role filter event
    $('#UserRole').on('change', function() {
      const role = $(this).val();
      userTable.column(3).search(role).draw();
    });

    // Status filter event
    $('#UserStatus').on('change', function() {
      const status = $(this).val();
      userTable.column(4).search(status).draw();
    });
  }

  // Edit Record
  $(document).on('click', '.edit-record', function() {
    const id = $(this).data('id');
    // Fetch user data
    $.ajax({
      url: `/admin/users/${id}`,
      type: 'GET',
      success: function(data) {
        // Fill the edit form with user data
        $('#editUserForm input[name="username"]').val(data.username);
        $('#editUserForm input[name="email"]').val(data.email);
        $('#editUserForm select[name="role"]').val(data.role);
        $('#editUserForm').data('id', id);
      }
    });
  });

  // Update User
  $('#editUserForm').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const id = form.data('id');

    $.ajax({
      url: `/admin/users/${id}`,
      type: 'PUT',
      data: form.serialize(),
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(data) {
        if (data.success) {
          userTable.ajax.reload();
          $('#offcanvasEditUser').offcanvas('hide');

          Swal.fire({
            icon: 'success',
            title: 'Updated!',
            text: 'User has been updated successfully.',
            customClass: {
              confirmButton: 'btn btn-success'
            },
            buttonsStyling: false
          });
        }
      },
      error: function(error) {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: error.responseJSON?.message || 'Could not update the user.',
          customClass: {
            confirmButton: 'btn btn-primary'
          },
          buttonsStyling: false
        });
      }
    });
  });

  // Delete Record
  $(document).on('click', '.delete-record', function() {
    const id = $(this).data('id');

    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        // Delete request
        $.ajax({
          url: `/admin/users/${id}`,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (data) {
            if (data.success) {
              userTable.ajax.reload();
              Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'User has been deleted.',
                customClass: {
                  confirmButton: 'btn btn-success'
                },
                buttonsStyling: false
              });
            }
          },
          error: function (error) {
            console.error('Error:', error);
            Swal.fire({
              title: 'Error!',
              text: 'Could not delete the user.',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-primary'
              },
              buttonsStyling: false
            });
          }
        });
      }
    });
  });

  // Form submission for new user
  $(document).on('submit', '#addNewUserForm', function (e) {
    e.preventDefault();
    console.log('Form submitted');

    const form = $(this);
    const url = form.data('action');
    const formData = form.serialize();
    const token = $('meta[name="csrf-token"]').attr('content');

    console.log('Submission details:', {
      url: url,
      formData: formData,
      hasToken: !!token
    });

    $.ajax({
      url: url,
      type: 'POST',
      data: formData,
      headers: {
        'X-CSRF-TOKEN': token
      },
      success: function (data) {
        if (data.success) {
          userTable.ajax.reload();
          $('#offcanvasAddUser').offcanvas('hide');
          form[0].reset();

          Swal.fire({
            icon: 'success',
            title: 'Added!',
            text: 'User has been added successfully.',
            customClass: {
              confirmButton: 'btn btn-success'
            },
            buttonsStyling: false
          });
        }
      },
      error: function (error) {
        console.error('Full error details:', error);
        let errorMessage = 'Could not add the user.';

        if (error.responseJSON) {
          console.log('Server response:', error.responseJSON);
          if (error.responseJSON.errors) {
            // Handle validation errors
            errorMessage = Object.entries(error.responseJSON.errors)
              .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
              .join('\n');
          } else if (error.responseJSON.message) {
            errorMessage = error.responseJSON.message;
            console.log('Error message:', error.responseJSON.message);
            if (error.responseJSON.exception) {
              console.log('Exception:', error.responseJSON.exception);
              console.log('File:', error.responseJSON.file);
              console.log('Line:', error.responseJSON.line);
            }
          }
        }

        Swal.fire({
          icon: 'error',
          title: 'Error!',
          html: errorMessage.replace(/\n/g, '<br>'),
          customClass: {
            confirmButton: 'btn btn-primary'
          },
          buttonsStyling: false
        });
      }
    });
  });

  // Ban User
  $(document).on('click', '.ban-record', function() {
    const id = $(this).data('id');

    Swal.fire({
      title: 'Ban User',
      html: `
        <div class="mb-3">
          <label for="ban-reason" class="form-label">Reason for banning:</label>
          <textarea id="ban-reason" class="form-control" rows="3"
                    placeholder="Enter the reason for banning this user..." required></textarea>
        </div>
      `,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ban User',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'btn btn-warning me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false,
      preConfirm: () => {
        const reason = document.getElementById('ban-reason').value;
        if (!reason.trim()) {
          Swal.showValidationMessage('Please provide a reason for banning');
          return false;
        }
        return { reason: reason };
      }
    }).then(function (result) {
      if (result.value) {
        $.ajax({
          url: `/admin/users/${id}/ban`,
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ban_reason: result.value.reason
          },
          success: function (data) {
            if (data.success) {
              userTable.ajax.reload();
              Swal.fire({
                icon: 'success',
                title: 'Banned!',
                text: 'User has been banned successfully.',
                customClass: {
                  confirmButton: 'btn btn-success'
                },
                buttonsStyling: false
              });
            }
          },
          error: function (error) {
            let errorMessage = 'Could not ban the user.';
            if (error.responseJSON && error.responseJSON.message) {
              errorMessage = error.responseJSON.message;
            }
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: errorMessage,
              customClass: {
                confirmButton: 'btn btn-primary'
              },
              buttonsStyling: false
            });
          }
        });
      }
    });
  });

  // Unban User
  $(document).on('click', '.unban-record', function() {
    const id = $(this).data('id');

    Swal.fire({
      title: 'Unban User',
      text: 'Are you sure you want to unban this user?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Unban User',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'btn btn-success me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        $.ajax({
          url: `/admin/users/${id}/unban`,
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (data) {
            if (data.success) {
              userTable.ajax.reload();
              Swal.fire({
                icon: 'success',
                title: 'Unbanned!',
                text: 'User has been unbanned successfully.',
                customClass: {
                  confirmButton: 'btn btn-success'
                },
                buttonsStyling: false
              });
            }
          },
          error: function (error) {
            let errorMessage = 'Could not unban the user.';
            if (error.responseJSON && error.responseJSON.message) {
              errorMessage = error.responseJSON.message;
            }
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: errorMessage,
              customClass: {
                confirmButton: 'btn btn-primary'
              },
              buttonsStyling: false
            });
          }
        });
      }
    });
  });
});
