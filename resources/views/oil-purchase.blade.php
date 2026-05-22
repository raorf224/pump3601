@extends('partials.layouts.master')

@section('title', 'Oil-Purchase | ' . Auth::user()->full_name)
@section('title-sub', 'Admin')
@section('pagetitle', 'Oil Purchase')
@section('css')
    <link rel="stylesheet" href="assets/libs/prismjs/themes/prism-coy.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <style>
        .payment-method-card {
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 15px;
            margin: 5px;
            background: #f8f9fa;
        }

        .payment-method-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .payment-method-card.selected {
            border: 2px solid #0d6efd !important;
            background: #e7f1ff !important;
        }

        .payment-method-icon i {
            font-size: 2rem;
        }

        .shortage-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .nav-tabs .nav-link {
            font-weight: 500;
            color: #6c757d;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom-color: #0d6efd;
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-4" id="oilTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="purchase-tab" data-bs-toggle="tab"
                                data-bs-target="#purchase" type="button" role="tab" aria-controls="purchase"
                                aria-selected="true">Oil Purchase</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history"
                                type="button" role="tab">History</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="oilTabsContent">
                        <!-- Purchase Tab -->
                        <div class="tab-pane fade show active" id="purchase" role="tabpanel" aria-labelledby="purchase-tab"
                            tabindex="0">
                            <div class="accordion accordion-primary accordion-border-box mb-4" id="purchaseAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingPurchase">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#purchaseFormCollapse" aria-expanded="false"
                                            aria-controls="purchaseFormCollapse">
                                            <i class="bi bi-basket2 me-2"></i> New Oil Purchase
                                        </button>
                                    </h2>
                                    <div id="purchaseFormCollapse" class="accordion-collapse collapse"
                                        data-bs-parent="#purchaseAccordion">
                                        <div class="accordion-body">
                                            <form id="purchaseForm">
                                                <div class="row mb-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Station</label>
                                                        <select class="form-select" id="station" name="station_id">
                                                            <option value="">Search Station...</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">Shift</label>
                                                        <select class="form-select" id="shift" name="shift_id">
                                                            <option value="">Select Shift...</option>
                                                        </select>
                                                    </div>

                                                    <!-- New Oil Purchase Form me yeh field add karo -->
                                                    <div class="col-md-4">
                                                        <label class="form-label required-label">Product</label>
                                                        <select class="form-select" id="product" name="product_id" required>
                                                            <option value="">Select Product...</option>
                                                            <!-- Products will be loaded dynamically -->
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">Supplier</label>
                                                        <select class="form-select" id="supplier" name="supplier_id">
                                                            <option value="">Search Supplier...</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">Order Date</label>
                                                        <input type="text" class="form-control" id="order_date"
                                                            name="order_date" placeholder="Select a date">
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">Payment Status</label>
                                                        <select class="form-select" id="payment_status"
                                                            name="payment_status">
                                                            <option value="">Select Status...</option>
                                                            <!-- <option value="paid">Paid</option> -->
                                                            <!-- <option value="not_paid">Not Paid</option> -->
                                                            <option value="partial" selected>Partial</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label">Order Quantity</label>
                                                        <input type="number" step="0.01" class="form-control" id="qty"
                                                            name="qty" placeholder="Ordered quantity">
                                                    </div>



                                                    <div class="col-md-4">
                                                        <label class="form-label">Rate</label>
                                                        <input type="number" step="0.01" class="form-control" id="rate"
                                                            name="rate" placeholder="Per liter rate">
                                                    </div>

                                                    <!-- <div class="col-md-4">
                                                                                                                                                                            <label class="form-label">Invoice No</label>
                                                                                                                                                                            <input type="text" class="form-control" id="invoice_no"
                                                                                                                                                                                name="invoice_no" placeholder="Invoice number">
                                                                                                                                                                        </div>

                                                                                                                                                                        <div class="col-md-4">
                                                                                                                                                                            <label class="form-label">Reference Number</label>
                                                                                                                                                                            <input type="text" class="form-control" id="ref_num" name="ref_num"
                                                                                                                                                                                placeholder="Reference number">
                                                                                                                                                                        </div> -->

                                                    <!-- ✅ STOCK UPDATE HIDDEN - ALWAYS 0 AT PURCHASE TIME -->
                                                    <input type="hidden" id="stock_update" name="stock_update" value="0">

                                                    <input type="hidden" id="created_by" name="created_by"
                                                        value="{{ Auth::id() }}">
                                                </div>

                                                <div class="d-flex justify-content-end">
                                                    <button type="reset" class="btn btn-light me-2">Reset</button>
                                                    <button type="submit" class="btn btn-primary">Save Purchase
                                                        Order</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Current Inventory</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table text-nowrap align-middle" id="inventoryTable">
                                            <thead>
                                                <tr>
                                                    <th>S.No</th>
                                                    <th>Site</th>
                                                    <th>Tank</th>
                                                    <th>Oil Type</th>
                                                    <th>Stock (Liters)</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="6" class="text-center">Loading...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- History Tab -->
                        <div class="tab-pane fade" id="history" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table text-nowrap align-middle" id="historyTable">
                                            <thead>
                                                <tr>
                                                    <th>Order Date</th>
                                                    <th>Supplier</th>
                                                    <th>Station</th>
                                                    <th>Product</th>
                                                    <th>Order Qty</th>
                                                    <th>Rate</th>
                                                    <th>Total</th>
                                                    <th>Received Qty</th>
                                                    <th>Status</th>
                                                    <th>Invoice #</th>
                                                    <th class="text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="11" class="text-center">No history...</td>
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
    </div>

    <!-- Order Receive Modal -->
    <div class="modal fade" id="receiveOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Receive Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="receiveOrderId">
                    <input type="hidden" id="receiveProductId">
                    <input type="hidden" id="receiveStationId">

                    <!-- Basic Information - CORRECTED LOGIC -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label required-label">Receiving Date</label>
                            <input type="text" class="form-control" id="receiveDate" required>
                        </div>

                        <!-- This Receive Quantity -->
                        <div class="col-md-4">
                            <label class="form-label required-label">This Receive Quantity</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="thisReceiveQty" required
                                    placeholder="Quantity being received now" min="0.01">
                                <span class="input-group-text">Liters</span>
                            </div>
                            <small class="text-muted" id="maxQtyInfo"></small>
                        </div>

                        <!-- Shortage in This Receive -->
                        <div class="col-md-4">
                            <label class="form-label">Shortage in This Receive</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="thisReceiveShortage" value="0"
                                    min="0" placeholder="Shortage in this batch" oninput="calculateNetReceived()">
                                <span class="input-group-text">L</span>
                            </div>
                            <small class="text-muted">Shortage will be deducted from this receive</small>
                        </div>
                    </div>

                    <!-- Net Received After Shortage -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <!-- <label class="form-label">Invoice Number</label>
                                                                                                                <input type="text" class="form-control" id="receiveInvoiceNo"
                                                                                                                    placeholder="Enter invoice number"> -->
                        </div>
                        <div class="col-md-4">
                            <!-- <label class="form-label">Reference Number</label>
                                                                                                                <input type="text" class="form-control" id="receiveRefNum" placeholder="Enter reference number"> -->
                        </div>

                        <!-- Display Net Received -->
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body py-2">
                                    <h6 class="card-title mb-1">
                                        <i class="bi bi-calculator me-2"></i>Net Received This Time
                                    </h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-4 fw-bold text-primary" id="netReceivedDisplay">0.00</span>
                                        <span class="text-muted">Liters</span>
                                    </div>
                                    <small class="text-muted">
                                        Formula: <span id="calculationFormula">0 - 0 = 0</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ NEW ROW: Vehicle Number & Invoice Details -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label required-label">Vehicle Number</label>
                            <input type="text" class="form-control" id="vehcileNumber" placeholder="Enter vehicle number"
                                required>
                            <small class="text-muted">Example: ABC-1234 or LZN-01-2345</small>
                        </div>

                        <!-- ✅ ADD THIS NEW SHIFT FIELD -->
                        <div class="col-md-4">
                            <label class="form-label required-label">Shift</label>
                            <select class="form-select" id="receiveShiftId" required>
                                <option value="">Select Shift...</option>
                            </select>
                            <small class="text-muted">Select the shift during which this oil is received</small>
                        </div>


                        <div class="col-md-4">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" id="receiveInvoiceNo"
                                placeholder="Enter invoice number (optional)">
                            <small class="text-muted">Optional field</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Reference Number</label>
                            <input type="text" class="form-control" id="receiveRefNum"
                                placeholder="Enter reference number (optional)">
                            <small class="text-muted">Optional field</small>
                        </div>
                    </div>

                    <!-- ✅ NEW ROW: Invoice Image Upload -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Invoice Image (Optional)</label>
                            <input type="file" class="form-control" id="invoiceImage"
                                accept="image/jpeg,image/png,image/jpg,application/pdf">
                            <small class="text-muted">Upload invoice image (JPEG, PNG, JPG, PDF - Max 2MB)</small>
                            <div id="imagePreview" class="mt-2" style="display: none;">
                                <img id="previewImg" src="#" alt="Preview" style="max-width: 200px; max-height: 150px;"
                                    class="img-thumbnail">
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Card -->
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info bg-opacity-10">
                            <h6 class="mb-0">
                                <i class="bi bi-clipboard-data me-2"></i>Order Summary
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6 class="text-muted">Ordered</h6>
                                        <h4 id="orderedQtyDisplay">0</h4>
                                        <small class="text-muted">Liters</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6 class="text-muted">Already Received</h6>
                                        <h4 id="alreadyReceivedDisplay">0</h4>
                                        <small class="text-muted">Liters</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6 class="text-muted">Remaining</h6>
                                        <h4 id="remainingQtyDisplay">0</h4>
                                        <small class="text-muted">Liters</small>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Tanks Distribution Section -->
                    <div id="tanksDistributionSection">
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-hourglass-split fs-1"></i>
                            <p class="mt-2">Loading tanks distribution...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveReceive">Receive Order</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Status Modal -->
    <div class="modal fade" id="paymentStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalTitle">Mark Payment as Paid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="paymentPurchaseId">
                    <input type="hidden" id="currentPaymentStatus">

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Station</label>
                            <input type="text" class="form-control" id="paymentStationName" readonly>
                            <input type="hidden" id="paymentStationId">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label required-label">Select Shift</label>
                            <select class="form-select" id="paymentShift">
                                <option value="">Select Shift...</option>
                            </select>
                            <small class="text-muted">Select the shift for which payment is being made</small>
                        </div>

                        <!-- Partial Payment Fields (Initially Hidden) -->
                        <div id="partialPaymentFields" style="display: none;">
                            <div class="col-md-12 mb-3">
                                <label class="form-label required-label">Payment Date</label>
                                <input type="text" class="form-control" id="paymentDate" placeholder="Select payment date">
                                <small class="text-muted">Select the date when payment was made</small>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label required-label">Payment Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs</span>
                                    <input type="number" class="form-control" id="paymentAmount"
                                        placeholder="Enter amount to pay" step="0.01" min="0.01">
                                </div>
                                <small class="text-muted">Maximum: Rs. <span id="maxPaymentAmount"
                                        class="fw-bold">0.00</span></small>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label required-label">Payment Method</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="paymentMethod" id="cashMethod"
                                            value="cash" checked>
                                        <label class="form-check-label" for="cashMethod">
                                            Cash
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="paymentMethod" id="bankMethod"
                                            value="bank">
                                        <label class="form-check-label" for="bankMethod">
                                            Bank
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Account Field (Initially Hidden) -->
                            <div class="col-md-12 mb-3" id="bankAccountField" style="display: none;">
                                <label class="form-label required-label">Select Bank Account</label>
                                <select class="form-select" id="bankAccount">
                                    <option value="">Select Bank Account...</option>
                                </select>
                                <small class="text-muted">Select the bank account from which payment is made</small>
                            </div>

                            <!-- Current Purchase Info -->
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Order Summary:</strong><br>
                                    Total Order Amount: Rs. <span id="totalOrderAmount">0.00</span><br>
                                    Remaining Amount: Rs. <span id="remainingOrderAmount" class="fw-bold">0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Not Paid Payment Info -->
                        <div id="notPaidInfo" style="display: none;">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    This will mark the payment status as <strong>Paid</strong> for this purchase order.
                                </div>
                            </div>
                        </div>

                        <!-- View Payment History Section (Initially Hidden) -->
                        <div id="viewPaymentSection" style="display: none;">
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    <i class="bi bi-eye me-2"></i>
                                    This purchase is already paid. You can view payment history below.
                                </div>
                            </div>

                            <!-- View Payment History Button -->
                            <div class="col-md-12 mb-3">
                                <button type="button" class="btn btn-outline-info w-100" id="viewPaymentHistoryBtn">
                                    <i class="bi bi-clock-history me-2"></i> View Payment History
                                </button>
                            </div>

                            <!-- Payment History Table (Initially Hidden) -->
                            <div id="paymentHistoryTable" style="display: none;">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Method</th>
                                                <th>Amount</th>
                                                <th>Shift</th>
                                                <th>Total Paid</th>
                                            </tr>
                                        </thead>
                                        <tbody id="paymentHistoryBody">
                                            <!-- Payment history will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="savePaymentStatus" style="display: none;">Mark as
                        Paid</button>
                    <button type="button" class="btn btn-success" id="addPaymentBtn" style="display: none;">Add
                        Payment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice & Ref Number Modal -->
    <div class="modal fade" id="invoiceRefModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Invoice & Reference Number</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="invoicePurchaseId">

                    <div class="mb-3">
                        <label class="form-label">Reference Number <small class="text-muted">(Optional)</small></label>
                        <input type="text" class="form-control" id="modalRefNum" placeholder="Enter reference number">
                    </div>

                    <div class="mb-3">
                        <label class="form-label required-label">Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modalInvoiceNo" required
                            placeholder="Enter invoice number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveInvoiceRef">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Combined Shortage Payment & History Modal -->
    <div class="modal fade" id="shortagePaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                        Shortage Management
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="shortagePurchaseId">
                    <input type="hidden" id="shortageStationId">

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-4" id="shortageTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="payment-tab" data-bs-toggle="tab"
                                data-bs-target="#paymentTab" type="button" role="tab">
                                <i class="bi bi-cash-stack me-1"></i> Make Payment
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab-shortage" data-bs-toggle="tab"
                                data-bs-target="#historyTab" type="button" role="tab">
                                <i class="bi bi-clock-history me-1"></i> Payment History
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- ==================== PAYMENT TAB ==================== -->
                        <div class="tab-pane fade show active" id="paymentTab" role="tabpanel">
                            <!-- Pending Receives Container -->
                            <div id="pendingReceivesContainer"></div>

                            <!-- Shortage Summary Card -->
                            <div class="card shortage-card mb-4 bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 text-center">
                                            <div class="shortage-label text-muted">Total Shortage</div>
                                            <div class="shortage-amount fs-3 fw-bold text-warning" id="modalTotalShortage">0
                                            </div>
                                            <small>Liters</small>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="shortage-label text-muted">Rate per Liter</div>
                                            <div class="shortage-amount fs-3 fw-bold text-primary" id="modalRate">0</div>
                                            <small>Rs.</small>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="shortage-label text-muted">Total Amount</div>
                                            <div class="shortage-amount fs-3 fw-bold text-success" id="modalTotalAmount">0
                                            </div>
                                            <small>Rs.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Section (Initially Hidden) -->
                            <div id="shortagePaymentSection" style="display: none;">
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Please select shift and payment method to record shortage payment.
                                </div>

                                <!-- Shift Selection -->
                                <div class="mb-3">
                                    <label class="form-label required-label">Select Shift</label>
                                    <select class="form-select" id="shortageShift">
                                        <option value="">Select Shift...</option>
                                    </select>
                                    <small class="text-muted">Select the shift during which this payment is being
                                        made</small>
                                </div>

                                <!-- Payment Method Selection -->
                                <div class="mb-3">
                                    <label class="form-label required-label">Payment Method</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="payment-method-card text-center border rounded p-3 cursor-pointer"
                                                data-method="cash" onclick="selectShortagePaymentMethod('cash')">
                                                <div class="payment-method-icon fs-1">
                                                    <i class="bi bi-cash-stack"></i>
                                                </div>
                                                <h6 class="mt-2">Cash</h6>
                                                <small class="text-muted">Direct Cash Payment</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="payment-method-card text-center border rounded p-3 cursor-pointer"
                                                data-method="bank" onclick="selectShortagePaymentMethod('bank')">
                                                <div class="payment-method-icon fs-1">
                                                    <i class="bi bi-bank"></i>
                                                </div>
                                                <h6 class="mt-2">Bank</h6>
                                                <small class="text-muted">Bank Transfer</small>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="selectedPaymentMethod" value="">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required-label">Payment Amount (Rs.)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rs</span>
                                        <input type="number" step="0.01" class="form-control" id="shortagePaymentAmount"
                                            placeholder="Enter amount to pay" min="0.01">
                                    </div>
                                    <small class="text-muted">Maximum: Rs. <span id="maxPaymentAmount"
                                            class="fw-bold">0.00</span></small>
                                </div>

                                <!-- Bank Account Selection (Initially Hidden) -->
                                <div class="mb-3" id="shortageBankAccountField" style="display: none;">
                                    <label class="form-label required-label">Select Bank Account</label>
                                    <select class="form-select" id="shortageBankAccount">
                                        <option value="">Select Bank Account...</option>
                                    </select>
                                    <small class="text-muted">Select the bank account to receive payment</small>
                                </div>

                                <!-- Payment Summary -->
                                <div class="alert alert-success mt-3" id="paymentSummary" style="display: none;">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Payment Summary:</strong><br>
                                    Amount to be paid: Rs. <span id="paymentSummaryAmount">0</span><br>
                                    Method: <span id="paymentSummaryMethod">-</span>
                                </div>
                            </div>

                            <!-- Already Paid Message -->
                            <div id="alreadyPaidMessage" class="alert alert-success" style="display: none;">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>All Shortages Paid!</strong><br>
                                All shortage amounts for this purchase have been paid back.
                            </div>
                        </div>

                        <!-- ==================== HISTORY TAB ==================== -->
                        <div class="tab-pane fade" id="historyTab" role="tabpanel">
                            <div id="shortageHistoryBody">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading payment history...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveShortagePayment" style="display: none;">
                        <i class="bi bi-check-circle me-2"></i> Record Payment
                    </button>
                </div>
            </div>
        </div>
    </div>


    </main>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="assets/libs/prismjs/prism.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        const apiBase = "api";
        const AUTH_USER_ID = "{{ Auth::id() }}";
        const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";

        let editingId = null;
        let supplierChoices, stationChoices, shiftChoices, productChoices;
        let selectedStationId = null;
        let currentReceivingOrderId = null;

        // ✅ Initialize everything
        $(document).ready(function () {
            // Load initial data
            loadSuppliers();
            loadStations();
            loadHistory();
            loadInventory();


            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Initialize date pickers
            flatpickr("#order_date", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "F j, Y",
                allowInput: true,
                defaultDate: "today"
            });


            // Initialize payment date picker
            flatpickr("#paymentDate", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "F j, Y",
                allowInput: true,
                defaultDate: "today"
            });


        });

        // ✅ Receive modal date picker
        flatpickr("#receiveDate", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
            allowInput: true,
            defaultDate: "today"
        });

        // Auto-expand accordion on first load
        try {
            const purchaseCollapseEl = document.getElementById('purchaseFormCollapse');
            if (purchaseCollapseEl) {
                const bsCollapse = new bootstrap.Collapse(purchaseCollapseEl, {
                    toggle: false
                });
                bsCollapse.show();
            }
        } catch (e) {
            console.warn('Could not auto-show accordion', e);
        }

        // Toastr configuration
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        // Re-initialize tooltips when modal is shown
        $('#receiveOrderModal').on('shown.bs.modal', function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        // ✅ Load products for station on station selection
        function loadProductsForStation(stationId) {
            if (!stationId) {
                $('#product').html('<option value="">Select Product...</option>');
                if (productChoices) {
                    productChoices.clearStore();
                    productChoices.setChoices([{ value: '', label: 'Select Product...' }], 'value', 'label', true);
                }
                return;
            }

            $.ajax({
                url: `${apiBase}/station-products/${stationId}`,
                method: 'GET',
                success: function (response) {
                    let products = [];

                    // Handle different response formats
                    if (Array.isArray(response)) {
                        products = response;
                    } else if (response && response.data && Array.isArray(response.data)) {
                        products = response.data;
                    } else if (response && Array.isArray(response)) {
                        products = response;
                    }

                    console.log('Products loaded:', products); // Debug log

                    let productSelect = $("#product");
                    productSelect.empty();

                    // Create options array for Choices.js
                    const productOptions = [{ value: '', label: 'Select Product...' }];

                    if (products && products.length > 0) {
                        products.forEach(product => {
                            productOptions.push({
                                value: product.id,
                                label: product.name || `Product ${product.id}`
                            });
                        });
                    } else {
                        productOptions.push({ value: '', label: 'No products found' });
                    }

                    // Initialize or update Choices.js for product
                    if (productChoices) {
                        productChoices.destroy();
                    }

                    productChoices = new Choices("#product", {
                        searchEnabled: true,
                        itemSelectText: '',
                        shouldSort: false,
                        placeholderValue: "Select Product...",
                        removeItemButton: true,
                        choices: productOptions
                    });
                },
                error: function (err) {
                    console.error('Failed to load products:', err);
                    $('#product').html('<option value="">Error loading products</option>');
                    if (productChoices) {
                        productChoices.destroy();
                    }
                }
            });
        }

        // ✅ Load suppliers based on selected station
        function loadSuppliersByStation(stationId) {
            if (!stationId) {
                console.error('No station selected');
                return;
            }

            $.ajax({
                url: `${apiBase}/accounts/station/${stationId}/type/supplier`,
                method: 'GET',
                success: function (data) {
                    let supplierSelect = $("#supplier");
                    supplierSelect.empty().append(`<option value="">Select Supplier...</option>`);

                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(s => {
                            supplierSelect.append(
                                `<option value="${s.id}">${s.name} - ${s.phone || 'No Phone'}</option>`
                            );
                        });
                    } else {
                        supplierSelect.append(`<option value="">No suppliers found for this station</option>`);
                    }

                    if (supplierChoices) supplierChoices.destroy();
                    supplierChoices = new Choices("#supplier", {
                        searchEnabled: true,
                        itemSelectText: '',
                        shouldSort: false,
                        placeholderValue: "Select Supplier...",
                        removeItemButton: true
                    });
                },
                error: function (err) {
                    console.error('Failed to load suppliers for station', err);
                    let supplierSelect = $("#supplier");
                    supplierSelect.empty().append(`<option value="">Error loading suppliers</option>`);

                    if (supplierChoices) supplierChoices.destroy();
                    supplierChoices = new Choices("#supplier", {
                        searchEnabled: false,
                        itemSelectText: '',
                        shouldSort: false,
                        placeholderValue: "Error loading suppliers",
                        removeItemButton: false
                    });
                }
            });
        }

        // Station change par suppliers load karein
        $(document).on('change', '#station', function () {
            const stationId = $(this).val();
            if (stationId) {
                loadSuppliersByStation(stationId);
            } else {
                // Clear suppliers if no station selected
                let supplierSelect = $("#supplier");
                supplierSelect.empty().append(`<option value="">Select Station First...</option>`);
                if (supplierChoices) supplierChoices.destroy();
            }
        });

        // ✅ Load suppliers (role-aware)
        function loadSuppliers() {
            let url = (AUTH_ROLE === 'admin') ? `${apiBase}/accounts/category/supplier` :
                `${apiBase}/accounts/category/supplier/${AUTH_USER_ID}`;

            $.ajax({
                url: url,
                method: 'GET',
                success: function (data) {
                    let supplierSelect = $("#supplier");
                    supplierSelect.empty().append(`<option value="">Search Supplier...</option>`);
                    (Array.isArray(data) ? data : []).forEach(s => supplierSelect.append(
                        `<option value="${s.id}">${s.name}</option>`));

                    if (supplierChoices) supplierChoices.destroy();
                    supplierChoices = new Choices("#supplier", {
                        searchEnabled: true,
                        itemSelectText: '',
                        shouldSort: false,
                        placeholderValue: "Search Supplier...",
                        removeItemButton: true
                    });
                },
                error: function (err) {
                    console.error('Failed to load suppliers', err);
                }
            });
        }

        // ✅ Load stations
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
                method: 'GET',
                success: function (data) {
                    let stationSelect = $("#station");
                    stationSelect.empty().append(`<option value="">Search Station...</option>`);
                    (Array.isArray(data) ? data : []).forEach(st => stationSelect.append(
                        `<option value="${st.id}">${st.name}</option>`));

                    if (stationChoices) stationChoices.destroy();
                    stationChoices = new Choices("#station", {
                        searchEnabled: true,
                        itemSelectText: '',
                        shouldSort: false,
                        placeholderValue: "Search Station...",
                        removeItemButton: true
                    });
                },
                error: function (err) {
                    console.error('Failed to load stations', err);
                }
            });
        }

        // ✅ Load shifts for selected station
        function loadShifts(stationId) {
            if (!stationId) {
                $('#shift').html('<option value="">Select Shift...</option>');
                if (shiftChoices) {
                    shiftChoices.setChoices([{ value: '', label: 'Select Shift...' }], 'value', 'label', true);
                }
                return;
            }

            // ✅ Clear current options
            $('#shift').html('<option value="">Loading shifts...</option>');

            $.ajax({
                url: `/api/stations/${stationId}/open-shifts`,
                method: 'GET',
                success: function (resp) {
                    let shiftSelect = $("#shift");
                    shiftSelect.empty().append(`<option value="">Select Shift...</option>`);

                    if (resp && resp.data && Array.isArray(resp.data) && resp.data.length > 0) {
                        resp.data.forEach(shift => {
                            // ✅ Format time properly
                            const startTime = shift.start_time ? shift.start_time.split(' ')[0] : '';
                            shiftSelect.append(
                                `<option value="${shift.id}">Shift #${shift.shift_no} (${startTime})</option>`
                            );
                        });
                    } else {
                        shiftSelect.append(`<option value="">No open shifts found</option>`);
                        toastr.info('No open shifts available for this station');
                    }

                    // ✅ Re-initialize Choices.js
                    if (shiftChoices) shiftChoices.destroy();
                    shiftChoices = new Choices("#shift", {
                        searchEnabled: true,
                        itemSelectText: '',
                        shouldSort: false,
                        placeholderValue: "Select Shift...",
                        removeItemButton: true
                    });
                },
                error: function (err) {
                    console.error('Failed to load shifts:', err);
                    $('#shift').html('<option value="">Error loading shifts</option>');

                    // ✅ Re-initialize Choices.js with error state
                    if (shiftChoices) shiftChoices.destroy();
                    shiftChoices = new Choices("#shift", {
                        searchEnabled: false,
                        itemSelectText: '',
                        shouldSort: false,
                        placeholderValue: "Error loading shifts",
                        removeItemButton: false
                    });

                    toastr.error('Failed to load shifts for this station');
                }
            });
        }

        // ✅ Event listener for station change
        $(document).on('change', '#station', function () {
            selectedStationId = $(this).val();
            if (selectedStationId) {
                loadShifts(selectedStationId);
                loadProductsForStation(selectedStationId); // ✅ NEW: Load products too
            } else {
                $('#shift').html('<option value="">Select Shift...</option>');
                $('#product').html('<option value="">Select Product...</option>');
                if (shiftChoices) shiftChoices.destroy();
                if (productChoices) productChoices.destroy();
            }
        });


        // ✅ Save or update oil purchase - UPDATED
        $('#purchaseForm').on('submit', function (e) {
            e.preventDefault();

            // Get form data - RECEIVING FIELDS REMOVED
            let formData = {
                supplier_id: $('#supplier').val() || null,
                station_id: $('#station').val() || null,
                shift_id: $('#shift').val() || null,
                product_id: $('#product').val() || null,
                order_date: $('#order_date').val() || null,
                payment_status: $('#payment_status').val() || null,
                rate: $('#rate').val() || null,
                qty: $('#qty').val() || null, // Order quantity
                invoice_no: $('#invoice_no').val() || null,
                ref_num: $('#ref_num').val() || null,
                stock_update: 0, // ✅ ALWAYS 0 at purchase time
                created_by: AUTH_USER_ID
            };

            // Validate at least some data is provided
            if (!formData.supplier_id || !formData.station_id || !formData.qty || !formData.product_id) {
                toastr.warning('Please fill in Supplier, Station, Product and Order Quantity fields');
                return;
            }

            if (editingId) {
                // 🔄 UPDATE
                $.ajax({
                    url: `${apiBase}/oil-purchases/${editingId}`,
                    type: 'PUT',
                    data: formData,
                    success: function (response) {
                        toastr.success('Purchase Updated Successfully!');
                        loadHistory();
                        $('#purchaseForm')[0].reset();
                        editingId = null;
                        resetChoices();
                        location.reload();
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        toastr.error('Update failed!');
                    }
                });
            } else {
                // 🆕 CREATE
                $.ajax({
                    url: `${apiBase}/oil-purchases`,
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        toastr.success('Purchase Order Created Successfully!');
                        loadHistory();
                        $('#purchaseForm')[0].reset();
                        resetChoices();
                        // Agar phir bhi reload karna hai toh thoda delay karo
                        setTimeout(function () {
                            location.reload();
                        }, 1500);
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        toastr.error('Save failed!');
                    }
                });
            }
        });

        // ✅ Reset Choices instances after form reset
        function resetChoices() {
            if (supplierChoices) supplierChoices.clearStore();
            if (stationChoices) {
                stationChoices.clearStore();
                selectedStationId = null;
            }
            if (shiftChoices) shiftChoices.clearStore();
            if (productChoices) productChoices.clearStore();
        }

        // ✅ Load Current Inventory
        async function loadInventory() {
            try {
                let tanks = [];

                if (AUTH_ROLE === 'admin') {
                    const res = await fetch('/api/tanks');
                    const json = await res.json();
                    tanks = Array.isArray(json) ? json : (json.data || []);
                } else if (AUTH_ROLE === 'employee') {
                    const sres = await fetch(`/api/stations_emp/${AUTH_USER_ID}`);
                    const stations = await sres.json();
                    if (Array.isArray(stations) && stations.length) {
                        const calls = stations.map(s =>
                            fetch(`/api/stationwise/${s.id}`).then(r => r.ok ? r.json() : []).catch(() => [])
                        );
                        const results = await Promise.all(calls);
                        tanks = results.flatMap(r => Array.isArray(r) ? r : (r.data || []));
                    }
                } else {
                    const res = await fetch(`/api/user-tanks/${AUTH_USER_ID}`);
                    const json = await res.json();
                    tanks = Array.isArray(json) ? json : (json.data || []);
                }

                let rows = "";
                if (tanks.length) {
                    tanks.forEach((t, i) => {
                        let statusBadge = (t.status === "active" || String(t.status).toLowerCase() === 'active') ?
                            `<span class="badge bg-success">Active</span>` :
                            `<span class="badge bg-danger">Inactive</span>`;

                        rows += `
                                                                                                                                                                                        <tr>
                                                                                                                                                                                            <td>${i + 1}</td>
                                                                                                                                                                                            <td>${t.station_name || '-'}</td>
                                                                                                                                                                                            <td>${t.name || '-'}</td>
                                                                                                                                                                                            <td>${t.product_name || "-"}</td>
                                                                                                                                                                                            <td>${t.current_level || 0}</td>
                                                                                                                                                                                            <td>${statusBadge}</td>
                                                                                                                                                                                        </tr>`;
                    });
                } else {
                    rows = `<tr><td colspan="6" class="text-center">No tanks found</td></tr>`;
                }

                $("#inventoryTable tbody").html(rows);
            } catch (e) {
                console.error('Failed to load inventory', e);
                $("#inventoryTable tbody").html(
                    `<tr><td colspan="6" class="text-center">Failed to load inventory</td></tr>`);
            }
        }

        // ✅ Load purchase history (role-aware) - WITH SHORTAGE FEATURE
        async function loadHistory() {
            try {
                let purchases = [];
			
                if (AUTH_ROLE === 'admin') {
                    const res = await fetch(`/api/oil-purchases`);
                    const json = await res.json();
                    purchases = Array.isArray(json) ? json : (json.data || []);
					
                } else if (AUTH_ROLE === 'employee') {
                    const sres = await fetch(`/api/stations_emp/${AUTH_USER_ID}`);
                    const stations = await sres.json();
                    if (Array.isArray(stations) && stations.length) {
						
                        const calls = stations.map(s =>
                            fetch(`/api/oil-purchases/station/${s.id}`).then(r => r.ok ? r.json() : []).catch(() => [])
                        );
                        const results = await Promise.all(calls);
                        purchases = results.flatMap(r => Array.isArray(r) ? r : (r.data || []));
                    }
                } else {
                    const res = await fetch(`/api/oil-purchases/${AUTH_USER_ID}`);
                    const json = await res.json();
                    purchases = Array.isArray(json) ? json : (json.data || []);
                }

                let rows = "";
                if (purchases && purchases.length) {
                    for (const o of purchases) {
                        console.log(`Purchase ${o.id}: recive_status = ${o.recive_status}, recieved_qty = ${o.recieved_qty}`);
                        const total = (o.qty || 0) * (o.rate || 0);
                        const shiftInfo = o.shift_no ? `Shift #${o.shift_no}` : '-';

                        // ✅ DIRECT FROM API RESPONSE - No extra API call needed
                        let totalShortage = parseFloat(o.total_shortage_sum) || 0;
                        let isShortagePaid = parseInt(o.shortage_paid) === 1;

                        // ✅ Payment status with update button
                        let paymentBadge = "";
                        let paymentBtn = "";

                        if (o.payment_status === "paid") {
                            paymentBadge = `<span class="badge bg-success">Paid</span>`;
                            paymentBtn = `<button class="btn btn-sm btn-info" 
                                                                onclick="updatePaymentStatus(${o.id}, 'paid', ${o.station_id || 0}, '${(o.station_name || '').replace(/'/g, "\\'")}', ${total}, ${o.qty || 0}, ${o.rate || 0})">
                                                                <i class="bi bi-eye me-1"></i> View Payment
                                                            </button>`;
                        } else if (o.payment_status === "partial") {
                            paymentBadge = `<span class="badge bg-warning">Partial</span>`;
                            paymentBtn = `<button class="btn btn-sm btn-warning" 
                                                                onclick="updatePaymentStatus(${o.id}, 'partial', ${o.station_id || 0}, '${(o.station_name || '').replace(/'/g, "\\'")}', ${total}, ${o.qty || 0}, ${o.rate || 0})">
                                                                <i class="bi bi-cash-stack me-1"></i> Mark Paid
                                                            </button>`;
                        } else {
                            paymentBadge = `<span class="badge bg-danger">Not Paid</span>`;
                            paymentBtn = `<button class="btn btn-sm btn-danger" 
                                                                onclick="updatePaymentStatus(${o.id}, '${o.payment_status}', ${o.station_id || 0}, '${(o.station_name || '').replace(/'/g, "\\'")}', ${total}, ${o.qty || 0}, ${o.rate || 0})">
                                                                <i class="bi bi-cash-stack me-1"></i> Mark Paid
                                                            </button>`;
                        }

                        // ✅ SHORTAGE BUTTON - Direct from API response
                        let shortageBtn = '';
                        if (totalShortage > 0) {
                            shortageBtn = `<button class="btn btn-sm ${isShortagePaid ? 'btn-success' : 'btn-warning'} ms-1" 
            onclick="openShortagePaymentModal(${o.id})" 
            title="${isShortagePaid ? 'View shortage payment history' : 'Pay shortage: ' + totalShortage + 'L (Rs. ' + (totalShortage * o.rate).toFixed(2) + ')'}">
            <i class="bi ${isShortagePaid ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-1"></i> 
            ${isShortagePaid ? 'Shortage Paid' : `Shortage (${totalShortage}L)`}
        </button>`;
                        }



                        // ✅ Receive status - FIXED DISPLAY
                        let receiveStatus = "";
                        let receiveBtn = "";

                        const reciveStatusValue = o.recive_status || 'Not-Recived';
                        const receivedQtyDisplay = parseFloat(o.recieved_qty) || 0;

                        if (reciveStatusValue === 'Recived') {
                            receiveStatus = `<span class="badge bg-success">${receivedQtyDisplay.toFixed(2)}L Received</span>`;
                            receiveBtn = `<button class="btn btn-sm btn-info" 
                                                                onclick="openReceiveModal(${o.id}, ${o.station_id || 0}, ${o.qty || 0})" 
                                                                title="View Receive History">
                                                                <i class="bi bi-clock-history me-1"></i> History
                                                            </button>`;
                        } else {
                            receiveStatus = `<span class="badge bg-warning">${receivedQtyDisplay.toFixed(2)}L Received</span>`;
                            receiveBtn = `<button class="btn btn-sm btn-success" 
                                                                onclick="openReceiveModal(${o.id}, ${o.station_id || 0}, ${o.qty || 0})">
                                                                <i class="bi bi-check-circle"></i> Receive
                                                            </button>`;
                        }

                        // ✅ Invoice Button
                        let invoiceBtn = "";

                        if (!o.invoice_no || o.invoice_no === 'N/A' || o.invoice_no === '') {
                            invoiceBtn = `<button class="btn btn-sm btn-outline-warning" onclick="openInvoiceModal(${o.id})" title="Add Invoice">
                                                                <i class="bi bi-receipt me-1"></i> Add Invoice
                                                            </button>`;
                        } else {
                            invoiceBtn = `<span class="badge bg-success">${o.invoice_no}</span><br>
                                                                <small class="text-muted">${o.ref_num || 'No ref'}</small>`;
                        }

                        // ✅ EDIT BUTTON
                        let editBtn = "";

                        if (reciveStatusValue !== 'Recived') {
                            editBtn = `<button class="btn btn-sm btn-primary" onclick="editPurchase(${o.id})" title="Edit">
                                                                <i class="bi bi-pencil"></i> Edit
                                                            </button>`;
                        } else {
                            editBtn = `<button class="btn btn-sm btn-secondary" disabled title="Cannot edit received order">
                                                                <i class="bi bi-pencil"></i> Edit
                                                            </button>`;
                        }

                        // ✅ Table Row with all buttons
                        rows += `
                                                            <tr>
                                                                <td>${o.order_date || '-'}</td>
                                                                <td>${o.supplier_name || "-"}</td>
                                                                <td>${o.station_name || "-"}</td>
                                                                <td>${o.product_name || "-"}</td>
                                                                <td>${o.qty || 0}</td>
                                                                <td>Rs.${o.rate || 0}</td>
                                                                <td>Rs.${total.toFixed(2)}</td>
                                                                <td>${receiveStatus}</td>
                                                                <td>${paymentBadge}</td>
                                                                <td class="text-center">${invoiceBtn}</td>
                                                                <td class="text-center">
                                                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                                        <div class="btn-group btn-group-sm" role="group">
                                                                            ${paymentBtn}
                                                                        </div>
                                                                        <div class="btn-group btn-group-sm" role="group">
                                                                            ${editBtn}
                                                                            <button class="btn btn-sm btn-danger" onclick="deletePurchase(${o.id})" title="Delete">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                            ${receiveBtn}
                                                                        </div>
                                                                        ${shortageBtn}
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        `;
                    }
                } else {
                    rows = `<tr><td colspan="11" class="text-center">No purchase history found</td></tr>`;
                }

                $("#historyTable thead tr").html(`
                                                    <th>Order Date</th>
                                                    <th>Supplier</th>
                                                    <th>Station</th>
                                                    <th>Product</th>
                                                    <th>Order Qty</th>
                                                    <th>Rate</th>
                                                    <th>Total</th>
                                                    <th>Received Qty</th>
                                                    <th>Status</th>
                                                    <th>Invoice # / Ref</th>
                                                    <th class="text-center">Actions</th>
                                                `);

                $("#historyTable tbody").html(rows);
                $('[data-bs-toggle="tooltip"]').tooltip();

            } catch (e) {
                console.error('Failed to load history', e);
                $("#historyTable tbody").html(
                    `<tr><td colspan="11" class="text-center text-danger">Failed to load history: ${e.message}</td></tr>`
                );
            }
        }

        // ✅ Update payment status function
        async function updatePaymentStatus(id, currentStatus, stationId, stationName, totalAmount, orderQty, orderRate) {
            try {
                // Store the data for modal
                window.paymentData = {
                    purchaseId: id,
                    stationId: stationId,
                    stationName: stationName,
                    currentStatus: currentStatus,
                    totalAmount: totalAmount || 0,
                    orderQty: orderQty || 0,
                    orderRate: orderRate || 0
                };

                // First, ALWAYS check existing payments from ammount_paid table
                let existingPayments = [];
                let totalPaid = 0;

                try {
                    const paymentRes = await fetch(`${apiBase}/oil-purchases/${id}/payment-history`);
                    const paymentData = await paymentRes.json();

                    console.log('Payment history response:', paymentData);

                    if (paymentData && paymentData.payments && Array.isArray(paymentData.payments)) {
                        existingPayments = paymentData.payments;

                        // Calculate total debit amount (payments made) - type='debit'
                        totalPaid = existingPayments
                            .filter(p => p.type === 'debit')
                            .reduce((sum, p) => sum + (parseFloat(p.ammount) || 0), 0);

                        console.log('Total debit found:', totalPaid);
                    }
                } catch (err) {
                    console.log('Error fetching payment history:', err);
                }

                const remainingAmount = totalAmount - totalPaid;

                // Set modal values
                $('#paymentPurchaseId').val(id);
                $('#paymentStationId').val(stationId);
                $('#paymentStationName').val(stationName || 'Unknown Station');
                $('#currentPaymentStatus').val(currentStatus);

                // Set payment date to today
                $('#paymentDate').val(new Date().toISOString().split('T')[0]);

                // Clear and load shifts
                $('#paymentShift').html('<option value="">Loading shifts...</option>');

                // Reset all fields
                $('#paymentAmount').val('');
                $('#paymentAmount').attr('max', remainingAmount);
                $('#maxPaymentAmount').text(remainingAmount.toFixed(2));
                $('#cashMethod').prop('checked', true);
                $('#bankAccount').html('<option value="">Select Bank Account...</option>');
                $('#bankAccountField').hide();

                // Hide all sections first
                $('#partialPaymentFields').hide();
                $('#notPaidInfo').hide();
                $('#viewPaymentSection').hide();
                $('#savePaymentStatus').hide();
                $('#addPaymentBtn').hide();

                // Set order amounts
                $('#totalOrderAmount').text(totalAmount.toFixed(2));
                $('#remainingOrderAmount').text(remainingAmount.toFixed(2));

                // Determine what to show based on payment status and existing payments
                if (currentStatus === 'paid' || remainingAmount <= 0) {
                    // CASE 1: Already fully paid - Show view payment history
                    $('#paymentModalTitle').text('View Payment History');
                    $('#viewPaymentSection').show();
                    loadPaymentHistoryForView(id);
                }
                else if (currentStatus === 'partial') {
                    // CASE 2: Partial payment - Show partial payment form
                    $('#paymentModalTitle').text('Make Payment');
                    $('#partialPaymentFields').show();
                    $('#addPaymentBtn').show();
                }
                else {
                    // CASE 3: Not paid - Show simple paid button
                    $('#paymentModalTitle').text('Mark Payment as Paid');
                    $('#notPaidInfo').show();
                    $('#savePaymentStatus').show();
                }

                // Load open shifts for this station
                if (stationId) {
                    $.ajax({
                        url: `/api/stations/${stationId}/open-shifts`,
                        method: 'GET',
                        success: function (resp) {
                            let shiftSelect = $("#paymentShift");
                            shiftSelect.empty().append(`<option value="">Select Shift...</option>`);

                            if (resp && resp.data && Array.isArray(resp.data) && resp.data.length > 0) {
                                resp.data.forEach(shift => {
                                    shiftSelect.append(
                                        `<option value="${shift.id}">Shift #${shift.shift_no} (${shift.start_time})</option>`
                                    );
                                });
                            } else {
                                shiftSelect.append(`<option value="">No open shifts found</option>`);
                            }

                            // For partial payments, also load bank accounts
                            if (currentStatus === 'partial' && remainingAmount > 0) {
                                loadBankAccountsForPayment(stationId);
                            }

                            // Show modal
                            $('#paymentStatusModal').modal('show');
                        },
                        error: function (err) {
                            console.error('Failed to load shifts:', err);
                            $('#paymentShift').html('<option value="">Error loading shifts</option>');

                            if (currentStatus === 'partial' && remainingAmount > 0) {
                                loadBankAccountsForPayment(stationId);
                            }

                            $('#paymentStatusModal').modal('show');
                        }
                    });
                } else {
                    $('#paymentShift').html('<option value="">Station not available</option>');
                    $('#paymentStatusModal').modal('show');
                }
            } catch (error) {
                console.error('Error in updatePaymentStatus:', error);
                toastr.error('Failed to load payment details');
            }
        }

        // ✅ Load payment history for view only
        async function loadPaymentHistoryForView(purchaseId) {
            try {
                const response = await fetch(`${apiBase}/oil-purchases/${purchaseId}/payment-history`);
                const data = await response.json();

                const historyBody = $('#paymentHistoryBody');
                historyBody.empty();

                if (data.payments && data.payments.length > 0) {
                    data.payments.forEach(payment => {
                        const date = new Date(payment.date).toLocaleDateString();
                        const methodBadge = payment.method === 'bank' ?
                            '<span class="badge bg-primary">Bank</span>' :
                            '<span class="badge bg-success">Cash</span>';

                        historyBody.append(`
                                                                                                                                                    <tr>
                                                                                                                                                        <td>${date}</td>
                                                                                                                                                        <td>${methodBadge}</td>
                                                                                                                                                        <td>Rs. ${parseFloat(payment.ammount).toFixed(2)}</td>
                                                                                                                                                        <td>${payment.shift_no || 'N/A'}</td>
                                                                                                                                                        <td>Rs. ${parseFloat(payment.total_ammount).toFixed(2)}</td>
                                                                                                                                                    </tr>
                                                                                                                                                `);
                    });
                } else {
                    historyBody.append('<tr><td colspan="5" class="text-center">No payment history found</td></tr>');
                }
            } catch (error) {
                console.error('Error loading payment history:', error);
                $('#paymentHistoryBody').html('<tr><td colspan="5" class="text-center text-danger">Error loading history</td></tr>');
            }
        }

        // ✅ View payment history button click
        $(document).on('click', '#viewPaymentHistoryBtn', function () {
            $('#paymentHistoryTable').toggle();
        });

        // ✅ FUNCTION TO CHECK CASH HANDOVER LIMIT 
        async function checkCashHandoverLimit(stationId, shiftId, paymentAmount) {
            try {
                console.log('Checking cash handover for station:', stationId, 'shift:', shiftId);

                // Shift ki details fetch karo - open-shifts endpoint se
                const response = await fetch(`/api/stations/${stationId}/open-shifts`);
                const data = await response.json();

                console.log('Open shifts response:', data);

                if (data && data.data && Array.isArray(data.data)) {
                    // Selected shift find karo
                    const selectedShift = data.data.find(shift => shift.id == shiftId);

                    console.log('Selected shift:', selectedShift);

                    if (selectedShift) {
                        const availableCash = parseFloat(selectedShift.cash_handover) || 0;
                        console.log(`Available cash: ${availableCash}, Payment amount: ${paymentAmount}`);

                        if (paymentAmount > availableCash) {
                            toastr.error(`Insufficient cash handover! Available cash: Rs. ${availableCash.toFixed(2)}`);
                            return false;
                        }
                        return true;
                    } else {
                        console.warn('Shift not found in response');
                        toastr.error('Selected shift not found');
                        return false; // Shift nahi mila toh payment BLOCK karo
                    }
                }

                console.error('Invalid response format:', data);
                toastr.error('Failed to verify cash availability');
                return false; // Agar response format sahi nahi hai toh payment BLOCK karo
            } catch (error) {
                console.error('Error checking cash handover:', error);
                toastr.error('Failed to verify cash availability. Please try again.');
                return false; // Error pe payment BLOCK karo
            }
        }

        // ✅ Add Payment button for partial payments
        $('#addPaymentBtn').on('click', async function () {
            const purchaseId = $('#paymentPurchaseId').val();
            const shiftId = $('#paymentShift').val();
            const currentStatus = $('#currentPaymentStatus').val();
            const paymentDate = $('#paymentDate').val();

            if (!shiftId) {
                toastr.error('Please select a shift');
                return;
            }

            if (!paymentDate) {
                toastr.error('Please select payment date');
                return;
            }

            const amount = parseFloat($('#paymentAmount').val()) || 0;
            const method = $('input[name="paymentMethod"]:checked').val();
            const remainingAmount = parseFloat($('#remainingOrderAmount').text()) || 0;

            if (amount <= 0) {
                toastr.error('Please enter a valid payment amount');
                return;
            }

            if (amount > remainingAmount) {
                toastr.error(`Payment amount cannot exceed remaining amount (Rs. ${remainingAmount.toFixed(2)})`);
                return;
            }

            // ✅ CASH HANDOVER VALIDATION - Sirf cash payments ke liye
            if (method === 'cash') {
                const stationId = $('#paymentStationId').val();
                console.log('Station ID for cash payment:', stationId); // ✅ YEH CHECK KARO
                console.log('Shift ID:', shiftId); // ✅ YEH BHI CHECK KARO
                console.log('Amount:', amount); // ✅ YEH BHI CHECK KARO


                if (!stationId) {
                    toastr.error('Station information missing');
                    return;
                }

                // Loading indicator dikhao
                const saveBtn = $(this);
                const originalText = saveBtn.html();
                saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Checking cash availability...');

                try {
                    const isValid = await checkCashHandoverLimit(stationId, shiftId, amount);

                    if (!isValid) {
                        saveBtn.prop('disabled', false).html(originalText);
                        return;
                    }
                } catch (error) {
                    console.error('Cash handover check failed:', error);
                    toastr.error('Failed to verify cash availability');
                    saveBtn.prop('disabled', false).html(originalText);
                    return;
                }

                saveBtn.prop('disabled', false).html(originalText);
            }

            // Bank method ke liye account validate karo
            if (method === 'bank') {
                const accountId = $('#bankAccount').val();
                if (!accountId) {
                    toastr.error('Please select a bank account');
                    return;
                }
            }

            // Loading dikhao
            const saveBtn = $(this);
            const originalText = saveBtn.html();
            saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

            // Partial payment request bhejo
            const paymentData = {
                shift_id: shiftId,
                payment_amount: amount,
                payment_method: method,
                payment_date: paymentDate,
                type: 'debit',
                account_id: (method === 'bank') ? $('#bankAccount').val() : null
            };

            $.ajax({
                url: `${apiBase}/oil-purchases/${purchaseId}/partial-payment`,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(paymentData),
                success: function (response) {
                    toastr.success('Payment added successfully!');
                    $('#paymentStatusModal').modal('hide');
                    loadHistory();

                    saveBtn.prop('disabled', false).html(originalText);
                },
                error: function (xhr) {
                    console.error('Payment error:', xhr.responseText);
                    const errorMsg = xhr.responseJSON?.message || 'Failed to add payment';
                    toastr.error(errorMsg);
                    saveBtn.prop('disabled', false).html(originalText);
                }
            });
        });


        // ✅ Load bank accounts for payment
        function loadBankAccountsForPayment(stationId) {
            let url;
            if (stationId) {
                url = `/api/stations/${stationId}/accounts`;
            } else {
                url = `/api/accounts/category/bank`;
            }

            $.ajax({
                url: url,
                method: 'GET',
                success: function (resp) {
                    const accounts = Array.isArray(resp) ? resp : (resp && Array.isArray(resp.data) ? resp.data : []);

                    // Filter bank accounts (type = 'bank')
                    const banks = accounts.filter(a => (a.type || '').toString().toLowerCase() === 'bank');

                    let bankSelect = $("#bankAccount");
                    bankSelect.empty().append(`<option value="">Select Bank Account...</option>`);

                    if (banks && banks.length > 0) {
                        banks.forEach(bank => {
                            const displayName = `${bank.name} - ${bank.account_number || 'N/A'} (${bank.bank_name || 'Bank'})`;
                            bankSelect.append(
                                `<option value="${bank.id}">${displayName}</option>`
                            );
                        });
                    } else {
                        bankSelect.append(`<option value="">No bank accounts found</option>`);
                    }
                },
                error: function (err) {
                    console.error('Failed to load bank accounts:', err);
                    $('#bankAccount').html('<option value="">Error loading accounts</option>');
                }
            });
        }

        // ✅ Payment method change handler
        $(document).on('change', 'input[name="paymentMethod"]', function () {
            const method = $(this).val();
            if (method === 'bank') {
                $('#bankAccountField').show();
            } else {
                $('#bankAccountField').hide();
            }
        });

        // ✅ Validate payment amount input
        $(document).on('input', '#paymentAmount', function () {
            const amount = parseFloat($(this).val()) || 0;
            const maxAmount = parseFloat($('#remainingOrderAmount').text()) || 0;

            if (amount > maxAmount) {
                $(this).addClass('is-invalid');
                toastr.warning(`Amount cannot exceed Rs. ${maxAmount.toFixed(2)}`);
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // ✅ Function to calculate actual received quantity after shortage
        function calculateActualReceived() {
            const orderedQty = parseFloat($('#orderedQtyDisplay').val()) || 0;
            const shortage = parseFloat($('#shortage').val()) || 0;

            // Calculate actual received quantity
            let actualReceived = orderedQty - shortage;

            // Ensure it's not negative
            if (actualReceived < 0) {
                actualReceived = 0;
                $('#shortage').val(orderedQty); // Set shortage to max
                toastr.warning('Shortage cannot exceed ordered quantity');
            }

            $('#actualReceivedQty').val(actualReceived.toFixed(2));
            $('#actualQtyInfo').text(`Ordered: ${orderedQty}L - Shortage: ${shortage}L = ${actualReceived.toFixed(2)}L`);

            // Update tanks distribution max limit
            const maxReceive = parseFloat($('#maxReceiveQty').text()) || 0;
            if (actualReceived > maxReceive) {
                toastr.warning(`Actual received quantity (${actualReceived}L) exceeds maximum allowed (${maxReceive}L)`);
            }
        }

        // ✅ Receive button based on recive_status
        function openReceiveModal(orderId, stationId, orderQty) {
            console.log(`Opening receive modal for order: ${orderId}, station: ${stationId}`);

            $.ajax({
                url: `${apiBase}/oil-purchasess/${orderId}`,
                method: 'GET',
                success: function (purchaseResponse) {
                    console.log('Purchase response:', purchaseResponse);

                    if (!purchaseResponse || purchaseResponse.length === 0) {
                        toastr.error('Purchase not found');
                        return;
                    }

                    const purchaseData = purchaseResponse[0];
                    console.log('Purchase data:', purchaseData);

                    // Get current received status
                    const alreadyReceived = parseFloat(purchaseData.recieved_qty) || 0;
                    const orderedQty = parseFloat(purchaseData.qty) || 0;
                    const reciveStatus = purchaseData.recive_status || 'Not-Recived';

                    selectedStationId = stationId;
                    currentReceivingOrderId = orderId;

                    // Set modal values
                    $('#receiveOrderId').val(orderId);
                    $('#receiveStationId').val(stationId);
                    $('#receiveProductId').val(purchaseData.product_id || '');
                    $('#receiveProductName').val(purchaseData.product_name || 'Unknown Product');

                    // ✅ Load shifts for this station
                    loadReceiveShifts(stationId);

                    // ✅ Calculate remaining to receive
                    const remaining = orderedQty - alreadyReceived;

                    // Update order summary
                    $('#orderedQtyDisplay').text(orderedQty.toFixed(2));
                    $('#alreadyReceivedDisplay').text(alreadyReceived.toFixed(2));
                    $('#remainingQtyDisplay').text(remaining.toFixed(2));

                    // Check if already fully received
                    if (reciveStatus === 'Recived' || remaining <= 0.01) {
                        // Already fully received - show history only
                        $('#thisReceiveQty').val('').attr('disabled', true);
                        $('#thisReceiveShortage').val('0').attr('disabled', true);
                        $('#receiveDate').val('').attr('disabled', true);
                        $('#receiveShiftId').prop('disabled', true);
                        $('#saveReceive').hide();

                        $('#tanksDistributionSection').html(`
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>Fully Received:</strong> This order has been completely received (${orderedQty.toFixed(2)}L).
                            </div>
                        `);

                        $('#receiveOrderModal .modal-title').html(`
                            Receive History
                            <small class="text-muted">${orderedQty.toFixed(2)}L fully received</small>
                        `);
                    } else {
                        // Can receive more - show receiving form
                        $('#thisReceiveQty').val('')
                            .removeAttr('disabled')
                            .attr('max', remaining)
                            .attr('min', '0.01')
                            .on('input', calculateNetReceived);

                        $('#thisReceiveShortage').val('0')
                            .removeAttr('disabled')
                            .attr('max', remaining)
                            .on('input', calculateNetReceived);

                        $('#maxQtyInfo').text(`Max: ${remaining.toFixed(2)}L remaining`);
                        $('#receiveDate').val('').removeAttr('disabled');
                        $('#receiveShiftId').prop('disabled', false);
                        $('#saveReceive').show().prop('disabled', true);

                        // Clear distribution section
                        $('#tanksDistributionSection').html(`
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-hourglass-split fs-1"></i>
                                <p class="mt-2">Loading tanks distribution...</p>
                            </div>
                        `);

                        if (purchaseData.product_id && stationId) {
                            loadTanksForDistributionDirect(stationId, purchaseData.product_id, remaining);
                        } else {
                            $('#tanksDistributionSection').html(`
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Product or station information not available.
                                </div>
                            `);
                        }

                        $('#receiveOrderModal .modal-title').html(`
                            Receive Order (Partial)
                            <small class="text-muted">${alreadyReceived.toFixed(2)}L already received, ${remaining.toFixed(2)}L remaining</small>
                        `);
                    }

                    // Always load receive history
                    loadReceiveHistory(orderId);

                    $('#receiveOrderModal').modal('show');
                },
                error: function (err) {
                    console.error('Failed to load purchase:', err);
                    toastr.error('Failed to load order details');
                }
            });
        }

        // ✅ NEW: Load shifts for receive modal
        function loadReceiveShifts(stationId) {
            if (!stationId) {
                $('#receiveShiftId').html('<option value="">No station selected</option>');
                return;
            }

            $('#receiveShiftId').html('<option value="">Loading shifts...</option>');

            $.ajax({
                url: `/api/stations/${stationId}/open-shifts`,
                method: 'GET',
                success: function (resp) {
                    let shiftSelect = $("#receiveShiftId");
                    shiftSelect.empty().append(`<option value="">Select Shift...</option>`);

                    if (resp && resp.data && Array.isArray(resp.data) && resp.data.length > 0) {
                        resp.data.forEach(shift => {
                            const startTime = shift.start_time ? shift.start_time.split(' ')[0] : '';
                            shiftSelect.append(
                                `<option value="${shift.id}">Shift #${shift.shift_no} (${startTime})</option>`
                            );
                        });
                    } else {
                        shiftSelect.append(`<option value="">No open shifts found</option>`);
                        toastr.warning('No open shifts available for this station');
                    }
                },
                error: function (err) {
                    console.error('Failed to load shifts:', err);
                    $('#receiveShiftId').html('<option value="">Error loading shifts</option>');
                    toastr.error('Failed to load shifts');
                }
            });
        }



        // ✅ NEW: Load receive history and display in modal
        function loadReceiveHistory(purchaseId) {
            $.ajax({
                url: `${apiBase}/oil-purchases/${purchaseId}/receive-history`,
                method: 'GET',
                success: function (history) {
                    // Remove existing history section if exists
                    $('#receiveHistorySection').remove();

                    if (history && history.length > 0) {
                        // Create history section
                        const historySection = $(`
                                                                                                            <div class="card mt-3" id="receiveHistorySection">
                                                                                                                <div class="card-header">
                                                                                                                    <h6 class="mb-0">
                                                                                                                        <i class="bi bi-clock-history me-2"></i>
                                                                                                                        Previous Receives
                                                                                                                        <span class="badge bg-primary ms-2">${history.length} records</span>
                                                                                                                    </h6>
                                                                                                                </div>
                                                                                                                <div class="card-body">
                                                                                                                    <div class="table-responsive">
                                                                                                                        <table class="table table-sm table-hover">
                                                                                                                            <thead class="table-light">
                                                                                                                                <tr>
                                                                                                                                    <th>Date</th>
                                                                                                                                    <th>Tank</th>
                                                                                                                                    <th>Quantity</th>
                                                                                                                                    <th>Product</th>
                                                                                                                                    <th>Invoice #</th>
                                                                                                                                    <th>Ref #</th>
                                                                                                                                    <th>Shortage</th>
                                                                                                                                </tr>
                                                                                                                            </thead>
                                                                                                                            <tbody id="receiveHistoryBody">
                                                                                                                                <!-- History will be loaded here -->
                                                                                                                            </tbody>
                                                                                                                        </table>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        `);

                        // Insert before tanks distribution section
                        $('#tanksDistributionSection').before(historySection);

                        // Populate history rows
                        const historyBody = $('#receiveHistoryBody');
                        historyBody.empty();

                        history.forEach(rec => {
                            const date = new Date(rec.recive_date).toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            });

                            historyBody.append(`
                                                                                                                <tr>
                                                                                                                    <td>${date}</td>
                                                                                                                    <td>${rec.tank_name || 'Tank ' + rec.tanks_id}</td>
                                                                                                                    <td><span class="badge bg-info">${rec.recived_qty} L</span></td>
                                                                                                                    <td>${rec.product_name || 'N/A'}</td>
                                                                                                                    <td>${rec.inovice_number || '-'}</td>
                                                                                                                    <td>${rec.reference_number || '-'}</td>
                                                                                                                    <td>${rec.shortage ? `<span class="badge bg-warning">${rec.shortage}L</span>` : '-'}</td>
                                                                                                                </tr>
                                                                                                            `);
                        });

                        // Add total row
                        const totalReceived = history.reduce((sum, rec) => sum + (parseFloat(rec.recived_qty) || 0), 0);
                        const totalShortage = history.reduce((sum, rec) => sum + (parseFloat(rec.shortage) || 0), 0);

                        historyBody.append(`
                                                                                                            <tr class="table-primary">
                                                                                                                <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                                                                                                <td><strong>${totalReceived.toFixed(2)} L</strong></td>
                                                                                                                <td></td>
                                                                                                                <td></td>
                                                                                                                <td class="text-end"><strong>Total Shortage:</strong></td>
                                                                                                                <td><strong>${totalShortage.toFixed(2)} L</strong></td>
                                                                                                            </tr>
                                                                                                        `);
                    }
                },
                error: function (err) {
                    console.error('Failed to load receive history:', err);
                }
            });
        }

        // ✅ Modal reset function
        function resetReceiveModal() {
            $('#receiveOrderId').val('');
            $('#receiveStationId').val('');
            $('#receiveProductId').val('');
            $('#receiveProductName').val('');
            $('#orderedQtyDisplay').text('0');
            $('#alreadyReceivedDisplay').text('0');
            $('#remainingQtyDisplay').text('0');
            $('#thisReceiveDisplay').text('0');

            // Reset input fields
            $('#thisReceiveQty').val('')
                .removeAttr('disabled')
                .removeClass('is-invalid')
                .off('input');

            $('#thisReceiveShortage').val('0')
                .removeAttr('disabled')
                .off('input');

            $('#netReceivedDisplay').text('0.00');
            $('#calculationFormula').text('0 - 0 = 0');
            $('#maxQtyInfo').text('');

            // Reset date field
            $('#receiveDate').val('').removeAttr('disabled');

            // Reset additional fields
            $('#receiveInvoiceNo').val('');
            $('#receiveRefNum').val('');
            $('#vehcileNumber').val('');
            $('#invoiceImage').val('');
            $('#imagePreview').hide();
            $('#previewImg').attr('src', '#');

            // Clear distribution section
            $('#tanksDistributionSection').empty();
            $('#receiveHistorySection').remove();

            // Reset save button
            $('#saveReceive').show().prop('disabled', true);
        }


        // ✅ Modal close pe reset karo
        $('#receiveOrderModal').on('hidden.bs.modal', function () {
            resetReceiveModal();
        });

        // ✅ Modal open hone pe date picker set karo
        $('#receiveOrderModal').on('shown.bs.modal', function () {
            // Date picker already hai, bus ensure karo
            if (!$('#receiveDate').hasClass('flatpickr-input')) {
                flatpickr("#receiveDate", {
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "F j, Y",
                    allowInput: true,
                    defaultDate: "today"
                });
            }
        });

        // ✅ Preview invoice image before upload
        $('#invoiceImage').on('change', function (e) {
            const file = e.target.files[0];
            if (file && (file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpg')) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    $('#previewImg').attr('src', event.target.result);
                    $('#imagePreview').show();
                };
                reader.readAsDataURL(file);
            } else if (file && file.type === 'application/pdf') {
                $('#imagePreview').hide();
                toastr.info('PDF file selected. Preview not available.');
            } else {
                $('#imagePreview').hide();
            }
        });


        // ✅ Directly load tanks for distribution (no product selection)
        function loadTanksForDistributionDirect(stationId, productId, maxRemaining) {
            if (!stationId || !productId) {
                $('#tanksDistributionSection').html(`
                                                                                                    <div class="alert alert-warning">
                                                                                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                                                                                        Station or product information missing.
                                                                                                    </div>
                                                                                                `);
                return;
            }

            $('#tanksDistributionSection').html(`
                                                                                                <div class="text-center py-4">
                                                                                                    <div class="spinner-border text-primary" role="status">
                                                                                                        <span class="visually-hidden">Loading...</span>
                                                                                                    </div>
                                                                                                    <p class="mt-2">Loading tanks...</p>
                                                                                                </div>
                                                                                            `);

            $.ajax({
                url: `/api/station-product-tanks/${stationId}/${productId}`,
                method: 'GET',
                success: function (tanks) {
                    const container = $('#tanksDistributionSection');

                    if (!tanks || tanks.length === 0) {
                        container.html(`
                                                                                                            <div class="alert alert-warning">
                                                                                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                                                                                No active tanks found for this product.
                                                                                                            </div>
                                                                                                        `);
                        return;
                    }

                    // Build tanks table
                    let html = `
                                                                                                        <div class="card">
                                                                                                            <div class="card-body">
                                                                                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                                                                                    <h6 class="mb-0">Distribute Oil to Tanks:</h6>
                                                                                                                    <div>
                                                                                                                        <strong>Net to distribute: <span id="netToDistribute" class="text-primary">0.00</span> L</strong><br>
                                                                                                                        <small>Remaining to distribute: <span id="remainingDistributeQty">0</span> L</small>
                                                                                                                    </div>
                                                                                                                </div>

                                                                                                                <div class="table-responsive">
                                                                                                                    <table class="table table-bordered table-hover">
                                                                                                                        <thead class="table-light">
                                                                                                                            <tr>
                                                                                                                                <th style="width: 20%">Tank Name</th>
                                                                                                                                <th class="text-center">Current Level</th>
                                                                                                                                <th class="text-center">Capacity</th>
                                                                                                                                <th class="text-center">Dry Limit</th>
                                                                                                                                <th class="text-center">Available Space</th>
                                                                                                                                <th style="width: 25%">Quantity to Add</th>
                                                                                                                            </tr>
                                                                                                                        </thead>
                                                                                                                        <tbody>`;

                    tanks.forEach(tank => {
                        const currentLevel = parseFloat(tank.current_level) || 0;
                        const capacity = parseFloat(tank.capacity) || 0;
                        const dryLimit = parseFloat(tank.dry_limit) || 0;

                        // Calculate available space
                        let availableSpace = capacity - currentLevel;

                        // Consider dry limit
                        if (dryLimit > 0) {
                            const minLevel = dryLimit;
                            availableSpace = Math.max(0, capacity - currentLevel - minLevel);
                        }

                        availableSpace = Math.max(0, availableSpace);

                        let statusBadge = '';
                        if (currentLevel <= 0) {
                            statusBadge = '<span class="badge bg-danger">Empty</span>';
                        } else if (dryLimit > 0 && currentLevel <= dryLimit) {
                            statusBadge = '<span class="badge bg-warning">At/Below Dry Limit</span>';
                        } else if (currentLevel >= capacity) {
                            statusBadge = '<span class="badge bg-danger">Full</span>';
                        } else {
                            statusBadge = '<span class="badge bg-success">OK</span>';
                        }

                        html += `
                                                                                                            <tr>
                                                                                                                <td>
                                                                                                                    <strong>${tank.name || 'Unnamed Tank'}</strong><br>
                                                                                                                    <small class="text-muted">${statusBadge}</small>
                                                                                                                </td>
                                                                                                                <td class="text-center">
                                                                                                                    ${currentLevel.toFixed(2)} L<br>
                                                                                                                    <small class="text-muted">${((currentLevel / capacity) * 100).toFixed(1)}%</small>
                                                                                                                </td>
                                                                                                                <td class="text-center">${capacity.toFixed(2)} L</td>
                                                                                                                <td class="text-center">
                                                                                                                    ${dryLimit > 0 ? `
                                                                                                                        ${dryLimit.toFixed(2)} L<br>
                                                                                                                        <small class="text-muted">${((dryLimit / capacity) * 100).toFixed(1)}%</small>
                                                                                                                    ` : 'N/A'}
                                                                                                                </td>
                                                                                                                <td class="text-center">
                                                                                                                    <span class="badge bg-info fs-6">${availableSpace.toFixed(2)} L</span>
                                                                                                                </td>
                                                                                                                <td>
                                                                                                                    <div class="input-group">
                                                                                                                        <input type="number" 
                                                                                                                               class="form-control tank-qty-input" 
                                                                                                                               data-tank-id="${tank.id}"
                                                                                                                               data-tank-name="${tank.name}"
                                                                                                                               data-current="${currentLevel}"
                                                                                                                               data-capacity="${capacity}"
                                                                                                                               data-dry-limit="${dryLimit}"
                                                                                                                               data-max="${availableSpace}"
                                                                                                                               min="0" 
                                                                                                                               max="${availableSpace}"
                                                                                                                               step="0.01"
                                                                                                                               placeholder="0.00"
                                                                                                                               oninput="updateRemainingQty()"
                                                                                                                               disabled>
                                                                                                                        <span class="input-group-text">L</span>
                                                                                                                    </div>
                                                                                                                    <div class="d-flex justify-content-between mt-1">
                                                                                                                        <small class="text-muted">
                                                                                                                            Max: ${availableSpace.toFixed(2)}L
                                                                                                                        </small>
                                                                                                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-sm" 
                                                                                                                                onclick="fillToMax(${tank.id})" disabled>
                                                                                                                            <i class="bi bi-arrow-up"></i> Fill
                                                                                                                        </button>
                                                                                                                    </div>
                                                                                                                </td>
                                                                                                            </tr>`;
                    });

                    html += `</tbody></table></div></div></div>`;

                    container.html(html);
                    calculateNetReceived(); // Initialize calculation
                },
                error: function (err) {
                    console.error('Failed to load tanks:', err);
                    $('#tanksDistributionSection').html(`
                                                                                                        <div class="alert alert-danger">
                                                                                                            <i class="bi bi-x-circle me-2"></i>
                                                                                                            Failed to load tanks. Please try again.
                                                                                                        </div>
                                                                                                    `);
                }
            });
        }


        // ✅ Validate total received quantity
        function validateTotalReceivedQty(maxAllowed) {
            const totalReceived = parseFloat($('#totalReceivedQty').val()) || 0;
            const qtyError = $('#qtyError');

            console.log(`Validating: ${totalReceived} against max: ${maxAllowed}`);

            if (totalReceived > maxAllowed) {
                qtyError.text(` Cannot exceed ${maxAllowed.toFixed(2)}L`).show();
                $('#totalReceivedQty').addClass('is-invalid');

                // Disable tank inputs
                $('.tank-qty-input').prop('disabled', true);
                $('.tank-qty-input').val('');
                $('.btn-sm[onclick^="fillToMax"]').prop('disabled', true);
                $('#autoDistributeBtn').prop('disabled', true);
                $('#saveReceive').prop('disabled', true);
            } else if (totalReceived < 0) {
                qtyError.text(' Cannot be negative').show();
                $('#totalReceivedQty').addClass('is-invalid');

                // Disable tank inputs
                $('.tank-qty-input').prop('disabled', true);
                $('.tank-qty-input').val('');
                $('.btn-sm[onclick^="fillToMax"]').prop('disabled', true);
                $('#autoDistributeBtn').prop('disabled', true);
                $('#saveReceive').prop('disabled', true);
            } else if (totalReceived === 0) {
                qtyError.hide();
                $('#totalReceivedQty').removeClass('is-invalid');

                // Disable tank inputs
                $('.tank-qty-input').prop('disabled', true);
                $('.tank-qty-input').val('');
                $('.btn-sm[onclick^="fillToMax"]').prop('disabled', true);
                $('#autoDistributeBtn').prop('disabled', true);
                $('#saveReceive').prop('disabled', true);
            } else {
                qtyError.hide();
                $('#totalReceivedQty').removeClass('is-invalid');

                // Enable tank inputs
                $('.tank-qty-input').prop('disabled', false);
                $('.btn-sm[onclick^="fillToMax"]').prop('disabled', false);
                $('#autoDistributeBtn').prop('disabled', false);

                updateRemainingQty();
            }
        }


        // ✅ Load tanks for distribution - FIXED
        function loadTanksForDistribution(stationId, productId) {
            if (!stationId || !productId) {
                $('#tanksContainer').html(`
                                                                                                                                        <div class="alert alert-warning">
                                                                                                                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                                                                                                                            Please select a product first
                                                                                                                                        </div>
                                                                                                                                    `);
                return;
            }

            // Show loading in tanks container
            $('#tanksContainer').html(`
                                                                                                                                                                    <div class="text-center py-3">
                                                                                                                                                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                                                                                                                                            <span class="visually-hidden">Loading...</span>
                                                                                                                                                                        </div>
                                                                                                                                                                        <p class="mt-2 small text-muted">Loading tanks...</p>
                                                                                                                                                                    </div>
                                                                                                                                                                `);

            // Show the distribution area
            $('#tanksDistributionArea').show();

            $.ajax({
                url: `/api/station-product-tanks/${stationId}/${productId}`,
                method: 'GET',
                success: function (tanks) {
                    const container = $('#tanksContainer');

                    if (!tanks || tanks.length === 0) {
                        container.html(`
                                                                                                                                                                                <div class="alert alert-warning">
                                                                                                                                                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                                                                                                                                                    No active tanks found for this product at this station.
                                                                                                                                                                                </div>
                                                                                                                                                                            `);
                        updateRemainingQty();
                        return;
                    }

                    // Build tanks table
                    let html = `
                                                                                                                                                                            <div class="table-responsive">
                                                                                                                                                                                <table class="table table-bordered table-hover">
                                                                                                                                                                                    <thead class="table-light">
                                                                                                                                                                                        <tr>
                                                                                                                                                                                            <th style="width: 20%">Tank Name</th>
                                                                                                                                                                                            <th class="text-center">Current Level</th>
                                                                                                                                                                                            <th class="text-center">Capacity</th>
                                                                                                                                                                                            <th class="text-center">Dry Limit</th>
                                                                                                                                                                                            <th class="text-center">Available Space</th>
                                                                                                                                                                                            <th style="width: 25%">Quantity to Add</th>
                                                                                                                                                                                        </tr>
                                                                                                                                                                                    </thead>
                                                                                                                                                                                    <tbody>`;

                    tanks.forEach(tank => {
                        const currentLevel = parseFloat(tank.current_level) || 0;
                        const capacity = parseFloat(tank.capacity) || 0;
                        const dryLimit = parseFloat(tank.dry_limit) || 0;

                        // Calculate available space
                        let availableSpace = capacity - currentLevel;

                        // Consider dry limit
                        if (dryLimit > 0 && currentLevel < dryLimit) {
                            availableSpace = capacity - currentLevel;
                        }

                        availableSpace = Math.max(0, availableSpace);

                        // Determine status badge color
                        let statusBadge = '';
                        if (currentLevel <= 0) {
                            statusBadge = '<span class="badge bg-danger">Empty</span>';
                        } else if (dryLimit > 0 && currentLevel < dryLimit) {
                            statusBadge = '<span class="badge bg-warning">Below Dry Limit</span>';
                        } else if (currentLevel >= capacity) {
                            statusBadge = '<span class="badge bg-danger">Full</span>';
                        } else {
                            statusBadge = '<span class="badge bg-success">OK</span>';
                        }

                        html += `
                                                                                                                                                                                <tr>
                                                                                                                                                                                    <td>
                                                                                                                                                                                        <strong>${tank.name || 'Unnamed Tank'}</strong><br>
                                                                                                                                                                                        <small class="text-muted">${statusBadge}</small>
                                                                                                                                                                                    </td>
                                                                                                                                                                                    <td class="text-center">
                                                                                                                                                                                        ${currentLevel.toFixed(2)} L<br>
                                                                                                                                                                                        <small class="text-muted">${((currentLevel / capacity) * 100).toFixed(1)}%</small>
                                                                                                                                                                                    </td>
                                                                                                                                                                                    <td class="text-center">${capacity.toFixed(2)} L</td>
                                                                                                                                                                                    <td class="text-center">
                                                                                                                                                                                        ${dryLimit > 0 ? `
                                                                                                                                                                                            ${dryLimit.toFixed(2)} L<br>
                                                                                                                                                                                            <small class="text-muted">${((dryLimit / capacity) * 100).toFixed(1)}%</small>
                                                                                                                                                                                        ` : 'N/A'}
                                                                                                                                                                                    </td>
                                                                                                                                                                                    <td class="text-center">
                                                                                                                                                                                        <span class="badge bg-info fs-6">${availableSpace.toFixed(2)} L</span>
                                                                                                                                                                                    </td>
                                                                                                                                                                                    <td>
                                                                                                                                                                                        <div class="input-group">
                                                                                                                                                                                            <input type="number" 
                                                                                                                                                                                                   class="form-control tank-qty-input" 
                                                                                                                                                                                                   data-tank-id="${tank.id}"
                                                                                                                                                                                                   data-tank-name="${tank.name}"
                                                                                                                                                                                                   data-current="${currentLevel}"
                                                                                                                                                                                                   data-capacity="${capacity}"
                                                                                                                                                                                                   data-dry-limit="${dryLimit}"
                                                                                                                                                                                                   data-max="${availableSpace}"
                                                                                                                                                                                                   min="0" 
                                                                                                                                                                                                   max="${availableSpace}"
                                                                                                                                                                                                   step="0.01"
                                                                                                                                                                                                   placeholder="0.00"
                                                                                                                                                                                                   oninput="updateRemainingQty()">
                                                                                                                                                                                            <span class="input-group-text">L</span>
                                                                                                                                                                                        </div>
                                                                                                                                                                                        <div class="d-flex justify-content-between mt-1">
                                                                                                                                                                                            <small class="text-muted">
                                                                                                                                                                                                Max: ${availableSpace.toFixed(2)}L
                                                                                                                                                                                            </small>
                                                                                                                                                                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-sm" 
                                                                                                                                                                                                    onclick="fillToMax(${tank.id})">
                                                                                                                                                                                                <i class="bi bi-arrow-up"></i> Fill
                                                                                                                                                                                            </button>
                                                                                                                                                                                        </div>
                                                                                                                                                                                    </td>
                                                                                                                                                                                </tr>`;
                    });

                    html += `</tbody></table></div>`;

                    container.html(html);
                    updateRemainingQty();

                    // Add tooltips
                    container.find('input').tooltip({
                        title: 'Enter quantity to add to this tank',
                        placement: 'top'
                    });
                },
                error: function (err) {
                    console.error('Failed to load tanks:', err);
                    $('#tanksContainer').html(`
                                                                                                                                                                            <div class="alert alert-danger">
                                                                                                                                                                                <i class="bi bi-x-circle me-2"></i>
                                                                                                                                                                                Failed to load tanks. Please try again.<br>
                                                                                                                                                                                <small>Error: ${err.responseJSON?.message || err.statusText}</small>
                                                                                                                                                                            </div>
                                                                                                                                                                        `);
                }
            });
        }

        // ✅ Helper function to fill a tank to max capacity
        function fillToMax(tankId) {
            const input = $(`.tank-qty-input[data-tank-id="${tankId}"]`);
            const max = parseFloat(input.data('max')) || 0;
            input.val(max.toFixed(2));
            updateRemainingQty();
        }

        // ✅ Function to calculate net received after shortage
        function calculateNetReceived() {
            const thisReceiveQty = parseFloat($('#thisReceiveQty').val()) || 0;
            const thisShortage = parseFloat($('#thisReceiveShortage').val()) || 0;

            // Calculate net received (after shortage deduction for THIS receive)
            let netReceived = thisReceiveQty - thisShortage;

            // Ensure it's not negative
            if (netReceived < 0) {
                netReceived = 0;
                $('#thisReceiveShortage').val(thisReceiveQty); // Set shortage to max
                toastr.warning('Shortage cannot exceed this receive quantity');
            }

            // Update display
            $('#netReceivedDisplay').text(netReceived.toFixed(2));
            $('#calculationFormula').text(`${thisReceiveQty.toFixed(2)} - ${thisShortage.toFixed(2)} = ${netReceived.toFixed(2)}`);
            $('#thisReceiveDisplay').text(thisReceiveQty.toFixed(2));

            // Update tanks distribution max limit
            if (netReceived > 0) {
                $('.tank-qty-input').prop('disabled', false);
                $('.btn-sm[onclick^="fillToMax"]').prop('disabled', false);
            } else {
                $('.tank-qty-input').prop('disabled', true);
                $('.btn-sm[onclick^="fillToMax"]').prop('disabled', true);
            }

            updateRemainingQty();
        }


        // ✅ Calculate remaining quantity - FIXED
        function updateRemainingQty() {
            const netReceived = parseFloat($('#netReceivedDisplay').text()) || 0;
            let distributed = 0;

            $('.tank-qty-input').each(function () {
                const val = parseFloat($(this).val()) || 0;
                const max = parseFloat($(this).data('max')) || 0;

                // Validate max limit
                if (val > max) {
                    $(this).val(max);
                    distributed += max;
                } else {
                    distributed += val;
                }
            });

            const remaining = netReceived - distributed;
            const remainingSpan = $('#remainingDistributeQty');
            if (remainingSpan.length) {
                remainingSpan.text(remaining.toFixed(2));
            }

            // Update save button state
            const saveBtn = $('#saveReceive');

            if (netReceived <= 0) {
                saveBtn.prop('disabled', true);
                if (remainingSpan.length) {
                    remainingSpan.removeClass('text-danger text-warning text-success');
                }
            } else if (Math.abs(remaining) <= 0.01) {
                if (remainingSpan.length) {
                    remainingSpan.removeClass('text-danger text-warning').addClass('text-success');
                }
                saveBtn.prop('disabled', false);
            } else if (remaining > 0) {
                if (remainingSpan.length) {
                    remainingSpan.removeClass('text-danger text-success').addClass('text-warning');
                }
                saveBtn.prop('disabled', false);
            } else {
                if (remainingSpan.length) {
                    remainingSpan.removeClass('text-success text-warning').addClass('text-danger');
                }
                saveBtn.prop('disabled', true);
            }
        }




        // ✅ Auto-fill distribution function
        function autoDistributeQuantity() {
            const totalReceived = parseFloat($('#totalReceivedQty').val()) || 0;
            if (totalReceived <= 0) {
                toastr.warning('Please enter received quantity first');
                return;
            }

            let totalAvailable = 0;
            const tankInputs = $('.tank-qty-input');
            const tankData = [];

            // Collect tank data
            tankInputs.each(function () {
                const max = parseFloat($(this).data('max')) || 0;
                totalAvailable += max;
                tankData.push({
                    element: $(this),
                    max: max
                });
            });

            if (totalAvailable <= 0) {
                toastr.warning('No available space in tanks');
                return;
            }

            if (totalReceived > totalAvailable) {
                toastr.warning(`Order quantity (${totalReceived}L) exceeds available space (${totalAvailable.toFixed(2)}L)`);
                return;
            }

            // Distribute proportionally
            let remaining = totalReceived;
            tankData.forEach((tank, index) => {
                if (index === tankData.length - 1) {
                    // Last tank gets remaining quantity
                    tank.element.val(remaining.toFixed(2));
                } else {
                    const proportion = tank.max / totalAvailable;
                    const allocated = Math.min(tank.max, totalReceived * proportion);
                    tank.element.val(allocated.toFixed(2));
                    remaining -= allocated;
                }
            });

            updateRemainingQty();
            toastr.info('Quantity auto-distributed proportionally');
        }

        // ✅ Save receive - UPDATED with base64 image
        $('#saveReceive').on('click', function () {
            const orderId = $('#receiveOrderId').val();
            const receiveDate = $('#receiveDate').val();
            const shiftId = $('#receiveShiftId').val();  // ✅ GET SHIFT ID

            const thisReceiveQty = parseFloat($('#thisReceiveQty').val()) || 0;
            const thisShortage = parseFloat($('#thisReceiveShortage').val()) || 0;
            const netReceived = parseFloat($('#netReceivedDisplay').text()) || 0;
            const productId = $('#receiveProductId').val();
            const stationId = $('#receiveStationId').val();

            const invoiceNo = $('#receiveInvoiceNo').val() || '';
            const refNum = $('#receiveRefNum').val() || '';

            // ✅ VALIDATE SHIFT
            if (!shiftId) {
                toastr.error('Please select a shift');
                return;
            }

            if (!stationId) {
                toastr.error('Station information not available');
                return;
            }

            const vehicleNumber = $('#vehcileNumber').val();
            if (!vehicleNumber) {
                toastr.error('Please enter vehicle number');
                return;
            }

            if (!receiveDate) {
                toastr.error('Please select receiving date');
                return;
            }

            if (thisReceiveQty <= 0) {
                toastr.error('Please enter valid receive quantity');
                return;
            }

            if (thisShortage > thisReceiveQty) {
                toastr.error(`Shortage (${thisShortage}L) cannot exceed this receive quantity (${thisReceiveQty}L)`);
                return;
            }

            if (netReceived <= 0) {
                toastr.error('Net received quantity must be greater than 0');
                return;
            }

            if (!productId) {
                toastr.error('Product information missing');
                return;
            }

            // Collect tank distribution
            const tankDistribution = [];
            let totalDistributed = 0;
            let hasErrors = false;

            $('.tank-qty-input').each(function () {
                const qty = parseFloat($(this).val()) || 0;
                const tankId = $(this).data('tank-id');
                const tankName = $(this).data('tank-name');
                const max = parseFloat($(this).data('max')) || 0;

                if (qty > max) {
                    toastr.error(`${tankName}: Quantity (${qty}L) exceeds available space (${max}L)`);
                    hasErrors = true;
                    return false;
                }

                if (qty > 0) {
                    tankDistribution.push({
                        tank_id: tankId,
                        tank_name: tankName,
                        quantity: qty
                    });
                    totalDistributed += qty;
                }
            });

            if (hasErrors) return;

            if (tankDistribution.length === 0) {
                toastr.error('Please distribute quantity to at least one tank');
                return;
            }

            const remaining = netReceived - totalDistributed;
            if (Math.abs(remaining) > 0.01) {
                toastr.error(`Please distribute all quantity. Remaining: ${remaining.toFixed(2)}L`);
                return;
            }

            const orderedQty = parseFloat($('#orderedQtyDisplay').text()) || 0;
            const alreadyReceived = parseFloat($('#alreadyReceivedDisplay').text()) || 0;

            const confirmMessage = `Receive Details:\n\nOrder Total: ${orderedQty}L\nAlready Received: ${alreadyReceived}L\nThis Receive: ${thisReceiveQty}L\nShortage This Time: ${thisShortage}L\nNet Added to Tanks: ${netReceived}L\nVehicle Number: ${vehicleNumber}\nDistribute into ${tankDistribution.length} tank(s)?`;
            if (!confirm(confirmMessage)) return;

            const saveBtn = $(this);
            const originalText = saveBtn.html();
            saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Receiving...');

            // ✅ CONVERT IMAGE TO BASE64
            const invoiceImageFile = $('#invoiceImage')[0].files[0];

            const processImageAndSend = (imageBase64 = null) => {
                const postData = {
                    receive_date: receiveDate,
                    shift_id: shiftId,  // ✅ ADD SHIFT ID

                    this_receive_qty: thisReceiveQty,
                    this_shortage_qty: thisShortage,
                    net_received_qty: netReceived,
                    product_id: productId,
                    station_id: stationId,
                    invoice_number: invoiceNo,
                    reference_number: refNum,
                    vehicle_number: vehicleNumber,
                    shortage: thisShortage,
                    tanks: tankDistribution
                };

                if (imageBase64) {
                    postData.invoice_image = imageBase64;  // ✅ CHANGE TO 'invoice_image'
                }

                $.ajax({
                    url: `${apiBase}/oil-purchases/${orderId}/receive`,
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(postData),
                    success: function (response) {
                        toastr.success('Order received successfully!');
                        $('#receiveOrderModal').modal('hide');
                        loadHistory();
                        loadInventory();
                        saveBtn.prop('disabled', false).html(originalText);
                        selectedStationId = null;
                        currentReceivingOrderId = null;
                    },
                    error: function (xhr) {
                        console.error('Receive error:', xhr.responseText);
                        const errorMsg = xhr.responseJSON?.message || 'Failed to receive order';
                        toastr.error(errorMsg);
                        saveBtn.prop('disabled', false).html(originalText);
                    }
                });
            };

            // Agar image hai toh base64 mein convert karo
            if (invoiceImageFile && (invoiceImageFile.type === 'image/jpeg' || invoiceImageFile.type === 'image/png' || invoiceImageFile.type === 'image/jpg')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    processImageAndSend(e.target.result);
                };
                reader.onerror = function () {
                    toastr.warning('Image read failed, continuing without image');
                    processImageAndSend(null);
                };
                reader.readAsDataURL(invoiceImageFile);
            } else {
                processImageAndSend(null);
            }
        });


        // ✅ Edit purchase - UPDATED
        async function editPurchase(id) {
            try {
                const res = await fetch(`${apiBase}/oil-purchasess/${id}`);
                const data = await res.json();

                if (data && data.length > 0) {
                    const purchase = data[0];
                    editingId = id;

                    // Switch to purchase tab
                    $('#purchase-tab').tab('show');

                    // Reset form
                    $('#purchaseForm')[0].reset();

                    // Set supplier
                    if (supplierChoices) {
                        supplierChoices.setChoiceByValue(purchase.supplier_id?.toString() || "");
                    }

                    // Set station
                    if (stationChoices && purchase.station_id) {
                        stationChoices.setChoiceByValue(purchase.station_id.toString());
                        selectedStationId = purchase.station_id;

                        // Load shifts
                        loadShifts(purchase.station_id);

                        // Load products
                        $.ajax({
                            url: `${apiBase}/station-products/${purchase.station_id}`,
                            method: 'GET',
                            success: function (productsResponse) {
                                let products = [];

                                if (Array.isArray(productsResponse)) {
                                    products = productsResponse;
                                } else if (productsResponse && productsResponse.data) {
                                    products = productsResponse.data;
                                }

                                let productSelect = $("#product");
                                productSelect.empty().append(`<option value="">Select Product...</option>`);

                                if (products && products.length > 0) {
                                    products.forEach(product => {
                                        productSelect.append(
                                            `<option value="${product.id}">${product.name}</option>`
                                        );
                                    });

                                    // Set product
                                    setTimeout(() => {
                                        productSelect.val(purchase.product_id);
                                    }, 100);
                                }

                                // Initialize product choices
                                if (productChoices) productChoices.destroy();
                                productChoices = new Choices("#product", {
                                    searchEnabled: true,
                                    itemSelectText: '',
                                    shouldSort: false,
                                    placeholderValue: "Select Product...",
                                    removeItemButton: true
                                });

                                // Set product value
                                if (purchase.product_id && productChoices) {
                                    setTimeout(() => {
                                        productChoices.setChoiceByValue(purchase.product_id.toString());
                                    }, 200);
                                }
                            },
                            error: function (err) {
                                console.error('Failed to load products:', err);
                                toastr.error('Failed to load products');
                            }
                        });
                    }

                    // ✅ FIX: Set order_date properly
                    setTimeout(() => {
                        if (purchase.order_date) {
                            $('#order_date').val(purchase.order_date);

                            // Flatpickr ko update karo
                            const flatpickrInstance = flatpickr("#order_date");
                            if (flatpickrInstance) {
                                flatpickrInstance.setDate(purchase.order_date);
                            }
                        }

                        // Set other fields
                        $('#payment_status').val(purchase.payment_status || '');
                        $('#qty').val(purchase.qty || '');
                        $('#rate').val(purchase.rate || '');

                        // Auto-expand accordion
                        const accCollapse = new bootstrap.Collapse(document.getElementById('purchaseFormCollapse'), {
                            toggle: false
                        });
                        accCollapse.show();

                        toastr.info('Purchase loaded for editing');
                    }, 1500); // Increase delay to ensure all dropdowns loaded

                }
            } catch (error) {
                console.error('Error loading purchase for edit:', error);
                toastr.error('Failed to load purchase data');
            }
        }

        // ✅ Delete purchase
        function deletePurchase(id) {
            if (!confirm('Are you sure you want to delete this purchase?')) return;

            $.ajax({
                url: `${apiBase}/oil-purchases/${id}`,
                type: 'DELETE',
                success: function () {
                    toastr.success('Purchase deleted successfully!');
                    loadHistory();
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    toastr.error('Failed to delete purchase');
                }
            });
        }


        // ✅ Save payment status with modal
        $('#savePaymentStatus').on('click', async function () {
            const purchaseId = $('#paymentPurchaseId').val();
            const shiftId = $('#paymentShift').val();

            if (!shiftId) {
                toastr.error('Please select a shift');
                return;
            }

            const totalAmount = parseFloat($('#totalOrderAmount').text()) || 0;
            const stationId = $('#paymentStationId').val();

            const saveBtn = $(this);
            const originalText = saveBtn.html();
            saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Checking cash availability...');

            // ✅ Cash handover validation for full payment
            if (stationId) {
                try {
                    const isValid = await checkCashHandoverLimit(stationId, shiftId, totalAmount);
                    if (!isValid) {
                        saveBtn.prop('disabled', false).html(originalText);
                        return;
                    }
                } catch (error) {
                    console.error('Cash handover check failed:', error);
                    toastr.error('Failed to verify cash availability');
                    saveBtn.prop('disabled', false).html(originalText);
                    return;
                }
            }

            saveBtn.prop('disabled', false).html(originalText);
            saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Updating...');

            const paymentData = {
                payment_status: 'paid',
                shift_id: shiftId
            };

            $.ajax({
                url: `${apiBase}/oil-purchases/${purchaseId}/payment-status`,
                type: 'PATCH',
                contentType: 'application/json',
                data: JSON.stringify(paymentData),
                success: function (response) {
                    toastr.success('Payment marked as paid successfully!');
                    $('#paymentStatusModal').modal('hide');
                    loadHistory();
                    saveBtn.prop('disabled', false).html(originalText);
                },
                error: function (xhr) {
                    console.error('Payment update error:', xhr.responseText);
                    const errorMsg = xhr.responseJSON?.message || 'Failed to update payment status';
                    toastr.error(errorMsg);
                    saveBtn.prop('disabled', false).html(originalText);
                }
            });
        });


        // ✅ Open Invoice Modal
        function openInvoiceModal(purchaseId) {
            $('#invoicePurchaseId').val(purchaseId);
            $('#modalRefNum').val('');
            $('#modalInvoiceNo').val('');
            $('#invoiceRefModal').modal('show');
        }

        // ✅ Save Invoice & Ref Number - CORRECT URL
        $('#saveInvoiceRef').on('click', function () {
            const purchaseId = $('#invoicePurchaseId').val();
            const invoiceNo = $('#modalInvoiceNo').val();
            const refNum = $('#modalRefNum').val();

            if (!invoiceNo.trim()) {
                toastr.error('Please enter invoice number');
                return;
            }

            // ✅ CORRECT ENDPOINT: /api/oil-purchases/{id}/invoice
            $.ajax({
                url: `${apiBase}/oil-purchases/${purchaseId}/invoice`, // ✅ YEH SAHI HAI
                type: 'PATCH',
                contentType: 'application/json',
                data: JSON.stringify({
                    invoice_no: invoiceNo,
                    ref_num: refNum || null
                }),
                success: function (response) {
                    toastr.success('Invoice details updated!');
                    $('#invoiceRefModal').modal('hide');
                    loadHistory();
                },
                error: function (xhr) {
                    console.error('Invoice update error:', xhr.responseText);
                    toastr.error('Failed to update invoice details');
                }
            });
        });

        // ✅ Modal close hone pe reset
        $('#paymentStatusModal').on('hidden.bs.modal', function () {
            $('#paymentPurchaseId').val('');
            $('#paymentStationId').val('');
            $('#paymentStationName').val('');
            $('#paymentShift').html('<option value="">Select Shift...</option>');
            $('#currentPaymentStatus').val('');
            $('#partialPaymentFields').hide();
            $('#notPaidInfo').hide();
            $('#paymentAmount').val('');
            $('#cashMethod').prop('checked', true);
            $('#bankAccount').html('<option value="">Select Bank Account...</option>');
            $('#bankAccountField').hide();
        });

        // ✅ Function to open shortage payment modal - WITH TABS AND HISTORY
        async function openShortagePaymentModal(purchaseId) {
            try {
                resetShortageModal();

                toastr.info('Loading shortage details...', 'Please Wait', { timeOut: 0, extendedTimeOut: 0 });

                // Fetch shortage details
                const response = await fetch(`${apiBase}/oil-purchases/${purchaseId}/shortage-details`);
                const data = await response.json();

                // Fetch payment history as well
                const historyResponse = await fetch(`${apiBase}/oil-purchases/${purchaseId}/shortage-payment-history`);
                const historyData = await historyResponse.json();

                toastr.clear();

                if (!data.success) {
                    toastr.error(data.error || 'Failed to fetch shortage details');
                    return;
                }

                $('#shortagePurchaseId').val(purchaseId);
                $('#shortageStationId').val(data.station_id);

                // ✅ Display totals correctly
                const totalShortage = parseFloat(data.total_shortage) || 0;
                const rate = parseFloat(data.rate) || 0;
                const totalAmount = totalShortage * rate;  // ✅ Calculate properly

                $('#modalTotalShortage').text(totalShortage.toFixed(2));
                $('#modalRate').text(rate.toFixed(2));
                $('#modalTotalAmount').text(totalAmount.toFixed(2));

                // Build pending receives table
                let receivesHtml = `
                            <div id="receivesTableWrapper" class="card mb-3 pending-receives-table">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-list-check me-2"></i>
                                        Pending Shortage Receives (${data.pending_receives.length})
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 5%"><input type="checkbox" id="selectAllReceives"></th>
                                                    <th>Receive Date</th>
                                                    <th>Quantity Received</th>
                                                    <th>Shortage (L)</th>
                                                    <th>Amount (Rs.)</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                        `;

                if (data.pending_receives.length === 0) {
                    receivesHtml += `
                                <tr>
                                    <td colspan="6" class="text-center text-success py-4">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        No pending shortages! All shortages have been paid.
                                    <tr>
                                </tr>
                            `;
                } else {
                    data.pending_receives.forEach(receive => {
                        // ✅ CORRECT CALCULATION: shortage * rate
                        const shortageQty = parseFloat(receive.shortage) || 0;
                        const amount = shortageQty * rate;  // ✅ This is correct
                        const receiveDate = receive.recive_date ? new Date(receive.recive_date).toLocaleDateString() : 'N/A';

                        receivesHtml += `
                                    <tr>
                                        <td><input type="checkbox" class="receive-checkbox" 
                                                value="${receive.receive_id}" 
                                                data-shortage="${shortageQty}" 
                                                data-amount="${amount}"
                                                data-rate="${rate}"></td>
                                        <td>${receiveDate}</td>
                                        <td>${(parseFloat(receive.recived_qty) || 0).toFixed(2)} L</td>
                                        <td class="text-warning fw-bold">${shortageQty.toFixed(2)} L</td>
                                        <td class="text-success fw-bold">Rs. ${amount.toFixed(2)}</td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                    </tr>
                                `;
                    });
                }

                receivesHtml += `
                                            </tbody>
                                        </table>
                                    </div>
                                    ${data.pending_receives.length > 0 ? `
                                    <div class="p-3 bg-light border-top">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Selected Total Shortage:</strong> 
                                                <span id="selectedShortage" class="text-warning fw-bold">0.00</span> L
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Selected Total Amount:</strong> 
                                                Rs. <span id="selectedAmount" class="text-success fw-bold">0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        `;

                $('#pendingReceivesContainer').html(receivesHtml);

                // Bind checkbox events only if there are pending receives
                if (data.pending_receives.length > 0) {
                    $('#selectAllReceives').off('click').on('click', function (e) {
                        const isChecked = $(this).prop('checked');
                        $('.receive-checkbox').prop('checked', isChecked).trigger('change');
                    });

                    $('.receive-checkbox').off('change').on('change', function () {
                        updateShortageSelectedTotal();
                        const allChecked = $('.receive-checkbox:checked').length === $('.receive-checkbox').length;
                        $('#selectAllReceives').prop('checked', allChecked);
                    });

                    updateShortageSelectedTotal();
                }

                // Load payment history in history tab
                if (historyData.success && historyData.payments && historyData.payments.length > 0) {
                    displayShortagePaymentHistory(historyData.payments, rate);
                } else {
                    $('#shortageHistoryBody').html(`
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No payment history found for this purchase.
                                </div>
                            `);
                }

                // Show/Hide payment section based on pending shortages
                if (totalShortage <= 0) {
                    $('#shortagePaymentSection').hide();
                    $('#alreadyPaidMessage').show();
                    $('#saveShortagePayment').hide();
                    toastr.info('All shortages have been paid for this purchase');
                } else {
                    $('#shortagePaymentSection').show();
                    $('#alreadyPaidMessage').hide();
                    $('#saveShortagePayment').show();
                    loadShortsageShifts(data.station_id);
                    loadShortageBankAccounts(data.station_id);
                    resetShortagePaymentMethodSelection();
                }

                $('#shortagePaymentModal').modal('show');

            } catch (error) {
                toastr.clear();
                console.error('Error:', error);
                toastr.error('Failed to load shortage details');
                resetShortageModal();
            }
        }


        // ✅ Function to display shortage payment history
        function displayShortagePaymentHistory(payments, currentRate) {
            if (!payments || payments.length === 0) {
                $('#shortageHistoryBody').html(`
                            <div class="alert alert-info text-center">
                                <i class="bi bi-info-circle me-2"></i>
                                No payment history found.
                            </div>
                        `);
                return;
            }

            let totalPaid = 0;
            let historyHtml = `
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Payment Date</th>
                                        <th>Receive Date</th>
                                        <th>Shortage (L)</th>
                                        <th>Amount (Rs.)</th>
                                        <th>Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

            payments.forEach((payment, index) => {
                const paymentDate = new Date(payment.created_at).toLocaleDateString();
                const receiveDate = payment.recive_date ? new Date(payment.recive_date).toLocaleDateString() : 'N/A';

                // ✅ Use total_amount from database (already stored correctly)
                const amount = parseFloat(payment.total_amount) || 0;
                const shortage = parseFloat(payment.total_shortage) || 0;
                const shiftNo = payment.shift_no || 'N/A';
                const paymentType = payment.payment_type || (payment.is_paid == 1 ? 'full' : 'partial');
                const typeBadge = paymentType === 'full' ?
                    '<span class="badge bg-success">Full</span>' :
                    '<span class="badge bg-warning">Partial</span>';
                const method = payment.account_id ?
                    '<span class="badge bg-primary"><i class="bi bi-bank me-1"></i> Bank</span>' :
                    '<span class="badge bg-success"><i class="bi bi-cash-stack me-1"></i> Cash</span>';

                totalPaid += amount;

                historyHtml += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${paymentDate}</td>
                                <td>${receiveDate}</td>
                                <td class="text-warning fw-bold">${shortage.toFixed(2)} L</td>
                                <td class="text-success fw-bold">Rs. ${amount.toFixed(2)}</td>

                                <td>${method}</td>
                            </tr>
                        `;
            });

            historyHtml += `
                            </tbody>
                            <tfoot class="table-primary">
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total Paid:</strong></td>
                                    <td colspan="4"><strong>Rs. ${totalPaid.toFixed(2)}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>`;

            $('#shortageHistoryBody').html(historyHtml);
        }


        // ✅ Update selected shortage total
        function updateShortageSelectedTotal() {
            let totalShortage = 0;
            let totalAmount = 0;

            $('.receive-checkbox:checked').each(function () {
                const shortage = parseFloat($(this).data('shortage') || 0);
                const amount = parseFloat($(this).data('amount') || 0);

                totalShortage += shortage;
                totalAmount += amount;
            });

            $('#selectedShortage').text(totalShortage.toFixed(2));
            $('#selectedAmount').text(totalAmount.toFixed(2));

            // Update payment amount field
            if ($('#shortagePaymentAmount').length) {
                $('#shortagePaymentAmount').val(totalAmount.toFixed(2));
                $('#maxPaymentAmount').text(totalAmount.toFixed(2));
            }

            // Enable/disable save button based on selection
            const saveBtn = $('#saveShortagePayment');
            if (totalAmount > 0) {
                saveBtn.prop('disabled', false);
            } else {
                saveBtn.prop('disabled', true);
            }
        }


        // ✅ Function to show shortage payment history
        function showShortagePaymentHistory(payments) {
            if (!payments || payments.length === 0) {
                $('#shortagePaymentHistorySection').hide();
                return;
            }

            let historyHtml = `
                                                            <div class="card mt-3" id="shortagePaymentHistorySection">
                                                                <div class="card-header bg-info bg-opacity-10">
                                                                    <h6 class="mb-0">
                                                                        <i class="bi bi-clock-history me-2"></i>
                                                                        Payment History
                                                                        <span class="badge bg-primary ms-2">${payments.length} payments</span>
                                                                    </h6>
                                                                </div>
                                                                <div class="card-body p-2">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-bordered">
                                                                            <thead class="table-light">
                                                                                <tr>
                                                                                    <th>Date</th>
                                                                                    <th>Shift</th>
                                                                                    <th>Method</th>
                                                                                    <th>Amount (Rs.)</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                        `;

            payments.forEach(payment => {
                const date = new Date(payment.created_at).toLocaleDateString();
                historyHtml += `
                                                                <tr>
                                                                    <td>${date}</td>
                                                                    <td>Shift #${payment.shift_no || 'N/A'}</td>
                                                                    <td>${payment.payment_method || 'cash'}</td>
                                                                    <td>Rs. ${parseFloat(payment.total_amount).toFixed(2)}</td>
                                                                </tr>
                                                            `;
            });

            historyHtml += `
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        `;

            $('#shortagePaymentHistorySection').remove();
            $('.modal-footer').before(historyHtml);
        }



        // ✅ Load shifts for shortage payment
        function loadShortsageShifts(stationId) {
            if (!stationId) {
                $('#shortageShift').html('<option value="">No station selected</option>');
                return;
            }
            $('#shortageShift').html('<option value="">Loading shifts...</option>');
            $.ajax({
                url: `/api/stations/${stationId}/open-shifts`,
                method: 'GET',
                success: function (resp) {
                    let shiftSelect = $("#shortageShift");
                    shiftSelect.empty().append(`<option value="">Select Shift...</option>`);
                    if (resp && resp.data && Array.isArray(resp.data) && resp.data.length > 0) {
                        resp.data.forEach(shift => {
                            shiftSelect.append(`<option value="${shift.id}">Shift #${shift.shift_no} (${shift.start_time})</option>`);
                        });
                    } else {
                        shiftSelect.append(`<option value="">No open shifts found</option>`);
                        toastr.info('No open shifts available for this station');
                    }
                },
                error: function (err) {
                    console.error('Failed to load shifts:', err);
                    $('#shortageShift').html('<option value="">Error loading shifts</option>');
                    toastr.error('Failed to load shifts');
                }
            });
        }


        // ✅ Load bank accounts for shortage payment
        function loadShortageBankAccounts(stationId) {
            let url = stationId ? `/api/stations/${stationId}/accounts` : '/api/accounts/category/bank';
            $.ajax({
                url: url,
                method: 'GET',
                success: function (resp) {
                    const accounts = Array.isArray(resp) ? resp : (resp && Array.isArray(resp.data) ? resp.data : []);
                    const banks = accounts.filter(a => (a.type || '').toString().toLowerCase() === 'bank');
                    let bankSelect = $("#shortageBankAccount");
                    bankSelect.empty().append(`<option value="">Select Bank Account...</option>`);
                    if (banks && banks.length > 0) {
                        banks.forEach(bank => {
                            const displayName = `${bank.name} - ${bank.account_number || 'N/A'} (${bank.bank_name || 'Bank'})`;
                            bankSelect.append(`<option value="${bank.id}">${displayName}</option>`);
                        });
                    } else {
                        bankSelect.append(`<option value="">No bank accounts found</option>`);
                    }
                },
                error: function (err) {
                    console.error('Failed to load bank accounts:', err);
                    $('#shortageBankAccount').html('<option value="">Error loading accounts</option>');
                }
            });
        }


        // ✅ Select payment method for shortage
        function selectShortagePaymentMethod(method) {
            $('#selectedPaymentMethod').val(method);
            $('.payment-method-card').removeClass('selected border-primary bg-light');
            $(`.payment-method-card[data-method="${method}"]`).addClass('selected border-primary bg-light');

            if (method === 'bank') {
                $('#shortageBankAccountField').show();
            } else {
                $('#shortageBankAccountField').hide();
            }

            const totalAmount = $('#selectedAmount').text() || $('#modalTotalAmount').text();
            $('#paymentSummaryAmount').text(totalAmount);
            $('#paymentSummaryMethod').text(method === 'cash' ? 'Cash' : 'Bank Transfer');
            $('#paymentSummary').show();
        }


        // ✅ Reset payment method selection
        function resetShortagePaymentMethodSelection() {
            $('#selectedPaymentMethod').val('');
            $('.payment-method-card').removeClass('selected border-primary bg-light');
            $('#shortageBankAccountField').hide();
            $('#paymentSummary').hide();
            $('#shortagePaymentAmount').val('');
        }


        // ✅ Updated save shortage payment with receive selection
        $('#saveShortagePayment').on('click', async function () {
            const purchaseId = $('#shortagePurchaseId').val();
            const shiftId = $('#shortageShift').val();
            const paymentMethod = $('#selectedPaymentMethod').val();
            const bankAccountId = $('#shortageBankAccount').val();

            const selectedReceives = [];
            $('.receive-checkbox:checked').each(function () {
                selectedReceives.push($(this).val());
            });

            if (selectedReceives.length === 0) {
                toastr.error('Please select at least one receive to pay shortage for');
                return;
            }

            // ✅ Calculate total from selected checkboxes (this is already shortage * rate)
            const totalShortage = parseFloat($('#selectedShortage').text()) || 0;
            const totalAmount = parseFloat($('#selectedAmount').text()) || 0;

            if (totalShortage <= 0) {
                toastr.error('Invalid shortage amount');
                return;
            }

            if (!shiftId) {
                toastr.error('Please select a shift');
                return;
            }

            if (!paymentMethod) {
                toastr.error('Please select a payment method');
                return;
            }

            if (paymentMethod === 'bank' && !bankAccountId) {
                toastr.error('Please select a bank account');
                return;
            }

            const totalPendingShortage = parseFloat($('#modalTotalShortage').text()) || 0;
            const isPartial = Math.abs(totalShortage - totalPendingShortage) > 0.01;

            const confirmMsg = `Confirm Shortage Payment\n\n` +
                `Selected Receives: ${selectedReceives.length}\n` +
                `Total Shortage: ${totalShortage.toFixed(2)} Liters\n` +
                `Total Amount: Rs. ${totalAmount.toFixed(2)}\n` +
                `Payment Method: ${paymentMethod === 'cash' ? 'Cash' : 'Bank Transfer'}\n` +
                (isPartial ? `⚠️ This is a PARTIAL payment\n` : `✅ This will pay ALL selected shortages\n`) +
                `\nAre you sure you want to record this payment?`;

            if (!confirm(confirmMsg)) return;

            const saveBtn = $(this);
            const originalText = saveBtn.html();
            saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

            try {
                const response = await fetch(`${apiBase}/oil-purchases/${purchaseId}/shortage-payment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
                    },
                    body: JSON.stringify({
                        shift_id: shiftId,
                        payment_method: paymentMethod,
                        account_id: paymentMethod === 'bank' ? bankAccountId : null,
                        total_shortage: totalShortage,
                        total_amount: totalAmount,
                        receive_ids: selectedReceives,
                        is_partial: isPartial
                    })
                });

                const result = await response.json();

                if (result.success) {
                    toastr.success(`Shortage payment recorded successfully! Amount: Rs. ${totalAmount.toFixed(2)}`);
                    $('#shortagePaymentModal').modal('hide');
                    loadHistory(); // Refresh the history table

                    if (result.bank_balance_updated === 'yes') {
                        toastr.success('Bank balance updated successfully');
                    }
                } else {
                    throw new Error(result.message || 'Payment failed');
                }

            } catch (error) {
                console.error('Payment error:', error);
                toastr.error(error.message || 'Failed to record payment');
            } finally {
                saveBtn.prop('disabled', false).html(originalText);
            }
        });


        // ✅ RESET SHORTAGE MODAL FUNCTION
        function resetShortageModal() {
            console.log('Resetting shortage modal...');

            $('#pendingReceivesContainer').empty();
            $('#receivesTableWrapper').remove();

            $('#shortagePurchaseId').val('');
            $('#shortageStationId').val('');
            $('#modalTotalShortage').text('0');
            $('#modalRate').text('0');
            $('#modalTotalAmount').text('0');

            $('#shortageShift').html('<option value="">Select Shift...</option>');
            $('#shortageBankAccount').html('<option value="">Select Bank Account...</option>');

            $('#selectedPaymentMethod').val('');
            $('.payment-method-card').removeClass('selected border-primary bg-light');
            $('#shortageBankAccountField').hide();
            $('#paymentSummary').hide();

            $('#shortagePaymentAmount').val('');
            $('#maxPaymentAmount').text('0');
            $('#selectedShortage').text('0.00');
            $('#selectedAmount').text('0.00');

            $('#shortagePaymentSection').hide();
            $('#alreadyPaidMessage').hide();
            $('#saveShortagePayment').hide();

            $('#shortageHistoryBody').html(`
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading payment history...</p>
                            </div>
                        `);

            console.log('Shortage modal reset complete');
        }

        // ✅ Shortage Payment Modal close hone pe reset
        $('#shortagePaymentModal').on('hidden.bs.modal', function () {
            resetShortageModal();
        });


    </script>
@endsection