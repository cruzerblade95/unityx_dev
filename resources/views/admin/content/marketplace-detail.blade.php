@extends('admin/layouts/layoutMaster')

@section('title', 'Transaction Details')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-6">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Transaction Details</h5>
                            <p class="text-muted mb-0">ID: {{ $transaction->unique_id }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.marketplace.index') }}" class="btn btn-outline-secondary">
                                <i class="ti tabler-arrow-left me-1"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Transaction Overview -->
            <div class="col-lg-8 col-12 mb-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Transaction Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="ps-0 fw-medium">Transaction ID:</td>
                                        <td class="pe-0">
                                            <span class="badge bg-label-primary">{{ $transaction->unique_id }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0 fw-medium">Status:</td>
                                        <td class="pe-0">
                                            @switch($transaction->status)
                                                @case('completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @break

                                                @case('pending_verification')
                                                    <span class="badge bg-warning">Pending Verification</span>
                                                @break

                                                @case('cancelled')
                                                    <span class="badge bg-danger">Cancelled</span>
                                                @break

                                                @case('expired')
                                                    <span class="badge bg-secondary">Expired</span>
                                                @break

                                                @default
                                                    <span
                                                        class="badge bg-light text-dark">{{ ucfirst($transaction->status) }}</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0 fw-medium">USDT Amount:</td>
                                        <td class="pe-0 fw-medium text-primary">{{ number_format($transaction->amount, 2) }}
                                            USDT</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0 fw-medium">Price per USDT:</td>
                                        <td class="pe-0">${{ number_format($transaction->price_per_usdt, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0 fw-medium">Total Price:</td>
                                        <td class="pe-0 fw-medium">${{ number_format($transaction->total_price, 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="ps-0 fw-medium">Tax Rate:</td>
                                        <td class="pe-0">1.8%</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0 fw-medium">Tax Amount:</td>
                                        <td class="pe-0 text-warning fw-medium">
                                            ${{ number_format($transaction->total_price * 0.018, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0 fw-medium">Net Amount:</td>
                                        <td class="pe-0 text-success fw-medium">
                                            ${{ number_format($transaction->total_price - $transaction->total_price * 0.018, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0 fw-medium">Created:</td>
                                        <td class="pe-0">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0 fw-medium">Last Updated:</td>
                                        <td class="pe-0">{{ $transaction->updated_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="col-lg-4 col-12 mb-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Transaction Timeline</h5>
                    </div>
                    <div class="card-body">
                        <ul class="timeline mb-0">
                            <li class="timeline-item timeline-item-transparent">
                                <span class="timeline-point timeline-point-primary"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-3">
                                        <h6 class="mb-0">Listing Created</h6>
                                        <small
                                            class="text-muted">{{ $transaction->created_at->format('M d, Y H:i') }}</small>
                                    </div>
                                    <p class="mb-2">Seller created USDT listing</p>
                                </div>
                            </li>

                            @if ($transaction->verification_started_at)
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-info"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-3">
                                            <h6 class="mb-0">Verification Started</h6>
                                            <small
                                                class="text-muted">{{ \Carbon\Carbon::parse($transaction->verification_started_at)->format('M d, Y H:i') }}</small>
                                        </div>
                                        <p class="mb-2">Buyer initiated verification process</p>
                                    </div>
                                </li>
                            @endif

                            @if ($transaction->status == 'completed')
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-success"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-3">
                                            <h6 class="mb-0">Transaction Completed</h6>
                                            <small
                                                class="text-muted">{{ $transaction->updated_at->format('M d, Y H:i') }}</small>
                                        </div>
                                        <p class="mb-2">Seller approved the transaction</p>
                                    </div>
                                </li>
                            @elseif($transaction->status == 'cancelled')
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-danger"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-3">
                                            <h6 class="mb-0">Transaction Cancelled</h6>
                                            <small
                                                class="text-muted">{{ $transaction->updated_at->format('M d, Y H:i') }}</small>
                                        </div>
                                        <p class="mb-2">
                                            {{ $transaction->transaction_reason ?? 'Transaction was cancelled' }}</p>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Buyer Information -->
            <div class="col-lg-6 col-12 mb-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ti tabler-user me-2"></i>Buyer Information
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($transaction->buyer)
                            <div class="d-flex align-items-center mb-4">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary fs-4">
                                        {{ strtoupper(substr($transaction->buyer->username, 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $transaction->buyer->username }}</h6>
                                    <small class="text-muted">{{ $transaction->buyer->firstname }}
                                        {{ $transaction->buyer->lastname }}</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="ps-0 fw-medium">Email:</td>
                                            <td class="pe-0">{{ $transaction->buyer->email }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-0 fw-medium">Telegram Username:</td>
                                            <td class="pe-0">
                                                @if ($transaction->buyer->telegramVerification && $transaction->buyer->telegramVerification->telegram_username)
                                                    <span class="badge bg-label-success">@{{ $transaction - > buyer - > telegramVerification - > telegram_username }}</span>
                                                @else
                                                    <span class="text-muted">Not connected</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-0 fw-medium">Joined:</td>
                                            <td class="pe-0">{{ $transaction->buyer->created_at->format('M d, Y') }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ti tabler-user-off ti-48px text-muted mb-2"></i>
                                <p class="text-muted">No buyer assigned</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Seller Information -->
            <div class="col-lg-6 col-12 mb-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ti tabler-user-check me-2"></i>Seller Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded-circle bg-label-info fs-4">
                                    {{ strtoupper(substr($transaction->seller->username, 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $transaction->seller->username }}</h6>
                                <small class="text-muted">{{ $transaction->seller->firstname }}
                                    {{ $transaction->seller->lastname }}</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="ps-0 fw-medium">Email:</td>
                                        <td class="pe-0">{{ $transaction->seller->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0 fw-medium">Telegram Username:</td>
                                        <td class="pe-0">
                                            @if ($transaction->seller->telegramVerification && $transaction->seller->telegramVerification->telegram_username)
                                                <span class="badge bg-label-success">@{{ $transaction - > seller - > telegramVerification - > telegram_username }}</span>
                                            @else
                                                <span class="text-muted">Not connected</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0 fw-medium">Joined:</td>
                                        <td class="pe-0">{{ $transaction->seller->created_at->format('M d, Y') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
