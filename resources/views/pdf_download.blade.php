<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Stock Report - {{ $shift->station->name ?? 'Fuel Station' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: white;
            padding: 10px;
        }

        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
        }

        .company-header {
            border-bottom: 2px solid #1e466e;
            padding-bottom: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .company-header h2 {
            color: #1e466e;
            font-size: 24px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .company-header h4 {
            font-size: 16px;
            color: #555;
            margin-bottom: 5px;
        }

        .company-header p {
            font-size: 12px;
            color: #777;
        }

        .card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            margin-bottom: 15px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 15px;
            font-weight: 700;
            font-size: 14px;
        }

        .card-body {
            padding: 15px;
        }

        .summary-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            background: #ffffff;
        }

        .summary-card h6 {
            font-size: 12px;
            margin-bottom: 8px;
            font-weight: 700;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 5px;
        }

        .summary-card h4 {
            font-size: 18px;
            margin: 8px 0;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th,
        td {
            border: 1px solid #dee2e6;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 700;
            font-size: 10px;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .variance-positive {
            color: #28a745;
            font-weight: bold;
        }

        .variance-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 600;
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

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -8px;
        }

        .col-md-2,
        .col-md-3,
        .col-md-4,
        .col-md-6 {
            padding: 0 8px;
        }

        .col-md-2 {
            width: 16.666%;
        }

        .col-md-3 {
            width: 25%;
        }

        .col-md-4 {
            width: 33.333%;
        }

        .col-md-6 {
            width: 50%;
        }

        .mt-3 {
            margin-top: 12px;
        }

        .mt-4 {
            margin-top: 16px;
        }

        footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            color: #777;
        }

        .tank-heading {
            margin: 16px 0 10px 0;
            font-size: 16px;
            font-weight: 700;
            color: #1e466e;
            border-left: 3px solid #1e466e;
            padding-left: 10px;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .report-container {
                padding: 0.15in;
            }

            .card {
                break-inside: avoid;
            }

            @page {
                size: A4;
                margin: 0.2in;
            }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-spinner {
            background: white;
            padding: 25px 35px;
            border-radius: 12px;
            text-align: center;
        }

        .loading-spinner .spinner {
            width: 45px;
            height: 45px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #1e466e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 12px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="report-container" id="pdfContent">
        @php
            $pdfFileName = "shift_stock_report_SHIFT-{$shift->id}_" . date('Y-m-d', strtotime($shift->start_time)) . "_to_" . date('Y-m-d', strtotime($shift->end_time));
            // Fetch shortage payments
            $shiftShortagePayments = DB::select('
                            SELECT sapb.id, sapb.shift_id, sapb.total_shortage, sapb.total_amount, sapb.created_at, sapb.payment_type,
                            a.name as supplier_name, ort.recive_date,
                            CASE WHEN sapb.account_id IS NOT NULL THEN "bank" ELSE "cash" END as payment_method,
                            acc.name as bank_name
                            FROM shortage_ammount_paid_back sapb
                            LEFT JOIN oil_purchase op ON sapb.oil_purchase_id = op.id
                            LEFT JOIN accounts a ON op.supplier_id = a.id
                            LEFT JOIN oil_recived_tanks ort ON sapb.oil_recived_id = ort.id
                            LEFT JOIN accounts acc ON sapb.account_id = acc.id
                            WHERE sapb.shift_id = ' . ($shift->id ?? 0) . ' AND sapb.oil_recived_id IS NOT NULL
                            ORDER BY sapb.created_at DESC
                        ');
            $totalShortagePaid = 0;
            $totalShortageLiters = 0;
            $totalCashShortage = 0;
            $totalBankShortage = 0;
            foreach ($shiftShortagePayments as $payment) {
                $totalShortagePaid += floatval($payment->total_amount ?? 0);
                $totalShortageLiters += floatval($payment->total_shortage ?? 0);
                if (($payment->payment_method ?? '') == 'cash') {
                    $totalCashShortage += floatval($payment->total_amount ?? 0);
                } else {
                    $totalBankShortage += floatval($payment->total_amount ?? 0);
                }
            }
        @endphp

        <!-- Header -->
        <div class="company-header">
            <h2>Shift Stock Reconciliation Report</h2>
            <h4>{{ $shift->station->name ?? 'N/A' }}</h4>
            <p>Professional Fuel Management System</p>
            <p style="margin-top: 5px;">Report ID: SHIFT-{{ $shift->id }} | Generated: {{ now()->format('M d, Y H:i') }}
            </p>
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
                    <div class="col-md-2"><strong>Status:</strong><br><span class="badge bg-secondary">Closed</span>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6"><strong>Start
                            Time:</strong><br>{{ date('M d, Y H:i', strtotime($shift->start_time)) }}</div>
                    <div class="col-md-6"><strong>End
                            Time:</strong><br>{{ $shift->end_time ? date('M d, Y H:i', strtotime($shift->end_time)) : 'Not Ended' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Stock Analysis (with Variance %) -->
        @if(count($tankCalculations) > 0)
            <div class="card">
                <div class="card-header">Professional Stock Analysis</div>
                <div class="card-body" style="padding: 0;">
                    <table style="width:100%">
                        <thead>
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
                            @foreach($tankCalculations as $calc)
                                <tr>
                                    <td>{{ $calc['tank_name'] }}</td>
                                    <td>{{ $calc['product_name'] }}</td>
                                    <td class="text-end">{{ number_format($calc['opening_stock'], 2) }}</td>
                                    <td class="text-end">{{ number_format($calc['closing_stock'], 2) }}</td>
                                    <td class="text-end">{{ number_format($calc['oil_purchased'], 2) }}</td>
                                    <td class="text-end">{{ number_format($calc['total_nozzle_sales'], 2) }}</td>
                                    <td
                                        class="text-end {{ $calc['variance'] > 0 ? 'variance-positive' : ($calc['variance'] < 0 ? 'variance-negative' : '') }}">
                                        {{ $calc['variance'] > 0 ? '+' : '' }}{{ number_format($calc['variance'], 2) }}
                                    </td>
                                    <td class="text-end">
                                        @if($calc['total_nozzle_sales'] > 0)
                                            {{ $calc['variance_percent'] > 0 ? '+' : '' }}{{ number_format($calc['variance_percent'], 2) }}%
                                        @else N/A @endif
                                    </td>
                                    <td><span
                                            class="badge bg-{{ $calc['status'] == 'Normal' ? 'success' : ($calc['status'] == 'Warning' ? 'warning' : 'danger') }}">{{ $calc['status'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tank-wise Detailed Analysis -->
            <h5 class="tank-heading">Tank-wise Detailed Analysis</h5>
            @foreach($tankCalculations as $calc)
                <div class="card">
                    <div class="card-header">{{ $calc['tank_name'] }} - {{ $calc['product_name'] }}</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="summary-card">
                                    <h6>Stock Movement (Liters)</h6>
                                    <table style="width:100%">
                                        <tr>
                                            <td width="55%">Opening Stock:</td>
                                            <td class="text-end">{{ number_format($calc['opening_stock'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td width="55%">Closing Stock:</td>
                                            <td class="text-end">{{ number_format($calc['closing_stock'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td width="55%">Physical Usage:</td>
                                            <td class="text-end">
                                                {{ $calc['physical_usage'] > 0 ? '+' : '' }}{{ number_format($calc['physical_usage'], 2) }}
                                            </td>
                                        </tr>
                                        @if($calc['oil_purchased'] > 0)
                                            <tr>
                                                <td>Oil Purchased:</td>
                                                <td class="text-end text-success">+{{ number_format($calc['oil_purchased'], 2) }}
                                                </td>
                                        </tr>@endif
                                        <tr>
                                            <td><strong>Adjusted Usage:</strong></td>
                                            <td class="text-end">
                                                <strong>{{ $calc['adjusted_physical_usage'] > 0 ? '+' : '' }}{{ number_format($calc['adjusted_physical_usage'], 2) }}</strong>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="summary-card">
                                    <h6>Sales & Variance Analysis</h6>
                                    <table style="width:100%">
                                        <tr>
                                            <td width="55%">Nozzle Sales (Liters):</td>
                                            <td class="text-end">{{ number_format($calc['nozzle_sales_liters'], 2) }} L
                                        </tr>
                                        <tr>
                                            <td>Reset Sales (Liters):</td>
                                            <td class="text-end">{{ number_format($calc['reset_sales_liters'], 2) }} L
                                        </tr>
                                        <tr>
                                            <td><strong>Total Nozzle Sales:</strong></td>
                                            <td class="text-end"><strong>{{ number_format($calc['total_nozzle_sales'], 2) }}
                                                    L</strong>
                                        </tr>
                                        <tr>
                                            <td><strong>Variance Analysis:</strong></td>
                                            <td class="text-end">
                                                <strong
                                                    class="{{ $calc['variance'] > 0 ? 'variance-positive' : ($calc['variance'] < 0 ? 'variance-negative' : '') }}">
                                                    {{ $calc['variance'] > 0 ? '+' : '' }}{{ number_format($calc['variance'], 2) }}
                                                    L
                                                    @if($calc['total_nozzle_sales'] > 0)
                                                    ({{ number_format($calc['variance_percent'], 2) }}%) @endif
                                                </strong><br>
                                                <small>{{ $calc['variance_text'] }}</small><br>
                                                <span
                                                    class="badge bg-{{ $calc['gain_loss'] == 'GAIN' ? 'success' : ($calc['gain_loss'] == 'LOSS' ? 'danger' : 'secondary') }}">{{ $calc['gain_loss'] }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Nozzle Transactions -->
                        @php $filteredReadings = isset($nozzleReadings) ? $nozzleReadings->filter(fn($r) => $r->nozzle && $r->nozzle->tank_id == ($calc['tank_id'] ?? 0)) : collect(); @endphp
                        @if(count($filteredReadings) > 0)
                            <div style="margin-top: 12px;">
                                <h6>Nozzle Transactions</h6>
                                <table style="width:100%">
                                    <thead>
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
                                        @foreach($filteredReadings as $reading)
                                            <tr>
                                                <td>{{ $reading->nozzle->name ?? 'N/A' }}</td>
                                                <td>{{ $reading->nozzle->dispenser->name ?? 'N/A' }}</td>
                                                <td class="text-end">{{ number_format($reading->opening_reading, 2) }}</td>
                                                <td class="text-end">{{ number_format($reading->closing_reading, 2) }}</td>
                                                <td class="text-end">{{ number_format($reading->total_dispensed, 2) }}</td>
                                                <td class="text-end">{{ number_format($reading->rate, 2) }}</td>
                                                <td class="text-end">Rs. {{ number_format($reading->total_amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <!-- Nozzle Reset Records -->
                        @php $filteredResets = isset($nozzleResets) ? $nozzleResets->filter(fn($r) => $r->nozzle && $r->nozzle->tank_id == ($calc['tank_id'] ?? 0)) : collect(); @endphp
                        @if(count($filteredResets) > 0)
                            <div style="margin-top: 12px;">
                                <h6>Nozzle Reset Records</h6>
                                <table style="width:100%">
                                    <thead>
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
                                        @foreach($filteredResets as $reset)
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
                    <div class="row">
                        <div class="col-md-4">
                            <div class="summary-card text-center">
                                <h6>Total Revenue</h6>
                                <h4 style="color:#28a745;">Rs.
                                    {{ number_format($financialSummary['total_revenue'] ?? 0, 2) }}</h4><small>Fuel: Rs.
                                    {{ number_format($financialSummary['fuel_sales'] ?? 0, 2) }}</small><br><small>Lube: Rs.
                                    {{ number_format($financialSummary['lube_sales'] ?? 0, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card text-center">
                                <h6>Total Expenses</h6>
                                <h4 style="color:#dc3545;">Rs.
                                    {{ number_format($financialSummary['total_expenses'] ?? 0, 2) }}</h4><small>Oil
                                    Purchase: Rs.
                                    {{ number_format($financialSummary['oil_purchase'] ?? 0, 2) }}</small><br><small>Lube
                                    Purchase: Rs. {{ number_format($financialSummary['lube_purchase'] ?? 0, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card text-center">
                                <h6>Net Income</h6>
                                <h4
                                    style="color:{{ ($financialSummary['net_income'] ?? 0) >= 0 ? '#28a745' : '#dc3545' }};">
                                    Rs. {{ number_format($financialSummary['net_income'] ?? 0, 2) }}</h4><small>Fuel Card:
                                    Rs. {{ number_format($cashFlow->fuelcard ?? 0, 2) }}</small><br><small>Credit Card: Rs.
                                    {{ number_format($cashFlow->creditcard ?? 0, 2) }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="summary-card text-center">
                                <h6>Opening Balance</h6><strong>Rs.
                                    {{ number_format($financialSummary['cash_handover'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card text-center">
                                <h6>Closing Balance</h6><strong>Rs.
                                    {{ number_format($financialSummary['cash_in_hand'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card text-center">
                                <h6>Cash in Bank</h6><strong>Rs.
                                    {{ number_format($financialSummary['cash_in_bank'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card text-center">
                                <h6>Total Cash Balance</h6><strong>Rs.
                                    {{ number_format($financialSummary['total_cash_balance'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Lubricant Transactions Section -->
        @if(isset($lubeDocuments) && $lubeDocuments->count() > 0)
            <div class="card">
                <div class="card-header">Lubricant Transactions</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="summary-card">
                                <h6>Lubricant Purchase Summary</h6>
                                <table style="width:100%">
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
                                <table style="width:100%">
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

        <!-- Fuel Oil Purchase Section -->
        @if(isset($oilPurchaseSummary) && $oilPurchaseSummary['count'] > 0)
            <div class="card">
                <div class="card-header">Fuel Oil Purchase</div>
                <div class="card-body">
                    <div class="summary-card">
                        <h6>Fuel Oil Purchase Summary</h6>
                        <table style="width:100%">
                            <tr>
                                <td>Total Quantity:</td>
                                <td class="text-end">{{ number_format($oilPurchaseSummary['total_qty'], 2) }} Liters</td>
                            </tr>
                            <tr>
                                <td>Total Amount:</td>
                                <td class="text-end text-danger">Rs.
                                    {{ number_format($oilPurchaseSummary['total_amount'], 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Shortage Payments Section -->
        @if(count($shiftShortagePayments) > 0)
            <div class="card">
                <div class="card-header">Shortage Payments</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="summary-card text-center">
                                <h6>Total Shortage</h6><strong>{{ number_format($totalShortageLiters, 2) }} L</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card text-center">
                                <h6>Total Amount Paid</h6><strong>Rs. {{ number_format($totalShortagePaid, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card text-center">
                                <h6>Bank Payments</h6><strong>Rs. {{ number_format($totalBankShortage, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card text-center">
                                <h6>Cash Payments</h6><strong>Rs. {{ number_format($totalCashShortage, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Other Financial Transactions -->
        @if(isset($transactions) && $transactions->count() > 0)
            <div class="card">
                <div class="card-header">Other Financial Transactions</div>
                <div class="card-body">
                    <table style="width:100%">
                        <thead>
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

        <footer>Generated by Pump360 • {{ now()->format('M d, Y \a\t H:i') }}</footer>
    </div>

    <div id="loadingOverlay" class="loading-overlay" style="display: flex;">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Generating PDF, please wait...</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        setTimeout(function () {
            var element = document.getElementById('pdfContent');
            var fileName = '{{ $pdfFileName }}';
            var loading = document.getElementById('loadingOverlay');
            html2canvas(element, { scale: 1.3, backgroundColor: '#ffffff', logging: false, useCORS: true }).then(function (canvas) {
                var imgData = canvas.toDataURL('image/jpeg', 0.75);
                var { jsPDF } = window.jspdf;
                var imgWidth = 210;
                var imgHeight = (canvas.height * imgWidth) / canvas.width;
                var pdf = new jsPDF('p', 'mm', 'a4');
                pdf.addImage(imgData, 'JPEG', 0, 0, imgWidth, imgHeight, undefined, 'FAST');
                pdf.save(fileName + '.pdf');
                loading.style.display = 'none';
                setTimeout(function () { window.close(); }, 1000);
            }).catch(function (error) {
                loading.style.display = 'none';
                alert('PDF generation failed. Please try again.');
            });
        }, 500);
    </script>
</body>

</html>