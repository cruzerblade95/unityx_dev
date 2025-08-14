@extends('layouts/layoutMaster')

@section('title', 'Wallet Management')

@section('vendor-style')
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
                <input type="text" class="form-control" id="searchInput" placeholder="Search by username or name...">
            </div>
        </div>
    </div>
    <div class="card-datatable table-responsive">
        <table class="datatables-wallets table border-top">
            <thead>
                <tr>
                    <th></th>
                    <th>User</th>
                    <th>Deposit</th>
                    <th>Profit</th>
                    <th>Bonus</th>
                    <th>Cash</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Offcanvas to update wallet -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasUpdateWallet" aria-labelledby="offcanvasUpdateWalletLabel">
    <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasUpdateWalletLabel" class="offcanvas-title">Update Wallet</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 pt-0 h-100">
        <form id="updateWalletForm" class="row g-3">
            @csrf
            <div class="col-12">
                <div id="userInfo" class="d-flex align-items-center mb-3">
                    <div class="avatar avatar-sm me-3">
                        <img src="" alt="User Avatar" class="rounded-circle" id="userAvatar">
                    </div>
                    <div class="d-flex flex-column">
                        <h6 class="mb-0" id="userName"></h6>
                        <small class="text-muted" id="userUsername"></small>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">Select Balance Type</label>
                <select class="form-select" name="type" required>
                    <option value="deposit">Deposit</option>
                    <option value="profit">Profit</option>
                    <option value="bonus">Bonus</option>
                    <option value="cash">Cash</option>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Action</label>
                <select class="form-select" name="action" required>
                    <option value="add">Add Amount</option>
                    <option value="subtract">Subtract Amount</option>
                    <option value="modify">Modify Balance</option>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Amount</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                </div>
            </div>

            <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary me-1">Update Balance</button>
                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
@vite(['resources/assets/js/app-wallet-list.js'])
@endsection
