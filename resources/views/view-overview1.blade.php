@extends('partials.layouts.master')

@section('title', 'Site Setup')

@section('title-sub', 'Logistics')
@section('pagetitle', 'Overview')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <style>
        <style>.card-hover {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #0d6efd !important;
        }

        .order-item {
            border-bottom: 1px dashed #e9ecef;
            padding: 12px 0;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .status-badge {
            font-size: 0.7rem;
            padding: 4px 8px;
        }

        /* Validation styles */
        .is-valid {
            border-color: #198754 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6.4.4.4-.4'/%3e%3cpath d='M6 7v2'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
    </style>
    </style>
@endsection
@section('content')

    <!-- Begin page -->
    <div id="layout-wrapper">
        <div class="row">
            <div class="col-xl-9">
                <div class="card">
                    <div class="card-body">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <!-- Status badge if needed -->
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row g-3">
                            <!-- Tanks -->
                            <div class="col-md-6 col-xl-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex gap-4 border-bottom pb-5 mb-5">
                                            <div
                                                class="h-50px w-50px bg-primary text-white d-flex align-items-center justify-content-center rounded fs-3">
                                                <i class="bi bi-fuel-pump"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-2" id="tanks-count">0</h5>
                                                <p class="text-muted mb-0 fs-12">Tanks</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dispensers -->
                            <div class="col-md-6 col-xl-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex gap-4 border-bottom pb-5 mb-5">
                                            <div
                                                class="h-50px w-50px bg-success text-white d-flex align-items-center justify-content-center rounded fs-3">
                                                <i class="bi bi-fuel-pump"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-2" id="dispensers-count">0</h5>
                                                <p class="text-muted mb-0 fs-12">Dispensers</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nozzles -->
                            <div class="col-md-6 col-xl-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex gap-4 border-bottom pb-5 mb-5">
                                            <div
                                                class="h-50px w-50px bg-info text-white d-flex align-items-center justify-content-center rounded fs-3">
                                                <i class="bi bi-droplet"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-2" id="nozzles-count">0</h5>
                                                <p class="text-muted mb-0 fs-12">Nozzles</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Products -->
                            <div class="col-md-6 col-xl-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex gap-4 border-bottom pb-5 mb-5">
                                            <div
                                                class="h-50px w-50px bg-secondary text-white d-flex align-items-center justify-content-center rounded fs-3">
                                                <i class="bi bi-box-seam"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-2" id="products-count">0</h5>
                                                <p class="text-muted mb-0 fs-12">Products</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4 py-5">
                            <!-- Order Information Card - Last 2 Orders -->
                            <div class="col-md-6 col-xl-4">
                                <div class="card mb-0 border card-hover">
                                    <div class="card-body">
                                        <h6 class="mb-4 text-uppercase"><i class="bi bi-ticket-detailed me-2"></i>Recent
                                            Orders</h6>
                                        <div id="recentOrders">
                                            <div class="text-center">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-2 text-muted fs-12">Loading orders...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Location Card - Station Information -->
                            <div class="col-md-6 col-xl-4">
                                <div class="card mb-0 card-h-100 border card-hover">
                                    <div class="card-body">
                                        <h6 class="mb-4 text-uppercase"><i class="bi bi-geo-alt me-2"></i>Station Location
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><span class="fs-12 text-muted">Station Name:</span>
                                                <p class="fw-medium" id="stationName">Loading...</p>
                                            </li>
                                            <li><span class="fs-12 text-muted">Location:</span>
                                                <p class="fw-medium mb-0" id="stationLocation">Loading...</p>
                                            </li>
                                            <li><span class="fs-12 text-muted">City:</span>
                                                <p class="fw-medium mb-0" id="stationCity">Loading...</p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Information Card - Station Owner -->
                            <div class="col-md-6 col-xl-4">
                                <div class="card mb-0 border card-hover">
                                    <div class="card-body">
                                        <h6 class="mb-4 text-uppercase"><i class="bi bi-info-square me-2"></i>Station
                                            Information</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><span class="fs-12 text-muted">Owner Name:</span>
                                                <p class="fw-medium" id="fullname">Loading...</p>
                                            </li>
                                            <li><span class="fs-12 text-muted">Email:</span>
                                                <p class="fw-medium" id="email">Loading...</p>
                                            </li>
                                            <li><span class="fs-12 text-muted">Phone Number:</span>
                                                <p class="fw-medium mb-0" id="phone">Loading...</p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Management Section -->
                        <div class="py-5">
                            <div class="card card-h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"> Manage Tanks | Dispensers | Nozzles | Products </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs-bordered nav-justified" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" data-bs-toggle="tab" href="#products-tab" role="tab">
                                                <span><i class="bi bi-box-seam"></i></span>
                                                <span>Products</span>
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#tanks-tab" role="tab"
                                                aria-selected="true">
                                                <span><i class="bi bi-fuel-pump"></i></span>
                                                <span>Tanks</span>
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" data-bs-toggle="tab" href="#dispensers-tab" role="tab"
                                                aria-selected="false">
                                                <span><i class="bi bi-fuel-pump"></i></span>
                                                <span>Dispensers</span>
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" data-bs-toggle="tab" href="#nozzles-tab" role="tab"
                                                aria-selected="false">
                                                <span><i class="bi bi-droplet"></i></span>
                                                <span>Nozzles</span>
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" data-bs-toggle="tab" href="#shift-tab" role="tab"
                                                aria-selected="false">
                                                <span><i class="bi bi-droplet"></i></span>
                                                <span>Shift Reading</span>
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" data-bs-toggle="tab" href="#tankdip-tab" role="tab"
                                                aria-selected="false">
                                                <span><i class="bi bi-droplet"></i></span>
                                                <span>Tank Dip</span>
                                            </a>
                                        </li>
                                    </ul>

                                    <!-- Tab panes -->
                                    <div class="tab-content pt-3">

                                        <!-- Products Tab -->
                                        <div class="tab-pane fade" id="products-tab" role="tabpanel">
                                            <div class="d-flex justify-content-between mb-3">
                                                <h6 class="mt-1">Products List</h6>
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#addProductModal">
                                                    <i class="bi bi-plus-circle"></i> Add Product
                                                </button>
                                            </div>

                                            <table id="productsTable" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Sr No</th>
                                                        <th>Category</th>
                                                        <th>Product Name</th>
                                                        <!-- <th>Stock</th> -->
                                                        <th>Price</th>
                                                        <th>Effective From</th>
                                                        <th>Effective To</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Data will be populated via JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Tank tab -->
                                        <div class="tab-pane fade active show" id="tanks-tab" role="tabpanel">
                                            <div class="d-flex justify-content-between mb-3">
                                                <h6 class="mt-1">Tanks List</h6>
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#addTankModal">
                                                    <i class="bi bi-plus-circle"></i> Add Tank
                                                </button>
                                            </div>

                                            <table id="tanksTable" class="table table-bordered table-striped text-center">
                                                <thead>
                                                    <tr>
                                                        <th>Sr No</th>
                                                        <th>Tank Name</th>
                                                        <th>Capacity</th>
                                                        <th>Current Level</th>
														  <th>Product</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Data will be populated via JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Dispensers Tab -->
                                        <div class="tab-pane fade" id="dispensers-tab" role="tabpanel">
                                            <div class="d-flex justify-content-between mb-3">
                                                <h6 class="mt-1">Dispensers List</h6>
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#addDispenserModal">
                                                    <i class="bi bi-plus-circle"></i> Add Dispenser
                                                </button>
                                            </div>

                                            <table id="dispensersTable"
                                                class="table table-bordered table-striped text-center">
                                                <thead>
                                                    <tr>
                                                        <th>Sr No</th>
                                                        <th>Dispenser Name</th>
                                                        <th>Tank Name</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Data will be populated via JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Nozzles Tab -->
                                        <div class="tab-pane fade" id="nozzles-tab" role="tabpanel">
                                            <div class="d-flex justify-content-between mb-3">
                                                <h6 class="mt-1">Nozzles List</h6>
                                            </div>

                                            <table id="nozzlesTable" class="table table-bordered table-striped text-center">
                                                <thead>
                                                    <tr>
                                                        <th>Sr No</th>
                                                        <th>Nozzle Name</th>
                                                        <th>Dispenser Name</th>
                                                        <th>Current Reading</th>
                                                        <th>Current Tank Level</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Data will be populated via JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Shift Nozzle reading Tab -->
                                        <div class="tab-pane fade" id="shift-tab" role="tabpanel">


                                            <!-- Top Buttons -->
                                            <div class="d-flex justify-content-between mb-3">
                                                <h6 class="mt-1">Daily Nozzle Reading</h6>
                                                <div>
                                                    <!-- <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#changeNozzleResetModal">
                                                        <i class="bi bi-plus-circle"></i> Change Nozzle Reset
                                                    </button> -->
                                                    <button class="btn btn-sm btn-info" onclick="loadNozzleResetLogs()">
                                                        <i class="bi bi-clock-history"></i>Nozzle Reset Log
                                                    </button>
                                                </div>
                                            </div>

                                            <table id="shift_nozzle_table" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Sr No</th>
                                                        <th>Nozzle Name</th>
                                                        <th>Opening Qty</th>
                                                        <th>Closing Qty</th>
                                                        <th>Sale Qty</th>
                                                        <th>Item Name</th>
                                                        <th>Sale Rate</th>
                                                        <th>Sale Amount</th>
                                                        <th>Tank Name</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Data will be populated via JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Tank Dip Tab -->
                                        <div class="tab-pane fade" id="tankdip-tab" role="tabpanel">
                                            <!-- Data Table -->
                                            <div class="card mt-4">
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table id="tankDipTable"
                                                            class="table no-wrap no-wrap table-striped text-center">
                                                            <thead>
                                                                <tr>
                                                                    <th>S.no</th>
                                                                    <th>Tank Name</th>
                                                                    <th>Dip in mm</th>
                                                                    <th>Dip in liters</th>
                                                                    <th>Reading Date & Time</th>
                                                                    <th>created_by</th>
                                                                    <th>Actions</th> <!-- ✅ NEW ACTIONS COLUMN -->
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- Data will be populated via JavaScript -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right sidebar -->
            <div class="col-xl-3">
                <div class="card position-sticky" style="top: 100px;">
                    <div class="card-header">
                        <h6 class="card-title">Orders List</h6>
                    </div>
                    <div class="card-body">
                        <div class="pb-5 d-flex align-items-center gap-3">
                            <div class="position-relative w-100">
                                <div class="form-icon right">
                                    <input type="text" class="form-control rounded-3 border form-control-icon"
                                        placeholder="Search..." id="ordersSearch">
                                    <i class="ri-search-2-line search-icon"></i>
                                </div>
                            </div>
                            <button class="btn btn-outline-light text-muted icon-btn flex-shrink-0 rounded-pill"
                                onclick="loadOrders()">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                        <div class="mx-n5 px-5" data-simplebar style="height: 645px;" id="ordersList">
                            <!-- Orders will be loaded here dynamically -->
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading orders...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addProductForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductLabel">Assign Product to Station</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Select Station</label>
                            <select name="station_id" class="form-control" id="stationSelect" required>

                            </select>
                        </div>

                        <!-- Product dropdown -->
                        <div class="mb-3">
                            <label class="form-label">Select Product</label>
                            <select name="product_id" class="form-control" id="productSelect" required>
                                <option value="">-- Select Product --</option>
                            </select>
                        </div>
                        <!-- Price -->
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" name="price" class="form-control" required min="0" step="0.01">
                        </div>

                        <!-- Effective From -->
                        <div class="mb-3">
                            <label class="form-label">Effective From</label>
                            <input type="datetime-local" name="effective_from" class="form-control" required>
                        </div>

                        <!-- Effective To -->
                        <div class="mb-3">
                            <label class="form-label">Effective To</label>
                            <input type="datetime-local" name="effective_to" class="form-control" required>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Tank Modal -->
  <div class="modal fade" id="addTankModal" tabindex="-1" aria-labelledby="addTankLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addTankForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addTankLabel">Add Tank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- Station -->
                    <div class="mb-3">
                        <label class="form-label">Station</label>
                        <select name="station_id" class="form-select" required>
                            <option value="">Select Station</option>
                        </select>
                    </div>

                    <!-- Product -->
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <select name="product_id" id="tankproduct" class="form-select" required>
                            <option value="">Select Product</option>
                        </select>
                    </div>

                    <!-- Tank Name -->
                    <div class="mb-3">
                        <label class="form-label">Tank Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <!-- Capacity -->
                    <div class="mb-3">
                        <label class="form-label">Capacity</label>
                        <input type="number" id="capacity" name="capacity" class="form-control" required min="0" step="0.01">
                    </div>

                    <!-- Current Level -->
                    <div class="mb-3">
                        <label class="form-label">Current Level</label>
                        <input type="number" id="current_level" name="current_level" class="form-control" required min="0" step="0.01" value="0">
                        <small class="text-danger d-none" id="levelError">
                            Current level cannot be greater than capacity
                        </small>
                    </div>

                    <!-- Current Level MM -->
                    <div class="mb-3">
                        <label class="form-label">Current Level MM</label>
                        <input type="number" name="current_level_mm" class="form-control" required min="0" step="0.01" value="0">
                    </div>

                    <!-- Dry Limit -->
                    <div class="mb-3">
                        <label class="form-label">Dry Limit</label>
                        <input type="number" name="dry_limit" class="form-control" required min="0" step="0.01" value="0">
                    </div>

                    <!-- Initial Date -->
                    <div class="mb-3">
                        <label class="form-label">Initial Date & Time</label>
                        <input type="datetime-local" name="intial_date" id="tank_initial_date" class="form-control" required>
                    </div>

                    <!-- ✅ Initial Setup -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="initial_setup">
                        <label class="form-check-label">Initial Setup</label>
                    </div>

                    <!-- ✅ Buying Price (hidden) -->
                    <div class="mb-3 d-none" id="buyingPriceField">
                        <label class="form-label">Buying Price</label>
                        <input type="number" name="buying_price" class="form-control" step="0.01" min="0">
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Tank</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <!-- Add Dispenser Modal -->
    <div class="modal fade" id="addDispenserModal" tabindex="-1" aria-labelledby="addDispenserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDispenserModalLabel">Add Dispenser</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addDispenserForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Station</label>
                            <select name="station_id" class="form-select" required>
                                <option value="">Select Station</option>
                            </select>
                        </div>
                        <!-- ❌ TANK FIELD REMOVED FROM MAIN FORM -->
                        <div class="mb-3">
                            <label class="form-label">Dispenser Name</label>
                            <input type="text" class="form-control" name="name" id="dispenser_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Number of Nozzles</label>
                            <select name="nozzle_count" id="nozzle_count" class="form-select" required>
                                <option value="1" selected>1 Nozzle</option>
                                <option value="2">2 Nozzles</option>
                                <option value="3">3 Nozzles</option>
                                <option value="4">4 Nozzles</option>
                                <option value="5">5 Nozzles</option>
                                <option value="6">6 Nozzles</option>
                                <option value="7">7 Nozzles</option>
                                <option value="8">8 Nozzles</option>
                            </select>
                        </div>

                        <!-- Nozzle Fields Section -->
                        <div id="nozzleFieldsSection" class="border rounded p-3 mt-3" style="display: none;">
                            <h6 class="text-primary mb-3">Nozzle Configuration</h6>
                            <div id="nozzleFieldsContainer">
                                <!-- Nozzle fields will be dynamically added here -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Initial Date & Time</label>
                            <input type="datetime-local" class="form-control" name="intial_date" id="intial_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addDispenserForm" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Dispenser Modal -->
    <div class="modal fade" id="editDispenserModal" tabindex="-1" aria-labelledby="editDispenserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDispenserModalLabel">Edit Dispenser</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editDispenserForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="edit_id" id="edit_dispenser_id">

                        <div class="mb-3">
                            <label class="form-label">Station</label>
                            <select name="station_id" class="form-select" id="edit_station_id" required>
                                <option value="">Select Station</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tank</label>
                            <select name="tank_id" class="form-select" id="edit_tank_id" required>
                                <option value="">Select Tank</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dispenser Name</label>
                            <input type="text" class="form-control" name="name" id="edit_dispenser_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Number of Nozzles</label>
                            <input type="number" class="form-control" name="nozzle_count" id="edit_nozzle_count" min="1"
                                required readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" id="edit_status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="editDispenserForm" class="btn btn-primary">Update</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Add Nozzle Modal -->
    <div class="modal fade" id="addNozzleModal" tabindex="-1" aria-labelledby="addNozzleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNozzleModalLabel">Add Nozzle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addNozzleForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Dispenser</label>
                            <select name="dispenser_id" class="form-select" required>
                                <option value="">Select Dispenser</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nozzle Name</label>
                            <input type="text" class="form-control" name="nozzlename" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <select name="nozzleproduct" id="nozzleproduct" class="form-select" required>
                                <option value="">Select Product</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tank</label>
                            <select name="nozzletank" id="nozzletank" class="form-select" required>
                                <option value="">Select Product</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Reading</label>
                            <input type="number" class="form-control" name="current_reading" min="0" step="0.01" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="nozzlestatus" class="form-select" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>

                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addNozzleForm" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Nozzle Reset Log Modal -->
    <div class="modal fade" id="nozzleResetLogModal" tabindex="-1" aria-labelledby="nozzleResetLogLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nozzleResetLogLabel">Nozzle Reset Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table no-wrap" id="nozzleResetTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Site</th>
                                <th>Dispenser</th>

                                <th>Nozzle</th>
                                <th>Old Reading</th>
                                <th>New Reading</th>
                                <th>Reason</th>
                                <th>Date</th>
                                <th>Changed By</th>

                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    </main>

@endsection

@section('js')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="assets/js/table/datatable.init.js"></script>
    <script type="module" src="assets/js/app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        let tanks = [],
            dispensers = [],
            nozzles = [],
            products = [],
            allProducts = [],
            stations = [];
        let sntable;
		var local=0;
        let oilPurchases = [];


        const AUTH_USER_ID = "{{ Auth::id() }}";
        const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";



        // ✅ Load stations
        function loadStations() {
            let stationId = `{{ request()->segment(2) }}`;

            if (!stationId || stationId === 'undefined') {
                console.error('Invalid station ID');
                showToast('Invalid station ID', 'error');
                return;
            }

            console.log('Loading station with ID:', stationId);

            $.get(`/api/stationss/${stationId}`, function (res) {
                console.log("Raw station response:", res);

                if (!res) {
                    console.error('Invalid station response:', res);
                    stations = [];
                    showToast('Station not found', 'warning');
                    return;
                }

                stations = Array.isArray(res) ? res : [res];
				 local=stations[0]['local'];
                console.log('Processed stations array:', stations, local);

                if (stations.length > 0) {
                    populateDropdown('#addTankForm select[name="station_id"]', stations, 'id', 'name');
                    populateDropdown('#addDispenserForm select[name="station_id"]', stations, 'id', 'name');
                    populateDropdown('#stationSelect', stations, 'id', 'name');
                    populateDropdown('#addProductModal select[name="station_id"]', stations, 'id', 'name');
                    populateDropdown('#addTankModal select[name="station_id"]', stations, 'id', 'name');
                    populateDropdown('#addDispenserModal select[name="station_id"]', stations, 'id', 'name');

                    // ✅ Auto select station id
                    autoSelectStation(stationId);
                } else {
                    showToast('No stations available', 'warning');
                }
            }).fail((xhr, status, error) => {
                console.error('Error loading station:', error);
                showToast('Error loading station: ' + error, 'error');
            });
        }

        // ✅ FIXED: Auto select station with safe initialization
        function autoSelectStation(stationId) {
            if (!stationId || !stations.length) return;

            console.log('Auto selecting station:', stationId);

            $('select[name="station_id"]').each(function () {
                let element = this;
                let station = stations.find(s => s.id == stationId);

                if (!station) return;

                // Use direct value setting for simplicity
                $(element).val(stationId);

                // If Choices.js is initialized, update it too
                if (element.choicesInstance && typeof element.choicesInstance.setChoiceByValue === 'function') {
                    try {
                        element.choicesInstance.setChoiceByValue(stationId.toString());
                    } catch (e) {
                        console.warn('Choices.js not properly initialized, using fallback');
                    }
                }
            });
        }


        // ✅ Count update
        function updateCounts() {
            $('#tanks-count').text(tanks.length);
            $('#dispensers-count').text(dispensers.length);
            $('#nozzles-count').text(nozzles.length);
            $('#products-count').text(products.length);
        }

        // ================== Tanks ==================
        // Set default initial date for tank modal
        function setDefaultTankInitialDate() {
            const now = new Date();
            const localDateTime = now.toISOString().slice(0, 16);
            $('#tank_initial_date').val(localDateTime);
        }

        function loadTanks() {
            let stationId = `{{ request()->segment(2) }}`;
            if (!stationId) return;

            $.get(`/api/stationwise/${stationId}`, function (res) {
                tanks = Array.isArray(res) ? res : [];

                // ✅ Tank dropdowns populate karo
                populateDropdown('#addDispenserForm select[name="tank_id"]', tanks, 'id', 'name');
                populateDropdown('#nozzletank', tanks, 'id', 'name');

                populateTanksTable(tanks);
                updateCounts();
            }).fail(() => showToast('Error loading tanks', 'error'));
        }

        function populateTanksTable(tanks) {
            const tbody = $('#tanksTable tbody').empty();
            tanks.forEach((t, i) => {
                tbody.append(`
                            <tr>
                                <td>${i + 1}</td>
                                <td>${t.name}</td>
                                <td>${t.capacity}</td>
                                <td>${t.current_level}</td>
                                <td>${t.product_name}</td>
								
                                <td><span class="badge bg-${getStatusBadgeClass(t.status)}">${t.status}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editTank(${t.id})">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTank(${t.id})">Delete</button>
                                </td>
                            </tr>
                        `);
            });
        }

        function editTank(id) {
            $.get(`/api/tanks/${id}`, function (res) {
                if (!res) return;
                console.log("Editing tank:", res);

                // ✅ Fill all form fields
                $('#addTankModal input[name="name"]').val(res.name);
                $('#addTankModal input[name="capacity"]').val(res.capacity);
                $('#addTankModal input[name="current_level"]').val(res.current_level);
                $('#addTankModal input[name="current_level_mm"]').val(res.current_level_mm || 0);
                $('#addTankModal input[name="dry_limit"]').val(res.dry_limit || 0);
                $('#addTankModal select[name="status"]').val(res.status);

                // ✅ Set initial date
                if (res.intial_date) {
                    const initialDate = new Date(res.intial_date);
                    const localDateTime = initialDate.toISOString().slice(0, 16);
                    $('#tank_initial_date').val(localDateTime);
                }

                // ✅ Set station dropdown
                const stationSelect = document.querySelector('#addTankModal select[name="station_id"]');
                if (stationSelect) {
                    $(stationSelect).val(res.station_id);
                    if (stationSelect.choicesInstance) {
                        stationSelect.choicesInstance.setChoiceByValue(String(res.station_id));
                    }
                }

                // ✅ Load station-specific products
                loadStationProducts().then((products) => {
                    console.log("Products loaded for edit:", products);

                    const productSelect = document.querySelector('#tankproduct');
                    if (!productSelect) {
                        console.warn("❌ Product dropdown not found in modal");
                        return;
                    }

                    // ✅ Reinitialize dropdown immediately after products load
                    const choicesArray = products.map(p => ({
                        value: String(p.product_id),
                        label: p.product || p.product_name,
                        selected: false,
                    }));

                    // Destroy old instance if exists
                    if (productSelect.choicesInstance) {
                        try {
                            productSelect.choicesInstance.destroy();
                        } catch (err) {
                            console.warn('Error destroying old Choices instance', err);
                        }
                        productSelect.choicesInstance = null;
                    }

                    // ✅ Create new instance with fresh data
                    const instance = new Choices(productSelect, {
                        searchEnabled: choicesArray.length > 1,
                        shouldSort: false,
                        removeItemButton: false,
                        placeholderValue: 'Select Product',
                    });
                    productSelect.choicesInstance = instance;

                    // ✅ Add options and select product immediately
                    instance.setChoices(choicesArray, 'value', 'label', true);

                    // ✅ Find match by ID or name and select
                    const match = products.find(p =>
                        Number(p.product_id) === Number(res.product_id) ||
                        (p.product || p.product_name)?.toLowerCase() === res.product_name?.toLowerCase()
                    );

                    if (match) {
                        instance.setChoiceByValue(String(match.product_id));
                        console.log(`✅ Product selected instantly: ${match.product}`);
                    } else {
                        console.warn("⚠️ No matching product found for:", res.product_id, res.product_name);
                    }

                }).catch(() => {
                    showToast('Error loading station products', 'error');
                });

                // ✅ Show modal
                $('#addTankModal').data('edit-id', res.id).modal('show');
            }).fail(() => {
                showToast('Error fetching tank details', 'error');
            });
        }

        // ✅ Modal reset handlers
        $('#addTankModal').on('hidden.bs.modal', function () {
            // Reset form fields
            $(this).find('input[name="name"]').val('');
            $(this).find('input[name="capacity"]').val('');
            $(this).find('input[name="current_level"]').val('');
            $(this).find('input[name="current_level_mm"]').val('0');
            $(this).find('input[name="dry_limit"]').val('0');
            $(this).find('select[name="status"]').val('active');

            // Set default initial date
            setDefaultTankInitialDate();

            // Clear edit ID
            $(this).removeData('edit-id');
        });

        $('#addTankForm').off('submit').on('submit', function (e) {
    e.preventDefault();

    let id = $('#addTankModal').data('edit-id');

    let station_id = $('#addTankModal').find('select[name="station_id"]').val();
    let product_id = $('#addTankModal').find('select[name="product_id"]').val();

    // Choices fallback
    try {
        const tankProductEl = document.querySelector('#tankproduct');
        if ((!product_id || product_id === '') && tankProductEl?.choicesInstance) {
            product_id = tankProductEl.choicesInstance.getValue(true);
        }

        const stationEl = document.querySelector('#addTankModal select[name="station_id"]');
        if ((!station_id || station_id === '') && stationEl?.choicesInstance) {
            station_id = stationEl.choicesInstance.getValue(true);
        }
    } catch (err) {
        console.warn('Choices fallback failed', err);
    }

    // ✅ BASIC VALIDATION
    if (!station_id || !product_id) {
        showToast('Please select both station and product', 'error');
        return;
    }

    // ✅ CHECK INITIAL SETUP
    let isInitial = $('#initial_setup').is(':checked');

    let data = {
        station_id: station_id,
        product_id: product_id,
        name: $('#addTankModal').find('input[name="name"]').val(),
        capacity: $('#addTankModal').find('input[name="capacity"]').val(),
        current_level: $('#addTankModal').find('input[name="current_level"]').val(),
        current_level_mm: $('#addTankModal').find('input[name="current_level_mm"]').val(),
        dry_limit: $('#addTankModal').find('input[name="dry_limit"]').val(),
        intial_date: $('#addTankModal').find('input[name="intial_date"]').val(),
        status: $('#addTankModal').find('select[name="status"]').val(),

        // ✅ NEW FIELDS
        initial: isInitial ? 1 : 0,
        buying_price: isInitial 
            ? $('#addTankModal').find('input[name="buying_price"]').val()
            : null
    };

    // ✅ EXTRA VALIDATION
    if (isInitial && (!data.buying_price || data.buying_price <= 0)) {
        showToast('Buying price is required for initial setup', 'error');
        return;
    }

    // ✅ CAPACITY CHECK
    if (parseFloat(data.current_level) > parseFloat(data.capacity)) {
        showToast('Current level cannot exceed capacity', 'error');
        return;
    }

    let url = id
        ? `{{ route('tanks.update', ':id') }}`.replace(':id', id)
        : `{{ route('tanks.store') }}`;

    let method = id ? 'PUT' : 'POST';

    $.ajax({
        url,
        method,
        contentType: 'application/json',
        data: JSON.stringify(data),

        success: function () {
            showToast(id ? 'Tank updated' : 'Tank added', 'success');
            $('#addTankModal').modal('hide');
            loadTanks();
        },

        error: function (xhr) {
            let errors = xhr.responseJSON?.errors;

            if (errors) {
                let errorMsg = Object.values(errors).flat().join(', ');
                showToast(errorMsg, 'error');
            } else {
                showToast('Error saving tank', 'error');
            }
        }
    });
});
        function deleteTank(id) {
            if (!confirm("Delete this tank?")) return;
            $.ajax({
                url: `{{ route('tanks.destroy', ['tank' => ':id']) }}`.replace(':id', id),
                method: 'DELETE',
                success: () => {
                    showToast('Tank deleted', 'success');
                    loadTanks();
                },
                error: () => showToast('Error deleting tank', 'error')
            });
        }
        // ================== Dispensers ==================
        function loadDispensers() {
            let stationId = `{{ request()->segment(2) }}`;
            if (!stationId) return;

            $.get(`/api/station_dispensers/${stationId}`, function (res) {
                dispensers = Array.isArray(res) ? res : [];
                populateDropdown('#addNozzleForm select[name="dispenser_id"]', dispensers, 'id', 'name');
                populateDispensersTable(dispensers);
                updateCounts();
            }).fail(() => showToast('Error loading dispensers', 'error'));
        }

        function populateDispensersTable(dispensers) {
            const tbody = $('#dispensersTable tbody').empty();
            dispensers.forEach((d, i) => {
                tbody.append(`
                            <tr>
                                <td>${i + 1}</td>
                                <td>${d.name}</td>
                                <td>${d.tank_name}</td>
                                <td><span class="badge bg-${getStatusBadgeClass(d.status)}">${d.status}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editDispenser(${d.id})">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteDispenser(${d.id})">Delete</button>
                                </td>
                            </tr>
                        `);
            });
        }

        // Function to generate nozzle fields based on count
        function generateNozzleFields(count) {
            const container = $('#nozzleFieldsContainer').empty();
            const section = $('#nozzleFieldsSection');

            if (count > 0) {
                section.show();

                for (let i = 1; i <= count; i++) {
                    const nozzleHtml = `
                            <div class="nozzle-field-group border-bottom pb-3 mb-3">
                                <h6 class="text-secondary">Nozzle ${i}</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Nozzle Name</label>
                                        <input type="text" class="form-control nozzle-name" name="nozzles[${i}][name]" placeholder="Enter nozzle name" required>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Product</label>
                                        <select class="form-select nozzle-product" name="nozzles[${i}][product_id]" required>
                                            <option value="">Select Product</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Current Reading</label>
                                        <input type="number" class="form-control nozzle-reading" name="nozzles[${i}][current_reading]" min="0" step="0.01" placeholder="0.00" required>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Tank</label>
                                        <select class="form-select nozzle-tank" name="nozzles[${i}][tank_id]" required>
                                            <option value="">Select Tank</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        `;
                    container.append(nozzleHtml);
                }

                // Populate product and tank dropdowns for all nozzles
                populateNozzleDropdowns();
            } else {
                section.hide();
            }
        }

        // ✅ UPDATED: Function to populate both product and tank dropdowns
        function populateNozzleDropdowns() {
            $('.nozzle-product').each(function (index, element) {
                populateDropdown(element, products, 'product_id', 'product');
            });

            $('.nozzle-tank').each(function (index, element) {
                populateDropdown(element, tanks, 'id', 'name');
            });
        }

        // Event listener for nozzle count change
        $('#nozzle_count').on('change', function () {
            const count = parseInt($(this).val()) || 0;
            generateNozzleFields(count);
        });

        // Initialize nozzle count dropdown with Choices.js
        function initializeNozzleCountDropdown() {
            const nozzleCountSelect = document.querySelector('#nozzle_count');
            if (nozzleCountSelect && !nozzleCountSelect.choicesInstance) {
                const choices = new Choices(nozzleCountSelect, {
                    searchEnabled: false,
                    shouldSort: false,
                    placeholderValue: 'Select number of nozzles'
                });
                nozzleCountSelect.choicesInstance = choices;
            }
        }

        // Set default initial date to current datetime
        function setDefaultInitialDate() {
            const now = new Date();
            // Convert to local datetime string in format YYYY-MM-DDTHH:MM
            const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
            $('#intial_date').val(localDateTime);
        }

        // Auto-generate nozzle fields when modal opens
        $('#addDispenserModal').on('shown.bs.modal', function () {
            const count = parseInt($('#nozzle_count').val()) || 0;
            if (count > 0) {
                generateNozzleFields(count);
            }
        });

        // ✅ SIMPLE FIX: Edit Dispenser function - Remove Choices.js complexity
        function editDispenser(id) {
            $.get(`/api/dispensers/${id}`, function (res) {
                if (!res) return;

                console.log("Editing dispenser:", res);

                // ✅ Fill edit form fields
                $('#edit_dispenser_id').val(res.id);
                $('#edit_dispenser_name').val(res.name);
                $('#edit_nozzle_count').val(res.number_of_nozzels || 1);
                $('#edit_status').val(res.status);

                // ✅ Use correct tank_id
                const tankId = res.tank_id || res.tankk_id;

                // ✅ SIMPLE SOLUTION: Destroy Choices.js and use normal select
                const stationSelect = document.querySelector('#edit_station_id');
                const tankSelect = document.querySelector('#edit_tank_id');

                // Destroy Choices instances if they exist
                if (stationSelect && stationSelect.choicesInstance) {
                    stationSelect.choicesInstance.destroy();
                    stationSelect.choicesInstance = null;
                }
                if (tankSelect && tankSelect.choicesInstance) {
                    tankSelect.choicesInstance.destroy();
                    tankSelect.choicesInstance = null;
                }

                // ✅ Use normal select elements (no Choices.js)
                $('#edit_station_id').html('<option value="">Select Station</option>');
                $('#edit_tank_id').html('<option value="">Select Tank</option>');

                // Populate stations
                stations.forEach(station => {
                    $('#edit_station_id').append(`<option value="${station.id}" ${station.id == res.station_id ? 'selected' : ''}>${station.name}</option>`);
                });

                // Populate tanks
                tanks.forEach(tank => {
                    $('#edit_tank_id').append(`<option value="${tank.id}" ${tank.id == tankId ? 'selected' : ''}>${tank.name}</option>`);
                });

                console.log('✅ Values set - Station:', res.station_id, 'Tank:', tankId);

                // ✅ Show EDIT modal
                $('#editDispenserModal').modal('show');

            }).fail(() => showToast('Error fetching dispenser details', 'error'));
        }

        // ✅ NEW: Initialize edit modal dropdowns when modal opens
        $('#editDispenserModal').on('show.bs.modal', function () {
            console.log('Edit Dispenser Modal opening...');


        });

        // ✅ NEW: Edit Dispenser form submission
        $('#editDispenserForm').off('submit').on('submit', function (e) {
            e.preventDefault();

            let id = $('#edit_dispenser_id').val();
            let station_id = $('#edit_station_id').val();
            let tank_id = $('#edit_tank_id').val();
            let dispenser_name = $('#edit_dispenser_name').val();
            let nozzle_count = $('#edit_nozzle_count').val();
            let status = $('#edit_status').val();

            // ✅ VALIDATION
            if (!station_id || !tank_id || !dispenser_name) {
                showToast('Please select both station, tank and enter dispenser name', 'error');
                return;
            }

            let data = {
                station_id: parseInt(station_id),
                tank_id: parseInt(tank_id),
                name: dispenser_name,
                number_of_nozzels: parseInt(nozzle_count),
                status: status
            };

            console.log('Edit dispenser data:', data);

            let url = `{{ route('dispensers.update', ['dispenser' => ':id']) }}`.replace(':id', id);
            let method = 'PUT';

            $.ajax({
                url,
                method,
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function (response) {
                    showToast('Dispenser updated successfully', 'success');
                    $('#editDispenserModal').modal('hide');
                    loadDispensers();
                    loadNozzles();
                },
                error: (xhr) => {
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        let errorMsg = Object.values(errors).flat().join(', ');
                        showToast('Error: ' + errorMsg, 'error');
                    } else {
                        showToast('Error updating dispenser', 'error');
                    }
                }
            });
        });

        // ✅ FIXED: Edit Modal reset on close
        $('#editDispenserModal').on('hidden.bs.modal', function () {
            // Reset edit form fields
            $(this).find('form')[0].reset();

            // ✅ FIXED: Reset station dropdown
            $('#edit_station_id').val('').html('<option value="">Select Station</option>');

            // ✅ FIXED: Reset tank dropdown  
            $('#edit_tank_id').val('').html('<option value="">Select Tank</option>');

            // ✅ FIXED: Reset status to active
            $('#edit_status').val('active');
        });

        // ✅ FIXED: Add Dispenser Modal reset on close - PROPERLY RESET ALL FIELDS
        $('#addDispenserModal').on('hidden.bs.modal', function () {
            // Reset form fields
            $(this).find('form')[0].reset();

            // ✅ FIXED: Reset station dropdown to default
            resetChoiceValue('#addDispenserModal select[name="station_id"]');

            // ✅ FIXED: Reset nozzle count dropdown to default value "1"
            const nozzleCountSelect = document.querySelector('#nozzle_count');
            if (nozzleCountSelect && nozzleCountSelect.choicesInstance) {
                nozzleCountSelect.choicesInstance.setChoiceByValue('1');
            } else {
                $('#nozzle_count').val('1');
            }

            // ✅ FIXED: Reset status dropdown to "active"
            // resetChoiceValue('#addDispenserModal select[name="status"]');

            // ✅ FIXED: Reset nozzle fields
            $('#nozzleFieldsSection').hide();
            $('#nozzleFieldsContainer').empty();

            // ✅ FIXED: Set default initial date to current datetime
            setDefaultInitialDate();

            // ✅ FIXED: Remove any edit ID
            $(this).removeData('edit-id');
        });

        // Helper function to reset Choices.js dropdowns
        function resetChoiceValue(selector) {
            const element = document.querySelector(selector);
            if (element && element.choicesInstance) {
                element.choicesInstance.removeActiveItems();
                element.choicesInstance.setChoiceByValue('');
            } else {
                $(selector).val('');
            }
        }

        // Add Dispenser form submission - ONLY UPDATED NOZZLES PART
        $('#addDispenserForm').off('submit').on('submit', function (e) {
            e.preventDefault();
            let id = $('#addDispenserModal').data('edit-id');

            let station_id = getChoiceValue('#addDispenserModal select[name="station_id"]');
            let nozzle_count = parseInt($('#nozzle_count').val()) || 0;
            let dispenser_name = $('#addDispenserModal input[name="name"]').val();

            // ✅ VALIDATION - tank_id removed from main form
            if (!station_id || !dispenser_name) {
                showToast('Please select station and enter dispenser name', 'error');
                return;
            }

            // Collect nozzle data
            let nozzles = [];
            let uniqueTankIds = []; // Track unique tank IDs for tank_dispenser table

            if (nozzle_count > 0) {
                for (let i = 1; i <= nozzle_count; i++) {
                    let nozzleName = $(`input[name="nozzles[${i}][name]"]`).val();
                    let productId = getChoiceValue(`select[name="nozzles[${i}][product_id]"]`);
                    let tankId = getChoiceValue(`select[name="nozzles[${i}][tank_id]"]`);
                    let currentReading = $(`input[name="nozzles[${i}][current_reading]"]`).val(); // ✅ ADDED

                    if (!nozzleName || !productId || !tankId || !currentReading) {
                        showToast(`Please fill all fields for Nozzle ${i}`, 'error');
                        return;
                    }

                    nozzles.push({
                        name: nozzleName,
                        product_id: parseInt(productId),
                        tank_id: parseInt(tankId), // ✅ nozzle ka alag tank
                        status: 1,
                        intial_date: $('[name="intial_date"]').val(),
                        intial_meter_reading: parseFloat(currentReading) // ✅ ADDED THIS FIELD
                    });

                    // Track unique tank IDs for tank_dispenser table
                    if (!uniqueTankIds.includes(parseInt(tankId))) {
                        uniqueTankIds.push(parseInt(tankId));
                    }
                }
            }

            // ✅ Use first tank ID for main form (backend compatibility)
            let mainTankId = uniqueTankIds.length > 0 ? uniqueTankIds[0] : null;

            let data = {
                station_id: parseInt(station_id),
                tank_id: mainTankId, // ✅ First tank ID for backend compatibility
                name: dispenser_name,
                number_of_nozzels: nozzle_count,
                intial_date: $('[name="intial_date"]').val(),
                status: getChoiceValue('#addDispenserModal select[name="status"]') || 'active',
                nozzles: nozzles
            };

            console.log('Dispenser data:', data);

            let url = id ?
                `{{ route('dispensers.update', ['dispenser' => ':id']) }}`.replace(':id', id) :
                `{{ route('dispensers.store') }}`;
            let method = id ? 'PUT' : 'POST';

            $.ajax({
                url,
                method,
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify(data),
                success: function (response) {
                    // ✅ FIXED: Check if response contains success message
                    let successMessage = 'Dispenser ' + (id ? 'updated' : 'added') + ' successfully';

                    // If backend returns specific message, use it
                    if (response && response.message) {
                        successMessage = response.message;
                    }

                    showToast(successMessage, 'success');
                    $('#addDispenserModal').modal('hide');
                    loadDispensers();
                    loadNozzles();
                },
                error: (xhr) => {
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        let errorMsg = Object.values(errors).flat().join(', ');
                        showToast('Error: ' + errorMsg, 'error');
                    } else {
                        showToast('Error saving dispenser', 'error');
                    }
                }
            });
        });
        // Helper function to get value from Choices.js dropdowns
        function getChoiceValue(selector) {
            const element = document.querySelector(selector);
            if (element && element.choicesInstance) {
                return element.choicesInstance.getValue(true);
            }
            return $(selector).val();
        }

        function deleteDispenser(id) {
            if (!confirm("Delete this dispenser?")) return;
            $.ajax({
                url: `{{ route('dispensers.destroy', ['dispenser' => ':id']) }}`.replace(':id', id),
                method: 'DELETE',
                success: () => {
                    showToast('Dispenser deleted', 'success');
                    loadDispensers();
                },
                error: () => showToast('Error deleting dispenser', 'error')
            });
        }

        // ================== Nozzles ==================
        function loadNozzles() {
            let stationId = `{{ request()->segment(2) }}`;
            if (!stationId) return;

            $.get(`/api/station_nozzle/${stationId}`, function (res) {
                nozzles = Array.isArray(res) ? res : [];
                populateNozzlesTable(nozzles);
                populateDropdown('#nozzleSelect', nozzles, 'id', 'name');
                updateCounts();
            }).fail(() => showToast('Error loading nozzles', 'error'));
        }

        function populateNozzlesTable(nozzles) {
            const tbody = $('#nozzlesTable tbody').empty();
            nozzles.forEach((n, i) => {
                const isActive = parseInt(n.status) === 1;
                const statusClass = isActive ? 'success' : 'danger';
                const statusText = isActive ? 'Active' : 'Inactive';

                tbody.append(`
                                                                                                            <tr>
                                                                                                                <td>${i + 1}</td>
                                                                                                                <td>${n.name}</td>
                                                                                                                <td>${n.dispenser_name}</td>
                                                                                                                <td>${n.nozzle_reading}</td>
                                                                                                                <td>${n.tank_reading || 0}</td>
                                                                                                                <td>
                                                                                                                    <span class="badge bg-${statusClass}">${statusText}</span>
                                                                                                                </td>
                                                                                                                <td>
                                                                                                                    <button class="btn btn-sm btn-outline-primary" onclick="editNozzle(${n.id})">Edit</button>
                                                                                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteNozzle(${n.id})">Delete</button>
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                        `);
            });
        }

        function editNozzle(id) {
            $.get(`/api/nozzles/${id}`, function (res) {
                if (!res) return;

                console.log("Editing nozzle:", res);

                // ✅ Basic fields
                $('#addNozzleModal input[name="nozzlename"]').val(res.nozzle_name);
                $('#addNozzleModal input[name="current_reading"]').val(res.current_reading || 0);
                $('#nozzlestatus').val(res.nozzle_status);

                // ✅ Dispenser dropdown - Direct value set karo (integer ensure karo)
                let dispenserSelect = document.querySelector('#addNozzleModal select[name="dispenser_id"]');
                if (dispenserSelect && dispenserSelect.choicesInstance) {
                    dispenserSelect.choicesInstance.clearChoices();
                    dispenserSelect.choicesInstance.setChoices([{
                        value: res.dispenser_id, // API se direct dispenser_id mil raha hai
                        label: res.dispenser_name,
                        selected: true
                    }], 'value', 'label', true);
                } else {
                    // Simple method se integer value set karo
                    $('#addNozzleModal select[name="dispenser_id"]').val(res.dispenser_id);
                }

                // ✅ Tank dropdown - Direct value set karo (integer ensure karo)
                let tankSelect = document.querySelector('#addNozzleModal select[name="nozzletank"]');
                if (tankSelect && tankSelect.choicesInstance) {
                    tankSelect.choicesInstance.clearChoices();
                    tankSelect.choicesInstance.setChoices([{
                        value: res.tank_id, // API se direct tank_id mil raha hai
                        label: res.tank_name,
                        selected: true
                    }], 'value', 'label', true);
                } else {
                    // Simple method se integer value set karo
                    $('#addNozzleModal select[name="nozzletank"]').val(res.tank_id);
                }

                // ✅ Product dropdown - Tank ke through product find karo
                let productSelect = document.querySelector('#addNozzleModal select[name="nozzleproduct"]');
                if (productSelect && productSelect.choicesInstance) {
                    // Tank ke product_id find karo
                    let tank = tanks.find(t => t.id == res.tank_id);
                    if (tank && tank.product_id) {
                        productSelect.choicesInstance.clearChoices();
                        productSelect.choicesInstance.setChoices([{
                            value: tank.product_id,
                            label: tank.product_name || `Product ${tank.product_id}`,
                            selected: true
                        }], 'value', 'label', true);
                    }
                } else {
                    // Simple method
                    let tank = tanks.find(t => t.id == res.tank_id);
                    if (tank && tank.product_id) {
                        $('#addNozzleModal select[name="nozzleproduct"]').val(tank.product_id);
                    }
                }

                // ✅ Save edit ID
                $('#addNozzleModal').data('edit-id', res.nozzle_id).modal('show');
            }).fail(() => showToast('Error fetching nozzle details', 'error'));
        }

        // ✅ Reset nozzle modal on close
        $('#addNozzleModal').on('hidden.bs.modal', function () {
            // Reset form fields
            $(this).find('input[name="nozzlename"]').val('');
            $(this).find('input[name="current_reading"]').val('');
            $(this).find('select[name="status"]').val('1');
            $(this).find('select[name="dispenser_id"]').val('');
            $(this).find('select[name="nozzleproduct"]').val('');
            $(this).find('select[name="nozzletank"]').val('');

            $(this).removeData('edit-id');
        });

        $('#addNozzleForm').off('submit').on('submit', function (e) {
            e.preventDefault();
            let id = $('#addNozzleModal').data('edit-id');

            let dispenser_id = $('[name="dispenser_id"]').val();
            let product_id = $('[name="nozzleproduct"]').val();
            let tank_id = $('[name="nozzletank"]').val();

            // ✅ VALIDATION
            if (!dispenser_id || !product_id || !tank_id) {
                showToast('Please select dispenser, product and tank', 'error');
                return;
            }

            let data = {
                dispenser_id: dispenser_id,
                name: $('[name="nozzlename"]').val(),
                product_id: product_id,
                current_reading: $('[name="current_reading"]').val(),
                status: $('#nozzlestatus').val(),
                tank_id: tank_id
            };

            console.log("Nozzle form data:", data);

            let url = id ?
                `{{ route('nozzles.update', ['nozzle' => ':id']) }}`.replace(':id', id) :
                `{{ route('nozzles.store') }}`;
            let method = id ? 'PUT' : 'POST';

            $.ajax({
                url,
                method,
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function () {
                    showToast(id ? 'Nozzle updated' : 'Nozzle added', 'success');
                    $('#addNozzleModal').modal('hide');
                    loadNozzles();
                },
                error: (xhr) => {
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        let errorMsg = Object.values(errors).flat().join(', ');
                        showToast('Error: ' + errorMsg, 'error');
                    } else {
                        showToast('Error saving nozzle', 'error');
                    }
                }
            });
        });

        function deleteNozzle(id) {
            if (!confirm("Delete this nozzle?")) return;
            $.ajax({
                url: `{{ route('nozzles.destroy', ['nozzle' => ':id']) }}`.replace(':id', id),

                method: 'DELETE',
                success: () => {
                    showToast('Nozzle deleted', 'success');
                    loadNozzles();
                },
                error: () => showToast('Error deleting nozzle', 'error')
            });
        }

        // ================== Products ==================

        function loadAllProducts() {
            return new Promise((resolve, reject) => {
                $.get(`/api/products/fuel`, function (res) {
                    allProducts = Array.isArray(res) ? res : [];
                    console.log("All products loaded:", allProducts);

                    // Use all products for "Add Product" modal
                    populateDropdown('#productSelect', allProducts, 'id', 'name');
                    resolve(allProducts);
                }).fail(() => {
                    showToast('Error loading all products', 'error');
                    reject();
                });
            });
        }

        function loadStationProducts() {
            return new Promise((resolve, reject) => {
                let stationId = `{{ request()->segment(2) }}`;
                if (!stationId) {
                    console.error('No station ID found');
                    reject('No station ID');
                    return;
                }

                console.log('Loading products for station:', stationId);

                $.get(`/api/stations/${stationId}/products-with-prices`)
                    .done(function (res) {
                        products = Array.isArray(res) ? res : [];
                        console.log("Station products loaded successfully:", products);

                        // Use station-specific products for tanks and nozzles
                        populateDropdown('#tankproduct', products, 'product_id', 'product');
                        populateDropdown('#nozzleproduct', products, 'product_id', 'product');

                        populateProductsTable(products);
                        updateCounts();
                        resolve(products);
                    })
                    .fail(function (xhr, status, error) {
                        console.error('Error loading station products:', {
                            status: xhr.status,
                            error: error,
                            response: xhr.responseText
                        });

                        // If 404, try alternative endpoint or show helpful message
                        if (xhr.status === 404) {
                            showToast('Products endpoint not found. Please check API configuration.', 'error');
                        } else {
                            showToast('Error loading station products: ' + error, 'error');
                        }
                        reject(error);
                    });
            });
        }


        function loadAllProductData() {
            loadAllProducts();      // For "Add Product" modal
            loadStationProducts();  // For tanks, nozzles, dispensers
        }


        function populateProductsTable(products) {
            const tbody = $('#productsTable tbody').empty();
            products.forEach((p, i) => {
                tbody.append(`
                            <tr>
                                <td>${i + 1}</td>
                                <td>${p.category || '-'}</td>
                                <td>${p.product || '-'}</td>
                                <td>${p.price || '-'}</td>
                                <td>${p.effective_from || '-'}</td>
                                <td>${p.effective_to || '-'}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editProduct(${p.station_product_id})">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(${p.id})">Delete</button>
                                </td>
                            </tr>
                        `);
            });
        }

        function editProduct(id) {
            $.get(`/api/station-product/${id}`, function (res) {
                if (!res) return;

                console.log("Editing product:", res);

                // ✅ Station dropdown (Choices.js)
                let stationSelect = document.querySelector('#addProductModal select[name="station_id"]');
                if (stationSelect && stationSelect.choicesInstance) {
                    // clear old & set new
                    stationSelect.choicesInstance.clearChoices();
                    stationSelect.choicesInstance.setChoices([{
                        value: res.station_id,
                        label: res.station_name,
                        selected: true
                    }], 'value', 'label', true);
                }

                // ✅ Product dropdown (Choices.js)
                let productSelect = document.querySelector('#addProductModal select[name="product_id"]');
                if (productSelect && productSelect.choicesInstance) {
                    productSelect.choicesInstance.clearChoices();
                    productSelect.choicesInstance.setChoices([{
                        value: res.product_id,
                        label: res.product_name,
                        selected: true
                    }], 'value', 'label', true);
                }

                // ✅ Stock

                // ✅ Price
                $('#addProductModal input[name="price"]').val(res.price);

                // ✅ Effective From
                if (res.effective_from) {
                    let dt = new Date(res.effective_from);
                    let formatted = dt.toISOString().slice(0, 16);
                    $('#addProductModal input[name="effective_from"]').val(formatted);
                }

                // ✅ Effective To
                if (res.effective_to) {
                    let dt = new Date(res.effective_to);
                    let formatted = dt.toISOString().slice(0, 16);
                    $('#addProductModal input[name="effective_to"]').val(formatted);
                }

                // ✅ Save edit ID
                $('#addProductModal').data('edit-id', res.station_product_id).modal('show');
            });
        }

        // ✅ Reset modal when closed
        $('#addProductModal').on('hidden.bs.modal', function () {
            // Reset form
            $(this).find('form')[0].reset();



            // Re-select station after reset
            let stationId = `{{ request()->segment(2) }}`;
            setTimeout(() => {
                autoSelectStation(stationId);
            }, 200);

            $(this).removeData('edit-id');
        });

        $('#addProductForm').off('submit').on('submit', function (e) {
            e.preventDefault();

            let station_id = $('[name="station_id"]').val();
            let product_id = $('[name="product_id"]').val();

            // ✅ VALIDATION
            if (!station_id || !product_id) {
                showToast('Please select both station and product', 'error');
                return;
            }

            let id = $('#addProductModal').data('edit-id');
            let data = $(this).serializeArray().reduce((a, c) => {
                a[c.name] = c.value;
                return a;
            }, {});

            console.log("Add Product form data:", data);

            let url, method;

            if (id) {
                url = `/api/station-product/${id}`;
                method = 'PUT';
            } else {
                url = `/api/stations/assign-product-with-price`;
                method = 'POST';
            }

            $.ajax({
                url: url,
                type: method,
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function () {
                    showToast(id ? 'Product updated' : 'Product added', 'success');
                    $('#addProductModal').modal('hide');
                    loadStationProducts(); // Reload station products after adding
                },
                error: (xhr) => {
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        let errorMsg = Object.values(errors).flat().join(', ');
                        showToast('Error: ' + errorMsg, 'error');
                    } else {
                        showToast('Error saving product', 'error');
                    }
                }
            });
        });

        function deleteProduct(id) {
            if (!confirm("Delete this product?")) return;
            $.ajax({
                url: `{{ route('products.destroy', ['id' => ':id']) }}`.replace(':id', id),
                method: 'DELETE',
                success: () => {
                    showToast('Product deleted', 'success');
                    loadProducts();
                },
                error: () => showToast('Error deleting product', 'error')
            });
        }

        //////////////////////////////////////// Shift Reading /////////////////////////

        function loadShiftNozzleReadings() {
            let stationId = `{{ request()->segment(2) }}`;
            let url = `{{ route('shift-nozzle-readings.station', ['stationId' => ':id']) }}`.replace(':id', stationId);

            $.get(url, function (res) {
                // clear old rows
                sntable.clear();

                $.each(res, function (index, row) {
                    sntable.row.add([
                        index + 1,
                        row.nozzle_name,
                        parseFloat(row.opening_reading).toFixed(2),
                        parseFloat(row.closing_reading).toFixed(2),
                        parseFloat(row.total_dispensed).toFixed(2),
                        row.product_name,
                        parseFloat(row.rate).toFixed(2),
                        parseFloat(row.total_amount).toFixed(2),
                        row.tank_name,
                    ]);
                });

                // redraw table
                sntable.draw();
            }).fail(() => {
                alert("❌ Failed to fetch Shift Nozzle Readings");
            });
        }

        // Function to load last closing reading when nozzle is selected
        function loadLastClosingReading() {
            const nozzleId = $('#nozzleSelect').val();

            if (!nozzleId) {
                $('input[name="opening_reading"]').val('');
                return;
            }

            // API call to get last reading for this nozzle
            $.get(`/api/shift-nozzle-readings/last-reading/${nozzleId}`, function (response) {
                if (response.success && response.data) {
                    // ✅ CORRECT: Use last_reading instead of closing_reading
                    $('input[name="opening_reading"]').val(response.data.last_reading || 0);
                } else {
                    $('input[name="opening_reading"]').val('0');
                    showToast('No previous reading found. Starting from 0.', 'info');
                }
            }).fail(function () {
                $('input[name="opening_reading"]').val('0');
                showToast('Error loading previous reading. Starting from 0.', 'warning');
            });
        }

        $('#addReadingForm').on('submit', function (e) {
            e.preventDefault();

            // Get form values
            const openingReading = parseFloat($('input[name="opening_reading"]').val()) || 0;
            const closingReading = parseFloat($('input[name="closing_reading"]').val()) || 0;

            // Validate opening reading cannot be greater than closing reading
            if (closingReading > 0 && openingReading > closingReading) {
                showToast('Opening reading cannot be greater than closing reading', 'error');
                $('input[name="closing_reading"]').addClass('is-invalid');
                return;
            }

            // Validate closing reading cannot be less than opening reading
            if (closingReading > 0 && closingReading < openingReading) {
                showToast('Closing reading cannot be less than opening reading', 'error');
                $('input[name="closing_reading"]').addClass('is-invalid');
                return;
            }

            let formData = $(this).serialize(); // serialize form data

            $.ajax({
                url: `{{ route('shift-nozzle-readings_store.store') }}`, // your store route
                method: 'POST',
                data: formData,
                success: function (res) {
                    // ✅ Show success toast / alert
                    showToast('Shift reading saved successfully!', 'success');
                    // reset form + close modal
                    $('#addReadingForm')[0].reset();
                    $('#addReadingModal').modal('hide');
                    loadShiftNozzleReadings(); // reload table data
                },
                error: function (xhr) {
                    let errors = xhr.responseJSON?.errors;
                    let msg = "Something went wrong!";
                    if (errors) {
                        msg = Object.values(errors).flat().join("\n");
                    }
                    showToast(msg, 'error');
                }
            });
        });

        // ================== Nozel Change ==================

        // Save Reset
        $('#nozzleResetForm').on('submit', function (e) {
            e.preventDefault();

            let data = $(this).serialize();
            $.ajax({
                url: `/api/nozzle-totalizer-resets`,
                method: 'POST',
                data: data,
                success: function (res) {
                    showToast('Nozzle reset saved', 'success');
                    $('#nozzleResetForm')[0].reset();
                    $('#changeNozzleResetModal').modal('hide');
                    loadNozzleResetLogs(); // reload after save
                },
                error: function (xhr) {
                    showToast('Error saving reset', 'error');
                }
            });
        });

        // Load Reset Logs
        function loadNozzleResetLogs() {
            let stationId = `{{ request()->segment(2) }}`; // URL se station id le rahe hain

            $.get(`/api/nozzle-totalizer-resets/station/${stationId}`, function (res) {
                let tbody = $('#nozzleResetTable tbody').empty();

                $.each(res, function (i, row) {
                    tbody.append(`
                                                                                                                                                        <tr>
                                                                                                                                                            <td>${i + 1}</td>
                                                                                                                                                            <td>${row.station_name || '-'}</td>
                                                                                                                                                            <td>${row.dispenser_name || '-'}</td>
                                                                                                                                                            <td>${row.nozzle_name || '-'}</td>
                                                                                                                                                            <td>${row.old_reading}</td>
                                                                                                                                                            <td>${row.new_reading}</td>
                                                                                                                                                            <td>${row.reason || '-'}</td>
                                                                                                                                                            <td>${row.reset_date}</td>
                                                                                                                                                            <td>${row.username || '-'}</td>
                                                                                                                                                        </tr>
                                                                                                                                                    `);
                });

                $('#nozzleResetLogModal').modal('show');
            }).fail(() => showToast('❌ Failed to fetch logs', 'error'));
        }


        function loadResetModalNozzles() {
            let stationId = `{{ request()->segment(2) }}`;
            if (!stationId) return;

            $.get(`/api/station_nozzle/${stationId}`, function (res) {
                populateDropdown('#resetNozzleSelect', Array.isArray(res) ? res : [], 'id', 'name');
            }).fail(() => showToast('Error loading nozzles for reset modal', 'error'));
        }


        // Function to load last closing reading for reset modal
        function loadLastClosingReadingForReset() {
            const nozzleId = $('#resetNozzleSelect').val();

            if (!nozzleId) {
                $('input[name="old_reading"]').val('');
                return;
            }

            // API call to get last reading for this nozzle
            $.get(`/api/shift-nozzle-readings/last-reading/${nozzleId}`, function (response) {
                if (response.success && response.data) {
                    // ✅ CORRECT: Use last_reading instead of closing_reading
                    $('input[name="old_reading"]').val(response.data.last_reading || 0);
                    showToast('Old reading auto-filled from last record', 'info');
                } else {
                    $('input[name="old_reading"]').val('0');
                    showToast('No previous reading found. Starting from 0.', 'info');
                }
            }).fail(function () {
                $('input[name="old_reading"]').val('0');
                showToast('Error loading previous reading. Starting from 0.', 'warning');
            });
        }

        // Function to load last closing reading for shift reading form
        function loadLastClosingReadingForShift() {
            const nozzleId = $('#nozzleSelect').val();

            if (!nozzleId) {
                $('input[name="opening_reading"]').val('');
                return;
            }

            // API call to get last reading for this nozzle
            $.get(`/api/shift-nozzle-readings/last-reading/${nozzleId}`, function (response) {
                if (response.success && response.data) {
                    // ✅ CORRECT: Use last_reading instead of closing_reading
                    $('input[name="opening_reading"]').val(response.data.last_reading || 0);
                    showToast('Opening reading auto-filled from last record', 'info');
                } else {
                    $('input[name="opening_reading"]').val('0');
                    showToast('No previous reading found. Starting from 0.', 'info');
                }
            }).fail(function () {
                $('input[name="opening_reading"]').val('0');
                showToast('Error loading previous reading. Starting from 0.', 'warning');
            });
        }

        // ============================ Right Sidebar Purchase list=============================
        // Load oil purchases for the current station
        function loadOrders() {
            let stationId = `{{ request()->segment(2) }}`;
            $.get(`/api/oil-purchases/station/${stationId}`, function (res) {
                oilPurchases = res;
                populateOrdersList(res);
            }).fail(() => showToast('Error loading orders', 'error'));
        }

        // Populate orders list
        function populateOrdersList(orders) {
            const ordersList = $('#ordersList');

            if (orders.length === 0) {
                ordersList.html(`
                                                                                                                                    <div class="text-center py-4">
                                                                                                                                        <i class="bi bi-inbox display-4 text-muted"></i>
                                                                                                                                        <p class="mt-2 text-muted">No orders found</p>
                                                                                                                                    </div>
                                                                                                                                `);
                return;
            }

            let ordersHtml = '';

            orders.forEach(order => {
                // Format dates
                const orderDate = new Date(order.order_date).toLocaleDateString();
                const receivingDate = new Date(order.recieving_date).toLocaleDateString();

                // Get status badge class
                const statusClass = getOrderStatusClass(order.payment_status);
                const statusText = getOrderStatusText(order.payment_status);

                ordersHtml += `
                                                                                                                                    <div class="d-flex justify-content-between align-items-center border-bottom border-dashed py-3">
                                                                                                                                        <div class="flex-grow-1">
                                                                                                                                            <h6 class="mb-1"><span class="fw-normal me-2">Invoice:</span>${order.invoice_no || 'N/A'}</h6>
                                                                                                                                            <p class="mb-1 fs-12 text-muted">Order: ${orderDate} | Receive: ${receivingDate}</p>
                                                                                                                                            <p class="mb-0 fs-12 text-muted">Qty: ${order.recieved_qty} | Rate: ${order.rate}</p>
                                                                                                                                            <p class="mb-0 fs-12 text-muted">Supplier: ${order.supplier_name || 'N/A'}</p>
                                                                                                                                        </div>
                                                                                                                                        <div class="text-end">
                                                                                                                                            <span class="badge ${statusClass}">${statusText}</span>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                `;
            });

            ordersList.html(ordersHtml);
        }

        // Get order status badge class
        function getOrderStatusClass(status) {
            switch (status) {
                case 'paid':
                    return 'bg-success-subtle text-success';
                case 'partial':
                    return 'bg-warning-subtle text-warning';
                case 'pending':
                    return 'bg-primary-subtle text-primary';
                case 'overdue':
                    return 'bg-danger-subtle text-danger';
                default:
                    return 'bg-secondary-subtle text-secondary';
            }
        }

        // Get order status text
        function getOrderStatusText(status) {
            switch (status) {
                case 'paid':
                    return 'Paid';
                case 'partial':
                    return 'Partial';
                case 'pending':
                    return 'Pending';
                case 'overdue':
                    return 'Overdue';
                default:
                    return status;
            }
        }

        // Search functionality for orders
        $('#ordersSearch').on('input', function () {
            const searchTerm = $(this).val().toLowerCase();

            if (searchTerm === '') {
                populateOrdersList(oilPurchases);
                return;
            }

            const filteredOrders = oilPurchases.filter(order =>
                (order.invoice_no && order.invoice_no.toLowerCase().includes(searchTerm)) ||
                (order.supplier_name && order.supplier_name.toLowerCase().includes(searchTerm)) ||
                order.payment_status.toLowerCase().includes(searchTerm)
            );

            populateOrdersList(filteredOrders);
        });

        // ======================= Information Cards =======================
        // Load station information for location and owner cards
        function loadStationInfo() {
            let stationId = `{{ request()->segment(2) }}`;

            // Get all stations to find current station
            $.get(`/api/stations`, function (stations) {
                const currentStation = stations.find(station => station.id == stationId);

                if (currentStation) {
                    // Update Location Card
                    $('#stationName').text(currentStation.name);
                    $('#stationLocation').text(currentStation.location);
                    $('#stationCity').text(currentStation.city);

                    // Update Station Information Card
                    $('#fullname').text(currentStation.full_name);
                    $('#email').text(currentStation.email);
                    $('#phone').text(currentStation.phone);
                } else {
                    showToast('Station not found', 'error');
                }
            }).fail(() => showToast('Error loading station information', 'error'));
        }

        // Load recent orders (last 2)
        function loadRecentOrders() {
            let stationId = `{{ request()->segment(2) }}`;

            $.get(`/api/oil-purchases/station/${stationId}`, function (orders) {
                const recentOrders = orders.slice(0, 2); // Get last 2 orders

                if (recentOrders.length === 0) {
                    $('#recentOrders').html(`
                                                                                                                                    <div class="text-center text-muted">
                                                                                                                                        <i class="bi bi-inbox display-6"></i>
                                                                                                                                        <p class="mt-2 fs-12">No recent orders</p>
                                                                                                                                    </div>
                                                                                                                                `);
                    return;
                }

                let ordersHtml = '';

                recentOrders.forEach(order => {
                    const orderDate = new Date(order.order_date).toLocaleDateString();
                    const receivingDate = new Date(order.recieving_date).toLocaleDateString();
                    const statusClass = getOrderStatusClass(order.payment_status);
                    const statusText = getOrderStatusText(order.payment_status);

                    ordersHtml += `
                                                                                                                                    <div class="order-item">
                                                                                                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                                                                                                            <div>
                                                                                                                                                <strong class="fs-14">${order.invoice_no || 'N/A'}</strong>
                                                                                                                                                <p class="mb-1 fs-12 text-muted">Order: ${orderDate}</p>
                                                                                                                                            </div>
                                                                                                                                            <span class="badge ${statusClass} status-badge">${statusText}</span>
                                                                                                                                        </div>
                                                                                                                                        <div class="fs-12">
                                                                                                                                            <span class="text-muted">Qty:</span> ${order.recieved_qty} | 
                                                                                                                                            <span class="text-muted">Rate:</span> ${order.rate}
                                                                                                                                        </div>
                                                                                                                                        <div class="fs-12 text-muted">
                                                                                                                                            Receive: ${receivingDate}
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                `;
                });

                $('#recentOrders').html(ordersHtml);
            }).fail(() => {
                $('#recentOrders').html(`
                                                                                                                                <div class="text-center text-danger">
                                                                                                                                    <i class="bi bi-exclamation-triangle"></i>
                                                                                                                                    <p class="mt-2 fs-12">Failed to load orders</p>
                                                                                                                                </div>
                                                                                                                            `);
            });
        }

        // Helper functions for order status
        function getOrderStatusClass(status) {
            switch (status?.toLowerCase()) {
                case 'paid':
                    return 'bg-success-subtle text-success';
                case 'partial':
                    return 'bg-warning-subtle text-warning';
                case 'pending':
                    return 'bg-primary-subtle text-primary';
                case 'overdue':
                    return 'bg-danger-subtle text-danger';
                default:
                    return 'bg-secondary-subtle text-secondary';
            }
        }

        function getOrderStatusText(status) {
            switch (status?.toLowerCase()) {
                case 'paid':
                    return 'Paid';
                case 'partial':
                    return 'Partial';
                case 'pending':
                    return 'Pending';
                case 'overdue':
                    return 'Overdue';
                default:
                    return status || 'Unknown';
            }
        }

        // ================== Tank Dip Reading ==================

        let tanksData = [];
        let tankDipReadings = [];

        // Function to fetch tanks data for dip reading
        function loadTanksForDipReading() {
            let stationId = `{{ request()->segment(2) }}`;
            if (!stationId) {
                console.error('Station ID not found');
                return;
            }

            console.log('Loading tanks for station:', stationId);

            $.get(`/api/stationwise/${stationId}`, function (res) {
                console.log('Tanks data received:', res);
                tanksData = Array.isArray(res) ? res : [];
            }).fail((xhr, status, error) => {
                console.error('Error loading tanks:', error);
                showToast('Error loading tanks for dip reading', 'error');
            });
        }


        // Function to load tank dip table data
        function loadTankDipTable() {
            let stationId = `{{ request()->segment(2) }}`;
            if (!stationId) return;

            $.get(`/api/tank-dip-readings/station/${stationId}`, function (response) {
                console.log('Tank dip readings received:', response);
                tankDipReadings = Array.isArray(response) ? response : [];
                populateTankDipTable(tankDipReadings);
            }).fail((xhr, status, error) => {
                console.error('Error loading tank dip readings:', error);
                // Don't show error for 404 - it's expected if no readings exist yet
                if (xhr.status !== 404) {
                    showToast('Error loading tank dip readings', 'error');
                } else {
                    // If 404, just show empty table
                    populateTankDipTable([]);
                }
            });
        }

        // Function to populate tank dip table - UPDATED WITH REPORT BUTTON
        function populateTankDipTable(readings) {
            const tbody = $('#tankDipTable tbody').empty();

            if (readings.length === 0) {
                tbody.append(`
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="bi bi-inbox display-6"></i>
                                                    <p class="mt-2">No tank dip readings found</p>
                                                </td>
                                            </tr>
                                        `);
                return;
            }

            readings.forEach((reading, index) => {
                const readingDate = new Date(reading.Reading_date_Time).toLocaleString();

                tbody.append(`
                            <tr>
                                <td>${index + 1}</td>
                                <td>${reading.tank_name || `Tank ${reading.tank_id}`}</td>
                                <td>${reading.dip_mm ?? '-'}</td>
                                <td>${reading.dip_in_liters ? parseFloat(reading.dip_in_liters).toFixed(2) : '-'}</td>
                                <td>${reading.created_at}</td>
                                <td>${reading.created_by_name ?? '-'}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTankDipReading(${reading.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
            });
        }


        // Function to delete tank dip reading
        function deleteTankDipReading(readingId) {
            if (!confirm('Are you sure you want to delete this reading?')) return;

            $.ajax({
                url: `/api/tank-dip-readings/${readingId}`,
                method: 'DELETE',
                success: function (response) {
                    if (response.success) {
                        showToast('Reading deleted successfully', 'success');
                        loadTankDipTable(); // Refresh table
                    } else {
                        showToast(response.message, 'error');
                    }
                },
                error: function (xhr) {
                    let errorMessage = 'Error deleting reading';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showToast(errorMessage, 'error');
                }
            });
        }

        // Initialize tank dip functionality
        function initializeTankDip() {
            console.log('Initializing Tank Dip functionality...');

            // Load data immediately
            loadTanksForDipReading();
            loadTankDipTable();
        }



        // ================== Helpers ==================
        function populateDropdown(selector, items, valueField, textField) {
            // ✅ FIX: Handle both selector strings and DOM elements
            let element;
            if (typeof selector === 'string') {
                element = document.querySelector(selector);
            } else if (selector instanceof Element) {
                element = selector;
            } else {
                console.warn('Invalid selector for populateDropdown:', selector);
                return;
            }

				console.log("element",element);
            if (!element) {
                console.warn('Dropdown element not found:', selector);
                return;
            }

            if (!items) items = [];
            if (!Array.isArray(items)) items = [items];
			console.log("items",items);
            console.log(`Populating dropdown with ${items.length} items:`, items);

            // Check if this is a station dropdown
            const isStationDropdown = element.name === 'station_id';
            const stationId = `{{ request()->segment(2) }}`;

            // Destroy existing Choices instance if exists
            if (element.choicesInstance) {
                try {
                    element.choicesInstance.destroy();
					console.log("element destroyed")
                } catch (e) {
                    console.warn('Error destroying Choices instance:', e);
                }
                element.choicesInstance = null;
            }

            // Create choices array
            const choicesArray = items.map(item => {
                if (!item) return null;

                const value = item[valueField];
                const label = item[textField];

                if (value === undefined || value === null || label === undefined) {
                    console.warn('Invalid item for dropdown:', item);
                    return null;
                }

                // Auto-select if it's a station dropdown and matches current station
                const selected = isStationDropdown && value == stationId;

                return {
                    value: value,
                    label: String(label),
                    selected: selected,
                    disabled: false,
                };
            }).filter(choice => choice !== null && choice.value !== '' && choice.value !== null);

            console.log(`Final choices:`, choicesArray);

            // Initialize Choices.js only if we have items
            if (choicesArray.length > 0) {
                try {
                    const choices = new Choices(element, {
                        searchEnabled: choicesArray.length > 1,
                        removeItemButton: choicesArray.length > 1,
                        placeholderValue: 'Select',
                        shouldSort: false,
                        allowHTML: false
                    });

                    element.choicesInstance = choices;
                    choices.setChoices(choicesArray, 'value', 'label', true);

                    // For station dropdowns, ensure selection
                    if (isStationDropdown) {
                        const selectedItem = choicesArray.find(item => item.selected);
                        if (selectedItem) {
                            setTimeout(() => {
                                try {
                                    choices.setChoiceByValue(selectedItem.value.toString());
                                    console.log(`Auto-selected station:`, selectedItem.label);
                                } catch (e) {
                                    console.warn('Error setting choice by value:', e);
                                    element.value = selectedItem.value;
                                }
                            }, 300);
                        }
                    }

                } catch (error) {
                    console.error('Error initializing Choices.js:', error);
                    // Fallback: populate dropdown manually
                    element.innerHTML = '<option value="">Select</option>' +
                        choicesArray.map(choice =>
                            `<option value="${choice.value}" ${choice.selected ? 'selected' : ''}>${choice.label}</option>`
                        ).join('');
                }
            } else {
                // No items - show placeholder
                element.innerHTML = '<option value="">No options available</option>';
            }
        }

        function loadAllDropdowns() {
            let stationId = `{{ request()->segment(2) }}`;

            // Load both types of products
            loadAllProductData();

            // Load other station-wise data
            loadTanks();
            loadDispensers();
            loadNozzles();


        }

        // Rest of your existing functions remain the same...
        function getStatusText(status) {
            if (status === 1 || status === 'active') return 'Active';
            if (status === 0 || status === 'inactive') return 'Inactive';
            return 'Unknown';
        }

        function getStatusBadgeClass(status) {
            if (status === 1 || status === 'active') return 'success';
            if (status === 0 || status === 'inactive') return 'secondary';
            return 'dark';
        }

        function showToast(message, type = 'success') {
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            if (type === 'success') toastr.success(message);
            else if (type === 'error') toastr.error(message);
            else if (type === 'warning') toastr.warning(message);
            else toastr.info(message);
        }

        // ✅ Add to your  init function - NO DELAY
        $(function () {
            sntable = new DataTable('#shift_nozzle_table', {
                dom: 'frtip',
            });

            // Load data immediately
            loadStations();
            loadAllDropdowns();
            station_employee();
            // shifts();
            loadResetModalNozzles();
            loadOrders();
            loadStationInfo();
            loadRecentOrders();
            loadShiftNozzleReadings();
            initializeTankDip(); // ✅ Initialize tank dip immediately
            setDefaultTankInitialDate();

            // Initialize nozzle count dropdown
            initializeNozzleCountDropdown();
            setDefaultInitialDate();

            $(document).on('ordersRefreshed', function () {
                loadRecentOrders();
            });
            // Shift reading form
            $('#nozzleSelect').on('change', loadLastClosingReading);

            // Reset modal form  
            $('#resetNozzleSelect').on('change', loadLastClosingReadingForReset);
        });


        function allproducts() {
            $.get(`/api/products`, function (res) {
                populateDropdown('#productSelect', Array.isArray(res) ? res : [], 'id', 'name');
            }).fail(() => showToast('Error loading products', 'error'));
        }

        function station_employee() {
            let stationId = `{{ request()->segment(2) }}`;
            if (!stationId) return;

            // ✅ Use correct route from your API
            $.get(`/api/employeebystation/${stationId}`, function (res) {
                console.log("Employees loaded:", res);
                populateDropdown('#employeeSelect', Array.isArray(res) ? res : [], 'id', 'full_name');
            }).fail(() => showToast('Error loading employees', 'error'));
        }


        function shifts() {
            let stationId = `{{ request()->segment(2) }}`;
            if (!stationId) return;

            // ✅ Use correct route from your API
            $.get(`/api/shift_show/${stationId}`, function (res) {
                console.log("Shifts loaded:", res);
                populateDropdown('#shiftSelect', Array.isArray(res) ? res : [], 'id', 'shift_type');
            }).fail(() => showToast('Error loading shifts', 'error'));
        }
// ✅ Validate Current Level <= Capacity (LIVE)
$(document).on("input", "#current_level, #capacity", function () {

    const capacity = parseFloat($("#capacity").val()) || 0;
    const currentLevel = parseFloat($("#current_level").val()) || 0;

    if (currentLevel > capacity) {
        $("#levelError").removeClass("d-none");
        $("#current_level").addClass("is-invalid");
    } else {
        $("#levelError").addClass("d-none");
        $("#current_level").removeClass("is-invalid");
    }
});


// ✅ Show / Hide Buying Price
$(document).on("change", "#initial_setup", function () {
    if ($(this).is(":checked")) {
        $("#buyingPriceField").removeClass("d-none");
    } else {
        $("#buyingPriceField").addClass("d-none");
        $("input[name='buying_price']").val("");
    }
});


// ✅ Prevent submit if invalid
$("#addTankForm").on("submit", function (e) {

    const capacity = parseFloat($("#capacity").val()) || 0;
    const currentLevel = parseFloat($("#current_level").val()) || 0;

    if (currentLevel > capacity) {
        e.preventDefault();
        $("#levelError").removeClass("d-none");
        $("#current_level").addClass("is-invalid");
        return false;
    }

});
    </script>

@endsection