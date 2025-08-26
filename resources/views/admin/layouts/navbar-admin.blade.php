{{-- dont use this one --}}

@if (isset($menuHorizontal))
    {{ $menuHorizontal }}
@endif

<nav class="layout-navbar container-{{ config('variables.style') }} navbar navbar-expand-xl {{ config('variables.navbarType') }} align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="navbar-brand app-brand demo d-xl-flex py-0 me-4">
        <a href="{{ url('/') }}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo"><img src="{{ asset('assets/img/icons/logo-new-unityx.png') }}"
                    alt="UnityX Logo" width="200"></span>
            <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
            <i class="icon-base ti tabler-x"></i>
        </a>
    </div>

    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="icon-base ti tabler-menu-2"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- Logout Button -->
            <li class="nav-item">
                <a class="nav-link btn btn-outline-danger btn-sm" href="#"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="icon-base ti tabler-logout me-2"></i>
                    <span class="align-middle">Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
            <!--/ Logout Button -->
        </ul>
    </div>
</nav>
