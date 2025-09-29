<!-- Withdrawal History -->
<div class="card">
    <div class="card-header">
        <h4>Withdrawal History</h4>
    </div>
    <div class="card-body">
        @if ($withdrawals->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped withdrawal-history-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Wallet</th>
                            <th>Amount</th>
                            <th>Fee</th>
                            <th>Net Amount</th>
                            <th>Payment Details</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($withdrawals as $withdrawal)
                            <tr>
                                <td class="font-monospace">#{{ $withdrawal->id }}</td>
                                <td>
                                    @if ($withdrawal->withdrawal_type === 'crypto')
                                        <span class="badge bg-success type-badge">
                                            <i class="fab fa-bitcoin me-1"></i>Crypto
                                        </span>
                                    @elseif($withdrawal->withdrawal_type === 'etc_black')
                                        <span class="badge type-badge" style="background-color: rgba(111, 66, 193, 1);">
                                            <i class="fas fa-credit-card me-1"></i>Crypto Bank Card
                                        </span>
                                    @elseif($withdrawal->withdrawal_type === 'gold')
                                        <span class="badge bg-warning type-badge">
                                            <i class="fas fa-coins me-1"></i>Gold
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($withdrawal->wallet_type === 'profit')
                                        <span class="badge bg-primary">
                                            <i class="fas fa-chart-line me-1"></i>Profit
                                        </span>
                                    @elseif($withdrawal->wallet_type === 'bonus')
                                        <span class="badge bg-info">
                                            <i class="fas fa-gift me-1"></i>Bonus
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-question me-1"></i>Legacy
                                        </span>
                                    @endif
                                </td>
                                <td class="amount-cell">${{ number_format($withdrawal->amount, 2) }}</td>
                                <td class="text-muted">
                                    {{ $withdrawal->tax_percent }}%
                                    <br>
                                    <small>${{ number_format($withdrawal->tax_amount, 2) }}</small>
                                </td>
                                <td class="amount-cell text-success">${{ number_format($withdrawal->net_amount, 2) }}
                                </td>
                                <td class="payment-details">
                                    @if ($withdrawal->withdrawal_type === 'crypto')
                                        <div class="small">
                                            <strong>{{ $withdrawal->bank_name ?? 'N/A' }}</strong><br>
                                            <span
                                                class="font-monospace">{{ \Illuminate\Support\Str::limit($withdrawal->account_number ?? 'N/A', 20) }}</span>
                                        </div>
                                    @elseif($withdrawal->withdrawal_type === 'etc_black')
                                        @php
                                            $paymentDetails = is_string($withdrawal->payment_details)
                                                ? json_decode($withdrawal->payment_details, true)
                                                : $withdrawal->payment_details;
                                        @endphp
                                        <div class="small">
                                            <strong>Crypto Bank Card</strong><br>
                                            Card:
                                            ****{{ substr($paymentDetails['etc_black_card_number'] ?? 'N/A', -4) }}<br>
                                            {{ $paymentDetails['cardholder_name'] ?? 'N/A' }}
                                        </div>
                                    @elseif($withdrawal->withdrawal_type === 'gold')
                                        @php
                                            $paymentDetails = is_string($withdrawal->payment_details)
                                                ? json_decode($withdrawal->payment_details, true)
                                                : $withdrawal->payment_details;
                                        @endphp
                                        <div class="small">
                                            <strong>Gold Redemption</strong><br>
                                            ID: {{ $paymentDetails['national_id'] ?? 'N/A' }}<br>
                                            @if ($withdrawal->branch_name && $withdrawal->branch_selected)
                                                <strong class="text-success">Branch:
                                                    {{ $withdrawal->branch_name }}</strong><br>
                                                @if ($withdrawal->branch_manager_name)
                                                    Manager: {{ $withdrawal->branch_manager_name }}<br>
                                                    Phone: {{ $withdrawal->branch_manager_phone }}
                                                @endif
                                            @elseif ($withdrawal->status === 'approved')
                                                <em class="text-info">Branch being assigned...</em>
                                            @else
                                                <em class="text-muted">Awaiting approval</em>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="status-cell">
                                    @if ($withdrawal->status === 'pending')
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Pending
                                        </span>
                                    @elseif($withdrawal->status === 'approved')
                                        @if ($withdrawal->withdrawal_type === 'gold' && !$withdrawal->branch_selected)
                                            <span class="badge bg-info">
                                                <i class="fas fa-cog me-1"></i>Processing
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Approved
                                            </span>
                                        @endif
                                    @elseif($withdrawal->status === 'rejected')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i>Rejected
                                        </span>
                                        @if ($withdrawal->rejection_reason)
                                            <br>
                                            <small class="text-muted mt-1" style="display: block;">
                                                <i class="fas fa-info-circle me-1"></i>
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectionModal{{ $withdrawal->id }}">
                                                    View Reason
                                                </button>
                                            </small>
                                        @endif
                                    @elseif($withdrawal->status === 'completed')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-double me-1"></i>Completed
                                        </span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $withdrawal->created_at->format('M j, Y') }}<br>
                                    {{ $withdrawal->created_at->format('g:i A') }}
                                </td>
                                <td>
                                    @if ($withdrawal->withdrawal_type === 'gold' && $withdrawal->status === 'approved' && $withdrawal->branch_selected)
                                        <!-- Receipt Download Button for Gold Withdrawals -->
                                        <a href="{{ route('user.withdrawal.receipt', $withdrawal->id) }}"
                                            class="btn btn-sm btn-success" target="_blank">
                                            <i class="fas fa-receipt me-1"></i>Download Receipt
                                        </a>
                                    @elseif ($withdrawal->withdrawal_type === 'gold' && $withdrawal->status === 'approved' && !$withdrawal->branch_selected)
                                        <!-- Gold withdrawal approved but no branch assigned yet -->
                                        <span class="badge bg-info">
                                            <i class="fas fa-clock me-1"></i>Awaiting Branch Assignment
                                        </span>
                                    @elseif ($withdrawal->status === 'rejected' && $withdrawal->rejection_reason)
                                        <!-- View Rejection Reason Button -->
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectionModal{{ $withdrawal->id }}">
                                            <i class="fas fa-exclamation-circle me-1"></i>View Reason
                                        </button>
                                    @else
                                        <span class="text-muted small">No actions</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Rejection Reason Modals -->
            @foreach ($withdrawals as $withdrawal)
                @if ($withdrawal->status === 'rejected' && $withdrawal->rejection_reason)
                    <div class="modal fade" id="rejectionModal{{ $withdrawal->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        Withdrawal Rejection Details
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <h6 class="fw-bold">Withdrawal Information:</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Withdrawal ID:</small>
                                                <div class="fw-semibold">#{{ $withdrawal->id }}</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Amount:</small>
                                                <div class="fw-semibold">${{ number_format($withdrawal->amount, 2) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6">
                                                <small class="text-muted">Date:</small>
                                                <div class="fw-semibold">
                                                    {{ $withdrawal->created_at->format('M j, Y g:i A') }}</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Type:</small>
                                                <div class="fw-semibold text-capitalize">
                                                    {{ $withdrawal->withdrawal_type }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="mb-3">
                                        <h6 class="fw-bold text-danger">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Rejection Reason:
                                        </h6>
                                        <div class="alert alert-danger">
                                            <div style="white-space: pre-wrap;">{{ $withdrawal->rejection_reason }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-info">
                                        <small>
                                            <i class="fas fa-info-circle me-1"></i>
                                            The withdrawal amount has been returned to your profit balance.
                                            You can submit a new withdrawal request after addressing the issues
                                            mentioned above.
                                        </small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="text-center py-4">
                <i class="fas fa-history text-muted fa-3x mb-3"></i>
                <p class="text-muted">No withdrawal requests found.</p>
            </div>
        @endif
    </div>
</div>
