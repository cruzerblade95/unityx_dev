@php
$configData = Helper::appClasses();
@endphp

@extends('admin.layouts.layoutMaster')

@section('title', 'KYC Approval Management')

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
        <h4 class="card-title mb-0">KYC Approval Management</h4>
        <small class="text-muted">Pending KYC verifications requiring review</small>
      </div>

      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3" role="alert">
          {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="card-body">
        <div class="table-responsive">
          <table id="kycTable" class="table table-striped table-hover">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Submitted At</th>
                <th>Documents</th>
                <th>Status</th>
                <th width="200">Actions</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reject KYC Verification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="reject-form" method="POST">
        @csrf
        <div class="modal-body">
          <p>You are about to reject KYC verification for user: <strong id="reject-username"></strong></p>
          <div class="mb-3">
            <label for="reject-reason" class="form-label">Reason for rejection <span class="text-danger">*</span></label>
            <textarea class="form-control" id="reject-reason" name="reason" rows="3" placeholder="Please provide a clear reason for rejection..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Reject KYC</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
let kycTable;

document.addEventListener('DOMContentLoaded', function() {
    initializeDataTable();
});

function initializeDataTable() {
    kycTable = $('#kycTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.kyc.data") }}',
            type: 'GET'
        },
        columns: [
            {
                data: 'id',
                name: 'id',
                width: '50px'
            },
            {
                data: 'user_info.firstname',
                name: 'user_info',
                render: function(data, type, row) {
                    return row.user_info.firstname;
                }
            },
            {
                data: 'user_info.lastname',
                name: 'user_info',
                render: function(data, type, row) {
                    return row.user_info.lastname;
                }
            },
            {
                data: 'user_info.username',
                name: 'user_info',
                render: function(data, type, row) {
                    return row.user_info.username;
                }
            },
            {
                data: 'user_info.email',
                name: 'user_info',
                render: function(data, type, row) {
                    return row.user_info.email;
                }
            },
            {
                data: 'submitted_at',
                name: 'created_at',
                orderable: true
            },
            {
                data: 'documents',
                name: 'documents',
                orderable: false,
                searchable: false
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: false
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                width: '200px'
            }
        ],
        pageLength: 25,
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            search: "Search KYC records:",
            lengthMenu: "Show _MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ KYC records",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            },
            emptyTable: "No pending KYC verifications found",
            zeroRecords: "No matching KYC records found"
        },
        order: [[0, 'desc']] // Order by ID descending (newest first)
    });
}

function quickApprove(kycId) {
    if (confirm('Are you sure you want to approve this KYC verification?')) {
        // Create and submit a form for approval
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/kyc/' + kycId + '/approve';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function showRejectModal(kycId, username) {
    document.getElementById('reject-username').textContent = username;
    document.getElementById('reject-form').action = '/admin/kyc/' + kycId + '/reject';
    document.getElementById('reject-reason').value = '';

    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
@endsection
