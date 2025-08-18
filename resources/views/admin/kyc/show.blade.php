@extends('admin.layouts.layoutMaster')

@section('title', 'KYC Document Review')

@section('page-style')
<style>
.document-preview {
    max-width: 100%;
    max-height: 400px;
    border: 2px solid #e3e6ea;
    border-radius: 8px;
    object-fit: contain;
    cursor: pointer;
    transition: transform 0.2s;
}
.document-preview:hover {
    transform: scale(1.02);
    border-color: #696cff;
}
.document-container {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    background: #f8f9fa;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <!-- User Information Card -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">KYC Document Review</h5>
        <a href="{{ route('admin.kyc.index') }}" class="btn btn-outline-secondary btn-sm">
          <i class="icon-base ti tabler-arrow-left me-1"></i>Back to List
        </a>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6>User Information</h6>
            <table class="table table-borderless">
              <tr>
                <td><strong>Name:</strong></td>
                <td>{{ $kycVerification->user->firstname }} {{ $kycVerification->user->lastname }}</td>
              </tr>
              <tr>
                <td><strong>Username:</strong></td>
                <td>{{ $kycVerification->user->username }}</td>
              </tr>
              <tr>
                <td><strong>Email:</strong></td>
                <td>{{ $kycVerification->user->email }}</td>
              </tr>
              <tr>
                <td><strong>Joined:</strong></td>
                <td>{{ $kycVerification->user->created_at->format('M d, Y') }}</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <h6>KYC Information</h6>
            <table class="table table-borderless">
              <tr>
                <td><strong>Submitted:</strong></td>
                <td>{{ $kycVerification->created_at->format('M d, Y H:i') }}</td>
              </tr>
              <tr>
                <td><strong>Status:</strong></td>
                <td><span class="badge bg-{{ $kycVerification->status_color }}">{{ $kycVerification->status_text }}</span></td>
              </tr>
              <tr>
                <td><strong>Document Type:</strong></td>
                <td>
                  @if($kycVerification->kyc_doc3)
                    <span class="badge bg-info">Passport</span>
                  @else
                    <span class="badge bg-primary">National ID</span>
                  @endif
                </td>
              </tr>
              @if($kycVerification->kyc_reason)
              <tr>
                <td><strong>Previous Reason:</strong></td>
                <td>{{ $kycVerification->kyc_reason }}</td>
              </tr>
              @endif
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Document Images Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Submitted Documents</h5>
        <small class="text-muted">Click on images to view in full size</small>
      </div>
      <div class="card-body">
        <div class="row">
          @if(isset($documents['kyc_doc1']))
          <div class="col-md-6 mb-4">
            <h6>National ID - Front Side</h6>
            <div class="document-container">
              <img src="{{ $documents['kyc_doc1'] }}" alt="National ID Front" class="document-preview" 
                   onclick="showImageModal('{{ $documents['kyc_doc1'] }}', 'National ID - Front Side')">
            </div>
          </div>
          @endif

          @if(isset($documents['kyc_doc2']))
          <div class="col-md-6 mb-4">
            <h6>National ID - Back Side</h6>
            <div class="document-container">
              <img src="{{ $documents['kyc_doc2'] }}" alt="National ID Back" class="document-preview"
                   onclick="showImageModal('{{ $documents['kyc_doc2'] }}', 'National ID - Back Side')">
            </div>
          </div>
          @endif

          @if(isset($documents['kyc_doc3']))
          <div class="col-md-12 mb-4">
            <h6>Passport</h6>
            <div class="document-container">
              <img src="{{ $documents['kyc_doc3'] }}" alt="Passport" class="document-preview"
                   onclick="showImageModal('{{ $documents['kyc_doc3'] }}', 'Passport')">
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Action Buttons Card -->
    <div class="card">
      <div class="card-body">
        <div class="row justify-content-center">
          <div class="col-auto">
            <button type="button" class="btn btn-success me-3" onclick="approveKyc()">
              <i class="icon-base ti tabler-check me-2"></i>Approve KYC
            </button>
            <button type="button" class="btn btn-danger" onclick="showRejectModal()">
              <i class="icon-base ti tabler-x me-2"></i>Reject KYC
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="imageModalTitle">Document Image</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img id="modalImage" src="" alt="Document" style="max-width: 100%; height: auto;">
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
      <form method="POST" action="{{ route('admin.kyc.reject', $kycVerification->id) }}">
        @csrf
        <div class="modal-body">
          <p>You are about to reject KYC verification for: <strong>{{ $kycVerification->user->username }}</strong></p>
          <div class="mb-3">
            <label for="reject-reason" class="form-label">Reason for rejection <span class="text-danger">*</span></label>
            <textarea class="form-control" id="reject-reason" name="reason" rows="3" 
                     placeholder="Please provide a clear reason for rejection (e.g., blurry images, incomplete documents, etc.)" required></textarea>
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

<!-- Approve Form (Hidden) -->
<form id="approve-form" method="POST" action="{{ route('admin.kyc.approve', $kycVerification->id) }}" style="display: none;">
  @csrf
</form>

<script>
function showImageModal(imageSrc, title) {
  document.getElementById('modalImage').src = imageSrc;
  document.getElementById('imageModalTitle').textContent = title;
  
  const modal = new bootstrap.Modal(document.getElementById('imageModal'));
  modal.show();
}

function approveKyc() {
  if (confirm('Are you sure you want to approve this KYC verification?\n\nThis action will grant the user full access to verified features.')) {
    document.getElementById('approve-form').submit();
  }
}

function showRejectModal() {
  const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
  modal.show();
}
</script>

@endsection
