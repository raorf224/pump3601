<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Stock Report - {{ $shift->station->name ?? 'Fuel Station' }}</title>
    <style>
        /* ========== PRINT STYLES ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            background: white;
        }

        .report-container {
            max-width: 100%;
            padding: 8px;
        }

        /* Company Header */
        .company-header {
            border-bottom: 2px solid #1e466e;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .company-header h2 {
            font-size: 16px;
            margin-bottom: 3px;
            color: #1e466e;
        }

        .company-header h4 {
            font-size: 12px;
            margin-bottom: 2px;
            color: #666;
        }

        /* Cards */
        .card {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 10px;
            overflow: hidden;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #dee2e6;
            padding: 6px 10px;
            font-weight: 600;
            font-size: 11px;
        }

        .card-body {
            padding: 8px 10px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-bordered {
            border: 1px solid #dee2e6;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 4px 6px;
        }

        .table-sm th,
        .table-sm td {
            padding: 3px 5px;
        }

        .table-light th {
            background-color: #f8f9fc;
            font-weight: 600;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-start {
            text-align: left;
        }

        /* Summary Cards */
        .summary-card {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 6px;
            margin-bottom: 6px;
            background: #ffffff;
        }

        .summary-card h6 {
            font-size: 10px;
            margin-bottom: 4px;
            font-weight: 600;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 8px;
            font-weight: 600;
            border-radius: 3px;
        }

        .bg-success {
            background-color: #28a745;
            color: white;
        }

        .bg-danger {
            background-color: #dc3545;
            color: white;
        }

        .bg-warning {
            background-color: #ffc107;
            color: #333;
        }

        .bg-secondary {
            background-color: #6c757d;
            color: white;
        }

        .bg-info {
            background-color: #17a2b8;
            color: white;
        }

        .bg-primary {
            background-color: #1e466e;
            color: white;
        }

        .variance-positive {
            color: #28a745;
            font-weight: bold;
        }

        .variance-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-warning {
            color: #ffc107;
        }

        .text-info {
            color: #17a2b8;
        }

        .text-muted {
            color: #6c757d;
        }

        .fw-bold {
            font-weight: 700;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mb-1 {
            margin-bottom: 4px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-3 {
            margin-bottom: 12px;
        }

        .mt-2 {
            margin-top: 8px;
        }

        .mt-3 {
            margin-top: 12px;
        }

        .border-top {
            border-top: 1px solid #dee2e6;
        }

        .row {
            display: table;
            width: 100%;
        }

        .col-md-2,
        .col-md-3,
        .col-md-4,
        .col-md-6,
        .col-4,
        .col-8 {
            display: table-cell;
            vertical-align: top;
            padding: 0 5px;
        }

        .col-md-2 {
            width: 16.66%;
        }

        .col-md-3 {
            width: 25%;
        }

        .col-md-4 {
            width: 33.33%;
        }

        .col-md-6 {
            width: 50%;
        }

        .col-4 {
            width: 33.33%;
        }

        .col-8 {
            width: 66.66%;
        }

        .text-end {
            text-align: right;
        }

        /* Page break */
        .page-break {
            page-break-before: always;
        }

        @page {
            size: A4 landscape;
            margin: 0.5cm;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .report-container {
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="report-container">
        @php
            $totalSalesLitres = $nozzleReadings->sum('total_dispensed') + $nozzleResets->sum('total_dispensed');
            $pdfFileName = "shift_stock_report_SHIFT-{$shift->id}_" . date('Y-m-d', strtotime($shift->start_time)) . "_to_" . date('Y-m-d', strtotime($shift->end_time ?? $shift->start_time));
        @endphp

        <!-- Header -->
        <div class="company-header">
            <div class="row">
                <div class="col-8">
                    <h2>Shift Stock Reconciliation Report</h2>
                    <h4>{{ $shift->station->name ?? 'N/A' }}</h4>
                    <p class="text-muted mb-0">Professional Fuel Management System</p>
                </div>
                <div class="col-4 text-end">
                    <p class="mb-0"><strong>Report ID:</strong> SHIFT-{{ $shift->id }}</p>
                    <p class="mb-0"><strong>Generated:</strong> {{ now()->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Shift Overview -->
        <div class="card">
            <div class="card-header">Shift Overview</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><strong>Station:</strong><br>{{ $shift->station->name ?? 'N/A' }}</div>
                    <div class="col-md-2"><strong>Shift
                            Type:</strong><br>{{ $shift->shift_no == 1 ? 'Day Shift' : 'Night Shift' }}</div>
                    <div class="col-md-3"><strong>Shift
                            Incharge:</strong><br>{{ $shift->shiftIncharger->user->full_name ?? 'N/A' }}</div>
                    <div class="col-md-2"><strong>Opening Balance:</strong><br>Rs.
                        {{ number_format($shift->cash_handover ?? 0, 2) }}</div>
                    <div class="col-md-2"><strong>Status:</strong><br><span
                            class="badge bg-secondary">{{ ucfirst($shift->status ?? 'Closed') }}</span></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6"><strong>Start
                            Time:</strong><br>{{ \Carbon\Carbon::parse($shift->start_time)->format('M d, Y H:i') }}
                    </div>
                    <div class="col-md-6"><strong>End
                            Time:</strong><br>{{ $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('M d, Y H:i') : 'Not Ended' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Stock Analysis -->
        @if(count($tankCalculations) > 0)
            <div class="card">
                <div class="card-header">Professional Stock Analysis</div>
                <div class="card-body p-0">
                    <table class="table-bordered table-sm" style="width: 100%;">
                        <thead class="table-light">
                            <tr>
                                <th>Tank</th>
                                <th>Product</th>
                                <th class="text-end">Opening (L)</th>
                                <th class="text-end">Closing (L)</th>
                                <th class="text-end">Oil Received (L)</th>
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
                                    <td class="text-end">{{ number_format($calculation['oil_purchased'], 2) }}</td>
                                    <td class="text-end">{{ number_format($calculation['total_nozzle_sales'], 2) }}</td>
                                    <td
                                        class="text-end {{ $calculation['variance'] > 0 ? 'variance-positive' : ($calculation['variance'] < 0 ? 'variance-negative' : '') }}">
                                        @if($calculation['variance'] > 0)+{{ number_format($calculation['variance'], 2) }}@elseif($calculation['variance'] < 0){{ number_format($calculation['variance'], 2) }}@else
                                        0.00 @endif
                                    </td>
                                    <td class="text-end">
                                        @if($calculation['total_nozzle_sales'] > 0)
                                            @if($calculation['variance_percent'] > 0)+{{ number_format($calculation['variance_percent'], 2) }}%@elseif($calculation['variance_percent'] < 0){{ number_format($calculation['variance_percent'], 2) }}%@else
                                            0.00% @endif
                                        @else N/A @endif
                                    </td>
                                    <td><span
                                            class="badge bg-{{ $calculation['status_class'] }}">{{ $calculation['status'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tank-wise Detailed Analysis -->
            <h5 class="mb-2" style="font-size: 11px; margin-top: 10px;">Tank-wise Detailed Analysis</h5>
            @foreach($tankCalculations as $tankId => $calculation)
                <div class="card">
                    <div class="card-header">{{ $calculation['tank_name'] }} - {{ $calculation['product_name'] }}</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="summary-card">
                                    <h6>Stock Movement (Liters)</h6>
                                    <table class="table-sm table-bordered" style="width: 100%;">
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
                                            <td class="text-end">{{ number_format($calculation['physical_usage'], 2) }}</td>
                                        </tr>
                                        @if($calculation['oil_purchased'] > 0)
                                            <tr>
                                                <td>Oil Purchased:</td>
                                                <td class="text-end text-info">
                                                    +{{ number_format($calculation['oil_purchased'], 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Adjusted Usage:</strong></td>
                                            <td class="text-end">
                                                <strong>{{ number_format($calculation['adjusted_physical_usage'], 2) }}</strong>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="summary-card">
                                    <h6>Sales & Variance Analysis</h6>
                                    <table class="table-sm table-bordered" style="width: 100%;">
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
                                            <td class="text-end">
                                                <strong>{{ number_format($calculation['total_nozzle_sales'], 2) }} L</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Variance Analysis:</strong></td>
                                            <td class="text-end">
                                                <strong
                                                    class="{{ $calculation['variance'] > 0 ? 'variance-positive' : ($calculation['variance'] < 0 ? 'variance-negative' : '') }}">
                                                    @if($calculation['variance'] > 0)+{{ number_format($calculation['variance'], 2) }}
                                                        L
                                                    @elseif($calculation['variance'] < 0){{ number_format($calculation['variance'], 2) }}
                                                        L
                                                    @else 0.00 L @endif
                                                </strong>
                                                <br><small class="text-muted">{{ $calculation['variance_text'] }}</small>
                                                <br><span
                                                    class="badge bg-{{ $calculation['gain_loss_class'] }}">{{ $calculation['gain_loss'] }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Nozzle Transactions -->
                        @if(isset($nozzleReadings) && count($nozzleReadings->where('nozzle.tank_id', $tankId)) > 0)
                            <div class="mt-2">
                                <h6 style="font-size: 10px; margin: 8px 0 4px 0;">Nozzle Transactions</h6>
                                <table class="table-sm table-bordered" style="width: 100%;">
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
                        @endif

                        <!-- Nozzle Reset Records -->
                        @if(isset($nozzleResets) && count($nozzleResets->where('nozzle.tank_id', $tankId)) > 0)
                            <div class="mt-2">
                                <h6 style="font-size: 10px; margin: 8px 0 4px 0;">Nozzle Reset Records</h6>
                                <table class="table-sm table-bordered" style="width: 100%;">
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
                        @endif
                    </div>
                </div>
            @endforeach
        @endif

        <!-- Complete Financial Summary -->
        @if(isset($financialSummary))
            <div class="card">
                <div class="card-header">Complete Financial Summary</div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="summary-card">
                                <h6>Total Revenue</h6>
                                <strong class="text-success">Rs.
                                    {{ number_format($financialSummary['total_revenue'] ?? 0, 2) }}</strong>
                                <br><small>Fuel: Rs. {{ number_format($financialSummary['fuel_sales'] ?? 0, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card">
                                <h6>Total Expenses</h6>
                                <strong class="text-danger">Rs.
                                    {{ number_format($financialSummary['total_expenses'] ?? 0, 2) }}</strong>
                                <br><small>Oil Purchase: Rs.
                                    {{ number_format($financialSummary['oil_purchase'] ?? 0, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card">
                                <h6>Net Income</h6>
                                <strong class="text-success">Rs.
                                    {{ number_format($financialSummary['net_income'] ?? 0, 2) }}</strong>
                                <br><small>Fuel Card: Rs. {{ number_format($cashFlow->fuelcard ?? 0, 2) }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center mt-2">
                        <div class="col-md-3">
                            <div class="summary-card">
                                <h6>Opening Balance</h6>
                                <strong>Rs. {{ number_format($financialSummary['cash_handover'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <h6>Closing Balance</h6>
                                <strong>Rs. {{ number_format($financialSummary['cash_in_hand'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <h6>Cash in Bank</h6>
                                <strong>Rs. {{ number_format($financialSummary['cash_in_bank'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <h6>Total Cash Balance</h6>
                                <strong>Rs. {{ number_format($financialSummary['total_cash_balance'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Lubricant Transactions -->
        @if(isset($lubeDocuments) && $lubeDocuments->count() > 0)
            <div class="card">
                <div class="card-header">Lubricant Transactions</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="summary-card">
                                <h6>Lubricant Purchase Summary</h6>
                                <table class="table-sm" style="width: 100%;">
                                    <tr>
                                        <td>Total Quantity:</td>
                                        <td class="text-end">{{ number_format($lubeSummary['purchase']['total_qty'], 2) }}
                                            Units</td>
                                    </tr>
                                    <tr>
                                        <td>Total Amount:</td>
                                        <td class="text-end">Rs.
                                            {{ number_format($lubeSummary['purchase']['total_amount'], 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="summary-card">
                                <h6>Lubricant Sale Summary</h6>
                                <table class="table-sm" style="width: 100%;">
                                    <tr>
                                        <td>Total Quantity:</td>
                                        <td class="text-end">{{ number_format($lubeSummary['sale']['total_qty'], 2) }} Units
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Total Amount:</td>
                                        <td class="text-end">Rs.
                                            {{ number_format($lubeSummary['sale']['total_amount'], 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Fuel Oil Purchase -->
        @if(isset($oilPurchaseSummary) && $oilPurchaseSummary['count'] > 0)
            <div class="card">
                <div class="card-header">Fuel Oil Purchase</div>
                <div class="card-body">
                    <div class="summary-card">
                        <h6>Fuel Oil Purchase Summary</h6>
                        <table class="table-sm" style="width: 100%;">
                            <tr>
                                <td>Total Quantity:</td>
                                <td class="text-end">{{ number_format($oilPurchaseSummary['total_qty'], 2) }} Liters</td>
                            </tr>
                            <tr>
                                <td>Total Amount:</td>
                                <td class="text-end">Rs. {{ number_format($oilPurchaseSummary['total_amount'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>Total Purchases:</td>
                                <td class="text-end">{{ $oilPurchaseSummary['count'] }} records</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Other Financial Transactions -->
        @if(isset($transactions) && $transactions->count() > 0)
            <div class="card">
                <div class="card-header">Other Financial Transactions</div>
                <div class="card-body">
                    <table class="table-sm table-bordered" style="width: 100%;">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Account</th>
                                <th>To Account</th>
                                <th>Method</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr>
                                    <td><span
                                            class="badge bg-{{ $transaction->type == 'income' ? 'success' : 'danger' }}">{{ ucfirst($transaction->type) }}</span>
                                    </td>
                                    <td>{{ $transaction->account->name ?? 'N/A' }}</td>
                                    <td>{{ $transaction->toAccount->name ?? 'N/A' }}</td>
                                    <td><span
                                            class="badge bg-{{ $transaction->method == 'cash' ? 'success' : ($transaction->method == 'bank' ? 'info' : 'warning') }}">{{ ucfirst($transaction->method) }}</span>
                                    </td>
                                    <td class="text-end">
                                        {{ $transaction->type == 'expense' ? 'Rs. ' . number_format($transaction->debit, 2) : '-' }}
                                    </td>
                                    <td class="text-end">
                                        {{ $transaction->type == 'income' ? 'Rs. ' . number_format($transaction->credit, 2) : '-' }}
                                    </td>
                                    <td>{{ $transaction->note }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="mt-3 pt-2 border-top text-center">
            <p class="text-muted" style="font-size: 8px;">Generated by Pump360 • {{ now()->format('M d, Y \a\t H:i') }}
            </p>
        </div>
    </div>
</body>

</html>