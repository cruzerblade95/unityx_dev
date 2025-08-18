@extends('user.layouts.layoutMaster')

@section('title', 'Investments')

@section('content')
<div class="container py-4">
    <h2>Investments</h2>
    <a href="javascript:;" class="btn btn-success mb-3" id="activate-investment">Activate Investment</a>
    <div id="activate-message" class="alert alert-info d-none">Investment activation requested! (This is a placeholder, actual insert will be by cron)</div>
    <hr>
    <h3>Investment List</h3>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Transaction ID</th>
                <th>Status</th>
                <th>PNL</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($investments as $investment)
                <tr>
                    <td>{{ $investment->id }}</td>
                    <td>{{ $investment->transaction_id }}</td>
                    <td>{{ $investment->status }}</td>
                    <td>{{ $investment->pnl }}</td>
                    <td>{{ $investment->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('page-script')
<script>
document.getElementById('activate-investment').addEventListener('click', function() {
    document.getElementById('activate-message').classList.remove('d-none');
});
</script>
@endsection
