@extends('partials.layouts.master')

@section('title', 'Employee Salary Management | ' . Auth::user()->full_name)
@section('title-sub', 'HR Management')
@section('pagetitle', 'Employee Salary Management')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <style>
        .choices__inner {
            min-height: 45px;
            border-radius: 0.375rem !important;
            border: 1px solid #ced4da !important;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <input type="text" id="tableSearch" class="form-control w-25 rounded-pill" placeholder="Search...">
                        <button class="btn btn-primary rounded-pill px-3" data-bs-toggle="modal"
                            data-bs-target="#addAssignmentModal">
                            <i class="bi bi-plus-lg me-1"></i> Assign Salary Components
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="employeeSalaryTable" class="table no-wrap align-middle w-100">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Site</th>
                                    <th>Employee</th>
                                    <th>Salary</th>
                                    <th>Components</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Assignment Modal -->
    <div class="modal fade" id="addAssignmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="addAssignmentForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Salary Components</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Employee Selection -->
                    <div class="mb-3">
                        <label class="form-label">Select Employee</label>
                        <select name="emloye_id" id="employeeSelect" required></select>
                    </div>

                    <!-- Employee Details -->
                    <div class="row mb-3" id="employeeDetails" style="display:none;">
                        <div class="col-md-6">
                            <label class="form-label">Station Name</label>
                            <input type="text" id="stationName" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Base Salary</label>
                            <input type="text" id="employeeSalary" class="form-control" readonly>
                        </div>
                    </div>

                    <!-- Salary Components -->
                    <div class="mb-3">
                        <label class="form-label">Select Salary Components</label>
                        <select name="component_ids[]" id="componentSelect" multiple required></select>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Assign Components</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Assignment Modal -->
    <div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="editAssignmentForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Salary Components</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="edit_employee_id">
                    <!-- Employee Details -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee Name</label>
                            <input type="text" id="edit_employee_name" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Station Name</label>
                            <input type="text" id="edit_station_name" class="form-control" readonly>
                        </div>
                    </div>

                    <!-- Salary Components -->
                    <div class="mb-3">
                        <label class="form-label">Select Salary Components</label>
                        <select name="component_ids[]" id="edit_componentSelect" multiple required></select>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="edit_status" name="status" class="form-select" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Components</button>
                </div>
            </form>
        </div>
    </div>
    </main>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>

        const AUTH_USER_ID = "{{ Auth::id() }}";
        const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";


        $(document).ready(function () {
            // Toastr options - Fixed for better error display
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000",
        "extendedTimeOut": "2000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    // Initialize Choices.js
    let employeeChoices = new Choices('#employeeSelect', { searchEnabled: true, placeholder: true, removeItemButton: false });
    let componentChoices = new Choices('#componentSelect', { searchEnabled: true, placeholder: true, removeItemButton: true });
    let editComponentChoices = new Choices('#edit_componentSelect', { searchEnabled: true, placeholder: true, removeItemButton: true });

    let employeeList = []; // Store employees locally
    let componentList = []; // Store components locally

    // Helper: show API/validation errors in toast - FIXED
    function showError(xhr) {
        console.error('API Error:', xhr);
        let err = xhr.responseJSON?.error;

        if (!err) {
            err = 'Something went wrong. Please try again.';
        } else if (typeof err === 'object') {
            // Handle Laravel validation errors
            err = Object.values(err).flat().join('<br>');
        } else if (typeof err === 'string') {
            // Already a string error
            err = err;
        }

        toastr.error(err);
    }

    // ✅ Load Employees for dropdown - ROLE AWARE
    function loadEmployees() {
        let apiUrl;
        
        if (AUTH_ROLE === 'admin') {
            apiUrl = '/api/employees-dropdown';
        } else if (AUTH_ROLE === 'employee') {
            // Employee ke liye uska station id lekar aao
            apiUrl = `/api/employees-dropdown/by-employee/${AUTH_USER_ID}`;
        } else {
            // Owner ke liye uske stations ke employees
            apiUrl = `/api/employees-dropdown/${AUTH_USER_ID}`;
        }

        $.get(apiUrl, function (res) {
            employeeList = res;
            employeeChoices.clearStore();
            
            if (res.length === 0) {
                toastr.warning('No employees found for your station');
            }
            
            let choices = res.map(emp => ({
                value: emp.employee_id,
                label: `${emp.user_full_name} (${emp.station_name})`
            }));
            employeeChoices.setChoices(choices, 'value', 'label', true);
        }).fail(showError);
    }

    // Load Salary Components for dropdown
    function loadComponents() {
        $.get('/api/salary-components-dropdown', function (res) {
            componentList = res.data;
            componentChoices.clearStore();
            let choices = res.data.map(comp => ({
                value: comp.id,
                label: `${comp.component_name} (${comp.type} - ${comp.calculation} - ${comp.cal_ammount}${comp.calculation === 'Percentage' ? '%' : 'Rs.'})`
            }));
            componentChoices.setChoices(choices, 'value', 'label', true);
        }).fail(showError);
    }


            // Load dropdowns initially
            loadEmployees();
            loadComponents();

    // Auto-fill employee details when selected
    $('#employeeSelect').on('change', function () {
        const empId = $(this).val();
        if (!empId) { $('#employeeDetails').hide(); return; }
        const emp = employeeList.find(e => e.employee_id == empId);
        if (emp) {
            $('#stationName').val(emp.station_name);
            $('#employeeSalary').val('Rs. ' + Number(emp.salary).toLocaleString());
            $('#employeeDetails').show();
        } else {
            $('#employeeDetails').hide();
        }
    });

    // Reset modal on close
    $('#addAssignmentModal').on('hidden.bs.modal', function () {
        $('#addAssignmentForm')[0].reset();
        $('#employeeDetails').hide();
        employeeChoices.clearStore();
        componentChoices.clearStore();
        loadEmployees();
        loadComponents();
    });

            // Initialize DataTable - UPDATED with badge for status
let apiUrl;
    let table;


    if (AUTH_ROLE === 'admin') {
        apiUrl = '/api/employee-salary-management';
    } else if (AUTH_ROLE === 'employee') {
        // Employee ke liye uski station ka data
        apiUrl = `/api/employee-salary-management-by-employee/${AUTH_USER_ID}`;
    } else {
        apiUrl = `/api/employee-salary-management/${AUTH_USER_ID}`;
    }

    table = $('#employeeSalaryTable').DataTable({ 
        ajax: apiUrl,
        columns: [
            {
                data: null,
                render: (data, type, row, meta) => meta.row + 1
            },
            { data: 'station_name' },
            { data: 'employee_name' },
            {
                data: 'employee_salary',
                render: data => 'Rs. ' + Number(data).toLocaleString()
            },
            { data: 'components' },
            {
                data: 'status',
                render: (data) => {
                    const badgeClass = data === 'Active' ? 'bg-success' : 'bg-danger';
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            {
                data: 'created_at',
                render: d => new Date(d).toLocaleDateString('en-PK')
            },
            {
                data: null,
                className: 'text-center',
                render: data => `
                    <button class="btn btn-sm btn-primary edit-btn" data-employee-id="${data.employee_id}" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-employee-id="${data.employee_id}" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>`
            }
        ],
        dom: 'rt',
        paging: false,
        ordering: false,
        info: false,
        language: {
            emptyTable: "No salary assignments found",
            zeroRecords: "No matching records found"
        }
    });

            // Table search
            $('#tableSearch').on('keyup', function () {
                table.search(this.value).draw();
            });

            // Add assignment
            $('#addAssignmentForm').on('submit', function (e) {
                e.preventDefault();

                if (componentChoices.getValue(true).length === 0) {
                    toastr.error('Select at least one component');
                    return;
                }

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Assigning...');

                // Debug: Check what data is being sent
                const formData = $(this).serialize();
                console.log('Form data being sent:', formData);

                $.ajax({
                    url: '/api/employee-salary-management/store',
                    type: 'POST',
                    data: formData,
                    success: function (res) {
                        $('#addAssignmentModal').modal('hide');
                        $('#addAssignmentForm')[0].reset();
                        $('#employeeDetails').hide();
                        employeeChoices.clearStore();
                        componentChoices.clearStore();
                        loadEmployees();
                        loadComponents();
                        table.ajax.reload();
                        toastr.success(res.message || 'Components assigned successfully!');
                    },
                    error: function (xhr) {
                        console.log('Full error response:', xhr.responseJSON);
                        showError(xhr);
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Edit assignment - UPDATED to use same modal structure
            $(document).on('click', '.edit-btn', function () {
                let employeeId = $(this).data('employee-id');

                // Find employee details
                const employee = employeeList.find(emp => emp.employee_id == employeeId);
                if (employee) {
                    $('#edit_employee_id').val(employeeId);
                    $('#edit_employee_name').val(employee.user_full_name);
                    $('#edit_station_name').val(employee.station_name);
                }

                // Load current assignments for this employee
                $.get(`/api/employee-salary-management?employee_id=${employeeId}`)
                    .done(function (res) {
                        if (res.data && res.data.length > 0) {
                            const assignment = res.data[0];
                            $('#edit_status').val(assignment.status);

                            // Get component IDs for this employee
                            $.get(`/api/employee-components/${employeeId}`)
                                .done(function (componentsRes) {
                                    // Set selected components
                                    const componentIds = componentsRes.data.map(comp => comp.component_id);
                                    editComponentChoices.clearStore();

                                    let choices = componentList.map(comp => ({
                                        value: comp.id,
                                        label: `${comp.component_name} (${comp.type} - ${comp.calculation} - ${comp.cal_ammount}${comp.calculation === 'Percentage' ? '%' : 'Rs.'})`,
                                        selected: componentIds.includes(comp.id)
                                    }));

                                    editComponentChoices.setChoices(choices, 'value', 'label', true);
                                    $('#editAssignmentModal').modal('show');
                                })
                                .fail(showError);
                        }
                    })
                    .fail(showError);
            });

            // Update assignment - FIXED to use PUT method
            $('#editAssignmentForm').on('submit', function (e) {
                e.preventDefault();
                let employeeId = $('#edit_employee_id').val();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Updating...');

                // Use PUT method for update
                $.ajax({
                    url: `/api/employee-salary-management/update-employee/${employeeId}`,
                    type: 'PUT',
                    data: $(this).serialize(),
                    success: function (res) {
                        $('#editAssignmentModal').modal('hide');
                        table.ajax.reload();
                        toastr.success(res.message || 'Components updated successfully!');
                    },
                    error: function (xhr) {
                        console.log('Update error:', xhr.responseJSON);
                        showError(xhr);
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Delete assignment for employee (all components)
            $(document).on('click', '.delete-btn', function () {
                let employeeId = $(this).data('employee-id');
                if (!confirm('Are you sure you want to delete all components for this employee?')) return;

                $.ajax({
                    url: `/api/employee-salary-management/delete-all/${employeeId}`,
                    type: 'DELETE',
                    success: function (res) {
                        table.ajax.reload();
                        toastr.success(res.message || 'All components deleted successfully!');
                    },
                    error: showError
                });
            });
        });
    </script>
@endsection