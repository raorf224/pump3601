<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Stock Report - {{ $shift->station->name ?? 'Fuel Station' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        /* SCREEN STYLES */
        .report-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .company-header {
            border-bottom: 2px solid #1e466e;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .company-header h2 {
            color: #1e466e;
            font-weight: 600;
        }

        .summary-card {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .variance-positive {
            color: #28a745;
            font-weight: bold;
        }

        .variance-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .table-sm th, .table-sm td {
            padding: 0.5rem;
        }

        .badge {
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 20px;
        }

        /* PDF capture styles */
        .pdf-mode {
            background: white;
        }
        
        .pdf-mode .card {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
            margin-bottom: 10px !important;
        }
    </style>
</head>

<body>
    <div class="report-container" id="reportContainer">
        @php
            // ✅ DEFINE totalSalesLitres VARIABLE HERE
            $totalSalesLitres = 0;
            if (isset($nozzleReadings) && isset($nozzleResets)) {
                $totalSalesLitres = $nozzleReadings->sum('total_dispensed') + $nozzleResets->sum('total_dispensed');
            }
            
            // ✅ Generate PDF filename with shift ID and date range
            $pdfStartDate = isset($shift->start_time) ? \Carbon\Carbon::parse($shift->start_time)->format('Y-m-d') : 'start';
            $pdfEndDate = isset($shift->end_time) ? \Carbon\Carbon::parse($shift->end_time)->format('Y-m-d') : 'end';
            $pdfFileName = "shift_stock_report_SHIFT-{$shift->id}_{$pdfStartDate}_to_{$pdfEndDate}";
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
                            <button class="btn btn-primary btn-sm" id="downloadPdfBtn">
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
                            <span class="text-dark">{{ \Carbon\Carbon::parse($shift->start_time)->format('M d, Y H:i') }}</span>
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
                                        <td class="text-end">{{ number_format($calculation['oil_purchased'], 2) }}</td>
                                        <td class="text-end">{{ number_format($calculation['total_nozzle_sales'], 2) }}</td>
                                        <td class="text-end {{ $calculation['gain_loss_class'] == 'success' ? 'variance-positive' : ($calculation['gain_loss_class'] == 'danger' ? 'variance-negative' : '') }}">
                                            @if($calculation['variance'] > 0)
                                                +{{ number_format($calculation['variance'], 2) }}
                                            @elseif($calculation['variance'] < 0)
                                                {{ number_format($calculation['variance'], 2) }}
                                            @else
                                                0.00
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($calculation['total_nozzle_sales'] > 0)
                                                @if($calculation['variance_percent'] > 0)
                                                    +{{ number_format($calculation['variance_percent'], 2) }}%
                                                @elseif($calculation['variance_percent'] < 0)
                                                    {{ number_format($calculation['variance_percent'], 2) }}%
                                                @else
                                                    0.00%
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
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
                            <div class="col-md-6">
                                <div class="summary-card">
                                    <h6>Stock Movement (Liters)</h6>
                                    <table class="table table-sm table-bordered">
                                        <tr><td width="60%">Opening Stock:</td><td class="text-end">{{ number_format($calculation['opening_stock'], 2) }}</td></tr>
                                        <tr><td>Closing Stock:</td><td class="text-end">{{ number_format($calculation['closing_stock'], 2) }}</td></tr>
                                        <tr><td>Physical Usage:</td>
                                            <td class="text-end {{ $calculation['physical_usage'] > 0 ? 'text-success' : ($calculation['physical_usage'] < 0 ? 'text-danger' : '') }}">
                                                @if($calculation['physical_usage'] > 0)+{{ number_format($calculation['physical_usage'], 2) }}@elseif($calculation['physical_usage'] < 0){{ number_format($calculation['physical_usage'], 2) }}@else 0.00 @endif
                                            </td>
                                        </tr>
                                        @if($calculation['oil_purchased'] > 0)
                                        <tr><td>Oil Purchased:</td><td class="text-end text-info">+{{ number_format($calculation['oil_purchased'], 2) }}</td></tr>
                                        @endif
                                        <tr><td><strong>Adjusted Usage:</strong></td>
                                            <td class="text-end {{ $calculation['adjusted_physical_usage'] > 0 ? 'text-success' : ($calculation['adjusted_physical_usage'] < 0 ? 'text-danger' : '') }}">
                                                <strong>@if($calculation['adjusted_physical_usage'] > 0)+{{ number_format($calculation['adjusted_physical_usage'], 2) }}@elseif($calculation['adjusted_physical_usage'] < 0){{ number_format($calculation['adjusted_physical_usage'], 2) }}@else 0.00 @endif</strong>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="summary-card">
                                    <h6>Sales & Variance Analysis</h6>
                                    <table class="table table-sm table-bordered">
                                        <tr><td width="60%">Nozzle Sales (Liters):</td><td class="text-end">{{ number_format($calculation['nozzle_sales_liters'], 2) }} L</td></tr>
                                        <tr><td>Reset Sales (Liters):</td><td class="text-end">{{ number_format($calculation['reset_sales_liters'], 2) }} L</td></tr>
                                        <tr><td><strong>Total Nozzle Sales:</strong></td><td class="text-end"><strong>{{ number_format($calculation['total_nozzle_sales'], 2) }} L</strong></td></tr>
                                        <tr class="{{ $calculation['gain_loss_class'] == 'success' ? 'table-success' : ($calculation['gain_loss_class'] == 'danger' ? 'table-danger' : 'table-secondary') }}">
                                            <td><strong>Variance Analysis:</strong></td>
                                            <td class="text-end">
                                                <strong class="{{ $calculation['gain_loss_class'] == 'success' ? 'variance-positive' : ($calculation['gain_loss_class'] == 'danger' ? 'variance-negative' : '') }}">
                                                    @if($calculation['variance'] > 0)+{{ number_format($calculation['variance'], 2) }} L @elseif($calculation['variance'] < 0){{ number_format($calculation['variance'], 2) }} L @else 0.00 L @endif
                                                    @if($calculation['total_nozzle_sales'] > 0) ({{ number_format($calculation['variance_percent'], 2) }}%) @endif
                                                </strong><br>
                                                <small class="text-muted">{{ $calculation['variance_text'] }}</small><br>
                                                <span class="badge bg-{{ $calculation['gain_loss_class'] }}">{{ $calculation['gain_loss'] }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if(isset($nozzleReadings) && count($nozzleReadings->where('nozzle.tank_id', $tankId)) > 0)
                        <div class="mt-3">
                            <h6>Nozzle Transactions</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr><th>Nozzle</th><th>Dispenser</th><th class="text-end">Opening</th><th class="text-end">Closing</th><th class="text-end">Dispensed</th><th class="text-end">Rate (Rs.)</th><th class="text-end">Amount (Rs.)</th></tr>
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
                            <div class="summary-card">
                                <h4 class="text-success fw-bold">Rs. {{ number_format($financialSummary['total_revenue'] ?? 0, 2) }}</h4>
                                <p class="mb-2">Total Revenue</p>
                                <div class="small text-start">
                                    <div>Fuel Sales: <strong>Rs. {{ number_format($financialSummary['fuel_sales'] ?? 0, 2) }}</strong></div>
                                    <div>Lube Sales: <strong>Rs. {{ number_format($financialSummary['lube_sales'] ?? 0, 2) }}</strong></div>
                                    <div>Other Income: <strong>Rs. {{ number_format($financialSummary['transaction_income'] ?? 0, 2) }}</strong></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card">
                                <h4 class="text-danger fw-bold">Rs. {{ number_format($financialSummary['total_expenses'] ?? 0, 2) }}</h4>
                                <p class="mb-2">Total Expenses</p>
                                <div class="small text-start">
                                    <div>Fuel Oil Purchase: <strong>Rs. {{ number_format($financialSummary['oil_purchase'] ?? 0, 2) }}</strong></div>
                                    <div>Lube Purchase: <strong>Rs. {{ number_format($financialSummary['lube_purchase'] ?? 0, 2) }}</strong></div>
                                    <div>Other Expenses: <strong>Rs. {{ number_format($financialSummary['transaction_expense'] ?? 0, 2) }}</strong></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card mb-3">
                                <h4 class="{{ ($financialSummary['net_income'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                    Rs. {{ number_format($financialSummary['net_income'] ?? 0, 2) }}
                                </h4>
                                <p class="mb-0">Net Income</p>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="summary-card">
                                        <h6 class="text-primary mb-0">Rs. {{ number_format($cashFlow->fuelcard ?? 0, 2) }}</h6>
                                        <small>Fuel Card</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="summary-card">
                                        <h6 class="text-info mb-0">Rs. {{ number_format($cashFlow->creditcard ?? 0, 2) }}</h6>
                                        <small>Credit Card</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="summary-card text-center">
                                <h5 class="text-warning">Rs. {{ number_format($financialSummary['cash_handover'] ?? 0, 2) }}</h5>
                                <span>Opening Balance</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card text-center">
                                <h5 class="text-primary">Rs. {{ number_format($financialSummary['cash_in_hand'] ?? 0, 2) }}</h5>
                                <span>Closing Balance</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card text-center">
                                <h5 class="text-dark">Rs. {{ number_format($financialSummary['cash_in_bank'] ?? 0, 2) }}</h5>
                                <span>Cash in Bank</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card text-center">
                                <h5 class="text-success">Rs. {{ number_format($financialSummary['total_cash_balance'] ?? 0, 2) }}</h5>
                                <span>Total Cash Balance</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Footer -->
            <div class="mt-4 pt-3 border-top text-center">
                <p class="text-muted small">Generated by Pump360 • {{ now()->format('M d, Y \a\t H:i') }}</p>
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

    <script>
        document.getElementById('downloadPdfBtn')?.addEventListener('click', async function() {
            const element = document.getElementById('reportContainer');
            const btn = this;
            const pdfFileName = '{{ $pdfFileName }}';
            
            btn.disabled = true;
            btn.innerText = 'Generating PDF...';
            
            try {
                element.classList.add('pdf-mode');
                
                const canvas = await html2canvas(element, {
                    scale: 2.5,
                    backgroundColor: '#ffffff',
                    logging: false,
                    useCORS: true,
                    windowWidth: element.scrollWidth,
                    windowHeight: element.scrollHeight
                });
                
                const imgData = canvas.toDataURL('image/png');
                const { jsPDF } = window.jspdf;
                
                const imgWidth = 210;
                const pageHeight = 297;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                
                let pdf = new jsPDF('p', 'mm', 'a4');
                let position = 0;
                
                if (imgHeight <= pageHeight) {
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                } else {
                    let heightLeft = imgHeight;
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                    
                    while (heightLeft > 0) {
                        position = heightLeft - imgHeight;
                        pdf.addPage();
                        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                        heightLeft -= pageHeight;
                    }
                }
                
                pdf.save(pdfFileName + '.pdf');
                
            } catch (error) {
                console.error('PDF generation error:', error);
                alert('Error generating PDF. Please try again.');
            } finally {
                element.classList.remove('pdf-mode');
                btn.disabled = false;
                btn.innerText = '📄 Download PDF';
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    @if(!isset($shift))
    <script>
        document.getElementById('station_id')?.addEventListener('change', function () {
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