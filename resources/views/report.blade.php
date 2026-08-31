@extends('partials.layouts.master')

@section('title', 'Dashboard | Pure Tank Volume Gain & Loss')
@section('title-sub', 'Analytics')
@section('pagetitle', 'Volume Gain / Loss')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .page-title-font { font-family: 'Poppins', sans-serif; }

        .dashboard-stat-card {
            transition: all 0.25s ease;
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            background: #fff;
            height: 100%;
        }

        .dashboard-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            min-width: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            font-size: 20px;
            color: #fff;
            box-shadow: 0 6px 14px -4px rgba(0, 0, 0, 0.25);
        }

        .stat-icon i { line-height: 1; }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            letter-spacing: -0.5px;
            color: #1a1a2e;
        }

        .stat-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .bg-icon-navy   { background: #1a237e; }
        .bg-icon-blue   { background: #0091ea; }
        .bg-icon-green  { background: #00c853; }
        .bg-icon-red    { background: #d50000; }
        .bg-icon-orange { background: #ff6d00; }
        .bg-icon-purple { background: #6a1b9a; }

        .filter-section {
            background: #fff;
            padding: 22px 24px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .filter-section .form-label {
            font-weight: 600;
            font-size: 12.5px;
            color: #495057;
            margin-bottom: 6px;
        }

        .filter-section .form-control,
        .filter-section .form-select {
            border-radius: 10px;
            border: 1.5px solid #e9ecef;
            padding: 9px 14px;
            font-size: 14px;
        }

        .btn-filter {
            padding: 10px 30px;
            border-radius: 10px;
            font-weight: 600;
            background: #1a237e;
            color: #fff;
            border: none;
            transition: all 0.25s ease;
        }

        .btn-filter:hover { background: #0d1a5c; color: #fff; }

        .section-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .section-card .section-header {
            padding: 18px 22px;
            border-bottom: 1px solid #f0f1f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-card .section-header h5 {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 16px;
            color: #1a1a2e;
        }

        .section-card .section-body { padding: 20px 22px; }

        .chart-box { height: 300px; position: relative; }

        .table-dashboard { font-size: 13px; margin-bottom: 0; }

        .table-dashboard thead th {
            background: #1a237e;
            color: #fff;
            font-weight: 600;
            padding: 12px 15px;
            border: none;
            white-space: nowrap;
        }

        .table-dashboard tbody td {
            padding: 10px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f1f5;
            white-space: nowrap;
        }

        .badge-profit, .badge-loss {
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            display: inline-block;
        }

        .badge-profit { background: #00c853; }
        .badge-loss   { background: #d50000; }

        .layer-card {
            background: #fff;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 10px;
            border-left: 4px solid #1a237e;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .layer-product { font-weight: 700; font-size: 14px; color: #1a1a2e; }
        .layer-detail { font-size: 12.5px; color: #6c757d; margin-top: 2px; }
        .layer-qty { font-weight: 700; color: #1a237e; font-size: 15px; }

        .text-profit { color: #00c853; font-weight: 700; }
        .text-loss   { color: #d50000; font-weight: 700; }
        .empty-state { text-align: center; color: #9aa0ac; padding: 30px 0; font-size: 13.5px; }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">

        {{-- 1. FILTERS --}}
        <div class="filter-section">
            <form action="{{ route('report.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Station</label>
                    <select name="station_id" class="form-select" id="filter_station">
                        <option value="">All Stations</option>
                        @foreach($stations as $station)
                            <option value="{{ $station->id }}" {{ (string) $stationId === (string) $station->id ? 'selected' : '' }}>
                                {{ $station->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tank</label>
                    <select name="tank_id" class="form-select" id="filter_tank">
                        <option value="">All Tanks</option>
                        @foreach($tanks as $tank)
                            <option value="{{ $tank->id }}" data-station="{{ $tank->station_id }}" {{ (string) $tankId === (string) $tank->id ? 'selected' : '' }}>
                                {{ $tank->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" id="filter_from_date" value="{{ $fromDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" id="filter_to_date" value="{{ $toDate }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-filter w-100">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        {{-- 2. PURE VOLUME STAT CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="dashboard-stat-card p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon bg-icon-navy"><i class="fas fa-gas-pump"></i></div>
                    <div>
                        <div class="stat-value">{{ $stats['total_tanks'] }}</div>
                        <div class="stat-label">Tanks</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="dashboard-stat-card p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon bg-icon-blue"><i class="fas fa-truck-droplet"></i></div>
                    <div>
                        <div class="stat-value">{{ number_format($stats['total_received_qty'], 0) }} {{ $settings['unit'] }}</div>
                        <div class="stat-label">Total Fuel Received</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="dashboard-stat-card p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon bg-icon-purple"><i class="fas fa-oil-can"></i></div>
                    <div>
                        <div class="stat-value">{{ number_format($stats['total_dispensed_qty'], 0) }} {{ $settings['unit'] }}</div>
                        <div class="stat-label">Total Fuel Dispensed</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="dashboard-stat-card p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon bg-icon-orange"><i class="fas fa-layer-group"></i></div>
                    <div>
                        <div class="stat-value">{{ number_format($stats['total_current_stock'], 0) }} {{ $settings['unit'] }}</div>
                        <div class="stat-label">Active Stock Remaining</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. CHARTS (VOLUME BASED) --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-5">
                <div class="section-card h-100">
                    <div class="section-header"><h5>Dispensed Volume Trend</h5></div>
                    <div class="section-body">
                        <div class="chart-box"><canvas id="salesTrendChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="section-card h-100">
                    <div class="section-header"><h5>Product Volume Split</h5></div>
                    <div class="section-body">
                        <div class="chart-box"><canvas id="productSplitChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="section-card h-100">
                    <div class="section-header"><h5>Tank Volume Gain / Loss</h5></div>
                    <div class="section-body">
                        <div class="chart-box"><canvas id="tankProfitChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. PURE TANK-WISE VOLUME GAIN/LOSS REPORT --}}
        <div class="section-card">
            <div class="section-header">
                <h5><i class="fas fa-scale-balanced text-primary me-2"></i>Tank-wise Fuel Gain / Loss (Volume in {{ $settings['unit'] }})</h5>
            </div>
            <div class="section-body p-0">
                <div class="table-responsive">
                    <table class="table table-dashboard mb-0">
                        <thead>
                            <tr>
                                <th>Station</th>
                                <th>Tank</th>
                                <th>Product</th>
                                <th>Received ({{ $settings['unit'] }})</th>
                                <th>Dispensed ({{ $settings['unit'] }})</th>
                                <th>Remaining Stock ({{ $settings['unit'] }})</th>
                                <th>Volume Gain / Loss</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tankPerformance as $row)
                                <tr>
                                    <td>{{ $row->station_name }}</td>
                                    <td>{{ $row->tank_name }}</td>
                                    <td>{{ $row->product_name }}</td>
                                    <td>{{ number_format($row->total_received_qty, 2) }}</td>
                                    <td>{{ number_format($row->total_dispensed_qty, 2) }}</td>
                                    <td>{{ number_format($row->current_stock_qty, 2) }}</td>
                                    <td>
                                        <span class="badge-{{ $row->volume_gain_loss >= 0 ? 'profit' : 'loss' }}">
                                            {{ $row->volume_gain_loss >= 0 ? '+ Gain' : '- Loss' }}
                                            {{ number_format(abs($row->volume_gain_loss), 2) }} {{ $settings['unit'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="empty-state">No volume records found for selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- 5. FIFO STOCK LAYERS --}}
            <div class="col-lg-5">
                <div class="section-card h-100">
                    <div class="section-header">
                        <h5><i class="fas fa-layer-group text-primary me-2"></i>Current Stock Layers</h5>
                        @if(count($fifoLayers) > $settings['fifoDisplayLimit'])
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#fifoLayersModal">
                                <i class="fas fa-expand"></i> Show More
                            </button>
                        @endif
                    </div>
                    <div class="section-body">
                        @forelse(array_slice($fifoLayers, 0, $settings['fifoDisplayLimit']) as $layer)
                            <div class="layer-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="layer-product">{{ $layer->product_name }} &middot; {{ $layer->tank_name }}</div>
                                    <div class="layer-detail">{{ $layer->station_name }} &nbsp;|&nbsp; Added: {{ \Carbon\Carbon::parse($layer->created_at)->format('d-M-Y H:i') }}</div>
                                </div>
                                <div class="layer-qty">{{ number_format($layer->remaining_qty, 2) }} {{ $settings['unit'] }}</div>
                            </div>
                        @empty
                            <div class="empty-state">No active stock layers found.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- 6. PURCHASES (VOLUME ONLY) --}}
            <div class="col-lg-7">
                <div class="section-card h-100">
                    <div class="section-header">
                        <h5><i class="fas fa-truck text-primary me-2"></i>Recent Purchases & Receives</h5>
                    </div>
                    <div class="section-body p-0">
                        <div class="table-responsive" style="max-height: 420px;">
                            <table class="table table-dashboard mb-0">
                                <thead>
                                    <tr>
                                        <th>Recv. Date</th>
                                        <th>Station</th>
                                        <th>Tank</th>
                                        <th>Product</th>
                                        <th>Ordered Qty</th>
                                        <th>Received Qty</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchases as $p)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($p->recieving_date)->format('d M Y') }}</td>
                                            <td>{{ $p->station_name ?? 'N/A' }}</td>
                                            <td>{{ $p->tank_name ?? 'N/A' }}</td>
                                            <td>{{ $p->product_name }}</td>
                                            <td>{{ number_format($p->ordered_qty, 0) }} {{ $settings['unit'] }}</td>
                                            <td>{{ number_format($p->recived_qty, 0) }} {{ $settings['unit'] }}</td>
                                            <td>
                                                <span class="badge {{ $p->recived_qty >= $p->ordered_qty ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $p->recived_qty >= $p->ordered_qty ? 'Received' : 'Partial/Pending' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="empty-state">No purchases found for this period.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 7. RECENT TRANSACTIONS (VOLUME ONLY) --}}
        <div class="section-card">
            <div class="section-header">
                <h5><i class="fas fa-list text-primary me-2"></i>Recent Dispensed Transactions (Latest {{ $settings['recentTxLimit'] }})</h5>
                <button class="btn btn-sm btn-outline-primary" id="btnShowMore" data-bs-toggle="modal" data-bs-target="#unlimitedDataModal">
                    <i class="fas fa-expand"></i> Show All
                </button>
            </div>
            <div class="section-body p-0">
                <div class="table-responsive">
                    <table class="table table-dashboard mb-0" id="limitedDataTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date & Time</th>
                                <th>Station</th>
                                <th>Tank</th>
                                <th>Nozzle</th>
                                <th>Product</th>
                                <th>Dispensed Qty ({{ $settings['unit'] }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $t)
                                <tr>
                                    <td>{{ $t->id }}</td>
                                    <td>{{ \Carbon\Carbon::parse($t->transaction_date)->format('Y-m-d H:i') }}</td>
                                    <td>{{ $t->station_name }}</td>
                                    <td>{{ $t->tank_name }}</td>
                                    <td>{{ $t->nozzle_name }}</td>
                                    <td>{{ $t->product_name }}</td>
                                    <td class="fw-bold text-primary">{{ number_format($t->qty, 2) }} {{ $settings['unit'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="empty-state">No transactions available for this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS --}}
    <div class="modal fade" id="unlimitedDataModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background:#e5e6ed; color:#fff;">
                    <h5 class="modal-title">All Dispensed Transactions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-bordered w-100" id="unlimitedDataTable">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Date & Time</th>
                                <th>Station</th>
                                <th>Tank</th>
                                <th>Nozzle</th>
                                <th>Product</th>
                                <th>Dispensed Qty ({{ $settings['unit'] }})</th>
                            </tr>
                        </thead>
                        <tbody id="modalTableBody"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function () {
            const unit = @json($settings['unit']);
            const recentTxLimit = @json($settings['recentTxLimit']);
            const chartColors = @json($settings['chartColors']);

            // ---- Filter Tank dropdown by Station ----
            function filterTanksByStation() {
                const stationVal = $('#filter_station').val();
                const $tankSelect = $('#filter_tank');
                const currentTank = $tankSelect.val();

                $tankSelect.find('option').each(function () {
                    const $opt = $(this);
                    if (!$opt.val()) { return; }
                    const belongs = !stationVal || String($opt.data('station')) === String(stationVal);
                    $opt.toggle(belongs);
                });

                if (currentTank && stationVal && String($tankSelect.find('option[value="' + currentTank + '"]').data('station')) !== String(stationVal)) {
                    $tankSelect.val('');
                }
            }

            $('#filter_station').on('change', filterTanksByStation);
            filterTanksByStation();

            $('#limitedDataTable').DataTable({ pageLength: recentTxLimit, lengthChange: false, order: [] });

            // ---- Volume Trend Chart ----
            const trendLabels = @json(collect($salesTrend)->pluck('sale_date'));
            const trendQty = @json(collect($salesTrend)->pluck('qty'));

            new Chart(document.getElementById('salesTrendChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: `Dispensed Volume (${unit})`,
                        data: trendQty,
                        borderColor: chartColors[0] || '#1a237e',
                        backgroundColor: 'rgba(26,35,126,0.08)',
                        tension: 0.35,
                        fill: true
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
            });

            // ---- Product-wise Volume Split Chart ----
            const prodLabels = @json(collect($productSplit)->pluck('product_name'));
            const prodQty    = @json(collect($productSplit)->pluck('total_qty'));

            new Chart(document.getElementById('productSplitChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: prodLabels,
                    datasets: [{ data: prodQty, backgroundColor: chartColors }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // ---- Tank Volume Gain/Loss Bar Chart ----
            const tankLabels = @json(collect($tankPerformance)->pluck('tank_name'));
            const tankGains = @json(collect($tankPerformance)->pluck('volume_gain_loss'));
            const tankBarColors = tankGains.map(v => v >= 0 ? '#00c853' : '#d50000');

            new Chart(document.getElementById('tankProfitChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: tankLabels,
                    datasets: [{
                        label: `Volume Gain / Loss (${unit})`,
                        data: tankGains,
                        backgroundColor: tankBarColors,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let val = context.raw || 0;
                                    return val >= 0 ? ` + Gain: ${val.toLocaleString()} ${unit}` : ` - Loss: ${Math.abs(val).toLocaleString()} ${unit}`;
                                }
                            }
                        }
                    },
                    scales: { y: { beginAtZero: true } }
                }
            });

            // ---- Show All Transactions Modal (AJAX) ----
            $('#unlimitedDataModal').on('show.bs.modal', function () {
                if ($.fn.DataTable.isDataTable('#unlimitedDataTable')) {
                    $('#unlimitedDataTable').DataTable().destroy();
                }
                $('#modalTableBody').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');

                $.ajax({
                    url: "{{ route('report.fetchUnlimitedData') }}",
                    type: "GET",
                    data: {
                        station_id: $('#filter_station').val(),
                        tank_id: $('#filter_tank').val(),
                        from_date: $('#filter_from_date').val(),
                        to_date: $('#filter_to_date').val()
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            let rows = '';
                            response.data.forEach(function (item) {
                                rows += `<tr>
                                    <td>${item.id}</td>
                                    <td>${item.date}</td>
                                    <td>${item.station}</td>
                                    <td>${item.tank}</td>
                                    <td>${item.nozzle}</td>
                                    <td>${item.product}</td>
                                    <td class="fw-bold">${item.qty} ${unit}</td>
                                </tr>`;
                            });
                            $('#modalTableBody').html(rows || '<tr><td colspan="7" class="text-center">No records.</td></tr>');
                            $('#unlimitedDataTable').DataTable({ paging: true, pageLength: 50, order: [] });
                        }
                    }
                });
            });
        });
    </script>
@endsection