@extends('partials.layouts.master')

@section('title', 'Nozzle Visualization | ' . Auth::user()->full_name)
@section('title-sub', 'Pages')
@section('pagetitle', 'Nozzle Visualization')

@section('css')
    {{-- ✅ Choices.js for modern dropdowns --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

    <style>
        .card-hover:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }

        .filter-label {
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid">

            <!-- 🔹 Filters -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="stationFilter" class="form-label filter-label">Filter by Station</label>
                    <select id="stationFilter" class="form-select">
                        <option value="">-- All Stations --</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="tankFilter" class="form-label filter-label">Filter by Tank</label>
                    <select id="tankFilter" class="form-select">
                        <option value="">-- All Tanks --</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="dispenserFilter" class="form-label filter-label">Filter by Dispenser</label>
                    <select id="dispenserFilter" class="form-select">
                        <option value="">-- All Dispensers --</option>
                    </select>
                </div>
            </div>

            <!-- 🔹 Nozzles -->
            <div class="row" id="nozzleContainer"></div>

        </div>
    </div>
    </main>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script>
        const AUTH_USER_ID = "{{ Auth::id() }}";
        const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";

        $(document).ready(function () {
            let allNozzles = [];              // master list (never filtered)
            let stationChoices = null;
            let tankChoices = null;
            let dispenserChoices = null;
            let availableStations = [];
            let availableTanks = [];          // full tank list
            let availableDispensers = [];     // full dispenser list

            // 🔹 Safe id getter
            function getId(obj, ...keys) {
                for (const k of keys) {
                    if (obj && obj[k] !== undefined && obj[k] !== null && obj[k] !== '') return obj[k];
                }
                return null;
            }

            // 🔹 Normalize string for comparison
            function eq(a, b) {
                if (a === null || a === undefined || a === '') return false;
                if (b === null || b === undefined || b === '') return false;
                return String(a) === String(b);
            }

            // ============================================================
            // 🔹 LOAD STATIONS
            // ============================================================
            function loadStations() {
                let apiUrl;
                if (AUTH_ROLE === 'admin') apiUrl = '/api/stations';
                else if (AUTH_ROLE === 'employee') apiUrl = `/api/stations_emp/${AUTH_USER_ID}`;
                else apiUrl = `/api/stations/${AUTH_USER_ID}`;

                $.ajax({
                    url: apiUrl,
                    method: "GET",
                    success: function (stations) {
                        availableStations = stations || [];
                        $('#stationFilter').find('option:not(:first)').remove();
                        availableStations.forEach(s => $('#stationFilter').append(`<option value="${s.id}">${s.name}</option>`));

                        if (stationChoices) stationChoices.destroy();
                        stationChoices = new Choices("#stationFilter", {
                            searchEnabled: true, shouldSort: false, itemSelectText: '',
                        });
                    },
                    error: err => console.error("❌ Stations load failed:", err)
                });
            }

            // ============================================================
            // 🔹 LOAD TANKS  (filtered by station)
            // ============================================================
            function loadTanks(stationId = '') {
                // Admin
                if (AUTH_ROLE === 'admin') {
                    $.ajax({
                        url: `/api/tanks`,
                        method: 'GET',
                        success: tanks => populateTankFilter(tanks, stationId),
                        error: err => console.error('❌ Tanks load failed', err)
                    });
                    return;
                }

                // Employee
                if (AUTH_ROLE === 'employee') {
                    if (stationId) {
                        $.ajax({
                            url: `/api/stationwise/${stationId}`,
                            method: 'GET',
                            success: tanks => populateTankFilter(tanks, stationId),
                            error: err => console.error('❌ Station tanks failed', err)
                        });
                        return;
                    }
                    if (!availableStations || availableStations.length === 0) {
                        $.ajax({
                            url: `/api/stations_emp/${AUTH_USER_ID}`,
                            method: 'GET',
                            success: stations => {
                                availableStations = stations;
                                fetchAndPopulateTanksForStations(stations);
                            },
                            error: err => console.error('❌ Employee stations failed', err)
                        });
                        return;
                    }
                    fetchAndPopulateTanksForStations(availableStations);
                    return;
                }

                // Owner
                $.ajax({
                    url: `/api/user-tanks/${AUTH_USER_ID}`,
                    method: 'GET',
                    success: tanks => populateTankFilter(tanks, stationId),
                    error: err => console.error('❌ Tanks load failed', err)
                });
            }

            function fetchAndPopulateTanksForStations(stations) {
                if (!stations || stations.length === 0) { populateTankFilter([], ''); return; }
                const calls = stations.map(s =>
                    fetch(`/api/stationwise/${s.id}`).then(r => r.json()).catch(e => { console.error(e); return []; })
                );
                Promise.all(calls).then(results => {
                    const combined = results.flatMap(r => Array.isArray(r) ? r : (r && Array.isArray(r.data) ? r.data : []));
                    populateTankFilter(combined, '');
                }).catch(e => console.error('❌ Failed fetching station tanks', e));
            }

            function populateTankFilter(tanks, stationId) {
                availableTanks = tanks || [];

                $('#tankFilter').find('option:not(:first)').remove();

                let filtered = availableTanks;
                if (stationId) {
                    filtered = filtered.filter(t => eq(t.station_id, stationId));
                }

                filtered.forEach(t => $('#tankFilter').append(`<option value="${t.id}">${t.name}</option>`));

                if (tankChoices) tankChoices.destroy();
                tankChoices = new Choices('#tankFilter', { searchEnabled: true, shouldSort: false, itemSelectText: '' });
            }

            // ============================================================
            // 🔹 LOAD DISPENSERS  (filtered by station + tank)
            // ============================================================
            function loadDispensers(stationId = '', tankId = '') {
                if (AUTH_ROLE === 'admin') {
                    $.ajax({
                        url: `/api/dispensers`,
                        method: 'GET',
                        success: d => populateDispenserFilter(d, stationId, tankId),
                        error: err => console.error('❌ Dispensers failed', err)
                    });
                    return;
                }

                if (AUTH_ROLE === 'employee') {
                    if (stationId) {
                        $.ajax({
                            url: `/api/station_dispensers/${stationId}`,
                            method: 'GET',
                            success: d => populateDispenserFilter(d, stationId, tankId),
                            error: err => console.error('❌ Station dispensers failed', err)
                        });
                        return;
                    }
                    if (!availableStations || availableStations.length === 0) {
                        $.ajax({
                            url: `/api/stations_emp/${AUTH_USER_ID}`,
                            method: 'GET',
                            success: stations => {
                                availableStations = stations;
                                fetchAndPopulateDispensersForStations(stations);
                            },
                            error: err => console.error('❌ Employee stations failed', err)
                        });
                        return;
                    }
                    fetchAndPopulateDispensersForStations(availableStations);
                    return;
                }

                // Owner
                $.ajax({
                    url: `/api/user-dispensers/${AUTH_USER_ID}`,
                    method: 'GET',
                    success: d => populateDispenserFilter(d, stationId, tankId),
                    error: err => console.error('❌ Dispensers failed', err)
                });
            }

            function fetchAndPopulateDispensersForStations(stations) {
                if (!stations || stations.length === 0) { populateDispenserFilter([], '', ''); return; }
                const calls = stations.map(s =>
                    fetch(`/api/station_dispensers/${s.id}`).then(r => r.json()).catch(e => { console.error(e); return []; })
                );
                Promise.all(calls).then(results => {
                    const combined = results.flatMap(r => Array.isArray(r) ? r : (r && Array.isArray(r.data) ? r.data : []));
                    populateDispenserFilter(combined, '', '');
                }).catch(e => console.error('❌ Failed fetching station dispensers', e));
            }

            function populateDispenserFilter(dispensers, stationId, tankId) {
                availableDispensers = dispensers || [];

                $('#dispenserFilter').find('option:not(:first)').remove();

                let filtered = availableDispensers;
                if (stationId) filtered = filtered.filter(d => eq(d.station_id, stationId));
                if (tankId) filtered = filtered.filter(d => eq(d.tank_id, tankId));

                filtered.forEach(d => {
                    const id = getId(d, 'dispenser_id', 'id');
                    const name = getId(d, 'dispenser_name', 'name') || `Dispenser ${id}`;
                    $('#dispenserFilter').append(`<option value="${id}">${name}</option>`);
                });

                if (dispenserChoices) dispenserChoices.destroy();
                dispenserChoices = new Choices('#dispenserFilter', { searchEnabled: true, shouldSort: false, itemSelectText: '' });
            }

            // ============================================================
            // 🔹 LOAD NOZZLES (role-aware — station-specific)
            // ============================================================
            function loadNozzles() {
                const selectedStation = $('#stationFilter').val();

    if (AUTH_ROLE === 'admin') {
        if (selectedStation) {
            $.ajax({
                url: `/api/station_nozzle/${selectedStation}`,   // ✅ station-specific
                method: 'GET',
                success: res => { allNozzles = res || []; applyFilters(); },
                error: err => console.error('❌ Station nozzles failed', err)
            });
            return;
        }
        // Agar station select nahi hai → saare nozzles
        $.ajax({
            url: `/api/nozzles`,
            method: 'GET',
            success: res => { allNozzles = res || []; applyFilters(); },
            error: err => console.error('❌ Nozzles failed', err)
        });
        return;
    }


                if (AUTH_ROLE === 'employee') {
                    if (selectedStation) {
                        $.ajax({
                            url: `/api/station_nozzle/${selectedStation}`,
                            method: 'GET',
                            success: res => { allNozzles = res || []; applyFilters(); },
                            error: err => console.error('❌ Station nozzles failed', err)
                        });
                        return;
                    }
                    if (!availableStations || availableStations.length === 0) {
                        $.ajax({
                            url: `/api/stations_emp/${AUTH_USER_ID}`,
                            method: 'GET',
                            success: stations => {
                                availableStations = stations;
                                fetchAndRenderNozzlesForStations(stations);
                            },
                            error: err => console.error('❌ Employee stations failed', err)
                        });
                        return;
                    }
                    fetchAndRenderNozzlesForStations(availableStations);
                    return;
                }

                // Owner
                if (selectedStation) {
                    // ✅ KEY FIX: owner ke liye station-specific endpoint use karo
                    $.ajax({
                        url: `/api/station_nozzle/${selectedStation}`,
                        method: 'GET',
                        success: res => { allNozzles = res || []; applyFilters(); },
                        error: err => console.error('❌ Station nozzles failed', err)
                    });
                    return;
                }

                $.ajax({
                    url: `/api/user-nozzles/${AUTH_USER_ID}`,
                    method: 'GET',
                    success: res => { allNozzles = res || []; applyFilters(); },
                    error: err => console.error('❌ Nozzles failed', err)
                });
            }

            function fetchAndRenderNozzlesForStations(stations) {
                if (!stations || stations.length === 0) { allNozzles = []; applyFilters(); return; }
                const calls = stations.map(s =>
                    fetch(`/api/station_nozzle/${s.id}`).then(r => r.json()).catch(e => { console.error(e); return []; })
                );
                Promise.all(calls).then(results => {
                    const combined = results.flatMap(r => Array.isArray(r) ? r : (r && Array.isArray(r.data) ? r.data : []));
                    allNozzles = combined;
                    applyFilters();
                }).catch(e => console.error('❌ Failed fetching station nozzles', e));
            }

            // ============================================================
            // 🔹 RENDER
            // ============================================================
            function renderNozzles(data) {
                $('#nozzleContainer').empty();

                if (!data || data.length === 0) {
                    $('#nozzleContainer').html(`<div class="text-center text-muted mt-4">No nozzles found.</div>`);
                    return;
                }

                data.forEach(n => {
                    const nozzleName = getId(n, 'nozzle_name', 'name') || 'N/A';
                    const dispenserId = getId(n, 'dispenser_id', 'dispenserId');
                    const tankId = getId(n, 'tank_id', 'tankId');
                    const stationId = getId(n, 'station_id', 'stationId');

                    // 🔹 Reading & date fields — flexible keys handle karo
                    const currentReading = getId(n, 'nozzle_reading', 'current_reading', 'reading', 'intial_meter_reading') ?? 'N/A'; 4
                    const lastUpdated = getId(n, 'updated_at', 'latest_update', 'last_updated') ?? 'N/A';

                    const lastUpdatedDate = lastUpdated !== 'N/A' ? String(lastUpdated).split(' ')[0] : 'N/A';

                    const dispObj = dispenserId
                        ? availableDispensers.find(d => eq(getId(d, 'dispenser_id', 'id'), dispenserId))
                        : null;
                    const tankObj = tankId
                        ? availableTanks.find(t => eq(t.id, tankId))
                        : null;

                    const stationName = getId(n, 'station_name') || (dispObj && dispObj.station_name) || (tankObj && tankObj.station_name) || 'N/A';
                    const dispenserName = getId(n, 'dispenser_name') || (dispObj && getId(dispObj, 'dispenser_name', 'name')) || 'N/A';
                    const tankName = getId(n, 'tank_name') || (tankObj && getId(tankObj, 'name', 'tank_name')) || 'N/A';

                    const rawStatus = (typeof n.nozzle_status !== 'undefined') ? n.nozzle_status : (n.status || '');
                    let statusClass = 'secondary', statusText = 'Inactive';
                    if (rawStatus == 1 || rawStatus === 'active') { statusClass = 'success'; statusText = 'Active'; }
                    else if (rawStatus == 2 || rawStatus === 'warning') { statusClass = 'warning'; statusText = 'Warning'; }

                    $('#nozzleContainer').append(`
                            <div class="col-12 col-xl-4 col-lg-6 mb-4 nozzle-card"
                                 data-station="${stationId || ''}"
                                 data-tank="${tankId || ''}"
                                 data-dispenser="${dispenserId || ''}">
                                <div class="card card-hover shadow-sm border-0 rounded-3">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0 text-primary">${nozzleName}</h5>
                                        <span class="badge bg-${statusClass}">${statusText}</span>
                                    </div>
                                    <div class="card-body d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-fuel-pump" style="font-size: 2.2rem; color: #0d6efd;"></i>
                                        </div>
                                        <div>
                                            <div><strong>Station:</strong> ${stationName}</div>
                                            <div><strong>Dispenser:</strong> ${dispenserName}</div>
                                            <div><strong>Tank:</strong> ${tankName}</div>
                                            <div><strong>Last Reading:</strong> ${currentReading}</div>
                                            <div><strong>Latest Update:</strong> ${lastUpdatedDate}</div>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                });
            }

            // ============================================================
            // 🔹 APPLY FILTERS
            // ============================================================
            function applyFilters() {
                const s = $('#stationFilter').val();
                const t = $('#tankFilter').val();
                const d = $('#dispenserFilter').val();

                console.log('🔎 Filtering:', { station: s, tank: t, dispenser: d, total: allNozzles.length });

                const filtered = allNozzles.filter(n => {
                    const nStation = getId(n, 'station_id', 'stationId');
                    const nTank = getId(n, 'tank_id', 'tankId');
                    const nDispenser = getId(n, 'dispenser_id', 'dispenserId');

                    if (s && !eq(nStation, s)) return false;
                    if (t && !eq(nTank, t)) return false;
                    if (d && !eq(nDispenser, d)) return false;
                    return true;
                });

                console.log('✅ Matched:', filtered.length);
                renderNozzles(filtered);
            }

            // ============================================================
            // 🔹 EVENT LISTENERS
            // ============================================================

            // Station change → tanks + dispensers + nozzles reload
            $(document).on("change", "#stationFilter", function () {
                const stationId = $(this).val();

                // reset tanks & dispensers
                if (tankChoices) tankChoices.removeActiveItems();
                if (dispenserChoices) dispenserChoices.removeActiveItems();
                $('#tankFilter').val('');
                $('#dispenserFilter').val('');

                loadTanks(stationId);
                loadDispensers(stationId, '');
                loadNozzles();       // ✅ station-specific nozzles load honge
            });

            // Tank change → dispensers reload for that tank + filter
            $(document).on("change", "#tankFilter", function () {
                const stationId = $('#stationFilter').val();
                const tankId = $(this).val();

                if (dispenserChoices) dispenserChoices.removeActiveItems();
                $('#dispenserFilter').val('');

                loadDispensers(stationId, tankId);
                applyFilters();
            });

            // Dispenser change → filter
            $(document).on("change", "#dispenserFilter", applyFilters);

            // ============================================================
            // 🔹 INITIAL LOAD
            // ============================================================
            loadStations();
            loadTanks();
            loadDispensers();
            loadNozzles();
        });
    </script>
@endsection