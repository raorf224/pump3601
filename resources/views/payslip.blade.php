@extends('partials.layouts.master')

@section('title', 'Payslips | ' . Auth::user()->full_name)
@section('title-sub', 'HR Management')
@section('pagetitle', 'Salary Component')

@section('css')
    <!-- ✅ Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <!-- ✅ Air Datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/air-datepicker@3.4.0/air-datepicker.css">
    <script src="https://cdn.jsdelivr.net/npm/air-datepicker@3.4.0/air-datepicker.js"></script>

    <!-- ✅ Choices.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <!-- ✅ DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">

    <style>
        .toast-error {
            background-color: #dc3545 !important;
            color: white !important;
        }

        .toast-success {
            background-color: #198754 !important;
            color: white !important;
        }

        .payslip-preview {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .payslip-header {
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .payslip-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payslip-table th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
        }

        .payslip-table td {
            border: 1px solid #dee2e6;
            padding: 10px;
        }

        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }

        .signature-section {
            margin-top: 50px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <!-- ✅ Filter Section -->
                    <div class="card p-3 mb-4">
                        <div class="row g-3 align-items-end">

                            <!-- ✅ Station Dropdown -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Station</label>
                                <select id="station" class="form-select"></select>
                            </div>

                            <!-- ✅ Employees Dropdown (Multi-select) -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Employees</label>
                                <select id="employee_id" class="form-select" multiple></select>
                            </div>

                            <!-- ✅ Pay From Date -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Pay From</label>
                                <input type="text" id="pay_from" class="form-control datepicker" placeholder="Select date">
                            </div>

                            <!-- ✅ Pay To Date -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Pay To</label>
                                <input type="text" id="pay_to" class="form-control datepicker" placeholder="Select date">
                            </div>

                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <button class="btn btn-primary w-100" id="apply_filters">
                                    <i class="fas fa-filter me-2"></i>Apply
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ Table Section -->
                    <div class="card p-3">
                        <div class="table-responsive">
                            <table id="payslipTable" class="table no-wrap align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Site</th>
                                        <th>Employee</th>
                                        <th>Role</th>
                                        <th>Pay Period</th>
                                        <th>Pay Date</th>
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
    </div>

    <!-- ✅ Payslip Modal -->
    <div class="modal fade" id="payslipModal" tabindex="-1" aria-labelledby="payslipModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payslipModalLabel">Payslip</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="payslipContent">
                    <!-- Payslip content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="downloadPayslip">
                        <i class="fas fa-download me-2"></i>Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
    </main>
@endsection

@section('js')
    <!-- ✅ jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ✅ DataTables -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

    <!-- ✅ PDF Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!-- ✅ Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        let stationChoices, employeeChoices, employeesCache = [];
        let isMultiMode = true;
        let dataTable;
        const AUTH_USER_ID = "{{ Auth::id() }}";
        const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";
        let currentPayslipId = null;

        // ✅ Toastr configuration for better error visibility
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 5000,
            extendedTimeOut: 2000
        };

        $(document).ready(function () {
            initializeDatePickers();
            loadStations();
            loadEmployees();
            initializeDataTable();

            // ✅ Apply filters
            $('#apply_filters').on('click', function () {
                reloadDataTable();
            });

            // ✅ Download Payslip
            $('#downloadPayslip').on('click', function () {
                if (currentPayslipId) {
                    downloadPayslipPDF(currentPayslipId);
                }
            });
        });

        // ✅ Initialize Date Pickers
        function initializeDatePickers() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            const englishLocale = {
                days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                daysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                daysMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                months: ['January', 'February', 'March', 'April', 'May', 'June', 'July',
                    'August', 'September', 'October', 'November', 'December'
                ],
                monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                today: 'Today',
                clear: 'Clear',
                dateFormat: 'yyyy-MM-dd',
                timeFormat: 'hh:ii aa',
                firstDay: 0
            };

            new AirDatepicker('#pay_from', {
                autoClose: true,
                locale: englishLocale,
                dateFormat: 'yyyy-MM-dd',
                selectedDates: [firstDay]
            });

            new AirDatepicker('#pay_to', {
                autoClose: true,
                locale: englishLocale,
                dateFormat: 'yyyy-MM-dd',
                selectedDates: [lastDay]
            });
        }

        // ✅ Initialize DataTable (simplified version)
        function initializeDataTable() {
            dataTable = $('#payslipTable').DataTable({
                processing: true,
                serverSide: false,
                searching: false, // Hide search box
                paging: false, // Hide pagination
                info: false, // Hide "Showing X of Y entries"
                lengthChange: false, // Hide "Show X entries" dropdown
                ajax: {
                    url: '/api/payslips',
                    data: function (d) {
                        return {
                            station_id: $('#station').val(),
                            employees: $('#employee_id').val(),
                            pay_from: $('#pay_from').val(),
                            pay_to: $('#pay_to').val(),
                            ajax: true
                        };
                    }
                },
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'station_name',
                        name: 'station_name',
                        width: '15%'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        width: '15%'
                    },
                    {
                        data: 'role',
                        name: 'role',
                        width: '10%'
                    },
                    {
                        data: 'pay_period',
                        name: 'pay_period',
                        orderable: false,
                        width: '15%'
                    },
                    {
                        data: 'pay_date',
                        name: 'pay_date',
                        width: '10%'
                    },
                    {
                        data: 'net_pay_formatted',
                        name: 'net_pay',
                        orderable: true,
                        width: '10%'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        width: '8%',
                        render: function (data) {
                            const badgeClass = data === 'Completed' ? 'bg-success' : 'bg-warning';
                            return `<span class="badge ${badgeClass}">${data}</span>`;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '12%',
                        render: function (data, type, row) {
                            return `
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary view-payslip" data-id="${row.id}" title="View Payslip">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success download-payslip" data-id="${row.id}" title="Download PDF">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[0, 'desc']],
                dom: 't', // Only show table, no other elements
                language: {
                    emptyTable: "No payslips found matching your filters"
                }
            });

            // Event handlers for view and download
            $('#payslipTable').on('click', '.view-payslip', function () {
                const payslipId = $(this).data('id');
                viewPayslip(payslipId);
            });

            $('#payslipTable').on('click', '.download-payslip', function () {
                const payslipId = $(this).data('id');
                downloadPayslipPDF(payslipId);
            });
        }

        // ✅ Reload DataTable with filters
        function reloadDataTable() {
            dataTable.ajax.reload();
            toastr.success("Filters applied successfully");
        }

        // ✅ View Payslip Details
        function viewPayslip(payslipId) {
            $.ajax({
                url: `/api/payslips/${payslipId}`,
                method: 'GET',
                success: function (response) {
                    currentPayslipId = payslipId;
                    $('#payslipContent').html(generatePayslipHTML(response.data));
                    $('#payslipModal').modal('show');
                },
                error: function () {
                    toastr.error("Error loading payslip details");
                }
            });
        }

        // ✅ Generate Payslip HTML (Updated for your JSON structure)
        function generatePayslipHTML(payslip) {
            // Calculate actual present days
            const actualPresentDays = payslip.working_days - payslip.absent_days;

            return `
                <div class="payslip-preview" id="payslipToPrint">
                    <div class="payslip-header text-center">
                        <h2 class="text-primary">Pump 360</h2>
                        <h4>Salary Slip for ${new Date(payslip.period_start).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</h4>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Employee Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Employee Name:</th>
                                    <td>${payslip.employee_name || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Employee ID:</th>
                                    <td>#${payslip.employe_id || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Designation:</th>
                                    <td>${payslip.role || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Station:</th>
                                    <td>${payslip.station_name || 'N/A'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Payroll Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Pay Period:</th>
                                    <td>${payslip.period_start} to ${payslip.period_end}</td>
                                </tr>
                                <tr>
                                    <th>Pay Date:</th>
                                    <td>${payslip.pay_date}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td><span class="badge ${payslip.status === 'Completed' ? 'bg-success' : 'bg-warning'}">${payslip.status}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Attendance Summary</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Working Days</th>
                                            <th>Present Days</th>
                                            <th>Absent Days</th>
                                            <th>Late Days</th>
                                            <th>Half Days</th>
                                            <th>Per Day Salary</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>${payslip.working_days || 0}</td>
                                            <td>${actualPresentDays}</td>
                                            <td>${payslip.absent_days || 0}</td>
                                            <td>${payslip.late_days || 0}</td>
                                            <td>${payslip.half_days || 0}</td>
                                            <td>Rs. ${Number(payslip.per_day_salary || 0).toLocaleString('en-IN')}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Attendance Deductions</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Absent Deduction</th>
                                            <th>Late Deduction</th>
                                            <th>Half Day Deduction</th>
                                            <th>Total Deduction</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Rs. ${Number(payslip.absent_deduction || 0).toLocaleString('en-IN')}</td>
                                            <td>Rs. ${Number(payslip.late_deduction || 0).toLocaleString('en-IN')}</td>
                                            <td>Rs. ${Number(payslip.half_day_deduction || 0).toLocaleString('en-IN')}</td>
                                            <td class="text-danger fw-bold">Rs. ${Number(payslip.total_deduction || 0).toLocaleString('en-IN')}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Earnings</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Component</th>
                                        <th>Amount (Rs.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${payslip.earnings && payslip.earnings.length > 0 ? payslip.earnings.map(earning => `
                                        <tr>
                                            <td>${earning.component_name}</td>
                                            <td>${Number(earning.amount).toLocaleString('en-IN')}</td>
                                        </tr>
                                    `).join('') : `
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No earnings components</td>
                                        </tr>
                                    `}
                                    <tr class="total-row">
                                        <td><strong>Basic Salary</strong></td>
                                        <td><strong>Rs. ${Number(payslip.basic_pay || 0).toLocaleString('en-IN')}</strong></td>
                                    </tr>
                                    <tr class="total-row">
                                        <td><strong>Total Earnings</strong></td>
                                        <td><strong>Rs. ${Number(payslip.gross_pay || 0).toLocaleString('en-IN')}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Deductions</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Component</th>
                                        <th>Amount (Rs.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${payslip.deductions && payslip.deductions.length > 0 ? payslip.deductions.map(deduction => `
                                        <tr>
                                            <td>${deduction.component_name}</td>
                                            <td>${Number(deduction.amount).toLocaleString('en-IN')}</td>
                                        </tr>
                                    `).join('') : `
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No deduction components</td>
                                        </tr>
                                    `}
                                    <tr class="total-row text-danger">
                                        <td><strong>Attendance Deductions</strong></td>
                                        <td><strong>Rs. ${Number(payslip.total_deduction || 0).toLocaleString('en-IN')}</strong></td>
                                    </tr>
                                    <tr class="total-row">
                                        <td><strong>Total Deductions</strong></td>
                                        <td><strong>Rs. ${Number((payslip.gross_pay || 0) - (payslip.net_pay || 0)).toLocaleString('en-IN')}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <div class="total-section p-3 bg-light rounded">
                                <h3 class="text-success">Net Salary: Rs. ${Number(payslip.net_pay || 0).toLocaleString('en-IN')}</h3>
                                <p class="text-muted mb-0">Gross Pay: Rs. ${Number(payslip.gross_pay || 0).toLocaleString('en-IN')} - Total Deductions: Rs. ${Number((payslip.gross_pay || 0) - (payslip.net_pay || 0)).toLocaleString('en-IN')}</p>
                            </div>
                        </div>
                    </div>


                </div>
            `;
        }

        // ✅ Download Payslip as PDF
        function downloadPayslipPDF(payslipId) {
            toastr.info("Generating PDF... Please wait.");

            $.ajax({
                url: `/api/payslips/${payslipId}`,
                method: 'GET',
                success: function (response) {
                    const payslip = response.data;

                    // Create temporary element for PDF generation
                    const tempElement = document.createElement('div');
                    tempElement.innerHTML = generatePayslipHTML(payslip);
                    document.body.appendChild(tempElement);

                    html2canvas(tempElement.querySelector('#payslipToPrint'), {
                        scale: 2,
                        useCORS: true,
                        logging: false
                    }).then(canvas => {
                        const imgData = canvas.toDataURL('image/png');
                        const pdf = new jspdf.jsPDF('p', 'mm', 'a4');
                        const imgWidth = 210;
                        const pageHeight = 295;
                        const imgHeight = canvas.height * imgWidth / canvas.width;
                        let heightLeft = imgHeight;
                        let position = 0;

                        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                        heightLeft -= pageHeight;

                        while (heightLeft >= 0) {
                            position = heightLeft - imgHeight;
                            pdf.addPage();
                            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                            heightLeft -= pageHeight;
                        }

                        pdf.save(`payslip-${payslip.employee_name}-${payslip.period_start}.pdf`);
                        document.body.removeChild(tempElement);
                        toastr.success("Payslip downloaded successfully");
                    }).catch(error => {
                        console.error('PDF generation error:', error);
                        toastr.error("Error generating PDF");
                        document.body.removeChild(tempElement);
                    });
                },
                error: function () {
                    toastr.error("Error loading payslip data for download");
                }
            });
        }

        // ✅ Station Loader (same as before)
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
                    stations.forEach(st => sel.append(`<option value="${st.id}">${st.name}</option>`));

                    if (stationChoices) stationChoices.destroy();

                    stationChoices = new Choices("#station", {
                        searchEnabled: true,
                        shouldSort: false
                    });

                    if (selectedId) {
                        stationChoices.setChoiceByValue(selectedId.toString());
                    }

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
                                toastr.error("Error loading employees for this station");
                            }
                        });
                    });
                }
            });
        }

        // ✅ Employees Loader (same as before)
        function loadEmployees() {
            function normalizeForDropdown(r) {
                if (!r) return { employee_id: null, user_full_name: '', salary: 0 };
                return {
                    employee_id: r.employee_id || r.id || null,
                    user_full_name: r.user_full_name || r.full_name || r.user_name || r.username || '',
                    salary: r.salary || 0,
                    raw: r
                };
            }

            if (AUTH_ROLE === 'admin') {
                $.ajax({
                    url: '/api/employees', method: 'GET', success(res) {
                        const arr = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
                        employeesCache = arr.map(normalizeForDropdown);
                        initDropdown(employeesCache);
                    }, error() { toastr.error("Error loading employees"); }
                });
                return;
            }

            if (AUTH_ROLE === 'employee') {
                fetch(`/api/stations_emp/${AUTH_USER_ID}`).then(r => r.ok ? r.json() : []).then(stations => {
                    stations = Array.isArray(stations) ? stations : (stations && Array.isArray(stations.data) ? stations.data : []);
                    if (!stations || stations.length === 0) {
                        employeesCache = [];
                        initDropdown(employeesCache);
                        return;
                    }
                    const calls = stations.map(s => fetch(`/api/employeebystation/${s.id}`).then(r => r.ok ? r.json() : []).catch(() => []));
                    Promise.all(calls).then(results => {
                        const combined = results.flatMap(r => Array.isArray(r) ? r : (r && Array.isArray(r.data) ? r.data : []));
                        employeesCache = combined.map(normalizeForDropdown);
                        initDropdown(employeesCache);
                    }).catch(() => { toastr.error("Error loading employees"); });
                }).catch(() => { toastr.error("Error loading stations for employee"); });
                return;
            }

            $.ajax({
                url: `/api/user-employees/${AUTH_USER_ID}`, method: 'GET', success(res) {
                    const arr = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
                    employeesCache = arr.map(normalizeForDropdown);
                    initDropdown(employeesCache);
                }, error() { toastr.error("Error loading employees"); }
            });
        }

        // ✅ Initialize Employee Dropdown (same as before)
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
        }
    </script>
@endsection