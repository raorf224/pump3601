@extends('partials.layouts.master')

@section('title', 'Employee | ' . Auth::user()->full_name)
@section('title-sub', 'Admin')
@section('pagetitle', 'Employee')

@section('css')
<!-- ✅ DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- ✅ Choices.js CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
/* Force toastr colors */
.toast-error {
    background-color: #BD362F !important;
    color: white !important;
}

.toast-success {
    background-color: #51A351 !important;
    color: white !important;
}

.toast-warning {
    background-color: #F89406 !important;
    color: white !important;
}

.toast-info {
    background-color: #2F96B4 !important;
    color: white !important;
}

/* Agar text white dikh raha hai */
.toast-message {
    color: white !important;
}
</style>


@endsection

@section('content')
<div id="layout-wrapper">
    <div class="container-fluid mt-4">

        <!-- Users Table -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex mb-3" id="add_employee">
                    <button class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#userModal"
                        id="addEmployeeBtn">
                        <i class="bi bi-person-badge"></i> Add Employee
                    </button>
                </div>

                <div class="table-responsive">
                    <table id="usersTable" class="table text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Station</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>City</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>

    </div><!-- End container-fluid -->
</div><!-- End layout-wrapper -->

<!-- Add/Edit Employee Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="userForm" class="needs-validation" novalidate>
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Create Employee</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">

                    <input type="hidden" id="user_id" name="id">

                    <!-- ✅ Station -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Station</label>
                        <select name="station_id" id="station" class="form-select" required>
                            <option value="">Select Station</option>
                        </select>
                        <div class="invalid-feedback">Please select a station</div>
                    </div>

                    <!-- ✅ Role -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Role</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="">Select Role</option>
                            <option value="manager">Manager</option>
                            <option value="cashier">Cashier</option>
                            <option value="pump_operator">Pump Operator</option>
                            <option value="other">Others</option>
                        </select>
                        <div class="invalid-feedback">Please select a role</div>
                    </div>

                    <!-- ✅ Username -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                        <div class="invalid-feedback">Please enter a username</div>
                    </div>

                    <!-- ✅ Email -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                        <div class="invalid-feedback">Please enter a valid email</div>
                    </div>

                    <!-- ✅ Password -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" id="password" class="form-control">
                        <div class="form-text">Leave blank to keep existing password</div>
                    </div>

                    <!-- ✅ Full Name -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="full_name" id="full_name" class="form-control" required>
                        <div class="invalid-feedback">Please enter full name</div>
                    </div>

                    <!-- ✅ Salary -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Salary</label>
                        <input type="number" name="salary" id="salary" class="form-control" required>
                        <div class="invalid-feedback">Please enter salary</div>
                    </div>

                    <!-- ✅ Phone -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="number" name="phone" id="phone" class="form-control">
                    </div>

                    <!-- ✅ CNIC -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">CNIC</label>
                        <input type="number" name="cnic" id="cnic" class="form-control">
                    </div>

                    <!-- ✅ Address -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" id="address" class="form-control">
                    </div>

                    <!-- ✅ City -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">City</label>
                        <input type="text" name="city" id="city" class="form-control">
                    </div>

                    <!-- ✅ Region -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Region</label>
                        <input type="text" name="region" id="region" class="form-control">
                    </div>

                    <!-- ✅ Country -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Country</label>
                        <input type="text" name="country" id="country" class="form-control">
                    </div>

                    <!-- ✅ Status -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Employee</button>
                </div>
            </div>
        </form>
    </div>
</div>
</main>
@endsection

@section('js')
<!-- ✅ jQuery (must come first) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- ✅ Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- ✅ DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- ✅ Choices.js -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<!-- ✅ Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
const API_BASE = "/api";
const AUTH_USER_ID = "{{ Auth::id() }}";
const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";

let table, stationChoices, roleChoices;
let currentTableAjaxUrl = null;
let employeeStationsCache = null;
		    userPermissions = []; // ✅ define globally to avoid ReferenceError


$(document).ready(function() {
    loadStations();


    $.get(`/api/getpermissionbyuserid/{{Auth::user()->id}}/{{Auth::user()->role}}`, function(permissions) {
        userPermissions = permissions;
        console.log("Loaded permissions:", userPermissions);



        // Hide Add button if not allowed
        if (!hasPermission('employees', 'create')) {
            $('#addEmployeeBtn').hide();
        }
    });
    roleChoices = new Choices("#role", {
        searchEnabled: true,
        shouldSort: false
    });

    // Role-aware DataTable init for employees
    (function initEmployeesTable() {
        const adminUrl = '/api/employees';
        const ownerUrl = `/api/user-employees/${AUTH_USER_ID}`;

        function createConfig(dataSourceIsAjax, source) {
            const base = {
                paging: false,
                info: false,
                searching: false,
                ordering: false,
                scrollX: false,
                scrollY: false,
                columns: [{
                        data: "employee_id"
                    },
                    {
                        data: "station_name"
                    },
                    {
                        data: "user_name"
                    },
                    {
                        data: "user_email"
                    },
                    {
                        data: "employee_role"
                    },
                    {
                        data: "city"
                    },
                    {
                        data: "phone"
                    },
                    {
                        data: "employee_status",
                        render: d => ((d || '').toString().toLowerCase() === "active") ?
                            `<span class="badge bg-success">Active</span>` :
                            `<span class="badge bg-danger">Inactive</span>`
                    },
                    {
                        data: "created_at"
                    },
                    {
                        data: null,
                        className: "text-center",
                        render: function(row) {
                            let buttons = '';

                            if (hasPermission('employees', 'update')) {
                                buttons += `
            <button class="btn btn-sm btn-outline-primary editUser" data-id="${row.employee_id}">
                <i class="bi bi-pencil-square"></i>
            </button>`;
                            }

                            if (hasPermission('employees', 'delete')) {
                                buttons += `
            <button class="btn btn-sm btn-outline-danger deleteUser" data-id="${row.employee_id}">
                <i class="bi bi-trash"></i>
            </button>`;
                            }

                            return buttons ?
                                `<div class="btn-group btn-group-sm" role="group">${buttons}</div>` :
                                `<span class="text-muted small">No actions</span>`;
                        }


                    }
                ]
            };

            if (dataSourceIsAjax) base.ajax = {
                url: source,
                dataSrc: ""
            };
            else base.data = source || [];
            return base;
        }

        function fetchEmployeeStations() {
            return fetch(`/api/stations_emp/${AUTH_USER_ID}`)
                .then(r => r.ok ? r.json() : [])
                .then(stations => {
                    employeeStationsCache = Array.isArray(stations) ? stations : (stations && Array
                        .isArray(stations.data) ? stations.data : []);
                    return employeeStationsCache;
                }).catch(() => []);
        }

        function fetchEmployeesByStation(stationId) {
            return fetch(`/api/employeebystation/${stationId}`).then(r => r.ok ? r.json() : []).catch(
                () => []);
        }

        function normalizeEmployeeRecord(r) {
            if (!r) return {};
            return {
                employee_id: r.employee_id || r.id || null,
                employee_role: r.employee_role || r.role || '',
                address: r.address || null,
                city: r.city || null,
                region: r.region || null,
                country: r.country || null,
                cnic: r.cnic || null,
                phone: r.phone || null,
                salary: r.salary || null,
                employee_status: r.employee_status || r.status || '',
                created_at: r.created_at || '',
                user_name: r.user_name || r.username || r.user_full_name || '',
                user_email: r.user_email || r.email || '',
                user_full_name: r.user_full_name || r.full_name || '',
                station_name: r.station_name || null,
                station_location: r.station_location || null
            };
        }

        function refreshEmployeesTable() {
            if (!table) return;
            if (currentTableAjaxUrl) {
                try {
                    if (table && table.ajax) {
                        table.ajax.url(currentTableAjaxUrl).load();
                        return;
                    }
                } catch (e) {
                    console.warn('Ajax reload failed', e);
                }
            }

            if (AUTH_ROLE === 'employee') {
                fetchEmployeeStations().then(stations => {
                    if (!stations || stations.length === 0) {
                        try {
                            table.clear().draw();
                        } catch (e) {};
                        return;
                    }
                    const calls = stations.map(s => fetchEmployeesByStation(s.id));
                    Promise.all(calls).then(results => {
                        const combined = results.flatMap(r => Array.isArray(r) ? r : (r &&
                            Array.isArray(r.data) ? r.data : []));
                        const normalized = combined.map(normalizeEmployeeRecord);
                        try {
                            table.clear();
                            table.rows.add(normalized);
                            table.draw(false);
                        } catch (e) {
                            console.warn('Failed to refresh employee inline table', e);
                        }
                    });
                });
            }
        }
        // expose for outside handlers (create/update/delete)
        window.refreshEmployeesTable = refreshEmployeesTable;

        if (AUTH_ROLE === 'admin') {
            currentTableAjaxUrl = adminUrl;
            table = $('#usersTable').DataTable(createConfig(true, adminUrl));
        } else if (AUTH_ROLE === 'employee') {
            fetchEmployeeStations().then(stations => {
                if (!stations || stations.length === 0) {
                    table = $('#usersTable').DataTable(createConfig(false, []));
                    return;
                }
                const calls = stations.map(s => fetchEmployeesByStation(s.id));
                Promise.all(calls).then(results => {
                    const combined = results.flatMap(r => Array.isArray(r) ? r : (r && Array
                        .isArray(r.data) ? r.data : []));
                    const normalized = combined.map(normalizeEmployeeRecord);
                    table = $('#usersTable').DataTable(createConfig(false, normalized));
                }).catch(err => {
                    console.error('Failed to fetch employees for stations', err);
                    table = $('#usersTable').DataTable(createConfig(false, []));
                });
            }).catch(err => {
                console.error('Failed to fetch employee stations', err);
                table = $('#usersTable').DataTable(createConfig(false, []));
            });
        } else {
            currentTableAjaxUrl = ownerUrl;
            table = $('#usersTable').DataTable(createConfig(true, ownerUrl));
        }
    })();

    $('#addEmployeeBtn').click(() => {
        $('#userForm')[0].reset();
        $('#user_id').val('');
        $('#password').attr("required", true);
        $('#userModalLabel').text("Create Employee");
        if (stationChoices) stationChoices.removeActiveItems();
    });

    // ✅ Edit Employee
    $(document).on('click', '.editUser', function() {
        let id = $(this).data('id');
        $.get(`${API_BASE}/employees/${id}`, function(u) {
            $('#user_id').val(u.employee_id);
            $('#username').val(u.user_name);
            $('#email').val(u.user_email);
            $('#full_name').val(u.user_full_name);
            $('#phone').val(u.phone);
            $('#salary').val(u.salary);
            $('#cnic').val(u.cnic);
            $('#address').val(u.address);
            $('#city').val(u.city);
            $('#region').val(u.region);
            $('#country').val(u.country);

            // ✅ Role Choices
            if (roleChoices) {
                roleChoices.setChoiceByValue(u.employee_role);
            }

            // ✅ Status (dropdown me 1/0 set hoga)
            $('#status').val(u.employee_status.toLowerCase());

            // ✅ Station Choices
            loadStations(u.station_id);

            $('#password').val("").removeAttr("required");
            $('#userModalLabel').text("Edit Employee");
            $('#userModal').modal('show');
        });
    });

    // ✅ Delete
    $(document).on('click', '.deleteUser', function() {
        let id = $(this).data('id');
        if (confirm("Are you sure?")) {
            $.ajax({
                url: `${API_BASE}/employees/${id}`,
                method: "DELETE",
                success: () => {
                    if (window.refreshEmployeesTable) {
                        try {
                            window.refreshEmployeesTable();
                        } catch (e) {
                            console.warn('refreshEmployeesTable failed', e);
                        }
                    } else if (table && table.ajax) {
                        try {
                            table.ajax.reload();
                        } catch (e) {
                            console.warn('table.ajax.reload failed', e);
                        }
                    }
                    toastr.success("Employee deleted!");
                },
                error: xhr => toastr.error(xhr.responseText)
            });
        }
    });

    // ✅ Save
    $('#userForm').submit(function(e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }
        let id = $('#user_id').val();
        id ? updateEmployee(id) : createEmployee();
    });
});


function getFormData() {
    let pass = $('#password').val();
    let payload = {
        username: $('#username').val(),
        email: $('#email').val(),
        full_name: $('#full_name').val(),
        phone: $('#phone').val(),
        station_id: $('#station').val(),
        role: $('#role').val(),
        salary: $('#salary').val(),
        cnic: $('#cnic').val(),
        address: $('#address').val(),
        city: $('#city').val(),
        region: $('#region').val(),
        country: $('#country').val(),
        status: $('#status').val() // ✅ ab enum value jaayegi ("active"/"inactive")
    };
    if (pass) payload.password = pass; // only send if entered
    return payload;
}

function createEmployee() {
    $.ajax({
        url: `${API_BASE}/employees`,
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify(getFormData()),
        success: () => {
            $('#userModal').modal('hide');
            if (window.refreshEmployeesTable) {
                try {
                    window.refreshEmployeesTable();
                } catch (e) {
                    console.warn('refreshEmployeesTable failed', e);
                }
            } else if (table && table.ajax) {
                try {
                    table.ajax.reload();
                } catch (e) {
                    console.warn('table.ajax.reload failed', e);
                }
            }
            toastr.success("Employee created!");
			window.location.reload();
        },
        error: xhr => handleAjaxError(xhr)
    });
}

function updateEmployee(id) {
    $.ajax({
        url: `${API_BASE}/employees/${id}`,
        method: "PUT",
        contentType: "application/json",
        data: JSON.stringify(getFormData()),
        success: () => {
            $('#userModal').modal('hide');
            if (window.refreshEmployeesTable) {
                try {
                    window.refreshEmployeesTable();
                } catch (e) {
                    console.warn('refreshEmployeesTable failed', e);
                }
            } else if (table && table.ajax) {
                try {
                    table.ajax.reload();
                } catch (e) {
                    console.warn('table.ajax.reload failed', e);
                }
            }
            toastr.success("Employee updated!");
        },
        error: xhr => handleAjaxError(xhr)
    });
}


function handleAjaxError(xhr) {
    let res = xhr.responseJSON;

    if (res) {
        if (res.errors) {
            // Laravel validation errors
            Object.values(res.errors).forEach(errArr => {
                errArr.forEach(err => {
                    // Explicit error toast with custom options
                    toastr.error(err, '', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-top-right',
                        timeOut: 5000,
                        extendedTimeOut: 1000
                    });
                });
            });
        } else if (res.message) {
            toastr.error(res.message, '', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 5000,
                extendedTimeOut: 1000
            });
        } else {
            toastr.error("❌ Unexpected error occurred.", '', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 5000,
                extendedTimeOut: 1000
            });
        }
    } else {
        let msg = xhr.responseText || "❌ Server not responding.";
        msg = msg.replace(/<\/?[^>]+(>|$)/g, "");
        toastr.error(msg, '', {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 5000,
            extendedTimeOut: 1000
        });
    }
}


function loadStations(selectedId = null) {
    let apiUrl;
    if (AUTH_ROLE === 'admin') {
        apiUrl = '/api/stations';
    } else if (AUTH_ROLE === 'employee') {
        apiUrl = `/api/stations_emp/${AUTH_USER_ID}`;
    } else {
        apiUrl = `/api/stations/${AUTH_USER_ID}`;
    }

    $.ajax({
        url: apiUrl,
        method: 'GET',
        success: function(resp) {
            const stations = Array.isArray(resp) ? resp : (resp && Array.isArray(resp.data) ? resp.data :
            []);
            let sel = $("#station");
            sel.empty().append('<option value="">Select Station</option>');

            stations.forEach(st => sel.append(
                `<option value="${st.id}">${st.name}</option>`
            ));

            if (stationChoices) stationChoices.destroy();

            stationChoices = new Choices("#station", {
                searchEnabled: true,
                shouldSort: false
            });

            // ✅ agar edit mode hai to station select karo
            if (selectedId) {
                stationChoices.setChoiceByValue(selectedId.toString());
            }
        },
        error: function() {
            toastr.error('Failed to load stations.');
        }
    });
}

function hasPermission(moduleName, action) {
    const module = userPermissions.find(p => p.name === moduleName);
    if (!module) return false;
    return module[action] == 1;
}
</script>

@endsection