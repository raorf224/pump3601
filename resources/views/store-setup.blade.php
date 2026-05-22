@extends('partials.layouts.master')

@section('title', 'Store Setup | ' . Auth::user()->full_name)
@section('title-sub', 'Employee')
@section('pagetitle', 'Shift Management')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
@endsection

@section('content')
<div id="layout-wrapper">
    <div class="container-fluid mt-4">

        <div class="card shadow-sm">
            <div class="card-body">

                <!-- ✅ Toast Container -->
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
                    <div id="toastContainer"></div>
                </div>

                <!-- ✅ Accordion: Add / Edit Store -->
                <div class="accordion accordion-primary accordion-border-box mb-4" id="storeAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingStoreForm">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#storeFormCollapse" aria-expanded="true"
                                aria-controls="storeFormCollapse">
                                <i class="bi bi-shop me-2"></i> Add / Edit Store
                            </button>
                        </h2>

                        <div id="storeFormCollapse" class="accordion-collapse collapse show"
                            data-bs-parent="#storeAccordion">
                            <div class="accordion-body">
                                <form id="store_form">

                                    <!-- Station & Store Info -->
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label required-label">Select Station</label>
                                            <select class="form-select" id="station_id_select" required></select>
                                            <input type="hidden" name="station_id" id="station_id">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label required-label">Store Name</label>
                                            <input type="text" class="form-control" name="store_name" id="store_name"
                                                placeholder="Enter store name" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Store Code</label>
                                            <input type="text" class="form-control" name="store_code" id="store_code"
                                                placeholder="e.g., ST001">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label required-label">Owned By</label>
                                            <input type="text" class="form-control" name="owned_by" id="owned_by"
                                                placeholder="Enter owner name" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Manager Name</label>
                                            <input type="text" class="form-control" name="manager_name"
                                                id="manager_name" placeholder="Enter manager name">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Contact Number</label>
                                            <input type="text" class="form-control" name="contact_number"
                                                id="contact_number" placeholder="Enter phone number">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label required-label">Status</label>
                                            <select class="form-select" name="status" id="status" required>
                                                <option value="active" selected>Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Opening Date</label>
                                            <input type="date" class="form-control" name="opening_date"
                                                id="opening_date">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Closing Date</label>
                                            <input type="date" class="form-control" name="closing_date"
                                                id="closing_date">
                                        </div>
                                    </div>

                                    <input type="hidden" name="store_id" id="store_id">

                                    <div class="d-flex justify-content-end">
                                        <button type="button" id="resetBtn" class="btn btn-light me-2">Reset</button>
                                        <button type="submit" class="btn btn-primary">Save Store</button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ✅ Store Table -->
                <h5 class="card-title mb-3">Store Records</h5>
                <div class="table-responsive">
                    <table id="storeTable" class="table table-striped text-center w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Station</th>
                                <th>Store Name</th>
                                <th>Address</th>
                                <th>Store Code</th>
                                <th>Owned By</th>
                                <th>Status</th>
                                <th>Opening Date</th>
                                <th>Closing Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
</main>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
let storeTable, stationSelect, storeFormCollapse;
let allStations = [];
const AUTH_USER_ID = "{{ Auth::id() }}";
let userPermissions=[];
const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";
$(document).ready(function() {

    $.get(`/api/getpermissionbyuserid/{{Auth::user()->id}}/{{Auth::user()->role}}`, function(permissions) {
        userPermissions = permissions;
        console.log("Loaded permissions:", userPermissions);



        // Hide Add button if not allowed
        if (!hasPermission('store_setup', 'create')) {
            $('#storeAccordion').hide();
        }
        initDataTable();

    });
    // ✅ Set current date as default for opening date
    setCurrentDate();

    const collapseEl = document.getElementById('storeFormCollapse');
    storeFormCollapse = collapseEl ? new bootstrap.Collapse(collapseEl, {
        toggle: false
    }) : null;

    // ✅ Initialize Choices dropdown
    stationSelect = new Choices('#station_id_select', {
        searchPlaceholderValue: 'Search station...',
        shouldSort: false,
        itemSelectText: '',
    });

    loadStations();

    // ✅ Submit form
    $('#store_form').on('submit', function(e) {
        e.preventDefault();
        saveStore();
    });

    // ✅ Reset form button
    $('#resetBtn').on('click', function() {
        resetForm();
    });

    // ✅ Sync hidden field with dropdown
    $('#station_id_select').on('change', function(e) {
        $('#station_id').val(e.target.value);
    });
});

// ✅ Set current date as default
function setCurrentDate() {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const currentDate = `${yyyy}-${mm}-${dd}`;
    $('#opening_date').val(currentDate);
}

// ✅ Load stations globally
function loadStations() {
    let apiUrl;
    if (AUTH_ROLE === 'admin') {
        apiUrl = '/api/stations';
    } else if (AUTH_ROLE === 'employee') {
        apiUrl = `/api/stations_emp/${AUTH_USER_ID}`;
    } else {
        apiUrl = `/api/stations/${AUTH_USER_ID}`;
    }

    $.get(apiUrl, function(res) {
        const stations = res.data || res;
        allStations = stations;
        stationSelect.clearChoices();
        stationSelect.setChoices(
            stations.map(st => ({
                value: st.id,
                label: st.name
            })),
            'value', 'label', true
        );
    }).fail(() => showToast("Error loading stations", "danger"));
}

// ✅ Initialize DataTable
function initDataTable() {
    let apiUrl;
    console.log(AUTH_USER_ID, AUTH_ROLE);

    if (AUTH_ROLE === 'admin') {
        apiUrl = '/api/store';
    } else if (AUTH_ROLE === 'employee') {
        apiUrl = `/api/user-store/${AUTH_USER_ID}`;
    } else {
        apiUrl = `/api/user-store/${AUTH_USER_ID}`;
    }

    storeTable = $('#storeTable').DataTable({
        ajax: {
            url: apiUrl,
            dataSrc: '' // ✅ tells DataTables “data is at root”
        },
        destroy: true,
        paging: false,
        info: false,
        searching: false,
        ordering: false,
        columns: [{
                data: 'id'
            },
            {
                data: 'station_name'
            },
            {
                data: 'store_name'
            },
            {
                data: 'station_location'
            },
            {
                data: 'store_code'
            },
            {
                data: 'owned_by'
            },
            {
                data: 'status',
                render: data => `
                        <span class="badge bg-${data === 'active' ? 'success' : 'danger'} text-uppercase">
                            ${data}
                        </span>`
            },
            {
                data: 'opening_date'
            },
            {
                data: 'closing_date',
                render: data => data && data.trim() !== '' ? data : '—' // ✅ hide if empty
            },
            {
                data: null,
                render: function(row) {
                    let buttons = '';

                    if (hasPermission('store_setup', 'update')) {
                        buttons += `
                <button class="btn btn-sm btn-primary" onclick="editStore(${row.id})" title="Edit">
                    <i class="bi bi-pencil-square"></i>
                </button>`;
                    }

                    if (hasPermission('store_setup', 'delete')) {
                        buttons += `
                <button class="btn btn-sm btn-danger" onclick="deleteStore(${row.id})" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>`;
                    }

                    return buttons ?
                        `<div class="d-flex justify-content-center align-items-center gap-2">${buttons}</div>` :
                        `<span class="text-muted small">No actions</span>`;
                }
            }

        ]
    });
}

// ✅ Save or update store
function saveStore() {
    const id = $('#store_id').val();
    const data = {
        station_id: $('#station_id').val(),
        store_name: $('#store_name').val(),
        store_code: $('#store_code').val(),
        owned_by: $('#owned_by').val(),
        manager_name: $('#manager_name').val(),
        contact_number: $('#contact_number').val(),
        status: $('#status').val(),
        opening_date: $('#opening_date').val(),
        closing_date: $('#closing_date').val(),
    };

    const url = id ? `/api/store/${id}` : '/api/store';
    const method = id ? 'PUT' : 'POST';

    $.ajax({
        url,
        method,
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: () => {
            showToast(id ? 'Store updated successfully' : 'Store created successfully', 'success');

            // ✅ Reset everything & collapse accordion
            resetForm();

            // ✅ Reload table
            storeTable.ajax.reload(null, false);
        },
        error: () => showToast('Error saving store', 'danger')
    });
}

// ✅ Edit store
function editStore(id) {
    $.get(`/api/store/${id}`, function(res) {
        if (!res) return;

        console.log("Editing store:", res);

        $('#store_id').val(res.id);
        $('#store_name').val(res.store_name);
        $('#store_code').val(res.store_code);
        $('#owned_by').val(res.owned_by);
        $('#manager_name').val(res.manager_name);
        $('#contact_number').val(res.contact_number);
        $('#status').val(res.status);
        $('#opening_date').val(res.opening_date);
        $('#closing_date').val(res.closing_date);
        $('#station_id').val(res.station_id);

        // ✅ Update dropdown
        stationSelect.clearChoices();
        stationSelect.setChoices([{
            value: res.station_id,
            label: res.station_name,
            selected: true
        }], 'value', 'label', true);

        // ✅ Expand accordion for editing
        if (storeFormCollapse) storeFormCollapse.show();

        $('html, body').animate({
            scrollTop: $('#store_form').offset().top - 100
        }, 300);
    }).fail(() => showToast('Error loading store details', 'danger'));
}

// ✅ Delete store
function deleteStore(id) {
    if (!confirm('Are you sure you want to delete this store?')) return;
    $.ajax({
        url: `/api/store/${id}`,
        method: 'DELETE',
        success: () => {
            showToast('Store deleted successfully', 'success');
            storeTable.ajax.reload(null, false);
        },
        error: () => showToast('Error deleting store', 'danger')
    });
}

// ✅ Reset form + close accordion (after update or reset click)
function resetForm() {
    $('#store_form')[0].reset();
    $('#store_id').val('');

    // ✅ Reset to current date
    setCurrentDate();

    // ✅ Set default status to active
    $('#status').val('active');

    if (stationSelect) {
        stationSelect.clearStore(); // clears Choices internal store
        stationSelect.clearChoices(); // remove dropdown items
        loadStations(); // reload station list fresh
    }

    // ✅ Keep accordion open after reset (don't hide it)
    if (storeFormCollapse) {
        storeFormCollapse.show();
    }
}

// ✅ Toast helper
function showToast(msg, type = 'info') {
    $('.toast').remove();

    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;

    document.getElementById('toastContainer').appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();

    setTimeout(() => toast.remove(), 3000);
}

function hasPermission(moduleName, action) {
    const module = userPermissions.find(p => p.name === moduleName);
    if (!module) return false;
    return module[action] == 1;
}
</script>

@endsection