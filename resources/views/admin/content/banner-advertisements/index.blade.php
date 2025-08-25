@extends('admin/layouts/layoutMaster')

@section('title', 'Banner Advertisements')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
<style>
.banner-preview {
    height: 100px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    position: relative;
    overflow: hidden;
}
.banner-preview img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.banner-preview .overlay {
    position: relative;
    z-index: 1;
    background: rgba(0,0,0,0.5);
    padding: 5px 10px;
    border-radius: 4px;
}
</style>
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('page-script')
<script>
// Function to initialize DataTable when jQuery is ready
function initializeBannerScripts() {
    if (typeof $ === 'undefined') {
        console.warn('jQuery not available for DataTable');
        return;
    }
    
    // jQuery is available
    $(document).ready(function() {
        // Initialize DataTable
        if ($.fn.DataTable) {
            $('.datatable-banner-advertisements').DataTable({
                responsive: true,
                order: [[4, 'asc']], // Sort by sort_order
                columnDefs: [
                    { orderable: false, targets: [0, 6] } // Disable sorting for image and actions
                ]
            });
        }
    });
}

// Wait for DOM and then try to initialize
document.addEventListener('DOMContentLoaded', function() {
    initializeBannerScripts();
    
    // If jQuery still not available, wait a bit and try again
    if (typeof $ === 'undefined') {
        setTimeout(initializeBannerScripts, 100);
    }
});
</script>
</script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold py-3 mb-0">
                        <span class="text-muted fw-light">Admin /</span> Banner Advertisements
                    </h4>
                    <p class="text-muted">Manage promotional banners displayed on user dashboard</p>
                </div>
                <a href="{{ route('admin.banner-advertisements.create') }}" class="btn btn-primary">
                    <i class="tabler-plus me-1"></i>
                    Add New Banner
                </a>
            </div>

            <!-- Success Alert -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Banner Advertisements Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Banner Advertisements ({{ $banners->total() }})</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#orderModal">
                            <i class="tabler-arrows-sort me-1"></i>
                            Sort Order
                        </button>
                    </div>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover datatable-banner-advertisements">
                        <thead>
                            <tr>
                                <th>Preview</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Link</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banners as $banner)
                            <tr>
                                <td>
                                    <div class="banner-preview" style="background: linear-gradient(135deg, {{ $banner->background_color }} 0%, {{ $banner->gradient_color }} 100%);">
                                        @if($banner->image_path)
                                            <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" title="{{ $banner->description }}">
                                        @endif
                                        <div class="overlay">
                                            <small>{{ \Illuminate\Support\Str::limit($banner->title, 15) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium">{{ $banner->title }}</span>
                                        <small class="text-muted">{{ $banner->button_text }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span title="{{ $banner->description }}">
                                        {{ \Illuminate\Support\Str::limit($banner->description, 50) }}
                                    </span>
                                </td>
                                <td>
                                    @if($banner->link_url)
                                        <a href="{{ $banner->link_url }}" target="_blank" class="text-primary">
                                            <i class="tabler-external-link me-1"></i>
                                            {{ \Illuminate\Support\Str::limit($banner->link_url, 30) }}
                                        </a>
                                    @else
                                        <span class="text-muted">No link</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-label-info">{{ $banner->sort_order }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        @if($banner->is_active)
                                            <span class="badge bg-success">Enabled</span>
                                        @else
                                            <span class="badge bg-secondary">Disabled</span>
                                        @endif
                                        
                                        @if($banner->isCurrentlyActive())
                                            <small class="badge bg-success-subtle text-success">Currently Active</small>
                                        @else
                                            <small class="badge bg-secondary-subtle text-secondary">Not Active</small>
                                        @endif
                                    </div>
                                    
                                    @if($banner->start_date || $banner->end_date)
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                @if($banner->start_date)
                                                    From: {{ $banner->start_date->format('M d, Y') }}
                                                @endif
                                                @if($banner->end_date)
                                                    <br>To: {{ $banner->end_date->format('M d, Y') }}
                                                @endif
                                            </small>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.banner-advertisements.show', $banner) }}">
                                                <i class="tabler-eye me-1"></i> View
                                            </a>
                                            <a class="dropdown-item" href="{{ route('admin.banner-advertisements.edit', $banner) }}">
                                                <i class="tabler-pencil me-1"></i> Edit
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form action="{{ route('admin.banner-advertisements.toggle-status', $banner) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item" 
                                                        onclick="return confirm('Are you sure you want to toggle this banner status?')">
                                                    <i class="tabler-toggle-{{ $banner->is_active ? 'left' : 'right' }} me-1"></i> 
                                                    {{ $banner->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            <div class="dropdown-divider"></div>
                                            <form action="{{ route('admin.banner-advertisements.destroy', $banner) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this banner?')">
                                                    <i class="tabler-trash me-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="tabler-photo-off mb-2" style="font-size: 3rem; color: #ddd;"></i>
                                        <h6 class="text-muted">No banner advertisements found</h6>
                                        <p class="text-muted mb-3">Create your first promotional banner to get started</p>
                                        <a href="{{ route('admin.banner-advertisements.create') }}" class="btn btn-primary">
                                            <i class="tabler-plus me-1"></i>
                                            Add New Banner
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($banners->hasPages())
                <div class="card-footer">
                    {{ $banners->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
