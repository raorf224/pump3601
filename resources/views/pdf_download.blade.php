<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Stock Report - {{ $shift->station->name ?? 'Fuel Station' }}</title>
    <style>
        /* ========== PRINT STYLES - PREVENT PAGE BREAKS ========== */
        @media print {
            .no-print {
                display: none !important;
            }

            body,
            html {
                height: auto;
                overflow: visible;
            }

            .report-container {
                max-width: 100% !important;
                padding: 0.1in !important;
            }

            /* Prevent ALL page breaks */
            .card,
            .card-body,
            .card-header,
            .summary-card,
            .table-responsive,
            table,
            tr,
            td,
            th,
            tbody,
            thead,
            .row,
            .col-md-2,
            .col-md-3,
            .col-md-4,
            .col-md-6,
            .company-header,
            footer {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
                break-after: avoid !important;
                page-break-after: avoid !important;
                break-before: avoid !important;
                page-break-before: avoid !important;
            }

            .card {
                margin-bottom: 8px !important;
                border: 1px solid #ccc !important;
            }

            .card-body {
                padding: 0.5rem !important;
            }

            .table-sm th,
            .table-sm td {
                padding: 0.25rem !important;
                font-size: 9px !important;
            }

            .summary-card {
                padding: 6px !important;
                margin-bottom: 6px !important;
            }

            .company-header {
                margin-bottom: 10px !important;
                padding-bottom: 5px !important;
            }

            footer {
                margin-top: 8px !important;
                padding-top: 5px !important;
            }

            @page {
                size: A4;
                margin: 0.15in;
            }
        }

        /* ========== SCREEN STYLES ========== */
        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }

        .company-header {
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .company-header h2 {
            margin-bottom: 5px;
        }

        .summary-card {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background: #ffffff;
            border-radius: 12px;
            transition: all 0.25s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
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

        .card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 15px;
            font-weight: 600;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
        }

        .bg-light {
            background-color: #f8f9fc;
        }

        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-spinner {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        .loading-spinner .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #1e466e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        .loading-spinner p {
            font-size: 14px;
            color: #333;
            margin: 0;
        }

        .loading-spinner .progress-text {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="report-container" id="pdfContent">
        @php
            $pdfFileName = "shift_stock_report_SHIFT-{$shift->id}_" . date('Y-m-d', strtotime($shift->start_time)) . "_to_" . date('Y-m-d', strtotime($shift->end_time));
        @endphp

        <!-- Header -->
        <div class="company-header">
            <div class="row">
                <div class="col-8">
                    <h2 class="mb-1">Shift Stock Reconciliation Report</h2>
                    <h4 class="text-muted mb-2">{{ $shift->station->name ?? 'N/A' }}</h4>
                    <p class="mb-0 text-muted">Professional Fuel Management System</p>
                </div>
                <div class="col-4 text-end">
                    <p class="mb-0"><small>Report ID: SHIFT-{{ $shift->id }}</small></p>
                    <p class="mb-0"><small>Generated: {{ now()->format('M d, Y H:i') }}</small></p>
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
                        <span
                            class="text-dark">{{ \Carbon\Carbon::parse($shift->start_time)->format('M d, Y H:i') }}</span>
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
                                    <th class="text-end">Oil Recived (L)</th>
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
                                            class="text-end {{ $calculation['gain_loss_class'] == 'success' ? 'variance-positive' : ($calculation['gain_loss_class'] == 'danger' ? 'variance-negative' : '') }}">
                                            @if($calculation['variance'] > 0)+{{ number_format($calculation['variance'], 2) }}@elseif($calculation['variance'] < 0){{ number_format($calculation['variance'], 2) }}@else
                                            0.00 @endif
                                        </td>
                                        <td class="text-end">
                                            @if($calculation['total_nozzle_sales'] > 0)
                                                @if($calculation['variance_percent'] > 0)+{{ number_format($calculation['variance_percent'], 2) }}%
                                                @elseif($calculation['variance_percent'] < 0){{ number_format($calculation['variance_percent'], 2) }}%
                                                @else 0.00% @endif
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
                                            <td width="60%">Closing Stock:</td>
                                            <td class="text-end">{{ number_format($calculation['closing_stock'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td width="60%">Physical Usage:</td>
                                            <td class="text-end">
                                                @if($calculation['physical_usage'] > 0)+{{ number_format($calculation['physical_usage'], 2) }}@elseif($calculation['physical_usage'] < 0){{ number_format($calculation['physical_usage'], 2) }}@else
                                                0.00 @endif</td>
                                        </tr>
                                        @if($calculation['oil_purchased'] > 0)
                                            <tr>
                                                <td width="60%">Oil Purchased:</td>
                                                <td class="text-end text-info">
                                                    +{{ number_format($calculation['oil_purchased'], 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td width="60%"><strong>Adjusted Usage:</strong></td>
                                            <td class="text-end">
                                                <strong>@if($calculation['adjusted_physical_usage'] > 0)+{{ number_format($calculation['adjusted_physical_usage'], 2) }}@elseif($calculation['adjusted_physical_usage'] < 0){{ number_format($calculation['adjusted_physical_usage'], 2) }}@else
                                                0.00 @endif</strong></td>
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
                                            <td width="60%">Reset Sales (Liters):</td>
                                            <td class="text-end">{{ number_format($calculation['reset_sales_liters'], 2) }} L
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="60%"><strong>Total Nozzle Sales:</strong></td>
                                            <td class="text-end">
                                                <strong>{{ number_format($calculation['total_nozzle_sales'], 2) }} L</strong>
                                            </td>
                                        </tr>
                                        <tr
                                            class="{{ $calculation['gain_loss_class'] == 'success' ? 'table-success' : ($calculation['gain_loss_class'] == 'danger' ? 'table-danger' : 'table-secondary') }}">
                                            <td width="60%"><strong>Variance Analysis:</strong></td>
                                            <td class="text-end">
                                                <strong
                                                    class="{{ $calculation['gain_loss_class'] == 'success' ? 'variance-positive' : ($calculation['gain_loss_class'] == 'danger' ? 'variance-negative' : '') }}">
                                                    @if($calculation['variance'] > 0)+{{ number_format($calculation['variance'], 2) }}
                                                        L
                                                    @elseif($calculation['variance'] < 0){{ number_format($calculation['variance'], 2) }}
                                                    L @else 0.00 L @endif
                                                    @if($calculation['total_nozzle_sales'] > 0)
                                                    ({{ number_format($calculation['variance_percent'], 2) }}%) @endif
                                                </strong><br>
                                                <small class="text-muted">{{ $calculation['variance_text'] }}</small><br>
                                                <span
                                                    class="badge bg-{{ $calculation['gain_loss_class'] }}">{{ $calculation['gain_loss'] }}</span>
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
        @if(isset($financialSummary))
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Complete Financial Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4 text-center mb-4">
                            <div class="col-md-4">
                                <div class="summary-card h-100 p-4 border rounded bg-white">
                                    <h4 class="text-success fw-bold">Rs.
                                        {{ number_format($financialSummary['total_revenue'] ?? 0, 2) }}</h4>
                                    <p class="mb-3 text-muted fw-semibold">Total Revenue</p>
                                    <div class="small text-start">
                                        <div class="d-flex justify-content-between mb-2"><span>Fuel Sales:</span><strong>Rs.
                                                {{ number_format($financialSummary['fuel_sales'] ?? 0, 2) }}</strong></div>
                                        <div class="d-flex justify-content-between mb-2"><span>Lube Sales:</span><strong>Rs.
                                                {{ number_format($financialSummary['lube_sales'] ?? 0, 2) }}</strong></div>
                                        <div class="d-flex justify-content-between"><span>Other Income:</span><strong>Rs.
                                                {{ number_format($financialSummary['transaction_income'] ?? 0, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="summary-card h-100 p-4 border rounded bg-white">
                                    <h4 class="text-danger fw-bold">Rs.
                                        {{ number_format($financialSummary['total_expenses'] ?? 0, 2) }}</h4>
                                    <p class="mb-3 text-muted fw-semibold">Total Expenses</p>
                                    <div class="small text-start">
                                        <div class="d-flex justify-content-between mb-2"><span>Fuel Oil
                                                Purchase:</span><strong>Rs.
                                                {{ number_format($financialSummary['oil_purchase'] ?? 0, 2) }}</strong></div>
                                        <div class="d-flex justify-content-between mb-2"><span>Lube Purchase:</span><strong>Rs.
                                                {{ number_format($financialSummary['lube_purchase'] ?? 0, 2) }}</strong></div>
                                        <div class="d-flex justify-content-between"><span>Other Expenses:</span><strong>Rs.
                                                {{ number_format($financialSummary['transaction_expense'] ?? 0, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="summary-card p-4 border rounded bg-white mb-3">
                                    <h4
                                        class="{{ ($financialSummary['net_income'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                        Rs. {{ number_format($financialSummary['net_income'] ?? 0, 2) }}</h4>
                                    <p class="mb-0 text-muted fw-semibold">Net Income</p>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="summary-card p-3 border rounded bg-white h-100">
                                            <h5 class="text-primary fw-bold mb-1">Rs.
                                                {{ number_format($cashFlow->fuelcard ?? 0, 2) }}</h5><small
                                                class="text-muted fw-semibold">Fuel Card</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="summary-card p-3 border rounded bg-white h-100">
                                            <h5 class="text-info fw-bold mb-1">Rs.
                                                {{ number_format($cashFlow->creditcard ?? 0, 2) }}</h5><small
                                                class="text-muted fw-semibold">Credit Card</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <div class="summary-card p-4 border rounded bg-white text-center h-100">
                                    <h4 class="text-warning fw-bold">Rs.
                                        {{ number_format($financialSummary['cash_handover'] ?? 0, 2) }}</h4>
                                    <p class="mb-0 text-muted fw-semibold">Opening Balance</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card p-4 border rounded bg-white text-center h-100">
                                    <h4 class="text-primary fw-bold">Rs.
                                        {{ number_format($financialSummary['cash_in_hand'] ?? 0, 2) }}</h4>
                                    <p class="mb-0 text-muted fw-semibold">Closing Balance</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card p-4 border rounded bg-white text-center h-100">
                                    <h4 class="text-info fw-bold">Rs.
                                        {{ number_format($financialSummary['cash_in_bank'] ?? 0, 2) }}</h4>
                                    <p class="mb-0 text-muted fw-semibold">Cash in Bank</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card p-4 border rounded bg-white text-center h-100">
                                    <h4 class="text-dark fw-bold">Rs.
                                        {{ number_format($financialSummary['total_cash_balance'] ?? 0, 2) }}</h4>
                                    <p class="mb-0 text-muted fw-semibold">Total Cash Balance</p>
                                </div>
                            </div>
                        </div>

            @php
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
                    </div>
                </div>
        @endif

        <!-- Lubricant Transactions Section -->
        @if(isset($lubeDocuments) && $lubeDocuments->count() > 0)
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Lubricant Transactions</h5>
                </div>
                <div class="card-body">
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
                                        <td class="text-end">Rs.
                                            {{ number_format($lubeSummary['purchase']['total_amount'], 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
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

        <!-- Oil Purchase Section -->
        @if(isset($oilPurchaseSummary) && $oilPurchaseSummary['count'] > 0)
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Fuel Oil Purchase</h5>
                </div>
                <div class="card-body">
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
                                        <td class="text-end text-danger">Rs.
                                            {{ number_format($oilPurchaseSummary['total_amount'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Total Purchases:</td>
                                        <td class="text-end">{{ $oilPurchaseSummary['count'] }} records</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
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
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="summary-card p-3 border rounded bg-white text-center">
                                <h4 class="text-warning fw-bold mb-2">{{ number_format($totalShortageLiters, 2) }}
                                    <small>L</small></h4>
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
                </div>
            </div>
        @endif

        <!-- Other Financial Transactions -->
        @if(isset($transactions) && $transactions->count() > 0)
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Other Financial Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Account</th>
                                    <th>To Account</th>
                                    <th>Method</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Credit</th>
                                    <th>Note</th>
                                    <th>Time</th>
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
                                        <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-info">
                                    <td colspan="4" class="text-end"><strong>Net Transactions:</strong></td>
                                    <td class="text-end"><strong>Rs.
                                            {{ number_format($financialSummary['transaction_summary']['expense']['total'] ?? 0, 2) }}</strong>
                                    </td>
                                    <td class="text-end"><strong>Rs.
                                            {{ number_format($financialSummary['transaction_summary']['income']['total'] ?? 0, 2) }}</strong>
                                    </td>
                                    <td colspan="2"
                                        class="{{ ($financialSummary['transaction_net'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        <strong>Net: Rs.
                                            {{ number_format($financialSummary['transaction_net'] ?? 0, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="mt-5 pt-3 border-top text-center">
            <p class="text-muted small">Generated by Pump360 • {{ now()->format('M d, Y \a\t H:i') }}</p>
        </div>
    </div>

    <div id="loadingOverlay" class="loading-overlay" style="display: flex;">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Generating PDF, please wait...</p>
            <div class="progress-text">Capturing report data...</div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        (function () {
            var element = document.getElementById('pdfContent');
            var fileName = '{{ $pdfFileName }}';
            var loading = document.getElementById('loadingOverlay');
            var progressText = document.querySelector('.progress-text');

            // Scroll to top to capture full content
            window.scrollTo(0, 0);

            // Wait for page to be fully loaded and rendered
            setTimeout(function () {
                if (progressText) progressText.innerHTML = 'Rendering report content...';

                html2canvas(element, {
                    scale: 1.5,
                    backgroundColor: '#ffffff',
                    logging: false,
                    useCORS: true,
                    windowWidth: element.scrollWidth,
                    windowHeight: element.scrollHeight,
                    onclone: function (clonedDoc, element) {
                        // Ensure cloned document has proper styling
                        console.log('Clone ready');
                    }
                }).then(function (canvas) {
                    if (progressText) progressText.innerHTML = 'Creating PDF file...';

                    var imgData = canvas.toDataURL('image/jpeg', 0.85);
                    var { jsPDF } = window.jspdf;
                    var imgWidth = 210; // A4 width in mm
                    var pageHeight = 297; // A4 height in mm
                    var imgHeight = (canvas.height * imgWidth) / canvas.width;

                    var pdf = new jsPDF('p', 'mm', 'a4');

                    // Add image to PDF
                    pdf.addImage(imgData, 'JPEG', 0, 0, imgWidth, imgHeight, undefined, 'FAST');

                    // Save PDF
                    pdf.save(fileName + '.pdf');

                    // Hide loading and close window
                    loading.style.display = 'none';
                    setTimeout(function () {
                        window.close();
                    }, 1000);
                }).catch(function (error) {
                    console.error('PDF Error:', error);
                    loading.style.display = 'none';
                    alert('PDF generation failed. Please try again or use Print option (Ctrl+P).');
                });
            }, 800); // Increased delay to ensure full rendering
        })();
    </script>
</body>

</html>