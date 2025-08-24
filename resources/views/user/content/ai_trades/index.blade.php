@extends('user/layouts/layoutMaster')

@section('title', 'Ai Trades')

@section('vendor-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleave-zen/cleave-zen.js'])
@endsection

@section('page-script')
@vite('resources/assets/js/app-user-list.js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('ai-trade-toggle-btn');
    if (btn) {
        btn.addEventListener('click', function() {
            btn.disabled = true;
            fetch("{{ route('user.ai_trade_activate.toggle') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 1) {
                    document.getElementById('ai-trade-status').innerHTML = '<span class="text-success">Activated</span>';
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-danger');
                    btn.textContent = 'Deactivate';
                } else {
                    document.getElementById('ai-trade-status').innerHTML = '<span class="text-danger">Not Activated</span>';
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-success');
                    btn.textContent = 'Activate';
                }
                btn.disabled = false;
                Swal.fire({
                    icon: 'success',
                    title: data.message,
                    timer: 1200,
                    showConfirmButton: false
                });
            })
            .catch(() => {
                btn.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not update activation status.'
                });
            });
        });
    }
});
</script>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">AI Trade Activation</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mt-3">
                    <span>Status:
                        <span id="ai-trade-status" class="fw-bold">
                            @if(isset($aiTradeActivate) && $aiTradeActivate->status)
                                <span class="text-success">Activated</span>
                            @else
                                <span class="text-danger">Not Activated</span>
                            @endif
                        </span>
                    </span>
                    <button id="ai-trade-toggle-btn" class="btn btn-{{ (isset($aiTradeActivate) && $aiTradeActivate->status) ? 'danger' : 'success' }}">
                        {{ (isset($aiTradeActivate) && $aiTradeActivate->status) ? 'Deactivate' : 'Activate' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row g-6 mb-6">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">AI Trades - Running</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive m-5">
                    <table class="table table-dark table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Amount Invest</th>
                                <th>Current Profit</th>
                                <th>Ending Profit</th>
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
                </div>
                <div class="d-flex justify-content-center p-3">
                    {{ $tradesRunning->appends([
                        'ended_page' => request('ended_page'),
                        'cancelled_page' => request('cancelled_page')
                    ])->links() }}
                </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">AI Trades - Ended</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive m-5">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Amount Invest</th>
                                <th>Current Profit</th>
                                <th>Ending Profit</th>
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
                </div>
                <div class="d-flex justify-content-center p-3">
                    {{ $tradesEnded->appends(['cancelled_page' => request('cancelled_page')])->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">AI Trades - Cancelled</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive m-5">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Amount Invest</th>
                                <th>Current Profit</th>
                                <th>Ending Profit</th>
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
                <div class="d-flex justify-content-center p-3">
                    {{ $tradesCancelled->appends(['ended_page' => request('ended_page')])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
