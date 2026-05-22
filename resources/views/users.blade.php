@extends('partials.layouts.master')

@section('title', 'Users | ' . Auth::user()->full_name)
@section('title-sub', 'Admin')
@section('pagetitle', 'Users')
@section('css')
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
#toast-container>.toast {
    width: auto !important;
    max-width: 350px !important;
    font-size: 14px !important;
}

/* Force toastr colors */
.toast-error {
    background-color: #BD362F !important;
    color: white !important;
}

.toast-success {
    background-color: #51A351 !important;
    color: white !important;
}

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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">Manage Users</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                        <i class="bi bi-person-plus"></i> Add User
                    </button>
                </div>

                <div class="table-responsive">
                    <table id="usersTable" class="table table-striped text-center w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <!-- End Users Table -->

    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="userForm" class="needs-validation" novalidate>
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Create User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">

                    <input type="hidden" id="user_id" name="id">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                        <div class="invalid-feedback">Please enter a username</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                        <div class="invalid-feedback">Please enter a valid email</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" id="password" class="form-control">
                        <div class="invalid-feedback">Please enter a password</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="full_name" id="full_name" class="form-control" required>
                        <div class="invalid-feedback">Please enter full name</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Role</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="">Select Roles</option>
                            <option value="admin">Admin</option>
                            <option value="owner">Owner</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </div>
        </form>
    </div>
</div>
</main>
@endsection



@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
// ✅ Toast function - Fixed with proper configuration
function showToast(message, type = 'success') {
    // Clear any existing toasts first
    toastr.clear();

    // Configure toastr
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

    // Show toast with proper type
    if (type === 'success') {
        toastr.success(message);
    } else if (type === 'error') {
        toastr.error(message);
    } else if (type === 'warning') {
        toastr.warning(message);
    } else {
        toastr.info(message);
    }
}

// ✅ Declare table variable in outer scope
let table;

$(document).ready(function() {
    // ✅ Load permissions first
    $.get(`/api/getpermissionbyuserid/{{Auth::user()->id}}/{{Auth::user()->role}}`, function(permissions) {
        userPermissions = permissions;
        console.log("Loaded permissions:", userPermissions);

        // Now initialize table only after permissions are ready
        initializeUsersTable();

        // Hide Add button if not allowed
        if (!hasPermission('users', 'create')) {
            $('[data-bs-target="#userModal"]').hide();
        }
    });

function initializeUsersTable() {
    table = $('#usersTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "/api/user",
            dataSrc: ""
        },
        dom: 'rtip',
        lengthChange: false,
        ordering: true, // ✅ Enable ordering
        order: [[0, 'desc']], // ✅ Order by first column (ID) DESC
        columns: [{
                data: 'id',
                render: function(data, type, row) {
                    return data ? data : 'N/A';
                }
            },
            {
                data: 'username',
                render: function(data, type, row) {
                    return data ? data : 'N/A';
                }
            },
            {
                data: 'email',
                render: function(data, type, row) {
                    return data ? data : 'N/A';
                }
            },
            {
                data: 'full_name',
                render: function(data, type, row) {
                    return data ? data : 'N/A';
                }
            },
            {
                data: 'phone',
                render: function(data, type, row) {
                    return data ? data : 'N/A';
                }
            },
            {
                data: 'role',
                render: function(data, type, row) {
                    return data ? data.charAt(0).toUpperCase() + data.slice(1) : 'N/A';
                }
            },
            {
                data: 'status',
                render: function(data) {
                    if (data == 1) {
                        return '<span class="badge bg-success">Active</span>';
                    } else if (data == 0) {
                        return '<span class="badge bg-danger">Inactive</span>';
                    } else {
                        return '<span class="badge bg-secondary">N/A</span>';
                    }
                }
            },
            {
                data: null,
                orderable: false, // ✅ Action column sorting disable
                className: "text-center",
                render: function(row) {
                    let buttons = '';

                    if (hasPermission('users', 'update')) {
                        buttons += `
                        <button class="btn btn-outline-primary btn-sm editUser" data-id="${row.id}">
                            <i class="bi bi-pencil-square"></i>
                        </button>`;
                    }

                    if (hasPermission('users', 'delete')) {
                        buttons += `
                        <button class="btn btn-outline-danger btn-sm deleteUser" data-id="${row.id}">
                            <i class="bi bi-trash"></i>
                        </button>`;
                    }

                    return buttons ?
                        `<div class="btn-group btn-group-sm" role="group">${buttons}</div>` :
                        `<span class="text-muted small">No actions</span>`;
                }
            }
        ],
        language: {
            emptyTable: "No users found",
            zeroRecords: "No matching users found"
        }
    });
}

    // ✅ Reset modal when opening for new user
    $('#userModal').on('show.bs.modal', function() {
        $('#userForm').removeClass('was-validated');
        $('#userModalLabel').text("Create User");
        $('#password').attr('required', true); // Add required for new user
    });

    // ✅ Save User (Create/Update)
    $('#userForm').submit(function(e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        let id = $('#user_id').val();
        let url = id ? `/api/updateuser/${id}` : "/api/createuser";
        let formData = $(this).serialize();

        $.ajax({
            url: url,
            method: "POST",
            data: formData,
            success: function(response) {
                $('#userModal').modal('hide');
                $('#userForm')[0].reset();
                $('#userForm').removeClass('was-validated');
                $('#user_id').val('');
                
                // ✅ Check if table is initialized before reloading
                if (table) {
                    table.ajax.reload(null, false); // false = stay on current page
                } else {
                    // If table not initialized, reload the page
                    location.reload();
                }
                
                showToast(id ? "User updated successfully!" : "User created successfully!");
            },
            error: function(xhr) {
                let errorMessage = "An error occurred";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors)[0][0];
                } else if (xhr.responseText) {
                    errorMessage = xhr.responseText;
                }
                showToast("Error: " + errorMessage, "error");
            }
        });
    });

    // ✅ Edit User
    $(document).on('click', '.editUser', function() {
        let id = $(this).data('id');
        $.get(`/api/user/${id}`, function(user) {
            $('#user_id').val(user.id);
            $('#username').val(user.username);
            $('#email').val(user.email);
            $('#password').val('').removeAttr('required'); // Clear password field for edit
            $('#full_name').val(user.full_name);
            $('#phone').val(user.phone);
            $('#status').val(user.status);
            $('#role').val(user.role);

            $('#userModalLabel').text("Edit User");
            $('#userModal').modal('show');
        }).fail(function() {
            showToast("Error loading user data", "error");
        });
    });

    // ✅ Delete User
    $(document).on('click', '.deleteUser', function() {
        if (!confirm("Are you sure you want to delete this user?")) return;
        let id = $(this).data('id');

        $.ajax({
            url: `/api/user/${id}`,
            method: "POST",
            data: {
                _method: "DELETE"
            },
            success: function() {
                if (table) {
                    table.ajax.reload(null, false);
                }
                showToast("User deleted successfully!", "success");
            },
            error: function(xhr) {
                let errorMessage = "Error deleting user";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showToast(errorMessage, "error");
            }
        });
    });
});

function hasPermission(moduleName, action) {
    const module = userPermissions.find(p => p.name === moduleName);
    if (!module) return false;
    return module[action] == 1;
}
</script>
@endsection