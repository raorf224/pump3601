@extends('partials.layouts.master')

@section('title', 'Attendance | ' . Auth::user()->full_name)
@section('title-sub', 'Employee')
@section('pagetitle', 'Attendance Records')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .attendance-row:hover {
            background-color: #f8f9fa;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-hover">
                        <div class="card-body">
                            <span class="text-muted fw-semibold">Total Employees</span>
                            <h4 class="mb-0" id="totalEmployees">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-hover">
                        <div class="card-body">
                            <span class="text-muted fw-semibold">Present Today</span>
                            <h4 class="mb-0" id="presentCount">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-hover">
                        <div class="card-body">
                            <span class="text-muted fw-semibold">Absent Today</span>
                            <h4 class="mb-0" id="absentCount">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-hover">
                        <div class="card-body">
                            <span class="text-muted fw-semibold">Not Marked</span>
                            <h4 class="mb-0" id="notMarkedCount">0</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="date" class="form-control" id="filter_date" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Role</label>
                            <select id="role" class="form-select">
                                <option value="">All Roles</option>
                                <option value="manager">Manager</option>
                                <option value="cashier">Cashier</option>
                                <option value="pump_operator">Pump Operator</option>
                                <option value="other">Others</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select id="filter_status" class="form-select">
                                <option value="">All Status</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="leave">Leave</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button class="btn btn-primary w-50" id="apply_filters">
                                <i class="fas fa-filter me-2"></i>Apply
                            </button>
                            <button class="btn btn-outline-secondary w-50" id="reset_filters">
                                <i class="fas fa-redo me-2"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee Name</th>
                                    <th>Designation</th>
                                    <th>Station</th>
                                    <th>Shift</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="attendance_table_body"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Attendance Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Mark Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="attendance_form">
                        <input type="hidden" id="attendance_id">
                        <input type="hidden" id="employee_id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee</label>
                                <!-- Hidden input for ID -->
                                <input type="hidden" id="employee_id">
                                <!-- Readonly visible input -->
                                <input id="employee_name" class="form-control" readonly>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label">Station</label>
                                <input type="text" class="form-control" id="station_name" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Shift</label>
                                <select id="shift_id" class="form-select"></select>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" id="date">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Check In</label>
                                <input type="time" class="form-control" id="check_in">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Check Out</label>
                                <input type="time" class="form-control" id="check_out">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select id="status" class="form-select">
                                    <option value="">Select Status</option>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                    <option value="leave">Leave</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" id="remarks" rows="1"
                                    placeholder="Optional remarks"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="save_attendance">
                        <i class="fas fa-save me-2"></i>Save Attendance
                    </button>
                </div>
            </div>
        </div>
    </div>
    </main>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script>
        const AUTH_USER_ID = "{{ Auth::id() }}";
        const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";


        $(function () {
            const tableBody = $("#attendance_table_body");

            function getStatusBadge(status) {
                const map = {
                    present: { class: "bg-success", text: "Present" },
                    absent: { class: "bg-danger", text: "Absent" },
                    late: { class: "bg-warning", text: "Late" },
                    leave: { class: "bg-info", text: "Leave" }
                };
                return `<span class="badge ${map[status]?.class || "bg-secondary"}">${map[status]?.text || "Not Marked"}</span>`;
            }

            function renderShift(shiftId) {
                return shiftId == 1 ? "Day Shift" : shiftId == 2 ? "Night Shift" : "N/A";
            }
            // Return an array of station IDs accessible to the current user
            function getAccessibleStationIds() {
                return new Promise((resolve) => {
                    if (AUTH_ROLE === 'admin') return resolve(null); // null -> means all stations

                    const url = AUTH_ROLE === 'employee' ? `/api/stations_emp/${AUTH_USER_ID}` : `/api/stations/${AUTH_USER_ID}`;
                    fetch(url).then(r => r.ok ? r.json() : []).then(list => {
                        const arr = Array.isArray(list) ? list : (list && Array.isArray(list.data) ? list.data : []);
                        resolve(arr.map(s => s.id));
                    }).catch(() => resolve([]));
                });
            }

            function loadShifts() {
                const shiftSelect = $("#shift_id");
                shiftSelect.empty().append(`<option value="">Select Shift</option>`);

                // First get accessible station IDs
                getAccessibleStationIds().then(stationIds => {
                    if (!stationIds || stationIds.length === 0) {
                        // No stations accessible, just show empty select
                        try {
                            if (window.shiftChoices) window.shiftChoices.destroy();
                            window.shiftChoices = new Choices("#shift_id", {
                                searchEnabled: true,
                                itemSelectText: "",
                                shouldSort: false,
                            });
                        } catch (e) { console.error('Choices init error', e); }
                        return;
                    }

                    // For each accessible station, fetch its open shifts
                    const shiftPromises = stationIds.map(stationId =>
                        fetch(`/api/open-shifts/${stationId}`)
                            .then(r => r.ok ? r.json() : [])
                            .catch(() => [])
                    );

                    Promise.all(shiftPromises).then(allShiftsArrays => {
                        // Flatten all shifts from all stations
                        const allShifts = allShiftsArrays.flat();

                        // Populate select with shifts
                        allShifts.forEach(shift => {
                            const text = `${shift.station_name} - ${shift.shift_type} (Shift #${shift.shift_no})`;
                            shiftSelect.append(`<option value="${shift.id}">${text}</option>`);
                        });

                        // Re-init Choices.js (if used)
                        try {
                            if (window.shiftChoices) window.shiftChoices.destroy();
                            window.shiftChoices = new Choices("#shift_id", {
                                searchEnabled: true,
                                itemSelectText: "",
                                shouldSort: false,
                            });
                        } catch (e) { console.error('Choices init error', e); }
                    }).catch(() => toastr.error("Failed to load shifts"));
                }).catch(() => toastr.error("Failed to load stations"));
            }

            function renderTable(data) {
                tableBody.html("");
                if (!data?.length) {
                    return tableBody.html(`<tr><td colspan="9" class="text-center py-4">No attendance records found</td></tr>`);
                }
                data.forEach((row, i) => {
                    const hasAttendance = row.attendance_id !== null;
                    tableBody.append(`
                                    <tr class="attendance-row ${!hasAttendance ? 'table-warning' : ''}">
                                        <td>${i + 1}</td>
                                        <td>${row.employee_name || "N/A"}</td>
                                        <td>${row.designation || "N/A"}</td>
                                        <td>${row.station_name || "N/A"}</td>
                                        <td>${hasAttendance ? (row.shift_name || "N/A") : "N/A"}</td>
                                        <td>${hasAttendance ? (row.check_in || "N/A") : "N/A"}</td>
                                        <td>${hasAttendance ? (row.check_out || "N/A") : "N/A"}</td>
                                        <td>${hasAttendance ? getStatusBadge(row.status) : getStatusBadge()}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm ${hasAttendance ? "btn-info" : "btn-success"} edit-btn"
                                                data-employee-id="${row.employee_id}"
                                                data-attendance-id="${row.attendance_id}">
                                                <i class="fas ${hasAttendance ? "fa-edit" : "fa-check"}"></i>
                                                ${hasAttendance ? "Edit" : "Mark"}
                                            </button>
                                        </td>
                                    </tr>`);
                });
            }

            // Shared fetcher that returns Promise<attendanceArray> depending on role and filters
            function fetchAttendance(filters = {}) {
                const params = new URLSearchParams(filters).toString();
                return new Promise((resolve, reject) => {
                    if (AUTH_ROLE === 'admin') {
                        fetch(`/api/attendance?${params}`).then(r => r.ok ? r.json() : Promise.reject(r)).then(res => {
                            const data = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
                            resolve(data);
                        }).catch(err => reject(err));
                        return;
                    }

                    if (AUTH_ROLE === 'employee') {
                        getAccessibleStationIds().then(stationIds => {
                            if (!stationIds || stationIds.length === 0) return resolve([]);
                            fetch(`/api/attendance?${params}`).then(r => r.ok ? r.json() : Promise.reject(r)).then(res => {
                                const all = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
                                const filtered = all.filter(a => stationIds.includes(a.station_id));
                                resolve(filtered);
                            }).catch(err => reject(err));
                        }).catch(err => reject(err));
                        return;
                    }

                    // Owner
                    fetch(`/api/user-attendance/${AUTH_USER_ID}?${params}`).then(r => r.ok ? r.json() : Promise.reject(r)).then(res => {
                        const data = Array.isArray(res) ? res : (res && Array.isArray(res.data) ? res.data : []);
                        resolve(data);
                    }).catch(err => reject(err));
                });
            }

            function loadAttendance(filters = {}) {
                fetchAttendance(filters).then(data => renderTable(data)).catch(() => toastr.error('Failed to load attendance'));
            }

            function loadSummary(filters = {}) {
                // If no filters provided, read from UI
                if (!filters || Object.keys(filters).length === 0) {
                    filters = {
                        date: $("#filter_date").val(),
                        role: $("#role").val(),
                        status: $("#filter_status").val()
                    };
                }

                fetchAttendance(filters).then(data => {
                    const totalEmployees = data.length || 0;
                    const presentCount = data.filter(r => r.status === 'present').length;
                    const absentCount = data.filter(r => r.status === 'absent').length;
                    const notMarkedCount = data.filter(r => r.attendance_id == null).length;

                    $("#totalEmployees").text(totalEmployees);
                    $("#presentCount").text(presentCount);
                    $("#absentCount").text(absentCount);
                    $("#notMarkedCount").text(notMarkedCount);
                }).catch(err => {
                    console.error('Error loading summary', err);
                    toastr.error('Failed to load summary');
                });
            }

            $("#apply_filters").click(() => {
                loadAttendance({
                    date: $("#filter_date").val(),
                    role: $("#role").val(),
                    status: $("#filter_status").val()
                });
            });

            $("#reset_filters").click(() => {
                $("#filter_date").val("{{ date('Y-m-d') }}");
                $("#role").val("");
                $("#filter_status").val("");
                loadAttendance();
            });

            tableBody.on("click", ".edit-btn", function () {
                const row = $(this).closest("tr");
                const empId = $(this).data("employee-id");
                const attId = $(this).data("attendance-id");

                $("#attendance_form")[0].reset();
                $("#attendance_id").val(attId || "");
                $("#employee_id").val(empId); // hidden field
                $("#employee_name").val(row.find("td:eq(1)").text()); // readonly field
                $("#station_name").val(row.find("td:eq(3)").text());

                if (attId) {
                    $.get(`/api/attendance/${attId}`, rec => {
                        $("#shift_id").val(rec.shift_id);
                        $("#date").val(rec.date);
                        $("#check_in").val(rec.check_in || "");
                        $("#check_out").val(rec.check_out || "");
                        $("#status").val(rec.status);
                        $("#remarks").val(rec.remarks || "");
                    });
                } else {
                    const today = new Date().toISOString().split("T")[0];
                    $("#date").val(today);
                    $("#status").val("present");
                }

                $("#modalTitle").text(attId ? "Edit Attendance" : "Mark Attendance");
                $("#attendanceModal").modal("show");
            });

            $("#save_attendance").click(() => {
                const id = $("#attendance_id").val();
                const payload = {
                    employee_id: $("#employee_id").val(),
                    shift_id: $("#shift_id").val(),
                    date: $("#date").val(),
                    check_in: $("#check_in").val() || null,
                    check_out: $("#check_out").val() || null,
                    status: $("#status").val(),
                    remarks: $("#remarks").val()
                };
                if (!payload.employee_id || !payload.shift_id || !payload.date || !payload.status) {
                    return toastr.error("Please fill employee, shift, date and status");
                }
                $.ajax({
                    url: id ? `/api/attendance/${id}` : "/api/attendance",
                    method: id ? "PUT" : "POST",
                    contentType: "application/json",
                    data: JSON.stringify(payload),
                    success: r => {
                        toastr.success(r.message || "Saved");
                        $("#attendanceModal").modal("hide");
                        loadAttendance();
                        loadSummary();
                    },
                    error: xhr => toastr.error(xhr.responseJSON?.error || "Error saving attendance")
                });
            });

            loadAttendance();
            loadSummary();
            loadShifts();
            setInterval(() => { loadAttendance(); loadSummary(); }, 300000);
        });
    </script>
@endsection