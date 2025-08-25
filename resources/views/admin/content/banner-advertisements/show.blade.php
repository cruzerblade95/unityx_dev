@extends('admin/layouts/layoutMaster')

@section('title', 'View Banner Advertisement')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('page-style')
<style>
    .banner-preview-large {
        min-height: 200px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--bs-primary), var(--bs-info));
    }
    
    .banner-preview-large img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .banner-preview-large .overlay {
        position: relative;
        z-index: 2;
        background: rgba(0,0,0,0.6);
        padding: 15px 25px;
        border-radius: 8px;
        text-align: center;
    }

    .info-card {
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        padding: 1.5rem;
        background: var(--bs-body-bg);
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .status-badge {
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
    }

    .gradient-preview {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        border: 2px solid var(--bs-border-color);
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Banner Advertisement Details</h4>
            <p class="text-muted mb-0">View and manage banner advertisement information</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.banner-advertisements.edit', $bannerAdvertisement) }}" 
               class="btn btn-primary">
                <i class="tabler-edit me-1"></i> Edit
            </a>
            <a href="{{ route('admin.banner-advertisements.index') }}" 
               class="btn btn-outline-secondary">
                <i class="tabler-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Banner Preview -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="tabler-photo me-2"></i>Banner Preview
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        @if($bannerAdvertisement->is_active)
                            <span class="badge bg-success status-badge">
                                <i class="tabler-check me-1"></i>Active
                            </span>
                        @else
                            <span class="badge bg-secondary status-badge">
                                <i class="tabler-x me-1"></i>Inactive
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="banner-preview-large" 
                         style="background: linear-gradient(135deg, {{ $bannerAdvertisement->background_color ?? '#6f42c1' }}, {{ $bannerAdvertisement->gradient_color ?? '#0d6efd' }});">
                        @if($bannerAdvertisement->image_path)
                            <img src="{{ $bannerAdvertisement->image_url }}" alt="{{ $bannerAdvertisement->title }}">
                        @endif
                        <div class="overlay" style="color: {{ $bannerAdvertisement->text_color ?? '#ffffff' }};">
                            <h3 class="mb-2">{{ $bannerAdvertisement->title }}</h3>
                            @if($bannerAdvertisement->description)
                                <p class="mb-3">{{ $bannerAdvertisement->description }}</p>
                            @endif
                            @if($bannerAdvertisement->button_text)
                                <button class="btn btn-light">{{ $bannerAdvertisement->button_text }}</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Basic Information -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="tabler-info-circle me-2"></i>Basic Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Title</label>
                            <p class="mb-0">{{ $bannerAdvertisement->title }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Sort Order</label>
                            <p class="mb-0">{{ $bannerAdvertisement->sort_order }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-muted">Description</label>
                            <p class="mb-0">{{ $bannerAdvertisement->description ?: 'No description provided' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Button Text</label>
                            <p class="mb-0">{{ $bannerAdvertisement->button_text ?: 'No button text' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">Link URL</label>
                            @if($bannerAdvertisement->link_url)
                                <p class="mb-0">
                                    <a href="{{ $bannerAdvertisement->link_url }}" target="_blank" class="text-primary">
                                        {{ \Illuminate\Support\Str::limit($bannerAdvertisement->link_url, 50) }}
                                        <i class="tabler-external-link ms-1"></i>
                                    </a>
                                </p>
                            @else
                                <p class="mb-0 text-muted">No link URL</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Design & Settings -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="tabler-palette me-2"></i>Design & Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <label class="form-label fw-semibold text-muted">Background Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <div class="gradient-preview" 
                                     style="background-color: {{ $bannerAdvertisement->background_color ?? '#6f42c1' }};"></div>
                                <code>{{ $bannerAdvertisement->background_color ?? '#6f42c1' }}</code>
                            </div>
                        </div>
                        
                        <div>
                            <label class="form-label fw-semibold text-muted">Gradient Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <div class="gradient-preview" 
                                     style="background-color: {{ $bannerAdvertisement->gradient_color ?? '#0d6efd' }};"></div>
                                <code>{{ $bannerAdvertisement->gradient_color ?? '#0d6efd' }}</code>
                            </div>
                        </div>
                        
                        <div>
                            <label class="form-label fw-semibold text-muted">Text Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <div class="gradient-preview" 
                                     style="background-color: {{ $bannerAdvertisement->text_color ?? '#ffffff' }};"></div>
                                <code>{{ $bannerAdvertisement->text_color ?? '#ffffff' }}</code>
                            </div>
                        </div>

                        <div>
                            <label class="form-label fw-semibold text-muted">Status</label>
                            <div>
                                @if($bannerAdvertisement->is_active)
                                    <span class="badge bg-success">
                                        <i class="tabler-check me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="tabler-x me-1"></i>Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Information -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="tabler-calendar me-2"></i>Schedule Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted">Start Date</label>
                            <p class="mb-0">
                                @if($bannerAdvertisement->start_date)
                                    {{ $bannerAdvertisement->start_date->format('M d, Y H:i') }}
                                @else
                                    <span class="text-muted">No start date set</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted">End Date</label>
                            <p class="mb-0">
                                @if($bannerAdvertisement->end_date)
                                    {{ $bannerAdvertisement->end_date->format('M d, Y H:i') }}
                                @else
                                    <span class="text-muted">No end date set</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted">Current Status</label>
                            <p class="mb-0">
                                @if($bannerAdvertisement->isCurrentlyActive())
                                    <span class="badge bg-success">
                                        <i class="tabler-circle-check me-1"></i>Currently Active
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="tabler-clock me-1"></i>Not in Active Period
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metadata -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="tabler-file-info me-2"></i>Metadata
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">ID</label>
                            <p class="mb-0">#{{ $bannerAdvertisement->id }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Created</label>
                            <p class="mb-0">{{ $bannerAdvertisement->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Last Updated</label>
                            <p class="mb-0">{{ $bannerAdvertisement->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Image File</label>
                            <p class="mb-0">
                                @if($bannerAdvertisement->image_path)
                                    <a href="{{ $bannerAdvertisement->image_url }}" target="_blank" class="text-primary">
                                        View Image <i class="tabler-external-link ms-1"></i>
                                    </a>
                                @else
                                    <span class="text-muted">No image</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
