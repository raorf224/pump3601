@extends('partials.layouts.master')

@section('title', 'Dispenser Visualization | ' . Auth::user()->full_name)
@section('title-sub', 'Pages')
@section('pagetitle', 'Dispenser Visualization')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        .card-hover:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid">

            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="stationFilter" class="form-label">Filter by Station</label>
                    <select id="stationFilter" class="form-select">
                        <option value=""> All Stations </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="tankFilter" class="form-label">Filter by Tank</label>
                    <select id="tankFilter" class="form-select">
                        <option value=""> All Tanks </option>
                    </select>
                </div>
            </div>

            <div class="row" id="dispenserContainer">
                <!-- Dispensers will load here via AJAX -->
            </div>

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
            let stationChoices = null;
            let tankChoices = null;
            let availableStations = [];

            // Load stations
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

                        stations.forEach(station => {
                            $('#stationFilter').append(`<option value="${station.id}">${station.name}</option>`);
                        });

                        // store for employee aggregation
                        availableStations = stations;

                        if (stationChoices) stationChoices.destroy();
                        stationChoices = new Choices("#stationFilter", {
                            searchEnabled: true,
                            shouldSort: false,
                            itemSelectText: '',
                            placeholderValue: "Select a station"
                        });
                    },
                    error: function (err) {
                        console.error("Failed to load stations", err);
                    }
                });
            }

            // Load tanks (optionally filter by station)
            function loadTanks(stationId = '') {
                // Admin: all tanks
                if (AUTH_ROLE === 'admin') {
                    $.ajax({
                        url: `/api/tanks`,
                        method: "GET",
                        success: function (tanks) {
                            populateTankFilter(tanks, stationId);
                        },
                        error: function (err) { console.error('Failed to load tanks', err); }
                    });
                    return;
                }

                // Employee: stationwise tanks
                if (AUTH_ROLE === 'employee') {
                    if (stationId) {
                        $.ajax({
                            url: `/api/stationwise/${stationId}`,
                            method: 'GET',
                            success: function (tanks) { populateTankFilter(tanks, stationId); },
                            error: function (err) { console.error('Failed to load station tanks', err); }
                        });
                        return;
                    }

                    // No station selected: aggregate tanks for availableStations
                    if (!availableStations || availableStations.length === 0) {
                        $.ajax({
                            url: `/api/stations_emp/${AUTH_USER_ID}`,
                            method: 'GET',
                            success: function (stations) {
                                availableStations = stations;
                                fetchAndPopulateTanksForStations(stations);
                            },
                            error: function (err) { console.error('Failed to load employee stations', err); }
                        });
                        return;
                    }

                    fetchAndPopulateTanksForStations(availableStations);
                    return;
                }

                // Owner / other: user tanks
                $.ajax({
                    url: `/api/user-tanks/${AUTH_USER_ID}`,
                    method: "GET",
                    success: function (tanks) { populateTankFilter(tanks, stationId); },
                    error: function (err) { console.error('Failed to load tanks', err); }
                });
            }

            function fetchAndPopulateTanksForStations(stations) {
                if (!stations || stations.length === 0) { populateTankFilter([], ''); return; }
                const calls = stations.map(s => fetch(`/api/stationwise/${s.id}`).then(r => r.json()));
                Promise.all(calls).then(results => {
                    const combined = results.flat();
                    populateTankFilter(combined, '');
                }).catch(err => console.error('Failed to fetch station tanks', err));
            }

            function populateTankFilter(tanks, stationId) {
                $('#tankFilter').find('option:not(:first)').remove();
                if (stationId) tanks = tanks.filter(t => t.station_id == stationId);
                tanks.forEach(tank => { $('#tankFilter').append(`<option value="${tank.id}">${tank.name}</option>`); });
                if (tankChoices) tankChoices.destroy();
                tankChoices = new Choices("#tankFilter", {
                    searchEnabled: true,
                    shouldSort: false,
                    itemSelectText: '',
                    placeholderValue: "Select a tank"
                });
            }

            // Load dispensers (filter by station/tank)
            function loadDispensers(stationId = '', tankId = '') {
                // Admin: all dispensers
                if (AUTH_ROLE === 'admin') {
                    $.ajax({
                        url: `/api/dispensers`,
                        method: 'GET',
                        success: function (dispensers) { renderDispensers(dispensers, stationId, tankId); },
                        error: function (err) { console.error('Failed to load dispensers', err); }
                    });
                    return;
                }

                // Employee: station-wise dispensers
                if (AUTH_ROLE === 'employee') {
                    if (stationId) {
                        $.ajax({
                            url: `/api/station_dispensers/${stationId}`,
                            method: 'GET',
                            success: function (dispensers) { renderDispensers(dispensers, stationId, tankId); },
                            error: function (err) { console.error('Failed to load station dispensers', err); }
                        });
                        return;
                    }

                    // No station selected: aggregate dispensers for availableStations
                    if (!availableStations || availableStations.length === 0) {
                        $.ajax({
                            url: `/api/stations_emp/${AUTH_USER_ID}`,
                            method: 'GET',
                            success: function (stations) {
                                availableStations = stations;
                                fetchAndRenderDispensersForStations(stations, tankId);
                            },
                            error: function (err) { console.error('Failed to load employee stations', err); }
                        });
                        return;
                    }

                    fetchAndRenderDispensersForStations(availableStations, tankId);
                    return;
                }

                // Owner / other: user dispensers
                $.ajax({
                    url: `/api/user-dispensers/${AUTH_USER_ID}`,
                    method: "GET",
                    success: function (dispensers) { renderDispensers(dispensers, stationId, tankId); },
                    error: function (err) { console.error('Failed to load dispensers', err); }
                });
            }

            function fetchAndRenderDispensersForStations(stations, tankId) {
                if (!stations || stations.length === 0) { renderDispensers([], '', tankId); return; }
                const calls = stations.map(s => fetch(`/api/station_dispensers/${s.id}`).then(r => r.json()).catch(e => { console.error('fetch error', e); return []; }));
                Promise.all(calls).then(results => {
                    // Normalize results: some endpoints may return objects instead of arrays
                    const combined = results.flatMap(r => Array.isArray(r) ? r : (r && Array.isArray(r.data) ? r.data : []));
                    renderDispensers(combined, '', tankId);
                }).catch(err => console.error('Failed to fetch station dispensers', err));
            }

            function renderDispensers(dispensers, stationId = '', tankId = '') {
                let filtered = dispensers;
                if (stationId) filtered = filtered.filter(d => d.station_id == stationId);
                if (tankId) filtered = filtered.filter(d => d.tank_id == tankId);

                $('#dispenserContainer').empty();

                if (filtered.length === 0) {
                    $('#dispenserContainer').append(`
                            <div class="col-12 text-center text-muted mt-4">No dispensers found for selected filters.</div>
                        `);
                    return;
                }

                filtered.forEach(dispenser => {
                    // Defensive defaults
                    const statusRaw = (dispenser && dispenser.dispenser_status) ? String(dispenser.dispenser_status) : '';
                    const statusText = statusRaw ? (statusRaw.charAt(0).toUpperCase() + statusRaw.slice(1)) : 'Unknown';
                    const badgeClass = statusRaw === 'active' ? 'success' : (statusRaw === 'warning' ? 'warning' : 'secondary');

                    const dispName = dispenser && (dispenser.dispenser_name || dispenser.name) ? (dispenser.dispenser_name || dispenser.name) : 'N/A';
                    const stationName = dispenser && (dispenser.station_name || dispenser.s_name || '') ? (dispenser.station_name || dispenser.s_name) : 'N/A';
                    const tankName = dispenser && (dispenser.tank_name || dispenser.t_name || '') ? (dispenser.tank_name || dispenser.t_name) : 'N/A';
                    const stationIdVal = dispenser && dispenser.station_id ? dispenser.station_id : '';
                    const tankIdVal = dispenser && dispenser.tank_id ? dispenser.tank_id : '';

                    let card = `
                            <div class="col-12 col-xl-4 col-lg-6 mb-4 dispenser-card" data-station="${stationIdVal}" data-tank="${tankIdVal}">
                                <div class="card card-hover">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">${dispName}</h5>
                                    </div>
                                    <div class="card-body d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-fuel-pump" style="font-size: 2rem; color: #0d6efd;"></i>
                                        </div>
                                        <div>
                                            <div><strong>Station:</strong> ${stationName}</div>
                                            <div><strong>Tank:</strong> ${tankName}</div>
                                            <div><strong>Status:</strong>
                                                <span class="badge bg-${badgeClass}">${statusText}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    $('#dispenserContainer').append(card);
                });
            }

            // Init
            loadStations();
            loadTanks();
            loadDispensers();

            // When station changes → reload tanks + filter dispensers
            $(document).on("change", "#stationFilter", function () {
                let stationId = $(this).val();
                loadTanks(stationId); // tanks filtered by station
                loadDispensers(stationId, $('#tankFilter').val());
            });

            // When tank changes → filter dispensers
            $(document).on("change", "#tankFilter", function () {
                let stationId = $('#stationFilter').val();
                let tankId = $(this).val();
                loadDispensers(stationId, tankId);
            });
        });
    </script>

@endsection