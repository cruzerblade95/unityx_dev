@extends('admin/layouts/layoutMaster')

@section('title', 'Wallet Management')

@section('vendor-style')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js'])
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-3">Search Filter</h5>
        <div class="d-flex justify-content-between align-items-center row pb-2 gap-3 gap-md-0">
            <div class="col-md-4">
                <input type="text" class="form-control" id="searchInput" placeholder="Search by username, name, or email...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="roleFilter">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary" id="applyFilters">
                    <i class="fas fa-filter"></i> Apply
                </button>
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary" id="clearFilters" title="Clear Filters">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>
    </div>
    <div class="card-datatable table-responsive">
        <table class="datatables-wallets table border-top">
            <thead>
                <tr>
                    <th></th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Wallet Status</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@endsection

@section('page-script')
@vite(['resources/assets/js/app-wallet-list.js'])
@endsection
