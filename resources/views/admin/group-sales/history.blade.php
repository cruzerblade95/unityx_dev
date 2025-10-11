@extends('admin/layouts/layoutMaster')

@section('title', 'Group Sales Claims History')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Group Sales Claims History</h5>
                    <div>
                        <a href="{{ route('admin.group-sales.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Rewards
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title mb-0">Total Claims</h6>
                                            <h4 class="mb-0" id="totalClaims">0</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-chart-line fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title mb-0">This Month</h6>
                                            <h4 class="mb-0" id="thisMonthClaims">0</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-calendar fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title mb-0">Total Rewarded</h6>
                                            <h4 class="mb-0" id="totalRewarded">$0</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-dollar-sign fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="historyTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Reward Level</th>
                                    <th>Reward Title</th>
                                    <th>Sales Amount</th>
                                    <th>Reward Amount</th>
                                    <th>Tax %</th>
                                    <th>Total Sales</th>
                                    <th>Claimed Month</th>
                                    <th>Claimed Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">Claim Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="claimDetails">
                    <!-- Claim details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#historyTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.group-sales.history.data") }}',
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'user_name', name: 'u.name' },
            { data: 'user_email', name: 'u.email' },
            { data: 'reward_level', name: 'gsr.reward_level' },
            { data: 'reward_title', name: 'gsrc.reward_title', defaultContent: 'N/A' },
            { data: 'sales_amount', name: 'gsr.sales_threshold', render: function(data) {
                return '$' + parseFloat(data || 0).toFixed(2);
            }},
            { data: 'reward_amount', name: 'gsr.reward_amount', render: function(data) {
                return '$' + parseFloat(data || 0).toFixed(2);
            }},
            { data: 'tax_percentage', name: 'gsr.tax_percentage', render: function(data) {
                return parseFloat(data || 0).toFixed(2) + '%';
            }},
            { data: 'total_sales_at_claim', name: 'gsr.total_sales_at_claim', render: function(data) {
                return '$' + parseFloat(data || 0).toFixed(2);
            }},
            { data: 'claimed_month', name: 'gsr.claimed_month' },
            { data: 'claimed_at', name: 'gsr.claimed_at', render: function(data) {
                return data ? new Date(data).toLocaleDateString() : 'N/A';
            }},
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        responsive: true,
        drawCallback: function() {
            updateStatistics();
        }
    });

    // Update statistics
    function updateStatistics() {
        $.get('{{ route("admin.group-sales.history.data") }}?get_stats=true', function(response) {
            $('#totalClaims').text(response.total || 0);
        });
    }

    // View claim details
    $(document).on('click', '.view-claim', function() {
        const claimId = $(this).data('id');

        $('#claimDetails').html('<p>Loading claim details...</p>');
        $('#viewModal').modal('show');
    });

    // Initial statistics load
    updateStatistics();
});
</script>
@endsection
