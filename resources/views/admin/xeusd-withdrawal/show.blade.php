@extends('admin/layouts/layoutMaster')

@section('title', 'XEUSD Withdrawal Details')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">XEUSD Withdrawal Details</h4>
                            <p class="text-muted mb-0">Withdrawal ID: {{ $withdrawal->unique_id }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.xeusd_withdrawals.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row mt-4">
            <!-- Withdrawal Information -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Withdrawal Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Withdrawal ID:</label>
                                <div class="text-muted">{{ $withdrawal->unique_id }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <div>{!! $withdrawal->getStatusBadge() !!}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Amount USD:</label>
                                <div class="text-muted">
                                    <strong>${{ number_format($withdrawal->amount_usd, 2) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Amount XEUSD:</label>
                                <div class="text-muted">{{ number_format($withdrawal->amount_xeusd, 8) }} XEUSD</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Source Wallet:</label>
                                <div class="text-muted">
                                    <span class="badge bg-info">{{ ucfirst($withdrawal->source_wallet) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Request Date:</label>
                                <div class="text-muted">{{ $withdrawal->created_at->format('M d, Y H:i:s') }}</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Recipient Address:</label>
                                <div class="text-muted">
                                    <code>{{ $withdrawal->recipient_address }}</code>
                                    <button type="button" class="btn btn-sm btn-outline-primary ms-2"
                                        onclick="copyToClipboard('{{ $withdrawal->recipient_address }}')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if ($withdrawal->transaction_hash)
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Transaction Hash:</label>
                                    <div class="text-muted">
                                        <code>{{ $withdrawal->transaction_hash }}</code>
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2"
                                            onclick="copyToClipboard('{{ $withdrawal->transaction_hash }}')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <a href="https://bscscan.com/tx/{{ $withdrawal->transaction_hash }}"
                                            target="_blank" class="btn btn-sm btn-success ms-2">
                                            <i class="fas fa-external-link-alt"></i> View on BSCScan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($withdrawal->admin_notes)
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Admin Notes:</label>
                                    <div class="alert alert-info">
                                        {{ $withdrawal->admin_notes }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- User Information & Actions -->
            <div class="col-lg-4">
                <!-- User Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>User Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    {{ strtoupper(substr($withdrawal->user->username, 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $withdrawal->user->username }}</h6>
                                <small class="text-muted">{{ $withdrawal->user->email }}</small>
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">User ID:</small>
                            <div>{{ $withdrawal->user->id }}</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Current Balance:</small>
                            <div>${{ number_format($withdrawal->user->wallet->profit_wallet ?? 0, 2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                @if ($withdrawal->status === 'pending')
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cogs me-2"></i>Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Auto Process Form -->
                            <form id="autoProcessForm"
                                action="{{ route('admin.xeusd_withdrawals.approve', $withdrawal->id) }}" method="POST"
                                class="mb-3">
                                @csrf
                                <input type="hidden" name="auto_process" value="1">
                                <div class="mb-3">
                                    <label for="admin_notes_auto" class="form-label">Admin Notes (Optional)</label>
                                    <textarea class="form-control" id="admin_notes_auto" name="admin_notes" rows="3"
                                        placeholder="Optional notes for this approval..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-rocket me-1"></i> Auto-Process via Blockchain
                                </button>
                                <small class="text-muted">This will automatically send XEUSD tokens from the owner
                                    wallet</small>
                            </form>

                            <hr class="my-3">
                            <div class="text-center mb-2">
                                <small class="text-muted">OR</small>
                            </div>

                            <!-- Manual Approval Form -->
                            <form id="manualApproveForm"
                                action="{{ route('admin.xeusd_withdrawals.approve', $withdrawal->id) }}" method="POST"
                                class="mb-3">
                                @csrf
                                <input type="hidden" name="auto_process" value="0">
                                <div class="mb-3">
                                    <label for="transaction_hash" class="form-label">Transaction Hash *</label>
                                    <input type="text" class="form-control" id="transaction_hash"
                                        name="transaction_hash" placeholder="0x...">
                                    <div class="form-text">Enter existing BSC transaction hash if already processed
                                        manually</div>
                                </div>
                                <div class="mb-3">
                                    <label for="admin_notes_manual" class="form-label">Notes (Optional)</label>
                                    <textarea class="form-control" id="admin_notes_manual" name="admin_notes" rows="3"
                                        placeholder="Additional notes..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check me-1"></i> Manual Approval
                                </button>
                            </form>

                            <button type="button" class="btn btn-danger w-100" onclick="showRejectModal()">
                                <i class="fas fa-times me-1"></i> Reject Withdrawal
                            </button>
                        </div>
                    </div>
                @elseif($withdrawal->status === 'processing')
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cogs me-2"></i>Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-clock me-1"></i>
                                This withdrawal is currently being processed.
                            </div>

                            <form action="{{ route('admin.xeusd_withdrawals.approve', $withdrawal->id) }}" method="POST"
                                class="mb-3">
                                @csrf
                                <div class="mb-3">
                                    <label for="transaction_hash" class="form-label">Transaction Hash *</label>
                                    <input type="text" class="form-control" id="transaction_hash"
                                        name="transaction_hash" required placeholder="0x..."
                                        value="{{ $withdrawal->transaction_hash }}">
                                </div>
                                <div class="mb-3">
                                    <label for="admin_notes" class="form-label">Notes (Optional)</label>
                                    <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3"
                                        placeholder="Additional notes...">{{ $withdrawal->admin_notes }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check me-1"></i> Complete Withdrawal
                                </button>
                            </form>

                            <button type="button" class="btn btn-danger w-100" onclick="showRejectModal()">
                                <i class="fas fa-times me-1"></i> Reject Withdrawal
                            </button>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                @if ($withdrawal->status === 'completed')
                                    <i class="fas fa-check-circle text-success fa-3x"></i>
                                    <h5 class="mt-2 text-success">Withdrawal Completed</h5>
                                @elseif($withdrawal->status === 'rejected')
                                    <i class="fas fa-times-circle text-danger fa-3x"></i>
                                    <h5 class="mt-2 text-danger">Withdrawal Rejected</h5>
                                @endif
                            </div>
                            @if ($withdrawal->processedBy)
                                <p class="text-muted mb-0">
                                    Processed by: <strong>{{ $withdrawal->processedBy->username }}</strong><br>
                                    <small>{{ $withdrawal->processed_at ? $withdrawal->processed_at->format('M d, Y H:i:s') : 'N/A' }}</small>
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Withdrawal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.xeusd_withdrawals.reject', $withdrawal->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reject_reason" class="form-label">Reason for Rejection *</label>
                            <textarea class="form-control" id="reject_reason" name="reject_reason" rows="4" required
                                placeholder="Please provide a reason for rejecting this withdrawal..."></textarea>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Warning:</strong> Rejecting this withdrawal will return the amount to the user's profit
                            wallet.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i> Reject Withdrawal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('page-script')
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Text copied to clipboard',
                    timer: 1500,
                    showConfirmButton: false
                });
            }).catch(function(err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to copy text'
                });
            });
        }

        function showRejectModal() {
            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
        }

        // Handle auto-process form submission
        document.getElementById('autoProcessForm')?.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Auto-Process Withdrawal?',
                html: 'This will automatically send <strong>${{ number_format($withdrawal->amount_usd, 2) }} XEUSD</strong> to:<br><code>{{ $withdrawal->recipient_address }}</code><br><br>Are you sure you want to proceed?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, process it!',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        }
                    }).then(response => response.json());
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            html: result.value.message + (result.value.transaction_hash ?
                                '<br><small>TX: ' + result.value.transaction_hash + '</small>' :
                                ''),
                            timer: 5000,
                            showConfirmButton: true
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: result.value.message
                        });
                    }
                }
            });
        });

        // Handle manual approval form submission
        document.getElementById('manualApproveForm')?.addEventListener('submit', function(e) {
            e.preventDefault();

            const txHash = document.getElementById('transaction_hash').value;
            if (!txHash || !txHash.match(/^0x[a-fA-F0-9]{64}$/)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Transaction Hash',
                    text: 'Please enter a valid BSC transaction hash'
                });
                return;
            }

            Swal.fire({
                title: 'Manual Approval',
                html: 'Approve withdrawal with transaction hash:<br><code>' + txHash + '</code>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        // Success/Error messages
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}'
            });
        @endif
    </script>
@endsection
