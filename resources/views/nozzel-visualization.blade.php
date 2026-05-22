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
    let nozzles = [];
    let stationChoices = null;
    let tankChoices = null;
    let dispenserChoices = null;
    let availableStations = [];
    let availableTanks = [];
    let availableDispensers = [];

    // 🔹 Load Stations
    function loadStations() {

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
            method: "GET",
            success: function (stations) {
                $('#stationFilter').find('option:not(:first)').remove();
                stations.forEach(s => $('#stationFilter').append(`<option value="${s.id}">${s.name}</option>`));

                if (stationChoices) stationChoices.destroy();
                stationChoices = new Choices("#stationFilter", {
                    searchEnabled: true,
                    shouldSort: false,
                    itemSelectText: '',
                });
            },
            error: err => console.error("❌ Stations load failed:", err)
        });
    }

    // 🔹 Load Tanks (optionally filtered by station)
    function loadTanks(stationId = '') {
        // Admin -> all tanks
        if (AUTH_ROLE === 'admin') {
            $.ajax({
                url: `/api/tanks`,
                method: 'GET',
                success: function (tanks) { populateTankFilter(tanks, stationId); },
                error: err => console.error('❌ Tanks load failed', err)
            });
            return;
        }

        // Employee -> stationwise
        if (AUTH_ROLE === 'employee') {
            if (stationId) {
                $.ajax({
                    url: `/api/stationwise/${stationId}`,
                    method: 'GET',
                    success: function (tanks) { populateTankFilter(tanks, stationId); },
                    error: err => console.error('❌ Station tanks load failed', err)
                });
                return;
            }

            // no station selected: aggregate tanks for employee stations
            if (!availableStations || availableStations.length === 0) {
                $.ajax({
                    url: `/api/stations_emp/${AUTH_USER_ID}`,
                    method: 'GET',
                    success: function (stations) { availableStations = stations; fetchAndPopulateTanksForStations(stations); },
                    error: err => console.error('❌ Employee stations load failed', err)
                });
                return;
            }

            fetchAndPopulateTanksForStations(availableStations);
            return;
        }

        // Owner / other -> user tanks
        $.ajax({
            url: `/api/user-tanks/${AUTH_USER_ID}`,
            method: 'GET',
            success: function (tanks) { populateTankFilter(tanks, stationId); },
            error: err => console.error('❌ Tanks load failed', err)
        });
    }

    function fetchAndPopulateTanksForStations(stations) {
        if (!stations || stations.length === 0) { populateTankFilter([], ''); return; }
        const calls = stations.map(s => fetch(`/api/stationwise/${s.id}`).then(r => r.json()).catch(e => { console.error(e); return []; }));
        Promise.all(calls).then(results => {
            const combined = results.flatMap(r => Array.isArray(r) ? r : (r && Array.isArray(r.data) ? r.data : []));
            populateTankFilter(combined, '');
        }).catch(e => console.error('❌ Failed fetching station tanks', e));
    }

    function populateTankFilter(tanks, stationId) {
        $('#tankFilter').find('option:not(:first)').remove();
        if (stationId) tanks = tanks.filter(t => t.station_id == stationId);
        tanks.forEach(t => $('#tankFilter').append(`<option value="${t.id}">${t.name}</option>`));
        if (tankChoices) tankChoices.destroy();
        tankChoices = new Choices('#tankFilter', { searchEnabled: true, shouldSort: false, itemSelectText: '' });
        // store for lookups
        availableTanks = tanks;
    }

    // 🔹 Load Dispensers (optionally filtered by station)
    function loadDispensers(stationId = '') {
        // Admin -> all dispensers
        if (AUTH_ROLE === 'admin') {
            $.ajax({ url: `/api/dispensers`, method: 'GET', success: d => populateDispenserFilter(d, stationId), error: err => console.error('❌ Dispensers load failed', err) });
            return;
        }

        // Employee -> station-wise
        if (AUTH_ROLE === 'employee') {
            if (stationId) {
                $.ajax({ url: `/api/station_dispensers/${stationId}`, method: 'GET', success: d => populateDispenserFilter(d, stationId), error: err => console.error('❌ Station dispensers load failed', err) });
                return;
            }

            if (!availableStations || availableStations.length === 0) {
                $.ajax({ url: `/api/stations_emp/${AUTH_USER_ID}`, method: 'GET', success: stations => { availableStations = stations; fetchAndPopulateDispensersForStations(stations); }, error: err => console.error('❌ Employee stations load failed', err) });
                return;
            }

            fetchAndPopulateDispensersForStations(availableStations);
            return;
        }

        // Owner / other -> user-dispensers
        $.ajax({ url: `/api/user-dispensers/${AUTH_USER_ID}`, method: 'GET', success: d => populateDispenserFilter(d, stationId), error: err => console.error('❌ Dispensers load failed', err) });
    }

    function fetchAndPopulateDispensersForStations(stations) {
        if (!stations || stations.length === 0) { populateDispenserFilter([], ''); return; }
        const calls = stations.map(s => fetch(`/api/station_dispensers/${s.id}`).then(r => r.json()).catch(e => { console.error(e); return []; }));
        Promise.all(calls).then(results => {
            const combined = results.flatMap(r => Array.isArray(r) ? r : (r && Array.isArray(r.data) ? r.data : []));
            populateDispenserFilter(combined, '');
        }).catch(e => console.error('❌ Failed fetching station dispensers', e));
    }

    function populateDispenserFilter(dispensers, stationId) {
        $('#dispenserFilter').find('option:not(:first)').remove();
        if (stationId) dispensers = dispensers.filter(d => d.station_id == stationId);
        dispensers.forEach(d => $('#dispenserFilter').append(`<option value="${d.id}">${d.dispenser_name || d.name}</option>`));
        if (dispenserChoices) dispenserChoices.destroy();
        dispenserChoices = new Choices('#dispenserFilter', { searchEnabled: true, shouldSort: false, itemSelectText: '' });
        // store for lookups
        availableDispensers = dispensers;
    }

    // 🔹 Load all Nozzles
    function loadNozzles() {
        // Admin -> all nozzles
        if (AUTH_ROLE === 'admin') {
            $.ajax({ url: `/api/nozzles`, method: 'GET', success: res => { nozzles = res; renderNozzles(nozzles); }, error: err => console.error('❌ Nozzles load failed', err) });
            return;
        }

        // Employee -> station-wise
        if (AUTH_ROLE === 'employee') {
            // if station selected, use station_nozzle
            const selectedStation = $('#stationFilter').val();
            if (selectedStation) {
                $.ajax({ url: `/api/station_nozzle/${selectedStation}`, method: 'GET', success: res => { nozzles = res; renderNozzles(nozzles); }, error: err => console.error('❌ Station nozzles load failed', err) });
                return;
            }

            // aggregate by employee stations
            if (!availableStations || availableStations.length === 0) {
                $.ajax({ url: `/api/stations_emp/${AUTH_USER_ID}`, method: 'GET', success: stations => { availableStations = stations; fetchAndRenderNozzlesForStations(stations); }, error: err => console.error('❌ Employee stations load failed', err) });
                return;
            }

            fetchAndRenderNozzlesForStations(availableStations);
            return;
        }

        // Owner / other -> user-nozzles
        $.ajax({ url: `/api/user-nozzles/${AUTH_USER_ID}`, method: 'GET', success: res => { nozzles = res; renderNozzles(nozzles); }, error: err => console.error('❌ Nozzles load failed', err) });
    }

    function fetchAndRenderNozzlesForStations(stations) {
        if (!stations || stations.length === 0) { nozzles = []; renderNozzles([]); return; }
        const calls = stations.map(s => fetch(`/api/station_nozzle/${s.id}`).then(r => r.json()).catch(e => { console.error(e); return []; }));
        Promise.all(calls).then(results => {
            const combined = results.flatMap(r => Array.isArray(r) ? r : (r && Array.isArray(r.data) ? r.data : []));
            nozzles = combined;
            renderNozzles(nozzles);
        }).catch(e => console.error('❌ Failed fetching station nozzles', e));
    }

    // 🔹 Render Nozzles on screen
    function renderNozzles(data) {
        $('#nozzleContainer').empty();

        if (data.length === 0) {
            $('#nozzleContainer').html(`<div class="text-center text-muted mt-4">No nozzles found.</div>`);
            return;
        }

        data.forEach(n => {
            // defensive lookups
            const nozzleName = n.nozzle_name || n.name || 'N/A';
            const dispenserId = n.dispenser_id || n.dispenserId || null;
            const tankId = n.tank_id || n.tankId || null;

            const dispObj = dispenserId ? (availableDispensers.find(d => Number(d.id) === Number(dispenserId)) || null) : null;
            const tankObj = tankId ? (availableTanks.find(t => Number(t.id) === Number(tankId)) || null) : null;

            const stationName = n.station_name || (dispObj && (dispObj.station_name || dispObj.s_name)) || (tankObj && tankObj.station_name) || 'N/A';
            const dispenserName = n.dispenser_name || (dispObj && (dispObj.dispenser_name || dispObj.name)) || 'N/A';
            const tankName = n.tank_name || (tankObj && (tankObj.name || tankObj.tank_name)) || 'N/A';

            // status handling (numeric or string)
            const rawStatus = (typeof n.nozzle_status !== 'undefined') ? n.nozzle_status : (n.status || '');
            let statusClass = 'secondary';
            let statusText = 'Inactive';
            if (rawStatus === 1 || rawStatus === '1' || rawStatus === 'active') { statusClass = 'success'; statusText = 'Active'; }
            else if (rawStatus === 2 || rawStatus === '2' || rawStatus === 'warning') { statusClass = 'warning'; statusText = 'Warning'; }

            $('#nozzleContainer').append(`
                <div class="col-12 col-xl-4 col-lg-6 mb-4 nozzle-card"
                     data-station="${n.station_id || (dispObj && dispObj.station_id) || ''}"
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
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
    }

    // 🔹 Apply filters together
    function applyFilters() {
        const s = $('#stationFilter').val();
        const t = $('#tankFilter').val();
        const d = $('#dispenserFilter').val();

        const filtered = nozzles.filter(n =>
            (!s || n.station_id == s) &&
            (!t || n.tank_id == t) &&
            (!d || n.dispenser_id == d)
        );
        renderNozzles(filtered);
    }

    // 🔹 Event Listeners
    $(document).on("change", "#stationFilter", function () {
        const stationId = $(this).val();
        loadTanks(stationId);
        loadDispensers(stationId);
        applyFilters();
    });

    $(document).on("change", "#tankFilter, #dispenserFilter", applyFilters);

    // 🔹 Initial load
    loadStations();
    loadTanks();
    loadDispensers();
    loadNozzles();
});
</script>

@endsection
