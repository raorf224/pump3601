@extends('partials.layouts.master')

@section('title', 'Dashboard | Fuel Station Management')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">

    <style>
        .stat-card {
            transition: transform 0.2s ease-in-out;
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .supplier-list-item {
            cursor: pointer;
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }

        .supplier-list-item:hover,
        .supplier-list-item.active {
            background-color: #f0f4ff;
        }

        .supplier-list-item:last-child {
            border-bottom: none;
        }

        .apexcharts-container {
            background: transparent !important;
        }

        .stat-card .card-body {
            padding: 1.25rem !important;
        }

        /* Same height for both cards */
        .dashboard-main-cards {
            height: 320px !important;
            display: flex;
            flex-direction: column;
        }

        .dashboard-main-cards .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        #overview_chart {
            height: 200px !important;
            flex: 1;
        }

        .purchase-history-container {
            height: 250px !important;
            overflow-y: auto;
        }

        .purchase-history-container::-webkit-scrollbar {
            width: 4px;
        }

        .purchase-history-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .purchase-history-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .purchase-history-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Compact Purchase Items */
        .purchase-compact-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }

        .purchase-compact-item:hover {
            background-color: #f8f9fa;
        }

        .purchase-compact-item:last-child {
            border-bottom: none;
        }

        .purchase-type-badge {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .oil-badge {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .lube-badge {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .status-badge {
            font-size: 0.65rem;
            padding: 0.25rem 0.5rem;
        }

        .text-xs {
            font-size: 0.75rem !important;
        }

        .text-xxs {
            font-size: 0.7rem !important;
        }

        .row.gy-5 {
            --bs-gutter-y: 1rem !important;
        }

        /* Simple Icons Design */
        .stats-icon-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stats-icon {
            font-size: 1.8rem;
            padding: 10px;
            border-radius: 10px;
        }

        .tank-icon {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .dispenser-icon {
            background-color: #fce4ec;
            color: #c2185b;
        }

        .nozzle-icon {
            background-color: #e8f5e8;
            color: #388e3c;
        }

        .fuel-icon {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .lube-icon {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }

        .employee-icon {
            background-color: #e0f2f1;
            color: #00796b;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">

            <div class="row">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-end g-3 mb-4">

                            <!-- From Date -->
                            <div class="col-md-3">
                                <label for="from_date" class="form-label">From Date</label>
                                <input type="text" class="form-control" id="from_date" name="from_date"
                                    placeholder="Select start date">
                            </div>

                            <!-- To Date -->
                            <div class="col-md-3">
                                <label for="to_date" class="form-label">To Date</label>
                                <input type="text" class="form-control" id="to_date" name="to_date"
                                    placeholder="Select end date">
                            </div>

                            <!-- Station Dropdown -->
                            <div class="col-md-3">
                                <label for="station_filter" class="form-label">Station</label>
                                <select class="form-control" id="station_filter">
                                    <option value="">All Stations</option>
                                </select>
                            </div>

                            <!-- Filter Button -->
                            <div class="col-md-3">
                                <button class="btn btn-primary w-100" id="applyFilter">
                                    <i class="bi bi-funnel"></i> Apply Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xxl-8">
                        <div class="card stat-card dashboard-main-cards">
                            <div class="card-body">
                                <div class="row h-100">
                                    <div class="col-md-4 col-xxl-3">
                                        <p class="text-muted mb-3">Overview of Current Month</p>
                                        <div class="mb-3">
                                            <h3 class="mb-1 text-success" id="monthly-credit">Rs. 0.00</h3>
                                            <p class="text-muted text-xs">Current Monthly Credit (Income)</p>
                                        </div>
                                        <div class="mb-3">
                                            <h3 class="mb-1 text-danger" id="monthly-debit">Rs. 0.00</h3>
                                            <p class="text-muted text-xs">Current Monthly Debit (Expense)</p>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-xxl-9">
                                        <div id="overview_chart"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-4">
                        <div class="card shadow-sm border-0 dashboard-main-cards">
                            <div class="card-header d-flex justify-content-between align-items-center py-3">
                                <h6 class="mb-0 fw-semibold">Recent Purchase History</h6>
                                <a href="javascript:void(0)" class="link link-primary text-muted fs-13">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div id="purchase-history-list" class="purchase-history-container p-3">
                                    <!-- Purchase history will be loaded via JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Stats Cards -->
                <div class="col-12 mt-4">
                    <div class="row">
                        <div class="col-xl-2 col-md-4 col-6">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <div class="stats-icon-container mb-2">
                                        <i class="ri-database-2-line stats-icon tank-icon"></i>
                                    </div>
                                    <h1 class="display-5 mb-1" id="total-tanks">0</h1>
                                    <p class="text-muted mb-0">Total Tanks</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <div class="stats-icon-container mb-2">
                                        <i class="ri-gas-station-line stats-icon dispenser-icon"></i>
                                    </div>
                                    <h1 class="display-5 mb-1" id="total-dispensers">0</h1>
                                    <p class="text-muted mb-0">Dispensers</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <div class="stats-icon-container mb-2">
                                        <i class="bi bi-droplet stats-icon nozzle-icon"></i>
                                    </div>
                                    <h1 class="display-5 mb-1" id="total-nozzles">0</h1>
                                    <p class="text-muted mb-0">Nozzles</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <div class="stats-icon-container mb-2">
                                        <i class="ri-oil-line stats-icon fuel-icon"></i>
                                    </div>
                                    <h1 class="display-5 mb-1" id="fuel-products">0</h1>
                                    <p class="text-muted mb-0">Fuel Products</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <div class="stats-icon-container mb-2">
                                        <i class="ri-flask-line stats-icon lube-icon"></i>
                                    </div>
                                    <h1 class="display-5 mb-1" id="lube-products">0</h1>
                                    <p class="text-muted mb-0">Lube Products</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <div class="card text-center stat-card">
                                <div class="card-body">
                                    <div class="stats-icon-container mb-2">
                                        <i class="ri-user-line stats-icon employee-icon"></i>
                                    </div>
                                    <h1 class="display-5 mb-1" id="total-employees">0</h1>
                                    <p class="text-muted mb-0">Total Employees</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supplier Tracking -->
                <div class="col-xl-5 mt-4">
                    <div class="card card-h-100 stat-card">
                        <div class="card-header">
                            <h6 class="mb-0">Supplier Locations</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-0">
                                <div class="col-md-5" style="height: 350px; overflow-y: auto;">
                                    <div id="supplier-list">
                                        <!-- Data will be loaded via JavaScript -->
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div id="supplier_map"
                                        style="height: 350px; width: 100%; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                        <p class="text-muted">Loading map...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="col-xl-7 mt-4">
                    <div class="card card-h-100 stat-card">
                        <div class="card-header">
                            <h6 class="mb-0">Monthly Revenue (Income vs Expense)</h6>
                        </div>
                        <div class="card-body">
                            <div id="revenue_chart" class="apexcharts-container"></div>
                        </div>
                    </div>
                </div>

                <!-- Stations Table -->
                <div class="col-lg-12 mt-4">
                    <div class="card stat-card">
                        <div class="card-header">
                            <h6 class="mb-0">Stations</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Location</th>
                                            <th>City</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="stations-table">
                                        <!-- Data will be loaded via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employees Table -->
                <div class="col-lg-12 mt-4">
                    <div class="card stat-card">
                        <div class="card-header">
                            <h6 class="mb-0">Employees</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Role</th>
                                            <th>Phone</th>
                                            <th>Salary</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="employees-table">
                                        <!-- Data will be loaded via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>
    <script src="{{ asset('assets/js/ui/air-datepicker.init.js') }}"></script>


    <!-- Google Maps API -->
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyATkES15vt7nkDurNYc45ss_XgsqiZxd1U&libraries=places&callback=initMap">
        </script>

    <script>
        let map;
        let markers = [];
        let currentInfoWindow = null;
        const AUTH_USER_ID = "{{ Auth::id() }}";
        const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";
        console.log("Authenticated User ID:", AUTH_USER_ID);

        // Initialize Google Map
        function initMap() {
            // Default location (Karachi)
            const defaultLocation = { lat: 24.8607, lng: 67.0011 };

            map = new google.maps.Map(document.getElementById("supplier_map"), {
                zoom: 10,
                center: defaultLocation,
                mapTypeControl: true,
                streetViewControl: false,
                styles: [
                    {
                        "featureType": "administrative",
                        "elementType": "labels.text.fill",
                        "stylers": [{ "color": "#444444" }]
                    },
                    {
                        "featureType": "landscape",
                        "stylers": [{ "color": "#f2f2f2" }]
                    },
                    {
                        "featureType": "poi",
                        "stylers": [{ "visibility": "off" }]
                    },
                    {
                        "featureType": "road",
                        "stylers": [{ "saturation": -100 }, { "lightness": 45 }]
                    },
                    {
                        "featureType": "road.highway",
                        "stylers": [{ "visibility": "simplified" }]
                    },
                    {
                        "featureType": "road.arterial",
                        "elementType": "labels.icon",
                        "stylers": [{ "visibility": "off" }]
                    },
                    {
                        "featureType": "transit",
                        "stylers": [{ "visibility": "off" }]
                    },
                    {
                        "featureType": "water",
                        "stylers": [{ "color": "#d4e6ff" }, { "visibility": "on" }]
                    }
                ]
            });

            console.log('Google Maps initialized');
        }

        // Add supplier markers to map
        function addSupplierMarkers(suppliers) {
            // Clear existing markers
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            if (suppliers.length === 0) {
                document.getElementById('supplier_map').innerHTML = '<p class="text-muted">No suppliers with coordinates found.</p>';
                return;
            }

            const bounds = new google.maps.LatLngBounds();
            let hasValidCoordinates = false;

            suppliers.forEach((supplier, index) => {
                if (!supplier.coords) return;

                try {
                    const [lat, lng] = supplier.coords.split(',').map(coord => parseFloat(coord.trim()));

                    if (isNaN(lat) || isNaN(lng)) {
                        console.warn(`Invalid coordinates for supplier ${supplier.name}: ${supplier.coords}`);
                        return;
                    }

                    const position = { lat, lng };

                    // Create marker
                    const marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: supplier.name,
                        animation: google.maps.Animation.DROP,
                        icon: {
                            url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                            scaledSize: new google.maps.Size(32, 32)
                        }
                    });

                    // Create info window
                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                                                                        <div style="padding: 10px; max-width: 250px;">
                                                                            <h6 style="margin: 0 0 8px 0; color: #333;">${supplier.name}</h6>
                                                                            <p style="margin: 0 0 5px 0; color: #666; font-size: 12px;">
                                                                                <strong>Phone:</strong> ${supplier.phone || 'N/A'}<br>
                                                                                <strong>Address:</strong> ${supplier.address || 'N/A'}
                                                                            </p>
                                                                            <small style="color: #888;">Coordinates: ${lat.toFixed(6)}, ${lng.toFixed(6)}</small>
                                                                        </div>
                                                                    `
                    });

                    // Add click event to marker
                    marker.addListener('click', () => {
                        if (currentInfoWindow) {
                            currentInfoWindow.close();
                        }
                        infoWindow.open(map, marker);
                        currentInfoWindow = infoWindow;

                        // Highlight corresponding list item
                        highlightSupplierListItem(index);
                    });

                    markers.push(marker);
                    bounds.extend(position);
                    hasValidCoordinates = true;

                } catch (error) {
                    console.error(`Error processing coordinates for supplier ${supplier.name}:`, error);
                }
            });

            // Fit map to show all markers
            if (hasValidCoordinates) {
                map.fitBounds(bounds);

                // Add a slight padding
                const padding = 50;
                map.panToBounds(bounds, padding);
            } else {
                document.getElementById('supplier_map').innerHTML = '<p class="text-muted">No valid coordinates found for suppliers.</p>';
            }

            // Add click events to supplier list items
            addSupplierListEvents(suppliers);
        }

        // Add click events to supplier list items
        function addSupplierListEvents(suppliers) {
            const listItems = document.querySelectorAll('.supplier-list-item');

            listItems.forEach((item, index) => {
                item.addEventListener('click', function () {
                    const supplier = suppliers[index];
                    if (!supplier.coords) return;

                    try {
                        const [lat, lng] = supplier.coords.split(',').map(coord => parseFloat(coord.trim()));

                        if (isNaN(lat) || isNaN(lng)) return;

                        const position = { lat, lng };

                        // Center map on this supplier
                        map.setCenter(position);
                        map.setZoom(15);

                        // Open info window for corresponding marker
                        if (markers[index]) {
                            if (currentInfoWindow) {
                                currentInfoWindow.close();
                            }

                            const infoWindow = new google.maps.InfoWindow({
                                content: `
                                                                                <div style="padding: 10px; max-width: 250px;">
                                                                                    <h6 style="margin: 0 0 8px 0; color: #333;">${supplier.name}</h6>
                                                                                    <p style="margin: 0 0 5px 0; color: #666; font-size: 12px;">
                                                                                        <strong>Phone:</strong> ${supplier.phone || 'N/A'}<br>
                                                                                        <strong>Address:</strong> ${supplier.address || 'N/A'}
                                                                                    </p>
                                                                                    <small style="color: #888;">Coordinates: ${lat.toFixed(6)}, ${lng.toFixed(6)}</small>
                                                                                </div>
                                                                            `
                            });

                            infoWindow.open(map, markers[index]);
                            currentInfoWindow = infoWindow;
                        }

                        // Highlight list item
                        highlightSupplierListItem(index);

                    } catch (error) {
                        console.error('Error centering map on supplier:', error);
                    }
                });
            });

            // Auto-click first supplier with valid coordinates
            setTimeout(() => {
                const firstValidSupplier = suppliers.findIndex(s => s.coords);
                if (firstValidSupplier !== -1) {
                    listItems[firstValidSupplier]?.click();
                }
            }, 1000);
        }

        // Highlight supplier list item
        function highlightSupplierListItem(index) {
            const listItems = document.querySelectorAll('.supplier-list-item');
            listItems.forEach(item => item.classList.remove('active'));
            if (listItems[index]) {
                listItems[index].classList.add('active');

                // Scroll to make item visible
                listItems[index].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        // Load dashboard data from API
        async function loadDashboardData() {
            try {
                const response = await fetch('/api/dashboard');
                const data = await response.json();

                if (data) {
                    updateDashboardUI(data);

                    // Initialize maps after a short delay to ensure DOM is ready
                    setTimeout(() => {
                        if (typeof google !== 'undefined' && google.maps) {
                            addSupplierMarkers(data.suppliers);
                        } else {
                            console.warn('Google Maps not loaded yet, retrying...');
                            setTimeout(() => {
                                if (typeof google !== 'undefined' && google.maps) {
                                    addSupplierMarkers(data.suppliers);
                                }
                            }, 2000);
                        }
                    }, 500);
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        // Update UI with data
        function updateDashboardUI(data) {
            // Update financial data
            document.getElementById('monthly-credit').textContent = 'Rs. ' + (data.monthlyCredit || 0).toLocaleString();
            document.getElementById('monthly-debit').textContent = 'Rs .' + (data.monthlyDebit || 0).toLocaleString();

            // Update stats cards
            document.getElementById('total-tanks').textContent = data.stats?.tanks || 0;
            document.getElementById('total-dispensers').textContent = data.stats?.dispensers || 0;
            document.getElementById('total-nozzles').textContent = data.stats?.nozzles || 0;
            document.getElementById('fuel-products').textContent = data.stats?.fuelProducts || 0;
            document.getElementById('lube-products').textContent = data.stats?.lubeProducts || 0;
            document.getElementById('total-employees').textContent = data.stats?.employees || 0;

            // Update purchase history
            updatePurchaseHistory(data.purchaseHistory);

            // Update suppliers list
            updateSuppliersList(data.suppliers);

            // Update stations table
            updateStationsTable(data.stations);

            // Update employees table
            updateEmployeesTable(data.employeesList);

            // Initialize charts
            initializeCharts(data);
        }

        // Update suppliers list
        function updateSuppliersList(suppliers) {
            const container = document.getElementById('supplier-list');
            let html = '';

            if (!suppliers || suppliers.length === 0) {
                html = '<p class="p-3 text-muted">No suppliers found.</p>';
            } else {
                suppliers.forEach((supplier, index) => {
                    const hasCoords = supplier.coords && supplier.coords.includes(',');
                    const coordsText = hasCoords ? '📍' : '❌';

                    html += `
                                                                <div class="d-flex align-items-center supplier-list-item ${hasCoords ? 'has-coords' : 'no-coords'}" 
                                                                     data-coords="${supplier.coords || ''}" 
                                                                     data-name="${supplier.name}">
                                                                    <div class="me-2">${coordsText}</div>
                                                                    <div class="flex-grow-1">
                                                                        <h6 class="mb-0 fs-14">${supplier.name}</h6>
                                                                        <p class="text-muted fs-12 mb-0">${supplier.address || 'No address'}</p>
                                                                        ${!hasCoords ? '<small class="text-warning">No coordinates</small>' : ''}
                                                                    </div>
                                                                </div>
                                                            `;
                });
            }

            container.innerHTML = html;
        }

        // Oil Purchase History
        function updatePurchaseHistory(purchaseHistory) {
            const container = document.getElementById('purchase-history-list');
            let html = '';

            // === OIL PURCHASES ===
            (purchaseHistory?.oil || []).forEach(purchase => {
                html += `
                            <div class="list-group-item py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold text-primary">Oil Purchase - INV#${purchase.invoice_no}</h6>
                                    <span class="badge bg-${purchase.payment_status === 'paid'
                        ? 'success'
                        : purchase.payment_status === 'partial'
                            ? 'warning'
                            : 'secondary'} text-capitalize">
                                        ${purchase.payment_status}
                                    </span>
                                </div>

                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    ${new Date(purchase.order_date).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })}
                                </small>

                                ${purchase.station ? `
                                    <div><i class="bi bi-geo-alt me-1 text-secondary"></i>
                                        <strong>Station:</strong> ${purchase.station.name}
                                    </div>` : ''}

                                ${purchase.supplier ? `
                                    <div><i class="bi bi-person-badge me-1 text-secondary"></i>
                                        <strong>Supplier:</strong> ${purchase.supplier.name}
                                    </div>` : ''}

                                ${purchase.tank ? `
                                    <div><i class="bi bi-fuel-pump me-1 text-secondary"></i>
                                        <strong>Tank:</strong> ${purchase.tank.name}
                                    </div>` : ''}

                                <div class="mt-2 small text-muted">
                                    <strong>Qty:</strong> ${purchase.recieved_qty ?? 0} |
                                    <strong>Rate:</strong> ${purchase.rate ?? '-'}
                                </div>
                            </div>
                            `;
            });

            // === LUBE PURCHASES ===
            (purchaseHistory?.lube || []).forEach(purchase => {
                html += `
                            <div class="list-group-item py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold text-warning">Lube Purchase - INV#${purchase.invoice_no}</h6>
                                    <span class="badge bg-${purchase.payment_status === 'paid'
                        ? 'success'
                        : purchase.payment_status === 'partial'
                            ? 'warning'
                            : 'secondary'} text-capitalize">
                                        ${purchase.payment_status}
                                    </span>
                                </div>

                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    ${new Date(purchase.date).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })}
                                </small>

                                ${purchase.station ? `
                                    <div><i class="bi bi-geo-alt me-1 text-secondary"></i>
                                        <strong>Station:</strong> ${purchase.station.name}
                                    </div>` : ''}

                                ${purchase.account ? `
                                    <div><i class="bi bi-person-badge me-1 text-secondary"></i>
                                        <strong>Supplier:</strong> ${purchase.account.name}
                                    </div>` : ''}

                                ${purchase.lines && purchase.lines.length ? `
                                    <div class="mt-1 small">
                                        <i class="bi bi-droplet me-1 text-secondary"></i>
                                        <strong>Product:</strong> ${purchase.lines[0].product?.name || '-'} (${purchase.lines[0].qty} × ${purchase.lines[0].unit_price})
                                    </div>` : ''}
                            </div>
                            `;
            });

            // === Empty State ===
            if (!purchaseHistory?.oil?.length && !purchaseHistory?.lube?.length) {
                html = `
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 mb-2"></i>
                                <p class="mb-0">No recent purchases found</p>
                            </div>`;
            }

            container.innerHTML = `<div class="list-group list-group-flush">${html}</div>`;
        }


        // Update stations table
        function updateStationsTable(stations) {
            const container = document.getElementById('stations-table');
            let html = '';

            (stations || []).forEach(station => {
                html += `
                                                            <tr>
                                                                <td>${station.id}</td>
                                                                <td>${station.name}</td>
                                                                <td>${station.location}</td>
                                                                <td>${station.city}</td>
                                                                <td>
                                                                    ${station.status == 1 ?
                        '<span class="badge bg-success">Active</span>' :
                        '<span class="badge bg-danger">Inactive</span>'
                    }
                                                                </td>
                                                            </tr>
                                                        `;
            });

            if (!stations || stations.length === 0) {
                html = '<tr><td colspan="5" class="text-muted text-center">No stations found</td></tr>';
            }

            container.innerHTML = html;
        }

        // Update employees table
        function updateEmployeesTable(employees) {
            const container = document.getElementById('employees-table');
            let html = '';

            (employees || []).forEach(employee => {
                html += `
                                                            <tr>
                                                                <td>${employee.id}</td>
                                                                <td>${employee.user?.full_name || 'N/A'}</td>
                                                                <td>${(employee.role || '').charAt(0).toUpperCase() + (employee.role || '').slice(1)}</td>
                                                                <td>${employee.phone || 'N/A'}</td>
                                                                <td>Rs. ${(employee.salary || 0).toLocaleString()}</td>
                                                                <td>
                                                                    <span class="badge bg-${employee.status == 'active' ? 'success' : 'secondary'}">
                                                                        ${(employee.status || '').charAt(0).toUpperCase() + (employee.status || '').slice(1)}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        `;
            });

            if (!employees || employees.length === 0) {
                html = '<tr><td colspan="6" class="text-muted text-center">No employees found</td></tr>';
            }

            container.innerHTML = html;
        }

        // Initialize charts
        function initializeCharts(data) {
            // Overview Chart
            if (document.querySelector("#overview_chart")) {
                const overviewChart = new ApexCharts(document.querySelector("#overview_chart"), {
                    chart: {
                        type: 'area',
                        height: 250,
                        toolbar: { show: false }
                    },
                    series: [
                        {
                            name: 'Credit (Income)',
                            data: data.transactionGraph?.creditData || []
                        },
                        {
                            name: 'Debit (Expense)',
                            data: data.transactionGraph?.debitData || []
                        }
                    ],
                    xaxis: {
                        categories: data.transactionGraph?.labels || []
                    },
                    colors: ['#28a745', '#dc3545'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2 },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: 0.6,
                            opacityTo: 0.1,
                        }
                    },
                    legend: { position: 'top' }
                });
                overviewChart.render();
            }

            // Revenue Chart
            if (document.querySelector("#revenue_chart")) {
                const revenueChart = new ApexCharts(document.querySelector("#revenue_chart"), {
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    series: [{
                        name: 'Monthly Data',
                        data: [data.monthlyCredit || 0, data.monthlyDebit || 0]
                    }],
                    xaxis: {
                        categories: ['Income', 'Expense']
                    },
                    colors: ['#17a2b8', '#fd7e14'],
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '50%'
                        }
                    }
                });
                revenueChart.render();
            }
        }



        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function () {
            // === Default From/To Dates ===
            const today = new Date();
            const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            // Format YYYY-MM-DD
            const formatDate = (date) =>
                date.toISOString().split('T')[0];

            document.getElementById('from_date').value = formatDate(startOfMonth);
            document.getElementById('to_date').value = formatDate(endOfMonth);

            // === Initialize AirDatepickers ===
            new AirDatepicker('#from_date', {
                autoClose: true,
                dateFormat: 'yyyy-MM-dd',
                locale: localeEn,
                selectedDates: [startOfMonth],
            });

            new AirDatepicker('#to_date', {
                autoClose: true,
                dateFormat: 'yyyy-MM-dd',
                locale: localeEn,
                selectedDates: [endOfMonth],
            });

            // === Station Dropdown (Choices.js) ===
            const stationSelect = new Choices('#station_filter', {
                searchPlaceholderValue: 'Search station...',
                shouldSort: false,
            });

            // === Load Station Options from API ===
            const AUTH_USER_ID = "{{ Auth::id() }}";
            const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";

            let apiUrl;
            if (AUTH_ROLE === 'admin') {
                apiUrl = '/api/stations';
            } else if (AUTH_ROLE === 'employee') {
                apiUrl = `/api/stations_emp/${AUTH_USER_ID}`;
            } else {
                apiUrl = `/api/stations/${AUTH_USER_ID}`;
            }

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    const stationOptions = data.map(station => ({
                        value: station.id,
                        label: station.name
                    }));
                    stationSelect.clearChoices();
                    stationSelect.setChoices(stationOptions, 'value', 'label', true);
                })
                .catch(err => console.error('Station API error:', err));

            // === Apply Filter Button ===
            document.getElementById('applyFilter').addEventListener('click', function () {
                const fromDate = document.getElementById('from_date').value;
                const toDate = document.getElementById('to_date').value;
                const stationId = document.getElementById('station_filter').value;

                console.log('Filter applied:', { fromDate, toDate, stationId });

                // Later you can connect this with your dashboard filter API:
                // loadDashboardData({ fromDate, toDate, stationId });
            });

            // Load Dashboard Data initially
            loadDashboardData();
        });


        // Fallback if Google Maps callback fails
        setTimeout(() => {
            if (typeof google === 'undefined') {
                console.error('Google Maps failed to load');
                document.getElementById('supplier_map').innerHTML =
                    '<div class="text-center text-muted p-4">' +
                    '<p>Google Maps failed to load</p>' +
                    '<small>Please check your internet connection</small>' +
                    '</div>';
            }
        }, 10000);
    </script>
@endsection