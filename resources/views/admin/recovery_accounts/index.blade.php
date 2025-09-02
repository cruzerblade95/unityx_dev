@php
$configData = Helper::appClasses();
@endphp

@extends('admin.layouts.layoutMaster')

@section('title', 'Recovery Accounts Management')

@section('vendor-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('content')
<div class="row g-6">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">
                        <i class="fas fa-life-ring me-2"></i>Recovery Accounts
                    </h4>
                    <small class="text-muted">Manage account recovery requests</small>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="recoveryTable" class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                <th><i class="fas fa-envelope me-1"></i>Email</th>
                                <th><i class="fas fa-user me-1"></i>Username</th>
                                <th><i class="fas fa-dollar-sign me-1"></i>Deposit</th>
                                <th><i class="fas fa-chart-line me-1"></i>Profit</th>
                                <th><i class="fas fa-user-friends me-1"></i>Referral</th>
                                <th><i class="fas fa-flag me-1"></i>Status</th>
                                <th><i class="fas fa-calendar me-1"></i>Submitted</th>
                                <th width="120"><i class="fas fa-cogs me-1"></i>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
let recoveryTable;

document.addEventListener('DOMContentLoaded', function() {
    initializeDataTable();
});

function initializeDataTable() {
    recoveryTable = $('#recoveryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.recovery_accounts.data") }}',
            type: 'GET'
        },
        columns: [
            {
                data: 'id',
                name: 'id',
                width: '60px'
            },
            {
                data: 'email',
                name: 'email',
                orderable: true
            },
            {
                data: 'username',
                name: 'username',
                orderable: true
            },
            {
                data: 'deposit_formatted',
                name: 'deposit',
                orderable: true
            },
            {
                data: 'profit_formatted',
                name: 'profit',
                orderable: true
            },
            {
                data: 'referral_username',
                name: 'referral_username',
                orderable: false,
                render: function(data, type, row) {
                    return data || '-';
                }
            },
            {
                data: 'status_badge',
                name: 'status',
                orderable: true,
                searchable: false
            },
            {
                data: 'submitted_at',
                name: 'created_at',
                orderable: true,
                searchable: false
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                width: '120px'
            }
        ],
        pageLength: 25,
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            search: "Search recovery accounts:",
            lengthMenu: "Show _MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ recovery accounts",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            },
            emptyTable: "No recovery requests found",
            zeroRecords: "No matching recovery records found"
        },
        order: [[0, 'desc']], // Order by ID descending (newest first)
        columnDefs: [
            {
                targets: [3, 4], // Deposit and Profit columns
                className: 'text-end'
            },
            {
                targets: [6], // Status column
                className: 'text-center'
            },
            {
                targets: [8], // Actions column
                className: 'text-center'
            }
        ]
    });
}
</script>
@endsection
