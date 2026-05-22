@extends('partials.layouts.master')

@section('title', 'Close Shift | ' . Auth::user()->full_name)
@section('title-sub', 'Employee')
@section('pagetitle', 'Close Shift')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <style>
        .reset-fields-container {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
        }

        .validation-message {
            font-size: 0.8rem;
        }

        .loading-spinner {
            display: none;
        }

        /* Highlight changes */
        .cash-return-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            border-left: 4px solid #0d6efd;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- ✅ Toast Container (top-right) -->
                    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
                        <div id="toastContainer"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Close Shift</h4>
                        <a href="/shifts" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Shifts
                        </a>
                    </div>

                    <form id="close_shift_form">
                        <input type="hidden" name="shift_id" id="close_shift_id" value="{{ request('shift_id') }}">
                        <input type="hidden" name="shift_start_time" id="shift_start_time">

                        <!-- ✅ SHIFT INFORMATION SECTION -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Shift Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label required-label">End Time</label>
                                                <input type="datetime-local" class="form-control" name="end_time"
                                                    id="close_end_time" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Shift Status</label>
                                                <input type="text" class="form-control" value="Open" readonly
                                                    style="background-color: #fff3cd;">
                                            </div>

                                            <!-- ✅ CASH RETURN FIELD -->
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold required-label">Cash Return</label>
                                                <input type="number" name="cash_return" id="cash_return"
                                                    class="form-control" min="0" step="0.01" placeholder="0.00" readonly
                                                    style="background-color: #f8f9fa;">
                                                <small class="text-muted">Auto-filled from In Hand amount</small>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">&nbsp;</label>
                                                <div class="d-grid">
                                                    <button type="button" class="btn btn-primary" id="save_close_shift">
                                                        <span class="spinner-border spinner-border-sm d-none" role="status"
                                                            id="save_loading"></span>
                                                        Save & Close Shift
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="shift_info_container">
                                            <!-- Shift info will load here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ TANK DIPS SECTION -->
                        <div class="card mb-4">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Tank Dip Readings</h6>
                                <div class="loading-spinner" id="tank_loading">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span class="ms-2">Loading tanks...</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="tank_dips_container">
                                    <div class="text-center">
                                        <div class="spinner-border" role="status"></div>
                                        <p>Loading tanks...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ NOZZLE READINGS SECTION -->
                        <div class="card mb-4">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Nozzle Readings</h6>
                                <div class="loading-spinner" id="nozzle_loading">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span class="ms-2">Loading nozzles...</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="nozzle_readings_container">
                                    <div class="text-center">
                                        <div class="spinner-border" role="status"></div>
                                        <p>Loading nozzles...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ CASH FLOW SUMMARY SECTION -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Cash Flow Summary</h6>
                            </div>
                            <div class="card-body">
                                <!-- Loading State -->
                                <div id="cash_flow_loading" class="text-center">
                                    <div class="spinner-border" role="status"></div>
                                    <p>Calculating sales summary...</p>
                                </div>

                                <!-- Summary Table -->
                                <div id="cash_flow_summary" style="display: none;">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Rate (per liter)</th>
                                                    <th>Total Volume (Liters)</th>
                                                    <th>Total Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="product_summary_body">
                                                <!-- Product rows will be populated here -->
                                            </tbody>
                                            <tfoot class="table-dark">
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                                                    <td><strong id="grand_total_amount">0.00</strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <!-- Cash Distribution Inputs -->
                                    <div class="row mt-4">
                                        <div class="col-md-3">
                                            <label class="form-label required-label">Total Sales Amount</label>
                                            <input type="number" class="form-control" id="total_cash" readonly
                                                style="background-color: #f8f9fa;">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label required-label">In Hand (Cash)</label>
                                            <input type="number" class="form-control" id="in_hand" name="in_hand" min="0"
                                                required>
                                            <div class="invalid-feedback">In Hand amount is required</div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label required-label">In Bank</label>
                                            <input type="number" class="form-control" id="in_bank" name="in_bank" min="0"
                                                value="0">
                                            <div class="invalid-feedback">In Bank amount is required</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check mt-4 pt-2">
                                                <input class="form-check-input" type="checkbox"
                                                    id="transfer_to_bank_checkbox">
                                                <label class="form-check-label" for="transfer_to_bank_checkbox">
                                                    <strong>Transfer to Bank?</strong>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-check mt-4 pt-2">
                                                <input class="form-check-input" type="checkbox"
                                                    id="credit_to_driver_checkbox">
                                                <label class="form-check-label" for="credit_to_driver_checkbox">
                                                    <strong>Credit to Driver?</strong>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ✅ BANK TRANSFER SECTION (Initially Hidden) -->
                                    <div class="row mt-3" id="bank_transfer_section" style="display: none;">
                                        <div class="col-md-12">
                                            <div class="card border-primary">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0">Bank Transfer Details</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label class="form-label required-label">Bank Account</label>
                                                            <select class="form-control" id="bank_account_select">
                                                                <option value="">Select Bank Account...</option>
                                                            </select>
                                                            <div class="invalid-feedback">Please select a bank account</div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label required-label">Transfer Amount</label>
                                                            <input type="number" class="form-control" id="transfer_amount"
                                                                min="0" step="0.01">
                                                            <small class="text-muted">Amount to transfer to bank</small>
                                                            <div class="invalid-feedback">Transfer amount is required</div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">New In Bank Total</label>
                                                            <input type="number" class="form-control" id="new_in_bank_total"
                                                                readonly style="background-color: #f8f9fa;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ✅ CREDIT TO DRIVER SECTION (Initially Hidden) -->
                                    <div class="row mt-3" id="credit_driver_section" style="display: none;">
                                        <div class="col-md-12">
                                            <div class="card border-success">
                                                <div
                                                    class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">Credit to Driver Details</h6>
                                                    <button type="button" class="btn btn-light btn-sm"
                                                        id="add_another_driver">
                                                        <i class="bi bi-plus-circle me-1"></i> Add Another
                                                    </button>
                                                </div>
                                                <div class="card-body">
                                                    <div id="credit_driver_forms_container">
                                                        <!-- First form will be added here by JavaScript -->
                                                    </div>

                                                    <!-- Template for driver credit form (hidden) -->
                                                    <template id="credit_driver_form_template">
                                                        <div class="driver-credit-form mb-4 p-3 border rounded">
                                                            <div class="row">
                                                                <div class="col-md-12 mb-3">
                                                                    <h6 class="text-primary">Driver Credit Entry <span
                                                                            class="form-number">#1</span>
                                                                        <button type="button"
                                                                            class="btn btn-danger btn-sm float-end remove-driver-form">
                                                                            <i class="bi bi-trash"></i> Remove
                                                                        </button>
                                                                    </h6>
                                                                </div>

                                                                <!-- Station Selection -->
                                                                <div class="col-md-3">
                                                                    <label class="form-label required-label">Station</label>
                                                                    <select class="form-control credit-station-select"
                                                                        required>
                                                                        <option value="">Select Station...</option>
                                                                    </select>
                                                                    <div class="invalid-feedback">Please select a station
                                                                    </div>
                                                                </div>

                                                                <!-- Customer Selection -->
                                                                <div class="col-md-3">
                                                                    <label
                                                                        class="form-label required-label">Customer</label>
                                                                    <select class="form-control credit-customer-select"
                                                                        required>
                                                                        <option value="">Select Customer...</option>
                                                                    </select>
                                                                    <div class="invalid-feedback">Please select a customer
                                                                    </div>
                                                                </div>

                                                                <!-- Amount Given To -->
                                                                <div class="col-md-3">
                                                                    <label class="form-label required-label">Amount Given
                                                                        To</label>
                                                                    <div class="mt-2">
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input amount-given-to"
                                                                                type="radio"
                                                                                name="AMOUNT_GIVEN_TO_PLACEHOLDER"
                                                                                value="Driver" checked>
                                                                            <label class="form-check-label">Driver</label>
                                                                        </div>
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input amount-given-to"
                                                                                type="radio"
                                                                                name="AMOUNT_GIVEN_TO_PLACEHOLDER"
                                                                                value="Vehicle">
                                                                            <label class="form-check-label">Vehicle</label>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Amount -->
                                                                <div class="col-md-3">
                                                                    <label class="form-label required-label">Amount</label>
                                                                    <input type="number" class="form-control credit-amount"
                                                                        step="0.01" min="0" placeholder="0.00" required>
                                                                    <div class="invalid-feedback">Amount is required</div>
                                                                </div>
                                                            </div>

                                                            <!-- DRIVER CNIC SECTION (Visible by default) -->
                                                            <div class="row mt-2 driver-cnic-section">
                                                                <div class="col-md-4">
                                                                    <label class="form-label required-label">Driver
                                                                        CNIC</label>
                                                                    <input type="number" class="form-control credit-cnic"
                                                                        placeholder="Enter 13-digit CNIC" maxlength="13"
                                                                        min="1000000000000" max="9999999999999">
                                                                    <small class="text-muted">13 digits without
                                                                        dashes</small>
                                                                    <div class="invalid-feedback">Valid 13-digit CNIC is
                                                                        required</div>
                                                                </div>
                                                            </div>

                                                            <!-- VEHICLE NUMBER SECTION (Initially Hidden) -->
                                                            <div class="row mt-2 vehicle-number-section"
                                                                style="display: none;">
                                                                <div class="col-md-4">
                                                                    <label class="form-label required-label">Vehicle
                                                                        Number</label>
                                                                    <input type="text"
                                                                        class="form-control credit-vehicle-number"
                                                                        placeholder="Enter vehicle number">
                                                                    <div class="invalid-feedback">Vehicle number is required
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Validation Message -->
                                    <div class="alert alert-warning mt-3" id="cash_validation_msg" style="display: none;">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <span id="validation_text"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        const AUTH_USER_ID = "{{ Auth::id() }}";

        document.addEventListener("DOMContentLoaded", function () {
            const shiftId = $("#close_shift_id").val();

            if (!shiftId) {
                showToast("Shift ID not found! Please go back and try again.", "error");
                return;
            }

            // Set current datetime as end time
            const now = new Date();
            const localDateTime = now.toISOString().slice(0, 16);
            $("#close_end_time").val(localDateTime);

            // Load shift details and data
            loadShiftDetails(shiftId);

            // ✅ Load tanks and nozzles with promise
            loadTanksAndNozzlesForShift(shiftId)
                .then(() => {
                    console.log("Tanks and nozzles loaded successfully");
                })
                .catch(error => {
                    console.error("Error loading tanks and nozzles:", error);
                    showToast("Error loading shift data!", "error");
                });


            // ✅ Toast Function
            function showToast(message, type = "success") {
                const toastId = `toast-${Date.now()}`;
                const bgClass = type === "success" ? "bg-success text-white" : "bg-danger text-white";

                const toastHtml = `
                                                                                                                                    <div id="${toastId}" class="toast align-items-center ${bgClass} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                                                                                                                                        <div class="d-flex">
                                                                                                                                            <div class="toast-body">${message}</div>
                                                                                                                                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                `;

                $("#toastContainer").append(toastHtml);
                const toastElement = document.getElementById(toastId);
                const bsToast = new bootstrap.Toast(toastElement, {
                    delay: 3000
                });
                bsToast.show();

                toastElement.addEventListener("hidden.bs.toast", () => {
                    $(toastElement).remove();
                });
            }

            // Load shift details
            function loadShiftDetails(shiftId) {
                $.ajax({
                    url: getApiUrl(`shifts/${shiftId}`),
                    method: "GET",
                    success: function (shift) {
                        $("#shift_start_time").val(shift.start_time);

                        // Display shift information
                        $("#shift_info_container").html(`
                                                                                                                                            <div class="row">
                                                                                                                                                <div class="col-md-6">
                                                                                                                                                    <strong>Station:</strong> ${shift.station_name || 'N/A'}<br>
                                                                                                                                                    <strong>Shift:</strong> ${shift.shift_no === 1 ? 'Day' : 'Night'}<br>
                                                                                                                                                    <strong>Start Time:</strong> ${shift.start_time}
                                                                                                                                                </div>
                                                                                                                                                <div class="col-md-6">
                                                                                                                                                    <strong>Shift Incharge:</strong> ${shift.shift_incharger_name || 'N/A'}<br>
                                                                                                                                                    <strong>Station ID:</strong> ${shift.station_id || 'N/A'}
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                        `);
                    },
                    error: function (xhr) {
                        console.error("Error fetching shift details:", xhr.responseText);
                        showToast("Error loading shift details!", "error");
                    }
                });
            }

            // ✅ Validate end time is after start time
            function validateEndTime(startTime, endTime) {
                if (!startTime || !endTime) return true;
                const start = new Date(startTime);
                const end = new Date(endTime);
                return end > start;
            }

            // ✅ Validate all required fields before closing shift
            function validateRequiredFields() {
                let isValid = true;
                let errorMessages = [];

                // Validate Tank Dips
                $(".tank-dip-row").each(function (index) {
                    const tankId = $(this).data('tank-id');
                    const tankName = $(this).find('input[type="text"]').first().val();
                    const dipMm = $(this).find('.tank-dip-mm').val();
                    const dipLiters = $(this).find('.tank-dip-liters').val();

                    if (!dipMm || dipMm.trim() === '') {
                        isValid = false;
                        errorMessages.push(`Tank "${tankName}" - Dip in mm is required`);
                        $(this).find('.tank-dip-mm').addClass('is-invalid');
                    } else {
                        $(this).find('.tank-dip-mm').removeClass('is-invalid');
                    }

                    if (!dipLiters || dipLiters.trim() === '') {
                        isValid = false;
                        errorMessages.push(`Tank "${tankName}" - Dip in liters is required`);
                        $(this).find('.tank-dip-liters').addClass('is-invalid');
                    } else {
                        $(this).find('.tank-dip-liters').removeClass('is-invalid');
                    }
                });

                // Validate Nozzle Closing Readings
                $(".nozzle-reading-row").each(function (index) {
                    const nozzleId = $(this).data('nozzle-id');
                    const nozzleName = $(this).find('input[type="text"]').first().val();
                    const closingReading = $(this).find('.nozzle-closing').val();

                    if (!closingReading || closingReading.trim() === '') {
                        isValid = false;
                        errorMessages.push(`Nozzle "${nozzleName}" - Closing reading is required`);
                        $(this).find('.nozzle-closing').addClass('is-invalid');
                    } else {
                        $(this).find('.nozzle-closing').removeClass('is-invalid');
                    }
                });

                // Validate Cash Return
                if (!$("#cash_return").val() || $("#cash_return").val().trim() === '') {
                    isValid = false;
                    errorMessages.push("Cash Return is required");
                    $("#cash_return").addClass('is-invalid');
                } else {
                    $("#cash_return").removeClass('is-invalid');
                }

                if (!isValid) {
                    showToast("Please fill all required fields: " + errorMessages.join(', '), "error");
                }

                return isValid;
            }

            // ✅ Load Tanks and Nozzles for Shift Closing
            function loadTanksAndNozzlesForShift(shiftId) {
                return new Promise((resolve, reject) => {
                    // First get shift details to know station
                    $.ajax({
                        url: getApiUrl(`shifts/${shiftId}`),
                        method: "GET",
                        success: function (shift) {
                            const stationId = shift.station_id;

                            // Load tanks
                            $.ajax({
                                url: getApiUrl(`tanks/station/${stationId}`),
                                method: "GET",
                                success: function (tanks) {
                                    getLastTankDips(tanks).then(tanksWithDips => {
                                        renderTankDips(tanksWithDips);
                                    }).catch(error => {
                                        console.error("Error getting tank dips:", error);
                                    });
                                },
                                error: function (xhr) {
                                    console.error("Error loading tanks:", xhr.responseText);
                                    $("#tank_dips_container").html('<p class="text-danger">Error loading tanks</p>');
                                }
                            });

                            // Load nozzles
                            $.ajax({
                                url: getApiUrl(`nozzles/station/${stationId}`),
                                method: "GET",
                                success: function (nozzles) {
                                    getLastNozzleReadings(nozzles).then(nozzlesWithReadings => {
                                        renderNozzleReadings(nozzlesWithReadings);

                                        // Cash flow calculate karo
                                        setTimeout(() => {
                                            calculateCashFlowSummary();
                                        }, 300);

                                        resolve();
                                    }).catch(error => {
                                        console.error("Error getting nozzle readings:", error);
                                        setTimeout(() => {
                                            calculateCashFlowSummary();
                                        }, 300);
                                        resolve();
                                    });
                                },
                                error: function (xhr) {
                                    console.error("Error loading nozzles:", xhr.responseText);
                                    $("#nozzle_readings_container").html('<p class="text-danger">Error loading nozzles</p>');
                                    resolve();
                                }
                            });
                        },
                        error: function (xhr) {
                            console.error("Error fetching shift details:", xhr.responseText);
                            reject(xhr);
                        }
                    });
                });
            }

            // ✅ Get last tank dips for all tanks
            function getLastTankDips(tanks) {
                const promises = tanks.map(tank => {
                    return new Promise((resolve) => {
                        $.ajax({
                            url: getApiUrl(`tank-dips/last/${tank.id}`),
                            method: "GET",
                            success: function (lastDip) {
                                // ✅ Use tank-dips API for last dip, but keep current_level from tanks API
                                tank.last_dip_mm = lastDip?.dip_mm || tank.current_level_mm || 0;
                                tank.last_dip_liters = lastDip?.dip_in_liters || tank.current_level || 0;
                                resolve(tank);
                            },
                            error: function () {
                                // If no tank-dips record found, use current_level from tanks API
                                tank.last_dip_mm = tank.current_level_mm || 0;
                                tank.last_dip_liters = tank.current_level || 0;
                                resolve(tank);
                            }
                        });
                    });
                });
                return Promise.all(promises);
            }

            // ✅ Get last nozzle readings for all nozzles
            function getLastNozzleReadings(nozzles) {
                const promises = nozzles.map(nozzle => {
                    return new Promise((resolve) => {
                        $.ajax({
                            url: getApiUrl(`shift-nozzle-readings/last-reading/${nozzle.id}`),
                            method: "GET",
                            success: function (response) {
                                if (response.success && response.data) {
                                    nozzle.last_reading = response.data.last_reading || nozzle.intial_meter_reading || 0;
                                } else {
                                    nozzle.last_reading = nozzle.intial_meter_reading || 0;
                                }
                                resolve(nozzle);
                            },
                            error: function () {
                                nozzle.last_reading = nozzle.intial_meter_reading || 0;
                                resolve(nozzle);
                            }
                        });
                    });
                });
                return Promise.all(promises);
            }

            // ✅ Render Tank Dips
            function renderTankDips(tanks) {
                const container = $("#tank_dips_container");
                container.html("");

                if (!Array.isArray(tanks) || tanks.length === 0) {
                    container.html('<p class="text-muted">No tanks found for this station.</p>');
                    return;
                }

                tanks.forEach(tank => {
                    // ✅ IMPORTANT: Tank ka CURRENT LEVEL show karo, na ki capacity
                    const currentLevel = tank.current_level || tank.last_dip_liters || 0;
                    const capacity = tank.capacity || 'N/A';

                    container.append(`
                                                <div class="row mb-3 tank-dip-row" data-tank-id="${tank.id}">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Tank Name</label>
                                                        <input type="text" class="form-control" value="${tank.name}" readonly>
                                                        <small class="text-muted">Current Level: ${currentLevel} L</small>
                                                        <br>
                                                        <small class="text-muted">Capacity: ${capacity} L</small>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Product</label>
                                                        <input type="text" class="form-control" value="${tank.product_name || 'N/A'}" readonly>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Last Dip (mm)</label>
                                                        <input type="number" class="form-control" value="${tank.last_dip_mm}" readonly>
                                                        <small class="text-muted">Previous reading</small>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Last Dip (Liters)</label>
                                                        <input type="number" class="form-control" value="${tank.last_dip_liters || currentLevel}" readonly>
                                                        <small class="text-muted">Previous reading</small>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label required-label">New Dip (mm)</label>
                                                        <input type="number" class="form-control tank-dip-mm" name="tank_dip_mm[${tank.id}]" 
                                                               step="0.01" min="0" placeholder="Enter new dip in mm" required>
                                                        <div class="invalid-feedback">Dip in mm is required</div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label required-label">New Dip (Liters)</label>
                                                        <input type="number" class="form-control tank-dip-liters" name="tank_dip_liters[${tank.id}]" 
                                                               step="0.01" min="0" max="${capacity}" 
                                                               placeholder="Enter new dip in liters" 
                                                               data-current-level="${currentLevel}"
                                                               required>
                                                        <small class="text-muted">Max: ${capacity} L</small>
                                                        <br>
                                                        <small class="text-warning" id="tank-warning-${tank.id}"></small>
                                                        <div class="invalid-feedback">Dip in liters is required</div>
                                                    </div>
                                                </div>
                                            `);
                });
            }

            // ✅ VALIDATION: New dip cannot be greater than current tank level
            $(document).on("input", ".tank-dip-liters", function () {
                const newDip = parseFloat($(this).val()) || 0;
                const currentLevel = parseFloat($(this).data('current-level')) || 0;
                const tankId = $(this).closest('.tank-dip-row').data('tank-id');
                const warningElement = $(`#tank-warning-${tankId}`);

                if (newDip > currentLevel) {
                    warningElement.html(`⚠️ New dip (${newDip}L) > Current level (${currentLevel}L)`);
                    warningElement.css('color', 'red');
                    $(this).addClass('is-invalid');
                } else {
                    warningElement.html('');
                    warningElement.css('color', '');
                    $(this).removeClass('is-invalid');
                }
            });

            // ✅ Render Nozzle Readings
            function renderNozzleReadings(nozzles) {
                const container = $("#nozzle_readings_container");
                container.html("");

                if (!Array.isArray(nozzles) || nozzles.length === 0) {
                    container.html('<p class="text-muted">No nozzles found for this station.</p>');
                    return;
                }

                nozzles.forEach(nozzle => {
                    container.append(`
                                                                                                                                        <div class="row mb-3 nozzle-reading-row" data-nozzle-id="${nozzle.id}">
                                                                                                                                            <div class="col-md-2">
                                                                                                                                                <label class="form-label">Nozzle Name</label>
                                                                                                                                                <input type="text" class="form-control" value="${nozzle.name}" readonly>
                                                                                                                                                <small class="text-muted">Dispenser: ${nozzle.dispenser_name || 'N/A'}</small>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-2">
                                                                                                                                                <label class="form-label">Product</label>
                                                                                                                                                <input type="text" class="form-control" value="${nozzle.product_name || 'N/A'}" readonly>
                                                                                                                                                <small class="text-muted">Tank: ${nozzle.tank_name || 'N/A'}</small>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-2">
                                                                                                                                                <label class="form-label">Opening Reading</label>
                                                                                                                                                <input type="number" class="form-control nozzle-opening" name="nozzle_opening[${nozzle.id}]" 
                                                                                                                                                    value="${nozzle.last_reading}" step="0.01" min="0" readonly>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-2">
                                                                                                                                                <label class="form-label required-label">Closing Reading</label>
                                                                                                                                                <input type="number" class="form-control nozzle-closing" name="nozzle_closing[${nozzle.id}]" 
                                                                                                                                                    step="0.01" min="0" placeholder="Enter closing reading"
                                                                                                                                                    data-opening="${nozzle.last_reading}" required>
                                                                                                                                                <div class="invalid-feedback">Closing reading is required</div>
                                                                                                                                                <small class="text-muted validation-message" id="validation-${nozzle.id}" style="display:none; color: red;"></small>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-2">
                                                                                                                                                <label class="form-label">Total Dispensed</label>
                                                                                                                                                <input type="number" class="form-control nozzle-total" readonly>
                                                                                                                                                <small class="text-muted">Auto calculated</small>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-2">
                                                                                                                                                <label class="form-label">Actions</label>
                                                                                                                                                <div>
                                                                                                                                                    <button class="btn btn-sm reset-nozzle-btn" 
                                                                                                                                                            style="background-color: #20c997; color: white; border: none;"
                                                                                                                                                            data-nozzle-id="${nozzle.id}" 
                                                                                                                                                            data-nozzle-name="${nozzle.name}" 
                                                                                                                                                            data-opening-reading="${nozzle.last_reading}"
                                                                                                                                                            data-product-name="${nozzle.product_name || 'N/A'}"
                                                                                                                                                            data-product-id="${nozzle.product_id || ''}">
                                                                                                                                                            Reset Nozzle
                                                                                                                                                    </button>
                                                                                                                                                </div>
                                                                                                                                            </div>

                                                                                                                                            <!-- ✅ HIDDEN RESET FIELDS -->
                                                                                                                                            <div class="col-12 mt-3 reset-fields-container" id="reset-fields-${nozzle.id}" style="display: none;">
                                                                                                                                                <div class="row">
                                                                                                                                                    <div class="col-md-12">
                                                                                                                                                        <h6 class="text-primary">Nozzle Reset Details</h6>
                                                                                                                                                    </div>
                                                                                                                                                    <div class="col-md-3 mb-3">
                                                                                                                                                        <label class="form-label required-label">Reset Date & Time</label>
                                                                                                                                                        <input type="datetime-local" class="form-control reset-date" 
                                                                                                                                                            data-nozzle-id="${nozzle.id}" required>
                                                                                                                                                    </div>
                                                                                                                                                    <div class="col-md-3 mb-3">
                                                                                                                                                        <label class="form-label required-label">Old Reading</label>
                                                                                                                                                        <input type="number" class="form-control reset-old-reading" 
                                                                                                                                                            data-nozzle-id="${nozzle.id}" 
                                                                                                                                                            value="${nozzle.last_reading}" 
                                                                                                                                                            step="0.01" min="0" required>
                                                                                                                                                    </div>
                                                                                                                                                    <div class="col-md-3 mb-3">
                                                                                                                                                        <label class="form-label required-label">New Reading</label>
                                                                                                                                                        <input type="number" class="form-control reset-new-reading" 
                                                                                                                                                            data-nozzle-id="${nozzle.id}" 
                                                                                                                                                            step="0.01" min="0" placeholder="Enter new reading" required>
                                                                                                                                                    </div>
                                                                                                                                                    <div class="col-md-3 mb-3">
                                                                                                                                                        <label class="form-label required-label">Rate (per liter)</label>
                                                                                                                                                        <input type="number" class="form-control reset-rate" 
                                                                                                                                                            data-nozzle-id="${nozzle.id}" 
                                                                                                                                                            step="0.01" min="0" required readonly>
                                                                                                                                                    </div>
                                                                                                                                                    <div class="col-md-3 mb-3">
                                                                                                                                                        <label class="form-label">Reset Total Dispensed</label>
                                                                                                                                                        <input type="number" class="form-control reset-total-dispensed" 
                                                                                                                                                            data-nozzle-id="${nozzle.id}" readonly>
                                                                                                                                                    </div>
                                                                                                                                                    <div class="col-md-3 mb-3">
                                                                                                                                                        <label class="form-label">Reset Total Amount</label>
                                                                                                                                                        <input type="number" class="form-control reset-total-amount" 
                                                                                                                                                            data-nozzle-id="${nozzle.id}" readonly>
                                                                                                                                                    </div>
                                                                                                                                                    <div class="col-md-6 mb-3">
                                                                                                                                                        <label class="form-label required-label">Reason</label>
                                                                                                                                                        <textarea class="form-control reset-reason" 
                                                                                                                                                                data-nozzle-id="${nozzle.id}" 
                                                                                                                                                                rows="2" placeholder="Enter reason for nozzle reset" required>Nozzle reset during shift closing</textarea>
                                                                                                                                                    </div>
                                                                                                                                                    <div class="col-md-12">
                                                                                                                                                        <button class="btn btn-success btn-sm save-reset-btn" data-nozzle-id="${nozzle.id}">
                                                                                                                                                            Save Reset
                                                                                                                                                        </button>
                                                                                                                                                        <button class="btn btn-secondary btn-sm cancel-reset-btn ms-2" data-nozzle-id="${nozzle.id}">
                                                                                                                                                            Cancel
                                                                                                                                                        </button>
                                                                                                                                                    </div>
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                    `);
                });
            }

            // ✅ Reset Nozzle Button Click
            $(document).on("click", ".reset-nozzle-btn", function (e) {
                e.preventDefault();
                e.stopPropagation();

                const nozzleId = $(this).data('nozzle-id');
                const nozzleName = $(this).data('nozzle-name');
                const openingReading = $(this).data('opening-reading');
                const productName = $(this).data('product-name');
                const productId = $(this).data('product-id');
                const shiftId = $("#close_shift_id").val();

                // console.log("Reset button clicked:", { nozzleId, nozzleName, openingReading, productName, productId, shiftId });

                // Hide all other reset fields first
                $(".reset-fields-container").hide();

                // Show reset fields for this nozzle
                const resetContainer = $(`#reset-fields-${nozzleId}`);
                resetContainer.slideDown();

                // ✅ CHANGE: USER SELECT KAR SAKTA HAI RESET DATE
                const now = new Date();
                const localDateTime = now.toISOString().slice(0, 16);
                $(`.reset-date[data-nozzle-id="${nozzleId}"]`).val(localDateTime);

                // Set values in reset fields
                $(`.reset-old-reading[data-nozzle-id="${nozzleId}"]`).val(openingReading);
                $(`.reset-new-reading[data-nozzle-id="${nozzleId}"]`).val('');
                $(`.reset-rate[data-nozzle-id="${nozzleId}"]`).val('');
                $(`.reset-total-dispensed[data-nozzle-id="${nozzleId}"]`).val('');
                $(`.reset-total-amount[data-nozzle-id="${nozzleId}"]`).val('');
                $(`.reset-reason[data-nozzle-id="${nozzleId}"]`).val('Nozzle reset during shift closing');

                // Load product rate
                loadProductRateForReset(productId, shiftId, nozzleId);
            });

            // ✅ NEW: Save Reset Button Click
            $(document).on("click", ".save-reset-btn", function (e) {
                e.preventDefault();
                e.stopPropagation();

                const nozzleId = $(this).data('nozzle-id');
                const shiftId = $("#close_shift_id").val();

                // Get values from reset fields
                const resetDate = $(`.reset-date[data-nozzle-id="${nozzleId}"]`).val();
                const oldReading = parseFloat($(`.reset-old-reading[data-nozzle-id="${nozzleId}"]`).val());
                const newReading = parseFloat($(`.reset-new-reading[data-nozzle-id="${nozzleId}"]`).val());
                const rate = parseFloat($(`.reset-rate[data-nozzle-id="${nozzleId}"]`).val());
                const totalDispensed = parseFloat($(`.reset-total-dispensed[data-nozzle-id="${nozzleId}"]`).val()) || 0;
                const totalAmount = parseFloat($(`.reset-total-amount[data-nozzle-id="${nozzleId}"]`).val()) || 0;
                const reason = $(`.reset-reason[data-nozzle-id="${nozzleId}"]`).val();

                // ✅ VALIDATION - All fields required including reset_date
                if (!nozzleId || !resetDate || !newReading || !rate || !reason.trim()) {
                    showToast("Please fill all required fields including reset date!", "error");
                    return;
                }

                if (newReading < 0) {
                    showToast("New reading cannot be negative!", "error");
                    return;
                }

                if (newReading < oldReading) {
                    showToast("New reading cannot be less than old reading!", "error");
                    return;
                }

                const saveBtn = $(this);
                const originalText = saveBtn.html();
                saveBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...');
                saveBtn.prop('disabled', true);

                // ✅ Payload for nozzle reset - INCLUDES reset_date
                const resetPayload = {
                    nozzle_id: parseInt(nozzleId),
                    shift_id: parseInt(shiftId),
                    reset_date: resetDate, // ✅ USER INPUT DATE
                    old_reading: oldReading,
                    new_reading: newReading,
                    total_dispensed: totalDispensed,
                    rate: rate,
                    total_amount: totalAmount,
                    reason: reason,
                    created_by: parseInt(AUTH_USER_ID)
                };

                $.ajax({
                    url: getApiUrl("nozzle-totalizer-resets"),
                    method: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(resetPayload),
                    success: function (response) {
                        saveBtn.html(originalText).prop('disabled', false);

                        // Hide reset fields
                        $(`#reset-fields-${nozzleId}`).slideUp();

                        // Update the opening reading in main nozzle row
                        $(`.nozzle-opening[name="nozzle_opening[${nozzleId}]"]`).val(newReading);
                        $(`.nozzle-closing[name="nozzle_closing[${nozzleId}]"]`).attr('data-opening', newReading);

                        showToast("Nozzle reset saved successfully!", "success");

                        // Recalculate cash flow
                        setTimeout(() => {
                            calculateCashFlowSummary();
                        }, 300);
                    },
                    error: function (xhr) {
                        console.error("Error saving nozzle reset:", xhr.responseText);
                        saveBtn.html(originalText).prop('disabled', false);
                        showToast("Error saving nozzle reset!", "error");
                    }
                });
            });

            // ✅ Cancel Reset Button
            $(document).on("click", ".cancel-reset-btn", function (e) {
                e.preventDefault();
                e.stopPropagation();

                const nozzleId = $(this).data('nozzle-id');
                $(`#reset-fields-${nozzleId}`).slideUp();
            });

            // ✅ NEW: Calculate Reset Amount
            function calculateResetAmount(nozzleId) {
                const oldReading = parseFloat($(`.reset-old-reading[data-nozzle-id="${nozzleId}"]`).val()) || 0;
                const newReading = parseFloat($(`.reset-new-reading[data-nozzle-id="${nozzleId}"]`).val()) || 0;
                const rate = parseFloat($(`.reset-rate[data-nozzle-id="${nozzleId}"]`).val()) || 0;

                if (newReading >= oldReading) {
                    const totalDispensed = newReading - oldReading;
                    const totalAmount = totalDispensed * rate;

                    $(`.reset-total-dispensed[data-nozzle-id="${nozzleId}"]`).val(totalDispensed.toFixed(2));
                    $(`.reset-total-amount[data-nozzle-id="${nozzleId}"]`).val(totalAmount.toFixed(2));

                    // ✅ IMPORTANT: CASH FLOW MAI BHI UPDATE KARO
                    setTimeout(() => {
                        calculateCashFlowSummary();
                    }, 100);
                } else {
                    $(`.reset-total-dispensed[data-nozzle-id="${nozzleId}"]`).val('');
                    $(`.reset-total-amount[data-nozzle-id="${nozzleId}"]`).val('');
                }
            }

            // ✅ Auto-calculate when new reading or rate changes
            $(document).on("input", ".reset-new-reading, .reset-rate", function () {
                const nozzleId = $(this).data('nozzle-id');
                calculateResetAmount(nozzleId);
            });

            // ✅ NEW: Load Product Rate for Reset Fields
            function loadProductRateForReset(productId, shiftId, nozzleId) {
                if (!productId || !shiftId) {
                    $(`.reset-rate[data-nozzle-id="${nozzleId}"]`).val('');
                    return;
                }

                $.ajax({
                    url: getApiUrl(`shifts/${shiftId}`),
                    method: "GET",
                    success: function (shift) {
                        const stationId = shift.station_id;
                        const shiftDate = shift.start_time;

                        // console.log("Fetching rate for reset:", { stationId, productId, shiftDate, nozzleId });

                        $.ajax({
                            url: getApiUrl(`product-price/${stationId}/${productId}/${shiftDate}`),
                            method: "GET",
                            success: function (priceData) {
                                // console.log("Rate API Response for reset:", priceData);

                                if (priceData && priceData.price !== undefined) {
                                    $(`.reset-rate[data-nozzle-id="${nozzleId}"]`).val(priceData.price);
                                    calculateResetAmount(nozzleId);
                                    // console.log("Rate loaded for reset:", priceData.price);
                                } else {
                                    $(`.reset-rate[data-nozzle-id="${nozzleId}"]`).val('');
                                    showToast("Product rate not found for this date!", "warning");
                                }
                            },
                            error: function (xhr) {
                                console.error("Error loading product rate for reset:", xhr.responseText);
                                $(`.reset-rate[data-nozzle-id="${nozzleId}"]`).val('');
                                showToast("Error loading product rate! Please enter manually.", "error");
                            }
                        });
                    },
                    error: function (xhr) {
                        console.error("Error loading shift details for reset:", xhr.responseText);
                        $(`.reset-rate[data-nozzle-id="${nozzleId}"]`).val('');
                    }
                });
            }

            // ✅ Auto-calculate nozzle total dispensed when closing reading changes
            $(document).on("input", ".nozzle-closing", function () {
                const opening = parseFloat($(this).data('opening')) || 0;
                const closing = parseFloat($(this).val()) || 0;
                const totalField = $(this).closest('.nozzle-reading-row').find('.nozzle-total');
                const validationMsg = $(this).closest('.nozzle-reading-row').find('.validation-message');

                if (closing < opening) {
                    validationMsg.text('Closing reading cannot be less than opening reading').show();
                    totalField.val('');
                    $(this).addClass('is-invalid');
                } else {
                    validationMsg.hide();
                    const totalDispensed = closing - opening;
                    totalField.val(totalDispensed.toFixed(2));
                    $(this).removeClass('is-invalid');
                }
            });

            // ✅ Auto-fill Cash Return with In Hand value
            $(document).on("input", "#in_hand", function () {
                const inHandValue = $(this).val();
                $("#cash_return").val(inHandValue);
            });

            // ✅ Auto-calculate cash flow summary when closing reading changes
            $(document).on("input", ".nozzle-closing", function () {
                setTimeout(() => {
                    calculateCashFlowSummary();
                }, 300);
            });

            // ✅ Calculate Lubricants Cash Transactions for Shift
            function calculateLubricantsCashTransactions(shiftId) {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: `/api/lubes/shift/${shiftId}`,
                        method: "GET",
                        success: function (lubesData) {
                            if (!lubesData || lubesData.length === 0) {
                                resolve({ total: 0, purchases: 0, sales: 0, count: 0, cash: 0, bank: 0 });
                                return;
                            }

                            let lubricantsTotal = 0;
                            let lubricantsPurchases = 0;
                            let lubricantsSales = 0;
                            let count = 0;
                            let cashAmount = 0;
                            let bankAmount = 0;

                            // ✅ DIRECTLY USE ammount_paid.ammount FIELD (actual cash received/paid)
                            lubesData.forEach(doc => {
                                const paymentMethod = doc.payment_method || 'cash';
                                const actualCashAmount = parseFloat(doc.payment_amount) || 0; // ✅ ammount_paid.ammount

                                // ✅ SIRF CASH PAYMENTS KO CONSIDER KARO!
                                if (paymentMethod === 'cash' || paymentMethod === 'Cash') {
                                    if (doc.doc_type === 'purchase') {
                                        lubricantsPurchases += actualCashAmount;
                                        lubricantsTotal -= actualCashAmount; // ✅ Purchase = cash OUT (MINUS)
                                        cashAmount -= actualCashAmount; // ✅ Cash se payment hui
                                    } else if (doc.doc_type === 'sale') {
                                        lubricantsSales += actualCashAmount;
                                        lubricantsTotal += actualCashAmount; // ✅ Sale = cash IN (PLUS)
                                        cashAmount += actualCashAmount; // ✅ Cash aaya
                                    }
                                    count++;
                                }
                            });

                            resolve({
                                total: lubricantsTotal,
                                purchases: lubricantsPurchases,
                                sales: lubricantsSales,
                                count: count,
                                cash: cashAmount, // ✅ Net cash flow (sale - purchase)
                                bank: 0 // ✅ Bank payments cash flow mein include nahi hote
                            });
                        },
                        error: function (xhr) {
                            resolve({ total: 0, purchases: 0, sales: 0, count: 0, cash: 0, bank: 0 });
                        }
                    });
                });
            }

            // ✅ UPDATED: Calculate Oil Purchases for Shift
            function calculateOilPurchases(shiftId) {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: `/api/oil-purchases/shift/${shiftId}`,
                        method: "GET",
                        success: function (oilPurchases) {
                            if (!oilPurchases || oilPurchases.length === 0) {
                                resolve({ total: 0, count: 0, cash: 0, bank: 0, purchases: [], total_cash_payments: 0 });
                                return;
                            }

                            let oilTotal = 0;
                            let count = 0;
                            let cashAmount = 0;
                            let bankAmount = 0;
                            let totalCashPayments = 0;
                            const purchaseDetails = [];

                            oilPurchases.forEach(purchase => {
                                // ✅ USE total_cash_paid FROM API (SUM of all cash payments)
                                const totalCashPaid = parseFloat(purchase.total_cash_paid) || 0;
                                const cashPaymentCount = parseInt(purchase.cash_payment_count) || 0;
                                const hasCashPayment = purchase.has_cash_payment === 'cash';

                                // ✅ SIRF CASH PAYMENTS WALE PURCHASES KO CONSIDER KARO!
                                if (hasCashPayment && totalCashPaid > 0) {
                                    oilTotal += totalCashPaid; // ✅ Total of all cash payments
                                    totalCashPayments += totalCashPaid;
                                    count++;
                                    cashAmount += totalCashPaid; // ✅ All cash payments

                                    purchaseDetails.push({
                                        id: purchase.id,
                                        product_name: purchase.product_name || 'Oil Purchase',
                                        rate: parseFloat(purchase.rate) || 0,
                                        quantity: parseFloat(purchase.qty) || 0,
                                        amount: totalCashPaid, // ✅ TOTAL cash payments amount
                                        invoice_no: purchase.invoice_no || 'N/A',
                                        payment_status: purchase.payment_status,
                                        payment_method: 'cash',
                                        cash_payment_count: cashPaymentCount,
                                        recieving_date: purchase.recieving_date
                                    });
                                }
                            });

                            // ✅ FIX: Return POSITIVE value, calculation will handle minus
                            resolve({
                                total: oilTotal, // ✅ CHANGE: POSITIVE value, not negative
                                count: count,
                                cash: cashAmount, // ✅ CHANGE: POSITIVE value
                                bank: 0,
                                purchases: purchaseDetails,
                                total_cash_payments: totalCashPayments
                            });
                        },
                        error: function (xhr) {
                            resolve({ total: 0, count: 0, cash: 0, bank: 0, purchases: [], total_cash_payments: 0 });
                        }
                    });
                });
            }

            //AUTO CALCULATE IN HAND WHEN TOTAL SALES CHANGES
            function autoCalculateDistribution(totalSales) {
                const isBankTransfer = $("#transfer_to_bank_checkbox").is(":checked");
                const transferAmount = parseFloat($("#transfer_amount").val()) || 0;

                // ✅ Calculate total driver credit amount
                let driverCreditAmount = 0;
                if ($("#credit_to_driver_checkbox").is(":checked")) {
                    $(".driver-credit-form").each(function () {
                        const amount = parseFloat($(this).find('.credit-amount').val()) || 0;
                        driverCreditAmount += amount;
                    });
                }

                if (isBankTransfer && transferAmount > 0) {
                    // Bank transfer mode
                    const inBank = transferAmount;
                    const remainingAfterDriverCredit = totalSales - driverCreditAmount;
                    const inHand = remainingAfterDriverCredit - transferAmount;

                    if (inHand < 0) {
                        showToast("Transfer amount is too high after driver credits!", "error");
                        return;
                    }

                    $("#in_bank").val(inBank.toFixed(2));
                    $("#in_hand").val(inHand.toFixed(2));
                    $("#cash_return").val(inHand.toFixed(2));
                    $("#new_in_bank_total").val(inBank.toFixed(2));

                    validateCashDistribution(totalSales, inHand, inBank);
                } else {
                    // Normal mode - in_hand = total sales - driver credit, in_bank = 0
                    const inHand = totalSales - driverCreditAmount;
                    if (inHand < 0) {
                        showToast("Driver credit amount exceeds total sales!", "error");
                        return;
                    }

                    $("#in_hand").val(inHand.toFixed(2));
                    $("#in_bank").val('0');
                    $("#cash_return").val(inHand.toFixed(2));

                    validateCashDistribution(totalSales, inHand, 0);
                }
            }

            // ✅ Calculate Cash Flow Summary - UPDATED WITH LUBRICANTS
            function calculateCashFlowSummary() {
                const shiftId = $("#close_shift_id").val();
                const shiftStartTime = $("#shift_start_time").val();

                if (!shiftId || !shiftStartTime) {
                    $("#cash_flow_loading").html('<p class="text-muted">Shift data not loaded yet</p>');
                    return;
                }

                // ✅ Validate end time is after start time
                const endTime = $("#close_end_time").val();
                if (endTime && !validateEndTime(shiftStartTime, endTime)) {
                    $("#cash_flow_loading").html('<p class="text-danger">End time must be after start time!</p>');
                    return;
                }

                $.ajax({
                    url: getApiUrl(`shifts/${shiftId}`),
                    method: "GET",
                    success: function (shift) {
                        const stationId = shift.station_id;
                        const cashHandover = parseFloat(shift.cash_handover) || 0;

                        $.ajax({
                            url: getApiUrl(`nozzles/station/${stationId}`),
                            method: "GET",
                            success: function (nozzles) {
                                if (!nozzles || nozzles.length === 0) {
                                    $("#cash_flow_loading").html('<p class="text-muted">No nozzles found for calculation</p>');
                                    return;
                                }

                                // ✅ Get ONLY CASH transactions that are PAID for this shift
                                $.ajax({
                                    url: getApiUrl(`transactions/shift/${shiftId}`),
                                    method: "GET",
                                    success: function (transactions) {
                                        const productsMap = new Map();

                                        nozzles.forEach(nozzle => {
                                            const productId = nozzle.product_id;
                                            if (!productsMap.has(productId)) {
                                                productsMap.set(productId, {
                                                    product_id: productId,
                                                    product_name: nozzle.product_name || 'Unknown',
                                                    nozzles: []
                                                });
                                            }
                                            productsMap.get(productId).nozzles.push(nozzle);
                                        });

                                        const productPromises = Array.from(productsMap.values()).map(product => {
                                            return new Promise((resolve) => {
                                                $.ajax({
                                                    url: getApiUrl(`product-price/${stationId}/${product.product_id}/${shiftStartTime}`),
                                                    method: "GET",
                                                    success: function (priceData) {
                                                        const rate = priceData && priceData.price ? parseFloat(priceData.price) : 0;

                                                        let totalVolume = 0;
                                                        let resetVolume = 0;
                                                        let resetAmount = 0;

                                                        // ✅ FIXED: Calculate nozzle sales with proper validation
                                                        product.nozzles.forEach(nozzle => {
                                                            const openingInput = $(`.nozzle-opening[name="nozzle_opening[${nozzle.id}]"]`);
                                                            const closingInput = $(`.nozzle-closing[name="nozzle_closing[${nozzle.id}]"]`);

                                                            const opening = openingInput.length ? parseFloat(openingInput.val()) || 0 : 0;
                                                            const closing = closingInput.length ? parseFloat(closingInput.val()) || 0 : 0;

                                                            // ✅ FIX: Agar closing reading empty hai toh 0 consider karo
                                                            if (!closingInput.val() || closingInput.val().trim() === '') {
                                                                totalVolume += 0;
                                                            } else if (closing >= opening) {
                                                                const volume = closing - opening;
                                                                totalVolume += volume;
                                                            } else {
                                                                totalVolume += 0; // Invalid reading
                                                            }
                                                        });

                                                        const normalAmount = totalVolume * rate;
                                                        const nozzleTotal = normalAmount + resetAmount;

                                                        resolve({
                                                            product_id: product.product_id,
                                                            product_name: product.product_name,
                                                            rate: rate,
                                                            total_volume: totalVolume + resetVolume,
                                                            nozzle_amount: nozzleTotal,
                                                            reset_amount: resetAmount
                                                        });
                                                    },
                                                    error: function () {
                                                        resolve({
                                                            product_id: product.product_id,
                                                            product_name: product.product_name,
                                                            rate: 0,
                                                            total_volume: 0,
                                                            nozzle_amount: 0,
                                                            reset_amount: 0
                                                        });
                                                    }
                                                });
                                            });
                                        });

                                        Promise.all(productPromises).then(productSummaries => {
                                            // ✅ Calculate ONLY CASH transactions that are PAID for this shift
                                            let cashCredits = 0;
                                            let cashDebits = 0;

                                            if (transactions && Array.isArray(transactions)) {
                                                transactions.forEach(transaction => {
                                                    // ✅ ONLY COUNT CASH METHOD TRANSACTIONS
                                                    // Note: Assuming 'cash' payment method means paid in cash
                                                    if (transaction.method === 'cash') {
                                                        cashCredits += parseFloat(transaction.credit) || 0;
                                                        cashDebits += parseFloat(transaction.debit) || 0;
                                                    }
                                                });
                                            }

                                            // ✅ Calculate Lubricants Cash Transactions (PAID only)
                                            calculateLubricantsCashTransactions(shiftId).then(lubricantsData => {
                                                // ✅ Calculate Oil Purchases (PAID only)
                                                calculateOilPurchases(shiftId).then(oilPurchaseData => {
                                                    // ✅ CORRECTED CALCULATION WITH ALL COMPONENTS:
                                                    const totalNozzleSales = productSummaries.reduce((sum, product) => sum + product.nozzle_amount, 0);
                                                    const netCashTransactions = cashCredits - cashDebits;
                                                    const lubricantsNet = lubricantsData.total;
                                                    const oilPurchasesTotal = oilPurchaseData.total;

                                                    // ✅ UPDATED Formula: 
                                                    // Cash Handover + Nozzle Sales + Net Cash Transactions + Lubricants Net - Oil Purchases
                                                    const grandTotal = cashHandover + totalNozzleSales + netCashTransactions + lubricantsNet - oilPurchasesTotal;

                                                    renderCashFlowSummary(
                                                        productSummaries,
                                                        cashCredits,
                                                        cashDebits,
                                                        netCashTransactions,
                                                        cashHandover,
                                                        totalNozzleSales,
                                                        lubricantsData,
                                                        oilPurchaseData,
                                                        grandTotal
                                                    );
                                                });
                                            });
                                        }).catch(error => {
                                            $("#cash_flow_loading").html('<p class="text-danger">Error calculating sales</p>');
                                        });
                                    },
                                    error: function () {
                                        // Continue with nozzle sales only
                                        Promise.all(productPromises).then(productSummaries => {
                                            calculateLubricantsCashTransactions(shiftId).then(lubricantsData => {
                                                calculateOilPurchases(shiftId).then(oilPurchaseData => {
                                                    const totalNozzleSales = productSummaries.reduce((sum, product) => sum + product.nozzle_amount, 0);
                                                    const cashHandover = parseFloat(shift.cash_handover) || 0;
                                                    const lubricantsNet = lubricantsData.total;
                                                    const oilPurchasesTotal = oilPurchaseData.total;
                                                    const grandTotal = cashHandover + totalNozzleSales + lubricantsNet - oilPurchasesTotal;

                                                    renderCashFlowSummary(
                                                        productSummaries,
                                                        0,
                                                        0,
                                                        0,
                                                        cashHandover,
                                                        totalNozzleSales,
                                                        lubricantsData,
                                                        oilPurchaseData,
                                                        grandTotal
                                                    );
                                                });
                                            });
                                        });
                                    }
                                });
                            },
                            error: function () {
                                $("#cash_flow_loading").html('<p class="text-danger">Error loading nozzles</p>');
                            }
                        });
                    },
                    error: function () {
                        $("#cash_flow_loading").html('<p class="text-danger">Error loading shift details</p>');
                    }
                });
            }

            // ✅ Render Cash Flow Summary with LUBRICANTS - UPDATED
            function renderCashFlowSummary(productSummaries, cashCredits, cashDebits, netCashTransactions, cashHandover, totalNozzleSales, lubricantsData, oilPurchaseData, grandTotal) {
                const container = $("#product_summary_body");
                container.empty();

                let hasSales = false;

                const sortedProducts = productSummaries.sort((a, b) => {
                    return a.product_name.localeCompare(b.product_name);
                });

                // ✅ 1. Show nozzle sales details - FIXED
                sortedProducts.forEach(product => {
                    // ✅ CHECK: Agar nozzle_amount > 0 hai toh show karo
                    if (product.nozzle_amount > 0) {
                        hasSales = true;

                        let displayText = `${product.product_name}`;
                        if (product.reset_amount > 0) {
                            displayText += `<br><small class="text-success">(Reset: ${product.reset_amount.toFixed(2)})</small>`;
                        }

                        // ✅ FIXED: Agar total_volume 0 hai toh mat show karo "0.00 L"
                        const volumeDisplay = product.total_volume > 0 ? `${product.total_volume.toFixed(2)} L` : `0.00 L`;
                        const amountDisplay = product.nozzle_amount > 0 ? product.nozzle_amount.toFixed(2) : '0.00';

                        container.append(`
                                        <tr>
                                            <td>${displayText}</td>
                                            <td>${product.rate.toFixed(2)}</td>
                                            <td>${volumeDisplay}</td>
                                            <td>${amountDisplay}</td>
                                        </tr>
                                    `);
                    }
                });

                // ✅ FIXED: Agar koi sales nahi hai toh message show karo
                if (!hasSales) {
                    container.append(`
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            <i class="bi bi-info-circle me-2"></i>
                                            No nozzle sales recorded (fill nozzle closing readings)
                                        </td>
                                    </tr>
                                `);
                }

                // ✅ 2. Show summary section - ALWAYS SHOW (even if 0)
                container.append(`
                                <tr class="table-primary">
                                    <td colspan="3" class="text-end"><strong>1. Opening Cash Handover:</strong></td>
                                    <td><strong>${cashHandover.toFixed(2)}</strong></td>
                                </tr>
                                <tr class="table-secondary">
                                    <td colspan="3" class="text-end"><strong>2. Total Nozzle Sales:</strong></td>
                                    <td><strong>${totalNozzleSales.toFixed(2)}</strong></td>
                                </tr>
                            `);

                // ✅ 3. Show lubricants transactions - FIXED CONDITION
                if (lubricantsData && lubricantsData.count > 0 && lubricantsData.total !== 0) {
                    let lubricantsText = '';
                    if (lubricantsData.sales > 0 && lubricantsData.purchases > 0) {
                        lubricantsText = `<br><small class="text-success">(+${lubricantsData.sales.toFixed(2)} Sales)</small> <small class="text-danger">(-${lubricantsData.purchases.toFixed(2)} Purchases)</small>`;
                    } else if (lubricantsData.sales > 0) {
                        lubricantsText = `<br><small class="text-success">(+${lubricantsData.sales.toFixed(2)} Sales)</small>`;
                    } else if (lubricantsData.purchases > 0) {
                        lubricantsText = `<br><small class="text-danger">(-${lubricantsData.purchases.toFixed(2)} Purchases)</small>`;
                    }

                    container.append(`
                                    <tr class="table-info">
                                        <td colspan="3" class="text-end"><strong>3. Lubricants Cash${lubricantsText}:</strong></td>
                                        <td><strong>${lubricantsData.total.toFixed(2)}</strong></td>
                                    </tr>
                                `);
                }

                // ✅ 4. Show oil purchases - FIXED CONDITION
                if (oilPurchaseData && oilPurchaseData.count > 0 && oilPurchaseData.total !== 0) {
                    container.append(`
                                    <tr class="table-danger">
                                        <td colspan="3" class="text-end">
                                            <strong>4. Oil Purchases:</strong>
                                            <br><small class="text-muted">(${oilPurchaseData.count} purchase${oilPurchaseData.count > 1 ? 's' : ''})</small>
                                        </td>
                                        <td><strong>-${oilPurchaseData.total.toFixed(2)}</strong></td>
                                    </tr>
                                `);
                }

                // ✅ 5. Show cash transactions summary - FIXED CONDITION
                if ((cashCredits > 0 || cashDebits > 0) && netCashTransactions !== 0) {
                    if (cashCredits > 0) {
                        container.append(`
                                        <tr class="table-success">
                                            <td colspan="3" class="text-end"><strong>5. ➕ Cash Income:</strong></td>
                                            <td><strong>${cashCredits.toFixed(2)}</strong></td>
                                        </tr>
                                    `);
                    }

                    if (cashDebits > 0) {
                        container.append(`
                                        <tr class="table-danger">
                                            <td colspan="3" class="text-end"><strong>6. ➖ Cash Expenses:</strong></td>
                                            <td><strong>-${cashDebits.toFixed(2)}</strong></td>
                                        </tr>
                                    `);
                    }

                    container.append(`
                                    <tr class="table-warning">
                                        <td colspan="3" class="text-end"><strong>7. Net Cash Transactions (5-6):</strong></td>
                                        <td><strong>${netCashTransactions.toFixed(2)}</strong></td>
                                    </tr>
                                `);
                }

                // ✅ 6. Show final calculation - SIMPLIFIED
                let formulaText = "💵 TOTAL AVAILABLE CASH ";

                // Decide which formula to show
                const hasLubricants = lubricantsData && lubricantsData.total !== 0;
                const hasOilPurchases = oilPurchaseData && oilPurchaseData.total !== 0;
                const hasCashTransactions = netCashTransactions !== 0;

                if (hasOilPurchases) {
                    formulaText += "(1+2";
                    if (hasLubricants) formulaText += "+3";
                    formulaText += "-4";
                    if (hasCashTransactions) formulaText += "+7";
                    formulaText += ")";
                } else {
                    formulaText += "(1+2";
                    if (hasLubricants) formulaText += "+3";
                    if (hasCashTransactions) formulaText += "+7";
                    formulaText += ")";
                }

                container.append(`
                                <tr class="table-dark">
                                    <td colspan="3" class="text-end"><strong>${formulaText}:</strong></td>
                                    <td><strong>${grandTotal.toFixed(2)}</strong></td>
                                </tr>
                            `);

                $("#grand_total_amount").text(grandTotal.toFixed(2));
                $("#total_cash").val(grandTotal.toFixed(2));

                $("#cash_flow_loading").hide();
                $("#cash_flow_summary").show();

                autoCalculateDistribution(grandTotal);
            }

            // ✅ Auto-calculate cash flow summary when closing reading changes
            $(document).on("input", ".nozzle-closing", function () {
                setTimeout(() => {
                    calculateCashFlowSummary();
                }, 300);
            });

            // ✅ Auto-calculate cash flow when reset amount changes
            $(document).on("input", ".reset-total-amount", function () {
                setTimeout(() => {
                    calculateCashFlowSummary();
                }, 300);
            });

            // ✅ TOGGLE BANK TRANSFER SECTION
            $("#transfer_to_bank_checkbox").on("change", function () {
                const isChecked = $(this).is(":checked");
                const stationId = $("#close_shift_id").data("station-id") || getStationIdFromShift();

                if (isChecked) {
                    $("#bank_transfer_section").slideDown();
                    loadBankAccountsForPayment(stationId);
                    // ✅ IMPORTANT: Bank transfer time, in_bank field ko auto 0 set karo
                    $("#in_bank").val(0).prop('readonly', true).css('background-color', '#f8f9fa');
                } else {
                    $("#bank_transfer_section").slideUp();
                    // ✅ Bank transfer cancel karte time in_bank field ko editable banao
                    $("#in_bank").val('').prop('readonly', false).css('background-color', '');
                    $("#transfer_amount").val("");
                    $("#new_in_bank_total").val("");
                }
            });

            // ✅ TOGGLE CREDIT TO DRIVER SECTION
            $("#credit_to_driver_checkbox").on("change", function () {
                const isChecked = $(this).is(":checked");

                if (isChecked) {
                    $("#credit_driver_section").slideDown();

                    // Clear container
                    $("#credit_driver_forms_container").empty();

                    // Reset form counter
                    window.driverFormCounter = 1;

                    // Add first form
                    setTimeout(() => {
                        const firstForm = addDriverCreditForm();

                        // ✅ FORCE VISIBILITY
                        firstForm.show().css('opacity', 1);

                        // Load stations
                        loadStationsForCreditDriver();

                        console.log("✅ Credit to Driver enabled. Form #1 added and visible.");

                        // Debug: Check what's in the container
                        setTimeout(() => {
                            console.log(`📊 Forms in container:`, $("#credit_driver_forms_container").children().length);
                            console.log(`👁️ Container visibility:`, $("#credit_driver_forms_container").is(':visible'));
                        }, 100);
                    }, 100);
                } else {
                    $("#credit_driver_section").slideUp();
                    $("#credit_driver_forms_container").empty();
                    window.driverFormCounter = 1;
                }
            });

            // ✅ ADD ANOTHER DRIVER FORM (UPDATED)
            $(document).on("click", "#add_another_driver", function (e) {
                e.preventDefault();
                const newForm = addDriverCreditForm();

                // ✅ Load stations for the new form
                setTimeout(() => {
                    loadStationsForNewForm(newForm);

                    // Also refresh all forms to ensure consistency
                    loadStationsForCreditDriver();
                }, 100);
            });

            // ✅ REMOVE DRIVER FORM
            $(document).on("click", ".remove-driver-form", function (e) {
                e.preventDefault();
                const form = $(this).closest('.driver-credit-form');
                const formNumber = form.find('.form-number').text();

                // Don't remove if it's the only form
                if ($(".driver-credit-form").length <= 1) {
                    showToast("At least one driver credit entry is required", "warning");
                    return;
                }

                console.log(`🗑️ Removing ${formNumber}`);
                form.remove();

                // ✅ RE-INDEX ALL FORMS
                reindexDriverForms();

                // Recalculate cash flow
                setTimeout(() => {
                    const totalSales = parseFloat($("#total_cash").val()) || 0;
                    const inHand = parseFloat($("#in_hand").val()) || 0;
                    const inBank = parseFloat($("#in_bank").val()) || 0;
                    validateCashDistribution(totalSales, inHand, inBank);
                }, 300);
            });

            // ✅ RE-INDEX ALL FORMS
            function reindexDriverForms() {
                const forms = $(".driver-credit-form");
                window.driverFormCounter = 1; // Reset counter

                forms.each(function (index) {
                    const form = $(this);
                    const newNumber = index + 1;
                    const newRadioName = `amount_given_to_${newNumber}`;

                    // Update form number display
                    form.find('.form-number').text(`#${newNumber}`);

                    // Update radio button names
                    form.find('.amount-given-to').attr('name', newRadioName);

                    // Keep current selection
                    const currentSelection = form.find('.amount-given-to:checked').val();

                    // Update counter
                    window.driverFormCounter++;
                });

                console.log(`🔄 Re-indexed ${forms.length} forms. New counter: ${window.driverFormCounter}`);
            }

            // ✅ TOGGLE BETWEEN DRIVER AND VEHICLE FIELDS (ADD AT THE BEGINNING OF THE FUNCTION)
            $(document).on("change", ".amount-given-to", function () {
                const form = $(this).closest('.driver-credit-form');
                const selectedValue = $(this).val();

                updateFieldVisibility(form, selectedValue);
            });

            // ✅ UPDATE VALIDATION WHEN DRIVER CREDIT AMOUNT CHANGES
            $(document).on("input", ".credit-amount, .credit-vehicle-number, .credit-cnic", function () {
                setTimeout(() => {
                    const totalSales = parseFloat($("#total_cash").val()) || 0;
                    const inHand = parseFloat($("#in_hand").val()) || 0;
                    const inBank = parseFloat($("#in_bank").val()) || 0;

                    // Recalculate cash flow with driver credit
                    let driverCreditAmount = 0;
                    if ($("#credit_to_driver_checkbox").is(":checked")) {
                        $(".driver-credit-form").each(function () {
                            const amount = parseFloat($(this).find('.credit-amount').val()) || 0;
                            driverCreditAmount += amount;
                        });

                        // Auto-adjust in_hand based on driver credit
                        const newInHand = totalSales - driverCreditAmount - inBank;
                        if (newInHand >= 0) {
                            $("#in_hand").val(newInHand.toFixed(2));
                            $("#cash_return").val(newInHand.toFixed(2));
                        }
                    }

                    validateCashDistribution(totalSales, parseFloat($("#in_hand").val()) || 0, inBank);
                }, 300);
            });

            // ✅ DEBUG: CHECK DOM STATE
            function debugDOMState() {
                console.log("=== DEBUG DOM STATE ===");

                // Check template
                const template = $("#credit_driver_form_template");
                console.log(`Template exists: ${template.length > 0}`);
                console.log(`Template visibility: ${template.is(':visible')}`);
                console.log(`Template HTML length: ${template.html() ? template.html().length : 0}`);

                // Check container
                const container = $("#credit_driver_forms_container");
                console.log(`Container exists: ${container.length > 0}`);
                console.log(`Container children: ${container.children().length}`);
                console.log(`Container visibility: ${container.is(':visible')}`);

                // Check all forms
                const allForms = $(".driver-credit-form");
                console.log(`Total forms in DOM: ${allForms.length}`);

                allForms.each(function (i, el) {
                    const $el = $(el);
                    console.log(`Form ${i + 1}:`);
                    console.log(`  Visible: ${$el.is(':visible')}`);
                    console.log(`  Display: ${$el.css('display')}`);
                    console.log(`  Opacity: ${$el.css('opacity')}`);
                    console.log(`  Parent visible: ${$el.parent().is(':visible')}`);
                });

                console.log("=== END DEBUG ===");
            }

            // ✅ LOAD STATIONS FOR CREDIT DRIVER
            function loadStationsForCreditDriver() {
                const shiftId = $("#close_shift_id").val();
                const userRole = "{{ Auth::user()->role }}"; // 'employee' ya 'owner'

                // First get shift to know current station
                $.ajax({
                    url: getApiUrl(`shifts/${shiftId}`),
                    method: "GET",
                    success: function (shift) {
                        const currentStationId = shift.station_id;

                        // ✅ DIFFERENT API BASED ON USER ROLE
                        let apiUrl;
                        if (userRole === 'employee') {
                            apiUrl = getApiUrl(`stations-employee/${AUTH_USER_ID}`);
                        } else {
                            apiUrl = getApiUrl(`stations/${AUTH_USER_ID}`);
                        }

                        // Get stations for the user
                        $.ajax({
                            url: apiUrl,
                            method: "GET",
                            success: function (stations) {
                                // Load stations for ALL existing forms
                                $(".credit-station-select").each(function () {
                                    const $select = $(this);
                                    const currentValue = $select.val();

                                    $select.empty().append('<option value="">Select Station...</option>');

                                    stations.forEach(station => {
                                        const selected = (station.id == currentStationId) ? 'selected' : '';
                                        $select.append(`<option value="${station.id}" ${selected}>${station.name}</option>`);
                                    });

                                    if (currentValue) {
                                        $select.val(currentValue);
                                    }

                                    if ($select.val()) {
                                        $select.trigger('change');
                                    }
                                });
                            },
                            error: function (xhr) {
                                console.error("Error loading stations:", xhr.responseText);
                                showToast("Error loading stations!", "error");
                            }
                        });
                    },
                    error: function (xhr) {
                        console.error("Error loading shift:", xhr.responseText);
                    }
                });
            }

            // ✅ WHEN STATION CHANGES, LOAD CUSTOMERS AND TANKS
            $(document).on("change", ".credit-station-select", function () {
                const stationId = $(this).val();
                const form = $(this).closest('.driver-credit-form');

                if (stationId) {
                    // Load customers for this station
                    $.ajax({
                        url: `/api/accounts/category/customer`,
                        method: "GET",
                        success: function (customers) {
                            // Filter customers for this station
                            const stationCustomers = customers.filter(c => c.station_id == stationId);

                            const customerSelect = form.find('.credit-customer-select');
                            customerSelect.empty().append('<option value="">Select Customer...</option>');

                            stationCustomers.forEach(customer => {
                                customerSelect.append(`<option value="${customer.id}">${customer.name} - ${customer.phone}</option>`);
                            });
                        },
                        error: function (xhr) {
                            console.error("Error loading customers:", xhr.responseText);
                        }
                    });
                }
            });

            // ✅ TOGGLE BETWEEN DRIVER AND VEHICLE FIELDS
            $(document).on("change", ".amount-given-to", function () {
                const form = $(this).closest('.driver-credit-form');
                const radioName = $(this).attr('name');
                const selectedValue = $(this).val();

                console.log(`🔄 Toggle: Radio="${radioName}", Value="${selectedValue}"`);

                if (selectedValue === 'Vehicle') {
                    // Vehicle selected
                    form.find('.vehicle-number-section').slideDown();
                    form.find('.driver-cnic-section').slideUp();

                    form.find('.credit-vehicle-number')
                        .prop('required', true)
                        .show()
                        .removeClass('is-invalid');

                    form.find('.credit-cnic')
                        .prop('required', false)
                        .val('')
                        .removeClass('is-invalid');

                } else {
                    // Driver selected
                    form.find('.vehicle-number-section').slideUp();
                    form.find('.driver-cnic-section').slideDown();

                    form.find('.credit-cnic')
                        .prop('required', true)
                        .removeClass('is-invalid');

                    form.find('.credit-vehicle-number')
                        .prop('required', false)
                        .val('')
                        .hide()
                        .removeClass('is-invalid');
                }

                // Debug after toggle
                setTimeout(() => {
                    const formNum = form.find('.form-number').text();
                    console.log(`✅ Form ${formNum} toggled to ${selectedValue}`);
                }, 100);
            });

            // ✅ VALIDATE CNIC (13 digits)
            $(document).on("input", ".credit-cnic", function () {
                const cnic = $(this).val().toString();

                if (cnic.length !== 13) {
                    $(this).addClass('is-invalid');
                    $(this).removeClass('is-valid');
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).addClass('is-valid');
                }
            });

            // ✅ GLOBAL FORM COUNTER
            if (typeof window.driverFormCounter === 'undefined') {
                window.driverFormCounter = 1;
            }

            // ✅ ADD DRIVER CREDIT FORM
            function addDriverCreditForm() {
                const formNumber = window.driverFormCounter++;
                const uniqueRadioName = `amount_given_to_${formNumber}`;

                // Create form HTML directly
                const formHtml = `
                                                <div class="driver-credit-form mb-4 p-3 border rounded" style="display: block !important;">
                                                    <div class="row">
                                                        <div class="col-md-12 mb-3">
                                                            <h6 class="text-primary">Driver Credit Entry <span class="form-number">#${formNumber}</span>
                                                                <button type="button" class="btn btn-danger btn-sm float-end remove-driver-form">
                                                                    <i class="bi bi-trash"></i> Remove
                                                                </button>
                                                            </h6>
                                                        </div>

                                                        <!-- Station Selection -->
                                                        <div class="col-md-3">
                                                            <label class="form-label required-label">Station</label>
                                                            <select class="form-control credit-station-select" required>
                                                                <option value="">Select Station...</option>
                                                            </select>
                                                            <div class="invalid-feedback">Please select a station</div>
                                                        </div>

                                                        <!-- Customer Selection -->
                                                        <div class="col-md-3">
                                                            <label class="form-label required-label">Customer</label>
                                                            <select class="form-control credit-customer-select" required>
                                                                <option value="">Select Customer...</option>
                                                            </select>
                                                            <div class="invalid-feedback">Please select a customer</div>
                                                        </div>

                                                        <!-- Amount Given To -->
                                                        <div class="col-md-3">
                                                            <label class="form-label required-label">Amount Given To</label>
                                                            <div class="mt-2">
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input amount-given-to" type="radio" 
                                                                           name="${uniqueRadioName}" value="Driver" checked>
                                                                    <label class="form-check-label">Driver</label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input amount-given-to" type="radio" 
                                                                           name="${uniqueRadioName}" value="Vehicle">
                                                                    <label class="form-check-label">Vehicle</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Amount -->
                                                        <div class="col-md-3">
                                                            <label class="form-label required-label">Amount</label>
                                                            <input type="number" class="form-control credit-amount" 
                                                                   step="0.01" min="0" placeholder="0.00" required>
                                                            <div class="invalid-feedback">Amount is required</div>
                                                        </div>
                                                    </div>

                                                    <!-- DRIVER CNIC SECTION (Visible by default) -->
                                                    <div class="row mt-2 driver-cnic-section" style="display: block;">
                                                        <div class="col-md-4">
                                                            <label class="form-label required-label">Driver CNIC</label>
                                                            <input type="number" class="form-control credit-cnic" 
                                                                   placeholder="Enter 13-digit CNIC" maxlength="13" min="1000000000000" max="9999999999999">
                                                            <small class="text-muted">13 digits without dashes</small>
                                                            <div class="invalid-feedback">Valid 13-digit CNIC is required</div>
                                                        </div>
                                                    </div>

                                                    <!-- VEHICLE NUMBER SECTION (Initially Hidden) -->
                                                    <div class="row mt-2 vehicle-number-section" style="display: none;">
                                                        <div class="col-md-4">
                                                            <label class="form-label required-label">Vehicle Number</label>
                                                            <input type="text" class="form-control credit-vehicle-number" 
                                                                   placeholder="Enter vehicle number">
                                                            <div class="invalid-feedback">Vehicle number is required</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            `;

                // Append the form
                $("#credit_driver_forms_container").append(formHtml);

                // Get the newly added form
                const newFormElement = $("#credit_driver_forms_container .driver-credit-form").last();

                console.log(`✅ Added form #${formNumber} with radio name: ${uniqueRadioName}`);
                console.log(`👁️ New form visibility:`, newFormElement.is(':visible'));

                // ✅ Load stations for the newly added form
                loadStationsForNewForm(newFormElement);

                return newFormElement;
            }

            // ✅ LOAD STATIONS FOR NEW FORM
            function loadStationsForNewForm(formElement) {
                const shiftId = $("#close_shift_id").val();

                $.ajax({
                    url: getApiUrl(`shifts/${shiftId}`),
                    method: "GET",
                    success: function (shift) {
                        const currentStationId = shift.station_id;

                        // Get all stations for the user
                        $.ajax({
                            url: getApiUrl(`stations/${AUTH_USER_ID}`),
                            method: "GET",
                            success: function (stations) {
                                const $select = formElement.find('.credit-station-select');

                                $select.empty().append('<option value="">Select Station...</option>');

                                stations.forEach(station => {
                                    const selected = (station.id == currentStationId) ? 'selected' : '';
                                    $select.append(`<option value="${station.id}" ${selected}>${station.name}</option>`);
                                });

                                console.log(`✅ Loaded ${stations.length} stations for new form`);
                            },
                            error: function (xhr) {
                                console.error("Error loading stations for new form:", xhr.responseText);
                            }
                        });
                    },
                    error: function (xhr) {
                        console.error("Error loading shift details for new form:", xhr.responseText);
                    }
                });
            }

            // ✅ INITIALIZE FORM FUNCTION
            function initializeDriverCreditForm(formElement, formNumber, radioName) {
                // Make sure form is visible
                formElement.show();

                // Set form number
                formElement.find('.form-number').text(`#${formNumber}`);

                // Set radio button names and default selection
                formElement.find('.amount-given-to').attr('name', radioName);
                formElement.find(`input[name="${radioName}"][value="Driver"]`).prop('checked', true);

                // Initial state - show CNIC, hide vehicle
                formElement.find('.driver-cnic-section').show();
                formElement.find('.credit-cnic').prop('required', true);
                formElement.find('.vehicle-number-section').hide();
                formElement.find('.credit-vehicle-number').prop('required', false);

                // Load stations if available
                if ($(".credit-station-select").length > 0 && $(".credit-station-select").first().find('option').length > 1) {
                    const stationHtml = $(".credit-station-select").first().html();
                    formElement.find('.credit-station-select').html(stationHtml);
                }

                // Make sure the form itself is visible
                formElement.css('display', 'block');
            }

            // ✅ FIX EXISTING FORMS RADIO NAMES
            function fixAllFormRadioNames() {
                $(".driver-credit-form").each(function (index) {
                    const formNumber = index + 1;
                    const form = $(this);
                    const expectedRadioName = `amount_given_to_${formNumber}`;

                    // Check current radio name
                    const currentRadio = form.find('.amount-given-to').first();
                    const currentRadioName = currentRadio.attr('name');

                    if (currentRadioName !== expectedRadioName) {
                        console.log(`Fixing form #${formNumber}: Changing radio name from "${currentRadioName}" to "${expectedRadioName}"`);

                        // Update all radio buttons in this form
                        form.find('.amount-given-to').attr('name', expectedRadioName);

                        // Re-apply default selection
                        if (!form.find(`input[name="${expectedRadioName}"]:checked`).length) {
                            form.find(`input[name="${expectedRadioName}"][value="Driver"]`).prop('checked', true);
                        }
                    }
                });
            }

            // ✅ FIX EXISTING FORMS RADIO NAMES
            function debugFormsStatus() {
                console.log("=== DEBUG FORM STATUS ===");
                $(".driver-credit-form").each(function (index) {
                    const form = $(this);
                    const formNumber = index + 1;
                    const radioName = form.find('.amount-given-to').first().attr('name');
                    const selectedValue = form.find(`input[name="${radioName}"]:checked`).val();
                    const station = form.find('.credit-station-select').val();
                    const customer = form.find('.credit-customer-select').val();
                    const amount = form.find('.credit-amount').val();
                    const cnic = form.find('.credit-cnic').val();
                    const vehicle = form.find('.credit-vehicle-number').val();

                    console.log(`Form #${formNumber}:`);
                    console.log(`  Radio Name: ${radioName}`);
                    console.log(`  Selected: ${selectedValue}`);
                    console.log(`  Station: ${station}`);
                    console.log(`  Customer: ${customer}`);
                    console.log(`  Amount: ${amount}`);
                    console.log(`  CNIC: ${cnic}`);
                    console.log(`  Vehicle: ${vehicle}`);
                    console.log(`  CNIC Section Visible: ${form.find('.driver-cnic-section').is(':visible')}`);
                    console.log(`  Vehicle Section Visible: ${form.find('.vehicle-number-section').is(':visible')}`);
                });
                console.log("=== END DEBUG ===");
            }

            // ✅ UPDATE FORM NUMBERS
            function updateFormNumbers() {
                $(".driver-credit-form").each(function (index) {
                    const formNumber = index + 1;
                    const form = $(this);

                    // Update form number display
                    form.find('.form-number').text(`#${formNumber}`);

                    // Get current radio name (if exists)
                    const currentRadio = form.find('.amount-given-to').first();
                    if (currentRadio.length) {
                        const currentRadioName = currentRadio.attr('name');
                        const expectedRadioName = `amount_given_to_${formNumber}`;

                        // If names don't match, update them
                        if (currentRadioName !== expectedRadioName) {
                            console.log(`🔧 Fixing form #${formNumber}: Changing radio name from "${currentRadioName}" to "${expectedRadioName}"`);
                            form.find('.amount-given-to').attr('name', expectedRadioName);
                        }

                        // Make sure one radio is selected
                        if (!form.find(`input[name="${expectedRadioName}"]:checked`).length) {
                            form.find(`input[name="${expectedRadioName}"][value="Driver"]`).prop('checked', true);
                        }

                        // Update field visibility based on selection
                        const selectedValue = form.find(`input[name="${expectedRadioName}"]:checked`).val();
                        updateFieldVisibility(form, selectedValue);
                    }
                });
            }

            // ✅ HELPER FUNCTION TO UPDATE FIELD VISIBILITY
            function updateFieldVisibility(form, selectedValue) {
                if (selectedValue === 'Vehicle') {
                    // Show vehicle section, hide CNIC section
                    form.find('.vehicle-number-section').slideDown();
                    form.find('.driver-cnic-section').slideUp();

                    // Make vehicle number required, CNIC not required
                    form.find('.credit-vehicle-number')
                        .prop('required', true)
                        .removeClass('is-invalid');
                    form.find('.credit-cnic')
                        .prop('required', false)
                        .val('')
                        .removeClass('is-invalid');

                    console.log(`🔄 Form switched to Vehicle mode`);
                } else {
                    // Show CNIC section, hide vehicle section
                    form.find('.driver-cnic-section').slideDown();
                    form.find('.vehicle-number-section').slideUp();

                    // Make CNIC required, vehicle number not required
                    form.find('.credit-cnic')
                        .prop('required', true)
                        .removeClass('is-invalid');
                    form.find('.credit-vehicle-number')
                        .prop('required', false)
                        .val('')
                        .removeClass('is-invalid');

                    console.log(`🔄 Form switched to Driver mode`);
                }
            }

            // ✅ VALIDATE DRIVER CREDIT FORMS
            function validateDriverCreditForms() {
                // ✅ ONLY GET VISIBLE FORMS
                const forms = $(".driver-credit-form:visible");

                console.log(`🔍 Validating ${forms.length} visible driver credit forms`);

                if (forms.length === 0) {
                    console.log("⚠️ No visible forms to validate");
                    return true;
                }

                let isValid = true;

                forms.each(function (index) {
                    const form = $(this);
                    const formNumber = form.find('.form-number').text();

                    console.log(`--- Validating ${formNumber} ---`);

                    // Get the radio button group
                    const radioName = form.find('.amount-given-to').first().attr('name');
                    const givenTo = form.find(`input[name="${radioName}"]:checked`).val();

                    if (!givenTo) {
                        showToast(`${formNumber}: Please select Driver or Vehicle`, "error");
                        isValid = false;
                        return false;
                    }

                    // Clear previous errors
                    form.find('.is-invalid').removeClass('is-invalid');

                    // Validate required fields
                    const stationId = form.find('.credit-station-select').val();
                    const customerId = form.find('.credit-customer-select').val();
                    const amount = parseFloat(form.find('.credit-amount').val()) || 0;

                    if (!stationId) {
                        form.find('.credit-station-select').addClass('is-invalid');
                        showToast(`${formNumber}: Please select a station`, "error");
                        isValid = false;
                    }

                    if (!customerId) {
                        form.find('.credit-customer-select').addClass('is-invalid');
                        showToast(`${formNumber}: Please select a customer`, "error");
                        isValid = false;
                    }

                    if (amount <= 0) {
                        form.find('.credit-amount').addClass('is-invalid');
                        showToast(`${formNumber}: Please enter a valid amount`, "error");
                        isValid = false;
                    }

                    // Conditional validation
                    if (givenTo === 'Driver') {
                        const cnic = form.find('.credit-cnic').val() || '';
                        if (cnic.length !== 13) {
                            form.find('.credit-cnic').addClass('is-invalid');
                            showToast(`${formNumber}: Valid 13-digit CNIC is required`, "error");
                            isValid = false;
                        }
                    } else if (givenTo === 'Vehicle') {
                        const vehicleNumber = form.find('.credit-vehicle-number').val() || '';
                        if (!vehicleNumber.trim()) {
                            form.find('.credit-vehicle-number').addClass('is-invalid');
                            showToast(`${formNumber}: Vehicle number is required`, "error");
                            isValid = false;
                        }
                    }
                });

                return isValid;
            }

            // ✅ COLLECT DRIVER CREDIT DATA FOR SAVING
            function collectDriverCreditData(shiftId) {
                const driverData = [];

                console.log(`📦 Collecting data from ${$(".driver-credit-form").length} forms`);

                $(".driver-credit-form").each(function (index) {
                    const form = $(this);
                    const formNumber = index + 1;
                    const stationId = form.find('.credit-station-select').val();
                    const customerId = form.find('.credit-customer-select').val();
                    const amount = parseFloat(form.find('.credit-amount').val()) || 0;

                    console.log(`Form #${formNumber}: Station=${stationId}, Customer=${customerId}, Amount=${amount}`);

                    // Get the radio button group name for this form
                    const radioName = form.find('.amount-given-to').first().attr('name');
                    const givenTo = form.find(`input[name="${radioName}"]:checked`).val();

                    let vehicleNumber = null;
                    let cnic = null;

                    if (givenTo === 'Vehicle') {
                        vehicleNumber = form.find('.credit-vehicle-number').val();
                        console.log(`Form #${formNumber}: Vehicle mode - Vehicle Number="${vehicleNumber}"`);
                    } else {
                        cnic = form.find('.credit-cnic').val();
                        console.log(`Form #${formNumber}: Driver mode - CNIC="${cnic}"`);
                    }

                    driverData.push({
                        shift_id: parseInt(shiftId),
                        station_id: parseInt(stationId),
                        account_id: parseInt(customerId),
                        amount_given_to: givenTo,
                        vehicle_number: vehicleNumber,
                        cnic: cnic,
                        amount: amount,
                        created_by: parseInt(AUTH_USER_ID)
                    });
                });

                console.log(`📦 Collected ${driverData.length} driver credit entries:`, driverData);
                return driverData;
            }

            // ✅ UPDATE CASH FLOW FOR DRIVER CREDITS
            function updateCashFlowForDriverCredits() {
                let totalCashAmount = 0;

                $(".driver-credit-form").each(function () {
                    const form = $(this);
                    const amount = parseFloat(form.find('.credit-amount').val()) || 0;
                    totalCashAmount += amount;
                });

                // Update the validation message
                const totalSales = parseFloat($("#total_cash").val()) || 0;
                const inHand = parseFloat($("#in_hand").val()) || 0;
                const inBank = parseFloat($("#in_bank").val()) || 0;

                validateCashDistribution(totalSales, inHand, inBank);

                return totalCashAmount;
            }

            // ✅ GET STATION ID FROM SHIFT
            function getStationIdFromShift() {
                const shiftId = $("#close_shift_id").val();
                let stationId = null;

                // Sync call to get station ID
                $.ajax({
                    url: getApiUrl(`shifts/${shiftId}`),
                    method: "GET",
                    async: false,
                    success: function (shift) {
                        stationId = shift.station_id;
                        $("#close_shift_id").data("station-id", stationId);
                    }
                });

                return stationId;
            }

            // ✅ LOAD BANK ACCOUNTS (Your existing function, modified)
            function loadBankAccountsForPayment(stationId) {
                let url;
                if (stationId) {
                    url = `/api/stations/${stationId}/bank-accounts`;
                } else {
                    url = `/api/accounts/category/bank`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function (resp) {
                        const accounts = Array.isArray(resp) ? resp : (resp && Array.isArray(resp.data) ? resp.data : []);

                        // Filter bank accounts (type = 'bank')
                        const banks = accounts.filter(a => (a.type || '').toString().toLowerCase() === 'bank');

                        let bankSelect = $("#bank_account_select");
                        bankSelect.empty().append(`<option value="">Select Bank Account...</option>`);

                        if (banks && banks.length > 0) {
                            banks.forEach(bank => {
                                const displayName = `${bank.name} - ${bank.account_number || 'N/A'} (${bank.bank_name || 'Bank'})`;
                                bankSelect.append(
                                    `<option value="${bank.id}" data-account-number="${bank.account_number || ''}">${displayName}</option>`
                                );
                            });
                        } else {
                            bankSelect.append(`<option value="">No bank accounts found</option>`);
                        }
                    },
                    error: function (err) {
                        console.error('Failed to load bank accounts:', err);
                        $('#bank_account_select').html('<option value="">Error loading accounts</option>');
                    }
                });
            }

            // ✅ AUTO-CALCULATE TRANSFER AMOUNT AND UPDATE SUMMARY
            $("#transfer_amount").on("input", function () {
                const transferAmount = parseFloat($(this).val()) || 0;
                const totalSales = parseFloat($("#total_cash").val()) || 0;
                const inHand = parseFloat($("#in_hand").val()) || 0;

                // ✅ IMPORTANT: In bank field ko transfer amount se update karo
                $("#in_bank").val(transferAmount);
                $("#new_in_bank_total").val(transferAmount.toFixed(2));

                // Auto-calculate in hand baki amount
                const remainingInHand = inHand - transferAmount;
                if (remainingInHand >= 0) {
                    $("#in_hand").val(remainingInHand.toFixed(2));
                    // Cash return bhi update karo
                    $("#cash_return").val(remainingInHand.toFixed(2));
                }

                // Update validation message
                const inBank = transferAmount;
                validateCashDistribution(totalSales, remainingInHand, inBank);
            });

            // ✅ IN_HAND CHANGE  VALIDATE 
            $("#in_hand").on("input", function () {
                const inHand = parseFloat($(this).val()) || 0;
                const isBankTransfer = $("#transfer_to_bank_checkbox").is(":checked");
                const transferAmount = parseFloat($("#transfer_amount").val()) || 0;
                const totalSales = parseFloat($("#total_cash").val()) || 0;

                // Cash return auto update
                $("#cash_return").val(inHand.toFixed(2));

                if (isBankTransfer) {
                    // Agar bank transfer hai, toh in_hand + transfer_amount = totalSales hona chahiye
                    const inBank = transferAmount;
                    validateCashDistribution(totalSales, inHand, inBank);
                } else {
                    // Simple validation
                    const inBank = parseFloat($("#in_bank").val()) || 0;
                    validateCashDistribution(totalSales, inHand, inBank);
                }
            });

            // ✅ Validate Cash Distribution
            function validateCashDistribution(totalSales, inHand, inBank) {
                const validationMsg = $("#cash_validation_msg");
                const validationText = $("#validation_text");

                // ✅ Calculate total driver credit amount
                let driverCreditAmount = 0;
                if ($("#credit_to_driver_checkbox").is(":checked")) {
                    $(".driver-credit-form").each(function () {
                        const amount = parseFloat($(this).find('.credit-amount').val()) || 0;
                        driverCreditAmount += amount;
                    });
                }

                const distributed = inHand + inBank + driverCreditAmount;
                const difference = Math.abs(distributed - totalSales);

                if (difference > 0.01) {
                    validationText.html(`
                                                                    ⚠️ <strong>Cash Mismatch!</strong><br>
                                                                    Total Sales: <strong>${totalSales.toFixed(2)}</strong><br>
                                                                    In Hand: <strong>${inHand.toFixed(2)}</strong> + 
                                                                    In Bank: <strong>${inBank.toFixed(2)}</strong> + 
                                                                    Driver Credit: <strong>${driverCreditAmount.toFixed(2)}</strong> = 
                                                                    <strong>${distributed.toFixed(2)}</strong><br>
                                                                    Difference: <strong class="text-danger">${difference.toFixed(2)}</strong>
                                                                `);
                    validationMsg.show().removeClass("alert-success").addClass("alert-warning");
                    return false;
                } else {
                    validationText.html(`
                                                                    ✅ <strong>Perfect Match!</strong><br>
                                                                    Total Sales: <strong>${totalSales.toFixed(2)}</strong><br>
                                                                    In Hand: <strong>${inHand.toFixed(2)}</strong> + 
                                                                    In Bank: <strong>${inBank.toFixed(2)}</strong> + 
                                                                    Driver Credit: <strong>${driverCreditAmount.toFixed(2)}</strong> = 
                                                                    <strong>${distributed.toFixed(2)}</strong>
                                                                `);
                    validationMsg.show().removeClass("alert-warning").addClass("alert-success");
                    return true;
                }
            }

            // ✅ FIXED: Save Bank Transfer Function with proper debugging
function saveBankTransfer(shiftId, stationId, accountId, transferAmount) {
    return new Promise((resolve, reject) => {
        console.log("🚨 BANK TRANSFER STARTED:", { shiftId, stationId, accountId, transferAmount });

        // ✅ Step 1: Get LATEST current balance
        $.ajax({
            url: `/api/site-total-amount/current/${stationId}/${accountId}`,
            method: "GET",
            success: function(currentData) {
                // ✅ CURRENT BALANCE = latest amount
                let currentBalance = parseFloat(currentData?.current_balance) || 0;
                
                // ✅ NEW BALANCE = CURRENT + TRANSFER
                let newBalance = currentBalance + transferAmount;
                
                console.log("💰 Calculation:", {
                    current_balance: currentBalance,
                    transfer_amount: transferAmount,
                    new_balance: newBalance
                });

                // ✅ Prepare data with CORRECT values
                const siteTotalAmountData = {
                    station_id: parseInt(stationId),
                    account_id: parseInt(accountId),
                    amount: newBalance,                    // ✅ NEW BALANCE (current + transfer)
                    previous_amount: currentBalance,      // ✅ OLD BALANCE (current before transfer)
                    date: new Date().toISOString().slice(0, 19).replace('T', ' '),
                    created_by: parseInt(AUTH_USER_ID)
                };

                console.log("💾 Sending to server:", siteTotalAmountData);
                console.log("🔑 AUTH_USER_ID:", AUTH_USER_ID);
                console.log("📝 created_by type:", typeof parseInt(AUTH_USER_ID));

                // ✅ Send to server (INSERT new record)
                $.ajax({
                    url: '/api/site-total-amounts',
                    method: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(siteTotalAmountData),
                    success: function(response) {
                        console.log("✅ BANK TRANSFER SAVED:", response);
                        
                        // ✅ VERIFY: response.total_amount should equal newBalance
                        if (response.total_amount !== newBalance) {
                            console.warn("⚠️ Server returned wrong amount!", {
                                sent: newBalance,
                                received: response.total_amount
                            });
                        }
                        
                        showToast(
                            `Bank transfer of ${transferAmount.toFixed(2)} completed! ` +
                            `Balance: ${currentBalance.toFixed(2)} → ${newBalance.toFixed(2)}`, 
                            "success"
                        );
                        
                        resolve({
                            success: true,
                            data: {
                                amount: newBalance,
                                previous_amount: currentBalance,
                                transfer_amount: transferAmount
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error("❌ Error saving:", xhr.responseText);
                        let errorMsg = xhr.responseJSON?.message || "Bank transfer failed";
                        showToast(errorMsg, "error");
                        reject(xhr.responseJSON);
                    }
                });
            },
            error: function(xhr) {
                console.error("❌ Error fetching current balance:", xhr.responseText);
                
                // ✅ First time - no record exists
                if (xhr.status === 404) {
                    console.log("📝 First time transfer - no previous record");
                    
                    const siteTotalAmountData = {
                        station_id: parseInt(stationId),
                        account_id: parseInt(accountId),
                        amount: transferAmount,        // ✅ Amount = Transfer Amount
                        previous_amount: 0,             // ✅ Previous = 0
                        date: new Date().toISOString().slice(0, 19).replace('T', ' '),
                        created_by: parseInt(AUTH_USER_ID)
                    };

                    $.ajax({
                        url: '/api/site-total-amount',
                        method: "POST",
                        contentType: "application/json",
                        data: JSON.stringify(siteTotalAmountData),
                        success: function(response) {
                            showToast(`First bank transfer of ${transferAmount} completed!`, "success");
                            resolve(response);
                        },
                        error: function(xhr) {
                            reject(xhr.responseJSON);
                        }
                    });
                } else {
                    reject(xhr.responseJSON);
                }
            }
        });
    });
}    

// ✅ Save Close Shift with VALIDATION 
            $("#save_close_shift").on("click", function () {
                const shiftId = $("#close_shift_id").val();
                const endTime = $("#close_end_time").val();
                const shiftStartTime = $("#shift_start_time").val();
                const isBankTransfer = $("#transfer_to_bank_checkbox").is(":checked");
                const accountId = $("#bank_account_select").val();
                const transferAmount = parseFloat($("#transfer_amount").val()) || 0;
                const stationId = $("#close_shift_id").data("station-id") || getStationIdFromShift();

                // ✅ IMPORTANT: In hand aur in bank values get karo
                let inHand = parseFloat($("#in_hand").val()) || 0;
                let inBank = parseFloat($("#in_bank").val()) || 0;
                const cash_return = inHand; // Cash Return = In Hand
                const totalSales = parseFloat($("#total_cash").val()) || 0;

                // ✅ Collect Driver Credit Data FIRST
                const driverCreditData = [];
                let driverCreditAmount = 0;
                if ($("#credit_to_driver_checkbox").is(":checked")) {
                    // Validate Driver Credit Forms
                    if (!validateDriverCreditForms()) {
                        return;
                    }

                    driverCreditData.push(...collectDriverCreditData(shiftId));
                    driverCreditAmount = updateCashFlowForDriverCredits();
                }

                // Validate end time
                if (!validateEndTime(shiftStartTime, endTime)) {
                    showToast("End time must be after start time!", "error");
                    return;
                }

                if (!inHand && !inBank) {
                    showToast("Please fill either In Hand or In Bank amounts!", "error");
                    return;
                }

                // ✅ BANK TRANSFER VALIDATION
                if (isBankTransfer) {
                    if (!accountId) {
                        showToast("Please select a bank account for transfer!", "error");
                        $("#bank_account_select").addClass('is-invalid');
                        return;
                    }

                    if (!transferAmount || transferAmount <= 0) {
                        showToast("Please enter transfer amount!", "error");
                        $("#transfer_amount").addClass('is-invalid');
                        return;
                    }

                    if (transferAmount > totalSales) {
                        showToast("Transfer amount cannot exceed total sales!", "error");
                        $("#transfer_amount").addClass('is-invalid');
                        return;
                    }

                    // ✅ SET CORRECT VALUES FOR BANK TRANSFER
                    inBank = transferAmount; // In bank = transfer amount
                    inHand = totalSales - transferAmount - driverCreditAmount; // In hand = remaining amount after bank transfer and driver credits
                    if (inHand < 0) {
                        showToast("Amounts exceed total sales!", "error");
                        return;
                    }

                    $("#in_bank").val(inBank.toFixed(2));
                    $("#in_hand").val(inHand.toFixed(2));
                    $("#cash_return").val(inHand.toFixed(2));
                } else {
                    // If no bank transfer, adjust inHand for driver credits
                    inHand = totalSales - driverCreditAmount - inBank;
                    if (inHand < 0) {
                        showToast("Driver credit amount exceeds available cash!", "error");
                        return;
                    }
                    $("#in_hand").val(inHand.toFixed(2));
                    $("#cash_return").val(inHand.toFixed(2));
                }

                // ✅ FINAL VALIDATION: In Hand + In Bank + Driver Credit = Total Sales
                const distributed = inHand + inBank + driverCreditAmount;
                const difference = Math.abs(distributed - totalSales);

                if (difference > 0.01) {
                    showToast(`Cash distribution (${distributed.toFixed(2)}) must equal total sales (${totalSales.toFixed(2)})! 
                              In Hand: ${inHand.toFixed(2)} + In Bank: ${inBank.toFixed(2)} + Driver Credit: ${driverCreditAmount.toFixed(2)} = ${distributed.toFixed(2)}
                              Difference: ${difference.toFixed(2)}`, "error");
                    return;
                }

                // Validate ALL required fields
                if (!validateRequiredFields()) {
                    return;
                }

                // Collect tank dips data
                const tankData = [];
                $(".tank-dip-row").each(function () {
                    const tankId = $(this).data('tank-id');
                    const dipMm = $(this).find('.tank-dip-mm').val();
                    const dipLiters = $(this).find('.tank-dip-liters').val();

                    tankData.push({
                        tank_id: parseInt(tankId),
                        dip_mm: parseFloat(dipMm),
                        dip_in_liters: parseFloat(dipLiters),
                        shift_id: parseInt(shiftId),
                        from_date: shiftStartTime,
                        to_date: endTime,
                        remarks: "Shift closing dip reading",
                        created_by: parseInt(AUTH_USER_ID)
                    });
                });

                // Collect nozzle readings data
                const nozzleReadingsData = [];
                $(".nozzle-reading-row").each(function () {
                    const nozzleId = $(this).data('nozzle-id');
                    const closingReading = $(this).find('.nozzle-closing').val();
                    const openingReading = parseFloat($(this).find('.nozzle-opening').val()) || 0;

                    nozzleReadingsData.push({
                        shift_id: parseInt(shiftId),
                        nozzle_id: parseInt(nozzleId),
                        opening_reading: openingReading,
                        closing_reading: parseFloat(closingReading),
                        collected_from: parseInt(AUTH_USER_ID)
                    });
                });

                // Collect nozzle resets data
                const nozzleResetData = [];
                $(".save-reset-btn").each(function () {
                    const nozzleId = $(this).data('nozzle-id');
                    const resetDate = $(`.reset-date[data-nozzle-id="${nozzleId}"]`).val();
                    const oldReading = parseFloat($(`.reset-old-reading[data-nozzle-id="${nozzleId}"]`).val());
                    const newReading = parseFloat($(`.reset-new-reading[data-nozzle-id="${nozzleId}"]`).val());
                    const rate = parseFloat($(`.reset-rate[data-nozzle-id="${nozzleId}"]`).val());
                    const totalDispensed = parseFloat($(`.reset-total-dispensed[data-nozzle-id="${nozzleId}"]`).val()) || 0;
                    const totalAmount = parseFloat($(`.reset-total-amount[data-nozzle-id="${nozzleId}"]`).val()) || 0;
                    const reason = $(`.reset-reason[data-nozzle-id="${nozzleId}"]`).val();

                    if (resetDate && newReading && rate && reason.trim()) {
                        nozzleResetData.push({
                            nozzle_id: parseInt(nozzleId),
                            shift_id: parseInt(shiftId),
                            reset_date: resetDate,
                            old_reading: oldReading,
                            new_reading: newReading,
                            total_dispensed: totalDispensed,
                            rate: rate,
                            total_amount: totalAmount,
                            reason: reason,
                            created_by: parseInt(AUTH_USER_ID)
                        });
                    }
                });

                const saveBtn = $(this);
                const originalText = saveBtn.html();
                saveBtn.html('<span class="spinner-border spinner-border-sm" role="status" id="save_loading"></span> Saving...');
                saveBtn.prop('disabled', true);
                $("#save_loading").removeClass('d-none');

                const promises = [];
                const savedItems = [];

                // 1. Update shift end time and status
                promises.push(
                    $.ajax({
                        url: getApiUrl(`shifts/${shiftId}`),
                        method: "PUT",
                        contentType: "application/json",
                        data: JSON.stringify({
                            end_time: endTime,
                            cash_return: cash_return,
                            status: "closed"
                        })
                    }).then(() => {
                        savedItems.push("shift");
                    })
                );

                // 2. Save tank dips
                if (tankData.length > 0) {
                    promises.push(
                        $.ajax({
                            url: getApiUrl("tank-dips"),
                            method: "POST",
                            contentType: "application/json",
                            data: JSON.stringify({
                                tank_data: tankData
                            })
                        }).then(() => {
                            savedItems.push(`tank dips for ${tankData.length} tanks`);
                        })
                    );
                }

                // 3. Save nozzle readings
                if (nozzleReadingsData.length > 0) {
                    nozzleReadingsData.forEach(reading => {
                        promises.push(
                            $.ajax({
                                url: getApiUrl("shift-nozzle-readings"),
                                method: "POST",
                                contentType: "application/json",
                                data: JSON.stringify(reading)
                            }).then(() => {
                                savedItems.push(`nozzle reading for ${reading.nozzle_id}`);
                            })
                        );
                    });
                }

                // 4. Save nozzle resets
                if (nozzleResetData.length > 0) {
                    nozzleResetData.forEach(reset => {
                        promises.push(
                            $.ajax({
                                url: getApiUrl("nozzle-totalizer-resets"),
                                method: "POST",
                                contentType: "application/json",
                                data: JSON.stringify(reset)
                            }).then(() => {
                                savedItems.push(`nozzle reset for nozzle ${reset.nozzle_id}`);
                            }).catch(error => {
                                console.error("Nozzle reset save error:", error);
                                showToast(`Error saving nozzle reset for nozzle ${reset.nozzle_id}!`, "error");
                            })
                        );
                    });
                }

                // ✅ 5. Save Driver Credit Data
                if (driverCreditData.length > 0) {
                    promises.push(
                        $.ajax({
                            url: '/api/driver-credits',
                            method: "POST",
                            contentType: "application/json",
                            data: JSON.stringify({
                                driver_data: driverCreditData
                            })
                        }).then(() => {
                            savedItems.push(`driver credits for ${driverCreditData.length} entries`);
                        }).catch(error => {
                            console.error("Error saving driver credits:", error);
                            showToast("Error saving driver credits!", "error");
                        })
                    );
                }

                // ✅ ✅ ✅ ✅ ✅ BANK TRANSFER PROMISE ✅ ✅ ✅ ✅ ✅
                if (isBankTransfer && accountId && transferAmount > 0) {
                    console.log("🚨 Adding bank transfer promise...");

                    const bankTransferPromise = new Promise((resolve, reject) => {
                        saveBankTransfer(shiftId, stationId, accountId, transferAmount)
                            .then(result => {
                                console.log("✅ Bank transfer completed:", result);
                                savedItems.push(`bank transfer (${transferAmount})`);
                                resolve(result);
                            })
                            .catch(error => {
                                console.error("❌ Bank transfer failed:", error);
                                // Agar bank transfer fail bhi ho jaye, toh shift close ho jana chahiye
                                savedItems.push(`bank transfer failed (${error.message})`);
                                resolve({
                                    warning: "Bank transfer failed but shift closed",
                                    error: error
                                });
                            });
                    });

                    promises.push(bankTransferPromise);
                }

                // ✅ 6. SAVE CASH FLOW DATA
                $.ajax({
                    url: getApiUrl(`shifts/${shiftId}`),
                    method: "GET",
                    success: function (shift) {
                        const shiftInchargeId = shift.shift_incharger;

                        const cashFlowData = {
                            shift_id: parseInt(shiftId),
                            shift_incharge: shiftInchargeId,
                            total_cash: totalSales,
                            in_hand: inHand,
                            in_bank: inBank,
                            from_date: shiftStartTime,
                            to_date: endTime
                        };

                        const cashFlowPromise = new Promise((resolve) => {
                            $.ajax({
                                url: getApiUrl("shift-cash-flow"),
                                method: "POST",
                                contentType: "application/json",
                                data: JSON.stringify(cashFlowData),
                                success: function () {
                                    savedItems.push("cash flow");
                                    resolve();
                                },
                                error: function (xhr) {
                                    console.error("Cash flow save error:", xhr.responseText);
                                    resolve(); // Still continue
                                }
                            });
                        });

                        promises.push(cashFlowPromise);

                        // ✅ Execute all promises
                        Promise.all(promises)
                            .then(() => {
                                saveBtn.html(originalText).prop('disabled', false);
                                $("#save_loading").addClass('d-none');

                                showToast(`Shift closed successfully!`, "success");

                                // ✅ REDIRECT BACK TO SHIFTS PAGE AFTER 2 SECONDS
                                setTimeout(() => {
                                     window.location.href = "/shifts";
                                }, 2000);
                            })
                            .catch(error => {
                                console.error("Error closing shift:", error);
                                saveBtn.html(originalText).prop('disabled', false);
                                $("#save_loading").addClass('d-none');
                                showToast("Error closing shift! Some data may not have been saved.", "error");
                            });
                    },
                    error: function (xhr) {
                        console.error("Error fetching shift details:", xhr.responseText);
                        Promise.all(promises)
                            .then(() => {
                                saveBtn.html(originalText).prop('disabled', false);
                                $("#save_loading").addClass('d-none');
                                showToast(`Shift closed! (Cash flow not saved)`, "warning");
                                setTimeout(() => {
                                    window.location.href = "/shifts";
                                }, 2000);
                            })
                            .catch(error => {
                                console.error("Error closing shift:", error);
                                saveBtn.html(originalText).prop('disabled', false);
                                $("#save_loading").addClass('d-none');
                                showToast("Error closing shift!", "error");
                            });
                    }
                });
            });

            function getApiUrl(endpoint) {
                return `/api/${endpoint}`;
            }
        });
    </script>
@endsection