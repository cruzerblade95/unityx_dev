<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gold Withdrawal Receipt #{{ $withdrawal->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }

            .container {
                max-width: 100% !important;
            }
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .receipt-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 800px;
            padding: 40px;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 3px solid #ffd700;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }

        .company-logo {
            font-size: 2.5rem;
            color: #ffd700;
            margin-bottom: 10px;
        }

        .receipt-title {
            color: #2c3e50;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .receipt-number {
            background: linear-gradient(135deg, #ffd700, #ffed4a);
            color: #2c3e50;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            display: inline-block;
        }

        .section-title {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 15px;
            margin: 30px 0 20px 0;
            border-radius: 8px;
            font-weight: bold;
        }

        .info-row {
            padding: 12px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .info-value {
            color: #34495e;
        }

        .amount-highlight {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }

        .branch-highlight {
            background: linear-gradient(135deg, #e67e22, #f39c12);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            color: #7f8c8d;
        }

        .qr-code {
            text-align: center;
            margin: 20px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        @media (max-width: 768px) {
            .receipt-container {
                margin: 10px;
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="receipt-container">
            <!-- Print/Download Button -->
            <div class="no-print text-end mb-3">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print me-2"></i>Print Receipt
                </button>
                <button onclick="window.close()" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>

            <!-- Receipt Header -->
            <div class="receipt-header">
                <div class="company-logo">
                    <i class="fas fa-coins"></i>
                </div>
                <h1 class="receipt-title">UNITYX GOLD WITHDRAWAL RECEIPT</h1>
                <div class="receipt-number">
                    Receipt #{{ $withdrawal->id }}
                </div>
                <p class="mt-3 mb-0">
                    <strong>Date Issued:</strong> {{ now()->format('F d, Y \a\t g:i A') }}<br>
                    <strong>Withdrawal Date:</strong> {{ $withdrawal->created_at->format('F d, Y \a\t g:i A') }}
                </p>
            </div>

            <!-- Customer Information -->
            <div class="section-title">
                <i class="fas fa-user me-2"></i>Customer Information
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">Full Name</div>
                        <div class="info-value">{{ $withdrawal->user->firstname }} {{ $withdrawal->user->lastname }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Username</div>
                        <div class="info-value">{{ $withdrawal->user->username }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">{{ $withdrawal->user->email }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">Customer ID</div>
                        <div class="info-value">#{{ $withdrawal->user->id }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">National ID</div>
                        <div class="info-value">{{ $withdrawal->user->national_id ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Withdrawal Status</div>
                        <div class="info-value">
                            <span class="status-badge status-approved">
                                <i class="fas fa-check-circle me-1"></i>Approved
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction Details -->
            <div class="section-title">
                <i class="fas fa-receipt me-2"></i>Transaction Details
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">Withdrawal Type</div>
                        <div class="info-value">
                            <i class="fas fa-coins text-warning me-1"></i>Gold Redemption
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Wallet Source</div>
                        <div class="info-value">{{ ucfirst($withdrawal->wallet_type ?? 'profit') }} Wallet</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Reference</div>
                        <div class="info-value">{{ $withdrawal->reference ?? 'Not provided' }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">Request Amount</div>
                        <div class="info-value">${{ number_format($withdrawal->amount, 2) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Processing Fee (7.0%)</div>
                        <div class="info-value">${{ number_format($withdrawal->tax_amount, 2) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Branch Assigned</div>
                        <div class="info-value">
                            @if ($withdrawal->branch_selected && $withdrawal->branch_name)
                                <span class="text-success">
                                    <i class="fas fa-check me-1"></i>{{ $withdrawal->branch_name }}
                                </span>
                            @else
                                <span class="text-muted">Not assigned</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount Summary -->
            <div class="amount-highlight">
                <h3 class="mb-2">
                    <i class="fas fa-coins me-2"></i>Net Gold Value
                </h3>
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="mb-0">${{ number_format($withdrawal->net_amount, 2) }} USD</h2>
                        <small>Original withdrawal amount</small>
                    </div>
                    <div class="col-md-6">
                        <h2 class="mb-0">RM {{ number_format($withdrawal->net_amount * 4.7, 2) }}</h2>
                        <small>Approximate value in Malaysian Ringgit</small>
                    </div>
                </div>
                <div class="mt-2">
                    <small><em>* Exchange rate is approximate and subject to current market rates at time of
                            redemption</em></small>
                </div>
            </div>

            <!-- Branch Information -->
            @if ($withdrawal->branch_selected)
                @php
                    // Get branch manager info from config as fallback
                    $branchConfig = collect(config('gold_branches.branches'))->first(function ($branch) use (
                        $withdrawal,
                    ) {
                        return $branch['name'] === $withdrawal->branch_name;
                    });

                    $managerName =
                        $withdrawal->branch_manager_name ?? ($branchConfig['manager_name'] ?? 'Not assigned');
                    $managerPhone =
                        $withdrawal->branch_manager_phone ?? ($branchConfig['manager_phone'] ?? 'Not available');
                @endphp
                <div class="section-title">
                    <i class="fas fa-map-marker-alt me-2"></i>Branch Assignment Details
                </div>
                <div class="branch-highlight">
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="mb-3">
                                <i class="fas fa-building me-2"></i>{{ $withdrawal->branch_name }}
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <strong><i class="fas fa-user-tie me-2"></i>Branch Manager:</strong><br>
                                        {{ $managerName }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <strong><i class="fas fa-phone me-2"></i>Contact Number:</strong><br>
                                        {{ $managerPhone }}
                                    </p>
                                </div>
                            </div>
                            <div class="alert alert-success mt-3 mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Ready for Pickup:</strong> Your gold redemption has been processed and is ready
                                for collection at this branch.
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Important Instructions -->
            <div class="section-title">
                <i class="fas fa-exclamation-triangle me-2"></i>Important Instructions
            </div>
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="card-title text-warning">
                        <i class="fas fa-info-circle me-2"></i>Before Visiting the Branch:
                    </h6>
                    <ul class="mb-3">
                        <li>Contact the branch manager
                            @if ($withdrawal->branch_selected)
                                @php
                                    // Use the same fallback logic for instructions
                                    $branchConfig = collect(config('gold_branches.branches'))->first(function (
                                        $branch,
                                    ) use ($withdrawal) {
                                        return $branch['name'] === $withdrawal->branch_name;
                                    });

                                    $instructionManagerName =
                                        $withdrawal->branch_manager_name ?? ($branchConfig['manager_name'] ?? null);
                                    $instructionManagerPhone =
                                        $withdrawal->branch_manager_phone ?? ($branchConfig['manager_phone'] ?? null);
                                @endphp

                                @if ($instructionManagerName && $instructionManagerPhone)
                                    <strong>{{ $instructionManagerName }}</strong> at
                                    <strong>{{ $instructionManagerPhone }}</strong>
                                @else
                                    at <strong>{{ $withdrawal->branch_name ?? 'the assigned branch' }}</strong>
                                @endif
                            @else
                                at <strong>{{ $withdrawal->branch_name ?? 'the assigned branch' }}</strong>
                            @endif
                            to schedule your visit
                        </li>
                        <li>Bring a valid government-issued photo ID</li>
                        <li>Present this receipt (printed or digital)</li>
                        <li>Quote your withdrawal reference: <strong>#{{ $withdrawal->id }}</strong></li>
                    </ul>

                    <h6 class="card-title text-info">
                        <i class="fas fa-clock me-2"></i>Processing Information:
                    </h6>
                    <ul class="mb-0">
                        <li>Gold redemption processing time: 3-5 business days</li>
                        <li>Branch pickup must be completed within 30 days</li>
                        <li>ID verification is mandatory for security purposes</li>
                        <li>Gold will be provided based on current market rates</li>
                    </ul>
                </div>
            </div>

            <!-- Additional Notes -->
            @if ($withdrawal->note)
                <div class="section-title">
                    <i class="fas fa-sticky-note me-2"></i>Additional Notes
                </div>
                <div class="alert alert-info">
                    {{ $withdrawal->note }}
                </div>
            @endif

            <!-- Footer -->
            <div class="footer">
                <p class="mb-2">
                    <strong>UNITYX Trading Platform</strong><br>
                    Gold Redemption Services in Partnership with Musleem Gold
                </p>
                <p class="small mb-0">
                    This receipt is generated electronically and serves as proof of your approved gold withdrawal
                    request.<br>
                    For support, contact: <a href="mailto:support@unityx.com">support@unityx.com</a>
                </p>
                <div class="mt-3">
                    <small class="text-muted">
                        Generated on {{ now()->format('Y-m-d H:i:s') }} | Receipt ID:
                        {{ $withdrawal->id }}-{{ date('Ymd') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
