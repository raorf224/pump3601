@extends('partials.layouts.master')

@section('title', 'Lubricants Management | ' . Auth::user()->full_name)
@section('title-sub', 'Finance')
@section('pagetitle', 'Lubricants')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">

    <style>
        .card-hover:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }

        .required:after {
            content: " *";
            color: red;
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

        .table-box {
            overflow-x: hidden !important;
            /* Removes horizontal scrollbar */
            overflow-y: hidden !important;
            /* Removes vertical scrollbar */
            max-height: none !important;
            /* Prevent height restrictions */
        }
    </style>
    <!-- <style>
                                .card-hover:hover {
                                    transform: translateY(-5px);
                                    transition: transform 0.3s ease;
                                }

                                .required:after {
                                    content: " *";
                                    color: red;
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

                                .table-box {
                                    overflow-x: hidden !important;
                                    overflow-y: hidden !important;
                                    max-height: none !important;
                                }

                                /* Inventory Table Specific */
                                #inventoryTable_wrapper {
                                    width: 100%;
                                    overflow-x: auto;
                                }

                                #inventoryTable {
                                    width: 100% !important;
                                    border-collapse: collapse;
                                }

                                #inventoryTable th,
                                #inventoryTable td {
                                    padding: 12px 15px;
                                    white-space: nowrap;
                                    vertical-align: middle;
                                }

                                #inventoryTable td:first-child,
                                #inventoryTable th:first-child {
                                    position: sticky;
                                    left: 0;
                                    background: white;
                                    z-index: 1;
                                }

                                #inventoryTable th {
                                    background: #f8f9fa;
                                    font-weight: 600;
                                }

                                .table-responsive {
                                    overflow-x: auto;
                                    -webkit-overflow-scrolling: touch;
                                    max-height: 70vh;
                                }

                                button:disabled {
                                    cursor: not-allowed !important;
                                    opacity: 0.7;
                                }

                                .btn-primary:disabled {
                                    background-color: #6c757d !important;
                                    border-color: #6c757d !important;
                                }
                            </style> -->
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs mb-4" id="lubeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="purchase-tab" data-bs-toggle="tab"
                                data-bs-target="#purchase" type="button" role="tab" aria-controls="purchase"
                                aria-selected="true">Lube Purchase/Sale</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory"
                                type="button" role="tab">Inventory</button>
                        </li>
                    </ul>


                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Purchase Tab -->
                        <div class="tab-pane fade show active" id="purchase" role="tabpanel">
                            <div class="accordion accordion-primary accordion-border-box mb-4" id="accountAccordion">
                                <!-- Create Lubricants Document -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#createDocumentCollapse" aria-expanded="true">
                                            <i class="bi bi-file-earmark-plus me-2"></i> Purchase/Sale Lubricants
                                        </button>
                                    </h2>

                                    <!-- Toast Container -->
                                    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
                                        <div id="mainToast" class="toast align-items-center border-0" role="alert"
                                            aria-live="assertive" aria-atomic="true">
                                            <div class="d-flex">
                                                <div class="toast-body" id="toastMessage"></div>
                                                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                                    data-bs-dismiss="toast" aria-label="Close"></button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="createDocumentCollapse" class="accordion-collapse collapse show"
                                        data-bs-parent="#accountAccordion">
                                        <div class="accordion-body">
                                            <form id="documentForm" method="POST">
                                                @csrf
                                                <div class="row g-3 align-items-end">

                                                    <!-- Station -->
                                                    <div class="col-3">
                                                        <label for="station_id" class="form-label">Station *</label>
                                                        <select name="station_id" id="station_id" class="form-select"
                                                            required>
                                                            <option value="">Search stations...</option>
                                                        </select>
                                                    </div>

                                                    <!-- Shift -->
                                                    <div class="col-3">
                                                        <label for="shift_id" class="form-label">Shift *</label>
                                                        <select name="shift_id" id="shift_id" class="form-select" required>
                                                            <option value="">Select Shift...</option>
                                                        </select>
                                                    </div>

                                                    <!-- Document Type -->
                                                    <div class="col-3">
                                                        <label class="form-label d-block">Document Type *</label>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="doc_type"
                                                                id="purchaseRadio" value="purchase" required checked>
                                                            <label class="form-check-label"
                                                                for="purchaseRadio">Purchase</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="doc_type"
                                                                id="saleRadio" value="sale" required>
                                                            <label class="form-check-label" for="saleRadio">Sale</label>
                                                        </div>
                                                    </div>

                                                    <!-- Account Type & Account -->
                                                    <div class="col-6">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <label class="form-label d-block">Account Type *</label>
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="radio"
                                                                        name="account_type" id="customerRadio"
                                                                        value="customer" required>
                                                                    <label class="form-check-label"
                                                                        for="customerRadio">Customer</label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="radio"
                                                                        name="account_type" id="supplierRadio"
                                                                        value="supplier" required checked>
                                                                    <label class="form-check-label"
                                                                        for="supplierRadio">Supplier</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <label for="account_id" class="form-label">Account *</label>
                                                                <select name="account_id" id="account_id"
                                                                    class="form-select" required>
                                                                    <option value="">Search Account...</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Invoice Number -->
                                                    <div class="col-3">
                                                        <label for="invoice_no" class="form-label">Invoice No *</label>
                                                        <input type="text" name="invoice_no" id="invoice_no"
                                                            class="form-control">
                                                    </div>

                                                    <!-- Date -->
                                                    <div class="col-3">
                                                        <label for="date" class="form-label">Date *</label>
                                                        <input type="text" class="form-control" id="date" name="date"
                                                            placeholder="Select a date" required>
                                                    </div>

                                                    <!-- Payment Status -->
                                                    <div class="col-3">
                                                        <label for="payment_status" class="form-label">Payment Status
                                                            *</label>
                                                        <select name="payment_status" id="payment_status"
                                                            class="form-select" required>
                                                            <option value="">Select Status...</option>
                                                            <option value="not_paid">Not Paid</option>
                                                            <option value="partial">Partial</option>
                                                        </select>
                                                    </div>

                                                </div>

                                                <!-- Product Lines Section -->
                                                <div class="mt-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h5>Product Lines</h5>
                                                        <button type="button" class="btn btn-success btn-sm"
                                                            id="addProductLine">
                                                            <i class="bi bi-plus-circle"></i> Add Product
                                                        </button>
                                                    </div>

                                                    <div id="productLines">
                                                        <div class="product-line row g-3 align-items-end mb-3">
                                                            <div class="col-3">
                                                                <label class="form-label">Product *</label>
                                                                <select name="lines[0][product_id]"
                                                                    class="form-select product-select" required>
                                                                    <option value="">Select Product...</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-2">
                                                                <label class="form-label">Quantity (Packs) *</label>
                                                                <input type="number" name="lines[0][qty]"
                                                                    class="form-control qty-input" required min="1"
                                                                    value="1">
                                                            </div>
                                                            <div class="col-2">
                                                                <label class="form-label">Unit Price Per Pack *</label>
                                                                <input type="number" name="lines[0][unit_price]"
                                                                    class="form-control price-input" required min="0"
                                                                    step="0.01" value="" placeholder="Enter price">
                                                            </div>
                                                            <div class="col-2">
                                                                <label class="form-label">Tax Per Pack %</label>
                                                                <input type="number" name="lines[0][tax_percent]"
                                                                    class="form-control tax-input" min="0" max="100"
                                                                    step="0.01" value="0">
                                                            </div>
                                                            <div class="col-2">
                                                                <label class="form-label">Total</label>
                                                                <input type="text" class="form-control total-display"
                                                                    readonly value="0.00">
                                                            </div>
                                                            <div class="col-1">
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm remove-line" disabled>
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Summary Section -->
                                                <div class="row mt-4">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="remarks" class="form-label">Notes</label>
                                                            <textarea name="remarks" id="remarks" class="form-control"
                                                                rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <h6 class="card-title">Summary</h6>
                                                                <div class="d-flex justify-content-between">
                                                                    <span>Subtotal:</span>
                                                                    <span id="subtotal">0.00</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between">
                                                                    <span>Tax:</span>
                                                                    <span id="taxTotal">0.00</span>
                                                                </div>
                                                                <hr>
                                                                <div class="d-flex justify-content-between fw-bold">
                                                                    <span>Grand Total:</span>
                                                                    <span id="grandTotal">0.00</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="created_by" id="created_by"
                                                    value="{{ Auth::id() }}">

                                                <div class="mt-3">
                                                    <button type="submit" class="btn btn-primary">Save Document</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        onclick="resetDocumentForm()">Reset</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow-sm card-hover">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Lubricants Documents List</h5>
                                </div>

                                <div class="card-body">
                                    <div class="table-box table-responsive">
                                        <table id="lubesTable" class="table text-nowrap align-middle">
                                            <thead>
                                                <tr>
                                                    <th>SNO</th>
                                                    <th>Station</th>
                                                    <th>Shift</th>
                                                    <th>DOC Type</th>
                                                    <th>Invoice Number</th>
                                                    <th>Date</th>
                                                    <th>Total Amount</th>
                                                    <th>Payment Status</th>
                                                    <th>Account</th>
                                                    <th class="text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inventory Tab -->
                        <div class="tab-pane fade" id="inventory" role="tabpanel">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">📊 Current Inventory Status</h5>
                                    <button type="button" class="btn btn-primary btn-sm" id="setupInventoryBtn">
                                        <i class="bi bi-plus-circle"></i> Initial Setup / Add Stock
                                    </button>
                                </div>

                                <div class="card-body p-0">
                                    <div class="table-responsive" style="overflow-x: auto;">
                                        <table id="inventoryTable" class="table table-bordered table-hover mb-0"
                                            style="min-width: 1000px;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th>Category</th>
                                                    <th>Current Stock</th>
                                                    <th>Avg Price</th>
                                                    <th>Last Purchase</th>
                                                    <th>Total Purchased</th>
                                                    <th>Total Sold</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">Loading inventory data...
                                                    </td>
                                                </tr>
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

        <!-- Payment Status Modal -->
        <div class="modal fade" id="paymentStatusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalTitle">Manage Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="paymentLubeId">
                        <input type="hidden" id="paymentStationId">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Document Information</label>
                                <div class="card">
                                    <div class="card-body">
                                        <p><strong>Invoice No:</strong> <span id="modalInvoiceNo"></span></p>
                                        <p><strong>Station:</strong> <span id="modalStationName"></span></p>
                                        <p><strong>Total Amount:</strong> Rs. <span id="modalTotalAmount">0.00</span></p>
                                        <p><strong>Current Status:</strong> <span id="modalCurrentStatus"></span></p>
                                        <p><strong>Remaining Amount:</strong> Rs. <span id="modalRemainingAmount"
                                                class="fw-bold">0.00</span></p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required-label">Select Shift</label>
                                <select class="form-select" id="paymentShift">
                                    <option value="">Select Shift...</option>
                                </select>
                                <small class="text-muted">Select the shift for which payment is being made</small>
                            </div>
                            <!-- Payment Summary Section (Add this in modal body) -->
                            <div class="col-md-12 mb-3" id="paymentSummary">
                                <!-- Dynamic content will be inserted here -->
                            </div>


                            <!-- Payment Type Selection -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label d-block">Payment Type</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="paymentType" id="partialPayment"
                                        value="partial" autocomplete="off" checked>
                                    <label class="btn btn-outline-primary" for="partialPayment">Partial Payment</label>

                                    <input type="radio" class="btn-check" name="paymentType" id="viewHistory"
                                        value="history" autocomplete="off">
                                    <label class="btn btn-outline-info" for="viewHistory">View History</label>
                                </div>
                            </div>

                            <!-- Partial Payment Fields -->
                            <div class="col-md-12 mb-3" id="partialPaymentSection" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-label">Payment Date</label>
                                        <input type="text" class="form-control" id="paymentDate"
                                            placeholder="Select payment date">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-label">Payment Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rs</span>
                                            <input type="number" class="form-control" id="paymentAmount"
                                                placeholder="Enter amount to pay" step="0.01" min="0.01">
                                        </div>
                                        <small class="text-muted">Maximum: Rs. <span id="maxPaymentAmount"
                                                class="fw-bold">0.00</span></small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-label">Payment Method</label>
                                        <select class="form-select" id="partialPaymentMethod">
                                            <option value="cash">Cash</option>
                                            <option value="bank">Bank</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3" id="partialBankAccountField" style="display: none;">
                                        <label class="form-label required-label">Select Bank Account</label>
                                        <select class="form-select" id="partialBankAccount">
                                            <option value="">Select Bank Account...</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-label">Payment Type</label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="paymentTransactionType"
                                                    id="debitRadio" value="debit" checked>
                                                <label class="form-check-label" for="debitRadio">Debit (Payment)</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="paymentTransactionType"
                                                    id="creditRadio" value="credit">
                                                <label class="form-check-label" for="creditRadio">Credit (Refund)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment History Section -->
                            <div class="col-md-12" id="paymentHistorySection" style="display: none;">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Method</th>
                                                <th>Amount</th>
                                                <th>Shift</th>
                                                <th>Bank Account</th>
                                            </tr>
                                        </thead>
                                        <tbody id="paymentHistoryBody">
                                            <!-- Payment history will be loaded here -->
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="3" class="text-end">Totals:</th>
                                                <th id="historyTotalDebit" class="text-success">0.00</th>
                                                <th id="historyTotalCredit" class="text-danger">0.00</th>
                                                <th id="historyNetBalance" class="fw-bold">0.00</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="addPartialPayment" style="display: none;">
                            <i class="bi bi-plus-circle me-2"></i>Add Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Setup Modal -->
        <div class="modal fade" id="inventorySetupModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Initial Inventory Setup</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="inventorySetupForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label required">Station</label>
                                <select id="setup_station_id" class="form-select" required>
                                    <option value="">Select Station...</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Product</label>
                                <select id="setup_product_id" class="form-select" required>
                                    <option value="">Select Product...</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Quantity (Packs)</label>
                                <input type="number" id="setup_quantity" class="form-control" step="0.01" min="0.01"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Buying Price Per Pack (Rs.)</label>
                                <input type="number" id="setup_price" class="form-control" step="0.01" min="0" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Date</label>
                                <input type="text" id="setup_date" class="form-control" placeholder="YYYY-MM-DD" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveInventorySetup">
                            <i class="bi bi-save"></i> Save Inventory
                        </button>
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
        <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>
        <script src="{{ asset('assets/js/ui/air-datepicker.init.js') }}"></script>

        <script>
            const AUTH_USER_ID = "{{ Auth::id() }}";
            const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";
            const API_BASE = "{{ url('api') }}";
            let table, inventoryTable, lineCounter = 1;  // Add inventoryTable here
            let isSubmitting = false; // Add this at the top of your script


            $(document).ready(function () {
                loadStations();
                loadLubricantsProducts();
                initDatePickers();
                initEventListeners();
                initLubricantsTable();
                initInventoryTable();  // Add this line
            });


            function initDatePickers() {
                new AirDatepicker('#date', { autoClose: true, dateFormat: 'yyyy-MM-dd', locale: localeEn });
                new AirDatepicker('#paymentDate', { autoClose: true, dateFormat: 'yyyy-MM-dd', locale: localeEn, defaultDate: new Date() });
            }

            function initEventListeners() {
                $('#station_id').on('change', function () {
                    const stationId = $(this).val();
                    if (stationId) {
                        loadShifts(stationId);
                        loadUsersStationAware($("input[name='account_type']:checked").val());
                    } else clearShiftsDropdown();
                });

                $("input[name='account_type']").on("change", function () {
                    loadUsersStationAware($(this).val());
                });

                // Document type change - validate stock on sale
                $("input[name='doc_type']").on("change", function () {
                    if ($(this).val() === 'sale') {
                        validateSaleQuantities();
                    }
                });

                $('#addProductLine').on('click', addProductLine);

                // ✅ Remove the duplicate form submission binding - only keep one
                $('#documentForm').off('submit').on('submit', function (e) {
                    e.preventDefault();
                    createLubricantsDocument();
                });

                $('#partialPaymentMethod').on('change', function () {
                    $('#partialBankAccountField').toggle($(this).val() === 'bank');
                });

                $('input[name="paymentType"]').on('change', function () {
                    const type = $(this).val();
                    $('#partialPaymentSection').toggle(type === 'partial');
                    $('#paymentHistorySection').toggle(type === 'history');
                    $('#addPartialPayment').toggle(type === 'partial');
                    if (type === 'history') loadPaymentHistory();
                });

                $('#addPartialPayment').on('click', processPartialPayment);
            }

            async function validateSaleQuantities() {
                const lines = document.querySelectorAll('.product-line');
                let hasError = false;

                for (const line of lines) {
                    const productSelect = line.querySelector('.product-select');
                    const productId = productSelect.value;
                    const productName = productSelect.options[productSelect.selectedIndex]?.text || 'Unknown';
                    const qty = parseFloat(line.querySelector('.qty-input').value) || 0;
                    const stationId = $('#station_id').val();

                    if (productId && qty > 0 && stationId) {
                        try {
                            const response = await $.ajax({
                                url: `${API_BASE}/lubes/check-stock`,
                                method: 'POST',
                                data: JSON.stringify({
                                    product_id: productId,
                                    station_id: stationId,
                                    quantity: qty
                                }),
                                contentType: 'application/json'
                            });

                            if (!response.available) {
                                hasError = true;
                                showToast(`⚠️ ${response.product_name}: Only ${response.current_stock} packs available!`, true);
                                // Optionally disable sale button or adjust quantity
                                line.querySelector('.qty-input').value = response.current_stock;
                                calculateLineTotal(line);
                            }
                        } catch (error) {
                            console.error('Stock check failed:', error);
                            showToast(`⚠️ Could not verify stock for ${productName}`, true);
                            hasError = true;
                        }
                    }
                }

                return !hasError;
            }

            // ✅ Also validate before form submission
            async function validateStockBeforeSubmit(lineElements, stationId) {
                let hasStockIssue = false;
                let stockErrors = [];

                for (const line of lineElements) {
                    const productSelect = line.querySelector('.product-select');
                    const productId = productSelect.value;
                    const productName = productSelect.options[productSelect.selectedIndex]?.text || 'Unknown';
                    const qty = parseFloat(line.querySelector('.qty-input').value) || 0;

                    if (productId && qty > 0) {
                        try {
                            const stockCheck = await $.ajax({
                                url: `${API_BASE}/lubes/check-stock`,
                                method: 'POST',
                                data: JSON.stringify({
                                    product_id: productId,
                                    station_id: stationId,
                                    quantity: qty
                                }),
                                contentType: 'application/json'
                            });

                            if (!stockCheck.available) {
                                hasStockIssue = true;
                                stockErrors.push(`${productName}: Only ${stockCheck.current_stock} packs available, you requested ${qty} packs`);
                                // Auto-correct quantity
                                line.querySelector('.qty-input').value = stockCheck.current_stock;
                                calculateLineTotal(line);
                            }
                        } catch (error) {
                            hasStockIssue = true;
                            stockErrors.push(`${productName}: Could not verify stock`);
                        }
                    }
                }

                if (hasStockIssue) {
                    showToast(`❌ Cannot create sale document:\n${stockErrors.join('\n')}`, true);
                    return false;
                }
                return true;
            }

            function calculateLineTotalForElement(line) {
                const qty = parseFloat(line.querySelector('.qty-input').value) || 0;
                const price = parseFloat(line.querySelector('.price-input').value) || 0;
                const taxPercent = parseFloat(line.querySelector('.tax-input').value) || 0;
                const subtotal = qty * price;
                const taxAmount = subtotal * (taxPercent / 100);
                line.querySelector('.total-display').value = (subtotal + taxAmount).toFixed(2);
                calculateGrandTotal();
            }

            // Load Stations
            function loadStations() {
                let apiUrl = AUTH_ROLE === 'admin' ? '/api/stations' :
                    AUTH_ROLE === 'employee' ? `/api/stations_emp/${AUTH_USER_ID}` :
                        `/api/stations/${AUTH_USER_ID}`;
                $.ajax({
                    url: apiUrl, method: 'GET',
                    success: (resp) => populateDropdown('#station_id', resp, 'id', 'name'),
                    error: () => showToast("Error loading stations", true)
                });
            }

            // Load Shifts
            function loadShifts(stationId) {
                if (!stationId) return;
                $.ajax({
                    url: `/api/stations/${stationId}/open-shifts`, method: 'GET',
                    success: (resp) => {
                        if (resp?.data?.length) {
                            populateShiftsDropdown(resp.data);
                            populatePaymentModalShifts(resp.data);
                        } else {
                            showToast("No open shifts found", true);
                            clearShiftsDropdown();
                        }
                    },
                    error: (xhr) => {
                        console.error("Error loading shifts:", xhr.responseText);
                        showToast("Error loading shifts", true);
                        clearShiftsDropdown();
                    }
                });
            }

            function populateShiftsDropdown(shifts) {
                const element = document.querySelector('#shift_id');
                if (!element) return;
                if (element.choicesInstance) element.choicesInstance.destroy();
                element.choicesInstance = new Choices(element, {
                    searchEnabled: true, removeItemButton: false, placeholderValue: 'Select Shift', shouldSort: false
                });
                const shiftOptions = shifts.map(shift => ({
                    value: shift.id, label: `Shift #${shift.shift_no} (${shift.start_time})`
                }));
                element.choicesInstance.setChoices(shiftOptions, 'value', 'label', true);
            }

            function populatePaymentModalShifts(shifts) {
                const element = document.querySelector('#paymentShift');
                if (!element) return;
                if (element.choicesInstance) element.choicesInstance.destroy();
                element.choicesInstance = new Choices(element, {
                    searchEnabled: true, removeItemButton: false, placeholderValue: 'Select Shift', shouldSort: false
                });
                element.choicesInstance.setChoices(shifts.map(s => ({ value: s.id, label: `Shift #${s.shift_no} (${s.start_time})` })), 'value', 'label', true);
            }

            function clearShiftsDropdown() {
                const element = document.querySelector('#shift_id');
                if (!element) return;
                if (element.choicesInstance) element.choicesInstance.destroy();
                element.choicesInstance = new Choices(element, {
                    searchEnabled: true, removeItemButton: false, placeholderValue: 'Select Shift', shouldSort: false
                });
                element.choicesInstance.setChoices([{ value: '', label: 'Select Shift...' }], 'value', 'label', true);
            }

            // Load Products
            function loadLubricantsProducts() {
                $.ajax({
                    url: '/api/products/lubricants', method: 'GET',
                    success: (products) => {
                        const productData = Array.isArray(products) ? products : (products?.data || []);
                        document.querySelectorAll('.product-select').forEach(select => {
                            while (select.options.length > 1) select.remove(1);
                            productData.forEach(p => {
                                const option = document.createElement('option');
                                option.value = p.id;
                                option.textContent = p.name;
                                select.appendChild(option);
                            });
                            if (select.choicesInstance) select.choicesInstance.destroy();
                            select.choicesInstance = new Choices(select, {
                                searchEnabled: true, removeItemButton: false, placeholderValue: 'Select Product', shouldSort: false
                            });
                        });
                    },
                    error: () => showToast("Error loading products", true)
                });
            }

            // Load Accounts
            function loadUsersStationAware(type) {
                if (!type) return;
                const stationId = $('#station_id').val();
                if (stationId) {
                    $.ajax({
                        url: `/api/stations/${stationId}/accounts`, method: 'GET',
                        success: (resp) => {
                            const accounts = Array.isArray(resp) ? resp : (resp?.data || []);
                            const filtered = accounts.filter(a => (a.type || '').toString().toLowerCase() === type.toLowerCase());
                            populateDropdown('#account_id', filtered, 'id', 'name');
                        },
                        error: () => loadUsersByCategory(type)
                    });
                } else loadUsersByCategory(type);
            }

            function loadUsersByCategory(type) {
                $.ajax({
                    url: `/api/accounts/category/${type.toLowerCase()}`, method: 'GET',
                    success: (resp) => {
                        const users = Array.isArray(resp) ? resp : (resp?.data || []);
                        populateDropdown('#account_id', users, 'id', 'name');
                    },
                    error: () => showToast("Error loading users", true)
                });
            }

            // Product Lines
            function addProductLine() {
                const newLine = document.createElement('div');
                newLine.className = 'product-line row g-3 align-items-end mb-3';
                newLine.innerHTML = `
                <div class="col-3"><select name="lines[${lineCounter}][product_id]" class="form-select product-select" required>
                    <option value="">Select Product...</option></select></div>
                <div class="col-2"><input type="number" name="lines[${lineCounter}][qty]" class="form-control qty-input" required min="1" value="1"></div>
                <div class="col-2"><input type="number" name="lines[${lineCounter}][unit_price]" class="form-control price-input" required min="0" step="0.01" value="" placeholder="Enter price"></div>
                <div class="col-2"><input type="number" name="lines[${lineCounter}][tax_percent]" class="form-control tax-input" required min="0" max="100" step="0.01" value="0"></div>
                <div class="col-2"><input type="text" class="form-control total-display" readonly value="0.00"></div>
                <div class="col-1"><button type="button" class="btn btn-danger btn-sm remove-line"><i class="bi bi-trash"></i></button></div>`;
                document.getElementById('productLines').appendChild(newLine);
                loadProductsForLine(newLine);
                const removeButtons = document.querySelectorAll('.remove-line');
                if (removeButtons.length > 1) removeButtons.forEach(btn => btn.disabled = false);
                addLineEventListeners(newLine);
                lineCounter++;
            }

            function loadProductsForLine(lineElement) {
                const select = lineElement.querySelector('.product-select');
                $.ajax({
                    url: '/api/products/lubricants', method: 'GET',
                    success: (products) => {
                        const productData = Array.isArray(products) ? products : (products?.data || []);
                        while (select.options.length > 1) select.remove(1);
                        productData.forEach(p => {
                            const option = document.createElement('option');
                            option.value = p.id;
                            option.textContent = p.name;
                            select.appendChild(option);
                        });
                        if (select.choicesInstance) select.choicesInstance.destroy();
                        select.choicesInstance = new Choices(select, {
                            searchEnabled: true, removeItemButton: false, placeholderValue: 'Select Product', shouldSort: false
                        });
                    }
                });
            }

            function addLineEventListeners(line) {
                const qtyInput = line.querySelector('.qty-input');
                const priceInput = line.querySelector('.price-input');
                const taxInput = line.querySelector('.tax-input');
                const totalDisplay = line.querySelector('.total-display');
                const removeBtn = line.querySelector('.remove-line');
                const productSelect = line.querySelector('.product-select');

                [qtyInput, priceInput, taxInput].forEach(input => {
                    input.addEventListener('input', function () {
                        calculateLineTotal(line);
                    });
                });

                productSelect.addEventListener('change', function () {
                    if ($("input[name='doc_type']:checked").val() === 'sale') {
                        validateSingleSaleQuantity(line);
                    }
                    calculateLineTotal(line);
                });

                qtyInput.addEventListener('change', function () {
                    if ($("input[name='doc_type']:checked").val() === 'sale') {
                        validateSingleSaleQuantity(line);
                    }
                });

                removeBtn.addEventListener('click', function () {
                    line.remove();
                    calculateGrandTotal();
                    const removeButtons = document.querySelectorAll('.remove-line');
                    if (removeButtons.length === 1) removeButtons[0].disabled = true;
                });

                calculateLineTotal(line);
            }

            function validateSingleSaleQuantity(line) {
                const productSelect = line.querySelector('.product-select');
                const productId = productSelect.value;
                const productName = productSelect.options[productSelect.selectedIndex]?.text || 'Unknown';
                const qty = parseFloat(line.querySelector('.qty-input').value) || 0;
                const stationId = $('#station_id').val();

                if (productId && qty > 0 && stationId) {
                    $.ajax({
                        url: `${API_BASE}/lubes/check-stock`,
                        method: 'POST',
                        async: false,
                        data: JSON.stringify({
                            product_id: productId,
                            station_id: stationId,
                            quantity: qty
                        }),
                        contentType: 'application/json',
                        success: function (response) {
                            if (!response.available) {
                                showToast(`⚠️ Only ${response.current_stock} packs available for ${response.product_name}! Quantity adjusted.`, true);
                                line.querySelector('.qty-input').value = response.current_stock;
                                calculateLineTotal(line);
                            }
                        }
                    });
                }
            }


            function calculateLineTotal(line) {
                const qty = parseFloat(line.querySelector('.qty-input').value) || 0;
                const price = parseFloat(line.querySelector('.price-input').value) || 0;
                const taxPercent = parseFloat(line.querySelector('.tax-input').value) || 0;
                const subtotal = qty * price;
                const taxAmount = subtotal * (taxPercent / 100);
                line.querySelector('.total-display').value = (subtotal + taxAmount).toFixed(2);
                calculateGrandTotal();
            }

            function calculateGrandTotal() {
                let subtotal = 0, taxTotal = 0;
                document.querySelectorAll('.product-line').forEach(line => {
                    const qty = parseFloat(line.querySelector('.qty-input').value) || 0;
                    const price = parseFloat(line.querySelector('.price-input').value) || 0;
                    const taxPercent = parseFloat(line.querySelector('.tax-input').value) || 0;
                    const lineSubtotal = qty * price;
                    const lineTax = lineSubtotal * (taxPercent / 100);
                    subtotal += lineSubtotal;
                    taxTotal += lineTax;
                });
                const grandTotal = subtotal + taxTotal;
                $('#subtotal').text(subtotal.toFixed(2));
                $('#taxTotal').text(taxTotal.toFixed(2));
                $('#grandTotal').text(grandTotal.toFixed(2));
            }

            // Create Document
            async function createLubricantsDocument(e) {
                if (e) e.preventDefault();

                // ✅ Prevent double submission
                if (isSubmitting) {
                    showToast("⚠️ Please wait, processing...", false);
                    return;
                }

                const lineElements = document.querySelectorAll('.product-line');
                if (lineElements.length === 0) {
                    showToast("❌ Please add at least one product!", true);
                    return;
                }

                const shiftId = $('#shift_id').val();
                if (!shiftId) {
                    showToast("❌ Please select a shift!", true);
                    return;
                }

                const stationId = $('#station_id').val();
                if (!stationId) {
                    showToast("❌ Please select a station!", true);
                    return;
                }

                // Price validation before submit
                let hasZeroPrice = false;
                lineElements.forEach(line => {
                    const price = parseFloat(line.querySelector('.price-input').value) || 0;
                    if (price <= 0) {
                        hasZeroPrice = true;
                        showToast("❌ Please enter valid price for all products! Price cannot be zero.", true);
                        return;
                    }
                });
                if (hasZeroPrice) return;

                const docType = $("input[name='doc_type']:checked").val();

                // ✅ STOCK VALIDATION FOR SALE - More robust
                if (docType === 'sale') {
                    // First check if any product has zero stock
                    let hasZeroStock = false;
                    for (const line of lineElements) {
                        const productSelect = line.querySelector('.product-select');
                        const productId = productSelect.value;
                        const qty = parseFloat(line.querySelector('.qty-input').value) || 0;

                        if (productId && qty > 0) {
                            const stockCheck = await $.ajax({
                                url: `${API_BASE}/lubes/check-stock`,
                                method: 'POST',
                                data: JSON.stringify({
                                    product_id: productId,
                                    station_id: stationId,
                                    quantity: qty
                                }),
                                contentType: 'application/json'
                            }).catch(() => ({ available: false, current_stock: 0 }));

                            if (!stockCheck.available) {
                                hasZeroStock = true;
                                showToast(`❌ ${stockCheck.product_name}: Only ${stockCheck.current_stock} packs available!`, true);
                                line.querySelector('.qty-input').value = stockCheck.current_stock;
                                calculateLineTotal(line);
                            }
                        }
                    }

                    if (hasZeroStock) {
                        return; // Stop submission
                    }
                }


                let valid = true;
                lineElements.forEach(line => {
                    if (!line.querySelector('.product-select').value) {
                        valid = false;
                        showToast("❌ Please select product for all lines!", true);
                        return;
                    }
                });
                if (!valid) return;

                const formData = new FormData(document.getElementById('documentForm'));
                const jsonData = {
                    station_id: parseInt(formData.get('station_id')),
                    shift_id: parseInt(shiftId),
                    doc_type: docType,
                    account_id: parseInt(formData.get('account_id')),
                    invoice_no: formData.get('invoice_no'),
                    date: formData.get('date'),
                    payment_status: formData.get('payment_status'),
                    remarks: formData.get('remarks') || '',
                    created_by: AUTH_USER_ID,
                    lines: []
                };

                lineElements.forEach((line, index) => {
                    jsonData.lines.push({
                        product_id: parseInt(line.querySelector('.product-select').value),
                        qty: parseFloat(line.querySelector('.qty-input').value),
                        unit_price: parseFloat(line.querySelector('.price-input').value),
                        tax_percent: parseFloat(line.querySelector('.tax-input').value)
                    });
                });

                isSubmitting = true;

                const submitBtn = $('#documentForm button[type="submit"]');
                const originalBtnText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

                $.ajax({
                    url: '/api/lubes',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(jsonData),
                    success: (response) => {
                        showToast("✅ " + response.message);
                        resetDocumentForm(false);

                        // ✅ Reload tables only once
                        if (table) table.ajax.reload(null, false);
                        if (inventoryTable) inventoryTable.ajax.reload(null, false);

                        isSubmitting = false;
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    },
                    error: (xhr) => {
                        const error = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Error creating document!';
                        showToast("❌ " + error, true);

                        isSubmitting = false;
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            }




function resetDocumentForm(showMessage = true, keepStationAndProducts = false) {
    document.getElementById('documentForm').reset();

    if ($('#station_id')[0]?.choicesInstance) $('#station_id')[0].choicesInstance.destroy();
    if ($('#shift_id')[0]?.choicesInstance) $('#shift_id')[0].choicesInstance.destroy();
    if ($('#account_id')[0]?.choicesInstance) $('#account_id')[0].choicesInstance.destroy();

    $('#station_id').empty().append('<option value="">Select Station...</option>');
    $('#shift_id').empty().append('<option value="">Select Shift...</option>');
    $('#account_id').empty().append('<option value="">Select Account...</option>');

    clearShiftsDropdown();
    
    // ✅ Only reload stations and products if not keeping them
    if (!keepStationAndProducts) {
        loadStations();
        loadLubricantsProducts();
    }

    const productLines = document.getElementById('productLines');
    productLines.innerHTML = `
        <div class="product-line row g-3 align-items-end mb-3">
            <div class="col-3"><select name="lines[0][product_id]" class="form-select product-select" required><option value="">Select Product...</option></select></div>
            <div class="col-2"><input type="number" name="lines[0][qty]" class="form-control qty-input" required min="1" value="1"></div>
            <div class="col-2"><input type="number" name="lines[0][unit_price]" class="form-control price-input" required min="0" step="0.01" value="" placeholder="Enter price"></div>
            <div class="col-2"><input type="number" name="lines[0][tax_percent]" class="form-control tax-input" required min="0" max="100" step="0.01" value="0"></div>
            <div class="col-2"><input type="text" class="form-control total-display" readonly value="0.00"></div>
            <div class="col-1"><button type="button" class="btn btn-danger btn-sm remove-line" disabled><i class="bi bi-trash"></i></button></div>
        </div>`;

    lineCounter = 1;
    addLineEventListeners(document.querySelector('.product-line'));
    calculateGrandTotal();

    if (showMessage) {
        showToast("✅ Form reset successfully!");
    }
}

// DataTable for Documents
            function initLubricantsTable() {
                let apiUrl = AUTH_ROLE === 'admin' ? '/api/lubes' :
                    AUTH_ROLE === 'owner' ? `/api/lubes/owner/${AUTH_USER_ID}` :
                        AUTH_ROLE === 'employee' ? `/api/lubes/employee/${AUTH_USER_ID}` :
                            '/api/lubes';
                table = $('#lubesTable').DataTable({
                    paging: true, searching: true, info: true, lengthChange: true, ordering: true,
                    ajax: { url: apiUrl, dataSrc: '' },
                    columns: [
                        { data: null, render: (data, type, row, meta) => meta.row + 1 },
                        { data: "station_name" },
                        { data: "shift_id", render: data => data ? `Shift #${data}` : 'N/A' },
                        { data: "doc_type", render: data => data === 'purchase' ? '<span class="badge bg-primary">Purchase</span>' : '<span class="badge bg-success">Sale</span>' },
                        { data: "invoice_no", render: function (data) { return data ? data : 'N/A'; } },
                        { data: "date", render: data => new Date(data).toLocaleDateString() },
                        { data: "total_amount", render: data => `Rs. ${parseFloat(data || 0).toFixed(2)}` },
                        {
                            data: "payment_status", render: function (data) {
                                const statusMap = { 'paid': '<span class="badge bg-success">Paid</span>', 'not_paid': '<span class="badge bg-danger">Not Paid</span>', 'partial': '<span class="badge bg-warning">Partial</span>' };
                                return statusMap[data] || data;
                            }
                        },
                        { data: "account_name" },
                        {
                            data: null, render: function (data, type, row) {
                                let actions = `<button class="btn btn-sm btn-info" onclick="viewLubricantsDocument(${row.id})" title="View"><i class="bi bi-eye"></i></button>`;
                                if (row.payment_status !== 'paid') {
                                    actions += `<button class="btn btn-sm btn-warning ms-1" onclick="openPaymentModal(${row.id})" title="Manage Payment"><i class="bi bi-credit-card"></i></button>`;
                                } else {
                                    actions += `<button class="btn btn-sm btn-outline-info ms-1" onclick="openPaymentModal(${row.id})" title="Payment History"><i class="bi bi-clock-history"></i></button>`;
                                }
                                actions += `<button class="btn btn-sm btn-danger ms-1" onclick="deleteLubricantsDocument(${row.id})" title="Delete"><i class="bi bi-trash"></i></button>`;
                                return `<div class="btn-group">${actions}</div>`;
                            }, className: "text-center"
                        }
                    ]
                });
            }

            // Inventory Table Function
            function initInventoryTable() {
                // ✅ Destroy existing DataTable if it exists
                if ($.fn.DataTable.isDataTable('#inventoryTable')) {
                    $('#inventoryTable').DataTable().destroy();
                    $('#inventoryTable tbody').empty();
                }

                inventoryTable = $('#inventoryTable').DataTable({
                    processing: true,
                    serverSide: false,
                    scrollX: true,
                    autoWidth: true,
                    ajax: {
                        url: '/api/lubes/inventory',
                        type: 'GET',
                        dataSrc: function (json) {
                            if (!json || json.length === 0) {
                                return [];
                            }
                            return json;
                        },
                        error: function (xhr, status, error) {
                            console.error('Inventory AJAX Error:', error);
                            showToast('Failed to load inventory data', true);
                            return [];
                        }
                    },
                    columns: [
                        { data: 'product_name', defaultContent: '—' },
                        { data: 'category', defaultContent: 'lubricants' },
                        {
                            data: 'current_stock',
                            render: function (data, type, row) {
                                let stock = parseFloat(data || 0);
                                let badgeClass = stock <= 0 ? 'danger' : (stock < 50 ? 'warning' : 'success');
                                return `<span class="badge bg-${badgeClass} px-3 py-2">${stock.toFixed(2)} ${row.unit || 'Packs'}</span>`;
                            }
                        },
                        {
                            data: 'avg_buying_price',
                            render: function (data) {
                                return `Rs. ${parseFloat(data || 0).toFixed(2)}`;
                            }
                        },
                        {
                            data: 'last_purchase_date',
                            render: function (data) {
                                return data ? new Date(data).toLocaleDateString() : '—';
                            }
                        },
                        {
                            data: 'total_purchased',
                            render: function (data) {
                                return parseFloat(data || 0).toFixed(2);
                            }
                        },
                        {
                            data: 'total_sold',
                            render: function (data) {
                                return parseFloat(data || 0).toFixed(2);
                            }
                        },
                        {
                            data: 'current_stock',
                            render: function (data) {
                                let stock = parseFloat(data || 0);
                                if (stock <= 0) return '<span class="badge bg-danger px-3 py-2">Out of Stock</span>';
                                if (stock < 50) return '<span class="badge bg-warning text-dark px-3 py-2">Low Stock</span>';
                                return '<span class="badge bg-success px-3 py-2">In Stock</span>';
                            }
                        }
                    ],
                    order: [[0, 'asc']],
                    pageLength: 25,
                    language: {
                        emptyTable: "📦 No inventory data found. Add some purchases first!",
                        processing: "Loading..."
                    },
                    // ✅ Prevent multiple requests
                    deferRender: true,
                    retrieve: false
                });
            }



            // Payment Modal Functions (keep existing)
            function openPaymentModal(lubeId) {
                $('#paymentModalTitle').html('Loading Payment Details...');
                $.ajax({
                    url: `${API_BASE}/lubes/${lubeId}`, method: 'GET',
                    success: function (response) {
                        const doc = response.document || {};
                        const totalAmount = parseFloat(response.total_amount || 0);
                        const totalPaid = parseFloat(response.total_paid || 0);
                        const remaining = parseFloat(response.remaining_amount || (totalAmount - totalPaid));

                        $('#paymentLubeId').val(lubeId);
                        $('#paymentStationId').val(doc.station_id || '');
                        $('#modalInvoiceNo').text(doc.invoice_no || 'N/A');
                        $('#modalStationName').text(doc.station_name || 'N/A');
                        $('#modalTotalAmount').text(totalAmount.toFixed(2));
                        $('#modalCurrentStatus').text(doc.payment_status || 'N/A');
                        $('#modalRemainingAmount').text(remaining.toFixed(2));
                        updatePaymentSummary(totalAmount, totalPaid, remaining);
                        $('#paymentAmount').val(remaining > 0 ? remaining.toFixed(2) : '0.00');
                        $('#maxPaymentAmount').text(remaining.toFixed(2));
                        $('#paymentAmount').attr({ 'max': remaining, 'min': '0.01', 'step': '0.01' });

                        if (doc.station_id) {
                            loadShiftsForPaymentModal(doc.station_id);
                            loadBankAccountsForPaymentModal(doc.station_id);
                        }

                        resetPaymentModalForm();
                        const today = new Date().toISOString().split('T')[0];
                        $('#paymentDate').val(today);

                        if (remaining <= 0) {
                            $('#addPartialPayment').hide();
                            $('input[name="paymentType"][value="history"]').prop('checked', true).trigger('change');
                        } else {
                            $('#addPartialPayment').show();
                            $('input[name="paymentType"][value="partial"]').prop('checked', true).trigger('change');
                        }

                        $('#paymentModalTitle').html(`Manage Payment - Invoice: ${doc.invoice_no || lubeId}`);
                        $('#paymentStatusModal').modal('show');
                    },
                    error: function (xhr) {
                        $('#paymentModalTitle').html('Manage Payment');
                        showToast("Error loading document details: " + (xhr.responseJSON?.message || 'Error'), true);
                    }
                });
            }

            function updatePaymentSummary(totalAmount, totalPaid, remaining) {
                const summaryHTML = `
                                                                <div class="alert ${remaining <= 0 ? 'alert-success' : remaining < totalAmount ? 'alert-warning' : 'alert-danger'}">
                                                                    <div class="row">
                                                                        <div class="col-4"><strong>Total Amount:</strong><br><span class="fs-5">Rs. ${totalAmount.toFixed(2)}</span></div>
                                                                        <div class="col-4"><strong>Total Paid:</strong><br><span class="fs-5">Rs. ${totalPaid.toFixed(2)}</span></div>
                                                                        <div class="col-4"><strong>Remaining:</strong><br><span class="fs-5 fw-bold">Rs. ${remaining.toFixed(2)}</span></div>
                                                                    </div>
                                                                    <hr class="my-2">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <small class="text-muted">Payment Status: <span class="badge ${remaining <= 0 ? 'bg-success' : remaining < totalAmount ? 'bg-warning' : 'bg-danger'}">${remaining <= 0 ? 'Fully Paid' : remaining < totalAmount ? 'Partially Paid' : 'Not Paid'}</span></small>
                                                                        ${remaining > 0 ? `<small class="text-muted">You can pay up to Rs. ${remaining.toFixed(2)}</small>` : ''}
                                                                    </div>
                                                                </div>`;
                $('#paymentSummary').html(summaryHTML);
            }

            function loadShiftsForPaymentModal(stationId) {
                if (!stationId) return;
                $.ajax({
                    url: `/api/stations/${stationId}/open-shifts`, method: 'GET',
                    success: (resp) => {
                        const shifts = resp?.data || [];
                        let shiftSelect = $("#paymentShift");
                        shiftSelect.empty().append('<option value="">Select Shift...</option>');
                        if (shifts.length) shifts.forEach(s => shiftSelect.append(`<option value="${s.id}">Shift #${s.shift_no} (${s.start_time})</option>`));
                        else shiftSelect.append('<option value="">No open shifts found</option>');
                    },
                    error: (xhr) => $('#paymentShift').html('<option value="">Error loading shifts</option>')
                });
            }

            function loadBankAccountsForPaymentModal(stationId) {
                if (!stationId) return;
                $.ajax({
                    url: `/api/stations/${stationId}/accounts`, method: 'GET',
                    success: (resp) => {
                        const accounts = Array.isArray(resp) ? resp : (resp?.data || []);
                        const bankAccounts = accounts.filter(a => (a.type || '').toString().toLowerCase() === 'bank');
                        const bankSelect = $('#partialBankAccount');
                        bankSelect.empty().append('<option value="">Select Bank Account...</option>');
                        if (bankAccounts.length) bankAccounts.forEach(b => bankSelect.append(`<option value="${b.id}">${b.name} - ${b.account_number || 'N/A'} (${b.bank_name || 'Bank'})</option>`));
                        else bankSelect.append('<option value="">No bank accounts found</option>');
                    },
                    error: (err) => $('#partialBankAccount').html('<option value="">Error loading accounts</option>')
                });
            }

            function resetPaymentModalForm() {
                $('#partialPaymentMethod').val('cash');
                $('#partialBankAccountField').hide();
                $('input[name="paymentTransactionType"][value="debit"]').prop('checked', true);
                $('input[name="paymentType"][value="partial"]').prop('checked', true);
                $('#partialPaymentSection').show();
                $('#paymentHistorySection').hide();
                $('#addPartialPayment').show();
            }


            // ✅ Load Payment History
            function loadPaymentHistory() {
                const lubeId = $('#paymentLubeId').val();
                if (!lubeId) return;
                $.ajax({
                    url: `${API_BASE}/lubes/${lubeId}/payment-history`, method: 'GET',
                    success: function (response) {
                        const payments = response.payments || [];
                        const totals = response.totals || {};
                        let html = '';
                        if (payments.length) {
                            payments.forEach(p => {
                                html += `<tr>
                                                                                <td>${new Date(p.date).toLocaleDateString()}</td>
                                                                                <td><span class="badge ${p.type === 'debit' ? 'bg-danger' : 'bg-success'}">${p.type === 'debit' ? 'Debit' : 'Credit'}</span></td>
                                                                                <td><span class="badge ${p.method === 'bank' ? 'bg-info' : 'bg-secondary'}">${p.method === 'bank' ? 'Bank' : 'Cash'}</span></td>
                                                                                <td class="${p.type === 'debit' ? 'text-danger' : 'text-success'}">Rs. ${parseFloat(p.ammount).toFixed(2)}</td>
                                                                                <td>${p.shift_no || 'N/A'}</td>
                                                                                <td>${p.account_name || 'N/A'}</td>
                                                                            </tr>`;
                            });
                        } else {
                            html = `<tr><td colspan="6" class="text-center">No payment history found</td></tr>`;
                        }
                        $('#paymentHistoryBody').html(html);
                        $('#historyTotalDebit').text(`Rs. ${(totals.total_debit || 0).toFixed(2)}`);
                        $('#historyTotalCredit').text(`Rs. ${(totals.total_credit || 0).toFixed(2)}`);
                        $('#historyNetBalance').text(`Rs. ${(totals.net_balance || 0).toFixed(2)}`);
                    },
                    error: function (xhr) {
                        showToast("Error loading payment history", true);
                    }
                });
            }


            // ✅ Process Payment
            function processPartialPayment() {
                const lubeId = $('#paymentLubeId').val();
                const shiftId = $('#paymentShift').val();
                const paymentAmount = parseFloat($('#paymentAmount').val()) || 0;
                const paymentMethod = $('#partialPaymentMethod').val();
                const paymentDate = $('#paymentDate').val();
                const accountId = paymentMethod === 'bank' ? $('#partialBankAccount').val() : null;
                const type = $('input[name="paymentTransactionType"]:checked').val();
                const remainingText = $('#modalRemainingAmount').text().replace('Rs. ', '').trim();
                const remainingAmount = parseFloat(remainingText) || 0;

                if (!shiftId) { showToast("Please select a shift", true); return; }
                if (!paymentAmount || paymentAmount <= 0) { showToast("Please enter a valid payment amount", true); return; }
                if (paymentAmount > remainingAmount) { showToast(`Payment amount exceeds remaining amount of Rs. ${remainingAmount.toFixed(2)}`, true); return; }
                if (!paymentDate) { showToast("Please select a payment date", true); return; }
                if (paymentMethod === 'bank' && !accountId) { showToast("Please select a bank account", true); return; }

                const addBtn = $('#addPartialPayment');
                const originalText = addBtn.html();
                addBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

                const data = { shift_id: shiftId, payment_amount: paymentAmount, payment_method: paymentMethod, payment_date: paymentDate, account_id: accountId, type: type };

                $.ajax({
                    url: `${API_BASE}/lubes/${lubeId}/partial-payment`, method: 'POST', contentType: 'application/json', data: JSON.stringify(data),
                    success: function (response) {
                        showToast("Payment recorded successfully!");
                        $('#paymentStatusModal').modal('hide');
                        if (table) table.ajax.reload();
                        addBtn.prop('disabled', false).html(originalText);
                    },
                    error: function (xhr) {
                        const error = xhr.responseJSON?.message || 'Failed to record payment';
                        showToast(error, true);
                        addBtn.prop('disabled', false).html(originalText);
                    }
                });
            }


            // View Document
            function viewLubricantsDocument(id) {
                $.ajax({
                    url: `${API_BASE}/lubes/${id}`, method: 'GET',
                    success: function (response) {
                        const doc = response.document;
                        const lines = response.lines;
                        populateDocumentForm(doc, lines);
                        const accordionElement = document.getElementById('createDocumentCollapse');
                        if (accordionElement) {
                            const accordionInstance = bootstrap.Collapse.getOrCreateInstance(accordionElement, { toggle: false });
                            accordionInstance.show();
                        }
                        // Switch to purchase tab
                        document.getElementById('purchase-tab').click();
                        showToast("✅ Document loaded successfully!");
                    },
                    error: function (xhr) {
                        showToast("❌ Error loading document details!", true);
                    }
                });
            }

            function populateDocumentForm(doc, lines) {
                $('#documentForm input, #documentForm select, #documentForm textarea').prop('readonly', true).prop('disabled', true);
                $('#addProductLine').hide();
                $('.remove-line').hide();
                $('button[type="submit"]').hide();
                populateDropdown('#station_id', [{ id: doc.station_id, name: doc.station_name }], 'id', 'name', doc.station_id);
                if (doc.station_id) {
                    setTimeout(() => {
                        loadShifts(doc.station_id);
                        if (doc.shift_id) {
                            setTimeout(() => {
                                $('#shift_id').val(doc.shift_id);
                                if ($('#shift_id').data('choices')) $('#shift_id').data('choices').setChoiceByValue(doc.shift_id.toString());
                            }, 500);
                        }
                    }, 300);
                }
                $(`input[name="doc_type"][value="${doc.doc_type}"]`).prop('checked', true).prop('disabled', true);
                $(`input[name="account_type"][value="${doc.account_type}"]`).prop('checked', true).prop('disabled', true);
                populateDropdown('#account_id', [{ id: doc.account_id, name: doc.account_name }], 'id', 'name', doc.account_id);
                $('#invoice_no').val(doc.invoice_no);
                $('#date').val(doc.date);
                $('#payment_status').val(doc.payment_status);
                $('#remarks').val(doc.remarks || '');
                $('#productLines').empty();
                lines.forEach((line) => {
                    $('#productLines').append(`
                                                                        <div class="product-line row g-3 align-items-end mb-3">
                                                                            <div class="col-3"><input type="text" class="form-control" value="${line.product_name}" readonly></div>
                                                                            <div class="col-2"><input type="number" class="form-control" value="${line.qty}" readonly></div>
                                                                            <div class="col-2"><input type="number" class="form-control" value="${line.unit_price}" readonly></div>
                                                                            <div class="col-2"><input type="number" class="form-control" value="${line.tax_percent}" readonly></div>
                                                                            <div class="col-2"><input type="text" class="form-control" value="${(parseFloat(line.qty) * parseFloat(line.unit_price) * (1 + parseFloat(line.tax_percent) / 100)).toFixed(2)}" readonly></div>
                                                                            <div class="col-1"><button type="button" class="btn btn-danger btn-sm remove-line" style="display: none;"><i class="bi bi-trash"></i></button></div>
                                                                        </div>`);
                });
                let subtotal = 0, taxTotal = 0;
                lines.forEach(line => {
                    const lineSubtotal = parseFloat(line.qty) * parseFloat(line.unit_price);
                    const lineTax = lineSubtotal * (parseFloat(line.tax_percent) / 100);
                    subtotal += lineSubtotal;
                    taxTotal += lineTax;
                });
                const grandTotal = subtotal + taxTotal;
                $('#subtotal').text(subtotal.toFixed(2));
                $('#taxTotal').text(taxTotal.toFixed(2));
                $('#grandTotal').text(grandTotal.toFixed(2));
                if (!$('#closeViewBtn').length) {
                    $('#documentForm').append(`<div class="mt-3"><button type="button" class="btn btn-secondary" id="closeViewBtn"><i class="bi bi-x-circle"></i> Close View</button></div>`);
                    $('#closeViewBtn').on('click', function () {
                        resetDocumentForm();
                        $('#documentForm input, #documentForm select, #documentForm textarea').prop('readonly', false).prop('disabled', false);
                        $('#addProductLine').show();
                        $('button[type="submit"]').show();
                        $('#closeViewBtn').remove();
                        const accordion = new bootstrap.Collapse(document.getElementById('createDocumentCollapse'), { toggle: false });
                        accordion.show();
                        loadStations();
                        loadLubricantsProducts();
                        showToast("✅ Form cleared and ready for new entry!");
                    });
                }
            }

            // Delete Document
            function deleteLubricantsDocument(id) {
                if (!confirm("Are you sure you want to delete this document?")) return;

                const deleteBtn = $(`button[onclick="deleteLubricantsDocument(${id})"]`);
                const originalText = deleteBtn.html();
                deleteBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                $.ajax({
                    url: `${API_BASE}/lubes/${id}`,
                    method: "DELETE",
                    success: function () {
                        showToast("✅ Document deleted successfully!");
                        // ✅ Reload without duplicate calls
                        if (table) table.ajax.reload(null, false);
                        if (inventoryTable) {
                            inventoryTable.ajax.reload(null, false);
                        }
                        deleteBtn.prop('disabled', false).html(originalText);
                    },
                    error: function (xhr) {
                        showToast("❌ Error deleting document: " + (xhr.responseJSON?.message || 'Unknown error'), true);
                        deleteBtn.prop('disabled', false).html(originalText);
                    }
                });
            }


            // Utilities
            function showToast(message, isError = false) {
                const toastEl = document.getElementById("mainToast");
                const toastBody = document.getElementById("toastMessage");
                toastBody.textContent = message;
                toastEl.className = "toast align-items-center border-0 " + (isError ? "text-bg-danger" : "text-bg-success");
                new bootstrap.Toast(toastEl, { delay: 3000 }).show();
            }

            function populateDropdown(selector, items, valueField, textField, selectedValue = null) {
                const element = document.querySelector(selector);
                if (!element) return;
                if (element.choicesInstance) element.choicesInstance.destroy();
                element.choicesInstance = new Choices(element, {
                    searchEnabled: true, removeItemButton: false, placeholderValue: 'Select', shouldSort: false
                });
                const itemsArray = Array.isArray(items) ? items : (items && Array.isArray(items.data) ? items.data : []);
                element.choicesInstance.setChoices(itemsArray.map(i => ({
                    value: i[valueField], label: i[textField], selected: selectedValue && selectedValue == i[valueField], disabled: false
                })), 'value', 'label', true);
                if (selectedValue) element.choicesInstance.setChoiceByValue(selectedValue.toString());
            }

            document.addEventListener('DOMContentLoaded', function () {
                const initialLine = document.querySelector('.product-line');
                if (initialLine) addLineEventListeners(initialLine);
            });

            // Load stations for setup modal
            function loadSetupStations() {
                let apiUrl = AUTH_ROLE === 'admin' ? '/api/stations' :
                    AUTH_ROLE === 'employee' ? `/api/stations_emp/${AUTH_USER_ID}` :
                        `/api/stations/${AUTH_USER_ID}`;

                $.ajax({
                    url: apiUrl,
                    method: 'GET',
                    success: (resp) => {
                        let select = $('#setup_station_id');
                        select.empty().append('<option value="">Select Station...</option>');
                        resp.forEach(station => {
                            select.append(`<option value="${station.id}">${station.name}</option>`);
                        });
                    }
                });
            }

            // Load products for setup modal
            function loadSetupProducts() {
                $.ajax({
                    url: '/api/products/lubricants',
                    method: 'GET',
                    success: (products) => {
                        let select = $('#setup_product_id');
                        select.empty().append('<option value="">Select Product...</option>');
                        products.forEach(product => {
                            select.append(`<option value="${product.id}">${product.name}</option>`);
                        });
                    }
                });
            }

            // Setup Inventory Button Click
            $('#setupInventoryBtn').on('click', function () {
                loadSetupStations();
                loadSetupProducts();
                $('#inventorySetupModal').modal('show');
            });

            // Save Inventory Setup
            $('#saveInventorySetup').on('click', function () {
                const stationId = $('#setup_station_id').val();
                const productId = $('#setup_product_id').val();
                const quantity = $('#setup_quantity').val();
                const price = $('#setup_price').val();
                const date = $('#setup_date').val();

                if (!stationId || !productId || !quantity || !price || !date) {
                    showToast('❌ Please fill all fields!', true);
                    return;
                }

                const saveBtn = $('#saveInventorySetup');
                const originalText = saveBtn.html();
                saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

                // ✅ Use FormData instead of JSON (for CSRF)
                const formData = new FormData();
                formData.append('station_id', parseInt(stationId));
                formData.append('product_id', parseInt(productId));
                formData.append('quantity', parseFloat(quantity));
                formData.append('buying_price', parseFloat(price));
                formData.append('date', date);
                formData.append('_token', $('input[name="_token"]').val()); // CSRF token
                console.log('Sending data:', {
                    station_id: stationId,
                    product_id: productId,
                    quantity: quantity,
                    buying_price: price,
                    date: date
                });

                $.ajax({
                    url: '/api/lubes/inventory/setup',
                    method: 'POST',
                    data: formData,
                    processData: false,  // ✅ Important for FormData
                    contentType: false,   // ✅ Important for FormData
                    success: function (response) {
                        showToast('✅ ' + response.message);
                        $('#inventorySetupModal').modal('hide');
                        $('#inventorySetupForm')[0].reset();

                        if (inventoryTable) {
                            inventoryTable.ajax.reload(null, false);
                        }

                        saveBtn.prop('disabled', false).html(originalText);
                    },
                    error: function (xhr) {
                        console.log('Error Response:', xhr);
                        let errorMsg = 'Failed to save inventory';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                            } else if (xhr.responseJSON.error) {
                                errorMsg = xhr.responseJSON.error;
                            } else if (xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                        }

                        showToast('❌ ' + errorMsg, true);
                        saveBtn.prop('disabled', false).html(originalText);
                    }
                });
            });


            // Initialize datepicker for setup modal
            new AirDatepicker('#setup_date', {
                autoClose: true,
                dateFormat: 'yyyy-MM-dd',
                defaultDate: new Date()
            });

        </script>
    @endsection