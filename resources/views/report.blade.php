@extends('partials.layouts.master')

@section('title', 'Dashboard | Station Overview')
@section('title-sub', 'Analytics')
@section('pagetitle', 'Dashboard')

@section('css')
    <!-- <link rel="stylesheet" href="{{ asset('css/report.css') }}"> -->
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

        .filter-section .form-control:focus,
        .filter-section .form-select:focus {
            border-color: #1a237e;
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
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

        .table-dashboard tbody tr:hover { background: #f8f9fb; }

        .badge-profit, .badge-loss, .badge-pending, .badge-received, .badge-partial {
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            display: inline-block;
        }

        .badge-profit    { background: #00c853; }
        .badge-loss      { background: #d50000; }
        .badge-pending   { background: #ff6d00; }
        .badge-received  { background: #0091ea; }
        .badge-partial   { background: #6a1b9a; }

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
        .text-loss { color: #d50000; font-weight: 700; }

        .empty-state { text-align: center; color: #9aa0ac; padding: 30px 0; font-size: 13.5px; }

        @media (max-width: 768px) {
            .stat-value { font-size: 20px; }
            .filter-section .row > div { margin-bottom: 12px; }
            .chart-box { height: 220px; }
        }
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

        {{-- 2. STAT CARDS --}}
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
                    <div class="stat-icon bg-icon-blue"><i class="fas fa-tint"></i></div>
                    <div>
                        <div class="stat-value">{{ $stats['total_dispensers'] }}</div>
                        <div class="stat-label">Dispensers</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="dashboard-stat-card p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon bg-icon-purple"><i class="fas fa-faucet"></i></div>
                    <div>
                        <div class="stat-value">{{ $stats['total_nozzles'] }}</div>
                        <div class="stat-label">Nozzles</div>
                    </div>
                </div>
            </div>
            <!-- <div class="col-6 col-md-3">
                <div class="dashboard-stat-card p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon bg-icon-orange"><i class="fas fa-receipt"></i></div>
                    <div>
                        <div class="stat-value">{{ $stats['total_transactions'] }}</div>
                        <div class="stat-label">Sales Transactions</div>
                    </div>
                </div>
            </div> -->
            <div class="col-6 col-md-3">
                <div class="dashboard-stat-card p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon bg-icon-navy"><i class="fas fa-oil-can"></i></div>
                    <div>
                        <div class="stat-value">{{ number_format($stats['total_qty'], 0) }} L</div>
                        <div class="stat-label">Fuel Sold</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="dashboard-stat-card p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon bg-icon-blue"><i class="fas fa-money-bill-wave"></i></div>
                    <div>
                        <div class="stat-value">Rs. {{ number_format($stats['total_amount'], 0) }}</div>
                        <div class="stat-label">Sales Revenue</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="dashboard-stat-card p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon {{ $stats['total_profit'] >= 0 ? 'bg-icon-green' : 'bg-icon-red' }}">
                        <i class="fas {{ $stats['total_profit'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                    </div>
                    <div>
                        <div class="stat-value {{ $stats['total_profit'] >= 0 ? 'text-profit' : 'text-loss' }}">
                            Rs. {{ number_format($stats['total_profit'], 0) }}
                        </div>
                        <div class="stat-label">Net Gain / Loss</div>
                    </div>
                </div>
            </div>
            <!-- <div class="col-6 col-md-3">
                <div class="dashboard-stat-card p-3 d-flex align-items-center gap-3">
                    <div class="stat-icon bg-icon-orange"><i class="fas fa-truck-loading"></i></div>
                    <div>
                        <div class="stat-value">{{ $stats['pending_orders'] }}</div>
                        <div class="stat-label">Pending Orders</div>
                    </div>
                </div>
            </div> -->
        </div>

        {{-- 3. CHARTS --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-5">
                <div class="section-card h-100">
                    <div class="section-header"><h5>Sales Trend</h5></div>
                    <div class="section-body">
                        <div class="chart-box"><canvas id="salesTrendChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="section-card h-100">
                    <div class="section-header"><h5>Product-wise Sales</h5></div>
                    <div class="section-body">
                        <div class="chart-box"><canvas id="productSplitChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="section-card h-100">
                    <div class="section-header"><h5>Tank Profit/Loss</h5></div>
                    <div class="section-body">
                        <div class="chart-box"><canvas id="tankProfitChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. TANK-WISE GAIN/LOSS REPORT --}}
        <div class="section-card">
            <div class="section-header">
                <h5><i class="fas fa-balance-scale text-primary me-2"></i>Tank-wise Gain / Loss (FIFO)</h5>
            </div>
            <div class="section-body p-0">
                <div class="table-responsive">
                    <table class="table table-dashboard mb-0">
                        <thead>
                            <tr>
                                <th>Station</th>
                                <th>Tank</th>
                                <th>Product</th>
                                <th>Sales Count</th>
                                <th>Qty Sold (L)</th>
                                <th>Avg Cost Rate</th>
                                <th>Avg Sale Rate</th>
                                <th>Total Cost</th>
                                <th>Total Revenue</th>
                                <th>Gain / Loss</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tankPerformance as $row)
                                <tr>
                                    <td>{{ $row->station_name }}</td>
                                    <td>{{ $row->tank_name }}</td>
                                    <td>{{ $row->product_name }}</td>
                                    <td>{{ $row->total_sales }}</td>
                                    <td>{{ number_format($row->total_qty_sold, 2) }}</td>
                                    <td>{{ number_format($row->avg_cost_rate, 2) }}</td>
                                    <td>{{ number_format($row->avg_sale_rate, 2) }}</td>
                                    <td>Rs. {{ number_format($row->total_cost, 2) }}</td>
                                    <td>Rs. {{ number_format($row->total_revenue, 2) }}</td>
                                    <td>
                                        <span class="badge-{{ $row->total_profit >= 0 ? 'profit' : 'loss' }}">
                                            {{ $row->total_profit >= 0 ? 'Profit' : 'Loss' }}
                                            Rs. {{ number_format(abs($row->total_profit), 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="empty-state">No sales in this period for the selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- 5. FIFO INVENTORY LAYERS --}}
            <div class="col-lg-5">
                <div class="section-card h-100">
                    <div class="section-header">
                        <h5><i class="fas fa-layer-group text-primary me-2"></i>Current FIFO Stock Layers</h5>
                    </div>
                    <div class="section-body">
                        @forelse($fifoLayers as $layer)
                            <div class="layer-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="layer-product">{{ $layer->product_name }} &middot; {{ $layer->tank_name }}</div>
                                    <div class="layer-detail">{{ $layer->station_name }} &nbsp;|&nbsp; Rate: Rs. {{ number_format($layer->cost_rate, 2) }} &nbsp;|&nbsp; {{ \Carbon\Carbon::parse($layer->created_at)->format('d M Y, h:i A') }}</div>
                                </div>
                                <div class="layer-qty">{{ number_format($layer->remaining_qty, 2) }} L</div>
                            </div>
                        @empty
                            <div class="empty-state">No stock layers with remaining quantity found.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- 6. RECENT PURCHASES & RECEIVES --}}
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
                                        <th>Ordered</th>
                                        <th>Received</th>
                                        <th>Rate</th>
                                        <th>Total Cost</th>
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
                                            <td>{{ number_format($p->ordered_qty, 0) }} L</td>
                                            <td>{{ number_format($p->recived_qty, 0) }} L</td>
                                            <td>Rs. {{ number_format($p->purchase_rate, 2) }}</td>
                                            <td>Rs. {{ number_format($p->total_cost, 2) }}</td>
                                            <td>
                                                @if($p->recive_status === 'Recived' && $p->recived_qty >= $p->ordered_qty)
                                                    <span class="badge-received">Received</span>
                                                @elseif($p->recived_qty > 0)
                                                    <span class="badge-partial">Partial</span>
                                                @else
                                                    <span class="badge-pending">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" class="empty-state">No purchases found for this period.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 7. RECENT TRANSACTIONS --}}
        <div class="section-card">
            <div class="section-header">
                <h5><i class="fas fa-list text-primary me-2"></i>Recent Transactions (Latest 25)</h5>
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
                                <th>Qty (L)</th>
                                <th>Rate</th>
                                <th>Amount</th>
                                <th>Profit</th>
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
                                    <td>{{ number_format($t->qty, 2) }}</td>
                                    <td>{{ number_format($t->sale_rate, 2) }}</td>
                                    <td>Rs. {{ number_format($t->total_amount, 2) }}</td>
                                    <td class="{{ $t->profit >= 0 ? 'text-profit' : 'text-loss' }}">Rs. {{ number_format($t->profit, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="empty-state">No transactions available for this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL - ALL TRANSACTIONS --}}
    <div class="modal fade" id="unlimitedDataModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background:#1a237e; color:#fff;">
                    <h5 class="modal-title">All Transactions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                <th>Qty (L)</th>
                                <th>Rate</th>
                                <th>Amount</th>
                                <th>Profit</th>
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

            // ---- Filter Tank dropdown by selected Station ----
            function filterTanksByStation() {
                const stationVal = $('#filter_station').val();
                const $tankSelect = $('#filter_tank');
                const currentTank = $tankSelect.val();

                $tankSelect.find('option').each(function () {
                    const $opt = $(this);
                    if (!$opt.val()) { return; } // keep "All Tanks"
                    const belongs = !stationVal || String($opt.data('station')) === String(stationVal);
                    $opt.toggle(belongs);
                });

                // If the currently selected tank no longer belongs to the station, reset it
                const $selectedOpt = $tankSelect.find('option[value="' + currentTank + '"]');
                if (currentTank && stationVal && String($selectedOpt.data('station')) !== String(stationVal)) {
                    $tankSelect.val('');
                }
            }

            $('#filter_station').on('change', filterTanksByStation);
            filterTanksByStation(); // apply on initial page load too

            $('#limitedDataTable').DataTable({
                pageLength: 25,
                lengthChange: false,
                order: []
            });

            // ---- Sales Trend (Line) ----
            const trendLabels = {!! json_encode(collect($salesTrend)->pluck('sale_date')) !!};
            const trendAmount = {!! json_encode(collect($salesTrend)->pluck('amount')) !!};
            const trendProfit = {!! json_encode(collect($salesTrend)->pluck('profit')) !!};

            new Chart(document.getElementById('salesTrendChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [
                        {
                            label: 'Revenue (Rs.)',
                            data: trendAmount,
                            borderColor: '#1a237e',
                            backgroundColor: 'rgba(26,35,126,0.08)',
                            tension: 0.35,
                            fill: true
                        },
                        {
                            label: 'Profit (Rs.)',
                            data: trendProfit,
                            borderColor: '#00c853',
                            backgroundColor: 'rgba(0,200,83,0.08)',
                            tension: 0.35,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true } }
                }
            });

            // ---- Product-wise Split (Doughnut) ----
            const prodLabels = {!! json_encode(collect($productSplit)->pluck('product_name')) !!};
            const prodAmounts = {!! json_encode(collect($productSplit)->pluck('total_amount')) !!};

            new Chart(document.getElementById('productSplitChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: prodLabels,
                    datasets: [{
                        data: prodAmounts,
                        backgroundColor: ['#1a237e', '#0091ea', '#00c853', '#ff6d00', '#6a1b9a', '#d50000']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });

            // ---- Tank Profit/Loss (Bar) ----
            const tankLabels = {!! json_encode(collect($tankPerformance)->pluck('tank_name')) !!};
            const tankProfits = {!! json_encode(collect($tankPerformance)->pluck('total_profit')) !!};
            const tankColors = tankProfits.map(v => v >= 0 ? '#00c853' : '#d50000');

            new Chart(document.getElementById('tankProfitChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: tankLabels,
                    datasets: [{
                        label: 'Profit / Loss (Rs.)',
                        data: tankProfits,
                        backgroundColor: tankColors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });

            // ---- Show All Modal (AJAX) ----
            $('#unlimitedDataModal').on('show.bs.modal', function () {
                if ($.fn.DataTable.isDataTable('#unlimitedDataTable')) {
                    $('#unlimitedDataTable').DataTable().destroy();
                }
                $('#modalTableBody').html('<tr><td colspan="10" class="text-center">Loading data... please wait</td></tr>');

                const filters = {
                    station_id: $('#filter_station').val(),
                    tank_id: $('#filter_tank').val(),
                    from_date: $('#filter_from_date').val(),
                    to_date: $('#filter_to_date').val()
                };

                $.ajax({
                    url: "{{ route('report.fetchUnlimitedData') }}",
                    type: "GET",
                    data: filters,
                    success: function (response) {
                        if (response.status === 'success') {
                            let rows = '';
                            response.data.forEach(function (item) {
                                const profitClass = parseFloat(item.profit) >= 0 ? 'text-success' : 'text-danger';
                                rows += `<tr>
                                    <td>${item.id}</td>
                                    <td>${item.date}</td>
                                    <td>${item.station}</td>
                                    <td>${item.tank}</td>
                                    <td>${item.nozzle}</td>
                                    <td>${item.product}</td>
                                    <td>${item.qty}</td>
                                    <td>${item.rate}</td>
                                    <td>Rs. ${item.amount}</td>
                                    <td class="${profitClass}">Rs. ${item.profit}</td>
                                </tr>`;
                            });
                            $('#modalTableBody').html(rows || '<tr><td colspan="10" class="text-center">No records found.</td></tr>');

                            $('#unlimitedDataTable').DataTable({
                                paging: true,
                                pageLength: 50,
                                lengthMenu: [[50, 100, 500, -1], [50, 100, 500, "All"]],
                                order: []
                            });
                        }
                    },
                    error: function () {
                        $('#modalTableBody').html('<tr><td colspan="10" class="text-center text-danger">Error loading data.</td></tr>');
                    }
                });
            });
        });
    </script>
@endsection