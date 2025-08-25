@if(isset($user->wallet))
  <div class="dropdown">
    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
      <i class="mdi mdi-dots-vertical"></i>
    </button>
    <div class="dropdown-menu">
      <a class="dropdown-item" href="javascript:void(0);">
        <i class="mdi mdi-eye-outline me-1"></i> View Wallet
      </a>
      <a class="dropdown-item" href="javascript:void(0);">
        <i class="mdi mdi-account-outline me-1"></i> View User
      </a>
    </div>
  </div>
@else
  <button type="button" class="btn btn-sm btn-primary create-wallet"
          data-user-id="{{ $user->id }}"
          data-user-name="{{ $user->name }}">
    <i class="fas fa-plus"></i> Create Wallet
  </button>
@endif
