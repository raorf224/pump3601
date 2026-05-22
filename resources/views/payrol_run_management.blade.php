@extends('partials.layouts.master')

@section('title', 'Payrol Run Management | ' . Auth::user()->full_name)
@section('title-sub', 'HR Management')
@section('pagetitle', 'Payrol Run Management')

@section('css')
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <!-- Add DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <style>
        .choices__inner {
            min-height: 45px;
            border-radius: 0.375rem !important;
            border: 1px solid #ced4da !important;
        }

        .calculation-results {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
        
        /* ✅ Custom Toastr Styles */
        .toast-error {
            background-color: #dc3545 !important;
            color: white !important;
        }
        
        .toast-success {
            background-color: #198754 !important;
            color: white !important;
        }
        
        .toast-warning {
            background-color: #ffc107 !important;
            color: black !important;
        }
        
        .toast-info {
            background-color: #0dcaf0 !important;
            color: black !important;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <input type="text" id="tableSearch" class="form-control w-25 rounded-pill" placeholder="Search...">
                        <button class="btn btn-primary rounded-pill px-3" data-bs-toggle="modal"
                            data-bs-target="#addPayrunModal">
                            <i class="bi bi-plus-lg me-1"></i> Create Payrun
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="payrunTable" class="table align-middle w-100">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Site</th>
                                    <th>Title</th>
                                    <th>Frequency</th>
                                    <th>Pay Period</th>
                                    <th>Pay Date</th>
                                    <th>Employees</th>
                                    <th>Gross Pay</th>
                                    <th>Net Pay</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payrun Modal -->
    <div class="modal fade" id="addPayrunModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="addPayrunForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Payrun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Site</label>
                        <select id="stationSelect" name="station_id" required></select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payrun Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. November Monthly Payroll"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Frequency</label>
                        <select name="frequency" id="frequencySelect" class="form-select" required>
                            <option value="" disabled selected>Select Frequency</option>
                            <option value="Daily">Daily</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Monthly">Monthly</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Employees</label>
                        <select id="employeeSelect" name="employee_ids[]" multiple required></select>
                    </div>

                    <!-- Calculation Results -->
                    <div class="calculation-results" id="calculationResults" style="display: none;">
                        <h6 class="mb-3">Salary Calculation Summary</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Total Base Salary</label>
                                <input type="text" id="totalBaseSalary" class="form-control" readonly>
                            </div>

                            <div class="col md-6">
                                <div class="col-md-6">
                                    <label class="form-label">Net Pay</label>
                                    <input type="text" id="netPay" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Total Earnings</label>
                                <input type="text" id="totalEarnings" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Total Deductions</label>
                                <input type="text" id="totalDeductions" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gross Pay</label>
                                <input type="text" id="grossPay" class="form-control" readonly>
                            </div>
                        </div>

                        <!-- Attendance Breakdown -->
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Attendance Deductions</label>
                                <input type="text" id="attendanceDeduction" class="form-control bg-warning bg-opacity-10"
                                    readonly>
                                <small class="text-muted">Based on absent, late, and half days</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Pay Period Start</label>
                            <input type="text" id="payPeriodStart" name="pay_period_start" class="form-control datepicker"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pay Period End</label>
                            <input type="text" id="payPeriodEnd" name="pay_period_end" class="form-control datepicker"
                                required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pay Date</label>
                        <input type="text" id="payDate" name="pay_date" class="form-control datepicker" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Completed">Completed</option>
                            <option value="Draft">Draft</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Payrol</button>
                </div>
            </form>
        </div>
    </div>


    </main>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- Add DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

    <script>
        console.log("✅ Payrun script loaded");

        let stationChoices, employeeChoices;
        const AUTH_USER_ID = "{{ Auth::id() }}";
        const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";
        let employeesCache = [];

        // ✅ On ready
 // ✅ On ready - ADD TOASTR CONFIG
$(document).ready(function () {
    // Configure toastr globally
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    
    initDatepickers();
    loadStations();
    loadAllEmployees();
    initDataTable();
});

        // ✅ Initialize DataTable
        // ✅ Initialize DataTable
function initDataTable() {
    // Determine API URL based on role
    let apiUrl;
    if (AUTH_ROLE === 'admin') {
        apiUrl = '/api/payroll-management'; // admin sees all
    } else {
        apiUrl = `/api/payroll-management/${AUTH_USER_ID}`; // owner sees only their stations
    }

    payrunTable = $('#payrunTable').DataTable({
        ajax: {
            url: apiUrl,
            error: function (xhr) {
                console.error('Error loading payroll data:', xhr);
                showToast("❌ Error loading payroll data", true);
            }
        },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'station_name' },
            { data: 'title' },
            { data: 'frequency' },
            { data: 'period_start', render: (data, type, row) => `${data} to ${row.period_end}` },
            { data: 'pay_date' },
            { data: 'employee_count', render: data => `${data} Employee${data > 1 ? 's' : ''}` },
            { data: 'total_gross_pay', render: data => 'Rs. ' + Number(data).toLocaleString() },
            { data: 'total_net_pay', render: data => 'Rs. ' + Number(data).toLocaleString() },
            {
                data: 'status',
                render: (data) => {
                    const badgeClass = data === 'Completed' ? 'bg-success' : 'bg-warning';
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            {
                data: null,
                className: 'text-center',
                render: data => `

                    <button class="btn btn-sm btn-danger delete-payrun-btn" data-id="${data.mutli_employes_id}">
                        <i class="bi bi-trash"></i>
                    </button>`
            }
        ],
        searching: false,
        paging: false,
        info: false,
        ordering: false,
        dom: 't',
        language: { emptyTable: "No payroll records found" }
    });
}
  
		
        // ✅ Custom Table search - UPDATED for grouped data
        $('#tableSearch').on('keyup', function () {
            const searchTerm = this.value.toLowerCase().trim();

            payrunTable.rows().every(function () {
                const row = this.node();
                const rowData = this.data();

                // ✅ Convert all row data to string for searching
                const searchableText = [
                    rowData.station_name || '',
                    rowData.title || '',
                    rowData.frequency || '',
                    rowData.period_start || '',
                    rowData.period_end || '',
                    rowData.pay_date || '',
                    rowData.employee_count?.toString() || '',
                    rowData.total_gross_pay?.toString() || '',
                    rowData.total_net_pay?.toString() || '',
                    rowData.status || ''
                ].join(' ').toLowerCase();

                // ✅ Show/hide row based on search match
                if (searchableText.includes(searchTerm)) {
                    $(row).show();
                } else {
                    $(row).hide();
                }
            });
        });

        // ✅ Toast helper
function showToast(msg, isError = false) {
    // Toastr options
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    
    if (isError) {
        toastr.error(msg);
    } else {
        toastr.success(msg);
    }
}

        // ✅ Initialize Datepickers
        function initDatepickers() {
            const englishLocale = {
                days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                daysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                daysMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                today: 'Today', clear: 'Clear', dateFormat: 'yyyy-MM-dd'
            };

            ['#payPeriodStart', '#payPeriodEnd', '#payDate'].forEach(sel => {
                new AirDatepicker(sel, { locale: englishLocale, autoClose: true, dateFormat: 'yyyy-MM-dd' });
            });
        }

        // ✅ Load Stations
        function loadStations(selectedId = null, isEdit = false) {
            let url = "";
            if (AUTH_ROLE === "admin") url = "/api/stations";
            else if (AUTH_ROLE === "employee") url = `/api/stations_emp/${AUTH_USER_ID}`;
            else url = `/api/stations/${AUTH_USER_ID}`;

            $.ajax({
                url: url, method: "GET",
                success: function (stations) {
                    const sel = isEdit ? $("#editStationSelect") : $("#stationSelect");
                    sel.empty().append('<option value="">All Stations</option>');
                    stations.forEach(st => sel.append(`<option value="${st.id}">${st.name}</option>`));

                    if (isEdit) {
                        if (window.editStationChoices) window.editStationChoices.destroy();
                        window.editStationChoices = new Choices("#editStationSelect", { searchEnabled: true, shouldSort: false });
                        if (selectedId) window.editStationChoices.setChoiceByValue(selectedId.toString());
                    } else {
                        if (stationChoices) stationChoices.destroy();
                        stationChoices = new Choices("#stationSelect", { searchEnabled: true, shouldSort: false });
                        if (selectedId) stationChoices.setChoiceByValue(selectedId.toString());
                    }

                    sel.off("change").on("change", function () {
                        const stationId = $(this).val();
                        if (stationId) loadEmployeesByStation(stationId, isEdit);
                        else loadAllEmployees(isEdit);
                    });
                },
                error: function () { showToast("❌ Error loading stations", true); }
            });
        }

        // ✅ Load ALL employees
        function loadAllEmployees(isEdit = false) {
            let url = "";
            if (AUTH_ROLE === "admin") url = "/api/employees";
            else if (AUTH_ROLE === "employee")
                url = `/api/stations_emp/${AUTH_USER_ID}`;
            else url = `/api/user-employees/${AUTH_USER_ID}`;

            $.ajax({
                url: url, method: "GET",
                success: function (res) {
                    const arr = Array.isArray(res) ? res : (res.data || []);
                    employeesCache = arr;
                    populateEmployeeDropdown(arr, isEdit);
                },
                error: function () { showToast("❌ Error loading employees", true); }
            });
        }

        // ✅ Load Employees by station
        function loadEmployeesByStation(stationId, isEdit = false) {
            $.ajax({
                url: `/api/employees/station/${stationId}`,
                method: "GET",
                success: function (res) {
                    const arr = Array.isArray(res) ? res : (res.data || []);
                    employeesCache = arr;
                    populateEmployeeDropdown(arr, isEdit);
                },
                error: function () { showToast("❌ Error loading employees for this station", true); }
            });
        }

        // ✅ Populate Employee Dropdown - WITH AUTO DATE FILLING
        function populateEmployeeDropdown(arr, isEdit = false) {
            const target = isEdit ? "#editEmployeeSelect" : "#employeeSelect";
            if (isEdit && window.editEmployeeChoices) window.editEmployeeChoices.destroy();
            if (!isEdit && employeeChoices) employeeChoices.destroy();

            const newChoices = new Choices(target, {
                removeItemButton: true, searchEnabled: true,
                placeholderValue: 'Select Employees', shouldSort: false
            });

            newChoices.setChoices(
                arr.map(emp => ({
                    value: emp.employee_id || emp.id,
                    label: emp.user_full_name || emp.full_name || emp.username || "Unknown"
                })), 'value', 'label', true
            );

            // ✅ FIXED: Use Choices.js built-in events WITH AUTO DATE FILLING
            if (!isEdit) {
                employeeChoices = newChoices;

                // Listen for choice selection
                employeeChoices.passedElement.element.addEventListener('change', function (event) {
                    const selectedEmployeeIds = employeeChoices.getValue(true);
                    console.log("🔄 EMPLOYEES SELECTED:", selectedEmployeeIds);

                    if (selectedEmployeeIds.length > 0) {
                        // ✅ AUTO-FILL DATES IF EMPTY
                        autoFillDates();
                        calculateSalary();
                    } else {
                        $('#calculationResults').hide();
                    }
                });

                // Also listen for choice removal
                employeeChoices.passedElement.element.addEventListener('removeItem', function (event) {
                    setTimeout(() => {
                        const selectedEmployeeIds = employeeChoices.getValue(true);
                        console.log("🗑️ EMPLOYEE REMOVED, REMAINING:", selectedEmployeeIds);

                        if (selectedEmployeeIds.length > 0) {
                            calculateSalary();
                        } else {
                            $('#calculationResults').hide();
                        }
                    }, 100);
                });
            } else {
                window.editEmployeeChoices = newChoices;
            }
        }

        // ✅ Auto-fill dates function
        // ✅ Auto-fill dates function BASED ON FREQUENCY
        function autoFillDates() {
            const today = new Date();
            const frequency = $('#frequencySelect').val();
            const periodStart = $('#payPeriodStart').val();
            const periodEnd = $('#payPeriodEnd').val();
            const payDate = $('#payDate').val();

            console.log("📅 AUTO-FILLING DATES FOR FREQUENCY:", frequency);

            if (!frequency) {
                console.log("⚠️ NO FREQUENCY SELECTED");
                return;
            }

            // ✅ Auto-fill based on frequency
            switch (frequency) {
                case 'Monthly':
                    // Monthly: 1st to last day of current month
                    if (!periodStart) {
                        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                        $('#payPeriodStart').val(formatDate(firstDay));
                        console.log("📅 MONTHLY - PERIOD START:", formatDate(firstDay));
                    }
                    if (!periodEnd) {
                        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                        $('#payPeriodEnd').val(formatDate(lastDay));
                        console.log("📅 MONTHLY - PERIOD END:", formatDate(lastDay));
                    }
                    if (!payDate) {
                        const payDay = new Date(today.getFullYear(), today.getMonth() + 1, 5); // 5th of next month
                        $('#payDate').val(formatDate(payDay));
                        console.log("📅 MONTHLY - PAY DATE:", formatDate(payDay));
                    }
                    break;

                case 'Weekly':
                    // Weekly: Monday to Sunday of current week
                    if (!periodStart) {
                        const monday = new Date(today);
                        monday.setDate(today.getDate() - today.getDay() + 1); // Monday
                        $('#payPeriodStart').val(formatDate(monday));
                        console.log("📅 WEEKLY - PERIOD START (Monday):", formatDate(monday));
                    }
                    if (!periodEnd) {
                        const sunday = new Date(today);
                        sunday.setDate(today.getDate() - today.getDay() + 7); // Sunday
                        $('#payPeriodEnd').val(formatDate(sunday));
                        console.log("📅 WEEKLY - PERIOD END (Sunday):", formatDate(sunday));
                    }
                    if (!payDate) {
                        const payDay = new Date(today);
                        payDay.setDate(today.getDate() + 7); // Next week same day
                        $('#payDate').val(formatDate(payDay));
                        console.log("📅 WEEKLY - PAY DATE:", formatDate(payDay));
                    }
                    break;

                case 'Daily':
                    // Daily: Same day for all dates
                    if (!periodStart) {
                        $('#payPeriodStart').val(formatDate(today));
                        console.log("📅 DAILY - PERIOD START:", formatDate(today));
                    }
                    if (!periodEnd) {
                        $('#payPeriodEnd').val(formatDate(today));
                        console.log("📅 DAILY - PERIOD END:", formatDate(today));
                    }
                    if (!payDate) {
                        $('#payDate').val(formatDate(today));
                        console.log("📅 DAILY - PAY DATE:", formatDate(today));
                    }
                    break;
            }
        }

        // ✅ Helper function to format date as YYYY-MM-DD
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // ✅ Calculate Salary WITH ATTENDANCE DEDUCTIONS - ADDED DEBUG LOGS
        function calculateSalary() {
            console.log("🔍 CALCULATE SALARY CALLED");

            const selectedEmployeeIds = employeeChoices ? employeeChoices.getValue(true) : [];
            const frequency = $('#frequencySelect').val();
            const stationId = $('#stationSelect').val();
            const periodStart = $('#payPeriodStart').val();
            const periodEnd = $('#payPeriodEnd').val();

            console.log("📊 CALCULATION PARAMS:", {
                selectedEmployeeIds: selectedEmployeeIds,
                frequency: frequency,
                stationId: stationId,
                periodStart: periodStart,
                periodEnd: periodEnd
            });

            if (selectedEmployeeIds.length === 0 || !frequency || !stationId || !periodStart || !periodEnd) {
                console.log("❌ CALCULATION SKIPPED - Missing required fields");
                $('#calculationResults').hide();
                return;
            }

            let totalBaseSalary = 0;
            let totalEarnings = 0;
            let totalDeductions = 0;
            let totalAttendanceDeductions = 0;

            console.log("📥 FETCHING ASSIGNMENTS...");

            // Get assignments and calculate component-based amounts
            $.get('/api/employee-salary-management-emp', function (assignmentsRes) {
                console.log("✅ ASSIGNMENTS LOADED:", assignmentsRes.data?.length || 0);
                const assignments = assignmentsRes.data || [];

                // Process each employee
                const employeePromises = selectedEmployeeIds.map(empId => {
                    return new Promise((resolve) => {
                        const employee = employeesCache.find(emp => (emp.employee_id || emp.id) == empId);
                        console.log("👤 PROCESSING EMPLOYEE:", employee);

                        if (employee && employee.salary) {
                            const employeeBaseSalary = parseFloat(employee.salary) || 0;
                            totalBaseSalary += employeeBaseSalary;

                            let employeeEarnings = 0;
                            let employeeDeductions = 0;

                            // Process salary components
                            const employeeAssignments = assignments.filter(assignment =>
                                parseInt(assignment.employee_id) === parseInt(empId)
                            );

                            console.log(`📋 EMPLOYEE ${empId} ASSIGNMENTS:`, employeeAssignments.length);

                            employeeAssignments.forEach(assignment => {
                                if (assignment.components) {
                                    const componentsList = assignment.components.split(', ');
                                    componentsList.forEach(componentStr => {
                                        const match = componentStr.match(/^([^(]+) \(([^)]+)\)$/);
                                        if (match) {
                                            const componentName = match[1].trim();
                                            const details = match[2].split(' - ');
                                            if (details.length >= 3) {
                                                const type = details[0].trim();
                                                const calculation = details[1].trim();
                                                const amountStr = details[2].trim();
                                                const amountValue = parseFloat(amountStr.replace(/[^\d.]/g, '')) || 0;
                                                let amount = amountValue;

                                                if (calculation === 'Percentage') {
                                                    amount = (employeeBaseSalary * amountValue) / 100;
                                                }

                                                if (type === 'Earning') {
                                                    employeeEarnings += amount;
                                                    console.log("➕ EARNING:", componentName, amount);
                                                } else if (type === 'Deduction') {
                                                    employeeDeductions += amount;
                                                    console.log("➖ DEDUCTION:", componentName, amount);
                                                }
                                            }
                                        }
                                    });
                                }
                            });

                            totalEarnings += employeeEarnings;
                            totalDeductions += employeeDeductions;

                            console.log(`💰 EMPLOYEE ${empId} SUBTOTAL:`, {
                                base: employeeBaseSalary,
                                earnings: employeeEarnings,
                                deductions: employeeDeductions
                            });

                            // Calculate attendance deduction
                            console.log(`📅 CALCULATING ATTENDANCE FOR EMPLOYEE ${empId}...`);
                            $.ajax({
                                url: '/api/calculate-attendance-deduction',
                                type: 'POST',
                                data: {
                                    employee_id: empId,
                                    station_id: stationId,
                                    period_start: periodStart,
                                    period_end: periodEnd,
                                    monthly_salary: employeeBaseSalary
                                },
                                success: function (attendanceRes) {
                                    if (attendanceRes.success) {
                                        const attendanceDeduction = attendanceRes.data.total_deduction || 0;
                                        totalAttendanceDeductions += attendanceDeduction;
                                        totalDeductions += attendanceDeduction;
                                        console.log(`📊 ATTENDANCE DEDUCTION FOR ${empId}:`, attendanceDeduction);
                                    }
                                    resolve();
                                },
                                error: function (xhr) {
                                    console.error(`❌ ATTENDANCE CALCULATION FAILED FOR ${empId}:`, xhr);
                                    resolve(); // Continue even if attendance calculation fails
                                }
                            });
                        } else {
                            console.log(`❌ EMPLOYEE ${empId} NOT FOUND IN CACHE`);
                            resolve();
                        }
                    });
                });

                // When all employees processed, update UI
                Promise.all(employeePromises).then(() => {
                    console.log("🎯 ALL EMPLOYEES PROCESSED");

                    const grossPay = totalBaseSalary + totalEarnings;
                    let netPay = grossPay - totalDeductions;

                    switch (frequency) {
                        case 'Daily': netPay = netPay / 30; break;
                        case 'Weekly': netPay = netPay / 5; break;
                    }

                    // Update UI
                    $('#totalBaseSalary').data('raw-value', totalBaseSalary).val('Rs. ' + totalBaseSalary.toLocaleString());
                    $('#totalEarnings').data('raw-value', totalEarnings).val('Rs. ' + totalEarnings.toLocaleString());
                    $('#totalDeductions').data('raw-value', totalDeductions).val('Rs. ' + totalDeductions.toLocaleString());
                    $('#grossPay').data('raw-value', grossPay).val('Rs. ' + grossPay.toLocaleString());
                    $('#netPay').data('raw-value', netPay).val('Rs. ' + netPay.toLocaleString());

                    // Show attendance deduction breakdown
                    $('#attendanceDeduction').data('raw-value', totalAttendanceDeductions)
                        .val('Rs. ' + totalAttendanceDeductions.toLocaleString());

                    $('#calculationResults').show();
                    console.log("✅ CALCULATION RESULTS SHOWN");

                    console.log("💰 FINAL CALCULATION WITH ATTENDANCE:", {
                        totalBaseSalary: totalBaseSalary,
                        totalEarnings: totalEarnings,
                        componentDeductions: totalDeductions - totalAttendanceDeductions,
                        attendanceDeductions: totalAttendanceDeductions,
                        totalDeductions: totalDeductions,
                        grossPay: grossPay,
                        netPay: netPay
                    });
                });

            }).fail(function (xhr) {
                console.error('❌ Error loading assignments:', xhr);
                showToast("❌ Error loading employee assignments", true);
            });
        }
        // ✅ Add frequency change event
        $('#frequencySelect').off('change').on('change', function () {
            if (employeeChoices && employeeChoices.getValue(true).length > 0) {
                calculateSalary();
            }
        });

        // ✅ Add Payroll
        $('#addPayrunForm').on('submit', function (e) {
            e.preventDefault();

            if (!employeeChoices || employeeChoices.getValue(true).length === 0) {
                showToast('❌ Please select at least one employee', true);
                return;
            }

            const frequency = $('#frequencySelect').val();
            if (!frequency) {
                showToast('❌ Please select frequency', true);
                return;
            }

            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Creating...');

            const formData = {
                station_id: $('#stationSelect').val(),
                title: $('input[name="title"]').val(),
                frequency: frequency,
                employee_ids: employeeChoices.getValue(true),
                pay_period_start: $('#payPeriodStart').val(),
                pay_period_end: $('#payPeriodEnd').val(),
                pay_date: $('#payDate').val(),
                status: $('select[name="status"]').val(),
            };

            console.log('📤 Submitting payroll data:', formData);

            $.ajax({
                url: '/api/payroll-management/store',
                type: 'POST',
                data: formData,
                success: function (response) {
                    $('#addPayrunModal').modal('hide');
                    payrunTable.ajax.reload();
                    showToast('✅ ' + response.message);
                    submitBtn.prop('disabled', false).html(originalText);
                },
                error: function (xhr) {
                    let errorMessage = 'Failed to create payroll';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        if (typeof xhr.responseJSON.error === 'object') {
                            errorMessage = 'Validation error: ' + Object.values(xhr.responseJSON.error).join(', ');
                        } else {
                            errorMessage = xhr.responseJSON.error;
                        }
                    }
                    showToast('❌ ' + errorMessage, true);
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // ✅ Delete Payroll
        $(document).on('click', '.delete-payrun-btn', function () {
            const payrollId = $(this).data('id');
            if (!confirm('Are you sure you want to delete this payroll record?')) return;

            $.ajax({
                url: `/api/payroll-management/delete/${payrollId}`,
                type: 'DELETE',
                success: function (response) {
                    payrunTable.ajax.reload();
                    showToast('✅ ' + response.message);
                },
                error: function (xhr) {
                    let errorMessage = 'Failed to delete payroll';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    showToast('❌ ' + errorMessage, true);
                }
            });
        });

        // ✅ Reset modal when closed
        $('#addPayrunModal').on('hidden.bs.modal', function () {
            const form = $('#addPayrunForm')[0];
            form.reset();

            if (stationChoices) stationChoices.destroy();
            if (employeeChoices) employeeChoices.destroy();

            loadAllEmployees(false);

            $('#payPeriodStart, #payPeriodEnd, #payDate').val('');
            $('#calculationResults').hide();
            initDatepickers();
        });
    </script>
@endsection