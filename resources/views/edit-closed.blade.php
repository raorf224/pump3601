@extends('partials.layouts.master')

@section('title', 'Edit Closed Shift | ' . Auth::user()->full_name)
@section('title-sub', 'Employee')
@section('pagetitle', 'Edit Closed Shift')

@section('css')
    <style>
        .required-label:after {
            content: "*";
            color: red;
        }
        .warning-banner {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .readonly-bg {
            background-color: #e9ecef;
        }
        .is-invalid {
            border-color: #dc3545 !important;
        }
        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div class="card shadow-sm">
                <div class="card-body">

                    <!-- Warning Banner -->
                    <div class="warning-banner">
                        <i class="bi bi-exclamation-triangle-fill me-2" style="color: #ffc107;"></i>
                        <strong>⚠️ One-time Edit Only!</strong> You can edit this shift only once. Please review all changes carefully.
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Edit Closed Shift</h4>
                        <a href="/shifts" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Shifts
                        </a>
                    </div>

                    <form id="edit_shift_form">
                        <input type="hidden" name="shift_id" id="shift_id" value="{{ $shift->id }}">
                        <input type="hidden" name="shift_start_time" id="shift_start_time" value="{{ $shift->start_time }}">

                        <!-- Shift Information -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Shift Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Station</label>
                                        <input type="text" class="form-control" value="{{ $shift->station_name }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Shift</label>
                                        <input type="text" class="form-control" value="{{ $shift->shift_no == 1 ? 'Day' : 'Night' }}" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Shift Incharge</label>
                                        <input type="text" class="form-control" value="{{ $shift->shift_incharger_name }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Cash Handover</label>
                                        <input type="text" class="form-control readonly-bg" value="{{ number_format($shift->cash_handover, 2) }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Start Time</label>
                                        <input type="text" class="form-control readonly-bg" value="{{ \Carbon\Carbon::parse($shift->start_time)->format('Y-m-d H:i:s') }}" readonly>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <label class="form-label required-label">End Time</label>
                                        <input type="datetime-local" class="form-control" name="end_time" id="end_time"
                                            value="{{ \Carbon\Carbon::parse($shift->end_time)->format('Y-m-d\TH:i') }}" required>
                                        <div class="invalid-feedback">End time is required and must be after start time</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label required-label">Cash Return</label>
                                        <input type="number" name="cash_return" id="cash_return" class="form-control"
                                            step="0.01" value="{{ $shift->cash_return }}" required>
                                        <div class="invalid-feedback">Cash return is required</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <input type="text" class="form-control" value="Closed" readonly style="background-color: #fff3cd;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tank Dip Readings with Validation -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Tank Dip Readings</h6>
                            </div>
                            <div class="card-body">
                                @foreach($tankDips as $dip)
                                @php
                                    $tankName = $dip->tank->name ?? 'N/A';
                                    $productName = $dip->tank->product_name ?? 'N/A';
                                    $capacity = $dip->tank->capacity ?? 0;
                                    $currentLevel = $dip->dip_in_liters ?? 0;
                                @endphp
                                <div class="row mb-3 tank-dip-row" data-tank-id="{{ $dip->tank_id }}">
                                    <div class="col-md-2">
                                        <label class="form-label">Tank Name</label>
                                        <input type="text" class="form-control" value="{{ $tankName }}" readonly>
                                        <small class="text-muted">Capacity: {{ number_format($capacity, 2) }} L</small>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Product</label>
                                        <input type="text" class="form-control" value="{{ $productName }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Previous Dip (mm)</label>
                                        <input type="number" class="form-control" value="{{ $dip->dip_mm }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Previous Dip (Liters)</label>
                                        <input type="number" class="form-control" value="{{ $currentLevel }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label required-label">New Dip (mm)</label>
                                        <input type="number" class="form-control tank-dip-mm" value="{{ $dip->dip_mm }}" step="0.01" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label required-label">New Dip (Liters)</label>
                                        <input type="number" class="form-control tank-dip-liters" value="{{ $currentLevel }}" 
                                            step="0.01" max="{{ $capacity }}" data-current-level="{{ $currentLevel }}" required>
                                        <small class="text-muted">Max: {{ number_format($capacity, 2) }} L</small>
                                        <div class="invalid-feedback">Dip cannot exceed current level ({{ $currentLevel }} L)</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Nozzle Readings with Validation -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Nozzle Readings</h6>
                            </div>
                            <div class="card-body">
							
                                @foreach($nozzleReadings as $reading)
                                <div class="row mb-3 nozzle-reading-row" data-nozzle-id="{{ $reading->nozzle_id }}" data-product-id="{{ $reading->product_id ?? '' }}">
                                    <div class="col-md-3">
                                        <label class="form-label">Nozzle Name</label>
                                        <input type="text" class="form-control" value="{{ $reading->nozzle_name ?? 'N/A' }}" readonly>
                                        <small>Dispenser: {{ $reading->dispenser_name ?? 'N/A' }}</small>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Product</label>
                                        <input type="text" class="form-control nozzle-product" value="{{ $reading->product_name ?? 'N/A' }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Opening Reading</label>
                                        <input type="number" class="form-control nozzle-opening" value="{{ $reading->opening_reading }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label required-label">Closing Reading</label>
                                        <input type="number" class="form-control nozzle-closing" value="{{ $reading->closing_reading }}" 
                                            step="0.01" data-opening="{{ $reading->opening_reading }}" required>
                                        <div class="invalid-feedback">Closing reading cannot be less than opening reading</div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Total Dispensed</label>
                                        <input type="number" class="form-control nozzle-total" value="{{ $reading->total_dispensed }}" readonly>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Cash Flow Summary -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Cash Flow Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr><th>Product</th><th>Rate</th><th>Volume (L)</th><th>Amount</th></tr>
                                        </thead>
                                        <tbody id="product_summary_body"></tbody>
                                        <tfoot class="table-dark">
                                            <tr><td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                                            <td><strong id="grand_total_amount">0.00</strong></td>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Total Sales Amount</label>
                                        <input type="number" class="form-control" id="total_cash" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label required-label">In Hand (Cash)</label>
                                        <input type="number" class="form-control" id="in_hand" name="in_hand" step="0.01" 
                                            value="{{ $cashFlow->in_hand ?? 0 }}" required>
                                        <div class="invalid-feedback">In Hand amount is required</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label required-label">In Bank</label>
                                        <input type="number" class="form-control" id="in_bank" name="in_bank" step="0.01" 
                                            value="{{ $cashFlow->in_bank ?? 0 }}" required>
                                        <div class="invalid-feedback">In Bank amount is required</div>
                                    </div>
                                </div>
                                <div class="alert alert-success mt-3" id="cash_validation_msg">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <span id="validation_text">Cash distribution verified</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="/shifts" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-warning" id="update_shift_btn">
                                <span class="spinner-border spinner-border-sm d-none" role="status" id="save_loading"></span>
                                Update Shift
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const SHIFT_ID = "{{ $shift->id }}";
        const SHIFT_START_TIME = "{{ $shift->start_time }}";
        const CASH_HANDOVER = parseFloat("{{ $shift->cash_handover }}") || 0;
        const STATION_ID = "{{ $shift->station_id }}";

        $(document).ready(function() {

            // ==================== TANK DIP VALIDATION ====================
            $(document).on('input', '.tank-dip-liters', function() {
                const newDip = parseFloat($(this).val()) || 0;
                const currentLevel = parseFloat($(this).data('current-level')) || 0;
                const tankRow = $(this).closest('.tank-dip-row');
                
                if (newDip > currentLevel) {
                    $(this).addClass('is-invalid');
                    $(this).siblings('.invalid-feedback').show();
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').hide();
                }
            });

            // ==================== NOZZLE CLOSING READING VALIDATION ====================
            $(document).on('input', '.nozzle-closing', function() {
                const opening = parseFloat($(this).data('opening')) || 0;
                const closing = parseFloat($(this).val()) || 0;
                const totalField = $(this).closest('.nozzle-reading-row').find('.nozzle-total');
                
                if (closing >= opening) {
                    totalField.val((closing - opening).toFixed(2));
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').hide();
                } else {
                    totalField.val('');
                    $(this).addClass('is-invalid');
                    $(this).siblings('.invalid-feedback').show();
                }
                calculateCashFlow();
            });

            // ==================== END TIME VALIDATION ====================
            function validateEndTime() {
                const startTime = SHIFT_START_TIME;
                const endTime = $('#end_time').val();
                
                if (!endTime) return true;
                
                const start = new Date(startTime);
                const end = new Date(endTime);
                
                if (end <= start) {
                    $('#end_time').addClass('is-invalid');
                    $('#end_time').siblings('.invalid-feedback').text('End time must be after start time');
                    return false;
                } else {
                    $('#end_time').removeClass('is-invalid');
                    return true;
                }
            }
            
            $('#end_time').on('change', function() {
                validateEndTime();
                calculateCashFlow();
            });

            // ==================== CASH RETURN VALIDATION ====================
            $('#cash_return').on('input', function() {
                const cashReturn = parseFloat($(this).val()) || 0;
                if (cashReturn <= 0) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // ==================== FETCH PRODUCT RATE ====================
            function fetchProductRate(productId, productName, callback) {
                const dateParam = encodeURIComponent(SHIFT_START_TIME);
                const url = `/api/product-price/${STATION_ID}/${productId}/${dateParam}`;
                
                $.ajax({
                    url: url,
                    method: "GET",
                    success: function(response) {
                        if (response && response.price) {
                            callback(parseFloat(response.price));
                        } else {
                            callback(0);
                        }
                    },
                    error: function() {
                        callback(0);
                    }
                });
            }

            // ==================== CALCULATE CASH FLOW ====================
            function calculateCashFlow() {
                const productMap = new Map();
                
                $('.nozzle-reading-row').each(function() {
                    const productName = $(this).find('.nozzle-product').val() || 'Unknown';
                    const productId = $(this).data('product-id');
                    const opening = parseFloat($(this).find('.nozzle-opening').val()) || 0;
                    const closing = parseFloat($(this).find('.nozzle-closing').val()) || 0;
                    
                    if (closing > opening) {
                        const volume = closing - opening;
                        if (!productMap.has(productName)) {
                            productMap.set(productName, {
                                productId: productId,
                                volume: 0,
                                rate: 0,
                                amount: 0
                            });
                        }
                        productMap.get(productName).volume += volume;
                    }
                });
                
                if (productMap.size === 0) {
                    const grandTotal = CASH_HANDOVER;
                    $('#total_cash').val(grandTotal.toFixed(2));
                    $('#grand_total_amount').text(grandTotal.toFixed(2));
                    $('#product_summary_body').html(`
                        <tr><td colspan="4" class="text-center text-muted">No nozzle sales recorded</td></tr>
                        <tr class="table-primary"><td colspan="3" class="text-end"><strong>1. Opening Cash Handover:</strong></td>
                        <td><strong>${CASH_HANDOVER.toFixed(2)}</strong></td></tr>
                        <tr class="table-secondary"><td colspan="3" class="text-end"><strong>2. Total Nozzle Sales:</strong></td>
                        <td><strong>0.00</strong></td></tr>
                        <tr class="table-dark"><td colspan="3" class="text-end"><strong>💵 TOTAL AVAILABLE CASH (1+2):</strong></td>
                        <td><strong>${grandTotal.toFixed(2)}</strong></td></tr>
                    `);
                    if (!$('#in_hand').val() || parseFloat($('#in_hand').val()) === 0) {
                        $('#in_hand').val(grandTotal.toFixed(2));
                        $('#cash_return').val(grandTotal.toFixed(2));
                    }
                    validateCashDistribution();
                    return;
                }
                
                const products = Array.from(productMap.keys());
                let completed = 0;
                
                products.forEach(productName => {
                    const productData = productMap.get(productName);
                    if (productData.productId) {
                        fetchProductRate(productData.productId, productName, function(rate) {
                            productData.rate = rate;
                            productData.amount = productData.volume * rate;
                            completed++;
                            if (completed === products.length) {
                                renderCashFlow(productMap);
                            }
                        });
                    } else {
                        productData.rate = 0;
                        productData.amount = 0;
                        completed++;
                        if (completed === products.length) {
                            renderCashFlow(productMap);
                        }
                    }
                });
            }
            
            function renderCashFlow(productMap) {
                const container = $("#product_summary_body");
                container.empty();
                
                let totalNozzleSales = 0;
                const sortedProducts = Array.from(productMap.keys()).sort();
                
                sortedProducts.forEach(productName => {
                    const data = productMap.get(productName);
                    if (data.volume > 0) {
                        totalNozzleSales += data.amount;
                        container.append(`
                            <tr>
                                <td>${productName}</td>
                                <td>${data.rate.toFixed(2)}</td>
                                <td>${data.volume.toFixed(2)} L</td>
                                <td>${data.amount.toFixed(2)}</td>
                            </tr>
                        `);
                    }
                });
                
                const grandTotal = CASH_HANDOVER + totalNozzleSales;
                
                container.append(`
                    <tr class="table-primary">
                        <td colspan="3" class="text-end"><strong>1. Opening Cash Handover:</strong></td>
                        <td><strong>${CASH_HANDOVER.toFixed(2)}</strong></td>
                    </tr>
                    <tr class="table-secondary">
                        <td colspan="3" class="text-end"><strong>2. Total Nozzle Sales:</strong></td>
                        <td><strong>${totalNozzleSales.toFixed(2)}</strong></td>
                    </tr>
                    <tr class="table-dark">
                        <td colspan="3" class="text-end"><strong>💵 TOTAL AVAILABLE CASH (1+2):</strong></td>
                        <td><strong>${grandTotal.toFixed(2)}</strong></td>
                    </tr>
                `);
                
                $('#total_cash').val(grandTotal.toFixed(2));
                $('#grand_total_amount').text(grandTotal.toFixed(2));
                
                const currentInHand = parseFloat($('#in_hand').val()) || 0;
                if (currentInHand === 0 || currentInHand === CASH_HANDOVER) {
                    $('#in_hand').val(grandTotal.toFixed(2));
                    $('#cash_return').val(grandTotal.toFixed(2));
                }
                
                validateCashDistribution();
            }
            
            // ==================== CASH DISTRIBUTION VALIDATION ====================
            function validateCashDistribution() {
                const totalSales = parseFloat($('#total_cash').val()) || 0;
                const inHand = parseFloat($('#in_hand').val()) || 0;
                const inBank = parseFloat($('#in_bank').val()) || 0;
                const distributed = inHand + inBank;
                const difference = Math.abs(distributed - totalSales);
                
                const validationMsg = $("#cash_validation_msg");
                const validationText = $("#validation_text");
                
                if (difference > 0.01) {
                    validationText.html(`⚠️ <strong>Cash Mismatch!</strong><br>Total Sales: ${totalSales.toFixed(2)} | In Hand: ${inHand.toFixed(2)} + In Bank: ${inBank.toFixed(2)} = ${distributed.toFixed(2)}<br>Difference: <strong class="text-danger">${difference.toFixed(2)}</strong>`);
                    validationMsg.removeClass("alert-success").addClass("alert-warning");
                    return false;
                } else {
                    validationText.html(`✅ <strong>Perfect Match!</strong><br>Total Sales: ${totalSales.toFixed(2)} = In Hand: ${inHand.toFixed(2)} + In Bank: ${inBank.toFixed(2)}`);
                    validationMsg.removeClass("alert-warning").addClass("alert-success");
					console.log("check")
                    return true;
                }
            }
            
            $('#in_hand, #in_bank').on('input', function() {
                const inHand = parseFloat($('#in_hand').val()) || 0;
                $('#cash_return').val(inHand.toFixed(2));
                validateCashDistribution();
            });
            
            // ==================== VALIDATE ALL REQUIRED FIELDS BEFORE SUBMIT ====================
            function validateAllFields() {
                let isValid = true;
                
                // Validate Tank Dips
                $('.tank-dip-row').each(function() {
                    const dipLiters = $(this).find('.tank-dip-liters');
                    const dipMm = $(this).find('.tank-dip-mm');
                    
                    if (!dipLiters.val() || dipLiters.val().trim() === '') {
                        dipLiters.addClass('is-invalid');
						console.log("dip")
                        isValid = false;
                    } else {
                        dipLiters.removeClass('is-invalid');
                    }
                    
                    if (!dipMm.val() || dipMm.val().trim() === '') {
                        dipMm.addClass('is-invalid');
						console.log("dipmm")
						
                        isValid = false;
                    } else {
                        dipMm.removeClass('is-invalid');
                    }
                });
                
                // Validate Nozzle Closing Readings
                $('.nozzle-reading-row').each(function() {
                    const closingReading = $(this).find('.nozzle-closing');
                    const opening = parseFloat(closingReading.data('opening')) || 0;
                    const closing = parseFloat(closingReading.val()) || 0;
                    
                    if (!closingReading.val() || closingReading.val().trim() === '') {
                        closingReading.addClass('is-invalid');
						
                        isValid = false;
                    } else if (closing < opening) {
                        closingReading.addClass('is-invalid');
						console.log("closing")
						
                        isValid = false;
                    } else {
                        closingReading.removeClass('is-invalid');
                    }
                });
                
                // Validate Cash Return
                const cashReturn = $('#cash_return');
                if (!cashReturn.val() || cashReturn.val().trim() === '' || parseFloat(cashReturn.val()) <= 0) {
                    cashReturn.addClass('is-invalid');
						console.log("cash")
					
                    isValid = false;
                } else {
                    cashReturn.removeClass('is-invalid');
                }
                
                // Validate End Time
                if (!validateEndTime()) {
						console.log("time")
					
                    isValid = false;
                }
                
                // Validate Cash Distribution
               // if (!validateCashDistribution()) {
				//		console.log("distr")
					
            //        isValid = false;
              //  }
                
                return isValid;
            }
            
            // ==================== UPDATE SHIFT FORM SUBMIT ====================
            $('#edit_shift_form').on('submit', function(e) {
                e.preventDefault();
                
                // ✅ Validate all fields first
                if (!validateAllFields()) {
                    showToast('Please fill all required fields correctly!', 'error');
                    return;
                }
                
                const endTime = $('#end_time').val();
                const cashReturn = $('#cash_return').val();
                const inHand = $('#in_hand').val();
                const inBank = $('#in_bank').val();
                const totalSales = $('#total_cash').val();
                
                const tankDips = [];
                $('.tank-dip-row').each(function() {
                    tankDips.push({
                        tank_id: $(this).data('tank-id'),
                        dip_mm: parseFloat($(this).find('.tank-dip-mm').val()),
                        dip_in_liters: parseFloat($(this).find('.tank-dip-liters').val())
                    });
                });
                
                const nozzleReadings = [];
                $('.nozzle-reading-row').each(function() {
                    const closingReading = parseFloat($(this).find('.nozzle-closing').val());
                    if (!isNaN(closingReading) && closingReading > 0) {
                        nozzleReadings.push({
                            nozzle_id: $(this).data('nozzle-id'),
                            closing_reading: closingReading
                        });
                    }
                });
                
                const payload = {
                    end_time: endTime,
                    cash_return: parseFloat(cashReturn),
                    tank_dips: tankDips,
                    nozzle_readings: nozzleReadings,
                    cash_flow: {
                        total_cash: parseFloat(totalSales),
                        in_hand: parseFloat(inHand),
                        in_bank: parseFloat(inBank)
                    }
                };
                
                const submitBtn = $('#update_shift_btn');
                const originalText = submitBtn.html();
                submitBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Updating...');
                submitBtn.prop('disabled', true);
                
                $.ajax({
                    url: `/api/shifts/${SHIFT_ID}/update-closed`,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    success: function(response) {
                        if (response.success) {
                            showToast(response.message, 'success');
                            setTimeout(() => { window.location.href = '/shifts'; }, 2000);
                        } else {
                            showToast(response.message, 'error');
                            submitBtn.html(originalText).prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Error updating shift!';
                        showToast(errorMsg, 'error');
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });
            
            function showToast(message, type) {
                const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
                const toastHtml = `<div class="position-fixed top-0 end-0 p-3" style="z-index: 1055"><div class="toast align-items-center ${bgClass} text-white border-0" role="alert"><div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div></div>`;
                $('body').append(toastHtml);
                const toast = $('.toast').last();
                const bsToast = new bootstrap.Toast(toast[0], { delay: 3000 });
                bsToast.show();
                toast.on('hidden.bs.toast', () => toast.remove());
            }
            
            // Initial calculation
            setTimeout(() => { calculateCashFlow(); }, 500);
        });
    </script>
@endsection