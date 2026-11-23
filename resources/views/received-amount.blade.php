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

            <div class="card shadow-sm mb-3" id="receivedFilterCard">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="filter_from_date" class="form-label fw-semibold mb-1">From</label>
                            <input type="date" class="form-control" id="filter_from_date">
                        </div>
                        <div class="col-md-3">
                            <label for="filter_to_date" class="form-label fw-semibold mb-1">To</label>
                            <input type="date" class="form-control" id="filter_to_date">
                        </div>
                        <div class="col-md-3">
                            <label for="filter_account" class="form-label fw-semibold mb-1" id="filter_account_label">Fuel Card</label>
                            <select class="form-select" id="filter_account">
                                <option value="">All</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="button" class="btn btn-primary w-100" id="applyReceivedFilters">
                                <i class="fas fa-filter me-1"></i>Apply
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100" id="resetReceivedFilters">
                                Reset
                            </button>
                        </div>
                    </div>
                    <div class="mt-3 p-3 rounded d-flex flex-wrap justify-content-between align-items-center gap-2" style="background:#f8fafc;border:1px solid #e5e7eb;">
                        <div>
                            <span class="text-muted">Grand Total (selected dates):</span>
                            <span class="fw-bold fs-5 ms-2" id="receivedGrandTotal">PKR 0.00</span>
                            <span class="text-muted ms-2" id="receivedGrandTotalCount"></span>
                        </div>
                        <button type="button" class="btn btn-success" id="openBulkReceiveBtn">
                            <i class="fas fa-hand-holding-usd me-1"></i>Receive Amount
                        </button>
                    </div>
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
                            <button class="nav-link" id="credit-driver-tab" data-bs-toggle="tab"
                                data-bs-target="#credit-driver" type="button" role="tab">
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
                                            <th>Coustomer</th>
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

    <!-- Bulk Receive Modal -->
    <div class="modal fade" id="bulkReceiveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-hand-holding-usd me-2"></i>Receive Amount (Bulk)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3" id="bulkReceiveInfo">
                        Select rows to receive. Shift selection is not required for bulk receive.
                    </div>

                    <div class="table-responsive mb-3" style="max-height: 360px; overflow:auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th style="width:40px;">
                                        <input type="checkbox" id="bulkSelectAll">
                                    </th>
                                    <th>Details</th>
                                    <th class="text-end">Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="bulkReceiveRows"></tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted" id="bulkSelectedCount">0 selected</div>
                        <div class="fw-bold">Selected Total: <span id="bulkSelectedTotal">PKR 0.00</span></div>
                    </div>

                    <div id="bulkDriverPaymentSection" style="display:none;">
                        <div class="payment-method-box">
                            <label class="form-label fw-semibold required-label mb-2">Payment Method (Driver Credit)</label>
                            <select id="bulk_driver_payment_method" class="form-select mb-3">
                                <option value="">Select payment method...</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                            </select>

                            <div id="bulk_driver_bank_section" style="display:none;">
                                <label class="form-label fw-semibold required-label">Select Bank Account</label>
                                <select id="bulk_driver_bank_account_id" class="form-select">
                                    <option value="">Select bank account...</option>
                                </select>
                                <small class="text-muted" id="bulkDriverBankHelp">Amount will be added to selected bank account.</small>
                            </div>

                            <div id="bulk_driver_cash_note" class="alert alert-success mt-3 mb-0" style="display:none;">
                                <i class="fas fa-info-circle"></i> Cash receive selected. Shift is not required for bulk receive.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmBulkReceiveBtn">
                        <i class="fas fa-check-circle me-1"></i>Receive Selected
                    </button>
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
                            <input type="text" class="form-control bg-white" id="mdr_amount" readonly
                                style="border-color: #ef4444;">
                            <small class="text-danger">This will be recorded as EXPENSE</small>
                        </div>

                        <div class="mb-3 income-box p-3 rounded">
                            <label class="form-label fw-semibold text-success">Net Amount (Income)</label>
                            <input type="text" class="form-control bg-white" id="net_amount" readonly
                                style="border-color: #22c55e;">
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
                                <div><strong>Amount:</strong> <span id="driver_amount" class="fw-bold text-primary"></span>
                                </div>
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

        // Filter state (old behavior preserved: no filters => show all)
        let fuelFullData = [];
        let creditFullData = [];
        let driverCreditFullData = [];
        let activeReceivedTab = 'fuel';

        function getDataUrl(type) {
            if (AUTH_ROLE === 'admin') {
                return `/api/received-amount/data/admin?type=${type}`;
            } else if (AUTH_ROLE === 'owner') {
                return `/api/received-amount/data/owner/${AUTH_USER_ID}?type=${type}`;
            } else if (AUTH_ROLE === 'employee') {
                return `/api/received-amount/data/employee/${AUTH_USER_ID}?type=${type}`;
            } else if (AUTH_ROLE === 'rm' || AUTH_ROLE === 'tm') {
                return `/api/received-amount/data/manager/${AUTH_USER_ID}?type=${type}`;
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
            } else if (AUTH_ROLE === 'rm' || AUTH_ROLE === 'tm') {
                return `/api/driver-credit/manager/${AUTH_USER_ID}`;
            }
            return null;
        }

        function getActiveFilterConfig() {
            if (activeReceivedTab === 'fuel') {
                return {
                    label: 'Fuel Card',
                    data: fuelFullData,
                    accountKey: 'account_id',
                    accountLabel: 'account_name',
                    dateKeys: ['start_time', 'created_at']
                };
            }
            if (activeReceivedTab === 'credit') {
                return {
                    label: 'Credit Card',
                    data: creditFullData,
                    accountKey: 'account_id',
                    accountLabel: 'account_name',
                    dateKeys: ['start_time', 'created_at']
                };
            }
            return {
                label: 'Customer (Fuel to Party)',
                data: driverCreditFullData,
                accountKey: 'account_id',
                accountLabel: 'name',
                dateKeys: ['created_at', 'start_time']
            };
        }

        function parseFilterDate(value) {
            if (!value) return null;
            const str = String(value).trim();
            if (!str) return null;
            const match = str.match(/^(\d{4}-\d{2}-\d{2})/);
            if (match) return match[1];
            const d = new Date(str);
            if (isNaN(d.getTime())) return null;
            return d.toISOString().slice(0, 10);
        }

        function getRecordDate(record, dateKeys) {
            for (const key of dateKeys || []) {
                const parsed = parseFilterDate(record[key]);
                if (parsed) return parsed;
            }
            return null;
        }

        function getFilteredReceivedData(tabKey) {
            const prev = activeReceivedTab;
            activeReceivedTab = tabKey;
            const config = getActiveFilterConfig();
            activeReceivedTab = prev;

            const from = $('#filter_from_date').val();
            const to = $('#filter_to_date').val();
            const selectedAccount = $('#filter_account').val();

            return (config.data || []).filter(row => {
                if (selectedAccount && String(row[config.accountKey] ?? '') !== String(selectedAccount)) {
                    return false;
                }
                if (from || to) {
                    const rowDate = getRecordDate(row, config.dateKeys);
                    if (!rowDate) return false;
                    if (from && rowDate < from) return false;
                    if (to && rowDate > to) return false;
                }
                return true;
            });
        }

        function updateReceivedGrandTotal(rows) {
            const total = (rows || []).reduce((sum, row) => sum + (parseFloat(row.amount) || 0), 0);
            $('#receivedGrandTotal').text(`PKR ${total.toFixed(2)}`);
            $('#receivedGrandTotalCount').text(rows && rows.length ? `(${rows.length} record${rows.length === 1 ? '' : 's'})` : '');
        }

        function updateFilterDropdownForActiveTab() {
            const config = getActiveFilterConfig();
            $('#filter_account_label').text(config.label);

            const currentValue = $('#filter_account').val();
            const optionsMap = new Map();
            (config.data || []).forEach(row => {
                const id = row[config.accountKey];
                const name = row[config.accountLabel];
                if (id === null || id === undefined || id === '' || !name) return;
                const key = String(id);
                if (!optionsMap.has(key)) {
                    optionsMap.set(key, String(name));
                }
            });

            let optionsHtml = '<option value="">All</option>';
            Array.from(optionsMap.entries())
                .sort((a, b) => a[1].localeCompare(b[1]))
                .forEach(([id, name]) => {
                    optionsHtml += `<option value="${id}">${$('<div>').text(name).html()}</option>`;
                });

            $('#filter_account').html(optionsHtml);
            if (currentValue && optionsMap.has(String(currentValue))) {
                $('#filter_account').val(currentValue);
            }
        }

        function renderActiveReceivedTable() {
            if (activeReceivedTab === 'fuel') {
                renderFuelTable(getFilteredReceivedData('fuel'));
            } else if (activeReceivedTab === 'credit') {
                renderCreditTable(getFilteredReceivedData('credit'));
            } else {
                renderDriverCreditTable(getFilteredReceivedData('driver'));
            }
        }

        function applyReceivedFilters() {
            updateFilterDropdownForActiveTab();
            renderActiveReceivedTable();
        }

        function resetReceivedFilters() {
            $('#filter_from_date').val('');
            $('#filter_to_date').val('');
            $('#filter_account').val('');
            updateFilterDropdownForActiveTab();
            renderActiveReceivedTable();
        }

        function loadOpenShifts(stationId, selectElementId) {
            if (!stationId) {
                $(selectElementId).html('<option value="">No station available</option>');
                return;
            }

            $.ajax({
                url: `/api/shifts/station/${stationId}/open`,
                method: "GET",
                success: function (response) {
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
                error: function (xhr) {
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
                    success: function (response) {
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
                    error: function (xhr) {
                        console.error('Error loading bank accounts:', xhr);
                        hasBankAccounts = false;
                        $('#bank_account_id').html('<option value="">Error loading accounts</option>');
                        $('#bank_account_id').prop('disabled', true);
                        resolve(false);
                    }
                });
            });
        }

        function renderFuelTable(data) {
            const rows = Array.isArray(data) ? data : [];
            if (fuelTable) {
                fuelTable.destroy();
            }
            fuelTable = $('#fuelTable').DataTable({
                data: rows,
                columns: [
                    { data: null, render: (d, t, r, m) => m.row + 1 },
                    { data: "account_name", defaultContent: "N/A" },
                    { data: "shift_no", defaultContent: "N/A" },
                    { data: "amount", render: (d) => `<span class="fw-bold">PKR ${parseFloat(d || 0).toFixed(2)}</span>` },
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
            if (activeReceivedTab === 'fuel') {
                updateReceivedGrandTotal(rows);
            }
        }

        // Load Fuel Card Data
        function loadFuelData() {
            const url = getDataUrl('fuel');
            if (!url) return;

            $.ajax({
                url: url,
                method: "GET",
                success: function (data) {
                    fuelFullData = Array.isArray(data) ? data : [];
                    if (activeReceivedTab === 'fuel') {
                        updateFilterDropdownForActiveTab();
                    }
                    renderFuelTable(getFilteredReceivedData('fuel'));
                },
                error: function (xhr) {
                    console.error('Error:', xhr);
                }
            });
        }

        function renderCreditTable(data) {
            const rows = Array.isArray(data) ? data : [];
            if (creditTable) {
                creditTable.destroy();
            }
            creditTable = $('#creditTable').DataTable({
                data: rows,
                columns: [
                    { data: null, render: (d, t, r, m) => m.row + 1 },
                    { data: "account_name", defaultContent: "N/A" },
                    { data: "shift_no", defaultContent: "N/A" },
                    { data: "amount", render: (d) => `<span class="fw-bold">PKR ${parseFloat(d || 0).toFixed(2)}</span>` },
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
            if (activeReceivedTab === 'credit') {
                updateReceivedGrandTotal(rows);
            }
        }

        // Load Credit Card Data
        function loadCreditData() {
            const url = getDataUrl('credit');
            if (!url) return;

            $.ajax({
                url: url,
                method: "GET",
                success: function (data) {
                    creditFullData = Array.isArray(data) ? data : [];
                    if (activeReceivedTab === 'credit') {
                        updateFilterDropdownForActiveTab();
                    }
                    renderCreditTable(getFilteredReceivedData('credit'));
                },
                error: function (xhr) {
                    console.error('Error:', xhr);
                }
            });
        }

        function renderDriverCreditTable(data) {
            const rows = Array.isArray(data) ? data : [];
            if (creditDriverTable) {
                creditDriverTable.destroy();
            }
            creditDriverTable = $('#creditDriverTable').DataTable({
                data: rows,
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
                    { data: "name", defaultContent: "N/A" },
                    { data: "amount", render: (d) => `<span class="fw-bold">PKR ${parseFloat(d || 0).toFixed(2)}</span>` },
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
                                    data-identifier="${row.amount_given_to === 'Driver' ? (row.cnic || '') : (row.vehicle_number || '')}">
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
            if (activeReceivedTab === 'driver') {
                updateReceivedGrandTotal(rows);
            }
        }

        // Load Driver Credit Data
        function loadDriverCreditData() {
            const url = getDriverCreditUrl();
            if (!url) return;

            $.ajax({
                url: url,
                method: "GET",
                success: function (data) {
                    driverCreditFullData = Array.isArray(data) ? data : [];
                    if (activeReceivedTab === 'driver') {
                        updateFilterDropdownForActiveTab();
                    }
                    renderDriverCreditTable(getFilteredReceivedData('driver'));
                },
                error: function (xhr) {
                    console.error('Error:', xhr);
                }
            });
        }

        // Fuel/Credit Card Receive Handler
        $(document).on('click', '.receive-btn', function () {
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

        $('#confirmReceiveBtn').on('click', function () {
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
                success: function (response) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#receiveModal').modal('hide');
                    loadFuelData();
                    loadCreditData();
                },
                error: function (xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Failed to receive payment';
                    Swal.fire('Error!', errorMsg, 'error');
                },
                complete: function () {
                    btn.html('Confirm Receive');
                    btn.prop('disabled', false);
                }
            });
        });

        // Driver Credit Receive Handler
        $(document).on('click', '.driver-credit-receive-btn', function () {
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

        $('#payment_method').on('change', function () {
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


        $('#confirmDriverCreditBtn').on('click', function () {
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
                success: function (response) {
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
                error: function (xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Failed to receive payment';
                    Swal.fire('Error!', errorMsg, 'error');
                },
                complete: function () {
                    btn.html('<i class="fas fa-check-circle me-1"></i> Confirm Receive');
                    btn.prop('disabled', false);
                }
            });
        });

        // Bulk receive helpers
        function formatPKR(value) {
            const n = parseFloat(value || 0) || 0;
            return 'PKR ' + n.toFixed(2);
        }

        function getBulkReceivableRows(tabKey) {
            const rows = getFilteredReceivedData(tabKey) || [];
            if (tabKey === 'driver') {
                return rows.filter(r => !(r.is_paid == 1 || r.is_paid === '1'));
            }
            return rows.filter(r => !(r.is_paid == 1 || r.is_paid === '1'));
        }

        function getBulkTabLabel(tabKey) {
            if (tabKey === 'fuel') return 'Fuel Card';
            if (tabKey === 'credit') return 'Credit Card';
            return 'Driver Credit';
        }

        function getBulkRowKey(row, tabKey) {
            return tabKey === 'driver' ? row.id : row.id; // cash_flow_id for fuel/credit, driver_credit id for driver
        }

        function renderBulkReceiveRows() {
            const rows = getBulkReceivableRows(activeReceivedTab);
            const $tbody = $('#bulkReceiveRows');
            $tbody.empty();

            if (!rows.length) {
                $tbody.append('<tr><td colspan="4" class="text-center text-muted py-4">No unpaid rows found for current filter.</td></tr>');
                $('#bulkSelectAll').prop('checked', false).prop('disabled', true);
            } else {
                $('#bulkSelectAll').prop('disabled', false);
                rows.forEach((row, idx) => {
                    let details = '';
                    if (activeReceivedTab === 'driver') {
                        const party = row.name || row.amount_given_to || '-';
                        const vehicle = row.vehicle_number || '-';
                        details = `<div><strong>${party}</strong></div><div class="text-muted small">Vehicle: ${vehicle} | Station: ${row.station_name || row.station_id || '-'}</div>`;
                    } else {
                        details = `<div><strong>${row.account_name || '-'}</strong></div><div class="text-muted small">Station: ${row.station_name || '-'} | Shift: ${row.shift_no || '-'} | Type: ${getBulkTabLabel(activeReceivedTab)}</div>`;
                    }
                    const amount = parseFloat(row.amount || 0);
                    const status = '<span class="badge bg-danger">Pending</span>';
                    $tbody.append(`
                        <tr>
                            <td><input type="checkbox" class="bulk-row-check" data-index="${idx}"></td>
                            <td>${details}</td>
                            <td class="text-end fw-semibold">${formatPKR(amount)}</td>
                            <td>${status}</td>
                        </tr>
                    `);
                });
            }

            updateBulkSelectionSummary();
            updateBulkDriverPaymentUI();
        }

        function getSelectedBulkRows() {
            const rows = getBulkReceivableRows(activeReceivedTab);
            const selected = [];
            $('#bulkReceiveRows .bulk-row-check:checked').each(function () {
                const idx = parseInt($(this).attr('data-index'), 10);
                if (!isNaN(idx) && rows[idx]) selected.push(rows[idx]);
            });
            return selected;
        }

        function updateBulkSelectionSummary() {
            const allChecks = $('#bulkReceiveRows .bulk-row-check');
            const checked = $('#bulkReceiveRows .bulk-row-check:checked');
            const selectedRows = getSelectedBulkRows();
            const total = selectedRows.reduce((sum, r) => sum + (parseFloat(r.amount || 0) || 0), 0);

            $('#bulkSelectedCount').text(`${checked.length} selected`);
            $('#bulkSelectedTotal').text(formatPKR(total));

            if (allChecks.length > 0) {
                $('#bulkSelectAll').prop('checked', checked.length === allChecks.length);
            } else {
                $('#bulkSelectAll').prop('checked', false);
            }
        }

        function resetBulkDriverPaymentFields() {
            $('#bulk_driver_payment_method').val('');
            $('#bulk_driver_bank_account_id').html('<option value="">Select bank account...</option>');
            $('#bulk_driver_bank_section').hide();
            $('#bulk_driver_cash_note').hide();
        }

        function updateBulkDriverPaymentUI() {
            if (activeReceivedTab === 'driver') {
                $('#bulkDriverPaymentSection').show();
            } else {
                $('#bulkDriverPaymentSection').hide();
                resetBulkDriverPaymentFields();
            }
        }

        function loadBulkDriverBankAccounts(stationId) {
            const $bankSelect = $('#bulk_driver_bank_account_id');
            $bankSelect.html('<option value="">Loading bank accounts...</option>');

            $.ajax({
                url: `/api/station/${stationId}/bank-accounts`,
                method: 'GET',
                success: function (response) {
                    const accounts = response.data || response || [];
                    $bankSelect.html('<option value="">Select bank account...</option>');
                    if (!accounts.length) {
                        $bankSelect.append('<option value="">No bank accounts found</option>');
                        return;
                    }
                    accounts.forEach(acc => {
                        const label = `${acc.name || 'Bank'}${acc.bank_name ? ' - ' + acc.bank_name : ''}${acc.account_number ? ' (' + acc.account_number + ')' : ''}`;
                        $bankSelect.append(`<option value="${acc.id}">${label}</option>`);
                    });
                },
                error: function () {
                    $bankSelect.html('<option value="">Failed to load bank accounts</option>');
                }
            });
        }

        function openBulkReceiveModal() {
            $('#bulkReceiveModal .modal-title').html(`<i class="fas fa-hand-holding-usd me-2"></i>Receive Amount (Bulk) - ${getBulkTabLabel(activeReceivedTab)}`);
            resetBulkDriverPaymentFields();
            renderBulkReceiveRows();
            $('#bulkReceiveModal').modal('show');
        }

        async function bulkReceiveFuelOrCredit(rows) {
            const paymentType = activeReceivedTab === 'fuel' ? 'fuel' : 'credit';
            let success = 0;
            const failures = [];

            for (const row of rows) {
                try {
                    const payload = {
                        cash_flow_id: row.id,
                        payment_type: paymentType,
                        account_id: row.account_id,
                        amount: parseFloat(row.amount || 0),
                        mdr_percentage: parseFloat(row.mdr || 0)
                        // shift_id intentionally omitted (bulk receive does not require open shift)
                    };
                    const res = await $.ajax({
                        url: '/api/received-amount/receive',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(payload)
                    });
                    if (res && res.success) {
                        success++;
                    } else {
                        failures.push(`ID ${row.id}: ${res?.message || 'Failed'}`);
                    }
                } catch (e) {
                    const msg = e?.responseJSON?.message || e?.message || 'Failed';
                    failures.push(`ID ${row.id}: ${msg}`);
                }
            }

            return { success, failures };
        }

        async function bulkReceiveDriverCredits(rows, paymentMethod, bankAccountId) {
            let success = 0;
            const failures = [];

            for (const row of rows) {
                try {
                    const payload = {
                        driver_credit_id: row.id,
                        station_id: row.station_id,
                        payment_method: paymentMethod,
                        amount: parseFloat(row.amount || 0)
                        // shift_id intentionally omitted (not compulsory for this receive flow)
                    };
                    if (paymentMethod === 'bank') {
                        payload.bank_account_id = bankAccountId;
                    }

                    const res = await $.ajax({
                        url: '/api/driver-credit/receive',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(payload)
                    });

                    if (res && (res.message || res.success)) {
                        success++;
                    } else {
                        failures.push(`ID ${row.id}: ${res?.message || 'Failed'}`);
                    }
                } catch (e) {
                    const msg = e?.responseJSON?.message || e?.message || 'Failed';
                    failures.push(`ID ${row.id}: ${msg}`);
                }
            }

            return { success, failures };
        }

        $(document).ready(function () {
            loadFuelData();
            loadCreditData();
            loadDriverCreditData();

            $('#openBulkReceiveBtn').on('click', function () {
                openBulkReceiveModal();
            });

            $('#bulkSelectAll').on('change', function () {
                const checked = $(this).is(':checked');
                $('#bulkReceiveRows .bulk-row-check').prop('checked', checked);
                updateBulkSelectionSummary();
                if (activeReceivedTab === 'driver' && $('#bulk_driver_payment_method').val() === 'bank') {
                    $('#bulk_driver_payment_method').trigger('change');
                }
            });

            $(document).on('change', '#bulkReceiveRows .bulk-row-check', function () {
                updateBulkSelectionSummary();
                if (activeReceivedTab === 'driver' && $('#bulk_driver_payment_method').val() === 'bank') {
                    // Re-evaluate station/bank options when selected rows change
                    const method = $('#bulk_driver_payment_method').val();
                    if (method === 'bank') {
                        const selectedRows = getSelectedBulkRows();
                        const stationIds = [...new Set(selectedRows.map(r => String(r.station_id || '')).filter(Boolean))];
                        if (stationIds.length === 1) {
                            loadBulkDriverBankAccounts(stationIds[0]);
                        } else {
                            $('#bulk_driver_bank_account_id').html('<option value="">Select rows from one station for bank transfer</option>');
                        }
                    }
                }
            });

            $('#bulk_driver_payment_method').on('change', function () {
                const method = $(this).val();
                $('#bulk_driver_cash_note').toggle(method === 'cash');
                $('#bulk_driver_bank_section').toggle(method === 'bank');

                if (method === 'bank') {
                    const selectedRows = getSelectedBulkRows();
                    const stationIds = [...new Set(selectedRows.map(r => String(r.station_id || '')).filter(Boolean))];
                    if (stationIds.length === 0) {
                        $('#bulk_driver_bank_account_id').html('<option value="">Select row(s) first</option>');
                    } else if (stationIds.length > 1) {
                        $('#bulk_driver_bank_account_id').html('<option value="">Bank transfer requires selected rows from one station</option>');
                        $('#bulkDriverBankHelp').text('Selected rows belong to multiple stations. Receive them separately for bank transfer, or use Cash.');
                    } else {
                        $('#bulkDriverBankHelp').text('Amount will be added to selected bank account.');
                        loadBulkDriverBankAccounts(stationIds[0]);
                    }
                } else {
                    $('#bulk_driver_bank_account_id').html('<option value="">Select bank account...</option>');
                }
            });

            $('#confirmBulkReceiveBtn').on('click', async function () {
                const selectedRows = getSelectedBulkRows();
                if (!selectedRows.length) {
                    Swal.fire('Error', 'Please select at least one row to receive', 'error');
                    return;
                }

                let paymentMethod = null;
                let bankAccountId = null;
                if (activeReceivedTab === 'driver') {
                    paymentMethod = $('#bulk_driver_payment_method').val();
                    if (!paymentMethod) {
                        Swal.fire('Error', 'Please select a payment method', 'error');
                        return;
                    }
                    if (paymentMethod === 'bank') {
                        const stationIds = [...new Set(selectedRows.map(r => String(r.station_id || '')).filter(Boolean))];
                        if (stationIds.length !== 1) {
                            Swal.fire('Error', 'For bank transfer, select rows from only one station', 'error');
                            return;
                        }
                        bankAccountId = $('#bulk_driver_bank_account_id').val();
                        if (!bankAccountId) {
                            Swal.fire('Error', 'Please select a bank account', 'error');
                            return;
                        }
                    }
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

                try {
                    let result;
                    if (activeReceivedTab === 'driver') {
                        result = await bulkReceiveDriverCredits(selectedRows, paymentMethod, bankAccountId);
                    } else {
                        result = await bulkReceiveFuelOrCredit(selectedRows);
                    }

                    const failureHtml = result.failures.length
                        ? `<div class="text-start mt-2"><strong>Failed:</strong><br>${result.failures.slice(0, 10).map(f => `<div>${f}</div>`).join('')}${result.failures.length > 10 ? `<div>...and ${result.failures.length - 10} more</div>` : ''}</div>`
                        : '';

                    if (result.success > 0 && result.failures.length === 0) {
                        Swal.fire('Success', `${result.success} payment(s) received successfully.`, 'success');
                    } else if (result.success > 0) {
                        Swal.fire('Partial Success', `${result.success} received, ${result.failures.length} failed.${failureHtml}`, 'warning');
                    } else {
                        Swal.fire('Error', `No payments were received.${failureHtml}`, 'error');
                    }

                    $('#bulkReceiveModal').modal('hide');
                    if (activeReceivedTab === 'fuel') loadFuelData();
                    if (activeReceivedTab === 'credit') loadCreditData();
                    if (activeReceivedTab === 'driver') loadDriverCreditData();
                } finally {
                    btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i>Receive Selected');
                }
            });

            $('#applyReceivedFilters').on('click', function () {
                applyReceivedFilters();
            });

            $('#resetReceivedFilters').on('click', function () {
                resetReceivedFilters();
            });

            $('#amountTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                const target = $(e.target).attr('data-bs-target');
                if (target === '#fuel') {
                    activeReceivedTab = 'fuel';
                } else if (target === '#credit') {
                    activeReceivedTab = 'credit';
                } else if (target === '#credit-driver') {
                    activeReceivedTab = 'driver';
                }
                updateFilterDropdownForActiveTab();
                renderActiveReceivedTable();
            });

            $('#fuel-tab').on('click', function () {
                setTimeout(() => loadFuelData(), 100);
            });
            $('#credit-tab').on('click', function () {
                setTimeout(() => loadCreditData(), 100);
            });
            $('#credit-driver-tab').on('click', function () {
                setTimeout(() => loadDriverCreditData(), 100);
            });
        });
    </script>
@endsection