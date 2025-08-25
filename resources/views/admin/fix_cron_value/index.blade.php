@extends('admin.layouts.layoutMaster')

@section('title', 'Fix Cron Value')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mt-5">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Fix Cron Value</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.fix_cron_value.process') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="timestamp" class="form-label">Input Timestamp</label>
                            <input type="datetime-local" class="form-control" id="timestamp" name="timestamp" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Process</button>
                    </form>
                </div>
            </div>
            @if(isset($results))
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Affected Users</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Profit Subtracted</th>
                                    <th>Bonus Subtracted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $userId => $data)
                                    <tr>
                                        <td>{{ $users[$userId]->username ?? $userId }}</td>
                                        <td>{{ $users[$userId]->email ?? '-' }}</td>
                                        <td>{{ $data['profit'] ?? '-' }}</td>
                                        <td>{{ $data['bonus'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
