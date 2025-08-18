@extends('admin.layouts.layoutMaster')

@section('title', 'KYC Approval Management')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/tables-datatables-basic.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">KYC Approval Management</h5>
        <small class="text-muted float-end">Pending KYC verifications requiring review</small>
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
        @if($pendingKycs->count() > 0)
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>User</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Submitted At</th>
                  <th>Documents</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($pendingKycs as $kyc)
                <tr>
                  <td>{{ $kyc->id }}</td>
                  <td>{{ $kyc->user->firstname }} {{ $kyc->user->lastname }}</td>
                  <td>{{ $kyc->user->username }}</td>
                  <td>{{ $kyc->user->email }}</td>
                  <td>{{ $kyc->created_at->format('M d, Y H:i') }}</td>
                  <td>
                    <div class="d-flex gap-1">
                      @if($kyc->kyc_doc1)
                        <span class="badge bg-primary">ID Front</span>
                      @endif
                      @if($kyc->kyc_doc2)
                        <span class="badge bg-primary">ID Back</span>
                      @endif
                      @if($kyc->kyc_doc3)
                        <span class="badge bg-info">Passport</span>
                      @endif
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-warning">{{ $kyc->status_text }}</span>
                  </td>
                  <td>
                    <div class="d-flex gap-2">
                      <a href="{{ route('admin.kyc.show', $kyc->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="icon-base ti tabler-eye me-1"></i>Review
                      </a>
                      
                      <!-- Quick Approve Button -->
                      <button type="button" class="btn btn-sm btn-success" onclick="quickApprove({{ $kyc->id }})">
                        <i class="icon-base ti tabler-check me-1"></i>Approve
                      </button>
                      
                      <!-- Quick Reject Button -->
                      <button type="button" class="btn btn-sm btn-danger" onclick="showRejectModal({{ $kyc->id }}, '{{ $kyc->user->username }}')">
                        <i class="icon-base ti tabler-x me-1"></i>Reject
                      </button>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div class="d-flex justify-content-center">
            {{ $pendingKycs->links() }}
          </div>
        @else
          <div class="text-center py-5">
            <i class="icon-base ti tabler-file-check display-4 text-muted"></i>
            <h4 class="mt-3">No Pending KYC Verifications</h4>
            <p class="text-muted">All KYC verifications have been processed.</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Quick Approve Forms (Hidden) -->
@foreach($pendingKycs as $kyc)
<form id="approve-form-{{ $kyc->id }}" method="POST" action="{{ route('admin.kyc.approve', $kyc->id) }}" style="display: none;">
  @csrf
</form>
@endforeach

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

<script>
function quickApprove(kycId) {
  if (confirm('Are you sure you want to approve this KYC verification?')) {
    document.getElementById('approve-form-' + kycId).submit();
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
