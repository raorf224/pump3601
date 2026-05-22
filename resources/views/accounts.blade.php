@extends('partials.layouts.master')

@section('title', 'Accounts Management | ' . Auth::user()->full_name)
@section('title-sub', 'Finance')
@section('pagetitle', 'Accounts Management')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <style>
        .card-hover:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }

        .required:after {
            content: " *";
            color: red;
        }

        .dataTables_wrapper {
            overflow-x: hidden !important;
        }


        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }

        .custom-pagination {
            margin-top: 15px;
            text-align: right;
        }

        .account-type-fields {
            display: none;
        }
    </style>
@endsection


@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">

            <!-- Accordion -->
            <div class="accordion accordion-primary accordion-border-box mb-4" id="accountAccordion">
                <!-- Create Account -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#createAccountCollapse" aria-expanded="true">
                            <i class="bi bi-person-plus me-2"></i> Create Account
                        </button>
                    </h2>
                    <!-- Toast Container -->
                    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
                        <div id="mainToast" class="toast align-items-center text-bg-primary border-0" role="alert"
                            aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body" id="toastMessage">
                                    <!-- Message Injected Here -->
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                    aria-label="Close"></button>
                            </div>
                        </div>
                    </div>

                    <div id="createAccountCollapse" class="accordion-collapse collapse show"
                        data-bs-parent="#accountAccordion">
                        <div class="accordion-body">
                            <form id="accountForm" method="POST">
                                @csrf
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label>Account Type *</label>
                                        <select name="type" id="type" class="form-select" required>
                                            <option value="">Select Account Type</option>
                                            <option value="customer">Customer</option>
                                            <option value="supplier">Supplier</option>
                                            <option value="bank">Bank</option>
                                            <option value="creditcard">Credit Card</option>
                                            <option value="fuelcard">Fuel Card</option>
                                            <option value="cash">Cash</option>
                                            <option value="extras">Extras</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label>Station *</label>
                                        <select name="station_id" id="station_id" class="form-select" required>
                                            <option value="">Search Station...</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label>Name *</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label>Phone *</label>
                                        <input type="number" name="phone" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label>CNIC</label>
                                        <input type="number" name="cnic" class="form-control">
                                    </div>
                                </div>

                                <!-- Supplier specific fields -->
                                <div id="supplierFields" class="account-type-fields">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label>Address</label>
                                            <input type="text" name="address" id="address" class="form-control"
                                                placeholder="Address">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Coordinates</label>
                                            <input type="text" name="coords" id="coords" class="form-control"
                                                placeholder="Lat,Lng">
                                        </div>
                                    </div>
                                    <!-- Uncomment if you want to use Google Maps autocomplete -->
                                    <!--
                                        <div class="mb-3">
                                            <label for="autocomplete">Search Address</label>
                                            <input type="text" id="autocomplete" placeholder="Enter supplier address"
                                                class="form-control" autocomplete="off">
                                        </div>
                                        <div id="map" style="height: 300px; width: 100%;" class="mb-3"></div>
                                        -->
                                </div>

                                <!-- Bank specific fields -->
                                <div id="bankFields" class="account-type-fields">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label>Account Number</label>
                                            <input type="text" name="account_number" class="form-control"
                                                placeholder="Account Number">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Bank Name</label>
                                            <input type="text" name="bank_name" class="form-control"
                                                placeholder="Bank Name">
                                        </div>
                                        <div class="col-md-6">
                                            <label>MDR</label>
                                            <input type="text" name="mdr" class="form-control"
                                                placeholder="MDR">
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="account_id" name="id">

                                <button type="submit" class="btn btn-primary">Create Account</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div><!-- /accordion -->

            <!-- Accounts Table -->
            <div class="card shadow-sm card-hover">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Accounts List</h5>
                </div>
                <div class="card-body">
                    <div class="table-box table-responsive">
                        <table id="accountsTable" class="table text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Site</th>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="custom-pagination">
                        <button class="btn btn-primary" id="nextPageBtn">Next Page</button>
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
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

    <!-- Google Maps -->
    <!-- <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyATkES15vt7nkDurNYc45ss_XgsqiZxd1U&libraries=places&callback=initMap">
    </script> -->
    <script>
        const AUTH_USER_ID = "{{ Auth::id() }}";
        const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";

        let map, marker, autocomplete, table;
        let typeChoices, stationChoices;
        // track current ajax url for table-backed modes
        let currentTableAjaxUrl = null;
        // cache for employee stations used when aggregating accounts
        let employeeStationsCache = null;
        let userPermissions = [];


        // function initMap() {
        //     const defaultLocation = {
        //         lat: 25.276987,
        //         lng: 55.296249
        //     };
        //     map = new google.maps.Map(document.getElementById("map"), {
        //         center: defaultLocation,
        //         zoom: 12,
        //     });

        //     marker = new google.maps.Marker({
        //         map,
        //         position: defaultLocation,
        //         draggable: true,
        //     });

        //     autocomplete = new google.maps.places.Autocomplete(document.getElementById("autocomplete"));
        //     autocomplete.bindTo("bounds", map);

        //     autocomplete.addListener("place_changed", function() {
        //         let place = autocomplete.getPlace();
        //         if (!place.geometry) return;
        //         if (place.geometry.viewport) map.fitBounds(place.geometry.viewport);
        //         else {
        //             map.setCenter(place.geometry.location);
        //             map.setZoom(15);
        //         }
        //         marker.setPosition(place.geometry.location);
        //         $("#coords").val(place.geometry.location.lat() + "," + place.geometry.location.lng());
        //         $("#address").val(place.formatted_address);
        //     });

        //     google.maps.event.addListener(marker, "dragend", function() {
        //         let lat = marker.getPosition().lat();
        //         let lng = marker.getPosition().lng();
        //         $("#coords").val(lat + "," + lng);
        //     });
        // }

        $(document).ready(function () {

            $.get(`/api/getpermissionbyuserid/{{Auth::user()->id}}/{{Auth::user()->role}}`, function (permissions) {
                userPermissions = permissions;
                if (!hasPermission('accounts', 'create')) {
                    $('#accountAccordion').hide();
                }

            });
            typeChoices = new Choices("#type", {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false
            });

            let apiUrl;
            if (AUTH_ROLE === 'admin') {
                apiUrl = '/api/stations';
            } else if (AUTH_ROLE === 'employee') {
                apiUrl = `/api/stations_emp/${AUTH_USER_ID}`;
            } else {
                apiUrl = `/api/stations/${AUTH_USER_ID}`;
            }


            // ✅ Fetch Stations via API and Init Choices.js (role-aware)
            (function fetchStations() {
                $.ajax({
                    url: apiUrl,
                    method: 'GET',
                    success: function (resp) {
                        // normalize array response: either [] or { data: [] }
                        let stations = Array.isArray(resp) ? resp : (resp && Array.isArray(resp.data) ?
                            resp.data : []);
                        let stationSelect = $("#station_id");
                        stationSelect.empty().append(`<option value="">Search Station...</option>`);
                        stations.forEach(st => {
                            stationSelect.append(
                                `<option value="${st.id}">${st.name}</option>`);
                        });

                        if (stationChoices) stationChoices.destroy();
                        stationChoices = new Choices("#station_id", {
                            searchEnabled: true,
                            itemSelectText: '',
                            shouldSort: false
                        });
                    },
                    error: function (xhr, status, err) {
                        console.error('Failed to load stations', status, err);
                    }
                });
            })();

            // ✅ Show/Hide account type specific fields - SINGLE EVENT HANDLER
            $("#type").on("change", function () {
                const selectedType = $(this).val();

                // Hide all type-specific fields first
                $(".account-type-fields").hide();

                // Show fields based on selected type
                if (selectedType === "supplier") {
                    $("#supplierFields").show();
                    // Uncomment if you want to initialize map for suppliers
                    // initMap();
                } else if (selectedType === "bank") {
                    $("#bankFields").show();
                } else if (selectedType === "creditcard") {
                    $("#bankFields").show();
                }
                else if (selectedType === "fuelcard") {
                    $("#bankFields").show();
                }
            });

            // ✅ DataTable init (role-aware)
            (function initAccountsTable() {
                const adminUrl = '/api/accounts';
                const ownerUrl = `/api/user-accounts/${AUTH_USER_ID}`;

                function createConfig(dataSourceIsAjax, source) {
                    const base = {
                        paging: true,
                        searching: false,
                        info: false,
                        lengthChange: false,
                        ordering: false,
                        pageLength: 10,
                        scrollX: false, // remove scroll
                        columns: [{
                            data: "id"
                        },
                        {
                            data: "station_name"
                        },
                        {
                            data: "type"
                        },
                        {
                            data: "name"
                        },
                        {
                            data: "phone"
                        },
                        {
                            data: "email"
                        },
                        {
                            data: null,
                            render: function (row) {
                                let buttons = '';

                                if (hasPermission('accounts', 'update')) {
                                    buttons += `
                <button class="btn btn-sm btn-primary" onclick="viewAccount(${row.id})">
                    <i class="bi bi-pencil"></i>
                </button>`;
                                }

                                if (hasPermission('accounts', 'delete')) {
                                    buttons += `
                <button class="btn btn-sm btn-danger" onclick="deleteAccount(${row.id})">
                    <i class="bi bi-trash"></i>
                </button>`;
                                }

                                return buttons ?
                                    `<div class="btn-group btn-group-sm" role="group">${buttons}</div>` :
                                    `<span class="text-muted small">No actions</span>`;
                            }

                        }
                        ],
                        columnDefs: [{
                            targets: -1,
                            className: "text-center"
                        }]
                    };

                    if (dataSourceIsAjax) {
                        base.ajax = {
                            url: source,
                            dataSrc: ""
                        };
                    } else {
                        base.data = source || [];
                    }

                    return base;
                }

                function initWithData(data) {
                    table = $('#accountsTable').DataTable(createConfig(false, data));
                }

                // For employee role: fetch accounts from all stations for this employee
                function fetchEmployeeAccounts() {
                    return fetch(`/api/stations_emp/${AUTH_USER_ID}`)
                        .then(r => r.ok ? r.json() : [])
                        .then(stations => {
                            if (!Array.isArray(stations) || stations.length === 0) return [];
                            // cache stations for later refreshes
                            employeeStationsCache = stations;
                            const calls = stations.map(s => fetch(`/api/stations/${s.id}/accounts`).then(r => r
                                .ok ? r.json() : []).catch(() => []));
                            return Promise.all(calls).then(results => {
                                return results.flatMap(r => Array.isArray(r) ? r : (r && Array.isArray(r
                                    .data) ? r.data : []));
                            });
                        }).catch(err => {
                            console.error('Failed to fetch employee stations/accounts', err);
                            return [];
                        });
                }

                // Refresh table data in a role-aware way
                function refreshTable() {
                    if (!table) return;

                    // Admin / Owner: DataTable is ajax-backed
                    if (currentTableAjaxUrl) {
                        try {
                            // prefer DataTables API reload
                            if (table && table.ajax) {
                                table.ajax.url(currentTableAjaxUrl).load(null, false);
                                return;
                            }
                        } catch (e) {
                            console.warn('Ajax table reload failed, falling back to reinit', e);
                        }
                    }

                    // Employee: re-aggregate and replace inline data
                    if (AUTH_ROLE === 'employee') {
                        fetchEmployeeAccounts().then(data => {
                            try {
                                if (table && table.clear && table.rows) {
                                    table.clear();
                                    table.rows.add(data);
                                    table.draw(false);
                                }
                            } catch (e) {
                                console.warn('Failed to refresh employee inline table', e);
                            }
                        });
                        return;
                    }

                    // Fallback: attempt a full redraw
                    try {
                        table.draw(false);
                    } catch (e) {
                        /* ignore */
                    }
                }

                if (AUTH_ROLE === 'admin') {
                    currentTableAjaxUrl = adminUrl;
                    table = $('#accountsTable').DataTable(createConfig(true, adminUrl));
                    attachTableHooks();
                } else if (AUTH_ROLE === 'employee') {
                    // aggregate station accounts for this employee (initial load)
                    fetchEmployeeAccounts().then(combined => {
                        initWithData(combined);
                        attachTableHooks();
                    }).catch(err => {
                        console.error('Failed to aggregate station accounts', err);
                        initWithData([]);
                        attachTableHooks();
                    });
                } else {
                    // owner / other
                    currentTableAjaxUrl = ownerUrl;
                    table = $('#accountsTable').DataTable(createConfig(true, ownerUrl));
                    attachTableHooks();
                }
            })();

            // Attach pagination hooks once table exists
            function attachTableHooks() {
                if (!table) return;
                // Next page button
                $('#nextPageBtn').off('click').on('click', function () {
                    try {
                        table.page('next').draw('page');
                    } catch (e) {
                        console.warn('Table paging failed', e);
                    }
                });

                // Update next button visibility on draw
                $('#accountsTable').off('draw.dt').on('draw.dt', function () {
                    try {
                        let pageInfo = table.page.info();
                        if (pageInfo.pages <= 1 || pageInfo.page === pageInfo.pages - 1) {
                            $('#nextPageBtn').hide();
                        } else {
                            $('#nextPageBtn').show();
                        }
                    } catch (e) {
                        console.warn('Failed to update pagination button', e);
                    }
                });

                // Run once to set initial state
                try {
                    let pageInfo = table.page.info();
                    if (pageInfo.pages <= 1 || pageInfo.page === pageInfo.pages - 1) $('#nextPageBtn').hide();
                    else $('#nextPageBtn').show();
                } catch (e) {
                    /* ignore */
                }
            }

            // ✅ Submit Form (Create or Update)
            $('#accountForm').on('submit', function (e) {
                e.preventDefault();

                let accountId = $("#account_id").val(); // hidden input
                let formData = $(this).serialize();

                let url = "/api/accounts";
                let method = "POST";

                // Agar id hai → Update
                if (accountId) {
                    url = `/api/accounts/${accountId}`;
                    method = "PUT";
                }

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    success: function (res) {
                        showToast(accountId ? "✅ Account updated successfully!" :
                            "✅ Account created successfully!");
                        // refresh table in a role-aware manner
                        try {
                            refreshTable();
                        } catch (e) {
                            console.warn('refreshTable failed', e);
                        }
                        $('#accountForm')[0].reset();
                        // Hide all type-specific fields on reset
                        $(".account-type-fields").hide();
                        $("#account_id").val(""); // reset id
                        // Reset the type select
                        if (typeChoices) {
                            typeChoices.setChoiceByValue('');
                        }
                        window.location.reload(); // Commented out to prevent page reload
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        showToast("❌ Error saving account!", true);
                    }
                });
            });

        });

        // ✅ Delete Account
        function deleteAccount(id) {
            if (!confirm("Are you sure you want to delete this account?")) return;
            $.ajax({
                url: `/api/accounts/${id}`,
                method: "DELETE",
                success: function () {
                    showToast("Account deleted successfully!");
                    try {
                        refreshTable();
                    } catch (e) {
                        console.warn('refreshTable failed', e);
                    }
                },
                error: function () {
                    showToast("Error deleting account!", true);
                }
            });
        }

        // ✅ View Account (for Edit)
        function viewAccount(id) {
            $.get(`/api/accounts/${id}`, function (data) {
                // hidden id
                $("#account_id").val(data.id);

                // type select
                if (typeChoices) {
                    typeChoices.setChoiceByValue(data.type);
                }

                // station select
                if (stationChoices) {
                    stationChoices.setChoiceByValue(data.station_id.toString());
                }

                // text inputs
                $("input[name='name']").val(data.name);
                $("input[name='phone']").val(data.phone);
                $("input[name='email']").val(data.email ?? "");
                $("input[name='cnic']").val(data.cnic ?? "");

                // Hide all type-specific fields first
                $(".account-type-fields").hide();

                // Show and populate fields based on account type
                if (data.type === "supplier") {
                    $("#supplierFields").show();
                    $("#address").val(data.address);
                    $("#coords").val(data.coords);

                    // if (data.coords) {
                    //     let [lat, lng] = data.coords.split(",");
                    //     let position = new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
                    //     map.setCenter(position);
                    //     map.setZoom(15);
                    //     marker.setPosition(position);
                    // }
                } else if (data.type === "bank") {
                    $("#bankFields").show();
                    $("input[name='account_number']").val(data.account_number ?? "");
                    $("input[name='bank_name']").val(data.bank_name ?? "");
                    $("input[name='mdr']").val(data.mdr ?? "");
                }
                else if (data.type === "creditcard") {
                    $("#bankFields").show();
                    $("input[name='account_number']").val(data.account_number ?? "");
                    $("input[name='bank_name']").val(data.bank_name ?? "");
                    $("input[name='mdr']").val(data.mdr ?? "");
                }
                else if (data.type === "fuelcard") {
                    $("#bankFields").show();
                    $("input[name='account_number']").val(data.account_number ?? "");
                    $("input[name='bank_name']").val(data.bank_name ?? "");
                    $("input[name='mdr']").val(data.mdr ?? "");
                }
            });
        }

        // ✅ Toast Notification
        function showToast(message, isError = false) {
            const toastEl = document.getElementById("mainToast");
            const toastBody = document.getElementById("toastMessage");

            toastBody.textContent = message;

            // Pehle dono hatao
            toastEl.classList.remove("text-bg-primary", "text-bg-danger", "text-bg-success");

            if (isError) {
                toastEl.classList.add("text-bg-danger"); // 🔴 Error = Red
            } else {
                toastEl.classList.add("text-bg-success"); // 🟢 Success = Green
            }

            const bsToast = new bootstrap.Toast(toastEl, {
                delay: 3000
            });
            bsToast.show();
        }


        function hasPermission(moduleName, action) {
            const module = userPermissions.find(p => p.name === moduleName);
            if (!module) return false;
            return module[action] == 1;
        }
    </script>

@endsection