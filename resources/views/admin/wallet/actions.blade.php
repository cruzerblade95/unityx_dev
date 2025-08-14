<button class="btn btn-sm btn-primary update-wallet"
        data-id="{{ $user->id }}"
        data-name="{{ $user->name ?? $user->username }}"
        data-username="{{ $user->username }}"
        data-photo="{{ $user->profile_photo_url ?? '/assets/img/avatars/1.png' }}"
        data-bs-toggle="offcanvas"
        data-bs-target="#offcanvasUpdateWallet">
    <i class="fas fa-wallet me-1"></i>
    Update
</button>
