@extends('partials.layouts.master')

@section('title', 'Shift Overview | ' . Auth::user()->full_name)
@section('title-sub', 'Dashboard')
@section('pagetitle', 'Shift Overview')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        /* Status Row Flags */
        .flag-red {
            border-left: 4px solid #dc3545 !important;
        }

        .flag-yellow {
            border-left: 4px solid #ffc107 !important;
        }

        .flag-green {
            border-left: 4px solid #198754 !important;
        }

        .flag-gray {
            border-left: 4px solid #adb5bd !important;
        }

        .flag-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .dot-red {
            background: #dc3545;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);
        }

        .dot-yellow {
            background: #ffc107;
            box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2);
        }

        .dot-green {
            background: #198754;
            box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.2);
        }

        .dot-gray {
            background: #adb5bd;
        }

        /* High-contrast Badges */
        .badge-solid-success {
            background-color: #198754;
            color: #ffffff;
            font-weight: 500;
        }

        .badge-solid-danger {
            background-color: #dc3545;
            color: #ffffff;
            font-weight: 500;
        }

        .badge-solid-warning {
            background-color: #ffc107;
            color: #000000;
            font-weight: 600;
        }

        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 4px;
            background: #f8fafc;
            color: #334155;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
        }

        /* Site Title Link */
        .site-title {
            font-weight: 600;
            color: #0f172a;
            text-decoration: none;
            transition: color 0.2s;
        }

        .site-title:hover {
            color: #2563eb;
        }

        /* Layout & Spacing Fix */
        .overview-wrapper {
            padding: 1.5rem 1rem;
        }

        .kpi-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
        }

        .kpi-icon {
            width: 46px;
            height: 46px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Clean Light Modal Styling */
        .modal-content {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .modal-header-custom {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 24px;
        }

        /* Table Light Headers */
        .table-light-custom {
            background-color: #f1f5f9 !important;
            color: #334155 !important;
            font-weight: 600;
            border-bottom: 2px solid #cbd5e1 !important;
        }

        .table-light-custom th {
            background-color: #f1f5f9 !important;
            color: #334155 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        .profile-avatar-box {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            border: 2px solid #e2e8f0;
        }
    </style>
@endsection

@section('content')
    <div class="overview-wrapper">

        <!-- ======= Top Summary KPI Cards ======= -->
        <div class="row g-3 mb-4" id="summaryCards">
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card border-0 shadow-sm">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-primary-subtle text-primary">
                            <i class="bi bi-fuel-pump"></i>
                        </div>
                        <div>
                            <span class="text-muted fs-7 fw-medium d-block">Total Sites</span>
                            <h3 class="fw-bold mb-0 text-dark" id="totalSites">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card border-0 shadow-sm">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-success-subtle text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <span class="text-muted fs-7 fw-medium d-block">Setup Completed</span>
                            <h3 class="fw-bold mb-0 text-dark" id="totalSetup">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card border-0 shadow-sm">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-info-subtle text-info">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <span class="text-muted fs-7 fw-medium d-block">Total Shifts</span>
                            <h3 class="fw-bold mb-0 text-dark" id="totalShifts">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card border-0 shadow-sm">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-danger-subtle text-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <span class="text-muted fs-7 fw-medium d-block">Inactive Sites (>3d)</span>
                            <h3 class="fw-bold mb-0 text-dark" id="totalAlerts">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======= Main Data Table Card ======= -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-grid-3x3-gap-fill text-primary"></i>
                    <h6 class="mb-0 fw-bold text-dark">Site Overview Directory</h6>
                </div>
                <button class="btn btn-sm btn-light border fw-medium" onclick="loadOverview()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive p-3">
                    <table id="overviewTable" class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr class="text-secondary small text-uppercase">
                                <th>Site & Location</th>
                                <th>Created Date</th>
                                <th>Initial Setup Status</th>
                                <th>Daily Closings</th>
                                <th>Station Manager</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== Shifts Modal ==================== -->
    <div class="modal fade" id="shiftsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0 text-dark">
                        <i class="bi bi-clock-history text-primary"></i>
                        Shift Records — <span id="shiftsStationName" class="text-primary fw-bold"></span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="shiftsTable">
                            <thead class="table-light-custom small">
                                <tr>
                                    <th>#</th>
                                    <th>Incharger</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Cash Handover</th>
                                    <th>Cash Return</th>
                                    <th>Readings</th>
                                    <th>Total Sale</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="small"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== Manager Details Modal ==================== -->
    <div class="modal fade" id="managerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h6 class="modal-title fw-bold mb-0 text-dark">
                        <i class="bi bi-person-badge me-2 text-primary"></i> Manager Profile Details
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="managerModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
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
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        const AUTH_USER_ID = "{{ Auth::id() }}";
        const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";

        let overviewTable = null;

        $(document).ready(function () {
            loadOverview();
        });

        function loadOverview() {
            $.ajax({
                url: '/api/overview/stations',
                method: 'GET',
                success: function (res) {
                    let stations = res || [];

                    if (AUTH_ROLE === 'owner') {
                        stations = stations.filter(s => String(s.user_id) === String(AUTH_USER_ID));
                    }

                    renderSummaryCards(stations);
                    renderOverviewTable(stations);
                },
                error: function () {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load site overview data.');
                    }
                }
            });
        }

        function renderSummaryCards(data) {
            $('#totalSites').text(data.length);
            $('#totalSetup').text(data.filter(s => s.initial_setup_done).length);
            $('#totalShifts').text(data.reduce((sum, s) => sum + (parseInt(s.total_shifts) || 0), 0));
            $('#totalAlerts').text(data.filter(s => s.flag === 'red').length);
        }

        function renderOverviewTable(data) {
            if (overviewTable) {
                overviewTable.destroy();
                $('#overviewTable tbody').empty();
            }

            const rows = data.map(station => {
                const flagClass = `flag-${station.flag || 'gray'}`;
                const dotClass = `dot-${station.flag || 'gray'}`;

                const createdDate = station.created_at
                    ? new Date(station.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
                    : 'N/A';

                const setupBadge = station.initial_setup_done
                    ? `<span class="badge badge-solid-success px-2 py-1"><i class="bi bi-check-circle me-1"></i> Completed</span>`
                    : `<span class="badge badge-solid-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i> Pending</span>`;

                const setupDetails = `
                        <div class="mt-2 d-flex gap-1">
                            <span class="stat-pill" title="Tanks"><i class="bi bi-fuel-pump text-primary"></i> T: ${station.tanks_count || 0}</span>
                            <span class="stat-pill" title="Dispensers"><i class="bi bi-droplet text-info"></i> D: ${station.dispensers_count || 0}</span>
                            <span class="stat-pill" title="Nozzles"><i class="bi bi-droplet-half text-secondary"></i> N: ${station.nozzles_count || 0}</span>
                        </div>
                    `;

                const shiftBtn = station.closed_shifts > 0
                    ? `<button class="btn btn-sm btn-outline-primary px-3 fw-medium" onclick="openShiftsModal(${station.id}, '${escapeHtml(station.name)}')">
                                <i class="bi bi-eye me-1"></i> View (${station.closed_shifts})
                           </button>`
                    : `<span class="text-muted small">No closed shifts</span>`;

                const managerLink = station.manager
                    ? `<a href="javascript:void(0)" class="text-primary fw-semibold text-decoration-none d-inline-flex align-items-center gap-1" onclick="openManagerModal(${station.id})">
                                <i class="bi bi-person-circle fs-6"></i> ${escapeHtml(station.manager.manager_name || 'N/A')}
                           </a>`
                    : `<span class="text-muted small">Not Assigned</span>`;

                return {
                    flagClass: flagClass,
                    site: `
                            <div>
                                <a href="javascript:void(0)" class="site-title" onclick="openShiftsModal(${station.id}, '${escapeHtml(station.name)}')">
                                    <span class="flag-dot ${dotClass}"></span>
                                    ${escapeHtml(station.name)}
                                </a>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-geo-alt"></i> ${escapeHtml(station.location || 'N/A')}
                                </div>
                            </div>
                        `,
                    created: `<div>${createdDate}</div>`,
                    setup: setupBadge + setupDetails,
                    closing: shiftBtn,
                    manager: managerLink
                };
            });

            overviewTable = $('#overviewTable').DataTable({
                data: rows,
                columns: [
                    { data: 'site' },
                    { data: 'created' },
                    { data: 'setup' },
                    { data: 'closing' },
                    { data: 'manager' }
                ],
                createdRow: function (row, data) {
                    $(row).addClass(data.flagClass);
                },
                order: [[1, 'desc']],
                pageLength: 10,
                responsive: true
            });
        }

        function openShiftsModal(stationId, stationName) {
            $('#shiftsStationName').text(stationName);
            $('#shiftsTable tbody').html(`<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>`);
            $('#shiftsModal').modal('show');

            $.ajax({
                url: `/api/overview/station/${stationId}/shifts`,
                method: 'GET',
                success: function (shifts) {
                    const tbody = $('#shiftsTable tbody').empty();

                    if (!shifts || shifts.length === 0) {
                        tbody.html(`<tr><td colspan="9" class="text-center py-4 text-muted">No shifts found</td></tr>`);
                        return;
                    }

                    shifts.forEach((s, i) => {
                        const statusBadge = s.status === 'closed'
                            ? `<span class="badge badge-solid-success px-2">Closed</span>`
                            : `<span class="badge badge-solid-warning px-2">Open</span>`;

                        tbody.append(`
                                <tr>
                                    <td class="fw-bold">${i + 1}</td>
                                    <td>${escapeHtml(s.incharger_name || '-')}</td>
                                    <td>${s.start_time ? new Date(s.start_time).toLocaleString('en-GB') : '-'}</td>
                                    <td>${s.end_time ? new Date(s.end_time).toLocaleString('en-GB') : '-'}</td>
                                    <td class="fw-medium">Rs. ${parseFloat(s.cash_handover || 0).toLocaleString()}</td>
                                    <td class="fw-medium">Rs. ${parseFloat(s.cash_return || 0).toLocaleString()}</td>
                                    <td><span class="badge bg-secondary">${s.readings_count}</span></td>
                                    <td class="fw-bold text-success">Rs. ${parseFloat(s.total_sale || 0).toLocaleString()}</td>
                                    <td>${statusBadge}</td>
                                </tr>
                            `);
                    });
                }
            });
        }

        function openManagerModal(stationId) {
            $('#managerModalBody').html(`<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>`);
            $('#managerModal').modal('show');

            $.ajax({
                url: `/api/overview/station/${stationId}/manager`,
                method: 'GET',
                success: function (res) {
                    if (!res.success) {
                        $('#managerModalBody').html(`<div class="alert alert-warning mb-0"><i class="bi bi-info-circle me-2"></i> ${res.message || 'No manager assigned'}</div>`);
                        return;
                    }

                    const m = res.data;
                    const statusBadge = (m.emp_status === 'active')
                        ? `<span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>`
                        : `<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>`;

                    $('#managerModalBody').html(`
                            <div class="row align-items-center g-4">
                                <div class="col-md-4 text-center border-end">
                                    <div class="profile-avatar-box mx-auto mb-3">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">${escapeHtml(m.full_name || 'N/A')}</h6>
                                    <span class="small text-muted d-block mb-2">Station Manager</span>
                                    ${statusBadge}
                                </div>
                                <div class="col-md-8">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless align-middle mb-0 small">
                                            <tr><th width="35%" class="text-secondary">Username</th><td class="fw-semibold text-dark">${escapeHtml(m.username || '-')}</td></tr>
                                            <tr><th class="text-secondary">Email</th><td>${escapeHtml(m.email || '-')}</td></tr>
                                            <tr><th class="text-secondary">Phone</th><td>${escapeHtml(m.user_phone || m.emp_phone || '-')}</td></tr>
                                            <tr><th class="text-secondary">City/Region</th><td>${escapeHtml(m.city || '-')} / ${escapeHtml(m.region || '-')}</td></tr>
                                            <tr><th class="text-secondary">Address</th><td>${escapeHtml(m.address || '-')}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        `);
                }
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }
    </script>
@endsection