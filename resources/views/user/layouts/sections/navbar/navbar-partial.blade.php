@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
@endphp

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
    <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4 ms-0">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-logo demo"><img src="{{ asset('assets/img/icons/logo-new-unityx.png') }}"
                    alt="UnityX Logo" width="200"></span>
            <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
        </a>

        <!-- Display menu close icon only for horizontal-menu with navbar-full -->
        @if (isset($menuHorizontal))
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                <i class="icon-base ti tabler-x icon-sm d-flex align-items-center justify-content-center"></i>
            </a>
        @endif
    </div>
@endif

<!-- ! Not required for layout-without-menu -->
@if (!isset($navbarHideToggle))
    <div
        class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base ti tabler-menu-2 icon-md"></i>
        </a>
    </div>
@endif

<div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">

    <ul class="navbar-nav flex-row align-items-center ms-md-auto">
        <!-- Language -->
        <li class="nav-item dropdown-language dropdown">
            <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class="icon-base ti tabler-language icon-22px text-heading"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                        href="{{ url('lang/en') }}" data-language="en" data-text-direction="ltr">
                        <span>English</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ app()->getLocale() === 'fr' ? 'active' : '' }}"
                        href="{{ url('lang/fr') }}" data-language="fr" data-text-direction="ltr">
                        <span>French</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ app()->getLocale() === 'ar' ? 'active' : '' }}"
                        href="{{ url('lang/ar') }}" data-language="ar" data-text-direction="rtl">
                        <span>Arabic</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ app()->getLocale() === 'de' ? 'active' : '' }}"
                        href="{{ url('lang/de') }}" data-language="de" data-text-direction="ltr">
                        <span>German</span>
                    </a>
                </li>
            </ul>
        </li>
        <!--/ Language -->

        @if ($configData['hasCustomizer'] == true)
            <!-- Style Switcher -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                    id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class="icon-base ti tabler-sun icon-22px theme-icon-active text-heading"></i>
                    <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                    <li>
                        <button type="button" class="dropdown-item align-items-center active"
                            data-bs-theme-value="light" aria-pressed="false">
                            <span><i class="icon-base ti tabler-sun icon-22px me-3" data-icon="sun"></i>Light</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark"
                            aria-pressed="true">
                            <span><i class="icon-base ti tabler-moon-stars icon-22px me-3"
                                    data-icon="moon-stars"></i>Dark</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system"
                            aria-pressed="false">
                            <span><i class="icon-base ti tabler-device-desktop-analytics icon-22px me-3"
                                    data-icon="device-desktop-analytics"></i>System</span>
                        </button>
                    </li>
                </ul>
            </li>
            <!-- / Style Switcher-->
        @endif

        <!-- Quick links  -->
        <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown">
            <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <i class="icon-base ti tabler-layout-grid-add icon-22px text-heading"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end p-0">
                <div class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-3">
                        <h6 class="mb-0 me-auto">Shortcuts</h6>
                        <a href="javascript:void(0)"
                            class="dropdown-shortcuts-add py-2 btn btn-text-secondary rounded-pill btn-icon"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Add shortcuts"><i
                                class="icon-base ti tabler-plus icon-20px text-heading"></i></a>
                    </div>
                </div>
                <div class="dropdown-shortcuts-list scrollable-container">
                    <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ti tabler-wallet icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('user.deposit.index') }}" class="stretched-link">Deposit</a>
                            <small>Add Funds</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ti tabler-cash icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('user.withdrawal.index') }}" class="stretched-link">Withdrawal</a>
                            <small>Withdraw Funds</small>
                        </div>
                    </div>
                    <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ti tabler-chart-line icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('user.investment.index') }}" class="stretched-link">Investment</a>
                            <small>Your Investments</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ti tabler-robot icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('user.ai_trades.index') }}" class="stretched-link">AI Trades</a>
                            <small>Trading Bot</small>
                        </div>
                    </div>
                    <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ti tabler-device-desktop-analytics icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('user.dashboard') }}" class="stretched-link">Dashboard</a>
                            <small>User Dashboard</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ti tabler-settings icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('user.profile') }}" class="stretched-link">Profile</a>
                            <small>Account Settings</small>
                        </div>
                    </div>
                    <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ti tabler-shield-check icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('user.kyc.index') }}" class="stretched-link">KYC</a>
                            <small>Verification</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                <i class="icon-base ti tabler-store icon-26px text-heading"></i>
                            </span>
                            <a href="{{ route('user.marketplace.index') }}" class="stretched-link">Marketplace</a>
                            <small>Buy & Sell</small>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <!-- Quick links -->

        <!-- Notification -->
        <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
            <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                aria-expanded="false" id="notificationDropdown">
                <span class="position-relative">
                    <i class="icon-base ti tabler-bell icon-22px text-heading"></i>
                    <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"
                        id="notificationBadge" style="display: none;"></span>
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width: 350px;">
                <li class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-3">
                        <h6 class="mb-0 me-auto">Notifications</h6>
                        <div class="d-flex align-items-center h6 mb-0">
                            <span class="badge bg-label-primary me-2" id="notificationCount">0 New</span>
                            <a href="javascript:void(0)" class="dropdown-notifications-all p-2 btn btn-icon"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read"
                                id="markAllRead"><i class="icon-base ti tabler-mail-opened text-heading"></i></a>
                        </div>
                    </div>
                </li>
                <li class="dropdown-notifications-list scrollable-container" style="max-height: 400px;">
                    <ul class="list-group list-group-flush" id="notificationsList">
                        <li class="list-group-item text-center py-4">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Loading notifications...</div>
                        </li>
                    </ul>
                    <!-- Empty state for when no notifications exist -->
                    <div id="notificationEmpty" class="text-center py-4" style="display: none;">
                        <div class="avatar avatar-sm mx-auto mb-2">
                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                <i class="bx bx-bell-off"></i>
                            </span>
                        </div>
                        <p class="mb-0 text-muted">No notifications yet</p>
                    </div>
                </li>
                <li class="border-top">
                    <div class="d-grid p-4">
                        <a class="btn btn-primary btn-sm d-flex" href="{{ route('user.notifications.index') }}">
                            <small class="align-middle">View all notifications</small>
                        </a>
                    </div>
                </li>
            </ul>
        </li>
        <!--/ Notification -->

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Notification functionality
                let notificationCount = 0;
                let unreadNotifications = [];

                // Function to load notifications
                function loadNotifications() {
                    fetch('/user/notifications/api')
                        .then(response => response.json())
                        .then(data => {
                            updateNotificationUI(data.notifications, data.unread_count);
                        })
                        .catch(error => {
                            console.error('Error loading notifications:', error);
                        });
                }

                // Function to update notification UI
                function updateNotificationUI(notifications, unreadCount) {
                    const badge = document.getElementById('notificationBadge');
                    const countSpan = document.getElementById('notificationCount');
                    const dropdown = document.getElementById('notificationsList');
                    const emptyState = document.getElementById('notificationEmpty');

                    // Check if elements exist before manipulating them
                    if (!badge || !countSpan || !dropdown || !emptyState) {
                        console.error('Notification UI elements not found');
                        return;
                    }

                    // Update badge
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                        badge.style.display = 'block';
                        countSpan.textContent = unreadCount + ' New';
                    } else {
                        badge.style.display = 'none';
                        countSpan.textContent = '0 New';
                    }

                    // Clear existing notifications
                    dropdown.innerHTML = '';

                    if (notifications.length === 0) {
                        emptyState.style.display = 'block';
                        dropdown.style.display = 'none';
                    } else {
                        emptyState.style.display = 'none';
                        dropdown.style.display = 'block';

                        notifications.forEach(notification => {
                            const notificationItem = createNotificationItem(notification);
                            dropdown.appendChild(notificationItem);
                        });
                    }
                }

                // Function to create notification item HTML
                function createNotificationItem(notification) {
                    const li = document.createElement('li');
                    li.className = 'list-group-item list-group-item-action dropdown-notifications-item' +
                        (notification.read_at ? '' : ' dropdown-notifications-item-unread');

                    const iconClass = getNotificationIcon(notification.type);
                    const timeAgo = formatTimeAgo(notification.created_at);

                    li.innerHTML = `
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                                <i class="${iconClass}"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${notification.title}</h6>
                            <p class="mb-0">${notification.message}</p>
                            <small class="text-muted">${timeAgo}</small>
                        </div>
                        ${!notification.read_at ? `<div class="flex-shrink-0 dropdown-notifications-actions"><a href="javascript:void(0)" class="dropdown-notifications-read" onclick="markAsRead(${notification.id})"><span class="badge badge-dot"></span></a></div>` : ''}
                    </div>
                `;

                    return li;
                }

                // Function to get notification icon based on type
                function getNotificationIcon(type) {
                    const icons = {
                        'kyc_verified': 'bx bx-check-circle text-success',
                        'kyc_rejected': 'bx bx-x-circle text-danger',
                        'withdrawal_success': 'bx bx-money text-success',
                        'withdrawal_failed': 'bx bx-x-circle text-danger',
                        'deposit_success': 'bx bx-plus-circle text-success',
                        'deposit_failed': 'bx bx-x-circle text-danger',
                        'marketplace_verification_pending': 'bx bx-time text-warning',
                        'marketplace_listing_approved': 'bx bx-check-circle text-success',
                        'marketplace_listing_rejected': 'bx bx-x-circle text-danger',
                        'marketplace_topup_success': 'bx bx-wallet text-success'
                    };

                    return icons[type] || 'bx bx-bell text-primary';
                }

                // Function to format time ago
                function formatTimeAgo(datetime) {
                    const now = new Date();
                    const past = new Date(datetime);
                    const diffInSeconds = Math.floor((now - past) / 1000);

                    if (diffInSeconds < 60) return 'Just now';
                    if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' minutes ago';
                    if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hours ago';
                    if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + ' days ago';

                    return past.toLocaleDateString();
                }

                // Function to mark notification as read
                window.markAsRead = function(notificationId) {
                    fetch(`/user/notifications/${notificationId}/read`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                loadNotifications(); // Reload notifications
                            }
                        })
                        .catch(error => {
                            console.error('Error marking notification as read:', error);
                        });
                };

                // Function to mark all notifications as read
                window.markAllAsRead = function() {
                    fetch('/user/notifications/read-all', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                loadNotifications(); // Reload notifications
                            }
                        })
                        .catch(error => {
                            console.error('Error marking all notifications as read:', error);
                        });
                };

                // Load notifications on page load
                loadNotifications();

                // Reload notifications every 30 seconds
                setInterval(loadNotifications, 30000);
            });
        </script>
        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    <img src="{{ auth()->user()->profile_photo_path ? asset(auth()->user()->profile_photo_path) : asset('assets/img/icons/unityx.png') }}"
                        alt class="rounded-circle" />
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item mt-0"
                        href="{{ Route::has('user.profile') ? route('user.profile') : url('pages/profile-user') }}">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-2">
                                <div class="avatar avatar-online">
                                    <img src="{{ auth()->user()->profile_photo_path ? asset(auth()->user()->profile_photo_path) : asset('assets/img/icons/unityx.png') }}"
                                        alt class="rounded-circle" />
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">
                                    @if (Auth::check())
                                        {{ Auth::user()->username }}
                                    @else
                                        John Doe
                                    @endif
                                </h6>
                                <small class="text-body-secondary">User</small>
                            </div>
                        </div>
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider my-1 mx-n2"></div>
                </li>
                <li>
                    <a class="dropdown-item"
                        href="{{ Route::has('user.profile') ? route('user.profile') : url('pages/profile-user') }}">
                        <i class="icon-base ti tabler-user me-3 icon-md"></i><span class="align-middle">My
                            Profile</span> </a>
                </li>
                @if (Auth::check() && Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <li>
                        <a class="dropdown-item" href="{{ route('api-tokens.index') }}">
                            <i class="icon-base ti tabler-settings me-3 icon-md"></i><span class="align-middle">API
                                Tokens</span> </a>
                    </li>
                @endif
                {{-- <li>
          <a class="dropdown-item" href="{{ url('pages/account-settings-billing') }}">
            <span class="d-flex align-items-center align-middle">
              <i class="flex-shrink-0 icon-base ti tabler-file-dollar me-3 icon-md"></i><span
                class="flex-grow-1 align-middle">Billing</span>
              <span class="flex-shrink-0 badge bg-danger d-flex align-items-center justify-content-center">4</span>
            </span>
          </a>
        </li> --}}
                @if (Auth::User() && Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <li>
                        <div class="dropdown-divider my-1 mx-n2"></div>
                    </li>
                    <li>
                        <h6 class="dropdown-header">Manage Team</h6>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <a class="dropdown-item"
                            href="{{ Auth::user() ? route('teams.show', Auth::user()->currentTeam->id) : 'javascript:void(0)' }}">
                            <i class="icon-base bx bx-cog icon-md me-3"></i><span>Team Settings</span>
                        </a>
                    </li>
                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <li>
                            <a class="dropdown-item" href="{{ route('teams.create') }}">
                                <i class="icon-base bx bx-user icon-md me-3"></i><span>Create New Team</span>
                            </a>
                        </li>
                    @endcan
                    @if (Auth::user()->allTeams()->count() > 1)
                        <li>
                            <div class="dropdown-divider my-1"></div>
                        </li>
                        <li>
                            <h6 class="dropdown-header">Switch Teams</h6>
                        </li>
                        <li>
                            <div class="dropdown-divider my-1"></div>
                        </li>
                    @endif
                    @if (Auth::user())
                        @foreach (Auth::user()->allTeams() as $team)
                            {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}

                            {{-- <x-switchable-team :team="$team" /> --}}
                        @endforeach
                    @endif
                @endif
                <li>
                    <div class="dropdown-divider my-1 mx-n2"></div>
                </li>
                @if (Auth::check())
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Logout</span>
                        </a>
                    </li>
                    <form method="POST" id="logout-form" action="{{ route('logout') }}">
                        @csrf
                    </form>
                @else
                    <li>
                        <div class="d-grid px-2 pt-2 pb-1">
                            <a class="btn btn-sm btn-danger d-flex"
                                href="{{ Route::has('login') ? route('login') : url('user/login') }}">
                                <small class="align-middle">Login</small>
                                <i class="icon-base ti tabler-login ms-2 icon-14px"></i>
                            </a>
                        </div>
                    </li>
                @endif
            </ul>
        </li>
        <!--/ User -->
    </ul>
</div>
