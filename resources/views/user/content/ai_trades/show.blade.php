@extends('user/layouts/layoutMaster')

@section('title', 'AI Trades')

@section('content')
<div class="container py-4">
    <h2>AI Trade #{{ $trade->id }} Detail</h2>
    <div class="mb-4">
        <strong>Amount Invest:</strong> {{ $trade->amount_invest }}<br>
        <strong>Status:</strong> 
        @if($trade->trade_status == 1)
            <span class="badge bg-success">Running</span>
        @elseif($trade->trade_status == 2)
            <span class="badge bg-primary">Ended</span>
        @else
            <span class="badge bg-danger">Cancelled</span>
        @endif
        <br>
        <strong>Current Amount:</strong> {{ $trade->current_amount }}<br>
        <strong>Ending Amount:</strong> {{ $trade->ending_amount }}<br>
    </div>
    <h4>Trade Histories</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Amount</th>
                <th>PNL</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($histories as $history)
                <tr>
                    <td>{{ $history->id }}</td>
                    <td>{{ $history->amount }}</td>
                    <td>{{ $history->pnl }}</td>
                    <td>{{ $history->description }}</td>
                    <td>{{ $history->created_at }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No trade history found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $histories->links() }}
    </div>
    <a href="{{ route('user.ai_trades.index') }}" class="btn btn-secondary mt-3">Back to Trades</a>
</div>
@endsection
