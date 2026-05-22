@extends('partials.layouts.master')

@section('title', 'Employee Payroll | ' . Auth::user()->full_name)
@section('title-sub', 'Finance')
@section('pagetitle', 'Employee Payroll')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
<style>
    .required:after {
        content: " *";
        color: red;
    }

    .choices {
        width: 100%;
        font-size: 14px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        min-height: 42px;
    }

    #payrollForm .form-label {
        font-weight: 600;
    }

    #payrollForm .row.g-3 {
        align-items: center;
    }

    #payrollForm .col-2,
    #payrollForm .col-3,
    #payrollForm .col-4 {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .choices__inner {
        min-height: 42px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font-size: 14px;
        padding: 6px 8px;
    }
</style>
@endsection

@section('content')
<div id="layout-wrapper">
    <div class="container-fluid mt-4">
        <div class="accordion accordion-primary accordion-border-box mb-4" id="payrollAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#createPayrollCollapse" aria-expanded="true">
                        <i class="bi bi-person-plus me-2"></i> Create Payroll
                    </button>
                </h2>

                <!-- Toast -->
                <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000;">
                    <div id="mainToast" class="toast align-items-center border-0" role="alert">
                        <div class="d-flex">
                            <div class="toast-body" id="toastMessage"></div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                aria-label="Close"></button>
                        </div>
                    </div>
                </div>

                <div id="createPayrollCollapse" class="accordion-collapse collapse show"
                    data-bs-parent="#payrollAccordion">
                    <div class="accordion-body">
                        <form id="payrollForm" method="POST">
                            @csrf

                            <!-- 🔹 First Row: Station + Employee -->
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Station</label>
                                    <select name="station_id" id="station" class="form-select" required>
                                        <option value="">Select Station</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a station</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="employee_id" class="form-label required">Employee(s)</label>
                                    <div class="d-flex gap-2 w-100">
                                        <select name="employee_id[]" id="employee_id" required></select>
                                        <button type="button" id="toggleMulti"
                                            class="btn btn-sm btn-outline-secondary">Multi</button>
                                    </div>
                                </div>
                            </div>

                            <!-- 🔹 Second Row: Salary + Date + Note -->
                            <div class="row g-3 align-items-start mt-1">
                                <!-- 🔹 Salary Column with Deduction Button -->
                                <div class="col-md-4 d-flex flex-column">
                                    <label for="salary" class="form-label required mb-1">Salary Paid</label>
                                    <div>
                                        <input type="number" name="salary" id="salary" class="form-control mb-2"
                                            readonly required>
                                        <button type="button" id="deductAttendanceBtn"
                                            class="btn btn-outline-danger btn-sm w-100">
                                            <i class="ri-scales-line me-1"></i> Deduct on Attendance
                                        </button>
                                    </div>
                                </div>

                                <!-- 🔹 Payment Date -->
                                <div class="col-md-4">
                                    <label for="payment_date" class="form-label required mb-1">Date</label>
                                    <input type="text" class="form-control" id="payment_date" name="payment_date"
                                        placeholder="Select a date" required>
                                </div>

                                <!-- 🔹 Note -->
                                <div class="col-md-4">
                                    <label for="note" class="form-label mb-1">Note</label>
                                    <input type="text" name="note" id="note" class="form-control"
                                        placeholder="Optional note...">
                                </div>
                            </div>

                            <input type="hidden" id="account_id" name="id">

                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-primary">Pay Salary</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll Table -->
        <div class="card shadow-sm card-hover">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Payroll List</h5>
            </div>
            <div class="card-body">
                <div class="table-box table-responsive">
                    <table id="accountsTable" class="table text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>SNO</th>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Role</th>
                                <th>Station</th>
                                <th>Salary</th>
                                <th>Note</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- 🔹 Deduct on Attendance Modal -->
    <div class="modal fade" id="deductAttendanceModal" tabindex="-1" aria-labelledby="deductAttendanceLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deductAttendanceLabel">
                        <i class="ri-scales-line me-1"></i> Deduct on Attendance
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-muted mb-2">Please select the month you want to apply attendance-based deduction for:
                    </p>

                    <div class="form-group">
                        <label class="form-label fw-semibold">Select Month</label>
                        <input type="month" id="deductionMonth" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeductionBtn" class="btn btn-danger">
                        <i class="ri-check-line me-1"></i> Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>
<script src="{{ asset('assets/js/ui/air-datepicker.init.js') }}"></script>

<script>
    let table, employeesCache = [],
        isMultiMode = false,
        employeeChoices, stationChoices;
    const AUTH_USER_ID = "{{ Auth::id() }}";
    const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";
    payrollTableIsAjax = false; // ✅ moved here for global access
    userPermissions = []; // ✅ define globally to avoid ReferenceError




    $(document).ready(function () {
        loadEmployees();
        loadStations();

        $.get(`/api/getpermissionbyuserid/{{Auth::user()->id}}/{{Auth::user()->role}}`, function (permissions) {
            userPermissions = permissions;
            console.log("Loaded permissions:", userPermissions);



            // Hide Add button if not allowed
            if (!hasPermission('employee_payroll', 'create')) {
                $('#payrollAccordion').hide();
            }
        });

        new AirDatepicker('#payment_date', {
            autoClose: true,
            dateFormat: 'yyyy-MM-dd',
            locale: localeEn
        });

        // Tracks whether current payroll table was initialized with ajax as data source
        let payrollTableIsAjax = false;

        function createPayrollConfig(dataSourceIsAjax, source) {
            const base = {
                paging: false,
                searching: false,
                info: false,
                ordering: false,
                dom: 't',
                columns: [{
                    data: "id"
                }, {
                    data: "payment_date"
                }, {
                    data: "employee_name"
                }, {
                    data: "role"
                },
                {
                    data: "station_name"
                }, {
                    data: "salary"
                }, {
                    data: "note"
                },
                {
                    data: null,
                    render: function (row) {
                        let buttons = '';

                        if (hasPermission('employee_payroll', 'update')) {
                            buttons += `
            <button class="btn btn-sm btn-primary" onclick="viewPayroll(${row.id})">
                <i class="bi bi-eye"></i>
            </button>`;
                        }

                        if (hasPermission('employee_payroll', 'delete')) {
                            buttons += `
            <button class="btn btn-sm btn-danger" onclick="deletePayroll(${row.id})">
                <i class="bi bi-trash"></i>
            </button>`;
                        }

                        return buttons ?
                            `<div class="btn-group btn-group-sm" role="group">${buttons}</div>` :
                            `<span class="text-muted small">No actions</span>`;
                    }
                }

                ],
                columnDefs: [{
                    targets: -1,
                    className: "text-center"
                }]
            };

            if (dataSourceIsAjax) {
                // Normalize different API shapes (array or { data: [...] }) to an array
                base.ajax = {
                    url: source,
                    dataSrc: function (json) {
                        if (!json) return [];
                        if (Array.isArray(json)) return json;
                        if (json && Array.isArray(json.data)) return json.data;
                        return [];
                    }
                };
            } else {
                base.data = source || [];
            }

            return base;
        }

        const adminUrl = '/api/payroll';
        const ownerUrl = `/api/user-payroll/${AUTH_USER_ID}`;

        if (AUTH_ROLE === 'admin') {
            payrollTableIsAjax = true;
            table = $('#accountsTable').DataTable(createPayrollConfig(true, adminUrl));
        } else if (AUTH_ROLE === 'employee') {
            // Try aggregating payroll by stations assigned to this employee
            fetch(`/api/stations_emp/${AUTH_USER_ID}`).then(r => r.ok ? r.json() : []).then(stations => {
                stations = Array.isArray(stations) ? stations : (stations && Array.isArray(stations
                    .data) ?
                    stations.data : []);
                if (!stations || stations.length === 0) {
                    payrollTableIsAjax = true;
                    table = $('#accountsTable').DataTable(createPayrollConfig(true, ownerUrl));
                    return;
                }

                // The server doesn't expose per-station payroll endpoints in this installation.
                // Fetch all payrolls once and filter client-side by station IDs assigned to this employee.
                fetch('/api/payroll').then(r => r.ok ? r.json() : []).then(all => {
                    const payload = Array.isArray(all) ? all : (all && Array.isArray(all.data) ?
                        all
                            .data : []);
                    const stationIds = stations.map(s => s.id);
                    const combined = payload.filter(p => stationIds.includes(p.station_id));
                    if (combined && combined.length > 0) {
                        payrollTableIsAjax = false;
                        table = $('#accountsTable').DataTable(createPayrollConfig(false,
                            combined));
                    } else {
                        // fallback to user-payroll if no records found for these stations
                        payrollTableIsAjax = true;
                        table = $('#accountsTable').DataTable(createPayrollConfig(true,
                            ownerUrl));
                    }
                }).catch(() => {
                    payrollTableIsAjax = true;
                    table = $('#accountsTable').DataTable(createPayrollConfig(true, ownerUrl));
                });
            }).catch(() => {
                payrollTableIsAjax = true;
                table = $('#accountsTable').DataTable(createPayrollConfig(true, ownerUrl));
            });
        } else {
            payrollTableIsAjax = true;
            table = $('#accountsTable').DataTable(createPayrollConfig(true, ownerUrl));
        }

        // remove responsive overflow to avoid scrollbars
        $('.table-box.table-responsive').css({
            'overflow': 'visible'
        });

        $('#payrollForm').on('submit', function (e) {
            e.preventDefault();

            let employees = $("#employee_id").val();
            let station_id = $("#station").val();
            let payment_date = $("#payment_date").val();
            let note = $("#note").val();

            if (!employees) {
                showToast("❌ Please select at least one employee", true);
                return;
            }

            if (!Array.isArray(employees)) {
                employees = [employees];
            }

            let formData = new FormData();
            formData.append('station_id', station_id);
            formData.append('payment_date', payment_date);
            formData.append('note', note);
            formData.append('_token', "{{ csrf_token() }}");

            // ✅ Send each employee individually
            employees.forEach(function (employeeId) {
                formData.append('employee_id[]', employeeId);
            });

            // ✅ Send the calculated salaries for each employee
            if (window.calculatedSalaries && Object.keys(window.calculatedSalaries).length > 0) {
                // If we have individual calculated salaries from deduction
                employees.forEach(function (employeeId) {
                    const salary = window.calculatedSalaries[employeeId] ||
                        employeesCache.find(e => e.employee_id == employeeId)?.salary || 0;
                    formData.append(`salaries[${employeeId}]`, salary);
                });
            } else {
                // If no deduction applied, use basic salaries
                employees.forEach(function (employeeId) {
                    const emp = employeesCache.find(e => e.employee_id == employeeId);
                    formData.append(`salaries[${employeeId}]`, emp ? emp.salary : 0);
                });
            }

            $.ajax({
                url: "/api/payroll",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    showToast(response.message);
                    reloadPayrollForRole();
                    $('#payrollForm')[0].reset();
                    // Clear calculated salaries
                    window.calculatedSalaries = {};
                    loadEmployees();
                    if (isMultiMode) {
                        toggleMultiMode();
                    }
                },
                error: (xhr) => {
                    console.log("Error response:", xhr.responseJSON);
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        showToast("❌ " + Object.values(xhr.responseJSON.errors)[0][0], true);
                    } else {
                        showToast("❌ Error saving payroll!", true);
                    }
                }
            });
        });

        $("#toggleMulti").on("click", toggleMultiMode);

        // 🔹 Open modal when "Deduct on Attendance" button is clicked
        $('#deductAttendanceBtn').on('click', function () {
            const modalEl = document.getElementById('deductAttendanceModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });

        // 🔹 Calculate deduction when confirm button is clicked
        $('#confirmDeductionBtn').on('click', calculateDeduction);
    });

    // Deduction calculation function
    function calculateDeduction() {
        const selectedMonth = document.getElementById('deductionMonth').value;
        const selectedEmployees = $("#employee_id").val();
        const selectedStation = document.getElementById('station').value;

        if (!selectedMonth || !selectedEmployees || selectedEmployees.length === 0 || !selectedStation) {
            showToast('❌ Please select employee(s), station and month', true);
            return;
        }

        // Show loading state
        $('#confirmDeductionBtn').prop('disabled', true).html('<i class="ri-loader-4-line me-1"></i> Calculating...');

        // Initialize calculated salaries object
        window.calculatedSalaries = window.calculatedSalaries || {};

        // Convert to array if single employee
        const employeesArray = Array.isArray(selectedEmployees) ? selectedEmployees : [selectedEmployees];
        let deductionPromises = [];

        employeesArray.forEach(employeeId => {
            const promise = $.ajax({
                url: '/api/calculate-deduction',
                method: 'POST',
                data: {
                    employee_id: employeeId,
                    month: selectedMonth,
                    station_id: selectedStation,
                    _token: "{{ csrf_token() }}"
                }
            });
            deductionPromises.push(promise);
        });

        // Wait for all deduction calculations to complete
        Promise.all(deductionPromises)
            .then(responses => {
                let totalFinalSalary = 0;

                responses.forEach((response, index) => {
                    if (response.success) {
                        const employeeId = employeesArray[index];
                        window.calculatedSalaries[employeeId] = response.data.final_salary;
                        totalFinalSalary += response.data.final_salary;
                    }
                });

                // Update salary field with total deducted amount (for display only)
                $('#salary').val(totalFinalSalary);

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('deductAttendanceModal'));
                modal.hide();

                showToast(`✅ Deduction applied for ${employeesArray.length} employees! Total salary: ${totalFinalSalary}`);
            })
            .catch(error => {
                showToast('❌ Error calculating deductions for some employees', true);
            })
            .finally(() => {
                $('#confirmDeductionBtn').prop('disabled', false).html('<i class="ri-check-line me-1"></i> Confirm');
            });
    }

    function toggleMultiMode() {
        isMultiMode = !isMultiMode;

        // Update button label
        $("#toggleMulti").text(isMultiMode ? "Single" : "Multi");

        // Destroy previous Choices instance cleanly
        if (employeeChoices) {
            employeeChoices.destroy();
        }

        const el = document.querySelector('#employee_id');

        // Switch between multiple/single mode
        if (isMultiMode) {
            el.setAttribute("multiple", "multiple");
        } else {
            el.removeAttribute("multiple");
        }

        // Re-initialize Choices
        employeeChoices = new Choices(el, {
            searchEnabled: true,
            removeItemButton: isMultiMode,
            placeholderValue: isMultiMode ? 'Select Employees' : 'Select Employee',
            shouldSort: true
        });

        // Reload employees data
        employeeChoices.setChoices(employeesCache.map(i => ({
            value: i.employee_id,
            label: i.user_full_name
        })), 'value', 'label', true);

        // Clear selected salary
        $("#salary").val("");
    }


    function loadStations(selectedId = null) {
        function getStationsApiUrl() {
            if (AUTH_ROLE === 'admin') return '/api/stations';
            if (AUTH_ROLE === 'employee') return `/api/stations_emp/${AUTH_USER_ID}`;
            return `/api/stations/${AUTH_USER_ID}`;
        }

        const apiUrl = getStationsApiUrl();

        $.ajax({
            url: apiUrl,
            method: 'GET',
            success: function (stations) {
                let sel = $("#station");
                sel.empty().append('<option value="">Select Station</option>');

                stations.forEach(st => sel.append(
                    `<option value="${st.id}">${st.name}</option>`
                ));

                if (stationChoices) stationChoices.destroy();

                stationChoices = new Choices("#station", {
                    searchEnabled: true,
                    shouldSort: false
                });

                // ✅ if edit mode, preselect the station
                if (selectedId) {
                    stationChoices.setChoiceByValue(selectedId.toString());
                }

                // ✅ when station changes, load employees for that station
                sel.off('change').on('change', function () {
                    const stationId = $(this).val();
                    if (!stationId) {
                        employeesCache = [];
                        initDropdown(employeesCache);
                        return;
                    }

                    $.ajax({
                        url: `/api/employees/station/${stationId}`,
                        method: 'GET',
                        success: function (res) {
                            const arr = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
                            employeesCache = arr.map(r => ({
                                employee_id: r.employee_id || r.id || null,
                                user_full_name: r.user_full_name || r.full_name || r.user_name || r.username || '',
                                salary: r.salary || 0,
                                raw: r
                            }));
                            initDropdown(employeesCache);
                        },
                        error: function () {
                            employeesCache = [];
                            initDropdown([]);
                            showToast("❌ Error loading employees for this station", true);
                        }
                    });
                });
            }
        });
    }


    function loadEmployees() {
        function normalizeForDropdown(r) {
            if (!r) return {
                employee_id: null,
                user_full_name: '',
                salary: 0
            };
            return {
                employee_id: r.employee_id || r.id || null,
                user_full_name: r.user_full_name || r.full_name || r.user_name || r.username || '',
                salary: r.salary || 0,
                raw: r
            };
        }

        // Role-aware loader
        if (AUTH_ROLE === 'admin') {
            $.ajax({
                url: '/api/employees',
                method: 'GET',
                success(res) {
                    const arr = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
                    employeesCache = arr.map(normalizeForDropdown);
                    initDropdown(employeesCache);
                },
                error() {
                    showToast("Error loading employees", true);
                }
            });
            return;
        }

        if (AUTH_ROLE === 'employee') {
            // fetch stations for this employee then aggregate employees per station
            fetch(`/api/stations_emp/${AUTH_USER_ID}`).then(r => r.ok ? r.json() : []).then(stations => {
                stations = Array.isArray(stations) ? stations : (stations && Array.isArray(stations.data) ?
                    stations
                        .data : []);
                if (!stations || stations.length === 0) {
                    employeesCache = [];
                    initDropdown(employeesCache);
                    return;
                }
                const calls = stations.map(s => fetch(`/api/employeebystation/${s.id}`).then(r => r.ok ? r
                    .json() : []).catch(() => []));
                Promise.all(calls).then(results => {
                    const combined = results.flatMap(r => Array.isArray(r) ? r : (r && Array
                        .isArray(r
                            .data) ? r.data : []));
                    employeesCache = combined.map(normalizeForDropdown);
                    initDropdown(employeesCache);
                }).catch(() => {
                    showToast("Error loading employees", true);
                });
            }).catch(() => {
                showToast("Error loading stations for employee", true);
            });
            return;
        }

        // owner (default)
        $.ajax({
            url: `/api/user-employees/${AUTH_USER_ID}`,
            method: 'GET',
            success(res) {
                const arr = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
                employeesCache = arr.map(normalizeForDropdown);
                initDropdown(employeesCache);
            },
            error() {
                showToast("Error loading employees", true);
            }
        });
    }

    function initDropdown(items, selectedValue = null) {
        const el = document.querySelector('#employee_id');
        if (!el) return;

        if (employeeChoices) employeeChoices.destroy();

        if (isMultiMode) {
            el.setAttribute("multiple", "multiple");
        } else {
            el.removeAttribute("multiple");
        }

        employeeChoices = new Choices(el, {
            searchEnabled: true,
            removeItemButton: isMultiMode,
            placeholderValue: isMultiMode ? 'Select Employees' : 'Select Employee',
            shouldSort: true
        });

        employeeChoices.setChoices(items.map(i => ({
            value: i.employee_id,
            label: i.user_full_name,
            selected: selectedValue == i.employee_id
        })), 'value', 'label', true);

        el.addEventListener('change', () => {
            let selected = employeeChoices.getValue(true);

            // Convert to array if single selection
            if (!Array.isArray(selected)) {
                selected = [selected];
            }

            if (selected.length === 1 && selected[0]) {
                const emp = employeesCache.find(e => e.employee_id == selected[0]);
                $("#salary").val(emp ? emp.salary : "");
            } else if (selected.length > 1) {
                let total = 0;
                selected.forEach(id => {
                    const emp = employeesCache.find(e => e.employee_id == id);
                    if (emp) total += emp.salary;
                });
                $("#salary").val(total);
            } else {
                $("#salary").val("");
            }
        });
    }

    function viewPayroll(id) {
        $.get(`/api/payroll/view/${id}`, data => {
            if (!data || !data.length) return showToast("❌ No payroll record found", true);

            const payroll = data[0];

            $("#account_id").val(payroll.id);
            $("#salary").val(payroll.salary);
            $("#payment_date").val(payroll.payment_date);
            $("#note").val(payroll.note || "");

            // ✅ Load station dropdown and select correct station
            loadStations(payroll.station_id);

            // ✅ Setup employees
            const ids = data.map(p => p.employee_id.toString());
            if (ids.length > 1 && !isMultiMode) {
                toggleMultiMode();
            } else if (ids.length === 1 && isMultiMode) {
                toggleMultiMode(); // ensure correct mode
            }

            initDropdown(employeesCache);
            employeeChoices.setChoiceByValue(ids);

            if (ids.length === 1) {
                employeeChoices.setChoices([{
                    value: payroll.employee_id,
                    label: payroll.employee_name,
                    selected: true
                }], 'value', 'label', true);
            }

            // ✅ Show a close/reset button dynamically
            if (!$('#closeViewBtn').length) {
                const closeBtn = `<button type="button" id="closeViewBtn" class="btn btn-secondary ms-2">Close View</button>`;
                $('#payrollForm .text-end').append(closeBtn);

                $('#closeViewBtn').on('click', function () {
                    $('#payrollForm')[0].reset();
                    $("#salary").val('');
                    if (isMultiMode) toggleMultiMode();
                    employeeChoices.clearStore();
                    loadEmployees();
                    loadStations();
                    $(this).remove();
                    showToast('✅ Form reset successfully');
                });
            }

        }).fail(() => showToast("❌ Error loading payroll data", true));
    }


    // Reload payroll table in a role-aware way. Works whether table uses ajax or inline data.
    function reloadPayrollForRole() {
        try {
            if (payrollTableIsAjax) {
                if (table && table.ajax) {
                    table.ajax.reload();
                    return;
                }
            }

            // If table uses inline data (aggregated), re-fetch data and re-draw table
            if (AUTH_ROLE === 'employee') {
                // re-aggregate from stations
                fetch(`/api/stations_emp/${AUTH_USER_ID}`).then(r => r.ok ? r.json() : []).then(stations => {
                    stations = Array.isArray(stations) ? stations : (stations && Array.isArray(stations
                        .data) ?
                        stations.data : []);
                    if (!stations || stations.length === 0) {
                        // fallback to user payroll ajax
                        payrollTableIsAjax = true;
                        if (table) table.destroy();
                        table = $('#accountsTable').DataTable(createPayrollConfig(true,
                            `/api/user-payroll/${AUTH_USER_ID}`));
                        return;
                    }

                    // Fetch all payrolls and filter by assigned station IDs instead of per-station endpoints
                    fetch('/api/payroll').then(r => r.ok ? r.json() : []).then(all => {
                        const payload = Array.isArray(all) ? all : (all && Array.isArray(all.data) ?
                            all
                                .data : []);
                        const stationIds = stations.map(s => s.id);
                        const combined = payload.filter(p => stationIds.includes(p.station_id));
                        if (combined && combined.length > 0) {
                            payrollTableIsAjax = false;
                            if (table) table.destroy();
                            table = $('#accountsTable').DataTable(createPayrollConfig(false,
                                combined));
                        } else {
                            payrollTableIsAjax = true;
                            if (table) table.destroy();
                            table = $('#accountsTable').DataTable(createPayrollConfig(true,
                                `/api/user-payroll/${AUTH_USER_ID}`));
                        }
                    }).catch(() => {
                        payrollTableIsAjax = true;
                        if (table) table.destroy();
                        table = $('#accountsTable').DataTable(createPayrollConfig(true,
                            `/api/user-payroll/${AUTH_USER_ID}`));
                    });
                }).catch(() => {
                    payrollTableIsAjax = true;
                    if (table) table.destroy();
                    table = $('#accountsTable').DataTable(createPayrollConfig(true,
                        `/api/user-payroll/${AUTH_USER_ID}`));
                });
                return;
            }

            // Admin/owner: just reload their ajax source
            if (table && table.ajax) {
                table.ajax.reload();
            }
        } catch (err) {
            console.error('Error reloading payroll table', err);
        }
    }



    function deletePayroll(id) {
        if (!confirm("Are you sure you want to delete this payroll record?")) return;
        $.ajax({
            url: `/api/payroll/${id}`,
            method: "DELETE",
            success: () => {
                showToast("✅ Payroll deleted successfully!");
                reloadPayrollForRole();
            },
            error: () => showToast("❌ Error deleting payroll!", true)
        });
    }

    function showToast(msg, isErr = false) {
        const el = document.getElementById("mainToast");
        const body = document.getElementById("toastMessage");
        body.textContent = msg;
        el.className = "toast align-items-center border-0 " + (isErr ? "text-bg-danger" : "text-bg-success");
        new bootstrap.Toast(el, {
            delay: 3000
        }).show();
    }

    function hasPermission(moduleName, action) {
        const module = userPermissions.find(p => p.name === moduleName);
        if (!module) return false;
        return module[action] == 1;
    }
</script>
@endsection