<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Stock Report - {{ $shift->station->name ?? 'Fuel Station' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            .container {
                max-width: 100% !important;
            }
        }

        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .company-header {
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .summary-card {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .variance-positive {
            color: #28a745;
            font-weight: bold;
        }

        .variance-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.5rem;
        }

        .summary-card {
            background: #ffffff;
            border-radius: 12px;
            transition: all 0.25s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
        }

        .summary-card h4,
        .summary-card h5 {
            margin-bottom: 10px;
        }

        .summary-card p {
            font-size: 14px;
        }

        .summary-card small {
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="report-container">
        @php
        // ✅ DEFINE totalSalesLitres VARIABLE HERE
        $totalSalesLitres = 0;
        if (isset($nozzleReadings) && isset($nozzleResets)) {
        $totalSalesLitres = $nozzleReadings->sum('total_dispensed') + $nozzleResets->sum('total_dispensed');
        }
        @endphp
        @if(isset($shift) && $shift)
        <!-- REPORT VIEW -->
        <div class="company-header">
            <div class="row">
                <div class="col-8">
                    <h2 class="mb-1">Shift Stock Reconciliation Report</h2>
                    <h4 class="text-muted mb-2">{{ $shift->station->name ?? 'N/A' }}</h4>
                    <p class="mb-0 text-muted">Professional Fuel Management System</p>
                </div>
                <div class="col-4 text-end">
                    <div class="no-print">
                        <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                            📄 Download PDF
                        </button>
                        <a href="/shifts" class="btn btn-outline-secondary btn-sm">
                            ← Back to Shifts
                        </a>
                    </div>
                    <p class="mb-0"><small>Report ID: SHIFT-{{ $shift->id }}</small></p>
                    <!-- <p class="mb-0"><small>Generated: {{ now()->format('M d, Y H:i') }}</small></p> -->
                </div>
            </div>
        </div>

        <!-- Shift Overview -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Shift Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Station:</strong><br>
                        <span class="text-dark">{{ $shift->station->name ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-2">
                        <strong>Shift Type:</strong><br>
                        <span class="text-dark">{{ $shift->shift_no == 1 ? 'Day Shift' : 'Night Shift' }}</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Shift Incharge:</strong><br>
                        <span class="text-dark">{{ $shift->shiftIncharger->user->full_name ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-2">
                        <strong>Opening Balance:</strong><br>
                        <span class="text-dark">Rs. {{ number_format($shift->cash_handover ?? 0, 2) }}</span>
                    </div>
                    <div class="col-md-2">
                        <strong>Status:</strong><br>
                        <span class="badge bg-{{ $shift->status == 'open' ? 'warning' : 'secondary' }}">
                            {{ ucfirst($shift->status) }}
                        </span>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <strong>Start Time:</strong><br>
                        <span class="text-dark">{{ \Carbon\Carbon::parse($shift->start_time)->format('M d, Y H:i')
                            }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>End Time:</strong><br>
                        <span class="text-dark">
                            @if($shift->end_time)
                            {{ \Carbon\Carbon::parse($shift->end_time)->format('M d, Y H:i') }}
                            @else
                            Not Ended
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Stock Analysis Table -->
        @if(count($tankCalculations) > 0)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Professional Stock Analysis</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tank</th>
                                <th>Product</th>
                                <th class="text-end">Opening (L)</th>
                                <th class="text-end">Closing (L)</th>
                                <!-- <th class="text-end">Physical Usage (L)</th> -->
                                <th class="text-end">Oil Recived (L)</th>
                                <!-- <th class="text-end">Adjusted Usage (L)</th> -->
                                <th class="text-end">Nozzle Sales (L)</th>
                                <th class="text-end">Variance (L)</th>
                                <th class="text-end">Variance %</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tankCalculations as $calculation)
                            <tr>
                                <td>{{ $calculation['tank_name'] }}</td>
                                <td>{{ $calculation['product_name'] }}</td>
                                <td class="text-end">{{ number_format($calculation['opening_stock'], 2) }}</td>
                                <td class="text-end">{{ number_format($calculation['closing_stock'], 2) }}</td>
                                <!-- <td class="text-end {{ $calculation['physical_usage'] > 0 ? 'text-success' : ($calculation['physical_usage'] < 0 ? 'text-danger' : '') }}">
                                    @if($calculation['physical_usage'] > 0)
                                        +{{ number_format($calculation['physical_usage'], 2) }}
                                    @elseif($calculation['physical_usage'] < 0)
                                        {{ number_format($calculation['physical_usage'], 2) }}
                                    @else
                                        0.00
                                    @endif
                                </td> -->
                                <td class="text-end">{{ number_format($calculation['oil_purchased'], 2) }}</td>
                                <!-- <td class="text-end {{ $calculation['adjusted_physical_usage'] > 0 ? 'text-success' : ($calculation['adjusted_physical_usage'] < 0 ? 'text-danger' : '') }}">
                                    @if($calculation['adjusted_physical_usage'] > 0)
                                        +{{ number_format($calculation['adjusted_physical_usage'], 2) }}
                                    @elseif($calculation['adjusted_physical_usage'] < 0)
                                        {{ number_format($calculation['adjusted_physical_usage'], 2) }}
                                    @else
                                        0.00
                                    @endif
                                </td> -->
                                <td class="text-end">{{ number_format($calculation['total_nozzle_sales'], 2) }}</td>
                                <td
                                    class="text-end {{ $calculation['gain_loss_class'] == 'success' ? 'variance-positive' : ($calculation['gain_loss_class'] == 'danger' ? 'variance-negative' : '') }}">
                                    @if($calculation['variance'] > 0)
                                    +{{ number_format($calculation['variance'], 2) }}
                                    @elseif($calculation['variance'] < 0) {{ number_format($calculation['variance'], 2)
                                        }} @else 0.00 @endif </td>
                                <td
                                    class="text-end {{ abs($calculation['variance_percent']) <= 0.5 ? 'text-success' : (abs($calculation['variance_percent']) <= 1.0 ? 'text-warning' : 'text-danger') }}">
                                    @if($calculation['total_nozzle_sales'] > 0)
                                    @if($calculation['variance_percent'] > 0)
                                    +{{ number_format($calculation['variance_percent'], 2) }}%
                                    @elseif($calculation['variance_percent'] < 0) {{
                                        number_format($calculation['variance_percent'], 2) }}% @else 0.00% @endif @else
                                        N/A @endif </td>
                                <td>
                                    <span class="badge bg-{{ $calculation['status_class'] }}">
                                        {{ $calculation['status'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tank-wise Detailed Analysis -->
        <h5 class="mb-3">Tank-wise Detailed Analysis</h5>
        @foreach($tankCalculations as $tankId => $calculation)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">{{ $calculation['tank_name'] }} - {{ $calculation['product_name'] }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Stock Movement -->
                    <div class="col-md-6">
                        <div class="summary-card">
                            <h6>Stock Movement (Liters)</h6>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td width="60%">Opening Stock:</td>
                                    <td class="text-end">{{ number_format($calculation['opening_stock'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Closing Stock:</td>
                                    <td class="text-end">{{ number_format($calculation['closing_stock'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Physical Usage:</td>
                                    <td
                                        class="text-end {{ $calculation['physical_usage'] > 0 ? 'text-success' : ($calculation['physical_usage'] < 0 ? 'text-danger' : '') }}">
                                        @if($calculation['physical_usage'] > 0)
                                        +{{ number_format($calculation['physical_usage'], 2) }}
                                        @elseif($calculation['physical_usage'] < 0) {{
                                            number_format($calculation['physical_usage'], 2) }} @else 0.00 @endif </td>
                                </tr>
                                @if($calculation['oil_purchased'] > 0)
                                <tr>
                                    <td>Oil Purchased:</td>
                                    <td class="text-end text-info">+{{ number_format($calculation['oil_purchased'], 2)
                                        }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Adjusted Usage:</strong></td>
                                    <td
                                        class="text-end {{ $calculation['adjusted_physical_usage'] > 0 ? 'text-success' : ($calculation['adjusted_physical_usage'] < 0 ? 'text-danger' : '') }}">
                                        <strong>
                                            @if($calculation['adjusted_physical_usage'] > 0)
                                            +{{ number_format($calculation['adjusted_physical_usage'], 2) }}
                                            @elseif($calculation['adjusted_physical_usage'] < 0) {{
                                                number_format($calculation['adjusted_physical_usage'], 2) }} @else 0.00
                                                @endif </strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Sales & Variance -->
                    <div class="col-md-6">
                        <div class="summary-card">
                            <h6>Sales & Variance Analysis</h6>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td width="60%">Nozzle Sales (Liters):</td>
                                    <td class="text-end">{{ number_format($calculation['nozzle_sales_liters'], 2) }} L
                                    </td>
                                </tr>
                                <tr>
                                    <td>Reset Sales (Liters):</td>
                                    <td class="text-end">{{ number_format($calculation['reset_sales_liters'], 2) }} L
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Total Nozzle Sales:</strong></td>
                                    <td class="text-end"><strong>{{ number_format($calculation['total_nozzle_sales'], 2)
                                            }} L</strong></td>
                                </tr>
                                <tr
                                    class="{{ $calculation['gain_loss_class'] == 'success' ? 'table-success' : ($calculation['gain_loss_class'] == 'danger' ? 'table-danger' : 'table-secondary') }}">
                                    <td><strong>Variance Analysis:</strong></td>
                                    <td class="text-end">
                                        <strong
                                            class="{{ $calculation['gain_loss_class'] == 'success' ? 'variance-positive' : ($calculation['gain_loss_class'] == 'danger' ? 'variance-negative' : '') }}">
                                            @if($calculation['variance'] > 0)
                                            +{{ number_format($calculation['variance'], 2) }} L
                                            @elseif($calculation['variance'] < 0) {{
                                                number_format($calculation['variance'], 2) }} L @else 0.00 L @endif
                                                @if($calculation['total_nozzle_sales']> 0)
                                                ({{ number_format($calculation['variance_percent'], 2) }}%)
                                                @endif
                                        </strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $calculation['variance_text'] }}
                                        </small>
                                        <br>
                                        <span class="badge bg-{{ $calculation['gain_loss_class'] }}">
                                            {{ $calculation['gain_loss'] }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- Nozzle Details -->
                @if(isset($nozzleReadings) && count($nozzleReadings->where('nozzle.tank_id', $tankId)) > 0)
                <div class="mt-3">
                    <h6>Nozzle Transactions</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Nozzle</th>
                                    <th>Dispenser</th>
                                    <th class="text-end">Opening</th>
                                    <th class="text-end">Closing</th>
                                    <th class="text-end">Dispensed</th>
                                    <th class="text-end">Rate (Rs.)</th>
                                    <th class="text-end">Amount (Rs.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nozzleReadings->where('nozzle.tank_id', $tankId) as $reading)
                                <tr>
                                    <td>{{ $reading->nozzle->name ?? 'N/A' }}</td>
                                    <td>{{ $reading->nozzle->dispenser->name ?? 'N/A' }}</td>
                                    <td class="text-end">{{ number_format($reading->opening_reading, 2) }}</td>
                                    <td class="text-end">{{ number_format($reading->closing_reading, 2) }}</td>
                                    <td class="text-end">{{ number_format($reading->total_dispensed, 2) }}</td>
                                    <td class="text-end">{{ number_format($reading->rate, 2) }}</td>
                                    <td class="text-end">{{ number_format($reading->total_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Nozzle Resets -->
                @if(isset($nozzleResets) && count($nozzleResets->where('nozzle.tank_id', $tankId)) > 0)
                <div class="mt-3">
                    <h6>Nozzle Reset Records</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Nozzle</th>
                                    <th>Reset Time</th>
                                    <th class="text-end">Previous</th>
                                    <th class="text-end">New</th>
                                    <th class="text-end">Dispensed</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nozzleResets->where('nozzle.tank_id', $tankId) as $reset)
                                <tr>
                                    <td>{{ $reset->nozzle->name ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($reset->reset_date)->format('M d, H:i') }}</td>
                                    <td class="text-end">{{ number_format($reset->old_reading, 2) }}</td>
                                    <td class="text-end">{{ number_format($reset->new_reading, 2) }}</td>
                                    <td class="text-end">{{ number_format($reset->total_dispensed, 2) }}</td>
                                    <td>{{ $reset->reason ?? 'Maintenance' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
        @else
        <div class="alert alert-info">
            <h6>No Data Available</h6>
            <p class="mb-0">No tank dips or nozzle readings recorded for this shift period.</p>
        </div>
        @endif

        <!-- Complete Financial Summary -->
        <!-- Complete Financial Summary -->
        @if(isset($financialSummary))
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Complete Financial Summary</h5>
            </div>

            <div class="card-body">

                <!-- TOP SUMMARY -->
                <div class="row g-4 text-center mb-4">

                    <!-- Revenue Summary -->
                    <div class="col-md-4">
                        <div class="summary-card h-100 p-4 border rounded bg-white">
                            <h4 class="text-success fw-bold">
                                Rs. {{ number_format($financialSummary['total_revenue'] ?? 0, 2) }}
                            </h4>
                            <p class="mb-3 text-muted fw-semibold">Total Revenue</p>
                            <div class="small text-start">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Fuel Sales:</span>
                                    <strong>Rs. {{ number_format($financialSummary['fuel_sales'] ?? 0, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Lube Sales:</span>
                                    <strong>Rs. {{ number_format($financialSummary['lube_sales'] ?? 0, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Other Income:</span>
                                    <strong>Rs. {{ number_format($financialSummary['transaction_income'] ?? 0, 2)
                                        }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expense Summary -->
                    <div class="col-md-4">
                        <div class="summary-card h-100 p-4 border rounded bg-white">
                            <h4 class="text-danger fw-bold">
                                Rs. {{ number_format($financialSummary['total_expenses'] ?? 0, 2) }}
                            </h4>
                            <p class="mb-3 text-muted fw-semibold">Total Expenses</p>
                            <div class="small text-start">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Fuel Oil Purchase:</span>
                                    <strong>Rs. {{ number_format($financialSummary['oil_purchase'] ?? 0, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Lube Purchase:</span>
                                    <strong>Rs. {{ number_format($financialSummary['lube_purchase'] ?? 0, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Other Expenses:</span>
                                    <strong>Rs. {{ number_format($financialSummary['transaction_expense'] ?? 0, 2)
                                        }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Income + Cards -->
                    <div class="col-md-4">
                        <!-- Net Income -->
                        <div class="summary-card p-4 border rounded bg-white mb-3">
                            <h4
                                class="{{ ($financialSummary['net_income'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                Rs. {{ number_format($financialSummary['net_income'] ?? 0, 2) }}
                            </h4>
                            <p class="mb-0 text-muted fw-semibold">Net Income</p>
                        </div>

                        <!-- Fuel + Credit Cards -->
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="summary-card p-3 border rounded bg-white h-100">
                                    <h5 class="text-primary fw-bold mb-1">
                                        Rs. {{ number_format($cashFlow->fuelcard ?? 0, 2) }}
                                    </h5>
                                    <small class="text-muted fw-semibold">Fuel Card</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="summary-card p-3 border rounded bg-white h-100">
                                    <h5 class="text-info fw-bold mb-1">
                                        Rs. {{ number_format($cashFlow->creditcard ?? 0, 2) }}
                                    </h5>
                                    <small class="text-muted fw-semibold">Credit Card</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CASH POSITION -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="summary-card p-4 border rounded bg-white text-center h-100">
                            <h4 class="text-warning fw-bold">
                                Rs. {{ number_format($financialSummary['cash_handover'] ?? 0, 2) }}
                            </h4>
                            <p class="mb-0 text-muted fw-semibold">Opening Balance</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card p-4 border rounded bg-white text-center h-100">
                            <h4 class="text-primary fw-bold">
                                Rs. {{ number_format($financialSummary['cash_in_hand'] ?? 0, 2) }}
                            </h4>
                            <p class="mb-0 text-muted fw-semibold">Closing Balance</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card p-4 border rounded bg-white text-center h-100">
                            <h4 class="text-info fw-bold">
                                Rs. {{ number_format($financialSummary['cash_in_bank'] ?? 0, 2) }}
                            </h4>
                            <p class="mb-0 text-muted fw-semibold">Cash in Bank</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card p-4 border rounded bg-white text-center h-100">
                            <h4 class="text-dark fw-bold">
                                Rs. {{ number_format($financialSummary['total_cash_balance'] ?? 0, 2) }}
                            </h4>
                            <p class="mb-0 text-muted fw-semibold">Total Cash Balance</p>
                        </div>
                    </div>
                </div>

                <!-- ================================================ -->
                <!-- ✅ NEW: SHORTAGE PAYMENTS SECTION (ADDED HERE) -->
                <!-- ================================================ -->
                @php
                // Fetch shortage payments for this shift - ONLY WITH oil_recived_id
                $shiftShortagePayments = DB::select('
                SELECT
                sapb.id,
                sapb.shift_id,
                sapb.oil_purchase_id,
                sapb.oil_recived_id,
                sapb.account_id,
                sapb.total_shortage,
                sapb.total_amount,
                sapb.is_paid,
                sapb.created_at,
                sapb.payment_type,
                op.id as purchase_id,
                op.rate as purchase_rate,
                op.qty as purchase_qty,
                a.name as supplier_name,
                ort.recive_date,
                ort.recived_qty as tank_received_qty,
                ort.vehicle_number,
                CASE
                WHEN sapb.account_id IS NOT NULL THEN "bank"
                ELSE "cash"
                END as payment_method,
                acc.name as bank_name,
                acc.account_number
                FROM shortage_ammount_paid_back sapb
                LEFT JOIN oil_purchase op ON sapb.oil_purchase_id = op.id
                LEFT JOIN accounts a ON op.supplier_id = a.id
                LEFT JOIN oil_recived_tanks ort ON sapb.oil_recived_id = ort.id
                LEFT JOIN accounts acc ON sapb.account_id = acc.id
                WHERE sapb.shift_id = ' . ($shift->id ?? 0) . '
                AND sapb.oil_recived_id IS NOT NULL -- ✅ ONLY WHERE oil_recived_id EXISTS
                ORDER BY sapb.created_at DESC
                ');

                // Calculate totals
                $totalShortagePaid = 0;
                $totalShortageLiters = 0;
                $totalCashShortage = 0;
                $totalBankShortage = 0;
                $totalFullPayments = 0;
                $totalPartialPayments = 0;

                foreach ($shiftShortagePayments as $payment) {
                $totalShortagePaid += floatval($payment->total_amount ?? 0);
                $totalShortageLiters += floatval($payment->total_shortage ?? 0);
                if (($payment->payment_method ?? '') == 'cash') {
                $totalCashShortage += floatval($payment->total_amount ?? 0);
                } else {
                $totalBankShortage += floatval($payment->total_amount ?? 0);
                }

                if (($payment->payment_type ?? '') == 'full') {
                $totalFullPayments++;
                } else {
                $totalPartialPayments++;
                }
                }
                @endphp

            </div>
        </div>
        @endif

        <!-- Lubricant Transactions Section -->
        @if($lubeDocuments->count() > 0)
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Lubricant Transactions</h5>
            </div>
            <div class="card-body">
                <!-- Purchase Summary -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="summary-card">
                            <h6>Lubricant Purchase Summary</h6>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td>Total Quantity:</td>
                                    <td class="text-end">{{ number_format($lubeSummary['purchase']['total_qty'], 2) }}
                                        Units</td>
                                </tr>
                                <tr>
                                    <td>Total Amount:</td>
                                    <td class="text-end">Rs. {{ number_format($lubeSummary['purchase']['total_amount'],
                                        2) }}</td>
                                </tr>
                                <tr>
                                    <td>Cash Paid:</td>
                                    <td class="text-end">Rs. {{ number_format($lubeSummary['purchase']['cash_paid'], 2)
                                        }}</td>
                                </tr>
                                <tr>
                                    <td>Bank Paid:</td>
                                    <td class="text-end">Rs. {{ number_format($lubeSummary['purchase']['bank_paid'], 2)
                                        }}</td>
                                </tr>
                                <tr>
                                    <td>Card Paid:</td>
                                    <td class="text-end">Rs. {{ number_format($lubeSummary['purchase']['card_paid'], 2)
                                        }}</td>
                                </tr>
                                <tr>
                                    <td>On Credit/Not Paid:</td>
                                    <td class="text-end text-warning">Rs. {{
                                        number_format($lubeSummary['purchase']['credit'], 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Sale Summary -->
                    <div class="col-md-6">
                        <div class="summary-card">
                            <h6>Lubricant Sale Summary</h6>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td>Total Quantity:</td>
                                    <td class="text-end">{{ number_format($lubeSummary['sale']['total_qty'], 2) }} Units
                                    </td>
                                </tr>
                                <tr>
                                    <td>Total Amount:</td>
                                    <td class="text-end">Rs. {{ number_format($lubeSummary['sale']['total_amount'], 2)
                                        }}</td>
                                </tr>
                                <tr>
                                    <td>Cash Received:</td>
                                    <td class="text-end">Rs. {{ number_format($lubeSummary['sale']['cash_received'], 2)
                                        }}</td>
                                </tr>
                                <tr>
                                    <td>Bank Received:</td>
                                    <td class="text-end">Rs. {{ number_format($lubeSummary['sale']['bank_received'], 2)
                                        }}</td>
                                </tr>
                                <tr>
                                    <td>Card Received:</td>
                                    <td class="text-end">Rs. {{ number_format($lubeSummary['sale']['card_received'], 2)
                                        }}</td>
                                </tr>
                                <tr>
                                    <td>On Credit/Not Paid:</td>
                                    <td class="text-end text-warning">Rs. {{
                                        number_format($lubeSummary['sale']['credit'], 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Detailed Documents - But filter for display -->
                <h6>Document Details (All Documents)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Doc Type</th>
                                <th>Invoice No</th>
                                <th>Date</th>
                                <th>Account</th>
                                <th>Payment Status</th>
                                <th>Payment Method</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Amount</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lubeDocuments as $doc)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $doc->doc_type == 'purchase' ? 'warning' : 'success' }}">
                                        {{ ucfirst($doc->doc_type) }}
                                    </span>
                                </td>
                                <td>{{ $doc->invoice_no }}</td>
                                <td>{{ \Carbon\Carbon::parse($doc->date)->format('M d, Y') }}</td>
                                <td>{{ $doc->account->name ?? 'N/A' }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $doc->payment_status == 'paid' ? 'success' : ($doc->payment_status == 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($doc->payment_status) }}
                                    </span>
                                </td>
                                <td>{{ ucfirst($doc->payment_method) }}</td>
                                <td class="text-end">{{ number_format($doc->lines->sum('qty'), 2) }}</td>
                                <td class="text-end {{ $doc->payment_status == 'paid' ? '' : 'text-warning' }}">
                                    Rs. {{ number_format($doc->lines->sum('line_amount'), 2) }}
                                    @if($doc->payment_status != 'paid')
                                    <br><small class="text-muted">(Not included in financial summary)</small>
                                    @endif
                                </td>
                                <td>{{ $doc->remarks }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        <!-- Oil Purchase Section -->
        @if($oilPurchaseSummary['count'] > 0)
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Fuel Oil Purchase</h5>
            </div>
            <div class="card-body">
                <!-- Oil Purchase Summary -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="summary-card">
                            <h6>Fuel Oil Purchase Summary</h6>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td>Total Quantity:</td>
                                    <td class="text-end">{{ number_format($oilPurchaseSummary['total_qty'], 2) }} Liters
                                    </td>
                                </tr>
                                <tr>
                                    <td>Total Amount:</td>
                                    <td class="text-end text-danger">Rs. {{
                                        number_format($oilPurchaseSummary['total_amount'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Total Purchases:</td>
                                    <td class="text-end">{{ $oilPurchaseSummary['count'] }} records</td>
                                </tr>
                                @if($oilPurchaseSummary['cash_paid'] > 0)
                                <tr>
                                    <td>Cash Paid:</td>
                                    <td class="text-end">Rs. {{ number_format($oilPurchaseSummary['cash_paid'], 2) }}
                                    </td>
                                </tr>
                                @endif
                                @if($oilPurchaseSummary['credit'] > 0)
                                <tr>
                                    <td>On Credit:</td>
                                    <td class="text-end">Rs. {{ number_format($oilPurchaseSummary['credit'], 2) }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Detailed Oil Purchases -->
                <h6>Purchase Details</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice No</th>
                                <th>Product</th>
                                <th>Supplier</th>
                                <th>Receiving Date</th>
                                <th class="text-end">Quantity (L)</th>
                                <th class="text-end">Rate (Rs.)</th>
                                <th class="text-end">Amount (Rs.)</th>
                                <th>Payment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($oilPurchaseSummary['purchases'] as $purchase)
                            <tr>
                                <td>{{ $purchase['invoice_no'] ?? 'N/A' }}</td>
                                <td>{{ $purchase['product_name'] }}</td>
                                <td>{{ $oilPurchases->firstWhere('id', $purchase['id'])->supplier->name ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($purchase['recieving_date'])->format('M d, Y') }}</td>
                                <td class="text-end">{{ number_format($purchase['qty'], 2) }}</td>
                                <td class="text-end">{{ number_format($purchase['rate'], 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($purchase['amount'], 2) }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $purchase['payment_status'] == 'paid' ? 'success' : ($purchase['payment_status'] == 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($purchase['payment_status']) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Shortage Payments Section -->
        @if(isset($shiftShortagePayments) && count($shiftShortagePayments) > 0)
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Shortage Payments</h5>
            </div>
            <div class="card-body">
                <!-- Shortage Summary Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="summary-card p-3 border rounded bg-white text-center">
                            <h4 class="text-warning fw-bold mb-2">{{ number_format($totalShortageLiters, 2) }} <small
                                    class="fs-6">L</small></h4>
                            <p class="mb-0 text-muted">Total Shortage</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card p-3 border rounded bg-white text-center">
                            <h4 class="text-success fw-bold mb-2">Rs. {{ number_format($totalShortagePaid, 2) }}</h4>
                            <p class="mb-0 text-muted">Total Amount Paid</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card p-3 border rounded bg-white text-center">
                            <h4 class="text-primary fw-bold mb-2">Rs. {{ number_format($totalBankShortage, 2) }}</h4>
                            <p class="mb-0 text-muted">Bank Payments</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card p-3 border rounded bg-white text-center">
                            <h4 class="text-info fw-bold mb-2">Rs. {{ number_format($totalCashShortage, 2) }}</h4>
                            <p class="mb-0 text-muted">Cash Payments</p>
                        </div>
                    </div>
                </div>

                <!-- Shortage Payment Details Table -->
                <h6>Payment Details</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Payment Date</th>
                                <th>Supplier</th>
                                <th>Receive Date</th>
                                <th class="text-end">Shortage (L)</th>
                                <th class="text-end">Amount (Rs.)</th>
                                <th>Method</th>
                                <th>Bank</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shiftShortagePayments as $index => $payment)
                            @php
                            $paymentType = $payment->payment_type ?? ($payment->is_paid == 1 ? 'full' : 'partial');
                            $typeBadge = $paymentType == 'full' ? 'success' : 'warning';
                            $methodBadge = ($payment->payment_method ?? 'cash') == 'cash' ? 'success' : 'info';
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('M d, Y H:i') }}</td>
                                <td>{{ $payment->supplier_name ?? 'N/A' }}</td>
                                <td>{{ $payment->recive_date ? \Carbon\Carbon::parse($payment->recive_date)->format('M
                                    d, Y') : 'N/A' }}</td>
                                <td class="text-end text-warning fw-bold">{{ number_format($payment->total_shortage ??
                                    0, 2) }} L</td>
                                <td class="text-end text-success fw-bold">Rs. {{ number_format($payment->total_amount ??
                                    0, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $methodBadge }}">
                                        {{ ucfirst($payment->payment_method ?? 'cash') }}
                                    </span>
                                </td>
                                <td>
                                    @if(($payment->payment_method ?? '') == 'bank' && $payment->bank_name)
                                    <small>{{ $payment->bank_name }}</small>
                                    @if($payment->account_number)
                                    <br><small class="text-muted">({{ $payment->account_number }})</small>
                                    @endif
                                    @else
                                    -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-warning">
                            <tr>
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>{{ number_format($totalShortageLiters, 2) }} L</strong>
                                </td>
                                <td class="text-end"><strong>Rs. {{ number_format($totalShortagePaid, 2) }}</strong>
                                </td>

                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- General Transactions Section -->
        @if($transactions->count() > 0)
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Other Financial Transactions</h5>
            </div>
            <div class="card-body">
                <!-- Transaction Summary -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="summary-card">
                            <h6>Income Summary</h6>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td>Total Income:</td>
                                    <td class="text-end text-success">Rs. {{
                                        number_format($financialSummary['transaction_summary']['income']['total'], 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Cash Received:</td>
                                    <td class="text-end">Rs. {{
                                        number_format($financialSummary['transaction_summary']['income']['cash'], 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Bank Received:</td>
                                    <td class="text-end">Rs. {{
                                        number_format($financialSummary['transaction_summary']['income']['bank'], 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Card Received:</td>
                                    <td class="text-end">Rs. {{
                                        number_format($financialSummary['transaction_summary']['income']['card'], 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>On Credit:</td>
                                    <td class="text-end">Rs. {{
                                        number_format($financialSummary['transaction_summary']['income']['credit'], 2)
                                        }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Expense Summary -->
                    <div class="col-md-6">
                        <div class="summary-card">
                            <h6>Expense Summary</h6>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td>Total Expense:</td>
                                    <td class="text-end text-danger">Rs. {{
                                        number_format($financialSummary['transaction_summary']['expense']['total'], 2)
                                        }}</td>
                                </tr>
                                <tr>
                                    <td>Cash Paid:</td>
                                    <td class="text-end">Rs. {{
                                        number_format($financialSummary['transaction_summary']['expense']['cash'], 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Bank Paid:</td>
                                    <td class="text-end">Rs. {{
                                        number_format($financialSummary['transaction_summary']['expense']['bank'], 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Card Paid:</td>
                                    <td class="text-end">Rs. {{
                                        number_format($financialSummary['transaction_summary']['expense']['card'], 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>On Credit:</td>
                                    <td class="text-end">Rs. {{
                                        number_format($financialSummary['transaction_summary']['expense']['credit'], 2)
                                        }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Detailed Transactions -->
                <h6>Transaction Details</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Account</th>
                                <th>To Account</th>
                                <th>Payment Method</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th>Note</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $transaction->type == 'income' ? 'success' : 'danger' }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>{{ $transaction->account->name ?? 'N/A' }}</td>
                                <td>{{ $transaction->toAccount->name ?? 'N/A' }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $transaction->method == 'cash' ? 'success' : ($transaction->method == 'bank' ? 'info' : 'warning') }}">
                                        {{ ucfirst($transaction->method) }}
                                    </span>
                                </td>
                                <td class="text-end {{ $transaction->type == 'expense' ? 'text-danger' : '' }}">
                                    @if($transaction->type == 'expense')
                                    Rs. {{ number_format($transaction->debit, 2) }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="text-end {{ $transaction->type == 'income' ? 'text-success' : '' }}">
                                    @if($transaction->type == 'income')
                                    Rs. {{ number_format($transaction->credit, 2) }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>{{ $transaction->note }}</td>
                                <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="4" class="text-end"><strong>Net Transactions:</strong></td>
                                <td class="text-end"><strong>Rs. {{
                                        number_format($financialSummary['transaction_summary']['expense']['total'], 2)
                                        }}</strong></td>
                                <td class="text-end"><strong>Rs. {{
                                        number_format($financialSummary['transaction_summary']['income']['total'], 2)
                                        }}</strong></td>
                                <td colspan="2"
                                    class="{{ $financialSummary['transaction_net'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    <strong>Net: Rs. {{ number_format($financialSummary['transaction_net'], 2)
                                        }}</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="mt-5 pt-3 border-top text-center">
            <p class="text-muted small">
                Generated by Pump360 • {{ now()->format('M d, Y \a\t H:i') }}
            </p>
        </div>

        @else
        <!-- INDEX VIEW (Simple Form) -->
        <div class="card">
            <div class="card-header bg-light">
                <h4 class="mb-0">Shift Report Generator</h4>
            </div>
            <div class="card-body">
                <form action="/shift-reports/generate" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Station</label>
                            <select class="form-select" name="station_id" id="station_id" required>
                                <option value="">Choose Station</option>
                                @foreach($stations as $station)
                                <option value="{{ $station->id }}">{{ $station->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Select Shift</label>
                            <select class="form-select" name="shift_id" id="shift_id" required>
                                <option value="">Choose Shift</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">From Date & Time</label>
                            <input type="datetime-local" class="form-control" name="from_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">To Date & Time</label>
                            <input type="datetime-local" class="form-control" name="to_date" required>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Generate Professional Report</button>
                        <a href="/shifts" class="btn btn-outline-secondary">Back to Shifts</a>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    @if(!isset($shift))
    <script>
        // Load shifts when station changes
        document.getElementById('station_id').addEventListener('change', function () {
            const stationId = this.value;
            const shiftSelect = document.getElementById('shift_id');

            shiftSelect.innerHTML = '<option value="">Choose Shift</option>';

            if (stationId) {
                fetch(`/api/shifts-by-station/${stationId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(shift => {
                            const option = document.createElement('option');
                            option.value = shift.id;
                            option.textContent = `Shift ${shift.shift_no} (${shift.start_time})`;
                            shiftSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    </script>
    @endif
</body>

</html>