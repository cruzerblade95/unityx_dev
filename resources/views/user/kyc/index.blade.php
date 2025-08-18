@extends('user/layouts/layoutMaster')

@section('title', 'KYC Verification')

@section('page-style')
<style>
.document-section {
    border: 1px solid #d9dee3;
    padding: 20px;
    border-radius: 12px;
    margin-top: 15px;
    background-color: #fbfcfd;
    transition: all 0.3s ease;
}
.document-section.active {
    border-color: #696cff;
    background-color: #f8f7ff;
    box-shadow: 0 2px 6px 0 rgba(105, 108, 255, 0.1);
}
.document-section h5 {
    color: #566a7f;
    margin-bottom: 15px;
    font-weight: 600;
}
.document-section.active h5 {
    color: #696cff;
}
</style>
@endsection

@section('content')
<!-- Header -->
<div class="row">
  <div class="col-12">
    <div class="card mb-6">
      <div class="card-body">
        <h4 class="mb-1">KYC Verification</h4>
        <p class="mb-0">Verify your identity to access all features</p>
      </div>
    </div>
  </div>
</div>

<!-- KYC Status -->
@if($kycVerification)
<div class="row">
  <div class="col-12">
    <div class="card mb-6">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="badge bg-{{ $kycVerification->status_color }} me-3">
            {{ $kycVerification->status_text }}
          </div>
          @if($kycVerification->kyc_status === App\Models\KycVerification::STATUS_NOT_SUBMITTED)
            <span class="text-danger">Your KYC verification has not been submitted.</span>
          @elseif($kycVerification->kyc_status === App\Models\KycVerification::STATUS_PENDING)
            <span class="text-warning">Your KYC verification is pending review.</span>
          @elseif($kycVerification->kyc_status === App\Models\KycVerification::STATUS_APPROVED)
            <span class="text-success">Your KYC verification has been approved.</span>
          @endif
        </div>
        
        @if($kycVerification->kyc_status === App\Models\KycVerification::STATUS_NOT_SUBMITTED && $kycVerification->kyc_reason)
        <div class="mt-3">
          <div class="alert alert-danger" role="alert">
            <h6 class="alert-heading mb-2">
              <i class="ti ti-info-circle me-2"></i>Rejection Reason
            </h6>
            <p class="mb-0">{{ $kycVerification->kyc_reason }}</p>
            <hr class="my-2">
            <small class="text-muted">Please address the issues mentioned above and resubmit your documents.</small>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endif

<!-- KYC Form -->
@if(!$kycVerification || $kycVerification->kyc_status === App\Models\KycVerification::STATUS_NOT_SUBMITTED)
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Upload Verification Documents</h5>
        
        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        <form method="POST" action="{{ route('user.kyc.store') }}" enctype="multipart/form-data" onsubmit="return validateForm()">
          @csrf
          
          <div class="mb-4">
            <label class="form-label">Document Type</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="document_type" id="national_id" value="national_id" 
                     onclick="showNationalIdSection()" onchange="showNationalIdSection()" 
                     {{ old('document_type', 'national_id') == 'national_id' ? 'checked' : '' }}>
              <label class="form-check-label" for="national_id">
                National ID
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="document_type" id="passport" value="passport" 
                     onclick="showPassportSection()" onchange="showPassportSection()" 
                     {{ old('document_type') == 'passport' ? 'checked' : '' }}>
              <label class="form-check-label" for="passport">
                Passport
              </label>
            </div>
            @error('document_type')
              <div class="text-danger small">{{ $message }}</div>
            @enderror
          </div>

          <!-- National ID Upload Section -->
          <div id="national_id_section" class="document-section" style="display: block;">
            <h5>National ID Documents</h5>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-4">
                  <label for="kyc_doc1" class="form-label">National ID - Front Side <span class="text-danger">*</span></label>
                  <input type="file" class="form-control" id="kyc_doc1" name="kyc_doc1" accept="image/*" required>
                  <div class="form-text">Upload clear image of front side of your National ID</div>
                  @error('kyc_doc1')
                    <div class="text-danger small">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-4">
                  <label for="kyc_doc2" class="form-label">National ID - Back Side <span class="text-danger">*</span></label>
                  <input type="file" class="form-control" id="kyc_doc2" name="kyc_doc2" accept="image/*" required>
                  <div class="form-text">Upload clear image of back side of your National ID</div>
                  @error('kyc_doc2')
                    <div class="text-danger small">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
          </div>

          <!-- Passport Upload Section -->
          <div id="passport_section" class="document-section" style="display: none;">
            <h5>Passport Document</h5>
            <div class="mb-4">
              <label for="kyc_doc3" class="form-label">Passport <span class="text-danger">*</span></label>
              <input type="file" class="form-control" id="kyc_doc3" name="kyc_doc3" accept="image/*">
              <div class="form-text">Upload clear image of your passport</div>
              @error('kyc_doc3')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary">Submit for Verification</button>
            <a href="{{ route('user.profile') }}" class="btn btn-secondary">Cancel</a>
          </div>
        </form>

        <script>
        // Define functions immediately after the form
        function showNationalIdSection() {
            console.log('showNationalIdSection called');
            const nationalSection = document.getElementById('national_id_section');
            const passportSection = document.getElementById('passport_section');
            
            // Update required attributes
            updateRequiredFields('national_id');
            
            if (nationalSection) {
                nationalSection.style.display = 'block';
                nationalSection.classList.add('active');
                console.log('National ID section shown');
            }
            if (passportSection) {
                passportSection.style.display = 'none';
                passportSection.classList.remove('active');
            }
        }

        function showPassportSection() {
            console.log('showPassportSection called');
            const nationalSection = document.getElementById('national_id_section');
            const passportSection = document.getElementById('passport_section');
            
            // Update required attributes
            updateRequiredFields('passport');
            
            if (nationalSection) {
                nationalSection.style.display = 'none';
                nationalSection.classList.remove('active');
            }
            if (passportSection) {
                passportSection.style.display = 'block';
                passportSection.classList.add('active');
                console.log('Passport section shown');
            }
        }

        function updateRequiredFields(documentType) {
            const kycDoc1 = document.getElementById('kyc_doc1');
            const kycDoc2 = document.getElementById('kyc_doc2');
            const kycDoc3 = document.getElementById('kyc_doc3');

            if (documentType === 'national_id') {
                // Make national ID fields required
                if (kycDoc1) kycDoc1.required = true;
                if (kycDoc2) kycDoc2.required = true;
                if (kycDoc3) kycDoc3.required = false;
            } else if (documentType === 'passport') {
                // Make passport field required
                if (kycDoc1) kycDoc1.required = false;
                if (kycDoc2) kycDoc2.required = false;
                if (kycDoc3) kycDoc3.required = true;
            }
        }

        function validateForm() {
            const nationalIdRadio = document.getElementById('national_id');
            const passportRadio = document.getElementById('passport');
            const kycDoc1 = document.getElementById('kyc_doc1');
            const kycDoc2 = document.getElementById('kyc_doc2');
            const kycDoc3 = document.getElementById('kyc_doc3');

            // Check if a document type is selected
            if (!nationalIdRadio.checked && !passportRadio.checked) {
                alert('Please select a document type (National ID or Passport).');
                return false;
            }

            // Validate based on selected document type
            if (nationalIdRadio.checked) {
                if (!kycDoc1.files.length) {
                    alert('Please upload the front side of your National ID.');
                    kycDoc1.focus();
                    return false;
                }
                if (!kycDoc2.files.length) {
                    alert('Please upload the back side of your National ID.');
                    kycDoc2.focus();
                    return false;
                }
            } else if (passportRadio.checked) {
                if (!kycDoc3.files.length) {
                    alert('Please upload your Passport image.');
                    kycDoc3.focus();
                    return false;
                }
            }

            return true;
        }
        </script>
      </div>
    </div>
  </div>
</div>
@endif

@endsection

<script>
// Additional event listeners for robustness - functions are defined above
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up KYC form');
    const nationalIdRadio = document.getElementById('national_id');
    const passportRadio = document.getElementById('passport');

    if (nationalIdRadio) {
        nationalIdRadio.addEventListener('change', function() {
            if (this.checked) {
                showNationalIdSection();
            }
        });
    }
    
    if (passportRadio) {
        passportRadio.addEventListener('change', function() {
            if (this.checked) {
                showPassportSection();
            }
        });
    }
    
    // Initialize - show the correct section based on checked radio
    if (nationalIdRadio && nationalIdRadio.checked) {
        showNationalIdSection();
    } else if (passportRadio && passportRadio.checked) {
        showPassportSection();
    } else {
        // Default to national ID and set required fields
        if (nationalIdRadio) {
            nationalIdRadio.checked = true;
            showNationalIdSection();
        }
    }
});
</script>
