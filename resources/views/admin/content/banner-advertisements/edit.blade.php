@extends('admin/layouts/layoutMaster')

@section('title', 'Edit Banner Advertisement')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
<style>
.color-preview {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: 2px solid #ddd;
    cursor: pointer;
}
.banner-preview-live {
    height: 200px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}
.banner-preview-live img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.banner-preview-live .content {
    position: relative;
    z-index: 1;
    text-align: center;
    background: rgba(0,0,0,0.3);
    padding: 20px;
    border-radius: 8px;
}
.current-image {
    max-width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
}
</style>
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Initialize flatpickr for date inputs
    $('.flatpickr').flatpickr({
        dateFormat: 'Y-m-d',
        allowInput: true
    });

    // Live preview functionality
    function updatePreview() {
        const title = $('#title').val() || 'Banner Title';
        const description = $('#description').val() || 'Banner description will appear here';
        const buttonText = $('#button_text').val() || 'Learn More';
        const backgroundColor = $('#background_color').val() || '#667eea';
        const gradientColor = $('#gradient_color').val() || '#764ba2';
        const textColor = $('#text_color').val() || '#ffffff';
        
        const $preview = $('.banner-preview-live');
        $preview.css({
            'background': `linear-gradient(135deg, ${backgroundColor} 0%, ${gradientColor} 100%)`,
            'color': textColor
        });
        
        $preview.find('.banner-title').text(title);
        $preview.find('.banner-description').text(description);
        $preview.find('.banner-button').text(buttonText);
    }

    // Update preview on input change
    $('#title, #description, #button_text, #background_color, #gradient_color, #text_color').on('input change', updatePreview);

    // Image preview
    $('#image').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('.banner-preview-live').css('background-image', `url(${e.target.result})`);
                $('.banner-preview-live').css('background-size', 'cover');
                $('.banner-preview-live').css('background-position', 'center');
            }
            reader.readAsDataURL(file);
        }
    });

    // Color picker simulation
    $('.color-preview').on('click', function() {
        $(this).siblings('input[type="color"]').click();
    });

    $('input[type="color"]').on('change', function() {
        $(this).siblings('.color-preview').css('background-color', $(this).val());
        $(this).siblings('input[type="text"]').val($(this).val());
        updatePreview();
    });

    // Initialize color previews
    $('input[type="color"]').each(function() {
        $(this).siblings('.color-preview').css('background-color', $(this).val());
        $(this).siblings('input[type="text"]').val($(this).val());
    });

    // Initialize preview with current image if exists
    @if($bannerAdvertisement->image_path)
        $('.banner-preview-live').css({
            'background-image': 'url({{ $bannerAdvertisement->image_url }})',
            'background-size': 'cover',
            'background-position': 'center'
        });
    @endif

    // Initialize preview
    updatePreview();
});
</script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold py-3 mb-0">
                        <span class="text-muted fw-light">Admin / Banner Advertisements /</span> Edit
                    </h4>
                    <p class="text-muted">Edit banner advertisement: {{ $bannerAdvertisement->title }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.banner-advertisements.show', $bannerAdvertisement) }}" class="btn btn-outline-info">
                        <i class="ti ti-eye me-1"></i>
                        View
                    </a>
                    <a href="{{ route('admin.banner-advertisements.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>
                        Back to List
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Form -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Banner Details</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.banner-advertisements.update', $bannerAdvertisement) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                
                                <!-- Basic Information -->
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label" for="title">Banner Title *</label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                               id="title" name="title" value="{{ old('title', $bannerAdvertisement->title) }}" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label" for="description">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="3" 
                                                  placeholder="Brief description for hover tooltip">{{ old('description', $bannerAdvertisement->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="button_text">Button Text *</label>
                                        <input type="text" class="form-control @error('button_text') is-invalid @enderror" 
                                               id="button_text" name="button_text" value="{{ old('button_text', $bannerAdvertisement->button_text) }}" required>
                                        @error('button_text')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="link_url">Link URL</label>
                                        <input type="url" class="form-control @error('link_url') is-invalid @enderror" 
                                               id="link_url" name="link_url" value="{{ old('link_url', $bannerAdvertisement->link_url) }}" 
                                               placeholder="https://example.com">
                                        @error('link_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label" for="image">Banner Image</label>
                                        @if($bannerAdvertisement->image_path)
                                            <div class="mb-2">
                                                <small class="text-muted">Current image:</small><br>
                                                <img src="{{ $bannerAdvertisement->image_url }}" alt="Current banner" class="current-image">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                               id="image" name="image" accept="image/*">
                                        <div class="form-text">
                                            Leave empty to keep current image. Recommended size: 1200x400px. Supported formats: JPEG, PNG, GIF (max 2MB)
                                        </div>
                                        @error('image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Color Settings -->
                                <div class="row g-3 mt-4">
                                    <div class="col-12">
                                        <h6 class="mb-3">Color Settings</h6>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label" for="background_color">Background Color</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="form-control @error('background_color') is-invalid @enderror" 
                                                   id="background_color" name="background_color" value="{{ old('background_color', $bannerAdvertisement->background_color) }}" style="display: none;">
                                            <div class="color-preview"></div>
                                            <input type="text" class="form-control" readonly>
                                        </div>
                                        @error('background_color')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label" for="gradient_color">Gradient Color</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="form-control @error('gradient_color') is-invalid @enderror" 
                                                   id="gradient_color" name="gradient_color" value="{{ old('gradient_color', $bannerAdvertisement->gradient_color) }}" style="display: none;">
                                            <div class="color-preview"></div>
                                            <input type="text" class="form-control" readonly>
                                        </div>
                                        @error('gradient_color')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label" for="text_color">Text Color</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="form-control @error('text_color') is-invalid @enderror" 
                                                   id="text_color" name="text_color" value="{{ old('text_color', $bannerAdvertisement->text_color) }}" style="display: none;">
                                            <div class="color-preview"></div>
                                            <input type="text" class="form-control" readonly>
                                        </div>
                                        @error('text_color')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Schedule & Settings -->
                                <div class="row g-3 mt-4">
                                    <div class="col-12">
                                        <h6 class="mb-3">Schedule & Settings</h6>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label" for="sort_order">Sort Order</label>
                                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                               id="sort_order" name="sort_order" value="{{ old('sort_order', $bannerAdvertisement->sort_order) }}" min="0">
                                        <div class="form-text">Lower numbers appear first</div>
                                        @error('sort_order')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label" for="start_date">Start Date</label>
                                        <input type="text" class="form-control flatpickr @error('start_date') is-invalid @enderror" 
                                               id="start_date" name="start_date" value="{{ old('start_date', $bannerAdvertisement->start_date ? $bannerAdvertisement->start_date->format('Y-m-d') : '') }}" 
                                               placeholder="Leave empty for immediate start">
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label" for="end_date">End Date</label>
                                        <input type="text" class="form-control flatpickr @error('end_date') is-invalid @enderror" 
                                               id="end_date" name="end_date" value="{{ old('end_date', $bannerAdvertisement->end_date ? $bannerAdvertisement->end_date->format('Y-m-d') : '') }}" 
                                               placeholder="Leave empty for no expiry">
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" {{ old('is_active', $bannerAdvertisement->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Banner is active
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="ti ti-check me-1"></i>
                                            Update Banner
                                        </button>
                                        <a href="{{ route('admin.banner-advertisements.index') }}" class="btn btn-outline-secondary">
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Live Preview -->
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 1rem;">
                        <div class="card-header">
                            <h5 class="mb-0">Live Preview</h5>
                        </div>
                        <div class="card-body">
                            <div class="banner-preview-live" style="background: linear-gradient(135deg, {{ $bannerAdvertisement->background_color }} 0%, {{ $bannerAdvertisement->gradient_color }} 100%);">
                                <div class="content">
                                    <h4 class="banner-title mb-2">{{ $bannerAdvertisement->title }}</h4>
                                    <p class="banner-description mb-3">{{ $bannerAdvertisement->description ?: 'Banner description will appear here' }}</p>
                                    <button class="btn btn-light btn-sm banner-button">{{ $bannerAdvertisement->button_text }}</button>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="ti ti-info-circle me-1"></i>
                                    This preview shows how your banner will appear on the user dashboard.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
