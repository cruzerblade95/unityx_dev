@extends('admin/layouts/layoutMaster')

@section('title', 'Edit Announcement')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Announcement</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           id="title" name="title" value="{{ old('title', $announcement->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="primary" {{ old('type', $announcement->type) == 'primary' ? 'selected' : '' }}>Primary (Blue)</option>
                                        <option value="success" {{ old('type', $announcement->type) == 'success' ? 'selected' : '' }}>Success (Green)</option>
                                        <option value="warning" {{ old('type', $announcement->type) == 'warning' ? 'selected' : '' }}>Warning (Yellow)</option>
                                        <option value="danger" {{ old('type', $announcement->type) == 'danger' ? 'selected' : '' }}>Danger (Red)</option>
                                        <option value="info" {{ old('type', $announcement->type) == 'info' ? 'selected' : '' }}>Info (Cyan)</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content" name="content" rows="4" required>{{ old('content', $announcement->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">You can use HTML tags for formatting.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date (Optional)</label>
                                    <input type="text" class="form-control flatpickr-datetime @error('start_date') is-invalid @enderror"
                                           id="start_date" name="start_date"
                                           value="{{ old('start_date', $announcement->start_date ? $announcement->start_date->format('Y-m-d H:i') : '') }}"
                                           placeholder="Select start date">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date (Optional)</label>
                                    <input type="text" class="form-control flatpickr-datetime @error('end_date') is-invalid @enderror"
                                           id="end_date" name="end_date"
                                           value="{{ old('end_date', $announcement->end_date ? $announcement->end_date->format('Y-m-d H:i') : '') }}"
                                           placeholder="Select end date">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', $announcement->sort_order) }}" min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Lower numbers appear first</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                           {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_dismissible" name="is_dismissible"
                                           {{ old('is_dismissible', $announcement->is_dismissible) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_dismissible">
                                        Dismissible (Users can close it)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">
                                <i class="ti tabler-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti tabler-check me-1"></i>Update Announcement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Flatpickr for date inputs
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.flatpickr-datetime', {
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            time_24hr: true
        });
    }
});
</script>
@endsection
