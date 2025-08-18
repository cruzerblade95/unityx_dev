@extends('user.layouts.layoutMaster')

@section('title', 'Deposit')

@section('content')
@include('components.coming-soon-overlay')
<div class="container py-4 position-relative" style="z-index: 1;">
    <h2>Deposit</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('user.deposit.store') }}">
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
        <button type="submit" class="btn btn-primary">Create Deposit</button>
    </form>
    <hr>
    <h3>Deposit List</h3>
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
            @foreach($deposits as $deposit)
                <tr>
                    <td>{{ $deposit->id }}</td>
                    <td>{{ $deposit->amount }}</td>
                    <td>{{ $deposit->tax_percent }}</td>
                    <td>{{ $deposit->tax_amount }}</td>
                    <td>{{ $deposit->net_amount }}</td>
                    <td>{{ $deposit->status }}</td>
                    <td>{{ $deposit->reference }}</td>
                    <td>{{ $deposit->note }}</td>
                    <td>{{ $deposit->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
