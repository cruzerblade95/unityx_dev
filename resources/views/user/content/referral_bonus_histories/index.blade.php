@extends('user/layouts/layoutMaster')

@section('title', 'Referral Bonus History')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Overall Summary Statistics -->
        <div class="col-12 mb-4">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <h5 class="card-title mb-3">Overall Referral Bonus Summary</h5>
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h3 class="mb-1">{{ number_format($totalStats->total_count ?? 0) }}</h3>
                            <p class="mb-0">Total Bonuses</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h3 class="mb-1">${{ number_format($totalStats->total_amount ?? 0, 8) }}</h3>
                            <p class="mb-0">Total Amount</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h3 class="mb-1">${{ number_format($totalStats->average_amount ?? 0, 8) }}</h3>
                            <p class="mb-0">Average Bonus</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deposit Bonus Statistics -->
        <div class="col-12 mb-4">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title mb-0">Deposit Bonuses</h6>
                                    <h3 class="mb-0">{{ number_format($depositStats->total_count ?? 0) }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-coins fa-2x opacity-75"></i>
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
                                    <h6 class="card-title mb-0">Total Deposit Bonus</h6>
                                    <h3 class="mb-0">${{ number_format($depositStats->total_amount ?? 0, 8) }}</h3>
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
                                    <h6 class="card-title mb-0">Avg Deposit Bonus</h6>
                                    <h3 class="mb-0">${{ number_format($depositStats->average_amount ?? 0, 8) }}</h3>
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
                                    <h6 class="card-title mb-0">Highest Deposit</h6>
                                    <h3 class="mb-0">${{ number_format($depositStats->highest_amount ?? 0, 8) }}</h3>
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

        <!-- Level Distribution and Monthly Stats -->
        <div class="col-lg-6 mb-4">
            @if($depositLevelStats && $depositLevelStats->count() > 0)
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Deposit Bonus by Level</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th>Count</th>
                                    <th>Total Amount</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($depositLevelStats as $level)
                                <tr>
                                    <td><span class="badge bg-primary">Level {{ $level->level }}</span></td>
                                    <td>{{ number_format($level->count) }}</td>
                                    <td>${{ number_format($level->total_amount, 8) }}</td>
                                    <td>{{ $depositStats->total_amount > 0 ? number_format(($level->total_amount / $depositStats->total_amount) * 100, 2) : 0 }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-6 mb-4">
            @if($depositMonthlyStats && $depositMonthlyStats->count() > 0)
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">Monthly Deposit Bonus Trend</h6>
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
                                @foreach($depositMonthlyStats as $month)
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
            @endif
        </div>
        </div>

        <!-- Deposit Bonus History Table -->
        {{-- <div class="col-12 mb-4">
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
        </div> --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Deposit Bonus History</h5>
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
                                    {{-- <th>Description</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposit as $bonus)
                                    <tr>
                                        <td>{{ $bonus->created_at }}</td>
                                        <td>{{ optional(App\Models\User::find($bonus->from_user_id))->username ?? $bonus->from_user_id }}</td>
                                        <td>{{ number_format($bonus->amount, 8) }}</td>
                                        <td>{{ $bonus->level ?? '-' }}</td>
                                        {{-- <td>{{ $bonus->description ?? '-' }}</td> --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No deposit bonus history found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $deposit->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
