@extends('partials.layouts.master')

@section('title', 'Expense Sheet')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <style>
        .stat-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.2s ease;
            border: 1px solid #e9ecef;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }

        .stat-label {
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a1e24;
            line-height: 1.2;
        }

        .filter-section {
            background: #f8f9fc;
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e9ecef;
        }

        /* Remove header background color */
        .table thead th {
            background: transparent !important;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 2px solid #dee2e6;
        }

        .badge-station {
            background: #eef2ff;
            color: #4f46e5;
            padding: 0.5rem 1rem;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* Choices.js custom styling */
        .choices {
            margin-bottom: 0;
        }

        .choices__inner {
            background-color: #fff;
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
            min-height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .choices__input {
            background-color: transparent;
            margin-bottom: 0;
            padding: 0;
        }

        .choices__list--single {
            padding: 0;
        }

        .choices__list--dropdown .choices__item--selectable.is-highlighted {
            background-color: #eef2ff;
            color: #4f46e5;
        }

        .choices__placeholder {
            opacity: 0.6;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .stat-card {
                border: 1px solid #ddd;
                box-shadow: none;
            }

            .filter-section {
                background: none;
                border: 1px solid #ddd;
            }
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-chart-line me-2 text-primary"></i>Expense Sheet
                        </h5>
                        <div class="btn-group no-print">
                            <button id="printReportBtn" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                            <button id="pdfReportBtn" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Filter Section with Choices.js -->
                        <div class="filter-section no-print">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small mb-1">
                                        <i class="fas fa-map-marker-alt me-1"></i>Station
                                    </label>
                                    <select id="stationSelect" class="form-select form-select-sm" data-choices
                                        data-choices-search="true" data-choices-placeholder="Select a station...">
                                        <option value="">All Stations</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small mb-1">
                                        <i class="fas fa-calendar-alt me-1"></i>Date Range
                                    </label>
                                    <input type="text" id="dateRange" class="form-control form-control-sm"
                                        placeholder="Select date range">
                                </div>
                                <div class="col-md-2">
                                    <button id="applyFilterBtn" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-search me-1"></i> Apply
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <button id="resetFilterBtn" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="fas fa-undo-alt me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Summary Cards -->
                        <div class="row g-3 mb-4" id="summaryCardsContainer">
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Total Expense</div>
                                            <div class="stat-value mt-1" id="totalExpenseVal">0.00</div>
                                        </div>
                                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                            <i class="fas fa-arrow-down"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Total Income</div>
                                            <div class="stat-value mt-1" id="totalIncomeVal">0.00</div>
                                        </div>
                                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                                            <i class="fas fa-arrow-up"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Net Balance</div>
                                            <div class="stat-value mt-1" id="netBalanceVal">0.00</div>
                                        </div>
                                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                                            <i class="fas fa-chart-simple"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Transactions</div>
                                            <div class="stat-value mt-1" id="transactionsCount">0</div>
                                        </div>
                                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="fas fa-receipt"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Station Badge (when single station selected) -->
                        <div id="stationInfoBadge" class="mb-3 no-print" style="display: none;">
                            <span class="badge-station">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                Showing: <strong id="selectedStationName"></strong>
                            </span>
                        </div>

                        <!-- Data Table -->
                        <div class="table-responsive">
                            <table id="expenseTable" class="table table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="20%">Site</th>
                                        <th width="15%">Type</th>
                                        <th width="25%">Note</th>
                                        <th width="15%">Expense</th>
                                        <th width="20%">Income</th>
                                        <th width="5%" class="no-print">Action</th>
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

    <!-- Receive Transaction Modal -->
    <div class="modal fade" id="receiveModal" tabindex="-1" aria-labelledby="receiveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiveModalLabel">
                        <i class="fas fa-check-circle me-2 text-success"></i>Receive Transaction
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="receiveForm">
                        <input type="hidden" id="receive_transaction_id">
                        <input type="hidden" id="receive_amount">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Transaction Details</label>
                            <div class="p-3 bg-light rounded">
                                <p class="mb-1"><strong>Station:</strong> <span id="receive_station_name"></span></p>
                                <p class="mb-1"><strong>Note:</strong> <span id="receive_note"></span></p>
                                <p class="mb-0"><strong>Amount:</strong> <span id="receive_amount_display"
                                        class="fw-bold text-primary"></span></p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold required-label">Select Shift</label>
                            <select id="receive_shift_id" class="form-select" required>
                                <option value="">Loading shifts...</option>
                            </select>
                            <div class="invalid-feedback">Please select a shift</div>
                        </div>

                        <!-- ✅ NEW: Method Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold required-label">Payment Method</label>
                            <select id="receive_method_select" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                            </select>
                            <div class="invalid-feedback">Please select a payment method</div>
                        </div>

                        <div class="mb-3" id="bank_account_section" style="display: none;">
                            <label class="form-label fw-semibold required-label">Select Bank Account</label>
                            <select id="receive_bank_account" class="form-select">
                                <option value="">Loading bank accounts...</option>
                            </select>
                            <div class="invalid-feedback">Please select a bank account</div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmReceiveBtn">
                        <i class="fas fa-check me-1"></i> Confirm Receive
                    </button>
                </div>
            </div>
        </div>
    </div>
    </main>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


    <script>
        const AUTH_USER_ID = "{{ Auth::id() }}";
        const AUTH_ROLE = "{{ Auth::user()->role }}";

        let currentTable = null;
        let currentFilters = { station_id: '', start_date: '', end_date: '' };
        let currentData = [];
        let choicesInstance = null;
        let currentReceiveTransaction = null;

        // Date helpers
        function getCurrentMonthRange() {
            const now = new Date();
            const start = new Date(now.getFullYear(), now.getMonth(), 1);
            const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            return { start_date: formatDate(start), end_date: formatDate(end) };
        }

        function formatDate(date) {
            let d = new Date(date);
            return d.toISOString().split('T')[0];
        }

        function formatDateDisplay(date) {
            if (!date) return '';
            let d = new Date(date);
            return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        // Toast function
        function showToast(message, type = 'success') {
            const toastHtml = `
                        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 mb-2" role="alert">
                            <div class="d-flex">
                                <div class="toast-body">${message}</div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                            </div>
                        </div>
                    `;
            const container = $('#toastContainer');
            if (container.length === 0) {
                $('body').append('<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100"></div>');
            }
            $('#toastContainer').append(toastHtml);
            const toastElement = $('.toast').last()[0];
            const bsToast = new bootstrap.Toast(toastElement, { delay: 3000 });
            bsToast.show();
            $(toastElement).on('hidden.bs.toast', function () { $(this).remove(); });
        }

        // Initialize Choices.js for station select
        function initChoices() {
            // Comment out Choices initialization to use normal select
            // const stationSelect = document.getElementById('stationSelect');
            // if (stationSelect) {
            //     if (choicesInstance) choicesInstance.destroy();
            //     choicesInstance = new Choices(stationSelect, {
            //         searchEnabled: true,
            //         placeholder: true,
            //         placeholderValue: 'All Stations',
            //         searchPlaceholderValue: 'Type to search...',
            //         noResultsText: 'No stations found',
            //         noChoicesText: 'No stations available',
            //         itemSelectText: '',
            //         shouldSort: true,
            //         position: 'auto'
            //     });
            // }

            // Use normal select instead
            choicesInstance = null;
            console.log("Choices.js disabled, using normal select");
        }


        // ✅ Listen to station selection change
        // $('#stationSelect').on('change', function () {
        //     var selectedVal = $(this).val();
        //     console.log("Station select changed to:", selectedVal);
        //     if (choicesInstance) {
        //         var selectedValue = choicesInstance.getValue(true);
        //         console.log("Choices selected value:", selectedValue);
        //         currentFilters.station_id = selectedValue && selectedValue.value ? selectedValue.value : '';
        //     } else {
        //         currentFilters.station_id = selectedVal;
        //     }
        //     console.log("Updated currentFilters.station_id:", currentFilters.station_id);

        //     // Auto apply filter when station changes
        //     applyFilters();
        // });


        // Load stations from API based on role
        function loadStations() {
            let apiUrl = '/api/stations';
            if (AUTH_ROLE === 'employee') {
                apiUrl = `/api/stations-employee/${AUTH_USER_ID}`;
            } else if (AUTH_ROLE === 'owner' || AUTH_ROLE === 'admin') {
                apiUrl = `/api/stations/${AUTH_USER_ID}`;
            }

            console.log("Loading stations from:", apiUrl, "Role:", AUTH_ROLE);

            $.ajax({
                url: apiUrl,
                method: "GET",
                success: function (stations) {
                    let options = '<option value="">All Stations</option>';
                    if (Array.isArray(stations)) {
                        stations.forEach(station => {
                            options += `<option value="${station.id}">${station.name}</option>`;
                        });
                    }
                    $('#stationSelect').html(options);
                    initChoices();

                    if (AUTH_ROLE === 'employee' && stations.length > 0) {
                        if (choicesInstance) {
                            choicesInstance.setChoiceByValue(stations[0].id.toString());
                        } else {
                            $('#stationSelect').val(stations[0].id);
                        }
                        setTimeout(() => { applyFilters(); }, 500);
                    }
                },
                error: function (xhr) {
                    console.error("Error loading stations:", xhr);
                    $('#stationSelect').html('<option value="">Error loading stations</option>');
                    initChoices();
                }
            });
        }

        // Build API URL with filters - ALWAYS use expense-sheet endpoint
        function buildApiUrl() {
            let baseUrl = "/api/sales/expense-sheet";
            let params = [];

            console.log("Building URL with currentFilters:", currentFilters);

            // ✅ Add user_id for role-based filtering
            if (AUTH_ROLE === 'owner' || AUTH_ROLE === 'admin') {
                params.push(`user_id=${AUTH_USER_ID}`);
            }

            // ✅ FIX: Add station filter if selected (string comparison)
            if (currentFilters.station_id && currentFilters.station_id !== '' && currentFilters.station_id !== 'null') {
                params.push(`station_id=${currentFilters.station_id}`);
                console.log("✅ Adding station filter:", currentFilters.station_id);
            } else {
                console.log("❌ No station filter - station_id is:", currentFilters.station_id);
            }

            // Add date filters
            if (currentFilters.start_date && currentFilters.start_date !== '') {
                params.push(`start_date=${currentFilters.start_date}`);
            }
            if (currentFilters.end_date && currentFilters.end_date !== '') {
                params.push(`end_date=${currentFilters.end_date}`);
            }

            if (params.length > 0) baseUrl += '?' + params.join('&');
            console.log("Final API URL:", baseUrl);
            return baseUrl;
        }




        // Update statistics cards
        function updateStats(data) {
            let totalExpense = 0, totalIncome = 0;
            data.forEach(item => {
                totalExpense += parseFloat(item.total_expense) || 0;
                totalIncome += parseFloat(item.total_income) || 0;
            });
            let netBalance = totalIncome - totalExpense;

            $('#totalExpenseVal').text(totalExpense.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#totalIncomeVal').text(totalIncome.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#netBalanceVal').text(netBalance.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#transactionsCount').text(data.length);

            if (netBalance >= 0) {
                $('#netBalanceVal').removeClass('text-danger').addClass('text-success');
            } else {
                $('#netBalanceVal').removeClass('text-success').addClass('text-danger');
            }
        }

        // Update station badge
        function updateStationBadge() {
            if (currentFilters.station_id && currentFilters.station_id !== '') {
                let stationName = '';
                if (choicesInstance) {
                    const selectedValue = choicesInstance.getValue(true);
                    stationName = selectedValue && selectedValue.label ? selectedValue.label : $('#stationSelect option:selected').text();
                } else {
                    stationName = $('#stationSelect option:selected').text();
                }
                if (stationName && stationName !== 'All Stations') {
                    $('#selectedStationName').text(stationName);
                    $('#stationInfoBadge').fadeIn();
                } else {
                    $('#stationInfoBadge').fadeOut();
                }
            } else {
                $('#stationInfoBadge').fadeOut();
            }
        }

        // Load open shifts for station
        function loadOpenShiftsForStation(stationId) {
            console.log("Loading shifts for station:", stationId);
            if (!stationId) {
                $('#receive_shift_id').html('<option value="">No station selected</option>');
                return;
            }
            $.ajax({
                url: `/api/stations/${stationId}/open-shifts`,
                method: "GET",
                success: function (response) {
                    let options = '<option value="">Select shift...</option>';
                    if (response.data && Array.isArray(response.data) && response.data.length > 0) {
                        response.data.forEach(shift => {
                            options += `<option value="${shift.id}">Shift #${shift.shift_no} - ${shift.start_time}</option>`;
                        });
                    } else if (Array.isArray(response) && response.length > 0) {
                        response.forEach(shift => {
                            options += `<option value="${shift.id}">Shift #${shift.shift_no} - ${shift.start_time}</option>`;
                        });
                    } else {
                        options = '<option value="">No open shifts found</option>';
                    }
                    $('#receive_shift_id').html(options);
                },
                error: function (xhr) {
                    console.error("Error loading shifts:", xhr);
                    $('#receive_shift_id').html('<option value="">Error loading shifts</option>');
                }
            });
        }

        // Load bank accounts for station
        function loadBankAccountsForStation(stationId) {
            console.log("Loading bank accounts for station:", stationId);
            if (!stationId) {
                $('#receive_bank_account').html('<option value="">No station selected</option>');
                return;
            }
            $.ajax({
                url: `/api/stations/${stationId}/bank-accounts`,
                method: "GET",
                success: function (accounts) {
                    let options = '<option value="">Select bank account...</option>';
                    if (Array.isArray(accounts) && accounts.length > 0) {
                        accounts.forEach(account => {
                            let currentAmount = account.current_amount || 0;
                            options += `<option value="${account.id}" data-current-amount="${currentAmount}">
                                        ${account.name} - ${account.account_number || 'N/A'} (Balance: ${currentAmount.toFixed(2)})
                                    </option>`;
                        });
                    } else {
                        options = '<option value="">No bank accounts found</option>';
                    }
                    $('#receive_bank_account').html(options);
                },
                error: function (xhr) {
                    console.error("Error loading bank accounts:", xhr);
                    $('#receive_bank_account').html('<option value="">Error loading bank accounts</option>');
                }
            });
        }

        // Function to open receive modal
        function openReceiveModal(id, stationName, stationId, note, amount, originalMethod) {
            currentReceiveTransaction = { id, station_name: stationName, station_id: stationId, note, amount, original_method: originalMethod };
            $('#receive_transaction_id').val(id);
            $('#receive_amount').val(amount);
            $('#receive_station_name').text(stationName);
            $('#receive_note').text(note);
            $('#receive_amount_display').text(parseFloat(amount).toFixed(2));
            $('#receive_method_select').val(originalMethod);
            loadOpenShiftsForStation(stationId);
            handleMethodChange();
            $('#receiveModal').modal('show');
        }

        // Handle method selection change
        function handleMethodChange() {
            const selectedMethod = $('#receive_method_select').val();
            const stationId = currentReceiveTransaction?.station_id;
            if (selectedMethod === 'bank') {
                $('#bank_account_section').show();
                if (stationId) loadBankAccountsForStation(stationId);
            } else {
                $('#bank_account_section').hide();
                $('#receive_bank_account').val('');
            }
        }

        // Generate PDF
        function generatePDF() {
            const element = document.createElement('div');
            element.innerHTML = `
                        <div style="padding: 20px; font-family: Arial, sans-serif;">
                            <h2 style="text-align: center; color: #2c3e50; margin-bottom: 10px;">Expense Sheet Report</h2>
                            <p style="text-align: center; color: #666; margin-bottom: 30px;">Generated on: ${new Date().toLocaleString()}</p>

                            <div style="margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                                <h3 style="margin-top: 0; font-size: 14px;">Filter Information</h3>
                                <p style="margin: 5px 0;"><strong>Station:</strong> ${currentFilters.station_id ? ($('#stationSelect option:selected').text() || 'Selected Station') : 'All Stations'}</p>
                                <p style="margin: 5px 0;"><strong>Date Range:</strong> ${formatDateDisplay(currentFilters.start_date)} - ${formatDateDisplay(currentFilters.end_date)}</p>
                            </div>

                            <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
                                <tr style="background: #f8f9fa;">
                                    <th style="border: 1px solid #ddd; padding: 10px; text-align: center;">Total Expense</th>
                                    <th style="border: 1px solid #ddd; padding: 10px; text-align: center;">Total Income</th>
                                    <th style="border: 1px solid #ddd; padding: 10px; text-align: center;">Net Balance</th>
                                    <th style="border: 1px solid #ddd; padding: 10px; text-align: center;">Transactions</th>
                                </tr>
                                <tr style="background: #f8f9fa;">
                                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center; color: #dc3545; font-weight: bold;">${$('#totalExpenseVal').text()}</td>
                                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center; color: #28a745; font-weight: bold;">${$('#totalIncomeVal').text()}</td>
                                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center; font-weight: bold;">${$('#netBalanceVal').text()}</td>
                                    <td style="border: 1px solid #ddd; padding: 10px; text-align: center;">${$('#transactionsCount').text()}</td>
                                </tr>
                            </table>

                            <h3 style="font-size: 14px; margin-bottom: 10px;">Transaction Details</h3>
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #2c3e50; color: white;">
                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">#</th>
                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Site</th>
                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Type</th>
                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Note</th>
                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Expense</th>
                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Income</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${currentData.map((item, idx) => `
                                        <tr>
                                            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${idx + 1}</td>
                                            <td style="border: 1px solid #ddd; padding: 8px;">${item.station_name || 'N/A'}</td>
                                            <td style="border: 1px solid #ddd; padding: 8px;">${item.type || 'N/A'}</td>
                                            <td style="border: 1px solid #ddd; padding: 8px;">${item.note || '-'}</td>
                                            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">${(parseFloat(item.total_expense) || 0).toFixed(2)}</td>
                                            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">${(parseFloat(item.total_income) || 0).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                            <p style="text-align: right; margin-top: 20px; font-size: 10px;">Total Records: ${currentData.length}</p>
                            <p style="text-align: right; font-size: 10px;">Generated from Expense Sheet System</p>
                        </div>
                    `;

            const opt = {
                margin: [0.5, 0.5, 0.5, 0.5],
                filename: `Expense_Sheet_${formatDateDisplay(currentFilters.start_date)}_to_${formatDateDisplay(currentFilters.end_date)}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, letterRendering: true },
                jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save();
        }

        // Print report
        function printReport() {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                        <html>
                        <head><title>Expense Sheet Report</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                            th { background: #f2f2f2; }
                            .header { text-align: center; margin-bottom: 30px; }
                            .summary { margin: 20px 0; }
                            .summary table { width: auto; margin: 0 auto; }
                        </style>
                        </head>
                        <body>
                            <div class="header">
                                <h2>Expense Sheet Report</h2>
                                <p>Generated on: ${new Date().toLocaleString()}</p>
                            </div>
                            <div class="filters">
                                <p><strong>Station:</strong> ${currentFilters.station_id ? ($('#stationSelect option:selected').text() || 'Selected Station') : 'All Stations'}</p>
                                <p><strong>Date Range:</strong> ${formatDateDisplay(currentFilters.start_date)} - ${formatDateDisplay(currentFilters.end_date)}</p>
                            </div>
                            <div class="summary">
                                <table border="1" cellpadding="8" cellspacing="0">
                                    <tr><th>Total Expense</th><td>${$('#totalExpenseVal').text()}</td></tr>
                                    <tr><th>Total Income</th><td>${$('#totalIncomeVal').text()}</td></tr>
                                    <tr><th>Net Balance</th><td>${$('#netBalanceVal').text()}</td></tr>
                                </table>
                            </div>
                            <h3>Transaction Details</h3>
                            <table border="1" cellpadding="8" cellspacing="0">
                                <thead>
                                    <tr><th>#</th><th>Site</th><th>Type</th><th>Note</th><th>Expense</th><th>Income</th></tr>
                                </thead>
                                <tbody>
                                    ${currentData.map((item, idx) => `
                                        <tr>
                                            <td>${idx + 1}</td>
                                            <td>${item.station_name || 'N/A'}</td>
                                            <td>${item.type || 'N/A'}</td>
                                            <td>${item.note || '-'}</td>
                                            <td>${(parseFloat(item.total_expense) || 0).toFixed(2)}</td>
                                            <td>${(parseFloat(item.total_income) || 0).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                            <p>Total Records: ${currentData.length}</p>
                        </body>
                        </html>
                    `);
            printWindow.document.close();
            printWindow.print();
        }

        // Initialize DataTable
        function initExpenseTable() {
            const apiUrl = buildApiUrl();
            console.log("Loading from:", apiUrl);
            if (currentTable) currentTable.destroy();

            currentTable = $('#expenseTable').DataTable({
                ajax: {
                    url: apiUrl,
                    dataSrc: function (json) {
                        console.log("Data received:", json);
                        const processedData = (json || []).map(row => ({
                            ...row,
                            total_expense: row.total_expense || 0,
                            total_income: row.total_income || 0,
                            station_name: row.station_name || 'N/A',
                            note: row.note || '-',
                            method: row.method || 'cash',
                            is_testing: row.is_testing || 0
                        }));
                        currentData = processedData;
                        updateStats(currentData);
                        updateStationBadge();
                        return processedData;
                    },
                    error: function (xhr, status, error) {
                        console.error("DataTable AJAX error:", error);
                        currentData = [];
                        updateStats(currentData);
                        updateStationBadge();
                        return [];
                    }
                },
                columns: [
                    { data: null, render: (data, type, row, meta) => meta.row + 1 },
                    { data: "station_name", defaultContent: "N/A" },
                    { data: "type", defaultContent: "N/A" },
                    { data: "note", defaultContent: "-" },
                    { data: "total_expense", defaultContent: "0.00", render: (data) => `<span class="text-danger fw-semibold">${(parseFloat(data) || 0).toFixed(2)}</span>` },
                    { data: "total_income", defaultContent: "0.00", render: (data) => `<span class="text-success fw-semibold">${(parseFloat(data) || 0).toFixed(2)}</span>` },
                    {
                        data: null, render: function (data, type, row) {
                            if (row.is_testing == 1) {
                                return `<button class="btn btn-sm btn-success receive-btn" data-id="${row.id}" data-station="${row.station_name}" data-station-id="${row.station_id}" data-note="${row.note || '-'}" data-amount="${row.total_income > 0 ? row.total_income : row.total_expense}" data-method="${row.method || 'cash'}"><i class="fas fa-hand-holding-usd me-1"></i> Receive</button>`;
                            } else if (row.is_testing == 2) {
                                return `<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Received</span>`;
                            } else {
                                return `<span class="badge bg-secondary">Normal</span>`;
                            }
                        }, orderable: false
                    }
                ],
                responsive: true,
                pageLength: 10,
                order: [[1, 'asc']],
                language: { search: "Search:", lengthMenu: "Show _MENU_ entries", info: "Showing _START_ to _END_ of _TOTAL_ entries", emptyTable: "No expense data found for the selected filters" },
                dom: '<"d-flex justify-content-between align-items-center"lf>tip'
            });

            $('#expenseTable').on('click', '.receive-btn', function () {
                openReceiveModal($(this).data('id'), $(this).data('station'), $(this).data('station-id'), $(this).data('note'), $(this).data('amount'), $(this).data('method'));
            });
        }

        // Confirm receive button click
        $('#confirmReceiveBtn').on('click', function () {
            const transactionId = $('#receive_transaction_id').val();
            const shiftId = $('#receive_shift_id').val();
            const selectedMethod = $('#receive_method_select').val();
            const accountId = selectedMethod === 'bank' ? $('#receive_bank_account').val() : null;

            if (!shiftId) {
                $('#receive_shift_id').addClass('is-invalid');
                showToast('Please select a shift', 'error');
                return;
            }
            if (selectedMethod === 'bank' && !accountId) {
                $('#receive_bank_account').addClass('is-invalid');
                showToast('Please select a bank account', 'error');
                return;
            }

            const confirmBtn = $(this);
            const originalText = confirmBtn.html();
            confirmBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');
            confirmBtn.prop('disabled', true);

            $.ajax({
                url: `/api/transactions/receive/${transactionId}`,
                method: "PUT",
                contentType: "application/json",
                data: JSON.stringify({ shift_id: shiftId, account_id: accountId, method: selectedMethod }),
                success: function (response) {
                    showToast(response.message || 'Transaction received successfully!', 'success');
                    $('#receiveModal').modal('hide');
                    if (currentTable) currentTable.ajax.reload();
                    $('#receive_shift_id').val('').removeClass('is-invalid');
                    $('#receive_bank_account').val('').removeClass('is-invalid');
                    $('#receive_method_select').val('cash');
                    $('#bank_account_section').hide();
                },
                error: function (xhr) {
                    console.error("Error:", xhr);
                    showToast(xhr.responseJSON?.message || 'Failed to receive transaction', 'error');
                },
                complete: function () {
                    confirmBtn.html(originalText);
                    confirmBtn.prop('disabled', false);
                }
            });
        });

        // Event listeners
        $('#receive_method_select').on('change', function () { handleMethodChange(); });
        $('#receive_shift_id').on('change', function () { $(this).removeClass('is-invalid'); });
        $('#receive_bank_account').on('change', function () { $(this).removeClass('is-invalid'); });

        // Apply filters
        function applyFilters() {
            console.log("Applying filters...");

            // ✅ FIX: Get station value from Choices instance - CORRECT WAY
            if (choicesInstance) {
                // Get the selected value from Choices
                const selectedChoice = choicesInstance.getValue(true);
                console.log("Selected choice object:", selectedChoice);

                // Handle different return types from Choices
                if (selectedChoice && typeof selectedChoice === 'object') {
                    // If it returns an object with value property
                    currentFilters.station_id = selectedChoice.value || '';
                } else if (selectedChoice) {
                    // If it returns just the value
                    currentFilters.station_id = selectedChoice.toString();
                } else {
                    currentFilters.station_id = '';
                }

                console.log("Selected station ID from Choices:", currentFilters.station_id);
            } else {
                currentFilters.station_id = $('#stationSelect').val() || '';
                console.log("Selected station from select:", currentFilters.station_id);
            }

            // Get date range
            const dateRange = $('#dateRange').val();
            console.log("Date range value:", dateRange);

            if (dateRange && dateRange.includes(' to ')) {
                const dates = dateRange.split(' to ');
                currentFilters.start_date = dates[0].trim();
                currentFilters.end_date = dates[1].trim();
            } else {
                const monthRange = getCurrentMonthRange();
                currentFilters.start_date = monthRange.start_date;
                currentFilters.end_date = monthRange.end_date;
                $('#dateRange').val(`${monthRange.start_date} to ${monthRange.end_date}`);
            }

            console.log("Final filters BEFORE API call:", currentFilters);

            // ✅ FIX: Reload the table with new filters
            if (currentTable) {
                currentTable.ajax.url(buildApiUrl()).load();
            } else {
                initExpenseTable();
            }
        }



        function resetFilters() {
            console.log("Resetting filters...");
            if (choicesInstance) choicesInstance.setChoiceByValue('');
            else $('#stationSelect').val('');

            const monthRange = getCurrentMonthRange();
            currentFilters = { station_id: '', start_date: monthRange.start_date, end_date: monthRange.end_date };
            $('#dateRange').val(`${monthRange.start_date} to ${monthRange.end_date}`);

            if (currentTable) currentTable.ajax.url(buildApiUrl()).load();
            else initExpenseTable();
        }

        // Initialize page
        $(document).ready(function () {
            flatpickr("#dateRange", { mode: "range", dateFormat: "Y-m-d" });
            loadStations();
            const defaultMonth = getCurrentMonthRange();
            currentFilters.start_date = defaultMonth.start_date;
            currentFilters.end_date = defaultMonth.end_date;
            $('#dateRange').val(`${defaultMonth.start_date} to ${defaultMonth.end_date}`);
            setTimeout(() => { initExpenseTable(); }, 300);
            $('#applyFilterBtn').on('click', applyFilters);
            $('#resetFilterBtn').on('click', resetFilters);
            $('#pdfReportBtn').on('click', generatePDF);
            $('#printReportBtn').on('click', printReport);

            // ✅ ONLY ONE change handler - keep this one
            $('#stationSelect').on('change', function () {
                console.log("Station dropdown changed");
                applyFilters();
            });
        });

    </script>
@endsection