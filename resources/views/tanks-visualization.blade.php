@extends('partials.layouts.master')

@section('title', 'Tank Visualization | ' . Auth::user()->full_name)
@section('title-sub', 'Pages')
@section('pagetitle', 'Tank Visualization')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

    <style>
        .tank-visualization {
            position: relative;
            width: 120px;
            height: 180px;
            margin: 0 auto;
            border: 3px solid #0d6efd;
            border-radius: 5px;
            overflow: hidden;
            background: #f8f9fa;
        }

        .tank-fill {
            position: absolute;
            bottom: 0;
            width: 100%;
            background: linear-gradient(to top, #0d6efd, #6ea8fe);
            border-radius: 2px;
        }

        .tank-percentage {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            color: #0d6efd;
            text-shadow: 1px 1px 2px white;
            z-index: 10;
        }

        .tank-details {
            font-size: 0.9rem;
        }

        .dispenser-badge {
            font-size: 0.8rem;
            margin-right: 5px;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }

        .filter-row {
            margin-bottom: 20px;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid">

            <!-- Filter by Station -->
            <div class="row filter-row">
                <div class="col-md-6">
                    <label for="stationFilter" class="form-label fw-semibold">Filter by Station</label>
                    <select id="stationFilter" class="form-select">
                        <option value="">All Stations</option>
                    </select>
                </div>
            </div>

            <div class="row" id="tankContainer">
                <!-- Tanks will be loaded here dynamically -->
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
            let availableStations = [];

            let apiUrl;
            if (AUTH_ROLE === 'admin') {
                apiUrl = '/api/stations';
            } else if (AUTH_ROLE === 'employee') {
                apiUrl = `/api/stations_emp/${AUTH_USER_ID}`;
            } else {
                apiUrl = `/api/stations/${AUTH_USER_ID}`;
            }

            // Load stations in dropdown with Choices.js
            $.ajax({
                url: apiUrl,
                method: 'GET',
                success: function (stations) {
                    // Clear old options except "All Stations"
                    $('#stationFilter').find('option:not(:first)').remove();
                    stations.forEach(station => {
                        $('#stationFilter').append(`<option value="${station.id}">${station.name}</option>`);
                    });

                    // keep stations for later use (employee flow)
                    availableStations = stations;

                    // Destroy old instance if exists
                    if (stationChoices) {
                        stationChoices.destroy();
                    }

                    // Initialize Choices.js
                    stationChoices = new Choices("#stationFilter", {
                        searchEnabled: true,
                        shouldSort: false,
                        itemSelectText: '',
                        placeholder: true,
                        placeholderValue: "Select a station"
                    });
                },
                error: function (err) {
                    console.error("Failed to load stations", err);
                }
            });

            // Load tanks
            function loadTanks(stationId = '') {
                // Admin: all tanks
                if (AUTH_ROLE === 'admin') {
                    $.ajax({
                        url: `/api/tanks`,
                        method: "GET",
                        success: function (tanks) {
                            let filtered = stationId ? tanks.filter(t => t.station_id == stationId) : tanks;
                            renderTanks(filtered);
                        },
                        error: function (err) {
                            console.error("Failed to load tanks", err);
                        }
                    });
                    return;
                }

                // Employee: station-wise tanks only
                if (AUTH_ROLE === 'employee') {
                    // If a specific station is selected, fetch tanks for that station only
                    if (stationId) {
                        $.ajax({
                            url: `/api/stationwise/${stationId}`,
                            method: "GET",
                            success: function (tanks) {
                                renderTanks(tanks);
                            },
                            error: function (err) {
                                console.error("Failed to load station tanks", err);
                            }
                        });
                        return;
                    }

                    // No station selected: load tanks for all stations assigned to this employee
                    if (!availableStations || availableStations.length === 0) {
                        // fallback: fetch employee stations then load tanks
                        $.ajax({
                            url: `/api/stations_emp/${AUTH_USER_ID}`,
                            method: 'GET',
                            success: function (stations) {
                                availableStations = stations;
                                if (stations.length === 0) {
                                    renderTanks([]);
                                    return;
                                }
                                // fetch stationwise tanks in parallel
                                const calls = stations.map(s => fetch(`/api/stationwise/${s.id}`).then(r => r.json()));
                                Promise.all(calls).then(results => {
                                    const combined = results.flat();
                                    renderTanks(combined);
                                }).catch(err => console.error('Failed to load employee station tanks', err));
                            },
                            error: function (err) {
                                console.error('Failed to load employee stations', err);
                            }
                        });
                        return;
                    }

                    // We have availableStations: fetch stationwise tanks for each and combine
                    if (availableStations.length === 0) {
                        renderTanks([]);
                        return;
                    }

                    const calls = availableStations.map(s => fetch(`/api/stationwise/${s.id}`).then(r => r.json()));
                    Promise.all(calls).then(results => {
                        const combined = results.flat();
                        renderTanks(combined);
                    }).catch(err => console.error('Failed to load employee station tanks', err));

                    return;
                }

                // Owner / other roles: tanks for stations owned by user
                $.ajax({
                    url: `/api/user-tanks/${AUTH_USER_ID}`,
                    method: "GET",
                    success: function (tanks) {
                        let filtered = stationId ? tanks.filter(t => t.station_id == stationId) : tanks;
                        renderTanks(filtered);
                    },
                    error: function (err) {
                        console.error("Failed to load tanks", err);
                    }
                });
            }

            // renderTanks helper
            function renderTanks(filtered) {
                $('#tankContainer').empty();

                filtered.forEach(tank => {
                    let percentage = ((tank.current_level / tank.capacity) * 100).toFixed(1);
                    let badgeClass = tank.status === 'active' ? 'success' :
                        (tank.status === 'warning' ? 'warning' : 'danger');

                    let card = `
                                    <div class="col-12 col-xl-4 col-lg-6 mb-4 tank-card" data-station="${tank.station_id}">
                                        <div class="card card-hover" data-blockui-element="tank-${tank.id}">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h5 class="card-title mb-0">${tank.name}</h5>
                                                <a href="javascript:void(0)" class="card-collapsible" data-bs-toggle="collapse"
                                                   data-bs-target="#tankContent${tank.id}">
                                                   <i class="ri-arrow-down-s-line"></i>
                                                </a>
                                            </div>
                                            <div class="card-body collapse show" id="tankContent${tank.id}">
                                                <div class="row align-items-center">
                                                    <div class="col-md-5 text-center">
                                                        <div class="tank-visualization">
                                                            <div class="tank-fill" style="height: ${percentage}%"></div>
                                                            <div class="tank-percentage">${percentage}%</div>
                                                        </div>
                                                        <div class="mt-2">
                                                            <small class="text-muted">${tank.current_level}L / ${tank.capacity}L</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-7 tank-details">
                                                        <div class="mb-2"><strong>Site:</strong> ${tank.station_name ?? 'N/A'}</div>
                                                        <div class="mb-2"><strong>Status:</strong>
                                                            <span class="badge bg-${badgeClass}">
                                                                ${tank.status.charAt(0).toUpperCase() + tank.status.slice(1)}
                                                            </span>
                                                        </div>
                                                        <div class="mb-2"><strong>Product:</strong> ${tank.product_name ?? 'N/A'}</div>
                                                        <div class="mb-2"><strong>Capacity:</strong> ${tank.capacity} L</div>
                                                        <div class="mb-2"><strong>Current Stock:</strong> ${tank.current_level} L</div>
                                                        <div class="mb-2"><strong>Last Updated:</strong> ${tank.updated_at}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;

                    $('#tankContainer').append(card);
                });

                // Animate tank fill
                const tankElements = document.querySelectorAll('.tank-fill');
                tankElements.forEach(tank => {
                    const targetHeight = tank.style.height;
                    tank.style.height = '0%';
                    setTimeout(() => {
                        tank.style.transition = 'height 1.5s ease-in-out';
                        tank.style.height = targetHeight;
                    }, 300);
                });
            }

            // Initial load
            loadTanks();

            // Filter by station
            $(document).on("change", "#stationFilter", function () {
                let stationId = $(this).val();
                loadTanks(stationId);
            });

        });
    </script>
@endsection