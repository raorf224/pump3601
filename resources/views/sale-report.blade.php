@extends('partials.layouts.master')

@section('title', 'Fuel Consumption Report')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" type="text/css"
    href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<link rel="stylesheet"
    href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />

<link rel="stylesheet"
    href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />

<style>
.stats-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    height: 100%;
}

.stats-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border-color: #d1d5db;
}

.stats-card .icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stats-card .icon-primary {
    background-color: #eef2ff;
    color: #4f46e5;
}

.stats-card .icon-success {
    background-color: #ecfdf5;
    color: #059669;
}

.stats-card .icon-danger {
    background-color: #fef2f2;
    color: #dc2626;
}

.stats-card .icon-info {
    background-color: #eff6ff;
    color: #3b82f6;
}

.stats-card .stats-title {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 0.25rem;
    letter-spacing: 0.3px;
}

.stats-card .stats-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
    line-height: 1.2;
    margin-bottom: 0;
}

.stats-card .stats-sub {
    font-size: 0.7rem;
    color: #9ca3af;
    margin-top: 0.25rem;
}

.filter-bar {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    padding: 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.chart-container {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    padding: 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.badge-profit {
    background-color: #ecfdf5;
    color: #059669;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-loss {
    background-color: #fef2f2;
    color: #dc2626;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.table-profit {
    color: #059669;
    font-weight: 500;
}

.table-loss {
    color: #dc2626;
    font-weight: 500;
}
</style>
@endsection

@section('content')

<div id="layout-wrapper">

    <div class="container-fluid mt-4">

        <!-- STATS -->
        <div class="row mb-4">

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="icon icon-primary mb-2">
                        <i class="fas fa-oil-can"></i>
                    </div>

                    <p class="stats-title">Total Quantity</p>

                    <h3 class="stats-value" id="totalQty">0.00</h3>

                    <p class="stats-sub">Liters</p>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="icon icon-info mb-2">
                        <i class="fas fa-shopping-cart"></i>
                    </div>

                    <p class="stats-title">Total Cost</p>

                    <h3 class="stats-value" id="totalCost">0.00</h3>

                    <p class="stats-sub">PKR</p>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="icon icon-success mb-2">
                        <i class="fas fa-chart-line"></i>
                    </div>

                    <p class="stats-title">Total Sale</p>

                    <h3 class="stats-value" id="totalSale">0.00</h3>

                    <p class="stats-sub">PKR</p>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="icon icon-success mb-2">
                        <i class="fas fa-dollar-sign"></i>
                    </div>

                    <p class="stats-title">Total Profit</p>

                    <h3 class="stats-value" id="totalProfit">0.00</h3>

                    <p class="stats-sub">PKR</p>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="icon icon-danger mb-2">
                        <i class="fas fa-fire"></i>
                    </div>

                    <p class="stats-title">Total Loss</p>

                    <h3 class="stats-value" id="totalLoss">0.00</h3>

                    <p class="stats-sub">PKR</p>
                </div>
            </div>

        </div>

        <!-- FILTER -->
        <div class="filter-bar">

            <div class="row align-items-end">

                <div class="col-md-3">
                    <label class="form-label">
                        Date Range
                    </label>

                    <input type="text"
                        id="dateRange"
                        class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Station
                    </label>

                    <select id="stationFilter" class="form-control">
                        <option value="">All Stations</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Tank
                    </label>

                    <select id="tankFilter" class="form-control">
                        <option value="">All Tanks</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <button class="btn btn-primary w-100"
                        id="applyFilter">
                        Apply
                    </button>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-secondary w-100"
                        id="resetFilter">
                        Reset
                    </button>
                </div>

            </div>

        </div>

        <!-- CHART -->
        <div class="chart-container">

            <h5 class="mb-3">
                Daily Profit / Loss Analysis
            </h5>

            <div style="height:350px;">
                <canvas id="profitLossChart"></canvas>
            </div>

        </div>

        <!-- TABLE -->
        <div class="card">

            <div class="card-header">
                <h5 class="mb-0">
                    Fuel Layer Consumption Details
                </h5>
            </div>

            <div class="card-body">

                <table id="consumptionTable"
                    class="table table-bordered table-striped w-100">

                    <thead>

                        <tr>
                            <th>S.No</th>
                            <th>Station</th>
                            <th>Tank</th>
                            <th>Layer ID</th>
                            <th>Sale ID</th>
                            <th>Quantity</th>
                            <th>Cost Rate</th>
                            <th>Sale Rate</th>
                            <th>Cost Amount</th>
                            <th>Sale Amount</th>
                            <th>Profit/Loss</th>
                            <th>Date</th>
                        </tr>

                    </thead>

                    <tbody></tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection

@section('js')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<script type="text/javascript"
    src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>

$(document).ready(function() {

    let dataTable;
    let chart;

    let stationsData = [];
    let tanksData = [];

    let startDate = moment().startOf('month');
    let endDate = moment().endOf('month');

    // DATE RANGE
    $('#dateRange').daterangepicker({
        startDate: startDate,
        endDate: endDate,
        locale: {
            format: 'YYYY-MM-DD'
        }
    });

    // LOAD STATIONS
    function loadStations() {

        $.ajax({
            url: "/api/stations",
            type: "GET",

            success: function(res) {

                stationsData = res;

                $('#stationFilter').html(`
                    <option value="">All Stations</option>
                `);

                res.forEach(function(station) {

                    $('#stationFilter').append(`
                        <option value="${station.id}">
                            ${station.name}
                        </option>
                    `);

                });

            }
        });

    }

    // LOAD TANKS
    function loadTanks() {

        $.ajax({
            url: "/api/tanks",
            type: "GET",

            success: function(res) {

                tanksData = res;

                $('#tankFilter').html(`
                    <option value="">All Tanks</option>
                `);

                res.forEach(function(tank) {

                    $('#tankFilter').append(`
                        <option value="${tank.id}">
                            ${tank.name}
                        </option>
                    `);

                });

            }
        });

    }

    // FILTER TANKS
    $('#stationFilter').change(function() {

        let stationId = $(this).val();

        $('#tankFilter').html(`
            <option value="">All Tanks</option>
        `);

        let filtered = tanksData.filter(t =>
            stationId == ''
            ? true
            : t.station_id == stationId
        );

        filtered.forEach(function(tank) {

            $('#tankFilter').append(`
                <option value="${tank.id}">
                    ${tank.name}
                </option>
            `);

        });

    });

    // FORMAT
    function formatNumber(num) {

        return parseFloat(num || 0).toLocaleString('en-PK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

    }

    // LOAD DATA
    function loadData(start, end, stationId = '', tankId = '') {

        $.ajax({

            url: "{{ route('fuel.consumption.data') }}",

            type: "GET",

            data: {
                start_date: start,
                end_date: end,
                station_id: stationId,
                tank_id: tankId
            },

            success: function(response) {

                // CARDS
                $('#totalQty').text(
                    formatNumber(response.total_qty)
                );

                $('#totalCost').text(
                    formatNumber(response.total_cost)
                );

                $('#totalSale').text(
                    formatNumber(response.total_sale)
                );

                $('#totalProfit').html(`
                    <span class="text-success">
                        ${formatNumber(response.total_profit)}
                    </span>
                `);

                $('#totalLoss').html(`
                    <span class="text-danger">
                        ${formatNumber(response.total_loss)}
                    </span>
                `);

                // CHART
                updateChart(
                    response.chart_dates,
                    response.chart_profits,
                    response.chart_losses
                );

                // TABLE
                if (dataTable) {

                    dataTable.clear().destroy();

                    $('#consumptionTable tbody').empty();

                }

                let tableData = [];

                response.records.forEach((item, index) => {

                    let profit = parseFloat(item.profit || 0);

                    let cls = profit >= 0
                        ? 'table-profit'
                        : 'table-loss';

                    tableData.push([

                        index + 1,

                        item.station_name ?? '-',

                        item.tank_name ?? '-',

                        item.layer_id,

                        item.sale_id,

                        formatNumber(item.qty),

                        formatNumber(item.cost_rate),

                        formatNumber(item.sale_rate),

                        formatNumber(item.cost_amount),

                        formatNumber(item.sale_amount),

                        `<span class="${cls}">
                            ${formatNumber(Math.abs(profit))}
                        </span>`,

                        item.created_at

                    ]);

                });

                dataTable = $('#consumptionTable').DataTable({

                    data: tableData,

                    responsive: true,

                    pageLength: 25

                });

            }

        });

    }

    // CHART
    function updateChart(dates, profits, losses) {

        const ctx = document.getElementById('profitLossChart')
            .getContext('2d');

        if (chart) {
            chart.destroy();
        }

        chart = new Chart(ctx, {

            type: 'bar',

            data: {

                labels: dates,

                datasets: [

                    {
                        label: 'Profit',
                        data: profits,
                        backgroundColor: '#10b981'
                    },

                    {
                        label: 'Loss',
                        data: losses,
                        backgroundColor: '#ef4444'
                    }

                ]

            },

            options: {
                responsive: true,
                maintainAspectRatio: false
            }

        });

    }

    // APPLY FILTER
    $('#applyFilter').click(function() {

        let range = $('#dateRange').val();

        let dates = range.split(' - ');

        let stationId = $('#stationFilter').val();

        let tankId = $('#tankFilter').val();

        loadData(
            dates[0],
            dates[1],
            stationId,
            tankId
        );

    });

    // RESET
    $('#resetFilter').click(function() {

        $('#stationFilter').val('');

        $('#tankFilter').val('');

        let start = moment().startOf('month')
            .format('YYYY-MM-DD');

        let end = moment().endOf('month')
            .format('YYYY-MM-DD');

        loadData(start, end);

    });

    // INITIAL
    loadStations();

    loadTanks();

    loadData(
        startDate.format('YYYY-MM-DD'),
        endDate.format('YYYY-MM-DD')
    );

});
</script>

@endsection