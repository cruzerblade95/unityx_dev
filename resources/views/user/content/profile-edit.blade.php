@extends('user/layouts/layoutMaster')

@section('title', 'Account settings - Account')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleave-zen/cleave-zen.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    {{-- @vite(['resources/assets/js/pages-account-settings-account.js']) --}}
@endsection

@section('content')
    @php
        $user = $user ?? auth()->user();
    @endphp

    <div class="row">
        <div class="col-md-12">
            <div class="nav-align-top">
                <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-md-0 gap-2">
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0);"><i
                                class="icon-base ti tabler-users icon-sm me-1_5"></i> Profile Edit</a>
                    </li>
                    {{-- <li class="nav-item">
          <a class="nav-link" href="{{ url('pages/account-settings-security') }}"><i class="icon-base ti tabler-lock icon-sm me-1_5"></i> Security</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('pages/account-settings-billing') }}"><i class="icon-base ti tabler-bookmark icon-sm me-1_5"></i> Billing & Plans</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('pages/account-settings-notifications') }}"><i class="icon-base ti tabler-bell icon-sm me-1_5"></i> Notifications</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('pages/account-settings-connections') }}"><i class="icon-base ti tabler-link icon-sm me-1_5"></i> Connections</a>
        </li> --}}
                </ul>
            </div>

            @if (session('profile_updated'))
                <div class="alert alert-success alert-dismissible d-flex align-items-baseline" role="alert">
                    <span class="alert-icon alert-icon-lg text-success me-2">
                        <i class="icon-base ti tabler-check icon-sm"></i>
                    </span>
                    <div class="d-flex flex-column ps-1">
                        <h5 class="alert-heading mb-2">Profile Updated!</h5>
                        <p class="mb-0">Your profile information has been successfully updated.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        </button>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible d-flex align-items-baseline" role="alert">
                    <span class="alert-icon alert-icon-lg text-danger me-2">
                        <i class="icon-base ti tabler-alert-circle icon-sm"></i>
                    </span>
                    <div class="d-flex flex-column ps-1">
                        <h5 class="alert-heading mb-2">Validation Errors</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        </button>
                    </div>
                </div>
            @endif

            <div class="card mb-6">
                <!-- Account -->
                <form id="formAccountSettings" method="POST" action="{{ route('user.profile.update') }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-6">
                            <img src="{{ isset($user) && $user->profile_photo_path ? asset($user->profile_photo_path) : asset('assets/img/avatars/1.png') }}"
                                alt="user-avatar" class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar" />
                            <div class="button-wrapper">
                                <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block">Upload new photo</span>
                                    <i class="icon-base ti tabler-upload d-block d-sm-none"></i>
                                    <input type="file" id="upload" name="profile_photo" class="account-file-input"
                                        hidden accept="image/png, image/jpeg, image/jpg, image/gif" />
                                </label>
                                <button type="button" class="btn btn-label-secondary account-image-reset mb-4">
                                    <i class="icon-base ti tabler-reset d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">Reset</span>
                                </button>

                                <div>Allowed JPG, GIF or PNG. Max size of 2MB</div>
                                @error('profile_photo')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row gy-4 gx-6 mb-6">
                            <div class="col-md-6 form-control-validation">
                                <label for="firstname" class="form-label">First Name</label>
                                <input class="form-control @error('firstname') is-invalid @enderror" type="text"
                                    id="firstname" name="firstname" value="{{ old('firstname', $user->firstname ?? '') }}"
                                    autofocus />
                                @error('firstname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-control-validation">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input class="form-control @error('lastname') is-invalid @enderror" type="text"
                                    name="lastname" id="lastname" value="{{ old('lastname', $user->lastname ?? '') }}" />
                                @error('lastname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-control-validation">
                                <label for="username" class="form-label">Username</label>
                                <input class="form-control @error('username') is-invalid @enderror" type="text"
                                    name="username" id="username" value="{{ old('username', $user->username ?? '') }}" />
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">E-mail</label>
                                <input class="form-control @error('email') is-invalid @enderror" type="text"
                                    id="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                                    placeholder="john.doe@example.com" />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- <div class="col-md-6">
              <label for="organization" class="form-label">Organization</label>
              <input type="text" class="form-control" id="organization" name="organization" value="{{ auth()->user()->organization ?? '' }}" />
            </div> --}}
                            {{-- <div class=" col-md-6">
              <label class="form-label" for="phoneNumber">Phone Number</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text">US (+1)</span>
                <input type="text" id="phoneNumber" name="phoneNumber" class="form-control" value="{{ auth()->user()->phone ?? '' }}" placeholder="202 555 0111" />
              </div>
            </div> --}}
                            <div class="col-md-6">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror"
                                    id="address" name="address" value="{{ old('address', $user->address ?? '') }}"
                                    placeholder="Address" />
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="state" class="form-label">State</label>
                                <input class="form-control @error('state') is-invalid @enderror" type="text"
                                    id="state" name="state" value="{{ old('state', $user->state ?? '') }}"
                                    placeholder="State" />
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="zipCode" class="form-label">Zip Code</label>
                                <input type="text" class="form-control @error('zipCode') is-invalid @enderror"
                                    id="zipCode" name="zipCode" value="{{ old('zipCode', $user->zipCode ?? '') }}"
                                    placeholder="12345" maxlength="6" />
                                @error('zipCode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="country">Country</label>
                                <select id="country" class="select2 form-select @error('country') is-invalid @enderror"
                                    name="country">
                                    <option value="">Select</option>
                                    <option value="Malaysia"
                                        {{ old('country', $user->country ?? '') == 'Malaysia' ? 'selected' : '' }}>Malaysia
                                    </option>
                                    <option value="Singapore"
                                        {{ old('country', $user->country ?? '') == 'Singapore' ? 'selected' : '' }}>
                                        Singapore</option>
                                    <option value="Indonesia"
                                        {{ old('country', $user->country ?? '') == 'Indonesia' ? 'selected' : '' }}>
                                        Indonesia</option>
                                    <option value="Thailand"
                                        {{ old('country', $user->country ?? '') == 'Thailand' ? 'selected' : '' }}>Thailand
                                    </option>
                                    <option value="Philippines"
                                        {{ old('country', $user->country ?? '') == 'Philippines' ? 'selected' : '' }}>
                                        Philippines</option>
                                    <option value="Vietnam"
                                        {{ old('country', $user->country ?? '') == 'Vietnam' ? 'selected' : '' }}>Vietnam
                                    </option>
                                    <option value="United States"
                                        {{ old('country', $user->country ?? '') == 'United States' ? 'selected' : '' }}>
                                        United States</option>
                                    <option value="United Kingdom"
                                        {{ old('country', $user->country ?? '') == 'United Kingdom' ? 'selected' : '' }}>
                                        United Kingdom</option>
                                    <option value="Australia"
                                        {{ old('country', $user->country ?? '') == 'Australia' ? 'selected' : '' }}>
                                        Australia</option>
                                    <option value="Canada"
                                        {{ old('country', $user->country ?? '') == 'Canada' ? 'selected' : '' }}>Canada
                                    </option>
                                    <option value="Other"
                                        {{ old('country', $user->country ?? '') == 'Other' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- <div class="col-md-6">
              <label for="language" class="form-label">Language</label>
              <select id="language" class="select2 form-select" name="language">
                <option value="">Select Language</option>
                <option value="en" {{ (auth()->user()->language ?? '') == 'en' ? 'selected' : '' }}>English</option>
                <option value="fr" {{ (auth()->user()->language ?? '') == 'fr' ? 'selected' : '' }}>French</option>
                <option value="de" {{ (auth()->user()->language ?? '') == 'de' ? 'selected' : '' }}>German</option>
                <option value="pt" {{ (auth()->user()->language ?? '') == 'pt' ? 'selected' : '' }}>Portuguese</option>
              </select>
            </div> --}}
                            {{-- <div class="col-md-6">
              <label for="timeZones" class="form-label">Timezone</label>
              <select id="timeZones" class="select2 form-select" name="timezone">
                <option value="">Select Timezone</option>
                <option value="-12" {{ (auth()->user()->timezone ?? '') == '-12' ? 'selected' : '' }}>(GMT-12:00) International Date Line West</option>
                <option value="-11" {{ (auth()->user()->timezone ?? '') == '-11' ? 'selected' : '' }}>(GMT-11:00) Midway Island, Samoa</option>
                <option value="-10" {{ (auth()->user()->timezone ?? '') == '-10' ? 'selected' : '' }}>(GMT-10:00) Hawaii</option>
                <option value="-9" {{ (auth()->user()->timezone ?? '') == '-9' ? 'selected' : '' }}>(GMT-09:00) Alaska</option>
                <option value="-8" {{ (auth()->user()->timezone ?? '') == '-8' ? 'selected' : '' }}>(GMT-08:00) Pacific Time (US & Canada)</option>
                <option value="-8" {{ (auth()->user()->timezone ?? '') == '-8' ? 'selected' : '' }}>(GMT-08:00) Tijuana, Baja California</option>
                <option value="-7" {{ (auth()->user()->timezone ?? '') == '-7' ? 'selected' : '' }}>(GMT-07:00) Arizona</option>
                <option value="-7" {{ (auth()->user()->timezone ?? '') == '-7' ? 'selected' : '' }}>(GMT-07:00) Chihuahua, La Paz, Mazatlan</option>
                <option value="-7" {{ (auth()->user()->timezone ?? '') == '-7' ? 'selected' : '' }}>(GMT-07:00) Mountain Time (US & Canada)</option>
                <option value="-6" {{ (auth()->user()->timezone ?? '') == '-6' ? 'selected' : '' }}>(GMT-06:00) Central America</option>
                <option value="-6" {{ (auth()->user()->timezone ?? '') == '-6' ? 'selected' : '' }}>(GMT-06:00) Central Time (US & Canada)</option>
                <option value="-6" {{ (auth()->user()->timezone ?? '') == '-6' ? 'selected' : '' }}>(GMT-06:00) Guadalajara, Mexico City, Monterrey</option>
                <option value="-6" {{ (auth()->user()->timezone ?? '') == '-6' ? 'selected' : '' }}>(GMT-06:00) Saskatchewan</option>
                <option value="-5" {{ (auth()->user()->timezone ?? '') == '-5' ? 'selected' : '' }}>(GMT-05:00) Bogota, Lima, Quito, Rio Branco</option>
                <option value="-5" {{ (auth()->user()->timezone ?? '') == '-5' ? 'selected' : '' }}>(GMT-05:00) Eastern Time (US & Canada)</option>
                <option value="-5" {{ (auth()->user()->timezone ?? '') == '-5' ? 'selected' : '' }}>(GMT-05:00) Indiana (East)</option>
                <option value="-4" {{ (auth()->user()->timezone ?? '') == '-4' ? 'selected' : '' }}>(GMT-04:00) Atlantic Time (Canada)</option>
                <option value="-4" {{ (auth()->user()->timezone ?? '') == '-4' ? 'selected' : '' }}>(GMT-04:00) Caracas, La Paz</option>
              </select>
            </div> --}}
                            {{-- <div class="col-md-6">
              <label for="currency" class="form-label">Currency</label>
              <select id="currency" class="select2 form-select" name="currency">
                <option value="">Select Currency</option>
                <option value="usd" {{ (auth()->user()->currency ?? '') == 'usd' ? 'selected' : '' }}>USD</option>
                <option value="euro" {{ (auth()->user()->currency ?? '') == 'euro' ? 'selected' : '' }}>Euro</option>
                <option value="pound" {{ (auth()->user()->currency ?? '') == 'pound' ? 'selected' : '' }}>Pound</option>
                <option value="bitcoin" {{ (auth()->user()->currency ?? '') == 'bitcoin' ? 'selected' : '' }}>Bitcoin</option>
              </select>
            </div> --}}
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-3">Save changes</button>
                            <button type="button" class="btn btn-label-secondary" id="cancelBtn">Cancel</button>
                        </div>
                    </div>
                </form>
                <!-- /Account -->
            </div>
            <div class="card">
                <h5 class="card-header">Delete Account</h5>
                <div class="card-body">
                    <div class="mb-6 col-12 mb-0">
                        <div class="alert alert-warning">
                            <h5 class="alert-heading mb-1">Are you sure you want to delete your account?</h5>
                            <p class="mb-0">Once you delete your account, there is no going back. Please be certain.</p>
                        </div>
                    </div>
                    <form id="formAccountDeactivation" onsubmit="return false">
                        <div class="form-check my-8">
                            <input class="form-check-input" type="checkbox" name="accountActivation"
                                id="accountActivation" />
                            <label class="form-check-label" for="accountActivation">I confirm my account
                                deactivation</label>
                        </div>
                        <button type="submit" class="btn btn-danger deactivate-account" disabled>Deactivate
                            Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formAccountSettings');
            const cancelBtn = document.getElementById('cancelBtn');
            const uploadInput = document.getElementById('upload');
            const uploadedAvatar = document.getElementById('uploadedAvatar');
            const resetBtn = document.querySelector('.account-image-reset');

            // Store initial values
            const defaultAvatarSrc = uploadedAvatar.src;
            let hasChanges = false;

            // Store initial form data
            const initialFormData = {};
            const formInputs = form.querySelectorAll('input[type="text"], input[type="email"], select, textarea');
            formInputs.forEach(input => {
                initialFormData[input.name] = input.value;
            });

            // Handle file upload preview
            uploadInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (2MB = 2097152 bytes)
                    if (file.size > 2097152) {
                        Swal.fire({
                            title: 'File Too Large',
                            text: 'Please select a file smaller than 2MB',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });
                        uploadInput.value = '';
                        return;
                    }

                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        Swal.fire({
                            title: 'Invalid File Type',
                            text: 'Please select a JPG, PNG, or GIF image',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });
                        uploadInput.value = '';
                        return;
                    }

                    // Preview the image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        uploadedAvatar.src = e.target.result;
                        checkForChanges();
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Handle reset button
            resetBtn.addEventListener('click', function() {
                uploadedAvatar.src = defaultAvatarSrc;
                uploadInput.value = '';
                checkForChanges();
            });

            // Function to check if form has changes
            function checkForChanges() {
                hasChanges = false;

                // Check text inputs, email inputs, selects, and textareas
                formInputs.forEach(input => {
                    if (initialFormData[input.name] !== input.value) {
                        hasChanges = true;
                    }
                });

                // Check if photo was changed
                if (uploadInput.files.length > 0) {
                    hasChanges = true;
                }
            }

            // Add event listeners to form inputs
            formInputs.forEach(input => {
                input.addEventListener('input', checkForChanges);
                input.addEventListener('change', checkForChanges);
            });

            // Handle form submission
            form.addEventListener('submit', function(e) {
                // Add loading state to submit button
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
                submitBtn.disabled = true;

                // Allow form to submit normally, but provide user feedback
            });

            // Handle cancel button click
            cancelBtn.addEventListener('click', function() {
                checkForChanges();

                if (hasChanges) {
                    Swal.fire({
                        title: 'Unsaved Changes',
                        text: 'You have unsaved changes. Are you sure you want to leave?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, leave',
                        cancelButtonText: 'Stay',
                        customClass: {
                            confirmButton: 'btn btn-danger me-3',
                            cancelButton: 'btn btn-label-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route('user.profile') }}';
                        }
                    });
                } else {
                    // No changes, redirect immediately
                    window.location.href = '{{ route('user.profile') }}';
                }
            });

            // Initialize Select2 if available
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#country').select2({
                    placeholder: "Select Country",
                    allowClear: true
                });
            }

            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert-dismissible');
                alerts.forEach(alert => {
                    const closeBtn = alert.querySelector('.btn-close');
                    if (closeBtn) {
                        closeBtn.click();
                    }
                });
            }, 5000);
        });
    </script>

@endsection
