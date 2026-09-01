@extends('partials.layouts.master')

@section('title', 'Nozzle Sales Dashboard')
@section('title-sub', 'Analytics')
@section('pagetitle', 'Nozzle Sales Dashboard')

@section('css')
    <link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <style>
        .card-kpi {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
            transition: all 0.2s ease;
        }

        .card-kpi:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .kpi-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: #6c757d;
        }

        .kpi-value {
            font-size: 22px;
            font-weight: 700;
            color: #212529;
        }

        .filter-card {
            background-color: #f8f9fa;
            border: 1px solid #e3e6f0;
            border-radius: 6px;
        }

        .table-box {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background: #ffffff;
            padding: 15px;
        }

        #nozzleSalesTable th {
            background-color: #f8f9fa;
            font-size: 12px;
            text-transform: uppercase;
            color: #495057;
            font-weight: 600;
        }

        #nozzleSalesTable td {
            font-size: 13px;
            color: #212529;
        }

        .choices__inner {
            min-height: 38px;
            padding: 3px 8px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            background-color: #fff;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">

            <!-- FILTER PANEL -->
            <div class="card filter-card mb-4">
                <div class="card-body">
                    <form id="filterForm" class="row g-3">

                        <!-- Date From -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold fs-12 text-secondary">FROM DATE</label>
                            <input type="date" id="date_from" class="form-control form-control-sm"
                                value="{{ date('Y-m-01') }}">
                        </div>

                        <!-- Date To -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold fs-12 text-secondary">TO DATE</label>
                            <input type="date" id="date_to" class="form-control form-control-sm"
                                value="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Station Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-semibold fs-12 text-secondary">STATION</label>
                            <select id="filter_station" class="form-select form-select-sm">
                                <option value="">All Stations</option>
                            </select>
                        </div>

                        <!-- Dispensers Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-semibold fs-12 text-secondary">DISPENSERS</label>
                            <select id="filter_dispensers" class="form-select form-select-sm" multiple>
                            </select>
                        </div>

                        <!-- Nozzles Filter -->
                        <div class="col-md-2">
                            <label class="form-label fw-semibold fs-12 text-secondary">NOZZLES</label>
                            <select id="filter_nozzles" class="form-select form-select-sm" multiple>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                            <button type="button" id="btnReset" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-funnel"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- KPI SUMMARY CARDS -->
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-kpi p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="kpi-title">Total Sales (PKR)</span>
                                <div class="kpi-value text-primary mt-1" id="kpi_total_sales">0.00</div>
                            </div>
                            <div class="avatar-sm bg-light-primary rounded p-2 text-center fs-18">
                                <i class="bi bi-currency-dollar text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-kpi p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="kpi-title">Total Volume (Ltrs)</span>
                                <div class="kpi-value text-dark mt-1" id="kpi_total_volume">0.00</div>
                            </div>
                            <div class="avatar-sm bg-light-info rounded p-2 text-center fs-18">
                                <i class="bi bi-fuel-pump text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-kpi p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="kpi-title">Today's Sales</span>
                                <div class="kpi-value text-success mt-1" id="kpi_today_sales">0.00</div>
                                <span class="fs-11 text-muted">Last Day: PKR <span
                                        id="kpi_last_day_sales">0.00</span></span>
                            </div>
                            <div class="avatar-sm bg-light-success rounded p-2 text-center fs-18">
                                <i class="bi bi-graph-up-arrow text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-kpi p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="kpi-title">Active Nozzles</span>
                                <div class="kpi-value text-secondary mt-1" id="kpi_active_nozzles">0</div>
                            </div>
                            <div class="avatar-sm bg-light-secondary rounded p-2 text-center fs-18">
                                <i class="bi bi-check-circle text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRAPHICAL ANALYTICS SECTION -->
            <div class="row g-3 mb-4">
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="card-title m-0 fw-bold text-dark">Sales & Volume Revenue Trend</h6>
                        </div>
                        <div class="card-body">
                            <div id="salesTrendChart" style="min-height: 320px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="card-title m-0 fw-bold text-dark">Top 10 Nozzles Performance</h6>
                        </div>
                        <div class="card-body">
                            <div id="nozzlePerformanceChart" style="min-height: 320px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <!-- Main Highlight: Total Volume Dispensed -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm h-100"
                        style="background: #ffffff; border-left: 5px solid #0d6efd !important; border-radius: 8px;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase fw-semibold fs-11 text-muted tracking-wide">
                                    <i class="bi bi-fuel-pump-fill me-1 text-primary"></i> Total Volume Dispensed (Filtered)
                                </span>
                                <div class="d-flex align-items-baseline mt-2">
                                    <h2 class="fw-bold text-dark mb-0 me-2" style="font-size: 32px;"
                                        id="summary_dispensed_liters">0.00</h2>
                                    <span class="badge bg-primary-subtle text-primary fw-bold fs-12 px-2 py-1">LITERS</span>
                                </div>
                                <div class="text-muted fs-11 mt-1">
                                    <i class="bi bi-check2-circle text-success me-1"></i> Cumulative volume for selected
                                    date range & filters
                                </div>
                            </div>
                            <div class="rounded-circle bg-primary-subtle p-3 text-primary d-flex align-items-center justify-content-center"
                                style="width: 56px; height: 56px;">
                                <i class="bi bi-speedometer2 fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Secondary Focus: Total Revenue Generated -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm h-100"
                        style="background: #ffffff; border-left: 5px solid #198754 !important; border-radius: 8px;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase fw-semibold fs-11 text-muted tracking-wide">
                                    <i class="bi bi-cash-stack me-1 text-success"></i> Total Revenue Generated
                                </span>
                                <div class="d-flex align-items-baseline mt-2">
                                    <h3 class="fw-bold text-success mb-0 me-2" style="font-size: 26px;"
                                        id="summary_total_amount">PKR 0.00</h3>
                                </div>
                                <div class="text-muted fs-11 mt-1">
                                    Gross amount accumulated from shift readings
                                </div>
                            </div>
                            <div class="rounded-circle bg-success-subtle p-3 text-success d-flex align-items-center justify-content-center"
                                style="width: 52px; height: 52px;">
                                <i class="bi bi-currency-dollar fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DATATABLE READINGS SECTION -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="card-title m-0 fw-bold text-dark">Shift Nozzle Readings & Sales Detailed Report</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-box table-responsive">
                            <table id="nozzleSalesTable" class="table table-hover align-middle mb-0 w-100">
    <thead>
        <tr>
            <th>Sr. No.</th>
            <th>Reading Date</th>
            <th>Tank</th>
            <th>Dispenser</th>
            <th>Product</th>
            <th>Nozzle Name</th>
            <th>Opening</th>
            <th>Closing</th>
            <th>Dispensed (L)</th>
            <th>Testing (L)</th>
            <th>Rate (PKR)</th>
            <th>Total Amount (PKR)</th>
            <th>Collected By</th>
        </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
        <tr class="fw-bold bg-light">
            <td colspan="8" class="text-end">Summary Total:</td>
            <td id="total_dispensed_sum" class="text-dark">0.00</td>
            <td colspan="2"></td>
            <td id="total_amount_sum" class="text-success">0.00</td>
            <td></td>
        </tr>
    </tfoot>
</table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>

    <!-- DataTables & Export Buttons -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        let choicesDispensers, choicesNozzles;
        let rawOptionsData = { dispensers: [], nozzles: [] };
        let dataTableInstance = null;

        let trendChartInstance = null;
        let performanceChartInstance = null;

        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            initializeChoices();
            loadFilterOptions();
            fetchDashboardData();

            $('#filterForm').on('submit', function (e) {
                e.preventDefault();
                fetchDashboardData();
            });

            $('#btnReset').on('click', function () {
                $('#filterForm')[0].reset();
                if (choicesDispensers) choicesDispensers.removeActiveItems();
                if (choicesNozzles) choicesNozzles.removeActiveItems();
                populateCascadedFilters($('#filter_station').val());
                fetchDashboardData();
            });

            // Station selection updates Dispensers & Nozzles
            $('#filter_station').on('change', function () {
                populateCascadedFilters($(this).val());
            });

            // Dispenser selection updates Nozzles dynamically
            $('#filter_dispensers').on('change', function () {
                updateNozzlesBySelectedDispensers();
            });
        });

        function initializeChoices() {
            choicesDispensers = new Choices('#filter_dispensers', { removeItemButton: true, searchEnabled: true, placeholderValue: 'Select Dispensers' });
            choicesNozzles = new Choices('#filter_nozzles', { removeItemButton: true, searchEnabled: true, placeholderValue: 'Select Nozzles' });
        }

        function loadFilterOptions() {
            $.get('/nozzle-sales/filters', function (response) {
                let stationOptions = '<option value="">All Stations</option>';
                if (response.stations) {
                    response.stations.forEach(s => {
                        stationOptions += `<option value="${s.id}">${s.name}</option>`;
                    });
                }
                $('#filter_station').html(stationOptions);

                rawOptionsData.dispensers = response.dispensers || [];
                rawOptionsData.nozzles = response.nozzles || [];

                populateCascadedFilters('');
            });
        }

        function populateCascadedFilters(stationId) {
            let filteredDispensers = rawOptionsData.dispensers;

            if (stationId) {
                filteredDispensers = filteredDispensers.filter(d => d.station_id == stationId);
            }

            choicesDispensers.clearStore();
            choicesDispensers.setChoices(filteredDispensers.map(d => ({ value: d.id, label: d.name })), 'value', 'label', false);

            updateNozzlesBySelectedDispensers();
        }

        function updateNozzlesBySelectedDispensers() {
            let stationId = $('#filter_station').val();
            let selectedDispenserIds = choicesDispensers ? choicesDispensers.getValue(true) : [];
            let filteredNozzles = rawOptionsData.nozzles;

            if (stationId) {
                filteredNozzles = filteredNozzles.filter(n => n.station_id == stationId);
            }

            if (selectedDispenserIds.length > 0) {
                filteredNozzles = filteredNozzles.filter(n => selectedDispenserIds.includes(n.dispenser_id.toString()) || selectedDispenserIds.includes(n.dispenser_id));
            }

            choicesNozzles.clearStore();
            choicesNozzles.setChoices(filteredNozzles.map(n => ({ value: n.id, label: n.name })), 'value', 'label', false);
        }

        function fetchDashboardData() {
            const payload = {
                date_from: $('#date_from').val(),
                date_to: $('#date_to').val(),
                station_id: $('#filter_station').val(),
                dispenser_ids: choicesDispensers ? choicesDispensers.getValue(true) : [],
                nozzle_ids: choicesNozzles ? choicesNozzles.getValue(true) : []
            };

            $.ajax({
                url: '/nozzle-sales/dashboard-data',
                method: 'GET',
                data: payload,
                success: function (res) {
                    // Update Top KPIs
                    $('#kpi_total_sales').text('PKR ' + res.kpis.total_sales);
                    $('#kpi_total_volume').text(res.kpis.total_volume + ' L');
                    $('#kpi_today_sales').text('PKR ' + res.kpis.today_sales);
                    $('#kpi_last_day_sales').text(res.kpis.last_day_sales);
                    $('#kpi_active_nozzles').text(res.kpis.active_nozzles);

                    // Update New Dedicated Main Summary Card
                    if (res.summary) {
                        $('#summary_dispensed_liters').text(res.summary.formatted_total_volume);
                        $('#summary_total_amount').text('PKR ' + res.summary.formatted_total_sales);
                    }

                    // Render Charts
                    renderTrendChart(res.charts.trend);
                    renderPerformanceChart(res.charts.nozzle_performance);

                    // Render DataTable
                    renderDataTable(res.table_data);
                },
                error: function (err) {
                    console.error('Error fetching dashboard data:', err);
                }
            });
        }

        function renderTrendChart(trendData) {
            const dates = trendData.map(item => item.date);
            const sales = trendData.map(item => parseFloat(item.total_amount));
            const volume = trendData.map(item => parseFloat(item.total_volume));

            const options = {
                series: [
                    { name: 'Total Sales (PKR)', type: 'column', data: sales },
                    { name: 'Volume Dispensed (L)', type: 'line', data: volume }
                ],
                chart: { height: 320, type: 'line', toolbar: { show: false } },
                stroke: { width: [0, 2], curve: 'smooth' },
                colors: ['#0d6efd', '#198754'],
                dataLabels: { enabled: false },
                xaxis: { categories: dates },
                yaxis: [
                    { title: { text: 'Sales (PKR)' } },
                    { opposite: true, title: { text: 'Volume (Ltr)' } }
                ],
                grid: { borderColor: '#f1f1f1' }
            };

            if (trendChartInstance) trendChartInstance.destroy();
            trendChartInstance = new ApexCharts(document.querySelector("#salesTrendChart"), options);
            trendChartInstance.render();
        }

        function renderPerformanceChart(performanceData) {
            const nozzleNames = performanceData.map(item => item.nozzle_name);
            const totals = performanceData.map(item => parseFloat(item.total_amount));

            const options = {
                series: [{ name: 'Sales Amount (PKR)', data: totals }],
                chart: { height: 320, type: 'bar', toolbar: { show: false } },
                plotOptions: { bar: { horizontal: true, barHeight: '50%', borderRadius: 3 } },
                colors: ['#495057'],
                dataLabels: { enabled: false },
                xaxis: { categories: nozzleNames },
                grid: { borderColor: '#f1f1f1' }
            };

            if (performanceChartInstance) performanceChartInstance.destroy();
            performanceChartInstance = new ApexCharts(document.querySelector("#nozzlePerformanceChart"), options);
            performanceChartInstance.render();
        }

        function renderDataTable(tableData) {
    if (dataTableInstance) {
        dataTableInstance.destroy();
    }

    dataTableInstance = $('#nozzleSalesTable').DataTable({
        data: tableData,
        destroy: true,
        dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rt<"d-flex justify-content-between align-items-center mt-3"lip>',
        buttons: [
            {
                extend: 'excelHtml5',
                className: 'btn btn-sm btn-success',
                text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel Export',
                title: 'Nozzle Sales Detailed Report',
                footer: true
            },
            {
                extend: 'pdfHtml5',
                className: 'btn btn-sm btn-danger',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF Export',
                orientation: 'landscape',
                title: 'Nozzle Sales Detailed Report',
                footer: true
            },
            {
                extend: 'print',
                className: 'btn btn-sm btn-secondary',
                text: '<i class="bi bi-printer me-1"></i> Print',
                title: 'Nozzle Sales Detailed Report',
                footer: true
            }
        ],
        columns: [
            // Sr. No. 1 se start hoga dynamically
            { 
                data: null, 
                render: function (data, type, row, meta) {
                    return `<span class="fw-semibold">${meta.row + 1}</span>`;
                } 
            },
            {
                data: 'reading_date',
                render: function (data) {
                    if (!data) return 'N/A';
                    return data.split(' ')[0];
                }
            },
            { data: 'tank_name', render: data => `<span class="badge bg-info-subtle text-info">${data ?? 'N/A'}</span>` },
            { data: 'dispenser_name', render: data => `<span class="fw-semibold text-secondary">${data ?? 'N/A'}</span>` },
            { data: 'product_name', render: data => `<span class="badge bg-primary-subtle text-primary">${data ?? 'N/A'}</span>` },
            { data: 'nozzel_name', render: data => `<span class="fw-semibold">${data ?? 'N/A'}</span>` },
            { data: 'opening_reading', render: data => parseFloat(data || 0).toFixed(2) },
            { data: 'closing_reading', render: data => parseFloat(data || 0).toFixed(2) },
            { data: 'total_dispensed', render: data => `<span class="fw-bold text-dark">${parseFloat(data || 0).toFixed(2)}</span>` },
            { data: 'testing_reading', render: data => parseFloat(data || 0).toFixed(2) },
            { data: 'rate', render: data => parseFloat(data || 0).toFixed(2) },
            { data: 'total_amount', render: data => `<span class="fw-bold text-success">${parseFloat(data || 0).toFixed(2)}</span>` },
            { data: 'collected_from_name', render: data => data ?? 'N/A' }
        ],
        footerCallback: function (row, data, start, end, display) {
            var api = this.api();

            var intVal = function (i) {
                return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
            };

            // Shift ID hatne ke baad Total Dispensed index 8 par hai
            var totalDispensed = api
                .column(8)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            // Shift ID hatne ke baad Total Amount index 11 par hai
            var totalAmount = api
                .column(11)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            $(api.column(8).footer()).html(totalDispensed.toFixed(2));
            $(api.column(11).footer()).html(totalAmount.toFixed(2));
        }
    });
}
    </script>
@endsection