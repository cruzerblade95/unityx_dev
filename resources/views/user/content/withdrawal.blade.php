@extends('user.layouts.layoutMaster')

@section('title', 'Withdrawal')

@section('content')
<div class="container py-4">
    <h2>Withdrawal</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('user.withdrawal.store') }}">
        @csrf
        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
        </div>
        <div class="mb-3">
            <label for="reference" class="form-label">Reference</label>
            <input type="text" class="form-control" id="reference" name="reference">
        </div>
        <div class="mb-3">
            <label for="note" class="form-label">Note</label>
            <textarea class="form-control" id="note" name="note"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create Withdrawal</button>
    </form>
    <hr>
    <h3>Withdrawal List</h3>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Amount</th>
                <th>Tax %</th>
                <th>Tax Amount</th>
                <th>Net Amount</th>
                <th>Status</th>
                <th>Reference</th>
                <th>Note</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($withdrawals as $withdrawal)
                <tr>
                    <td>{{ $withdrawal->id }}</td>
                    <td>{{ $withdrawal->amount }}</td>
                    <td>{{ $withdrawal->tax_percent }}</td>
                    <td>{{ $withdrawal->tax_amount }}</td>
                    <td>{{ $withdrawal->net_amount }}</td>
                    <td>{{ $withdrawal->status }}</td>
                    <td>{{ $withdrawal->reference }}</td>
                    <td>{{ $withdrawal->note }}</td>
                    <td>{{ $withdrawal->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
