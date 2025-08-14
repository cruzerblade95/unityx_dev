@extends('layouts/layoutMaster')

@section('title', 'User Management')

@section('vendor-style')
<!-- Add Fon          <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser">
            <i class="fas fa-plus me-1"></i> Add New User
          </button>wesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleave-zen/cleave-zen.js'])
@endsection

@section('page-script')
@vite('resources/assets/js/app-user-list.js')
@endsection

@section('content')
<div class="row g-6 mb-6">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Total Users</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $totalUsers }}</h4>
              <p class="text-success mb-0">(+{{ $userGrowth }}%)</p>
            </div>
            <small class="mb-0">Total Registered Users</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="fas fa-users fa-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Active Users</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $activeUsers }}</h4>
              <p class="text-success mb-0">(+{{ $activeUsersGrowth }}%)</p>
            </div>
            <small class="mb-0">Last week analytics</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-success">
              <i class="fas fa-user-check fa-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Online Users</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $onlineUsers }}</h4>
              <p class="text-success mb-0">(Now)</p>
            </div>
            <small class="mb-0">Currently Online</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="icon-base ti tabler-user-plus icon-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">New Users</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $newUsers }}</h4>
              <p class="text-success mb-0">(This Week)</p>
            </div>
            <small class="mb-0">This week's new registrations</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="icon-base ti tabler-user-plus icon-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Users List Table -->
<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-0">Search Filters</h5>
    <div class="d-flex justify-content-between align-items-center row pt-4 gap-4 gap-md-0">
      <div class="col-md-4 user_role"></div>
      <div class="col-md-4 user_status"></div>
      <div class="col-md-4">
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser">
          <i class="icon-base ti tabler-plus"></i> Add New User
        </button>
      </div>
    </div>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th></th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Joined Date</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
  
  <!-- Add CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <!-- Offcanvas to add new user -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form class="add-new-user pt-0" id="addNewUserForm" method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-user-fullname">Full Name</label>
          <input type="text" class="form-control" id="add-user-fullname" placeholder="John Doe" name="name" required />
        </div>
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-user-email">Email</label>
          <input type="email" id="add-user-email" class="form-control" placeholder="john.doe@example.com" name="email" required />
        </div>
        <div class="mb-6">
          <label class="form-label" for="add-user-password">Password</label>
          <input type="password" id="add-user-password" class="form-control" name="password" required />
        </div>
        <div class="mb-6">
          <label class="form-label" for="user-role">User Role</label>
          <select id="user-role" class="form-select" name="role" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary me-3 data-submit">Submit</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
      </form>
    </div>
  </div>

  <!-- Offcanvas to edit user -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditUser" aria-labelledby="offcanvasEditUserLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasEditUserLabel" class="offcanvas-title">Edit User</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form class="edit-user pt-0" id="editUserForm">
        @csrf
        @method('PUT')
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="edit-user-fullname">Full Name</label>
          <input type="text" class="form-control" id="edit-user-fullname" placeholder="John Doe" name="name" required />
        </div>
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="edit-user-email">Email</label>
          <input type="email" id="edit-user-email" class="form-control" placeholder="john.doe@example.com" name="email" required />
        </div>
        <div class="mb-6">
          <label class="form-label" for="edit-user-password">Password</label>
          <input type="password" id="edit-user-password" class="form-control" name="password" placeholder="Leave blank to keep current password" />
          <small class="text-muted">Leave blank to keep current password</small>
        </div>
        <div class="mb-6">
          <label class="form-label" for="edit-user-role">User Role</label>
          <select id="edit-user-role" class="form-select" name="role" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary me-3 data-submit">Save Changes</button>
        <button type="button" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
      </form>
    </div>
  </div>
</div>
@endsection
