@extends('admin/layouts/layoutMaster')

@section('title', 'Announcements Management')

@php
use Illuminate\Support\Str;
@endphp

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Announcements Management</h5>
                    <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                        <i class="ti tabler-plus me-1"></i>Add New Announcement
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($announcements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Active Period</th>
                                        <th>Sort Order</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($announcements as $announcement)
                                        <tr>
                                            <td>{{ $announcement->id }}</td>
                                            <td>
                                                <strong>{{ $announcement->title }}</strong>
                                                <br>
                                                <small class="text-muted">{{ Str::limit($announcement->content, 60) }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $announcement->type }}">
                                                    {{ ucfirst($announcement->type) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($announcement->is_currently_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($announcement->start_date || $announcement->end_date)
                                                    <small>
                                                        @if($announcement->start_date)
                                                            From: {{ $announcement->start_date->format('M d, Y') }}<br>
                                                        @endif
                                                        @if($announcement->end_date)
                                                            To: {{ $announcement->end_date->format('M d, Y') }}
                                                        @endif
                                                    </small>
                                                @else
                                                    <span class="text-muted">No limit</span>
                                                @endif
                                            </td>
                                            <td>{{ $announcement->sort_order }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.announcements.edit', $announcement) }}"
                                                       class="btn btn-sm btn-warning">
                                                        <i class="ti tabler-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.announcements.toggle-status', $announcement) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-{{ $announcement->is_active ? 'secondary' : 'success' }}">
                                                            <i class="ti tabler-{{ $announcement->is_active ? 'eye-off' : 'eye' }}"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.announcements.destroy', $announcement) }}"
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this announcement?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="ti tabler-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $announcements->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti tabler-speakerphone display-4 text-muted"></i>
                            <h5 class="mt-2">No announcements found</h5>
                            <p class="text-muted">Start by creating your first announcement.</p>
                            <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                                <i class="ti tabler-plus me-1"></i>Create First Announcement
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
