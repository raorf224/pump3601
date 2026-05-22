@extends('partials.layouts.master')

@section('title', 'Received Amount')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .nav-tabs .nav-link {
            font-weight: 600;
            color: #6c757d;
            border: none;
            padding: 0.75rem 1.5rem;
        }

        .nav-tabs .nav-link.active {
            color: #4f46e5;
            border-bottom: 2px solid #4f46e5;
            background: transparent;
        }

        .badge-paid {
            background: #d1fae5;
            color: #065f46;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-pending {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        
        .expense-box {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
        }

        .income-box {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h4 class="fw-bold">
                        <i class="fas fa-credit-card me-2 text-primary"></i>Received Amount
                    </h4>
                    <p class="text-muted mb-0">Manage fuel card and credit card payments</p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4" id="amountTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="fuel-tab" data-bs-toggle="tab" data-bs-target="#fuel"
                                type="button" role="tab">
                                <i class="fas fa-gas-pump me-2"></i>Fuel Card Payments
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="credit-tab" data-bs-toggle="tab" data-bs-target="#credit"
                                type="button" role="tab">
                                <i class="fas fa-credit-card me-2"></i>Credit Card Payments
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="fuel" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle w-100" id="fuelTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="20%">Account Name</th>
                                            <th width="10%">Shift #</th>
                                            <th width="15%">Amount</th>
                                            <th width="15%">Status</th>
                                            <th width="15%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="credit" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle w-100" id="creditTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="20%">Account Name</th>
                                            <th width="10%">Shift #</th>
                                            <th width="15%">Amount</th>
                                            <th width="15%">Status</th>
                                            <th width="15%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receive Modal with Shift Dropdown -->
    <div class="modal fade" id="receiveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-hand-holding-usd me-2"></i>Confirm Payment Receipt
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="receiveForm">
                        <input type="hidden" id="cash_flow_id">
                        <input type="hidden" id="payment_type">
                        <input type="hidden" id="account_id">
                        <input type="hidden" id="station_id_for_shifts">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-building me-1"></i>Account Name
                            </label>
                            <input type="text" class="form-control bg-light" id="account_name" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-money-bill-wave me-1"></i>Amount to Receive
                            </label>
                            <input type="text" class="form-control bg-light" id="receive_amount" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-percent me-1"></i>MDR Percentage
                            </label>
                            <input type="text" class="form-control bg-light" id="mdr_percentage" readonly>
                            <small class="text-muted">MDR = Merchant Discount Rate</small>
                        </div>

                        <!-- ✅ Shift Selection Dropdown - API se open shifts aayenge -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold required-label">
                                <i class="fas fa-exchange-alt me-1"></i>Select Shift
                            </label>
                            <select id="shift_id" class="form-select" required>
                                <option value="">Loading shifts...</option>
                            </select>
                            <small class="text-muted">Only open shifts will be shown</small>
                        </div>

                        <div class="mb-3 expense-box p-3 rounded">
                            <label class="form-label fw-semibold text-danger">
                                <i class="fas fa-arrow-down me-1"></i>MDR Amount (Expense)
                            </label>
                            <input type="text" class="form-control bg-white" id="mdr_amount" readonly style="border-color: #ef4444;">
                            <small class="text-danger">This will be recorded as EXPENSE</small>
                        </div>

                        <div class="mb-3 income-box p-3 rounded">
                            <label class="form-label fw-semibold text-success">
                                <i class="fas fa-arrow-up me-1"></i>Net Amount (Income)
                            </label>
                            <input type="text" class="form-control bg-white" id="net_amount" readonly style="border-color: #22c55e;">
                            <small class="text-success">This will be recorded as INCOME</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="confirmReceiveBtn">
                        <i class="fas fa-check-circle me-1"></i> Confirm Receive
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const AUTH_ROLE = "{{ Auth::user()->role }}";
        const AUTH_USER_ID = "{{ Auth::id() }}";

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let fuelTable = null;
        let creditTable = null;
        let currentStationId = null;

        function getDataUrl(type) {
            if (AUTH_ROLE === 'admin') {
                return `/api/received-amount/data/admin?type=${type}`;
            } else if (AUTH_ROLE === 'owner') {
                return `/api/received-amount/data/owner/${AUTH_USER_ID}?type=${type}`;
            } else if (AUTH_ROLE === 'employee') {
                return `/api/received-amount/data/employee/${AUTH_USER_ID}?type=${type}`;
            }
            return null;
        }

        // ✅ Load open shifts from API
        function loadOpenShifts(stationId) {
            if (!stationId) {
                $('#shift_id').html('<option value="">No station available</option>');
                return;
            }

            $.ajax({
                url: `/api/shifts/station/${stationId}/open`,
                method: "GET",
                success: function(response) {
                    let options = '<option value="">Select shift...</option>';
                    let shifts = response.data || response;
                    
                    if (Array.isArray(shifts) && shifts.length > 0) {
                        shifts.forEach(shift => {
                            options += `<option value="${shift.id}">Shift #${shift.shift_no} - ${shift.start_time}</option>`;
                        });
                        $('#shift_id').html(options);
                        $('#shift_id').prop('disabled', false);
                    } else {
                        $('#shift_id').html('<option value="">No open shifts found</option>');
                        $('#shift_id').prop('disabled', true);
                        Swal.fire('Warning', 'No open shifts available for this station', 'warning');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading shifts:', xhr);
                    $('#shift_id').html('<option value="">Error loading shifts</option>');
                    $('#shift_id').prop('disabled', true);
                    Swal.fire('Error', 'Failed to load open shifts', 'error');
                }
            });
        }

        function loadFuelData() {
            const url = getDataUrl('fuel');
            if (!url) return;

            $.ajax({
                url: url,
                method: "GET",
                success: function(data) {
                    if (fuelTable) {
                        fuelTable.clear();
                        fuelTable.destroy();
                    }

                    fuelTable = $('#fuelTable').DataTable({
                        data: data,
                        columns: [
                            { data: null, render: (d, t, r, m) => m.row + 1 },
                            { data: "account_name", defaultContent: "N/A" },
                            { data: "shift_no", defaultContent: "N/A" },
                            { data: "amount", render: (d) => `<span class="fw-bold">PKR ${parseFloat(d).toFixed(2)}</span>` },
                            { 
                                data: "is_paid", 
                                render: (d) => d == '1' 
                                    ? '<span class="badge-paid"><i class="fas fa-check-circle me-1"></i> Received</span>' 
                                    : '<span class="badge-pending"><i class="fas fa-clock me-1"></i> Not Received</span>' 
                            },
                            {
                                data: null, 
                                render: (row) => {
                                    if (row.is_paid == '1') {
                                        return '<button class="btn btn-sm btn-secondary" disabled><i class="fas fa-check me-1"></i> Received</button>';
                                    }
                                    return `<button class="btn btn-sm btn-success receive-btn" 
                                        data-id="${row.id}"
                                        data-account-id="${row.account_id}"
                                        data-account-name="${row.account_name}"
                                        data-amount="${row.amount}"
                                        data-mdr="${row.mdr || 0}"
                                        data-station-id="${row.station_id}"
                                        data-type="fuel">
                                        <i class="fas fa-hand-holding-usd me-1"></i> Receive Payment
                                    </button>`;
                                }, 
                                orderable: false
                            }
                        ],
                        pageLength: 10,
                        searching: false,
                        info: false,
                        destroy: true,
                        language: { emptyTable: "No fuel card records found" }
                    });
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    Swal.fire('Error', 'Failed to load fuel card data', 'error');
                }
            });
        }

        function loadCreditData() {
            const url = getDataUrl('credit');
            if (!url) return;

            $.ajax({
                url: url,
                method: "GET",
                success: function(data) {
                    if (creditTable) {
                        creditTable.clear();
                        creditTable.destroy();
                    }

                    creditTable = $('#creditTable').DataTable({
                        data: data,
                        columns: [
                            { data: null, render: (d, t, r, m) => m.row + 1 },
                            { data: "account_name", defaultContent: "N/A" },
                            { data: "shift_no", defaultContent: "N/A" },
                            { data: "amount", render: (d) => `<span class="fw-bold">PKR ${parseFloat(d).toFixed(2)}</span>` },
                            { 
                                data: "is_paid", 
                                render: (d) => d == '1' 
                                    ? '<span class="badge-paid"><i class="fas fa-check-circle me-1"></i> Received</span>' 
                                    : '<span class="badge-pending"><i class="fas fa-clock me-1"></i> Not Received</span>' 
                            },
                            {
                                data: null, 
                                render: (row) => {
                                    if (row.is_paid == '1') {
                                        return '<button class="btn btn-sm btn-secondary" disabled><i class="fas fa-check me-1"></i> Received</button>';
                                    }
                                    return `<button class="btn btn-sm btn-success receive-btn" 
                                        data-id="${row.id}"
                                        data-account-id="${row.account_id}"
                                        data-account-name="${row.account_name}"
                                        data-amount="${row.amount}"
                                        data-mdr="${row.mdr || 0}"
                                        data-station-id="${row.station_id}"
                                        data-type="credit">
                                        <i class="fas fa-hand-holding-usd me-1"></i> Receive Payment
                                    </button>`;
                                }, 
                                orderable: false
                            }
                        ],
                        pageLength: 10,
                        searching: false,
                        info: false,
                        destroy: true,
                        language: { emptyTable: "No credit card records found" }
                    });
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    Swal.fire('Error', 'Failed to load credit card data', 'error');
                }
            });
        }

        // Open modal and load open shifts
        $(document).on('click', '.receive-btn', function() {
            const id = $(this).data('id');
            const accountId = $(this).data('account-id');
            const accountName = $(this).data('account-name');
            const amount = $(this).data('amount');
            const mdr = $(this).data('mdr');
            const paymentType = $(this).data('type');
            const stationId = $(this).data('station-id');

            $('#cash_flow_id').val(id);
            $('#account_id').val(accountId);
            $('#payment_type').val(paymentType);
            $('#station_id_for_shifts').val(stationId);
            $('#account_name').val(accountName);
            $('#receive_amount').val(`PKR ${parseFloat(amount).toFixed(2)}`);
            $('#mdr_percentage').val(`${mdr}%`);

            // Calculate MDR Amount and Net Amount
            const mdrAmount = (parseFloat(amount) * parseFloat(mdr)) / 100;
            const netAmount = parseFloat(amount) - mdrAmount;
            $('#mdr_amount').val(`PKR ${mdrAmount.toFixed(2)}`);
            $('#net_amount').val(`PKR ${netAmount.toFixed(2)}`);

            // Reset shift dropdown and load open shifts from API
            $('#shift_id').html('<option value="">Loading shifts...</option>');
            $('#shift_id').prop('disabled', true);
            
            // Load open shifts for this station
            loadOpenShifts(stationId);

            $('#receiveModal').modal('show');
        });

        // Confirm receive payment
        $('#confirmReceiveBtn').on('click', function() {
            const cashFlowId = $('#cash_flow_id').val();
            const paymentType = $('#payment_type').val();
            const shiftId = $('#shift_id').val();
            const accountId = $('#account_id').val();
            const amount = $('#receive_amount').val().replace('PKR ', '').replace(/,/g, '');
            const mdrPercentage = $('#mdr_percentage').val().replace('%', '');

            if (!shiftId) {
                Swal.fire('Error', 'Please select a shift', 'error');
                return;
            }

            const btn = $(this);
            btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');
            btn.prop('disabled', true);

            $.ajax({
                url: `/api/received-amount/receive`,
                method: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    cash_flow_id: cashFlowId,
                    payment_type: paymentType,
                    shift_id: shiftId,
                    account_id: accountId,
                    amount: amount,
                    mdr_percentage: mdrPercentage
                }),
                success: function(response) {
                    Swal.fire({
                        title: 'Payment Received!',
                        html: `
                            <div style="text-align: left;">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> ${response.message}
                                </div>
                                <table class="table table-sm table-borderless">
                                    <tr><td><strong>Total Amount:</strong></td><td class="text-end">PKR ${parseFloat(response.total_amount).toFixed(2)}</td></tr>
                                    <tr class="text-danger"><td><strong>MDR (${response.mdr_percentage}%):</strong></td><td class="text-end">- PKR ${parseFloat(response.mdr_amount).toFixed(2)}</td></tr>
                                    <tr class="text-success fw-bold"><td><strong>Net Amount:</strong></td><td class="text-end">PKR ${parseFloat(response.net_amount).toFixed(2)}</td></tr>
                                    <tr><td colspan="2"><hr></td></tr>
                                    <tr><td><strong>Previous Balance:</strong></td><td class="text-end">PKR ${parseFloat(response.previous_balance).toFixed(2)}</td></tr>
                                    <tr class="fw-bold"><td><strong>New Balance:</strong></td><td class="text-end text-success">PKR ${parseFloat(response.new_balance).toFixed(2)}</td></tr>
                                </table>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Done'
                    });
                    $('#receiveModal').modal('hide');
                    loadFuelData();
                    loadCreditData();
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Failed to receive payment';
                    Swal.fire('Error!', errorMsg, 'error');
                },
                complete: function() {
                    btn.html('<i class="fas fa-check-circle me-1"></i> Confirm Receive');
                    btn.prop('disabled', false);
                }
            });
        });

        $(document).ready(function() {
            loadFuelData();
            loadCreditData();
            
            $('#fuel-tab').on('click', function() { 
                setTimeout(() => loadFuelData(), 100);
            });
            $('#credit-tab').on('click', function() { 
                setTimeout(() => loadCreditData(), 100);
            });
        });
    </script>
@endsection