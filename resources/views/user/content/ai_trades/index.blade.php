@extends('user/layouts/layoutMaster')

@section('title', 'Ai Trades')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">AI Trades - Running</h2>
    <table class="table table-dark table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Amount Invest</th>
                
                <th>Current Amount</th>
                <th>Ending Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tradesRunning as $trade)
                <tr>
                    <td>{{ $trade->id }}</td>
                    <td>{{ $trade->amount_invest }}</td>
                    
                    <td>{{ $trade->current_amount }}</td>
                    <td>{{ $trade->ending_amount }}</td>
                    <td>
                        <a href="{{ route('user.ai_trades.show', $trade->id) }}" class="btn btn-info btn-sm">Detail</a>
                        <form action="{{ route('user.ai_trades.cancel', $trade->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Cancel this trade?')">Cancel</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No running trades.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2 class="mb-4 mt-5">AI Trades - Ended</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Amount Invest</th>
                
                <th>Current Amount</th>
                <th>Ending Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tradesEnded as $trade)
                <tr>
                    <td>{{ $trade->id }}</td>
                    <td>{{ $trade->amount_invest }}</td>
                    
                    <td>{{ $trade->current_amount }}</td>
                    <td>{{ $trade->ending_amount }}</td>
                    <td>
                        <a href="{{ route('user.ai_trades.show', $trade->id) }}" class="btn btn-info btn-sm">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No ended trades.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2 class="mb-4 mt-5">AI Trades - Cancelled</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Amount Invest</th>
                
                <th>Current Amount</th>
                <th>Ending Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tradesCancelled as $trade)
                <tr>
                    <td>{{ $trade->id }}</td>
                    <td>{{ $trade->amount_invest }}</td>

                    <td>{{ $trade->current_amount }}</td>
                    <td>{{ $trade->ending_amount }}</td>
                    <td>
                        <a href="{{ route('user.ai_trades.show', $trade->id) }}" class="btn btn-info btn-sm">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No cancelled trades.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
