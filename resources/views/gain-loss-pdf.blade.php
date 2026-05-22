<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tank Gain/Loss Report</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></scri
            < style >
            body {
            font - family: 'DejaVu Sans', Arial, sans - serif;
            font - size: 12px;
            line - height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text - align: center;
            margin - bottom: 20px;
            border - bottom: 2px solid #333;
            padding - bottom: 10px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font - size: 24px;
        }
        .header.subtitle {
            color: #7f8c8d;
            font - size: 14px;
        }
        .section {
            margin - bottom: 15px;
        }
        .section - title {
            background - color: #f8f9fa;
            padding: 8px;
            border - left: 4px solid #3498db;
            margin - bottom: 10px;
            font - weight: bold;
            color: #2c3e50;
        }
        table {
            width: 100 %;
            border - collapse: collapse;
            margin - bottom: 10px;
        }
        table th {
            background - color: #34495e;
            color: white;
            padding: 8px;
            text - align: left;
            font - size: 11px;
        }
        table td {
            padding: 8px;
            border: 1px solid #ddd;
            font - size: 11px;
        }
        .summary - table {
            width: 100 %;
            margin - bottom: 15px;
        }
        .summary - table td {
            border: none;
            padding: 4px 8px;
        }
        .summary - table tr: nth - child(even) {
            background - color: #f8f9fa;
        }
        .footer {
            margin - top: 30px;
            padding - top: 10px;
            border - top: 1px solid #ddd;
            text - align: center;
            font - size: 10px;
            color: #7f8c8d;
        }
        .row {
            display: flex;
            flex - wrap: wrap;
            margin: 0 - 10px;
        }
        .col - 6 {
            flex: 0 0 50 %;
            padding: 0 10px;
            box - sizing: border - box;
        }
        .card {
            border: 1px solid #ddd;
            border - radius: 5px;
            margin - bottom: 10px;
            overflow: hidden;
        }
        .card - header {
            background - color: #f8f9fa;
            padding: 8px;
            font - weight: bold;
            border - bottom: 1px solid #ddd;
        }
        .card - body {
            padding: 10px;
        }
        .text - end {
            text - align: right;
        }
        .text - center {
            text - align: center;
        }
        .mb - 3 {
            margin - bottom: 15px;
        }
        .alert {
            padding: 10px;
            border - radius: 5px;
            margin: 10px 0;
        }
        .alert - success {
            background - color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert - danger {
            background - color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert - info {
            background - color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .text - danger { color: #dc3545; }
        .text - success { color: #28a745; }
    </style >
</head >
            <body>
                <div class="header">
                    <h1>TANK GAIN/LOSS REPORT</h1>
                    <div class="subtitle">
                        Generated on: {{ $reportData['report_generated_at'] ?? now()->format('Y-m-d H:i:s') }}
                    </div>
                </div>

                @php
                    // ✅ SAFE DATA ACCESS HELPER FUNCTIONS
                    function getValue($data, $key, $default = 'N/A')
                    {
                        if (is_array($data)) {
                            return $data[$key] ?? $default;
                        } elseif (is_object($data)) {
                            return $data->$key ?? $default;
                        }
                        return $default;
                    }

                    function getNestedValue($data, $key1, $key2, $default = 'N/A')
                    {
                        if (is_array($data)) {
                            return $data[$key1][$key2] ?? $default;
                        } elseif (is_object($data)) {
                            return $data->$key1->$key2 ?? $default;
                        }
                        return $default;
                    }

                    function formatNumber($value, $decimals = 2)
                    {
                        return is_numeric($value) ? number_format($value, $decimals) : '0.00';
                    }

                    // ✅ GET MAIN DATA SECTIONS
                    $currentReading = getValue($reportData, 'current_reading', []);
                    $salesData = getValue($reportData, 'sales_data', []);
                    $gainLossData = getValue($reportData, 'gain_loss_data', []);
                @endphp

                @if(!empty($currentReading))
                    <div class="section">
                        <div class="section-title">Tank Information</div>
                        <table class="summary-table">
                            <tr>
                                <td><strong>Tank Name:</strong></td>
                                <td>{{ getValue($currentReading, 'tank_name', 'N/A') }}</td>
                                <td><strong>Product:</strong></td>
                                <td>{{ getValue($currentReading, 'product_name', 'N/A') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Capacity:</strong></td>
                                <td>{{ formatNumber(getValue($currentReading, 'capacity', 0)) }} L</td>
                                <td><strong>Recorded By:</strong></td>
                                <td>{{ getValue($currentReading, 'recorded_by', 'N/A') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Report Period:</strong></td>
                                <td colspan="3">{{ getValue($reportData, 'report_period', 'N/A') }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="card">
                                <div class="card-header">Stock Analysis</div>
                                <div class="card-body">
                                    <table>
                                        <tr>
                                            <td>Previous Stock:</td>
                                            <td class="text-end">{{ formatNumber(getValue($currentReading, 'old_dip_liters', 0)) }} L</td>
                                        </tr>
                                        <tr>
                                            <td>Current Stock:</td>
                                            <td class="text-end">{{ formatNumber(getValue($currentReading, 'dip_in_liters', 0)) }} L</td>
                                        </tr>
                                        <tr>
                                            <td>Physical Usage:</td>
                                            <td class="text-end">{{ formatNumber(getValue($gainLossData, 'physical_stock_change', 0)) }} L</td>
                                        </tr>
                                        <tr>
                                            <td>System Sales:</td>
                                            <td class="text-end">{{ formatNumber(getValue($salesData, 'total_sales_liters', 0)) }} L</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card">
                                <div class="card-header">Gain/Loss Calculation</div>
                                <div class="card-body">
                                    <table>
                                        <tr>
                                            <td>Expected Stock:</td>
                                            <td class="text-end">{{ formatNumber(getValue($gainLossData, 'expected_stock', 0)) }} L</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Variance:</strong></td>
                                            <td class="text-end"><strong>{{ formatNumber(getValue($gainLossData, 'variance_liters', 0)) }} L</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Variance %:</td>
                                            <td class="text-end {{ abs(getValue($gainLossData, 'variance_percentage', 0)) > 5 ? 'text-danger' : 'text-success' }}">
                                                {{ formatNumber(getValue($gainLossData, 'variance_percentage', 0)) }}%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Monetary Value:</td>
                                            <td class="text-end">Rs. {{ formatNumber(getValue($gainLossData, 'variance_amount', 0)) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $nozzleSales = getValue($salesData, 'nozzle_sales', []);
                        $hasNozzleSales = !empty($nozzleSales) && (is_array($nozzleSales) ? count($nozzleSales) > 0 : true);
                    @endphp

                    @if($hasNozzleSales)
                        <div class="section">
                            <div class="section-title">Nozzle-wise Sales</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nozzle</th>
                                        <th class="text-end">Sales (Liters)</th>
                                        <th class="text-end">Amount (Rs.)</th>
                                        <th class="text-end">Avg. Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(is_array($nozzleSales))
                                        @foreach($nozzleSales as $nozzle)
                                            <tr>
                                                <td>{{ getValue($nozzle, 'nozzle_name', 'N/A') }}</td>
                                                <td class="text-end">{{ formatNumber(getValue($nozzle, 'total_liters', 0)) }} L</td>
                                                <td class="text-end">Rs. {{ formatNumber(getValue($nozzle, 'total_amount', 0)) }}</td>
                                                <td class="text-end">
                                                    @php
                                                        $liters = getValue($nozzle, 'total_liters', 0);
                                                        $amount = getValue($nozzle, 'total_amount', 0);
                                                        $avgPrice = $liters > 0 ? $amount / $liters : 0;
                                                    @endphp
                                                    Rs. {{ formatNumber($avgPrice) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center">No nozzle sales data available</td>
                                        </tr>
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td><strong>Total</strong></td>
                                        <td class="text-end"><strong>{{ formatNumber(getValue($salesData, 'total_sales_liters', 0)) }} L</strong></td>
                                        <td class="text-end"><strong>Rs. {{ formatNumber(getValue($salesData, 'total_sales_amount', 0)) }}</strong></td>
                                        <td class="text-end"><strong>Rs. {{ formatNumber(getValue($gainLossData, 'average_price', 0)) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="section">
                            <div class="section-title">Nozzle-wise Sales</div>
                            <p>No sales data available for this period.</p>
                        </div>
                    @endif

                    <div class="section">
                        <div class="section-title">Status Summary</div>
                        @php
                            $status = getValue($gainLossData, 'status', 'normal');

                            if ($status === 'gain') {
                                $alertClass = 'alert-success';
                                $message = 'Extra stock detected. Possible reasons: Delivery received, measurement error.';
                            } elseif ($status === 'loss') {
                                $alertClass = 'alert-danger';
                                $message = 'Stock shortage detected. Possible reasons: Theft, evaporation, measurement error.';
                            } else {
                                $alertClass = 'alert-info';
                                $message = 'Stock levels are within acceptable limits (variance < 5%).';
                            }
                        @endphp

                        <div class="alert {{ $alertClass }}">
                            <strong>Status: {{ strtoupper($status) }}</strong><br>
                                {{ $message }}
                        </div>
                    </div>
                @else
                    <div class="alert alert-danger">
                        <strong>Error:</strong> No report data available.
                    </div>
                @endif

                <div class="footer">
                    This report was generated automatically on {{ $reportData['report_generated_at'] ?? now()->format('Y-m-d H:i:s') }}<br>
                        Fuel Management System
                </div>
            </body>
</html >