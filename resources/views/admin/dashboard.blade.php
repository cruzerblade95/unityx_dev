@php
$configData = Helper::appClasses();
$admin = auth()->guard('admin')->user();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Admin Dashboard - Analytics')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
'resources/assets/vendor/libs/swiper/swiper.scss',
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/fonts/flag-icons.scss'
])
@endsection

@section('page-style')
@vite('resources/assets/vendor/scss/pages/cards-advance.scss')
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/apex-charts/apexcharts.js',
'resources/assets/vendor/libs/swiper/swiper.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'
])
@endsection

@section('page-script')
@vite('resources/assets/js/dashboards-analytics.js')
@endsection

@section('content')

<div class="row g-6">
  <!-- Website Analytics -->
  <div class="col-xl-6 col">
    <div class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg" id="swiper-with-pagination-cards">
      <div class="swiper-wrapper">
        <div class="swiper-slide">
          <div class="row">
            <div class="col-12">
              <h5 class="text-white mb-0">Welcome back, {{ $admin->username }}! 👋</h5>
              <small>Total {{ number_format($totalUsers * 0.285, 1) }}% Conversion Rate</small>
            </div>
            <div class="row">
              <div class="col-lg-7 col-md-9 col-12 order-2 order-md-1 pt-md-9">
                <h6 class="text-white mt-0 mt-md-3 mb-4">Traffic</h6>
                <div class="row">
                  <div class="col-6">
                    <ul class="list-unstyled mb-0">
                      <li class="d-flex mb-4 align-items-center">
                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">28%</p>
                        <p class="mb-0">Sessions</p>
                      </li>
                      <li class="d-flex align-items-center">
                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">{{ $totalUsers }}</p>
                        <p class="mb-0">Users</p>
                      </li>
                    </ul>
                  </div>
                  <div class="col-6">
                    <ul class="list-unstyled mb-0">
                      <li class="d-flex mb-4 align-items-center">
                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">{{ $activeSessions }}</p>
                        <p class="mb-0">Active</p>
                      </li>
                      <li class="d-flex align-items-center">
                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">12%</p>
                        <p class="mb-0">Conversions</p>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="col-lg-5 col-md-3 col-12 order-1 order-md-2 my-4 my-md-0 text-center">
                <img src="{{ asset('assets/img/illustrations/card-website-analytics-1.png') }}" alt="Website Analytics"
                  height="150" class="card-website-analytics-img" />
              </div>
            </div>
          </div>
        </div>
        <div class="swiper-slide">
          <div class="row">
            <div class="col-12">
              <h5 class="text-white mb-0">Analytics Overview</h5>
              <small>Total {{ number_format($totalUsers * 0.285, 1) }}% Growth</small>
            </div>
            <div class="col-lg-7 col-md-9 col-12 order-2 order-md-1 pt-md-9">
              <h6 class="text-white mt-0 mt-md-3 mb-4">Activity</h6>
              <div class="row">
                <div class="col-6">
                  <ul class="list-unstyled mb-0">
                    <li class="d-flex mb-4 align-items-center">
                      <p class="mb-0 fw-medium me-2 website-analytics-text-bg">{{ $activeSessions }}h</p>
                      <p class="mb-0">Active Time</p>
                    </li>
                    <li class="d-flex align-items-center">
                      <p class="mb-0 fw-medium me-2 website-analytics-text-bg">127</p>
                      <p class="mb-0">Actions</p>
                    </li>
                  </ul>
                </div>
                <div class="col-6">
                  <ul class="list-unstyled mb-0">
                    <li class="d-flex mb-4 align-items-center">
                      <p class="mb-0 fw-medium me-2 website-analytics-text-bg">18</p>
                      <p class="mb-0">Sessions/User</p>
                    </li>
                    <li class="d-flex align-items-center">
                      <p class="mb-0 fw-medium me-2 website-analytics-text-bg">2.3k</p>
                      <p class="mb-0">Requests</p>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-lg-5 col-md-3 col-12 order-1 order-md-2 my-4 my-md-0 text-center">
              <img src="{{ asset('assets/img/illustrations/card-website-analytics-2.png') }}" alt="Website Analytics"
                height="150" class="card-website-analytics-img" />
            </div>
          </div>
        </div>
      </div>
      <div class="swiper-pagination"></div>
    </div>
  </div>
  <!--/ Website Analytics -->

  <!-- Average Daily Sales -->
  <div class="col-xl-3 col-sm-6">
    <div class="card h-100">
      <div class="card-header pb-0">
        <h5 class="mb-3 card-title">Daily Activity</h5>
        <p class="mb-0 text-body">Total Activity This Month</p>
        <h4 class="mb-0">{{ $activeSessions * 24 }}</h4>
      </div>
      <div class="card-body px-0">
        <div id="averageDailySales"></div>
      </div>
    </div>
  </div>
  <!--/ Average Daily Sales -->

  <!-- Sales Overview -->
  <div class="col-xl-3 col-sm-6">
    <div class="card h-100">
      <div class="card-header">
        <div class="d-flex justify-content-between">
          <p class="mb-0 text-body">Users Overview</p>
          <p class="card-text fw-medium text-success">+18.2%</p>
        </div>
        <h4 class="card-title mb-1">{{ $totalUsers }}</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-4">
            <div class="d-flex gap-2 align-items-center mb-2">
              <span class="badge bg-label-info p-1 rounded"><i
                  class="icon-base ti tabler-user icon-sm"></i></span>
              <p class="mb-0">Active</p>
            </div>
            <h5 class="mb-0 pt-1">62.2%</h5>
            <small class="text-body-secondary">{{ (int)($totalUsers * 0.622) }}</small>
          </div>
          <div class="col-4">
            <div class="divider divider-vertical">
              <div class="divider-text">
                <span class="badge-divider-bg bg-label-secondary">VS</span>
              </div>
            </div>
          </div>
          <div class="col-4 text-end">
            <div class="d-flex gap-2 justify-content-end align-items-center mb-2">
              <p class="mb-0">Inactive</p>
              <span class="badge bg-label-primary p-1 rounded"><i class="icon-base ti tabler-user-off icon-sm"></i></span>
            </div>
            <h5 class="mb-0 pt-1">37.8%</h5>
            <small class="text-body-secondary">{{ (int)($totalUsers * 0.378) }}</small>
          </div>
        </div>
        <div class="d-flex align-items-center mt-6">
          <div class="progress w-100" style="height: 10px;">
            <div class="progress-bar bg-info" style="width: 62.2%" role="progressbar" aria-valuenow="62.2" aria-valuemin="0"
              aria-valuemax="100"></div>
            <div class="progress-bar bg-primary" role="progressbar" style="width: 37.8%" aria-valuenow="37.8"
              aria-valuemin="0" aria-valuemax="100"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Sales Overview -->

  <!-- Earning Reports -->
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header pb-0 d-flex justify-content-between">
        <div class="card-title mb-0">
          <h5 class="mb-1">User Activity Reports</h5>
          <p class="card-subtitle">Weekly Activity Overview</p>
        </div>
        <div class="dropdown">
          <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1" type="button"
            id="earningReportsId" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="earningReportsId">
            <a class="dropdown-item" href="javascript:void(0);">View More</a>
            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row align-items-center g-md-8">
          <div class="col-12 col-md-5 d-flex flex-column">
            <div class="d-flex gap-2 align-items-center mb-3 flex-wrap">
              <h2 class="mb-0">{{ $activeSessions }}</h2>
              <div class="badge rounded bg-label-success">+4.2%</div>
            </div>
            <small class="text-body">Active sessions this week compared to last week</small>
          </div>
          <div class="col-12 col-md-7 ps-xl-8">
            <div id="weeklyEarningReports"></div>
          </div>
        </div>
        <div class="border rounded p-5 mt-5">
          <div class="row gap-4 gap-sm-0">
            <div class="col-12 col-sm-4">
              <div class="d-flex gap-2 align-items-center">
                <div class="badge rounded bg-label-primary p-1"><i
                    class="icon-base ti tabler-user icon-18px"></i></div>
                <h6 class="mb-0 fw-normal">Total Users</h6>
              </div>
              <h4 class="my-2">{{ $totalUsers }}</h4>
              <div class="progress w-75" style="height:4px">
                <div class="progress-bar" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0"
                  aria-valuemax="100"></div>
              </div>
            </div>
            <div class="col-12 col-sm-4">
              <div class="d-flex gap-2 align-items-center">
                <div class="badge rounded bg-label-info p-1"><i class="icon-base ti tabler-activity icon-18px"></i>
                </div>
                <h6 class="mb-0 fw-normal">Active</h6>
              </div>
              <h4 class="my-2">{{ $activeSessions }}</h4>
              <div class="progress w-75" style="height:4px">
                <div class="progress-bar bg-info" role="progressbar" style="width: 50%" aria-valuenow="50"
                  aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
            <div class="col-12 col-sm-4">
              <div class="d-flex gap-2 align-items-center">
                <div class="badge rounded bg-label-danger p-1"><i
                    class="icon-base ti tabler-user-off icon-18px"></i></div>
                <h6 class="mb-0 fw-normal">Inactive</h6>
              </div>
              <h4 class="my-2">{{ (int)($totalUsers * 0.378) }}</h4>
              <div class="progress w-75" style="height:4px">
                <div class="progress-bar bg-danger" role="progressbar" style="width: 65%" aria-valuenow="65"
                  aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Earning Reports -->

  <!-- Support Tracker -->
  <div class="col-12 col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div class="card-title mb-0">
          <h5 class="mb-1">User Activity Tracker</h5>
          <p class="card-subtitle">Last 7 Days</p>
        </div>
        <div class="dropdown">
          <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1" type="button"
            id="supportTrackerMenu" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="supportTrackerMenu">
            <a class="dropdown-item" href="javascript:void(0);">View More</a>
            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
          </div>
        </div>
      </div>
      <div class="card-body row">
        <div class="col-12 col-sm-4">
          <div class="mt-lg-4 mt-lg-2 mb-lg-6 mb-2">
            <h2 class="mb-0">{{ $totalUsers }}</h2>
            <p class="mb-0">Total Users</p>
          </div>
          <ul class="p-0 m-0">
            <li class="d-flex gap-4 align-items-center mb-lg-3 pb-1">
              <div class="badge rounded bg-label-primary p-1_5"><i class="icon-base ti tabler-user icon-md"></i></div>
              <div>
                <h6 class="mb-0 text-nowrap">Active Users</h6>
                <small class="text-body-secondary">{{ (int)($totalUsers * 0.622) }}</small>
              </div>
            </li>
            <li class="d-flex gap-4 align-items-center mb-lg-3 pb-1">
              <div class="badge rounded bg-label-info p-1_5"><i class="icon-base ti tabler-user-check icon-md"></i>
              </div>
              <div>
                <h6 class="mb-0 text-nowrap">Online Now</h6>
                <small class="text-body-secondary">{{ $activeSessions }}</small>
              </div>
            </li>
            <li class="d-flex gap-4 align-items-center pb-1">
              <div class="badge rounded bg-label-warning p-1_5"><i class="icon-base ti tabler-clock icon-md"></i></div>
              <div>
                <h6 class="mb-0 text-nowrap">Average Time</h6>
                <small class="text-body-secondary">2.5 Hours</small>
              </div>
            </li>
          </ul>
        </div>
        <div class="col-12 col-md-8">
          <div id="supportTracker"></div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Support Tracker -->
</div>

  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="card-info">
            <p class="card-text">System Status</p>
            <div class="d-flex align-items-end mb-2">
              <h4 class="card-title mb-0 me-2">Healthy</h4>
            </div>
          </div>
          <div class="card-icon">
            <span class="badge bg-label-success rounded p-2">
              <i class="icon-base ti tabler-check icon-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div class="card-info">
            <p class="card-text">Last Login</p>
            <div class="d-flex align-items-end mb-2">
              <h4 class="card-title mb-0 me-2">{{ $admin->last_login_at ?? 'Never' }}</h4>
            </div>
          </div>
          <div class="card-icon">
            <span class="badge bg-label-info rounded p-2">
              <i class="icon-base ti tabler-clock icon-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activity -->
  <div class="col-xl-6 col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <h5 class="card-title mb-0">Recent Activity</h5>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="recentActivity" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-base ti tabler-dots-vertical"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="recentActivity">
            <a class="dropdown-item" href="javascript:void(0)">View All</a>
            <a class="dropdown-item" href="javascript:void(0)">Refresh</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <ul class="timeline timeline-center mt-3">
          @foreach($recentActivity as $activity)
          <li class="timeline-item">
            <span class="timeline-indicator timeline-indicator-primary">
              <i class="icon-base ti tabler-{{ $activity->icon }}"></i>
            </span>
            <div class="timeline-event">
              <div class="timeline-header mb-1">
                <h6 class="mb-0">{{ $activity->title }}</h6>
                <small class="text-muted">{{ $activity->time }}</small>
              </div>
              <p class="mb-2">{{ $activity->description }}</p>
            </div>
          </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="col-xl-6 col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Quick Actions</h5>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-sm-6">
            <div class="card shadow-none bg-label-primary">
              <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                  <div class="avatar">
                    <div class="avatar-initial bg-primary rounded">
                      <i class="icon-base ti tabler-user"></i>
                    </div>
                  </div>
                  <div class="ms-2">
                    <h5 class="mb-0">Users</h5>
                  </div>
                </div>
                <a href="#" class="btn btn-primary w-100">Manage Users</a>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="card shadow-none bg-label-success">
              <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                  <div class="avatar">
                    <div class="avatar-initial bg-success rounded">
                      <i class="icon-base ti tabler-settings"></i>
                    </div>
                  </div>
                  <div class="ms-2">
                    <h5 class="mb-0">Settings</h5>
                  </div>
                </div>
                <a href="#" class="btn btn-success w-100">System Settings</a>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="card shadow-none bg-label-info">
              <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                  <div class="avatar">
                    <div class="avatar-initial bg-info rounded">
                      <i class="icon-base ti tabler-report"></i>
                    </div>
                  </div>
                  <div class="ms-2">
                    <h5 class="mb-0">Reports</h5>
                  </div>
                </div>
                <a href="#" class="btn btn-info w-100">View Reports</a>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="card shadow-none bg-label-warning">
              <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                  <div class="avatar">
                    <div class="avatar-initial bg-warning rounded">
                      <i class="icon-base ti tabler-bell"></i>
                    </div>
                  </div>
                  <div class="ms-2">
                    <h5 class="mb-0">Notifications</h5>
                  </div>
                </div>
                <a href="#" class="btn btn-warning w-100">View Notifications</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- System Health -->
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">System Health</h5>
        <button type="button" class="btn btn-primary">
          Check Updates
        </button>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-12 col-md-4">
            <div class="mb-4">
              <h6 class="mb-2">Server Status</h6>
              <div class="progress" style="height: 6px">
                <div class="progress-bar bg-success" style="width: 98%" role="progressbar" aria-valuenow="98" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small>98% Uptime</small>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="mb-4">
              <h6 class="mb-2">Memory Usage</h6>
              <div class="progress" style="height: 6px">
                <div class="progress-bar bg-info" style="width: 45%" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small>45% Used</small>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="mb-4">
              <h6 class="mb-2">Database</h6>
              <div class="progress" style="height: 6px">
                <div class="progress-bar bg-warning" style="width: 72%" role="progressbar" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small>72% Load</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
      </div>
    </div>
  </div>
</div>
@endsection
