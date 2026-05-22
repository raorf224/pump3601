@extends('partials.layouts.master')


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
                    <h4 class="fw-bold mb-0">Manage Role</h4>
                    <button class="btn btn-primary" id="addRoleBtn">
                        <i class="bi bi-person-plus"></i> Add Role
                    </button>
                </div>


                <table class="table table-striped text-center w-100" id="rolesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Permissions</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Role Modal -->
            <div class="modal fade" id="roleModal">
                <div class="modal-dialog">
                    <form id="roleForm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Role</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="role_id">
                                <div class="mb-3">
                                    <label>Name</label>
                                    <input type="text" id="role_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Permissions</label><br>
                                    @foreach($permissions as $perm)
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}">
                                    {{ $perm->name }}<br>
                                    @endforeach
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-primary" id="saveRoleBtn" type="submit">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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
    $(function() {
        let table = $('#rolesTable').DataTable({
            ajax: "{{ route('roles.index') }}",
            columns: [{
                    data: 'id'
                },
                {
                    data: 'name'
                },
                {
                    data: 'permissions'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('#addRoleBtn').click(() => {
            $('#roleForm')[0].reset();
            $('#role_id').val('');
            $('#roleModal').modal('show');
        });

        $('#roleForm').submit(function(e) {
            e.preventDefault();
            let id = $('#role_id').val();
            let url = id ? `api/roles/${id}` : 'api/roles';
            let method = id ? 'PUT' : 'POST';
            let permissions = [];
            $('input[name="permissions[]"]:checked').each(function() {
                permissions.push($(this).val());
            });
            $.ajax({
                url: url,
                method: method,
                data: {
                    name: $('#role_name').val(),
                    permissions: permissions,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    $('#roleModal').modal('hide');
                    table.ajax.reload();
                    alert(res.success);
                }
            });
        });

        $(document).on('click', '.editRole', function() {
            let id = $(this).data('id');
            $.get(`api/roles/${id}/edit`, function(role) {
                $('#role_id').val(role.id);
                $('#role_name').val(role.name);
                $('input[name="permissions[]"]').prop('checked', false);
                role.permissions.forEach(p => {
                    $(`input[value="${p.name}"]`).prop('checked', true);
                });
                $('#roleModal').modal('show');
            });
        });

        $(document).on('click', '.deleteRole', function() {
            if (confirm('Are you sure?')) {
                $.ajax({
                    url: `api/roles/${$(this).data('id')}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        table.ajax.reload();
                        alert(res.success);
                    }
                });
            }
        });
    });
    </script>
    @endsection