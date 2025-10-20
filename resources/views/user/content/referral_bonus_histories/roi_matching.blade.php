@extends('user/layouts/layoutMaster')

@section('title', 'ROI Matching Bonus History')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Summary Statistics -->
        <div class="col-12 mb-4">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title mb-0">Total Bonuses</h6>
                                    <h3 class="mb-0">{{ number_format($stats->total_count ?? 0) }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-list-alt fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title mb-0">Total Amount</h6>
                                    <h3 class="mb-0">${{ number_format($stats->total_amount ?? 0, 8) }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title mb-0">Average Bonus</h6>
                                    <h3 class="mb-0">${{ number_format($stats->average_amount ?? 0, 8) }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title mb-0">Highest Bonus</h6>
                                    <h3 class="mb-0">${{ number_format($stats->highest_amount ?? 0, 8) }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-trophy fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Level Distribution -->
        @if($levelStats && $levelStats->count() > 0)
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Bonus Distribution by Level</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th>Count</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($levelStats as $level)
                                <tr>
                                    <td><span class="badge bg-primary">Level {{ $level->level }}</span></td>
                                    <td>{{ number_format($level->count) }}</td>
                                    <td>${{ number_format($level->total_amount, 8) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Monthly Breakdown -->
        @if($monthlyStats && $monthlyStats->count() > 0)
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">Recent Monthly Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Count</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyStats as $month)
                                <tr>
                                    <td>{{ date('M Y', strtotime($month->month . '-01')) }}</td>
                                    <td>{{ number_format($month->count) }}</td>
                                    <td>${{ number_format($month->total_amount, 8) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Transaction History -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">ROI Matching Bonus History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>From User</th>
                                    <th>Amount</th>
                                    <th>Level</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roiMatching as $bonus)
                                    <tr>
                                        <td>{{ $bonus->created_at }}</td>
                                        <td>{{ optional(App\Models\User::find($bonus->from_user_id))->username ?? $bonus->from_user_id }}</td>
                                        <td>{{ number_format($bonus->amount, 8) }}</td>
                                        <td>{{ $bonus->level ?? '-' }}</td>
                                        <td>{{ $bonus->note ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No ROI matching bonus history found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $roiMatching->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
