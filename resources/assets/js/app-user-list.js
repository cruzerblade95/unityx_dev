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
        data: 'name',
        render: function (data, type, row) {
          // Get the profile photo URL or use default avatar
          const photoUrl = row.profile_photo_path 
            ? `/storage/${row.profile_photo_path}`
            : '/assets/img/avatars/1.png';

          return `
            <div class="d-flex justify-content-start align-items-center user-name">
              <div class="avatar-wrapper">
                <div class="avatar me-2">
                  <img src="${photoUrl}" alt="Avatar" class="rounded-circle">
                </div>
              </div>
              <div class="d-flex flex-column">
                <span class="fw-medium">${data}</span>
                <small class="text-muted">${row.email}</small>
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
        render: function (data) {
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
          return `
            <div class="dropdown">
              <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-vertical"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <a href="javascript:;" class="dropdown-item edit-record" data-id="${row.id}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditUser">
                  <i class="fas fa-edit me-2"></i>Edit
                </a>
                <a href="javascript:;" class="dropdown-item text-danger delete-record" data-id="${row.id}">
                  <i class="fas fa-trash-alt me-2"></i>Delete
                </a>
              </div>
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
            return 'Details of ' + row.data().name;
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

  // Edit Record
  $(document).on('click', '.edit-record', function() {
    const id = $(this).data('id');
    // Fetch user data
    $.ajax({
      url: `/admin/users/${id}`,
      type: 'GET',
      success: function(data) {
        // Fill the edit form with user data
        $('#editUserForm input[name="name"]').val(data.name);
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
  $('#addNewUserForm').on('submit', function (e) {
    e.preventDefault();
    const form = $(this);
    
    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize(),
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: error.responseJSON?.message || 'Could not add the user.',
          customClass: {
            confirmButton: 'btn btn-primary'
          },
          buttonsStyling: false
        });
      }
    });
  });
});
