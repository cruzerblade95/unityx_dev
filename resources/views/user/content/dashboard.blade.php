{{-- @extends('user.layouts.layoutMaster')

@section('title', 'User Dashboard')

@section('content')
<div class="container py-4">
    <h1>Welcome to your Dashboard!</h1>
    <p>This is the user dashboard content area.</p>
</div>
@endsection --}}
@extends('user/layouts/layoutMaster')

@section('title', 'eCommerce Dashboard')

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

        /* Dashboard Banner Carousel Fix - Prevent button overlap */
        #advertisementCarousel {
            position: relative;
        }

        /* Ensure carousel controls have higher z-index than banner content */
        #advertisementCarousel .carousel-control-prev,
        #advertisementCarousel .carousel-control-next {
            z-index: 10;
            width: 50px;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 50%;
            margin: auto 10px;
            height: 50px;
            top: 50%;
            transform: translateY(-50%);
        }

        #advertisementCarousel .carousel-control-prev:hover,
        #advertisementCarousel .carousel-control-next:hover {
            background-color: rgba(0, 0, 0, 0.5);
        }

        /* Adjust banner content area to avoid overlap with navigation buttons */
        #advertisementCarousel .carousel-item .container-fluid {
            margin-left: 60px;
            margin-right: 60px;
        }

        /* On smaller screens, adjust margin and hide navigation arrows to prevent overlap */
        @media (max-width: 768px) {
            #advertisementCarousel .carousel-item .container-fluid {
                margin-left: 20px;
                margin-right: 20px;
            }

            #advertisementCarousel .carousel-control-prev,
            #advertisementCarousel .carousel-control-next {
                width: 40px;
                height: 40px;
                margin: auto 5px;
            }
        }

        /* Extra small screens - hide navigation arrows to prevent overlap */
        @media (max-width: 576px) {

            #advertisementCarousel .carousel-control-prev,
            #advertisementCarousel .carousel-control-next {
                display: none;
            }

            #advertisementCarousel .carousel-item .container-fluid {
                margin-left: 15px;
                margin-right: 15px;
            }
        }

        /* Ensure banner buttons don't extend into navigation area */
        #advertisementCarousel .carousel-item .btn {
            position: relative;
            z-index: 5;
            max-width: calc(100% - 120px);
            word-wrap: break-word;
        }

        @media (max-width: 768px) {
            #advertisementCarousel .carousel-item .btn {
                max-width: calc(100% - 80px);
            }
        }

        @media (max-width: 576px) {
            #advertisementCarousel .carousel-item .btn {
                max-width: 100%;
            }
        }

        /* Ensure navigation controls don't interfere with banner content clicks */
        #advertisementCarousel .carousel-control-prev {
            left: 10px;
        }

        #advertisementCarousel .carousel-control-next {
            right: 10px;
        }

        /* Add slight padding to prevent button overlap on medium screens */
        @media (min-width: 769px) and (max-width: 1200px) {
            #advertisementCarousel .carousel-item .container-fluid {
                margin-left: 50px;
                margin-right: 50px;
            }
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
            // Initialize tooltips for banner descriptions
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            var el = document.getElementById('sync-estimate');
            if (!el) return; // Exit if element doesn't exist
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

        // Banner Carousel Click Handler - Prevent overlap issues
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.getElementById('advertisementCarousel');
            if (carousel) {
                // Prevent carousel controls from interfering with banner button clicks
                const controlButtons = carousel.querySelectorAll('.carousel-control-prev, .carousel-control-next');
                controlButtons.forEach(function(button) {
                    button.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                });

                // Ensure banner buttons work properly
                const bannerButtons = carousel.querySelectorAll('.carousel-item .btn');
                bannerButtons.forEach(function(button) {
                    button.addEventListener('click', function(e) {
                        e.stopPropagation();
                        // The link will work normally since we're not preventing default
                    });
                });
            }
        });
    </script>
@endsection

@section('content')
    <div class="row g-6">
        <!-- Advertisement Banner Carousel -->
        <div class="col-xl-12 mb-4">
            @if (isset($bannerAdvertisements) && $bannerAdvertisements->count() > 0)
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ti tabler-speakerphone me-2"></i>
                            Promotions & Updates
                        </h5>
                        <div class="dropdown">
                            <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1"
                                type="button" id="bannerDropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="ti tabler-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="bannerDropdown">
                                <a class="dropdown-item" href="javascript:void(0);">Hide Banners</a>
                                <a class="dropdown-item" href="javascript:void(0);">Report Issue</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="advertisementCarousel" class="carousel slide" data-bs-ride="carousel"
                            data-bs-interval="5000">
                            @if ($bannerAdvertisements->count() > 1)
                                <div class="carousel-indicators">
                                    @foreach ($bannerAdvertisements as $index => $banner)
                                        <button type="button" data-bs-target="#advertisementCarousel"
                                            data-bs-slide-to="{{ $index }}"
                                            class="{{ $index === 0 ? 'active' : '' }}"
                                            aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                            aria-label="Slide {{ $index + 1 }}"></button>
                                    @endforeach
                                </div>
                            @endif
                            <div class="carousel-inner" style="border-radius: 0.375rem;">
                                @foreach ($bannerAdvertisements as $index => $banner)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <div class="position-relative carousel-banner-item"
                                            style="height: 200px;
                                                    background: {{ $banner->gradient_color
                                                        ? 'linear-gradient(135deg, ' . $banner->background_color . ' 0%, ' . $banner->gradient_color . ' 100%)'
                                                        : $banner->background_color }};
                                                    overflow: hidden;"
                                            title="{{ $banner->description }}" data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            data-desktop-image="{{ $banner->image_path ? $banner->image_url : '' }}"
                                            data-mobile-image="{{ $banner->mobile_image_path ? $banner->mobile_image_url : ($banner->image_path ? $banner->image_url : '') }}">

                                            <!-- Responsive Background Images -->
                                            @if ($banner->image_path || $banner->mobile_image_path)
                                                <!-- Desktop Image -->
                                                <div class="desktop-image position-absolute top-0 start-0 w-100 h-100 d-none d-md-block"
                                                    style="background-image: url('{{ $banner->image_path ? $banner->image_url : '' }}');
                                                           background-size: contain;
                                                           background-repeat: no-repeat;
                                                           background-position: center;">
                                                </div>

                                                <!-- Mobile Image -->
                                                <div class="mobile-image position-absolute top-0 start-0 w-100 h-100 d-block d-md-none"
                                                    style="background-image: url('{{ $banner->mobile_image_path ? $banner->mobile_image_url : ($banner->image_path ? $banner->image_url : '') }}');
                                                           background-size: contain;
                                                           background-repeat: no-repeat;
                                                           background-position: center;">
                                                </div>

                                                <!-- Overlay for better text readability on images -->
                                                <div class="position-absolute top-0 start-0 w-100 h-100"
                                                    style="background: linear-gradient(135deg, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0.2) 100%);">
                                                </div>
                                            @endif

                                            <div
                                                class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center">
                                                <div class="container-fluid">
                                                    <div class="row align-items-center">
                                                        <div class="col-12">
                                                            @if ($banner->show_title)
                                                                <h3 class="fw-bold mb-2"
                                                                    style="color: {{ $banner->text_color ?? '#ffffff' }};">
                                                                    {{ $banner->title }}
                                                                </h3>
                                                            @endif
                                                            @if ($banner->show_description)
                                                                <p class="mb-3"
                                                                    style="color: {{ $banner->text_color ?? '#ffffff' }}; opacity: 0.8;">
                                                                    {{ $banner->description }}
                                                                </p>
                                                            @endif
                                                            @if ($banner->link_url && $banner->button_text)
                                                                <a href="{{ $banner->link_url }}" target="_blank"
                                                                    rel="noopener noreferrer" class="btn btn-light btn-sm">
                                                                    <i class="ti tabler-external-link me-1"></i>
                                                                    {{ $banner->button_text }}
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if ($bannerAdvertisements->count() > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#advertisementCarousel"
                                    data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#advertisementCarousel"
                                    data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <!--/ Advertisement Banner Carousel -->

        <!-- Custom CSS for mobile responsiveness -->
        <style>
            /* Remove global background image settings since we use separate divs now */
            .carousel-banner-item {
                /* Background image is now handled by separate desktop/mobile divs */
            }

            /* Desktop and larger screens */
            @media (min-width: 992px) {
                .carousel-banner-item {
                    height: 250px !important;
                }
            }

            /* Tablet responsiveness */
            @media (max-width: 991px) and (min-width: 769px) {
                .carousel-banner-item {
                    height: 180px !important;
                }

                .carousel-banner-item h3 {
                    font-size: 1.5rem !important;
                }

                .carousel-banner-item p {
                    font-size: 0.9rem !important;
                    margin-bottom: 1rem !important;
                }
            }

            @media (max-width: 768px) {
                .carousel-banner-item {
                    height: 150px !important;
                }

                .carousel-banner-item h3 {
                    font-size: 1.2rem !important;
                    margin-bottom: 0.75rem !important;
                }

                .carousel-banner-item p {
                    font-size: 0.85rem !important;
                    margin-bottom: 1rem !important;
                    line-height: 1.3 !important;
                }

                .carousel-banner-item .btn {
                    font-size: 0.8rem !important;
                    padding: 0.375rem 0.75rem !important;
                }

                .carousel-banner-item .container-fluid {
                    padding-left: 1rem !important;
                    padding-right: 1rem !important;
                }
            }

            @media (max-width: 576px) {
                .carousel-banner-item {
                    height: 130px !important;
                }

                .carousel-banner-item h3 {
                    font-size: 1rem !important;
                    margin-bottom: 0.5rem !important;
                    line-height: 1.2 !important;
                }

                .carousel-banner-item p {
                    font-size: 0.75rem !important;
                    margin-bottom: 0.75rem !important;
                    line-height: 1.2 !important;
                }

                .carousel-banner-item .btn {
                    font-size: 0.7rem !important;
                    padding: 0.25rem 0.5rem !important;
                }

                .carousel-banner-item .container-fluid {
                    padding-left: 0.75rem !important;
                    padding-right: 0.75rem !important;
                }
            }

            /* Improve text readability on all devices */
            .carousel-banner-item h3,
            .carousel-banner-item p {
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
                word-wrap: break-word;
            }

            .carousel-banner-item .btn {
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }

            /* Enable touch sliding for mobile */
            .carousel-inner {
                touch-action: pan-y pinch-zoom;
            }

            @media (max-width: 768px) {
                .carousel-inner {
                    overflow: hidden;
                }

                /* Move entire content to bottom on mobile to avoid blocking image */
                .carousel-banner-item .d-flex.align-items-center {
                    align-items: flex-end !important;
                }

                .carousel-banner-item .container-fluid {
                    padding-bottom: 1.5rem !important;
                }

                /* Hide carousel controls on very small screens to save space */
                .carousel-control-prev,
                .carousel-control-next {
                    display: none;
                }

                /* Show indicators more prominently on mobile */
                .carousel-indicators {
                    bottom: 10px;
                }

                .carousel-indicators [data-bs-target] {
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    margin: 0 3px;
                }
            }
        </style>

        <!-- Touch Swipe JavaScript for Mobile Carousel -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const carousel = document.getElementById('advertisementCarousel');
                if (!carousel) return;

                let startX = 0;
                let endX = 0;
                let isDown = false;

                // Touch events for mobile
                carousel.addEventListener('touchstart', function(e) {
                    startX = e.touches[0].clientX;
                    isDown = true;
                    // Pause auto-sliding when user starts touching
                    const bsCarousel = bootstrap.Carousel.getInstance(carousel);
                    if (bsCarousel) {
                        bsCarousel.pause();
                    }
                }, {
                    passive: true
                });

                carousel.addEventListener('touchmove', function(e) {
                    if (!isDown) return;
                    // Prevent default scrolling while swiping horizontally
                    const currentX = e.touches[0].clientX;
                    const deltaX = Math.abs(currentX - startX);
                    if (deltaX > 10) { // Only prevent if significant horizontal movement
                        e.preventDefault();
                    }
                }, {
                    passive: false
                });

                carousel.addEventListener('touchend', function(e) {
                    if (!isDown) return;
                    isDown = false;
                    endX = e.changedTouches[0].clientX;

                    const deltaX = startX - endX;
                    const threshold = 50; // Minimum swipe distance

                    const bsCarousel = bootstrap.Carousel.getInstance(carousel) || new bootstrap.Carousel(
                        carousel);

                    if (Math.abs(deltaX) > threshold) {
                        if (deltaX > 0) {
                            // Swipe left - next slide
                            bsCarousel.next();
                        } else {
                            // Swipe right - previous slide
                            bsCarousel.prev();
                        }
                    }

                    // Resume auto-sliding after a delay
                    setTimeout(() => {
                        bsCarousel.cycle();
                    }, 3000);
                }, {
                    passive: true
                });

                // Mouse events for desktop (optional - for better UX)
                carousel.addEventListener('mousedown', function(e) {
                    startX = e.clientX;
                    isDown = true;
                    carousel.style.cursor = 'grabbing';
                });

                carousel.addEventListener('mousemove', function(e) {
                    if (!isDown) return;
                    e.preventDefault();
                });

                carousel.addEventListener('mouseup', function(e) {
                    if (!isDown) return;
                    isDown = false;
                    endX = e.clientX;
                    carousel.style.cursor = 'grab';

                    const deltaX = startX - endX;
                    const threshold = 50;

                    const bsCarousel = bootstrap.Carousel.getInstance(carousel) || new bootstrap.Carousel(
                        carousel);

                    if (Math.abs(deltaX) > threshold) {
                        if (deltaX > 0) {
                            bsCarousel.next();
                        } else {
                            bsCarousel.prev();
                        }
                    }
                });

                carousel.addEventListener('mouseleave', function() {
                    isDown = false;
                    carousel.style.cursor = 'default';
                });

                // Add grab cursor on hover for desktop
                carousel.addEventListener('mouseenter', function() {
                    if (window.innerWidth > 768) {
                        carousel.style.cursor = 'grab';
                    }
                });
            });
        </script>

        <!-- Announcements -->
        <div class="col-xl-12 mb-4">
            <div class="card">
                <div class="card-body">
                    @if (isset($announcements) && $announcements->count() > 0)
                        @foreach ($announcements as $announcement)
                            <div class="alert alert-{{ $announcement->type }} {{ $announcement->is_dismissible ? 'alert-dismissible' : '' }}"
                                role="alert">
                                <h5 class="alert-heading mb-2">{{ $announcement->title }}</h5>
                                <div class="mb-0">{!! $announcement->content !!}</div>
                                @if ($announcement->is_dismissible)
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <!-- Fallback to default announcement if no database announcements -->
                        <div class="alert alert-primary alert-dismissible" role="alert">
                            <h5 class="alert-heading mb-2">Announcement!</h5>
                            <p class="mb-0">Deposit, withdrawal & p2p is available now! Next update will be Referral
                                System Bonus.</p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (
                        \Illuminate\Support\Facades\Auth::user()->telegramVerification &&
                            \Illuminate\Support\Facades\Auth::user()->telegramVerification->chat_id)
                        @if (\Illuminate\Support\Facades\Auth::user()->telegramVerification->telegram_username)
                            <!-- Telegram Connected with Username -->
                            <div class="alert alert-success mt-3" role="alert">
                                <h6 class="alert-heading mb-2">✅ Telegram Connected</h6>
                                <p class="mb-2">Your Telegram account is connected and ready to receive notifications.</p>
                                <p class="mb-0"><strong>Telegram Username:</strong>
                                    {{ \Illuminate\Support\Facades\Auth::user()->telegramVerification->telegram_username }}
                                </p>
                            </div>
                        @else
                            <!-- Telegram Connected but No Username -->
                            <div class="alert alert-warning mt-3" role="alert">
                                <h6 class="alert-heading mb-2">⚠️ Telegram Username Required</h6>
                                <p class="mb-2">Your Telegram is connected, but you need to set a username for
                                    marketplace
                                    transactions.</p>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-info">Step 1:</span>
                                    <small>Set your username in Telegram app settings</small>
                                </div>
                                <div class="d-flex gap-2 mt-1">
                                    <span class="badge bg-info">Step 2:</span>
                                    <small>Verify again using our bot to update your username</small>
                                </div>
                                <a href="{{ route('connect-telegram') }}" class="btn btn-sm btn-warning mt-2">
                                    <i class="ti tabler-brand-telegram me-1"></i>Update Telegram Username
                                </a>
                            </div>
                        @endif
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
                            <h4 class="text-primary mb-1">{{ number_format($wallet->profit ?? 0, 2) }} USDT</h4>
                            <div class="d-flex gap-2">
                                <a href="{{ route('user.ai_trades.index') }}" class="btn btn-primary">View AI Trade</a>
                                {{-- <a href="{{ route('user.withdrawal.index') }}" class="btn btn-success">
                                    <i class="fas fa-money-bill-wave me-1"></i>Withdraw
                                </a> --}}
                            </div>
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
                    <h5 class="card-title mb-0">My Wallets (USD)</h5>
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
                                        <h5 class="mb-0">{{ number_format($wallet->deposit ?? 0, 2) }}</h5>
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
                                        <h5 class="mb-0">{{ number_format($wallet->profit ?? 0, 2) }}</h5>
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
                                        <h5 class="mb-0">{{ number_format($wallet->bonus ?? 0, 2) }}</h5>
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
                                        <h5 class="mb-0">{{ number_format($wallet->cash ?? 0, 2) }}</h5>
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
                                <h4 class="mb-0">{{ number_format($wallet->profit ?? 0, 2) }}</h4>
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
                            {{-- @include('components.coming-soon-overlay') --}}
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
                            {{-- @include('components.coming-soon-overlay') --}}
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
        {{-- <div class="col-xxl-6 col-md-6">
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
        </div> --}}
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
        {{-- <div class="col-xxl-6 col-md-6">
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
        </div> --}}
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

            // Add null checks for DOM elements
            if (!button || !resultDiv) {
                console.error('Required DOM elements not found for Telegram notification test');
                return;
            }

            // Disable button and show loading
            button.disabled = true;
            button.innerHTML = '<i class="ti tabler-loader ti-spin me-1"></i>Sending...';

            // Clear previous results
            resultDiv.innerHTML = '';

            // Get CSRF token safely
            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : '';

            fetch('/test-telegram-notification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        title: '🧪 Test Notification',
                        message: 'Hello! This is a test notification to verify your Telegram integration is working properly. Your notifications will include your username for multi-account support.'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Re-enable button - check if still exists
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '<i class="ti tabler-send me-1"></i>Send Test Notification';
                    }

                    if (resultDiv) {
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
                    }
                })
                .catch(error => {
                    // Re-enable button - check if still exists
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '<i class="ti tabler-send me-1"></i>Send Test Notification';
                    }

                    if (resultDiv) {
                        resultDiv.innerHTML = `
                <div class="alert alert-danger alert-dismissible mt-2 mb-0" role="alert">
                    <strong>❌ Error!</strong> Something went wrong. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
                    }
                    console.error('Error:', error);
                });
        }
    </script>

@endsection
