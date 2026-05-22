@extends('partials.layouts.master')

@section('title', 'Salary Component | ' . Auth::user()->full_name)
@section('title-sub', 'HR Management')
@section('pagetitle', 'Salary Component')
@section('css')
<!-- Add this in your head section if not already present -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

</section>
@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <div class="card-header bg-white border-0 px-3 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Search Box -->
                            <div class="position-relative" style="width: 290px;">
                                <i class="position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                                <input type="text" id="tableSearch" class="form-control ps-5 rounded-pill"
                                    placeholder="Search component...">
                            </div>

                            <!-- Add Component Button -->
                            <button class="btn btn-primary rounded-pill px-3" data-bs-toggle="modal"
                                data-bs-target="#addComponentModal">
                                <i class="bi bi-plus-lg me-1"></i> Add Component
                            </button>
                        </div>
                    </div>



                    <div class="card p-3">
                        <div class="table-responsive">
                            <table id="salaryComponentTable" class="table no-wrap align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Component Name</th>
                                        <th>Type</th>
                                        <th>Calculation</th>
                                        <th>Amount</th>
                                        <th>Mandatory</th>
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
    </div>

    <!-- Add Component Modal -->
    <div class="modal fade" id="addComponentModal" tabindex="-1" aria-labelledby="addComponentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="addComponentForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addComponentModalLabel">Add Salary Component</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Add Component Name Field -->
                    <div class="mb-3">
                        <label class="form-label">Component Name</label>
                        <input type="text" name="component_name" class="form-control" placeholder="Enter component name"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="Earning">Earning</option>
                            <option value="Deduction">Deduction</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Calculation</label>
                        <select name="calculation" class="form-select" required>
                            <option value="">Select Calculation</option>
                            <option value="Percentage">Percentage</option>
                            <option value="Fixed">Fixed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="cal_ammount" class="form-control" placeholder="Enter amount" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mandatory</label>
                        <select name="mandatory" class="form-select" required>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>

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
                    <button type="submit" class="btn btn-success">Save Component</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Component Modal -->
    <div class="modal fade" id="editComponentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editComponentForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Salary Component</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="edit_id">

                    <!-- Add Component Name Field -->
                    <div class="mb-3">
                        <label class="form-label">Component Name</label>
                        <input type="text" id="edit_component_name" name="component_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select id="edit_type" name="type" class="form-select" required>
                            <option value="Earning">Earning</option>
                            <option value="Deduction">Deduction</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Calculation</label>
                        <select id="edit_calculation" name="calculation" class="form-select" required>
                            <option value="Percentage">Percentage</option>
                            <option value="Fixed">Fixed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" id="edit_cal_ammount" name="cal_ammount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mandatory</label>
                        <select id="edit_mandatory" name="mandatory" class="form-select" required>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
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
                    <button type="submit" class="btn btn-success">Update Component</button>
                </div>
            </form>
        </div>
    </div>

    </main>
@endsection

@section('js')
    <!-- ✅ jQuery (must be loaded first) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ✅ DataTables -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- ✅ Toastr (if used) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        const AUTH_USER_ID = "{{ Auth::id() }}";
        const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";

        $(document).ready(function () {
            // Toastr configuration for top right position
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            let table = $('#salaryComponentTable').DataTable({
                ajax: '/api/salary_component',
                columns: [
                    { data: 'id' },
                    { data: 'component_name' },
                    { data: 'type' },
                    { data: 'calculation' },
                    {
                        data: 'cal_ammount',
                        render: function (data, type, row) {
                            // If calculation is Percentage, add % symbol
                            if (row.calculation === 'Percentage') {
                                return data + '%';
                            }
                            // If calculation is Fixed, format as currency/regular number
                            return 'Rs.' + data;
                        }
                    },
                    { data: 'mandatory' },
                    {
                        data: 'status',
                        render: function (data, type, row) {
                            let checked = data === 'Active' ? 'checked' : '';
                            return `
                            <div class="form-check form-switch text-center">
                                <input class="form-check-input toggle-status" type="checkbox" data-id="${row.id}" ${checked}>
                            </div>`;
                        }
                    },
                    { data: 'created_at' },
                    {
                        data: null,
                        className: "text-center",
                        render: data => `
                        <button class="btn btn-sm btn-primary edit-btn" data-id="${data.id}">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${data.id}">
                            <i class="bi bi-trash"></i>
                        </button>`
                    }
                ],
                dom: 'rt',
                paging: false,
                ordering: false,
                info: false,
            });

            // ✅ Search box
            $('#tableSearch').on('keyup', function () {
                table.search(this.value).draw();
            });

            // ✅ Add component
            $('#addComponentForm').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: '/api/salary_component/store',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        $('#addComponentModal').modal('hide');
                        $('#addComponentForm')[0].reset();
                        table.ajax.reload();
                        toastr.success(response.message || 'Component added successfully!');
                    },
                    error: function (xhr) {
                        let errorMessage = 'Failed to add component';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            if (typeof xhr.responseJSON.error === 'object') {
                                errorMessage = 'Validation error: ' + Object.values(xhr.responseJSON.error).join(', ');
                            } else {
                                errorMessage = xhr.responseJSON.error;
                            }
                        }
                        toastr.error(errorMessage);
                    }
                });
            });

            // ✅ Edit button → load data
            $(document).on('click', '.edit-btn', function () {
                let id = $(this).data('id');
                $.get(`/api/salary_component/${id}`, function (res) {
                    $('#edit_id').val(res.id);
                    $('#edit_component_name').val(res.component_name);
                    $('#edit_type').val(res.type);
                    $('#edit_calculation').val(res.calculation);
                    $('#edit_cal_ammount').val(res.cal_ammount);
                    $('#edit_mandatory').val(res.mandatory);
                    $('#edit_status').val(res.status);
                    $('#editComponentModal').modal('show');
                }).fail(function (xhr) {
                    toastr.error('Failed to load component data: ' + (xhr.responseJSON?.error || 'Unknown error'));
                });
            });

            // ✅ Update component
            $('#editComponentForm').on('submit', function (e) {
                e.preventDefault();
                let id = $('#edit_id').val();
                $.ajax({
                    url: `/api/salary_component/update/${id}`,
                    type: 'PUT',
                    data: $(this).serialize(),
                    success: function (response) {
                        $('#editComponentModal').modal('hide');
                        table.ajax.reload();
                        toastr.success(response.message || 'Component updated successfully!');
                    },
                    error: function (xhr) {
                        let errorMessage = 'Update failed';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            if (typeof xhr.responseJSON.error === 'object') {
                                errorMessage = 'Validation error: ' + Object.values(xhr.responseJSON.error).join(', ');
                            } else {
                                errorMessage = xhr.responseJSON.error;
                            }
                        }
                        toastr.error(errorMessage);
                    }
                });
            });

            // ✅ Delete component
            $(document).on('click', '.delete-btn', function () {
                if (!confirm('Are you sure you want to delete this component?')) return;
                let id = $(this).data('id');
                $.ajax({
                    url: `/api/salary_component/delete/${id}`,
                    type: 'DELETE',
                    success: function (response) {
                        table.ajax.reload();
                        toastr.success(response.message || 'Component deleted successfully!');
                    },
                    error: function (xhr) {
                        let errorMessage = 'Delete failed';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        toastr.error(errorMessage);
                    }
                });
            });

            // ✅ Toggle status switch
            $(document).on('change', '.toggle-status', function () {
                let id = $(this).data('id');
                $.ajax({
                    url: `/api/salary_component/toggle-status/${id}`,
                    type: 'PATCH',
                    success: function (response) {
                        table.ajax.reload();
                        toastr.success(response.message || 'Status updated successfully!');
                    },
                    error: function (xhr) {
                        let errorMessage = 'Status update failed';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        toastr.error(errorMessage);
                    }
                });
            });
        });
    </script>


@endsection