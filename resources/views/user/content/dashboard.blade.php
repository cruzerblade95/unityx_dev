{{-- @extends('user.layouts.layoutMaster')

@section('title', 'User Dashboard')

@section('content')
<div class="container py-4">
    <h1>Welcome to your Dashboard!</h1>
    <p>This is the user dashboard content area.</p>
</div>
@endsection --}}
@extends('user/layouts/layoutMaster')

@section('title', 'eCommerce Dashboard - Apps')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'public/assets/css/coming-soon-overlay.css'])
    <style>
        .listing-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
        }

        .listing-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/app-ecommerce-dashboard.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('sync-estimate');
            var customTimestamp = el.getAttribute('data-timestamp');

            function pad(n) {
                return n < 10 ? '0' + n : n;
            }

            function updateCountdown(target) {
                var now = new Date();
                var diff = Math.max(0, target - now);
                var days = Math.floor(diff / (1000 * 60 * 60 * 24));
                var hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
                var minutes = Math.floor((diff / (1000 * 60)) % 60);
                var seconds = Math.floor((diff / 1000) % 60);
                el.textContent = days + 'd ' + pad(hours) + 'h ' + pad(minutes) + 'm ' + pad(seconds) + 's';
            }
            if (customTimestamp) {
                var target = new Date(customTimestamp);
                updateCountdown(target);
                setInterval(function() {
                    updateCountdown(target);
                }, 1000);
            } else {
                var estimate = new Date();
                estimate.setDate(estimate.getDate() + 3);
                var options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };
                el.textContent = estimate.toLocaleString(undefined, options);
            }
        });
    </script>
@endsection

@section('content')
    <div class="row g-6">
        <!-- Alerts with headings -->
        <div class="col-xl-12 mb-4">
            <div class="card">
                {{-- <h5 class="card-header">Alerts with Heading</h5> --}}
                <div class="card-body">
                    <div class="alert alert-primary alert-dismissible" role="alert">
                        <h5 class="alert-heading mb-2">Announcement!</h5>
                        <p class="mb-0">Deposit, withdrawal & p2p is available now! Next update will be Referral System
                            Bonus.
                            {{-- <span id="sync-estimate" data-timestamp="2025-08-20T11:00:00"></span> --}}
                        </p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    @if (\Illuminate\Support\Facades\Auth::user()->telegram_id)
                        <!-- Telegram Test Section -->
                        <div class="alert alert-info mt-3" role="alert">
                            <h6 class="alert-heading mb-2">📱 Telegram Notifications Connected</h6>
                            <p class="mb-2">Telegram ID:
                                <strong>{{ \Illuminate\Support\Facades\Auth::user()->telegram_id }}</strong></p>
                            <p class="mb-3">Test your Telegram notification connection:</p>
                            <button type="button" class="btn btn-sm btn-info" onclick="testTelegramNotification()">
                                <i class="ti ti-send me-1"></i>Send Test Notification
                            </button>
                            <div id="telegram-test-result" class="mt-2"></div>
                        </div>
                    @else
                        <!-- Telegram Connection Prompt -->
                        <div class="alert alert-warning mt-3" role="alert">
                            <h6 class="alert-heading mb-2">📱 Connect Telegram for Notifications</h6>
                            <p class="mb-2">Get instant notifications on your Telegram account when you receive deposits,
                                withdrawals, and other important updates.</p>
                            <a href="{{ route('connect-telegram') }}" class="btn btn-sm btn-warning">
                                <i class="ti ti-brand-telegram me-1"></i>Connect Telegram
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!--/ Alerts with headings -->
    </div>
    <div class="row g-6">
        <!-- View sales -->
        <div class="col-xl-4">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-7">
                        <div class="card-body text-nowrap">
                            <h5 class="card-title mb-0">Hello, {{ Illuminate\Support\Facades\Auth::user()->firstname }}
                                {{ Illuminate\Support\Facades\Auth::user()->lastname }}! 🎉</h5>
                            <p class="mb-2">Welcome to UnityX</p>
                            <h4 class="text-primary mb-1">0.0000 USDT</h4>
                            <a href="javascript:;" class="btn btn-primary">View Sales</a>
                        </div>
                    </div>
                    <div class="col-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" height="140"
                                alt="view sales" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- View sales -->

        <!-- My Wallets -->
        <div class="col-xl-8 col-md-12">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">My Wallets</h5>
                    <small class="text-body-secondary">
                        @if (isset($wallet) && $wallet && $wallet->updated_at)
                            Updated {{ $wallet->updated_at->diffForHumans() }}
                        @endif
                    </small>
                </div>
                <div class="card-body d-flex align-items-end">
                    <div class="w-100">
                        <div class="row gy-3">
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-primary me-4 p-2">
                                        <i class="icon-base ti tabler-wallet icon-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ number_format($wallet->deposit ?? 0, 4) }} USDT</h5>
                                        <small class="text-uppercase">DEPOSIT</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-info me-4 p-2"><i
                                            class="icon-base ti tabler-trending-up icon-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ number_format($wallet->profit ?? 0, 4) }} USDT</h5>
                                        <small class="text-uppercase">PROFIT</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-danger me-4 p-2">
                                        <i class="icon-base ti tabler-gift icon-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ number_format($wallet->bonus ?? 0, 4) }} USDT</h5>
                                        <small class="text-uppercase">BONUS</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-success me-4 p-2">
                                        <i class="icon-base ti tabler-cash icon-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ number_format($wallet->cash ?? 0, 4) }} USDT</h5>
                                        <small class="text-uppercase">CASH</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ My Wallets -->

        <div class="col-xxl-4 col-12">
            <div class="row g-6">
                <!-- Profit last month -->
                <div class="col-xl-6 col-sm-6">
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <h5 class="card-title mb-1">Profit</h5>
                            <p class="card-subtitle">Last Month</p>
                        </div>
                        <div class="card-body">
                            <div id="profitLastMonth"></div>
                            <div class="d-flex justify-content-between align-items-center mt-3 gap-3">
                                <h4 class="mb-0">0.000</h4>
                                <small class="text-success">+8.24%</small>
                            </div>
                        </div>
                    </div>
                </div>
                <!--/ Profit last month -->

                <!-- Expenses -->
                <div class="col-xl-6 col-sm-6">
                    <div class="card h-100">
                        <div class="card-header pb-2">
                            <h5 class="card-title mb-1">82.5k</h5>
                            <p class="card-subtitle">Expenses</p>
                        </div>
                        <div class="card-body">
                            <div id="expensesChart"></div>
                            <div class="mt-3 text-center">
                                <small class="text-body-secondary mt-3">$21k Expenses more than last month</small>
                            </div>
                            <!-- Overlay -->
                            @include('components.coming-soon-overlay')
                        </div>
                    </div>
                </div>
                <!--/ Expenses -->

                <!-- Generated Leads -->
                <div class="col-xl-12 position-relative">
                    <div class="card h-100">
                        <div class="card-body d-flex justify-content-between position-relative">
                            <div class="d-flex flex-column">
                                <div class="card-title mb-auto">
                                    <h5 class="mb-0 text-nowrap">Generated Leads</h5>
                                    <p class="mb-0">Monthly Report</p>
                                </div>
                                <div class="chart-statistics">
                                    <h3 class="card-title mb-0">4,350</h3>
                                    <p class="text-success text-nowrap mb-0"><i
                                            class="icon-base ti tabler-chevron-up me-1"></i> 15.8%</p>
                                </div>
                            </div>
                            <div id="generatedLeadsChart"></div>
                            <!-- Overlay -->
                            @include('components.coming-soon-overlay')
                        </div>
                    </div>
                </div>
                <!--/ Generated Leads -->
            </div>
        </div>

        <!-- Mini Marketplace -->
        <div class="col-xxl-8">
            <div class="card h-100">
                <div class="card-body p-6">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="m-0 card-title">
                            <i class="fas fa-store me-2"></i>
                            USDT Marketplace
                        </h5>
                        <a href="{{ route('user.marketplace.index') }}" class="btn btn-primary">
                            <i class="fas fa-external-link-alt me-2"></i>
                            View All Listings
                        </a>
                    </div>

                    @if (isset($miniMarketplaceListings) && $miniMarketplaceListings->count() > 0)
                        <div class="row g-3">
                            @foreach ($miniMarketplaceListings as $listing)
                                <div class="col-xl-3 col-lg-6 col-md-6">
                                    <div class="card border listing-card h-100">
                                        <div class="card-body p-3">
                                            <div class="text-center mb-3">
                                                <i class="fas fa-coins text-warning" style="font-size: 2rem;"></i>
                                            </div>

                                            <h6 class="card-title text-center mb-2">
                                                {{ number_format($listing->amount, 2) }} USDT
                                            </h6>

                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">Multiplier:</small>
                                                    <strong>x{{ number_format($listing->price_per_usdt, 2) }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">Total Price:</small>
                                                    <strong>${{ number_format($listing->total_price, 2) }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">Fee (1.8%):</small>
                                                    <small
                                                        class="text-danger">${{ number_format($listing->total_price * 0.018, 2) }}</small>
                                                </div>
                                                <hr class="my-2">
                                                <div class="d-flex justify-content-between">
                                                    <strong>You Pay:</strong>
                                                    <strong
                                                        class="text-primary">${{ number_format($listing->total_price * 1.018, 2) }}</strong>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>
                                                    {{ $listing->seller->username ?? 'Seller' }}
                                                </small>
                                                <small class="text-muted">{{ $listing->unique_id }}</small>
                                            </div>

                                            <a href="{{ route('user.marketplace.index') }}"
                                                class="btn btn-primary btn-sm w-100">
                                                <i class="fas fa-shopping-cart me-2"></i>
                                                Buy Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-store-slash text-muted" style="font-size: 4rem;"></i>
                            <h6 class="mt-3 text-muted">No USDT listings available</h6>
                            <p class="text-muted">Check back later or create your own listing!</p>
                            <a href="{{ route('user.marketplace.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-plus me-2"></i>
                                Visit Marketplace
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!--/ Mini Marketplace -->

        <!-- Earning Reports -->
        <div class="col-xxl-6 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="mb-1">Earning Reports</h5>
                        <p class="card-subtitle">Weekly Earnings Overview</p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1"
                            type="button" id="earningReports" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="earningReports">
                            <a class="dropdown-item" href="javascript:void(0);">Download</a>
                            <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                            <a class="dropdown-item" href="javascript:void(0);">Share</a>
                        </div>
                    </div>
                </div>
                <div class="card-body pb-0">
                    <ul class="p-0 m-0">
                        <li class="d-flex align-items-center mb-5">
                            <div class="me-4">
                                <span class="badge bg-label-primary rounded p-1_5">
                                    <i class="icon-base ti tabler-chart-pie-2 icon-md"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Net Profit</h6>
                                    <small class="text-body">12.4k Sales</small>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-4">
                                    <small>$1,619</small>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="icon-base ti tabler-chevron-up text-success"></i>
                                        <small class="text-body-secondary">18.6%</small>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex align-items-center mb-5">
                            <div class="me-4">
                                <span class="badge bg-label-success rounded p-1_5">
                                    <i class="icon-base ti tabler-currency-dollar icon-md"></i></span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Total Income</h6>
                                    <small class="text-body">Sales, Affiliation</small>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-4">
                                    <small>$3,571</small>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="icon-base ti tabler-chevron-up text-success"></i>
                                        <small class="text-body-secondary">39.6%</small>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex align-items-center mb-5">
                            <div class="me-4">
                                <span class="badge bg-label-secondary text-body rounded p-1_5"><i
                                        class="icon-base ti tabler-credit-card icon-md"></i></span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Total Expenses</h6>
                                    <small class="text-body">ADVT, Marketing</small>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-4">
                                    <small>$430</small>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="icon-base ti tabler-chevron-up text-success"></i>
                                        <small class="text-body-secondary">52.8%</small>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div id="reportBarChart"></div>
                    <!-- Overlay -->
                    @include('components.coming-soon-overlay')
                </div>
            </div>
        </div>
        <!--/ Earning Reports -->

        <!-- Popular Product -->
        {{-- <div class="col-xxl-4 col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div class="card-title m-0 me-2">
          <h5 class="mb-1">Popular Products</h5>
          <p class="card-subtitle">Total 10.4k Visitors</p>
        </div>
        <div class="dropdown">
          <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1" type="button"
            id="popularProduct" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="popularProduct">
            <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
            <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
            <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <ul class="p-0 m-0">
          <li class="d-flex mb-6">
            <div class="me-4">
              <img src="{{ asset('assets/img/products/iphone.png')}}" alt="User" class="rounded" width="46" />
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Apple iPhone 13</h6>
                <small class="text-body d-block">Item: #FXZ-4567</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <p class="mb-0">$999.29</p>
              </div>
            </div>
          </li>
          <li class="d-flex mb-6">
            <div class="me-4">
              <img src="{{ asset('assets/img/products/nike-air-jordan.png')}}" alt="User" class="rounded" width="46" />
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Nike Air Jordan</h6>
                <small class="text-body d-block">Item: #FXZ-3456</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <p class="mb-0">$72.40</p>
              </div>
            </div>
          </li>
          <li class="d-flex mb-6">
            <div class="me-4">
              <img src="{{ asset('assets/img/products/headphones.png')}}" alt="User" class="rounded" width="46" />
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Beats Studio 2</h6>
                <small class="text-body d-block">Item: #FXZ-9485</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <p class="mb-0">$99</p>
              </div>
            </div>
          </li>
          <li class="d-flex mb-6">
            <div class="me-4">
              <img src="{{ asset('assets/img/products/apple-watch.png')}}" alt="User" class="rounded" width="46" />
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Apple Watch Series 7</h6>
                <small class="text-body d-block">Item: #FXZ-2345</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <p class="mb-0">$249.99</p>
              </div>
            </div>
          </li>
          <li class="d-flex mb-6">
            <div class="me-4">
              <img src="{{ asset('assets/img/products/amazon-echo.png')}}" alt="User" class="rounded" width="46" />
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Amazon Echo Dot</h6>
                <small class="text-body d-block">Item: #FXZ-8959</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <p class="mb-0">$79.40</p>
              </div>
            </div>
          </li>
          <li class="d-flex">
            <div class="me-4">
              <img src="{{ asset('assets/img/products/play-station.png')}}" alt="User" class="rounded" width="46" />
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Play Station Console</h6>
                <small class="text-body d-block">Item: #FXZ-7892</small>
              </div>
              <div class="user-progress d-flex align-items-center gap-1">
                <p class="mb-0">$129.48</p>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div> --}}
        <!--/ Popular Product -->

        <!-- Sales by Countries tabs-->
        {{-- <div class="col-xxl-4 col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="mb-1">Orders by Countries</h5>
          <p class="card-subtitle">62 deliveries in progress</p>
        </div>
        <div class="dropdown">
          <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1" type="button"
            id="salesByCountryTabs" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesByCountryTabs">
            <a class="dropdown-item" href="javascript:void(0);">Select All</a>
            <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
            <a class="dropdown-item" href="javascript:void(0);">Share</a>
          </div>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="nav-align-top">
          <ul class="nav nav-tabs nav-fill rounded-0 timeline-indicator-advanced" role="tablist">
            <li class="nav-item">
              <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-new" aria-controls="navs-justified-new"
                aria-selected="true">New</button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-link-preparing" aria-controls="navs-justified-link-preparing"
                aria-selected="false">Preparing</button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                data-bs-target="#navs-justified-link-shipping" aria-controls="navs-justified-link-shipping"
                aria-selected="false">Shipping</button>
            </li>
          </ul>
          <div class="tab-content border-0  mx-1">
            <div class="tab-pane fade show active" id="navs-justified-new" role="tabpanel">
              <ul class="timeline mb-0">
                <li class="timeline-item ps-6 border-dashed">
                  <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                    <i class="icon-base ti tabler-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase">sender</small>
                    </div>
                    <h6 class="my-50">Myrtle Ullrich</h6>
                    <p class="text-body mb-0">101 Boulder, California(CA), 95959</p>
                  </div>
                </li>
                <li class="timeline-item ps-6 border-0">
                  <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                    <i class="icon-base ti tabler-map-pin"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase">Receiver</small>
                    </div>
                    <h6 class="my-50">Barry Schowalter</h6>
                    <p class="text-body mb-0">939 Orange, California(CA), 92118</p>
                  </div>
                </li>
              </ul>
              <div class="border-1 border-light border-dashed my-4"></div>
              <ul class="timeline mb-0">
                <li class="timeline-item ps-6 border-dashed">
                  <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                    <i class="icon-base ti tabler-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase">sender</small>
                    </div>
                    <h6 class="my-50">Veronica Herman</h6>
                    <p class="text-body mb-0">162 Windsor, California(CA), 95492</p>
                  </div>
                </li>
                <li class="timeline-item ps-6 border-0">
                  <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                    <i class="icon-base ti tabler-map-pin"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase">Receiver</small>
                    </div>
                    <h6 class="my-50">Helen Jacobs</h6>
                    <p class="text-body mb-0">487 Sunset, California(CA), 94043</p>
                  </div>
                </li>
              </ul>
            </div>

            <div class="tab-pane fade" id="navs-justified-link-preparing" role="tabpanel">
              <ul class="timeline mb-0">
                <li class="timeline-item ps-6 border-dashed">
                  <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                    <i class="icon-base ti tabler-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase">sender</small>
                    </div>
                    <h6 class="my-50">Barry Schowalter</h6>
                    <p class="text-body mb-0">939 Orange, California(CA), 92118</p>
                  </div>
                </li>
                <li class="timeline-item ps-6 border-0">
                  <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                    <i class="icon-base ti tabler-map-pin"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase">Receiver</small>
                    </div>
                    <h6 class="my-50">Myrtle Ullrich</h6>
                    <p class="text-body mb-0">101 Boulder, California(CA), 95959</p>
                  </div>
                </li>
              </ul>
              <div class="border-1 border-light border-dashed my-4"></div>
              <ul class="timeline mb-0">
                <li class="timeline-item ps-6 border-dashed">
                  <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                    <i class="icon-base ti tabler-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase">sender</small>
                    </div>
                    <h6 class="my-50">Veronica Herman</h6>
                    <p class="text-body mb-0">162 Windsor, California(CA), 95492</p>
                  </div>
                </li>
                <li class="timeline-item ps-6 border-0">
                  <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                    <i class="icon-base ti tabler-map-pin"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase">Receiver</small>
                    </div>
                    <h6 class="my-50">Helen Jacobs</h6>
                    <p class="text-body mb-0">487 Sunset, California(CA), 94043</p>
                  </div>
                </li>
              </ul>
            </div>
            <div class="tab-pane fade" id="navs-justified-link-shipping" role="tabpanel">
              <ul class="timeline mb-0">
                <li class="timeline-item ps-6 border-dashed">
                  <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                    <i class="icon-base ti tabler-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase">sender</small>
                    </div>
                    <h6 class="my-50">Veronica Herman</h6>
                    <p class="text-body mb-0">101 Boulder, California(CA), 95959</p>
                  </div>
                </li>
                <li class="timeline-item ps-6 border-0">
                  <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                    <i class="icon-base ti tabler-map-pin"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase">Receiver</small>
                    </div>
                    <h6 class="my-50">Barry Schowalter</h6>
                    <p class="text-body mb-0">939 Orange, California(CA), 92118</p>
                  </div>
                </li>
              </ul>
              <div class="border-1 border-light border-dashed my-4"></div>
              <ul class="timeline mb-0">
                <li class="timeline-item ps-6 border-dashed">
                  <span class="timeline-indicator-advanced timeline-indicator-success border-0 shadow-none">
                    <i class="icon-base ti tabler-circle-check"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-success text-uppercase">sender</small>
                    </div>
                    <h6 class="my-50">Myrtle Ullrich</h6>
                    <p class="text-body mb-0">162 Windsor, California(CA), 95492</p>
                  </div>
                </li>
                <li class="timeline-item ps-6 border-0">
                  <span class="timeline-indicator-advanced timeline-indicator-primary border-0 shadow-none">
                    <i class="icon-base ti tabler-map-pin"></i>
                  </span>
                  <div class="timeline-event ps-1">
                    <div class="timeline-header">
                      <small class="text-primary text-uppercase">Receiver</small>
                    </div>
                    <h6 class="my-50">Helen Jacobs</h6>
                    <p class="text-body mb-0">487 Sunset, California(CA), 94043</p>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div> --}}
        <!--/ Sales by Countries tabs -->

        <!-- Transactions -->
        <div class="col-xxl-6 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <div class="card-title m-0 me-2">
                        <h5 class="mb-1">Transactions</h5>
                        <p class="card-subtitle">Total 58 Transactions done in this Month</p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1"
                            type="button" id="transactionID" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
                            <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
                            <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                            <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        <li class="d-flex mb-3 pb-1 align-items-center">
                            <div class="badge bg-label-primary me-4 rounded p-1_5">
                                <i class="icon-base ti tabler-wallet icon-md"></i>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Wallet</h6>
                                    <small class="text-body d-block">Starbucks</small>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0 text-danger">-$75</h6>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3 pb-1 align-items-center">
                            <div class="badge bg-label-success me-4 rounded p-1_5">
                                <i class="icon-base ti tabler-browser-check icon-md"></i>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Bank Transfer</h6>
                                    <small class="text-body d-block">Add Money</small>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0 text-success">+$480</h6>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3 pb-1 align-items-center">
                            <div class="badge bg-label-danger me-4 rounded p-1_5">
                                <i class="icon-base ti tabler-brand-paypal icon-md"></i>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Paypal</h6>
                                    <small class="text-body d-block">Client Payment</small>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0 text-success">+$268</h6>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3 pb-1 align-items-center">
                            <div class="badge bg-label-secondary me-4 rounded p-1_5">
                                <i class="icon-base ti tabler-credit-card icon-md"></i>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Master Card</h6>
                                    <small class="text-body d-block">Ordered iPhone 13</small>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0 text-danger">-$699</h6>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3 pb-1 align-items-center">
                            <div class="badge bg-label-info me-4 rounded p-1_5">
                                <i class="icon-base ti tabler-currency-dollar icon-md"></i>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Bank Transactions</h6>
                                    <small class="text-body d-block">Refund</small>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0 text-success">+$98</h6>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-3 pb-1 align-items-center">
                            <div class="badge bg-label-danger me-4 rounded p-1_5">
                                <i class="icon-base ti tabler-brand-paypal icon-md"></i>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Paypal</h6>
                                    <small class="text-body d-block">Client Payment</small>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0 text-success">+$126</h6>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex align-items-center">
                            <div class="badge bg-label-success me-4 rounded p-1_5">
                                <i class="icon-base ti tabler-building-bank icon-md"></i>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">Bank Transfer</h6>
                                    <small class="text-body d-block">Pay Office Rent</small>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0 text-danger">-$1290</h6>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <!-- Overlay -->
                    @include('components.coming-soon-overlay')
                </div>
            </div>
        </div>
        <!--/ Transactions -->

        <!-- Invoice table -->
        {{-- <div class="col-xxl-8">
    <div class="card">
      <div class="card-datatable table-responsive">
        <table class="table table-sm datatable-invoice border-top">
          <thead>
            <tr>
              <th></th>
              <th></th>
              <th>#</th>
              <th>Status</th>
              <th>Total</th>
              <th>Issued Date</th>
              <th>Invoice Status</th>
              <th>Action</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div> --}}
        <!-- /Invoice table -->
    </div>

    <script>
        function testTelegramNotification() {
            const button = document.querySelector('button[onclick="testTelegramNotification()"]');
            const resultDiv = document.getElementById('telegram-test-result');

            // Disable button and show loading
            button.disabled = true;
            button.innerHTML = '<i class="ti ti-loader ti-spin me-1"></i>Sending...';

            // Clear previous results
            resultDiv.innerHTML = '';

            fetch('/test-telegram-notification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        title: '🧪 Test Notification',
                        message: 'Hello! This is a test notification to verify your Telegram integration is working properly. Your notifications will include your username for multi-account support.'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Re-enable button
                    button.disabled = false;
                    button.innerHTML = '<i class="ti ti-send me-1"></i>Send Test Notification';

                    if (data.success) {
                        resultDiv.innerHTML = `
                <div class="alert alert-success alert-dismissible mt-2 mb-0" role="alert">
                    <strong>✅ Success!</strong> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
                    } else {
                        resultDiv.innerHTML = `
                <div class="alert alert-danger alert-dismissible mt-2 mb-0" role="alert">
                    <strong>❌ Failed!</strong> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
                    }
                })
                .catch(error => {
                    // Re-enable button
                    button.disabled = false;
                    button.innerHTML = '<i class="ti ti-send me-1"></i>Send Test Notification';

                    resultDiv.innerHTML = `
            <div class="alert alert-danger alert-dismissible mt-2 mb-0" role="alert">
                <strong>❌ Error!</strong> Something went wrong. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
                    console.error('Error:', error);
                });
        }
    </script>

@endsection
