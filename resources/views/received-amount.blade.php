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

        .payment-method-box {
            background: #f3e8ff;
            border-left: 4px solid #8b5cf6;
            padding: 15px;
            border-radius: 8px;
        }

        .required-label:after {
            content: " *";
            color: red;
        }
        
        option:disabled {
            background-color: #f5f5f5;
            color: #999;
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
                    <p class="text-muted mb-0">Manage fuel card, credit card and driver credit payments</p>
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
                        <li class="nav-item">
                            <button class="nav-link" id="credit-driver-tab" data-bs-toggle="tab" data-bs-target="#credit-driver"
                                type="button" role="tab">
                                <i class="fas fa-truck me-2"></i>Driver Credit Payments
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Fuel Card Tab -->
                        <div class="tab-pane fade show active" id="fuel" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle w-100" id="fuelTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Account Name</th>
                                            <th>Shift #</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Credit Card Tab -->
                        <div class="tab-pane fade" id="credit" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle w-100" id="creditTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Account Name</th>
                                            <th>Shift #</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Driver Credit Tab -->
                        <div class="tab-pane fade" id="credit-driver" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle w-100" id="creditDriverTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Amount Given To</th>
                                            <th>Vehicle Number/CNIC</th>
                                            <th>Shift #</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
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

    <!-- Receive Modal for Fuel Card & Credit Card -->
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
                            <label class="form-label fw-semibold">Account Name</label>
                            <input type="text" class="form-control bg-light" id="account_name" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Amount to Receive</label>
                            <input type="text" class="form-control bg-light" id="receive_amount" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">MDR Percentage</label>
                            <input type="text" class="form-control bg-light" id="mdr_percentage" readonly>
                            <small class="text-muted">MDR = Merchant Discount Rate</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold required-label">Select Shift</label>
                            <select id="shift_id" class="form-select" required>
                                <option value="">Loading shifts...</option>
                            </select>
                            <small class="text-muted">Only open shifts will be shown</small>
                        </div>

                        <div class="mb-3 expense-box p-3 rounded">
                            <label class="form-label fw-semibold text-danger">MDR Amount (Expense)</label>
                            <input type="text" class="form-control bg-white" id="mdr_amount" readonly style="border-color: #ef4444;">
                            <small class="text-danger">This will be recorded as EXPENSE</small>
                        </div>

                        <div class="mb-3 income-box p-3 rounded">
                            <label class="form-label fw-semibold text-success">Net Amount (Income)</label>
                            <input type="text" class="form-control bg-white" id="net_amount" readonly style="border-color: #22c55e;">
                            <small class="text-success">This will be recorded as INCOME</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmReceiveBtn">Confirm Receive</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receive Modal for Driver Credit -->
    <div class="modal fade" id="driverCreditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Driver Credit Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="driverCreditForm">
                        <input type="hidden" id="driver_credit_id">
                        <input type="hidden" id="driver_credit_station_id">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Credit Details</label>
                            <div class="border rounded p-3 bg-light">
                                <div><strong>Given To:</strong> <span id="driver_given_to"></span></div>
                                <div><strong>Vehicle/CNIC:</strong> <span id="driver_identifier"></span></div>
                                <div><strong>Amount:</strong> <span id="driver_amount" class="fw-bold text-primary"></span></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold required-label">Select Shift</label>
                            <select id="driver_shift_id" class="form-select" required>
                                <option value="">Loading shifts...</option>
                            </select>
                            <small class="text-muted">Only open shifts will be shown</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold required-label">Payment Method</label>
                            <select id="payment_method" class="form-select" required>
                                <option value="">Select payment method...</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                            </select>
                        </div>

                        <!-- Bank Account Selection (shown only when bank is selected and accounts exist) -->
                        <div class="mb-3" id="bank_account_section" style="display: none;">
                            <label class="form-label fw-semibold required-label">
                                <i class="fas fa-university me-1"></i>Select Bank Account
                            </label>
                            <select id="bank_account_id" class="form-select">
                                <option value="">Loading bank accounts...</option>
                            </select>
                            <small class="text-muted">Amount will be added to selected bank account</small>
                        </div>

                        <div class="payment-method-box" id="cash_details_section" style="display: none;">
                            <div class="alert alert-success">
                                <i class="fas fa-info-circle"></i> Amount will be updated in shift cash handover/return
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmDriverCreditBtn">Confirm Receive</button>
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
        let creditDriverTable = null;
        let hasBankAccounts = false;

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

        function getDriverCreditUrl() {
            if (AUTH_ROLE === 'admin') {
                return '/api/driver-credit/admin';
            } else if (AUTH_ROLE === 'owner') {
                return `/api/driver-credit/owner/${AUTH_USER_ID}`;
            } else if (AUTH_ROLE === 'employee') {
                return `/api/driver-credit/employee/${AUTH_USER_ID}`;
            }
            return null;
        }

        function loadOpenShifts(stationId, selectElementId) {
            if (!stationId) {
                $(selectElementId).html('<option value="">No station available</option>');
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
                        $(selectElementId).html(options);
                        $(selectElementId).prop('disabled', false);
                    } else {
                        $(selectElementId).html('<option value="">No open shifts found</option>');
                        $(selectElementId).prop('disabled', true);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading shifts:', xhr);
                    $(selectElementId).html('<option value="">Error loading shifts</option>');
                    $(selectElementId).prop('disabled', true);
                }
            });
        }

        // Load bank accounts for a station
        function loadBankAccounts(stationId) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: `/api/station/${stationId}/bank-accounts`,
                    method: "GET",
                    success: function(response) {
                        let accounts = response.data || response;
                        let options = '<option value="">Select bank account...</option>';
                        
                        if (Array.isArray(accounts) && accounts.length > 0) {
                            hasBankAccounts = true;
                            accounts.forEach(account => {
                                options += `<option value="${account.id}">${account.name} - ${account.bank_name || 'Bank Account'}</option>`;
                            });
                            $('#bank_account_id').html(options);
                            $('#bank_account_id').prop('disabled', false);
                            resolve(true);
                        } else {
                            hasBankAccounts = false;
                            $('#bank_account_id').html('<option value="">No bank accounts found</option>');
                            $('#bank_account_id').prop('disabled', true);
                            resolve(false);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading bank accounts:', xhr);
                        hasBankAccounts = false;
                        $('#bank_account_id').html('<option value="">Error loading accounts</option>');
                        $('#bank_account_id').prop('disabled', true);
                        resolve(false);
                    }
                });
            });
        }

        // Load Fuel Card Data
        function loadFuelData() {
            const url = getDataUrl('fuel');
            if (!url) return;

            $.ajax({
                url: url,
                method: "GET",
                success: function(data) {
                    if (fuelTable) {
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
                                    ? '<span class="badge-paid">Received</span>' 
                                    : '<span class="badge-pending">Not Received</span>' 
                            },
                            {
                                data: null, 
                                render: (row) => {
                                    if (row.is_paid == '1') {
                                        return '<button class="btn btn-sm btn-secondary" disabled>Received</button>';
                                    }
                                    return `<button class="btn btn-sm btn-success receive-btn" 
                                        data-id="${row.id}"
                                        data-account-id="${row.account_id}"
                                        data-account-name="${row.account_name}"
                                        data-amount="${row.amount}"
                                        data-mdr="${row.mdr || 0}"
                                        data-station-id="${row.station_id}"
                                        data-type="fuel">
                                        Receive Payment
                                    </button>`;
                                }, 
                                orderable: false
                            }
                        ],
                        pageLength: 10,
                        searching: false,
                        destroy: true
                    });
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                }
            });
        }

        // Load Credit Card Data
        function loadCreditData() {
            const url = getDataUrl('credit');
            if (!url) return;

            $.ajax({
                url: url,
                method: "GET",
                success: function(data) {
                    if (creditTable) {
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
                                    ? '<span class="badge-paid">Received</span>' 
                                    : '<span class="badge-pending">Not Received</span>' 
                            },
                            {
                                data: null, 
                                render: (row) => {
                                    if (row.is_paid == '1') {
                                        return '<button class="btn btn-sm btn-secondary" disabled>Received</button>';
                                    }
                                    return `<button class="btn btn-sm btn-success receive-btn" 
                                        data-id="${row.id}"
                                        data-account-id="${row.account_id}"
                                        data-account-name="${row.account_name}"
                                        data-amount="${row.amount}"
                                        data-mdr="${row.mdr || 0}"
                                        data-station-id="${row.station_id}"
                                        data-type="credit">
                                        Receive Payment
                                    </button>`;
                                }, 
                                orderable: false
                            }
                        ],
                        pageLength: 10,
                        searching: false,
                        destroy: true
                    });
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                }
            });
        }

        // Load Driver Credit Data
        function loadDriverCreditData() {
            const url = getDriverCreditUrl();
            if (!url) return;

            $.ajax({
                url: url,
                method: "GET",
                success: function(data) {
                    if (creditDriverTable) {
                        creditDriverTable.destroy();
                    }

                    creditDriverTable = $('#creditDriverTable').DataTable({
                        data: data,
                        columns: [
                            { data: null, render: (d, t, r, m) => m.row + 1 },
                            { data: "amount_given_to", defaultContent: "N/A" },
                            { 
                                data: null,
                                render: (row) => {
                                    if (row.amount_given_to === 'Driver') {
                                        return row.cnic || 'N/A';
                                    } else {
                                        return row.vehicle_number || 'N/A';
                                    }
                                }
                            },
                            { data: "shift_no", defaultContent: "N/A" },
                            { data: "amount", render: (d) => `<span class="fw-bold">PKR ${parseFloat(d).toFixed(2)}</span>` },
                            { 
                                data: "is_paid", 
                                render: (d) => d == '1' 
                                    ? '<span class="badge-paid">Received</span>' 
                                    : '<span class="badge-pending">Not Received</span>' 
                            },
                            {
                                data: null, 
                                render: (row) => {
                                    if (row.is_paid == '1') {
                                        return '<button class="btn btn-sm btn-secondary" disabled>Received</button>';
                                    }
                                    return `<button class="btn btn-sm btn-success driver-credit-receive-btn" 
                                        data-id="${row.id}"
                                        data-station-id="${row.station_id}"
                                        data-amount="${row.amount}"
                                        data-given-to="${row.amount_given_to}"
                                        data-identifier="${row.amount_given_to === 'Driver' ? row.cnic : row.vehicle_number}">
                                        Receive Payment
                                    </button>`;
                                }, 
                                orderable: false
                            }
                        ],
                        pageLength: 10,
                        searching: false,
                        destroy: true
                    });
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                }
            });
        }

        // Fuel/Credit Card Receive Handler
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

            const mdrAmount = (parseFloat(amount) * parseFloat(mdr)) / 100;
            const netAmount = parseFloat(amount) - mdrAmount;
            $('#mdr_amount').val(`PKR ${mdrAmount.toFixed(2)}`);
            $('#net_amount').val(`PKR ${netAmount.toFixed(2)}`);

            $('#shift_id').html('<option value="">Loading shifts...</option>');
            $('#shift_id').prop('disabled', true);
            
            loadOpenShifts(stationId, '#shift_id');

            $('#receiveModal').modal('show');
        });

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
            btn.html('Processing...');
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
                    Swal.fire('Success!', response.message, 'success');
                    $('#receiveModal').modal('hide');
                    loadFuelData();
                    loadCreditData();
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Failed to receive payment';
                    Swal.fire('Error!', errorMsg, 'error');
                },
                complete: function() {
                    btn.html('Confirm Receive');
                    btn.prop('disabled', false);
                }
            });
        });

        // Driver Credit Receive Handler
        $(document).on('click', '.driver-credit-receive-btn', function() {
    const id = $(this).data('id');
    const stationId = $(this).data('station-id');
    const amount = $(this).data('amount');
    const givenTo = $(this).data('given-to');
    const identifier = $(this).data('identifier');

    $('#driver_credit_id').val(id);
    $('#driver_credit_station_id').val(stationId);
    $('#driver_given_to').text(givenTo);
    $('#driver_identifier').text(identifier);
    $('#driver_amount').text(`PKR ${parseFloat(amount).toFixed(2)}`);
    
    // Reset form
    $('#payment_method').val('');
    $('#bank_account_section').hide();
    $('#cash_details_section').hide();
    $('#bank_account_id').val('');
    
    // Load open shifts
    $('#driver_shift_id').html('<option value="">Loading shifts...</option>');
    $('#driver_shift_id').prop('disabled', true);
    
    loadOpenShifts(stationId, '#driver_shift_id');

    $('#driverCreditModal').modal('show');
});

        $('#payment_method').on('change', function() {
    const method = $(this).val();
    $('#bank_account_section').hide();
    $('#cash_details_section').hide();
    $('#bank_account_id').prop('required', false);
    
    if (method === 'bank') {
        $('#bank_account_section').show();
        $('#bank_account_id').prop('required', true);
        
        // Load bank accounts when bank is selected
        const stationId = $('#driver_credit_station_id').val();
        if (stationId) {
            loadBankAccounts(stationId);
        }
    } else if (method === 'cash') {
        $('#cash_details_section').show();
    }
});


        $('#confirmDriverCreditBtn').on('click', function() {
    const driverCreditId = $('#driver_credit_id').val();
    const stationId = $('#driver_credit_station_id').val();
    const shiftId = $('#driver_shift_id').val();
    const paymentMethod = $('#payment_method').val();
    const amount = $('#driver_amount').text().replace('PKR ', '').replace(/,/g, '');
    const bankAccountId = $('#bank_account_id').val();

    if (!shiftId) {
        Swal.fire('Error', 'Please select a shift', 'error');
        return;
    }

    if (!paymentMethod) {
        Swal.fire('Error', 'Please select a payment method', 'error');
        return;
    }

    if (paymentMethod === 'bank' && !bankAccountId) {
        Swal.fire('Error', 'Please select a bank account', 'error');
        return;
    }

    const btn = $(this);
    btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');
    btn.prop('disabled', true);

    const requestData = {
        driver_credit_id: driverCreditId,
        station_id: stationId,
        shift_id: shiftId,
        payment_method: paymentMethod,
        amount: amount
    };

    if (paymentMethod === 'bank') {
        requestData.bank_account_id = bankAccountId;
    }

    $.ajax({
        url: `/api/driver-credit/receive`,
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify(requestData),
        success: function(response) {
            Swal.fire({
                title: 'Payment Received!',
                html: `
                    <div style="text-align: left;">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> ${response.message}
                        </div>
                        <div class="alert alert-info">
                            <strong>Payment Method:</strong> ${response.payment_method.toUpperCase()}<br>
                            <strong>Amount Received:</strong> PKR ${parseFloat(response.amount).toFixed(2)}<br>
                            <strong>Shift:</strong> #${response.shift_no}
                        </div>
                    </div>
                `,
                icon: 'success',
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Done'
            });
            $('#driverCreditModal').modal('hide');
            loadDriverCreditData();
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
            loadDriverCreditData();
            
            $('#fuel-tab').on('click', function() { 
                setTimeout(() => loadFuelData(), 100);
            });
            $('#credit-tab').on('click', function() { 
                setTimeout(() => loadCreditData(), 100);
            });
            $('#credit-driver-tab').on('click', function() { 
                setTimeout(() => loadDriverCreditData(), 100);
            });
        });
    </script>
@endsection