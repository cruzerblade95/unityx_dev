@extends('user/layouts/layoutMaster')

@section('title', 'Referral Bonus History')

@section('content')
<div class="container-fluid">
    <div class="row">
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
                                        <td>{{ number_format($bonus->amount, 2) }}</td>
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
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
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
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposit as $bonus)
                                    <tr>
                                        <td>{{ $bonus->created_at }}</td>
                                        <td>{{ optional(App\Models\User::find($bonus->from_user_id))->username ?? $bonus->from_user_id }}</td>
                                        <td>{{ number_format($bonus->amount, 2) }}</td>
                                        <td>{{ $bonus->level ?? '-' }}</td>
                                        <td>{{ $bonus->description ?? '-' }}</td>
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
