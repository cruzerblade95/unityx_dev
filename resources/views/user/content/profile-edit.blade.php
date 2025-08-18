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
<div class="row">
  <div class="col-md-12">
    <div class="nav-align-top">
      <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-md-0 gap-2">
        <li class="nav-item">
          <a class="nav-link active" href="javascript:void(0);"><i class="icon-base ti tabler-users icon-sm me-1_5"></i> Profile Edit</a>
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
    <div class="card mb-6">
      <!-- Account -->
      <div class="card-body">
        <div class="d-flex align-items-start align-items-sm-center gap-6">
          <img src="{{ asset('assets/img/avatars/1.png') }}" alt="user-avatar" class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar" />
          <div class="button-wrapper">
            <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
              <span class="d-none d-sm-block">Upload new photo</span>
              <i class="icon-base ti tabler-upload d-block d-sm-none"></i>
              <input type="file" id="upload" class="account-file-input" hidden accept="image/png, image/jpeg" />
            </label>
            <button type="button" class="btn btn-label-secondary account-image-reset mb-4">
              <i class="icon-base ti tabler-reset d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Reset</span>
            </button>

            <div>Allowed JPG, GIF or PNG. Max size of 2MB</div>
          </div>
        </div>
      </div>
      <div class="card-body pt-4">
        <form id="formAccountSettings" method="POST" action="{{ route('user.profile.update') }}">
          @csrf
          @method('PUT')
          <div class="row gy-4 gx-6 mb-6">
            <div class="col-md-6 form-control-validation">
              <label for="firstName" class="form-label">First Name</label>
              <input class="form-control" type="text" id="firstName" name="firstName" value="{{ auth()->user()->firstname ?? '' }}" autofocus />
            </div>
            <div class="col-md-6 form-control-validation">
              <label for="lastName" class="form-label">Last Name</label>
              <input class="form-control" type="text" name="lastName" id="lastName" value="{{ auth()->user()->lastname ?? '' }}" />
            </div>
            <div class="col-md-6 form-control-validation">
              <label for="username" class="form-label">Username</label>
              <input class="form-control" type="text" name="username" id="username" value="{{ auth()->user()->username ?? '' }}" />
            </div>
            <div class="col-md-6">
              <label for="email" class="form-label">E-mail</label>
              <input class="form-control" type="text" id="email" name="email" value="{{ auth()->user()->email ?? '' }}" placeholder="john.doe@example.com" />
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
              <input type="text" class="form-control" id="address" name="address" value="{{ auth()->user()->address ?? '' }}" placeholder="Address" />
            </div>
            <div class="col-md-6">
              <label for="state" class="form-label">State</label>
              <input class="form-control" type="text" id="state" name="state" value="{{ auth()->user()->state ?? '' }}" placeholder="State" />
            </div>
            <div class="col-md-6">
              <label for="zipCode" class="form-label">Zip Code</label>
              <input type="text" class="form-control" id="zipCode" name="zipCode" value="{{ auth()->user()->zipCode ?? '' }}" placeholder="12345" maxlength="6" />
            </div>
            <div class="col-md-6">
              <label class="form-label" for="country">Country</label>
              <select id="country" class="select2 form-select" name="country">
                <option value="">Select</option>
                <option value="Malaysia" {{ (auth()->user()->country ?? '') == 'Malaysia' ? 'selected' : '' }}>Malaysia</option>
                {{-- <option value="Bangladesh">Bangladesh</option>
                <option value="Belarus">Belarus</option>
                <option value="Brazil">Brazil</option>
                <option value="Canada">Canada</option>
                <option value="China">China</option>
                <option value="France">France</option>
                <option value="Germany">Germany</option>
                <option value="India">India</option>
                <option value="Indonesia">Indonesia</option>
                <option value="Israel">Israel</option>
                <option value="Italy">Italy</option>
                <option value="Japan">Japan</option>
                <option value="Korea">Korea, Republic of</option>
                <option value="Mexico">Mexico</option>
                <option value="Philippines">Philippines</option>
                <option value="Russia">Russian Federation</option>
                <option value="South Africa">South Africa</option>
                <option value="Thailand">Thailand</option>
                <option value="Turkey">Turkey</option>
                <option value="Ukraine">Ukraine</option>
                <option value="United Arab Emirates">United Arab Emirates</option>
                <option value="United Kingdom">United Kingdom</option>
                <option value="United States">United States</option> --}}
              </select>
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
            <button type="reset" class="btn btn-label-secondary">Cancel</button>
          </div>
        </form>
      </div>
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
            <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" />
            <label class="form-check-label" for="accountActivation">I confirm my account deactivation</label>
          </div>
          <button type="submit" class="btn btn-danger deactivate-account" disabled>Deactivate Account</button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection