@extends('partials.layouts.master')

@section('title', 'Stations | ' . Auth::user()->full_name)

@section('title-sub', 'Admin')
@section('pagetitle', 'Stations')
@section('css')
<link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
<style>
#stationsTable tbody td:last-child,
/* last column */
#stationsTable thead th:last-child {
    text-align: center;
}

.toast-progress {
    height: 3px;
    background: rgba(255, 255, 255, 0.8);
    animation: progressBar 3s linear forwards;
}

@keyframes progressBar {
    from {
        width: 100%;
    }

    to {
        width: 0%;
    }
}

.station-link:hover {
    color: #0056b3;
    text-decoration: none;
}
</style>


@endsection
@section('content')

<div id="layout-wrapper">
    <div class="container-fluid mt-4">

        <!-- Stations Table -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-end">
                    <a href="station-create">
                        <button class="btn btn-primary" id="add_station">
                            <i class="bi bi-fuel-pump"></i> Add Station
                        </button>
                    </a>
                </div>

                <div class="card-body p-0">
                    <div class="table-box table-responsive">
                        <table id="stationsTable" class="table text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Site Name</th>
                                    <th scope="col">Total Employe</th>
                                    <th scope="col">Overall Tank Capacity</th>
                                    <th scope="col">City</th>
                                    <th scope="col">Status</th>
                                    <!-- <th scope="col">Profile</th> -->
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Rows will be injected via AJAX -->
                            </tbody>
                        </table>
                        <div id="paginationContainer"></div>
                    </div>

                </div>
            </div>
        </div>

    </div><!-- End container-fluid -->
</div><!-- End layout-wrapper -->


<!-- Add/Edit Station Modal -->
<div class="modal fade" id="stationModal" tabindex="-1" aria-labelledby="stationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="stationForm" class="needs-validation" novalidate>
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header">
                    <h5 class="modal-title" id="stationModalLabel">Create Station</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">

                    <input type="hidden" id="station_id" name="id">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Owner Name</label>
                        <select name="user_id" id="user_id" class="form-select" required style="width: 100%;">
                            <option value="">Select Owner</option>
                        </select>
                        <div class="invalid-feedback">Please select owner name</div>
                    </div>




                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                        <div class="invalid-feedback">Please enter station name</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Location</label>
                        <input type="text" name="location" id="location" class="form-control" required>
                        <div class="invalid-feedback">Please enter location</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">City</label>
                        <input type="text" name="city" id="city" class="form-control" required>
                        <div class="invalid-feedback">Please enter city</div>
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
                    <button type="submit" class="btn btn-primary">Save Station</button>
                </div>
            </div>
        </form>
    </div>
</div>

</main>
@endsection

@section('js')


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="module" src="assets/js/app.js"></script>

<script src="assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>

<script src="assets/js/app/project-list.init.js"></script>

<script type="module">
const AUTH_USER_ID = "{{ Auth::id() }}";
const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";
console.log("Authenticated User ID:", AUTH_USER_ID);

let stationsData = [];
let currentPage = 1;
let perPage = 10;
let userPermissions = [];

$(document).ready(function() {

    // ✅ Load permissions first
    $.get(`/api/getpermissionbyuserid/{{Auth::user()->id}}/{{Auth::user()->role}}`, function(permissions) {
        userPermissions = permissions;
        console.log("Loaded permissions:", userPermissions);

        // ✅ Hide Add button if no create permission
        if (!hasPermission('sites', 'create')) {
            $('#add_station').hide();
        }

        // ✅ Load table data after permissions
        loadStations();
    });

    // ✅ Edit Station
    $(document).on('click', '.editStation', function() {
        const stationId = $(this).data('id');
        $.ajax({
            url: `/api/stations/${stationId}`,
            method: 'GET',
            success: function(station) {
                $('#station_id').val(station.id);
                $('#name').val(station.name);
                $('#location').val(station.location);
                $('#city').val(station.city);
                $('#status').val(station.status);
                $('#user_id').val(station.user_id).trigger('change');
                $('#stationModal').modal('show');
            },
            error: function() {
                alert('Failed to fetch station data.');
            }
        });
    });

    // ✅ Delete Station
    $(document).on('click', '.deleteStation', function() {
        const stationId = $(this).data('id');
        if (!confirm('Are you sure you want to delete this station?')) return;

        $.ajax({
            url: `/api/stations/${stationId}`,
            method: 'DELETE',
            success: function() {
                loadStations();
                showToast('Station deleted successfully.', 'success');
            },
            error: function() {
                showToast('Failed to delete station.', 'danger');
            }
        });
    });

    // ✅ Modal show event
    $('#stationModal').on('show.bs.modal', function() {
        loadOwners();
    });
});

// ✅ Load all stations
function loadStations() {
    let apiUrl;
    if (AUTH_ROLE === 'admin') apiUrl = '/api/stations';
    else if (AUTH_ROLE === 'employee') apiUrl = `/api/stations_emp/${AUTH_USER_ID}`;
    else apiUrl = `/api/stations/${AUTH_USER_ID}`;

    $.ajax({
        url: apiUrl,
        method: 'GET',
        success: function(response) {
            stationsData = response;
            renderTable();
            renderPagination();
        },
        error: function(err) {
            console.error('Error fetching stations:', err);
            $('#stationsTable tbody').html('<tr><td colspan="6" class="text-center">Failed to load data</td></tr>');
        }
    });
}

// ✅ Render table (now applies all permissions)
// ✅ Render table (now applies all permissions)
function renderTable() {
    $('#stationsTable tbody').empty();
    const start = (currentPage - 1) * perPage;
    const end = start + perPage;
    const pageData = stationsData.slice(start, end);

    pageData.forEach(station => {
        const canRead = hasPermission('sites', 'read');
        const canUpdate = hasPermission('sites', 'update');
        const canDelete = hasPermission('sites', 'delete');

        // 👉 View Report Button (NEW)
        const reportBtn = `<a href="/station-audit/${station.id}" class="btn btn-light-success btn-sm" target="_blank">
                                <i class="bi bi-file-text"></i> Report
                           </a>`;

        const viewBtn = canRead
            ? `<a href="view-overview1/${station.id}" class="btn btn-light-info btn-sm">
                    <i class="bi bi-person-circle"></i>
               </a>` : '';

        const editBtn = canUpdate
            ? `<a href="station-create?id=${station.id}" class="btn btn-light-primary btn-sm editStation" data-id="${station.id}">
                    <i class="bi bi-pencil-square"></i>
               </a>` : '';

        const deleteBtn = canDelete
            ? `<button type="button" class="btn btn-light-danger btn-sm deleteStation" data-id="${station.id}">
                    <i class="ri-delete-bin-line"></i>
               </button>` : '';

        const statusSwitch = canUpdate
            ? `<input class="form-check-input status-switch" type="checkbox" role="switch"
                      id="statusSwitch${station.id}" data-id="${station.id}" ${station.status == 1 ? 'checked' : ''}>`
            : `<input class="form-check-input" type="checkbox" disabled ${station.status == 1 ? 'checked' : ''}>`;

        $('#stationsTable tbody').append(`
            <tr data-id="${station.id}" class="station-row" style="cursor:pointer;">
                <td>
                    <h6 class="mb-1 station-link" data-id="${station.id}" 
                        style="color:#007bff; text-decoration:underline;">
                        ${station.name}
                    </h6>
                    <p class="mb-0 fs-12 text-muted">${station.location}</p>
                </td>
                <td>
                    <div class="mt-1 text-muted fs-12">${station.employees_count ?? 0} Employees</div>
                </td>
                <td>
                    <span class="fs-12 fw-semibold">${station.total_capacity ?? 0} L</span>
                    <div class="progress progress-xs">
                        <div class="progress-bar" style="width: ${station.total_capacity > 0 ? 100 : 0}%"></div>
                    </div>
                </td>
                <td>${station.city ?? 'N/A'}</td>
                <td>
                    <div class="form-check form-switch">
                        ${statusSwitch}
                    </div>
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        ${reportBtn}
                        ${viewBtn}
                        ${editBtn}
                        ${deleteBtn}
                    </div>
                </td>
            </tr>
        `);
    });

    // 🟢 Add double-click event for table rows
    $('#stationsTable tbody').off('dblclick', 'tr.station-row').on('dblclick', 'tr.station-row', function (e) {
        // Prevent redirect if double-clicked on a button or link
        if ($(e.target).is('button, a, i, input, .form-check-input')) return;

        const id = $(this).data('id');
        window.location.href = `view-overview1/${id}`;
    });
}

// ✅ Pagination
function renderPagination() {
    const totalPages = Math.ceil(stationsData.length / perPage);
    const startEntry = (currentPage - 1) * perPage + 1;
    const endEntry = Math.min(currentPage * perPage, stationsData.length);
    
    let html = `
        <div class="d-flex flex-wrap align-items-center gap-4 m-5">
            <div class="fw-medium">
                Showing ${startEntry} - ${endEntry} of ${stationsData.length} Entries
            </div>
            <div class="ms-auto">
                <nav><ul class="pagination pagination-primary mb-0">
    `;
    
    // Previous button
    if (currentPage > 1) {
        html += `<li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="changePage(${currentPage - 1})">
                        <i class="ri-arrow-left-s-line fw-semibold"></i>
                    </a>
                 </li>`;
    } else {
        html += `<li class="page-item disabled">
                    <a class="page-link" href="javascript:void(0)">
                        <i class="ri-arrow-left-s-line fw-semibold"></i>
                    </a>
                 </li>`;
    }
    
    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    // Adjust start page if we're near the end
    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    // First page
    if (startPage > 1) {
        html += `<li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="changePage(1)">1</a>
                 </li>`;
        if (startPage > 2) {
            html += `<li class="page-item disabled">
                        <span class="page-link">...</span>
                     </li>`;
        }
    }
    
    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
        html += `<li class="page-item ${i == currentPage ? 'active' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="changePage(${i})">${i}</a>
                 </li>`;
    }
    
    // Last page
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<li class="page-item disabled">
                        <span class="page-link">...</span>
                     </li>`;
        }
        html += `<li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="changePage(${totalPages})">${totalPages}</a>
                 </li>`;
    }
    
    // Next button
    if (currentPage < totalPages) {
        html += `<li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="changePage(${currentPage + 1})">
                        <i class="ri-arrow-right-s-line fw-semibold"></i>
                    </a>
                 </li>`;
    } else {
        html += `<li class="page-item disabled">
                    <a class="page-link" href="javascript:void(0)">
                        <i class="ri-arrow-right-s-line fw-semibold"></i>
                    </a>
                 </li>`;
    }
    
    html += `</ul></nav></div></div>`;
    $('#paginationContainer').html(html);
}
	
	window.changePage = function(page) {
    const totalPages = Math.ceil(stationsData.length / perPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderTable();
    renderPagination();
    
    // Scroll to top of table
    $('html, body').animate({
        scrollTop: $("#stationsTable").offset().top - 100
    }, 500);
};

function changePage(page) {
    const totalPages = Math.ceil(stationsData.length / perPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderTable();
    renderPagination();
}

// ✅ Status Toggle
$(document).on('change', '.status-switch', function() {
    const stationId = $(this).data('id');
    const newStatus = $(this).is(':checked') ? 1 : 0;

    $.ajax({
        url: `/api/stations/${stationId}/status`,
        method: 'PATCH',
        data: { status: newStatus },
        success: function() {
            showStatusToast(newStatus);
        },
        error: function() {
            $(`#statusSwitch${stationId}`).prop('checked', !newStatus);
            showStatusToast(null, true);
        }
    });
});

// ✅ Toast utilities
function showToast(message, type = 'success') {
    const toastHTML = `
        <div class="toast align-items-center text-bg-${type} border-0 show" role="alert"
             style="position: fixed; top: 20px; right: 20px; z-index: 1055;">
            <div class="d-flex"><div class="toast-body">${message}</div></div>
        </div>`;
    $('body').append(toastHTML);
    setTimeout(() => $('.toast').remove(), 3000);
}

function showStatusToast(status, isError = false) {
    let message, bgClass;
    if (isError) {
        message = "Failed to update status";
        bgClass = "bg-danger";
    } else if (status === 1) {
        message = "Site is Active";
        bgClass = "bg-success";
    } else {
        message = "Site is Inactive";
        bgClass = "bg-warning";
    }

    const toastHTML = `
        <div class="toast align-items-center text-white ${bgClass} border-0 show" 
             style="position: fixed; top: 20px; right: 20px; z-index: 1055; min-width: 250px;">
            <div class="d-flex flex-column w-100">
                <div class="toast-body">${message}</div>
                <div class="toast-progress"></div>
            </div>
        </div>`;
    $('body').append(toastHTML);
    setTimeout(() => $('.toast').remove(), 3000);
}

// ✅ Permission check helper
function hasPermission(moduleName, action) {
    const module = userPermissions.find(p => p.name === moduleName);
    if (!module) return false;
    return module[action] == 1;
}
</script>
@endsection
