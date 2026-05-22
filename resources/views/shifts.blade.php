@extends('partials.layouts.master')

@section('title', 'Shifts | ' . Auth::user()->full_name)
@section('title-sub', 'Employee')
@section('pagetitle', 'Shift Management')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <style>
        .badge-open {
            background-color: #28a745;
        }

        .badge-closed {
            background-color: #6c757d;
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

                    <!-- Accordion for Add / Edit Shift -->
                    <div class="accordion accordion-primary accordion-border-box mb-4" id="shiftAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingShiftForm">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#shiftFormCollapse" aria-expanded="true"
                                    aria-controls="shiftFormCollapse">
                                    <i class="bi bi-clock-history me-2"></i> Add / Edit Shift
                                </button>
                            </h2>
                            <div id="shiftFormCollapse" class="accordion-collapse collapse show"
                                data-bs-parent="#shiftAccordion">
                                <div class="accordion-body">
                                    <form id="shift_form">
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label required-label">Select Station</label>
                                                <select class="form-select" id="station_id_select" required>
                                                    <option value="">-- Select Station --</option>
                                                </select>
                                                <input type="hidden" name="station_id" id="station_id">
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label required-label">Shift</label>
                                                <select class="form-select" name="shift_no" id="shift_no" required>
                                                    <option value="1">Day</option>
                                                    <option value="2">Night</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label required-label">Shift Incharge</label>
                                                <select class="form-select" id="shift_incharger_select" required>
                                                    <option value="">-- Select Employee --</option>
                                                </select>
                                                <input type="hidden" name="shift_incharger" id="shift_incharger">
                                            </div>

<div class="col-md-2">
    <label class="form-label fw-semibold">Cash Handover</label>
    <input type="number" name="cash_handover" id="cash_handover"
        class="form-control" step="0.01" required>  
</div>

                                            <div class="col-md-3">
                                                <label class="form-label required-label">Start Time</label>
                                                <input type="datetime-local" class="form-control" name="start_time"
                                                    id="start_time" required>
                                            </div>
                                        </div>

                                        <input type="hidden" name="shift_id" id="shift_id">

                                        <div class="d-flex justify-content-end">
                                            <button type="reset" class="btn btn-light me-2">Reset</button>
                                            <button type="submit" class="btn btn-primary">Save Shift</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shifts Table -->
                    <h5 class="card-title mb-3">Shift Records</h5>
                    <div class="table-responsive">
                        <table id="shifts_table" class="table text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Station</th>
                                    <th>Shift</th>
                                    <th>Shift Incharge</th>
                                    <th>Cash Handover</th>
                                    <th>Cash Return</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="shifts_table_body"></tbody>
                        </table>
                    </div>

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
        const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";

        const stationSelect = new Choices('#station_id_select', {
            searchPlaceholderValue: 'Search station...',
            shouldSort: false
        });

        const employeeSelect = new Choices('#shift_incharger_select', {
            searchPlaceholderValue: 'Search employee...',
            shouldSort: false
        });

        let userPermissions = [];

        document.addEventListener("DOMContentLoaded", function () {
            // Dropdowns init
            const shiftNoSelect = new Choices('#shift_no', {
                shouldSort: false
            });

            const tableBody = $("#shifts_table_body");
            const form = $("#shift_form");

            // Load permissions
            $.get(`/api/getpermissionbyuserid/{{Auth::user()->id}}/{{Auth::user()->role}}`, function (permissions) {
                userPermissions = permissions;
                console.log("Loaded permissions:", userPermissions);

                // Hide Add button if not allowed
                if (!hasPermission('shifts', 'create')) {
                    $('#shiftAccordion').hide();
                }
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

            // ✅ Load last shift end time and set min start time
            function loadLastShiftEndTime(stationId) {
                if (!stationId) {
                    $('#start_time').prop('readonly', false).attr('min', '');
                    return;
                }

                $.ajax({
                    url: `api/last-shift-end-time/${stationId}`,
                    method: "GET",
                    success: function (response) {
                        if (response.min_start_time) {
                            $('#start_time').val(response.min_start_time)
                                .attr('min', response.min_start_time)
                                .prop('readonly', true);
                        } else {
                            $('#start_time').prop('readonly', false).attr('min', '');
                        }
                    },
                    error: function (xhr) {
                        // No previous shifts found, allow free input
                        if (xhr.status === 404) {
                            $('#start_time').prop('readonly', false).attr('min', '');
                        } else {
                            console.error("Error loading last shift end time:", xhr.responseText);
                            $('#start_time').prop('readonly', false).attr('min', '');
                        }
                    }
                });
            }

            // ✅ NEW: Load last shift cash return for cash handover
            function loadLastShiftCashReturn(stationId) {
    if (!stationId) {
        $('#cash_handover').prop('readonly', false).val('');
        return;
    }

    console.log("💰 Fetching last shift cash return for station:", stationId);
    
    $.ajax({
        url: getApiUrl(`last-shift-cash-return/${stationId}`),
        method: "GET",
        success: function (response) {
            console.log("💰 Cash Handover API Response:", response);
            
            if (response && response.last_cash_return !== null && response.last_cash_return !== undefined && parseFloat(response.last_cash_return) > 0) {
                // ✅ Format with 2 decimal places
                const formattedValue = parseFloat(response.last_cash_return).toFixed(2);
                $('#cash_handover').val(formattedValue)
                    .prop('readonly', true)
                    .css('background-color', '#f8f9fa')
                    .attr('title', 'Auto-filled from last shift cash return');
                
                console.log("✅ Cash handover auto-filled:", formattedValue);
            } else {
                $('#cash_handover').prop('readonly', false)
                    .val('')
                    .css('background-color', '')
                    .attr('title', 'Enter cash handover amount (First shift)');
                
                console.log("ℹ️ No previous cash return found or amount is 0");
            }
        },
        error: function (xhr) {
            console.error("❌ Error loading last shift cash return:", xhr.responseText);
            $('#cash_handover').prop('readonly', false)
                .val('')
                .css('background-color', '');
            
            if (xhr.status === 404) {
                console.log("ℹ️ No previous shifts found for this station");
                showToast("This appears to be the first shift for this station. Please enter cash handover amount.", "info");
            }
        }
    });
}

            // Load Stations
            let stationsCache = [];

            // Load Stations function ko update karo
function loadStations() {
    let endpoint;
    if (AUTH_ROLE === 'admin') {
        endpoint = 'stations';
    } else if (AUTH_ROLE === 'employee') {
        endpoint = `stations_emp/${AUTH_USER_ID}`;
    } else {
        endpoint = `stations/${AUTH_USER_ID}`;
    }

    return $.ajax({
        url: getApiUrl(endpoint),
        method: "GET",
        success: function (res) {
            const stations = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
            stationsCache = stations;

            try {
                stationSelect.clearChoices();
                stationSelect.setChoices(
                    stations.map(st => ({
                        value: st.id.toString(),
                        label: st.name
                    })),
                    'value', 'label', false
                );

                // ✅ NEW: Auto-select aur disable station for employees
                if (AUTH_ROLE === 'employee' && stations.length === 1) {
                    const stationId = stations[0].id.toString();
                    stationSelect.setChoiceByValue(stationId);
                    $("#station_id").val(stationId);

                    // ✅ Station dropdown ko disable karo
                    stationSelect.disable();

                    // ✅ IMPORTANT: Load employees, last shift end time, aur CASH HANDOVER
                    loadLastShiftEndTime(stationId);
                    loadEmployeesByStation(stationId);
                    loadLastShiftCashReturn(stationId); // ✅ YE LINE ADD KARO
                }
            } catch (e) {
                console.error('Error updating station choices', e);
            }
        },
        error: function (xhr) {
            console.error("Error loading stations:", xhr.responseText || xhr);
            showToast("Error loading stations!", "error");
        }
    });
}

			// ✅ NEW: Load last shift cash return for cash handover (Update this function)
function loadLastShiftCashReturn(stationId) {
    if (!stationId) {
        $('#cash_handover').prop('readonly', false).val('');
        return;
    }

    console.log("💰 Fetching last shift cash return for station:", stationId);
    
    $.ajax({
        url: getApiUrl(`last-shift-cash-return/${stationId}`),
        method: "GET",
        success: function (response) {
            console.log("💰 Cash Handover API Response:", response);
            
            if (response && response.last_cash_return !== null && response.last_cash_return !== undefined && response.last_cash_return > 0) {
                // Previous shift ka cash return hai
                $('#cash_handover').val(response.last_cash_return)
                    .prop('readonly', true)
                    .css('background-color', '#f8f9fa')
                    .attr('title', 'Auto-filled from last shift cash return');
                
                console.log("✅ Cash handover auto-filled:", response.last_cash_return);
            } else {
                // Pehli shift hai, user input de sakta hai
                $('#cash_handover').prop('readonly', false)
                    .val('')
                    .css('background-color', '')
                    .attr('title', 'Enter cash handover amount (First shift)');
                
                console.log("ℹ️ No previous cash return found or amount is 0");
            }
        },
        error: function (xhr) {
            console.error("❌ Error loading last shift cash return:", xhr.responseText);
            // Agar error aaye to user input de sakta hai
            $('#cash_handover').prop('readonly', false)
                .val('')
                .css('background-color', '');
            
            if (xhr.status === 404) {
                console.log("ℹ️ No previous shifts found for this station");
                showToast("This appears to be the first shift for this station. Please enter cash handover amount.", "info");
            }
        }
    });
}


            // Load employees by station
            function loadEmployeesByStation(stationId) {
                return new Promise((resolve, reject) => {
                    if (!stationId) {
                        employeeSelect.clearChoices();
                        employeeSelect.setChoices([{ value: '', label: 'Select Employee', selected: true }], 'value', 'label', true);
                        resolve([]);
                        return;
                    }

                    $.ajax({
                        url: `/api/employees/station/${stationId}`,
                        method: "GET",
                        success: function (employees) {
                            const employeeChoices = employees.map(emp => ({
                                value: emp.employee_id.toString(),
                                label: emp.user_full_name || emp.user_name || `Employee ${emp.employee_id}`
                            }));

                            employeeChoices.unshift({ value: '', label: 'Select Employee', selected: true, disabled: true });
                            employeeSelect.setChoices(employeeChoices, 'value', 'label', true);
                            resolve(employees);
                        },
                        error: function (xhr) {
                            console.error("Error loading employees:", xhr.responseText);
                            employeeSelect.setChoices([{ value: '', label: 'Error loading employees' }], 'value', 'label', true);
                            reject(xhr.responseText);
                        }
                    });
                });
            }

            // ✅ UPDATED: Sync hidden inputs with dropdowns - Added start time validation
            stationSelect.passedElement.element.addEventListener("change", function (e) {
                const stationId = e.detail.value;
                $("#station_id").val(stationId);

                if (stationId) {
                    loadLastShiftEndTime(stationId);
                    loadEmployeesByStation(stationId);
                    loadLastShiftCashReturn(stationId);
                } else {
                    $('#start_time').prop('readonly', false).attr('min', '');
                    $('#cash_handover').prop('readonly', false).val('');
                    employeeSelect.clearChoices();
                }
            });

            employeeSelect.passedElement.element.addEventListener("change", function (e) {
                $("#shift_incharger").val(e.detail.value);
            });

            // ✅ UPDATED: Reset form event
            form.on("reset", function () {
                setTimeout(() => {
                    $("#shift_incharger, #shift_id").val("");
                    $('#start_time').prop('readonly', false).attr('min', '');
                    employeeSelect.removeActiveItems();

                    // ✅ Agar employee hai aur single station hai toh wapas select karo
                    if (AUTH_ROLE === 'employee' && stationsCache.length === 1) {
                        const stationId = stationsCache[0].id.toString();
                        stationSelect.setChoiceByValue(stationId);
                        $("#station_id").val(stationId);
                        loadLastShiftEndTime(stationId);
                        loadEmployeesByStation(stationId);
                    } else {
                        $("#station_id").val("");
                        stationSelect.removeActiveItems();
                    }
                }, 100);
            });

            // ✅ UPDATED: Render rows helper - FIXED THE ERROR
function renderShiftRows(shifts) {
    tableBody.html("");

    if (!Array.isArray(shifts) || shifts.length === 0) return;

    shifts.forEach(function (shift, index) {
        const stationName = shift.station_name || shift.station?.name || '';
        const shiftType = shift.shift_type || shift.shift_no || '';
        const start = shift.start_time || '';
        const end = shift.end_time
            ? `<span class="badge bg-success">${shift.end_time}</span>`
            : `<span class="badge bg-danger">Not Ended</span>`;
        const status = shift.status || '';
        const created = shift.created_at || '';
        const id = shift.id || '';
        const shiftIncharger = shift.shift_incharger_name || 'Not Assigned';
        
        // ✅ Format decimal values with 2 decimal places
        const cash_handover = shift.cash_handover ? parseFloat(shift.cash_handover).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) : '0.00';
        
        const cash_return = shift.cash_return ? parseFloat(shift.cash_return).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) : '0.00';

        // ✅ Build action buttons based on permissions
        let actionButtons = '';
        
        // ✅ REPORT BUTTON - Only show if shift is closed
        const reportButton = status === 'closed'
            ? `<a href="/shift-reports/${id}" class="btn btn-sm btn-success me-1" target="_blank">View Report</a>`
            : '';

        if (status === 'open') {
            // Open shift actions
            if (hasPermission('shifts', 'update')) {
                actionButtons += `<button class="btn btn-sm btn-info edit-btn me-1" data-id="${id}">Edit</button>`;
            }
            if (hasPermission('shifts', 'delete')) {
                actionButtons += `<button class="btn btn-sm btn-warning close-shift-btn me-1" data-id="${id}">Close Shift</button>`;
            }
            actionButtons += reportButton;
        } else {
            // Closed shift actions
            if (hasPermission('shifts', 'update')) {
                actionButtons += `<a href="edit-close-shift/${id}" class="btn btn-sm btn-warning me-1">Edit Shift</a>`;
            }
            actionButtons += reportButton;
            actionButtons += `<span class="badge bg-secondary ms-1">Closed</span>`;
        }

        // If no actions allowed
        if (!actionButtons) {
            actionButtons = `<span class="text-muted small">No actions</span>`;
        }

        tableBody.append(`
            <tr>
                <td>${index + 1}</td>
                <td>${stationName}</td>
                <td>${shiftType}</td>
                <td>${shiftIncharger}</td>
                <td>${cash_handover}</td>
                <td>${cash_return}</td>
                <td>${start}</td>
                <td>${end}</td>
                <td><span class="badge bg-${status === 'open' ? 'success' : 'secondary'}">${status}</span></td>
                <td>${created}</td>
                <td class="text-center">${actionButtons}</td>
            </tr>
        `);
    });
}
            // ✅ Load All Shifts (role-aware)
            function loadShifts() {
                tableBody.html("");

                if (AUTH_ROLE === 'admin') {
                    $.ajax({
                        url: getApiUrl('shifts'),
                        method: 'GET',
                        success: function (res) {
                            const data = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
                            renderShiftRows(data);
                        },
                        error: function (xhr) {
                            console.error('Error loading shifts (admin):', xhr.responseText || xhr);
                            showToast('Error loading shifts!', 'error');
                        }
                    });
                    return;
                }

                if (AUTH_ROLE === 'employee') {
                    fetch(getApiUrl(`stations_emp/${AUTH_USER_ID}`)).then(r => r.ok ? r.json() : []).then(stations => {
                        stations = Array.isArray(stations) ? stations : (stations && Array.isArray(stations.data) ? stations.data : []);
                        const stationIds = stations.map(s => s.id);

                        $.ajax({
                            url: getApiUrl('shifts'),
                            method: 'GET',
                            success: function (res) {
                                const data = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
                                const filtered = data.filter(s => stationIds.includes(s.station_id));
                                renderShiftRows(filtered);
                            },
                            error: function (xhr) {
                                console.error('Error loading shifts for employee:', xhr.responseText || xhr);
                                showToast('Error loading shifts!', 'error');
                            }
                        });
                    }).catch(err => {
                        console.error('Error fetching employee stations:', err);
                        showToast('Error loading shifts!', 'error');
                    });
                    return;
                }

                $.ajax({
                    url: getApiUrl(`user-shifts/${AUTH_USER_ID}`),
                    method: 'GET',
                    success: function (res) {
                        const data = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
                        renderShiftRows(data);
                    },
                    error: function (xhr) {
                        console.error('Error loading shifts (owner):', xhr.responseText || xhr);
                        showToast('Error loading shifts!', 'error');
                    }
                });
            }

            // ✅ NEW: Check if open shift exists for this station
            function checkOpenShift(stationId) {
                return $.ajax({
                    url: getApiUrl(`open-shifts/${stationId}`),
                    method: "GET"
                });
            }

            // ✅ UPDATED: Save Shift with validation - Status hardcoded as "open"
            form.on("submit", function (e) {
                e.preventDefault();

                const shiftId = $("#shift_id").val();
                const shiftIncharger = $("#shift_incharger").val();
                const cashHandover = $("#cash_handover").val();
                const startTime = $("#start_time").val();
                const stationId = $("#station_id").val();

                if (!shiftIncharger) {
                    showToast("Please select a shift incharge!", "error");
                    return;
                }

                if (!cashHandover || isNaN(cashHandover) || parseFloat(cashHandover) < 0) {
                    showToast("Please enter a valid cash handover amount!", "error");
                    return;
                }

                if (!startTime) {
                    showToast("Please enter start time!", "error");
                    return;
                }

                // ✅ Check if open shift already exists for this station
                if (!shiftId) { // Only for new shifts, not for edits
                    checkOpenShift(stationId).then(function (response) {
                        if (response && response.length > 0) {
                            showToast("Cannot create new shift! There is already an open shift for this station. Please close the existing shift first.", "error");
                            return;
                        }
                        // Continue with shift creation
                        saveShiftData(shiftId, shiftIncharger, cashHandover, startTime, stationId);
                    }).catch(function (xhr) {
                        if (xhr.status === 404) {
                            // No open shift found, continue with creation
                            saveShiftData(shiftId, shiftIncharger, cashHandover, startTime, stationId);
                        } else {
                            console.error("Error checking open shifts:", xhr.responseText);
                            showToast("Error checking shift status!", "error");
                        }
                    });
                } else {
                    // For edit, directly save
                    saveShiftData(shiftId, shiftIncharger, cashHandover, startTime, stationId);
                }
            });

            // ✅ NEW: Separate function for saving shift data
            function saveShiftData(shiftId, shiftIncharger, cashHandover, startTime, stationId) {
    // ✅ Ensure cash_handover is sent with proper decimal format
    const formattedCashHandover = parseFloat(cashHandover).toFixed(2);
    
    const payload = {
        station_id: parseInt(stationId, 10),
        shift_no: parseInt($("#shift_no").val(), 10),
        shift_incharger: parseInt(shiftIncharger, 10),
        cash_handover: formattedCashHandover,  // ✅ Send as string with 2 decimals
        start_time: startTime,
        status: "open",
    };

    let url = getApiUrl("shifts");
    let method = "POST";

    if (shiftId) {
        url = getApiUrl(`shifts/${shiftId}`);
        method = "PUT";
    }

    $.ajax({
        url: url,
        method: method,
        contentType: "application/json",
        data: JSON.stringify(payload),
        success: function () {
            form[0].reset();
            $("#station_id, #shift_incharger, #shift_id").val("");
            stationSelect.removeActiveItems();
            employeeSelect.removeActiveItems();
            $('#start_time').prop('readonly', false).attr('min', '');

            if (AUTH_ROLE === 'employee' && stationsCache.length === 1) {
                const stationId = stationsCache[0].id.toString();
                stationSelect.setChoiceByValue(stationId);
                $("#station_id").val(stationId);
                loadLastShiftEndTime(stationId);
                loadEmployeesByStation(stationId);
            }

            loadShifts();
            showToast("Shift saved successfully!", "success");
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errorData = xhr.responseJSON;
                showToast(errorData.message, "error");
                if (errorData.min_start_time) {
                    $('#start_time').val(errorData.min_start_time)
                        .attr('min', errorData.min_start_time)
                        .prop('readonly', true);
                    showToast(`Start time auto-set to required minimum time`, "info");
                }
            } else {
                console.error("Error saving shift:", xhr.responseText);
                showToast("Failed to save shift!", "error");
            }
        }
    });
}

            // Edit Shift
            tableBody.on("click", ".edit-btn", function () {
                const id = $(this).data("id");

                $.ajax({
                    url: getApiUrl(`shifts/${id}`),
                    method: "GET",
                    success: function (shift) {
                        $("#shift_id").val(shift.id);
                        $("#station_id").val(shift.station_id);

                        // Set form values
                        stationSelect.setChoiceByValue(shift.station_id.toString());
                        shiftNoSelect.setChoiceByValue(shift.shift_no.toString());

                        $("#start_time").val(shift.start_time);
                        $("#cash_handover").val(shift.cash_handover);

                        console.log("Start time from API:", shift.start_time);

                        // Load and set employee
                        loadEmployeesByStation(shift.station_id).then(() => {
                            setTimeout(() => {
                                employeeSelect.setChoiceByValue(shift.shift_incharger.toString());
                                $("#shift_incharger").val(shift.shift_incharger.toString());
                            }, 300);
                        });

                        // ✅ For edit mode, don't apply readonly restriction
                        $('#start_time').prop('readonly', false).attr('min', '');
                    },
                    error: function (xhr) {
                        console.error("Error fetching shift:", xhr.responseText);
                        showToast("Error fetching shift!", "error");
                    }
                });
            });

            // ✅ UPDATED: Close Shift Button Click - Redirect to new page
            tableBody.on("click", ".close-shift-btn", function () {
                const shiftId = $(this).data("id");

                // Redirect to close shift page - FIXED URL
                window.location.href = "/close?shift_id=" + shiftId;
            });

            function getApiUrl(endpoint) {
                return `/api/${endpoint}`;
            }

            console.log('Shifts API URL:', getApiUrl('shifts'));

            // Initialize
            loadStations().then(() => {
                loadShifts();
            });
        });

        function hasPermission(moduleName, action) {
            const module = userPermissions.find(p => p.name === moduleName);
            if (!module) return false;
            return module[action] == 1;
        }
    </script>
@endsection