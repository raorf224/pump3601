@extends('partials.layouts.master')

@section('title', 'Overview | ' . Auth::user()->full_name)

@section('title-sub', 'Logistics')
@section('pagetitle', 'Overview')
@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
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
                        <div class="col-md-6 col-xl-4">
                            <div class="card mb-0 border">
                                <div class="card-body">
                                    <h6 class="mb-4 text-uppercase"><i class="bi bi-ticket-detailed me-2"></i>Order
                                        Information</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><span class="fs-12 text-muted">Pick up Date:</span>
                                            <p class="fw-medium">14:40:15 17, Feb 2024</p>
                                        </li>
                                        <li><span class="fs-12 text-muted">Drop of Estimation:</span>
                                            <p class="fw-medium">7 days</p>
                                        </li>
                                        <li><span class="fs-12 text-muted">Insurance:</span>
                                            <p class="fw-medium mb-0">SafeHorizon Insurance</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="card mb-0 card-h-100 border">
                                <div class="card-body">
                                    <h6 class="mb-4 text-uppercase"><i class="bi bi-geo-alt me-2"></i>Location</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><span class="fs-12 text-muted">Pick Up Location:</span>
                                            <p class="fw-medium">123 Main Street, New York, NY</p>
                                        </li>
                                        <li><span class="fs-12 text-muted">Drop Off Location:</span>
                                            <p class="fw-medium mb-0">456 Elm Avenue, Brooklyn, NY</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="card mb-0 border">
                                <div class="card-body">
                                    <h6 class="mb-4 text-uppercase"><i class="bi bi-info-square me-2"></i>Cusomer
                                        Information</h6>
                                    <ul class="list-unstyled  mb-0">
                                        <li><span class="fs-12 text-muted">Full Name:</span>
                                            <p class="fw-medium" id="fullname"></p>
                                        </li>
                                        <li><span class="fs-12 text-muted">Email:</span>
                                            <p class="fw-medium" id="email"></p>
                                        </li>
                                        <li><span class="fs-12 text-muted">Phone Number:</span>
                                            <p class="fw-medium mb-0" id="phone"></p>
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
                                                    <th>Stock</th>
                                                    <th>Price</th>
                                                    <th>Effective From</th>
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
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#addNozzleModal">
                                                <i class="bi bi-plus-circle"></i> Add Nozzle
                                            </button>
                                        </div>

                                        <table id="nozzlesTable" class="table table-bordered table-striped text-center">
                                            <thead>
                                                <tr>
                                                    <th>Sr No</th>
                                                    <th>Nozzle Name</th>
                                                    <th>Dispenser Name</th>
                                                    <th>Current Reading</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
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

        <!-- Right sidebar -->
        <div class="col-xl-3">
            <div class="card position-sticky" style="top: 100px;">
                <div class="card-header">
                    <h6 class="card-title">Tracking List</h6>
                </div>
                <div class="card-body">
                    <div class="pb-5 d-flex align-items-center gap-3">
                        <div class="position-relative w-100">
                            <div class="form-icon right">
                                <input type="text" class="form-control rounded-3 border form-control-icon"
                                    placeholder="Search...">
                                <i class="ri-search-2-line search-icon"></i>
                            </div>
                        </div>
                        <button class="btn btn-outline-light text-muted icon-btn flex-shrink-0 rounded-pill"><i
                                class="bi bi-filter"></i></button>
                    </div>
                    <div class="mx-n5 px-5" data-simplebar style="height: 645px;">
                        <div class="d-flex justify-content-between align-items-center border-bottom border-dashed py-4">
                            <div>
                                <h6 class="mb-1"><span class="fw-normal me-2">ID</span>#INV1001</h6>
                                <p class="mb-0 fs-12 text-muted">179230150</p>
                            </div>
                            <div>
                                <span class="badge bg-secondary-subtle text-secondary">In Transit</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom border-dashed py-4">
                            <div>
                                <h6 class="mb-1"><span class="fw-normal me-2">ID</span>#INV1002</h6>
                                <p class="mb-0 fs-12 text-muted">179230151</p>
                            </div>
                            <div>
                                <span class="badge bg-success-subtle text-success">Delivered</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom border-dashed py-4">
                            <div>
                                <h6 class="mb-1"><span class="fw-normal me-2">ID</span>#INV1003</h6>
                                <p class="mb-0 fs-12 text-muted">179230152</p>
                            </div>
                            <div>
                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom border-dashed py-4">
                            <div>
                                <h6 class="mb-1"><span class="fw-normal me-2">ID</span>#INV1004</h6>
                                <p class="mb-0 fs-12 text-muted">179230153</p>
                            </div>
                            <div>
                                <span class="badge bg-primary-subtle text-primary">Delayed</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom border-dashed py-4">
                            <div>
                                <h6 class="mb-1"><span class="fw-normal me-2">ID</span>#INV1006</h6>
                                <p class="mb-0 fs-12 text-muted">179230155</p>
                            </div>
                            <div>
                                <span class="badge bg-danger-subtle text-danger">Canceled</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom border-dashed py-4">
                            <div>
                                <h6 class="mb-1"><span class="fw-normal me-2">ID</span>#INV1007</h6>
                                <p class="mb-0 fs-12 text-muted">179230156</p>
                            </div>
                            <div>
                                <span class="badge bg-primary-subtle text-primary">In Transit</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom border-dashed py-4">
                            <div>
                                <h6 class="mb-1"><span class="fw-normal me-2">ID</span>#INV1008</h6>
                                <p class="mb-0 fs-12 text-muted">179230155</p>
                            </div>
                            <div>
                                <span class="badge bg-success-subtle text-success">Delivered</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom border-dashed py-4">
                            <div>
                                <h6 class="mb-1"><span class="fw-normal me-2">ID</span>#INV1009</h6>
                                <p class="mb-0 fs-12 text-muted">179230156</p>
                            </div>
                            <div>
                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom border-dashed py-4">
                            <div>
                                <h6 class="mb-1"><span class="fw-normal me-2">ID</span>#INV1009</h6>
                                <p class="mb-0 fs-12 text-muted">179230157</p>
                            </div>
                            <div>
                                <span class="badge bg-primary-subtle text-primary">Delayed</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-4">
                            <div>
                                <h6 class="mb-1"><span class="fw-normal me-2">ID</span>#INV1010</h6>
                                <p class="mb-0 fs-12 text-muted">179230158</p>
                            </div>
                            <div>
                                <span class="badge bg-danger-subtle text-danger">Canceled</span>
                            </div>
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

                    <!-- Stock -->
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" required min="0" step="0.01">
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
<div class="modal fade" id="addTankModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addTankForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Tank</h5>
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
                        <input type="number" id="current_level" name="current_level" class="form-control" required min="0" step="0.01">
                        <small class="text-danger d-none" id="levelError">
                            Current level cannot be greater than capacity
                        </small>
                    </div>

                    <!-- ✅ Initial Setup Checkbox -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="initial_setup">
                        <label class="form-check-label" for="initial_setup">
                            Initial Setup
                        </label>
                    </div>

                    <!-- ✅ Buying Price (Hidden by default) -->
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
    <div class="modal-dialog">
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
                            <!-- Options will be populated via JavaScript -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tank</label>
                        <select name="tank_id" class="form-select" required>
                            <option value="">Select Tank</option>
                            <!-- Options will be populated via JavaScript -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dispenser Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Number of Nozzles</label>
                        <input type="number" class="form-control" name="nozzle_count" min="1" required>
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
                        <input type="number" class="form-control" name="current_reading" min="0" step="0.01" required>
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
</main>

@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>


<script>
const API_BASE_URL = "api";

    let tanks = [],
    dispensers = [],
    nozzles = [],
    products = [],
    stations = [];

    // ✅ Load stations
    function loadStations() {
    $.get(`{{ route('stations.show', ['id' => request()->segment(2)]) }}`, function(res) {
    stations = res;
    populateDropdown('#addTankForm select[name="station_id"]', stations, 'id', 'name');
    populateDropdown('#addDispenserForm select[name="station_id"]', stations, 'id', 'name');
    populateDropdown('#stationSelect', stations, 'id', 'name');
    }).fail(() => showToast('Error loading stations', 'error'));
    }

    // ✅ Count update
    function updateCounts() {
    $('#tanks-count').text(tanks.length);
    $('#dispensers-count').text(dispensers.length);
    $('#nozzles-count').text(nozzles.length);
    $('#products-count').text(products.length);
    }

    // ================== Tanks ==================
    function loadTanks() {
    $.get(`{{ route('tanks.stationwise', ['id' => request()->segment(2)]) }}`, function(res) {
    tanks = res;
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
    let t = tanks.find(x => x.id === id);
    if (!t) return;

    $('#addTankModal input[name="name"]').val(t.name);
    $('#addTankModal input[name="capacity"]').val(t.capacity);
    $('#addTankModal input[name="current_level"]').val(t.current_level);
    $('#addTankModal select[name="status"]').val(t.status);
    $('#addTankModal select[name="station_id"]').val(t.station_id);
    $('#addTankModal select[name="product_id"]').val(t.product_id);

    $('#addTankModal').data('edit-id', id).modal('show');
    }

    $('#addTankForm').off('submit').on('submit', function(e) {
    e.preventDefault();
    let id = $('#addTankModal').data('edit-id');

    let data = {
    station_id: $('[name="station_id"]').val(),
    product_id: $('[name="product_id"]').val(),
    name: $('[name="name"]').val(),
    capacity: $('[name="capacity"]').val(),
    current_level: $('[name="current_level"]').val(),
    status: $('[name="status"]').val()
    };
    let url = id
    ? `{{ route('tanks.update', ':id') }}`.replace(':id', id) // ✅ single record
    : `{{ route('tanks.store') }}`; // ✅ create
    // ✅ use correct route name
    let method = id ? 'PUT' : 'POST';

    $.ajax({
    url,
    method,
    contentType: 'application/json',
    data: JSON.stringify(data),
    success: function() {
    showToast(id ? 'Tank updated' : 'Tank added', 'success');
    $('#addTankModal').modal('hide');
    $('#addTankForm')[0].reset();
    $('#addTankModal').removeData('edit-id');
    loadTanks();
    },
    error: () => showToast('Error saving tank', 'error')
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
    $.get(`{{ route('station.dispensers', ['id' => request()->segment(2)]) }}`, function(res) {
    dispensers = res;
    populateDropdown('#addNozzleForm select[name="dispenser_id"]', dispensers, 'id','name');
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

    function editDispenser(id) {
    let d = dispensers.find(x => x.id === id);
    if (!d) return;

    $('#addDispenserModal input[name="name"]').val(d.name);
    $('#addDispenserModal input[name="nozzle_count"]').val(d.nozzle_count || 1);
    $('#addDispenserModal select[name="status"]').val(d.status);
    $('#addDispenserModal select[name="station_id"]').val(d.station_id);
    $('#addDispenserModal select[name="tank_id"]').val(d.tank_id);

    $('#addDispenserModal').data('edit-id', id).modal('show');
    }

    $('#addDispenserForm').off('submit').on('submit', function(e) {
    e.preventDefault();
    let id = $('#addDispenserModal').data('edit-id');
    let data = $(this).serializeArray().reduce((a, c) => { a[c.name] = c.value; return a; }, {});

    let url = id
    ? `{{ route('dispensers.update', ['dispenser' => ':id']) }}`.replace(':id', id)
    : `{{ route('dispensers.store') }}`;

    let method = id ? 'PUT' : 'POST';

    $.ajax({
    url,
    method,
    contentType: 'application/json',
    data: JSON.stringify(data),
    success: function() {
    showToast(id ? 'Dispenser updated' : 'Dispenser added', 'success');
    $('#addDispenserModal').modal('hide');
    $('#addDispenserForm')[0].reset();
    $('#addDispenserModal').removeData('edit-id');
    loadDispensers();
    },
    error: () => showToast('Error saving dispenser', 'error')
    });
    });

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
    $.get(`{{ route('station.nozzles', ['id'=>request()->segment(2)]) }}`, function(res) {
    nozzles = res;
    populateNozzlesTable(nozzles);
    updateCounts();
    }).fail(() => showToast('Error loading nozzles', 'error'));
    }

    function populateNozzlesTable(nozzles) {
    const tbody = $('#nozzlesTable tbody').empty();
    nozzles.forEach((n, i) => {
    tbody.append(`
    <tr>
        <td>${i + 1}</td>
        <td>${n.name}</td>
        <td>${n.dispenser_name}</td>
        <td>${n.tank_reading || 0}</td>
        <td>
            <span class="badge bg-${getStatusBadgeClass(n.nozzle_status==" 1"?'active':'inactive')}">
                ${getStatusText(n.nozzle_status=="1"?'active':'inactive')}
            </span>
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
    let n = nozzles.find(x => x.id === id);
    if (!n) return;

    $('#addNozzleModal input[name="name"]').val(n.name);
    $('#addNozzleModal input[name="current_reading"]').val(n.current_reading || 0);
    $('#addNozzleModal select[name="status"]').val(n.status);
    $('#addNozzleModal select[name="dispenser_id"]').val(n.dispenser_id);

    $('#addNozzleModal').data('edit-id', id).modal('show');
    }

    $('#addNozzleForm').off('submit').on('submit', function(e) {
    e.preventDefault();
    let id = $('#addNozzleModal').data('edit-id');

    let data = {
    dispenser_id: $('[name="dispenser_id"]').val(),
    name: $('[name="nozzlename"]').val(),
    product_id: $('[name="nozzleproduct"]').val(),
    current_reading: $('[name="current_reading"]').val(),
    status: $('#nozzlestatus').val(),
    tank_id: $('[name="nozzletank"]').val()
    };

    let url = id
    ? `{{ route('nozzles.update', ['nozzle' => ':id']) }}`.replace(':id', id)
    : `{{ route('nozzles.store') }}`;

    let method = id ? 'PUT' : 'POST';

    $.ajax({
    url,
    method,
    contentType: 'application/json',
    data: JSON.stringify(data),
    success: function() {
    showToast(id ? 'Nozzle updated' : 'Nozzle added', 'success');
    $('#addNozzleModal').modal('hide');
    $('#addNozzleForm')[0].reset();
    $('#addNozzleModal').removeData('edit-id');
    loadNozzles();
    },
    error: () => showToast('Error saving nozzle', 'error')
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
    function loadProducts() {
    $.get(`{{ route('stations.products-with-prices',['stationId'=>request()->segment(2)]) }}`, function(res) {
    products = res;
    populateDropdown('#tankproduct', res, 'id', 'product');
    populateDropdown('#nozzleproduct', res, 'product_id', 'product');
    populateProductsTable(products);
    updateCounts();
    }).fail(() => showToast('Error loading products', 'error'));
    }

    function populateProductsTable(products) {
    const tbody = $('#productsTable tbody').empty();
    products.forEach((p, i) => {
    tbody.append(`
    <tr>
        <td>${i + 1}</td>
        <td>${p.category || '-'}</td>
        <td>${p.product || '-'}</td>
        <td>${p.stock || '-'}</td>
        <td>${p.price || '-'}</td>
        <td>${p.effective_from || '-'}</td>
        <td>
            <button class="btn btn-sm btn-outline-primary" onclick="editProduct(${p.id})">Edit</button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(${p.id})">Delete</button>
        </td>
    </tr>
    `);
    });
    }

    function editProduct(id) {
    let p = products.find(x => x.id === id);
    if (!p) return;
    $('#addProductModal input[name="name"]').val(p.name);
    $('#addProductModal input[name="type"]').val(p.type || '');
    $('#addProductModal input[name="unit"]').val(p.unit || '');
    $('#addProductModal select[name="status"]').val(p.status);
    $('#addProductModal').data('edit-id', id).modal('show');
    }

    $('#addProductForm').off('submit').on('submit', function(e) {
    e.preventDefault();
    let id = $('#addProductModal').data('edit-id');
    let data = $(this).serializeArray().reduce((a, c) => { a[c.name] = c.value; return a; }, {});

    let url = id
    ? `{{ route('products.update', ['id' => ':id']) }}`.replace(':id', id)
    : `{{ route('stations.assign-product-with-price') }}`;
    let method = id ? 'PUT' : 'POST';

    $.ajax({
    url,
    method,
    contentType: 'application/json',
    data: JSON.stringify(data),
    success: function() {
    showToast(id ? 'Product updated' : 'Product added', 'success');
    $('#addProductModal').modal('hide');
    $('#addProductForm')[0].reset();
    $('#addProductModal').removeData('edit-id');
    loadProducts();
    },
    error: () => showToast('Error saving product', 'error')
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

    // ================== Helpers ==================
    function populateDropdown(selector, items, valueField, textField) {
    const element = document.querySelector(selector);

    if (!element) return;

    if (element.choicesInstance) {
    element.choicesInstance.destroy();
    }

    const choices = new Choices(element, {
    searchEnabled: true,
    removeItemButton: true,
    placeholderValue: 'Select',
    shouldSort: false
    });

    element.choicesInstance = choices;

    choices.setChoices(
    items.map(i => ({
    value: i[valueField],
    label: i[textField],
    selected: false,
    disabled: false,
    })),
    'value',
    'label',
    true
    );
    }

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

    // ✅ Init
    $(function() {
    allproducts();
    loadTanks();
    loadDispensers();
    loadNozzles();
    loadProducts();
    loadStations();
    });

    function allproducts() {
    $.get(`{{ route('products.index') }}`, function(res) {
    populateDropdown('#productSelect', res, 'id', 'name');
    }).fail(() => showToast('Error loading products', 'error'));
    }

// ✅ Validate Current Level <= Capacity
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


// ✅ Show/Hide Buying Price
$(document).on("change", "#initial_setup", function () {
    if ($(this).is(":checked")) {
        $("#buyingPriceField").removeClass("d-none");
    } else {
        $("#buyingPriceField").addClass("d-none");
        $("input[name='buying_price']").val(""); // reset
    }
});


// ✅ Prevent form submit if invalid
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