<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Shift Stock Report - {{ $shift->station->name ?? 'Fuel Station' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            padding: 15px;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }

        /* Header */
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
            text-align: center;
        }

        .header h2 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .header h4 {
            font-size: 14px;
            color: #555;
            margin-bottom: 3px;
        }

        .header p {
            font-size: 10px;
            color: #777;
        }

        /* Cards */
        .card {
            border: 1px solid #dee2e6;
            margin-bottom: 12px;
        }

        .card-header {
            background: #f8f9fc;
            border-bottom: 1px solid #dee2e6;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 12px;
        }

        .card-body {
            padding: 10px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        th,
        td {
            border: 1px solid #dee2e6;
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Summary cards */
        .summary-card {
            border: 1px solid #e9ecef;
            padding: 8px;
            margin-bottom: 8px;
            background: #fff;
        }

        .summary-card h6 {
            font-size: 11px;
            margin-bottom: 5px;
            font-weight: bold;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 3px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }

        .bg-success {
            background: #28a745;
            color: white;
        }

        .bg-danger {
            background: #dc3545;
            color: white;
        }

        .bg-warning {
            background: #ffc107;
            color: #333;
        }

        .bg-secondary {
            background: #6c757d;
            color: white;
        }

        /* Layout */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -5px;
        }

        .col-md-2,
        .col-md-3,
        .col-md-4,
        .col-md-6 {
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

        .mt-2 {
            margin-top: 8px;
        }

        .mt-3 {
            margin-top: 12px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            font-size: 8px;
            color: #777;
        }

        .variance-positive {
            color: #28a745;
            font-weight: bold;
        }

        .variance-negative {
            color: #dc3545;
            font-weight: bold;
        }

        @page {
            size: A4 landscape;
            margin: 0.5cm;
        }
    </style>
</head>

<body>
    <div class="container">
        @php
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
        <div class="header">
            <h2>Shift Stock Reconciliation Report</h2>
            <h4>{{ $shift->station->name ?? 'N/A' }}</h4>
            <p>Professional Fuel Management System</p>
            <p>Report ID: SHIFT-{{ $shift->id }} | Generated: {{ now()->format('M d, Y H:i') }}</p>
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
                <div class="row mt-2">
                    <div class="col-md-6"><strong>Start
                            Time:</strong><br>{{ date('M d, Y H:i', strtotime($shift->start_time)) }}</div>
                    <div class="col-md-6"><strong>End
                            Time:</strong><br>{{ $shift->end_time ? date('M d, Y H:i', strtotime($shift->end_time)) : 'Not Ended' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Stock Analysis -->
        @if(count($tankCalculations) > 0)
            <div class="card">
                <div class="card-header">Professional Stock Analysis</div>
                <div class="card-body" style="padding: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Tank</th>
                                <th>Product</th>
                                <th class="text-end">Opening (L)</th>
                                <th class="text-end">Closing (L)</th>
                                <th class="text-end">Oil Received</th>
                                <th class="text-end">Nozzle Sales</th>
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
                                    <td class="text-end">
                                        @if($calc['variance'] > 0)+{{ number_format($calc['variance'], 2) }}@elseif($calc['variance'] < 0){{ number_format($calc['variance'], 2) }}@else
                                        0.00 @endif</td>
                                    <td class="text-end">
                                        @if($calc['total_nozzle_sales'] > 0)@if($calc['variance_percent'] > 0)+{{ number_format($calc['variance_percent'], 2) }}%@elseif($calc['variance_percent'] < 0){{ number_format($calc['variance_percent'], 2) }}%@else
                                        0.00% @endif@else N/A @endif</td>
                                    <td><span class="badge bg-{{ $calc['status_class'] }}">{{ $calc['status'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tank-wise Detailed Analysis -->
            <h5 style="margin: 10px 0 8px 0; font-size: 14px; font-weight: bold;">Tank-wise Detailed Analysis</h5>
            @foreach($tankCalculations as $tankId => $calc)
                <div class="card">
                    <div class="card-header">{{ $calc['tank_name'] }} - {{ $calc['product_name'] }}</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="summary-card">
                                    <h6>Stock Movement (Liters)</h6>
                                    <table>
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
                                                @if($calc['physical_usage'] > 0)+{{ number_format($calc['physical_usage'], 2) }}@elseif($calc['physical_usage'] < 0){{ number_format($calc['physical_usage'], 2) }}@else
                                                0.00 @endif</td>
                                        </tr>
                                        @if($calc['oil_purchased'] > 0)
                                            <tr>
                                                <td width="55%">Oil Purchased:</td>
                                                <td class="text-end">+{{ number_format($calc['oil_purchased'], 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td width="55%"><strong>Adjusted Usage:</strong></td>
                                            <td class="text-end">
                                                <strong>@if($calc['adjusted_physical_usage'] > 0)+{{ number_format($calc['adjusted_physical_usage'], 2) }}@elseif($calc['adjusted_physical_usage'] < 0){{ number_format($calc['adjusted_physical_usage'], 2) }}@else
                                                0.00 @endif</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="summary-card">
                                    <h6>Sales & Variance Analysis</h6>
                                    <table>
                                        <tr>
                                            <td width="55%">Nozzle Sales:</td>
                                            <td class="text-end">{{ number_format($calc['total_nozzle_sales'], 2) }} L</td>
                                        </tr>
                                        <tr>
                                            <td width="55%">Reset Sales:</td>
                                            <td class="text-end">{{ number_format($calc['reset_sales_liters'], 2) }} L</td>
                                        </tr>
                                        <tr>
                                            <td width="55%"><strong>Variance:</strong></td>
                                            <td class="text-end">
                                                <strong>@if($calc['variance'] > 0)+{{ number_format($calc['variance'], 2) }} L
                                                @elseif($calc['variance'] < 0){{ number_format($calc['variance'], 2) }} L
                                                    @else 0.00 L
                                                    @endif</strong><br><small>{{ $calc['variance_text'] }}</small><br><span
                                                    class="badge bg-{{ $calc['gain_loss_class'] }}">{{ $calc['gain_loss'] }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Nozzle Transactions -->
                        @if(isset($nozzleReadings) && count($nozzleReadings->where('nozzle.tank_id', $tankId)) > 0)
                            <div class="mt-2">
                                <h6>Nozzle Transactions</h6>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Nozzle</th>
                                            <th>Dispenser</th>
                                            <th class="text-end">Opening</th>
                                            <th class="text-end">Closing</th>
                                            <th class="text-end">Dispensed</th>
                                            <th class="text-end">Rate</th>
                                            <th class="text-end">Amount</th>
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
                    </div>
                </div>
            @endforeach
        @endif

        <!-- Footer -->
        <footer>Generated by Pump360 • {{ now()->format('M d, Y \a\t H:i') }}</footer>
    </div>
</body>

</html>