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
                                            <strong>Gold Redeem</strong><br>
                                            ID: {{ $paymentDetails['national_id'] ?? 'N/A' }}<br>
                                            @if ($withdrawal->branch_name)
                                                Branch: {{ $withdrawal->branch_name }}
                                            @else
                                                <em class="text-muted">Branch not selected</em>
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
                                                <i class="fas fa-map-marker-alt me-1"></i>Select Branch
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
                                    @if ($withdrawal->withdrawal_type === 'gold' && $withdrawal->status === 'approved' && !$withdrawal->branch_selected)
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#branchModal{{ $withdrawal->id }}">
                                            <i class="fas fa-map-marker-alt me-1"></i>Select Branch
                                        </button>

                                        <!-- Branch Selection Modal -->
                                        <div class="modal fade" id="branchModal{{ $withdrawal->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Select Musleem Gold Branch</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST"
                                                        action="{{ route('user.withdrawal.select-branch', $withdrawal->id) }}">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>Please select your preferred Musleem Gold branch for
                                                                pickup:</p>
                                                            <div class="mb-3">
                                                                <label class="form-label">Branch Location <span
                                                                        class="text-danger">*</span></label>
                                                                <select class="form-control" name="branch_name"
                                                                    required>
                                                                    <option value="">Select a branch...</option>
                                                                    <option value="SHAH ALAM">SHAH ALAM</option>
                                                                    <option value="BANGI">BANGI</option>
                                                                    <option value="BANTING">BANTING</option>
                                                                    <option value="ALOR SETAR">ALOR SETAR</option>
                                                                    <option value="JELAWAT">JELAWAT</option>
                                                                    <option value="KOTA TINGGI">KOTA TINGGI</option>
                                                                    <option value="BANDAR BARU UDA">BANDAR BARU UDA
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div class="alert alert-info">
                                                                <small>
                                                                    <i class="fas fa-info-circle me-1"></i>
                                                                    Once you select a branch, you'll receive further
                                                                    instructions for pickup.
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Confirm
                                                                Branch</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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
