<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Stock Report - {{ $shift->station->name ?? 'Fuel Station' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Required Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f2f5;
            padding: 20px;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        /* PDF Content Container */
        #pdfContent {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            
            #pdfContent {
                max-width: 100%;
                padding: 15px;
                margin: 0;
                box-shadow: none;
            }
            
            .no-print, .btn-group, .controls {
                display: none !important;
            }
            
            .card {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }

        /* Header Styles */
        .company-header {
            border-bottom: 3px solid #1a3a5c;
            padding-bottom: 15px;
            margin-bottom: 25px;
            overflow: hidden;
        }

        .company-header h2 {
            color: #1a3a5c;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-header h5 {
            color: #6c757d;
            margin-bottom: 5px;
        }

        /* Card Styles */
        .card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 12px 15px;
            border-radius: 8px 8px 0 0;
        }

        .card-header h5, .card-header h6 {
            margin: 0;
            font-weight: 600;
            color: #1a3a5c;
        }

        .card-body {
            padding: 15px;
        }

        /* Summary Cards */
        .summary-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
            height: 100%;
        }

        .summary-card h4 {
            color: #1a3a5c;
            margin-bottom: 10px;
            font-size: 1.5rem;
        }

        .summary-card h5 {
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        .summary-card p {
            margin-bottom: 0;
            color: #6c757d;
        }

        /* Table Styles */
        .table {
            font-size: 12px;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }

        .table td {
            vertical-align: middle;
        }

        .table-sm th, .table-sm td {
            padding: 0.5rem;
        }

        /* Variance Colors */
        .variance-positive {
            color: #28a745;
            font-weight: bold;
        }

        .variance-negative {
            color: #dc3545;
            font-weight: bold;
        }

        /* Badge Styles */
        .badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }

        .bg-success { background: #28a745; color: white; }
        .bg-danger { background: #dc3545; color: white; }
        .bg-warning { background: #ffc107; color: #333; }
        .bg-info { background: #17a2b8; color: white; }
        .bg-secondary { background: #6c757d; color: white; }

        /* Text Colors */
        .text-success { color: #28a745 !important; }
        .text-danger { color: #dc3545 !important; }
        .text-info { color: #17a2b8 !important; }
        .text-muted { color: #6c757d !important; }

        /* Control Buttons */
        .controls {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }

        .btn-pdf {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 40px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-pdf:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }

        .btn-print {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 40px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-print:hover {
            background: #138496;
            transform: translateY(-2px);
        }

        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-content {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            text-align: center;
            min-width: 300px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #dc3545;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .progress-bar-container {
            width: 100%;
            background: #f0f0f0;
            border-radius: 10px;
            margin-top: 15px;
            overflow: hidden;
        }

        .progress-bar {
            width: 0%;
            height: 30px;
            background: #28a745;
            color: white;
            text-align: center;
            line-height: 30px;
            border-radius: 10px;
            transition: width 0.3s;
        }

        /* Info Box */
        .info-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .controls {
                bottom: 10px;
                right: 10px;
            }
            
            .btn-pdf, .btn-print {
                padding: 8px 16px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h4>Generating PDF...</h4>
            <p id="loadingStatus">Preparing your report</p>
            <div class="progress-bar-container">
                <div id="progressBar" class="progress-bar">0%</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div id="pdfContent">
        <!-- Header -->
        <div class="company-header">
            <div class="row">
                <div class="col-8">
                    <h2 class="mb-2">Shift Stock Reconciliation Report</h2>
                    <h5 class="text-muted">{{ $shift->station->name ?? 'N/A' }}</h5>
                    <p class="text-muted mb-0">Professional Fuel Management System</p>
                </div>
                <div class="col-4 text-end">
                    <p class="mb-1"><strong>Report ID:</strong> SHIFT-{{ $shift->id }}</p>
                    <p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, H:i:s') }}</p>
                </div>
            </div>
        </div>

        <!-- Shift Overview -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Shift Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="info-box">
                            <strong>Station:</strong><br>
                            <span>{{ $shift->station->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="info-box">
                            <strong>Shift Type:</strong><br>
                            <span>{{ $shift->shift_no == 1 ? 'Day Shift' : 'Night Shift' }}</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="info-box">
                            <strong>Shift Incharge:</strong><br>
                            <span>{{ $shift->shiftIncharger->user->full_name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="info-box">
                            <strong>Opening Balance:</strong><br>
                            <span>Rs. {{ number_format($shift->cash_handover ?? 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="info-box">
                            <strong>Status:</strong><br>
                            <span class="badge bg-secondary">{{ ucfirst($shift->status ?? 'Closed') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="info-box">
                            <strong>Start Time:</strong><br>
                            <span>{{ \Carbon\Carbon::parse($shift->start_time)->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="info-box">
                            <strong>End Time:</strong><br>
                            <span>@if($shift->end_time){{ \Carbon\Carbon::parse($shift->end_time)->format('d M Y, H:i') }}@else Not Ended @endif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Analysis Table -->
        @if(count($tankCalculations) > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Stock Analysis Summary</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tank</th>
                                    <th>Product</th>
                                    <th class="text-end">Opening (L)</th>
                                    <th class="text-end">Closing (L)</th>
                                    <th class="text-end">Received (L)</th>
                                    <th class="text-end">Sales (L)</th>
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
                                        <td class="text-end @if($calculation['variance'] > 0) variance-positive @elseif($calculation['variance'] < 0) variance-negative @endif">
                                            @if($calculation['variance'] > 0)+@endif{{ number_format($calculation['variance'], 2) }}
                                        </td>
                                        <td class="text-end">
                                            @if($calculation['total_nozzle_sales'] > 0)
                                                @if($calculation['variance_percent'] > 0)+@endif{{ number_format($calculation['variance_percent'], 2) }}%
                                            @else N/A @endif
                                        </td>
                                        <td><span class="badge bg-{{ $calculation['status_class'] }}">{{ $calculation['status'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tank-wise Detailed Analysis -->
            <h5 class="mb-3 mt-4">Tank-wise Detailed Analysis</h5>
            @foreach($tankCalculations as $tankId => $calculation)
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">{{ $calculation['tank_name'] }} - {{ $calculation['product_name'] }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="summary-card">
                                    <h6>Stock Movement (Liters)</h6>
                                    <table class="table table-sm table-bordered">
                                        <tr><td width="60%">Opening Stock:</td><td class="text-end">{{ number_format($calculation['opening_stock'], 2) }}</td></tr>
                                        <tr><td width="60%">Closing Stock:</td><td class="text-end">{{ number_format($calculation['closing_stock'], 2) }}</td></tr>
                                        <tr><td width="60%">Physical Usage:</td><td class="text-end">{{ number_format($calculation['physical_usage'], 2) }}</td></tr>
                                        @if($calculation['oil_purchased'] > 0)
                                        <tr><td width="60%">Oil Purchased:</td><td class="text-end text-info">+{{ number_format($calculation['oil_purchased'], 2) }}</td></tr>
                                        @endif
                                        <tr><td width="60%"><strong>Adjusted Usage:</strong></td><td class="text-end"><strong>{{ number_format($calculation['adjusted_physical_usage'], 2) }}</strong></td></tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="summary-card">
                                    <h6>Sales & Variance Analysis</h6>
                                    <table class="table table-sm table-bordered">
                                        <tr><td width="60%">Nozzle Sales:</td><td class="text-end">{{ number_format($calculation['nozzle_sales_liters'], 2) }} L</td></tr>
                                        <tr><td width="60%">Reset Sales:</td><td class="text-end">{{ number_format($calculation['reset_sales_liters'], 2) }} L</td></tr>
                                        <tr><td width="60%"><strong>Total Sales:</strong></td><td class="text-end"><strong>{{ number_format($calculation['total_nozzle_sales'], 2) }} L</strong></td></tr>
                                        <tr class="{{ $calculation['gain_loss_class'] == 'success' ? 'table-success' : ($calculation['gain_loss_class'] == 'danger' ? 'table-danger' : '') }}">
                                            <td width="60%"><strong>Variance:</strong></td>
                                            <td class="text-end">
                                                <strong>{{ number_format($calculation['variance'], 2) }} L</strong><br>
                                                <small>{{ $calculation['variance_text'] }}</small><br>
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
                    </div>
                </div>
            @endforeach
        @endif

        <!-- Financial Summary -->
        @if(isset($financialSummary))
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Financial Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-md-4">
                            <div class="summary-card">
                                <h4 class="text-success">Rs. {{ number_format($financialSummary['total_revenue'] ?? 0, 2) }}</h4>
                                <p class="mb-0">Total Revenue</p>
                                <small>Fuel: Rs. {{ number_format($financialSummary['fuel_sales'] ?? 0, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card">
                                <h4 class="text-danger">Rs. {{ number_format($financialSummary['total_expenses'] ?? 0, 2) }}</h4>
                                <p class="mb-0">Total Expenses</p>
                                <small>Oil Purchase: Rs. {{ number_format($financialSummary['oil_purchase'] ?? 0, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card">
                                <h4 class="text-success">Rs. {{ number_format($financialSummary['net_income'] ?? 0, 2) }}</h4>
                                <p class="mb-0">Net Income</p>
                                <small>Fuel Card: Rs. {{ number_format($cashFlow->fuelcard ?? 0, 2) }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="summary-card">
                                <h5>Rs. {{ number_format($financialSummary['cash_handover'] ?? 0, 2) }}</h5>
                                <p class="mb-0">Opening Balance</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <h5>Rs. {{ number_format($financialSummary['cash_in_hand'] ?? 0, 2) }}</h5>
                                <p class="mb-0">Closing Balance</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <h5>Rs. {{ number_format($financialSummary['cash_in_bank'] ?? 0, 2) }}</h5>
                                <p class="mb-0">Cash in Bank</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <h5>Rs. {{ number_format($financialSummary['total_cash_balance'] ?? 0, 2) }}</h5>
                                <p class="mb-0">Total Balance</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="mt-4 pt-3 border-top text-center">
            <p class="text-muted small mb-0">Generated by Pump360 • {{ now()->format('d M Y, H:i:s') }}</p>
            <p class="text-muted small">This is a computer generated report</p>
        </div>
    </div>

    <!-- PDF Controls -->
    <div class="controls no-print">
       
       
        <button class="btn-pdf" onclick="generatePDF()">
            📄 Download PDF Report
        </button>
    </div>

    <script>
        async function generatePDF() {
            // Show loading overlay
            const overlay = document.getElementById('loadingOverlay');
            const loadingStatus = document.getElementById('loadingStatus');
            const progressBar = document.getElementById('progressBar');
            
            overlay.style.display = 'flex';
            loadingStatus.textContent = 'Capturing content...';
            progressBar.style.width = '20%';
            progressBar.textContent = '20%';
            
            const element = document.getElementById('pdfContent');
            
            try {
                // Capture the element as canvas with high quality
                loadingStatus.textContent = 'Rendering page...';
                progressBar.style.width = '40%';
                progressBar.textContent = '40%';
                
                const canvas = await html2canvas(element, {
                    scale: 3, // Higher scale for better quality
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff',
                    windowWidth: element.scrollWidth,
                    windowHeight: element.scrollHeight,
                    onclone: (clonedDoc, element) => {
                        // Ensure cloned document has proper styling
                        const clonedElement = clonedDoc.getElementById('pdfContent');
                        if (clonedElement) {
                            clonedElement.style.padding = '20px';
                        }
                    }
                });
                
                loadingStatus.textContent = 'Processing PDF...';
                progressBar.style.width = '70%';
                progressBar.textContent = '70%';
                
                const imgData = canvas.toDataURL('image/png', 1.0);
                const { jsPDF } = window.jspdf;
                
                // A4 dimensions in mm (landscape)
                const pdfWidth = 297; // A4 width in mm
                const pdfHeight = 210; // A4 height in mm
                
                // Calculate image dimensions to fit PDF
                const imgWidth = pdfWidth;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                
                let pdf;
                let position = 0;
                let pageHeight = pdfHeight;
                let heightLeft = imgHeight;
                
                // Create first page
                pdf = new jsPDF({
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'landscape'
                });
                
                // Add first page image
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
                
                // Add more pages if content overflows
                let pageCount = 1;
                while (heightLeft > 0) {
                    pdf.addPage();
                    position = heightLeft - imgHeight;
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                    pageCount++;
                    
                    loadingStatus.textContent = `Processing page ${pageCount}...`;
                    progressBar.style.width = `${70 + (pageCount * 5)}%`;
                    progressBar.textContent = `${70 + (pageCount * 5)}%`;
                }
                
                loadingStatus.textContent = 'Saving PDF...';
                progressBar.style.width = '95%';
                progressBar.textContent = '95%';
                
                // Save the PDF
                const filename = `Shift_Stock_Report_{{ $shift->id }}_{{ now()->format('Ymd_His') }}.pdf`;
                pdf.save(filename);
                
                // Complete
                progressBar.style.width = '100%';
                progressBar.textContent = '100%';
                loadingStatus.textContent = 'Complete!';
                
                // Hide overlay after delay
                setTimeout(() => {
                    overlay.style.display = 'none';
                    progressBar.style.width = '0%';
                    progressBar.textContent = '0%';
                }, 1500);
                
            } catch (error) {
                console.error('PDF Generation Error:', error);
                loadingStatus.textContent = 'Error generating PDF';
                progressBar.style.width = '100%';
                progressBar.style.background = '#dc3545';
                progressBar.textContent = 'Error';
                
                setTimeout(() => {
                    overlay.style.display = 'none';
                    alert('Error generating PDF. Please try again.');
                    progressBar.style.width = '0%';
                    progressBar.textContent = '0%';
                    progressBar.style.background = '#28a745';
                }, 2000);
            }
        }
        
        // Optional: Auto-generate PDF when page loads with a query parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto') === 'true') {
            setTimeout(() => {
                generatePDF();
            }, 1000);
        }
    </script>
</body>
</html>