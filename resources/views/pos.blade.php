@extends('partials.layouts.master')

@section('title', 'POS | FabKin')
@section('title-sub', 'Sales')
@section('pagetitle', 'Point of Sale')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <style>
        .pos-wrapper {
            display: flex;
            gap: 18px;
            align-items: flex-start;
            overflow: hidden;
        }

        .pos-left {
            width: 65%;
            overflow: hidden;
        }

        .pos-right {
            width: 35%;
            overflow: hidden;
        }

        .product-card {
            cursor: pointer;
            transition: transform .12s;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        }

        .cart-table td,
        .cart-table th {
            vertical-align: middle;
        }

        .muted-small {
            font-size: .9rem;
            color: #666;
        }

        .receipt-preview {
            border: 1px dashed #ddd;
            padding: 12px;
            max-width: 360px;
            background: #fff;
        }

        .receipt-line {
            display: flex;
            justify-content: space-between;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #receiptmodalprint,
            #receiptmodalprint * {
                visibility: visible;
            }

            #receiptmodalprint {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Point of Sale</h5>
                        <div>
                            <button id="btn-save-draft" class="btn btn-outline-secondary btn-sm">Save Draft</button>
                            <button id="btn-load-draft" class="btn btn-outline-dark btn-sm">Load Draft</button>
                            <button id="btn-history" class="btn btn-outline-success btn-sm">Order History</button>
                        </div>
                    </div>

                    <div class="pos-wrapper">
                        <!-- LEFT: Products -->
                        <div class="pos-left">
                            <div class="row mb-3 g-2">
                                <div class="col-md-4">
                                    <select id="categoryFilter" class="form-select">
                                        <option value="">All Categories</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input id="productSearch" class="form-control" placeholder="Search product name...">
                                </div>
                            </div>

                            <div id="productsGrid" class="row g-3"></div>
                        </div>

                        <!-- RIGHT: Cart -->
                        <div class="pos-right">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6>Cart <span id="cart-count" class="badge bg-primary badge-small ms-2">0</span></h6>
                                    <div class="table-responsive" style="max-height:360px;overflow:auto">
                                        <table class="table cart-table mt-2">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Item</th>
                                                    <th class="text-center">Qty</th>
                                                    <th class="text-end">Price</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="cartBody"></tbody>
                                        </table>
                                    </div>

                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between">
                                            <div class="muted-small">Subtotal</div>
                                            <div id="subtotal" class="fw-bold">0.00</div>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <div class="muted-small">Tax (10%)</div>
                                            <div id="tax" class="fw-bold">0.00</div>
                                        </div>
                                        <div class="d-flex justify-content-between fs-5 mt-2">
                                            <div>Total</div>
                                            <div id="total" class="fw-bold">0.00</div>
                                        </div>
                                    </div>

                                    <div class="mt-3 d-grid gap-2">
                                        <button id="btn-checkout" class="btn btn-primary">Checkout / Save Order</button>
                                        <button id="btn-print" class="btn btn-success">Print & Save Receipt</button>
                                        <button id="btn-clear" class="btn btn-light">Clear</button>

                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h6 class="mb-2">Receipt Preview</h6>
                                    <div class="receipt-preview" id="receiptPreview"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- History Modal -->
                    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Order History</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered table-striped" id="historyTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>Category</th>
                                                <th>Qty</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                                <th>Date</th>
                                                <th>Payment</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Draft Modal -->
                    <div class="modal fade" id="draftModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Saved Drafts</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-striped" id="draftTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Products</th>
                                                <th>Total</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ ADD THIS RECEIPT MODAL -->
                    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="receiptModalLabel">Receipt</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="receiptmodalprint">
                                    <div class="text-center p-4 text-muted">
                                        Select a receipt to view details
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" onclick="printReceipt()">Print</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div id="receipt-print-area" style="display:none;"></div>
        </div>
    </div>
    </main>
@endsection

@section('js')
    <!-- Add jQuery before your custom scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = "api";
        const AUTH_USER_ID = "{{ Auth::id() }}";


        let state = {
            categories: [],
            products: [],
            cart: []
        };

        // ======== 🔹 Toast Utility ==========
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed top-0 end-0 m-3`;
            toast.style.zIndex = 9999;
            toast.role = 'alert';
            toast.innerHTML = `
                                <div class="d-flex">
                                    <div class="toast-body">${message}</div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                                </div>`;
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast, { delay: 2500 });
            bsToast.show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        }

        // ======== 🔹 Load Categories & Products ==========
        async function loadData() {
            try {
                const [catRes, prodRes] = await Promise.all([
                    fetch(`${API_BASE}/category`).then(r => r.json()),
                    fetch(`${API_BASE}/store-product/${AUTH_USER_ID}`).then(r => r.json())
                ]);
                state.categories = catRes;
                state.products = prodRes;
                renderCategories();
                renderProducts();
            } catch (e) {
                console.error(e);
                showToast("Failed to load data", "danger");
            }
        }

        function renderCategories() {
            const sel = document.getElementById('categoryFilter');
            sel.innerHTML = `<option value="">All Categories</option>` + state.categories.map(c =>
                `<option value="${c.id}">${c.name}</option>`).join('');
        }

        function renderProducts() {
            const grid = document.getElementById('productsGrid');
            const catFilter = document.getElementById('categoryFilter').value;
            const search = document.getElementById('productSearch').value.toLowerCase();

            const list = state.products.filter(p =>
                (!catFilter || p.category_id == catFilter) &&
                (!search || p.product_name.toLowerCase().includes(search))
            );

            grid.innerHTML = list.map(p => `
                                <div class="col-md-3">
                                    <div class="card product-card" onclick="addToCart(${p.id})">
                                        <img src="/${p.product_image}" class="card-img-top" style="height:120px;object-fit:cover;">
                                        <div class="card-body p-2">
                                            <div class="fw-semibold">${p.product_name}</div>
                                            <div class="muted-small">${p.category_name}</div>
                                            <div class="fw-bold text-end">PKR ${Number(p.unit_price).toFixed(2)}</div>
                                        </div>
                                    </div>
                                </div>
                            `).join('');
        }

        function addToCart(id) {
            const p = state.products.find(x => x.id == id);
            if (!p) return;
            const existing = state.cart.find(i => i.id == id);
            if (existing) existing.qty++;
            else state.cart.push({
                id: id,
                name: p.product_name,
                price: parseFloat(p.unit_price),
                qty: 1,
                category_id: p.category_id,
                store_id: p.store_id
            });
            renderCart();
        }

        function renderCart() {
            const tbody = document.getElementById('cartBody');
            tbody.innerHTML = '';
            let subtotal = 0;
            state.cart.forEach(item => {
                const line = item.price * item.qty;
                subtotal += line;
                tbody.innerHTML += `
                                    <tr>
                                        <td>${item.name}</td>
                                        <td class="text-center">${item.qty}</td>
                                        <td class="text-end">PKR ${line.toFixed(2)}</td>
                                        <td><button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${item.id})">&times;</button></td>
                                    </tr>`;
            });
            const tax = subtotal * 0.10;
            const total = subtotal + tax;
            document.getElementById('subtotal').innerText = subtotal.toFixed(2);
            document.getElementById('tax').innerText = tax.toFixed(2);
            document.getElementById('total').innerText = total.toFixed(2);
            document.getElementById('cart-count').innerText = state.cart.reduce((s, i) => s + i.qty, 0);
            renderReceiptPreview();
        }

        function removeFromCart(id) {
            state.cart = state.cart.filter(i => i.id != id);
            renderCart();
        }

        // ======== 🔹 Save Drafts ==========
        document.getElementById('btn-save-draft').addEventListener('click', () => {
            if (!state.cart.length) return showToast("Cart is empty!", "warning");
            const draft = { cart: state.cart, date: new Date().toLocaleString() };
            const drafts = JSON.parse(localStorage.getItem('pos_drafts') || '[]');
            drafts.push(draft);
            localStorage.setItem('pos_drafts', JSON.stringify(drafts));
            showToast("Draft saved successfully!");
        });

        document.getElementById('btn-load-draft').addEventListener('click', () => {
            const drafts = JSON.parse(localStorage.getItem('pos_drafts') || '[]');
            const tbody = document.querySelector('#draftTable tbody');
            if (!drafts.length) {
                tbody.innerHTML = `<tr><td colspan='5' class='text-center'>No drafts saved</td></tr>`;
            } else {
                tbody.innerHTML = drafts.map((d, i) => `
                                    <tr>
                                        <td>${i + 1}</td>
                                        <td>${d.cart.map(c => c.name).join(', ')}</td>
                                        <td>${d.cart.reduce((t, c) => t + (c.qty * c.price), 0).toFixed(2)}</td>
                                        <td>${d.date}</td>
                                        <td>
                                            <button class='btn btn-sm btn-primary' onclick='loadDraft(${i})'>Load</button>
                                            <button class='btn btn-sm btn-danger' onclick='deleteDraft(${i})'>Delete</button>
                                        </td>
                                    </tr>`).join('');
            }
            new bootstrap.Modal(document.getElementById('draftModal')).show();
        });

        function deleteDraft(index) {
            const drafts = JSON.parse(localStorage.getItem('pos_drafts') || '[]');
            drafts.splice(index, 1);
            localStorage.setItem('pos_drafts', JSON.stringify(drafts));
            showToast("Draft deleted!", "danger");

            // Close modal first (to remove backdrop cleanly)
            const modalEl = document.getElementById('draftModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            // Wait for modal to fully close before reopening
            modalEl.addEventListener('hidden.bs.modal', () => {
                document.getElementById('btn-load-draft').click();
            }, { once: true });
        }

        function loadDraft(index) {
            const drafts = JSON.parse(localStorage.getItem('pos_drafts') || '[]');
            const draft = drafts[index];
            if (!draft) return;
            state.cart = draft.cart;
            renderCart();
            bootstrap.Modal.getInstance(document.getElementById('draftModal')).hide();
        }

        // ======== 🔹 Save Order ==========
        async function saveOrder(printAfter = false) {
            if (!state.cart.length) return showToast("Cart is empty!", "warning");

            const firstItem = state.cart[0];
            const storeData = state.products.find(p => p.id == firstItem.id);
            const storeId = storeData ? storeData.store_id : 1;

            const payload = {
                store_id: storeId,
                category_id: state.cart.map(i => i.category_id).join(','),
                product_id: state.cart.map(i => i.id).join(','),
                price: state.cart.map(i => i.price).join(','),
                quantity: state.cart.map(i => i.qty).join(','),
                total: state.cart.reduce((sum, i) => sum + (i.price * i.qty), 0),
                discount: 0,
                tax: 0,
                payment_type: "cash",
                status: "completed",
                date: new Date().toISOString().split('T')[0]
            };

            try {
                const res = await fetch(`${API_BASE}/pos/orders`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const json = await res.json();
                console.log("Order save response:", json);

                if (json.success) {
                    showToast("Order saved successfully!", "success");

                    // ✅ Fix: Support both `order_id` and `id`
                    const orderId = json.order_id || json.id || json.data?.id;

                    if (printAfter && orderId) {
                        console.log('Printing thermal receipt for order:', orderId);
                        showToast('Printing thermal receipt...', 'info');

                        // ✅ Small delay ensures DB is fully committed
                        setTimeout(() => {
                            fetch(`${API_BASE}/print-receipt/${orderId}`)
                                .then(r => r.json())
                                .then(d => {
                                    if (d.success) {
                                        showToast("🖨️ Receipt printed successfully!", "success");
                                    } else {
                                        showToast("⚠️ Print failed: " + d.error, "danger");
                                    }
                                })
                                .catch(err => {
                                    console.error(err);
                                    showToast("Printer connection error", "danger");
                                });
                        }, 800);
                    } else if (printAfter && !orderId) {
                        showToast("⚠️ No order ID returned from server", "danger");
                    }

                    clearCart();
                } else {
                    showToast("Failed: " + (json.message || 'Validation error'), "danger");
                }
            } catch (e) {
                console.error(e);
                showToast("Error saving order", "danger");
            }
        }

        // ======== 🔹 Order History ==========
        document.getElementById('btn-history').addEventListener('click', async () => {
            try {
                const res = await fetch(`${API_BASE}/pos/orders/${AUTH_USER_ID}`);
                const json = await res.json();

                const tbody = document.querySelector('#historyTable tbody');
                if (!json.success || !json.data.length) {
                    tbody.innerHTML = `<tr><td colspan='9' class='text-center'>No orders found</td></tr>`;
                } else {
                                     tbody.innerHTML = json.data.map((o, i) => {


                        return `
                                                <tr>
                                                    <td>${i + 1}</td>
                                                    <td>${o.product_names}</td>
                                                    <td>${o.category_names}</td>
                                                    <td>${o.total_quantity}</td>
                                                    <td>${o.product_prices}</td>
                                                    <td>${o.total_amount}</td>
                                                    <td>${o.date}</td>
                                                    <td>${o.payment_type}</td>
                                                    <td>
    												<button class='btn btn-sm btn-success' onclick='printThermalReceipt("${o.order_id}")'>
                                                        Print
                                                        </button>
                                                    </td>
                                                </tr>`;
                    }).join('');
                }

                new bootstrap.Modal(document.getElementById('historyModal')).show();
            } catch (e) {
                console.error(e);
                showToast("Error loading history", "danger");
            }
        });

        // ======== 🔹 Print Receipt ==========

        // Thermal printer function
        function printThermalReceipt(orderId) {
            console.log('Printing thermal receipt for order:', orderId);
            showToast('Printing thermal receipt...', 'info');

            // Use the CORRECT route - /print-receipt/{id}
            fetch(`${API_BASE}/print-receipt/${orderId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network error: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showToast('Receipt printed successfully!', 'success');
                    } else {
                        showToast('Print failed: ' + data.error, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Print error:', error);
                    showToast('Printing failed: ' + error.message, 'danger');
                });
        }



        function printOrderReceipt(order) {
            // Blade file se PDF print karega
            const printUrl = `api/pos/print-receipt/${order.id}`;
            window.open(printUrl, '_blank');
        }

        function clearCart() {
            state.cart = [];
            renderCart();
        }

        document.getElementById('btn-checkout').addEventListener('click', () => saveOrder(false));
        document.getElementById('btn-print').addEventListener('click', () => saveOrder(true));
        document.getElementById('btn-clear').addEventListener('click', clearCart);

        document.getElementById('categoryFilter').addEventListener('change', renderProducts);
        document.getElementById('productSearch').addEventListener('input', renderProducts);

        function renderReceiptPreview() {
            const div = document.getElementById('receiptPreview');
            if (!state.cart.length) return div.innerHTML = "<em>Empty</em>";
            div.innerHTML = state.cart.map(it => `
                                <div class="receipt-line">
                                    <div>${it.name} x${it.qty}</div>
                                    <div>PKR ${(it.qty * it.price).toFixed(2)}</div>
                                </div>`).join('');
        }


        // Init
        loadData();
    </script>
@endsection