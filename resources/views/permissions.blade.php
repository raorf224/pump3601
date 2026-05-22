@extends('partials.layouts.master')

@section('title', 'Role Permissions | ' . Auth::user()->full_name)
@section('title-sub', 'Admin')
@section('pagetitle', 'Role Permissions')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>
#toast-container>.toast {
    width: auto !important;
    max-width: 350px !important;
    font-size: 14px !important;
}

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

.permission-card {
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    transition: all 0.2s ease-in-out;
}

.permission-card:hover {
    background-color: #f8f9fa;
    transform: translateY(-3px);
}

.permission-name {
    font-weight: 500;
    color: #333;
    font-size: 15px;
}

.permission-checkbox {
    transform: scale(1.2);
    cursor: pointer;
}

.grid-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    /* exactly 4 cards per row */
    gap: 15px;
}
</style>
@endsection

@section('content')
<div id="layout-wrapper" class="p-3">
    <h3 class="mb-3">Role Permissions</h3>

    <div class="mb-3">
        <label for="roleSelect" class="form-label fw-bold">Select Role:</label>
        <select id="roleSelect" class="form-select w-50">
            <option value="">-- Select Role --</option>
            <option value="2">Manager</option>
            <option value="3">Cashier</option>
            <option value="4">Pump Operator</option>
        </select>
    </div>

    <div id="permissionsSection">
        <h5 class="mb-3">Permissions for <span id="roleName" class="text-primary"></span></h5>
        <div id="permissionsContainer" class="grid-container"></div>
        <button id="savePermissions" class="btn btn-primary mt-3">💾 Save Changes</button>
    </div>
</div>
</main>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(function() {
    let allPermissions = [];

    // 🔹 Load all permissions on page load
    $.get('/api/getpermissions', function(res) {
        allPermissions = res;
        renderPermissions(allPermissions);
    });

    // 🔹 Render permission cards with CRUD checkboxes + main switch
   function renderPermissions(data) {
    let html = '';
    const hideList = ['nozzel-visualization', 'dispenser-visualization', 'tanks-visualization','account_report','expense_sheet','pos'];

    data.forEach(p => {
        const showCrud = !hideList.includes(p.name);

        html += `
        <div class="permission-card" data-id="${p.id}" data-name="${p.name}">
            <div>
                <span class="permission-name text-capitalize">${p.name.replaceAll('_', ' ')}</span>
                ${showCrud ? `
                <div class="mt-2">
                    <label class="me-2"><input type="checkbox" class="crud-checkbox form-check-input create"> Create</label>
                    <label class="me-2"><input type="checkbox" class="crud-checkbox form-check-input read"> Read</label>
                    <label class="me-2"><input type="checkbox" class="crud-checkbox form-check-input update"> Update</label>
                    <label><input type="checkbox" class="crud-checkbox form-check-input delete"> Delete</label>
                </div>
                ` : ''}
            </div>
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input permission-switch">
            </div>
        </div>`;
    });

    $('#permissionsContainer').html(html);
}


    // 🔹 Handle switch toggle
    $(document).on('change', '.permission-switch', function() {
        let card = $(this).closest('.permission-card');
        let checked = $(this).is(':checked');
        card.find('.crud-checkbox').prop('checked', checked);
    });

    // 🔹 Handle individual CRUD checkboxes
    $(document).on('change', '.crud-checkbox', function() {
        let card = $(this).closest('.permission-card');
        let anyChecked = card.find('.crud-checkbox:checked').length > 0;
        card.find('.permission-switch').prop('checked', anyChecked);
    });

    // 🔹 When role selected
    $('#roleSelect').change(function() {
        let roleId = $(this).val();
        if (!roleId) {
            $('#permissionsSection').hide();
            return;
        }

        $('#permissionsSection').show();
        let roleName = $("#roleSelect option:selected").text();
        $('#roleName').text(roleName);

        // Uncheck all
        $('.permission-switch, .crud-checkbox').prop('checked', false);

        $.get(`/api/get_rolespermission/${roleId}`, function(assignedPermissions) {
            if (!Array.isArray(assignedPermissions)) return;

            $('.permission-card').each(function() {
                let card = $(this);
                let id = card.data('id');

                let perm = assignedPermissions.find(p => p.id == id);
                if (perm) {
                    card.find('.permission-switch').prop('checked', true);
                    card.find('.crud-checkbox.create').prop('checked', perm.create ==
                    1);
                    card.find('.crud-checkbox.read').prop('checked', perm.read == 1);
                    card.find('.crud-checkbox.update').prop('checked', perm.update ==
                    1);
                    card.find('.crud-checkbox.delete').prop('checked', perm.delete ==
                    1);
                     } else {
                    card.find('.permission-switch, .crud-checkbox').prop('checked',
                        false);
                }
            });
        });

    });

    // 🔹 Save permissions
    $('#savePermissions').click(function() {
        let roleId = $('#roleSelect').val();
        if (!roleId) {
            toastr.warning('Please select a role first!');
            return;
        }

        let selectedPermissions = [];
        $('.permission-card').each(function() {
            let id = $(this).data('id');
            let card = $(this);
            if (card.find('.permission-switch').is(':checked')) {
                selectedPermissions.push({
                    id: id,
                    create: card.find('.crud-checkbox.create').is(':checked') ? 1 : 0,
                    read: card.find('.crud-checkbox.read').is(':checked') ? 1 : 0,
                    update: card.find('.crud-checkbox.update').is(':checked') ? 1 : 0,
                    delete: card.find('.crud-checkbox.delete').is(':checked') ? 1 : 0,
                });
            }
        });

        $.ajax({
            url: `/api/assignPermissions/${roleId}`,
            method: 'POST',
            data: {
                permissions: selectedPermissions,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('#savePermissions').prop('disabled', true).text('Saving...');
            },
            success: function(res) {
                toastr.success(res.message || 'Permissions updated successfully!');
            },
            error: function(err) {
                console.error(err);
                toastr.error('Something went wrong');
            },
            complete: function() {
                $('#savePermissions').prop('disabled', false).text('💾 Save Changes');
            }
        });
    });
});
</script>


@endsection