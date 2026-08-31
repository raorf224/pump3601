@extends('partials.layouts.master')

@section('title', 'Comprehensive Shift Reports | ' . Auth::user()->full_name)
@section('title-sub', 'Employee')
@section('pagetitle', 'Shift Management')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <style>
        .profit {
            color: #28a745;
            font-weight: bold;
        }

        .loss {
            color: #dc3545;
            font-weight: bold;
        }

        .shift-row {
            cursor: pointer;
            transition: all 0.2s;
        }

        .shift-row:hover {
            background-color: #e3f2fd !important;
        }

        .shift-row.active {
            background-color: #bbdefb !important;
        }

        .bg-soft-success {
            background-color: #d4edda !important;
        }

        .bg-soft-danger {
            background-color: #f8d7da !important;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 50px;
        }

        #reportContent {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div class="report-container">

                <!-- Header -->
                <div class="company-header">
                    <div class="row align-items-center">
                        <!-- <div class="col-md-8">
                            <h4 class="mb-1">📊 Comprehensive Shift Reports</h4>
                            <p class="mb-0 text-muted">Click on any shift to view detailed report</p>
                        </div> -->
                        <div class="col-md-4 text-end">
                            <button class="btn btn-success btn-sm" onclick="window.location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div id="loadingSpinner" class="loading-spinner">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading shift report...</p>
                </div>

                <!-- Error Message -->
                <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>

                <!-- Shifts List -->
                <div id="shiftListContainer">
                    @if(isset($shifts) && count($shifts) > 0)
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <i class="bi bi-list-ul"></i> Available Shifts ({{ count($shifts) }})
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Shift ID</th>
                                                <th>Station</th>
                                                <th>Type</th>
                                                <th>Incharge</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Opening Balance</th>
                                                <th>Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($shifts as $index => $shift)
                                                <tr class="shift-row" data-shift-id="{{ $shift->id }}"
                                                    onclick="loadReport({{ $shift->id }})">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td><strong>#{{ $shift->id }}</strong></td>
                                                    {{-- ✅ FIXED: Use station_name directly from query --}}
                                                    <td>{{ $shift->station_name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $shift->shift_no == 1 ? 'primary' : 'secondary' }}">
                                                            {{ $shift->shift_type ?? ($shift->shift_no == 1 ? 'Day' : 'Night') }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $shift->incharge_name ?? 'N/A' }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($shift->start_time)->format('M d, Y H:i') }}</td>
                                                    <td>
                                                        @if($shift->end_time)
                                                            {{ \Carbon\Carbon::parse($shift->end_time)->format('M d, Y H:i') }}
                                                        @else
                                                            <span class="badge bg-warning">Not Ended</span>
                                                        @endif
                                                    </td>
                                                    <td>Rs. {{ number_format($shift->cash_handover ?? 0, 2) }}</td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $shift->status == 'closed' ? 'success' : 'warning' }}">
                                                            {{ ucfirst($shift->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-outline-primary"
                                                            onclick="loadReport({{ $shift->id }})">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <a href="/comprehensive-export/{{ $shift->id }}"
                                                            class="btn btn-sm btn-success">
                                                            <i class="bi bi-file-earmark-excel"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <h5><i class="bi bi-info-circle"></i> No closed shifts found</h5>
                            <p class="mb-0">Please close some shifts to generate comprehensive reports.</p>
                        </div>
                    @endif
                </div>

                <!-- Report Content -->
                <div id="reportContent" class="mt-4" style="display: none;"></div>

                <!-- Footer -->
                <div class="mt-5 pt-3 border-top text-center">
                    <p class="text-muted small">Generated by Pump360 • {{ now()->format('M d, Y \a\t H:i') }}</p>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script>
        function loadReport(shiftId) {
            const spinner = document.getElementById('loadingSpinner');
            const reportContent = document.getElementById('reportContent');
            const errorMsg = document.getElementById('errorMessage');

            spinner.style.display = 'block';
            reportContent.style.display = 'none';
            errorMsg.style.display = 'none';

            document.querySelectorAll('.shift-row').forEach(row => {
                row.classList.remove('active');
                if (row.dataset.shiftId == shiftId) {
                    row.classList.add('active');
                }
            });

            fetch('/comprehensive-report/' + shiftId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    spinner.style.display = 'none';
                    renderReport(data);
                })
                .catch(error => {
                    spinner.style.display = 'none';
                    errorMsg.textContent = 'Error: ' + error.message;
                    errorMsg.style.display = 'block';
                    console.error('Error:', error);
                });
        }

        function renderReport(data) {
            const shift = data.shift;
            const summary = data.summary;
            const tankValues = data.tankValues;
            const lubeValues = data.lubeValues;

            let html = `
                    <div class="card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">📋 Shift #${shift.id} - ${shift.station_name}</h5>
                            <div>
                                <span class="badge bg-${shift.status == 'open' ? 'warning' : 'secondary'}">${shift.status}</span>
                                <a href="/comprehensive-export/${shift.id}" class="btn btn-success btn-sm ms-2">📊 Excel</a>
                            </div>
                        </div>
                        <div class="card-body">

                            <!-- Shift Details -->
                            <div class="row mb-4">
                                <div class="col-md-3"><strong>Shift Type:</strong> ${shift.shift_no == 1 ? 'Day' : 'Night'}</div>
                                <div class="col-md-3"><strong>Incharge:</strong> ${shift.incharge}</div>
                                <div class="col-md-3"><strong>Start:</strong> ${shift.start_time}</div>
                                <div class="col-md-3"><strong>End:</strong> ${shift.end_time}</div>
                            </div>

                            <!-- Cash Reconciliation -->
                            <h6 class="border-bottom pb-2 mb-3">💰 Cash Reconciliation</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-2"><div class="summary-card text-center p-3"><small class="text-muted">Opening Balance</small><h5 class="text-warning">Rs. ${numberFormat(summary.cash_handover)}</h5></div></div>
                                <div class="col-md-2"><div class="summary-card text-center p-3"><small class="text-muted">Cash in Hand</small><h5 class="text-primary">Rs. ${numberFormat(summary.cash_in_hand)}</h5></div></div>
                                <div class="col-md-2"><div class="summary-card text-center p-3"><small class="text-muted">Cash in Bank</small><h5 class="text-info">Rs. ${numberFormat(summary.cash_in_bank)}</h5></div></div>
                                <div class="col-md-2"><div class="summary-card text-center p-3"><small class="text-muted">Fuel Card</small><h5 class="text-primary">Rs. ${numberFormat(summary.fuel_card)}</h5></div></div>
                                <div class="col-md-2"><div class="summary-card text-center p-3"><small class="text-muted">Credit Card</small><h5 class="text-info">Rs. ${numberFormat(summary.credit_card)}</h5></div></div>
                                <div class="col-md-2"><div class="summary-card text-center p-3 bg-soft-success"><small class="text-muted">Total Cash</small><h5 class="text-success">Rs. ${numberFormat(summary.total_cash)}</h5></div></div>
                            </div>

                            <!-- Tank Values -->
                            <h6 class="border-bottom pb-2 mb-3">⛽ Products Value in Tanks</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-dark"><tr><th>#</th><th>Tank Name</th><th>Product</th><th class="text-end">Level (L)</th><th class="text-end">Price (Rs.)</th><th class="text-end">Value (Rs.)</th></tr></thead>
                                    <tbody>
                                        ${tankValues.length > 0 ? tankValues.map((t, i) => `
                                            <tr><td>${i + 1}</td><td>${t.tank_name}</td><td>${t.product_name}</td><td class="text-end">${numberFormat(t.current_level)}</td><td class="text-end">${numberFormat(t.price)}</td><td class="text-end fw-bold">Rs. ${numberFormat(t.value)}</td></tr>
                                        `).join('') : '<tr><td colspan="6" class="text-center text-muted">No tank data available</td></tr>'}
                                    </tbody>
                                    <tfoot class="table-light"><tr><td colspan="5" class="text-end fw-bold">Total Tank Value:</td><td class="text-end fw-bold text-success">Rs. ${numberFormat(summary.total_tank_value)}</td></tr></tfoot>
                                </table>
                            </div>

                            <!-- Lube Values -->
                            <h6 class="border-bottom pb-2 mb-3">🛢️ Products in Store (Lubricants)</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-dark"><tr><th>#</th><th>Product Name</th><th class="text-end">Qty</th><th class="text-end">Avg Price (Rs.)</th><th class="text-end">Value (Rs.)</th></tr></thead>
                                    <tbody>
                                        ${lubeValues.length > 0 ? lubeValues.map((l, i) => `
                                            <tr><td>${i + 1}</td><td>${l.product_name}</td><td class="text-end">${numberFormat(l.quantity)}</td><td class="text-end">${numberFormat(l.avg_price)}</td><td class="text-end fw-bold">Rs. ${numberFormat(l.value)}</td></tr>
                                        `).join('') : '<tr><td colspan="5" class="text-center text-muted">No lubricant inventory data</td></tr>'}
                                    </tbody>
                                    <tfoot class="table-light"><tr><td colspan="4" class="text-end fw-bold">Total Store Value:</td><td class="text-end fw-bold text-success">Rs. ${numberFormat(summary.total_lube_value)}</td></tr></tfoot>
                                </table>
                            </div>

                            <!-- Profit Loss -->
                            <h6 class="border-bottom pb-2 mb-3">📈 Profit / Loss Summary</h6>
                            <div class="row g-3">
                                <div class="col-md-3"><div class="summary-card text-center p-3"><small class="text-muted">Opening Balance</small><h6>Rs. ${numberFormat(summary.cash_handover)}</h6></div></div>
                                <div class="col-md-3"><div class="summary-card text-center p-3"><small class="text-muted">Total Purchases</small><h6>Rs. ${numberFormat(summary.total_purchases)}</h6></div></div>
                                <div class="col-md-3"><div class="summary-card text-center p-3"><small class="text-muted">Total Investment</small><h6>Rs. ${numberFormat(summary.total_investment)}</h6></div></div>
                                <div class="col-md-3"><div class="summary-card text-center p-3"><small class="text-muted">Inventory Value</small><h6>Rs. ${numberFormat(summary.total_inventory_value)}</h6></div></div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6 mx-auto">
                                    <div class="summary-card text-center p-4 ${summary.profit_loss >= 0 ? 'bg-soft-success' : 'bg-soft-danger'}">
                                        <h5 class="text-muted">PROFIT / LOSS</h5>
                                        <h2 class="${summary.profit_loss >= 0 ? 'profit' : 'loss'}">
                                            ${summary.profit_loss >= 0 ? '📈' : '📉'}
                                            Rs. ${numberFormat(summary.profit_loss)}
                                            <small>(${summary.profit_loss_percentage.toFixed(2)}%)</small>
                                        </h2>
                                        <p class="mb-0">${summary.profit_loss >= 0 ? '✅ Profit' : '❌ Loss'}</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>`;

            document.getElementById('reportContent').innerHTML = html;
            document.getElementById('reportContent').style.display = 'block';
            document.getElementById('reportContent').scrollIntoView({ behavior: 'smooth' });
        }

        function numberFormat(num) {
            return parseFloat(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Load first shift automatically
        document.addEventListener('DOMContentLoaded', function () {
            @if(isset($shifts) && count($shifts) > 0)
                const firstShift = document.querySelector('.shift-row');
                if (firstShift) {
                    loadReport(firstShift.dataset.shiftId);
                }
            @endif
            });
    </script>
@endsection